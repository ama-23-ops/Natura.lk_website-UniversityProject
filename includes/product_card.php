<?php
if (!defined('INCLUDED')) {
    die('Direct access to this file is not allowed');
}
?>
<div class="bg-white rounded-lg shadow-lg product-card flex flex-col">
    <!-- Image Section -->
    <?php if (!empty($product['image'])): ?>
        <div class="product-card-image rounded-t-lg">
            <img src="/uploads/<?= $product['image'] ?>" alt="<?= $product['title'] ?>" class="w-full h-full object-cover">
            <span class="product-category"><?= $product['category'] ?></span>
            <?php if ($product['discount'] > 0): 
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
            <a href="/product_details.php?id=<?= $product['id'] ?>" 
               class="action-button w-10 h-10 bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center rounded-full transition-all duration-300 shadow-sm hover:shadow-md" title="View Details">
                <i class="fas fa-info-circle"></i>
            </a>
            <form method="post" action="/cart.php" class="inline">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="add_to_cart" value="1">
                <button type="submit" class="action-button w-10 h-10 bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center rounded-full transition-all duration-300 shadow-sm hover:shadow-md" title="Add to Cart">
                    <i class="fas fa-cart-plus"></i>
                </button>
            </form>
        </div>
    </div>
</div>
