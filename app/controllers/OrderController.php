<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Notification.php';

class OrderController {
    private $order;
    private $notification;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
        $this->notification = new Notification($this->db);
    }

    public function getAllOrders() {
        return $this->order->getAll();
    }

    public function getOrderById($id) {
        return $this->order->getById($id);
    }

    public function createOrder($data) {
        try {
            // Set order properties
            $this->order->user_id = $data['user_id'];
            $this->order->total_amount = $data['total_amount'];
            $this->order->special_instructions = $data['special_instructions'] ?? '';
            $this->order->order_type = $data['order_type'];
            $this->order->items = $data['items']; // Add the items to the order object

            // Create the order (this will also create order items in the same transaction)
            if($this->order->create()) {
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
            return ['success' => false, 'message' => 'Unable to create order'];
        } catch (PDOException $e) {
            error_log("Error in OrderController::createOrder: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'An error occurred while creating your order. Please try again.',
                'debug' => $e->getMessage()
            ];
        }
    }

    public function updateOrderStatus($orderId, $status) {
        if (!in_array($status, ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'])) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        if($this->order->updateStatus($orderId, $status)) {
            // Create notification for user
            $order = $this->getOrderById($orderId);
            if ($order) {
                $notificationTitle = "Order Status Updated";
                $notificationMessage = "Your order (#" . $orderId . ") has been " . $status;
                
                // If order is confirmed, add payment information
                if ($status === 'confirmed') {
                    $notificationMessage .= ". Please proceed with the payment.";
                    $paymentUrl = BASE_URL . "orders/pay.php?order_id=" . $orderId;
                    $notificationMessage .= "\nClick here to pay: " . $paymentUrl;
                }
                
                // Set notification properties
                $this->notification->user_id = $order['user_id'];
                $this->notification->title = $notificationTitle;
                $this->notification->message = $notificationMessage;
                $this->notification->type = "order";
                $this->notification->reference_id = $orderId;
                $this->notification->create();
            }

            return [
                'success' => true,
                'message' => 'Order status updated successfully'
            ];
        }
        return ['success' => false, 'message' => 'Unable to update order status'];
    }

    public function updatePaymentStatus($orderId, $status) {
        if (!in_array($status, ['paid', 'unpaid', 'refunded'])) {
            return ['success' => false, 'message' => 'Invalid payment status'];
        }

        if($this->order->updatePaymentStatus($orderId, $status)) {
            return [
                'success' => true,
                'message' => 'Payment status updated successfully'
            ];
        }
        return ['success' => false, 'message' => 'Unable to update payment status'];
    }

    public function getUserOrders($userId) {
        try {
            $query = "SELECT 
                        o.*,
                        GROUP_CONCAT(CONCAT(oi.quantity, 'x ', mi.name) SEPARATOR ', ') as items
                    FROM orders o
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
                    WHERE o.user_id = :user_id
                    GROUP BY o.order_id
                    ORDER BY o.order_date DESC";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getUserOrders: " . $e->getMessage());
            return false;
        }
    }

    public function getOrderItems($orderId) {
        try {
            $query = "SELECT 
                        oi.*,
                        mi.name,
                        mi.description
                    FROM order_items oi
                    LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
                    WHERE oi.order_id = :order_id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();
            
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getOrderItems: " . $e->getMessage());
            return false;
        }
    }
}
