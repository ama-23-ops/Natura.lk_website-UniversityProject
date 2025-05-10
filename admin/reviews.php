<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: " . $_SERVER['DOCUMENT_ROOT'] . "/login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Fetch all reviews with user and product details
$stmt = $conn->query("SELECT r.*, u.name as user_name, p.title as product_title FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.review_date DESC");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'MANAGE REVIEWS';

ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/reviews_table.php';
$content = ob_get_clean();

include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/dashboard_layout.php';
?>
