<?php
session_start();
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['firebase_uid'])) {
    $firebaseUid = $_POST['firebase_uid'];

    try {
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE firebase_uid = ?");
        $stmt->execute([$firebaseUid]);

        echo json_encode(['success' => true, 'message' => 'User verification status updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating verification status: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>