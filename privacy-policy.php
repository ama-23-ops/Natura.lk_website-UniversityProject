<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

<main class="relative min-h-screen pt-20">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <img src="/assets/images/background.jpg" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 container mx-auto px-4 py-16">
        <div class="bg-white rounded-lg shadow-xl p-8 max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold text-teal-600 mb-8 flex items-center justify-center">
                <i class="fas fa-shield-alt mr-4"></i>
                Privacy Policy
            </h1>

            <div class="space-y-6 text-gray-600">
                <div class="bg-gray-50 p-6 rounded-lg transform hover:-translate-y-1 transition-transform duration-300">
                    <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-user-shield text-teal-600 mr-2"></i>
                        Information We Collect
                    </h2>
                    <p class="leading-relaxed">
                        We collect information you provide directly to us, including name, email address, shipping address, and payment information when you make a purchase. We also automatically collect certain information about your device when you use our website.
                    </p>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg transform hover:-translate-y-1 transition-transform duration-300">
                    <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-tasks text-teal-600 mr-2"></i>
                        How We Use Your Information
                    </h2>
                    <p class="leading-relaxed">
                        We use the information we collect to process your orders, send you order confirmations, provide customer support, and send you marketing communications (with your consent).
                    </p>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg transform hover:-translate-y-1 transition-transform duration-300">
                    <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-share-alt text-teal-600 mr-2"></i>
                        Information Sharing
                    </h2>
                    <p class="leading-relaxed">
                        We do not sell your personal information. We share your information only with service providers who assist in our operations, such as payment processors and shipping companies.
                    </p>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg transform hover:-translate-y-1 transition-transform duration-300">
                    <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-cookie text-teal-600 mr-2"></i>
                        Cookies and Tracking
                    </h2>
                    <p class="leading-relaxed">
                        We use cookies and similar tracking technologies to track activity on our website and hold certain information to improve your shopping experience.
                    </p>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg transform hover:-translate-y-1 transition-transform duration-300">
                    <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-lock text-teal-600 mr-2"></i>
                        Data Security
                    </h2>
                    <p class="leading-relaxed">
                        We implement appropriate security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction.
                    </p>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg transform hover:-translate-y-1 transition-transform duration-300">
                    <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-envelope text-teal-600 mr-2"></i>
                        Contact Us
                    </h2>
                    <p class="leading-relaxed">
                        If you have any questions about our Privacy Policy, please contact us at info@natura.com or visit our contact page.
                    </p>
                </div>

                <div class="text-sm text-gray-500 mt-8">
                    <p class="mb-2">Last updated: <?php echo date("F d, Y"); ?></p>
                    <p>This privacy policy is subject to change without notice and was last updated on the date specified above.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
