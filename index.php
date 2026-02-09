<?php
/**
 * Home Page
 * Main landing page of the application
 */
$page_title = "Home";
include 'includes/header.php';

// Fetch featured categories
$conn = getDBConnection();
$categories_query = "SELECT * FROM categories WHERE status = 'active' LIMIT 8";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch featured medicines
$medicines_query = "SELECT m.*, c.category_name 
                    FROM medicines m 
                    JOIN categories c ON m.category_id = c.category_id 
                    WHERE m.status = 'active' 
                    ORDER BY m.created_at DESC 
                    LIMIT 12";
$medicines_result = mysqli_query($conn, $medicines_query);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1>India's Trusted Online Pharmacy</h1>
                <p>Buy medicines, health products and consult doctors online from the comfort of your home</p>
                <a href="medicines.php" class="btn btn-light btn-lg btn-custom">
                    <i class="fas fa-pills"></i> Browse Medicines
                </a>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <img src="assets/images/hero-banner.png" 
                     alt="Healthcare" 
                     class="img-fluid rounded"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div style="display:none; align-items:center; justify-content:center; height:400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius:10px; color:white; font-size:3rem;">
                    <i class="fas fa-heartbeat"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="container my-5">
    <h2 class="text-center mb-4" data-aos="fade-up">Shop by Category</h2>
    <div class="row g-4">
        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in">
                <a href="medicines.php?category=<?php echo $category['category_id']; ?>" class="text-decoration-none">
                    <div class="category-card">
                        <?php
                        // Try to use category image if exists
                        $category_image = 'assets/images/categories/' . strtolower(str_replace(' ', '-', $category['category_name'])) . '.png';
                        if (!file_exists($category_image)) {
                            // Fallback to Font Awesome icon
                            $icon_map = [
                                'pain relief' => 'fa-pills',
                                'vitamins' => 'fa-leaf',
                                'diabetes' => 'fa-syringe',
                                'heart care' => 'fa-heartbeat',
                                'cold & cough' => 'fa-thermometer',
                                'skin care' => 'fa-hand-sparkles',
                                'digestion' => 'fa-stomach',
                                'baby care' => 'fa-baby',
                            ];
                            $category_lower = strtolower($category['category_name']);
                            $icon_class = $icon_map[$category_lower] ?? 'fa-medkit';
                            echo '<div style="width:80px; height:80px; background:#1aab2a; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px;"><i class="fas ' . $icon_class . ' fa-2x text-white"></i></div>';
                        } else {
                            echo '<img src="' . $category_image . '" alt="' . htmlspecialchars($category['category_name']) . '" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                            echo '<div style="display:none; width:80px; height:80px; background:#1aab2a; border-radius:50%; align-items:center; justify-content:center; margin:0 auto 15px;"><i class="fas fa-medkit fa-2x text-white"></i></div>';
                        }
                        ?>
                        <h5><?php echo htmlspecialchars($category['category_name']); ?></h5>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- Featured Medicines Section -->
<section class="container my-5">
    <h2 class="text-center mb-4" data-aos="fade-up">Featured Medicines</h2>
    <div class="row g-4">
        <?php while ($medicine = mysqli_fetch_assoc($medicines_result)): ?>
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up">
                <div class="medicine-card">
                    <?php 
                    $image_path = 'assets/images/medicines/' . ($medicine['medicine_image'] ?? 'placeholder.png');
                    if (!file_exists($image_path) || empty($medicine['medicine_image'])) {
                        $image_path = 'assets/images/medicines/placeholder.png';
                    }
                    ?>
                    <img src="<?php echo $image_path; ?>" 
                         alt="<?php echo htmlspecialchars($medicine['medicine_name']); ?>">
                    <div class="medicine-card-body">
                        <h5><?php echo htmlspecialchars(truncateText($medicine['medicine_name'], 50)); ?></h5>
                        <p class="text-muted mb-2">
                            <small><?php echo htmlspecialchars($medicine['category_name']); ?></small>
                        </p>
                        
                        <?php if ($medicine['prescription_required'] == 'yes'): ?>
                            <span class="prescription-badge">Rx Required</span>
                        <?php endif; ?>
                        
                        <div class="mt-2">
                            <?php if ($medicine['discount_price']): ?>
                                <span class="medicine-price"><?php echo formatPrice($medicine['discount_price']); ?></span>
                                <span class="medicine-original-price"><?php echo formatPrice($medicine['price']); ?></span>
                                <span class="discount-badge"><?php echo calculateDiscount($medicine['price'], $medicine['discount_price']); ?>% OFF</span>
                            <?php else: ?>
                                <span class="medicine-price"><?php echo formatPrice($medicine['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <?php if ($medicine['stock_quantity'] > 0): ?>
                                <span class="in-stock"><i class="fas fa-check-circle"></i> In Stock</span>
                            <?php else: ?>
                                <span class="out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="medicine-details.php?id=<?php echo $medicine['medicine_id']; ?>" class="btn btn-outline-success btn-sm">
                                View Details
                            </a>
                            <?php if ($medicine['stock_quantity'] > 0): ?>
                                <?php if (isUserLoggedIn()): ?>
                                    <button class="btn btn-success btn-sm add-to-cart-btn" data-medicine-id="<?php echo $medicine['medicine_id']; ?>">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                <?php else: ?>
                                    <a href="user/login.php" class="btn btn-success btn-sm">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="medicines.php" class="btn btn-success btn-lg btn-custom">
            View All Medicines <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

<!-- Features Section -->
<section class="container my-5">
    <div class="row g-4">
        <div class="col-md-3" data-aos="flip-left">
            <div class="text-center p-4">
                <i class="fas fa-shipping-fast fa-3x text-success mb-3"></i>
                <h5>Fast Delivery</h5>
                <p class="text-muted">Get medicines delivered to your doorstep</p>
            </div>
        </div>
        <div class="col-md-3" data-aos="flip-left" data-aos-delay="100">
            <div class="text-center p-4">
                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                <h5>100% Genuine</h5>
                <p class="text-muted">All medicines are authentic and verified</p>
            </div>
        </div>
        <div class="col-md-3" data-aos="flip-left" data-aos-delay="200">
            <div class="text-center p-4">
                <i class="fas fa-lock fa-3x text-success mb-3"></i>
                <h5>Secure Payment</h5>
                <p class="text-muted">Multiple payment options available</p>
            </div>
        </div>
        <div class="col-md-3" data-aos="flip-left" data-aos-delay="300">
            <div class="text-center p-4">
                <i class="fas fa-headset fa-3x text-success mb-3"></i>
                <h5>24/7 Support</h5>
                <p class="text-muted">Customer support always available</p>
            </div>
        </div>
    </div>
</section>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
