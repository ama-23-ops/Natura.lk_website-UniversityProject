<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
  header("Location: ../login.php");
  exit();
}
include_once '../db.php';

// Fetch total number of users
$stmt = $conn->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Fetch total number of products
$stmt = $conn->query("SELECT COUNT(*) as total_products FROM products");
$totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

// Fetch total number of orders
$stmt = $conn->query("SELECT COUNT(*) as total_orders FROM orders");
$totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

// Fetch total sales amount
$stmt = $conn->query("SELECT SUM(total_amount) as total_sales FROM orders");
$totalSales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'];
$totalSales = $totalSales ? number_format($totalSales, 2, '.', ',') : '0.00';

// Fetch total profit amount
$profitStmt = $conn->query("SELECT SUM((oi.quantity * p.sale_price) - (oi.quantity * p.purchase_cost)) AS total_profit FROM order_items oi JOIN products p ON oi.product_id = p.id");
$totalProfit = $profitStmt->fetch(PDO::FETCH_ASSOC)['total_profit'];
$totalProfit = $totalProfit ? number_format($totalProfit, 2, '.', ',') : '0.00';

// Fetch total number of reviews
$reviewsStmt = $conn->query("SELECT COUNT(*) as total_reviews FROM reviews");
$totalReviews = $reviewsStmt->fetch(PDO::FETCH_ASSOC)['total_reviews'];

// Fetch total number of blogs
$blogsStmt = $conn->query("SELECT COUNT(*) as total_blogs FROM blogs");
$totalBlogs = $blogsStmt->fetch(PDO::FETCH_ASSOC)['total_blogs'];

// Fetch total number of inquiries
$inquiriesStmt = $conn->query("SELECT COUNT(*) as total_inquiries FROM inquiries");
$totalInquiries = $inquiriesStmt->fetch(PDO::FETCH_ASSOC)['total_inquiries'];

// Fetch monthly sales data
$monthlySalesStmt = $conn->prepare("SELECT MONTH(order_date) AS month, SUM(total_amount) AS sales FROM orders GROUP BY MONTH(order_date) ORDER BY MONTH(order_date)");
$monthlySalesStmt->execute();
$monthlySalesData = $monthlySalesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch monthly profit data
$monthlyProfitStmt = $conn->prepare("SELECT MONTH(o.order_date) AS month, SUM((oi.quantity * p.sale_price) - (oi.quantity * p.purchase_cost)) AS profit FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN products p ON oi.product_id = p.id GROUP BY MONTH(o.order_date) ORDER BY MONTH(o.order_date)");
$monthlyProfitStmt->execute();
$monthlyProfitData = $monthlyProfitStmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare sales data for JavaScript
$salesDataArray = array_fill(0, 12, 0); // Initialize with 0 for all months
if (!empty($monthlySalesData)) {
  foreach ($monthlySalesData as $sale) {
    $month = (int)$sale['month'] - 1; // Month is 1-12, array index is 0-11
    $salesDataArray[$month] = (float)$sale['sales'];
  }
}

// Prepare profit data for JavaScript
$profitDataArray = array_fill(0, 12, 0); // Initialize with 0 for all months
if (!empty($monthlyProfitData)) {
  foreach ($monthlyProfitData as $profit) {
    $month = (int)$profit['month'] - 1; // Month is 1-12, array index is 0-11
    $profitDataArray[$month] = (float)$profit['profit'];
  }
}

// Prepare the JavaScript data before the heredoc
$salesDataJSON = json_encode($salesDataArray);
$profitDataJSON = json_encode($profitDataArray);

$pageTitle = 'ADMIN DASHBOARD';

$content = <<<HTML
<style>
  .teal-gradient {
    background: linear-gradient(135deg, #00796b 0%, #009688 100%);
  }
</style>
<div class="min-h-screen ">
  <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
    <div class="flex items-center">
      <i class="fas fa-user-shield text-4xl mr-4"></i>
      <div>
        <h1 class="text-3xl font-bold">ADMIN DASHBOARD</h1>
        <p class="text-teal-100">MANAGE USERS, PRODUCTS, AND MONITOR BUSINESS METRICS</p>
      </div>
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Users -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col items-center">
      <div class="border-2 border-teal-600 rounded-full p-3 mb-2 w-16 h-16 flex items-center justify-center">
        <ion-icon name="people" class="text-3xl text-teal-600"></ion-icon>
      </div>
      <h2 class="text-lg font-semibold text-gray-700">TOTAL USERS</h2>
      <p class="text-3xl font-bold text-gray-600">$totalUsers</p>
    </div>

    <!-- Total Products -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col items-center">
      <div class="border-2 border-teal-600 rounded-full p-3 mb-2 w-16 h-16 flex items-center justify-center">
        <ion-icon name="cube" class="text-3xl text-teal-600"></ion-icon>
      </div>
      <h2 class="text-lg font-semibold text-gray-700">TOTAL PRODUCTS</h2>
      <p class="text-3xl font-bold text-gray-600">$totalProducts</p>
    </div>

    <!-- Total Orders -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col items-center">
      <div class="border-2 border-teal-600 rounded-full p-3 mb-2 w-16 h-16 flex items-center justify-center">
        <ion-icon name="cart" class="text-3xl text-teal-600"></ion-icon>
      </div>
      <h2 class="text-lg font-semibold text-gray-700">TOTAL ORDERS</h2>
      <p class="text-3xl font-bold text-gray-600">$totalOrders</p>
    </div>

    <!-- Total Sales -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col items-center">
      <div class="border-2 border-teal-600 rounded-full p-3 mb-2 w-16 h-16 flex items-center justify-center">
        <ion-icon name="cash" class="text-3xl text-teal-600"></ion-icon>
      </div>
      <h2 class="text-lg font-semibold text-gray-700">TOTAL SALES</h2>
      <p class="text-3xl font-bold text-gray-600">\$$totalSales</p>
    </div>

    <!-- Total Profit -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col items-center">
      <div class="border-2 border-teal-600 rounded-full p-3 mb-2 w-16 h-16 flex items-center justify-center">
        <ion-icon name="trending-up" class="text-3xl text-teal-600"></ion-icon>
      </div>
      <h2 class="text-lg font-semibold text-gray-700">TOTAL PROFIT</h2>
      <p class="text-3xl font-bold text-gray-600">\$$totalProfit</p>
    </div>

    <!-- Total Reviews -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col items-center">
      <div class="border-2 border-teal-600 rounded-full p-3 mb-2 w-16 h-16 flex items-center justify-center">
        <ion-icon name="star" class="text-3xl text-teal-600"></ion-icon>
      </div>
      <h2 class="text-lg font-semibold text-gray-700">TOTAL REVIEWS</h2>
      <p class="text-3xl font-bold text-gray-600">$totalReviews</p>
    </div>

    <!-- Total Blogs -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col items-center">
      <div class="border-2 border-teal-600 rounded-full p-3 mb-2 w-16 h-16 flex items-center justify-center">
        <ion-icon name="newspaper-outline" class="text-3xl text-teal-600"></ion-icon>
      </div>
      <h2 class="text-lg font-semibold text-gray-700">TOTAL BLOGS</h2>
      <p class="text-3xl font-bold text-gray-600">$totalBlogs</p>
    </div>

    <!-- Total Inquiries -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col items-center">
      <div class="border-2 border-teal-600 rounded-full p-3 mb-2 w-16 h-16 flex items-center justify-center">
        <ion-icon name="help-circle" class="text-3xl text-teal-600"></ion-icon>
      </div>
      <h2 class="text-lg font-semibold text-gray-700">TOTAL INQUIRIES</h2>
      <p class="text-3xl font-bold text-gray-600">$totalInquiries</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
    <!-- Sales Chart -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300">
      <h2 class="text-lg font-semibold text-gray-600 mb-2">SALES CHART</h2>
      <canvas id="salesChart"></canvas>
    </div>

    <!-- Profit Chart -->
    <div class="bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300">
      <h2 class="text-lg font-semibold text-gray-600 mb-2">PROFIT CHART</h2>
      <canvas id="profitChart"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Sales Chart Data
  let salesData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [{
      label: 'Sales',
      data: {$salesDataJSON},
      backgroundColor: 'rgba(54, 162, 235, 0.2)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1
    }]
  };

  // Profit Chart Data
  let profitData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [{
      label: 'Profit',
      data: {$profitDataJSON},
      backgroundColor: 'rgba(255, 99, 132, 0.2)',
      borderColor: 'rgba(255, 99, 132, 1)',
      borderWidth: 1
    }]
  };

  if (salesData.datasets[0].data.length === 0) {
    salesData.datasets[0].data = [0];
  }

  if (profitData.datasets[0].data.length === 0) {
    profitData.datasets[0].data = [0];
  }

  // Sales Chart Configuration
  const salesChartConfig = {
    type: 'line',
    data: salesData,
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  };

  // Profit Chart Configuration
  const profitChartConfig = {
    type: 'line',
    data: profitData,
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  };

  // Render Sales Chart
  const salesChart = new Chart(
    document.getElementById('salesChart'),
    salesChartConfig
  );

  // Render Profit Chart
  const profitChart = new Chart(
    document.getElementById('profitChart'),
    profitChartConfig
  );
</script>
HTML;

include 'includes/dashboard_layout.php';
?>
