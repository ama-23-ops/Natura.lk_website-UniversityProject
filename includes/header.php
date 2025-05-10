<?php
// Check if the user is logged in and get user data if needed
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
define('INCLUDED', true);
if (isset($_SESSION['user_id'])) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php'; // Include database connection if user data is needed
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get cart count from database for logged in users
    $cartStmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?");
    $cartStmt->execute([$userId]);
    $cartResult = $cartStmt->fetch(PDO::FETCH_ASSOC);
    $cartCount = $cartResult['cart_count'] ?? 0;
} else {
    // Calculate cart count from session for non-logged in users
    $cartCount = 0;
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['quantity'];
        }
    }
}

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
$current_page = basename($_SERVER['PHP_SELF']);
$show_search = isset($_GET['search']) && $_GET['search'] === 'true';

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/scripts.php';
?>
<!-- Add Favicon -->
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="shortcut icon" type="image/png" href="/assets/images/logo.png">
<header id="mainHeader" class="header fixed top-0 w-full bg-transparent text-white flex items-center justify-between px-8 py-4 z-[100] transition-all duration-300">
    <!-- logo -->
    <div class="w-3/12 flex items-center">
        <a href="/index.php" class="hover:text-teal-700 transition-colors duration-200">
            <img src="/assets/images/logo.png" alt="Logo" class="h-16 w-auto"/>
        </a>
    </div>

    <!-- navigation -->
    <nav class="nav font-semibold text-lg md:block hidden w-full md:w-auto" id="mainNav">
        <ul class="flex flex-col md:flex-row items-center space-x-4 mt-4 md:mt-0">
            <li>
                <a href="/index.php" class="nav-link flex items-center hover:text-teal-600 transition-colors duration-200 mx-2" data-nav-order="1">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
            </li>
            <li>
                <a href="/products.php" class="nav-link flex items-center hover:text-teal-600 transition-colors duration-200 mx-2">
                    <i class="fas fa-box-open mr-2"></i> Products
                </a>
            </li>
            <li>
                <a href="/collections.php" class="nav-link flex items-center hover:text-teal-600 transition-colors duration-200 mx-2">
                    <i class="fas fa-th-large mr-2"></i> Collections
                </a>
            </li>
            <li>
                <a href="/blogs/index.php" class="nav-link flex items-center hover:text-teal-600 transition-colors duration-200 mx-2">
                    <i class="fas fa-newspaper mr-2"></i> Blog
                </a>
            </li>
            <li>
                <a href="/contact.php" class="nav-link flex items-center hover:text-teal-600 transition-colors duration-200 mx-2">
                    <i class="fas fa-envelope mr-2"></i> Contact
                </a>
            </li>
        </ul>
    </nav>

    <!-- buttons -->
    <div class="w-3/12 flex items-center justify-end space-x-4">
        <!-- Search -->
        <button class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-teal-50 hover:text-teal-600 transition-all duration-200" 
                onclick="toggleSearch()">
            <i class="fas fa-search text-lg"></i>
        </button>

        <!-- Cart -->
        <a href="/cart.php" class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-teal-50 hover:text-teal-600 transition-all duration-200">
            <i class="fas fa-shopping-cart text-lg"></i>
            <span class="absolute -top-2 -right-2 bg-teal-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $cartCount ?></span>
        </a>
        
        <?php if(!isset($_SESSION['user_id'])): ?>
            <!-- Login Button -->
            <a href="/login.php" 
               class="inline-flex items-center px-4 py-2 border-2 border-teal-600 rounded-full hover:bg-teal-600 hover:text-white transition-all duration-200">
                <i class="fas fa-user-circle mr-2"></i>
                <span>Login</span>
            </a>
        <?php else: ?>
            <!-- User Dropdown -->
            <div class="relative group">
                <button class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-teal-50 hover:text-teal-600 transition-all duration-200">
                    <i class="fas fa-user-circle text-lg"></i>
                </button>
                <div class="absolute right-0 top-full mt-2 w-48 rounded-lg shadow-lg py-2 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden group-hover:block z-50">
                    <a href="/profile.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-teal-50 hover:text-teal-600 transition-colors duration-200">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <a href="/orders.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-teal-50 hover:text-teal-600 transition-colors duration-200">
                        <i class="fas fa-shopping-bag mr-2"></i> Orders
                    </a>
                    <a href="/wishlist.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-teal-50 hover:text-teal-600 transition-colors duration-200">
                        <i class="fas fa-heart mr-2"></i> Wishlist
                    </a>
                    <hr class="my-1 border-gray-200">
                    <a href="/logout.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-teal-50 hover:text-teal-600 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Mobile menu button -->
        <button id="mobileMenuButton" class="md:hidden hover:text-teal-600 focus:outline-none">
            <i class="fas fa-bars text-2xl"></i>
        </button>
    </div>
</header>


<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/search.php'; ?>

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

// Change header style on scroll
window.addEventListener('scroll', () => {
    const header = document.getElementById('mainHeader');
    if (window.scrollY > 50) {
        header.classList.add('bg-white', 'text-gray-600', 'shadow-md');
        header.classList.remove('bg-transparent', 'text-white');
    } else {
        header.classList.add('bg-transparent', 'text-white');
        header.classList.remove('bg-white', 'text-gray-600', 'shadow-md');
    }
});

// Mobile menu toggle
document.getElementById('mobileMenuButton').addEventListener('click', function() {
    document.getElementById('mainNav').classList.toggle('hidden');
});
</script>

<!-- Chat System -->
<script src="/assets/js/chat/index.js" defer></script>

<!-- Update the styles -->
<style>
.nav-link {
    @apply px-4 py-2 rounded-full hover:bg-teal-50 hover:text-teal-600 transition-all duration-200;
}

/* Mobile styles */
@media (max-width: 768px) {
    .header {
        padding: 1rem;
    }

    .w-3/12 {
        width: auto;
    }

    #searchPanel {
        width: 90%;
    }
    #mobileMenuButton {
        display: block;
    }
    .nav {
        position: fixed; /* Changed to fixed */
        top: 60px;
        left: 0;
        background-color: white;
        width: 100%;
        padding: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        z-index: 50;
    }

    .nav ul {
        flex-direction: column;
        align-items: stretch;
    }

    .nav li {
        margin-bottom: 0.5rem;
    }

    .nav a {
        display: block;
        padding: 0.5rem;
        text-align: center;
    }
}
</style>
