<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Login';
$authLayout = true;
$rootPath = '';
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token    = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($token)) {
        $error = 'Invalid form submission.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Both fields are required.';
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.email, u.password, u.profile_picture, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.username = ?
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            regenerateSession();
            $_SESSION['user_id']         = $user['id'];
            $_SESSION['username']        = $user['username'];
            $_SESSION['email']           = $user['email'];
            $_SESSION['role_name']       = $user['role_name'];
            $_SESSION['profile_picture'] = $user['profile_picture'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<form method="POST" action="" data-spinner>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required>
        </div>
    </div>

    <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="loginPassword">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">Sign In</button>
</form>

<p class="text-center mt-4 mb-0">
    Don't have an account? <a href="register.php" class="text-decoration-none fw-semibold">Register here</a>
</p>

<?php require_once 'includes/footer.php'; ?>
