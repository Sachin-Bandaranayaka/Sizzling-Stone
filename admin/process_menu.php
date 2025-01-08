<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/MenuController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
    header('Location: ' . BASE_URL);
    exit();
}

$menuController = new MenuController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            // Handle file upload
            $imagePath = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../public/images/menu/';
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = $fileName;
                }
            }
            
            $menuData = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'category' => $_POST['category'],
                'image_path' => $imagePath,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0
            ];
            
            $result = $menuController->createItem($menuData);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Menu item created successfully';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        case 'update':
            $itemId = $_POST['item_id'];
            
            // Handle file upload if new image is provided
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../public/images/menu/';
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = $fileName;
                    
                    // Delete old image if it exists
                    $oldItem = $menuController->getItemById($itemId);
                    if ($oldItem && !empty($oldItem['image_path'])) {
                        $oldImagePath = $uploadDir . $oldItem['image_path'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                }
            }
            
            $menuData = [
                'item_id' => $itemId,
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'category' => $_POST['category'],
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0
            ];
            
            if ($imagePath !== null) {
                $menuData['image_path'] = $imagePath;
            }
            
            $result = $menuController->updateItem($menuData);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Menu item updated successfully';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        case 'delete':
            $itemId = $_POST['item_id'];
            
            // Get item details to delete image file
            $item = $menuController->getItemById($itemId);
            if ($item && !empty($item['image_path'])) {
                $imagePath = __DIR__ . '/../public/images/menu/' . $item['image_path'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $result = $menuController->deleteItem($itemId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Menu item deleted successfully';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        default:
            $_SESSION['error_message'] = 'Invalid action';
            break;
    }
}

header('Location: ' . BASE_URL . 'admin/menu.php');
exit();
