<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/PaymentController.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to continue';
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'orders/');
    exit();
}

$orderId = $_POST['order_id'] ?? null;
if (!$orderId) {
    $_SESSION['error'] = 'Invalid order';
    header('Location: ' . BASE_URL . 'orders/');
    exit();
}

try {
    $paymentController = new PaymentController();
    
    // Initiate payment
    $paymentDetails = $paymentController->initiatePayment($orderId);
    
    // Process payment
    $paymentData = [
        'payment_method' => 'credit_card',
        'card_number' => substr($_POST['card_number'], -4), // Only store last 4 digits
        'card_name' => $_POST['card_name']
    ];
    
    $result = $paymentController->processPayment($paymentDetails['payment_id'], $paymentData);
    
    $_SESSION['success'] = 'Payment processed successfully!';
    header('Location: ' . BASE_URL . 'orders/');
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Payment failed: ' . $e->getMessage();
    header('Location: ' . BASE_URL . 'orders/pay.php?order_id=' . $orderId);
    exit();
}
