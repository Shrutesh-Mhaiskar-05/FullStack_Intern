<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$book_id = (int)($_GET['id'] ?? 0);
$book = getBookById($conn, $book_id);

if (!$book) {
    redirect('shop.php', 'Book not found.', 'danger');
}

if ($book['stock'] <= 0) {
    redirect('shop.php', 'This book is out of stock.', 'warning');
}

// Check if already in cart
$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND book_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $new_qty = $existing['quantity'] + 1;
    if ($new_qty > $book['stock']) {
        redirect('cart.php', 'Not enough stock.', 'warning');
    }
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_qty, $existing['id']);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, 1)");
    $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
    $stmt->execute();
}

$referrer = $_SERVER['HTTP_REFERER'] ?? 'shop.php';
redirect($referrer, 'Book added to cart!', 'success');
