<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: /products.php");
        exit();
    }

    // Check if user has purchased this product
    $canReview = false;
    $hasReviewed = false;
    if (isset($_SESSION['user_id'])) {
        // Check if user has purchased the product
        $stmt = $conn->prepare("
            SELECT COUNT(*) as purchase_count 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status IN ('Delivered')
        ");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        $purchaseResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $canReview = $purchaseResult['purchase_count'] > 0;

        // Check if user has already reviewed
        if ($canReview) {
            $stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $productId]);
            $reviewResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasReviewed = $reviewResult['review_count'] > 0;
        }
    }

    // Handle review submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
        if (!$canReview) {
            $_SESSION['error'] = "You must purchase this product before leaving a review.";
        } elseif ($hasReviewed) {
            $_SESSION['error'] = "You have already reviewed this product.";
        } else {
            $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
            $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
            
            if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
                $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $productId, $rating, $comment]);
                $_SESSION['success'] = "Your review has been submitted successfully!";
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                $_SESSION['error'] = "Please provide both a rating and a comment.";
            }
        }
    }

    // Fetch reviews for the product
    $stmt = $conn->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.review_date DESC");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: /products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['title'] ?> - Product Details</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            position: relative;
            background-image: url('/assets/images/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }
        
        .container {
            position: relative;
            z-index: 1;
        }
        
        .product-card {
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .custom-number-input input::-webkit-outer-spin-button,
        .custom-number-input input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .btn-hover {
            transition: all 0.3s ease;
        }
        
        .btn-hover:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 128, 128, 0.3);
        }
        
        .review-item {
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<div class="container mx-auto p-6 ">
        <!-- Product Details Card -->
        <div class="bg-white bg-opacity-95 rounded-lg shadow-lg p-6 mb-6 product-card mt-20">
            <div class="md:flex">
                <!-- Product Image -->
                <div class="md:w-1/2 mb-6 md:mb-0 md:pr-6">
                    <div class="relative w-full h-80">
                        <?php if (!empty($product['image'])): ?>
                            <img src="/uploads/<?= $product['image'] ?>" alt="<?= $product['title'] ?>" class="w-full h-full object-cover rounded">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-r from-gray-200 to-gray-300 flex items-center justify-center rounded">
                                <i class="fas fa-image text-5xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="md:w-1/2">
                    <h1 class="text-3xl font-bold text-gray-800 mb-4"><?= $product['title'] ?></h1>
                    <p class="text-gray-600 mb-6"><?= $product['details'] ?></p>
                    <!-- Price Section -->
                    <div class="flex flex-col mb-6">
                        <div class="flex items-baseline gap-2">
                            <?php if ($product['discount'] > 0): ?>
                                <?php 
                                    $discounted_amount = $product['sale_price'] * ($product['discount']/100);
                                    $final_price = $product['sale_price'] - $discounted_amount;
                                ?>
                                <span class="text-2xl font-bold text-teal-600">$<?= number_format($final_price, 2) ?></span>
                                <span class="text-sm line-through text-gray-400">$<?= number_format($product['sale_price'], 2) ?></span>
                                <span class="text-sm font-semibold text-red-500">-<?= $product['discount'] ?>%</span>
                            <?php else: ?>
                                <span class="text-2xl font-bold text-teal-600">$<?= number_format($product['sale_price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($product['discount'] > 0): ?>
                            <span class="text-xs text-gray-500">Save $<?= number_format($discounted_amount, 2) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($product['stock_quantity'] > 0): ?>
                        <div class="mb-6">
                            <span class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">
                                In Stock (<?= $product['stock_quantity'] ?>)
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="mb-6">
                            <span class="bg-red-100 text-red-800 text-sm font-semibold px-3 py-1 rounded-full">
                                Out of Stock
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="/cart.php" class="mb-4">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="add_to_cart" value="1">
                        
                        <div class="flex items-center mb-4">
                            <label for="quantity" class="mr-4 text-gray-700">Quantity:</label>
                            <div class="custom-number-input w-32">
                                <input type="number" name="quantity" id="quantity" min="1" max="<?= $product['stock_quantity'] ?>" value="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                        
                        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-lg flex items-center gap-2 btn-hover w-full md:w-auto mb-3">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </form>
                    
                    <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <a href="/login.php" class="font-semibold hover:underline">Sign in</a> to save items to your wishlist and track your orders!
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="bg-white bg-opacity-95 rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Customer Reviews</h2>
                <?php if(isset($_SESSION['user_id']) && !$canReview): ?>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle"></i>
                        Purchase this product to leave a review
                    </div>
                <?php endif; ?>
            </div>

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

            <!-- Review Form -->
            <?php if(isset($_SESSION['user_id']) && $canReview && !$hasReviewed): ?>
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold mb-4">Write a Review</h3>
                    <form method="POST" class="space-y-4">
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
                        <button type="submit" name="submit_review" 
                            class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                            Submit Review
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <?php if (count($reviews) > 0) : ?>
                <div class="space-y-6">
                    <?php foreach ($reviews as $review) : ?>
                        <div class="border-b border-gray-200 pb-6 last:border-b-0 last:pb-0 review-item">
                            <div class="flex items-center mb-2">
                                <div class="font-semibold text-gray-800"><?= $review['user_name'] ?></div>
                                <div class="text-gray-500 text-sm ml-4">
                                    <?= date('F j, Y', strtotime($review['review_date'])) ?>
                                </div>
                            </div>
                            <div class="flex items-center mb-2">
                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star text-yellow-400"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-gray-700"><?= $review['comment'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-4">
                        <i class="far fa-comment-alt text-4xl"></i>
                    </div>
                    <p class="text-gray-600">No reviews yet for this product.</p>
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <p class="mt-2">
                            <a href="/login.php" class="text-teal-600 hover:text-teal-700">Sign in to leave a review!</a>
                        </p>
                    <?php elseif($canReview): ?>
                        <p class="mt-2">Be the first to review this product!</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script>
        // Star rating interactivity
        const starLabels = document.querySelectorAll('.cursor-pointer');
        starLabels.forEach((label, index) => {
            label.addEventListener('mouseover', () => {
                // Fill in stars up to the current one on hover
                starLabels.forEach((star, i) => {
                    const starIcon = star.querySelector('i');
                    if (i <= index) {
                        starIcon.classList.remove('far');
                        starIcon.classList.add('fas', 'text-yellow-400');
                    }
                });
            });

            label.addEventListener('mouseout', () => {
                // Reset stars to original state based on selection
                starLabels.forEach((star) => {
                    const input = star.querySelector('input');
                    const starIcon = star.querySelector('i');
                    if (!input.checked) {
                        starIcon.classList.remove('fas', 'text-yellow-400');
                        starIcon.classList.add('far');
                    }
                });
            });
        });
    </script>
</body>
</html>
