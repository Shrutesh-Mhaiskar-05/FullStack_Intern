<?php
$page_title = 'Book Details';
require_once 'includes/config.php';
require_once 'includes/functions.php';

$book_id = (int)($_GET['id'] ?? 0);
$book = getBookById($conn, $book_id);

if (!$book) {
    redirect('shop.php', 'Book not found.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    requireLogin();
    
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    
    // Check stock
    if ($quantity > $book['stock']) {
        redirect("book_details.php?id=$book_id", 'Not enough stock available.', 'warning');
    }
    
    // Check if already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        $new_qty = $existing['quantity'] + $quantity;
        if ($new_qty > $book['stock']) {
            redirect("book_details.php?id=$book_id", 'Not enough stock available.', 'warning');
        }
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_qty, $existing['id']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $_SESSION['user_id'], $book_id, $quantity);
        $stmt->execute();
    }
    
    redirect("book_details.php?id=$book_id", 'Book added to cart!', 'success');
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card border-0 shadow-sm">
            <img src="assets/images/<?php echo h($book['image']); ?>" 
                 class="card-img-top p-4" 
                 alt="<?php echo h($book['title']); ?>"
                 onerror="this.src='assets/images/default-book.png'">
        </div>
    </div>
    <div class="col-md-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                <li class="breadcrumb-item active"><?php echo h($book['title']); ?></li>
            </ol>
        </nav>
        
        <h3 class="fw-bold"><?php echo h($book['title']); ?></h3>
        <p class="text-muted">by <?php echo h($book['author']); ?></p>
        
        <?php if ($book['rating']): ?>
            <?php echo renderStars($book['rating']); ?>
        <?php endif; ?>
        
        <?php if ($book['isbn']): ?>
        <p class="small text-muted">ISBN: <?php echo h($book['isbn']); ?></p>
        <?php endif; ?>
        
        <div class="mb-3">
            <span class="badge bg-info"><?php echo h($book['category_name'] ?? 'Uncategorized'); ?></span>
            <?php echo renderAvailabilityBadge($book['stock']); ?>
        </div>
        
        <div class="mb-4">
            <?php $discounted_price = getDiscountedPrice($book['price'], $book['discount']); ?>
            <?php if ($book['discount'] > 0): ?>
                <span class="text-muted text-decoration-line-through fs-5 me-2"><?php echo formatPrice($book['price']); ?></span>
                <span class="text-primary fw-bold fs-2"><?php echo formatPrice($discounted_price); ?></span>
                <span class="badge bg-danger ms-2 fs-6">Save <?php echo number_format($book['discount'], 0); ?>%</span>
            <?php else: ?>
                <span class="text-primary fw-bold fs-2"><?php echo formatPrice($book['price']); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="mb-4">
            <h6>Description</h6>
            <p class="text-muted"><?php echo nl2br(h($book['description'] ?? 'No description available.')); ?></p>
        </div>
        
        <?php if ($book['stock'] > 0): ?>
        <form method="POST" action="" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="form-label">Quantity:</label>
                <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo $book['stock']; ?>" style="width: 80px;">
            </div>
            <div class="col-auto">
                <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
