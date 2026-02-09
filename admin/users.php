<?php
/**
 * Admin - User Management
 */
$page_title = "Users";
include 'includes/header.php';

$conn = getDBConnection();
$success = '';
$error = '';

// Handle Block/Unblock
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
    $action = $_GET['action'];
    
    if ($action == 'block') {
        $update_query = "UPDATE users SET status = 'blocked' WHERE user_id = '$user_id'";
        if (mysqli_query($conn, $update_query)) {
            $success = "User blocked successfully!";
        } else {
            $error = "Failed to block user.";
        }
    } elseif ($action == 'unblock') {
        $update_query = "UPDATE users SET status = 'active' WHERE user_id = '$user_id'";
        if (mysqli_query($conn, $update_query)) {
            $success = "User unblocked successfully!";
        } else {
            $error = "Failed to unblock user.";
        }
    }
}

// Fetch users
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$where = "1=1";
if ($search) {
    $where .= " AND (full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}
if ($status_filter) {
    $where .= " AND status = '$status_filter'";
}

$users_query = "SELECT u.*, 
                COUNT(DISTINCT o.order_id) as total_orders,
                COALESCE(SUM(o.total_amount), 0) as total_spent
                FROM users u
                LEFT JOIN orders o ON u.user_id = o.user_id
                WHERE $where
                GROUP BY u.user_id
                ORDER BY u.created_at DESC";
$users_result = mysqli_query($conn, $users_query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users"></i> User Management</h2>
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

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email or phone" 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="blocked" <?php echo $status_filter == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            <div class="col-md-2">
                <a href="users.php" class="btn btn-secondary w-100">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-list"></i> All Users</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($users_result) > 0): ?>
                        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo $user['total_orders']; ?></td>
                                <td><?php echo formatPrice($user['total_spent']); ?></td>
                                <td>
                                    <?php if ($user['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Blocked</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <?php if ($user['status'] == 'active'): ?>
                                        <a href="users.php?action=block&user_id=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to block this user?')">
                                            <i class="fas fa-ban"></i> Block
                                        </a>
                                    <?php else: ?>
                                        <a href="users.php?action=unblock&user_id=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Are you sure you want to unblock this user?')">
                                            <i class="fas fa-check"></i> Unblock
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
