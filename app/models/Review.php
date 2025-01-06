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
                WHERE id = :review_id
                AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $comment = htmlspecialchars(strip_tags($comment));

        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":review_id", $reviewId);
        $stmt->bindParam(":user_id", $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($reviewId, $userId) {
        $query = "DELETE FROM " . $this->table_name . "
                WHERE id = :review_id
                AND user_id = :user_id";

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

    public function getAverageRating() {
        $query = "SELECT AVG(rating) as average_rating, 
                        COUNT(*) as total_reviews
                 FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'average_rating' => $result['average_rating'] ? round($result['average_rating'], 1) : 0,
            'total_reviews' => (int)$result['total_reviews']
        ];
    }
}
