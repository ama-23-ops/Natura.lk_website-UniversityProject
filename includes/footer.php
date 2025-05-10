<footer class="text-gray-600 relative z-10 bg-white">
    <div class="container mx-auto py-12 px-4">
        <div class="flex flex-col md:flex-row md:justify-between md:space-x-8 animate-fadeIn">
            <div class="mb-8 md:mb-0 md:w-1/3 transform hover:scale-105 transition-transform duration-300">
                <h3 class="text-xl font-bold mb-4 flex items-center text-gray-600">
                    <i class="fas fa-store-alt mr-2 text-gray-600 hover:text-teal-600"></i>
                    About Us
                </h3>
                <p class="text-gray-600 leading-relaxed  transition-colors duration-300 hover:text-teal-600">
                At Natura, we bring you the finest organic food, fresh produce, and sustainable products for a healthier lifestyle.
                </p>
            </div>
            <div class="mb-8 md:mb-0 md:w-1/3">
                <h3 class="text-xl font-bold mb-4 flex items-center text-gray-600">
<i class="fas fa-link mr-2 text-gray-600 hover:text-teal-600"></i>
                    Quick Links
                </h3>
                <ul class="space-y-2">
                    <li class="transform hover:translate-x-2 transition-transform duration-300">
                        <a href="/about.php" class="text-gray-600  transition-colors flex items-center hover:text-teal-600">
                            <i class="fas fa-angle-right mr-2 text-gray-600 hover:text-teal-600"></i>About
                        </a>
                    </li>
                    <li class="transform hover:translate-x-2 transition-transform duration-300">
                        <a href="/contact.php" class="text-gray-600  transition-colors flex items-center hover:text-teal-600">
                            <i class="fas fa-angle-right mr-2 text-gray-600 hover:text-teal-600"></i>Contact
                        </a>
                    </li>
                    <li class="transform hover:translate-x-2 transition-transform duration-300">
                        <a href="/faqs.php" class="text-gray-600  transition-colors flex items-center hover:text-teal-600">
                            <i class="fas fa-angle-right mr-2 text-gray-600 hover:text-teal-600"></i>FAQs
                        </a>
                    </li>
                    <li class="transform hover:translate-x-2 transition-transform duration-300">
                        <a href="/privacy-policy.php" class="text-gray-600  transition-colors flex items-center hover:text-teal-600">
                            <i class="fas fa-angle-right mr-2 text-gray-600 hover:text-teal-600"></i>Privacy Policy
                        </a>
                    </li>
                </ul>
            </div>
            <div class="mb-8 md:mb-0 md:w-1/3">
<h3 class="text-xl font-bold mb-4 flex items-center text-gray-600">
                    <i class="fas fa-share-alt mr-2 text-gray-600 hover:text-teal-600"></i>
                    Connect With Us
                </h3>
                <div class="flex space-x-4">
                    <a href="#" class="  transition-all duration-300 transform hover:scale-125">
                        <i class="fab fa-facebook-f text-xl text-gray-600 hover:text-teal-600"></i>
                    </a>
                    <a href="#" class="  transition-all duration-300 transform hover:scale-125">
                        <i class="fab fa-twitter text-xl text-gray-600 hover:text-teal-600"></i>
                    </a>
                    <a href="#" class="  transition-all duration-300 transform hover:scale-125">
                        <i class="fab fa-instagram text-xl text-gray-600 hover:text-teal-600"></i>
                    </a>
                    <a href="#" class="  transition-all duration-300 transform hover:scale-125">
                        <i class="fab fa-linkedin-in text-xl text-gray-600 hover:text-teal-600"></i>
                    </a>
                    <a href="#" class="  transition-all duration-300 transform hover:scale-125">
                        <i class="fab fa-youtube text-xl text-gray-600 hover:text-teal-600"></i>
                    </a>
                </div>
            </div>
        </div>
<div class="border-t border-secondary/20 mt-8 pt-8 text-center text-gray-600">
            <p class=" transition-colors duration-300 text-gray-600 hover:text-teal-600">
                <i class="far fa-copyright mr-1 text-gray-600 hover:text-teal-600"></i>
                <?= date("Y") ?> Natura.lk All rights reserved.
            </p>
        </div>
    </div>
</footer>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.8s ease-out;
}
</style>
