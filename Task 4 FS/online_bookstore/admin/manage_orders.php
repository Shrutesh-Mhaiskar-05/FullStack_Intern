<?php
$page_title = 'Manage Orders';
require_once 'includes/admin_header.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    redirect('manage_orders.php', 'Order status updated.', 'success');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    redirect('manage_orders.php', 'Order deleted.', 'success');
}

$status_filter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
$types = '';

if (!empty($status_filter)) {
    $where = "WHERE o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$count_sql = "SELECT COUNT(*) as c FROM orders o $where";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['c'];

$sql = "SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id $where ORDER BY o.order_date DESC LIMIT ? OFFSET ?";
$params[] = $limit; $params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();
$total_pages = ceil($total / $limit);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Manage Orders</h3>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <?php if (!empty($status_filter)): ?>
            <div class="col-md-2">
                <a href="manage_orders.php" class="btn btn-outline-secondary">Clear</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): 
                        $items = $conn->prepare("SELECT COUNT(*) as c, SUM(quantity) as q FROM order_items WHERE order_id = ?");
                        $items->bind_param("i", $order['id']);
                        $items->execute();
                        $item_data = $items->get_result()->fetch_assoc();
                    ?>
                    <tr>
                        <td class="fw-bold">#<?php echo $order['id']; ?></td>
                        <td>
                            <?php echo h($order['username']); ?>
                            <br><small class="text-muted"><?php echo h($order['email']); ?></small>
                        </td>
                        <td><?php echo $item_data['q']; ?> items</td>
                        <td class="fw-bold"><?php echo formatPrice($order['total_amount']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo strtoupper($order['payment_method']); ?></span></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $order['status'] === 'delivered' ? 'success' : 
                                    ($order['status'] === 'cancelled' ? 'danger' : 
                                    ($order['status'] === 'processing' ? 'info' : 
                                    ($order['status'] === 'shipped' ? 'primary' : 'warning'))); 
                            ?>"><?php echo ucfirst($order['status']); ?></span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="?delete=<?php echo $order['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this order?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <!-- Order Modal -->
                    <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Order #<?php echo $order['id']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p><strong>Customer:</strong> <?php echo h($order['username']); ?></p>
                                            <p><strong>Email:</strong> <?php echo h($order['email']); ?></p>
                                            <p><strong>Date:</strong> <?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Payment:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
                                            <p><strong>Shipping:</strong> <?php echo h($order['shipping_address']); ?></p>
                                            <p><strong>Status:</strong> 
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </p>
                                        </div>
                                    </div>
                                    <h6>Order Items</h6>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr><th>Book</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php $order_items = getOrderDetails($conn, $order['id']); ?>
                                            <?php while ($item = $order_items->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo h($item['title']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo formatPrice($item['price']); ?></td>
                                                <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold">
                                                <td colspan="3">Total</td>
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
    </div>
</div>

<?php if ($total_pages > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
