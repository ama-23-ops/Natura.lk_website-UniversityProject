<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/scripts.php'; ?>

<button id="sidebarToggle" class="fixed top-4 left-4 bg-white text-gray-600 hover:text-teal-600 p-2 rounded-md z-50 lg:hidden transform transition-all duration-300 hover:scale-110 hover:shadow-lg">
    <ion-icon name="menu" class="text-2xl transform transition-transform hover:rotate-180"></ion-icon>
</button>

<aside id="sidebar" class="bg-white border-r border-gray-200 shadow-xl w-64 flex-shrink-0 rounded-tr-lg rounded-br-lg lg:translate-x-0 transition-all duration-300 ease-in-out -translate-x-full lg:fixed fixed top-0 left-0 min-h-screen z-50 overflow-hidden">
    <div class="p-4 relative">
        <button id="sidebarClose" class="absolute top-0 left-0 bg-white text-gray-600 hover:text-teal-600 p-2 rounded-md lg:hidden transform transition-all duration-300 hover:scale-110 hover:shadow-lg" style="display: none;">
            <ion-icon name="close" class="text-2xl transform transition-transform hover:rotate-90"></ion-icon>
        </button>
        <a href="/admin/dashboard.php" class="text-gray-800 font-bold text-xl block py-2 mt-6 text-center uppercase tracking-wide transition-all duration-300 hover:text-teal-600 hover:scale-105 transform">
            <ion-icon name="stats-chart" class="mr-2"></ion-icon>Admin Panel
        </a>
    </div>
    <ul class="mt-4 space-y-1">
        <li class="relative overflow-hidden"><a href="/admin/dashboard.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="speedometer" class="mr-2"></ion-icon>Dashboard</a></li>
        <li class="relative overflow-hidden"><a href="/admin/customers.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="people" class="mr-2"></ion-icon>Customers</a></li>
        <li class="relative overflow-hidden"><a href="/admin/products.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="cube" class="mr-2"></ion-icon>Products</a></li>
        <li class="relative overflow-hidden"><a href="/admin/orders.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="cart" class="mr-2"></ion-icon>Orders</a></li>
        <li class="relative overflow-hidden"><a href="/admin/blogs.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="document-text" class="mr-2"></ion-icon>Blogs</a></li>
        <li class="relative overflow-hidden"><a href="/admin/inquiries.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="help-circle" class="mr-2"></ion-icon>Inquiries</a></li>
        <li class="relative overflow-hidden"><a href="/admin/reviews.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="star" class="mr-2"></ion-icon>Reviews</a></li>
        <li class="relative overflow-hidden"><a href="/admin/financial_data.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="pie-chart" class="mr-2"></ion-icon>Financial Data</a></li>
        <li class="relative overflow-hidden"><a href="/admin/faqs.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="help" class="mr-2"></ion-icon>FAQs</a></li>
        <li class="relative overflow-hidden"><a href="/logout.php" class="block text-gray-700 hover:bg-teal-600 hover:text-white py-2 px-4 uppercase text-sm tracking-wider transition-all duration-300 hover:translate-x-2 transform">
          <ion-icon name="log-out" class="mr-2"></ion-icon>Logout</a></li>
    </ul>
</aside>

<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');

    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('-translate-x-full');
        sidebarToggle.style.display = 'none';
        sidebarClose.style.display = 'block';
    });

    sidebarClose.addEventListener('click', function () {
        sidebar.classList.toggle('-translate-x-full');
        sidebarToggle.style.display = 'block';
        sidebarClose.style.display = 'none';
    });
</script>

<!-- Chat System (Admin Mode) -->
<script src="/assets/js/chat/index.js" defer></script>
