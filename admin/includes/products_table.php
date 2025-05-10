<div class="min-h-screen">
  <style>
    .teal-gradient {
      background: linear-gradient(135deg, #00796b 0%, #009688 100%);
    }
  </style>
  <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
    <div class="flex items-center">
      <i class="fas fa-boxes text-4xl mr-4"></i>
      <div>
        <h1 class="text-3xl font-bold">MANAGE PRODUCTS</h1>
        <p class="text-teal-100">ADD, EDIT, AND MANAGE YOUR PRODUCT INVENTORY</p>
      </div>
    </div>
  </div>

  <!-- Search and Filter Section -->
  <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-4">
      <div class="flex-1 min-w-[200px]">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" 
               placeholder="Search products..." 
               class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-teal-600 focus:border-transparent">
      </div>
      <div class="w-40">
        <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-teal-600 focus:border-transparent">
          <option value="">All Status</option>
          <option value="active" <?= ($status_filter === 'active') ? 'selected' : '' ?>>Active</option>
          <option value="disabled" <?= ($status_filter === 'disabled') ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div class="w-40">
        <select name="category" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-teal-600 focus:border-transparent">
          <option value="">All Categories</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?= htmlspecialchars($category) ?>" <?= ($category_filter === $category) ? 'selected' : '' ?>>
              <?= htmlspecialchars($category) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex gap-2">
      <button type="submit" class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
          <i class="fas fa-search mr-2"></i>FILTER
        </button>
        <a href="products.php" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
          <i class="fas fa-redo mr-2"></i>Reset
        </a>
      </div>
    </form>
  </div>

  <!-- Results count -->
  <div class="flex justify-between items-center mb-6">
    <div class="text-gray-600">
      Showing <?= min($offset + 1, $totalRecords) ?> to <?= min($offset + $limit, $totalRecords) ?> of <?= $totalRecords ?> products
    </div>
    <a href="products_add.php" class="bg-teal-600 hover:bg-teal-700 focus:ring-4 focus:ring-teal-200 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center gap-2 transition-colors">
      <i class="fas fa-plus-circle"></i>
      ADD NEW PRODUCT
    </a>
  </div>

  <!-- Products Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
    <?php foreach ($products as $product) : ?>
      <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col h-full">
        <!-- Product Image Banner - Full width with no margins -->
        <div class="relative w-full">
          <?php if (!empty($product['image'])): ?>
            <div class="h-52 w-full">
              <img src="/uploads/<?= $product['image'] ?>" alt="<?= $product['title'] ?>" class="w-full h-full object-cover">
            </div>
          <?php else: ?>
            <div class="h-52 w-full bg-gradient-to-r from-gray-200 to-gray-300 flex items-center justify-center">
              <i class="fas fa-image text-5xl text-gray-400"></i>
            </div>
          <?php endif; ?>
          <!-- Status Badge -->
          <div class=" p-4">
            <span class="px-3 py-1 rounded-full text-sm font-medium <?= $product['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
              <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </div>
        </div>
        
        <!-- Product Content - With proper padding -->
        <div class="p-6 flex-1 space-y-6">
          <!-- Product Title with Marquee -->
          <div class="overflow-hidden ">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
              <i class="fas fa-box text-green-500 flex-shrink-0"></i>
              <div class="overflow-hidden">
                <?php if (strlen($product['title']) > 20): ?>
                  <div class="marquee">
                    <span><?= $product['title'] ?></span>
                  </div>
                <?php else: ?>
                  <?= $product['title'] ?>
                <?php endif; ?>
              </div>
            </h2>
          </div>
          
          <div class="space-y-3">
            <div class="flex items-start gap-2">
              <i class="fas fa-tag text-gray-400 mt-1 flex-shrink-0"></i>
              <div class="overflow-hidden">
                <span class="text-xs text-gray-500 block">Purchase Cost</span>
                <span class="font-medium text-gray-800">$<?= $product['purchase_cost'] ?></span>
              </div>
            </div>
            
            <div class="flex items-start gap-2">
              <i class="fas fa-dollar-sign text-gray-400 mt-1 flex-shrink-0"></i>
              <div class="overflow-hidden">
                <span class="text-xs text-gray-500 block">Sale Price</span>
                <span class="font-medium text-gray-800">$<?= $product['sale_price'] ?></span>
              </div>
            </div>
            
            <div class="flex items-start gap-2">
              <i class="fas fa-folder text-gray-400 mt-1 flex-shrink-0"></i>
              <div class="overflow-hidden">
                <span class="text-xs text-gray-500 block">Category</span>
                <?php if (strlen($product['category']) > 20): ?>
                  <div class="marquee">
                    <span class="font-medium text-gray-800"><?= $product['category'] ?></span>
                  </div>
                <?php else: ?>
                  <span class="font-medium text-gray-800"><?= $product['category'] ?></span>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="flex items-start gap-2">
              <i class="fas fa-boxes text-gray-400 mt-1 flex-shrink-0"></i>
              <div class="overflow-hidden">
                <span class="text-xs text-gray-500 block">Stock</span>
                <span class="font-medium text-gray-800"><?= $product['stock_quantity'] ?></span>
              </div>
            </div>
            <?php if ($product['discount'] > 0): ?>
            <div class="flex items-start gap-2">
              <i class="fas fa-percent text-gray-400 mt-1 flex-shrink-0"></i>
              <div class="overflow-hidden">
                <span class="text-xs text-gray-500 block">Discount</span>
                <span class="font-medium text-gray-800"><?= $product['discount'] ?>%</span>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <div class="flex items-center justify-center space-x-2 mt-4 pt-4 border-t border-gray-100">
            <a href="products_edit.php?id=<?= $product['id'] ?>" 
               class="p-2 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors" 
               title="Edit Product">
              <i class="fas fa-edit text-lg"></i>
            </a>
            <?php if ($product['is_active']) : ?>
              <a href="products.php?disable_id=<?= $product['id'] ?>" 
                 class="p-2 text-yellow-500 hover:text-yellow-700 hover:bg-yellow-50 rounded-lg transition-colors"
                 onclick="return confirm('Are you sure you want to disable this product?')"
                 title="Disable Product">
                <i class="fas fa-ban text-lg"></i>
              </a>
            <?php else : ?>
              <a href="products.php?enable_id=<?= $product['id'] ?>" 
                 class="p-2 text-green-500 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                 onclick="return confirm('Are you sure you want to enable this product?')"
                 title="Enable Product">
                <i class="fas fa-check-circle text-lg"></i>
              </a>
            <?php endif; ?>
            <a href="products.php?delete_id=<?= $product['id'] ?>" 
               class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
               onclick="return confirm('Are you sure you want to delete this product?')"
               title="Delete Product">
              <i class="fas fa-trash-alt text-lg"></i>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="mt-6 flex justify-center">
    <div class="flex space-x-2">
      <?php if ($page > 1): ?>
        <a href="?page=<?= ($page - 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>" 
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
          Previous
        </a>
      <?php endif; ?>

      <?php
      $start = max(1, $page - 2);
      $end = min($totalPages, $page + 2);
      
      if ($start > 1) {
        echo '<span class="px-4 py-2 text-gray-600">...</span>';
      }
      
      for ($i = $start; $i <= $end; $i++):
      ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>" 
           class="px-4 py-2 <?= ($i === $page) ? 'bg-teal-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50 text-gray-700' ?> rounded-lg">
          <?= $i ?>
        </a>
      <?php endfor; 
      
      if ($end < $totalPages) {
        echo '<span class="px-4 py-2 text-gray-600">...</span>';
      }
      ?>

      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= ($page + 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>" 
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
          Next
        </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<style>
  .marquee {
    width: 100%;
    white-space: nowrap;
    overflow: hidden;
    box-sizing: border-box;
  }
  
  .marquee span {
    display: inline-block;
    padding-left: 100%;
    animation: marquee 12s linear infinite;
  }
  
  @keyframes marquee {
    0% {
      transform: translateX(0%);
    }
    100% {
      transform: translateX(-100%);
    }
  }
</style>
