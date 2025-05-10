<?php
session_start();
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>
    <script src="firebase/firebase-config.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50  min-h-screen bg-[url('assets/images/bg.jpg')] bg-cover bg-center bg-no-repeat flex flex-col">
    <?php include 'includes/header.php'; ?>
    
    <!-- Dark overlay that covers entire page -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-10"></div>
    
    <!-- Main content -->
    <div class="flex-grow flex items-center justify-center px-4 py-12 relative z-20 mt-6">
        <!-- Verification status container -->
        <div class="bg-white rounded-card shadow-card p-form-padding w-full max-w-md relative z-10">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-gray-800 mb-2">Email Verification</h1>
                <p class="text-gray-600">Please verify your email address</p>
            </div>
            
            <div id="verificationMessage" class="text-center space-y-4">
                <!-- Verification message will be inserted here by JavaScript -->
            </div>

            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Back to 
                    <a href="login.php" class="text-green-500 hover:text-green-600 font-medium">Sign in</a>
                </p>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>

    <script>
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();
        const verificationMessage = document.getElementById('verificationMessage');

        auth.onAuthStateChanged(user => {
            if (user) {
                if (user.emailVerified) {
                    fetch('update_verification_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'firebase_uid=' + user.uid
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            verificationMessage.innerHTML = `
                                <p class="text-green-500">Your email has been verified successfully!</p>
                                <a href="login.php" class="inline-block w-full bg-green-500 text-white py-3 rounded-lg font-medium hover:bg-green-600 transition-colors mt-4">
                                    Proceed to Login
                                </a>`;
                        } else {
                            verificationMessage.innerHTML = `<p class="text-red-500">Error updating verification status: ${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error updating verification status:', error);
                        verificationMessage.innerHTML = `<p class="text-red-500">Error updating verification status.</p>`;
                    });
                } else {
                    verificationMessage.innerHTML = `
                        <p class="text-red-500 mb-4">Your email is not verified yet.</p>
                        <p class="text-gray-600">Please check your inbox for the verification email and click the link to verify your account.</p>`;
                }
            } else {
                verificationMessage.innerHTML = `
                    <p class="text-gray-600">No user is currently signed in.</p>
                    <a href="login.php" class="inline-block w-full bg-green-500 text-white py-3 rounded-lg font-medium hover:bg-green-600 transition-colors mt-4">
                        Go to Login
                    </a>`;
            }
        });
    </script>
</body>
</html>