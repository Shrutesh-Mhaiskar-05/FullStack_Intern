<?php
$page_title = 'My Profile';
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'profile';
$errors = [];
$success = '';

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($username)) $errors[] = 'Username is required.';

    if (empty($errors)) {
        // Check username uniqueness
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Username already taken.';
        } else {
            // Handle profile picture upload
            $profile_pic = $user['profile_pic'];
            if (!empty($_FILES['profile_pic']['name'])) {
                $uploaded = uploadImage($_FILES['profile_pic'], 'uploads/', 'default.png');
                if ($uploaded !== 'default.png') {
                    if ($user['profile_pic'] !== 'default.png' && file_exists('uploads/' . $user['profile_pic'])) {
                        unlink('uploads/' . $user['profile_pic']);
                    }
                    $profile_pic = $uploaded;
                }
            }

            $stmt = $conn->prepare("UPDATE users SET username = ?, phone = ?, address = ?, profile_pic = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $username, $phone, $address, $profile_pic, $user_id);
            $stmt->execute();

            $_SESSION['username'] = $username;
            $_SESSION['profile_pic'] = $profile_pic;
            $success = 'Profile updated successfully!';
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
        $errors[] = 'All password fields are required.';
    } elseif ($new !== $confirm) {
        $errors[] = 'New passwords do not match.';
    } elseif (strlen($new) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } elseif (!password_verify($current, $user['password'])) {
        $errors[] = 'Current password is incorrect.';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        $stmt->execute();
        $success = 'Password changed successfully!';
    }
}

// Get orders
$orders = getUserOrders($conn, $user_id);

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold">My Profile</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <img src="uploads/<?php echo h($user['profile_pic']); ?>" 
                     class="rounded-circle img-thumbnail mb-3" 
                     style="width: 120px; height: 120px; object-fit: cover;"
                     alt="Profile" 
                     onerror="this.src='assets/images/default.png'">
                <h6><?php echo h($user['username']); ?></h6>
                <p class="text-muted small"><?php echo h($user['email']); ?></p>
                <hr>
                <div class="list-group list-group-flush">
                    <a href="profile.php?tab=profile" class="list-group-item list-group-item-action <?php echo $tab === 'profile' ? 'active' : ''; ?>">
                        <i class="bi bi-person"></i> Profile
                    </a>
                    <a href="profile.php?tab=orders" class="list-group-item list-group-item-action <?php echo $tab === 'orders' ? 'active' : ''; ?>">
                        <i class="bi bi-box"></i> My Orders
                    </a>
                    <a href="profile.php?tab=password" class="list-group-item list-group-item-action <?php echo $tab === 'password' ? 'active' : ''; ?>">
                        <i class="bi bi-shield-lock"></i> Password
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo h($success); ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo h($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($tab === 'orders'): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">My Orders</h5>
                <?php if ($orders->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $order['status'] === 'delivered' ? 'success' : 
                                            ($order['status'] === 'cancelled' ? 'danger' : 
                                            ($order['status'] === 'processing' ? 'info' : 'warning')); 
                                    ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                        View
                                    </button>
                                </td>
                            </tr>

                            <!-- Order Modal -->
                            <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Order #<?php echo $order['id']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Date:</strong> <?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></p>
                                            <p><strong>Shipping Address:</strong> <?php echo h($order['shipping_address']); ?></p>
                                            <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                            <p><strong>Status:</strong> <span class="badge bg-<?php 
                                                echo $order['status'] === 'delivered' ? 'success' : 
                                                    ($order['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                            ?>"><?php echo ucfirst($order['status']); ?></span></p>
                                            <hr>
                                            <h6>Items</h6>
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr><th>Book</th><th>Qty</th><th>Price</th></tr>
                                                </thead>
                                                <tbody>
                                                    <?php $items = getOrderDetails($conn, $order['id']); ?>
                                                    <?php while ($item = $items->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo h($item['title']); ?></td>
                                                        <td><?php echo $item['quantity']; ?></td>
                                                        <td><?php echo formatPrice($item['price']); ?></td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="fw-bold">
                                                        <td colspan="2">Total</td>
                                                        <td><?php echo formatPrice($order['total_amount']); ?></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No orders yet.</p>
                <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                <?php endif; ?>
            </div>
        </div>

        <?php elseif ($tab === 'password'): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Change Password</h5>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>

        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Edit Profile</h5>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" name="profile_pic" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo h($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo h($user['email']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo h($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"><?php echo h($user['address'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
