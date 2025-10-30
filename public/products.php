<?php
$page_title = "All Products - Browse Our Collection";
include '../includes/header.php';

// Get filter parameters
$category_slug = $_GET['category'] ?? '';
$search_query = $_GET['q'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Get category if specified
$current_category = null;
if ($category_slug) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = TRUE");
        $stmt->execute([$category_slug]);
        $current_category = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching category: " . $e->getMessage());
    }
}

// Build query for products
$sql = "SELECT SQL_CALC_FOUND_ROWS p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = TRUE";
$count_sql = "SELECT COUNT(*) FROM products p WHERE p.is_active = TRUE";
$params = [];

if ($current_category) {
    $sql .= " AND p.category_id = ?";
    $count_sql .= " AND p.category_id = ?";
    $params[] = $current_category['id'];
}

if ($search_query) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
    $count_sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$limit_param = $limit;
$offset_param = $offset;
$params[] = $limit_param;
$params[] = $offset_param;

// Execute query
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Get total count
    $count_stmt = $pdo->prepare($count_sql);
    $count_params = array_slice($params, 0, -2); // Remove limit and offset params
    $count_stmt->execute($count_params);
    $total_products = $count_stmt->fetchColumn();
    $total_pages = ceil($total_products / $limit);
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
    $total_products = 0;
    $total_pages = 1;
}

$categories = getCategories($pdo);
?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <?php if ($current_category): ?>
                <?= htmlspecialchars($current_category['name']) ?> Products
            <?php elseif ($search_query): ?>
                Search Results for "<?= htmlspecialchars($search_query) ?>"
            <?php else: ?>
                All Products
            <?php endif; ?>
        </h1>
        <p class="text-gray-600">
            <?php if ($current_category && $current_category['description']): ?>
                <?= htmlspecialchars($current_category['description']) ?>
            <?php else: ?>
                Discover our wide range of electronic devices and accessories
            <?php endif; ?>
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                <h3 class="font-semibold text-lg mb-4">Categories</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="products.php" class="block py-2 px-3 rounded <?= !$current_category ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' ?>">
                            All Categories
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="products.php?category=<?= $category['slug'] ?>"
                                class="block py-2 px-3 rounded <?= $current_category && $current_category['id'] == $category['id'] ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Search Box -->
                <div class="mt-6">
                    <h3 class="font-semibold text-lg mb-4">Search</h3>
                    <form action="products.php" method="GET" class="flex">
                        <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>"
                            placeholder="Search products..."
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700 transition duration-300">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Products Grid -->
        <main class="flex-1">
            <!-- Results Info -->
            <div class="flex justify-between items-center mb-6">
                <p class="text-gray-600">
                    Showing <?= count($products) ?> of <?= $total_products ?> products
                </p>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Sort by:</span>
                    <select class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option>Newest First</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                        <option>Name: A to Z</option>
                    </select>
                </div>
            </div>

            <!-- Products -->
            <?php if (!empty($products)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php foreach ($products as $product):
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
                                <span class="text-xs text-gray-500 uppercase tracking-wide"><?= htmlspecialchars($product['category_name']) ?></span>
                                <h3 class="font-semibold text-lg mb-2 line-clamp-2"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($product['short_description']) ?></p>

                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($product['discount_price']): ?>
                                            <span class="text-lg font-bold text-blue-600"><?= formatPrice($product['discount_price']) ?></span>
                                            <span class="text-sm text-gray-500 line-through"><?= formatPrice($product['price']) ?></span>
                                        <?php else: ?>
                                            <span class="text-lg font-bold text-blue-600"><?= formatPrice($product['price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <a href="product-detail.php?slug=<?= $product['slug'] ?>"
                                    class="block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700 transition duration-300">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center items-center space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-300">
                                <i class="fas fa-chevron-left mr-2"></i> Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                    class="px-4 py-2 border rounded-lg <?= $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50' ?> transition duration-300">
                                    <?= $i ?>
                                </a>
                            <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                <span class="px-4 py-2 text-gray-500">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-300">
                                Next <i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-16 bg-white rounded-lg shadow-sm">
                    <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No products found</h3>
                    <p class="text-gray-500 mb-6">Try adjusting your search or filter criteria</p>
                    <a href="products.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                        Browse All Products
                    </a>
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