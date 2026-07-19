<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/auth_check.php';
requireRole('Admin');
require_once '../includes/functions.php';

$conn = getDbConnection();
$userId = (int)($_GET['id'] ?? 0);

if ($userId <= 0) {
    redirectWith('dashboard.php', 'Invalid user ID.', 'danger');
}

// Prevent self-deletion
if ($userId === (int)$_SESSION['user_id']) {
    redirectWith('dashboard.php', 'You cannot delete your own account.', 'danger');
}

// Fetch profile picture to clean up file
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    redirectWith('dashboard.php', 'User not found.', 'danger');
}

// Delete profile picture file if exists
if ($user['profile_picture']) {
    $filePath = __DIR__ . '/../' . $user['profile_picture'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Delete user
$delete = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete->bind_param("i", $userId);

if ($delete->execute() && $delete->affected_rows > 0) {
    redirectWith('dashboard.php', 'User deleted successfully.');
} else {
    redirectWith('dashboard.php', 'Failed to delete user.', 'danger');
}
$delete->close();
