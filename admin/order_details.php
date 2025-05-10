<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header("Location: /admin/orders.php");
    exit();
}

$orderId = $_GET['id'];

// Get order details with user information
$stmt = $conn->prepare("
    SELECT o.*,
           oi.product_id, oi.quantity, oi.price, oi.discount_amount as item_discount,
           p.title, p.image,
           u.name as customer_name, u.email as customer_email, u.contact_number
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id 
    JOIN products p ON oi.product_id = p.id 
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($orderItems)) {
    header("Location: /admin/orders.php");
    exit();
}

// Get order summary from first row
$order = [
    'id' => $orderItems[0]['id'],
    'total_amount' => $orderItems[0]['total_amount'],
    'discount_amount' => $orderItems[0]['discount_amount'],
    'tax_amount' => $orderItems[0]['tax_amount'],
    'shipping_address' => $orderItems[0]['shipping_address'],
    'status' => $orderItems[0]['status'],
    'order_date' => $orderItems[0]['order_date'],
    'payment_method' => $orderItems[0]['payment_method'],
    'customer_name' => $orderItems[0]['customer_name'],
    'customer_email' => $orderItems[0]['customer_email'],
    'contact_number' => $orderItems[0]['contact_number']
];

// Calculate totals
$netAmount = $order['total_amount'] - $order['discount_amount'];
$finalAmount = $netAmount + $order['tax_amount'];

// Include the notification handler
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/order_notification.php';

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $orderId]);
    
    // Send notification email
    sendOrderStatusNotification($orderId, $newStatus);
    
    // Refresh page to show updated status
    header("Location: /admin/order_details.php?id=$orderId&status_updated=1");
    exit();
}

$pageTitle = 'Order Details';

ob_start();
?>

<div class="container mx-auto p-6">
    <?php if (isset($_GET['status_updated'])) : ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-2"></i>
            <p class="text-green-600">Order status has been successfully updated.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Order Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Order #<?= $order['id'] ?></h1>
                <p class="text-gray-600">Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
            </div>
            <div class="flex items-center gap-4">
                <a 
                    href="/admin/includes/print_invoice.php?order_id=<?= $order['id'] ?>" 
                    target="_blank"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md flex items-center gap-2"
                >
                    <i class="fas fa-print"></i>
                    Print Invoice
                </a>
                <form method="post" class="flex items-center gap-4">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Processing" <?= $order['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="Shipped" <?= $order['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="Delivered" <?= $order['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <button 
                        type="submit" 
                        name="update_status" 
                        class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md"
                    >
                        Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Order Items</h2>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($orderItems as $item) : ?>
                            <div class="py-4 flex items-center">
                                <div class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                                    <?php if (!empty($item['image'])) : ?>
                                        <img src="/uploads/<?= $item['image'] ?>" alt="<?= $item['title'] ?>" class="w-full h-full object-cover">
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4 flex-grow">
                                    <h3 class="font-medium"><?= $item['title'] ?></h3>
                                    <p class="text-gray-600">
                                        Quantity: <?= $item['quantity'] ?> × $<?= number_format($item['price'], 2) ?>
                                    </p>
                                    <?php if ($item['item_discount'] > 0): ?>
                                        <p class="text-green-600">Discount: -$<?= number_format($item['item_discount'], 2) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold">$<?= number_format(($item['price'] * $item['quantity']) - $item['item_discount'], 2) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Customer Details</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-600">Name</p>
                        <p class="font-medium"><?= htmlspecialchars($order['customer_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Email</p>
                        <p class="font-medium"><?= htmlspecialchars($order['customer_email']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Phone</p>
                        <p class="font-medium"><?= htmlspecialchars($order['contact_number']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-3">
                        <span class="text-gray-600">Subtotal</span>
                        <span>$<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-3">
                        <span class="text-gray-600">Discount</span>
                        <span class="text-green-600">-$<?= number_format($order['discount_amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-3">
                        <span class="text-gray-600">Net Amount</span>
                        <span>$<?= number_format($netAmount, 2) ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-3">
                        <span class="text-gray-600">Tax (10%)</span>
                        <span>$<?= number_format($order['tax_amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between pt-3">
                        <span class="font-bold">Total</span>
                        <span class="font-bold text-xl text-teal-600">$<?= number_format($finalAmount, 2) ?></span>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <div>
                        <h3 class="font-medium mb-2">Shipping Address</h3>
                        <p class="text-gray-600 whitespace-pre-line"><?= htmlspecialchars($order['shipping_address']) ?></p>
                    </div>
                    <div>
                        <h3 class="font-medium mb-2">Payment Method</h3>
                        <p class="flex items-center">
                            <?php if ($order['payment_method'] == 'credit') : ?>
                                <i class="fab fa-cc-visa text-blue-800 text-2xl mr-2"></i>
                                <span>Credit Card</span>
                            <?php else : ?>
                                <i class="fab fa-paypal text-blue-600 text-2xl mr-2"></i>
                                <span>PayPal</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/dashboard_layout.php';
?>