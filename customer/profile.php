<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/scripts.php';
$userId = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$profileMessage = '';
$errorMessage = ''; // General error message

// Check for session messages
if (isset($_SESSION['profileMessage'])) {
    $profileMessage = $_SESSION['profileMessage'];
    unset($_SESSION['profileMessage']);
}

if (isset($_SESSION['errorMessage'])) {
    $errorMessage = $_SESSION['errorMessage'];
    unset($_SESSION['errorMessage']);
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contactNumber = $_POST['contact_number'];
    $address = $_POST['address'];  // Add this line

    // Validation (example)
    if (empty($name) || empty($email) || empty($contactNumber) || empty($address)) {  // Modified validation
        $_SESSION['errorMessage'] = "All fields are required.";
    } else {
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            // Create uploads directory if it doesn't exist
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $fileExtension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
            $uniqueFilename = uniqid('profile_') . '.' . $fileExtension;
            $targetFile = $uploadDir . $uniqueFilename;

            // Delete old profile picture if exists
            if (!empty($user['profile_picture'])) {
                $oldFile = $_SERVER['DOCUMENT_ROOT'] . parse_url($user['profile_picture'], PHP_URL_PATH);
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            // Move uploaded file
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
                $profilePicture = "/uploads/" . $uniqueFilename; // Store relative path in database
            } else {
                $_SESSION['errorMessage'] = "Failed to upload image.";
                $profilePicture = $user['profile_picture'];
            }
        } else {
            $profilePicture = $user['profile_picture'];
        }

        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, contact_number = ?, profile_picture = ?, address = ? WHERE id = ?");  // Modified query
        $stmt->execute([$name, $email, $contactNumber, $profilePicture, $address, $userId]);  // Modified execute

        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['profileMessage'] = 'Profile updated successfully!';
        $_SESSION['profileMessageType'] = 'success'; // Add message type
    }
    header("Location: profile.php"); // Redirect to refresh the page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>
    <script src="/firebase/firebase-config.js"></script>
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #00796b 0%, #009688 100%);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        .profile-picture {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
    <script src="/assets/js/validation.js"></script>
    <script src="/assets/js/notification.js"></script>
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('profilePicturePreview');
                output.src = reader.result;
                output.classList.remove('hidden');
                document.getElementById('profileIcon').classList.add('hidden');
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php'; ?>
    <div class="container mx-auto p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Update Profile Form -->
            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Update Profile</h2>
                    <i class="fas fa-edit text-teal-600 text-2xl"></i>
                </div>
                <form method="post" enctype="multipart/form-data" onsubmit="return validateProfileForm()">
                    <div class="mb-4 text-center">
                        <?php if (!empty($user['profile_picture'])): ?>
                        <img id="profilePicturePreview" src="<?= $user['profile_picture'] ?>" alt="Profile Picture" class="profile-picture mx-auto">
                        <?php else: ?>
                        <i id="profileIcon" class="fas fa-user-circle text-6xl text-gray-300"></i>
                        <img id="profilePicturePreview" class="profile-picture mx-auto hidden">
                        <?php endif; ?>
                        <div class="mt-2">
                            <label for="profile_picture" class="cursor-pointer text-teal-600 hover:text-teal-800">
                                <i class="fas fa-pencil-alt"></i> Edit
                            </label>
                            <input type="file" name="profile_picture" id="profile_picture" class="hidden" onchange="previewImage(event)">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900" for="name">Name</label>
                        <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" 
                            type="text" name="name" id="name" value="<?= $user['name'] ?>" required oninput="validateName()">
                        <p id="nameError" class="text-red-500 text-xs italic"></p>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900" for="email">Email</label>
                        <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" 
                            type="email" name="email" id="email" value="<?= $user['email'] ?>" required oninput="validateEmailField()">
                        <p id="emailError" class="text-red-500 text-xs italic"></p>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900" for="contact_number">Contact Number</label>
                        <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" 
                            type="text" name="contact_number" id="contact_number" value="<?= $user['contact_number'] ?>" oninput="validateContactNumber()">
                        <p id="contactNumberError" class="text-red-500 text-xs italic"></p>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900" for="address">Address</label>
                        <textarea class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" 
                            name="address" id="address" rows="3" required oninput="validateAddress()"><?= $user['address'] ?></textarea>
                        <p id="addressError" class="text-red-500 text-xs italic"></p>
                    </div>
                    <button class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300 ease-in-out flex items-center justify-center"
                        type="submit" name="update_profile">
                        <i class="fas fa-save mr-2"></i>
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Password Reset Card -->
            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="text-center mb-8">
                    <div class="inline-block p-6 bg-teal-50 rounded-full mb-4 transform hover:scale-105 transition-transform duration-300">
                        <i class="fas fa-shield-alt text-teal-600 text-6xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800">Password Management</h2>
                </div>
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-info-circle text-teal-500 mr-2"></i>
                        <p class="text-gray-600">Your password should be:</p>
                    </div>
                    <ul class="text-sm text-gray-500 ml-6">
                        <li class="flex items-center mb-2">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            At least 8 characters long
                        </li>
                        <li class="flex items-center mb-2">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Include uppercase & lowercase letters
                        </li>
                        <li class="flex items-center mb-1">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Include numbers & special characters
                        </li>
                    </ul>
                </div>
                <div class="flex items-center mb-6 bg-blue-50 p-4 rounded-lg">
                    <i class="fas fa-envelope text-blue-500 mr-2"></i>
                    <p class="text-gray-600 text-sm">Click below to receive password reset instructions via email.</p>
                </div>
                <button onclick="sendPasswordReset()" class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300 ease-in-out flex items-center justify-center">
                    <i class="fas fa-key mr-2"></i>
                    Send Password Reset Email
                </button>
                <div id="passwordResetMessage" class="mt-4 text-sm"></div>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
    <script>
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();

        function sendPasswordReset() {
            const user = auth.currentUser;
            if (user) {
                auth.sendPasswordResetEmail(user.email)
                    .then(() => {
                        document.getElementById('passwordResetMessage').innerHTML = 
                            '<p class="text-green-500">Password reset email sent. Please check your inbox.</p>';
                    })
                    .catch((error) => {
                        document.getElementById('passwordResetMessage').innerHTML = 
                            `<p class="text-red-500">Error: ${error.message}</p>`;
                    });
            } else {
                document.getElementById('passwordResetMessage').innerHTML = 
                    '<p class="text-red-500">You must be signed in to reset your password.</p>';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php
            if (!empty($errorMessage)) {
                echo "showNotification('" . htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') . "', 'error');";
            }
            if (!empty($profileMessage)) {
                echo "showNotification('" . htmlspecialchars($profileMessage, ENT_QUOTES, 'UTF-8') . "', '" . (isset($_SESSION['profileMessageType']) ? $_SESSION['profileMessageType'] : 'info') . "');";
                unset($_SESSION['profileMessageType']);
            }
            ?>
        });
    </script>
</body>
</html>