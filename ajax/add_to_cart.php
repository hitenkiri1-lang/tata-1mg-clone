<?php
/**
 * AJAX - Add to Cart
 */
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['medicine_id'])) {
    $user_id = $_SESSION['user_id'];
    $medicine_id = mysqli_real_escape_string(getDBConnection(), $_POST['medicine_id']);
    
    $conn = getDBConnection();
    
    // Check if medicine exists and is in stock
    $medicine_query = "SELECT * FROM medicines WHERE medicine_id = '$medicine_id' AND status = 'active'";
    $medicine_result = mysqli_query($conn, $medicine_query);
    
    if (mysqli_num_rows($medicine_result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Medicine not found.']);
        exit();
    }
    
    $medicine = mysqli_fetch_assoc($medicine_result);
    
    if ($medicine['stock_quantity'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Medicine out of stock.']);
        exit();
    }
    
    // Check if already in cart
    $check_query = "SELECT * FROM cart WHERE user_id = '$user_id' AND medicine_id = '$medicine_id'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update quantity
        $update_query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = '$user_id' AND medicine_id = '$medicine_id'";
        mysqli_query($conn, $update_query);
    } else {
        // Insert new cart item
        $insert_query = "INSERT INTO cart (user_id, medicine_id, quantity) VALUES ('$user_id', '$medicine_id', 1)";
        mysqli_query($conn, $insert_query);
    }
    
    $cart_count = getCartCount($user_id);
    
    closeDBConnection($conn);
    
    echo json_encode(['success' => true, 'message' => 'Added to cart successfully!', 'cart_count' => $cart_count]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
