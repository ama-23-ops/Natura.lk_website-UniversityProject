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