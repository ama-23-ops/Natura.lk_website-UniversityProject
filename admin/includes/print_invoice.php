<?php
// Include database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Function to generate a printable invoice
function generateInvoice($orderId) {
    global $conn;
    
    // Get order details with user information and items
    $stmt = $conn->prepare("
        SELECT o.*, u.name, u.email, u.contact_number
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        return false;
    }
    
    // Get order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.title 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $netAmount = $order['total_amount'] - $order['discount_amount'];
    $finalAmount = $netAmount + $order['tax_amount'];
    
    ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $orderId ?></title>
    <style>
        /* Default styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-header h1 {
            color: #333;
            margin: 0;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f8f8;
        }
        .totals {
            float: right;
            width: 300px;
        }
        .totals table {
            margin-top: 20px;
        }
        .final-total {
            font-weight: bold;
            font-size: 1.2em;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #0d9488;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-button:hover {
            background-color: #0b7e75;
        }
        @media print {
            .print-button {
                display: none;
            }
            @page {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button">Print Invoice</button>
    <div class="invoice-header">
        <h1>INVOICE</h1>
        <p>Order #<?= $orderId ?></p>
        <p><?= date('F j, Y', strtotime($order['order_date'])) ?></p>
    </div>
    
    <div class="invoice-details">
        <div class="details-grid">
            <div>
                <strong>Bill To:</strong><br>
                <?= htmlspecialchars($order['name']) ?><br>
                <?= htmlspecialchars($order['email']) ?><br>
                <?= htmlspecialchars($order['contact_number']) ?><br>
                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
            </div>
            <div>
                <strong>Payment Method:</strong><br>
                <?= ucfirst($order['payment_method']) ?><br>
                <strong>Order Status:</strong><br>
                <?= $order['status'] ?>
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): 
                $itemTotal = ($item['price'] * $item['quantity']) - $item['discount_amount'];
            ?>
            <tr>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td>$<?= number_format($item['discount_amount'], 2) ?></td>
                <td>$<?= number_format($itemTotal, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td>$<?= number_format($order['total_amount'], 2) ?></td>
            </tr>
            <tr>
                <td>Discount:</td>
                <td>-$<?= number_format($order['discount_amount'], 2) ?></td>
            </tr>
            <tr>
                <td>Net Amount:</td>
                <td>$<?= number_format($netAmount, 2) ?></td>
            </tr>
            <tr>
                <td>Tax (10%):</td>
                <td>$<?= number_format($order['tax_amount'], 2) ?></td>
            </tr>
            <tr class="final-total">
                <td>Total:</td>
                <td>$<?= number_format($finalAmount, 2) ?></td>
            </tr>
        </table>
    </div>
</body>
</html>
<?php
    return ob_get_clean();
}

// Verify admin access
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    die("Access denied");
}

// Only process if an order ID is provided
if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];
    $invoice = generateInvoice($orderId);
    if ($invoice) {
        header('Content-Type: text/html');
        echo $invoice;
        exit();
    } else {
        die("Invoice not found");
    }
}
?>