<?php
/**
 * Checkout Page
 */
$page_title = "Checkout";
include '../includes/header.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    redirect(SITE_URL . '/user/login.php');
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);
$conn = getDBConnection();

// Fetch cart items
$cart_query = "SELECT c.*, m.medicine_name, m.price, m.discount_price, m.stock_quantity
               FROM cart c
               JOIN medicines m ON c.medicine_id = m.medicine_id
               WHERE c.user_id = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);

// Check if cart is empty
if (mysqli_num_rows($cart_result) == 0) {
    redirect(SITE_URL . '/user/cart.php');
}

// Calculate totals
$subtotal = 0;
$cart_items = [];
while ($item = mysqli_fetch_assoc($cart_result)) {
    $price = $item['discount_price'] ?? $item['price'];
    $item['item_total'] = $price * $item['quantity'];
    $subtotal += $item['item_total'];
    $cart_items[] = $item;
}

$shipping = ($subtotal >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_CHARGE;
$total = $subtotal + $shipping;

// Process order
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $pincode = sanitize($_POST['pincode']);
    $phone = sanitize($_POST['phone']);
    $payment_method = sanitize($_POST['payment_method']);
    
    if (empty($address) || empty($city) || empty($state) || empty($pincode) || empty($phone)) {
        $error = 'Please fill all shipping details.';
    } else {
        // Generate order number
        $order_number = generateOrderNumber();
        
        // Insert order
        $insert_order = "INSERT INTO orders (user_id, order_number, total_amount, payment_method, payment_status, order_status, shipping_address, shipping_city, shipping_state, shipping_pincode, shipping_phone) 
                        VALUES ('$user_id', '$order_number', '$total', '$payment_method', 'pending', 'pending', '$address', '$city', '$state', '$pincode', '$phone')";
        
        if (mysqli_query($conn, $insert_order)) {
            $order_id = mysqli_insert_id($conn);
            
            // Insert order items
            foreach ($cart_items as $item) {
                $medicine_id = $item['medicine_id'];
                $quantity = $item['quantity'];
                $price = $item['discount_price'] ?? $item['price'];
                $subtotal_item = $item['item_total'];
                
                $insert_item = "INSERT INTO order_items (order_id, medicine_id, quantity, price, subtotal) 
                               VALUES ('$order_id', '$medicine_id', '$quantity', '$price', '$subtotal_item')";
                mysqli_query($conn, $insert_item);
                
                // Update stock
                $update_stock = "UPDATE medicines SET stock_quantity = stock_quantity - $quantity WHERE medicine_id = '$medicine_id'";
                mysqli_query($conn, $update_stock);
            }
            
            // Insert payment record
            $transaction_id = ($payment_method == 'online') ? generateTransactionId() : NULL;
            $payment_status = ($payment_method == 'online') ? 'completed' : 'pending';
            
            $insert_payment = "INSERT INTO payments (order_id, transaction_id, payment_method, amount, payment_status) 
                              VALUES ('$order_id', '$transaction_id', '$payment_method', '$total', '$payment_status')";
            mysqli_query($conn, $insert_payment);
            
            // Update order payment status if online
            if ($payment_method == 'online') {
                $update_order = "UPDATE orders SET payment_status = 'completed', order_status = 'confirmed' WHERE order_id = '$order_id'";
                mysqli_query($conn, $update_order);
            }
            
            // Clear cart
            $clear_cart = "DELETE FROM cart WHERE user_id = '$user_id'";
            mysqli_query($conn, $clear_cart);
            
            // Redirect to success page
            if ($payment_method == 'online') {
                redirect(SITE_URL . '/user/payment-success.php?order=' . $order_number);
            } else {
                redirect(SITE_URL . '/user/order-success.php?order=' . $order_number);
            }
        } else {
            $error = 'Failed to place order. Please try again.';
        }
    }
}
?>

<div class="container my-5">
    <h2 class="mb-4" data-aos="fade-up"><i class="fas fa-shopping-bag"></i> Checkout</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Checkout Form -->
        <div class="col-lg-8" data-aos="fade-right">
            <div class="form-custom">
                <h4 class="mb-4">Shipping Details</h4>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Full Address *</label>
                            <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" class="form-control" required 
                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State *</label>
                            <input type="text" name="state" class="form-control" required 
                                   value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode *</label>
                            <input type="text" name="pincode" class="form-control" required pattern="[0-9]{6}" 
                                   value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" name="phone" class="form-control" required pattern="[0-9]{10}" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h4 class="mb-4">Payment Method</h4>
                    
                    <div class="mb-3">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                            <label class="form-check-label" for="cod">
                                <i class="fas fa-money-bill-wave text-success"></i> Cash on Delivery
                                <p class="text-muted mb-0"><small>Pay when you receive the order</small></p>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="online" value="online">
                            <label class="form-check-label" for="online">
                                <i class="fas fa-credit-card text-primary"></i> Online Payment
                                <p class="text-muted mb-0"><small>Pay now using debit/credit card or UPI</small></p>
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg btn-custom">
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>
                        <a href="cart.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Cart
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4" data-aos="fade-left">
            <div class="order-summary">
                <h4 class="mb-4">Order Summary</h4>
                
                <div class="mb-3">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo htmlspecialchars(truncateText($item['medicine_name'], 30)); ?> x <?php echo $item['quantity']; ?></span>
                            <span><?php echo formatPrice($item['item_total']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <hr>
                
                <div class="order-summary-item">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                
                <div class="order-summary-item">
                    <span>Shipping</span>
                    <span>
                        <?php if ($shipping == 0): ?>
                            <span class="text-success">FREE</span>
                        <?php else: ?>
                            <?php echo formatPrice($shipping); ?>
                        <?php endif; ?>
                    </span>
                </div>
                
                <hr>
                
                <div class="order-summary-item">
                    <span><strong>Total</strong></span>
                    <span class="order-summary-total"><?php echo formatPrice($total); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>
