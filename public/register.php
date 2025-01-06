<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$auth = new AuthController();

// If user is already logged in, redirect to dashboard
if($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit();
}

// Handle registration form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate password match
    if($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: ' . BASE_URL . 'register.php');
        exit();
    }
    
    // Prepare user data
    $userData = [
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $password,
        'phone' => $_POST['phone'] ?? ''
    ];
    
    $result = $auth->register($userData);
    
    if($result['success']) {
        $_SESSION['success'] = 'Registration successful! Please login.';
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    } else {
        $_SESSION['error'] = $result['message'];
        header('Location: ' . BASE_URL . 'register.php');
        exit();
    }
}

// Display registration form
require_once __DIR__ . '/../app/views/auth/register.php';
?>
