<?php
$page_title = 'Manage Users';
require_once 'includes/admin_header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id == $_SESSION['user_id']) {
        redirect('manage_users.php', 'You cannot delete your own account.', 'danger');
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    redirect('manage_users.php', 'User deleted successfully.', 'success');
}

// Handle Role Change
if (isset($_GET['toggle_role'])) {
    $id = (int)$_GET['toggle_role'];
    if ($id == $_SESSION['user_id']) {
        redirect('manage_users.php', 'You cannot change your own role.', 'danger');
    }
    $stmt = $conn->prepare("UPDATE users SET role_id = CASE WHEN role_id = 1 THEN 2 ELSE 1 END WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    redirect('manage_users.php', 'User role updated.', 'success');
}

$search = $_GET['search'] ?? '';
$period = $_GET['period'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ?)";
    $s = "%$search%";
    $params[] = $s; $params[] = $s;
    $types .= 'ss';
}

if (!empty($period)) {
    switch ($period) {
        case '7': $where_conditions[] = "u.created_at >= NOW() - INTERVAL 7 DAY"; break;
        case '30': $where_conditions[] = "u.created_at >= NOW() - INTERVAL 30 DAY"; break;
        case '90': $where_conditions[] = "u.created_at >= NOW() - INTERVAL 90 DAY"; break;
    }
}

$where = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$count_sql = "SELECT COUNT(*) as c FROM users u $where";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['c'];

$sql = "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit; $params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$users = $stmt->get_result();
$total_pages = ceil($total / $limit);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Manage Users</h3>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo h($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="period" class="form-select">
                    <option value="">All Time</option>
                    <option value="7" <?php echo $period === '7' ? 'selected' : ''; ?>>Last 7 Days (Active)</option>
                    <option value="30" <?php echo $period === '30' ? 'selected' : ''; ?>>Last 30 Days</option>
                    <option value="90" <?php echo $period === '90' ? 'selected' : ''; ?>>Last 90 Days</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="manage_users.php" class="btn btn-outline-secondary w-100">Clear</a>
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="../uploads/<?php echo h($user['profile_pic']); ?>" 
                                     class="rounded-circle me-2" 
                                     style="width: 35px; height: 35px; object-fit: cover;"
                                     onerror="this.src='../assets/images/default.png'">
                                <?php echo h($user['username']); ?>
                            </div>
                        </td>
                        <td><?php echo h($user['email']); ?></td>
                        <td><?php echo h($user['phone'] ?? '-'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $user['role_name'] === 'admin' ? 'danger' : 'secondary'; ?>">
                                <?php echo ucfirst($user['role_name']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="?toggle_role=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-info"
                               onclick="return confirm('Toggle role for this user?')">
                                <i class="bi bi-arrow-left-right"></i>
                            </a>
                            <a href="?delete=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this user permanently?')">
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

<?php if ($total_pages > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
