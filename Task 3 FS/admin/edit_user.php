<?php
$pageTitle = 'Edit User';
$rootPath = '../';
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth_check.php';
requireRole('Admin');
require_once '../includes/functions.php';
require_once '../includes/header.php';

$conn = getDbConnection();
$error = $success = '';
$userId = (int)($_GET['id'] ?? 0);

if ($userId <= 0) {
    redirectWith('dashboard.php', 'Invalid user ID.', 'danger');
}

$stmt = $conn->prepare("SELECT id, username, email, role_id, profile_picture, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    redirectWith('dashboard.php', 'User not found.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token    = $_POST['csrf_token'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId   = (int)($_POST['role_id'] ?? 2);

    if (!verifyCsrfToken($token)) {
        $error = 'Invalid form submission.';
    } elseif (empty($username) || empty($email)) {
        $error = 'Username and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!isValidUsername($username)) {
        $error = 'Username must be 3-50 characters (letters, numbers, underscores).';
    } elseif (!empty($password) && strlen($password) < MIN_PASSWORD_LENGTH) {
        $error = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check->bind_param("ssi", $username, $email, $userId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Username or email already taken by another user.';
        } else {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, role_id = ? WHERE id = ?");
                $update->bind_param("sssii", $username, $email, $hashed, $roleId, $userId);
            } else {
                $update = $conn->prepare("UPDATE users SET username = ?, email = ?, role_id = ? WHERE id = ?");
                $update->bind_param("ssii", $username, $email, $roleId, $userId);
            }

            if ($update->execute()) {
                $user['username'] = $username;
                $user['email']    = $email;
                $user['role_id']  = $roleId;
                $success = 'User updated successfully.';
            } else {
                $error = 'Failed to update user.';
            }
            $update->close();
        }
        $check->close();
    }
}

$roles = $conn->query("SELECT id, role_name FROM roles");
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <h3 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit User</h3>
    <span class="badge bg-secondary fs-6">#<?= $userId ?></span>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="edit_user.php?id=<?= $userId ?>" data-spinner>
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div class="text-center mb-3">
                        <img src="<?= $rootPath . getAvatarUrl($user['profile_picture']) ?>"
                             alt="" width="80" height="80" class="rounded-circle"
                             style="object-fit:cover;border:2px solid #e9ecef;">
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control"
                               value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <small class="text-secondary">(leave blank to keep current)</small></label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control">
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role</label>
                        <select name="role_id" id="role_id" class="form-select">
                            <?php while ($role = $roles->fetch_assoc()): ?>
                                <option value="<?= $role['id'] ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['role_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Created</label>
                        <input type="text" class="form-control" value="<?= date('d M Y, h:i A', strtotime($user['created_at'])) ?>" disabled>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update User</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
