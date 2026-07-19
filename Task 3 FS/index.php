<?php
session_start();
$pageTitle = 'Dashboard';
$rootPath = '';
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';

$conn = getDbConnection();

// Guest view
if (!isset($_SESSION['user_id'])) {
    require_once 'includes/header.php';
    ?>
    <div class="text-center py-5">
        <h1 class="display-4 fw-bold">User Management System</h1>
        <p class="lead text-secondary mb-5">A secure PHP & MySQL system with authentication, CRUD, and profile management.</p>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-shield-lock text-primary" style="font-size:2.5rem;"></i>
                                <h6 class="mt-2">Secure Auth</h6>
                                <small class="text-secondary">bcrypt hashing + sessions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-success h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-people text-success" style="font-size:2.5rem;"></i>
                                <h6 class="mt-2">User CRUD</h6>
                                <small class="text-secondary">Create, Read, Update, Delete</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-info h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-person-badge text-info" style="font-size:2.5rem;"></i>
                                <h6 class="mt-2">Role-Based</h6>
                                <small class="text-secondary">Admin / User access</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <a href="login.php" class="btn btn-dark btn-lg px-4 me-2"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
            <a href="register.php" class="btn btn-outline-dark btn-lg px-4"><i class="bi bi-person-plus me-2"></i>Register</a>
        </div>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit;
}

// ===== Logged-in user dashboard =====
require_once 'includes/header.php';

$userId = (int)$_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT username, email, profile_picture, created_at, role_id FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Stats
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$newToday   = $conn->query("SELECT COUNT(*) as c FROM users WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
$totalAdmins = $conn->query("SELECT COUNT(*) as c FROM users WHERE role_id = 1")->fetch_assoc()['c'];
?>

<?php renderFlash(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Welcome back, <?= htmlspecialchars($user['username']) ?>!</h3>
    <span class="badge bg-<?= $_SESSION['role_name'] === 'Admin' ? 'danger' : 'primary' ?> fs-6">
        <?= htmlspecialchars($_SESSION['role_name']) ?>
    </span>
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
        <div class="card stat-card text-bg-success position-relative">
            <div class="card-body">
                <i class="bi bi-person-plus stat-icon"></i>
                <h6>New Today</h6>
                <h3 class="mb-0"><?= $newToday ?></h3>
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
                <i class="bi bi-calendar-check stat-icon"></i>
                <h6>Member Since</h6>
                <h3 class="mb-0"><?= date('d M', strtotime($user['created_at'])) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <img src="<?= getAvatarUrl($user['profile_picture']) ?>"
                     alt="Avatar" width="140" height="140" class="rounded-circle mb-3"
                     style="object-fit:cover;border:3px solid #e9ecef;">
                <h5><?= htmlspecialchars($user['username']) ?></h5>
                <p class="text-secondary mb-2"><?= htmlspecialchars($user['email']) ?></p>
                <?= roleBadge($_SESSION['role_name']) ?>
                <hr>
                <a href="profile.php" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-pencil me-1"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-clock-history me-2"></i>Recent Users</strong>
                <?php if ($_SESSION['role_name'] === 'Admin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-sm btn-outline-primary">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $conn->query("
                                SELECT u.username, u.email, u.profile_picture, u.created_at, r.role_name
                                FROM users u
                                JOIN roles r ON u.role_id = r.id
                                ORDER BY u.created_at DESC
                                LIMIT 5
                            ");
                            while ($r = $recent->fetch_assoc()):
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= getAvatarUrl($r['profile_picture']) ?>" alt="" width="28" height="28" class="rounded-circle me-2" style="object-fit:cover;">
                                    <?= htmlspecialchars($r['username']) ?>
                                </td>
                                <td><?= htmlspecialchars($r['email']) ?></td>
                                <td><?= roleBadge($r['role_name']) ?></td>
                                <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
