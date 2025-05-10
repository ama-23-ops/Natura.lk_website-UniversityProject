<?php
session_start();
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['firebase_uid'])) {
    $firebaseUid = $_POST['firebase_uid'];
    $name = $_POST['name'] ?? 'User';  // Provide a default name if none is given
    $email = $_POST['email'] ?? '';    // Provide a default empty email if none is given

    // Generate a unique username that doesn't exceed 15 characters
    $baseUsername = strtolower(str_replace(' ', '', $name));
    $username = substr($baseUsername, 0, 10); // Truncate to 10 characters
    $randomNumber = rand(1000, 9999);
    $username = $username . $randomNumber;

    // Truncate again if needed, in case rand adds too much
    $username = substr($username, 0, 15);


    // Check if the user exists in the users table by firebase_uid
    $stmt = $conn->prepare("SELECT * FROM users WHERE firebase_uid = ?");
    $stmt->execute([$firebaseUid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if user account is disabled
        if ($user['status'] === 'disabled') {
            echo json_encode(['success' => false, 'message' => 'Your account has been disabled. Please contact support.']);
            exit;
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];

        // Include transfer products functionality
        define('INCLUDED', true);
        include_once 'includes/transfer_products_to_db.php';

        $redirectUrl = $user['is_admin'] ? 'admin/dashboard.php' : 'customer/dashboard.php';

        echo json_encode([
            'success' => true, 
            'redirect_url' => $redirectUrl,
            'user_id' => $user['id']
        ]);
    } else {

        // Generate a random password for the user, this is required because it is not using firebase for password management
         $randomPassword = bin2hex(random_bytes(8));
         $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);


        // User does not exist in the database, create a user
       try {
            $stmt = $conn->prepare("INSERT INTO users (firebase_uid, name, email, username, password, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$firebaseUid, $name, $email, $username, $hashedPassword]);

            // Get the new user ID
            $userId = $conn->lastInsertId();

           // Set session variables
           $_SESSION['user_id'] = $userId;
           $_SESSION['is_admin'] = false;

           // Include transfer products functionality
           define('INCLUDED', true);
           include_once 'includes/transfer_products_to_db.php';

            echo json_encode([
                'success' => true, 
                'redirect_url' => 'customer/dashboard.php',
                'user_id' => $userId
            ]);

        } catch (PDOException $e) {
             echo json_encode(['success' => false, 'message' => 'Error creating user: ' . $e->getMessage()]);
        }

    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

?>