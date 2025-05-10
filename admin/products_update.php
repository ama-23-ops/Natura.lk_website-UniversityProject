<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}
include_once '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $productId = $_POST['product_id'];
    $title = $_POST['title'];
    $purchase_cost = $_POST['purchase_cost'];
    $sale_price = $_POST['sale_price'];
    $category = $_POST['category'];
    $stock_quantity = $_POST['stock_quantity'];
    $details = $_POST['details'];
    $discount = $_POST['discount'] ?? 0;

    // Handle image update
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Create uploads directory if it doesn't exist
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
        if (!file_exists($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $fileExtension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $uniqueFilename = uniqid('product_') . '.' . $fileExtension;
        $targetFile = $uploadDir . $uniqueFilename;

        // Delete old image if it exists
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $oldImage = $stmt->fetch(PDO::FETCH_ASSOC)['image'];
        if ($oldImage && file_exists($uploadDir . $oldImage)) {
            unlink($uploadDir . $oldImage);
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image = $uniqueFilename; // Store filename only
        } else {
            // Keep old image if upload fails
            $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC)['image'];
        }
    } else {
        // Keep the old image if no new image is uploaded
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC)['image'];
    }

    $stmt = $conn->prepare("UPDATE products SET title = ?, purchase_cost = ?, sale_price = ?, category = ?, stock_quantity = ?, details = ?, discount = ?, image = ? WHERE id = ?");
    $stmt->execute([$title, $purchase_cost, $sale_price, $category, $stock_quantity, $details, $discount, $image, $productId]);

    header("Location: products.php");
    exit();
} else {
    header("Location: products.php");
    exit();
}
?>