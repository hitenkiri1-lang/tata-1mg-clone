<?php
/**
 * User Orders History
 */
$page_title = "My Orders";
include '../includes/header.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    redirect(SITE_URL . '/user/login.php');
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Fetch orders
$orders_query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<div class="container my-5">
    <h2 class="mb-4" data-aos="fade-up"><i class="fas fa-box"></i> My Orders</h2>
    
    <?php if (mysqli_num_rows($orders_result) > 0): ?>
        <div class="row g-4">
            <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                <div class="col-12" data-aos="fade-up">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <h5 class="mb-2">Order #<?php echo htmlspecialchars($order['order_number']); ?></h5>
                                    <p class="text-muted mb-0">
                                        <small><i class="fas fa-calendar"></i> <?php echo formatDate($order['order_date']); ?></small>
                                    </p>
                                </div>
                                
                                <div class="col-md-2">
                                    <p class="mb-0"><strong>Amount</strong></p>
                                    <h5 class="text-success mb-0"><?php echo formatPrice($order['total_amount']); ?></h5>
                                </div>
                                
                                <div class="col-md-2">
                                    <p class="mb-0"><strong>Payment</strong></p>
                                    <span class="badge bg-<?php echo $order['payment_method'] == 'cod' ? 'warning' : 'info'; ?>">
                                        <?php echo strtoupper($order['payment_method']); ?>
                                    </span>
                                </div>
                                
                                <div class="col-md-2">
                                    <p class="mb-0"><strong>Status</strong></p>
                                    <span class="badge badge-custom status-<?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </div>
                                
                                <div class="col-md-3 text-end">
                                    <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5" data-aos="fade-up">
            <i class="fas fa-box-open fa-5x text-muted mb-4"></i>
            <h3>No orders yet</h3>
            <p class="text-muted">Start shopping to see your orders here!</p>
            <a href="<?php echo SITE_URL; ?>/medicines.php" class="btn btn-success btn-lg btn-custom mt-3">
                <i class="fas fa-pills"></i> Browse Medicines
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>
