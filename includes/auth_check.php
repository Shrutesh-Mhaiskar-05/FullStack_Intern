<?php

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php', 'Please login to continue.', 'warning');
    }
    if (!isset($_SESSION['is_verified']) || !$_SESSION['is_verified']) {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return;
        }
        redirect('verify_otp.php?email=' . urlencode($_SESSION['email'] ?? ''), 'Please verify your email first.', 'warning');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('index.php', 'Access denied. Admin only.', 'danger');
    }
}
