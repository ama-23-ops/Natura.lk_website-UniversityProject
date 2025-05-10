<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$userId = $_SESSION['user_id'];

// Get cart items with product details
$stmt = $conn->prepare("
    SELECT c.*, p.title, p.image, p.sale_price as price, p.discount 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate cart totals
$cart_total = 0;
$cart_items_count = 0;
$total_discount = 0;

foreach ($cartItems as $item) {
    $unit_discount = $item['price'] * ($item['discount'] / 100);
    $discounted_price = $item['price'] - $unit_discount;
    $cart_total += $item['price'] * $item['quantity'];
    $total_discount += $unit_discount * $item['quantity'];
    $cart_items_count += $item['quantity'];
}
$net_total = $cart_total - $total_discount;

// Apply tax (10%)
$tax = $net_total * 0.10;

// Calculate final total amount after tax
$final_total = $net_total + $tax;

// Handle shipping address submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['continue_to_payment'])) {
    $shippingAddress = $_POST['shipping_address'];
    
    // Store shipping address in session for use in later steps
    $_SESSION['checkout_shipping_address'] = $shippingAddress;
    $_SESSION['checkout_cart_total'] = $cart_total;
    $_SESSION['checkout_discount'] = $total_discount;
    $_SESSION['checkout_net_total'] = $net_total;
    $_SESSION['checkout_tax'] = $tax;
    $_SESSION['checkout_final_total'] = $final_total;
    
    // Redirect to payment details page
    header("Location: /customer/payment_details.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Shipping Details</title>
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
    </style>
</head>
<body class="bg-gray-100">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php'; ?>
    <div class="container mx-auto p-6">
        <!-- Title Section with Gradient -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-shopping-cart text-4xl mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold">Checkout</h1>
                    <p class="text-teal-100">Complete your purchase</p>
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
                <div class="step-line step-line-right"></div>
                <span class="step-text active">Shipping</span>
            </div>
            <div class="step">
                <div class="step-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="step-line step-line-left"></div>
                <div class="step-line step-line-right"></div>
                <span class="step-text">Payment</span>
            </div>
            <div class="step">
                <div class="step-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-line step-line-left"></div>
                <div class="step-line step-line-right"></div>
                <span class="step-text">Confirmation</span>
            </div>
        </div>

        <?php if (empty($cartItems)) : ?>
            <!-- Empty State -->
            <div class="text-center py-16 bg-white rounded-lg shadow-md">
                <div class="text-teal-600 mb-4">
                    <i class="fas fa-shopping-cart text-8xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Your Cart is Empty</h2>
                <p class="text-gray-600 mb-8">Add items to your cart to get started</p>
                <div class="inline-block">
                    <a href="/customer/products.php" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-full inline-flex items-center gap-2 transition duration-300">
                        <i class="fas fa-shopping-cart"></i> Start Shopping
                    </a>
                </div>
            </div>
        <?php else : ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Shipping Address Form -->
                <div class="md:col-span-2">
                    <form method="post" class="space-y-6">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                                <i class="fas fa-map-marker-alt mr-2 text-teal-600"></i>
                                Shipping Address
                            </h2>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                        <input type="text" id="full_name" name="full_name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    </div>
                                </div>

                                <div>
                                    <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Complete Address</label>
                                    <textarea 
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" 
                                        name="shipping_address" 
                                        id="shipping_address" 
                                        rows="3"
                                        placeholder="Street address, apartment, city, state, and zip code"
                                        required
                                    ></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                        <input type="text" id="city" name="city" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    </div>
                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                        <input type="text" id="state" name="state" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    </div>
                                    <div>
                                        <label for="zip" class="block text-sm font-medium text-gray-700 mb-1">ZIP Code</label>
                                        <input type="text" id="zip" name="zip" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button 
                                type="submit" 
                                name="continue_to_payment" 
                                class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-full inline-flex items-center gap-2 transition duration-300"
                            >
                                Continue to Payment <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                        <h2 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                            <i class="fas fa-shopping-bag mr-2 text-teal-600"></i>
                            Order Summary
                        </h2>
                        <div class="space-y-4 mb-6">
                            <?php foreach ($cartItems as $item) : 
                                $unit_discount = $item['price'] * ($item['discount'] / 100);
                                $discounted_price = $item['price'] - $unit_discount;
                                $itemTotal = $discounted_price * $item['quantity'];
                            ?>
                                <div class="flex justify-between pb-2 border-b border-gray-200">
                                    <div>
                                        <p class="font-medium"><?= $item['title'] ?></p>
                                        <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?></p>
                                        <?php if ($item['discount'] > 0) : ?>
                                            <p class="text-xs text-red-500"><?= $item['discount'] ?>% OFF</p>
                                        <?php endif; ?>
                                    </div>
                                    <p class="font-medium">$<?= number_format($itemTotal, 2) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between py-1">
                                <p>Subtotal</p>
                                <p>$<?= number_format($cart_total, 2) ?></p>
                            </div>
                            <?php if ($total_discount > 0) : ?>
                            <div class="flex justify-between py-1 text-red-500">
                                <p>Discount</p>
                                <p>-$<?= number_format($total_discount, 2) ?></p>
                            </div>
                            <?php endif; ?>
                            <div class="flex justify-between py-1">
                                <p>Net Price</p>
                                <p>$<?= number_format($net_total, 2) ?></p>
                            </div>
                            <div class="flex justify-between py-1">
                                <p>Tax (10%)</p>
                                <p>$<?= number_format($tax, 2) ?></p>
                            </div>
                            <div class="flex justify-between py-1">
                                <p>Shipping</p>
                                <p>Free</p>
                            </div>
                            <div class="flex justify-between py-3 font-bold text-lg">
                                <p>Total</p>
                                <p class="text-teal-600">$<?= number_format($final_total, 2) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
</body>
</html>