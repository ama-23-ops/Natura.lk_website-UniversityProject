<?php
session_start();
include_once 'db.php';

// Fetch the latest products
$stmt = $conn->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC LIMIT 6");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Berkshire+Swash&display=swap" rel="stylesheet">
</head>
<body class="bg-teal-800">
<?php include('includes/header.php'); ?>
    <!-- Hero Section with Swiper -->
    <div class="w-full">
        <div class="swiper-container h-screen">
            <div class="swiper-wrapper">
                <div class="swiper-slide relative h-screen">
                    <img src="assets/images/hero1.jpg" alt="E-Commerce Hero 1" class="w-full h-full object-cover">
                    <div class="swiper-overlay absolute top-0 left-0 w-full h-full bg-black opacity-50"></div>
                    <div
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white z-10">
                        <h1 class="text-4xl md:text-6xl font-bold mb-4">Welcome to Our Store</h1>
                        <p class="text-lg md:text-xl">Discover amazing products!</p>
                    </div>
                </div>
                <div class="swiper-slide relative h-screen">
                    <img src="assets/images/hero2.jpg" alt="E-Commerce Hero 2" class="w-full h-full object-cover">
                    <div class="swiper-overlay absolute top-0 left-0 w-full h-full bg-black opacity-50"></div>
                    <div
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white z-10">
                        <h1 class="text-4xl md:text-6xl font-bold mb-4">Latest Collections</h1>
                        <p class="text-lg md:text-xl">Shop the latest trends now.</p>
                    </div>
                </div>
                <div class="swiper-slide relative h-screen">
                    <img src="assets/images/hero3.jpg" alt="E-Commerce Hero 3" class="w-full h-full object-cover">
                    <div class="swiper-overlay absolute top-0 left-0 w-full h-full bg-black opacity-50"></div>
                    <div
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white z-10">
                        <h1 class="text-4xl md:text-6xl font-bold mb-4">Explore Our Products</h1>
                        <p class="text-lg md:text-xl">Find something you will love.</p>
                    </div>
                </div>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>

            <!-- Add Navigation -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>

  

    <?php include('includes/footer.php'); ?>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        function randomOffset(max) {
            return (Math.random() * max - max/2).toFixed(2);
        }

        var swiper = new Swiper('.swiper-container', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            on: {
                init: function () {
                    document.querySelectorAll('.swiper-slide').forEach(slide => {
                        const title = slide.querySelector('h1');
                        const subtitle = slide.querySelector('p');
                        
                        title.style.setProperty('--random-offset', `${randomOffset(20)}%`);
                        subtitle.style.setProperty('--random-offset', `${randomOffset(20)}%`);
                    });
                }
            }
        });
    </script>

     <style>
        .swiper-container {
            width: 100%;
            overflow: hidden;
        }

        .swiper-slide {
            width: 100vw;
        }

        .swiper-overlay {
            background: rgba(0, 0, 0, 0.8); /* Black with 20% opacity */
        }

        body {
            margin: 0;
            padding: 0;
        }

        html {
             overflow-x: hidden;
         }

        /* Swiper navigation and pagination colors */
        .swiper-button-next,
        .swiper-button-prev {
            color:rgb(255, 255, 255) !important;
        }

        .swiper-pagination-bullet-active {
            background:rgb(255, 255, 255) !important; 
        }

        /* Custom Swiper navigation buttons */
        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            font-size: 24px;
        }

        .swiper-button-next:after {
            content: "\f0a9"; /* fa-circle-arrow-right */
        }

        .swiper-button-prev:after {
            content: "\f0a8"; /* fa-circle-arrow-left */
        }

        @keyframes slideFromLeft {
             0%, 100% {
                 transform: translateX(calc(var(--random-offset, -10%) - 10%));
                 opacity: 0.8;
             }
             50% {
                 transform: translateX(0);
                 opacity: 1;
             }
         }

         @keyframes slideFromRight {
             0%, 100% {
                 transform: translateX(calc(var(--random-offset, 10%) + 10%));
                 opacity: 0.5;
             }
             50% {
                 transform: translateX(0);
                 opacity: 1;
             }
         }

         .swiper-slide h1, .swiper-slide p {
             --random-offset: 0%;
         }

         .swiper-slide h1 {
             font-family: 'Berkshire Swash', cursive;
             animation: slideFromLeft 10s ease-in-out infinite;
         }

         .swiper-slide p {
             animation: slideFromRight 10s ease-in-out infinite;
         }

         .swiper-slide-active h1 {
             animation: slideFromLeft 10s ease-in-out infinite;
         }

         .swiper-slide-active p {
             animation: slideFromRight 10s ease-in-out infinite;
         }
    </style>
</body>
</html>
