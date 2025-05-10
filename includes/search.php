<?php
if (!defined('INCLUDED')) {
    header("Location: /index.php");
    exit();
}
?>

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
        overlay.classList.remove('hidden');
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            searchPanel.classList.remove('scale-95', 'opacity-0');
        }, 10);
        searchInput.focus();
    } else {
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
        const res = await fetch('/includes/search.php?q=' + encodeURIComponent(query));
        if (!res.ok) throw new Error('Network error');
        const data = await res.json();
        
        if (data.length > 0) {
            resultsContainer.innerHTML = data.map(product => `
                <a href="${product.url}" class="block">
                    <div class="p-4 border-b last:border-b-0 hover:bg-gray-50 cursor-pointer transition-colors duration-200 flex items-center gap-4">
                        <img src="${product.image}" alt="${product.title}" class="w-12 h-12 object-cover rounded">
                        <div>
                            <h3 class="font-medium text-gray-900">${product.title}</h3>
                            <p class="text-sm text-gray-500">$${parseFloat(product.price).toFixed(2)}</p>
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
                <p>Error fetching products. Please try again.</p>
            </div>
        `;
        console.error('Error fetching products:', e);
    }
}
</script>