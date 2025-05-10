<?php
// Include database connection
require_once 'db.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Check if user is admin (you should implement proper authentication)
function isAdmin() {
    // Check session first
    if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return true;
    }
    // For testing only - remove in production!
    if (isset($_GET['admin']) && $_GET['admin'] === 'true') {
        return true;
    }
    return false;
}

// Function to send a message
function sendMessage($conn) {
    // Validate inputs
    if (!isset($_POST['content']) || !isset($_POST['sender']) || !isset($_POST['receiver'])) {
        return ['status' => 'error', 'message' => 'Missing required fields'];
    }
    
    $content = $_POST['content'];
    $sender = $_POST['sender'];
    $receiver = $_POST['receiver'];
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    
    // Validate sender and receiver values
    $valid_types = ['admin', 'user', 'guest'];
    if (!in_array($sender, $valid_types) || !in_array($receiver, $valid_types)) {
        return ['status' => 'error', 'message' => 'Invalid sender or receiver type'];
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO messages (content, sender, receiver, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$content, $sender, $receiver, $user_id]);
        
        return [
            'status' => 'success',
            'message' => 'Message sent successfully',
            'message_id' => $conn->lastInsertId()
        ];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Function to get user chats
function getUserChats($conn) {
    if (!isset($_GET['user_id'])) {
        return ['status' => 'error', 'message' => 'User ID is required'];
    }
    
    $user_id = $_GET['user_id'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC");
        $stmt->execute([$user_id]);
        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'status' => 'success',
            'chats' => $chats
        ];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Function to get all chats (admin only)
function getAllChats($conn) {
    if (!isAdmin()) {
        return ['status' => 'error', 'message' => 'Unauthorized access'];
    }
    
    try {
        $stmt = $conn->prepare("SELECT m.*, u.name as user_name 
                               FROM messages m 
                               LEFT JOIN users u ON m.user_id = u.id 
                               ORDER BY m.created_at DESC");
        $stmt->execute();
        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'status' => 'success',
            'chats' => $chats
        ];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Process the request
$response = [];

switch ($action) {
    case 'send':
        $response = sendMessage($conn);
        break;
    
    case 'getUserChats':
        $response = getUserChats($conn);
        break;
    
    case 'getAllChats':
        $response = getAllChats($conn);
        break;
    
    default:
        $response = ['status' => 'error', 'message' => 'Invalid action'];
        break;
}

// Return JSON response
echo json_encode($response);
exit;
?>