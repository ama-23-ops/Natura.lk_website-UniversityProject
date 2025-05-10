<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Get all approved blogs
$stmt = $conn->prepare("SELECT b.*, u.name as author_name, u.username as author_username 
                        FROM blogs b 
                        JOIN users u ON b.user_id = u.id
                        WHERE b.status = 'approved'
                        ORDER BY b.created_at DESC");
$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Blog</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    </style>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/scripts.php'; ?>
</head>
<body class="bg-gray-100 font-poppins flex flex-col">
    <div class="content-container flex flex-col flex-grow">
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

        <div class="container mx-auto px-4 py-12">
            <!-- Title Section with Gradient -->
            <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg mt-10">
                <div class="flex items-center">
                    <i class="fas fa-newspaper text-4xl mr-4"></i>
                    <div>
                        <h1 class="text-3xl font-bold">Our Blog</h1>
                        <p class="text-teal-100">Discover the latest insights, news, and stories from our community</p>
                    </div>
                </div>
            </div>

            <!-- Blog List Section -->
            <?php if (empty($blogs)): ?>
                <div class="text-center py-16 bg-white rounded-lg shadow-md p-6">
                    <div class="text-teal-600 mb-4">
                        <i class="fas fa-newspaper text-8xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">No Published Posts Yet</h3>
                    <p class="text-gray-600 mb-8">Check back soon for new content!</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($blogs as $blog): ?>
                    <a href="/blogs/blog-details.php?id=<?= $blog['id'] ?>" class="blog-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                        <div class="p-6">
                            <div class="mb-4 relative">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-teal-100 text-teal-800">
                                    <i class="fas fa-pen-fancy mr-2"></i>Blog Post
                                </span>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2"><?= htmlspecialchars($blog['title']) ?></h2>
                            
                            <?php if (!empty($blog['subtitle'])): ?>
                                <p class="text-gray-600 mb-4 italic line-clamp-2"><?= htmlspecialchars($blog['subtitle']) ?></p>
                            <?php endif; ?>
                            
                            <div class="text-gray-500 mb-4 line-clamp-3">
                                <?= substr(strip_tags($blog['content']), 0, 150) ?>...
                            </div>
                            
                            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
                                <div class="flex items-center">
                                    <div class="text-sm">
                                        <p class="text-gray-900 font-semibold"><?= htmlspecialchars($blog['author_name']) ?></p>
                                        <p class="text-gray-500"><?= date('M d, Y', strtotime($blog['created_at'])) ?></p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center text-teal-600 hover:text-teal-800 font-medium transition duration-300">
                                    Read more
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
