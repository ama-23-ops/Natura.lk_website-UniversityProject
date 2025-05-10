<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$userId = $_SESSION['user_id'];

// Handle inquiry submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_inquiry'])) {
    $email = $_POST['email'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO inquiries (user_id, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $email, $message]);

    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">Your inquiry has been submitted successfully!</div>';
}

// Company contact information (replace with your actual details)
$companyInfo = [
    'address' => '123 Main Street, City, Country',
    'email' => 'info@example.com',
    'phone' => '+1 234 567 890',
    'map_url' => 'https://www.google.com/maps/embed?...' 
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php'; ?>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Contact Us</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Contact Form -->
            <div>
                <h2 class="text-xl font-semibold mb-2">Send Us a Message</h2>
                <form method="post">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Your Email:</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="email" name="email" id="email" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="message">Message:</label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="message" id="message" rows="5" required></textarea>
                    </div>
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="submit_inquiry">Submit Inquiry</button>
                </form>
            </div>

            <!-- Company Information -->
            <div>
            <h2 class="text-xl font-semibold mb-2">Our Contact Information</h2>
                <p><strong>Address:</strong> <?= $companyInfo['address'] ?></p>
                <p><strong>Email:</strong> <?= $companyInfo['email'] ?></p>
                <p><strong>Phone:</strong> <?= $companyInfo['phone'] ?></p>
                <div class="mt-4">
                    <!-- Replace the `src` attribute with your actual map URL -->
                    <iframe src="<?= $companyInfo['map_url'] ?>" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
</body>
</html>