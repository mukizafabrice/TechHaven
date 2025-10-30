<?php
$page_title = "Home - Discover Latest Electronics";
include '../includes/header.php';

// Get featured products and categories
$featured_products = getProducts($pdo, null, null, 8, true);
$categories = getCategories($pdo);
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 to-purple-700 text-white py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Welcome to TechHaven</h1>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Your ultimate destination for cutting-edge electronics, gadgets, and tech accessories at unbeatable prices.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="products.php" class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition duration-300 text-lg">
                Shop Now <i class="fas fa-arrow-right ml-2"></i>
            </a>
            <a href="#featured" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300 text-lg">
                Explore Products
            </a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Shop by Category</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php foreach ($categories as $category): ?>
                <a href="products.php?category=<?= $category['slug'] ?>" class="group">
                    <div class="bg-gray-50 rounded-lg p-6 text-center hover:shadow-lg transition duration-300 border border-gray-200 group-hover:border-blue-300">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 transition duration-300">
                            <i class="fas fa-mobile-alt text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-lg text-gray-800 group-hover:text-blue-600 transition duration-300"><?= htmlspecialchars($category['name']) ?></h3>
                        <p class="text-gray-600 text-sm mt-2">Explore products</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section id="featured" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
            <h2 class="text-3xl font-bold">Featured Products</h2>
            <a href="products.php" class="text-blue-600 hover:text-blue-700 font-semibold">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if (!empty($featured_products)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($featured_products as $product):
                    $discount_percentage = calculateDiscountPercentage($product['price'], $product['discount_price']);
                ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300 product-card">
                        <div class="relative">
                            <img src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                                class="w-full h-48 object-cover">
                            <?php if ($discount_percentage > 0): ?>
                                <span class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                                    -<?= $discount_percentage ?>%
                                </span>
                            <?php endif; ?>
                            <?php if ($product['stock_quantity'] == 0): ?>
                                <span class="absolute top-3 right-3 bg-gray-500 text-white px-2 py-1 rounded text-sm font-semibold">
                                    Out of Stock
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <span class="text-sm text-gray-500 uppercase"><?= htmlspecialchars($product['category_name']) ?></span>
                            <h3 class="font-semibold text-lg mb-2 h-12 overflow-hidden"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="text-gray-600 text-sm mb-4 h-12 overflow-hidden"><?= htmlspecialchars($product['short_description']) ?></p>

                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <?php if ($product['discount_price']): ?>
                                        <span class="text-lg font-bold text-blue-600"><?= formatPrice($product['discount_price']) ?></span>
                                        <span class="text-sm text-gray-500 line-through"><?= formatPrice($product['price']) ?></span>
                                    <?php else: ?>
                                        <span class="text-lg font-bold text-blue-600"><?= formatPrice($product['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-sm text-gray-500">Stock: <?= $product['stock_quantity'] ?></span>
                            </div>

                            <a href="product-detail.php?slug=<?= $product['slug'] ?>"
                                class="block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700 transition duration-300">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600">No featured products available</h3>
                <p class="text-gray-500 mt-2">Check back later for new arrivals!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Why Choose TechHaven?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shipping-fast text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Free Shipping</h3>
                <p class="text-gray-600">Free shipping on all orders over $50</p>
            </div>
            <div class="text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Quality Guarantee</h3>
                <p class="text-gray-600">30-day money back guarantee on all products</p>
            </div>
            <div class="text-center">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">24/7 Support</h3>
                <p class="text-gray-600">Round-the-clock customer support</p>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>