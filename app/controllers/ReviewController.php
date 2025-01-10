<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Review.php';

class ReviewController {
    private $review;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->review = new Review($db);
    }

    public function getAllReviews() {
        return $this->review->getAll();
    }

    public function createReview($data) {
        $this->review->user_id = $data['user_id'];
        $this->review->rating = $data['rating'];
        $this->review->review_text = $data['review_text'];
        $this->review->is_approved = false;

        if($this->review->create()) {
            return ['success' => true, 'message' => 'Review created successfully'];
        }
        return ['success' => false, 'message' => 'Unable to create review'];
    }

    public function updateReview($reviewId, $rating, $review_text) {
        if($this->review->update($reviewId, $rating, $review_text)) {
            return ['success' => true, 'message' => 'Review updated successfully'];
        }
        return ['success' => false, 'message' => 'Unable to update review'];
    }

    public function deleteReview($reviewId) {
        if($this->review->delete($reviewId)) {
            return ['success' => true, 'message' => 'Review deleted successfully'];
        }
        return ['success' => false, 'message' => 'Unable to delete review'];
    }

    public function toggleApproval($reviewId) {
        if($this->review->toggleApproval($reviewId)) {
            return ['success' => true, 'message' => 'Review approval status updated successfully'];
        }
        return ['success' => false, 'message' => 'Unable to update review approval status'];
    }

    public function getUserReviews($userId) {
        return $this->review->getByUserId($userId);
    }

    public function getReviewStatistics() {
        return $this->review->getReviewStatistics();
    }

    public function reportReview($reviewId) {
        if($this->review->reportReview($reviewId)) {
            return ['success' => true, 'message' => 'Review reported successfully'];
        }
        return ['success' => false, 'message' => 'Unable to report review'];
    }

    public function getReviewById($reviewId) {
        return $this->review->getReviewById($reviewId);
    }
}
