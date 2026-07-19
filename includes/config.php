<?php
// Database Configuration
// Local (XAMPP):
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_bookstore');

// Production (InfinityFree) — uncomment and fill after creating DB:
// define('DB_HOST', 'sqlXXX.infinityfree.com');
// define('DB_USER', 'if0_XXXXXX');
// define('DB_PASS', 'your_db_password');
// define('DB_NAME', 'if0_XXXXXX_online_bookstore');

// Base URL
$base = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
define('BASE_URL', $base);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create mysqli connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Mail configuration
require_once __DIR__ . '/mail_config.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
