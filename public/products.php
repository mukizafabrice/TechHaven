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

<div class="container px-4 py-8 mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li><a href="index.php" class="transition duration-300 hover:text-blue-600"><i class="mr-1 fas fa-home"></i>Home</a></li>
            <li><i class="mx-2 text-gray-400 fas fa-chevron-right"></i></li>
            <li><a href="products.php" class="transition duration-300 hover:text-blue-600">Products</a></li>
            <?php if ($current_category): ?>
                <li><i class="mx-2 text-gray-400 fas fa-chevron-right"></i></li>
                <li class="font-semibold text-blue-600"><?= htmlspecialchars($current_category['name']) ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-8 text-center">
        <h1 class="mb-4 text-4xl font-bold text-gray-900">
            <?php if ($current_category): ?>
                <?= htmlspecialchars($current_category['name']) ?>
            <?php elseif ($search_query): ?>
                Search Results for "<?= htmlspecialchars($search_query) ?>"
            <?php else: ?>
                Our Product Collection
            <?php endif; ?>
        </h1>
        <p class="max-w-3xl mx-auto text-xl text-gray-600">
            Discover premium electronics and cutting-edge technology for every aspect of your digital life.
        </p>
    </div>

    <div class="flex flex-col gap-8 lg:flex-row">
        <!-- Sidebar -->
        <aside class="flex-shrink-0 lg:w-80">
            <div class="sticky p-6 bg-white border border-gray-100 shadow-lg rounded-xl top-24">
                <!-- Categories -->
                <div class="mb-8">
                    <h3 class="flex items-center mb-4 text-lg font-bold text-gray-900">
                        <i class="mr-2 text-blue-600 fas fa-tags"></i>Categories
                    </h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="products.php" class="flex items-center justify-between py-3 px-4 rounded-lg transition duration-300 <?= !$current_category ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-50' ?>">
                                <span class="font-semibold">All Categories</span>
                                <span class="px-2 py-1 text-xs font-bold text-blue-800 bg-blue-100 rounded-full"><?= $total_products ?></span>
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="products.php?category=<?= $category['slug'] ?>" class="flex items-center justify-between py-3 px-4 rounded-lg transition duration-300 <?= $current_category && $current_category['id'] == $category['id'] ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-700 hover:bg-gray-50' ?>">
                                    <span><?= htmlspecialchars($category['name']) ?></span>
                                    <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full"><?= $category['product_count'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Search -->
                <div class="mb-8">
                    <h3 class="flex items-center mb-4 text-lg font-bold text-gray-900">
                        <i class="mr-2 text-blue-600 fas fa-search"></i>Search
                    </h3>
                    <form action="products.php" method="GET" class="space-y-3">
                        <?php if ($current_category): ?>
                            <input type="hidden" name="category" value="<?= $current_category['slug'] ?>">
                        <?php endif; ?>
                        <div class="relative">
                            <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search products..." class="w-full py-3 pl-10 pr-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <i class="absolute text-gray-400 transform -translate-y-1/2 fas fa-search left-3 top-1/2"></i>
                        </div>
                        <button type="submit" class="w-full py-3 font-semibold text-white transition duration-300 bg-blue-600 rounded-xl hover:bg-blue-700">Search</button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1">
            <!-- Results Header -->
            <div class="p-6 mb-8 bg-white border border-gray-100 shadow-lg rounded-xl">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center">
                        <span class="font-semibold text-gray-700">
                            Found <span class="text-lg font-bold text-blue-600"><?= $total_products ?></span> products
                            <?php if ($total_pages > 1): ?>
                                (Page <?= $page ?> of <?= $total_pages ?>)
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="font-semibold text-gray-700">Sort by:</span>
                        <form method="GET">
                            <?php if ($category_slug): ?>
                                <input type="hidden" name="category" value="<?= $category_slug ?>">
                            <?php endif; ?>
                            <?php if ($search_query): ?>
                                <input type="hidden" name="q" value="<?= htmlspecialchars($search_query) ?>">
                            <?php endif; ?>
                            <select name="sort" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="newest" <?= $sort_by === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                <option value="price_asc" <?= $sort_by === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_desc" <?= $sort_by === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (!empty($products)): ?>
                <div class="grid grid-cols-1 gap-8 mb-12 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($products as $product):
                        $discount_percentage = calculateDiscountPercentage($product['price'], $product['discount_price']);
                        $image_src = '../assets/uploads/products/' . ($product['featured_image'] ?? '');
                        $image_exists = !empty($product['featured_image']) && file_exists($image_src);
                    ?>
                        <div class="overflow-hidden transition-all duration-500 transform bg-white border border-gray-100 shadow-lg group rounded-2xl hover:shadow-2xl hover:-translate-y-2">
                            <div class="relative overflow-hidden bg-gray-100">
                                <a href="product-detail.php?slug=<?= $product['slug'] ?>">
                                    <img src="<?= $image_exists ? $image_src : 'https://via.placeholder.com/400x300?text=No+Image' ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="object-cover w-full h-64 transition-transform duration-700 group-hover:scale-110">
                                </a>
                                <?php if ($discount_percentage > 0): ?>
                                    <span class="absolute px-3 py-1 text-sm font-bold text-white bg-red-500 rounded-full shadow-lg top-4 left-4">-<?= $discount_percentage ?>% OFF</span>
                                <?php endif; ?>
                                <?php if ($product['is_featured']): ?>
                                    <span class="absolute px-3 py-1 text-sm font-bold text-white bg-yellow-500 rounded-full shadow-lg top-4 right-4"><i class="mr-1 fas fa-star"></i>Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <span class="px-2 py-1 text-xs text-blue-600 rounded bg-blue-50"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></span>
                                <h3 class="mt-2 mb-2 text-lg font-bold text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="mb-4 text-sm text-gray-600 line-clamp-2"><?= htmlspecialchars($product['short_description'] ?? $product['description'] ?? 'No description available') ?></p>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($product['discount_price']): ?>
                                            <span class="text-2xl font-bold text-gray-900"><?= formatPrice($product['discount_price']) ?></span>
                                            <span class="text-lg text-gray-500 line-through"><?= formatPrice($product['price']) ?></span>
                                        <?php else: ?>
                                            <span class="text-2xl font-bold text-gray-900"><?= formatPrice($product['price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $product['stock_quantity'] > 10 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= $product['stock_quantity'] ?> in stock
                                    </span>
                                </div>
                                <a href="product-detail.php?slug=<?= $product['slug'] ?>" class="block w-full py-3 font-semibold text-center text-white transition duration-300 bg-blue-600 rounded-xl hover:bg-blue-700">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="flex items-center justify-center mb-12 space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="<?= $pagination_base ?>&page=<?= $page - 1 ?>" class="px-4 py-2 transition duration-300 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?= $pagination_base ?>&page=<?= $i ?>" class="px-4 py-2 border rounded-lg <?= $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50' ?> transition duration-300"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="<?= $pagination_base ?>&page=<?= $page + 1 ?>" class="px-4 py-2 transition duration-300 border border-gray-300 rounded-lg hover:bg-gray-50">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="py-20 text-center bg-white border border-gray-100 shadow-lg rounded-2xl">
                    <i class="mb-4 text-6xl text-gray-300 fas fa-search"></i>
                    <h3 class="mb-2 text-2xl font-bold text-gray-700">No products found</h3>
                    <p class="mb-8 text-gray-600">Try adjusting your search or filter criteria.</p>
                    <a href="products.php" class="px-8 py-4 font-semibold text-white transition duration-300 bg-blue-600 rounded-xl hover:bg-blue-700">Browse All Products</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<?php include '../includes/footer.php'; ?>