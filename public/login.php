<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new AuthController();

// If user is already logged in, redirect appropriately
if($auth->isLoggedIn()) {
    if($auth->isAdmin()) {
        header('Location: ' . BASE_URL . 'public/admin/');
    } else {
        header('Location: ' . BASE_URL . 'dashboard.php');
    }
    exit();
}

$error = '';

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $auth->login($username, $password);
    
    if($result['success']) {
        if($auth->isAdmin()) {
            header('Location: ' . BASE_URL . 'public/admin/');
        } else {
            header('Location: ' . BASE_URL . 'dashboard.php');
        }
        exit();
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../app/views/auth/login.php';
?>
