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

    public function getReportedReviews() {
        return $this->review->getReportedReviews();
    }

    public function createReview($data) {
        $this->review->user_id = $data['user_id'];
        $this->review->rating = $data['rating'];
        $this->review->comment = $data['comment'];

        if($this->review->create()) {
            return ['success' => true, 'message' => 'Review created successfully'];
        }
        return ['success' => false, 'message' => 'Unable to create review'];
    }

    public function updateReview($reviewId, $data) {
        if($this->review->update($reviewId, $data['rating'], $data['comment'])) {
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

    public function approveReview($reviewId) {
        if($this->review->approveReview($reviewId)) {
            return ['success' => true, 'message' => 'Review approved successfully'];
        }
        return ['success' => false, 'message' => 'Unable to approve review'];
    }
}
