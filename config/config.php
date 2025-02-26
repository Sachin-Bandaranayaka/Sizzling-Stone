<?php
// Application configuration
define('BASE_URL', 'http://localhost/sizzling-stone/');
define('PUBLIC_URL', BASE_URL . 'public/');

// Only define SITE_NAME if not already defined
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'The Sizzling Stone');
}

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_error.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Time zone
date_default_timezone_set('Asia/Kolkata');

// Security
define('HASH_COST', 10); // For password hashing

// Site Configuration
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Sizzling Stone');
}

// Database Configuration
define('DB_HOST', 'localhost');  // Using default port
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sizzling_stone');

// Session Configuration
define('SESSION_NAME', 'sizzling_stone_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// Image Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 5000000); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Email Configuration (if needed)
define('SMTP_HOST', '');
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_PORT', 587);

// Pagination Configuration
define('ITEMS_PER_PAGE', 10);

// Other Constants
define('SALT', 'your-secret-salt-string');
define('TOKEN_EXPIRY', 3600); // 1 hour

// Base URL - Adjust this according to your setup
// Removed the if condition as BASE_URL is already defined
?>
