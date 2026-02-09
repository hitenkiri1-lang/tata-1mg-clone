<?php
/**
 * AJAX - Update Cart Quantity
 */
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $user_id = $_SESSION['user_id'];
    $cart_id = mysqli_real_escape_string(getDBConnection(), $_POST['cart_id']);
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity.']);
        exit();
    }
    
    $conn = getDBConnection();
    
    // Verify cart item belongs to user
    $verify_query = "SELECT c.*, m.stock_quantity FROM cart c JOIN medicines m ON c.medicine_id = m.medicine_id WHERE c.cart_id = '$cart_id' AND c.user_id = '$user_id'";
    $verify_result = mysqli_query($conn, $verify_query);
    
    if (mysqli_num_rows($verify_result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
        exit();
    }
    
    $cart_item = mysqli_fetch_assoc($verify_result);
    
    // Check stock availability
    if ($quantity > $cart_item['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock.']);
        exit();
    }
    
    // Update quantity
    $update_query = "UPDATE cart SET quantity = '$quantity' WHERE cart_id = '$cart_id' AND user_id = '$user_id'";
    
    if (mysqli_query($conn, $update_query)) {
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully.']);
    } else {
        closeDBConnection($conn);
        echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
