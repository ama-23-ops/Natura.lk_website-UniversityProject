<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Function to get best deals (products with highest discounts)
function getBestDeals($conn, $limit = null) {
    $sql = "SELECT p.*, 
            (p.sale_price - (p.sale_price * p.discount/100)) as final_price 
            FROM products p 
            WHERE p.is_active = 1 AND p.discount > 0 
            ORDER BY p.discount DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get trending products (best selling in last week)
function getTrendingProducts($conn, $limit = null) {
    $sql = "SELECT p.*, COUNT(oi.product_id) as sales_count 
            FROM products p 
            INNER JOIN order_items oi ON p.id = oi.product_id 
            INNER JOIN orders o ON oi.order_id = o.id 
            WHERE p.is_active = 1 
            AND o.order_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK) 
            GROUP BY p.id 
            ORDER BY sales_count DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get most selling products (all time)
function getMostSellingProducts($conn, $limit = null) {
    $sql = "SELECT p.*, COUNT(oi.product_id) as total_sales 
            FROM products p 
            INNER JOIN order_items oi ON p.id = oi.product_id 
            WHERE p.is_active = 1 
            GROUP BY p.id 
            ORDER BY total_sales DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get lowest price products
function getLowestPriceProducts($conn, $limit = null) {
    $sql = "SELECT * FROM products 
            WHERE is_active = 1 
            ORDER BY sale_price ASC";
    
    if ($limit) {
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get highest price products
function getHighestPriceProducts($conn, $limit = null) {
    $sql = "SELECT * FROM products 
            WHERE is_active = 1 
            ORDER BY sale_price DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initial limit for rows (before "View More")
$initial_limit = 4;

// Get products for each section with initial limit
$bestDeals = getBestDeals($conn, $initial_limit);
$trendingProducts = getTrendingProducts($conn, $initial_limit);
$mostSellingProducts = getMostSellingProducts($conn, $initial_limit);
$lowestPriceProducts = getLowestPriceProducts($conn, $initial_limit);
$highestPriceProducts = getHighestPriceProducts($conn, $initial_limit);

// Get all products for each section (used when "View More" is clicked)
$allBestDeals = getBestDeals($conn);
$allTrendingProducts = getTrendingProducts($conn);
$allMostSellingProducts = getMostSellingProducts($conn);
$allLowestPriceProducts = getLowestPriceProducts($conn);
$allHighestPriceProducts = getHighestPriceProducts($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections</title>
    <link href="/assets/css/style.css" rel="stylesheet">
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

        .teal-gradient {
            background: linear-gradient(135deg, #00796b 0%, #009688 100%);
        }

        .collection-section {
            margin-bottom: 2rem;
        }

        .products-container {
            transition: all 0.3s ease;
        }

        .product-row {
            display: flex;
            overflow-x: auto;
            gap: 1rem;
            padding: 1rem 0;
            scrollbar-width: thin;
            scrollbar-color: #009688 #f0f0f0;
        }

        .product-row::-webkit-scrollbar {
            height: 6px;
        }

        .product-row::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 3px;
        }

        .product-row::-webkit-scrollbar-thumb {
            background: #009688;
            border-radius: 3px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 1rem 0;
        }

        .product-card {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            min-width: 280px;
            background: white;
            border-radius: 0.5rem;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .product-card-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .product-card-image img {
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-card-image img {
            transform: scale(1.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .view-more-btn {
            background: transparent;
            border: 2px solid #009688;
            color: #009688;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .view-more-btn:hover {
            background: #009688;
            color: white;
        }

        .section-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
    
    <div class="container mx-auto p-6">
        <!-- Title Section with Gradient -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg mt-20">
            <div class="flex items-center">
                <i class="fas fa-th-large text-4xl mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold">Collections</h1>
                    <p class="text-teal-100">Discover our curated collections of amazing products</p>
                </div>
            </div>
        </div>

        <!-- Best Deals Section -->
        <div class="collection-section">
            <div class="section-header">
                <h2 class="section-title">Best Deals</h2>
                <button class="view-more-btn" onclick="toggleView('bestDeals')">View More</button>
            </div>
            <div id="bestDeals" class="products-container product-row">
                <?php foreach ($bestDeals as $product): ?>
                    <?php include 'includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Now Trending Section -->
        <div class="collection-section">
            <div class="section-header">
                <h2 class="section-title">Now Trending</h2>
                <button class="view-more-btn" onclick="toggleView('trending')">View More</button>
            </div>
            <div id="trending" class="products-container product-row">
                <?php foreach ($trendingProducts as $product): ?>
                    <?php include 'includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Most Selling Section -->
        <div class="collection-section">
            <div class="section-header">
                <h2 class="section-title">Most Selling</h2>
                <button class="view-more-btn" onclick="toggleView('mostSelling')">View More</button>
            </div>
            <div id="mostSelling" class="products-container product-row">
                <?php foreach ($mostSellingProducts as $product): ?>
                    <?php include 'includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lowest Price Section -->
        <div class="collection-section">
            <div class="section-header">
                <h2 class="section-title">Lowest Price</h2>
                <button class="view-more-btn" onclick="toggleView('lowestPrice')">View More</button>
            </div>
            <div id="lowestPrice" class="products-container product-row">
                <?php foreach ($lowestPriceProducts as $product): ?>
                    <?php include 'includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Highest Price Section -->
        <div class="collection-section">
            <div class="section-header">
                <h2 class="section-title">Highest Price</h2>
                <button class="view-more-btn" onclick="toggleView('highestPrice')">View More</button>
            </div>
            <div id="highestPrice" class="products-container product-row">
                <?php foreach ($highestPriceProducts as $product): ?>
                    <?php include 'includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Hidden containers for all products -->
        <div id="allBestDeals" class="hidden">
            <?php foreach ($allBestDeals as $product): ?>
                <?php include 'includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <div id="allTrending" class="hidden">
            <?php foreach ($allTrendingProducts as $product): ?>
                <?php include 'includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <div id="allMostSelling" class="hidden">
            <?php foreach ($allMostSellingProducts as $product): ?>
                <?php include 'includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <div id="allLowestPrice" class="hidden">
            <?php foreach ($allLowestPriceProducts as $product): ?>
                <?php include 'includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <div id="allHighestPrice" class="hidden">
            <?php foreach ($allHighestPriceProducts as $product): ?>
                <?php include 'includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function toggleView(sectionId) {
            const container = document.getElementById(sectionId);
            const allProductsId = 'all' + sectionId.charAt(0).toUpperCase() + sectionId.slice(1);
            const allProducts = document.getElementById(allProductsId);
            const btn = container.previousElementSibling.querySelector('.view-more-btn');

            if (container.classList.contains('product-row')) {
                // Switch to grid view with all products
                container.classList.remove('product-row');
                container.classList.add('product-grid');
                container.innerHTML = allProducts.innerHTML;
                btn.textContent = 'View Less';
            } else {
                // Switch back to row view with limited products
                container.classList.remove('product-grid');
                container.classList.add('product-row');
                container.innerHTML = allProducts.querySelector(':scope > :nth-child(-n+4)').outerHTML;
                btn.textContent = 'View More';
            }
        }
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
