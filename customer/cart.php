<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$userId = $_SESSION['user_id'];

// Handle add to cart
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Make sure quantity is at least 1
    $quantity = max(1, $quantity);
    
    // Get product info and check if it exists
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        // Check if product is already in cart
        $cartStmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $cartStmt->execute([$userId, $product_id]);
        $cartItem = $cartStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cartItem) {
            // Update existing cart item
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $userId, $product_id]);
        } else {
            // Insert new cart item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $product_id, $quantity]);
        }
    }
    
    // Redirect back to previous page or products page
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/customer/products.php';
    header("Location: $redirect_url");
    exit();
}

// Handle remove from cart
if (isset($_POST['remove_from_cart']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $product_id]);
    
    // Redirect back to cart page
    header("Location: /customer/cart.php");
    exit();
}

// Handle update quantity
if (isset($_POST['update_quantity']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);
    
    if ($quantity <= 0) {
        // Remove item if quantity is zero or negative
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $product_id]);
    } else {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $userId, $product_id]);
    }
    
    // Redirect back to cart page
    header("Location: /customer/cart.php");
    exit();
}

// Handle AJAX update quantity
if (isset($_POST['ajax_update_quantity']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);
    $response = ['success' => false];
    
    if ($quantity <= 0) {
        // Remove item if quantity is zero or negative
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $product_id]);
        $response['removed'] = true;
    } else {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $userId, $product_id]);
    }
    
    // Recalculate cart totals
    $stmt = $conn->prepare("
        SELECT c.*, p.title, p.image, p.sale_price as price, p.discount 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $updatedCartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate new cart totals
    $cart_total = 0;
    $cart_items_count = 0;
    $total_discount = 0;
    $item_total = 0;
    
    foreach ($updatedCartItems as $item) {
        $unit_discount = $item['price'] * ($item['discount'] / 100);
        $discounted_price = $item['price'] - $unit_discount;
        $cart_total += $item['price'] * $item['quantity'];
        $total_discount += $unit_discount * $item['quantity'];
        $cart_items_count += $item['quantity'];
        
        if ($item['product_id'] == $product_id) {
            $item_total = $discounted_price * $item['quantity'];
        }
    }
    
    $net_total = $cart_total - $total_discount;
    
    $response['success'] = true;
    $response['item_total'] = number_format($item_total, 2);
    $response['cart_total'] = number_format($cart_total, 2);
    $response['total_discount'] = number_format($total_discount, 2);
    $response['net_total'] = number_format($net_total, 2);
    $response['cart_items_count'] = $cart_items_count;
    
    // Return JSON response for AJAX request
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #00796b 0%, #009688 100%);
        }
        .quantity-loading {
            background-color: #e8f5e9;
            transition: background-color 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php'; ?>
    <div class="container mx-auto p-6 ">
        <!-- Title Section with Gradient -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-shopping-cart text-4xl mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold">Your Shopping Cart</h1>
                    <p class="text-teal-100">Items you've added to cart</p>
                </div>
            </div>
        </div>
        
        <?php if (empty($cartItems)): ?>
            <!-- Empty cart state -->
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-shopping-cart text-6xl"></i>
                </div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your cart is empty</h2>
                <p class="text-gray-600 mb-6">Looks like you haven't added any products to your cart yet.</p>
                <a href="/customer/products.php" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-lg inline-flex items-center gap-2">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Cart with items -->
            <div class="md:flex md:gap-6">
                <!-- Cart items -->
                <div class="md:w-2/3">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                        <!-- Table header -->
                        <div class="hidden md:grid md:grid-cols-12 bg-gray-50 p-4 border-b border-gray-200 font-semibold text-gray-600">
                            <div class="md:col-span-5">Product</div>
                            <div class="md:col-span-3 text-center">Price</div>
                            <div class="md:col-span-2 text-center">Quantity</div>
                            <div class="md:col-span-2 text-right">Total</div>
                        </div>
                        
                        <!-- Cart items -->
                        <?php foreach($cartItems as $item): ?>
                            <div class="grid md:grid-cols-12 gap-4 p-4 items-center border-b border-gray-200 last:border-b-0" id="cart-item-<?= $item['product_id'] ?>">
                                <!-- Product info -->
                                <div class="md:col-span-5 flex items-center">
                                    <div class="w-20 h-20 flex-shrink-0 mr-4">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="/uploads/<?= $item['image'] ?>" alt="<?= $item['title'] ?>" class="w-full h-full object-cover rounded">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gray-200 flex items-center justify-center rounded">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?= $item['title'] ?></h3>
                                        <?php if ($item['discount'] > 0): ?>
                                            <div class="text-red-500 text-sm font-medium mb-1">
                                                <i class="fas fa-tag mr-1"></i><?= $item['discount'] ?>% OFF
                                            </div>
                                        <?php endif; ?>
                                        <form method="post" class="mt-1">
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <input type="hidden" name="remove_from_cart" value="1">
                                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 flex items-center transition duration-150 ease-in-out">
                                                <i class="fas fa-trash-alt mr-1"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Price -->
                                <div class="md:col-span-3 text-center space-y-1">
                                    <span class="md:hidden text-gray-600 mr-2">Price:</span>
                                    <?php if ($item['discount'] > 0): ?>
                                        <div>
                                            <span class="line-through text-gray-400 text-sm">$<?= number_format($item['price'], 2) ?></span>
                                        </div>
                                        <div>
                                            <span class="text-lg font-semibold text-gray-900">$<?= number_format($item['price'] - ($item['price'] * $item['discount'] / 100), 2) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-lg font-semibold text-gray-900">$<?= number_format($item['price'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Quantity -->
                                <div class="md:col-span-2">
                                    <div class="flex items-center justify-center space-x-2">
                                        <input type="number" 
                                               data-product-id="<?= $item['product_id'] ?>" 
                                               value="<?= $item['quantity'] ?>" 
                                               min="1" 
                                               class="quantity-input w-16 p-2 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                    </div>
                                </div>
                                
                                <!-- Total -->
                                <div class="md:col-span-2 text-right">
                                    <span class="md:hidden text-gray-600 mr-2">Total:</span>
                                    <?php 
                                    $unit_discount = $item['price'] * ($item['discount'] / 100);
                                    $line_total = ($item['price'] - $unit_discount) * $item['quantity'];
                                    ?>
                                    <span class="text-lg font-semibold text-gray-900 item-total" data-product-id="<?= $item['product_id'] ?>">$<?= number_format($line_total, 2) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Continue shopping button -->
                        <div class="p-4 border-t border-gray-200 bg-gray-50">
                            <a href="/customer/products.php" class="inline-flex items-center px-4 py-2 text-sm font-medium text-teal-700 bg-teal-50 rounded-lg hover:bg-teal-100 transition duration-150 ease-in-out">
                                <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Cart summary -->
                <div class="md:w-1/3">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Order Summary</h2>
                        
                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal (<span id="cart-items-count"><?= $cart_items_count ?></span> items)</span>
                                <span id="cart-subtotal">$<?= number_format($cart_total, 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Discount</span>
                                <span class="text-red-500" id="cart-discount">-$<?= number_format($total_discount, 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Net Price</span>
                                <span id="cart-net">$<?= number_format($net_total, 2) ?></span>
                            </div>
                            <div class="border-t border-gray-200 pt-3 flex justify-between font-semibold">
                                <span>Total</span>
                                <span id="cart-total">$<?= number_format($net_total, 2) ?></span>
                            </div>
                        </div>
                        
                        <a href="/customer/checkout.php" class="flex items-center justify-center w-full bg-teal-600 hover:bg-teal-700 text-white font-medium py-3 px-4 rounded-lg transition duration-150 ease-in-out">
                            <i class="fas fa-credit-card mr-2"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener to all quantity inputs
            const quantityInputs = document.querySelectorAll('.quantity-input');
            
            quantityInputs.forEach(input => {
                let debounceTimer;
                
                input.addEventListener('change', function() {
                    updateCartItem(this);
                });
                
                // For when users type directly instead of using arrows
                input.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        updateCartItem(this);
                    }, 500); // 500ms debounce
                });
            });
            
            function updateCartItem(input) {
                const productId = input.getAttribute('data-product-id');
                const quantity = input.value;
                
                if (quantity < 1) {
                    input.value = 1;
                    return;
                }
                
                // Visual feedback that update is happening
                input.classList.add('quantity-loading');
                
                // Create and send FormData
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', quantity);
                formData.append('ajax_update_quantity', '1');
                
                fetch('/customer/cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // If item was removed due to quantity <= 0
                        if (data.removed) {
                            const cartItem = document.getElementById(`cart-item-${productId}`);
                            if (cartItem) {
                                cartItem.remove();
                            }
                            // If no items left, reload page to show empty cart message
                            if (data.cart_items_count === 0) {
                                window.location.reload();
                                return;
                            }
                        }
                        
                        // Update item total
                        const itemTotalElement = document.querySelector(`.item-total[data-product-id="${productId}"]`);
                        if (itemTotalElement) {
                            itemTotalElement.textContent = `$${data.item_total}`;
                        }
                        
                        // Update cart summary
                        document.getElementById('cart-items-count').textContent = data.cart_items_count;
                        document.getElementById('cart-subtotal').textContent = `$${data.cart_total}`;
                        document.getElementById('cart-discount').textContent = `-$${data.total_discount}`;
                        document.getElementById('cart-net').textContent = `$${data.net_total}`;
                        document.getElementById('cart-total').textContent = `$${data.net_total}`;
                    }
                })
                .catch(error => {
                    console.error('Error updating cart:', error);
                })
                .finally(() => {
                    // Remove visual feedback
                    setTimeout(() => {
                        input.classList.remove('quantity-loading');
                    }, 300);
                });
            }
        });
    </script>
</body>
</html>