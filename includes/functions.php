<?php
/**
 * Common Functions File
 * Contains reusable functions used throughout the application
 */

/**
 * Sanitize input data to prevent XSS attacks
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random order number
 */
function generateOrderNumber() {
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}

/**
 * Generate random transaction ID
 */
function generateTransactionId() {
    return 'TXN' . date('YmdHis') . rand(100, 999);
}

/**
 * Format price in Indian Rupees
 */
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

/**
 * Calculate discount percentage
 */
function calculateDiscount($original_price, $discount_price) {
    if ($original_price <= 0) return 0;
    return round((($original_price - $discount_price) / $original_price) * 100);
}

/**
 * Upload file with validation
 */
function uploadFile($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size (5MB max)
    if ($file["size"] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File is too large. Max 5MB allowed.'];
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

/**
 * Delete file from server
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Redirect to another page
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Get user data by ID
 */
function getUserById($user_id) {
    $conn = getDBConnection();
    $user_id = mysqli_real_escape_string($conn, $user_id);
    
    $query = "SELECT * FROM users WHERE user_id = '$user_id' AND status = 'active'";
    $result = mysqli_query($conn, $query);
    
    $user = null;
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    }
    
    closeDBConnection($conn);
    return $user;
}

/**
 * Get cart count for user
 */
function getCartCount($user_id) {
    $conn = getDBConnection();
    $user_id = mysqli_real_escape_string($conn, $user_id);
    
    $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    
    $count = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['total'] ?? 0;
    }
    
    closeDBConnection($conn);
    return $count;
}

/**
 * Format date in readable format
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Format date with time
 */
function formatDateTime($datetime) {
    return date('d M Y, h:i A', strtotime($datetime));
}

/**
 * Truncate text to specified length
 */
function truncateText($text, $length = 100) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

/**
 * Generate pagination HTML
 */
function generatePagination($current_page, $total_pages, $base_url) {
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page - 1) . '">Previous</a></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page + 1) . '">Next</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Send email (simulation for academic project)
 */
function sendEmail($to, $subject, $message) {
    // In production, use mail() function or PHPMailer
    // For academic project, we'll just log it
    error_log("Email sent to: $to | Subject: $subject");
    return true;
}

/**
 * Log activity (for debugging)
 */
function logActivity($message) {
    $log_file = __DIR__ . '/../logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}
?>
