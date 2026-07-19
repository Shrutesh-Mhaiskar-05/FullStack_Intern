<?php
$page_title = 'Manage Categories';
require_once 'includes/admin_header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    redirect('manage_categories.php', 'Category deleted successfully.', 'success');
}

// Handle Add/Edit
$edit_mode = false;
$edit_cat = ['id' => 0, 'name' => '', 'description' => ''];

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_cat = getCategoryById($conn, (int)$_GET['edit']);
    if (!$edit_cat) redirect('manage_categories.php', 'Category not found.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cat_id = (int)($_POST['cat_id'] ?? 0);

    $errors = [];
    if (empty($name)) $errors[] = 'Category name is required.';

    if (empty($errors)) {
        if ($cat_id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $description, $cat_id);
            $stmt->execute();
            redirect('manage_categories.php', 'Category updated successfully.', 'success');
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
            $stmt->execute();
            redirect('manage_categories.php', 'Category added successfully.', 'success');
        }
    }
}

$categories = getCategories($conn);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Manage Categories</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#catModal">
        <i class="bi bi-plus-lg"></i> Add Category
    </button>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $cat['id']; ?></td>
                                <td class="fw-bold"><?php echo h($cat['name']); ?></td>
                                <td><?php echo h($cat['description'] ?? '-'); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="?delete=<?php echo $cat['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this category? Books in this category will become uncategorized.')">
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
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><?php echo $edit_mode ? 'Edit Category' : 'Add Category'; ?></h5>
                <form method="POST" action="">
                    <input type="hidden" name="cat_id" value="<?php echo $edit_cat['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo h($edit_cat['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo h($edit_cat['description']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <?php echo $edit_mode ? 'Update' : 'Create'; ?> Category
                    </button>
                    <?php if ($edit_mode): ?>
                    <a href="manage_categories.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
