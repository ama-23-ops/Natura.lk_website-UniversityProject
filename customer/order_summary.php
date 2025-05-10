<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$userId = $_SESSION['user_id'];

// Check if previous steps have been completed
if (!isset($_SESSION['checkout_shipping_address']) || !isset($_SESSION['checkout_final_total']) || !isset($_SESSION['payment_method'])) {
    header("Location: /customer/checkout.php");
    exit();
}

// Get data from session
$shippingAddress = $_SESSION['checkout_shipping_address'];
$cart_total = $_SESSION['checkout_cart_total'];
$total_discount = $_SESSION['checkout_discount'];
$net_total = $_SESSION['checkout_net_total'];
$tax = $_SESSION['checkout_tax'];
$final_total = $_SESSION['checkout_final_total'];
$paymentMethod = $_SESSION['payment_method'];
$cardNumber = isset($_SESSION['card_number']) ? $_SESSION['card_number'] : '';
$cardName = isset($_SESSION['card_name']) ? $_SESSION['card_name'] : '';

// Mask the card number for security
if (!empty($cardNumber)) {
    $cardLastFour = substr($cardNumber, -4);
    $maskedCard = "•••• •••• •••• " . $cardLastFour;
} else {
    $maskedCard = "";
}

// Fetch cart items
$stmt = $conn->prepare("SELECT c.*, p.title, p.sale_price, p.discount, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle order submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    try {
        $conn->beginTransaction();
        
        // Create order with tax amount
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, discount_amount, tax_amount, shipping_address, status, payment_method) VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
        $stmt->execute([
            $userId, 
            $cart_total,         // Original total before discount
            $total_discount,     // Total discount amount
            $tax,               // Tax amount
            $shippingAddress, 
            $paymentMethod
        ]);
        $orderId = $conn->lastInsertId();
        
        // Add order items with individual discounts
        foreach ($cartItems as $item) {
            // Calculate item discount
            $itemDiscount = ($item['sale_price'] * ($item['discount'] / 100)) * $item['quantity'];
            
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount_amount) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['sale_price'],
                $itemDiscount
            ]);
            
            // Update product stock
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        $conn->commit();
        
        // Clear checkout session data
        unset($_SESSION['checkout_shipping_address']);
        unset($_SESSION['checkout_cart_total']);
        unset($_SESSION['checkout_discount']);
        unset($_SESSION['checkout_net_total']);
        unset($_SESSION['checkout_tax']);
        unset($_SESSION['checkout_final_total']);
        unset($_SESSION['payment_method']);
        unset($_SESSION['card_number']);
        unset($_SESSION['card_name']);
        unset($_SESSION['card_expiry']);
        
        // Set success flag in session
        $_SESSION['order_success'] = true;
        $_SESSION['order_id'] = $orderId;
        
        // Redirect to success page with order details
        header("Location: /customer/order_details.php?id=$orderId&success=1");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        // Handle error - you might want to show an error message to the user
        $_SESSION['error'] = "Failed to place order. Please try again.";
        header("Location: /customer/order_summary.php");
        exit();
    }
}

// Get success message if order was just placed
$orderSuccess = isset($_SESSION['order_success']) && $_SESSION['order_success'];
$successOrderId = isset($_SESSION['order_id']) ? $_SESSION['order_id'] : null;

// Clear the success flags after showing the message
if ($orderSuccess) {
    unset($_SESSION['order_success']);
    unset($_SESSION['order_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
        }
        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 33.333%;
        }
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        .step-icon.active {
            background-color: #0d9488;
            color: white;
        }
        .step-line {
            height: 4px;
            background-color: #e2e8f0;
            width: 100%;
            position: relative;
            top: -22px;
            z-index: 0;
        }
        .step-line.active {
            background-color: #0d9488;
        }
        .step:first-child .step-line-left,
        .step:last-child .step-line-right {
            opacity: 0;
        }
        .step-text {
            font-weight: 500;
            color: #64748b;
        }
        .step-text.active {
            color: #0d9488;
            font-weight: bold;
        }
        .animated-check {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        .summary-section {
            position: relative;
            overflow: hidden;
        }
        .summary-section:after {
            content: '';
            position: absolute;
            height: 2px;
            background: linear-gradient(90deg, #0d9488 0%, transparent 100%);
            width: 100%;
            left: 0;
            bottom: 0;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php'; ?>
    <div class="container mx-auto p-6">
        <?php if ($orderSuccess) : ?>
            <!-- Success Message -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-4xl text-green-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-green-800 mb-2">Order Successfully Confirmed!</h2>
                <p class="text-green-600 mb-4">Thank you for your purchase. Your order #<?= $successOrderId ?> has been confirmed.</p>
                <div class="flex justify-center gap-4">
                    <a href="/customer/order_details.php?id=<?= $successOrderId ?>" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full inline-flex items-center gap-2">
                        <i class="fas fa-eye"></i> View Order Details
                    </a>
                    <a href="/customer/products.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-full inline-flex items-center gap-2">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Title Section with Gradient -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-clipboard-check text-4xl mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold">Order Summary</h1>
                    <p class="text-teal-100">Review and confirm your order</p>
                </div>
            </div>
        </div>
        
        <!-- Checkout Steps -->
        <div class="checkout-steps mb-8">
            <div class="step">
                <div class="step-icon active">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="step-line step-line-left"></div>
                <div class="step-line step-line-right active"></div>
                <span class="step-text active">Shipping</span>
            </div>
            <div class="step">
                <div class="step-icon active">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="step-line step-line-left active"></div>
                <div class="step-line step-line-right active"></div>
                <span class="step-text active">Payment</span>
            </div>
            <div class="step">
                <div class="step-icon active">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-line step-line-left active"></div>
                <div class="step-line step-line-right"></div>
                <span class="step-text active">Confirmation</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Order Details -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Summary Header -->
                    <div class="bg-gray-50 p-6">
                        <h2 class="text-2xl font-bold mb-2 text-gray-800">Order Summary</h2>
                        <p class="text-gray-600">Please review your order details before confirming.</p>
                    </div>
                    
                    <!-- Products List -->
                    <div class="p-6 summary-section">
                        <h3 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                            <i class="fas fa-shopping-cart mr-2 text-teal-600"></i>
                            Items in Your Order
                        </h3>
                        
                        <div class="space-y-4">
                            <?php foreach ($cartItems as $item) : 
                                $itemTotal = $item['sale_price'] * $item['quantity'];
                            ?>
                                <div class="flex items-center border-b border-gray-200 pb-4">
                                    <div class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                                        <?php if (!empty($item['image'])) : ?>
                                            <img src="/uploads/<?= $item['image'] ?>" alt="<?= $item['title'] ?>" class="w-full h-full object-cover">
                                        <?php else : ?>
                                            <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4 flex-grow">
                                        <h4 class="font-medium"><?= $item['title'] ?></h4>
                                        <p class="text-gray-500">Quantity: <?= $item['quantity'] ?> x $<?= number_format($item['sale_price'], 2) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold">$<?= number_format($itemTotal, 2) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Shipping Information -->
                    <div class="p-6 summary-section">
                        <h3 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                            <i class="fas fa-map-marker-alt mr-2 text-teal-600"></i>
                            Shipping Information
                        </h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <p class="whitespace-pre-line"><?= htmlspecialchars($shippingAddress) ?></p>
                        </div>
                    </div>
                    
                    <!-- Payment Information -->
                    <div class="p-6 summary-section">
                        <h3 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                            <i class="fas fa-credit-card mr-2 text-teal-600"></i>
                            Payment Method
                        </h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <?php if ($paymentMethod == 'credit') : ?>
                                <div class="flex items-center">
                                    <i class="fab fa-cc-visa text-blue-800 text-2xl mr-3"></i>
                                    <div>
                                        <p class="font-medium"><?= htmlspecialchars($maskedCard) ?></p>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($cardName) ?></p>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="flex items-center">
                                    <i class="fab fa-paypal text-blue-600 text-2xl mr-3"></i>
                                    <p class="font-medium">PayPal</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Confirmation Button -->
                    <div class="p-6 bg-gray-50">
                        <form method="post">
                            <div class="flex items-center justify-between">
                                <a href="/customer/payment_details.php" class="text-teal-600 hover:underline flex items-center">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to Payment
                                </a>
                                <button 
                                    type="submit" 
                                    name="place_order" 
                                    class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-8 rounded-full inline-flex items-center gap-2 transition duration-300"
                                >
                                    <div class="animated-check">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    Confirm Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-bold mb-6 flex items-center text-gray-800 justify-between">
                        <span>
                            <i class="fas fa-receipt mr-2 text-teal-600"></i>
                            Order Total
                        </span>
                        <span class="text-sm font-normal text-teal-600">Final</span>
                    </h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <p class="text-gray-600">Subtotal</p>
                            <p class="font-medium">$<?= number_format($cart_total, 2) ?></p>
                        </div>
                        <?php if ($total_discount > 0) : ?>
                        <div class="flex justify-between">
                            <p class="text-gray-600">Discount</p>
                            <p class="text-red-500">-$<?= number_format($total_discount, 2) ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <p class="text-gray-600">Net Price</p>
                            <p class="font-medium">$<?= number_format($net_total, 2) ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-gray-600">Tax (10%)</p>
                            <p class="font-medium">$<?= number_format($tax, 2) ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-gray-600">Shipping</p>
                            <p>Free</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center">
                            <p class="font-bold text-lg">Total</p>
                            <p class="font-bold text-xl text-teal-600">$<?= number_format($final_total, 2) ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                            <div class="flex items-start">
                                <i class="fas fa-truck text-green-500 mt-1 mr-3"></i>
                                <div>
                                    <p class="font-medium text-green-800">Delivery Estimate</p>
                                    <p class="text-sm text-green-600">Your order will arrive within 3-5 business days</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                <div>
                                    <p class="font-medium text-blue-800">Refund Policy</p>
                                    <p class="text-sm text-blue-600">30-day easy return policy for all products</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
</body>
</html>