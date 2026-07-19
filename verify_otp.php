<?php
$page_title = 'Verify Email';
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$email = $_SESSION['otp_email'] ?? $_GET['email'] ?? '';
if (empty($email)) {
    redirect('register.php', 'Please register first.', 'warning');
}

// Validate that the email exists in DB
$stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
if (!$userRow) {
    redirect('register.php', 'Account not found. Please register.', 'warning');
}
if ($userRow['is_verified']) {
    redirect('login.php', 'Email already verified. Please login.', 'info');
}

$errors = [];
$success = '';

// Resend OTP (with rate limiting)
if (isset($_GET['resend'])) {
    $result = sendOtpEmail($conn, $email);
    if ($result === 'wait') {
        $errors[] = 'Please wait 30 seconds before requesting a new OTP.';
    } else {
        $success = 'A new OTP has been sent.';
    }
}

// Verify OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    if (!preg_match('/^\d{6}$/', $otp)) {
        $errors[] = 'Please enter a valid 6-digit numeric OTP.';
    } else {
        $result = verifyOtp($conn, $email, $otp);
        if ($result === 'verified') {
            unset($_SESSION['otp_email'], $_SESSION['otp_last_sent']);
            // Auto-login after verification
            $stmt = $conn->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role_name'];
                $_SESSION['profile_pic'] = $user['profile_pic'];
                $_SESSION['is_verified'] = 1;
                redirect('index.php', 'Email verified! Welcome to BookStore.', 'success');
            }
            redirect('login.php', 'Email verified successfully! You can now login.', 'success');
        } elseif ($result === 'expired') {
            $errors[] = 'OTP has expired. Please <a href="?resend=1&email=' . urlencode($email) . '">resend a new OTP</a>.';
        } elseif ($result === 'wrong') {
            $errors[] = 'Incorrect OTP. Please try again.';
        } elseif ($result === 'not_found') {
            $errors[] = 'No verification request found. Please <a href="?resend=1&email=' . urlencode($email) . '">request a new OTP</a>.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 text-center">
                <i class="bi bi-shield-check fs-1 text-primary"></i>
                <h4 class="fw-bold mt-2">Verify Email</h4>
                <p class="text-muted">Enter the 6-digit OTP sent to <strong><?php echo h($email); ?></strong></p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><?php echo implode('<br>', array_map('h', $errors)); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo h($success); ?></div>
                <?php endif; ?>

                <div class="alert alert-success">
                    <small>✅ An OTP has been sent to <strong><?php echo h($email); ?></strong>. Please check your inbox (also check Spam folder).</small>
                </div>

                <form method="POST" action="" class="mt-3">
                    <div class="mb-3">
                        <label class="form-label">OTP Code</label>
                        <input type="text" name="otp" class="form-control text-center fs-4 fw-bold" 
                               placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required
                               autocomplete="one-time-code">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Verify Email</button>
                </form>

                <div class="mt-3">
                    <a href="?resend=1&email=<?php echo urlencode($email); ?>" class="text-decoration-none small">Resend OTP</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
