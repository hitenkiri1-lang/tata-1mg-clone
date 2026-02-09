<?php
/**
 * Medicines Listing Page
 */
$page_title = "Medicines";
include 'includes/header.php';

$conn = getDBConnection();

// Get filter parameters
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$search_query = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Build query
$where_conditions = ["m.status = 'active'"];

if ($category_filter) {
    $where_conditions[] = "m.category_id = '$category_filter'";
}

if ($search_query) {
    $where_conditions[] = "(m.medicine_name LIKE '%$search_query%' OR m.manufacturer LIKE '%$search_query%' OR m.description LIKE '%$search_query%')";
}

$where_clause = implode(' AND ', $where_conditions);

// Count total medicines
$count_query = "SELECT COUNT(*) as total FROM medicines m WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_medicines = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_medicines / ITEMS_PER_PAGE);

// Fetch medicines
$medicines_query = "SELECT m.*, c.category_name 
                    FROM medicines m 
                    JOIN categories c ON m.category_id = c.category_id 
                    WHERE $where_clause 
                    ORDER BY m.medicine_name ASC 
                    LIMIT $offset, " . ITEMS_PER_PAGE;
$medicines_result = mysqli_query($conn, $medicines_query);

// Fetch categories for filter
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-3" data-aos="fade-right">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
                </div>
                <div class="card-body">
                    <h6>Categories</h6>
                    <div class="list-group">
                        <a href="medicines.php" class="list-group-item list-group-item-action <?php echo !$category_filter ? 'active' : ''; ?>">
                            All Categories
                        </a>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <a href="medicines.php?category=<?php echo $category['category_id']; ?>" 
                               class="list-group-item list-group-item-action <?php echo $category_filter == $category['category_id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Medicines Grid -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
                <h2>
                    <?php 
                    if ($search_query) {
                        echo 'Search Results for "' . htmlspecialchars($search_query) . '"';
                    } else {
                        echo 'All Medicines';
                    }
                    ?>
                </h2>
                <p class="text-muted"><?php echo $total_medicines; ?> products found</p>
            </div>
            
            <?php if (mysqli_num_rows($medicines_result) > 0): ?>
                <div class="row g-4">
                    <?php while ($medicine = mysqli_fetch_assoc($medicines_result)): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up">
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
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="mt-5">
                        <?php echo generatePagination($page, $total_pages, 'medicines.php' . ($category_filter ? '?category=' . $category_filter : '')); ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h4>No medicines found</h4>
                    <p>Try adjusting your search or filters</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
