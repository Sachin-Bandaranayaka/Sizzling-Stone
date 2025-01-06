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

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (name, description, price, category, available, image_path)
                VALUES
                (:name, :description, :price, :category, :available, :image_path)";

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

        if($stmt->execute()) {
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
                    image_path = :image_path
                WHERE
                    item_id = :item_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->image_path = htmlspecialchars(strip_tags($this->image_path));

        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":available", $this->available);
        $stmt->bindParam(":image_path", $this->image_path);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE item_id = :item_id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":item_id", $this->item_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY category, name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    public function getAllCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table_name . " ORDER BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    }

    public function getByCategory($category) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category = :category ORDER BY name";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":category", $category);
        $stmt->execute();
        
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE item_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
