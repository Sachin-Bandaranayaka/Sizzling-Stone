<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Notification.php';

class PaymentController {
    private $db;
    private $notification;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->notification = new Notification($this->db);
    }

    public function initiatePayment($orderId) {
        try {
            // Get order details
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                throw new Exception("Order not found");
            }

            // Create payment record
            $stmt = $this->db->prepare("
                INSERT INTO payments (order_id, amount, payment_status)
                VALUES (?, ?, 'pending')
            ");
            $stmt->execute([$orderId, $order['total_amount']]);
            $paymentId = $this->db->lastInsertId();

            // Update order payment status
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET payment_status = 'pending'
                WHERE order_id = ?
            ");
            $stmt->execute([$orderId]);

            return [
                'payment_id' => $paymentId,
                'amount' => $order['total_amount'],
                'order_id' => $orderId
            ];
        } catch (Exception $e) {
            throw new Exception("Failed to initiate payment: " . $e->getMessage());
        }
    }

    public function processPayment($paymentId, $paymentData) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Get payment details
            $stmt = $this->db->prepare("
                SELECT p.*, o.user_id, o.order_id 
                FROM payments p 
                JOIN orders o ON p.order_id = o.order_id 
                WHERE p.payment_id = ?
            ");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new Exception("Payment not found");
            }

            // Simulate payment processing
            $success = $this->processDummyPayment($paymentData);

            if ($success) {
                // Update payment status
                $stmt = $this->db->prepare("
                    UPDATE payments 
                    SET payment_status = 'completed',
                        transaction_id = ?,
                        payment_method = ?
                    WHERE payment_id = ?
                ");
                $stmt->execute([
                    'TXN_' . time(),
                    $paymentData['payment_method'],
                    $paymentId
                ]);

                // Update order payment status
                $stmt = $this->db->prepare("
                    UPDATE orders 
                    SET payment_status = 'paid'
                    WHERE order_id = ?
                ");
                $stmt->execute([$payment['order_id']]);

                // Send notification
                $this->notification->user_id = $payment['user_id'];
                $this->notification->title = 'Payment Successful';
                $this->notification->message = 'Your payment for order #' . $payment['order_id'] . ' has been processed successfully.';
                $this->notification->type = 'order';
                $this->notification->reference_id = $payment['order_id'];
                $this->notification->create();

                $this->db->commit();
                return true;
            } else {
                throw new Exception("Payment processing failed");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            
            // Update payment status to failed
            $stmt = $this->db->prepare("
                UPDATE payments 
                SET payment_status = 'failed'
                WHERE payment_id = ?
            ");
            $stmt->execute([$paymentId]);

            // Update order payment status
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET payment_status = 'failed'
                WHERE order_id = ?
            ");
            $stmt->execute([$payment['order_id']]);

            throw new Exception("Payment failed: " . $e->getMessage());
        }
    }

    private function processDummyPayment($paymentData) {
        // Simulate payment processing
        sleep(2); // Simulate processing time
        
        // Simulate success rate (90% success)
        return rand(1, 100) <= 90;
    }
}
