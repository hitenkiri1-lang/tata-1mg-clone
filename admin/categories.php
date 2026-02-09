<?php
/**
 * Admin - Category Management
 */
$page_title = "Categories";
include 'includes/header.php';

$conn = getDBConnection();
$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $category_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Check if category has medicines
    $check_query = "SELECT COUNT(*) as count FROM medicines WHERE category_id = '$category_id'";
    $check_result = mysqli_query($conn, $check_query);
    $count = mysqli_fetch_assoc($check_result)['count'];
    
    if ($count > 0) {
        $error = "Cannot delete category. It has $count medicines associated with it.";
    } else {
        $delete_query = "DELETE FROM categories WHERE category_id = '$category_id'";
        if (mysqli_query($conn, $delete_query)) {
            $success = "Category deleted successfully!";
        } else {
            $error = "Failed to delete category.";
        }
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = sanitize($_POST['category_name']);
    $description = sanitize($_POST['description']);
    $status = sanitize($_POST['status']);
    $category_id = isset($_POST['category_id']) ? sanitize($_POST['category_id']) : '';
    
    if (empty($category_name)) {
        $error = "Category name is required.";
    } else {
        if ($category_id) {
            // Update
            $update_query = "UPDATE categories SET 
                            category_name = '$category_name',
                            description = '$description',
                            status = '$status'
                            WHERE category_id = '$category_id'";
            if (mysqli_query($conn, $update_query)) {
                $success = "Category updated successfully!";
            } else {
                $error = "Failed to update category.";
            }
        } else {
            // Insert
            $insert_query = "INSERT INTO categories (category_name, description, status) 
                            VALUES ('$category_name', '$description', '$status')";
            if (mysqli_query($conn, $insert_query)) {
                $success = "Category added successfully!";
            } else {
                $error = "Failed to add category.";
            }
        }
    }
}

// Fetch categories
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ADMIN_ITEMS_PER_PAGE;

$count_query = "SELECT COUNT(*) as total FROM categories";
$count_result = mysqli_query($conn, $count_query);
$total_categories = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_categories / ADMIN_ITEMS_PER_PAGE);

$categories_query = "SELECT * FROM categories ORDER BY category_name ASC LIMIT $offset, " . ADMIN_ITEMS_PER_PAGE;
$categories_result = mysqli_query($conn, $categories_query);

// Get category for editing
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_query = "SELECT * FROM categories WHERE category_id = '$edit_id'";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_category = mysqli_fetch_assoc($edit_result);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-list"></i> Category Management</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Add Category
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

<!-- Categories Table -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($categories_result) > 0): ?>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <tr>
                                <td><?php echo $category['category_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($category['category_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars(truncateText($category['description'] ?? '', 50)); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $category['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($category['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($category['created_at']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $category['category_id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this category?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No categories found</td>
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
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="category_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="category_name" id="category_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('category_id').value = category.category_id;
    document.getElementById('category_name').value = category.category_name;
    document.getElementById('description').value = category.description || '';
    document.getElementById('status').value = category.status;
    
    var modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    modal.show();
}

function resetForm() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('category_id').value = '';
    document.getElementById('category_name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('status').value = 'active';
}
</script>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
