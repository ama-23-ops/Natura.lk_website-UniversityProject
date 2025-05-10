<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header("Location: /customer/order_history.php");
    exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$userId = $_SESSION['user_id'];
$orderId = $_GET['id'];

// Get order details with total discount and tax
$stmt = $conn->prepare("
    SELECT o.*,
           oi.product_id, oi.quantity, oi.price, oi.discount_amount as item_discount,
           p.title, p.image
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id 
    JOIN products p ON oi.product_id = p.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $userId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($orderItems)) {
    header("Location: /customer/order_history.php");
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
    'payment_method' => $orderItems[0]['payment_method']
];

// Calculate net amount using stored values
$netAmount = $order['total_amount'] - $order['discount_amount'];
$finalAmount = $netAmount + $order['tax_amount'];

// Check if coming from successful order placement
$showSuccess = isset($_GET['success']) && $_GET['success'] == 1;

// After fetching order items, check which ones can be reviewed
$reviewableProducts = [];
foreach ($orderItems as $item) {
    // Check if user has already reviewed this product
    $stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $item['product_id']]);
    $reviewResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reviewResult['review_count'] == 0) {
        // User hasn't reviewed this product yet
        $item['can_review'] = true;
        $reviewableProducts[] = $item;
    } else {
        $item['can_review'] = false;
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
    
    // Validate inputs
    if ($productId && $rating >= 1 && $rating <= 5 && !empty($comment)) {
        // Verify this product belongs to this order
        $validProduct = false;
        foreach ($orderItems as $item) {
            if ($item['product_id'] == $productId) {
                $validProduct = true;
                break;
            }
        }
        
        if ($validProduct) {
            // Check if already reviewed
            $stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $reviewResult = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reviewResult['review_count'] == 0) {
                // Insert the review
                $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$userId, $productId, $rating, $comment]);
                $_SESSION['success'] = "Your review has been submitted successfully!";
            } else {
                $_SESSION['error'] = "You have already reviewed this product.";
            }
        } else {
            $_SESSION['error'] = "Invalid product selection.";
        }
        
        // Redirect to refresh the page
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        $_SESSION['error'] = "Please select a product, provide a rating, and add a comment.";
    }
}

// Handle order status update to delivered
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_delivered'])) {
    // Only allow changing from "Shipped" to "Delivered"
    if ($order['status'] === 'Shipped') {
        $updateStmt = $conn->prepare("UPDATE orders SET status = 'Delivered' WHERE id = ? AND user_id = ?");
        if ($updateStmt->execute([$orderId, $userId])) {
            $_SESSION['success'] = "Order has been marked as delivered!";
            // Refresh the page to show updated status
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $_SESSION['error'] = "Failed to update order status.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-processing {
            background-color: #e0f2fe;
            color: #075985;
        }
        .status-shipped {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-delivered {
            background-color: #f0fdf4;
            color: #15803d;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .success-message {
            animation: fadeInDown 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php'; ?>
    
    <div class="container mx-auto p-6">
        <?php if ($showSuccess) : ?>
        <!-- Success Message -->
        <div class="success-message bg-green-50 border border-green-200 rounded-lg p-6 mb-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-4xl text-green-500"></i>
            </div>
            <h2 class="text-2xl font-bold text-green-800 mb-2">Order Successfully Placed!</h2>
            <p class="text-green-600 mb-4">Thank you for your purchase. Your order #<?= $orderId ?> has been confirmed and is being processed.</p>
            <p class="text-green-700 text-sm">A confirmation email has been sent to your registered email address.</p>
        </div>
        <?php endif; ?>

        <!-- Title Section with Gradient -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-shopping-bag text-4xl mr-4"></i>
                    <div>
                        <h1 class="text-3xl font-bold">Order #<?= $order['id'] ?></h1>
                        <p class="text-teal-100">Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
                    </div>
                </div>
                <div>
                    <span class="status-badge status-<?= strtolower($order['status']) ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                    
                    <?php if ($order['status'] == 'Shipped') : ?>
                        <form method="POST" class="inline-block ml-3">
                            <button type="submit" name="mark_delivered" 
                                class="bg-gradient-to-r from-green-500 to-teal-400 hover:from-green-600 hover:to-teal-500 
                                text-white font-medium py-1 px-4 rounded-full shadow-md hover:shadow-lg transition-all duration-300 
                                flex items-center gap-1 text-sm">
                                <i class="fas fa-check-circle"></i>
                                Mark as Delivered
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Order Items -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                            <i class="fas fa-box-open mr-2 text-teal-600"></i>
                            Order Items
                        </h2>
                        
                        <div class="space-y-4">
                            <?php foreach ($orderItems as $item) : ?>
                                <div class="flex items-center border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                                    <div class="w-20 h-20 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                                        <?php if (!empty($item['image'])) : ?>
                                            <img src="/uploads/<?= $item['image'] ?>" alt="<?= $item['title'] ?>" class="w-full h-full object-cover">
                                        <?php else : ?>
                                            <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4 flex-grow">
                                        <h3 class="font-medium text-gray-800"><?= $item['title'] ?></h3>
                                        <p class="text-gray-600">Quantity: <?= $item['quantity'] ?></p>
                                        <p class="text-gray-600">Price: $<?= number_format($item['price'], 2) ?></p>
                                        <?php if ($item['item_discount'] > 0): ?>
                                        <p class="text-green-600">Discount: -$<?= number_format($item['item_discount'], 2) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-800">$<?= number_format(($item['price'] * $item['quantity']) - $item['item_discount'], 2) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php if ($order['status'] == 'Delivered') : ?>
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                            <i class="fas fa-star mr-2 text-teal-600"></i>
                            Review Your Purchase
                        </h2>
                        <p class="text-gray-600 mb-4">Share your thoughts about the products you purchased to help other shoppers.</p>
                        
                        <!-- Session Messages -->
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                                <p><?= $_SESSION['success'] ?></p>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                                <p><?= $_SESSION['error'] ?></p>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <?php if (count($reviewableProducts) > 0): ?>
                            <button type="button" id="openReviewModal" 
                                class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center gap-2">
                                <i class="fas fa-edit"></i>
                                Write a Review
                            </button>
                        <?php else: ?>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-blue-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    You have reviewed all products in this order. Thank you for your feedback!
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Summary -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-bold mb-6 flex items-center text-gray-800">
                        <i class="fas fa-receipt mr-2 text-teal-600"></i>
                        Order Summary
                    </h2>

                    <!-- Payment Method -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="font-medium text-gray-800 mb-2">Payment Method</h3>
                        <div class="flex items-center text-gray-600">
                            <?php if ($order['payment_method'] == 'credit') : ?>
                                <i class="fab fa-cc-visa text-blue-800 text-2xl mr-2"></i>
                                <span>Credit Card</span>
                            <?php else : ?>
                                <i class="fab fa-paypal text-blue-600 text-2xl mr-2"></i>
                                <span>PayPal</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="font-medium text-gray-800 mb-2">Shipping Address</h3>
                        <p class="text-gray-600 whitespace-pre-line"><?= htmlspecialchars($order['shipping_address']) ?></p>
                    </div>

                    <!-- Order Total -->
                    <div>
                        <div class="space-y-3">
                            <div class="flex justify-between pb-3 border-b border-gray-200">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="text-gray-800">$<?= number_format($order['total_amount'], 2) ?></span>
                            </div>
                            <div class="flex justify-between pb-3 border-b border-gray-200">
                                <span class="text-gray-600">Discount</span>
                                <span class="text-green-600">-$<?= number_format($order['discount_amount'], 2) ?></span>
                            </div>
                            <div class="flex justify-between pb-3 border-b border-gray-200">
                                <span class="text-gray-600">Net Amount</span>
                                <span class="text-gray-800">$<?= number_format($netAmount, 2) ?></span>
                            </div>
                            <div class="flex justify-between pb-3 border-b border-gray-200">
                                <span class="text-gray-600">Tax (10%)</span>
                                <span class="text-gray-800">$<?= number_format($order['tax_amount'], 2) ?></span>
                            </div>
                            <div class="flex justify-between pt-3">
                                <span class="text-lg font-bold text-gray-800">Final Total</span>
                                <span class="text-lg font-bold text-gray-800">$<?= number_format($finalAmount, 2) ?></span>
                            </div>
                        </div>

                        <?php if ($showSuccess) : ?>
                            <div class="mt-6 text-center">
                                <a href="/customer/products.php" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-full inline-flex items-center gap-2 transition duration-300">
                                    <i class="fas fa-shopping-bag"></i>
                                    Continue Shopping
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <?php if (isset($reviewableProducts) && count($reviewableProducts) > 0): ?>
    <div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Write a Product Review</h2>
                    <button id="closeReviewModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="product_id" class="block text-gray-700 mb-2">Select Product</label>
                        <select id="product_id" name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">-- Select a product to review --</option>
                            <?php foreach($reviewableProducts as $product): ?>
                                <option value="<?= $product['product_id'] ?>"><?= $product['title'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-2">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="<?= $i ?>" class="hidden peer">
                                    <i class="far fa-star text-2xl peer-checked:fas peer-checked:text-yellow-400 hover:text-yellow-400 transition-colors"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div>
                        <label for="comment" class="block text-gray-700 mb-2">Your Review</label>
                        <textarea id="comment" name="comment" rows="4" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                            placeholder="Share your thoughts about this product..."></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" id="cancelReview" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">
                            Cancel
                        </button>
                        <button type="submit" name="submit_review" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-lg">
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal controls
        const modal = document.getElementById('reviewModal');
        const openModalBtn = document.getElementById('openReviewModal');
        const closeModalBtn = document.getElementById('closeReviewModal');
        const cancelBtn = document.getElementById('cancelReview');
        
        if (openModalBtn) {
            openModalBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
            });
        }
        
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        }
        
        // Star rating interactivity
        const starLabels = document.querySelectorAll('#reviewModal .cursor-pointer');
        let selectedRating = 0;

        function updateStars(index, hover = false) {
            starLabels.forEach((star, i) => {
                const starIcon = star.querySelector('i');
                if (i <= index) {
                    starIcon.classList.remove('far');
                    starIcon.classList.add('fas', 'text-yellow-400');
                } else {
                    // Only remove highlighting from non-selected stars during hover
                    if (hover || i > selectedRating - 1) {
                        starIcon.classList.remove('fas', 'text-yellow-400');
                        starIcon.classList.add('far');
                    }
                }
            });
        }

        starLabels.forEach((label, index) => {
            label.addEventListener('click', () => {
                selectedRating = index + 1;
                // Update all stars up to the clicked one
                updateStars(index);
                // Set the radio input value
                label.querySelector('input').checked = true;
            });

            label.addEventListener('mouseover', () => {
                // Temporarily show stars up to hovered position
                updateStars(index, true);
            });

            label.addEventListener('mouseout', () => {
                // Restore to selected rating state
                updateStars(selectedRating - 1);
            });
        });
    </script>
    <?php endif; ?>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
</body>
</html>