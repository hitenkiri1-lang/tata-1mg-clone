<?php
/**
 * Admin - Medicine Management
 */
$page_title = "Medicines";
include 'includes/header.php';

$conn = getDBConnection();
$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $medicine_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    $delete_query = "DELETE FROM medicines WHERE medicine_id = '$medicine_id'";
    if (mysqli_query($conn, $delete_query)) {
        $success = "Medicine deleted successfully!";
    } else {
        $error = "Failed to delete medicine.";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medicine_name = sanitize($_POST['medicine_name']);
    $category_id = sanitize($_POST['category_id']);
    $manufacturer = sanitize($_POST['manufacturer']);
    $description = sanitize($_POST['description']);
    $composition = sanitize($_POST['composition']);
    $uses = sanitize($_POST['uses']);
    $side_effects = sanitize($_POST['side_effects']);
    $price = sanitize($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? sanitize($_POST['discount_price']) : NULL;
    $stock_quantity = sanitize($_POST['stock_quantity']);
    $prescription_required = sanitize($_POST['prescription_required']);
    $status = sanitize($_POST['status']);
    $medicine_id = isset($_POST['medicine_id']) ? sanitize($_POST['medicine_id']) : '';
    
    if (empty($medicine_name) || empty($category_id) || empty($price)) {
        $error = "Medicine name, category, and price are required.";
    } elseif (empty($medicine_id) && (!isset($_FILES['medicine_image']) || $_FILES['medicine_image']['error'] == UPLOAD_ERR_NO_FILE)) {
        // Image is required when ADDING a new medicine
        $error = "Medicine image is required when adding a new medicine.";
    } else {
        // Handle image upload
        $medicine_image = '';
        $upload_dir = __DIR__ . '/../assets/images/medicines/';
        
        if (isset($_FILES['medicine_image']) && $_FILES['medicine_image']['error'] == UPLOAD_ERR_OK) {
            // Image was uploaded
            $upload_result = uploadFile($_FILES['medicine_image'], $upload_dir);
            
            if ($upload_result['success']) {
                $medicine_image = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        // Only proceed if no upload error
        if (empty($error)) {
            if ($medicine_id) {
                // Update - image is optional
                if (!empty($medicine_image)) {
                    // New image uploaded, update with new image
                    $update_query = "UPDATE medicines SET 
                                    medicine_name = '$medicine_name',
                                    category_id = '$category_id',
                                    manufacturer = '$manufacturer',
                                    description = '$description',
                                    composition = '$composition',
                                    uses = '$uses',
                                    side_effects = '$side_effects',
                                    price = '$price',
                                    discount_price = " . ($discount_price ? "'$discount_price'" : "NULL") . ",
                                    stock_quantity = '$stock_quantity',
                                    medicine_image = '$medicine_image',
                                    prescription_required = '$prescription_required',
                                    status = '$status'
                                    WHERE medicine_id = '$medicine_id'";
                } else {
                    // No new image, keep existing image
                    $update_query = "UPDATE medicines SET 
                                    medicine_name = '$medicine_name',
                                    category_id = '$category_id',
                                    manufacturer = '$manufacturer',
                                    description = '$description',
                                    composition = '$composition',
                                    uses = '$uses',
                                    side_effects = '$side_effects',
                                    price = '$price',
                                    discount_price = " . ($discount_price ? "'$discount_price'" : "NULL") . ",
                                    stock_quantity = '$stock_quantity',
                                    prescription_required = '$prescription_required',
                                    status = '$status'
                                    WHERE medicine_id = '$medicine_id'";
                }
                
                if (mysqli_query($conn, $update_query)) {
                    $success = "Medicine updated successfully!";
                } else {
                    $error = "Failed to update medicine.";
                }
            } else {
                // Insert - image is required and already uploaded
                $insert_query = "INSERT INTO medicines (medicine_name, category_id, manufacturer, description, composition, uses, side_effects, price, discount_price, stock_quantity, medicine_image, prescription_required, status) 
                                VALUES ('$medicine_name', '$category_id', '$manufacturer', '$description', '$composition', '$uses', '$side_effects', '$price', " . ($discount_price ? "'$discount_price'" : "NULL") . ", '$stock_quantity', '$medicine_image', '$prescription_required', '$status')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $success = "Medicine added successfully!";
                } else {
                    $error = "Failed to add medicine.";
                }
            }
        }
    }
}

// Fetch medicines
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ADMIN_ITEMS_PER_PAGE;

$where = "1=1";
if ($search) {
    $where .= " AND (m.medicine_name LIKE '%$search%' OR m.manufacturer LIKE '%$search%')";
}

$count_query = "SELECT COUNT(*) as total FROM medicines m WHERE $where";
$count_result = mysqli_query($conn, $count_query);
$total_medicines = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_medicines / ADMIN_ITEMS_PER_PAGE);

$medicines_query = "SELECT m.*, c.category_name 
                    FROM medicines m 
                    LEFT JOIN categories c ON m.category_id = c.category_id 
                    WHERE $where 
                    ORDER BY m.medicine_name ASC 
                    LIMIT $offset, " . ADMIN_ITEMS_PER_PAGE;
$medicines_result = mysqli_query($conn, $medicines_query);

// Fetch categories for dropdown
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-pills"></i> Medicine Management</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#medicineModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Add Medicine
    </button>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Search medicines..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>
    </div>
</div>

<!-- Medicines Table -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Medicine Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($medicines_result) > 0): ?>
                        <?php while ($medicine = mysqli_fetch_assoc($medicines_result)): ?>
                            <tr>
                                <td><?php echo $medicine['medicine_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($medicine['medicine_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($medicine['manufacturer']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($medicine['category_name']); ?></td>
                                <td>
                                    <?php if ($medicine['discount_price']): ?>
                                        <strong><?php echo formatPrice($medicine['discount_price']); ?></strong><br>
                                        <small><del><?php echo formatPrice($medicine['price']); ?></del></small>
                                    <?php else: ?>
                                        <strong><?php echo formatPrice($medicine['price']); ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($medicine['stock_quantity'] > 10): ?>
                                        <span class="badge bg-success"><?php echo $medicine['stock_quantity']; ?></span>
                                    <?php elseif ($medicine['stock_quantity'] > 0): ?>
                                        <span class="badge bg-warning"><?php echo $medicine['stock_quantity']; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $medicine['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($medicine['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='editMedicine(<?php echo json_encode($medicine); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $medicine['medicine_id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this medicine?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No medicines found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Medicine Modal -->
<div class="modal fade" id="medicineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Medicine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="medicine_id" id="medicine_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Medicine Name *</label>
                            <input type="text" name="medicine_name" id="medicine_name" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php 
                                mysqli_data_seek($categories_result, 0);
                                while ($cat = mysqli_fetch_assoc($categories_result)): 
                                ?>
                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Manufacturer</label>
                            <input type="text" name="manufacturer" id="manufacturer" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Composition</label>
                            <input type="text" name="composition" id="composition" class="form-control">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Price *</label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Discount Price</label>
                            <input type="number" step="0.01" name="discount_price" id="discount_price" class="form-control">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Quantity *</label>
                            <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" required value="0">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Medicine Image <span id="imageRequiredLabel" class="text-danger">*</span></label>
                            <input type="file" name="medicine_image" id="medicine_image" class="form-control" accept="image/*">
                            <small class="text-muted">Image is required when adding new medicine. Optional when editing.</small>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Uses</label>
                            <textarea name="uses" id="uses" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Side Effects</label>
                            <textarea name="side_effects" id="side_effects" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prescription Required</label>
                            <select name="prescription_required" id="prescription_required" class="form-select">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editMedicine(medicine) {
    document.getElementById('modalTitle').textContent = 'Edit Medicine';
    document.getElementById('medicine_id').value = medicine.medicine_id;
    document.getElementById('medicine_name').value = medicine.medicine_name;
    document.getElementById('category_id').value = medicine.category_id;
    document.getElementById('manufacturer').value = medicine.manufacturer || '';
    document.getElementById('composition').value = medicine.composition || '';
    document.getElementById('description').value = medicine.description || '';
    document.getElementById('uses').value = medicine.uses || '';
    document.getElementById('side_effects').value = medicine.side_effects || '';
    document.getElementById('price').value = medicine.price;
    document.getElementById('discount_price').value = medicine.discount_price || '';
    document.getElementById('stock_quantity').value = medicine.stock_quantity;
    document.getElementById('prescription_required').value = medicine.prescription_required;
    document.getElementById('status').value = medicine.status;
    
    // Image is optional when editing
    document.getElementById('medicine_image').removeAttribute('required');
    document.getElementById('imageRequiredLabel').style.display = 'none';
    
    var modal = new bootstrap.Modal(document.getElementById('medicineModal'));
    modal.show();
}

function resetForm() {
    document.getElementById('modalTitle').textContent = 'Add Medicine';
    document.getElementById('medicine_id').value = '';
    document.getElementById('medicine_name').value = '';
    document.getElementById('category_id').value = '';
    document.getElementById('manufacturer').value = '';
    document.getElementById('composition').value = '';
    document.getElementById('description').value = '';
    document.getElementById('uses').value = '';
    document.getElementById('side_effects').value = '';
    document.getElementById('price').value = '';
    document.getElementById('discount_price').value = '';
    document.getElementById('stock_quantity').value = '0';
    document.getElementById('prescription_required').value = 'no';
    document.getElementById('status').value = 'active';
    
    // Image is required when adding new medicine
    document.getElementById('medicine_image').setAttribute('required', 'required');
    document.getElementById('imageRequiredLabel').style.display = 'inline';
    document.getElementById('medicine_image').value = '';
}
</script>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
