<?php
session_start();
$pageTitle = 'Register';
$authLayout = true;
$rootPath = '';
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $token    = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($token)) {
        $error = 'Invalid form submission.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!isValidUsername($username)) {
        $error = 'Username must be 3-50 characters (letters, numbers, underscores).';
    } elseif (strlen($password) < MIN_PASSWORD_LENGTH) {
        $error = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = getDbConnection();

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $roleId = 2;

            $insert = $conn->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
            $insert->bind_param("sssi", $username, $email, $hashed, $roleId);

            if ($insert->execute()) {
                $success = 'Registration successful! You can now <a href="login.php" class="fw-semibold">login</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" action="" id="registerForm" data-spinner>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div class="mb-3">
        <label for="regUsername" class="form-label">Username</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" id="regUsername" class="form-control" placeholder="Choose a username" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+">
        </div>
    </div>

    <div class="mb-3">
        <label for="regEmail" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="regEmail" class="form-control" placeholder="Enter your email" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="regPassword" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="regPassword" class="form-control" placeholder="Min <?= MIN_PASSWORD_LENGTH ?> characters" required minlength="<?= MIN_PASSWORD_LENGTH ?>">
            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="regPassword">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <div class="mb-4">
        <label for="regConfirm" class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="confirm_password" id="regConfirm" class="form-control" placeholder="Repeat your password" required>
            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="regConfirm">
                <i class="bi bi-eye"></i>
            </button>
        </div>
        <div id="passwordMatchMsg" class="invalid-feedback" style="display:none;">Passwords do not match.</div>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">Create Account</button>
</form>

<p class="text-center mt-4 mb-0">
    Already have an account? <a href="login.php" class="text-decoration-none fw-semibold">Sign in</a>
</p>

<?php require_once 'includes/footer.php'; ?>
