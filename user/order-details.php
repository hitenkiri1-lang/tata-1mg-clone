<?php
/**
 * User Order Details
 */
$page_title = "Order Details";
include '../includes/header.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    redirect(SITE_URL . '/user/login.php');
}

if (!isset($_GET['id'])) {
    redirect(SITE_URL . '/user/orders.php');
}

$user_id = $_SESSION['user_id'];
$order_id = mysqli_real_escape_string(getDBConnection(), $_GET['id']);
$conn = getDBConnection();

// Fetch order details
$order_query = "SELECT * FROM orders WHERE order_id = '$order_id' AND user_id = '$user_id'";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    redirect(SITE_URL . '/user/orders.php');
}

$order = mysqli_fetch_assoc($order_result);

// Fetch order items
$items_query = "SELECT oi.*, m.medicine_name, m.manufacturer 
                FROM order_items oi 
                JOIN medicines m ON oi.medicine_id = m.medicine_id 
                WHERE oi.order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_query);

// Fetch payment details
$payment_query = "SELECT * FROM payments WHERE order_id = '$order_id'";
$payment_result = mysqli_query($conn, $payment_query);
$payment = mysqli_fetch_assoc($payment_result);
?>

<div class="container my-5">
    <div class="mb-4">
        <a href="orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Orders</a>
    </div>
    
    <h2 class="mb-4" data-aos="fade-up"><i class="fas fa-file-invoice"></i> Order Details</h2>
    
    <div class="row">
        <!-- Order Information -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4" data-aos="fade-right">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order Number:</strong><br><?php echo htmlspecialchars($order['order_number']); ?></p>
                            <p><strong>Order Date:</strong><br><?php echo formatDateTime($order['order_date']); ?></p>
                            <p><strong>Order Status:</strong><br>
                                <span class="badge badge-custom status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Payment Method:</strong><br><?php echo strtoupper($order['payment_method']); ?></p>
                            <p><strong>Payment Status:</strong><br>
                                <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                            <p><strong>Total Amount:</strong><br><strong class="text-success fs-4"><?php echo formatPrice($order['total_amount']); ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Address -->
            <div class="card shadow-sm mb-4" data-aos="fade-right">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    <p><?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state']); ?> - <?php echo htmlspecialchars($order['shipping_pincode']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card shadow-sm" data-aos="fade-right">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Medicine</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['medicine_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($item['manufacturer']); ?></small>
                                        </td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><strong><?php echo formatPrice($item['subtotal']); ?></strong></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><strong class="text-success fs-5"><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Order Timeline -->
            <div class="card shadow-sm mb-4" data-aos="fade-left">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item <?php echo in_array($order['order_status'], ['pending', 'confirmed', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i>
                            <p><strong>Order Placed</strong><br>
                            <small><?php echo formatDateTime($order['order_date']); ?></small></p>
                        </div>
                        
                        <div class="timeline-item <?php echo in_array($order['order_status'], ['confirmed', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i>
                            <p><strong>Order Confirmed</strong></p>
                        </div>
                        
                        <div class="timeline-item <?php echo in_array($order['order_status'], ['shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-truck"></i>
                            <p><strong>Shipped</strong></p>
                        </div>
                        
                        <div class="timeline-item <?php echo $order['order_status'] == 'delivered' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <p><strong>Delivered</strong></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Details -->
            <?php if ($payment): ?>
            <div class="card shadow-sm" data-aos="fade-left">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Payment Method:</strong><br><?php echo strtoupper($payment['payment_method']); ?></p>
                    <?php if ($payment['transaction_id']): ?>
                        <p><strong>Transaction ID:</strong><br><?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                    <?php endif; ?>
                    <p><strong>Amount:</strong><br><?php echo formatPrice($payment['amount']); ?></p>
                    <p><strong>Status:</strong><br>
                        <span class="badge bg-<?php echo $payment['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($payment['payment_status']); ?>
                        </span>
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    opacity: 0.5;
}

.timeline-item.active {
    opacity: 1;
}

.timeline-item i {
    position: absolute;
    left: -30px;
    top: 0;
    color: #ccc;
}

.timeline-item.active i {
    color: #28a745;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -23px;
    top: 20px;
    width: 2px;
    height: calc(100% - 10px);
    background: #ccc;
}

.timeline-item:last-child:before {
    display: none;
}
</style>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>
