<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_bookstore');

// Base URL (update if needed)
define('BASE_URL', 'http://localhost/online_bookstore/');

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
