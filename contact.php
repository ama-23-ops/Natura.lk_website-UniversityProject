<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

<main class="relative min-h-screen pt-20">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <img src="/assets/images/background.jpg" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 container mx-auto px-4 py-16 ">
        <div class="bg-white rounded-lg shadow-xl p-8 max-w-5xl mx-auto">
            <h1 class="text-4xl font-bold text-teal-600 mb-8 flex items-center">
                <i class="fas fa-envelope mr-4"></i>
                Contact Us
            </h1>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Contact Information -->
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="text-teal-600 text-xl mt-1">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Our Location</h3>
                            <p class="text-gray-600">123 Business Street, City, Country</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="text-teal-600 text-xl mt-1">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Phone</h3>
                            <p class="text-gray-600">+1 234 567 890</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="text-teal-600 text-xl mt-1">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Email</h3>
                            <p class="text-gray-600">contact@example.com</p>
                        </div>
                    </div>

    <!-- Contact Form -->
                    <form class="mt-8 space-y-4" method="POST">
                        <?php
                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            try {
                                require_once 'db.php';
                                
                                $name = htmlspecialchars(trim($_POST['name'] ?? ''));
                                $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
                                $message = htmlspecialchars(trim($_POST['message'] ?? ''));
                                
                                $errors = [];
                                if (empty($name)) {
                                    $errors[] = "Name is required";
                                } elseif (strlen($name) > 50) {
                                    $errors[] = "Name must be less than 50 characters";
                                }
                                
                                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    $errors[] = "Valid email is required";
                                }
                                
                                if (empty($message)) {
                                    $errors[] = "Message is required";
                                }

                                if (empty($errors)) {
                                    $sql = "INSERT INTO inquiries (name, email, message) VALUES (:name, :email, :message)";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                                    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
                                    
                                    if ($stmt->execute()) {
                                        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                                <strong class="font-bold">Success!</strong>
                                                <span class="block sm:inline"> Thank you for contacting us. We will get back to you soon.</span>
                                            </div>';
                                        // Clear the form on success
                                        $_POST = array();
                                    } else {
                                        throw new Exception("Failed to save inquiry");
                                    }
                                    $stmt = null;
                                } else {
                                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                            <strong class="font-bold">Please fix the following:</strong>
                                            <ul class="list-disc ml-5 mt-1">';
                                    foreach ($errors as $error) {
                                        echo '<li>' . $error . '</li>';
                                    }
                                    echo '</ul></div>';
                                }
                            } catch (Exception $e) {
                                error_log($e->getMessage());
                                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                        <strong class="font-bold">Error!</strong>
                                        <span class="block sm:inline"> An error occurred. Please try again later.</span>
                                    </div>';
                            } finally {
                                $conn = null;
                            }
                        }
                        ?>
                        <div>
                            <label class="block text-gray-700 mb-2" for="name">
                                <i class="fas fa-user text-teal-600 mr-2"></i>Name
                            </label>
                            <input type="text" id="name" name="name" required maxlength="50" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-600" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="email">
                                <i class="fas fa-envelope text-teal-600 mr-2"></i>Email
                            </label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-600" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2" for="message">
                                <i class="fas fa-comment text-teal-600 mr-2"></i>Message
                            </label>
                            <textarea id="message" name="message" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-600"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="bg-teal-600 text-white px-6 py-3 rounded-lg hover:bg-teal-700 transition-colors duration-300 flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Map -->
                <div class="h-[500px] rounded-lg overflow-hidden shadow-lg">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15843.339451467528!2d79.85256532466377!3d6.906915243279721!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae25943a7cc3353%3A0x3ceeed1a8ee7f236!2sColombo%2007%2C%20Colombo%2C%20Sri%20Lanka!5e0!3m2!1sen!2sus!4v1690304876235!5m2!1sen!2sus" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
