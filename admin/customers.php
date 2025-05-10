<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}
include_once '../db.php';

// Handle delete or disable
if (isset($_GET['action']) && isset($_GET['id'])) {
  $action = $_GET['action'];
  $id = $_GET['id'];

  if ($action == 'delete') {
    // Delete the user (soft delete by setting is_active to false is recommended)
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    // Consider deleting related data or setting up foreign key constraints for cascading deletes
  } elseif ($action == 'disable') {
    // Disable the user
    $stmt = $conn->prepare("UPDATE users SET status = 'disabled' WHERE id = ?");
    $stmt->execute([$id]);
  } elseif ($action == 'enable') {
    // Enable the user
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $stmt->execute([$id]);
  }

  header("Location: customers.php");
  exit();
}
if (isset($_POST['update_role'])) {
  $userId = $_POST['user_id'];
  $isAdmin = isset($_POST['is_admin']) ? 1 : 0;

  $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
  $stmt->execute([$isAdmin, $userId]);

  header("Location: customers.php");
  exit();
}

// Pagination settings
$limit = 12; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

// Base query
$query = "SELECT * FROM users WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM users WHERE 1=1";
$params = [];

// Add search condition
if (!empty($search)) {
    $searchTerm = "%$search%";
    $query .= " AND (name LIKE :search1 OR email LIKE :search2 OR username LIKE :search3 OR contact_number LIKE :search4)";
    $countQuery .= " AND (name LIKE :search1 OR email LIKE :search2 OR username LIKE :search3 OR contact_number LIKE :search4)";
    $params[':search1'] = $searchTerm;
    $params[':search2'] = $searchTerm;
    $params[':search3'] = $searchTerm;
    $params[':search4'] = $searchTerm;
}

// Add status filter
if (!empty($status_filter)) {
    $query .= " AND status = :status";
    $countQuery .= " AND status = :status";
    $params[':status'] = $status_filter;
}

// Add role filter
if ($role_filter !== '') {
    $query .= " AND is_admin = :role";
    $countQuery .= " AND is_admin = :role";
    $params[':role'] = $role_filter;
}

// Get total records for pagination
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Add pagination
$query .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);

// Bind the search and filter parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Bind pagination parameters explicitly as integers
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'MANAGE CUSTOMERS';

ob_start();
include 'includes/customers_table.php';
$content = ob_get_clean();

include 'includes/dashboard_layout.php';
?>
