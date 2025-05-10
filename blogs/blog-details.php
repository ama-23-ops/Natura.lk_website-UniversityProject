<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Check if blog ID is provided
$blogId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$blogId) {
    header('Location: /blogs/');
    exit();
}

// Get blog details with author information
$stmt = $conn->prepare("SELECT b.*, u.name as author_name, u.username as author_username, u.profile_picture
                        FROM blogs b 
                        JOIN users u ON b.user_id = u.id
                        WHERE b.id = ? AND b.status = 'approved'");
$stmt->execute([$blogId]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

// If blog not found or not approved, redirect to blogs listing
if (!$blog) {
    header('Location: /blogs/');
    exit();
}

// Get other recommended blogs (excluding current one)
$stmt = $conn->prepare("SELECT id, title, subtitle, created_at 
                        FROM blogs 
                        WHERE id != ? AND status = 'approved'
                        ORDER BY created_at DESC
                        LIMIT 3");
$stmt->execute([$blogId]);
$recommendedBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($blog['title']) ?> | Our Blog</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        body {
            position: relative;
            background-image: url('/assets/images/bg.jpg');
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

        .content-container {
            position: relative;
            z-index: 1;
        }
        
        .teal-gradient {
            background: linear-gradient(135deg, #00796b 0%, #009688 100%);
        }
        .blog-content {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.8;
            font-size: 1.125rem;
            color: #374151;
        }
        .blog-content h2 {
            font-size: 2rem;
            margin-top: 2.5rem;
            margin-bottom: 1.25rem;
            font-weight: 700;
            color: #1F2937;
        }
        .blog-content h3 {
            font-size: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: #1F2937;
        }
        .blog-content p {
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }
        .blog-content ul, .blog-content ol {
            margin: 1.5rem 0 1.5rem 1.5rem;
        }
        .blog-content li {
            margin-bottom: 0.75rem;
            position: relative;
        }
        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.75rem;
            margin: 2.5rem 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .blog-content blockquote {
            border-left: 4px solid #009688;
            padding: 1rem 1.5rem;
            margin: 2rem 0;
            background-color: #F3F4F6;
            border-radius: 0.5rem;
            font-style: italic;
            color: #4B5563;
        }
        .blog-content code {
            background-color: #F3F4F6;
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
            font-size: 0.875em;
            color: #00796b;
        }
        .blog-content pre {
            background-color: #1F2937;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1.5rem 0;
        }
        .blog-content pre code {
            background-color: transparent;
            color: #E5E7EB;
            padding: 0;
        }
    </style>
</head>
<body class="bg-gray-100 font-poppins flex flex-col">
    <div class="content-container flex flex-col flex-grow">
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

        <div class="container mx-auto px-4 py-12">
            <div class="max-w-4xl mx-auto">
                <!-- Back to blogs link -->
                <div class="mb-8 mt-10">
                    <a href="/blogs/" class="inline-flex items-center text-teal-600 hover:text-teal-800 font-medium transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to all blogs
                    </a>
                </div>
                
                <!-- Blog Header -->
                <div class="bg-white rounded-xl shadow-sm p-8 mb-10">
                    <div class="mb-8">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-teal-100 text-teal-800 mb-4">
                            Blog Post
                        </span>
                        <h1 class="text-4xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($blog['title']) ?></h1>
                        
                        <?php if (!empty($blog['subtitle'])): ?>
                            <p class="text-xl text-gray-600 italic"><?= htmlspecialchars($blog['subtitle']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex items-center border-t border-gray-100 pt-6">
                        <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 mr-4 ring-2 ring-teal-100">
                            <?php if (!empty($blog['profile_picture'])): ?>
                                <img src="/uploads/<?= htmlspecialchars($blog['profile_picture']) ?>" alt="Author" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-teal-100 text-teal-500">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-gray-900 font-semibold"><?= htmlspecialchars($blog['author_name']) ?></p>
                            <p class="text-gray-500 text-sm">
                                Published on <?= date('F j, Y', strtotime($blog['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Blog Content -->
                <div class="bg-white rounded-xl shadow-sm p-8 mb-12">
                    <div class="blog-content prose max-w-none">
                        <?= $blog['content'] ?>
                    </div>
                </div>
                
                <!-- Share Buttons -->
                <div class="bg-white rounded-xl shadow-sm p-8 mb-12">
                    <p class="text-gray-900 font-semibold mb-6">Share this article</p>
                    <div class="flex space-x-6">
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($blog['title']) ?>" 
                        target="_blank" 
                        class="flex items-center text-gray-600 hover:text-blue-400 transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                            <span class="ml-2">Share on Twitter</span>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                        target="_blank" 
                        class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fab fa-facebook-f text-xl"></i>
                            <span class="ml-2">Share on Facebook</span>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($blog['title']) ?>" 
                        target="_blank" 
                        class="flex items-center text-gray-600 hover:text-blue-700 transition-colors">
                            <i class="fab fa-linkedin-in text-xl"></i>
                            <span class="ml-2">Share on LinkedIn</span>
                        </a>
                    </div>
                </div>
                
                <!-- Recommended Blogs -->
                <?php if (!empty($recommendedBlogs)): ?>
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">You might also like</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach ($recommendedBlogs as $recBlog): ?>
                        <a href="/blogs/blog-details.php?id=<?= $recBlog['id'] ?>" 
                        class="group p-4 rounded-lg hover:bg-gray-50 transition-all">
                            <h4 class="font-bold text-gray-900 mb-2 group-hover:text-teal-600 transition-colors">
                                <?= htmlspecialchars($recBlog['title']) ?>
                            </h4>
                            <?php if (!empty($recBlog['subtitle'])): ?>
                                <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars(substr($recBlog['subtitle'], 0, 60)) . (strlen($recBlog['subtitle']) > 60 ? '...' : '') ?></p>
                            <?php endif; ?>
                            <p class="text-gray-500 text-sm"><?= date('M d, Y', strtotime($recBlog['created_at'])) ?></p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>