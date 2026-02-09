<?php
/**
 * Admin - Reports
 */
$page_title = "Reports";
include 'includes/header.php';

$conn = getDBConnection();

// Get date range
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');

// Sales Statistics
$sales_query = "SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as avg_order_value,
                SUM(CASE WHEN payment_status = 'completed' THEN total_amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_amount
                FROM orders 
                WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'";
$sales_result = mysqli_query($conn, $sales_query);
$sales_stats = mysqli_fetch_assoc($sales_result);

// Payment Method Breakdown
$payment_query = "SELECT 
                  payment_method,
                  COUNT(*) as count,
                  SUM(total_amount) as amount
                  FROM orders 
                  WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'
                  GROUP BY payment_method";
$payment_result = mysqli_query($conn, $payment_query);

// Order Status Breakdown
$status_query = "SELECT 
                 order_status,
                 COUNT(*) as count,
                 SUM(total_amount) as amount
                 FROM orders 
                 WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'
                 GROUP BY order_status";
$status_result = mysqli_query($conn, $status_query);

// Top Selling Medicines
$top_medicines_query = "SELECT 
                        m.medicine_name,
                        m.manufacturer,
                        SUM(oi.quantity) as total_quantity,
                        SUM(oi.subtotal) as total_revenue
                        FROM order_items oi
                        JOIN medicines m ON oi.medicine_id = m.medicine_id
                        JOIN orders o ON oi.order_id = o.order_id
                        WHERE DATE(o.order_date) BETWEEN '$start_date' AND '$end_date'
                        GROUP BY oi.medicine_id
                        ORDER BY total_quantity DESC
                        LIMIT 10";
$top_medicines_result = mysqli_query($conn, $top_medicines_query);

// Category-wise Sales
$category_query = "SELECT 
                   c.category_name,
                   COUNT(DISTINCT oi.order_id) as orders,
                   SUM(oi.quantity) as quantity,
                   SUM(oi.subtotal) as revenue
                   FROM order_items oi
                   JOIN medicines m ON oi.medicine_id = m.medicine_id
                   JOIN categories c ON m.category_id = c.category_id
                   JOIN orders o ON oi.order_id = o.order_id
                   WHERE DATE(o.order_date) BETWEEN '$start_date' AND '$end_date'
                   GROUP BY c.category_id
                   ORDER BY revenue DESC";
$category_result = mysqli_query($conn, $category_query);
?>

<h2 class="mb-4"><i class="fas fa-chart-bar"></i> Sales Reports</h2>

<!-- Date Filter -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Sales Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="dashboard-card">
            <i class="fas fa-shopping-bag text-primary"></i>
            <h3><?php echo $sales_stats['total_orders'] ?? 0; ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <i class="fas fa-rupee-sign text-success"></i>
            <h3><?php echo formatPrice($sales_stats['total_revenue'] ?? 0); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <i class="fas fa-chart-line text-info"></i>
            <h3><?php echo formatPrice($sales_stats['avg_order_value'] ?? 0); ?></h3>
            <p>Avg Order Value</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <i class="fas fa-check-circle text-success"></i>
            <h3><?php echo formatPrice($sales_stats['paid_amount'] ?? 0); ?></h3>
            <p>Paid Amount</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Payment Method Breakdown -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Payment Method Breakdown</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th>Orders</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payment = mysqli_fetch_assoc($payment_result)): ?>
                            <tr>
                                <td><?php echo strtoupper($payment['payment_method']); ?></td>
                                <td><?php echo $payment['count']; ?></td>
                                <td><?php echo formatPrice($payment['amount']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Order Status Breakdown -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Order Status Breakdown</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($status = mysqli_fetch_assoc($status_result)): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-custom status-<?php echo $status['order_status']; ?>">
                                        <?php echo ucfirst($status['order_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $status['count']; ?></td>
                                <td><?php echo formatPrice($status['amount']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top Selling Medicines -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning">
        <h5 class="mb-0">Top 10 Selling Medicines</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Medicine Name</th>
                        <th>Manufacturer</th>
                        <th>Quantity Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    while ($medicine = mysqli_fetch_assoc($top_medicines_result)): 
                    ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><strong><?php echo htmlspecialchars($medicine['medicine_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($medicine['manufacturer']); ?></td>
                            <td><?php echo $medicine['total_quantity']; ?></td>
                            <td><strong><?php echo formatPrice($medicine['total_revenue']); ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Category-wise Sales -->
<div class="card shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Category-wise Sales</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Orders</th>
                        <th>Quantity</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = mysqli_fetch_assoc($category_result)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($category['category_name']); ?></strong></td>
                            <td><?php echo $category['orders']; ?></td>
                            <td><?php echo $category['quantity']; ?></td>
                            <td><strong><?php echo formatPrice($category['revenue']); ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
