<?php
/**
 * Admin - Order Management
 */
$page_title = "Orders";
include 'includes/header.php';

$conn = getDBConnection();
$success = '';
$error = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = sanitize($_POST['order_id']);
    $order_status = sanitize($_POST['order_status']);
    
    $update_query = "UPDATE orders SET order_status = '$order_status' WHERE order_id = '$order_id'";
    if (mysqli_query($conn, $update_query)) {
        $success = "Order status updated successfully!";
    } else {
        $error = "Failed to update order status.";
    }
}

// Fetch orders
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ADMIN_ITEMS_PER_PAGE;

$where = "1=1";
if ($status_filter) {
    $where .= " AND o.order_status = '$status_filter'";
}
if ($search) {
    $where .= " AND (o.order_number LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$count_query = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.user_id WHERE $where";
$count_result = mysqli_query($conn, $count_query);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_orders / ADMIN_ITEMS_PER_PAGE);

$orders_query = "SELECT o.*, u.full_name, u.email, u.phone 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.user_id 
                 WHERE $where 
                 ORDER BY o.order_date DESC 
                 LIMIT $offset, " . ADMIN_ITEMS_PER_PAGE;
$orders_result = mysqli_query($conn, $orders_query);
?>

<h2 class="mb-4"><i class="fas fa-shopping-bag"></i> Order Management</h2>

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
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search by order number, customer name or email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-success">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($orders_result) > 0): ?>
                        <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($order['full_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                </td>
                                <td><?php echo formatDate($order['order_date']); ?></td>
                                <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                <td>
                                    <span class="badge bg-<?php echo $order['payment_method'] == 'cod' ? 'warning' : 'info'; ?>">
                                        <?php echo strtoupper($order['payment_method']); ?>
                                    </span><br>
                                    <small class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-custom status-<?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $order['order_id']; ?>, '<?php echo $order['order_status']; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No orders found</td>
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
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="order_id">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select name="order_status" id="order_status" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateStatus(orderId, currentStatus) {
    document.getElementById('order_id').value = orderId;
    document.getElementById('order_status').value = currentStatus;
    
    var modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}
</script>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
