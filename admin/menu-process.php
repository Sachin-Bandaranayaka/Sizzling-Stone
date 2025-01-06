<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/MenuController.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$menuController = new MenuController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../public/images/menu/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'images/menu/' . $filename;
            }
        }
    }

    // Prepare item data
    $itemData = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'category' => $_POST['category'] ?? '',
        'available' => isset($_POST['available']) ? 1 : 0,
        'image_path' => $image_path
    ];

    // Validate data
    $errors = $menuController->validateItemData($itemData);

    if (empty($errors)) {
        if (isset($_POST['item_id'])) {
            // Update existing item
            $result = $menuController->updateItem($_POST['item_id'], $itemData);
        } else {
            // Create new item
            $result = $menuController->addItem($itemData);
        }

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: ' . BASE_URL . 'admin/menu.php');
            exit();
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

// Redirect back if there was an error
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>
