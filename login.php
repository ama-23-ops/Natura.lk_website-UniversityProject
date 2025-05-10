<?php
session_start();
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Add Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.x.x/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.x.x/firebase-auth-compat.js"></script>
    <!-- Include Firebase config -->
    <script src="firebase/firebase-config.js"></script>
    <!-- Include notification.js -->
    <script src="assets/js/notification.js"></script>
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

        #googleSignInBtn:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
        }
    </style>
</head>

<body class="bg-gray-50 font-poppins flex flex-col">
    <div class="content-container flex flex-col flex-grow">
        <?php include 'includes/header.php'; ?>
        
        <!-- Main content -->
        <div class="flex-grow flex items-center justify-center px-4 py-12 relative mt-6">
            <!-- Login form container -->
            <div class="bg-white rounded-card shadow-card p-form-padding w-full max-w-md">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-semibold text-gray-800 mb-2">Login to Your Account</h1>
                    <p class="text-gray-600">Welcome back! Please enter your details</p>
                </div>
                <form method="post" id="loginForm" class="space-y-4">
                    <div>
                        <input class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-colors text-gray-700" id="email" name="email" type="email" placeholder="Email address" required>
                    </div>
                    <div>
                        <input class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-colors text-gray-700" id="password" name="password" type="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="w-full bg-green-500 text-white py-3 rounded-lg font-medium hover:bg-green-600 transition-colors">
                        Sign In
                    </button>
                    <button type="button" id="googleSignInBtn" class="w-full mt-3 bg-white border border-gray-200 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                        <i class="fab fa-google"></i> Continue with Google
                    </button>
                </form>
                <a href="reset_password.php" class="block mt-6 text-center text-green-500 hover:text-green-600">
                    Forgot password?
                </a>
                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Don't have an account?
                        <a href="register.php" class="text-green-500 hover:text-green-600 font-medium">Sign up</a>
                    </p>
                </div>
                <p id="loginMessage" class="mt-4 text-center text-sm"></p>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script>
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);

        const auth = firebase.auth();

        const googleSignInBtn = document.getElementById('googleSignInBtn');
        const loginMessage = document.getElementById('loginMessage');
        const loginForm = document.getElementById('loginForm');

        //Sign in with google
        googleSignInBtn.addEventListener('click', function() {
            const provider = new firebase.auth.GoogleAuthProvider();

            firebase.auth().signInWithPopup(provider)
                .then((result) => {
                    // User signed in successfully.
                    // Get the firebase UID, name and email
                    var user = result.user;
                    var firebaseUid = user.uid;
                    var name = user.displayName;
                    var email = user.email;

                    // Check if the user exists in the users table
                    fetch('firebase_auth_check.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'firebase_uid=' + firebaseUid + '&name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email),
                        }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Set session variables if user is valid
                                window.location.href = data.redirect_url;
                            } else {
                                // User not found, disabled, or error
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error checking user:', error);
                            showNotification('Error checking user: ' + error.message, 'error');
                        });
                })
                .catch((error) => {
                    console.error('Google sign-in error:', error);
                    showNotification('Google sign-in error: ' + error.message, 'error');
                });

        });

        //Sign in with email and password
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Sign in with email and password
            auth.signInWithEmailAndPassword(email, password)
                .then((userCredential) => {
                    // Check if the user's email is verified
                    if (userCredential.user.emailVerified) {
                        // User is verified, check if user exists in the users table using firebase UID
                        fetch('firebase_auth_check.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: 'firebase_uid=' + userCredential.user.uid,
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Redirect user to the appropriate dashboard
                                    window.location.href = data.redirect_url;
                                } else {
                                    // Handle the case where user is disabled or not found
                                    showNotification(data.message, 'error');
                                }
                            })
                            .catch((error) => {
                                console.error('Error checking user:', error);
                                showNotification('Error checking user', 'error');
                            });
                    } else {
                        // User's email is not verified
                        showNotification('Please verify your email before logging in.', 'warning');
                        auth.signOut(); // Sign out the unverified user
                    }
                })
                .catch((error) => {
                    // Handle login errors
                    console.error('Login error:', error);
                    let errorMessage = "";

                    switch (error.code) {
                        case 'auth/invalid-email':
                            errorMessage += "Please enter a valid email address.";
                            break;
                        case 'auth/user-disabled':
                            errorMessage += "This user account has been disabled.";
                            break;
                        case 'auth/user-not-found':
                            errorMessage += "There is no user record corresponding to the provided identifier.";
                            break;
                        case 'auth/wrong-password':
                            errorMessage += "Invalid password.";
                            break;
                        case 'auth/invalid-login-credentials':
                            errorMessage += "Invalid login credentials.";
                            break;
                        case 'auth/too-many-requests':
                            errorMessage += "Access to this account has been temporarily disabled due to many failed login attempts. You can immediately restore it by resetting your password or you can try again later.";
                            break;
                        default:
                            errorMessage += error.message;
                    }
                    showNotification(errorMessage, 'error');
                });
        });
    </script>
</body>

</html>
