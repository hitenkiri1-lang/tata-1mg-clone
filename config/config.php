<?php
/**
 * General Configuration File
 * Contains site-wide settings and constants
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site settings
define('SITE_NAME', 'Tata 1mg Clone');
define('SITE_URL', 'http://localhost/tata-1mg-clone');
define('ADMIN_EMAIL', 'admin@1mg.com');

// File upload settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination settings
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 10);

// Security settings
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Payment settings
define('SHIPPING_CHARGE', 50);
define('FREE_SHIPPING_THRESHOLD', 500);

// Order status
define('ORDER_STATUS', [
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled'
]);

// Payment methods
define('PAYMENT_METHODS', [
    'cod' => 'Cash on Delivery',
    'online' => 'Online Payment'
]);

// Include database configuration
require_once __DIR__ . '/database.php';

// Timezone
date_default_timezone_set('Asia/Kolkata');
?>
