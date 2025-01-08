<?php
require_once __DIR__ . '/../../config/config.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Set success message in a temporary cookie
setcookie('logout_message', 'You have been successfully logged out', time() + 5, '/');

// Redirect to home page
header('Location: ' . BASE_URL);
exit();
