<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$userId = $_SESSION['user_id'];

// Handle add to wishlist
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_wishlist'])) {
    $productId = $_POST['product_id'];

    // Check if the product already exists in the wishlist
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existingWishlistItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingWishlistItem) {
        // Add new item to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
    }
}

// Handle remove from wishlist
if (isset($_GET['remove_id'])) {
    $wishlistItemId = $_GET['remove_id'];
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
    $stmt->execute([$wishlistItemId, $userId]);
    header("Location: wishlist.php");
    exit();
}

// Fetch wishlist items
$stmt = $conn->prepare("SELECT w.*, p.title, p.sale_price, p.image, p.category, p.details, p.discount FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wishlist</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <script src="/assets/js/notification.js"></script>
        <?php if (isset($_SESSION['notification'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNotification('<?= $_SESSION['notification']['message'] ?>', '<?= $_SESSION['notification']['type'] ?>');
                });
            </script>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>
        <!-- Title Section with Gradient -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-heart text-4xl mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold">Your Wishlist</h1>
                    <p class="text-teal-100">Items you've saved for later</p>
                </div>
            </div>
        </div>

        <?php if (count($wishlistItems) > 0) : ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($wishlistItems as $item) : ?>
                    <div class="bg-white rounded-lg shadow-lg product-card flex flex-col">
                        <!-- Image Section -->
                        <?php if (!empty($item['image'])): ?>
                            <div class="product-card-image rounded-t-lg">
                                <img src="/uploads/<?= $item['image'] ?>" alt="<?= $item['title'] ?>" class="w-full h-full object-cover">
                                <span class="product-category"><?= $item['category'] ?></span>
                                <?php if ($item['discount'] > 0): ?>
                                    <?php 
                                        $discounted_amount = $item['sale_price'] * ($item['discount']/100);
                                        $final_price = $item['sale_price'] - $discounted_amount;
                                    ?>
                                    <span class="discount-badge">
                                        <i class="fas fa-tag"></i>
                                        <?= $item['discount'] ?>% OFF
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="product-card-image rounded-t-lg bg-gradient-to-r from-gray-200 to-gray-300 flex items-center justify-center">
                                <i class="fas fa-image text-5xl text-gray-400"></i>
                                <span class="product-category"><?= $item['category'] ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <!-- Product Details -->
                            <h2 class="text-xl font-bold text-gray-800 mb-2"><?= $item['title'] ?></h2>
                            <p class="text-gray-600 mb-4"><?= $item['details'] ?></p>
                            
                            <!-- Price Section -->
                            <div class="flex flex-col mb-4">
                                <div class="flex items-baseline gap-2">
                                    <?php if ($item['discount'] > 0): ?>
                                        <span class="text-2xl font-bold text-teal-600">$<?= number_format($final_price, 2) ?></span>
                                        <span class="text-sm line-through text-gray-400">$<?= number_format($item['sale_price'], 2) ?></span>
                                        <span class="text-sm font-semibold text-red-500">-<?= $item['discount'] ?>%</span>
                                    <?php else: ?>
                                        <span class="text-2xl font-bold text-teal-600">$<?= number_format($item['sale_price'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($item['discount'] > 0): ?>
                                    <span class="text-xs text-gray-500">Save $<?= number_format($discounted_amount, 2) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="mt-auto flex justify-end gap-3">
                                <a href="/customer/product_details.php?id=<?= $item['id'] ?>" 
                                   class="action-button w-10 h-10 bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center rounded-full transition-all duration-300 shadow-sm hover:shadow-md" title="View Details">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <form method="post" action="/customer/cart.php" class="inline">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <button type="submit" class="action-button w-10 h-10 bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center rounded-full transition-all duration-300 shadow-sm hover:shadow-md" title="Add to Cart">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>
                                <button 
                                    class="action-button w-10 h-10 bg-red-600 hover:bg-red-700 text-white flex items-center justify-center rounded-full transition-all duration-300 shadow-sm hover:shadow-md" 
                                    onclick="showConfirmation('Are you sure you want to remove this item from your wishlist?', function() { window.location.href='wishlist.php?remove_id=<?= $item['id'] ?>'; })"
                                    title="Remove from Wishlist">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-6 text-right">
                <a href="/customer/products.php" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-full inline-flex items-center gap-2 transition duration-300">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="/customer/add_all_to_cart.php" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-full inline-flex items-center gap-2 transition duration-300">
                    <i class="fas fa-cart-plus"></i> Add All to Cart
                </a>
            </div>
        <?php else : ?>
            <!-- Empty State -->
            <div class="text-center py-16 bg-white rounded-lg shadow-md p-6">
                <div class="text-teal-600 mb-4">
                    <i class="fas fa-heart text-8xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Your Wishlist is Empty</h2>
                <p class="text-gray-600 mb-8">Save items to your wishlist while shopping</p>
                <div class="inline-block">
                    <a href="/customer/products.php" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-full inline-flex items-center gap-2 transition duration-300">
                        <i class="fas fa-shopping-cart"></i> Start Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
    
</body>
</html>
