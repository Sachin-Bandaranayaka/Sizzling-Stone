<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/OrderController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize response array
$response = ['success' => false, 'message' => 'Invalid request'];

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Please login to place an order']);
        exit();
    }
    $_SESSION['error'] = 'Please login to place an order';
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$orderController = new OrderController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            // Log incoming data
            error_log('Creating order. POST data: ' . print_r($_POST, true));

            // Validate cart data
            if (!isset($_POST['cart']) || empty($_POST['cart'])) {
                $response = ['success' => false, 'message' => 'Cart is empty'];
                break;
            }

            $cart = json_decode($_POST['cart'], true);
            if (!$cart || empty($cart['items'])) {
                $response = ['success' => false, 'message' => 'Invalid cart data: ' . json_last_error_msg()];
                break;
            }

            // Log cart data
            error_log('Cart data: ' . print_r($cart, true));

            // Create order data
            $orderData = [
                'user_id' => $_SESSION['user_id'],
                'total_amount' => $cart['total'],
                'special_instructions' => $_POST['special_instructions'] ?? '',
                'order_type' => $_POST['order_type'] ?? 'takeaway',
                'items' => $cart['items']
            ];

            // Log order data
            error_log('Order data: ' . print_r($orderData, true));

            try {
                $response = $orderController->createOrder($orderData);
                error_log('Order creation response: ' . print_r($response, true));
            } catch (Exception $e) {
                error_log('Error creating order: ' . $e->getMessage());
                $response = ['success' => false, 'message' => 'Error creating order: ' . $e->getMessage()];
            }
            break;

        case 'update_status':
            // Check if user is admin
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $response = ['success' => false, 'message' => 'Unauthorized access'];
                break;
            }

            if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
                $response = ['success' => false, 'message' => 'Missing required parameters'];
                break;
            }

            $orderId = (int)$_POST['order_id'];
            $status = $_POST['status'];

            // Validate status
            $validStatuses = ['confirmed', 'preparing', 'ready', 'completed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                $response = ['success' => false, 'message' => 'Invalid status'];
                break;
            }

            try {
                $response = $orderController->updateOrderStatus($orderId, $status);
            } catch (Exception $e) {
                error_log('Error updating order status: ' . $e->getMessage());
                $response = ['success' => false, 'message' => 'Error updating order status: ' . $e->getMessage()];
            }
            break;

        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }

    // Return JSON response for AJAX requests
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// If someone tries to access this file directly without POST data
header('Location: ' . BASE_URL . 'menu.php');
exit();
