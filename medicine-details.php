<?php
/**
 * Medicine Details Page
 */
$page_title = "Medicine Details";
include 'includes/header.php';

if (!isset($_GET['id'])) {
    redirect(SITE_URL . '/medicines.php');
}

$medicine_id = mysqli_real_escape_string(getDBConnection(), $_GET['id']);
$conn = getDBConnection();

// Fetch medicine details
$medicine_query = "SELECT m.*, c.category_name 
                   FROM medicines m 
                   JOIN categories c ON m.category_id = c.category_id 
                   WHERE m.medicine_id = '$medicine_id' AND m.status = 'active'";
$medicine_result = mysqli_query($conn, $medicine_query);

if (mysqli_num_rows($medicine_result) == 0) {
    redirect(SITE_URL . '/medicines.php');
}

$medicine = mysqli_fetch_assoc($medicine_result);

// Fetch related medicines
$related_query = "SELECT * FROM medicines 
                  WHERE category_id = '{$medicine['category_id']}' 
                  AND medicine_id != '$medicine_id' 
                  AND status = 'active' 
                  LIMIT 4";
$related_result = mysqli_query($conn, $related_query);
?>

<div class="container my-5">
    <div class="row">
        <!-- Medicine Image and Info -->
        <div class="col-md-5" data-aos="fade-right">
            <div class="card shadow-sm">
                <?php 
                $image_path = 'assets/images/medicines/' . ($medicine['medicine_image'] ?? 'placeholder.png');
                if (!file_exists($image_path) || empty($medicine['medicine_image'])) {
                    $image_path = 'assets/images/medicines/placeholder.png';
                }
                ?>
                <img src="<?php echo $image_path; ?>" 
                     alt="<?php echo htmlspecialchars($medicine['medicine_name']); ?>" 
                     class="card-img-top">
            </div>
        </div>
        
        <!-- Medicine Details -->
        <div class="col-md-7" data-aos="fade-left">
            <h2><?php echo htmlspecialchars($medicine['medicine_name']); ?></h2>
            
            <p class="text-muted">
                <i class="fas fa-industry"></i> <?php echo htmlspecialchars($medicine['manufacturer']); ?>
            </p>
            
            <p class="text-muted">
                <i class="fas fa-list"></i> Category: 
                <a href="medicines.php?category=<?php echo $medicine['category_id']; ?>">
                    <?php echo htmlspecialchars($medicine['category_name']); ?>
                </a>
            </p>
            
            <?php if ($medicine['prescription_required'] == 'yes'): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-prescription"></i> <strong>Prescription Required</strong> - This medicine requires a valid prescription
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <?php if ($medicine['discount_price']): ?>
                    <h3 class="text-success d-inline"><?php echo formatPrice($medicine['discount_price']); ?></h3>
                    <span class="text-muted ms-2"><del><?php echo formatPrice($medicine['price']); ?></del></span>
                    <span class="badge bg-danger ms-2"><?php echo calculateDiscount($medicine['price'], $medicine['discount_price']); ?>% OFF</span>
                <?php else: ?>
                    <h3 class="text-success"><?php echo formatPrice($medicine['price']); ?></h3>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <?php if ($medicine['stock_quantity'] > 0): ?>
                    <span class="badge bg-success fs-6"><i class="fas fa-check-circle"></i> In Stock (<?php echo $medicine['stock_quantity']; ?> available)</span>
                <?php else: ?>
                    <span class="badge bg-danger fs-6"><i class="fas fa-times-circle"></i> Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <?php if ($medicine['composition']): ?>
                <div class="mb-3">
                    <h5><i class="fas fa-flask"></i> Composition</h5>
                    <p><?php echo nl2br(htmlspecialchars($medicine['composition'])); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($medicine['stock_quantity'] > 0): ?>
                <div class="d-grid gap-2">
                    <?php if (isUserLoggedIn()): ?>
                        <button class="btn btn-success btn-lg btn-custom add-to-cart-btn" data-medicine-id="<?php echo $medicine['medicine_id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    <?php else: ?>
                        <a href="user/login.php" class="btn btn-success btn-lg btn-custom">
                            <i class="fas fa-sign-in-alt"></i> Login to Add to Cart
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg w-100" disabled>
                    <i class="fas fa-times-circle"></i> Out of Stock
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Detailed Information -->
    <div class="row mt-5">
        <div class="col-12" data-aos="fade-up">
            <div class="card shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#description">Description</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#uses">Uses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#side-effects">Side Effects</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3">
                        <div id="description" class="tab-pane fade show active">
                            <h5>Description</h5>
                            <p><?php echo nl2br(htmlspecialchars($medicine['description'] ?? 'No description available.')); ?></p>
                        </div>
                        
                        <div id="uses" class="tab-pane fade">
                            <h5>Uses</h5>
                            <p><?php echo nl2br(htmlspecialchars($medicine['uses'] ?? 'No information available.')); ?></p>
                        </div>
                        
                        <div id="side-effects" class="tab-pane fade">
                            <h5>Side Effects</h5>
                            <p><?php echo nl2br(htmlspecialchars($medicine['side_effects'] ?? 'No information available.')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Medicines -->
    <?php if (mysqli_num_rows($related_result) > 0): ?>
        <div class="mt-5">
            <h3 class="mb-4" data-aos="fade-up">Related Medicines</h3>
            <div class="row g-4">
                <?php while ($related = mysqli_fetch_assoc($related_result)): ?>
                    <div class="col-md-3" data-aos="fade-up">
                        <div class="medicine-card">
                            <?php 
                            $image_path = 'assets/images/medicines/' . ($related['medicine_image'] ?? 'placeholder.png');
                            if (!file_exists($image_path) || empty($related['medicine_image'])) {
                                $image_path = 'assets/images/medicines/placeholder.png';
                            }
                            ?>
                            <img src="<?php echo $image_path; ?>" 
                                 alt="<?php echo htmlspecialchars($related['medicine_name']); ?>">
                            <div class="medicine-card-body">
                                <h5><?php echo htmlspecialchars(truncateText($related['medicine_name'], 50)); ?></h5>
                                <div class="mt-2">
                                    <?php if ($related['discount_price']): ?>
                                        <span class="medicine-price"><?php echo formatPrice($related['discount_price']); ?></span>
                                        <span class="medicine-original-price"><?php echo formatPrice($related['price']); ?></span>
                                    <?php else: ?>
                                        <span class="medicine-price"><?php echo formatPrice($related['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-grid mt-3">
                                    <a href="medicine-details.php?id=<?php echo $related['medicine_id']; ?>" class="btn btn-outline-success btn-sm">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
