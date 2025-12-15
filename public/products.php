<?php
$page_title = "All Products - Browse Our Collection";
include '../includes/header.php';

// Get filter parameters
$category_slug = trim($_GET['category'] ?? '');
$search_query = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$sort_by = $_GET['sort'] ?? 'newest';

// Sort options
$sort_sql = match ($sort_by) {
    'price_asc' => "p.price ASC",
    'price_desc' => "p.price DESC",
    'name_asc' => "p.name ASC",
    'name_desc' => "p.name DESC",
    'featured' => "p.is_featured DESC, p.created_at DESC",
    default => "p.created_at DESC"
};

// Get current category
$current_category = null;
if ($category_slug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = TRUE");
    $stmt->execute([$category_slug]);
    $current_category = $stmt->fetch();
}

// SIMPLE WORKING QUERY (from fresh_products.php)
try {
    // Build query exactly like fresh_products.php
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = TRUE";
    $params = [];

    if ($current_category) {
        $sql .= " AND p.category_id = ?";
        $params[] = $current_category['id'];
    }

    if ($search_query) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $search_term = "%$search_query%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $sql .= " ORDER BY $sort_sql";

    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $all_products = $stmt->fetchAll();

    // Simple PHP pagination
    $limit = 12;
    $total_products = count($all_products);
    $total_pages = ceil($total_products / $limit);
    $offset = ($page - 1) * $limit;
    $products = array_slice($all_products, $offset, $limit);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $products = [];
    $total_products = 0;
    $total_pages = 1;
}

// Get categories
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.is_active = TRUE
    WHERE c.is_active = TRUE 
    GROUP BY c.id 
    ORDER BY c.name ASC
")->fetchAll();

// Pagination URL
$pagination_base = "products.php?" . http_build_query(array_filter([
    'category' => $category_slug,
    'q' => $search_query,
    'sort' => $sort_by !== 'newest' ? $sort_by : null
]));
?>

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="mb-10 text-center">
            <div class="inline-flex items-center px-6 py-3 mb-6 border border-purple-100 bg-gradient-to-r from-purple-50 to-blue-50 rounded-2xl">
                <i class="mr-3 text-2xl text-purple-600 fas fa-boxes"></i>
                <span class="font-bold text-gray-800">Premium Electronics Collection</span>
            </div>

            <h1 class="mb-6 text-5xl font-black text-gray-900">
                <?php if ($current_category): ?>
                    <span class="text-gray-800">Explore</span>
                    <span class="text-transparent bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text"><?= htmlspecialchars($current_category['name']) ?></span>
                <?php elseif ($search_query): ?>
                    Search Results: "<span class="text-purple-600"><?= htmlspecialchars($search_query) ?></span>"
                <?php else: ?>
                    Our <span class="text-transparent bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text">Premium</span> Collection
                <?php endif; ?>
            </h1>

            <p class="max-w-3xl mx-auto text-xl leading-relaxed text-gray-600">
                Discover cutting-edge technology and premium electronics tailored for modern lifestyles.
                <?= $total_products ?> products available.
            </p>
        </div>

        <div class="flex flex-col gap-8 lg:flex-row">
            <!-- Sidebar Filter -->
            <aside class="flex-shrink-0 lg:w-80">
                <div class="sticky p-6 bg-white border border-gray-100 shadow-xl rounded-2xl top-24">
                    <!-- Categories Filter -->
                    <div class="mb-10">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Categories</h3>
                            <i class="text-purple-600 fas fa-filter"></i>
                        </div>
                        <div class="space-y-3">
                            <a href="products.php"
                                class="flex items-center justify-between p-4 transition-all duration-300 rounded-xl <?= !$current_category ? 'bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-200 shadow-sm' : 'hover:bg-gray-50 border border-gray-100' ?>">
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-lg bg-gradient-to-br from-purple-500 to-blue-500">
                                        <i class="text-white fas fa-th-large"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-900">All Products</span>
                                        <p class="text-sm text-gray-500">Complete collection</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 text-sm font-bold text-purple-600 bg-purple-100 rounded-full"><?= $total_products ?></span>
                            </a>

                            <?php foreach ($categories as $category): ?>
                                <a href="products.php?category=<?= $category['slug'] ?>"
                                    class="flex items-center justify-between p-4 transition-all duration-300 rounded-xl <?= $current_category && $current_category['id'] == $category['id'] ? 'bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-200 shadow-sm' : 'hover:bg-gray-50 border border-gray-100' ?>">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-10 h-10 mr-4 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500">
                                            <i class="text-white fas fa-<?= $category['name'] === 'Smartphones' ? 'mobile-alt' : ($category['name'] === 'Laptops & PCs' ? 'laptop' : ($category['name'] === 'Cameras' ? 'camera' : 'headphones')) ?>"></i>
                                        </div>
                                        <div>
                                            <span class="font-bold text-gray-900"><?= htmlspecialchars($category['name']) ?></span>
                                            <p class="text-sm text-gray-500">Latest collection</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 text-sm font-bold text-blue-600 bg-blue-100 rounded-full"><?= $category['product_count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Search Box -->
                    <div class="mb-10">
                        <h3 class="flex items-center mb-6 text-xl font-bold text-gray-900">
                            <i class="mr-3 text-purple-600 fas fa-search"></i>Search Products
                        </h3>
                        <form action="products.php" method="GET" class="space-y-4">
                            <?php if ($current_category): ?>
                                <input type="hidden" name="category" value="<?= $current_category['slug'] ?>">
                            <?php endif; ?>
                            <div class="relative group">
                                <input type="text"
                                    name="q"
                                    value="<?= htmlspecialchars($search_query) ?>"
                                    placeholder="What are you looking for?"
                                    class="w-full py-4 pl-12 pr-4 text-gray-700 transition-all duration-300 border-2 border-gray-200 bg-gray-50 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 group-hover:border-purple-300">
                                <i class="absolute text-gray-400 transform -translate-y-1/2 fas fa-search left-4 top-1/2 group-hover:text-purple-500"></i>
                                <?php if ($search_query): ?>
                                    <a href="<?= $current_category ? 'products.php?category=' . $current_category['slug'] : 'products.php' ?>"
                                        class="absolute text-gray-400 transition duration-300 transform -translate-y-1/2 right-4 top-1/2 hover:text-red-500">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <button type="submit"
                                class="w-full py-4 font-bold text-white transition-all duration-300 transform bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl hover:from-purple-700 hover:to-blue-700 hover:shadow-xl hover:scale-105">
                                <i class="mr-3 fas fa-search"></i>Search Now
                            </button>
                        </form>
                    </div>

                    <!-- Quick Stats -->
                    <div class="p-6 border border-gray-200 bg-gradient-to-br from-gray-50 to-white rounded-2xl">
                        <h3 class="mb-4 text-lg font-bold text-gray-900">Collection Stats</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total Products</span>
                                <span class="font-bold text-gray-900"><?= $total_products ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Active Categories</span>
                                <span class="font-bold text-gray-900"><?= count($categories) ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">In Stock</span>
                                <span class="font-bold text-green-600"><?= count(array_filter($all_products, fn($p) => $p['stock_quantity'] > 0)) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1">
                <!-- Results Header -->
                <div class="p-6 mb-8 bg-white border border-gray-100 shadow-lg rounded-2xl">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl">
                                <i class="text-2xl text-purple-600 fas fa-cube"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Products Found</h2>
                                <p class="text-gray-600">
                                    <?= $total_products ?> items
                                    <?php if ($search_query): ?>
                                        matching "<span class="font-semibold text-purple-600"><?= htmlspecialchars($search_query) ?></span>"
                                    <?php endif; ?>
                                    <?php if ($total_pages > 1): ?>
                                        <span class="text-gray-500">• Page <?= $page ?> of <?= $total_pages ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <span class="font-semibold text-gray-700">Sort by:</span>
                            <form method="GET" class="relative">
                                <?php if ($category_slug): ?>
                                    <input type="hidden" name="category" value="<?= $category_slug ?>">
                                <?php endif; ?>
                                <?php if ($search_query): ?>
                                    <input type="hidden" name="q" value="<?= htmlspecialchars($search_query) ?>">
                                <?php endif; ?>
                                <select name="sort"
                                    onchange="this.form.submit()"
                                    class="px-5 py-3 pr-10 font-medium text-gray-700 transition duration-300 bg-white border-2 border-gray-200 appearance-none cursor-pointer rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 hover:border-purple-300">
                                    <option value="newest" <?= $sort_by === 'newest' ? 'selected' : '' ?>>🆕 Newest First</option>
                                    <option value="price_asc" <?= $sort_by === 'price_asc' ? 'selected' : '' ?>>💰 Price: Low to High</option>
                                    <option value="price_desc" <?= $sort_by === 'price_desc' ? 'selected' : '' ?>>💰 Price: High to Low</option>
                                    <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>🔤 Name: A to Z</option>
                                    <option value="featured" <?= $sort_by === 'featured' ? 'selected' : '' ?>>⭐ Featured First</option>
                                </select>
                                <div class="absolute transform -translate-y-1/2 pointer-events-none right-3 top-1/2">
                                    <i class="text-gray-400 fas fa-chevron-down"></i>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (!empty($products)): ?>
                    <div class="grid grid-cols-1 gap-8 mb-12 sm:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($products as $product):
                            $discount_percentage = calculateDiscountPercentage($product['price'], $product['discount_price']);
                            $stock_status = $product['stock_quantity'] > 10 ? 'high' : ($product['stock_quantity'] > 0 ? 'low' : 'out');

                            // Image handling
                            $image_src = '../assets/uploads/products/' . ($product['featured_image'] ?? '');
                            $image_exists = !empty($product['featured_image']) && file_exists($image_src);
                            if (!$image_exists) {
                                $image_src = 'https://images.unsplash.com/photo-1556656793-08538906a9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                            }
                        ?>
                            <div class="relative overflow-hidden transition-all duration-500 bg-white border border-gray-100 shadow-lg group rounded-2xl hover:shadow-2xl hover:-translate-y-2">
                                <!-- Image Container -->
                                <div class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-white">
                                    <div class="overflow-hidden aspect-square">
                                        <img src="<?= $image_src ?>"
                                            alt="<?= htmlspecialchars($product['name']) ?>"
                                            class="object-contain w-full h-full p-6 transition-all duration-700 group-hover:scale-110 group-hover:p-4"
                                            loading="lazy"
                                            onerror="this.src='https://images.unsplash.com/photo-1556656793-08538906a9f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'">
                                    </div>

                                    <!-- Badges -->
                                    <div class="absolute space-y-2 top-4 left-4">
                                        <?php if ($discount_percentage > 0): ?>
                                            <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-red-500 to-pink-500 animate-pulse">
                                                <i class="mr-1.5 fas fa-tag"></i> -<?= $discount_percentage ?>% OFF
                                            </span>
                                        <?php endif; ?>
                                        <!-- <?php if ($product['is_featured']): ?>
                                            <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-yellow-500 to-orange-500">
                                                <i class="mr-1.5 fas fa-star"></i> Featured
                                            </span>
                                        <?php endif; ?> -->
                                    </div>

                                    <!-- Stock Badge -->
                                    <div class="absolute top-4 right-4">
                                        <?php if ($stock_status === 'high'): ?>
                                            <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-green-500 to-emerald-600">
                                                <i class="mr-1.5 fas fa-check-circle"></i> In Stock
                                            </span>
                                        <?php elseif ($stock_status === 'low'): ?>
                                            <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-yellow-500 to-amber-600">
                                                <i class="mr-1.5 fas fa-exclamation-circle"></i> Low Stock
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white rounded-full shadow-lg bg-gradient-to-r from-red-500 to-rose-600">
                                                <i class="mr-1.5 fas fa-times-circle"></i> Out of Stock
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Quick View Overlay -->
                                    <div class="absolute inset-0 flex items-end justify-center p-6 transition-opacity duration-500 opacity-0 bg-gradient-to-t from-black/60 via-transparent to-transparent group-hover:opacity-100">
                                        <a href="product-detail.php?slug=<?= $product['slug'] ?>"
                                            class="w-full transition-transform duration-500 transform translate-y-4 group-hover:translate-y-0">
                                            <button class="w-full py-3 font-bold text-white transition-all duration-300 shadow-xl bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl hover:from-purple-700 hover:to-blue-700">
                                                <i class="mr-2 fas fa-eye"></i> Quick View
                                            </button>
                                        </a>
                                    </div>
                                </div>

                                <!-- Product Details -->
                                <div class="p-6">
                                    <!-- Category -->
                                    <div class="mb-3">
                                        <a href="products.php?category=<?= $product['category_slug'] ?>"
                                            class="inline-flex items-center px-3 py-1 text-sm font-semibold text-blue-600 transition duration-300 rounded-lg bg-blue-50 hover:bg-blue-100">
                                            <i class="mr-2 fas fa-tag"></i>
                                            <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                        </a>
                                    </div>

                                    <!-- Product Name -->
                                    <h3 class="mb-3 text-xl font-bold leading-tight text-gray-900 transition-colors duration-300 group-hover:text-purple-600 line-clamp-2">
                                        <a href="product-detail.php?slug=<?= $product['slug'] ?>" class="hover:underline">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </a>
                                    </h3>

                                    <!-- Short Description -->
                                    <p class="mb-4 text-gray-600 line-clamp-2">
                                        <?= htmlspecialchars($product['short_description'] ?? $product['description'] ?? 'Premium quality product from Wima Store') ?>
                                    </p>

                                    <!-- Price & Stock -->
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="space-y-1">
                                            <div class="flex items-center space-x-3">
                                                <?php if ($product['discount_price']): ?>
                                                    <span class="text-2xl font-black text-gray-900"><?= formatPrice($product['discount_price']) ?></span>
                                                    <span class="text-sm text-gray-500 line-through"><?= formatPrice($product['price']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-2xl font-black text-gray-900"><?= formatPrice($product['price']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($discount_percentage > 0): ?>
                                                <p class="text-sm font-semibold text-green-600">
                                                    <i class="mr-1 fas fa-bolt"></i> Save <?= $discount_percentage ?>%
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="text-right">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-24 h-2 bg-gray-200 rounded-full">
                                                    <div class="h-2 rounded-full <?= $stock_status === 'high' ? 'bg-green-500' : ($stock_status === 'low' ? 'bg-yellow-500' : 'bg-red-500') ?>"
                                                        style="width: <?= min(100, ($product['stock_quantity'] / 50) * 100) ?>%"></div>
                                                </div>
                                                <span class="text-xs font-semibold <?= $stock_status === 'high' ? 'text-green-600' : ($stock_status === 'low' ? 'text-yellow-600' : 'text-red-600') ?>">
                                                    <?= $product['stock_quantity'] ?>
                                                </span>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">available</p>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex space-x-3">
                                        <a href="product-detail.php?slug=<?= $product['slug'] ?>"
                                            class="flex-1 py-3 font-bold text-center text-white transition-all duration-300 transform bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl hover:from-purple-700 hover:to-blue-700 hover:shadow-xl hover:scale-105">
                                            <i class="mr-2 fas fa-shopping-cart"></i> View Details
                                        </a>
                                        <button onclick="addToWishlist(<?= $product['id'] ?>)"
                                            class="p-3 text-gray-500 transition-all duration-300 border border-gray-200 rounded-xl hover:border-red-300 hover:bg-red-50 hover:text-red-500">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="flex items-center justify-center mb-16 space-x-3">
                            <?php if ($page > 1): ?>
                                <a href="<?= $pagination_base ?>&page=<?= $page - 1 ?>"
                                    class="flex items-center px-5 py-3 font-semibold text-gray-700 transition-all duration-300 border-2 border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 hover:text-purple-700">
                                    <i class="mr-2 fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>

                            <div class="flex items-center space-x-2">
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);

                                for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <a href="<?= $pagination_base ?>&page=<?= $i ?>"
                                        class="flex items-center justify-center w-12 h-12 font-semibold transition-all duration-300 border-2 rounded-xl <?= $i == $page ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-700 hover:border-purple-300 hover:bg-purple-50' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>

                            <?php if ($page < $total_pages): ?>
                                <a href="<?= $pagination_base ?>&page=<?= $page + 1 ?>"
                                    class="flex items-center px-5 py-3 font-semibold text-gray-700 transition-all duration-300 border-2 border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 hover:text-purple-700">
                                    Next <i class="ml-2 fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- No Products Found -->
                    <div class="py-20 text-center border-2 border-gray-100 shadow-lg bg-gradient-to-br from-white to-gray-50 rounded-2xl">
                        <div class="relative inline-block mb-6">
                            <div class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 blur-xl opacity-20"></div>
                            <i class="relative text-gray-300 text-7xl fas fa-search"></i>
                        </div>
                        <h3 class="mb-4 text-3xl font-bold text-gray-800">No Products Found</h3>
                        <p class="max-w-md mx-auto mb-8 text-lg text-gray-600">
                            <?php if ($search_query): ?>
                                We couldn't find any products matching "<?= htmlspecialchars($search_query) ?>"
                            <?php else: ?>
                                No products are currently available in this category
                            <?php endif; ?>
                        </p>
                        <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                            <a href="products.php"
                                class="px-8 py-4 font-bold text-white transition-all duration-300 transform bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl hover:from-purple-700 hover:to-blue-700 hover:shadow-xl hover:scale-105">
                                <i class="mr-3 fas fa-th-large"></i> Browse All Products
                            </a>
                            <a href="index.php"
                                class="px-8 py-4 font-bold text-gray-700 transition-all duration-300 bg-gray-100 border-2 border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50">
                                <i class="mr-3 fas fa-home"></i> Return Home
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<script>
    // Wishlist functionality
    function addToWishlist(productId) {
        // Create a toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 z-50 px-6 py-4 text-white bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-2xl transform transition-all duration-300 animate-slide-in';
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="mr-3 text-xl fas fa-heart"></i>
                <div>
                    <p class="font-bold">Added to wishlist!</p>
                    <p class="text-sm opacity-90">Product saved for later</p>
                </div>
            </div>
        `;
        document.body.appendChild(toast);

        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);

        // Here you would typically make an AJAX call to save to wishlist
        console.log('Added product', productId, 'to wishlist');
    }

    // Image lazy loading enhancement
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('img[loading="lazy"]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    });

    // Filter form submission enhancement
    document.querySelectorAll('select[name="sort"]').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>

<style>
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .animate-slide-in {
        animation: slideIn 0.3s ease-out;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Aspect ratio for image containers */
    .aspect-square {
        aspect-ratio: 1 / 1;
    }

    /* Custom scrollbar for select */
    select {
        scrollbar-width: thin;
        scrollbar-color: #a78bfa #f3f4f6;
    }

    select::-webkit-scrollbar {
        width: 8px;
    }

    select::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 4px;
    }

    select::-webkit-scrollbar-thumb {
        background: #a78bfa;
        border-radius: 4px;
    }

    select::-webkit-scrollbar-thumb:hover {
        background: #8b5cf6;
    }

    /* Smooth transitions */
    * {
        transition-property: color, background-color, border-color, transform, opacity;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }

    /* Gradient text */
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Image hover effects */
    .group:hover img {
        transform: scale(1.1);
    }

    /* Custom selection color */
    ::selection {
        background-color: rgba(147, 51, 234, 0.2);
        color: #7c3aed;
    }

    /* Focus styles */
    :focus {
        outline: 2px solid #8b5cf6;
        outline-offset: 2px;
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
</style>

<?php include '../includes/footer.php'; ?>