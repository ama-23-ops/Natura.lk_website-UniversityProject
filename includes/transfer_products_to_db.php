<?php
if (!defined('INCLUDED')) {
    header("Location: /index.php");
    exit();
}

/**
 * Transfer products from session cart to database cart when user logs in
 * 
 * This file should be included in login.php and firebase_auth_check.php after
 * the user is authenticated and session variables are set
 */

// Check if the user is logged in and there are items in the session cart
if (isset($_SESSION['user_id']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Get user ID
    $userId = $_SESSION['user_id'];
    
    // Get user's existing cart items from database
    $stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $existingCartItems = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Prepare statements
        $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        
        // Process each session cart item
        foreach ($_SESSION['cart'] as $productId => $item) {
            if (isset($existingCartItems[$productId])) {
                // Item exists in DB cart - update quantity
                $newQuantity = $existingCartItems[$productId] + $item['quantity'];
                $updateStmt->execute([$newQuantity, $userId, $productId]);
            } else {
                // Item doesn't exist in DB cart - insert new
                $insertStmt->execute([$userId, $productId, $item['quantity']]);
            }
        }
        
        // Commit the transaction
        $conn->commit();
        
        // Clear the session cart after successful transfer
        $_SESSION['cart'] = array();
        
    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $conn->rollBack();
        error_log("Error transferring cart: " . $e->getMessage());
    }
}