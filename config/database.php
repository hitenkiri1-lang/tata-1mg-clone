<?php
/**
 * Database Configuration File
 * Contains database connection settings
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tata_1mg_clone');

// Create database connection
function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Set charset to utf8
    mysqli_set_charset($conn, "utf8");
    
    return $conn;
}

// Close database connection
function closeDBConnection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}
?>
