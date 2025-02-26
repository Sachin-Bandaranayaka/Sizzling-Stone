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
            error_log("Starting order creation with data: " . print_r([
                'user_id' => $this->user_id,
                'total_amount' => $this->total_amount,
                'special_instructions' => $this->special_instructions,
                'order_type' => $this->order_type,
                'items' => $this->items
            ], true));

            // Start transaction
            $this->conn->beginTransaction();

            // Insert order
            $query = "INSERT INTO " . $this->table_name . "
                    (user_id, total_amount, special_instructions, status, order_type, payment_status)
                    VALUES
                    (:user_id, :total_amount, :special_instructions, 'pending', :order_type, 'unpaid')";

            error_log("Executing order query: " . $query);
            $stmt = $this->conn->prepare($query);

            // Bind values
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":special_instructions", $this->special_instructions);
            $stmt->bindParam(":order_type", $this->order_type);

            error_log("Bound parameters: " . print_r([
                'user_id' => $this->user_id,
                'total_amount' => $this->total_amount,
                'special_instructions' => $this->special_instructions,
                'order_type' => $this->order_type
            ], true));

            if(!$stmt->execute()) {
                error_log("Failed to execute order insert: " . print_r($stmt->errorInfo(), true));
                throw new PDOException("Failed to create order");
            }

            $this->order_id = $this->conn->lastInsertId();
            error_log("Created order with ID: " . $this->order_id);
            
            // Add order items within the same transaction
            $items_query = "INSERT INTO order_items 
                          (order_id, item_id, quantity, unit_price)
                          VALUES 
                          (:order_id, :item_id, :quantity, :unit_price)";
            
            error_log("Preparing order items query: " . $items_query);
            $items_stmt = $this->conn->prepare($items_query);
            
            foreach ($this->items as $item) {
                error_log("Processing item: " . print_r($item, true));
                
                $items_stmt->bindParam(':order_id', $this->order_id);
                $items_stmt->bindParam(':item_id', $item['item_id']);
                $items_stmt->bindParam(':quantity', $item['quantity']);
                $items_stmt->bindParam(':unit_price', $item['unit_price']);
                
                if (!$items_stmt->execute()) {
                    error_log("Failed to execute item insert: " . print_r($items_stmt->errorInfo(), true));
                    throw new PDOException("Failed to add order item");
                }
            }

            error_log("Successfully added all items to order");

            // Commit transaction
            $this->conn->commit();
            error_log("Transaction committed successfully");
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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

    public function getTotalOrders() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getTotalRevenue() {
        $query = "SELECT SUM(total_amount) as total FROM " . $this->table_name . " WHERE status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function getTodaysOrders() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE DATE(order_date) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getTodaysRevenue() {
        $query = "SELECT SUM(total_amount) as total FROM " . $this->table_name . " 
                 WHERE DATE(order_date) = CURDATE() AND status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function getRecentOrders($limit = 5) {
        $query = "SELECT o.*, u.username 
                FROM " . $this->table_name . " o 
                JOIN users u ON o.user_id = u.user_id 
                ORDER BY o.order_date DESC 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
