<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$search = trim($_GET['q'] ?? '');
$category_id = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));

$data = getBooks($conn, $search, $category_id, $min_price, $max_price, $sort, $page, 12);

$html = '';
if ($data['books']->num_rows > 0) {
    while ($book = $data['books']->fetch_assoc()):
        $discounted_price = getDiscountedPrice($book['price'], $book['discount']);
        $in_wishlist = isLoggedIn() ? isInWishlist($conn, $_SESSION['user_id'], $book['id']) : false;
        ob_start();
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
                         class="card-img-top" alt="<?php echo h($book['title']); ?>"
                         onerror="this.src='assets/images/default-book.png'">
                    <button class="btn btn-dark btn-sm quick-view-btn" 
                            data-bs-toggle="modal" data-bs-target="#quickViewModal"
                            onclick="loadQuickView(<?php echo $book['id']; ?>)">
                        <i class="bi bi-eye me-1"></i> Quick View
                    </button>
                </div>
                <div class="card-body d-flex flex-column p-3">
                    <div class="mb-2"><?php if ($book['rating']) echo renderStars($book['rating']); ?></div>
                    <h6 class="card-title text-truncate fw-semibold mb-1"><?php echo h($book['title']); ?></h6>
                    <p class="card-text text-muted small mb-2">by <?php echo h($book['author']); ?></p>
                    <div class="mb-2"><?php echo renderAvailabilityBadge($book['stock']); ?></div>
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
                            <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-primary btn-sm flex-grow-1">Details</a>
                            <?php if ($book['stock'] > 0): ?>
                            <a href="cart_add.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-cart-plus"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
        $html .= ob_get_clean();
    endwhile;
} else {
    $html = '<div class="col-12"><div class="empty-state"><i class="bi bi-search"></i><h5 class="mt-3">No books found</h5><p class="text-muted">Try adjusting your search or filters.</p></div></div>';
}

$query_string = $_GET;
$query_string['page'] = '{page}';
$url_pattern = 'shop.php?' . http_build_query($query_string);
$pagination = paginationLinks($data['current_page'], $data['pages'], $url_pattern);

header('Content-Type: application/json');
echo json_encode([
    'html' => $html,
    'pagination' => $pagination,
    'total' => $data['total'],
    'showing' => $data['books']->num_rows,
    'pages' => $data['pages']
]);
