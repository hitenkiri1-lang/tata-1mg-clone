<?php
/**
 * AJAX - Remove from Cart
 */
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_id = mysqli_real_escape_string(getDBConnection(), $_POST['cart_id']);
    
    $conn = getDBConnection();
    
    // Delete cart item
    $delete_query = "DELETE FROM cart WHERE cart_id = '$cart_id' AND user_id = '$user_id'";
    
    if (mysqli_query($conn, $delete_query)) {
        $cart_count = getCartCount($user_id);
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Item removed from cart.', 'cart_count' => $cart_count]);
    } else {
        closeDBConnection($conn);
        echo json_encode(['success' => false, 'message' => 'Failed to remove item.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
