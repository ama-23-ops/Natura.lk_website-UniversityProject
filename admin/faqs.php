<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: " . $_SERVER['DOCUMENT_ROOT'] . "/login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Handle FAQ addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_faq'])) {
    $question = $_POST['question'];
    $answer = $_POST['answer'];

    $stmt = $conn->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
    $stmt->execute([$question, $answer]);
}

// Handle FAQ deletion
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$deleteId]);
}

// Fetch all FAQs
$stmt = $conn->query("SELECT * FROM faqs ORDER BY id ASC");
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'MANAGE FAQS';

ob_start();
?>
    <div class="min-h-screen">
        <style>
            .teal-gradient {
                background: linear-gradient(135deg, #00796b 0%, #009688 100%);
            }
        </style>
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-question-circle text-4xl mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold">MANAGE FAQS</h1>
                    <p class="text-teal-100">ADD AND MANAGE FREQUENTLY ASKED QUESTIONS AND ANSWERS</p>
                </div>
            </div>
        </div>

        <!-- Add FAQ Form -->
        <form method="post" class="mb-4 p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700" for="question">Question</label>
                <input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" type="text" name="question" id="question" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700" for="answer">Answer</label>
                <textarea class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" name="answer" id="answer" required></textarea>
            </div>
            <button type="submit" name="add_faq" class="bg-teal-600 hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 text-white font-bold py-2 px-4 rounded focus:outline-none">Add FAQ</button>
        </form>

        <!-- FAQs Table -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($faqs as $faq) : ?>
                <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-300">
                    <h2 class="text-lg font-semibold text-gray-600"><?= $faq['question'] ?></h2>
                    <p class="text-gray-500"><?= $faq['answer'] ?></p>
                    <a href="javascript:void(0)" class="text-red-500 hover:text-red-700" onclick="deleteFAQ(<?= $faq['id'] ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Scripts -->
        <script src="/assets/js/notification.js"></script>
        <script>
        function deleteFAQ(id) {
            showConfirmation('Are you sure you want to delete this FAQ?', function() {
                window.location.href = 'faqs.php?delete_id=' + id;
            });
        }

        // Show success message after deletion
        <?php if (isset($_GET['delete_id'])): ?>
            showNotification('FAQ has been deleted successfully', 'success');
        <?php endif; ?>

        // Show success message after adding
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_faq'])): ?>
            showNotification('FAQ has been added successfully', 'success');
        <?php endif; ?>
        </script>
    </div>
<?php
$content = ob_get_clean();
include $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/dashboard_layout.php';
?>
