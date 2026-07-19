<?php
// Database Configuration
// Local (XAMPP) — comment out when deploying:
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'online_bookstore');

// Production (InfinityFree):
define('DB_HOST', 'sql211.infinityfree.com');
define('DB_USER', 'if0_42444207');
define('DB_PASS', 'bJZ2ZndL5Gsc');
define('DB_NAME', 'if0_42444207_onlinebookstore');

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

// Helper functions (must be before auth_check since auth_check uses them)
require_once __DIR__ . '/functions.php';

// Auth functions (uses functions from functions.php)
require_once __DIR__ . '/auth_check.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
