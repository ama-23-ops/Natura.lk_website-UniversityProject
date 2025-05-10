<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$userId = $_SESSION['user_id'];

try {
    $conn->beginTransaction();

    // Fetch wishlist items
    $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wishlistItems = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($wishlistItems) > 0) {
        foreach ($wishlistItems as $productId) {
            // Check if the product is already in the cart
            $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $existingCartItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingCartItem) {
                // If product exists, update quantity
                $newQuantity = $existingCartItem['quantity'] + 1;
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $existingCartItem['id']]);
            } else {
                // If product doesn't exist, add new item to cart
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $productId, 1]);
            }
        }

        // Empty the wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    $conn->commit();
    $_SESSION['notification'] = [
        'message' => 'All items have been added to your cart and your wishlist has been emptied.',
        'type' => 'success'
    ];
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['notification'] = [
        'message' => 'An error occurred while adding items to cart.',
        'type' => 'error'
    ];
}

header("Location: cart.php");
exit();
?>
