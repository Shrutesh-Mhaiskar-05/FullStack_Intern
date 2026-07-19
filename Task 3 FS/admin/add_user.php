<?php
$pageTitle = 'Add User';
$rootPath = '../';
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth_check.php';
requireRole('Admin');
require_once '../includes/functions.php';
require_once '../includes/header.php';

$error = $success = '';
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token    = $_POST['csrf_token'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId   = (int)($_POST['role_id'] ?? 2);

    if (!verifyCsrfToken($token)) {
        $error = 'Invalid form submission.';
    } elseif (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!isValidUsername($username)) {
        $error = 'Username must be 3-50 characters (letters, numbers, underscores).';
    } elseif (strlen($password) < MIN_PASSWORD_LENGTH) {
        $error = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
            $insert->bind_param("sssi", $username, $email, $hashed, $roleId);

            if ($insert->execute()) {
                redirectWith('dashboard.php', "User '$username' created successfully.");
            } else {
                $error = 'Failed to create user.';
            }
            $insert->close();
        }
        $check->close();
    }
}

$roles = $conn->query("SELECT id, role_name FROM roles");
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<h3 class="mb-4"><i class="bi bi-person-plus me-2"></i>Add New User</h3>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" data-spinner>
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required minlength="3" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" required minlength="<?= MIN_PASSWORD_LENGTH ?>">
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role</label>
                        <select name="role_id" id="role_id" class="form-select">
                            <?php while ($role = $roles->fetch_assoc()): ?>
                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Create User</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
