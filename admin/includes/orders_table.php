<div class="min-h-screen">
  <style>
    .teal-gradient {
      background: linear-gradient(135deg, #00796b 0%, #009688 100%);
    }
  </style>
  <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
    <div class="flex items-center">
      <i class="fas fa-shopping-cart text-4xl mr-4"></i>
      <div>
        <h1 class="text-3xl font-bold">Manage Orders</h1>
        <p class="text-teal-100">Track and manage customer orders and their status</p>
      </div>
    </div>
  </div>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Orders</h2>
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

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Order ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Customer
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Amount
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Discount
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tax
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Final Total
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($orders as $order) : 
                    $netAmount = $order['total_amount'] - $order['discount_amount'];
                    $finalAmount = $netAmount + $order['tax_amount'];
                ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #<?= $order['id'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($order['user_name']) ?>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($order['user_email']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('M d, Y', strtotime($order['order_date'])) ?>
                            <div class="text-xs text-gray-400"><?= date('h:i A', strtotime($order['order_date'])) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            $<?= number_format($order['total_amount'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                            -$<?= number_format($order['discount_amount'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            $<?= number_format($order['tax_amount'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            $<?= number_format($finalAmount, 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php
                                switch ($order['status']) {
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
                                ?>
                            ">
                                <?= $order['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="/admin/order_details.php?id=<?= $order['id'] ?>" class="text-teal-600 hover:text-teal-900">
                                View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('status-filter').addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const statusCell = row.querySelector('td:nth-child(8)');
        const statusText = statusCell.textContent.trim().toLowerCase();
        
        if (!status || statusText === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

document.getElementById('search-orders').addEventListener('input', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
