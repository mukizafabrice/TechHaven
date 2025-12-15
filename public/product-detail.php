<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isset($_GET['slug'])) {
    header('Location: products.php');
    exit;
}

$product_slug = $_GET['slug'];
$product = getProductBySlug($pdo, $product_slug);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Track product view
trackProductView($pdo, $product['id']);

// Get product images and related products
$product_images = getProductImages($pdo, $product['id']);
$related_products = getProducts($pdo, $product['category_id'], null, 4);

$page_title = $product['name'] . " - " . SITE_NAME;
include '../includes/header.php';

$discount_percentage = calculateDiscountPercentage($product['price'], $product['discount_price']);

// Generate WhatsApp link
$whatsapp_link = getWhatsAppLink($product);
?>

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <!-- Product Detail Section -->
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-8" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center space-x-2 text-sm text-gray-600">
                <li>
                    <a href="index.php" class="transition-all duration-300 hover:text-purple-600 hover:underline">
                        <i class="mr-2 fas fa-home"></i>Home
                    </a>
                </li>
                <li><i class="text-xs text-gray-400 fas fa-chevron-right"></i></li>
                <li>
                    <a href="products.php" class="transition-all duration-300 hover:text-purple-600 hover:underline">
                        <i class="mr-2 fas fa-box"></i>Products
                    </a>
                </li>
                <li><i class="text-xs text-gray-400 fas fa-chevron-right"></i></li>
                <li>
                    <a href="products.php?category=<?= $product['category_slug'] ?>" 
                       class="transition-all duration-300 hover:text-purple-600 hover:underline">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </a>
                </li>
                <li><i class="text-xs text-gray-400 fas fa-chevron-right"></i></li>
                <li class="max-w-xs font-medium text-gray-900 truncate"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>

        <!-- Product Card -->
        <div class="overflow-hidden bg-white border border-gray-100 shadow-xl rounded-2xl">
            <div class="grid grid-cols-1 gap-8 p-6 lg:grid-cols-2 md:p-8">
                <!-- Image Gallery -->
                <div class="space-y-4">
                    <!-- Main Image Container -->
                    <div class="relative overflow-hidden bg-gray-50 rounded-xl group">
                        <div class="w-full aspect-square">
                            <img id="mainImage" 
                                 src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 class="object-contain w-full h-full p-4 transition-transform duration-500 group-hover:scale-105">
                        </div>
                        
                        <!-- Badges -->
                        <div class="absolute space-y-2 top-4 left-4">
                            <?php if ($discount_percentage > 0): ?>
                                <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-red-500 to-pink-500 animate-pulse">
                                    <i class="mr-1.5 fas fa-tag"></i> -<?= $discount_percentage ?>%
                                </span>
                            <?php endif; ?>
                            <?php if ($product['is_featured']): ?>
                                <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-yellow-500 to-orange-500">
                                    <i class="mr-1.5 fas fa-star"></i> Featured
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Stock Status Badge -->
                        <div class="absolute top-4 right-4">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-green-500 to-emerald-600">
                                    <i class="mr-1.5 fas fa-check-circle"></i> In Stock
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-red-500 to-rose-600">
                                    <i class="mr-1.5 fas fa-times-circle"></i> Out of Stock
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Zoom Overlay -->
                        <div class="absolute inset-0 transition-all duration-300 bg-black bg-opacity-0 group-hover:bg-opacity-5 rounded-xl"></div>
                    </div>

                    <!-- Thumbnail Gallery -->
                    <?php if (!empty($product_images)): ?>
                        <div class="grid grid-cols-4 gap-3">
                            <div class="overflow-hidden transition-transform duration-300 transform border-2 border-purple-500 cursor-pointer rounded-xl hover:scale-105 hover:shadow-lg active-thumbnail">
                                <div class="aspect-square">
                                    <img src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         class="object-contain w-full h-full p-2 bg-white"
                                         onclick="changeMainImage(this.src)">
                                </div>
                            </div>
                            <?php foreach ($product_images as $index => $image): ?>
                                <div class="overflow-hidden transition-all duration-300 transform border border-gray-200 cursor-pointer rounded-xl hover:scale-105 hover:border-purple-300 hover:shadow-lg">
                                    <div class="aspect-square">
                                        <img src="../assets/uploads/products/<?= $image['image_url'] ?>"
                                             alt="<?= htmlspecialchars($image['alt_text'] ?? $product['name']) ?>"
                                             class="object-contain w-full h-full p-2 bg-white"
                                             onclick="changeMainImage(this.src)">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="space-y-6">
                    <!-- Category & Brand -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 text-xs font-semibold text-purple-600 uppercase rounded-full bg-purple-50">
                                <?= htmlspecialchars($product['category_name']) ?>
                            </span>
                            <span class="px-3 py-1 text-xs font-semibold text-blue-600 uppercase rounded-full bg-blue-50">
                                <i class="mr-1 fas fa-tag"></i> Wima Store
                            </span>
                        </div>
                        <div class="flex items-center text-amber-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span class="ml-2 text-sm text-gray-600">(24 reviews)</span>
                        </div>
                    </div>

                    <!-- Product Title -->
                    <h1 class="text-3xl font-bold leading-tight text-gray-900 md:text-4xl">
                        <?= htmlspecialchars($product['name']) ?>
                    </h1>

                    <!-- Short Description -->
                    <p class="text-lg leading-relaxed text-gray-600">
                        <?= htmlspecialchars($product['short_description']) ?>
                    </p>

                    <!-- Price Section -->
                    <div class="space-y-2">
                        <div class="flex items-center space-x-4">
                            <?php if ($product['discount_price']): ?>
                                <span class="text-4xl font-black text-gray-900">
                                    <?= formatPrice($product['discount_price']) ?>
                                </span>
                                <span class="text-2xl text-gray-500 line-through">
                                    <?= formatPrice($product['price']) ?>
                                </span>
                                <span class="px-4 py-2 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-red-500 to-pink-500">
                                    Save <?= formatPrice($product['price'] - $product['discount_price']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-4xl font-black text-gray-900">
                                    <?= formatPrice($product['price']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($discount_percentage > 0): ?>
                            <p class="text-sm font-semibold text-green-600">
                                <i class="mr-1 fas fa-bolt"></i> Great deal! You save <?= $discount_percentage ?>% off
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Stock Info -->
                    <div class="p-4 border border-gray-100 bg-gradient-to-r from-gray-50 to-white rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 rounded-lg bg-blue-50">
                                    <i class="text-blue-600 fas fa-box-open"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Available Quantity</p>
                                    <p class="text-lg font-bold text-gray-900">
                                        <?= $product['stock_quantity'] ?> units
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-48 bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-500 h-2.5 rounded-full" 
                                         style="width: <?= min(100, ($product['stock_quantity'] / 100) * 100) ?>%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-600">
                                    <?= ($product['stock_quantity'] > 10) ? 'High Stock' : (($product['stock_quantity'] > 0) ? 'Low Stock' : 'Out of Stock') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-white rounded-lg shadow-sm">
                                    <i class="text-gray-600 fas fa-barcode"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">SKU</p>
                                    <p class="font-semibold text-gray-900"><?= $product['sku'] ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-white rounded-lg shadow-sm">
                                    <i class="text-gray-600 fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Added On</p>
                                    <p class="font-semibold text-gray-900"><?= date('M j, Y', strtotime($product['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-4">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <a href="<?= $whatsapp_link ?>" 
                                   target="_blank"
                                   class="relative px-6 py-4 overflow-hidden font-bold text-center text-white transition-all duration-300 transform shadow-lg group bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl hover:shadow-2xl hover:scale-105">
                                    <div class="relative z-10 flex items-center justify-center">
                                        <i class="mr-3 text-xl fab fa-whatsapp"></i>
                                        <span>Contact via WhatsApp</span>
                                    </div>
                                    <div class="absolute inset-0 transition-opacity duration-300 opacity-0 bg-gradient-to-r from-emerald-600 to-green-500 group-hover:opacity-100"></div>
                                </a>
                                
                                <button onclick="addToWishlist('<?= $product['id'] ?>')"
                                        class="relative px-6 py-4 overflow-hidden font-bold text-purple-600 transition-all duration-300 transform border border-purple-200 shadow-lg group bg-gradient-to-r from-purple-50 to-white rounded-xl hover:shadow-2xl hover:scale-105">
                                    <div class="relative z-10 flex items-center justify-center">
                                        <i class="mr-3 text-xl far fa-heart group-hover:text-red-500"></i>
                                        <span>Add to Wishlist</span>
                                    </div>
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="p-6 text-center border border-gray-200 bg-gradient-to-r from-gray-50 to-white rounded-2xl">
                                <i class="mb-3 text-3xl text-gray-400 fas fa-bell"></i>
                                <p class="mb-2 font-medium text-gray-700">This product is currently unavailable</p>
                                <p class="mb-4 text-sm text-gray-500">Get notified when it's back in stock</p>
                                <button class="px-6 py-2 text-white transition duration-300 bg-gray-800 rounded-lg hover:bg-gray-900">
                                    Notify Me
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- Share & Print -->
                        <div class="flex pt-4 space-x-4">
                            <button onclick="shareProduct()"
                                    class="relative flex-1 px-4 py-3 overflow-hidden font-semibold text-gray-700 transition-all duration-300 transform bg-white border border-gray-200 shadow-sm group rounded-xl hover:shadow-lg hover:scale-105">
                                <div class="relative z-10 flex items-center justify-center">
                                    <i class="mr-3 text-gray-500 fas fa-share-alt group-hover:text-purple-600"></i>
                                    <span>Share Product</span>
                                </div>
                            </button>
                            
                            <button onclick="window.print()"
                                    class="relative flex-1 px-4 py-3 overflow-hidden font-semibold text-gray-700 transition-all duration-300 transform bg-white border border-gray-200 shadow-sm group rounded-xl hover:shadow-lg hover:scale-105">
                                <div class="relative z-10 flex items-center justify-center">
                                    <i class="mr-3 text-gray-500 fas fa-print group-hover:text-blue-600"></i>
                                    <span>Print Details</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="mt-8 border-t border-gray-100">
                <div class="px-8">
                    <nav class="flex space-x-8 border-b border-gray-100" aria-label="Tabs">
                        <button id="description-tab" 
                                class="flex items-center px-1 py-4 space-x-2 text-sm font-medium text-purple-600 border-b-2 border-purple-500 tab-button">
                            <i class="fas fa-align-left"></i>
                            <span>Description</span>
                        </button>
                        <button id="specs-tab" 
                                class="flex items-center px-1 py-4 space-x-2 text-sm font-medium text-gray-500 border-b-2 border-transparent tab-button hover:text-gray-700">
                            <i class="fas fa-list-alt"></i>
                            <span>Specifications</span>
                        </button>
                        <button id="reviews-tab" 
                                class="flex items-center px-1 py-4 space-x-2 text-sm font-medium text-gray-500 border-b-2 border-transparent tab-button hover:text-gray-700">
                            <i class="fas fa-star"></i>
                            <span>Reviews</span>
                        </button>
                    </nav>
                </div>

                <div class="px-8 py-8">
                    <!-- Description Tab -->
                    <div id="description-content" class="tab-content animate-fade-in">
                        <div class="prose max-w-none">
                            <div class="p-8 border border-gray-100 bg-gradient-to-br from-gray-50 to-white rounded-2xl">
                                <h3 class="mb-6 text-2xl font-bold text-gray-900">Product Description</h3>
                                <div class="space-y-4 leading-relaxed text-gray-700">
                                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Specifications Tab -->
                    <div id="specs-content" class="hidden tab-content animate-fade-in">
                        <div class="p-8 border border-gray-100 bg-gradient-to-br from-gray-50 to-white rounded-2xl">
                            <h3 class="mb-6 text-2xl font-bold text-gray-900">Technical Specifications</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-4">
                                    <div class="flex justify-between px-4 py-3 bg-white shadow-sm rounded-xl">
                                        <span class="font-medium text-gray-600">Brand</span>
                                        <span class="font-semibold text-gray-900">Wima Store</span>
                                    </div>
                                    <div class="flex justify-between px-4 py-3 bg-white shadow-sm rounded-xl">
                                        <span class="font-medium text-gray-600">Warranty</span>
                                        <span class="font-semibold text-gray-900">1 Year</span>
                                    </div>
                                    <div class="flex justify-between px-4 py-3 bg-white shadow-sm rounded-xl">
                                        <span class="font-medium text-gray-600">SKU</span>
                                        <span class="font-semibold text-gray-900"><?= $product['sku'] ?></span>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex justify-between px-4 py-3 bg-white shadow-sm rounded-xl">
                                        <span class="font-medium text-gray-600">Category</span>
                                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($product['category_name']) ?></span>
                                    </div>
                                    <div class="flex justify-between px-4 py-3 bg-white shadow-sm rounded-xl">
                                        <span class="font-medium text-gray-600">Added Date</span>
                                        <span class="font-semibold text-gray-900"><?= date('M j, Y', strtotime($product['created_at'])) ?></span>
                                    </div>
                                    <div class="flex justify-between px-4 py-3 bg-white shadow-sm rounded-xl">
                                        <span class="font-medium text-gray-600">Stock Status</span>
                                        <span class="font-semibold <?= $product['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                                            <?= $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div id="reviews-content" class="hidden tab-content animate-fade-in">
                        <div class="p-8 border border-gray-100 bg-gradient-to-br from-gray-50 to-white rounded-2xl">
                            <h3 class="mb-6 text-2xl font-bold text-gray-900">Customer Reviews</h3>
                            <div class="py-12 text-center">
                                <i class="mb-4 text-5xl text-gray-300 fas fa-comments"></i>
                                <p class="mb-4 text-gray-600">No reviews yet for this product</p>
                                <button class="px-6 py-2 text-white transition duration-300 bg-purple-600 rounded-lg hover:bg-purple-700">
                                    Be the first to review
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <section class="mt-16">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">Related Products</h2>
                        <p class="mt-2 text-gray-600">Discover similar products you might like</p>
                    </div>
                    <a href="products.php?category=<?= $product['category_slug'] ?>" 
                       class="flex items-center font-semibold text-purple-600 transition duration-300 hover:text-purple-700">
                        View All <i class="ml-2 fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <?php foreach ($related_products as $related_product):
                        if ($related_product['id'] == $product['id']) continue;
                        $related_discount = calculateDiscountPercentage($related_product['price'], $related_product['discount_price']);
                        $related_stock_class = $related_product['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600';
                    ?>
                        <div class="overflow-hidden transition-all duration-500 transform bg-white border border-gray-100 shadow-lg group rounded-2xl hover:shadow-2xl hover:-translate-y-2">
                            <div class="relative overflow-hidden">
                                <div class="aspect-square bg-gray-50">
                                    <img src="../assets/uploads/products/<?= $related_product['featured_image'] ?>"
                                         alt="<?= htmlspecialchars($related_product['name']) ?>"
                                         class="object-contain w-full h-full p-4 transition-transform duration-700 transform group-hover:scale-110">
                                </div>
                                
                                <?php if ($related_discount > 0): ?>
                                    <div class="absolute top-4 left-4">
                                        <span class="px-3 py-1 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-red-500 to-pink-500">
                                            -<?= $related_discount ?>%
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="absolute top-4 right-4">
                                    <span class="px-2 py-1 text-xs font-bold text-white bg-gray-900 rounded-full bg-opacity-70">
                                        <i class="mr-1 fas fa-box"></i> <?= $related_product['stock_quantity'] ?>
                                    </span>
                                </div>
                                
                                <div class="absolute inset-0 transition-opacity duration-500 opacity-0 bg-gradient-to-t from-black via-transparent to-transparent group-hover:opacity-40"></div>
                            </div>
                            
                            <div class="p-5">
                                <div class="mb-3">
                                    <span class="px-2 py-1 text-xs font-semibold text-blue-600 rounded-full bg-blue-50">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </span>
                                </div>
                                
                                <h3 class="mb-2 text-lg font-bold text-gray-900 transition-colors duration-300 line-clamp-2 group-hover:text-purple-600">
                                    <?= htmlspecialchars($related_product['name']) ?>
                                </h3>
                                
                                <p class="mb-3 text-sm text-gray-600 line-clamp-2">
                                    <?= htmlspecialchars($related_product['short_description'] ?? '') ?>
                                </p>
                                
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($related_product['discount_price']): ?>
                                            <span class="text-xl font-black text-gray-900"><?= formatPrice($related_product['discount_price']) ?></span>
                                            <span class="text-sm text-gray-500 line-through"><?= formatPrice($related_product['price']) ?></span>
                                        <?php else: ?>
                                            <span class="text-xl font-black text-gray-900"><?= formatPrice($related_product['price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $related_stock_class ?> bg-opacity-10">
                                        <?= $related_product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                    </span>
                                </div>
                                
                                <a href="product-detail.php?slug=<?= $related_product['slug'] ?>"
                                   class="block w-full py-3 font-semibold text-center text-white transition-all duration-300 transform shadow-lg bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl hover:from-purple-700 hover:to-indigo-700 group-hover:scale-105 hover:shadow-xl">
                                    <i class="mr-2 fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<script>
    // Main image changer
    function changeMainImage(src) {
        const mainImage = document.getElementById('mainImage');
        mainImage.classList.add('opacity-0');
        
        setTimeout(() => {
            mainImage.src = src;
            mainImage.classList.remove('opacity-0');
        }, 150);
        
        // Update active thumbnail
        document.querySelectorAll('.active-thumbnail').forEach(el => {
            el.classList.remove('active-thumbnail', 'border-purple-500');
            el.classList.add('border-gray-200');
        });
        event.currentTarget.parentElement.classList.add('active-thumbnail', 'border-purple-500');
        event.currentTarget.parentElement.classList.remove('border-gray-200');
    }

    // Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = {
            description: {
                tab: document.getElementById('description-tab'),
                content: document.getElementById('description-content')
            },
            specs: {
                tab: document.getElementById('specs-tab'),
                content: document.getElementById('specs-content')
            },
            reviews: {
                tab: document.getElementById('reviews-tab'),
                content: document.getElementById('reviews-content')
            }
        };

        function activateTab(activeTab) {
            // Deactivate all tabs
            Object.values(tabs).forEach(({tab, content}) => {
                tab.classList.remove('border-purple-500', 'text-purple-600');
                tab.classList.add('border-transparent', 'text-gray-500');
                content.classList.add('hidden');
            });

            // Activate selected tab
            tabs[activeTab].tab.classList.add('border-purple-500', 'text-purple-600');
            tabs[activeTab].tab.classList.remove('border-transparent', 'text-gray-500');
            tabs[activeTab].content.classList.remove('hidden');
        }

        // Add click events
        tabs.description.tab.addEventListener('click', () => activateTab('description'));
        tabs.specs.tab.addEventListener('click', () => activateTab('specs'));
        tabs.reviews.tab.addEventListener('click', () => activateTab('reviews'));

        // Initialize with description tab active
        activateTab('description');
    });

    // Share product function
    function shareProduct() {
        if (navigator.share) {
            navigator.share({
                title: '<?= addslashes($product['name']) ?>',
                text: 'Check out this amazing product from Wima Store',
                url: window.location.href
            });
        } else {
            // Fallback with modern UI
            navigator.clipboard.writeText(window.location.href).then(() => {
                // Create a toast notification
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 z-50';
                toast.innerHTML = `
                    <div class="flex items-center">
                        <i class="mr-3 fas fa-check-circle"></i>
                        <span>Link copied to clipboard!</span>
                    </div>
                `;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            });
        }
    }

    // Wishlist function (placeholder)
    function addToWishlist(productId) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-purple-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 z-50';
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="mr-3 fas fa-heart"></i>
                <span>Added to wishlist!</span>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
        
        // Here you would typically make an AJAX call to save to wishlist
        console.log('Added product', productId, 'to wishlist');
    }

    // Add smooth scroll for anchor links
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
</script>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }

    .tab-content {
        animation: fadeIn 0.3s ease-out;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .aspect-square {
        aspect-ratio: 1 / 1;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Selection color */
    ::selection {
        background-color: rgba(147, 51, 234, 0.2);
        color: #7c3aed;
    }

    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
        
        body {
            background: white !important;
            color: black !important;
        }
        
        a {
            color: black !important;
            text-decoration: underline !important;
        }
    }

    /* Image hover effects */
    .group:hover .group-hover\:scale-105 {
        transform: scale(1.05);
    }

    .transition-transform {
        transition-property: transform;
    }

    /* Gradient text */
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

<?php include '../includes/footer.php'; ?>