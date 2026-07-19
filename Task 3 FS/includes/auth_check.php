<?php
session_start();

$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$baseDir = str_replace('\\', '/', dirname(__DIR__));
$loginUrl = str_replace($docRoot, '', $baseDir) . '/login.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $loginUrl);
    exit;
}

function requireRole($allowedRoles) {
    global $loginUrl;
    if (!isset($_SESSION['role_name'])) {
        header('Location: ' . $loginUrl);
        exit;
    }
    if (!in_array($_SESSION['role_name'], (array)$allowedRoles)) {
        http_response_code(403);
        die('<div class="container mt-5"><div class="alert alert-danger">Access denied: insufficient permissions.</div></div>');
    }
}
