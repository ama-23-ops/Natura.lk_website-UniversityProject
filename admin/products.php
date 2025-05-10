<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
  header("Location: " . $_SERVER['DOCUMENT_ROOT'] . "/login.php");
  exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Handle product deletion
if (isset($_GET['delete_id'])) {
  $deleteId = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
  $stmt->execute([$deleteId]);
  header("Location: products.php");
  exit();
}

// Handle product disabling
if (isset($_GET['disable_id'])) {
  $disableId = $_GET['disable_id'];
  $stmt = $conn->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
  $stmt->execute([$disableId]);
  header("Location: products.php");
  exit();
}

// Handle product enabling
if (isset($_GET['enable_id'])) {
  $enableId = $_GET['enable_id'];
  $stmt = $conn->prepare("UPDATE products SET is_active = 1 WHERE id = ?");
  $stmt->execute([$enableId]);
  header("Location: products.php");
  exit();
}

// Pagination settings
$limit = 12; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Base query
$query = "SELECT * FROM products WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM products WHERE 1=1";
$params = [];

// Add search condition
if (!empty($search)) {
    $searchTerm = "%$search%";
    $query .= " AND (title LIKE :search1 OR category LIKE :search2)";
    $countQuery .= " AND (title LIKE :search1 OR category LIKE :search2)";
    $params[':search1'] = $searchTerm;
    $params[':search2'] = $searchTerm;
}

// Add status filter
if (!empty($status_filter)) {
    $isActive = $status_filter === 'active' ? 1 : 0;
    $query .= " AND is_active = :status";
    $countQuery .= " AND is_active = :status";
    $params[':status'] = $isActive;
}

// Add category filter
if (!empty($category_filter)) {
    $query .= " AND category = :category";
    $countQuery .= " AND category = :category";
    $params[':category'] = $category_filter;
}

// Get total records for pagination
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Add pagination
$query .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);

// Bind all parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Bind pagination parameters
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filter dropdown
$categoryStmt = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'MANAGE PRODUCTS';

ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/products_table.php';
$content = ob_get_clean();

include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/dashboard_layout.php';
?>
