<?php
$page_title = 'My Favorites';
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle remove from wishlist
if (isset($_GET['remove'])) {
    $book_id = (int)$_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    redirect('wishlist.php', 'Book removed from favorites.', 'info');
}

$items = getWishlistItems($conn, $user_id);

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold mb-1"><i class="bi bi-heart-fill text-danger me-2"></i>My Favorites</h3>
        <p class="text-muted">Books you've saved for later</p>
    </div>
</div>

<?php if ($items->num_rows > 0): ?>
<div class="row g-4">
    <?php while ($item = $items->fetch_assoc()): 
        $discounted_price = getDiscountedPrice($item['price'], $item['discount']);
    ?>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100 border-0 shadow-sm book-card">
            <div class="card-img-wrapper">
                <?php echo renderDiscountBadge($item['discount']); ?>
                <button class="favorite-btn active" onclick="window.location='wishlist.php?remove=<?php echo $item['book_id']; ?>'" title="Remove from favorites">
                    <i class="bi bi-heart-fill"></i>
                </button>
                <img src="assets/images/<?php echo h($item['image']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo h($item['title']); ?>"
                     onerror="this.src='assets/images/default-book.png'">
            </div>
            <div class="card-body d-flex flex-column p-3">
                <div class="mb-2">
                    <?php if ($item['rating']): ?>
                        <?php echo renderStars($item['rating']); ?>
                    <?php endif; ?>
                </div>
                <h6 class="card-title text-truncate fw-semibold mb-1"><?php echo h($item['title']); ?></h6>
                <p class="card-text text-muted small mb-2">by <?php echo h($item['author']); ?></p>
                <div class="mb-2"><?php echo renderAvailabilityBadge($item['stock']); ?></div>
                <div class="mt-auto">
                    <div class="book-price mb-2">
                        <?php if ($item['discount'] > 0): ?>
                            <span class="original"><?php echo formatPrice($item['price']); ?></span>
                            <span class="discounted"><?php echo formatPrice($discounted_price); ?></span>
                        <?php else: ?>
                            <span class="discounted"><?php echo formatPrice($item['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="book_details.php?id=<?php echo $item['book_id']; ?>" class="btn btn-outline-primary btn-sm flex-grow-1">Details</a>
                        <a href="cart_add.php?id=<?php echo $item['book_id']; ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-cart-plus"></i>
                        </a>
                        <a href="wishlist.php?remove=<?php echo $item['book_id']; ?>" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <i class="bi bi-heart"></i>
    <h5 class="mt-3">Your favorites list is empty</h5>
    <p class="text-muted">Browse books and click the heart icon to save your favorites.</p>
    <a href="shop.php" class="btn btn-primary mt-2"><i class="bi bi-shop me-1"></i> Browse Books</a>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
