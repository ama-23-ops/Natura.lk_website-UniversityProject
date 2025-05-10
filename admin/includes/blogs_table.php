<?php
// Filter by status
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$whereClause = '';

if ($statusFilter) {
    $whereClause = " WHERE b.status = '$statusFilter'";
}

// Get blogs with join to users table to get user information
$stmt = $conn->prepare("SELECT b.*, u.name as author_name, u.username as author_username 
                        FROM blogs b 
                        JOIN users u ON b.user_id = u.id
                        $whereClause
                        ORDER BY b.created_at DESC");
$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count by status
$pendingCount = $conn->query("SELECT COUNT(*) FROM blogs b WHERE b.status = 'pending'")->fetchColumn();
$approvedCount = $conn->query("SELECT COUNT(*) FROM blogs b WHERE b.status = 'approved'")->fetchColumn();
$rejectedCount = $conn->query("SELECT COUNT(*) FROM blogs b WHERE b.status = 'rejected'")->fetchColumn();
$totalCount = $conn->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
?>

<style>
  .teal-gradient {
    background: linear-gradient(135deg, #00796b 0%, #009688 100%);
  }
</style>

<div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
  <div class="flex items-center">
    <i class="fas fa-blog text-4xl mr-4"></i>
    <div>
      <h1 class="text-3xl font-bold">MANAGE BLOGS</h1>
      <p class="text-teal-100">REVIEW, APPROVE, AND MANAGE USER-SUBMITTED BLOG POSTS</p>
    </div>
  </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">TOTAL BLOGS</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['total_blogs'] ?></h3>
            </div>
            <div class="bg-teal-100 rounded-full p-3">
                <i class="fas fa-blog text-teal-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">PENDING BLOGS</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['pending_blogs'] ?></h3>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">APPROVED BLOGS</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['approved_blogs'] ?></h3>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Blog Management -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">BLOGS</h2>
        <div class="flex items-center gap-4">
            <!-- Status Filter Pills -->
            <div class="flex gap-2">
                <a href="?status=" class="px-4 py-2 rounded-md <?= empty($status_filter) ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' ?>">
                    All <span class="ml-1 px-2 py-1 bg-opacity-20 rounded-full text-xs"><?= $totalCount ?></span>
                </a>
                <a href="?status=pending" class="px-4 py-2 rounded-md <?= $status_filter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' ?>">
                    Pending <span class="ml-1 px-2 py-1 bg-opacity-20 rounded-full text-xs"><?= $pendingCount ?></span>
                </a>
                <a href="?status=approved" class="px-4 py-2 rounded-md <?= $status_filter === 'approved' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-800 hover:bg-green-200' ?>">
                    Approved <span class="ml-1 px-2 py-1 bg-opacity-20 rounded-full text-xs"><?= $approvedCount ?></span>
                </a>
                <a href="?status=rejected" class="px-4 py-2 rounded-md <?= $status_filter === 'rejected' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-800 hover:bg-red-200' ?>">
                    Rejected <span class="ml-1 px-2 py-1 bg-opacity-20 rounded-full text-xs"><?= $rejectedCount ?></span>
                </a>
            </div>

            <!-- Search Input -->
            <input 
                type="text" 
                id="search-blogs" 
                placeholder="Search blogs..." 
                class="rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
            >
        </div>
    </div>

    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <p class="flex items-center">
            <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
            <?= $message ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- Blog Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($blogs)): ?>
        <div class="col-span-3 text-center py-8">
            <p class="text-gray-500">No blogs found</p>
        </div>
        <?php else: ?>
        <?php foreach ($blogs as $blog): 
            $statusColors = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'approved' => 'bg-green-100 text-green-800',
                'rejected' => 'bg-red-100 text-red-800'
            ];
            $statusColor = $statusColors[$blog['status']] ?? 'bg-gray-100 text-gray-800';
        ?>
            <div class="bg-white rounded-lg shadow hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 blog-card overflow-hidden" data-status="<?= strtolower($blog['status']) ?>">
                <div class="p-6">
                    <!-- Blog Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 hover:text-teal-600 transition-colors duration-200">
                                <?= htmlspecialchars($blog['title']) ?>
                            </h3>
                            <p class="text-sm text-gray-500"><?= date('M d, Y', strtotime($blog['created_at'])) ?></p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">
                            <?= ucfirst($blog['status']) ?>
                        </span>
                    </div>

                    <!-- Author Info -->
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                                    <i class="fas fa-user text-teal-500"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($blog['author_name'] ?? 'Anonymous') ?></p>
                                <p class="text-xs text-gray-500">Author</p>
                            </div>
                        </div>
                    </div>

                    <!-- Blog Preview -->
                    <div class="mb-4">
                        <div class="text-sm text-gray-600 line-clamp-3 prose">
                            <?= htmlspecialchars(substr(strip_tags($blog['content']), 0, 150)) ?>...
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                        <button onclick="viewBlogContent(<?= $blog['id'] ?>, '<?= addslashes($blog['title']) ?>', '<?= addslashes($blog['subtitle'] ?? '') ?>', `<?= addslashes($blog['content']) ?>`)" 
                                class="btn-icon-label flex-1 px-4 py-2 text-sm font-medium text-teal-600 bg-teal-50 rounded-md hover:bg-teal-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                            <i class="fas fa-eye"></i>
                        </button>
                        
                        <?php if ($blog['status'] === 'pending'): ?>
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn-icon-label w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn-icon-label w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        <?php elseif ($blog['status'] === 'approved'): ?>
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn-icon-label w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        <?php elseif ($blog['status'] === 'rejected'): ?>
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn-icon-label w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this blog?')" 
                                    class="btn-icon-label w-full px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-6 flex items-center justify-between border-t border-gray-200 bg-white pt-4">
        <div class="flex items-center">
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium"><?= min(($page - 1) * $limit + 1, $totalRecords) ?></span>
                to
                <span class="font-medium"><?= min($page * $limit, $totalRecords) ?></span>
                of
                <span class="font-medium"><?= $totalRecords ?></span>
                results
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $status_filter ? '&status=' . $status_filter : '' ?>" 
                   class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Previous
                </a>
            <?php endif; ?>
            
            <?php
            $range = 2;
            for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++):
            ?>
                <a href="?page=<?= $i ?><?= $status_filter ? '&status=' . $status_filter : '' ?>" 
                   class="relative inline-flex items-center <?= $i === $page ? 'bg-teal-600 text-white' : 'bg-white text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50' ?> px-3 py-2 text-sm font-semibold rounded-md">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $status_filter ? '&status=' . $status_filter : '' ?>" 
                   class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Next
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Blog Content Modal -->
<div id="blogContentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
            <h3 class="text-xl font-semibold text-gray-800" id="modalBlogTitle">Blog Title</h3>
            <button onclick="closeBlogContentModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div class="mb-4">
                <h4 id="modalBlogSubtitle" class="text-lg text-gray-600 italic"></h4>
            </div>
            
            <div id="modalBlogContent" class="prose max-w-none">
                <!-- Blog content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('search-blogs').addEventListener('input', function() {
    const searchText = this.value.toLowerCase();
    const cards = document.querySelectorAll('.blog-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(searchText)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
});

function viewBlogContent(id, title, subtitle, content) {
    document.getElementById('modalBlogTitle').textContent = title;
    document.getElementById('modalBlogSubtitle').textContent = subtitle || '';
    document.getElementById('modalBlogContent').innerHTML = content;
    document.getElementById('blogContentModal').classList.remove('hidden');
}

function closeBlogContentModal() {
    document.getElementById('blogContentModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('blogContentModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeBlogContentModal();
    }
});
</script>
