<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle blog operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $title = trim($_POST['title']);
                $subtitle = trim($_POST['subtitle']);
                $content = trim($_POST['content_content']); 

                if (!empty($title) && !empty($content)) {
                    $stmt = $conn->prepare("INSERT INTO blogs (user_id, title, subtitle, content, status) VALUES (?, ?, ?, ?, 'pending')");
                    if ($stmt->execute([$userId, $title, $subtitle, $content])) {
                        $message = "Blog post created successfully! It will be reviewed by an admin.";
                        $messageType = "success";
                    } else {
                        $message = "Error creating blog post.";
                        $messageType = "error";
                    }
                }
                break;

            case 'update':
                $blogId = $_POST['blog_id'];
                $title = trim($_POST['title']);
                $subtitle = trim($_POST['subtitle']);
                $content = trim($_POST['content_content']);

                if (!empty($title) && !empty($content)) {
                    $stmt = $conn->prepare("UPDATE blogs SET title = ?, subtitle = ?, content = ?, status = 'pending' WHERE id = ? AND user_id = ?");
                    if ($stmt->execute([$title, $subtitle, $content, $blogId, $userId])) {
                        $message = "Blog post updated successfully! It will be reviewed again by an admin.";
                        $messageType = "success";
                    } else {
                        $message = "Error updating blog post.";
                        $messageType = "error";
                    }
                }
                break;

            case 'delete':
                $blogId = $_POST['blog_id'];
                $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ? AND user_id = ?");
                if ($stmt->execute([$blogId, $userId])) {
                    $message = "Blog post deleted successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error deleting blog post.";
                    $messageType = "error";
                }
                break;
        }
    }
}

// Fetch all blogs for the current user
$stmt = $conn->prepare("SELECT * FROM blogs WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$userBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total blogs count
$stmt = $conn->prepare("SELECT COUNT(*) FROM blogs WHERE user_id = ?");
$stmt->execute([$userId]);
$totalBlogs = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blogs</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/scripts.php'; ?>
    <style>
        .teal-gradient {
            background: linear-gradient(135deg, #00796b 0%, #009688 100%);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        .truncate-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .truncate-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .blog-content {
            min-height: 72px; /* Ensure consistent height for content area */
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php 
    include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/header.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/rich_text_editor.php';
    ?>

    <div class="container mx-auto p-6">
        <!-- Header Section -->
        <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">My Blogs</h1>
                    <p class="text-teal-100">Manage and create your blog posts</p>
                </div>
                <button onclick="openBlogModal()" 
                        class="bg-white text-teal-600 px-4 py-2 rounded-lg hover:bg-teal-50 transition duration-300 flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    New Blog Post
                </button>
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

        <!-- Blog Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600">Total Posts</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?= $totalBlogs ?></h3>
                    </div>
                    <div class="p-3 bg-teal-100 rounded-full">
                        <i class="fas fa-file-alt text-teal-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600">Recent Post</p>
                        <h3 class="text-lg font-medium text-gray-800">
                            <?= !empty($userBlogs) ? substr($userBlogs[0]['title'], 0, 20) . '...' : 'No posts yet' ?>
                        </h3>
                    </div>
                    <div class="p-3 bg-teal-100 rounded-full">
                        <i class="fas fa-clock text-teal-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600">Last Updated</p>
                        <h3 class="text-lg font-medium text-gray-800">
                            <?= !empty($userBlogs) ? date('M d, Y', strtotime($userBlogs[0]['created_at'])) : 'N/A' ?>
                        </h3>
                    </div>
                    <div class="p-3 bg-teal-100 rounded-full">
                        <i class="fas fa-calendar-alt text-teal-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blog Posts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($userBlogs as $blog): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden card-hover">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-semibold text-gray-800 truncate-1" title="<?= htmlspecialchars($blog['title']) ?>"><?= htmlspecialchars($blog['title']) ?></h3>
                        <span class="px-3 py-1 rounded-full text-sm flex-shrink-0 ml-2 <?php
                            echo match($blog['status']) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                            };
                        ?>">
                            <?= ucfirst($blog['status']) ?>
                        </span>
                    </div>
                    <?php if (!empty($blog['subtitle'])): ?>
                    <p class="text-gray-600 mb-4 truncate-2" title="<?= htmlspecialchars($blog['subtitle']) ?>"><?= htmlspecialchars($blog['subtitle']) ?></p>
                    <?php endif; ?>
                    <div class="text-gray-500 mb-4 blog-content truncate-3">
                        <?= strip_tags($blog['content']) ?>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="text-sm text-gray-500">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <?= date('M d, Y', strtotime($blog['created_at'])) ?>
                        </span>
                        <div class="space-x-2">
                            <button onclick="openEditBlog(<?= $blog['id'] ?>, '<?= addslashes($blog['title']) ?>', '<?= addslashes($blog['subtitle']) ?>', '<?= addslashes($blog['content']) ?>')" 
                                    class="text-teal-600 hover:text-teal-800">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmDelete(<?= $blog['id'] ?>)" 
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($userBlogs)): ?>
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <i class="fas fa-blog text-6xl"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-600 mb-2">No Blog Posts Yet</h3>
            <p class="text-gray-500">Start writing your first blog post today!</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Blog Modal (Combined Create/Edit) -->
    <div id="blogModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg w-full max-w-2xl mx-4 relative">
            <!-- Close button -->
            <button onclick="closeBlogModal()" 
                    class="absolute -top-4 -right-4 w-8 h-8 flex items-center justify-center bg-red-500 text-white rounded-full hover:bg-red-600 transition-all duration-200 transform hover:rotate-90">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <h3 class="text-xl font-semibold text-gray-800" id="modalTitle">Create New Blog Post</h3>
                </div>
            </div>
            
            <form method="POST" class="p-6" id="blogForm">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="blog_id" id="blogId">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                        Title
                    </label>
                    <input type="text" name="title" id="title" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="subtitle">
                        Subtitle (Optional)
                    </label>
                    <input type="text" name="subtitle" id="subtitle"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="content">
                        Content
                    </label>
                    <?php echo initRichTextEditor('content'); ?>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" 
                            onclick="closeBlogModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                        <i class="fas fa-save mr-2"></i>
                        <span id="submitButtonText">Create Post</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/customer/includes/footer.php'; ?>
    
    <script>
    function openBlogModal(mode = 'create', blogData = null) {
        const modal = document.getElementById('blogModal');
        const form = document.getElementById('blogForm');
        const modalTitle = document.getElementById('modalTitle');
        const submitButton = document.getElementById('submitButtonText');
        const formAction = document.getElementById('formAction');
        
        // Reset form
        form.reset();
        document.getElementById('content').innerHTML = '';
        document.getElementById('content_content').value = '';
        
        if (mode === 'edit' && blogData) {
            modalTitle.textContent = 'Edit Blog Post';
            submitButton.textContent = 'Update Post';
            formAction.value = 'update';
            
            // Fill form with blog data
            document.getElementById('blogId').value = blogData.id;
            document.getElementById('title').value = blogData.title;
            document.getElementById('subtitle').value = blogData.subtitle || '';
            document.getElementById('content').innerHTML = blogData.content;
            document.getElementById('content_content').value = blogData.content; // Same field name for both create and edit
        } else {
            modalTitle.textContent = 'Create New Blog Post';
            submitButton.textContent = 'Create Post';
            formAction.value = 'create';
            document.getElementById('blogId').value = '';
        }
        
        modal.classList.remove('hidden');
    }

    function closeBlogModal() {
        document.getElementById('blogModal').classList.add('hidden');
    }

    function openEditBlog(blogId, title, subtitle, content) {
        openBlogModal('edit', {
            id: blogId,
            title: title,
            subtitle: subtitle,
            content: content
        });
    }

    function confirmDelete(blogId) {
        if (confirm('Are you sure you want to delete this blog post?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="blog_id" value="${blogId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Update the click handlers in the existing blog cards
    document.querySelectorAll('[onclick*="openEditModal"]').forEach(button => {
        const onclick = button.getAttribute('onclick');
        const params = onclick.match(/\((.*?)\)/)[1];
        button.setAttribute('onclick', `openEditBlog(${params})`);
    });

    // Update the "New Blog Post" button
    document.querySelector('[onclick*="newBlogModal"]').setAttribute('onclick', 'openBlogModal()');
    </script>
</body>
</html>