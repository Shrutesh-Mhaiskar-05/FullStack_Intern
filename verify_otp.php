<?php
$page_title = 'Verify Email';
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$email = $_GET['email'] ?? $_SESSION['otp_email'] ?? '';
if (empty($email)) {
    redirect('register.php', 'Please register first.', 'warning');
}

$errors = [];
$success = '';

// Resend OTP
if (isset($_GET['resend'])) {
    sendOtpEmail($conn, $email);
    $success = 'A new OTP has been sent.';
}

// Verify OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    if (empty($otp) || strlen($otp) !== 6) {
        $errors[] = 'Please enter a valid 6-digit OTP.';
    } else {
        if (verifyOtp($conn, $email, $otp)) {
            unset($_SESSION['otp_demo'], $_SESSION['otp_email']);
            redirect('login.php', 'Email verified successfully! You can now login.', 'success');
        } else {
            $errors[] = 'Invalid or expired OTP. Please try again.';
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

                <!-- Demo OTP display (remove in production) -->
                <?php if (isset($_SESSION['otp_demo']) && $_SESSION['otp_email'] === $email): ?>
                <div class="alert alert-info">
                    <small>📧 Demo Mode — Your OTP: <strong><?php echo h($_SESSION['otp_demo']); ?></strong></small>
                </div>
                <?php endif; ?>

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
