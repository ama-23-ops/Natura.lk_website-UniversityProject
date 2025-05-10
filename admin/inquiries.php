<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: " . $_SERVER['DOCUMENT_ROOT'] . "/login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Handle POST request for responding to inquiries
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inquiry_id'])) {
    try {
        $inquiry_id = $_POST['inquiry_id'];
        $response = $_POST['response'];
        
        $stmt = $conn->prepare("UPDATE inquiries SET status = 'responded', admin_response = :response, response_date = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute([
            ':response' => $response,
            ':id' => $inquiry_id
        ]);
        
        $_SESSION['success'] = "Response sent successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error sending response: " . $e->getMessage();
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Mark inquiry as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    try {
        $stmt = $conn->prepare("UPDATE inquiries SET status = 'read' WHERE id = :id AND status = 'new'");
        $stmt->execute([':id' => $_GET['mark_read']]);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating status: " . $e->getMessage();
    }
}

// Pagination settings
$limit = 10; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Base query
$query = "SELECT i.*, u.name as user_name 
          FROM inquiries i 
          LEFT JOIN users u ON i.user_id = u.id 
          WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total 
               FROM inquiries i 
               LEFT JOIN users u ON i.user_id = u.id 
               WHERE 1=1";
$params = [];

// Add search condition
if (!empty($search)) {
    $searchTerm = "%$search%";
    $query .= " AND (i.name LIKE :search1 OR i.email LIKE :search2 OR i.message LIKE :search3)";
    $countQuery .= " AND (i.name LIKE :search1 OR i.email LIKE :search2 OR i.message LIKE :search3)";
    $params[':search1'] = $searchTerm;
    $params[':search2'] = $searchTerm;
    $params[':search3'] = $searchTerm;
}

// Add status filter
if (!empty($status_filter)) {
    $query .= " AND i.status = :status";
    $countQuery .= " AND i.status = :status";
    $params[':status'] = $status_filter;
}

// Get total records for pagination
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Get inquiry statistics
$statsStmt = $conn->query("
    SELECT 
        COUNT(*) as total_inquiries,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_inquiries,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_inquiries,
        SUM(CASE WHEN status = 'responded' THEN 1 ELSE 0 END) as responded_inquiries
    FROM inquiries
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Add sorting and pagination
$query .= " ORDER BY FIELD(i.status, 'new', 'read', 'responded'), i.inquiry_date DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);

// Bind all parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Bind pagination parameters
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

// Set message type based on session
$message = '';
$messageType = '';
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $messageType = 'success';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $messageType = 'error';
    unset($_SESSION['error']);
}

$stmt->execute();
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'MANAGE INQUIRIES';

ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/inquiries_table.php';
$content = ob_get_clean();

include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/dashboard_layout.php';
?>
