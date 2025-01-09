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
    public $items; // Add this property to store order items

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

            if(!$stmt->execute()) {
                throw new PDOException("Failed to create order");
            }

            $this->order_id = $this->conn->lastInsertId();
            
            // Add order items within the same transaction
            $items_query = "INSERT INTO order_items 
                          (order_id, item_id, quantity, unit_price)
                          VALUES 
                          (:order_id, :item_id, :quantity, :unit_price)";
            
            $items_stmt = $this->conn->prepare($items_query);
            
            foreach ($this->items as $item) {
                $items_stmt->bindParam(':order_id', $this->order_id);
                $items_stmt->bindParam(':item_id', $item['item_id']);
                $items_stmt->bindParam(':quantity', $item['quantity']);
                $items_stmt->bindParam(':unit_price', $item['unit_price']);
                
                if (!$items_stmt->execute()) {
                    throw new PDOException("Failed to add order item");
                }
            }

            // Commit transaction
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            throw $e; // Re-throw to be caught by the controller
        }
    }

    public function getAll() {
        $query = "SELECT 
                    o.*,
                    u.username as customer_name,
                    GROUP_CONCAT(CONCAT(oi.quantity, 'x ', mi.name) SEPARATOR ', ') as items,
                    o.total_amount as total
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
                GROUP BY o.order_id
                ORDER BY o.order_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT 
                    o.*,
                    u.username as customer_name,
                    GROUP_CONCAT(CONCAT(oi.quantity, 'x ', mi.name) SEPARATOR ', ') as items,
                    o.total_amount as total
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
                WHERE o.order_id = :id
                GROUP BY o.order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUserId($userId) {
        $query = "SELECT 
                    o.*,
                    GROUP_CONCAT(CONCAT(oi.quantity, 'x ', mi.name) SEPARATOR ', ') as items,
                    o.total_amount as total
                FROM " . $this->table_name . " o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
                WHERE o.user_id = :user_id
                GROUP BY o.order_id
                ORDER BY o.order_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE order_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    public function updatePaymentStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET payment_status = :status
                WHERE order_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    public function addOrderItems($items) {
        try {
            foreach ($items as $item) {
                $query = "INSERT INTO order_items 
                        (order_id, item_id, quantity, unit_price)
                        VALUES 
                        (:order_id, :item_id, :quantity, :unit_price)";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':order_id', $this->order_id);
                $stmt->bindParam(':item_id', $item['item_id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':unit_price', $item['unit_price']);

                if (!$stmt->execute()) {
                    throw new PDOException("Failed to add order item");
                }
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error adding order items: " . $e->getMessage());
            return false;
        }
    }

    public function getOrderItems($orderId) {
        $query = "SELECT 
                    oi.*,
                    mi.name as item_name,
                    mi.description as item_description
                FROM order_items oi
                LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
                WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
