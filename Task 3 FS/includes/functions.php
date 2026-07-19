<?php
// Reusable helper functions

/**
 * Get a default avatar URL if the user has no profile picture.
 */
function getAvatarUrl($profilePicture) {
    if ($profilePicture && file_exists($profilePicture)) {
        return htmlspecialchars($profilePicture);
    }
    return DEFAULT_AVATAR;
}

/**
 * Display a Bootstrap badge for a role name.
 */
function roleBadge($roleName) {
    $map = [
        'Admin' => 'danger',
        'User'  => 'primary',
    ];
    $class = $map[$roleName] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . htmlspecialchars($roleName) . '</span>';
}

/**
 * Redirect with a session flash message.
 */
function redirectWith($url, $message, $type = 'success') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    header('Location: ' . $url);
    exit;
}

/**
 * Display and clear flash messages.
 */
function renderFlash() {
    if (isset($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . $msg['type'] . ' alert-dismissible fade show">'
           . htmlspecialchars($msg['message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
           . '</div>';
    }
}

/**
 * Validate username format.
 */
function isValidUsername($username) {
    $len = strlen($username);
    return $len >= MIN_USERNAME_LENGTH && $len <= MAX_USERNAME_LENGTH && preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

/**
 * Generate a CSRF token and store in session.
 */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token.
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate session ID to prevent session fixation.
 */
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
