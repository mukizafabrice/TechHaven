<?php
$page_title = "Home - Discover Latest Electronics";
include '../includes/header.php';

// Add missing helper functions if they don't exist
if (!function_exists('calculateDiscountPercentage')) {
    function calculateDiscountPercentage($originalPrice, $discountPrice)
    {
        if (!$discountPrice || $discountPrice >= $originalPrice) return 0;
        return round((($originalPrice - $discountPrice) / $originalPrice) * 100);
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price)
    {
        return '$' . number_format($price, 2);
    }
}

// DEBUG: Let's check the database connection first
echo "<!-- DEBUG: Database connection check -->";
try {
    $test_conn = $pdo->query("SELECT 1");
    echo "<!-- DEBUG: Database connection OK -->";
} catch (Exception $e) {
    echo "<!-- DEBUG: Database connection FAILED: " . $e->getMessage() . " -->";
}

// DEBUG: Manual check for featured products with proper joins
echo "<!-- DEBUG: Manual featured products check -->";
try {
    $manual_check = $pdo->query("
        SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.stock_quantity, 
               p.featured_image, p.is_featured, p.is_active, p.short_description,
               c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_featured = 1 AND p.is_active = 1 
        LIMIT 8
    ");
    $manual_results = $manual_check->fetchAll(PDO::FETCH_ASSOC);
    echo "<!-- DEBUG: Manual SQL found " . count($manual_results) . " featured products -->";

    foreach ($manual_results as $product) {
        echo "<!-- DEBUG: Featured Product - ID: " . $product['id'] . ", Name: " . $product['name'] . " -->";
    }
} catch (Exception $e) {
    echo "<!-- DEBUG: Manual check error: " . $e->getMessage() . " -->";
}

// Get featured products using your function
$featured_products = getProducts($pdo, null, null, 8, true);
echo "<!-- DEBUG: getProducts() returned " . count($featured_products) . " featured products -->";

// Get all products to use as fallback
$all_products = getProducts($pdo, null, null, 12, false);
echo "<!-- DEBUG: Total active products: " . count($all_products) . " -->";

$categories = getCategories($pdo);
echo "<!-- DEBUG: Categories count: " . count($categories) . " -->";

// FORCE DISPLAY: If manual check found products but getProducts didn't, use manual results
if (empty($featured_products) && !empty($manual_results)) {
    echo "<!-- DEBUG: Using manual results since getProducts failed -->";
    $featured_products = $manual_results;
}
// Fallback to latest products if still empty
elseif (empty($featured_products) && !empty($all_products)) {
    echo "<!-- DEBUG: Using latest products as fallback -->";
    $featured_products = array_slice($all_products, 0, 8);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Consolidated Animation Definitions */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 1;
            }

            to {
                opacity: 2;
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes backgroundCycle {
            0% {
                background-image: url('https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
                opacity: 1;
            }

            24% {
                opacity: 1;
            }

            26% {
                opacity: 1;
            }

            28% {
                background-image: url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
                opacity: 1;
            }

            30% {
                opacity: 1;
            }

            54% {
                opacity: 1;
            }

            56% {
                opacity: 0;
            }

            58% {
                background-image: url('https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
                opacity: 0;
            }

            60% {
                opacity: 1;
            }

            84% {
                opacity: 1;
            }

            86% {
                opacity: 0.5;
            }

            88% {
                background-image: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
                opacity: 0.5;
            }

            90% {
                opacity: 1;
            }

            100% {
                opacity: 1;
            }
        }

        /* Animation Classes */
        .animate-fade-in-up {
            animation: fadeInUp 1s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 1.5s ease-out;
        }

        .animate-slide-in-left {
            animation: slideInLeft 1s ease-out;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        /* Component Styles */
        .btn-primary {
            background-color: #7B2FCE;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary:hover {
            background-color: #6a27b8;
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(123, 47, 206, 0.3);
        }

        .product-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover {
            transform: translateY(-8px);
        }

        .category-card {
            transition: all 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        .text-glow {
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .hero-bg {
            animation: backgroundCycle 32s ease-in-out infinite;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Ensure animations work with Intersection Observer */
        .animate-on-scroll {
            opacity: 0;
            animation-fill-mode: both;
        }

        .animate-fade-in-up.animate-on-scroll {
            animation: fadeInUp 1s ease-out forwards;
            animation-play-state: paused;
        }

        .animate-fade-in.animate-on-scroll {
            animation: fadeIn 1.5s ease-out forwards;
            animation-play-state: paused;
        }

        .animate-slide-in-left.animate-on-scroll {
            animation: slideInLeft 1s ease-out forwards;
            animation-play-state: paused;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <!-- Hero Section -->
    <section class="relative flex items-center justify-center min-h-screen overflow-hidden">
        <div class="absolute inset-0 z-0 hero-bg"></div>
        <div class="absolute inset-0 z-10 bg-black bg-opacity-40"></div>

        <!-- Content -->
        <div class="relative z-20 max-w-5xl px-4 mx-auto text-center text-white">
            <!-- Animated Badge -->
            <div class="mb-6 animate-fade-in-up">
                <span class="inline-flex items-center px-6 py-2 text-sm font-semibold bg-white border border-white rounded-full bg-opacity-10 border-opacity-30 backdrop-blur-sm">
                    <i class="mr-2 fas fa-star"></i>
                    Wima Store - Your Tech Destination
                </span>
            </div>

            <!-- Main Heading -->
            <h1 class="mb-6 text-6xl font-black leading-tight md:text-7xl lg:text-8xl animate-slide-in-left">
                WIMA STORE
            </h1>

            <!-- Subheading -->
            <p class="max-w-3xl mx-auto mb-10 text-lg font-light leading-relaxed md:text-xl animate-fade-in">
                Premium electronics at unbeatable prices. Quality products, fast delivery, trusted service.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col justify-center gap-4 sm:flex-row animate-fade-in-up">
                <a href="products.php"
                    class="px-10 py-4 text-lg font-bold text-white transition-all duration-300 bg-blue-600 rounded-lg shadow-lg hover:bg-blue-700">
                    <i class="mr-2 fas fa-shopping-cart"></i>Shop Now
                </a>
                <a href="#featured"
                    class="px-10 py-4 text-lg font-bold text-white transition-all duration-300 bg-gray-800 border border-gray-600 rounded-lg shadow-lg hover:bg-gray-700">
                    <i class="mr-2 fas fa-star"></i>Best Sellers
                </a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="container px-4 mx-auto">
            <div class="mb-16 text-center animate-on-scroll animate-fade-in-up">
                <h2 class="mb-6 text-4xl font-black text-gray-900 md:text-5xl">
                    Shop by <span class="gradient-text">Category</span>
                </h2>
                <p class="max-w-2xl mx-auto text-xl text-gray-600">
                    Explore our carefully curated categories featuring the latest in technology and innovation
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                <?php foreach ($categories as $index => $category): ?>
                    <a href="products.php?category=<?= $category['slug'] ?>" class="group category-card animate-on-scroll animate-fade-in-up" style="animation-delay: <?= $index * 0.1 ?>s;">
                        <div class="p-8 text-center transition-all duration-300 bg-white border border-gray-100 shadow-lg rounded-2xl hover:shadow-2xl group-hover:border-purple-200">
                            <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 transition-transform duration-300 shadow-lg rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 group-hover:scale-110">
                                <i class="text-2xl text-white fas 
                                    <?= $category['name'] === 'Smartphones' ? 'fa-mobile-alt' : ($category['name'] === 'Laptops & PCs' ? 'fa-laptop' : ($category['name'] === 'Cameras' ? 'fa-camera' : 'fa-headphones')) ?>">
                                </i>
                            </div>
                            <h3 class="mb-3 text-xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-purple-600">
                                <?= htmlspecialchars($category['name']) ?>
                            </h3>
                            <p class="text-sm leading-relaxed text-gray-600">
                                Discover the latest innovations in <?= htmlspecialchars(strtolower($category['name'])) ?> technology
                            </p>
                            <div class="flex items-center justify-center mt-4 font-semibold text-purple-600 transition-opacity duration-300 opacity-0 group-hover:opacity-100">
                                <span>Explore</span>
                                <i class="ml-2 transition-transform duration-300 transform fas fa-arrow-right group-hover:translate-x-1"></i>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="featured" class="py-20 bg-gradient-to-br from-gray-900 to-purple-900">
        <div class="container px-4 mx-auto">
            <div class="mb-16 text-center animate-on-scroll animate-fade-in-up">
                <h2 class="mb-6 text-4xl font-black text-white md:text-5xl">
                    <span class="text-white">Featured</span>
                    <span class="gradient-text">Products</span>
                </h2>
                <p class="max-w-2xl mx-auto text-xl text-gray-300">
                    Handpicked selection of our most innovative and popular products
                </p>
            </div>

            <?php if (!empty($featured_products)): ?>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <?php foreach ($featured_products as $index => $product):
                        // Safe data access with null coalescing
                        $product_id = $product['id'] ?? 0;
                        $product_name = $product['name'] ?? 'Unknown Product';
                        $product_price = floatval($product['price'] ?? 0);
                        $discount_price = !empty($product['discount_price']) ? floatval($product['discount_price']) : null;
                        $stock_quantity = intval($product['stock_quantity'] ?? 0);
                        $product_slug = $product['slug'] ?? '#';
                        $category_name = $product['category_name'] ?? 'Uncategorized';
                        $short_description = $product['short_description'] ?? $product['description'] ?? 'No description available';
                        $is_featured = $product['is_featured'] ?? false;

                        $discount_percentage = calculateDiscountPercentage($product_price, $discount_price);

                        // Improved image path checking
                        $image_paths = [
                            '../assets/uploads/products/' . ($product['featured_image'] ?? ''),
                            './assets/uploads/products/' . ($product['featured_image'] ?? ''),
                            '../admin/assets/uploads/products/' . ($product['featured_image'] ?? ''),
                            'assets/uploads/products/' . ($product['featured_image'] ?? ''),
                            '../uploads/products/' . ($product['featured_image'] ?? '')
                        ];

                        $image_src = '';
                        foreach ($image_paths as $path) {
                            if (!empty($product['featured_image']) && file_exists($path)) {
                                $image_src = $path;
                                break;
                            }
                        }

                        // Fallback to placeholder if no image found
                        if (empty($image_src)) {
                            $image_src = 'https://via.placeholder.com/300x200?text=No+Image';
                        }
                    ?>
                        <div class="overflow-hidden bg-white border border-gray-100 shadow-lg group product-card rounded-2xl hover:shadow-2xl animate-on-scroll animate-fade-in-up" style="animation-delay: <?= $index * 0.1 ?>s;">
                            <div class="relative overflow-hidden">
                                <img src="<?= $image_src ?>"
                                    alt="<?= htmlspecialchars($product_name) ?>"
                                    class="object-cover w-full h-48 transition-transform duration-500 transform group-hover:scale-105"
                                    onerror="this.src='https://via.placeholder.com/300x200?text=Image+Error'">

                                <?php if ($discount_percentage > 0): ?>
                                    <span class="absolute px-3 py-1 text-sm font-bold text-white rounded-full shadow-lg top-4 left-4 bg-gradient-to-r from-red-500 to-pink-500">
                                        -<?= $discount_percentage ?>% OFF
                                    </span>
                                <?php endif; ?>

                                <?php if ($stock_quantity == 0): ?>
                                    <span class="absolute px-3 py-1 text-sm font-bold text-white bg-gray-600 rounded-full top-4 right-4">
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="px-2 py-1 text-xs font-semibold text-purple-600 rounded bg-purple-50">
                                        <?= htmlspecialchars($category_name) ?>
                                    </span>
                                    <?php if ($is_featured): ?>
                                        <i class="text-yellow-400 fas fa-star" title="Featured Product"></i>
                                    <?php endif; ?>
                                </div>

                                <h3 class="mb-2 text-lg font-bold leading-tight text-gray-900 line-clamp-2">
                                    <?= htmlspecialchars($product_name) ?>
                                </h3>

                                <p class="mb-4 text-sm leading-relaxed text-gray-600 line-clamp-2">
                                    <?= htmlspecialchars($short_description) ?>
                                </p>

                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($discount_price && $discount_price > 0): ?>
                                            <span class="text-xl font-black text-gray-900"><?= formatPrice($discount_price) ?></span>
                                            <span class="text-sm text-gray-500 line-through"><?= formatPrice($product_price) ?></span>
                                        <?php else: ?>
                                            <span class="text-xl font-black text-gray-900"><?= formatPrice($product_price) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full 
                                        <?= $stock_quantity > 10 ? 'bg-green-100 text-green-800' : ($stock_quantity > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= $stock_quantity ?> in stock
                                    </span>
                                </div>

                                <a href="product-detail.php?slug=<?= $product_slug ?>"
                                    class="block w-full py-3 font-semibold text-center text-white transition-all duration-300 transform shadow-lg bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl hover:from-purple-700 hover:to-indigo-700 hover:shadow-xl hover:scale-105">
                                    <i class="mr-2 fas fa-eye"></i>View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-12 text-center animate-on-scroll animate-fade-in-up">
                    <a href="products.php"
                        class="inline-flex items-center px-8 py-4 text-lg font-bold text-purple-600 transition-all duration-300 bg-white border border-purple-200 shadow-lg rounded-xl hover:bg-gray-50 hover:shadow-xl group">
                        <span>View All Products</span>
                        <i class="ml-3 transition-transform duration-300 transform fas fa-arrow-right group-hover:translate-x-1"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="py-16 text-center bg-white bg-opacity-10 rounded-2xl backdrop-blur-sm animate-on-scroll animate-fade-in-up">
                    <i class="mb-6 text-6xl text-gray-400 fas fa-box-open"></i>
                    <h3 class="mb-4 text-2xl font-bold text-white">No Products Available</h3>
                    <p class="mb-8 text-lg text-gray-300">We're preparing something amazing for you!</p>
                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="products.php" class="px-8 py-3 font-semibold text-purple-600 transition duration-300 bg-white rounded-lg hover:bg-gray-100">
                            Browse Products
                        </a>
                        <a href="contact.php" class="px-8 py-3 font-semibold text-white transition duration-300 border border-white rounded-lg hover:bg-white hover:bg-opacity-10">
                            Contact Us
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="container px-4 mx-auto">
            <div class="mb-16 text-center animate-on-scroll animate-fade-in-up">
                <h2 class="mb-6 text-4xl font-black text-gray-900 md:text-5xl">
                    Why Choose <span class="gradient-text">TechHaven</span>?
                </h2>
                <p class="max-w-2xl mx-auto text-xl text-gray-600">
                    Experience the difference with our commitment to quality, service, and innovation
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="p-8 text-center transition-all duration-300 bg-white border border-gray-100 shadow-lg rounded-2xl hover:shadow-xl hover:border-purple-200 animate-on-scroll animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 shadow-lg rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600">
                        <i class="text-2xl text-white fas fa-shipping-fast"></i>
                    </div>
                    <h3 class="mb-4 text-2xl font-black text-gray-900">Free Shipping</h3>
                    <p class="text-lg leading-relaxed text-gray-600">
                        Free express shipping on all orders over $50. Fast delivery to your doorstep.
                    </p>
                </div>

                <div class="p-8 text-center transition-all duration-300 bg-white border border-gray-100 shadow-lg rounded-2xl hover:shadow-xl hover:border-blue-200 animate-on-scroll animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 shadow-lg rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600">
                        <i class="text-2xl text-white fas fa-shield-alt"></i>
                    </div>
                    <h3 class="mb-4 text-2xl font-black text-gray-900">Quality Guarantee</h3>
                    <p class="text-lg leading-relaxed text-gray-600">
                        30-day money back guarantee and 2-year warranty on all our premium products.
                    </p>
                </div>

                <div class="p-8 text-center transition-all duration-300 bg-white border border-gray-100 shadow-lg rounded-2xl hover:shadow-xl hover:border-purple-200 animate-on-scroll animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 shadow-lg rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600">
                        <i class="text-2xl text-white fas fa-headset"></i>
                    </div>
                    <h3 class="mb-4 text-2xl font-black text-gray-900">24/7 Support</h3>
                    <p class="text-lg leading-relaxed text-gray-600">
                        Round-the-clock customer support with expert technicians ready to assist you.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-20 bg-gradient-to-r from-purple-600 to-indigo-700">
        <div class="container px-4 mx-auto text-center">
            <h2 class="mb-6 text-4xl font-black text-white md:text-5xl animate-on-scroll animate-fade-in-up">
                Ready to Experience the Future?
            </h2>
            <p class="max-w-2xl mx-auto mb-12 text-xl text-purple-100 animate-on-scroll animate-fade-in-up" style="animation-delay: 0.1s;">
                Join thousands of satisfied customers who trust Wima Store for their technology needs.
            </p>
            <div class="flex flex-col items-center justify-center gap-6 sm:flex-row animate-on-scroll animate-fade-in-up" style="animation-delay: 0.2s;">
                <a href="products.php"
                    class="px-12 py-5 text-xl font-bold text-purple-600 transition-all duration-300 bg-white shadow-2xl rounded-2xl hover:bg-gray-100 hover:scale-105">
                    <i class="mr-4 fas fa-shopping-cart"></i>Start Shopping Now
                </a>
                <a href="contact.php"
                    class="px-12 py-5 text-xl font-bold text-white transition-all duration-300 border-2 border-white rounded-2xl hover:bg-white hover:bg-opacity-10 backdrop-blur-sm">
                    <i class="mr-4 fas fa-envelope"></i>Contact Us
                </a>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Improved animation system with Intersection Observer
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Enhanced Intersection Observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        // Optional: unobserve after animation starts
                        // observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe all animated elements
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });

            // Fix for text-glow animation restart
            const glowElements = document.querySelectorAll('.text-glow');
            glowElements.forEach(element => {
                // Force reflow to restart animation if needed
                element.style.animation = 'none';
                element.offsetHeight; // Trigger reflow
                element.style.animation = null;
            });
        });
    </script>
</body>

</html>