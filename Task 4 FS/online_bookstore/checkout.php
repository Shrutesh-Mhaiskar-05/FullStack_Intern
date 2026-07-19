<?php
$page_title = 'Checkout';
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$cart_items = getCartItems($conn, $_SESSION['user_id']);
$cart_total = getCartTotal($conn, $_SESSION['user_id']);

if ($cart_items->num_rows === 0) {
    redirect('cart.php', 'Your cart is empty.', 'warning');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cod';

    if (empty($address)) {
        $errors[] = 'Shipping address is required.';
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $_SESSION['user_id'], $cart_total, $address, $payment_method);
            $stmt->execute();
            $order_id = $conn->insert_id;

            // Add order items & update stock
            $cart_items->data_seek(0);
            while ($item = $cart_items->fetch_assoc()) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $item['book_id'], $item['quantity'], $item['price']);
                $stmt->execute();

                // Update stock
                $stmt = $conn->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantity'], $item['book_id']);
                $stmt->execute();
            }

            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();

            $conn->commit();
            redirect('profile.php?tab=orders', 'Order placed successfully!', 'success');
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Checkout failed. Please try again.';
        }
    }
}

// Get user data for default address
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold">Checkout</h3>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Shipping Information</h5>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo h($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo h($user['username']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo h($user['email']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shipping Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="3" required><?php echo h($user['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cod">Cash on Delivery</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-check-lg"></i> Place Order
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                <?php $cart_items->data_seek(0); ?>
                <?php while ($item = $cart_items->fetch_assoc()): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0 small"><?php echo h($item['title']); ?></h6>
                        <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                    </div>
                    <span class="fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                </div>
                <?php endwhile; ?>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span class="fw-bold"><?php echo formatPrice($cart_total); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping</span>
                    <span class="text-success">Free</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">Total</span>
                    <span class="fw-bold fs-5 text-primary"><?php echo formatPrice($cart_total); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
