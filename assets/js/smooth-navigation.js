/**
 * Smooth Navigation System
 * Detects when a guest user scrolls to the bottom of the page and 
 * smoothly navigates to the next page in the navigation sequence.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Only enable for non-logged in users (guests)
    if (document.body.classList.contains('user-logged-in')) {
        return;
    }

    // Get navigation links from the main menu
    const navLinks = Array.from(document.querySelectorAll('#mainNav a.nav-link'))
        .map(link => link.getAttribute('href'));
    
    if (navLinks.length === 0) return;

    // Find current page in the navigation sequence
    const currentPath = window.location.pathname;
    const currentIndex = navLinks.findIndex(link => {
        // Remove /index.php from the end of links for matching
        const cleanLink = link.replace(/\/index\.php$/, '/');
        const cleanPath = currentPath.replace(/\/index\.php$/, '/');
        return cleanLink === cleanPath;
    });

    // If we found the current page and it's not the last one in navigation
    if (currentIndex !== -1 && currentIndex < navLinks.length - 1) {
        const nextPageUrl = navLinks[currentIndex + 1];
        
        // Set up scroll event listener
        let isTransitioning = false;
        let scrollTimeout;

        window.addEventListener('scroll', function() {
            // Clear previous timeout
            clearTimeout(scrollTimeout);
            
            // Set new timeout to avoid multiple triggers
            scrollTimeout = setTimeout(function() {
                // If already transitioning, do nothing
                if (isTransitioning) return;
                
                // Check if user has scrolled to the bottom
                const scrollPosition = window.scrollY;
                const viewportHeight = window.innerHeight;
                const documentHeight = document.documentElement.scrollHeight;
                
                // How close to the bottom (in pixels) to trigger navigation (100px from bottom)
                const threshold = 1;
                
                if (scrollPosition + viewportHeight >= documentHeight - threshold) {
                    isTransitioning = true;
                    
                    // Show transition overlay
                    showTransitionOverlay(nextPageUrl);
                }
            }, 200);
        });
    }
});

/**
 * Shows a smooth transition overlay and navigates to the next page
 */
function showTransitionOverlay(nextPageUrl) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-teal-600 z-[9999] transition-all duration-1000 opacity-0';
    overlay.style.pointerEvents = 'none';
    document.body.appendChild(overlay);
    
    // Animate overlay
    setTimeout(() => {
        overlay.classList.add('opacity-30');
        
        // After fade-in, navigate to next page
        setTimeout(() => {
            window.location.href = nextPageUrl;
        }, 400);
    }, 50);
    
    // Show user feedback about navigation
    const message = document.createElement('div');
    message.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[10000] bg-white px-6 py-4 rounded-lg shadow-lg text-center opacity-0 transition-opacity duration-300';
    message.innerHTML = `
        <p class="text-lg font-medium text-teal-700">
            <i class="fas fa-arrow-circle-down mr-2"></i>
            Taking you to the next page...
        </p>
    `;
    document.body.appendChild(message);
    
    // Fade in message
    setTimeout(() => {
        message.classList.add('opacity-100');
    }, 100);
}
