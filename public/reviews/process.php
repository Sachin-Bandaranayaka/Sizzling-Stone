<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ReviewController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to submit a review']);
    exit;
}

$reviewController = new ReviewController();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
            $reviewText = isset($_POST['review_text']) ? htmlspecialchars(trim($_POST['review_text']), ENT_QUOTES, 'UTF-8') : '';
            
            if (!$rating || $rating < 1 || $rating > 5) {
                echo json_encode(['success' => false, 'message' => 'Invalid rating']);
                exit;
            }
            
            if (empty($reviewText)) {
                echo json_encode(['success' => false, 'message' => 'Review text is required']);
                exit;
            }
            
            if ($reviewController->createReview($_SESSION['user_id'], $rating, $reviewText)) {
                echo json_encode(['success' => true, 'message' => 'Your review has been submitted and is pending approval']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
            }
            break;
            
        case 'update':
            $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
            $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
            $reviewText = isset($_POST['review_text']) ? htmlspecialchars(trim($_POST['review_text']), ENT_QUOTES, 'UTF-8') : '';
            
            if (!$reviewId || !$rating || empty($reviewText)) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }
            
            if ($reviewController->updateReview($reviewId, $rating, $reviewText)) {
                echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update review']);
            }
            break;
            
        case 'delete':
            $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
            
            if (!$reviewId) {
                echo json_encode(['success' => false, 'message' => 'Invalid review ID']);
                exit;
            }
            
            if ($reviewController->deleteReview($reviewId)) {
                echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete review']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Error in process_review.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}
