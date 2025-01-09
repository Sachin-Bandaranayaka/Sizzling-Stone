<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');

// Debug logging
error_log("=== New Order Request ===");
error_log("Session data: " . print_r($_SESSION, true));

// Log the request data
error_log("Order request received. Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'message' => 'Please log in to place an order']);
    exit;
}

// Get POST data
$rawData = file_get_contents('php://input');
error_log("Raw request data: " . $rawData);

$data = json_decode($rawData, true);
error_log("Decoded data: " . print_r($data, true));

if (!$data) {
    $jsonError = json_last_error_msg();
    error_log("JSON decode error: " . $jsonError);
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid data format',
        'debug' => $jsonError
    ]);
    exit;
}

// Validate required fields
if (empty($data['items']) || empty($data['total_amount'])) {
    error_log("Missing required fields: " . 
              "items=" . (isset($data['items']) ? 'yes' : 'no') . ", " .
              "total_amount=" . (isset($data['total_amount']) ? 'yes' : 'no'));
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields',
        'debug' => [
            'items' => isset($data['items']),
            'total_amount' => isset($data['total_amount'])
        ]
    ]);
    exit;
}

try {
    $orderController = new OrderController();
    
    // Prepare order data
    $orderData = [
        'user_id' => $_SESSION['user_id'],
        'total_amount' => $data['total_amount'],
        'special_instructions' => $data['special_instructions'] ?? '',
        'order_type' => $data['order_type'] ?? 'dine_in',
        'items' => array_map(function($item) {
            return [
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price']
            ];
        }, $data['items'])
    ];

    error_log("Processed order data: " . print_r($orderData, true));

    // Create the order
    $result = $orderController->createOrder($orderData);
    error_log("Order creation result: " . print_r($result, true));
    
    if ($result['success']) {
        // Clear the cart in session if order is successful
        unset($_SESSION['cart']);
        error_log("Order created successfully, cart cleared");
    } else {
        error_log("Order creation failed: " . ($result['message'] ?? 'No error message'));
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    $errorMessage = "Error processing order: " . $e->getMessage();
    $stackTrace = "Stack trace: " . $e->getTraceAsString();
    error_log($errorMessage);
    error_log($stackTrace);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your order. Please try again.',
        'debug' => $errorMessage
    ]);
}
