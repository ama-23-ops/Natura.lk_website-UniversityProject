<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: " . $_SERVER['DOCUMENT_ROOT'] . "/login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$pageTitle = 'Financial Data';

// Add Chart.js library here, before any charts are created
$extraHeader = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

ob_start();
?>
<div class="min-h-screen">
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #00796b 0%, #009688 100%);
        }
    </style>
    <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
        <div class="flex items-center">
            <i class="fas fa-chart-line text-4xl mr-4"></i>
            <div>
                <h1 class="text-3xl font-bold">Financial Overview</h1>
                <p class="text-teal-100">Comprehensive view of your business financials</p>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/financial_metrics.php'; ?>
    
    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Recent Transactions</h2>
            <a href="orders.php" class="text-teal-600 hover:text-teal-700 flex items-center">
                <span>View All</span>
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/recent_transactions_table.php'; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/dashboard_layout.php';
?>
