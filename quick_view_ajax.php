<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$book_id = (int)($_GET['id'] ?? 0);
$book = getBookById($conn, $book_id);

if (!$book) {
    echo '<div class="text-center py-4"><p class="text-danger">Book not found.</p></div>';
    exit;
}

$discounted_price = getDiscountedPrice($book['price'], $book['discount']);
?>

<div class="row g-4">
    <div class="col-md-5">
        <img src="assets/images/<?php echo h($book['image']); ?>" 
             class="img-fluid quick-view-image rounded" 
             alt="<?php echo h($book['title']); ?>"
             onerror="this.src='assets/images/default-book.png'">
    </div>
    <div class="col-md-7">
        <div class="mb-1">
            <?php if ($book['rating']): ?>
                <?php echo renderStars($book['rating']); ?>
            <?php endif; ?>
        </div>
        <h4 class="fw-bold mb-1"><?php echo h($book['title']); ?></h4>
        <p class="text-muted mb-2">by <strong><?php echo h($book['author']); ?></strong></p>
        
        <div class="mb-3">
            <span class="badge bg-info me-1"><?php echo h($book['category_name'] ?? 'Uncategorized'); ?></span>
            <?php echo renderAvailabilityBadge($book['stock']); ?>
        </div>

        <?php if ($book['isbn']): ?>
        <p class="small text-muted mb-2">ISBN: <?php echo h($book['isbn']); ?></p>
        <?php endif; ?>

        <div class="quick-view-price mb-3">
            <?php if ($book['discount'] > 0): ?>
                <span class="original"><?php echo formatPrice($book['price']); ?></span>
                <span class="discounted"><?php echo formatPrice($discounted_price); ?></span>
                <span class="badge bg-danger ms-2">-<?php echo number_format($book['discount'], 0); ?>% OFF</span>
            <?php else: ?>
                <span class="discounted"><?php echo formatPrice($book['price']); ?></span>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <h6>Description</h6>
            <p class="text-muted small"><?php echo nl2br(h(substr($book['description'] ?? '', 0, 300))); ?>
            <?php if (strlen($book['description'] ?? '') > 300): ?>...<?php endif; ?></p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <?php if ($book['stock'] > 0): ?>
            <button class="btn btn-primary add-to-cart-btn" data-book-id="<?php echo $book['id']; ?>">
                <i class="bi bi-cart-plus me-1"></i> Add to Cart
            </button>
            <?php endif; ?>
            <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-right me-1"></i> Full Details
            </a>
        </div>
        <script>
        document.querySelector('#quickViewModal .add-to-cart-btn')?.addEventListener('click', function() {
            const bookId = this.dataset.bookId;
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
            this.disabled = true;
            fetch('cart_ajax.php?id=' + bookId)
                .then(r => r.json())
                .then(data => {
                    if (data.success) { 
                        showToast('Added to cart!', 'success');
                        updateCartBadge(data.cart_count);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('quickViewModal'));
                        if (modal) modal.hide();
                    }
                    else if (data.login_required) { window.location.href = 'login.php'; }
                    else { showToast(data.message || 'Failed.', 'error'); }
                })
                .catch(() => { alert('Something went wrong.'); })
                .finally(() => { this.innerHTML = originalText; this.disabled = false; });
        });
        </script>
    </div>
</div>
