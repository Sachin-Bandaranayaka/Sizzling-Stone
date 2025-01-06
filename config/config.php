<?php
// Application configuration
define('BASE_URL', 'http://localhost/uniProject/sizzling-stone/');
define('SITE_NAME', 'The Sizzling Stone');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('Asia/Kolkata');

// Security
define('HASH_COST', 10); // For password hashing
?>
