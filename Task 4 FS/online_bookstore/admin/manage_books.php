<?php
$page_title = 'Manage Books';
require_once 'includes/admin_header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $book = getBookById($conn, $id);
    if ($book && $book['image'] !== 'default-book.png' && file_exists('../assets/images/' . $book['image'])) {
        unlink('../assets/images/' . $book['image']);
    }
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    redirect('manage_books.php', 'Book deleted successfully.', 'success');
}

// Handle Add/Edit
$edit_mode = false;
$book = ['id' => 0, 'title' => '', 'author' => '', 'isbn' => '', 'description' => '', 'price' => '', 'stock' => '', 'image' => 'default-book.png', 'category_id' => ''];

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $book = getBookById($conn, (int)$_GET['edit']);
    if (!$book) redirect('manage_books.php', 'Book not found.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $book_id = (int)($_POST['book_id'] ?? 0);

    $errors = [];
    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($author)) $errors[] = 'Author is required.';
    if ($price <= 0) $errors[] = 'Price must be greater than 0.';
    if ($stock < 0) $errors[] = 'Stock cannot be negative.';

    if (empty($errors)) {
        $image = $book['image'];
        if (!empty($_FILES['image']['name'])) {
            $uploaded = uploadImage($_FILES['image'], '../assets/images/', 'default-book.png');
            if ($uploaded !== 'default-book.png') {
                if ($book['image'] !== 'default-book.png' && file_exists('../assets/images/' . $book['image'])) {
                    unlink('../assets/images/' . $book['image']);
                }
                $image = $uploaded;
            }
        }

        if ($book_id > 0) {
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, isbn=?, description=?, price=?, stock=?, image=?, category_id=? WHERE id=?");
            $stmt->bind_param("ssssdsisi", $title, $author, $isbn, $description, $price, $stock, $image, $category_id, $book_id);
            $stmt->execute();
            redirect('manage_books.php', 'Book updated successfully.', 'success');
        } else {
            $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, description, price, stock, image, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssdsis", $title, $author, $isbn, $description, $price, $stock, $image, $category_id);
            $stmt->execute();
            redirect('manage_books.php', 'Book added successfully.', 'success');
        }
    }
}

$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$data = getBooks($conn, $search, '', '', '', 'newest', $page, 10);
$categories = getCategories($conn);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Manage Books</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookModal">
        <i class="bi bi-plus-lg"></i> Add Book
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search books..." value="<?php echo h($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = $data['books']->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $book['id']; ?></td>
                        <td>
                            <img src="../assets/images/<?php echo h($book['image']); ?>" 
                                 style="width: 50px; height: 70px; object-fit: cover;" 
                                 alt="" onerror="this.src='../assets/images/default-book.png'">
                        </td>
                        <td class="fw-bold"><?php echo h($book['title']); ?></td>
                        <td><?php echo h($book['author']); ?></td>
                        <td><span class="badge bg-info"><?php echo h($book['category_name'] ?? 'N/A'); ?></span></td>
                        <td><?php echo formatPrice($book['price']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $book['stock'] > 0 ? 'success' : 'danger'; ?>">
                                <?php echo $book['stock']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="?edit=<?php echo $book['id']; ?>" class="btn btn-sm btn-warning edit-book" 
                               data-id="<?php echo $book['id']; ?>"
                               data-title="<?php echo h($book['title']); ?>"
                               data-author="<?php echo h($book['author']); ?>"
                               data-isbn="<?php echo h($book['isbn']); ?>"
                               data-description="<?php echo h($book['description']); ?>"
                               data-price="<?php echo $book['price']; ?>"
                               data-stock="<?php echo $book['stock']; ?>"
                               data-category="<?php echo $book['category_id']; ?>">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="?delete=<?php echo $book['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirmDelete('book')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$query_string = $_GET;
$query_string['page'] = '{page}';
$url_pattern = 'manage_books.php?' . http_build_query($query_string);
echo '<div class="mt-3">' . paginationLinks($data['current_page'], $data['pages'], $url_pattern) . '</div>';
?>

<!-- Book Modal -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookModalTitle">Add Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="book_id" id="book_id" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="book_title" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Author <span class="text-danger">*</span></label>
                            <input type="text" name="author" id="book_author" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" id="book_isbn" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="book_category" class="form-select">
                                <option value="">Select Category</option>
                                <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo h($cat['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="book_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="book_stock" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="book_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($edit_mode): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('edit')) {
        const btn = document.querySelector('.edit-book');
        if (btn) {
            document.getElementById('bookModalTitle').textContent = 'Edit Book';
            document.getElementById('book_id').value = btn.dataset.id;
            document.getElementById('book_title').value = btn.dataset.title;
            document.getElementById('book_author').value = btn.dataset.author;
            document.getElementById('book_isbn').value = btn.dataset.isbn;
            document.getElementById('book_description').value = btn.dataset.description;
            document.getElementById('book_price').value = btn.dataset.price;
            document.getElementById('book_stock').value = btn.dataset.stock;
            document.getElementById('book_category').value = btn.dataset.category;
            new bootstrap.Modal(document.getElementById('bookModal')).show();
        }
    }
});
</script>
<?php endif; ?>

<script>
function confirmDelete(item) {
    return confirm('Are you sure you want to delete this ' + item + '? This action cannot be undone.');
}

document.querySelectorAll('.edit-book').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('bookModalTitle').textContent = 'Edit Book';
        document.getElementById('book_id').value = this.dataset.id;
        document.getElementById('book_title').value = this.dataset.title;
        document.getElementById('book_author').value = this.dataset.author;
        document.getElementById('book_isbn').value = this.dataset.isbn;
        document.getElementById('book_description').value = this.dataset.description;
        document.getElementById('book_price').value = this.dataset.price;
        document.getElementById('book_stock').value = this.dataset.stock;
        document.getElementById('book_category').value = this.dataset.category;
        new bootstrap.Modal(document.getElementById('bookModal')).show();
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
