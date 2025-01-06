<?php
require_once __DIR__ . '/../models/MenuItem.php';

class MenuController {
    private $menuItem;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->menuItem = new MenuItem($db);
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
        // Get a selection of items from different categories
        $items = [];
        $categories = $this->getAllCategories();
        
        // Get one item from each category, up to 6 items
        foreach ($categories as $category) {
            $categoryItems = $this->getItemsByCategory($category);
            if ($categoryItems && $row = $categoryItems->fetch(PDO::FETCH_ASSOC)) {
                $items[] = $row;
            }
            if (count($items) >= 6) {
                break;
            }
        }
        
        return $items;
    }

    public function getItemById($id) {
        return $this->menuItem->getById($id);
    }

    public function createItem($data) {
        try {
            // Validate required fields
            if (empty($data['name']) || empty($data['description']) || !isset($data['price'])) {
                return ['success' => false, 'message' => 'Required fields are missing'];
            }

            // Handle new category
            if (isset($_POST['new_category']) && !empty($_POST['new_category'])) {
                $data['category'] = $_POST['new_category'];
            }

            // Set the data
            $this->menuItem->name = $data['name'];
            $this->menuItem->description = $data['description'];
            $this->menuItem->price = $data['price'];
            $this->menuItem->category = $data['category'];
            $this->menuItem->available = $data['available'];
            $this->menuItem->image_path = $data['image_path'];

            if ($this->menuItem->create()) {
                return ['success' => true, 'message' => 'Menu item created successfully'];
            }
            return ['success' => false, 'message' => 'Failed to create menu item'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }

    public function updateItem($id, $data) {
        try {
            $this->menuItem->item_id = $id;
            
            // Get existing item
            $existingItem = $this->menuItem->getById($id);
            if (!$existingItem) {
                return ['success' => false, 'message' => 'Menu item not found'];
            }

            // Update fields
            $this->menuItem->name = $data['name'] ?? $existingItem['name'];
            $this->menuItem->description = $data['description'] ?? $existingItem['description'];
            $this->menuItem->price = $data['price'] ?? $existingItem['price'];
            $this->menuItem->category = $data['category'] ?? $existingItem['category'];
            $this->menuItem->available = isset($data['available']) ? $data['available'] : $existingItem['available'];
            $this->menuItem->image_path = !empty($data['image_path']) ? $data['image_path'] : $existingItem['image_path'];

            if ($this->menuItem->update()) {
                return ['success' => true, 'message' => 'Menu item updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update menu item'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }

    public function deleteItem($id) {
        try {
            $this->menuItem->item_id = $id;
            
            // Check if item exists
            if (!$this->menuItem->getById($id)) {
                return ['success' => false, 'message' => 'Menu item not found'];
            }

            if ($this->menuItem->delete()) {
                return ['success' => true, 'message' => 'Menu item deleted successfully'];
            }
            return ['success' => false, 'message' => 'Failed to delete menu item'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
}
