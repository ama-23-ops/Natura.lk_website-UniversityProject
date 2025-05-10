<?php
session_start();
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            <!-- Registration form container -->
            <div class="bg-white rounded-card shadow-card p-form-padding w-full max-w-md">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-semibold text-gray-800 mb-2">Create an Account</h1>
                    <p class="text-gray-600">Please fill in your details to register</p>
                </div>
                
                <form id="registerForm" class="space-y-4">
                    <div>
                        <input 
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-colors text-gray-700"
                            id="name" 
                            name="name" 
                            type="text" 
                            placeholder="Your Name" 
                            required
                        >
                    </div>
                    
                    <div>
                        <input 
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-colors text-gray-700"
                            id="email" 
                            name="email" 
                            type="email" 
                            placeholder="Email address" 
                            required
                        >
                    </div>
                    
                    <div>
                        <input 
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-colors text-gray-700"
                            id="password" 
                            name="password" 
                            type="password" 
                            placeholder="Password" 
                            required
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-green-500 text-white py-3 rounded-lg font-medium hover:bg-green-600 transition-colors"
                    >
                        Create Account
                    </button>
                </form>

                <p id="registrationMessage" class="mt-4 text-center text-sm"></p>
                
                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Already have an account? 
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
        const auth = firebase.auth();

        const registerForm = document.getElementById('registerForm');
        const registrationMessage = document.getElementById('registrationMessage');

        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Create user with email and password using Firebase
            auth.createUserWithEmailAndPassword(email, password)
                .then((userCredential) => {
                    // User created successfully
                    const user = userCredential.user;

                    // Send email verification
                    user.sendEmailVerification()
                        .then(() => {
                            // Verification email sent
                            registrationMessage.innerHTML = `<p class="text-green-500">Registration successful! Please check your email to verify your account.</p>`;

                            // Call your PHP script to add user details to the database
                            fetch('add_user.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: 'name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email) + '&firebase_uid=' + user.uid
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // User added to database
                                        // You can add further actions if needed
                                    } else {
                                        // Error adding user to database
                                        registrationMessage.innerHTML += `<p class="text-red-500">${data.message}</p>`;
                                    }
                                })
                                .catch((error) => {
                                    console.error('Error adding user to database:', error);
                                    registrationMessage.innerHTML += `<p class="text-red-500">Error adding user to database.</p>`;
                                });

                        })
                        .catch((error) => {
                            // Error sending verification email
                            registrationMessage.innerHTML = `<p class="text-red-500">Error sending verification email: ${error.message}</p>`;
                        });
                })
                .catch((error) => {
                    // Handle Errors here
                    let errorMessage = "";

                    switch (error.code) {
                        case 'auth/email-already-in-use':
                            errorMessage = "This email is already in use.";
                            break;
                        case 'auth/invalid-email':
                            errorMessage = "Please enter a valid email address.";
                            break;
                        case 'auth/weak-password':
                            errorMessage = "The password is too weak.";
                            break;
                        default:
                            errorMessage = error.message;
                    }
                    registrationMessage.innerHTML = `<p class="text-red-500">${errorMessage}</p>`;
                });
        });
    </script>
</body>

</html>