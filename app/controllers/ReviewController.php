<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Review.php';

class ReviewController {
    private $db;
    private $review;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->review = new Review($this->db);
    }

    public function getAllReviews() {
        return $this->review->getAll();
    }

    public function getReviewStatistics() {
        return $this->review->getAverageRating();
    }

    public function createReview($userId, $rating, $comment) {
        $this->review->user_id = $userId;
        $this->review->rating = $rating;
        $this->review->comment = $comment;
        return $this->review->create();
    }

    public function updateReview($reviewId, $rating, $comment) {
        return $this->review->update($reviewId, $rating, $comment);
    }

    public function deleteReview($reviewId, $userId) {
        return $this->review->delete($reviewId, $userId);
    }

    public function getUserReviews($userId) {
        return $this->review->getByUserId($userId);
    }
}
