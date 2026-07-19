<?php
$page_title = 'Dashboard';
require_once 'includes/admin_header.php';

// Stats
$total_users     = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_books     = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
$total_orders    = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$total_revenue   = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'];

// Active users (registered in last 30 days)
$active_users    = $conn->query("SELECT COUNT(*) as c FROM users WHERE created_at >= NOW() - INTERVAL 30 DAY")->fetch_assoc()['c'];

// Pending orders
$pending_orders  = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")->fetch_assoc()['c'];

// Low stock books (stock <= 5)
$low_stock       = $conn->query("SELECT COUNT(*) as c FROM books WHERE stock > 0 AND stock <= 5")->fetch_assoc()['c'];

// Orders per day (last 7 days)
$orders_chart = $conn->query("
    SELECT DATE(order_date) as day, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue
    FROM orders 
    WHERE order_date >= NOW() - INTERVAL 7 DAY
    GROUP BY DATE(order_date)
    ORDER BY day ASC
");
$chart_labels = []; $chart_counts = []; $chart_revenue = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('D', strtotime($date));
    $found = false;
    $orders_chart->data_seek(0);
    while ($row = $orders_chart->fetch_assoc()) {
        if ($row['day'] === $date) {
            $chart_counts[] = (int)$row['count'];
            $chart_revenue[] = (float)$row['revenue'];
            $found = true;
            break;
        }
    }
    if (!$found) { $chart_counts[] = 0; $chart_revenue[] = 0; }
}

// Category distribution
$cat_dist = $conn->query("SELECT c.name, COUNT(b.id) as count FROM categories c LEFT JOIN books b ON c.id = b.category_id GROUP BY c.id ORDER BY count DESC");

// User registrations per day (last 7 days)
$user_chart = $conn->query("
    SELECT DATE(created_at) as day, COUNT(*) as count
    FROM users 
    WHERE created_at >= NOW() - INTERVAL 7 DAY
    GROUP BY DATE(created_at)
    ORDER BY day ASC
");
$user_labels = []; $user_counts = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $user_labels[] = date('D', strtotime($date));
    $found = false;
    $user_chart->data_seek(0);
    while ($row = $user_chart->fetch_assoc()) {
        if ($row['day'] === $date) { $user_counts[] = (int)$row['count']; $found = true; break; }
    }
    if (!$found) $user_counts[] = 0;
}

// Order status distribution
$status_dist = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$status_labels = []; $status_counts = [];
while ($row = $status_dist->fetch_assoc()) {
    $status_labels[] = ucfirst($row['status']);
    $status_counts[] = (int)$row['count'];
}

// Recent orders
$recent_orders = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 5");

// Recent users
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h3>
    <small class="text-muted">Welcome, <?php echo h($_SESSION['username']); ?>!</small>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm bg-primary text-light h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title small opacity-75">Total Users</h6>
                        <h2 class="fw-bold mb-0"><?php echo $total_users; ?></h2>
                        <small class="opacity-75"><i class="bi bi-person-plus"></i> +<?php echo $active_users; ?> this month</small>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm bg-success text-light h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title small opacity-75">Total Books</h6>
                        <h2 class="fw-bold mb-0"><?php echo $total_books; ?></h2>
                        <small class="opacity-75"><i class="bi bi-exclamation-triangle"></i> <?php echo $low_stock; ?> low stock</small>
                    </div>
                    <i class="bi bi-book fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm bg-warning text-light h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title small opacity-75">Total Orders</h6>
                        <h2 class="fw-bold mb-0"><?php echo $total_orders; ?></h2>
                        <small class="opacity-75"><i class="bi bi-clock"></i> <?php echo $pending_orders; ?> pending</small>
                    </div>
                    <i class="bi bi-box-seam fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm bg-info text-light h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="card-title small opacity-75">Revenue</h6>
                        <h2 class="fw-bold mb-0"><?php echo formatPrice($total_revenue); ?></h2>
                        <small class="opacity-75"><i class="bi bi-graph-up"></i> Lifetime sales</small>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart-line me-2"></i>Orders & Revenue (Last 7 Days)</span>
            </div>
            <div class="card-body">
                <canvas id="ordersChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-pie-chart me-2"></i>Books by Category
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Second Row: User Registrations & Order Status -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="bi bi-people me-2"></i>New User Registrations (Last 7 Days)</div>
            <div class="card-body">
                <canvas id="userChart" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="bi bi-pie-chart me-2"></i>Order Status Distribution</div>
            <div class="card-body">
                <canvas id="statusChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders & Users -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Recent Orders</span>
                <a href="manage_orders.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo h($order['username']); ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td><span class="badge bg-<?php 
                                    echo $order['status'] === 'delivered' ? 'success' : 
                                        ($order['status'] === 'cancelled' ? 'danger' : 
                                        ($order['status'] === 'processing' ? 'info' : 'warning')); 
                                ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($recent_orders->num_rows === 0): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No orders yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-plus me-2"></i>Recent Users</span>
                <a href="manage_users.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                            <tr>
                                <td><div class="d-flex align-items-center"><img src="../uploads/<?php echo h($user['profile_pic']); ?>" class="rounded-circle me-2" style="width:30px;height:30px;object-fit:cover;" onerror="this.src='../assets/images/default.png'"><?php echo h($user['username']); ?></div></td>
                                <td><?php echo h($user['email']); ?></td>
                                <td><span class="badge bg-<?php echo $user['role_id'] == 1 ? 'danger' : 'secondary'; ?>"><?php echo $user['role_id'] == 1 ? 'Admin' : 'User'; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($recent_users->num_rows === 0): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No users yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Orders & Revenue Chart
    new Chart(document.getElementById('ordersChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [
                {
                    label: 'Orders',
                    data: <?php echo json_encode($chart_counts); ?>,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderColor: '#0d6efd',
                    borderWidth: 2,
                    borderRadius: 4,
                    order: 2
                },
                {
                    label: 'Revenue (₹)',
                    data: <?php echo json_encode($chart_revenue); ?>,
                    type: 'line',
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#198754',
                    pointRadius: 4,
                    borderWidth: 2,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, padding: 15 } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // User Registrations Chart
    new Chart(document.getElementById('userChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($user_labels); ?>,
            datasets: [{
                label: 'New Users',
                data: <?php echo json_encode($user_counts); ?>,
                borderColor: '#6f42c1',
                backgroundColor: 'rgba(111, 66, 193, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6f42c1',
                pointRadius: 4,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
        }
    });

    // Order Status Pie Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($status_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($status_counts); ?>,
                backgroundColor: ['#ffc107', '#0dcaf0', '#0d6efd', '#198754', '#dc3545'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 10 } } }
        }
    });

    // Category Pie Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: [<?php $cat_dist->data_seek(0); $first = true; while ($c = $cat_dist->fetch_assoc()) { echo ($first ? '' : ',') . '"' . h($c['name']) . '"'; $first = false; } ?>],
            datasets: [{
                data: [<?php $cat_dist->data_seek(0); $first = true; while ($c = $cat_dist->fetch_assoc()) { echo ($first ? '' : ',') . $c['count']; $first = false; } ?>],
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 11 } } }
            },
            cutout: '60%'
        }
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
