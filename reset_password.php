<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>
    <script src="firebase/firebase-config.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            position: relative;
            background-image: url('/assets/images/bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .content-container {
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body class="bg-gray-50 font-poppins flex flex-col">
    <div class="content-container flex flex-col flex-grow">
        <?php include 'includes/header.php'; ?>
        
        <!-- Main content -->
        <div class="flex-grow flex items-center justify-center px-4 py-12 relative mt-6">
            <!-- Reset password form container -->
            <div class="bg-white rounded-card shadow-card p-form-padding w-full max-w-md">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-semibold text-gray-800 mb-2">Reset Password</h1>
                    <p class="text-gray-600">Enter your email to reset your password</p>
                </div>
                
                <form id="resetForm" class="space-y-4">
                    <div>
                        <input 
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-colors text-gray-700"
                            id="email" 
                            type="email" 
                            placeholder="Email address" 
                            required
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-green-500 text-white py-3 rounded-lg font-medium hover:bg-green-600 transition-colors"
                        onclick="sendPasswordResetEmail(event)"
                    >
                        Send Reset Link
                    </button>
                </form>

                <p id="resetPasswordMessage" class="mt-4 text-center text-sm"></p>
                
                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Remember your password? 
                        <a href="login.php" class="text-green-500 hover:text-green-600 font-medium">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>

<script>
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    function sendPasswordResetEmail(e) {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const resetPasswordMessage = document.getElementById('resetPasswordMessage');

        firebase.auth().sendPasswordResetEmail(email)
            .then(() => {
                resetPasswordMessage.innerHTML = '<p class="text-green-500">Password reset email sent. Please check your inbox.</p>';
            })
            .catch((error) => {
                let errorMessage = "";

                switch (error.code) {
                    case 'auth/invalid-email':
                        errorMessage = "Please enter a valid email address.";
                        break;
                    case 'auth/user-not-found':
                        errorMessage = "There is no user record corresponding to the provided identifier.";
                        break;
                    case 'auth/missing-email':
                        errorMessage = "Please enter your email address.";
                        break;
                    default:
                        errorMessage = error.message;
                }
                resetPasswordMessage.innerHTML = `<p class="text-red-500">${errorMessage}</p>`;
            });
    }
</script>
</body>
</html>