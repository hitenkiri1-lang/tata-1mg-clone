<?php
/**
 * User Registration Page
 */
$page_title = "Register";
include '../includes/header.php';

// Redirect if already logged in
if (isUserLoggedIn()) {
    redirect(SITE_URL . '/user/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $security_question = sanitize($_POST['security_question']);
    $security_answer = sanitize($_POST['security_answer']);
    
    // Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!validateEmail($email)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $conn = getDBConnection();
        
        // Check if email already exists
        $email_check = mysqli_real_escape_string($conn, $email);
        $check_query = "SELECT user_id FROM users WHERE email = '$email_check'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Email already registered. Please login.';
        } else {
            // Insert new user
            $hashed_password = hashPassword($password);
            $insert_query = "INSERT INTO users (full_name, email, phone, password, security_question, security_answer) 
                            VALUES ('$full_name', '$email', '$phone', '$hashed_password', '$security_question', '$security_answer')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = 'Registration successful! Please login.';
                // Redirect after 2 seconds
                header("refresh:2;url=login.php");
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        closeDBConnection($conn);
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6" data-aos="fade-up">
            <div class="form-custom">
                <h2 class="text-center mb-4">Create Account</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required 
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="phone" class="form-control" required pattern="[0-9]{10}" 
                               placeholder="10 digit mobile number"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required 
                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                        <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Security Question *</label>
                        <select name="security_question" class="form-select" required>
                            <option value="">Select a question</option>
                            <option value="What is your favorite color?">What is your favorite color?</option>
                            <option value="What is your pet's name?">What is your pet's name?</option>
                            <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                            <option value="What city were you born in?">What city were you born in?</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Security Answer *</label>
                        <input type="text" name="security_answer" class="form-control" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg btn-custom">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
