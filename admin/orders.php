<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 9;
$offset = ($page - 1) * $itemsPerPage;

// Get total count for pagination
$totalCountStmt = $conn->query("SELECT COUNT(*) FROM orders");
$totalOrders = $totalCountStmt->fetchColumn();
$totalPages = ceil($totalOrders / $itemsPerPage);

// Fetch paginated orders with user details and totals
$stmt = $conn->prepare("
    SELECT o.*, 
           u.name as user_name, 
           u.email as user_email,
           u.contact_number,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC
    LIMIT :offset, :limit
");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get stats from all orders
$statsStmt = $conn->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(total_amount - discount_amount + tax_amount) as total_revenue
    FROM orders
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'MANAGE ORDERS';
ob_start();
?>

<style>
    .teal-gradient {
        background: linear-gradient(135deg, #00796b 0%, #009688 100%);
    }
</style>

<div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
    <div class="flex items-center">
        <i class="fas fa-shopping-cart text-4xl mr-4"></i>
        <div>
            <h1 class="text-3xl font-bold">MANAGE ORDERS</h1>
            <p class="text-teal-100">VIEW AND PROCESS CUSTOMER ORDERS</p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">TOTAL ORDERS</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['total_orders'] ?></h3>
            </div>
            <div class="bg-teal-100 rounded-full p-3">
                <i class="fas fa-shopping-bag text-teal-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">PENDING ORDERS</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['pending_orders'] ?></h3>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">TOTAL REVENUE</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">$<?= number_format($stats['total_revenue'], 2) ?></h3>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Orders Management -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">ORDERS</h2>
        <div class="flex items-center gap-4">
            <select id="status-filter" class="rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
                <option value="Cancelled">Cancelled</option>
            </select>
            <input 
                type="text" 
                id="search-orders" 
                placeholder="Search orders..." 
                class="rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
            >
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($orders as $order) : 
            $netAmount = $order['total_amount'] - $order['discount_amount'];
            $finalAmount = $netAmount + $order['tax_amount'];

            // Get status color classes
            $statusColors = [
                'Pending' => 'bg-yellow-100 text-yellow-800',
                'Processing' => 'bg-blue-100 text-blue-800',
                'Shipped' => 'bg-indigo-100 text-indigo-800',
                'Delivered' => 'bg-green-100 text-green-800',
                'Cancelled' => 'bg-red-100 text-red-800',
            ];
            $statusColor = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800';
        ?>
            <div class="bg-white rounded-lg shadow hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 order-card" data-status="<?= strtolower($order['status']) ?>">
                <div class="p-6">
                    <!-- Order Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Order #<?= $order['id'] ?></h3>
                            <p class="text-sm text-gray-500"><?= date('M d, Y', strtotime($order['order_date'])) ?></p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">
                            <?= $order['status'] ?>
                        </span>
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center">
                                    <i class="fas fa-user text-teal-500"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($order['user_name']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($order['user_email']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($order['contact_number']) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Items</span>
                            <span class="font-medium"><?= $order['items_count'] ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="font-medium">$<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Discount</span>
                            <span class="text-green-600">-$<?= number_format($order['discount_amount'], 2) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tax</span>
                            <span class="font-medium">$<?= number_format($order['tax_amount'], 2) ?></span>
                        </div>
                        <div class="flex justify-between text-sm font-bold pt-2 border-t border-gray-200">
                            <span>Total</span>
                            <span class="text-teal-600">$<?= number_format($finalAmount, 2) ?></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="/admin/order_details.php?id=<?= $order['id'] ?>" 
                           class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                            <i class="fas fa-eye mr-2"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-6 flex items-center justify-between border-t border-gray-200 bg-white pt-4">
        <div class="flex items-center">
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium"><?= min(($page - 1) * $itemsPerPage + 1, $totalOrders) ?></span>
                to
                <span class="font-medium"><?= min($page * $itemsPerPage, $totalOrders) ?></span>
                of
                <span class="font-medium"><?= $totalOrders ?></span>
                results
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Previous
                </a>
            <?php endif; ?>
            
            <?php
            $range = 2;
            for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++):
            ?>
                <a href="?page=<?= $i ?>" class="relative inline-flex items-center <?= $i === $page ? 'bg-teal-600 text-white' : 'bg-white text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50' ?> px-3 py-2 text-sm font-semibold rounded-md">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Next
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Existing script -->
<script>
document.getElementById('status-filter').addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const cards = document.querySelectorAll('.order-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const cardStatus = card.dataset.status;
        if (!status || cardStatus === status) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    document.getElementById('no-results').style.display = visibleCount === 0 ? 'block' : 'none';
    updatePaginationVisibility(visibleCount > 0);
});

document.getElementById('search-orders').addEventListener('input', function() {
    const searchText = this.value.toLowerCase();
    const cards = document.querySelectorAll('.order-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(searchText)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    document.getElementById('no-results').style.display = visibleCount === 0 ? 'block' : 'none';
    updatePaginationVisibility(visibleCount > 0);
});

// Update pagination links when filtering
function updatePaginationVisibility(hasResults) {
    const paginationContainer = document.querySelector('.pagination');
    if (paginationContainer) {
        paginationContainer.style.display = hasResults ? '' : 'none';
    }
}
</script>

<?php
$content = ob_get_clean();
include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/dashboard_layout.php';
?>
