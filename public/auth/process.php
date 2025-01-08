<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize response array
$response = ['success' => false, 'message' => 'Invalid request'];

$authController = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            if (!isset($_POST['username']) || !isset($_POST['password'])) {
                $_SESSION['error_message'] = 'Username/Email and password are required';
                header('Location: ' . BASE_URL . 'public/auth/login.php');
                exit();
            }

            $username = trim($_POST['username']);
            $password = $_POST['password'];

            try {
                $result = $authController->login($username, $password);
                if ($result['success']) {
                    // Set all session variables
                    $_SESSION['user_id'] = $result['user']['user_id'];
                    $_SESSION['username'] = $result['user']['username'];
                    $_SESSION['role'] = $result['user']['role'];
                    
                    // Debug information
                    error_log("Login successful. User ID: " . $_SESSION['user_id'] . ", Role: " . $_SESSION['role']);
                    
                    $_SESSION['success_message'] = 'Welcome back!';
                    header('Location: ' . BASE_URL);
                } else {
                    $_SESSION['error_message'] = $result['message'];
                    header('Location: ' . BASE_URL . 'public/auth/login.php');
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $_SESSION['error_message'] = 'An error occurred during login';
                header('Location: ' . BASE_URL . 'public/auth/login.php');
            }
            break;

        case 'register':
            // Validate required fields
            $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'password', 'confirm_password'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    $_SESSION['error_message'] = 'All fields are required';
                    header('Location: ' . BASE_URL . 'public/auth/register.php');
                    exit();
                }
            }

            // Validate password match
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $_SESSION['error_message'] = 'Passwords do not match';
                header('Location: ' . BASE_URL . 'public/auth/register.php');
                exit();
            }

            $userData = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'password' => $_POST['password']
            ];

            try {
                $result = $authController->register($userData);
                if ($result['success']) {
                    $_SESSION['success_message'] = 'Registration successful! Please login.';
                    header('Location: ' . BASE_URL . 'public/auth/login.php');
                } else {
                    $_SESSION['error_message'] = $result['message'];
                    header('Location: ' . BASE_URL . 'public/auth/register.php');
                }
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $_SESSION['error_message'] = 'An error occurred during registration';
                header('Location: ' . BASE_URL . 'public/auth/register.php');
            }
            break;

        default:
            header('Location: ' . BASE_URL . 'public/auth/login.php');
            break;
    }
    exit();
}

// If someone tries to access this file directly
header('Location: ' . BASE_URL);
exit();
