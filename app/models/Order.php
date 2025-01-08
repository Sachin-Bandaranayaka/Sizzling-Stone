<?php
class Order {
    private $conn;
    private $table_name = "orders";

    public $order_id;
    public $user_id;
    public $total_amount;
    public $special_instructions;
    public $status;
    public $order_type;
    public $order_date;
    public $payment_status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Insert order
            $query = "INSERT INTO " . $this->table_name . "
                    (user_id, total_amount, special_instructions, status, order_type, payment_status)
                    VALUES
                    (:user_id, :total_amount, :special_instructions, 'pending', :order_type, 'unpaid')";

            $stmt = $this->conn->prepare($query);

            // Bind values
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":special_instructions", $this->special_instructions);
            $stmt->bindParam(":order_type", $this->order_type);

            error_log("Executing order creation with data: " . print_r([
                'user_id' => $this->user_id,
                'total_amount' => $this->total_amount,
                'special_instructions' => $this->special_instructions,
                'order_type' => $this->order_type
            ], true));

            if(!$stmt->execute()) {
                throw new PDOException("Failed to create order");
            }

            $this->order_id = $this->conn->lastInsertId();
            error_log("Order created with ID: " . $this->order_id);

            // Commit transaction
            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function addOrderItems($items) {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            $query = "INSERT INTO order_items (order_id, item_id, quantity, unit_price)
                    VALUES (:order_id, :item_id, :quantity, :unit_price)";

            $stmt = $this->conn->prepare($query);

            error_log("Adding order items for order ID " . $this->order_id . ": " . print_r($items, true));

            foreach ($items as $item) {
                $stmt->bindParam(":order_id", $this->order_id);
                $stmt->bindParam(":item_id", $item['item_id']);
                $stmt->bindParam(":quantity", $item['quantity']);
                $stmt->bindParam(":unit_price", $item['unit_price']);
                
                error_log("Executing order item insert with data: " . print_r([
                    'order_id' => $this->order_id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price']
                ], true));

                if (!$stmt->execute()) {
                    throw new PDOException("Failed to add order item");
                }
            }

            // Commit transaction
            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            error_log("Error adding order items: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function getAll() {
        $query = "SELECT o.*, u.username, u.email, u.phone
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.user_id
                ORDER BY o.order_date DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getting orders: " . $e->getMessage());
            return false;
        }
    }

    public function getById($orderId) {
        $query = "SELECT o.*, u.username, u.email, u.phone
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.user_id
                WHERE o.order_id = :order_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $orderId);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getting order: " . $e->getMessage());
            return false;
        }
    }

    public function getOrderItems($orderId) {
        $query = "SELECT oi.*, mi.name, mi.description
                FROM order_items oi
                LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
                WHERE oi.order_id = :order_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $orderId);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getting order items: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($orderId, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE order_id = :order_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $orderId);
            $stmt->bindParam(":status", $status);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }

    public function getUserOrders($userId) {
        $query = "SELECT o.*, COUNT(oi.order_item_id) as item_count
                FROM " . $this->table_name . " o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.user_id = :user_id
                GROUP BY o.order_id
                ORDER BY o.order_date DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getting user orders: " . $e->getMessage());
            return false;
        }
    }

    public function getPendingOrders() {
        $query = "SELECT o.*, u.username, u.email, u.phone
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.user_id
                WHERE o.status = 'pending'
                ORDER BY o.order_date ASC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getting pending orders: " . $e->getMessage());
            return false;
        }
    }
}
