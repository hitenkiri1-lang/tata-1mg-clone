<?php
/**
 * Order Success Page (COD)
 */
$page_title = "Order Placed";
include '../includes/header.php';

if (!isUserLoggedIn()) {
    redirect(SITE_URL . '/user/login.php');
}

$order_number = isset($_GET['order']) ? sanitize($_GET['order']) : '';

if (empty($order_number)) {
    redirect(SITE_URL . '/index.php');
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Fetch order details
$order_query = "SELECT * FROM orders WHERE order_number = '$order_number' AND user_id = '$user_id'";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    redirect(SITE_URL . '/index.php');
}

$order = mysqli_fetch_assoc($order_result);
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8" data-aos="zoom-in">
            <div class="text-center form-custom">
                <div class="mb-4">
                    <i class="fas fa-check-circle fa-5x text-success"></i>
                </div>
                
                <h2 class="text-success mb-3">Order Placed Successfully!</h2>
                <p class="lead">Thank you for your order</p>
                
                <div class="alert alert-info">
                    <h4>Order Number: <strong><?php echo htmlspecialchars($order['order_number']); ?></strong></h4>
                    <p class="mb-0">Total Amount: <strong><?php echo formatPrice($order['total_amount']); ?></strong></p>
                    <p class="mb-0">Payment Method: <strong>Cash on Delivery</strong></p>
                </div>
                
                <div class="my-4">
                    <p><i class="fas fa-info-circle text-primary"></i> Your order has been placed successfully. You will receive a confirmation email shortly.</p>
                    <p><i class="fas fa-truck text-success"></i> Your order will be delivered within 3-5 business days.</p>
                    <p><i class="fas fa-money-bill-wave text-warning"></i> Please keep the exact amount ready for cash on delivery.</p>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary btn-lg btn-custom">
                        <i class="fas fa-eye"></i> View Order Details
                    </a>
                    <a href="orders.php" class="btn btn-success btn-custom">
                        <i class="fas fa-box"></i> My Orders
                    </a>
                    <a href="<?php echo SITE_URL; ?>/medicines.php" class="btn btn-outline-success">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>
