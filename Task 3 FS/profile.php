<?php
$pageTitle = 'My Profile';
$rootPath = '';
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/auth_check.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$conn = getDbConnection();
$userId = (int)$_SESSION['user_id'];
$error = $success = '';
$activeTab = $_GET['tab'] ?? 'info';

// Fetch user
$stmt = $conn->prepare("SELECT id, username, email, profile_picture, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ---- Handle profile info update ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $token    = $_POST['csrf_token'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if (!verifyCsrfToken($token)) {
        $error = 'Invalid form submission.';
    } elseif (empty($username) || empty($email)) {
        $error = 'Username and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!isValidUsername($username)) {
        $error = 'Username must be 3-50 characters (letters, numbers, underscores).';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check->bind_param("ssi", $username, $email, $userId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Username or email already taken.';
        } else {
            $update = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $update->bind_param("ssi", $username, $email, $userId);
            if ($update->execute()) {
                $_SESSION['username'] = $username;
                $_SESSION['email']    = $email;
                $user['username']     = $username;
                $user['email']        = $email;
                $success = 'Profile updated successfully.';
            } else {
                $error = 'Failed to update profile.';
            }
            $update->close();
        }
        $check->close();
    }
}

// ---- Handle password change ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $token    = $_POST['csrf_token'] ?? '';
    $current  = $_POST['current_password'] ?? '';
    $newPwd   = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!verifyCsrfToken($token)) {
        $error = 'Invalid form submission.';
    } elseif (empty($current) || empty($newPwd) || empty($confirm)) {
        $error = 'All password fields are required.';
    } elseif (strlen($newPwd) < MIN_PASSWORD_LENGTH) {
        $error = 'New password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
    } elseif ($newPwd !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!password_verify($current, $row['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $hashed = password_hash($newPwd, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed, $userId);
            if ($update->execute()) {
                $success = 'Password changed successfully.';
            } else {
                $error = 'Failed to change password.';
            }
            $update->close();
        }
    }
}

// ---- Handle profile picture upload ----
if (isset($_POST['upload_picture']) && isset($_FILES['profile_picture'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $error = 'Invalid form submission.';
    } else {
        $file = $_FILES['profile_picture'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload error. Please try again.';
        } elseif (!in_array($file['type'], UPLOAD_ALLOWED_TYPES)) {
            $error = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } elseif ($file['size'] > UPLOAD_MAX_SIZE) {
            $error = 'File size must be under 2MB.';
        } else {
            if ($user['profile_picture']) {
                $oldPath = __DIR__ . '/' . $user['profile_picture'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $error = 'Invalid file extension.';
            } else {
                $newName = 'profile_' . $userId . '_' . time() . '.' . $ext;
                $destPath = UPLOAD_DIR . $newName;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $update = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                    $update->bind_param("si", $destPath, $userId);
                    if ($update->execute()) {
                        $_SESSION['profile_picture'] = $destPath;
                        $user['profile_picture'] = $destPath;
                        $success = 'Profile picture updated.';
                    } else {
                        $error = 'Database update failed.';
                    }
                    $update->close();
                } else {
                    $error = 'Failed to save file.';
                }
            }
        }
    }
}

// ---- Handle picture removal ----
if (isset($_POST['remove_picture'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $error = 'Invalid form submission.';
    } else {
        if ($user['profile_picture']) {
            $filePath = __DIR__ . '/' . $user['profile_picture'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $update = $conn->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
        $update->bind_param("i", $userId);
        $update->execute();
        $update->close();

        $_SESSION['profile_picture'] = null;
        $user['profile_picture'] = null;
        $success = 'Profile picture removed.';
    }
}
?>

<?php renderFlash(); ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<h3 class="mb-4"><i class="bi bi-person-gear me-2"></i>My Profile</h3>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'info' ? 'active' : '' ?>" href="?tab=info">
            <i class="bi bi-info-circle me-1"></i>Profile Info
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'password' ? 'active' : '' ?>" href="?tab=password">
            <i class="bi bi-key me-1"></i>Change Password
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'picture' ? 'active' : '' ?>" href="?tab=picture">
            <i class="bi bi-camera me-1"></i>Profile Picture
        </a>
    </li>
</ul>

<?php if ($activeTab === 'info'): ?>
<!-- ========== PROFILE INFO TAB ========== -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="?tab=info" data-spinner>
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
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
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['role_name']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Member Since</label>
                        <input type="text" class="form-control" value="<?= date('F d, Y', strtotime($user['created_at'])) ?>" disabled>
                    </div>
                    <button type="submit" name="update_info" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php elseif ($activeTab === 'password'): ?>
<!-- ========== CHANGE PASSWORD TAB ========== -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="?tab=password" data-spinner>
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="current_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="new_password" class="form-control" required minlength="<?= MIN_PASSWORD_LENGTH ?>">
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="new_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="confirm_password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-warning"><i class="bi bi-key me-1"></i>Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php elseif ($activeTab === 'picture'): ?>
<!-- ========== PROFILE PICTURE TAB ========== -->
<div class="row">
    <div class="col-lg-4 text-center">
        <div class="card">
            <div class="card-body">
                <div class="avatar-wrapper mb-3">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile" class="avatar-img" id="imagePreview">
                    <?php else: ?>
                        <img src="<?= DEFAULT_AVATAR ?>" alt="Default" class="avatar-img" id="imagePreview">
                    <?php endif; ?>
                </div>

                <form method="POST" enctype="multipart/form-data" class="mt-3">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="mb-2">
                        <input type="file" name="profile_picture" id="profilePictureInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    <button type="submit" name="upload_picture" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-upload me-1"></i>Upload Picture
                    </button>
                </form>

                <?php if ($user['profile_picture']): ?>
                    <form method="POST" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <button type="submit" name="remove_picture" class="btn btn-outline-danger btn-sm w-100"
                                onclick="return confirm('Remove profile picture?')">
                            <i class="bi bi-trash me-1"></i>Remove Picture
                        </button>
                    </form>
                <?php endif; ?>

                <small class="text-muted d-block mt-2">JPG, PNG, GIF, WebP &bull; Max 2MB</small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
