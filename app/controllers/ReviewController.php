<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/User.php';

class ReviewController {
    private $reviewModel;
    private $userModel;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->reviewModel = new Review($db);
        $this->userModel = new User($db);
    }

    public function getAllReviews() {
        try {
            $reviews = $this->reviewModel->getAll();
            // Check for admin role instead of is_admin flag
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                return $reviews;
            } else {
                $reviews = array_filter($reviews, function($review) {
                    return $review['is_approved'] == 1;
                });
            }
            return array_values($reviews); // Reset array keys after filtering
        } catch (Exception $e) {
            error_log("Error in ReviewController::getAllReviews: " . $e->getMessage());
            return [];
        }
    }

    public function getReviewStatistics() {
        try {
            $reviews = $this->getAllReviews();
            
            $totalReviews = count($reviews);
            if ($totalReviews === 0) {
                return [
                    'total_reviews' => 0,
                    'average_rating' => 0,
                    'five_star_percentage' => 0
                ];
            }

            $totalRating = 0;
            $fiveStarCount = 0;

            foreach ($reviews as $review) {
                $totalRating += $review['rating'];
                if ($review['rating'] == 5) {
                    $fiveStarCount++;
                }
            }

            $averageRating = $totalRating / $totalReviews;
            $fiveStarPercentage = ($fiveStarCount / $totalReviews) * 100;

            return [
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating, 1),
                'five_star_percentage' => round($fiveStarPercentage)
            ];
        } catch (Exception $e) {
            error_log("Error in ReviewController::getReviewStatistics: " . $e->getMessage());
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'five_star_percentage' => 0
            ];
        }
    }

    public function createReview($userId, $rating, $reviewText) {
        try {
            $this->reviewModel->user_id = $userId;
            $this->reviewModel->rating = $rating;
            $this->reviewModel->review_text = $reviewText;
            $this->reviewModel->is_approved = false;

            return $this->reviewModel->create();
        } catch (Exception $e) {
            error_log("Error in ReviewController::createReview: " . $e->getMessage());
            return false;
        }
    }

    public function updateReview($reviewId, $rating, $reviewText) {
        try {
            return $this->reviewModel->update($reviewId, $rating, $reviewText);
        } catch (Exception $e) {
            error_log("Error in ReviewController::updateReview: " . $e->getMessage());
            return false;
        }
    }

    public function deleteReview($reviewId) {
        try {
            return $this->reviewModel->delete($reviewId);
        } catch (Exception $e) {
            error_log("Error in ReviewController::deleteReview: " . $e->getMessage());
            return false;
        }
    }

    public function approveReview($reviewId) {
        try {
            return $this->reviewModel->update($reviewId, ['is_approved' => true]);
        } catch (Exception $e) {
            error_log("Error in ReviewController::approveReview: " . $e->getMessage());
            return false;
        }
    }

    public function toggleApproval($reviewId) {
        try {
            if ($this->reviewModel->toggleApproval($reviewId)) {
                $review = $this->reviewModel->getReviewById($reviewId);
                $status = $review['is_approved'] ? 'approved' : 'unapproved';
                return [
                    'success' => true,
                    'message' => "Review has been {$status} successfully"
                ];
            }
            return [
                'success' => false,
                'message' => 'Failed to update review status'
            ];
        } catch (Exception $e) {
            error_log("Error in ReviewController::toggleApproval: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while updating the review status'
            ];
        }
    }

    public function getUserReviews($userId) {
        return $this->reviewModel->getByUserId($userId);
    }

    public function reportReview($reviewId) {
        if($this->reviewModel->reportReview($reviewId)) {
            return ['success' => true, 'message' => 'Review reported successfully'];
        }
        return ['success' => false, 'message' => 'Unable to report review'];
    }

    public function getReviewById($reviewId) {
        return $this->reviewModel->getReviewById($reviewId);
    }
}
