<?php
/**
 * User Profile Management
 */
$page_title = "My Profile";
include '../includes/header.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    redirect(SITE_URL . '/user/login.php');
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $pincode = sanitize($_POST['pincode']);
    
    if (empty($full_name) || empty($phone)) {
        $error = 'Name and phone are required.';
    } else {
        $update_query = "UPDATE users SET 
                        full_name = '$full_name',
                        phone = '$phone',
                        address = '$address',
                        city = '$city',
                        state = '$state',
                        pincode = '$pincode'
                        WHERE user_id = '$user_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['user_name'] = $full_name;
            $success = 'Profile updated successfully!';
        } else {
            $error = 'Failed to update profile.';
        }
    }
}

// Fetch user data
$user = getUserById($user_id);
?>

<div class="container my-5">
    <h2 class="mb-4" data-aos="fade-up"><i class="fas fa-user"></i> My Profile</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8" data-aos="fade-right">
            <div class="form-custom">
                <h4 class="mb-4">Personal Information</h4>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" readonly 
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" name="phone" class="form-control" required pattern="[0-9]{10}" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" pattern="[0-9]{6}" 
                                   value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg btn-custom">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-4" data-aos="fade-left">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Member Since:</strong><br><?php echo formatDate($user['created_at']); ?></p>
                    <p><strong>Account Status:</strong><br>
                        <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </p>
                    <p><strong>Security Question:</strong><br><?php echo htmlspecialchars($user['security_question']); ?></p>
                </div>
            </div>
            
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="orders.php" class="btn btn-primary">
                            <i class="fas fa-box"></i> My Orders
                        </a>
                        <a href="cart.php" class="btn btn-success">
                            <i class="fas fa-shopping-cart"></i> My Cart
                        </a>
                        <a href="<?php echo SITE_URL; ?>/medicines.php" class="btn btn-info">
                            <i class="fas fa-pills"></i> Browse Medicines
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>
