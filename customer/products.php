<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Get filter parameters
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build query
$sql = "SELECT * FROM products WHERE is_active = 1";
$params = [];
if ($min_price !== '') {
    $sql .= " AND sale_price >= ?";
    $params[] = $min_price;
}
if ($max_price !== '') {
    $sql .= " AND sale_price <= ?";
    $params[] = $max_price;
}
if ($category_filter !== '' && $category_filter !== 'All') {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch distinct categories for filter dropdown
$catStmt = $conn->query("SELECT DISTINCT category FROM products");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's wishlist items
$wishlistStmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
$wishlistStmt->execute([$_SESSION['user_id']]);
$wishlistItems = $wishlistStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Customer Dashboard</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #00796b 0%, #009688 100%);
        }
        .product-card {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .product-card-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }
        .product-card-image img {
            transition: transform 0.5s ease;
        }
        .product-card:hover .product-card-image img {
            transform: scale(1.05);
        }
        .product-category, .discount-badge {
            position: absolute;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .product-category {
            top: 1rem;
            right: 1rem;
            background: rgba(0, 150, 136, 0.9);
        }
        .discount-badge {
            top: 1rem;
            left: 1rem;
            background: rgba(220, 38, 38, 0.9);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .action-button {
            transition: all 0.2s ease;
            transform: translateY(0);
        }
        .action-button:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php'; ?>
    <div class="container mx-auto p-6">
        <!-- Title Section with Gradient -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-shopping-bag text-4xl mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold">Explore Products</h1>
                    <p class="text-teal-100">Discover our amazing collection of products</p>
                </div>
            </div>
        </div>

        <!-- Centered Filter Section -->
        <div class="mb-8 max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6">
            <form method="get" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center bg-gray-50 rounded p-2">
                        <i class="fas fa-dollar-sign text-teal-600 mr-2"></i>
                        <input type="number" step="0.01" name="min_price" value="<?= htmlspecialchars($min_price) ?>" 
                               placeholder="Min Price" class="w-full bg-transparent focus:outline-none">
                    </div>
                    <div class="flex items-center bg-gray-50 rounded p-2">
                        <i class="fas fa-dollar-sign text-teal-600 mr-2"></i>
                        <input type="number" step="0.01" name="max_price" value="<?= htmlspecialchars($max_price) ?>" 
                               placeholder="Max Price" class="w-full bg-transparent focus:outline-none">
                    </div>
                    <div class="flex items-center bg-gray-50 rounded p-2">
                        <i class="fas fa-tags text-teal-600 mr-2"></i>
                        <select name="category" class="w-full bg-transparent focus:outline-none">
                            <option value="All">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category'] ?>" <?= ($category_filter == $cat['category']) ? 'selected' : '' ?>>
                                    <?= $cat['category'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-full flex items-center gap-2 transition duration-300">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <?php if (empty($products)): ?>
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="text-teal-600 mb-4">
                    <i class="fas fa-box-open text-8xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">No Products Found</h2>
                <p class="text-gray-600">We couldn't find any products matching your criteria</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($products as $product) : ?>
                    <div class="bg-white rounded-lg shadow-lg product-card flex flex-col">
                        <!-- Image Section -->
                        <?php if (!empty($product['image'])): ?>
                            <div class="product-card-image rounded-t-lg">
                                <img src="/uploads/<?= $product['image'] ?>" alt="<?= $product['title'] ?>" class="w-full h-full object-cover">
                                <span class="product-category"><?= $product['category'] ?></span>
                                <?php if ($product['discount'] > 0): ?>
                                    <?php 
                                        $discounted_amount = $product['sale_price'] * ($product['discount']/100);
                                        $final_price = $product['sale_price'] - $discounted_amount;
                                    ?>
                                    <span class="discount-badge">
                                        <i class="fas fa-tag"></i>
                                        <?= $product['discount'] ?>% OFF
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="product-card-image rounded-t-lg bg-gradient-to-r from-gray-200 to-gray-300 flex items-center justify-center">
                                <i class="fas fa-image text-5xl text-gray-400"></i>
                                <span class="product-category"><?= $product['category'] ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <!-- Product Details -->
                            <h2 class="text-xl font-bold text-gray-800 mb-2"><?= $product['title'] ?></h2>
                            <p class="text-gray-600 mb-4"><?= $product['details'] ?></p>
                            
                            <!-- Price Section -->
                            <div class="flex flex-col mb-4">
                                <div class="flex items-baseline gap-2">
                                    <?php if ($product['discount'] > 0): ?>
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
                            
                            <!-- Action Buttons -->
                            <div class="mt-auto flex justify-end gap-3">
                                <a href="/customer/product_details.php?id=<?= $product['id'] ?>" 
                                   class="action-button w-10 h-10 bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center rounded-full transition-all duration-300 shadow-sm hover:shadow-md" title="View Details">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <form method="post" action="/customer/cart.php" class="inline">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <button type="submit" class="action-button w-10 h-10 bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center rounded-full transition-all duration-300 shadow-sm hover:shadow-md" title="Add to Cart">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>
                                <?php $isInWishlist = in_array($product['id'], $wishlistItems); ?>
                                <button 
                                    class="action-button wishlist-button w-10 h-10 <?= $isInWishlist ? 'bg-red-200 hover:bg-red-300 text-red-600' : 'bg-gray-200 hover:bg-gray-300 text-gray-600' ?> flex items-center justify-center rounded-full transition-all duration-300 shadow-sm hover:shadow-md" 
                                    data-product-id="<?= $product['id'] ?>"
                                    aria-label="<?= $isInWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>">
                                    <i class="<?= $isInWishlist ? 'fas' : 'far' ?> fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const wishlistButtons = document.querySelectorAll('.wishlist-button');
    const wishlistCountBadge = document.querySelector('#wishlistCount');

    function updateWishlistButton(button, isInWishlist) {
        const icon = button.querySelector('i');
        
        if (isInWishlist) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.classList.remove('bg-gray-200', 'hover:bg-gray-300', 'text-gray-600');
            button.classList.add('bg-red-200', 'hover:bg-red-300', 'text-red-600');
            button.setAttribute('aria-label', 'Remove from wishlist');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            button.classList.add('bg-gray-200', 'hover:bg-gray-300', 'text-gray-600');
            button.classList.remove('bg-red-200', 'hover:bg-red-300', 'text-red-600');
            button.setAttribute('aria-label', 'Add to wishlist');
        }
    }

    wishlistButtons.forEach(button => {
        const productId = button.dataset.productId;

        button.addEventListener('click', function(event) {
            event.preventDefault();
            const isInWishlist = button.querySelector('.fas') !== null;
            const action = isInWishlist ? 'remove' : 'add';

            fetch('/customer/wishlist_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=' + action + '&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'added' || data.status === 'removed') {
                    updateWishlistButton(button, data.status === 'added');
                    if(wishlistCountBadge) {
                        wishlistCountBadge.textContent = data.count;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
</body>
</html>
