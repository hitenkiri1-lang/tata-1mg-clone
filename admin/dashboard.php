<?php
/**
 * Admin Dashboard
 */
$page_title = "Dashboard";
include 'includes/header.php';

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total users
$users_query = "SELECT COUNT(*) as total FROM users WHERE status = 'active'";
$users_result = mysqli_query($conn, $users_query);
$stats['total_users'] = mysqli_fetch_assoc($users_result)['total'];

// Total orders
$orders_query = "SELECT COUNT(*) as total FROM orders";
$orders_result = mysqli_query($conn, $orders_query);
$stats['total_orders'] = mysqli_fetch_assoc($orders_result)['total'];

// Total revenue
$revenue_query = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'";
$revenue_result = mysqli_query($conn, $revenue_query);
$stats['total_revenue'] = mysqli_fetch_assoc($revenue_result)['total'] ?? 0;

// Total medicines
$medicines_query = "SELECT COUNT(*) as total FROM medicines WHERE status = 'active'";
$medicines_result = mysqli_query($conn, $medicines_query);
$stats['total_medicines'] = mysqli_fetch_assoc($medicines_result)['total'];

// Pending orders
$pending_query = "SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'";
$pending_result = mysqli_query($conn, $pending_query);
$stats['pending_orders'] = mysqli_fetch_assoc($pending_result)['total'];

// Low stock medicines
$low_stock_query = "SELECT COUNT(*) as total FROM medicines WHERE stock_quantity < 10 AND status = 'active'";
$low_stock_result = mysqli_query($conn, $low_stock_query);
$stats['low_stock'] = mysqli_fetch_assoc($low_stock_result)['total'];

// Recent orders
$recent_orders_query = "SELECT o.*, u.full_name, u.email 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.user_id 
                        ORDER BY o.order_date DESC 
                        LIMIT 10";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);
?>

<h2 class="mb-4">Dashboard</h2>

<!-- Statistics Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="dashboard-card">
            <i class="fas fa-users text-primary"></i>
            <h3 class="counter"><?php echo $stats['total_users']; ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <i class="fas fa-shopping-bag text-success"></i>
            <h3 class="counter"><?php echo $stats['total_orders']; ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <i class="fas fa-rupee-sign text-warning"></i>
            <h3><?php echo formatPrice($stats['total_revenue']); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <i class="fas fa-pills text-info"></i>
            <h3 class="counter"><?php echo $stats['total_medicines']; ?></h3>
            <p>Total Medicines</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <i class="fas fa-clock text-danger"></i>
            <h3 class="counter"><?php echo $stats['pending_orders']; ?></h3>
            <p>Pending Orders</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <i class="fas fa-exclamation-triangle text-warning"></i>
            <h3 class="counter"><?php echo $stats['low_stock']; ?></h3>
            <p>Low Stock Items</p>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Recent Orders</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['full_name']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td><?php echo formatDate($order['order_date']); ?></td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-custom status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
