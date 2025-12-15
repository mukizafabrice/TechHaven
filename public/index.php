<?php
$page_title = "Home - Discover Latest Electronics";
include '../includes/header.php';

// Add missing helper functions if they don't exist
if (!function_exists('calculateDiscountPercentage')) {
    function calculateDiscountPercentage($originalPrice, $discountPrice)
    {
        if (!$discountPrice || $discountPrice >= $originalPrice) return 0;
        return round((($originalPrice - $discount_price) / $originalPrice) * 100);
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price)
    {
        $price = floatval($price);
        if ($price <= 0) {
            return 'RWF 0';
        }
        return 'RWF ' . number_format($price, 0, '.', ',');
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
                opacity: 0;
            }

            to {
                opacity: 1;
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
                background-image: url('https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
                opacity: 1;
            }

            30% {
                opacity: 1;
            }

            31% {
                background-image: url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
                opacity: 1;
            }

            60% {
                opacity: 1;
            }

            61% {
                background-image: url('https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
                opacity: 1;
            }

            62% {
                opacity: 1;
            }

            89% {
                background-image: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
                opacity: 0;
            }

            90% {
                opacity: 1;
            }

            100% {
                opacity: 1;
            }
        }

        @keyframes pulseGlow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.4);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(37, 211, 102, 0);
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

        .animate-pulse-glow {
            animation: pulseGlow 2s infinite;
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
            height: 100%;
            display: flex;
            flex-direction: column;
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
            will-change: background-image;
        }

        /* Ensure animations work with Intersection Observer */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }

        /* Sticky Social Media Bar Styles */
        .social-bar {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .social-bar:hover {
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .social-bar.hidden {
            transform: translateY(-50%) translateX(100px);
            opacity: 0;
            pointer-events: none;
        }

        .social-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 16px;
            color: white;
            font-size: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .social-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .social-icon:hover::before {
            left: 100%;
        }

        .social-icon:hover {
            transform: translateY(-5px) scale(1.1);
        }

        .social-icon.whatsapp {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        .social-icon.instagram {
            background: linear-gradient(135deg, #E4405F, #833AB4, #405DE6);
        }

        .social-icon.facebook {
            background: linear-gradient(135deg, #1877F2, #0A5BC4);
        }

        .social-tooltip {
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-right: 12px;
            padding: 8px 16px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .social-icon:hover .social-tooltip {
            opacity: 1;
            transform: translateY(-50%) translateX(-5px);
        }

        .social-tooltip::after {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: transparent transparent transparent rgba(0, 0, 0, 0.8);
        }

        /* Professional Image Container */
        .image-container {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            height: 280px;
            min-height: 280px;
            max-height: 280px;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover .product-image {
            transform: scale(1.08);
        }

        /* Aspect ratio for product images */
        .aspect-square {
            aspect-ratio: 1 / 1;
        }

        /* Mobile responsiveness for social bar */
        @media (max-width: 768px) {
            .social-bar {
                right: 10px;
                padding: 8px;
                border-radius: 20px;
            }

            .social-icon {
                width: 44px;
                height: 44px;
                font-size: 1.25rem;
                border-radius: 12px;
            }

            .social-tooltip {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .social-bar {
                bottom: 20px;
                top: auto;
                right: 20px;
                transform: none;
                flex-direction: row;
                border-radius: 20px;
                padding: 10px;
                width: auto;
            }

            .social-bar:hover {
                transform: none;
            }

            .hero-title {
                font-size: 2.25rem !important;
            }

            .hero-subtitle {
                font-size: 0.95rem !important;
            }

            .image-container {
                height: 220px;
                min-height: 220px;
                max-height: 220px;
            }
        }
    </style>
</head>

<body class="font-sans antialiased">
    <!-- Sticky Social Media Bar -->
    <div class="social-bar" id="socialBar">
        <!-- WhatsApp -->
        <a href="https://wa.me/250780088390"
            target="_blank"
            rel="noopener noreferrer"
            class="social-icon whatsapp animate-pulse-glow"
            aria-label="Contact us on WhatsApp">
            <i class="fab fa-whatsapp"></i>
            <span class="social-tooltip">Chat on WhatsApp</span>
        </a>

        <!-- Instagram -->
        <a href="https://instagram.com"
            target="_blank"
            rel="noopener noreferrer"
            class="social-icon instagram"
            aria-label="Follow us on Instagram">
            <i class="fab fa-instagram"></i>
            <span class="social-tooltip">Follow on Instagram</span>
        </a>

        <!-- Facebook -->
        <a href="https://facebook.com"
            target="_blank"
            rel="noopener noreferrer"
            class="social-icon facebook"
            aria-label="Like us on Facebook">
            <i class="fab fa-facebook-f"></i>
            <span class="social-tooltip">Like on Facebook</span>
        </a>
    </div>

    <!-- Hero Section - Fixed Animation -->
    <section class="relative flex items-center justify-center min-h-[85vh] overflow-hidden">
        <!-- Hero Background with fixed animation -->
        <div class="absolute inset-0 z-0 hero-bg"></div>
        <div class="absolute inset-0 z-10 bg-black/30"></div>

        <!-- Content -->
        <div class="relative z-20 max-w-6xl px-4 mx-auto text-center text-white">
            <!-- Animated Badge -->
            <div class="mb-6 animate-fade-in-up">
                <span class="inline-flex items-center px-5 py-2 text-sm font-semibold tracking-wide text-white uppercase border bg-white/10 backdrop-blur-sm border-white/20 rounded-xl">
                    <i class="mr-2 fas fa-crown"></i>
                    Premium Electronics
                </span>
            </div>

            <!-- Main Heading - Reduced Size -->
            <h1 class="mb-4 text-4xl font-black leading-tight md:text-5xl lg:text-6xl hero-title animate-slide-in-left">
                WIMA <span class="gradient-text">STORE</span>
            </h1>

            <!-- Subheading - Reduced Size -->
            <p class="max-w-2xl mx-auto mb-10 text-base font-light leading-relaxed md:text-lg hero-subtitle animate-fade-in">
                Discover premium electronics at unbeatable prices. Experience quality, innovation, and exceptional service.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col justify-center gap-3 sm:flex-row animate-fade-in-up">
                <a href="products.php"
                    class="relative px-6 py-3 overflow-hidden text-base font-bold text-white transition-all duration-300 shadow-xl group bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:shadow-2xl hover:scale-105">
                    <div class="relative z-10 flex items-center justify-center">
                        <i class="mr-2 fas fa-shopping-cart"></i>Shop Now
                    </div>
                    <div class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-r from-purple-600 to-blue-600 group-hover:opacity-100"></div>
                </a>
                <a href="#featured"
                    class="px-6 py-3 text-base font-bold text-white transition-all duration-300 bg-transparent border border-white/40 rounded-lg group hover:bg-white/10 hover:border-white/60">
                    <div class="flex items-center justify-center">
                        <i class="mr-2 fas fa-star"></i>Featured Products
                    </div>
                </a>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute transform -translate-x-1/2 bottom-6 left-1/2 animate-bounce">
            <a href="#categories" class="transition-colors duration-300 text-white/70 hover:text-white">
                <i class="text-xl fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="container px-4 mx-auto">
            <div class="mb-12 text-center animate-on-scroll">
                <h2 class="mb-4 text-3xl font-black text-gray-900 md:text-4xl">
                    Shop by <span class="gradient-text">Category</span>
                </h2>
                <p class="max-w-2xl mx-auto text-lg text-gray-600">
                    Explore our carefully curated categories featuring the latest in technology and innovation
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <?php foreach ($categories as $index => $category): ?>
                    <div class="animate-on-scroll" style="animation-delay: <?= $index * 0.1 ?>s;">
                        <a href="products.php?category=<?= $category['slug'] ?>" class="block group">
                            <div class="p-6 text-center transition-all duration-300 bg-white border border-gray-100 shadow-lg rounded-xl hover:shadow-xl group-hover:border-purple-200">
                                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 transition-transform duration-300 shadow-lg rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 group-hover:scale-110">
                                    <i class="text-xl text-white fas 
                                        <?= $category['name'] === 'Smartphones' ? 'fa-mobile-alt' : ($category['name'] === 'Laptops & PCs' ? 'fa-laptop' : ($category['name'] === 'Cameras' ? 'fa-camera' : 'fa-headphones')) ?>">
                                    </i>
                                </div>
                                <h3 class="mb-2 text-lg font-bold text-gray-900 transition-colors duration-300 group-hover:text-purple-600">
                                    <?= htmlspecialchars($category['name']) ?>
                                </h3>
                                <p class="text-sm leading-relaxed text-gray-600">
                                    Discover the latest innovations in <?= htmlspecialchars(strtolower($category['name'])) ?> technology
                                </p>
                                <div class="flex items-center justify-center mt-3 text-sm font-semibold text-purple-600 transition-opacity duration-300 opacity-0 group-hover:opacity-100">
                                    <span>Explore</span>
                                    <i class="ml-1 transition-transform duration-300 transform fas fa-arrow-right group-hover:translate-x-1"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="featured" class="py-16 bg-gradient-to-br from-gray-900 to-purple-900">
        <div class="container px-4 mx-auto">
            <div class="mb-12 text-center animate-on-scroll">
                <h2 class="mb-4 text-3xl font-black text-white md:text-4xl">
                    <span class="text-white">Featured</span>
                    <span class="gradient-text">Products</span>
                </h2>
                <p class="max-w-2xl mx-auto text-lg text-gray-300">
                    Handpicked selection of our most innovative and popular products
                </p>
            </div>

            <?php if (!empty($featured_products)): ?>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
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

                        // Improved image path checking with better fallback
                        $image_src = '';
                        if (!empty($product['featured_image'])) {
                            $image_paths = [
                                '../assets/uploads/products/' . $product['featured_image'],
                                './assets/uploads/products/' . $product['featured_image'],
                                '../admin/assets/uploads/products/' . $product['featured_image'],
                                'assets/uploads/products/' . $product['featured_image'],
                                '../uploads/products/' . $product['featured_image']
                            ];

                            foreach ($image_paths as $path) {
                                if (file_exists($path)) {
                                    $image_src = $path;
                                    break;
                                }
                            }
                        }

                        // Fallback to professional placeholder
                        if (empty($image_src)) {
                            $image_src = 'https://images.unsplash.com/photo-1556656793-08538906a9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                        }
                    ?>
                        <div class="animate-on-scroll" style="animation-delay: <?= $index * 0.1 ?>s;">
                            <div class="overflow-hidden bg-white border border-gray-100 shadow-lg group product-card rounded-xl hover:shadow-xl">
                                <!-- Professional Image Container with fixed height -->
                                <div class="relative image-container">
                                    <img src="<?= $image_src ?>"
                                        alt="<?= htmlspecialchars($product_name) ?>"
                                        class="product-image"
                                        onerror="this.src='https://images.unsplash.com/photo-1556656793-08538906a9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'">

                                    <?php if ($discount_percentage > 0): ?>
                                        <span class="absolute top-3 left-3 px-2 py-1 text-xs font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-red-500 to-pink-500">
                                            -<?= $discount_percentage ?>%
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($stock_quantity == 0): ?>
                                        <span class="absolute top-3 right-3 px-2 py-1 text-xs font-bold text-white bg-gradient-to-r from-gray-600 to-gray-700 rounded-full shadow-lg">
                                            Out of Stock
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex flex-col flex-1 p-5">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2 py-1 text-xs font-semibold text-purple-600 rounded-full bg-purple-50">
                                            <?= htmlspecialchars($category_name) ?>
                                        </span>
                                        <?php if ($is_featured): ?>
                                            <i class="text-yellow-400 text-sm fas fa-star" title="Featured Product"></i>
                                        <?php endif; ?>
                                    </div>

                                    <h3 class="mb-2 text-base font-bold leading-tight text-gray-900 transition-colors duration-300 line-clamp-2 group-hover:text-purple-600 flex-grow">
                                        <?= htmlspecialchars($product_name) ?>
                                    </h3>

                                    <p class="mb-3 text-sm text-gray-600 line-clamp-2">
                                        <?= htmlspecialchars($short_description) ?>
                                    </p>

                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-2">
                                            <?php if ($discount_price && $discount_price > 0): ?>
                                                <span class="text-lg font-black text-gray-900"><?= formatPrice($discount_price) ?></span>
                                                <span class="text-sm text-gray-500 line-through"><?= formatPrice($product_price) ?></span>
                                            <?php else: ?>
                                                <span class="text-lg font-black text-gray-900"><?= formatPrice($product_price) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full 
                                            <?= $stock_quantity > 10 ? 'bg-green-100 text-green-800' : ($stock_quantity > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                            <?= $stock_quantity ?> in stock
                                        </span>
                                    </div>

                                    <a href="product-detail.php?slug=<?= $product_slug ?>"
                                        class="block w-full py-2.5 text-sm font-semibold text-center text-white transition-all duration-300 shadow-md bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg hover:from-purple-700 hover:to-indigo-700 hover:shadow-lg hover:scale-105 mt-auto">
                                        <i class="mr-1.5 fas fa-eye"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-10 text-center animate-on-scroll">
                    <a href="products.php"
                        class="inline-flex items-center px-6 py-3 text-base font-bold text-purple-600 transition-all duration-300 bg-white border border-purple-200 shadow-lg rounded-lg hover:bg-gray-50 hover:shadow-xl group">
                        <span>View All Products</span>
                        <i class="ml-2 transition-transform duration-300 transform fas fa-arrow-right group-hover:translate-x-1"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="animate-on-scroll">
                    <div class="py-16 text-center border bg-white/10 backdrop-blur-sm rounded-xl border-white/20">
                        <div class="relative inline-block mb-4">
                            <div class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 blur-xl opacity-20"></div>
                            <i class="relative text-5xl text-gray-300 fas fa-box-open"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Featured Products Coming Soon</h3>
                        <p class="max-w-md mx-auto mb-6 text-gray-300">We're preparing something amazing for you!</p>
                        <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                            <a href="products.php" class="px-6 py-2.5 text-sm font-semibold text-purple-600 transition duration-300 bg-white rounded-lg hover:bg-gray-100">
                                Browse All Products
                            </a>
                            <a href="contact.php" class="px-6 py-2.5 text-sm font-semibold text-white transition duration-300 border border-white rounded-lg hover:bg-white/10">
                                Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-16 bg-gradient-to-b from-white to-gray-50">
        <div class="container px-4 mx-auto">
            <div class="mb-12 text-center animate-on-scroll">
                <h2 class="mb-4 text-3xl font-black text-gray-900 md:text-4xl">
                    Why Choose <span class="gradient-text">Wima Store</span>?
                </h2>
                <p class="max-w-2xl mx-auto text-lg text-gray-600">
                    Experience the difference with our commitment to quality, service, and innovation
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="animate-on-scroll" style="animation-delay: 0.1s;">
                    <div class="p-6 text-center transition-all duration-300 bg-white border border-gray-100 shadow-lg rounded-xl hover:shadow-xl hover:border-purple-200">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 shadow-lg rounded-xl bg-gradient-to-br from-green-500 to-emerald-600">
                            <i class="text-xl text-white fas fa-shipping-fast"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-black text-gray-900">Free Shipping</h3>
                        <p class="text-base leading-relaxed text-gray-600">
                            Free express shipping on all orders over RWF 50,000. Fast delivery to your doorstep.
                        </p>
                    </div>
                </div>

                <div class="animate-on-scroll" style="animation-delay: 0.2s;">
                    <div class="p-6 text-center transition-all duration-300 bg-white border border-gray-100 shadow-lg rounded-xl hover:shadow-xl hover:border-blue-200">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 shadow-lg rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600">
                            <i class="text-xl text-white fas fa-shield-alt"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-black text-gray-900">Quality Guarantee</h3>
                        <p class="text-base leading-relaxed text-gray-600">
                            30-day money back guarantee and 2-year warranty on all our premium products.
                        </p>
                    </div>
                </div>

                <div class="animate-on-scroll" style="animation-delay: 0.3s;">
                    <div class="p-6 text-center transition-all duration-300 bg-white border border-gray-100 shadow-lg rounded-xl hover:shadow-xl hover:border-purple-200">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 shadow-lg rounded-xl bg-gradient-to-br from-purple-500 to-pink-600">
                            <i class="text-xl text-white fas fa-headset"></i>
                        </div>
                        <h3 class="mb-3 text-xl font-black text-gray-900">24/7 Support</h3>
                        <p class="text-base leading-relaxed text-gray-600">
                            Round-the-clock customer support with expert technicians ready to assist you.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-16 bg-gradient-to-r from-purple-600 to-indigo-700">
        <div class="container px-4 mx-auto text-center">
            <div class="animate-on-scroll">
                <h2 class="mb-4 text-3xl font-black text-white md:text-4xl">
                    Ready to Experience the Future?
                </h2>
            </div>
            <div class="animate-on-scroll" style="animation-delay: 0.1s;">
                <p class="max-w-2xl mx-auto mb-8 text-lg text-purple-100">
                    Join thousands of satisfied customers who trust Wima Store for their technology needs.
                </p>
            </div>
            <div class="animate-on-scroll" style="animation-delay: 0.2s;">
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="products.php"
                        class="relative px-8 py-3 overflow-hidden text-lg font-bold text-purple-600 transition-all duration-300 bg-white shadow-xl group rounded-xl hover:shadow-2xl hover:scale-105">
                        <div class="relative z-10 flex items-center">
                            <i class="mr-3 fas fa-shopping-cart"></i>Start Shopping Now
                        </div>
                        <div class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-r from-purple-50 to-blue-50 group-hover:opacity-100"></div>
                    </a>
                    <a href="contact.php"
                        class="px-8 py-3 text-lg font-bold text-white transition-all duration-300 border-2 border-white rounded-xl hover:bg-white/10 backdrop-blur-sm">
                        <i class="mr-3 fas fa-envelope"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Improved Intersection Observer with cleaner animation
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

            // Modern Intersection Observer for animations
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        // Optional: unobserve after animation
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -100px 0px'
            });

            // Observe all animated elements
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });

            // Social media bar scroll behavior
            const socialBar = document.getElementById('socialBar');
            let lastScrollTop = 0;
            const socialBarThreshold = 100;
            let isScrolling;

            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                // Show/hide based on scroll direction
                if (scrollTop > lastScrollTop && scrollTop > socialBarThreshold) {
                    socialBar.classList.add('hidden');
                } else {
                    socialBar.classList.remove('hidden');
                }

                lastScrollTop = scrollTop;

                // Clear timeout for continuous scrolling
                clearTimeout(isScrolling);

                // Show bar when scrolling stops
                isScrolling = setTimeout(function() {
                    socialBar.classList.remove('hidden');
                }, 150);
            }, false);

            // Mobile responsiveness for social bar
            function updateSocialBarPosition() {
                if (window.innerWidth <= 480) {
                    socialBar.style.top = 'auto';
                    socialBar.style.bottom = '20px';
                    socialBar.style.transform = 'none';
                    socialBar.style.flexDirection = 'row';
                } else {
                    socialBar.style.top = '50%';
                    socialBar.style.bottom = 'auto';
                    socialBar.style.transform = 'translateY(-50%)';
                    socialBar.style.flexDirection = 'column';
                }
            }

            // Initial call and on resize
            updateSocialBarPosition();
            window.addEventListener('resize', updateSocialBarPosition);

            // Image error handling
            document.querySelectorAll('.product-image').forEach(img => {
                img.addEventListener('error', function() {
                    this.src = 'https://images.unsplash.com/photo-1556656793-08538906a9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                });
            });
        });

        // Preload hero images to prevent grey flash
        window.addEventListener('load', function() {
            const heroImages = [
                'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
                'https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
                'https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
                'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'
            ];

            heroImages.forEach(src => {
                const img = new Image();
                img.src = src;
            });
        });
    </script>
</body>

</html>