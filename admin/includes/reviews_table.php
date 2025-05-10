<div class="min-h-screen">
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #00796b 0%, #009688 100%);
        }
    </style>
    <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
        <div class="flex items-center">
            <i class="fas fa-star text-4xl mr-4"></i>
            <div>
                <h1 class="text-3xl font-bold">MANAGE REVIEWS</h1>
                <p class="text-teal-100">MONITOR AND MANAGE CUSTOMER PRODUCT REVIEWS</p>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($reviews as $review) : ?>
            <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-300">
                <h3 class="text-lg font-semibold text-gray-600">User: <?= $review['user_name'] ?></h3>
                <p class="text-gray-500">Product: <?= $review['product_title'] ?></p>
                <p class="text-gray-500">Rating: <?= $review['rating'] ?></p>
                <p class="text-gray-500">Comment: <?= $review['comment'] ?></p>
                <p class="text-gray-500">Date: <?= $review['review_date'] ?></p>
                <a href="reviews.php?delete_id=<?= $review['id'] ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this review?')">
                    <i class="fas fa-trash-alt"></i>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
