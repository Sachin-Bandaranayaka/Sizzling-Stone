<?php
require_once __DIR__ . '/../../config/database.php';
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
        return $this->menuItem->getFeaturedItems();
    }

    public function getItemById($id) {
        return $this->menuItem->getById($id);
    }

    public function createItem($data) {
        try {
            // Validate required fields
            if (empty($data['name']) || empty($data['price']) || empty($data['category'])) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            // Set menu item properties
            $this->menuItem->name = $data['name'];
            $this->menuItem->description = $data['description'] ?? '';
            $this->menuItem->price = $data['price'];
            $this->menuItem->category = $data['category'];
            $this->menuItem->image = $data['image'] ?? '';
            $this->menuItem->is_featured = $data['is_featured'] ?? 0;

            // Create the menu item
            if ($this->menuItem->create()) {
                return [
                    'success' => true,
                    'message' => 'Menu item created successfully',
                    'id' => $this->menuItem->id
                ];
            }
            return ['success' => false, 'message' => 'Unable to create menu item'];
        } catch (Exception $e) {
            error_log('Error creating menu item: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating menu item'];
        }
    }

    public function updateItem($id, $data) {
        try {
            // Get existing item
            $item = $this->getItemById($id);
            if (!$item) {
                return ['success' => false, 'message' => 'Menu item not found'];
            }

            // Update menu item properties
            $this->menuItem->id = $id;
            $this->menuItem->name = $data['name'] ?? $item['name'];
            $this->menuItem->description = $data['description'] ?? $item['description'];
            $this->menuItem->price = $data['price'] ?? $item['price'];
            $this->menuItem->category = $data['category'] ?? $item['category'];
            $this->menuItem->image = $data['image'] ?? $item['image'];
            $this->menuItem->is_featured = $data['is_featured'] ?? $item['is_featured'];

            // Update the menu item
            if ($this->menuItem->update()) {
                return [
                    'success' => true,
                    'message' => 'Menu item updated successfully'
                ];
            }
            return ['success' => false, 'message' => 'Unable to update menu item'];
        } catch (Exception $e) {
            error_log('Error updating menu item: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating menu item'];
        }
    }

    public function deleteItem($id) {
        try {
            $this->menuItem->id = $id;
            if ($this->menuItem->delete()) {
                return [
                    'success' => true,
                    'message' => 'Menu item deleted successfully'
                ];
            }
            return ['success' => false, 'message' => 'Unable to delete menu item'];
        } catch (Exception $e) {
            error_log('Error deleting menu item: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting menu item'];
        }
    }

    public function searchItems($query) {
        return $this->menuItem->search($query);
    }

    public function getPopularItems($limit = 5) {
        return $this->menuItem->getPopularItems($limit);
    }
}
