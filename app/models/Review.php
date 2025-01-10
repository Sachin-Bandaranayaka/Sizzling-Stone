<?php
class Review {
    private $conn;
    private $table_name = "reviews";

    public $review_id;
    public $user_id;
    public $rating;
    public $review_text;
    public $is_approved;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        error_log("Fetching all reviews...");
        
        // Modified query to show all reviews, regardless of approval status
        $query = "SELECT r.*, u.username, 
                        CASE 
                            WHEN r.is_approved = 1 THEN 'Approved'
                            ELSE 'Pending'
                        END as status
                 FROM " . $this->table_name . " r
                 JOIN users u ON r.user_id = u.user_id
                 ORDER BY r.created_at DESC";

        try {
            error_log("Executing query: " . $query);
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Found " . count($result) . " reviews");
            
            if (empty($result)) {
                error_log("No reviews found in database");
                $checkQuery = "SELECT COUNT(*) as count FROM " . $this->table_name;
                $checkStmt = $this->conn->query($checkQuery);
                $count = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
                error_log("Total reviews in database: " . $count);
                
                // Check if the table exists
                $tableCheckQuery = "SHOW TABLES LIKE '" . $this->table_name . "'";
                $tableCheckStmt = $this->conn->query($tableCheckQuery);
                $tableExists = $tableCheckStmt->rowCount() > 0;
                error_log("Reviews table exists: " . ($tableExists ? 'Yes' : 'No'));
                
                if ($tableExists) {
                    // Check table structure
                    $describeQuery = "DESCRIBE " . $this->table_name;
                    $describeStmt = $this->conn->query($describeQuery);
                    $columns = $describeStmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("Table structure: " . print_r($columns, true));
                }
            } else {
                error_log("First review in result: " . print_r($result[0], true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in Review::getAll(): " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }

    public function create() {
        error_log("Starting review creation with data: " . print_r([
            'user_id' => $this->user_id,
            'rating' => $this->rating,
            'review_text' => $this->review_text,
            'is_approved' => $this->is_approved
        ], true));

        $query = "INSERT INTO " . $this->table_name . "
                (user_id, rating, review_text, is_approved)
                VALUES
                (:user_id, :rating, :review_text, :is_approved)";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $this->review_text = htmlspecialchars(strip_tags($this->review_text));

            // Set default values
            $this->is_approved = $this->is_approved ?? false;

            error_log("Executing query: " . $query);
            error_log("With parameters: " . print_r([
                'user_id' => $this->user_id,
                'rating' => $this->rating,
                'review_text' => $this->review_text,
                'is_approved' => $this->is_approved
            ], true));

            // Bind parameters
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":rating", $this->rating);
            $stmt->bindParam(":review_text", $this->review_text);
            $stmt->bindParam(":is_approved", $this->is_approved, PDO::PARAM_BOOL);

            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Failed to execute review creation. Error info: " . print_r($stmt->errorInfo(), true));
            } else {
                error_log("Review created successfully with ID: " . $this->conn->lastInsertId());
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in Review::create(): " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function update($reviewId, $rating, $review_text) {
        $query = "UPDATE " . $this->table_name . "
                SET rating = :rating,
                    review_text = :review_text,
                    updated_at = CURRENT_TIMESTAMP
                WHERE review_id = :review_id";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $review_text = htmlspecialchars(strip_tags($review_text));

            // Bind parameters
            $stmt->bindParam(":review_id", $reviewId);
            $stmt->bindParam(":rating", $rating);
            $stmt->bindParam(":review_text", $review_text);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in Review::update(): " . $e->getMessage());
            return false;
        }
    }

    public function delete($reviewId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE review_id = :review_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":review_id", $reviewId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in Review::delete(): " . $e->getMessage());
            return false;
        }
    }

    public function toggleApproval($reviewId) {
        $query = "UPDATE " . $this->table_name . "
                SET is_approved = NOT is_approved,
                    updated_at = CURRENT_TIMESTAMP
                WHERE review_id = :review_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":review_id", $reviewId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in Review::toggleApproval(): " . $e->getMessage());
            return false;
        }
    }

    public function getByUserId($userId) {
        $query = "SELECT r.*, u.username 
                 FROM " . $this->table_name . " r
                 LEFT JOIN users u ON r.user_id = u.user_id
                 WHERE r.user_id = :user_id
                 ORDER BY r.created_at DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in Review::getByUserId(): " . $e->getMessage());
            return [];
        }
    }

    public function getReviewStatistics() {
        try {
            // Get total reviews
            $totalQuery = "SELECT COUNT(*) as total_reviews, 
                                 AVG(rating) as average_rating,
                                 (SELECT COUNT(*) * 100.0 / NULLIF(COUNT(*), 0) 
                                  FROM " . $this->table_name . " 
                                  WHERE rating = 5) as five_star_percentage
                          FROM " . $this->table_name;
            
            $stmt = $this->conn->prepare($totalQuery);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_reviews' => (int)$result['total_reviews'],
                'average_rating' => $result['total_reviews'] > 0 ? round($result['average_rating'], 1) : 0,
                'five_star_percentage' => $result['total_reviews'] > 0 ? round($result['five_star_percentage'], 0) : 0
            ];
            
        } catch (PDOException $e) {
            error_log("Error in Review::getReviewStatistics(): " . $e->getMessage());
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'five_star_percentage' => 0
            ];
        }
    }

    public function getReportedReviews() {
        $query = "SELECT r.*, u.username 
                FROM " . $this->table_name . " r
                LEFT JOIN users u ON r.user_id = u.user_id
                WHERE r.is_reported = 1
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function updateStatus($reviewId, $isReported) {
        $query = "UPDATE " . $this->table_name . "
                SET is_reported = :is_reported
                WHERE review_id = :review_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":review_id", $reviewId);
        $stmt->bindParam(":is_reported", $isReported, PDO::PARAM_BOOL);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function reportReview($reviewId) {
        return $this->updateStatus($reviewId, true);
    }

    public function approveReview($reviewId) {
        return $this->updateStatus($reviewId, false);
    }

    public function getReviewById($reviewId) {
        $query = "SELECT r.*, u.username 
                 FROM " . $this->table_name . " r
                 LEFT JOIN users u ON r.user_id = u.user_id
                 WHERE r.review_id = :review_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":review_id", $reviewId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in Review::getReviewById(): " . $e->getMessage());
            return null;
        }
    }

    public function getTotalReviews() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getRecentReviews($limit = 5) {
        $query = "SELECT r.*, u.username 
                 FROM " . $this->table_name . " r
                 JOIN users u ON r.user_id = u.user_id
                 ORDER BY r.created_at DESC 
                 LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
}
