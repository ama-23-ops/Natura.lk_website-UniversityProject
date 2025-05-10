<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Fetch FAQs from database
$query = "SELECT * FROM faqs ORDER BY id ASC";
$stmt = $conn->query($query);
?>

<main class="relative min-h-screen pt-20">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <img src="/assets/images/background.jpg" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 container mx-auto px-4 py-16">
        <div class="bg-white rounded-lg shadow-xl p-8 max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold text-teal-600 mb-8 flex items-center justify-center">
                <i class="fas fa-question-circle mr-4"></i>
                Frequently Asked Questions
            </h1>

            <div class="space-y-6">
        <?php if($stmt->rowCount() > 0): ?>
            <?php while($faq = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="bg-gray-50 p-6 rounded-lg transform hover:-translate-y-1 transition-transform duration-300">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">
                        <i class="fas fa-angle-right text-teal-600 mr-2"></i>
                        <?php echo htmlspecialchars($faq['question']); ?>
                    </h3>
                    <p class="text-gray-600">
                        <?php echo htmlspecialchars($faq['answer']); ?>
                    </p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-8">
                <div class="text-gray-600">
                    <i class="fas fa-info-circle text-4xl text-teal-600 mb-4"></i>
                    <p class="text-lg">No FAQs available at the moment.</p>
                </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
</main>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
