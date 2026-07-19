<?php
$page_title = 'Shopping Cart';
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $_SESSION['user_id']);
    $stmt->execute();
    redirect('cart.php', 'Cart updated.', 'success');
}

// Handle remove item
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
    $stmt->execute();
    redirect('cart.php', 'Item removed from cart.', 'info');
}

$cart_items = getCartItems($conn, $_SESSION['user_id']);
$cart_total = getCartTotal($conn, $_SESSION['user_id']);

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold">Shopping Cart</h3>
    </div>
</div>

<?php if ($cart_items->num_rows > 0): ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php while ($item = $cart_items->fetch_assoc()): 
                    $unit_price = getDiscountedPrice($item['price'], $item['discount'] ?? 0);
                ?>
                <div class="row align-items-center border-bottom pb-3 mb-3">
                    <div class="col-md-2 col-4">
                        <img src="assets/images/<?php echo h($item['image']); ?>" 
                             class="img-fluid rounded" 
                             alt="<?php echo h($item['title']); ?>"
                             onerror="this.src='assets/images/default-book.png'">
                    </div>
                    <div class="col-md-4 col-8">
                        <h6 class="mb-1"><?php echo h($item['title']); ?></h6>
                        <?php if ($item['discount'] > 0): ?>
                        <p class="mb-0">
                            <span class="text-muted text-decoration-line-through small me-1"><?php echo formatPrice($item['price']); ?></span>
                            <span class="text-primary fw-bold"><?php echo formatPrice($unit_price); ?></span>
                            <span class="badge bg-danger ms-1" style="font-size:0.65rem;">-<?php echo number_format($item['discount'], 0); ?>%</span>
                        </p>
                        <?php else: ?>
                        <p class="text-primary fw-bold mb-0"><?php echo formatPrice($item['price']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3 col-6 mt-2 mt-md-0">
                        <form method="POST" action="" class="d-flex align-items-center">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantity" class="form-control form-control-sm me-2" 
                                   value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" style="width: 70px;">
                            <button type="submit" name="update_qty" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-2 col-3 mt-2 mt-md-0 text-end">
                        <p class="fw-bold mb-0"><?php echo formatPrice($unit_price * $item['quantity']); ?></p>
                    </div>
                    <div class="col-md-1 col-3 mt-2 mt-md-0 text-end">
                        <a href="cart.php?remove=<?php echo $item['id']; ?>" 
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Remove this item from cart?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Order Summary</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span class="fw-bold"><?php echo formatPrice($cart_total); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping</span>
                    <span class="text-success">Free</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold fs-5">Total</span>
                    <span class="fw-bold fs-5 text-primary"><?php echo formatPrice($cart_total); ?></span>
                </div>
                <a href="checkout.php" class="btn btn-primary w-100 py-2">Proceed to Checkout</a>
                <a href="shop.php" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="text-center py-5">
    <i class="bi bi-cart-x fs-1 text-muted"></i>
    <h5 class="mt-3">Your cart is empty</h5>
    <p class="text-muted">Browse our collection and add items to your cart.</p>
    <a href="shop.php" class="btn btn-primary">Start Shopping</a>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
