<?php
$page_title = 'Shop';
require_once 'includes/config.php';
require_once 'includes/functions.php';

$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));

$data = getBooks($conn, $search, $category_id, $min_price, $max_price, $sort, $page, 8);
$categories = getCategories($conn);

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold">Browse Books</h3>
    </div>
</div>

<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Filters</h5>
                <form method="GET" action="shop.php">
                    <div class="mb-3">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Title or author..." value="<?php echo h($search); ?>">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo h($cat['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Min Price</label>
                            <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo h($min_price); ?>" min="0" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Max Price</label>
                            <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo h($max_price); ?>" min="0" step="0.01">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="title" <?php echo $sort == 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                        </select>
                    </div>

                    <?php if (!empty($search) || !empty($category_id) || !empty($min_price) || !empty($max_price)): ?>
                    <a href="shop.php" class="btn btn-outline-secondary w-100">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0">Showing <?php echo $data['books']->num_rows; ?> of <?php echo $data['total']; ?> books</p>
        </div>

        <?php if ($data['books']->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($book = $data['books']->fetch_assoc()): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 border-0 shadow-sm book-card">
                    <div class="card-img-wrapper">
                        <img src="assets/images/<?php echo h($book['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo h($book['title']); ?>"
                             onerror="this.src='assets/images/default-book.png'">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate"><?php echo h($book['title']); ?></h6>
                        <p class="card-text text-muted small"><?php echo h($book['author']); ?></p>
                        <p class="text-muted small"><i class="bi bi-bookmark"></i> <?php echo h($book['category_name'] ?? 'Uncategorized'); ?></p>
                        <div class="mt-auto">
                            <p class="fw-bold text-primary mb-2"><?php echo formatPrice($book['price']); ?></p>
                            <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary w-100">Details</a>
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
        echo paginationLinks($data['current_page'], $data['pages'], $url_pattern);
        ?>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-emoji-frown fs-1 text-muted"></i>
            <h5 class="mt-3">No books found</h5>
            <p class="text-muted">Try adjusting your filters or search terms.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
