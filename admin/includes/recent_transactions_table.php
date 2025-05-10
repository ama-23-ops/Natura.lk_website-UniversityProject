<?php
// Get recent transactions
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC 
    LIMIT 5
");
$stmt->execute();
$recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="overflow-x-auto">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($recentTransactions as $transaction): 
                $netAmount = $transaction['total_amount'] - $transaction['discount_amount'];
                $finalAmount = $netAmount + $transaction['tax_amount'];
            ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #<?= $transaction['id'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($transaction['user_name']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('M d, Y H:i', strtotime($transaction['order_date'])) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        $<?= number_format($finalAmount, 2) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php
                            switch ($transaction['status']) {
                                case 'Pending':
                                    echo 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'Processing':
                                    echo 'bg-blue-100 text-blue-800';
                                    break;
                                case 'Shipped':
                                    echo 'bg-indigo-100 text-indigo-800';
                                    break;
                                case 'Delivered':
                                    echo 'bg-green-100 text-green-800';
                                    break;
                                case 'Cancelled':
                                    echo 'bg-red-100 text-red-800';
                                    break;
                                default:
                                    echo 'bg-gray-100 text-gray-800';
                            }
                            ?>">
                            <?= $transaction['status'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="order_details.php?id=<?= $transaction['id'] ?>" 
                           class="text-teal-600 hover:text-teal-900">
                            View Details
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>