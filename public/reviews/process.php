<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ReviewController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize response array
$response = ['success' => false, 'message' => 'Invalid request'];

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Please login to perform this action']);
        exit();
    }
    $_SESSION['error'] = 'Please login to perform this action';
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$reviewController = new ReviewController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle different actions
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            // Validate required fields
            if (!isset($_POST['rating']) || !isset($_POST['comment'])) {
                $response = ['success' => false, 'message' => 'Missing required fields'];
                break;
            }

            // Create review data array
            $reviewData = [
                'user_id' => $_SESSION['user_id'],
                'rating' => (int)$_POST['rating'],
                'comment' => $_POST['comment']
            ];

            // Validate rating
            if ($reviewData['rating'] < 1 || $reviewData['rating'] > 5) {
                $response = ['success' => false, 'message' => 'Invalid rating value'];
                break;
            }

            $response = $reviewController->createReview($reviewData);
            
            if ($response['success']) {
                $_SESSION['success_message'] = 'Thank you for your review!';
                header('Location: ' . BASE_URL);
                exit();
            }
            break;

        case 'update_status':
            // Check if user is admin
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $response = ['success' => false, 'message' => 'Unauthorized access'];
                break;
            }

            if (!isset($_POST['review_id']) || !isset($_POST['status'])) {
                $response = ['success' => false, 'message' => 'Missing required parameters'];
                break;
            }

            $reviewId = (int)$_POST['review_id'];
            $status = $_POST['status'];

            if ($status === 'approve') {
                $response = $reviewController->approveReview($reviewId);
            } elseif ($status === 'report') {
                $response = $reviewController->reportReview($reviewId);
            } else {
                $response = ['success' => false, 'message' => 'Invalid status'];
            }
            break;

        case 'delete':
            // Check if user is admin
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $response = ['success' => false, 'message' => 'Unauthorized access'];
                break;
            }

            if (!isset($_POST['review_id'])) {
                $response = ['success' => false, 'message' => 'Missing review ID'];
                break;
            }

            $reviewId = (int)$_POST['review_id'];
            $response = $reviewController->deleteReview($reviewId);
            break;

        case 'report':
            if (!isset($_POST['review_id'])) {
                $response = ['success' => false, 'message' => 'Missing review ID'];
                break;
            }

            $reviewId = (int)$_POST['review_id'];
            $response = $reviewController->reportReview($reviewId);
            break;

        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }

    // Return JSON response for AJAX requests
    echo json_encode($response);
    exit();
}

// If we reach here, there was an error
$_SESSION['error_message'] = $response['message'];
header('Location: ' . BASE_URL . 'public/reviews/create.php');
exit();
