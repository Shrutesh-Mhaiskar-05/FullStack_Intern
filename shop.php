<?php
$page_title = 'Shop';
require_once 'includes/config.php';
require_once 'includes/functions.php';

$search = trim($_GET['search'] ?? '');
$category_id = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;

$data = getBooks($conn, $search, $category_id, $min_price, $max_price, $sort, $page, $limit);
$categories = getCategories($conn);

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold mb-1">Browse Books</h3>
        <p class="text-muted">Discover your next great read from our collection</p>
    </div>
</div>

<!-- Category Pills -->
<div class="category-pills mb-4">
    <a href="shop.php" class="category-pill <?php echo empty($category_id) ? 'active' : ''; ?>">
        <i class="bi bi-grid-fill me-1"></i> All
    </a>
    <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
    <a href="shop.php?category=<?php echo $cat['id']; ?>" 
       class="category-pill <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
        <?php echo h($cat['name']); ?>
    </a>
    <?php endwhile; ?>
</div>

<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-sliders2 me-2"></i>Filters</h5>
                <form method="GET" action="shop.php" id="filterForm">
                    <?php if (!empty($category_id)): ?>
                    <input type="hidden" name="category" value="<?php echo h($category_id); ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Search</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Title or author..." value="<?php echo h($search); ?>">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Category</label>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo h($cat['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Price Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control form-control-sm" 
                                       placeholder="Min" value="<?php echo h($min_price); ?>" min="0" step="0.01">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control form-control-sm" 
                                       placeholder="Max" value="<?php echo h($max_price); ?>" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Sort By</label>
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="title" <?php echo $sort == 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                        </select>
                    </div>

                    <?php if (!empty($search) || !empty($category_id) || !empty($min_price) || !empty($max_price) || $sort !== 'newest'): ?>
                    <a href="shop.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="bi bi-x-circle me-1"></i> Clear All Filters
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="col-lg-9">
        <!-- Results Bar -->
        <div class="results-bar shadow-sm">
            <div>
                <span class="fw-medium"><?php echo $data['total']; ?></span> book<?php echo $data['total'] !== 1 ? 's' : ''; ?> found
                <?php if (!empty($search)): ?>
                <span class="text-muted">for "<strong><?php echo h($search); ?></strong>"</span>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($search) || !empty($category_id) || !empty($min_price) || !empty($max_price)): ?>
                <div class="filter-tags d-none d-md-flex">
                    <?php if (!empty($search)): ?>
                    <span class="filter-tag"><?php echo h($search); ?> <a href="?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" class="remove text-decoration-none">&times;</a></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <form method="GET" action="shop.php" class="m-0">
                    <?php foreach ($_GET as $k => $v): ?>
                        <?php if ($k !== 'sort' && $k !== 'page'): ?>
                        <input type="hidden" name="<?php echo h($k); ?>" value="<?php echo h($v); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <select name="sort" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low-High</option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High-Low</option>
                        <option value="title" <?php echo $sort == 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                    </select>
                </form>
            </div>
        </div>

        <?php if ($data['books']->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($book = $data['books']->fetch_assoc()): 
                $discounted_price = getDiscountedPrice($book['price'], $book['discount']);
                $in_wishlist = isLoggedIn() ? isInWishlist($conn, $_SESSION['user_id'], $book['id']) : false;
            ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100 border-0 shadow-sm book-card">
                    <div class="card-img-wrapper">
                        <?php echo renderDiscountBadge($book['discount']); ?>
                        
                        <?php if (isLoggedIn()): ?>
                        <button class="favorite-btn <?php echo $in_wishlist ? 'active' : ''; ?>" 
                                data-book-id="<?php echo $book['id']; ?>"
                                onclick="toggleWishlist(this, <?php echo $book['id']; ?>)">
                            <i class="bi <?php echo $in_wishlist ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                        </button>
                        <?php endif; ?>

                        <img src="assets/images/<?php echo h($book['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo h($book['title']); ?>"
                             onerror="this.src='assets/images/default-book.png'">
                        
                        <button class="btn btn-dark btn-sm quick-view-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#quickViewModal"
                                onclick="loadQuickView(<?php echo $book['id']; ?>)">
                            <i class="bi bi-eye me-1"></i> Quick View
                        </button>
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <div class="mb-2">
                            <?php if ($book['rating']): ?>
                                <?php echo renderStars($book['rating']); ?>
                            <?php endif; ?>
                        </div>
                        <h6 class="card-title text-truncate fw-semibold mb-1"><?php echo h($book['title']); ?></h6>
                        <p class="card-text text-muted small mb-2">by <?php echo h($book['author']); ?></p>
                        <div class="mb-2">
                            <?php echo renderAvailabilityBadge($book['stock']); ?>
                        </div>
                        <div class="mt-auto">
                            <div class="book-price mb-2">
                                <?php if ($book['discount'] > 0): ?>
                                    <span class="original"><?php echo formatPrice($book['price']); ?></span>
                                    <span class="discounted"><?php echo formatPrice($discounted_price); ?></span>
                                <?php else: ?>
                                    <span class="discounted"><?php echo formatPrice($book['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                                    <i class="bi bi-info-circle me-1"></i> Details
                                </a>
                                <?php if ($book['stock'] > 0): ?>
                                <a href="cart_add.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-cart-plus"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php 
        $query_string = $_GET;
        $query_string['page'] = '{page}';
        $url_pattern = 'shop.php?' . http_build_query($query_string);
        echo '<div class="mt-4">' . paginationLinks($data['current_page'], $data['pages'], $url_pattern) . '</div>';
        ?>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-search"></i>
            <h5 class="mt-3">No books found</h5>
            <p class="text-muted">Try adjusting your filters or search terms.</p>
            <a href="shop.php" class="btn btn-primary mt-2">View All Books</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickViewContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading book details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Wishlist Script (inline for quick response) -->
<script>
function toggleWishlist(btn, bookId) {
    const icon = btn.querySelector('i');
    const wasActive = btn.classList.contains('active');
    
    fetch('wishlist_ajax.php?book_id=' + bookId + '&action=' + (wasActive ? 'remove' : 'add'))
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('active');
                if (btn.classList.contains('active')) {
                    icon.className = 'bi bi-heart-fill';
                } else {
                    icon.className = 'bi bi-heart';
                }
                // Update header badge
                const badge = document.querySelector('.navbar a[href="wishlist.php"] .badge');
                if (badge) {
                    const count = parseInt(badge.textContent) || 0;
                    badge.textContent = data.count;
                    if (data.count === 0) badge.remove();
                }
            } else if (data.require_login) {
                window.location.href = 'login.php';
            }
        });
}

function loadQuickView(bookId) {
    const container = document.getElementById('quickViewContent');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading book details...</p></div>';
    
    fetch('quick_view_ajax.php?id=' + bookId)
        .then(r => r.text())
        .then(html => {
            container.innerHTML = html;
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>
