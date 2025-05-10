<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit();
}

include_once '../db.php';

$message = '';
$messageType = '';

// Handle blog actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $blogId = $_POST['blog_id'] ?? 0;
    
    switch ($_POST['action']) {
        case 'approve':
            $stmt = $conn->prepare("UPDATE blogs SET status = 'approved' WHERE id = ?");
            if ($stmt->execute([$blogId])) {
                $message = "Blog approved successfully.";
                $messageType = "success";
            } else {
                $message = "Error updating blog status.";
                $messageType = "error";
            }
            break;
            
        case 'reject':
            $stmt = $conn->prepare("UPDATE blogs SET status = 'rejected' WHERE id = ?");
            if ($stmt->execute([$blogId])) {
                $message = "Blog rejected successfully.";
                $messageType = "success";
            } else {
                $message = "Error updating blog status.";
                $messageType = "error";
            }
            break;
            
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
            if ($stmt->execute([$blogId])) {
                $message = "Blog deleted successfully.";
                $messageType = "success";
            } else {
                $message = "Error deleting blog.";
                $messageType = "error";
            }
            break;
    }
}

// Pagination settings
$limit = 9; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Base query
$query = "SELECT b.*, u.name as author_name FROM blogs b LEFT JOIN users u ON b.user_id = u.id WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM blogs WHERE 1=1";
$params = [];

// Add search condition
if (!empty($search)) {
    $searchTerm = "%$search%";
    $query .= " AND (b.title LIKE :search1 OR b.content LIKE :search2)";
    $countQuery .= " AND (title LIKE :search1 OR content LIKE :search2)";
    $params[':search1'] = $searchTerm;
    $params[':search2'] = $searchTerm;
}

// Add status filter
if (!empty($status_filter)) {
    $query .= " AND b.status = :status";
    $countQuery .= " AND status = :status";
    $params[':status'] = $status_filter;
}

// Get total records and statistics
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Get blog statistics
$statsStmt = $conn->query("
    SELECT 
        COUNT(*) as total_blogs,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_blogs,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_blogs
    FROM blogs
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Add pagination
$query .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);

// Bind all parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Bind pagination parameters
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'BLOG MANAGEMENT';
ob_start();

// Add CSS for text truncation before including the blogs table
?>
<style>
    .truncate-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .truncate-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .blog-content {
        min-height: 72px; /* Ensure consistent height for content area */
    }
</style>
<?php
// Include the blogs table component
include 'includes/blogs_table.php';

$content = ob_get_clean();
include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/dashboard_layout.php';
?>
