<?php
/**
 * Shopping Cart Page
 */
$page_title = "Shopping Cart";
include '../includes/header.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    redirect(SITE_URL . '/user/login.php');
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Fetch cart items
$cart_query = "SELECT c.*, m.medicine_name, m.price, m.discount_price, m.medicine_image, m.stock_quantity, m.prescription_required
               FROM cart c
               JOIN medicines m ON c.medicine_id = m.medicine_id
               WHERE c.user_id = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);

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
?>

<div class="container my-5">
    <h2 class="mb-4" data-aos="fade-up"><i class="fas fa-shopping-cart"></i> Shopping Cart</h2>
    
    <?php if (count($cart_items) > 0): ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8" data-aos="fade-right">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php 
                                $image_path = '../assets/images/medicines/' . ($item['medicine_image'] ?? 'placeholder.png');
                                if (!file_exists($image_path) || empty($item['medicine_image'])) {
                                    $image_path = '../assets/images/medicines/placeholder.png';
                                }
                                ?>
                                <img src="<?php echo $image_path; ?>" 
                                     alt="<?php echo htmlspecialchars($item['medicine_name']); ?>" 
                                     class="img-fluid rounded">
                            </div>
                            <div class="col-md-4">
                                <h5><?php echo htmlspecialchars($item['medicine_name']); ?></h5>
                                <?php if ($item['prescription_required'] == 'yes'): ?>
                                    <span class="prescription-badge">Rx Required</span>
                                <?php endif; ?>
                                <p class="text-muted mb-0">
                                    <?php if ($item['stock_quantity'] > 0): ?>
                                        <small class="in-stock">In Stock</small>
                                    <?php else: ?>
                                        <small class="out-of-stock">Out of Stock</small>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-2">
                                <p class="mb-0"><strong><?php echo formatPrice($item['discount_price'] ?? $item['price']); ?></strong></p>
                                <?php if ($item['discount_price']): ?>
                                    <small class="text-muted"><del><?php echo formatPrice($item['price']); ?></del></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2">
                                <div class="quantity-control">
                                    <button class="update-quantity" data-cart-id="<?php echo $item['cart_id']; ?>" data-action="decrease">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span id="quantity-<?php echo $item['cart_id']; ?>"><?php echo $item['quantity']; ?></span>
                                    <button class="update-quantity" data-cart-id="<?php echo $item['cart_id']; ?>" data-action="increase">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <p class="mb-2"><strong><?php echo formatPrice($item['item_total']); ?></strong></p>
                                <button class="btn btn-sm btn-danger remove-from-cart" data-cart-id="<?php echo $item['cart_id']; ?>">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4" data-aos="fade-left">
                <div class="order-summary">
                    <h4 class="mb-4">Order Summary</h4>
                    
                    <div class="order-summary-item">
                        <span>Subtotal (<?php echo count($cart_items); ?> items)</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    
                    <div class="order-summary-item">
                        <span>Shipping Charges</span>
                        <span>
                            <?php if ($shipping == 0): ?>
                                <span class="text-success">FREE</span>
                            <?php else: ?>
                                <?php echo formatPrice($shipping); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
                        <div class="alert alert-info">
                            <small>Add <?php echo formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal); ?> more for FREE shipping!</small>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="order-summary-item">
                        <span><strong>Total Amount</strong></span>
                        <span class="order-summary-total"><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="checkout.php" class="btn btn-success btn-lg btn-custom">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                        <a href="<?php echo SITE_URL; ?>/medicines.php" class="btn btn-outline-success">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <div class="text-center py-5" data-aos="fade-up">
            <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Add some medicines to get started!</p>
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
