<?php
class Review {
    private $conn;
    private $table_name = "reviews";

    public $review_id;
    public $user_id;
    public $rating;
    public $comment;
    public $is_reported;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT r.review_id, r.user_id, r.rating, r.comment, r.created_at, r.is_reported,
                        u.username as customer_name 
                FROM " . $this->table_name . " r
                LEFT JOIN users u ON r.user_id = u.user_id
                ORDER BY r.created_at DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt; 
        } catch (PDOException $e) {
            error_log("Error in Review::getAll(): " . $e->getMessage());
            return false;
        }
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, rating, comment)
                VALUES
                (:user_id, :rating, :comment)";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $this->comment = htmlspecialchars(strip_tags($this->comment));

            // Bind parameters
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":rating", $this->rating);
            $stmt->bindParam(":comment", $this->comment);

            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error in Review::create(): " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $rating, $comment) {
        $query = "UPDATE " . $this->table_name . "
                SET rating = :rating,
                    comment = :comment
                WHERE review_id = :id";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $comment = htmlspecialchars(strip_tags($comment));

            // Bind parameters
            $stmt->bindParam(":rating", $rating);
            $stmt->bindParam(":comment", $comment);
            $stmt->bindParam(":id", $id);

            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error in Review::update(): " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE review_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error in Review::delete(): " . $e->getMessage());
            return false;
        }
    }

    public function getByUserId($userId) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE user_id = :user_id
                ORDER BY created_at DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            return $stmt;
            
        } catch (PDOException $e) {
            error_log("Error in Review::getByUserId(): " . $e->getMessage());
            return false;
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
                WHERE r.review_id = :review_id
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":review_id", $reviewId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
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
