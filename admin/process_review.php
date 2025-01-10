<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/ReviewController.php';
require_once __DIR__ . '/../app/middleware/admin_auth.php';

$reviewController = new ReviewController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reviewId = $_POST['review_id'] ?? '';

    if (!$reviewId) {
        $_SESSION['error_message'] = 'Review ID is required';
        header('Location: reviews.php');
        exit;
    }

    switch ($action) {
        case 'approve':
        case 'unapprove':
            $result = $reviewController->toggleApproval($reviewId);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;

        case 'delete':
            $result = $reviewController->deleteReview($reviewId);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;

        default:
            $_SESSION['error_message'] = 'Invalid action';
    }
}

header('Location: reviews.php');
exit;
