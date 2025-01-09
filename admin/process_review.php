<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ReviewController.php';
require_once __DIR__ . '/../app/middleware/admin_auth.php';

$reviewController = new ReviewController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reviewId = $_POST['review_id'] ?? null;
    
    if (!$reviewId) {
        $_SESSION['error_message'] = 'Review ID is required';
        header('Location: ' . BASE_URL . 'admin/reviews.php');
        exit();
    }
    
    switch ($action) {
        case 'delete':
            $result = $reviewController->deleteReview($reviewId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Review deleted successfully';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        default:
            $_SESSION['error_message'] = 'Invalid action';
            break;
    }
}

header('Location: ' . BASE_URL . 'admin/reviews.php');
exit();
