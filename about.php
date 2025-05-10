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
            <h1 class="text-4xl font-bold text-teal-600 mb-8 flex items-center">
                <i class="fas fa-building mr-4"></i>
                About Us
            </h1>

            <div class="space-y-6 text-gray-600">
                <p class="leading-relaxed">
                    <i class="fas fa-star text-teal-600 mr-2"></i>
                    Welcome to Natura, your trusted destination for fresh, organic, and healthy food. We believe in nourishing people with nature’s best, offering high-quality organic fruits, vegetables, dairy, pantry staples, and more. Our mission is to promote a sustainable and healthier lifestyle by connecting customers with pure, chemical-free, and eco-friendly products.
                </p>

                <div class="grid md:grid-cols-2 gap-8 mt-8">
                    <div class="bg-gray-50 p-6 rounded-lg transform hover:-translate-y-1 transition-transform duration-300">
                        <div class="text-teal-600 text-4xl mb-4">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Our Mission</h3>
                        <ul class="list-disc list-inside">
                            <li class="marker:text-teal-600">To provide 100% organic, non-GMO, and chemical-free food to our customers.</li>
                            <li class="marker:text-teal-600">To support sustainable farming and ethical sourcing.</li>
                            <li class="marker:text-teal-600">To educate and encourage a healthier, eco-friendly lifestyle.</li>
                            <li class="marker:text-teal-600">To reduce our carbon footprint and promote environmental responsibility.</li>
                        </ul>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg transform hover:-translate-y-1 transition-transform duration-300">
                        <div class="text-teal-600 text-4xl mb-4">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Our Vision</h3>
                        <p>To become a leading organic food provider that inspires a healthier lifestyle while preserving the planet for future generations.</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-teal-600 mb-4 flex items-center">
                        <i class="fas fa-values mr-2"></i>
                        Our Values
                    </h2>
                    <ul class="grid md:grid-cols-3 gap-4">
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check-circle text-teal-600 mt-1"></i>
                            <span>Purity</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check-circle text-teal-600 mt-1"></i>
                            <span>Sustainability</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-check-circle text-teal-600 mt-1"></i>
                            <span>Health & Wellness</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
