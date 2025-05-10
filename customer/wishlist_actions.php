<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

function add_to_wishlist($conn, $user_id, $product_id) {
    $sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $product_id]);
    return get_wishlist_count($conn, $user_id);
}

function remove_from_wishlist($conn, $user_id, $product_id) {
    $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $product_id]);
    return get_wishlist_count($conn, $user_id);
}

function is_in_wishlist($conn, $user_id, $product_id) {
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function get_wishlist_count($conn, $user_id) {
    $sql = "SELECT COUNT(*) as wishlist_count FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['wishlist_count'] ?? 0;
}

// Handle AJAX requests
if (isset($_POST['action']) && isset($_SESSION['user_id'])) {
    $action = $_POST['action'];
    $user_id = $_SESSION['user_id'];
    
    if($action === 'initial_check' && isset($_POST['product_ids'])) {
        $product_ids = json_decode($_POST['product_ids']);
        $wishlist_status = [];
        foreach ($product_ids as $product_id) {
            $wishlist_status[$product_id] = is_in_wishlist($conn, $user_id, $product_id);
        }
        echo json_encode([
            'status' => 'success',
            'wishlist_status' => $wishlist_status,
            'count' => get_wishlist_count($conn, $user_id)
        ]);
        exit;
    }
    
    if(isset($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        
        if ($action === 'add') {
            $count = add_to_wishlist($conn, $user_id, $product_id);
            echo json_encode(['status' => 'added', 'count' => $count]);
        } elseif ($action === 'remove') {
            $count = remove_from_wishlist($conn, $user_id, $product_id);
            echo json_encode(['status' => 'removed', 'count' => $count]);
        }
    }
    exit;
}
?>
