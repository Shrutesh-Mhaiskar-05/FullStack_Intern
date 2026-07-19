<?php
$pageTitle = 'Admin Dashboard';
$rootPath = '../';
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth_check.php';
requireRole('Admin');
require_once '../includes/functions.php';
require_once '../includes/header.php';

$conn = getDbConnection();

// Stats
$totalUsers   = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalAdmins  = $conn->query("SELECT COUNT(*) as c FROM users WHERE role_id = 1")->fetch_assoc()['c'];
$totalRegular = $conn->query("SELECT COUNT(*) as c FROM users WHERE role_id = 2")->fetch_assoc()['c'];
$newToday     = $conn->query("SELECT COUNT(*) as c FROM users WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
?>

<?php
// Flash messages from redirects
if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_GET['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_GET['error']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php renderFlash(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h3>
    <a href="add_user.php" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i>Add New User</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card text-bg-primary position-relative">
            <div class="card-body">
                <i class="bi bi-people stat-icon"></i>
                <h6>Total Users</h6>
                <h3 class="mb-0"><?= $totalUsers ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-bg-danger position-relative">
            <div class="card-body">
                <i class="bi bi-shield stat-icon"></i>
                <h6>Admins</h6>
                <h3 class="mb-0"><?= $totalAdmins ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-bg-info position-relative">
            <div class="card-body">
                <i class="bi bi-person stat-icon"></i>
                <h6>Regular Users</h6>
                <h3 class="mb-0"><?= $totalRegular ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-bg-success position-relative">
            <div class="card-body">
                <i class="bi bi-person-plus stat-icon"></i>
                <h6>New Today</h6>
                <h3 class="mb-0"><?= $newToday ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-table me-2"></i>All Users</strong>
        <span class="text-secondary small"><?= $totalUsers ?> total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("
                        SELECT u.id, u.username, u.email, u.profile_picture, u.created_at, r.role_name
                        FROM users u
                        JOIN roles r ON u.role_id = r.id
                        ORDER BY u.created_at DESC
                    ");
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <img src="<?= $rootPath . getAvatarUrl($row['profile_picture']) ?>"
                                 alt="" width="32" height="32" class="rounded-circle me-2 table-user-img"
                                 style="object-fit:cover;">
                            <?= htmlspecialchars($row['username']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= roleBadge($row['role_name']) ?></td>
                        <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                        <td class="text-center">
                            <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-danger" title="Delete"
                                    data-id="<?= $row['id'] ?>"
                                    data-username="<?= htmlspecialchars($row['username']) ?>"
                                    onclick="confirmDelete(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteUserName"></strong>?
                <p class="text-danger small mt-2">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(btn) {
    var id = parseInt(btn.getAttribute('data-id'));
    var username = btn.getAttribute('data-username');
    document.getElementById('deleteUserName').textContent = username;
    document.getElementById('deleteConfirmBtn').href = 'delete_user.php?id=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
