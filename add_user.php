<?php
session_start();
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firebaseUid = $_POST['firebase_uid'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = strtolower(str_replace(' ', '', $name)) . rand(1000, 9999);

    // Check if the user already exists in the users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE firebase_uid = ?");
    $stmt->execute([$firebaseUid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['success' => false, 'message' => 'User already exists.']);
        exit();
    }

    try {
        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO users (firebase_uid, name, email, username) VALUES (?, ?, ?, ?)");
        $stmt->execute([$firebaseUid, $name, $email, $username]);

        echo json_encode(['success' => true, 'message' => 'User added successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error adding user: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>