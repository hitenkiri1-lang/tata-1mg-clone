<?php
/**
 * User Login Page
 */
$page_title = "Login";
include '../includes/header.php';

// Redirect if already logged in
if (isUserLoggedIn()) {
    redirect(SITE_URL . '/user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $conn = getDBConnection();
        $email = mysqli_real_escape_string($conn, $email);
        
        $query = "SELECT * FROM users WHERE email = '$email' AND status = 'active'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            if (verifyPassword($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirect to dashboard
                redirect(SITE_URL . '/user/dashboard.php');
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        
        closeDBConnection($conn);
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5" data-aos="fade-up">
            <div class="form-custom">
                <h2 class="text-center mb-4">Login to Your Account</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3 text-end">
                        <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg btn-custom">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <p class="text-muted"><small>Demo Credentials:</small></p>
                    <p class="text-muted"><small>Email: user@test.com | Password: user123</small></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
