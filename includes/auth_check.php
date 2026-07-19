<?php
/**
 * Authentication check middleware
 */

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php', 'Please login to continue.', 'warning');
    }
}

// Require admin role
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('index.php', 'Access denied. Admin only.', 'danger');
    }
}
