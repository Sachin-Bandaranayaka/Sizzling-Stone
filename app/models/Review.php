<?php
class Review {
    private $conn;
    private $table_name = "reviews";

    public $review_id;
    public $user_id;
    public $rating;
    public $comment;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, rating, comment)
                VALUES
                (:user_id, :rating, :comment)";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->comment = htmlspecialchars(strip_tags($this->comment));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":comment", $this->comment);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($reviewId, $rating, $comment) {
        $query = "UPDATE " . $this->table_name . "
                SET rating = :rating,
                    comment = :comment
                WHERE review_id = :review_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $comment = htmlspecialchars(strip_tags($comment));

        $stmt->bindParam(":review_id", $reviewId);
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":comment", $comment);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($reviewId, $userId) {
        $query = "DELETE FROM " . $this->table_name . " 
                WHERE review_id = :review_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":review_id", $reviewId);
        $stmt->bindParam(":user_id", $userId);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getByUserId($userId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE user_id = :user_id
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();

        return $stmt;
    }

    public function getReviewStatistics() {
        $stats = [
            'total_reviews' => 0,
            'average_rating' => '0.0',
            'five_star_percentage' => '0'
        ];
        
        // Get total reviews
        $query = "SELECT COUNT(*) as total_reviews FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_reviews = (int)$result['total_reviews'];
        $stats['total_reviews'] = $total_reviews;

        if ($total_reviews > 0) {
            // Get average rating
            $query = "SELECT AVG(rating) as average_rating FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $average_rating = $result['average_rating'];
            $stats['average_rating'] = $average_rating ? number_format((float)$average_rating, 1) : '0.0';

            // Get percentage of 5-star reviews
            $query = "SELECT 
                        (COUNT(CASE WHEN rating = 5 THEN 1 END) * 100.0 / COUNT(*)) as five_star_percentage 
                     FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $five_star_percentage = $result['five_star_percentage'];
            $stats['five_star_percentage'] = $five_star_percentage ? number_format((float)$five_star_percentage, 0) : '0';
        }

        return $stats;
    }
}
