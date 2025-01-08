<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
    header('Location: ' . BASE_URL);
    exit();
}

$orderController = new OrderController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = $_POST['order_id'] ?? null;
    
    if (!$orderId) {
        $_SESSION['error_message'] = 'Order ID is required';
        header('Location: ' . BASE_URL . 'admin/orders.php');
        exit();
    }
    
    switch ($action) {
        case 'confirm':
            $result = $orderController->updateOrderStatus($orderId, 'confirmed');
            if ($result['success']) {
                $_SESSION['success_message'] = 'Order confirmed successfully';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        case 'complete':
            $result = $orderController->updateOrderStatus($orderId, 'completed');
            if ($result['success']) {
                $_SESSION['success_message'] = 'Order marked as completed';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        case 'cancel':
            $result = $orderController->updateOrderStatus($orderId, 'cancelled');
            if ($result['success']) {
                $_SESSION['success_message'] = 'Order cancelled successfully';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        default:
            $_SESSION['error_message'] = 'Invalid action';
            break;
    }
}

header('Location: ' . BASE_URL . 'admin/orders.php');
exit();
