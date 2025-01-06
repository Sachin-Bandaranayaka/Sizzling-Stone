<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/MenuItem.php';

class MenuController {
    private $db;
    private $menuItem;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->menuItem = new MenuItem($this->db);
    }

    public function getAllItems() {
        return $this->menuItem->getAll();
    }

    public function getAllCategories() {
        return $this->menuItem->getAllCategories();
    }

    public function getItemsByCategory($category) {
        return $this->menuItem->getByCategory($category);
    }

    public function getFeaturedItems() {
        // Temporary method until we have the featured items set up
        return false;
    }

    public function addItem($data) {
        if(empty($data['name']) || empty($data['price']) || empty($data['category'])) {
            return ['success' => false, 'message' => 'Required fields are missing'];
        }

        $this->menuItem->name = $data['name'];
        $this->menuItem->description = $data['description'] ?? '';
        $this->menuItem->price = $data['price'];
        $this->menuItem->category = $data['category'];
        $this->menuItem->available = $data['available'] ?? true;
        $this->menuItem->image_path = $data['image_path'] ?? '';

        if($this->menuItem->create()) {
            return ['success' => true, 'message' => 'Item added successfully'];
        }
        return ['success' => false, 'message' => 'Failed to add item'];
    }

    public function updateItem($id, $data) {
        $item = $this->menuItem->getById($id);
        if(!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }

        $this->menuItem->item_id = $id;
        $this->menuItem->name = $data['name'] ?? $item['name'];
        $this->menuItem->description = $data['description'] ?? $item['description'];
        $this->menuItem->price = $data['price'] ?? $item['price'];
        $this->menuItem->category = $data['category'] ?? $item['category'];
        $this->menuItem->available = $data['available'] ?? $item['available'];
        $this->menuItem->image_path = $data['image_path'] ?? $item['image_path'];

        if($this->menuItem->update()) {
            return ['success' => true, 'message' => 'Item updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update item'];
    }

    public function deleteItem($id) {
        $this->menuItem->item_id = $id;
        if($this->menuItem->delete()) {
            return ['success' => true, 'message' => 'Item deleted successfully'];
        }
        return ['success' => false, 'message' => 'Failed to delete item'];
    }

    public function validateItemData($data) {
        $errors = [];
        
        if(empty($data['name'])) {
            $errors[] = 'Name is required';
        }
        
        if(empty($data['price'])) {
            $errors[] = 'Price is required';
        } elseif(!is_numeric($data['price']) || $data['price'] <= 0) {
            $errors[] = 'Price must be a positive number';
        }
        
        if(empty($data['category'])) {
            $errors[] = 'Category is required';
        }
        
        return $errors;
    }
}
?>
