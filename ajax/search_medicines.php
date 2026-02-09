<?php
/**
 * AJAX - Search Medicines
 */
require_once '../config/config.php';
require_once '../includes/functions.php';

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search = mysqli_real_escape_string(getDBConnection(), $_GET['q']);
    $conn = getDBConnection();
    
    $query = "SELECT medicine_id, medicine_name, price, discount_price 
              FROM medicines 
              WHERE status = 'active' 
              AND (medicine_name LIKE '%$search%' OR manufacturer LIKE '%$search%') 
              LIMIT 10";
    
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        while ($medicine = mysqli_fetch_assoc($result)) {
            $price = $medicine['discount_price'] ?? $medicine['price'];
            echo '<div class="search-suggestion-item" onclick="window.location.href=\'medicine-details.php?id=' . $medicine['medicine_id'] . '\'">';
            echo '<strong>' . htmlspecialchars($medicine['medicine_name']) . '</strong><br>';
            echo '<small class="text-success">' . formatPrice($price) . '</small>';
            echo '</div>';
        }
    } else {
        echo '<div class="search-suggestion-item">No results found</div>';
    }
    
    closeDBConnection($conn);
}
?>
