<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Notification.php';

class OrderController {
    private $order;
    private $notification;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->order = new Order($db);
        $this->notification = new Notification($db);
    }

    public function createOrder($data) {
        // Set order properties
        $this->order->user_id = $data['user_id'];
        $this->order->total_amount = $data['total_amount'];
        $this->order->special_instructions = $data['special_instructions'] ?? '';
        $this->order->order_type = $data['order_type'];

        // Create the order
        if($this->order->create()) {
            // Add order items
            if($this->order->addOrderItems($data['items'])) {
                // Create notification for admin
                $this->notification->user_id = 1; // Admin user ID
                $this->notification->title = "New Order Received";
                $this->notification->message = "A new order (#" . $this->order->order_id . ") has been placed and requires your confirmation.";
                $this->notification->type = "order";
                $this->notification->reference_id = $this->order->order_id;
                $this->notification->create();

                return [
                    'success' => true,
                    'message' => 'Order created successfully',
                    'order_id' => $this->order->order_id
                ];
            }
        }
        return ['success' => false, 'message' => 'Unable to create order'];
    }

    public function updateOrderStatus($orderId, $status) {
        // Get order details first
        $result = $this->order->getById($orderId);
        $order = $result->fetch(PDO::FETCH_ASSOC);
        
        if(!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        // Update the status
        if($this->order->updateStatus($orderId, $status)) {
            // Create notification for customer
            $this->notification->user_id = $order['user_id'];
            $this->notification->type = "order";
            $this->notification->reference_id = $orderId;

            switch($status) {
                case 'confirmed':
                    $this->notification->title = "Order Confirmed";
                    $this->notification->message = "Your order (#$orderId) has been confirmed and is being prepared.";
                    break;
                case 'preparing':
                    $this->notification->title = "Order Being Prepared";
                    $this->notification->message = "Your order (#$orderId) is now being prepared in our kitchen.";
                    break;
                case 'ready':
                    $this->notification->title = "Order Ready";
                    $this->notification->message = "Your order (#$orderId) is ready for pickup/delivery.";
                    break;
                case 'completed':
                    $this->notification->title = "Order Completed";
                    $this->notification->message = "Your order (#$orderId) has been completed. Enjoy your meal!";
                    break;
                case 'cancelled':
                    $this->notification->title = "Order Cancelled";
                    $this->notification->message = "Your order (#$orderId) has been cancelled. Please contact us if you have any questions.";
                    break;
            }

            $this->notification->create();

            return [
                'success' => true,
                'message' => 'Order status updated successfully'
            ];
        }
        return ['success' => false, 'message' => 'Unable to update order status'];
    }

    public function getAllOrders() {
        return $this->order->getAll();
    }

    public function getPendingOrders() {
        return $this->order->getPendingOrders();
    }

    public function getOrderById($orderId) {
        return $this->order->getById($orderId);
    }

    public function getOrderItems($orderId) {
        return $this->order->getOrderItems($orderId);
    }

    public function getUserOrders($userId) {
        return $this->order->getUserOrders($userId);
    }
}
