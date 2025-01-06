<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new AuthController();
$auth->logout();

// Redirect to home page
header('Location: ' . BASE_URL);
exit();
