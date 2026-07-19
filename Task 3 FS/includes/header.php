<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $rootPath ?>assets/css/style.css">
</head>
<body>

<?php if (!empty($authLayout)): ?>
    <div class="auth-wrapper">
        <div class="auth-card card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock auth-icon"></i>
                    <h3><?= htmlspecialchars($pageTitle ?? '') ?></h3>
                </div>
<?php else: ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="<?= $rootPath ?>index.php">
                <i class="bi bi-people-fill me-2"></i>User Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <img src="<?= $rootPath . getAvatarUrl($_SESSION['profile_picture'] ?? null) ?>"
                                     alt="" width="32" height="32" class="rounded-circle me-2"
                                     style="object-fit:cover;">
                                <span><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
                                <span class="badge bg-<?= ($_SESSION['role_name'] ?? 'User') === 'Admin' ? 'danger' : 'primary' ?> ms-2">
                                    <?= htmlspecialchars($_SESSION['role_name'] ?? '') ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= $rootPath ?>profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <?php if (($_SESSION['role_name'] ?? '') === 'Admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= $rootPath ?>admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= $rootPath ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $rootPath ?>login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $rootPath ?>register.php"><i class="bi bi-person-plus me-1"></i>Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="wrapper">
        <?php if (isset($_SESSION['user_id']) && empty($noSidebar)): ?>
        <aside class="sidebar" id="sidebar">
            <div class="p-3 text-center border-bottom border-secondary">
                <img src="<?= $rootPath . getAvatarUrl($_SESSION['profile_picture'] ?? null) ?>"
                     alt="" width="64" height="64" class="rounded-circle mb-2"
                     style="object-fit:cover;border:2px solid rgba(255,255,255,0.2);">
                <div class="fw-semibold"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></div>
                <small class="text-secondary"><?= htmlspecialchars($_SESSION['role_name'] ?? '') ?></small>
            </div>
            <nav class="mt-2">
                <a href="<?= $rootPath ?>index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
                <a href="<?= $rootPath ?>profile.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
                    <i class="bi bi-person"></i> My Profile
                </a>
                <?php if (($_SESSION['role_name'] ?? '') === 'Admin'): ?>
                <div class="px-3 pt-3 pb-1"><small class="text-uppercase text-secondary">Admin</small></div>
                <a href="<?= $rootPath ?>admin/dashboard.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="<?= $rootPath ?>admin/add_user.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'add_user.php' ? 'active' : '' ?>">
                    <i class="bi bi-person-plus"></i> Add User
                </a>
                <?php endif; ?>
            </nav>
        </aside>
        <?php endif; ?>
        <main class="main-content">
<?php endif; ?>
