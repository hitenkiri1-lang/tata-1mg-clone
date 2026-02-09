<?php
/**
 * Forgot Password Page
 */
$page_title = "Forgot Password";
include '../includes/header.php';

// Redirect if already logged in
if (isUserLoggedIn()) {
    redirect(SITE_URL . '/user/dashboard.php');
}

$error = '';
$success = '';
$step = 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['verify_email'])) {
        // Step 1: Verify email
        $email = sanitize($_POST['email']);
        
        $query = "SELECT * FROM users WHERE email = '$email' AND status = 'active'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $_SESSION['reset_email'] = $email;
            $user = mysqli_fetch_assoc($result);
            $_SESSION['security_question'] = $user['security_question'];
            $_SESSION['security_answer'] = $user['security_answer'];
            $step = 2;
        } else {
            $error = 'Email not found.';
        }
    } elseif (isset($_POST['verify_answer'])) {
        // Step 2: Verify security answer
        $answer = sanitize($_POST['security_answer']);
        
        if (strtolower($answer) == strtolower($_SESSION['security_answer'])) {
            $step = 3;
        } else {
            $error = 'Incorrect answer.';
            $step = 2;
        }
    } elseif (isset($_POST['reset_password'])) {
        // Step 3: Reset password
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
            $step = 3;
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
            $step = 3;
        } else {
            $email = $_SESSION['reset_email'];
            $hashed_password = hashPassword($password);
            
            $update_query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
            if (mysqli_query($conn, $update_query)) {
                $success = 'Password reset successful! You can now login with your new password.';
                unset($_SESSION['reset_email']);
                unset($_SESSION['security_question']);
                unset($_SESSION['security_answer']);
                $step = 4;
            } else {
                $error = 'Failed to reset password. Please try again.';
                $step = 3;
            }
        }
    }
    
    closeDBConnection($conn);
} elseif (isset($_SESSION['reset_email'])) {
    $step = isset($_SESSION['security_question']) ? 2 : 3;
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6" data-aos="fade-up">
            <div class="form-custom">
                <h2 class="text-center mb-4">Forgot Password</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($step == 1): ?>
                    <!-- Step 1: Enter Email -->
                    <p class="text-center text-muted mb-4">Enter your email address to reset your password</p>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="verify_email" class="btn btn-success btn-lg btn-custom">
                                Continue
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($step == 2): ?>
                    <!-- Step 2: Answer Security Question -->
                    <p class="text-center text-muted mb-4">Answer your security question</p>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Security Question</label>
                            <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($_SESSION['security_question']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Your Answer</label>
                            <input type="text" name="security_answer" class="form-control" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="verify_answer" class="btn btn-success btn-lg btn-custom">
                                Verify Answer
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($step == 3): ?>
                    <!-- Step 3: Reset Password -->
                    <p class="text-center text-muted mb-4">Enter your new password</p>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                            <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="reset_password" class="btn btn-success btn-lg btn-custom">
                                Reset Password
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($step == 4): ?>
                    <!-- Step 4: Success -->
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                        <h4>Password Reset Successful!</h4>
                        <p class="text-muted">You can now login with your new password</p>
                        <a href="login.php" class="btn btn-success btn-lg btn-custom mt-3">
                            Go to Login
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <p>Remember your password? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
