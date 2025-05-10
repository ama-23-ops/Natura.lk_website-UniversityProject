<?php
// Check if the user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Get cart count from database for logged in users
$userId = $_SESSION['user_id'];
$cartStmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?");
$cartStmt->execute([$userId]);
$cartResult = $cartStmt->fetch(PDO::FETCH_ASSOC);
$cartCount = $cartResult['cart_count'] ?? 0;

// Get wishlist count
$wishlistStmt = $conn->prepare("SELECT COUNT(*) as wishlist_count FROM wishlist WHERE user_id = ?");
$wishlistStmt->execute([$userId]);
$wishlistResult = $wishlistStmt->fetch(PDO::FETCH_ASSOC);
$wishlistCount = $wishlistResult['wishlist_count'] ?? 0;

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/scripts.php';

// Fetch user profile picture if not already available
if (!isset($user) && isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!-- Add Favicon -->
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="shortcut icon" type="image/png" href="/assets/images/logo.png">
<nav class="bg-white border-b shadow-sm">
   
    <div class="container mx-auto px-6">
        <div class="flex justify-between h-20">
            <!-- Logo -->
            <div class="w-3/12 flex items-center">
                <a href="/index.php" class="hover:text-teal-700 transition-colors duration-200">
                    <img src="/assets/images/logo.png" alt="Logo" class="h-16 w-auto"/>
                </a>
            </div>

            <!-- Center Navigation -->
            <div class="hidden md:flex items-center justify-center flex-1">
                <div class="flex space-x-10">
                    <a href="/customer/dashboard.php" class="text-gray-600 hover:text-teal-600 px-4 py-3 rounded-md text-base font-medium flex items-center transition duration-150 ease-in-out">
                        <i class="fas fa-tachometer-alt mr-2 text-lg"></i>Dashboard
                    </a>
                    <a href="/customer/products.php" class="text-gray-600 hover:text-teal-600 px-4 py-3 rounded-md text-base font-medium flex items-center transition duration-150 ease-in-out">
                        <i class="fas fa-box-open mr-2 text-lg"></i>Products
                    </a>
                    <a href="/customer/order_history.php" class="text-gray-600 hover:text-teal-600 px-4 py-3 rounded-md text-base font-medium flex items-center transition duration-150 ease-in-out">
                        <i class="fas fa-history mr-2 text-lg"></i>Order History
                    </a>
                    <a href="/customer/blog.php" class="text-gray-600 hover:text-teal-600 px-4 py-3 rounded-md text-base font-medium flex items-center transition duration-150 ease-in-out">
                        <i class="fas fa-blog mr-2 text-lg"></i>Blogs
                    </a>
                </div>
            </div>

            <!-- Right User Menu -->
            <div class="flex items-center space-x-6">
                <!-- Search -->
                <button class="text-gray-600 flex items-center justify-center w-10 h-10 rounded-full hover:bg-teal-50 hover:text-teal-600 transition-all duration-200 " 
                        onclick="toggleSearch()">
                    <i class="fas fa-search text-xl "></i>
                </button>
                
                <!-- Cart Icon with Badge -->
                <a href="/customer/cart.php" class="relative text-gray-600 hover:text-teal-600 transition duration-150 ease-in-out">
                    <i class="fas fa-shopping-cart text-2xl"></i>
                    <span class="absolute -top-4 -right-4 bg-teal-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs"><?= $cartCount ?></span>
                </a>
                
                <!-- Wishlist Icon with Badge -->
                <a href="/customer/wishlist.php" class="relative text-gray-600 hover:text-teal-600 transition duration-150 ease-in-out">
                    <i class="fas fa-heart text-2xl"></i>
                    <span id="wishlistCount" class="absolute -top-4 -right-4 bg-teal-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs"><?= $wishlistCount ?></span>
                </a>

                <!-- User Profile Dropdown -->
                <div class="relative group">
                    <button class="flex items-center text-gray-600 hover:text-teal-600 focus:outline-none">
                        <?php if (!empty($user['profile_picture']) || (!empty($userProfile['profile_picture']))): ?>
                            <img class="h-10 w-10 rounded-full border-2 border-gray-200 object-cover" 
                                 src="<?= !empty($user['profile_picture']) ? $user['profile_picture'] : $userProfile['profile_picture'] ?>" 
                                 alt="User profile picture">
                        <?php else: ?>
                            <img class="h-10 w-10 rounded-full border-2 border-gray-200" 
                                 src="https://ui-avatars.com/api/?name=User&background=random" 
                                 alt="User avatar">
                        <?php endif; ?>
                        <i class="fas fa-chevron-down ml-2 text-sm"></i>
                    </button>
                    <div class="absolute right-0 z-10 hidden group-hover:block w-56 bg-white rounded-md shadow-lg py-2">
                        <a href="/customer/profile.php" class="block px-4 py-3 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-600">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <div class="border-t border-gray-100"></div>
                        <a href="/logout.php" class="block px-4 py-3 text-sm text-teal-600 hover:bg-teal-50">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button moved to right -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-600 hover:text-teal-600 focus:outline-none" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/customer/dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="/customer/products.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50">
                    <i class="fas fa-box-open mr-2"></i>Products
                </a>
                <a href="/customer/order_history.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50">
                    <i class="fas fa-history mr-2"></i>Order History
                </a>
                <a href="/customer/blogs.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50">
                    <i class="fas fa-blog mr-2"></i>Blogs
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Chat System -->
<script src="/assets/js/chat/index.js" defer></script>

<!-- Enhanced Overlay Search Bar -->
<div id="searchOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[1000] flex justify-center items-start transition-opacity duration-300 opacity-0">
    <div class="bg-white w-11/12 md:w-2/5 rounded-xl shadow-2xl p-6 relative mt-20 transform transition-transform duration-300 scale-95 opacity-0" id="searchPanel">
        <!-- Close button -->
        <button onclick="toggleSearch()" class="absolute -top-3 -right-3 w-8 h-8 flex items-center justify-center bg-teal-600 text-white rounded-full hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-all duration-200 group">
            <svg class="w-5 h-5 transform group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Search input -->
        <div class="mb-4">
            <label for="searchInput" class="block text-sm font-medium text-gray-700 mb-2">Search Products</label>
            <div class="relative">
                <input type="text" 
                       id="searchInput" 
                       placeholder="Type to search..." 
                       class="w-full border-2 border-gray-200 p-4 pl-12 rounded-lg focus:outline-none focus:border-teal-600 transition-colors duration-200" 
                       onkeyup="searchProducts()">
                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Results container -->
        <div id="searchResults" class="mt-3 max-h-[60vh] overflow-y-auto rounded-lg">
            <!-- Search results will appear here -->
        </div>
    </div>
</div>

<script>
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.remove('hidden');
    } else {
        mobileMenu.classList.add('hidden');
    }
}

function toggleSearch() {
    const overlay = document.getElementById('searchOverlay');
    const searchPanel = document.getElementById('searchPanel');
    const searchInput = document.getElementById('searchInput');

    if (overlay.classList.contains('hidden')) {
        // Show the search panel
        overlay.classList.remove('hidden');
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            searchPanel.classList.remove('scale-95', 'opacity-0');
        }, 10);
        searchInput.focus();
    } else {
        // Hide the search panel
        overlay.classList.add('opacity-0');
        searchPanel.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
            searchInput.value = '';
            document.getElementById('searchResults').innerHTML = '';
        }, 300);
    }
}

// Close search panel when clicking outside
document.getElementById('searchOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'searchOverlay') {
        toggleSearch();
    }
});

// Close search panel with Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !document.getElementById('searchOverlay').classList.contains('hidden')) {
        toggleSearch();
    }
});

async function searchProducts() {
    const query = document.getElementById('searchInput').value;
    const resultsContainer = document.getElementById('searchResults');
    
    if (query.trim() === '') {
        resultsContainer.innerHTML = '';
        return;
    }
    
    try {
        const res = await fetch('/search.php?q=' + encodeURIComponent(query));
        if (!res.ok) throw new Error('Network error');
        const data = await res.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Error fetching results');
        }
        
        if (data.results && data.results.length > 0) {
            resultsContainer.innerHTML = data.results.map(product => `
                <a href="${product.url}" class="block">
                    <div class="p-4 border-b last:border-b-0 hover:bg-gray-50 cursor-pointer transition-colors duration-200 flex items-center gap-4">
                        <img src="${product.image}" alt="${product.title}" class="w-12 h-12 object-cover rounded">
                        <div>
                            <h3 class="font-medium text-gray-900">${product.title}</h3>
                            <p class="text-sm text-gray-500">$${product.price}</p>
                        </div>
                    </div>
                </a>
            `).join('');
        } else {
            resultsContainer.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>No products found for this search term.</p>
                </div>
            `;
        }
    } catch (e) {
        resultsContainer.innerHTML = `
            <div class="p-4 text-center text-red-500">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>${e.message || 'Error fetching products. Please try again.'}</p>
            </div>
        `;
        console.error('Search error:', e);
    }
}
</script>
