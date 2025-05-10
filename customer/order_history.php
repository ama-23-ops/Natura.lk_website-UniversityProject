<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$userId = $_SESSION['user_id'];

// Fetch order history with actual discount amounts and tax
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// No need for discount calculation anymore since we're using values from database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #00796b 0%, #009688 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php'; ?>
    <div class="container mx-auto p-6">
        <!-- Title Section with Gradient -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-history text-4xl mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold">Order History</h1>
                    <p class="text-teal-100">Track all your previous orders</p>
                </div>
            </div>
        </div>

        <?php if (count($orders) > 0) : ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-teal-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                                    <i class="fas fa-hashtag mr-2"></i>Order ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                                    <i class="fas fa-calendar-alt mr-2"></i>Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                                    <i class="fas fa-dollar-sign mr-2"></i>Total Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                                    <i class="fas fa-tag mr-2"></i>Discount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                                    <i class="fas fa-receipt mr-2"></i>Net Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                                    <i class="fas fa-info-circle mr-2"></i>Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-teal-800 uppercase tracking-wider">
                                    <i class="fas fa-tasks mr-2"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($orders as $order) : ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#<?= $order['id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= date('M d, Y', strtotime($order['order_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        $<?= number_format($order['total_amount'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        $<?= number_format($order['discount_amount'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        $<?= number_format($order['total_amount'] - $order['discount_amount'] + $order['tax_amount'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $order['status'] === 'Completed' ? 'bg-green-100 text-green-800' : 
                                               ($order['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                'bg-gray-100 text-gray-800') ?>">
                                            <i class="fas fa-circle text-xs mr-1 mt-1"></i>
                                            <?= $order['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="/customer/order_details.php?id=<?= $order['id'] ?>" class="text-teal-600 hover:text-teal-800">
                                            <i class="fas fa-eye mr-1"></i>View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else : ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-center py-16">
                    <div class="text-teal-600 mb-4">
                        <i class="fas fa-shopping-bag text-8xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">No Orders Found</h2>
                    <p class="text-gray-600 mb-4">You haven't placed any orders yet</p>
                    <div class="inline-block">
                        <a href="/customer/products.php" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-full inline-flex items-center gap-2 transition duration-300">
                            <i class="fas fa-shopping-cart"></i> Start Shopping
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
</body>
</html>