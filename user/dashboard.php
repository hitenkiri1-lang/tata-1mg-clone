<?php
/**
 * User Dashboard
 */
$page_title = "Dashboard";
include '../includes/header.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    redirect(SITE_URL . '/user/login.php');
}

$user = getUserById($_SESSION['user_id']);
$conn = getDBConnection();

// Get user statistics
$user_id = $_SESSION['user_id'];

// Total orders
$orders_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = '$user_id'";
$orders_result = mysqli_query($conn, $orders_query);
$total_orders = mysqli_fetch_assoc($orders_result)['total'];

// Total spent
$spent_query = "SELECT SUM(total_amount) as total FROM orders WHERE user_id = '$user_id' AND payment_status = 'completed'";
$spent_result = mysqli_query($conn, $spent_query);
$total_spent = mysqli_fetch_assoc($spent_result)['total'] ?? 0;

// Recent orders
$recent_orders_query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);
?>

<div class="container my-5">
    <h2 class="mb-4" data-aos="fade-up">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4" data-aos="fade-up">
            <div class="dashboard-card">
                <i class="fas fa-box text-primary"></i>
                <h3 class="counter"><?php echo $total_orders; ?></h3>
                <p>Total Orders</p>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="dashboard-card">
                <i class="fas fa-rupee-sign text-success"></i>
                <h3><?php echo formatPrice($total_spent); ?></h3>
                <p>Total Spent</p>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="dashboard-card">
                <i class="fas fa-shopping-cart text-info"></i>
                <h3 class="counter"><?php echo getCartCount($user_id); ?></h3>
                <p>Cart Items</p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <a href="<?php echo SITE_URL; ?>/medicines.php" class="btn btn-success w-100 btn-custom">
                <i class="fas fa-pills"></i> Browse Medicines
            </a>
        </div>
        <div class="col-md-3">
            <a href="cart.php" class="btn btn-primary w-100 btn-custom">
                <i class="fas fa-shopping-cart"></i> View Cart
            </a>
        </div>
        <div class="col-md-3">
            <a href="orders.php" class="btn btn-info w-100 btn-custom">
                <i class="fas fa-box"></i> My Orders
            </a>
        </div>
        <div class="col-md-3">
            <a href="profile.php" class="btn btn-warning w-100 btn-custom">
                <i class="fas fa-user"></i> Edit Profile
            </a>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="card shadow-sm" data-aos="fade-up">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-history"></i> Recent Orders</h5>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($recent_orders_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td><?php echo formatDate($order['order_date']); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <span class="badge badge-custom status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No orders yet. <a href="<?php echo SITE_URL; ?>/medicines.php">Start shopping!</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>
