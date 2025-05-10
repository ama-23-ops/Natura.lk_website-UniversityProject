<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Dashboard' ?></title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-white min-h-screen flex flex-col overflow-x-hidden text-gray-600">
    <?php include 'header.php'; ?>
    
    <div class="lg:ml-64">
        <div class="p-4">
            <?= $content ?>
        </div>
        <footer class="bg-gray-100 text-center lg:text-left p-4">
            &copy; <?= date('Y'); ?> Admin Dashboard. All rights reserved.
        </footer>
    </div>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>