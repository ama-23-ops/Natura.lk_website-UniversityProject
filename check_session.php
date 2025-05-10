<?php
session_start();
header('Content-Type: application/json');

// Return user ID if exists, otherwise null
$response = [
    'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
    'is_admin' => isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false
];

echo json_encode($response);
exit;
?>