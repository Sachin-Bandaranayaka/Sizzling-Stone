<?php
class MenuItem {
    private $conn;
    private $table_name = "menu_items";

    public $item_id;
    public $name;
    public $description;
    public $price;
    public $category;
    public $available;
    public $image_path;
    public $is_featured;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (name, description, price, category, available, image_path, is_featured)
                VALUES
                (:name, :description, :price, :category, :available, :image_path, :is_featured)";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->image_path = htmlspecialchars(strip_tags($this->image_path));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":available", $this->available);
        $stmt->bindParam(":image_path", $this->image_path);
        $stmt->bindParam(":is_featured", $this->is_featured);

        if($stmt->execute()) {
            $this->item_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    name = :name,
                    description = :description,
                    price = :price,
                    category = :category,
                    available = :available,
                    image_path = :image_path,
                    is_featured = :is_featured
                WHERE
                    item_id = :item_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->image_path = htmlspecialchars(strip_tags($this->image_path));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":available", $this->available);
        $stmt->bindParam(":image_path", $this->image_path);
        $stmt->bindParam(":is_featured", $this->is_featured);
        $stmt->bindParam(":item_id", $this->item_id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE item_id = :item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_id", $this->item_id);
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY category, name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE item_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCategory($category) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category = :category ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category", $category);
        $stmt->execute();
        return $stmt;
    }

    public function getAllCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table_name . " ORDER BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function search($query) {
        $searchTerm = "%{$query}%";
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE name LIKE :query
                OR description LIKE :query
                OR category LIKE :query
                ORDER BY category, name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":query", $searchTerm);
        $stmt->execute();
        return $stmt;
    }

    public function getFeaturedItems() {
        // First try to get items marked as featured
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE is_featured = 1 AND available = 1
                ORDER BY category, name
                LIMIT 6";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        // If no featured items found, get random available items
        if ($stmt->rowCount() == 0) {
            $query = "SELECT * FROM " . $this->table_name . "
                    WHERE available = 1
                    ORDER BY RAND()
                    LIMIT 6";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        }
        
        return $stmt;
    }

    public function getPopularItems($limit = 5) {
        $query = "SELECT m.*, COUNT(oi.item_id) as order_count
                FROM " . $this->table_name . " m
                LEFT JOIN order_items oi ON m.item_id = oi.item_id
                WHERE m.available = 1
                GROUP BY m.item_id
                ORDER BY order_count DESC
                LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
