<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'require_login' => true]);
    exit;
}

$book_id = (int)($_GET['book_id'] ?? 0);
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

if ($book_id <= 0 || !in_array($action, ['add', 'remove'])) {
    echo json_encode(['success' => false]);
    exit;
}

if ($action === 'add') {
    $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, book_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
}

$count = getWishlistCount($conn, $user_id);

echo json_encode(['success' => true, 'count' => $count]);
