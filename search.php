<?php
/**
 * Search Results Page
 */
$page_title = "Search Results";
include 'includes/header.php';

$conn = getDBConnection();

// Get search query
$search_query = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';

if (empty($search_query)) {
    redirect(SITE_URL . '/medicines.php');
}

// Fetch search results
$medicines_query = "SELECT m.*, c.category_name 
                    FROM medicines m 
                    JOIN categories c ON m.category_id = c.category_id 
                    WHERE m.status = 'active' 
                    AND (m.medicine_name LIKE '%$search_query%' 
                         OR m.manufacturer LIKE '%$search_query%' 
                         OR m.description LIKE '%$search_query%'
                         OR c.category_name LIKE '%$search_query%')
                    ORDER BY m.medicine_name ASC";
$medicines_result = mysqli_query($conn, $medicines_query);
$total_results = mysqli_num_rows($medicines_result);
?>

<div class="container my-5">
    <div class="mb-4" data-aos="fade-up">
        <h2>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
        <p class="text-muted"><?php echo $total_results; ?> result(s) found</p>
    </div>
    
    <?php if ($total_results > 0): ?>
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
    <?php else: ?>
        <div class="text-center py-5" data-aos="fade-up">
            <i class="fas fa-search fa-5x text-muted mb-4"></i>
            <h3>No results found</h3>
            <p class="text-muted">Try searching with different keywords</p>
            <a href="medicines.php" class="btn btn-success btn-lg btn-custom mt-3">
                <i class="fas fa-pills"></i> Browse All Medicines
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
