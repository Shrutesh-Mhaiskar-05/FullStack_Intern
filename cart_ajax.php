<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'login_required' => true, 'message' => 'Please login to add items to cart.']);
    exit;
}

$book_id = (int)($_GET['id'] ?? 0);
$book = getBookById($conn, $book_id);

if (!$book) {
    echo json_encode(['success' => false, 'message' => 'Book not found.']);
    exit;
}

if ($book['stock'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'This book is out of stock.']);
    exit;
}

$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND book_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $new_qty = $existing['quantity'] + 1;
    if ($new_qty > $book['stock']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock.']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_qty, $existing['id']);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, 1)");
    $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
    $stmt->execute();
}

$cart_count = getCartCount($conn, $_SESSION['user_id']);
echo json_encode(['success' => true, 'message' => 'Book added to cart!', 'cart_count' => $cart_count]);
