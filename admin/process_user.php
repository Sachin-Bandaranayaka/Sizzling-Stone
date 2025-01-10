<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';

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

$userController = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update':
            if (!isset($_POST['user_id'])) {
                $_SESSION['error_message'] = 'User ID is required.';
                header('Location: users.php');
                exit();
            }

            $data = [
                'user_id' => $_POST['user_id'],
                'name' => $_POST['name'],
                'phone' => $_POST['phone'],
                'role' => $_POST['role'],
                'is_active' => $_POST['is_active']
            ];

            $result = $userController->updateUser($data);
            
            if ($result['success']) {
                $_SESSION['success_message'] = 'User updated successfully.';
                header('Location: edit_user.php?id=' . $_POST['user_id']);
            } else {
                $_SESSION['error_message'] = $result['message'];
                header('Location: edit_user.php?id=' . $_POST['user_id']);
            }
            break;

        case 'delete':
            if (!isset($_POST['user_id'])) {
                $_SESSION['error_message'] = 'User ID is required.';
                header('Location: users.php');
                exit();
            }

            // Prevent self-deletion
            if ($_POST['user_id'] == $_SESSION['user_id']) {
                $_SESSION['error_message'] = 'You cannot delete your own account.';
                header('Location: users.php');
                exit();
            }

            $result = $userController->deleteUser($_POST['user_id']);
            
            if ($result['success']) {
                $_SESSION['success_message'] = 'User deleted successfully.';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            header('Location: users.php');
            break;

        default:
            $_SESSION['error_message'] = 'Invalid action.';
            header('Location: users.php');
            break;
    }
    exit();
} else {
    // If not POST request, redirect to users page
    header('Location: users.php');
    exit();
}
