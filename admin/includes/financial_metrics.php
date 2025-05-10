<?php
// Add title section at the start
?>
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-gray-800">Financial Metrics</h2>
    <p class="text-gray-600">Track revenue, sales performance, and financial analytics</p>
</div>

<?php
// Get overall financial metrics
$revenueStmt = $conn->prepare("
    SELECT 
        SUM(total_amount - discount_amount + tax_amount) as total_revenue,
        COUNT(*) as total_orders,
        AVG(total_amount - discount_amount + tax_amount) as avg_order_value
    FROM orders
");
$revenueStmt->execute();
$revenueData = $revenueStmt->fetch(PDO::FETCH_ASSOC);

// Get monthly revenue data
$monthlyRevenueStmt = $conn->prepare("
    SELECT 
        MONTH(order_date) as month,
        YEAR(order_date) as year,
        SUM(total_amount - discount_amount + tax_amount) as revenue,
        COUNT(*) as orders,
        SUM(discount_amount) as total_discounts
    FROM orders
    GROUP BY YEAR(order_date), MONTH(order_date)
    ORDER BY year DESC, month DESC
    LIMIT 12
");
$monthlyRevenueStmt->execute();
$monthlyRevenue = $monthlyRevenueStmt->fetchAll(PDO::FETCH_ASSOC);

// Get top selling products
$topProductsStmt = $conn->prepare("
    SELECT 
        p.id,
        p.title,
        p.sale_price,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.quantity * (p.sale_price - p.purchase_cost)) as profit
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id, p.title
    ORDER BY total_quantity DESC
    LIMIT 5
");
$topProductsStmt->execute();
$topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get payment method distribution
$paymentMethodStmt = $conn->prepare("
    SELECT 
        payment_method,
        COUNT(*) as count,
        SUM(total_amount - discount_amount + tax_amount) as total_amount
    FROM orders
    GROUP BY payment_method
");
$paymentMethodStmt->execute();
$paymentMethods = $paymentMethodStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Statistics Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                <h3 class="text-2xl font-bold text-teal-600 mt-1">$<?= number_format($revenueData['total_revenue'], 2) ?></h3>
            </div>
            <div class="bg-teal-100 rounded-full p-3">
                <i class="fas fa-dollar-sign text-teal-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Average Order Value</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-1">$<?= number_format($revenueData['avg_order_value'], 2) ?></h3>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Orders</p>
                <h3 class="text-2xl font-bold text-green-600 mt-1"><?= number_format($revenueData['total_orders']) ?></h3>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Monthly Growth</p>
                <?php 
                if (count($monthlyRevenue) >= 2) {
                    $currentMonth = $monthlyRevenue[0]['revenue'];
                    $previousMonth = $monthlyRevenue[1]['revenue'];
                    $growth = $previousMonth != 0 ? (($currentMonth - $previousMonth) / $previousMonth) * 100 : 0;
                    $isPositive = $growth >= 0;
                ?>
                <h3 class="text-2xl font-bold <?= $isPositive ? 'text-green-600' : 'text-red-600' ?> mt-1">
                    <?= $isPositive ? '+' : '' ?><?= number_format($growth, 1) ?>%
                </h3>
                <?php } ?>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Top Products and Payment Methods Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Products -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Top Selling Products</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Units Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($topProducts as $product): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($product['title']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= number_format($product['total_quantity']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900">$<?= number_format($product['total_quantity'] * $product['sale_price'], 2) ?></td>
                            <td class="px-6 py-4 text-sm text-green-600">$<?= number_format($product['profit'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Payment Methods Distribution</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($paymentMethods as $method): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="flex items-center">
                                    <i class="<?= $method['payment_method'] === 'credit' ? 'fab fa-cc-visa text-blue-600' : 'fab fa-paypal text-blue-500' ?> text-lg mr-2"></i>
                                    <?= ucfirst($method['payment_method']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= number_format($method['count']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900">$<?= number_format($method['total_amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Monthly Revenue Chart -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800 mb-3 md:mb-0">Revenue & Orders Analytics</h2>
        <div class="flex flex-wrap gap-2">
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button type="button" id="monthViewBtn" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-teal-50 hover:text-teal-600 focus:z-10 focus:ring-2 focus:ring-teal-600 focus:text-teal-600">
                    Monthly
                </button>
                <button type="button" id="quarterViewBtn" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-teal-50 hover:text-teal-600 focus:z-10 focus:ring-2 focus:ring-teal-600 focus:text-teal-600">
                    Quarterly
                </button>
                <button type="button" id="yearViewBtn" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-lg hover:bg-teal-50 hover:text-teal-600 focus:z-10 focus:ring-2 focus:ring-teal-600 focus:text-teal-600">
                    Yearly
                </button>
            </div>
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button type="button" id="lineChartBtn" class="px-4 py-2 text-sm font-medium text-white bg-teal-600 border border-teal-600 rounded-l-lg hover:bg-teal-700">
                    <i class="fas fa-chart-line mr-1"></i> Line
                </button>
                <button type="button" id="barChartBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-r-lg hover:bg-teal-50 hover:text-teal-600 focus:z-10 focus:ring-2 focus:ring-teal-600 focus:text-teal-600">
                    <i class="fas fa-chart-bar mr-1"></i> Bar
                </button>
            </div>
        </div>
    </div>
    
    <div class="bg-gray-50 p-3 rounded-md mb-4 hidden" id="chartInfo">
        <div class="flex items-center">
            <div class="mr-3 bg-teal-100 rounded-full p-2">
                <i class="fas fa-info-circle text-teal-600"></i>
            </div>
            <p class="text-sm text-gray-600" id="chartInfoText">Displaying monthly data</p>
        </div>
    </div>
    
    <div class="h-96 relative" id="chartContainer">
        <div class="absolute inset-0 flex items-center justify-center" id="chartLoading">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-teal-600"></div>
        </div>
        <canvas id="revenueChart" class="hidden"></canvas>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-4 border-t border-gray-200">
        <div class="bg-teal-50 p-4 rounded-lg border-l-4 border-teal-600">
            <p class="text-sm text-gray-500 font-medium">Total Revenue</p>
            <div class="flex items-center">
                <h3 class="text-xl font-bold text-teal-600 mt-1" id="periodRevenue">$0.00</h3>
                <span class="ml-2 text-xs hidden" id="revenueChange"></span>
            </div>
        </div>
        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-600">
            <p class="text-sm text-gray-500 font-medium">Total Orders</p>
            <div class="flex items-center">
                <h3 class="text-xl font-bold text-blue-600 mt-1" id="periodOrders">0</h3>
                <span class="ml-2 text-xs hidden" id="ordersChange"></span>
            </div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-gray-600">
            <p class="text-sm text-gray-500 font-medium">Average Order Value</p>
            <div class="flex items-center">
                <h3 class="text-xl font-bold text-gray-600 mt-1" id="avgOrderValue">$0.00</h3>
                <span class="ml-2 text-xs hidden" id="avgValueChange"></span>
            </div>
        </div>
    </div>
</div>

<!-- Ensure Chart.js is loaded -->
<script>
// Check if Chart.js is already loaded, if not, load it
(function() {
    let chartInstance = null;
    let currentPeriod = 'month';
    let currentChartType = 'line';
    
    // Function to format currency
    function formatCurrency(value) {
        return '$' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    
    // Function to format numbers with commas
    function formatNumber(value) {
        return parseFloat(value).toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&,');
    }
    
    // Function to show the loading state
    function showLoading() {
        document.getElementById('chartLoading').classList.remove('hidden');
        document.getElementById('revenueChart').classList.add('hidden');
    }
    
    // Function to hide the loading state
    function hideLoading() {
        document.getElementById('chartLoading').classList.add('hidden');
        document.getElementById('revenueChart').classList.remove('hidden');
    }
    
    // Function to show chart info message
    function showChartInfo(message, type = 'info') {
        const chartInfo = document.getElementById('chartInfo');
        const chartInfoText = document.getElementById('chartInfoText');
        chartInfo.classList.remove('hidden', 'bg-gray-50', 'bg-teal-50', 'bg-yellow-50');
        
        if (type === 'info') {
            chartInfo.classList.add('bg-gray-50');
        } else if (type === 'success') {
            chartInfo.classList.add('bg-teal-50');
        } else if (type === 'warning') {
            chartInfo.classList.add('bg-yellow-50');
        }
        
        chartInfoText.textContent = message;
        chartInfo.classList.remove('hidden');
    }
    
    // Load and initialize chart
    function loadChart() {
        if (typeof Chart === 'undefined') {
            console.log('Loading Chart.js library...');
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.async = false;
            script.onload = function() {
                initializeChart();
            };
            document.head.appendChild(script);
        } else {
            initializeChart();
        }
    }
    
    // Initialize chart with data
    function initializeChart() {
        const monthlyData = <?= json_encode($monthlyRevenue) ?>;
        if (!monthlyData || monthlyData.length === 0) {
            document.getElementById('chartContainer').innerHTML = 
                '<div class="flex justify-center items-center h-64"><p class="text-gray-500">No revenue data available</p></div>';
            return;
        }
        
        // Set button active states initially
        setActiveButton('period', 'month');
        setActiveButton('chart', 'line');
        
        // Create the chart
        createChart(monthlyData, 'month', 'line');
        
        // Attach event listeners to period buttons
        document.getElementById('monthViewBtn').addEventListener('click', function() {
            currentPeriod = 'month';
            setActiveButton('period', 'month');
            createChart(monthlyData, 'month', currentChartType);
        });
        
        document.getElementById('quarterViewBtn').addEventListener('click', function() {
            currentPeriod = 'quarter';
            setActiveButton('period', 'quarter');
            createChart(monthlyData, 'quarter', currentChartType);
        });
        
        document.getElementById('yearViewBtn').addEventListener('click', function() {
            currentPeriod = 'year';
            setActiveButton('period', 'year');
            createChart(monthlyData, 'year', currentChartType);
        });
        
        // Attach event listeners to chart type buttons
        document.getElementById('lineChartBtn').addEventListener('click', function() {
            currentChartType = 'line';
            setActiveButton('chart', 'line');
            createChart(monthlyData, currentPeriod, 'line');
        });
        
        document.getElementById('barChartBtn').addEventListener('click', function() {
            currentChartType = 'bar';
            setActiveButton('chart', 'bar');
            createChart(monthlyData, currentPeriod, 'bar');
        });
    }
    
    // Helper function to set active button state
    function setActiveButton(group, active) {
        if (group === 'period') {
            document.getElementById('monthViewBtn').classList.remove('bg-teal-600', 'text-white');
            document.getElementById('quarterViewBtn').classList.remove('bg-teal-600', 'text-white');
            document.getElementById('yearViewBtn').classList.remove('bg-teal-600', 'text-white');
            
            document.getElementById('monthViewBtn').classList.add('bg-white', 'text-gray-900');
            document.getElementById('quarterViewBtn').classList.add('bg-white', 'text-gray-900');
            document.getElementById('yearViewBtn').classList.add('bg-white', 'text-gray-900');
            
            if (active === 'month') {
                document.getElementById('monthViewBtn').classList.remove('bg-white', 'text-gray-900');
                document.getElementById('monthViewBtn').classList.add('bg-teal-600', 'text-white');
            } else if (active === 'quarter') {
                document.getElementById('quarterViewBtn').classList.remove('bg-white', 'text-gray-900');
                document.getElementById('quarterViewBtn').classList.add('bg-teal-600', 'text-white');
            } else if (active === 'year') {
                document.getElementById('yearViewBtn').classList.remove('bg-white', 'text-gray-900');
                document.getElementById('yearViewBtn').classList.add('bg-teal-600', 'text-white');
            }
        } else if (group === 'chart') {
            document.getElementById('lineChartBtn').classList.remove('bg-teal-600', 'text-white');
            document.getElementById('barChartBtn').classList.remove('bg-teal-600', 'text-white');
            
            document.getElementById('lineChartBtn').classList.add('bg-white', 'text-gray-700');
            document.getElementById('barChartBtn').classList.add('bg-white', 'text-gray-700');
            
            if (active === 'line') {
                document.getElementById('lineChartBtn').classList.remove('bg-white', 'text-gray-700');
                document.getElementById('lineChartBtn').classList.add('bg-teal-600', 'text-white');
            } else if (active === 'bar') {
                document.getElementById('barChartBtn').classList.remove('bg-white', 'text-gray-700');
                document.getElementById('barChartBtn').classList.add('bg-teal-600', 'text-white');
            }
        }
    }
    
    // Function to create and update the chart
    function createChart(originalData, period, chartType) {
        showLoading();
        
        // Destroy existing chart if it exists
        if (chartInstance) {
            chartInstance.destroy();
        }
        
        let groupedData = processDataByPeriod(originalData, period);
        
        // Calculate and update summary metrics
        updateSummaryMetrics(groupedData);
        
        // Set appropriate chart info message
        if (period === 'month') {
            showChartInfo('Displaying monthly data for the past ' + groupedData.labels.length + ' months');
        } else if (period === 'quarter') {
            showChartInfo('Displaying quarterly data aggregated by quarter');
        } else {
            showChartInfo('Displaying yearly data aggregated by calendar year');
        }
        
        // Create datasets based on chart type
        let datasets = [];
        if (chartType === 'line') {
            datasets = [
                {
                    label: 'Revenue',
                    data: groupedData.revenues,
                    borderColor: 'rgb(13, 148, 136)',
                    backgroundColor: 'rgba(13, 148, 136, 0.1)',
                    yAxisID: 'y',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Orders',
                    data: groupedData.orders,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    yAxisID: 'y1',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Discounts',
                    data: groupedData.discounts,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    yAxisID: 'y',
                    fill: false,
                    borderDash: [5, 5],
                    tension: 0.4
                }
            ];
        } else {
            datasets = [
                {
                    label: 'Revenue',
                    data: groupedData.revenues,
                    backgroundColor: 'rgba(13, 148, 136, 0.7)',
                    borderColor: 'rgb(13, 148, 136)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Orders',
                    data: groupedData.orders,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                },
                {
                    label: 'Discounts',
                    data: groupedData.discounts,
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }
            ];
        }
        
        // Create the chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        chartInstance = new Chart(ctx, {
            type: chartType,
            data: {
                labels: groupedData.labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.yAxisID === 'y' && (label.includes('Revenue') || label.includes('Discounts'))) {
                                    label += '$' + parseFloat(context.parsed.y).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue ($)',
                            color: 'rgb(13, 148, 136)',
                            font: {
                                size: 11,
                                weight: 'bold'
                            }
                        },
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Number of Orders',
                            color: 'rgb(59, 130, 246)',
                            font: {
                                size: 11,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        },
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        hideLoading();
    }
    
    // Function to process data based on selected period
    function processDataByPeriod(originalData, period) {
        let groupedData = {};
        let labels = [];
        let revenues = [];
        let orders = [];
        let discounts = [];
        
        if (period === 'year') {
            // Group by year
            originalData.forEach(item => {
                const yearKey = item.year.toString();
                if (!groupedData[yearKey]) {
                    groupedData[yearKey] = { revenue: 0, orders: 0, discounts: 0 };
                    labels.push(yearKey);
                }
                groupedData[yearKey].revenue += parseFloat(item.revenue) || 0;
                groupedData[yearKey].orders += parseInt(item.orders) || 0;
                groupedData[yearKey].discounts += parseFloat(item.total_discounts) || 0;
            });
            
            // Sort labels chronologically
            labels.sort();
            
            // Create data arrays from grouped data
            revenues = labels.map(label => groupedData[label].revenue);
            orders = labels.map(label => groupedData[label].orders);
            discounts = labels.map(label => groupedData[label].discounts);
        } else if (period === 'quarter') {
            // Group by quarter
            originalData.forEach(item => {
                const quarter = Math.ceil(item.month / 3);
                const quarterKey = `Q${quarter} ${item.year}`;
                if (!groupedData[quarterKey]) {
                    groupedData[quarterKey] = { 
                        revenue: 0, 
                        orders: 0, 
                        discounts: 0,
                        sortKey: (item.year * 10) + quarter // For sorting
                    };
                    labels.push(quarterKey);
                }
                groupedData[quarterKey].revenue += parseFloat(item.revenue) || 0;
                groupedData[quarterKey].orders += parseInt(item.orders) || 0;
                groupedData[quarterKey].discounts += parseFloat(item.total_discounts) || 0;
            });
            
            // Sort by sortKey (year and quarter)
            labels.sort((a, b) => groupedData[a].sortKey - groupedData[b].sortKey);
            
            // Create data arrays from grouped data
            revenues = labels.map(label => groupedData[label].revenue);
            orders = labels.map(label => groupedData[label].orders);
            discounts = labels.map(label => groupedData[label].discounts);
        } else {
            // Monthly (default view)
            originalData.forEach(item => {
                const date = new Date(item.year, item.month - 1);
                const monthKey = date.toLocaleString('default', { month: 'short', year: '2-digit' });
                const sortKey = (item.year * 100) + parseInt(item.month);
                
                if (!groupedData[monthKey]) {
                    groupedData[monthKey] = { 
                        revenue: parseFloat(item.revenue) || 0,
                        orders: parseInt(item.orders) || 0,
                        discounts: parseFloat(item.total_discounts) || 0,
                        sortKey: sortKey
                    };
                    labels.push(monthKey);
                }
            });
            
            // Sort by sortKey (year and month)
            labels.sort((a, b) => groupedData[a].sortKey - groupedData[b].sortKey);
            
            // Create data arrays from grouped data
            revenues = labels.map(label => groupedData[label].revenue);
            orders = labels.map(label => groupedData[label].orders);
            discounts = labels.map(label => groupedData[label].discounts);
        }
        
        return {
            labels: labels,
            revenues: revenues,
            orders: orders,
            discounts: discounts
        };
    }
    
    // Function to update summary metrics displayed below chart
    function updateSummaryMetrics(data) {
        const totalRevenue = data.revenues.reduce((sum, val) => sum + val, 0);
        const totalOrders = data.orders.reduce((sum, val) => sum + val, 0);
        const avgOrderValue = totalOrders > 0 ? totalRevenue / totalOrders : 0;
        
        document.getElementById('periodRevenue').textContent = formatCurrency(totalRevenue);
        document.getElementById('periodOrders').textContent = formatNumber(totalOrders);
        document.getElementById('avgOrderValue').textContent = formatCurrency(avgOrderValue);
        
        // You could add percentage change calculations here
        // For example, comparing current period to previous period
    }
    
    // Start the initialization when document is ready
    document.addEventListener('DOMContentLoaded', loadChart);
})();
</script>
