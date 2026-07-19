<?php
/**
 * Helper functions for the Online Bookstore
 */

// Get all categories
function getCategories($conn) {
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name ASC");
    $stmt->execute();
    return $stmt->get_result();
}

// Get category by ID
function getCategoryById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Get all books with optional filters
function getBooks($conn, $search = '', $category_id = '', $min_price = '', $max_price = '', $sort = 'newest', $page = 1, $limit = 8) {
    $where = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $where[] = "(b.title LIKE ? OR b.author LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ss';
    }

    if (!empty($category_id)) {
        $where[] = "b.category_id = ?";
        $params[] = (int)$category_id;
        $types .= 'i';
    }

    if (!empty($min_price)) {
        $where[] = "b.price >= ?";
        $params[] = (float)$min_price;
        $types .= 'd';
    }

    if (!empty($max_price)) {
        $where[] = "b.price <= ?";
        $params[] = (float)$max_price;
        $types .= 'd';
    }

    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    switch ($sort) {
        case 'price_asc': $orderBy = "b.price ASC"; break;
        case 'price_desc': $orderBy = "b.price DESC"; break;
        case 'title': $orderBy = "b.title ASC"; break;
        default: $orderBy = "b.created_at DESC"; break;
    }

    // Count total
    $countSql = "SELECT COUNT(*) as total FROM books b $whereClause";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    $offset = ($page - 1) * $limit;
    $sql = "SELECT b.*, c.name as category_name 
            FROM books b 
            LEFT JOIN categories c ON b.category_id = c.id 
            $whereClause 
            ORDER BY $orderBy 
            LIMIT ? OFFSET ?";
    
    $params[] = (int)$limit;
    $params[] = (int)$offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return [
        'books' => $result,
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page
    ];
}

// Get book by ID
function getBookById($conn, $id) {
    $stmt = $conn->prepare("SELECT b.*, c.name as category_name 
                           FROM books b 
                           LEFT JOIN categories c ON b.category_id = c.id 
                           WHERE b.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Get cart items for a user
function getCartItems($conn, $user_id) {
    $stmt = $conn->prepare("SELECT c.*, b.title, b.price, b.discount, b.image, b.stock 
                           FROM cart c 
                           JOIN books b ON c.book_id = b.id 
                           WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Get cart total
function getCartTotal($conn, $user_id) {
    $stmt = $conn->prepare("SELECT c.quantity, b.price, b.discount 
                           FROM cart c 
                           JOIN books b ON c.book_id = b.id 
                           WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $unit_price = getDiscountedPrice($row['price'], $row['discount']);
        $total += $unit_price * $row['quantity'];
    }
    return $total;
}

// Get cart count
function getCartCount($conn, $user_id) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] ?? 0;
}

// Get orders for a user
function getUserOrders($conn, $user_id, $limit = 10) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Get order details
function getOrderDetails($conn, $order_id) {
    $stmt = $conn->prepare("SELECT oi.*, b.title, b.image 
                           FROM order_items oi 
                           JOIN books b ON oi.book_id = b.id 
                           WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect with message
function redirect($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }
    header("Location: $url");
    exit();
}

// Display flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return "<div class='alert alert-{$msg['type']} alert-dismissible fade show'>
                    {$msg['message']}
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    return '';
}

// Format price
function formatPrice($price) {
    return '₹' . number_format($price, 0, '.', ',');
}

// Calculate discounted price
function getDiscountedPrice($price, $discount) {
    if ($discount > 0) {
        return $price - ($price * $discount / 100);
    }
    return $price;
}

// Check if book is in wishlist
function isInWishlist($conn, $user_id, $book_id) {
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Get wishlist count
function getWishlistCount($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'] ?? 0;
}

// Get wishlist items
function getWishlistItems($conn, $user_id) {
    $stmt = $conn->prepare("SELECT w.*, b.title, b.author, b.price, b.discount, b.rating, b.image, b.stock, b.id as book_id
                           FROM wishlist w 
                           JOIN books b ON w.book_id = b.id 
                           WHERE w.user_id = ? 
                           ORDER BY w.added_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Render star rating HTML
function renderStars($rating) {
    $html = '<div class="book-rating">';
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="bi bi-star-fill text-warning"></i>';
        } elseif ($i == $full + 1 && $half) {
            $html .= '<i class="bi bi-star-half text-warning"></i>';
        } else {
            $html .= '<i class="bi bi-star text-warning"></i>';
        }
    }
    $html .= ' <small class="text-muted">(' . number_format($rating, 1) . ')</small>';
    $html .= '</div>';
    return $html;
}

// Render discount badge
function renderDiscountBadge($discount) {
    if ($discount > 0) {
        return '<span class="discount-badge">-' . number_format($discount, 0) . '%</span>';
    }
    return '';
}

// Render availability badge
function renderAvailabilityBadge($stock) {
    if ($stock > 10) {
        return '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>In Stock</span>';
    } elseif ($stock > 0) {
        return '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle-fill me-1"></i>Only ' . $stock . ' left</span>';
    } else {
        return '<span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i>Out of Stock</span>';
    }
}

// Sanitize output
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Upload image
function uploadImage($file, $target_dir, $default = 'default.png') {
    if ($file['error'] !== UPLOAD_ERR_OK) return $default;
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) return $default;
    
    $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target = $target_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $filename;
    }
    return $default;
}

// Generate pagination links
function paginationLinks($current_page, $total_pages, $url_pattern) {
    if ($total_pages <= 1) return '';
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous
    $prev_disabled = $current_page <= 1 ? 'disabled' : '';
    $prev_url = $current_page > 1 ? str_replace('{page}', $current_page - 1, $url_pattern) : '#';
    $html .= "<li class='page-item $prev_disabled'><a class='page-link' href='$prev_url'>Previous</a></li>";
    
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $url = str_replace('{page}', $i, $url_pattern);
        $html .= "<li class='page-item $active'><a class='page-link' href='$url'>$i</a></li>";
    }
    
    // Next
    $next_disabled = $current_page >= $total_pages ? 'disabled' : '';
    $next_url = $current_page < $total_pages ? str_replace('{page}', $current_page + 1, $url_pattern) : '#';
    $html .= "<li class='page-item $next_disabled'><a class='page-link' href='$next_url'>Next</a></li>";
    
    $html .= '</ul></nav>';
    return $html;
}
