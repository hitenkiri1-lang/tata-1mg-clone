<?php
/**
 * Admin - Order Details
 */
$page_title = "Order Details";
include 'includes/header.php';

if (!isset($_GET['id'])) {
    redirect(SITE_URL . '/admin/orders.php');
}

$order_id = mysqli_real_escape_string(getDBConnection(), $_GET['id']);
$conn = getDBConnection();

// Fetch order details
$order_query = "SELECT o.*, u.full_name, u.email, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.user_id 
                WHERE o.order_id = '$order_id'";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    redirect(SITE_URL . '/admin/orders.php');
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

<div class="mb-4">
    <a href="orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Orders</a>
</div>

<h2 class="mb-4"><i class="fas fa-file-invoice"></i> Order Details</h2>

<div class="row">
    <!-- Order Information -->
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Order Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                        <p><strong>Order Date:</strong> <?php echo formatDateTime($order['order_date']); ?></p>
                        <p><strong>Order Status:</strong> 
                            <span class="badge badge-custom status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Payment Method:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
                        <p><strong>Payment Status:</strong> 
                            <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </p>
                        <p><strong>Total Amount:</strong> <strong class="text-success"><?php echo formatPrice($order['total_amount']); ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Customer Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
            </div>
        </div>
        
        <!-- Shipping Address -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Shipping Address</h5>
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                <p><?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state']); ?> - <?php echo htmlspecialchars($order['shipping_pincode']); ?></p>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="card shadow-sm">
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
                                <td><strong class="text-success"><?php echo formatPrice($order['total_amount']); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Payment Details -->
        <?php if ($payment): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Payment Details</h5>
            </div>
            <div class="card-body">
                <p><strong>Payment Method:</strong> <?php echo strtoupper($payment['payment_method']); ?></p>
                <?php if ($payment['transaction_id']): ?>
                    <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                <?php endif; ?>
                <p><strong>Amount:</strong> <?php echo formatPrice($payment['amount']); ?></p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-<?php echo $payment['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst($payment['payment_status']); ?>
                    </span>
                </p>
                <p><strong>Date:</strong> <?php echo formatDateTime($payment['payment_date']); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Actions -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="orders.php">
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Update Status</label>
                        <select name="order_status" class="form-select">
                            <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $order['order_status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
