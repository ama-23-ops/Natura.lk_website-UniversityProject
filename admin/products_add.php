<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
  header("Location: ../login.php");
  exit();
}
include_once '../db.php';

$categories = ['Fresh Produce', 'Grains & Pulses', 'Bakery & Snacks', 'Nuts, Seeds & Dried Fruits', 'Beverages', 'Pantry Staples', 'Organic Baby Food', 'Superfoods & Supplements'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $title = $_POST['title'];
  $purchase_cost = $_POST['purchase_cost'];
  $sale_price = $_POST['sale_price'];
  $category = $_POST['category'];
  $stock_quantity = $_POST['stock_quantity'];
  $details = $_POST['details'];
  $discount = $_POST['discount'] ?? 0;

  // Handle image upload
  $productImage = null;
  
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    // Create uploads directory if it doesn't exist
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $fileExtension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $uniqueFilename = uniqid('product_') . '.' . $fileExtension;
    $targetFile = $uploadDir . $uniqueFilename;
    
    // Move uploaded file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
      $productImage = $uniqueFilename; // Store filename only
      
      // Show success notification
      echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
          showNotification('Image uploaded successfully', 'success');
        });
      </script>";
    } else {
      // Show error notification
      echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
          showNotification('Failed to upload image', 'error');
        });
      </script>";
    }
  }

  $stmt = $conn->prepare("INSERT INTO products (title, purchase_cost, sale_price, category, stock_quantity, details, discount, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  
  try {
    $stmt->execute([$title, $purchase_cost, $sale_price, $category, $stock_quantity, $details, $discount, $productImage]);
    // Show success notification
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotification('Product added successfully!', 'success');
        setTimeout(function() {
          window.location.href = 'products.php';
        }, 2000);
      });
    </script>";
  } catch (PDOException $e) {
    // Show error notification
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotification('Failed to add product: " . $e->getMessage() . "', 'error');
      });
    </script>";
  }
}

$pageTitle = 'Add Product';

ob_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Product</title>
  <script src="../assets/js/notification.js"></script>
  <script src="../assets/js/validation.js"></script>
  <script>
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        const output = document.getElementById('productImagePreview');
        output.src = reader.result;
        output.classList.remove('hidden');
        document.getElementById('productIcon').classList.add('hidden');
      };
      reader.readAsDataURL(event.target.files[0]);
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Add event listeners for real-time validation
      document.getElementById('title').addEventListener('input', validateProductTitle);
      document.getElementById('purchase_cost').addEventListener('input', validatePurchaseCost);
      document.getElementById('sale_price').addEventListener('input', validateSalePrice);
      document.getElementById('stock_quantity').addEventListener('input', validateStockQuantity);
      document.getElementById('discount').addEventListener('input', validateDiscount);
      document.getElementById('details').addEventListener('input', validateProductDetails);
      
      // Form submission validation
      document.getElementById('productForm').addEventListener('submit', function(event) {
        if (!validateProductForm()) {
          event.preventDefault();
          showNotification('Please correct the errors in the form', 'error');
        }
      });
    });
  </script>
</head>

<body class="bg-gray-50">
  <div class="container mx-auto p-4 rounded-lg shadow-xl">
    <h1 class="text-2xl font-bold mb-4">Add Product</h1>
    <form id="productForm" method="post" enctype="multipart/form-data" class="p-6">
      <!-- Product Image Section - Full Width Top Section -->
      <div class="mb-6">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
          <div class="flex items-center justify-center mb-3">
            <i class="fas fa-image text-teal-600 mr-2"></i>
            <label class="block text-sm font-medium text-gray-700">Product Image</label>
          </div>
          <div class="mb-4">
            <div id="productIcon" class="mx-auto w-48 h-48 bg-gray-200 flex items-center justify-center rounded-lg">
              <i class="fas fa-image text-5xl text-gray-400"></i>
            </div>
            <img id="productImagePreview" class="hidden mx-auto w-48 h-48 object-cover rounded-lg border-2 border-gray-200">
          </div>
          <div class="flex justify-center">
            <label for="image" class="cursor-pointer bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded-md transition">
              <i class="fas fa-camera mr-2"></i>Upload Image
            </label>
            <input type="file" name="image" id="image" class="hidden" onchange="previewImage(event)" accept="image/*">
          </div>
        </div>
      </div>

      <!-- Two Column Layout for remaining form fields -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Left Column -->
        <div class="space-y-6">
          <!-- Product Title -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-3">
              <i class="fas fa-heading text-teal-600 mr-2"></i>
              <label class="block text-sm font-medium text-gray-700" for="title">Product Title</label>
            </div>
            <input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" type="text" name="title" id="title" required>
            <p id="titleError" class="mt-1 text-sm text-red-600"></p>
          </div>

          <!-- Category -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-3">
              <i class="fas fa-folder text-teal-600 mr-2"></i>
              <label class="block text-sm font-medium text-gray-700" for="category">Category</label>
            </div>
            <select class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" name="category" id="category">
              <?php foreach ($categories as $cat) : ?>
                <option value="<?= $cat ?>"><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Product Details -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-3">
              <i class="fas fa-file-alt text-teal-600 mr-2"></i>
              <label class="block text-sm font-medium text-gray-700" for="details">Product Details</label>
            </div>
            <textarea class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" name="details" id="details" rows="4"></textarea>
            <p id="detailsError" class="mt-1 text-sm text-red-600"></p>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Purchase Cost -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-3">
              <i class="fas fa-tag text-teal-600 mr-2"></i>
              <label class="block text-sm font-medium text-gray-700" for="purchase_cost">Purchase Cost</label>
            </div>
            <div>
              <label class="block text-gray-700 text-sm mb-1"><input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 inline-block w-full p-2.5" type="number" step="0.01" name="purchase_cost" id="purchase_cost" required></label>
            </div>
            <p id="purchaseCostError" class="mt-1 text-sm text-red-600"></p>
          </div>

          <!-- Sale Price -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-3">
              <i class="fas fa-dollar-sign text-teal-600 mr-2"></i>
              <label class="block text-sm font-medium text-gray-700" for="sale_price">Sale Price</label>
            </div>
            <div>
              <label class="block text-gray-700 text-sm mb-1"><input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 inline-block w-full p-2.5" type="number" step="0.01" name="sale_price" id="sale_price" required></label>
            </div>
            <p id="salePriceError" class="mt-1 text-sm text-red-600"></p>
          </div>

          <!-- Stock Quantity -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-3">
              <i class="fas fa-boxes text-teal-600 mr-2"></i>
              <label class="block text-sm font-medium text-gray-700" for="stock_quantity">Stock Quantity</label>
            </div>
            <input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" type="number" name="stock_quantity" id="stock_quantity" required>
            <p id="stockQuantityError" class="mt-1 text-sm text-red-600"></p>
          </div>

          <!-- Discount -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center mb-3">
              <i class="fas fa-percent text-teal-600 mr-2"></i>
              <label class="block text-sm font-medium text-gray-700" for="discount">Discount</label>
            </div>
            <div class="relative">
              <input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" type="number" name="discount" id="discount" min="0" max="100" placeholder="0">
            </div>
            <p id="discountError" class="mt-1 text-sm text-red-600"></p>
          </div>
        </div>
      </div>

      <!-- Submit Button - Full Width -->
      <div class="mt-8">
        <button type="submit" class="w-full bg-teal-600 hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 text-white font-bold py-3 px-4 rounded-lg focus:outline-none transition-colors">
          <i class="fas fa-plus-circle mr-2"></i>Add Product
        </button>
      </div>
    </form>
  </div>
</body>

</html>

<?php
$content = ob_get_clean();
include 'includes/dashboard_layout.php';
?>