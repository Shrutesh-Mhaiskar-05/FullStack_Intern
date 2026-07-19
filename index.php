<?php
$page_title = 'Home';
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get featured books
$books = getBooks($conn, '', '', '', '', 'newest', 1, 4);
$categories = getCategories($conn);

require_once 'includes/header.php';
?>

<section class="hero-section text-center py-5 bg-primary text-light rounded-3 mb-4 shadow">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to Online BookStore</h1>
        <p class="lead">Discover thousands of books across every genre. Start your reading journey today.</p>
        <a href="shop.php" class="btn btn-light btn-lg px-5 mt-3">Browse Books</a>
    </div>
</section>

<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Categories</h3>
    </div>
    <div class="row g-3">
        <?php while ($cat = $categories->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="shop.php?category=<?php echo $cat['id']; ?>" class="text-decoration-none">
                <div class="card text-center h-100 border-0 shadow-sm category-card">
                    <div class="card-body">
                        <i class="bi bi-bookmark-fill fs-1 text-primary"></i>
                        <h6 class="mt-2 text-dark"><?php echo h($cat['name']); ?></h6>
                    </div>
                </div>
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Featured Books</h3>
        <a href="shop.php" class="btn btn-outline-primary">View All</a>
    </div>
    <div class="row g-4">
        <?php while ($book = $books['books']->fetch_assoc()): 
            $discounted_price = getDiscountedPrice($book['price'], $book['discount']);
        ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm book-card">
                <div class="card-img-wrapper">
                    <?php echo renderDiscountBadge($book['discount']); ?>
                    <img src="assets/images/<?php echo h($book['image']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo h($book['title']); ?>"
                         onerror="this.src='assets/images/default-book.png'">
                    <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-dark btn-sm quick-view-btn">
                        <i class="bi bi-eye me-1"></i> Quick View
                    </a>
                </div>
                <div class="card-body d-flex flex-column p-3">
                    <div class="mb-1">
                        <?php if ($book['rating']): ?>
                            <?php echo renderStars($book['rating']); ?>
                        <?php endif; ?>
                    </div>
                    <h6 class="card-title text-truncate fw-semibold mb-1"><?php echo h($book['title']); ?></h6>
                    <p class="card-text text-muted small mb-2">by <?php echo h($book['author']); ?></p>
                    <div class="mb-1 small"><?php echo renderAvailabilityBadge($book['stock']); ?></div>
                    <div class="mt-auto">
                        <div class="book-price mb-2">
                            <?php if ($book['discount'] > 0): ?>
                                <span class="original"><?php echo formatPrice($book['price']); ?></span>
                                <span class="discounted"><?php echo formatPrice($discounted_price); ?></span>
                            <?php else: ?>
                                <span class="discounted"><?php echo formatPrice($book['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
