<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/NotificationController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize response array
$response = ['success' => false, 'message' => 'Invalid request'];

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Please login to continue']);
        exit();
    }
    $_SESSION['error'] = 'Please login to continue';
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$notificationController = new NotificationController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'mark_read':
            if (!isset($_POST['notification_id'])) {
                $response = ['success' => false, 'message' => 'Missing notification ID'];
                break;
            }

            $notificationId = (int)$_POST['notification_id'];
            $response = $notificationController->markAsRead($notificationId);
            break;

        case 'mark_all_read':
            $response = $notificationController->markAllAsRead($_SESSION['user_id']);
            break;

        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }

    // Return JSON response for AJAX requests
    echo json_encode($response);
    exit();
}

// If someone tries to access this file directly without POST data
header('Location: ' . BASE_URL . 'notifications/index.php');
exit();
