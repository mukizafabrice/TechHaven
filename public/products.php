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

// Let's debug step by step
echo "<!-- DEBUG: Starting products query -->";

try {
    // First, let's get ALL active products without any joins to see if the basic query works
    $simple_sql = "SELECT * FROM products WHERE is_active = TRUE LIMIT 10";
    $simple_stmt = $pdo->query($simple_sql);
    $simple_products = $simple_stmt->fetchAll();

    echo "<!-- DEBUG: Simple query found: " . count($simple_products) . " products -->";

    if (count($simple_products) > 0) {
        echo "<!-- DEBUG: First product from simple query: " . $simple_products[0]['name'] . " -->";
    }

    // Now let's try the main query but simplified
    $main_sql = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.is_active = TRUE 
                 LIMIT 10";

    echo "<!-- DEBUG: Main SQL: $main_sql -->";

    $main_stmt = $pdo->query($main_sql);
    $main_products = $main_stmt->fetchAll();

    echo "<!-- DEBUG: Main query found: " . count($main_products) . " products -->";

    if (count($main_products) > 0) {
        echo "<!-- DEBUG: First product from main query: " . $main_products[0]['name'] . " -->";
        echo "<!-- DEBUG: Category name: " . ($main_products[0]['category_name'] ?? 'NULL') . " -->";
    }

    // If main query fails but simple query works, there's an issue with the JOIN
    if (count($simple_products) > 0 && count($main_products) === 0) {
        echo "<!-- DEBUG: ISSUE DETECTED: JOIN with categories is failing -->";

        // Let's check what's happening with category IDs
        $category_check_sql = "SELECT p.id, p.name, p.category_id, c.name as cat_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.is_active = TRUE 
                              LIMIT 5";
        $category_check_stmt = $pdo->query($category_check_sql);
        $category_check_results = $category_check_stmt->fetchAll();

        echo "<!-- DEBUG: Category check results: " . count($category_check_results) . " rows -->";
        foreach ($category_check_results as $row) {
            echo "<!-- DEBUG: Product: " . $row['name'] . " | Category ID: " . $row['category_id'] . " | Category Name: " . ($row['cat_name'] ?? 'NULL') . " -->";
        }
    }

    // Now build the actual query for the page
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

    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    echo "<!-- DEBUG: Final SQL with params: $sql -->";
    echo "<!-- DEBUG: Parameters count: " . count($params) . " -->";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    echo "<!-- DEBUG: Final query returned: " . count($products) . " products -->";

    // Get total count
    $count_sql = "SELECT COUNT(*) FROM products p WHERE p.is_active = TRUE";
    $count_params = [];

    if ($current_category) {
        $count_sql .= " AND p.category_id = ?";
        $count_params[] = $current_category['id'];
    }

    if ($search_query) {
        $count_sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $search_term = "%$search_query%";
        $count_params[] = $search_term;
        $count_params[] = $search_term;
        $count_params[] = $search_term;
    }

    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $total_products = $count_stmt->fetchColumn();
    $total_pages = ceil($total_products / $limit);
} catch (PDOException $e) {
    echo "<!-- DEBUG: SQL Error: " . $e->getMessage() . " -->";
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
    $total_products = 0;
    $total_pages = 1;
}

$categories = getCategories($pdo);
?>

<div class="container px-4 py-8 mx-auto">
    <!-- Enhanced Debug Info -->
    <div class="p-4 mb-6 border border-yellow-200 rounded-lg bg-yellow-50">
        <h3 class="mb-2 font-semibold text-yellow-800">Debug Information:</h3>
        <div class="space-y-1 text-sm text-yellow-700">
            <div>Simple Query Products: <span class="font-bold" id="debug-simple"><?= count($simple_products ?? []) ?></span></div>
            <div>Main Query Products: <span class="font-bold" id="debug-main"><?= count($main_products ?? []) ?></span></div>
            <div>Final Products Found: <span class="font-bold" id="debug-final"><?= count($products) ?></span></div>
            <div>Total Products in DB: <span class="font-bold"><?= $total_products ?></span></div>
            <div>Current Category: <span class="font-bold"><?= $current_category ? htmlspecialchars($current_category['name']) : 'All Categories' ?></span></div>
        </div>
        <?php if (count($simple_products ?? []) > 0 && count($products) === 0): ?>
            <div class="p-2 mt-3 border border-red-200 rounded bg-red-50">
                <p class="text-sm font-semibold text-red-700">Issue Detected: Products exist but aren't being returned by the final query. Check category relationships.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="mb-2 text-3xl font-bold text-gray-800">
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

    <div class="flex flex-col gap-8 lg:flex-row">
        <!-- Sidebar Filters -->
        <aside class="flex-shrink-0 lg:w-64">
            <div class="sticky p-6 bg-white rounded-lg shadow-sm top-24">
                <h3 class="mb-4 text-lg font-semibold">Categories</h3>
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
                    <h3 class="mb-4 text-lg font-semibold">Search</h3>
                    <form action="products.php" method="GET" class="flex">
                        <?php if ($current_category): ?>
                            <input type="hidden" name="category" value="<?= $current_category['slug'] ?>">
                        <?php endif; ?>
                        <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>"
                            placeholder="Search products..."
                            class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" class="px-4 py-2 text-white transition duration-300 bg-blue-600 rounded-r-lg hover:bg-blue-700">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Products Grid -->
        <main class="flex-1">
            <!-- Results Info -->
            <div class="flex items-center justify-between mb-6">
                <p class="text-gray-600">
                    Showing <?= count($products) ?> of <?= $total_products ?> products
                </p>
            </div>

            <!-- Products -->
            <?php if (!empty($products)): ?>
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($products as $product):
                        // Safe data access
                        $product_price = floatval($product['price'] ?? 0);
                        $discount_price = !empty($product['discount_price']) ? floatval($product['discount_price']) : null;
                        $stock_quantity = intval($product['stock_quantity'] ?? 0);
                        $discount_percentage = calculateDiscountPercentage($product_price, $discount_price);

                        // Check image path
                        $image_path = '../assets/uploads/products/' . ($product['featured_image'] ?? '');
                        $image_exists = !empty($product['featured_image']) && file_exists($image_path);
                    ?>
                        <div class="overflow-hidden transition duration-300 bg-white rounded-lg shadow-md hover:shadow-xl product-card">
                            <div class="relative">
                                <?php if ($image_exists): ?>
                                    <img src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        class="object-cover w-full h-48">
                                <?php else: ?>
                                    <div class="flex items-center justify-center w-full h-48 bg-gray-200">
                                        <i class="text-4xl text-gray-400 fas fa-image"></i>
                                        <span class="ml-2 text-sm text-gray-600">No Image</span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($discount_percentage > 0): ?>
                                    <span class="absolute px-2 py-1 text-sm font-semibold text-white bg-red-500 rounded top-3 left-3">
                                        -<?= $discount_percentage ?>%
                                    </span>
                                <?php endif; ?>

                                <?php if ($stock_quantity == 0): ?>
                                    <span class="absolute px-2 py-1 text-sm font-semibold text-white bg-gray-500 rounded top-3 right-3">
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="p-4">
                                <span class="text-xs tracking-wide text-gray-500 uppercase">
                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                </span>
                                <h3 class="mb-2 text-lg font-semibold line-clamp-2">
                                    <?= htmlspecialchars($product['name']) ?>
                                </h3>
                                <p class="mb-4 text-sm text-gray-600 line-clamp-2">
                                    <?= htmlspecialchars($product['short_description'] ?? $product['description'] ?? 'No description available') ?>
                                </p>

                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($discount_price && $discount_price > 0): ?>
                                            <span class="text-lg font-bold text-blue-600">
                                                <?= formatPrice($discount_price) ?>
                                            </span>
                                            <span class="text-sm text-gray-500 line-through">
                                                <?= formatPrice($product_price) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-lg font-bold text-blue-600">
                                                <?= formatPrice($product_price) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full 
                                        <?= $stock_quantity > 10 ? 'bg-green-100 text-green-800' : ($stock_quantity > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= $stock_quantity ?> in stock
                                    </span>
                                </div>

                                <a href="product-detail.php?slug=<?= $product['slug'] ?>"
                                    class="block w-full py-2 text-center text-white transition duration-300 bg-blue-600 rounded hover:bg-blue-700">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="flex items-center justify-center space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                                class="px-4 py-2 transition duration-300 border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="mr-2 fas fa-chevron-left"></i> Previous
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
                                class="px-4 py-2 transition duration-300 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Next <i class="ml-2 fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Temporary fix: Show products from simple query if main query fails -->
                <?php if (!empty($simple_products) && empty($products)): ?>
                    <div class="p-4 mb-6 border border-orange-200 rounded-lg bg-orange-50">
                        <h3 class="font-semibold text-orange-800">Showing Products (Fallback Mode)</h3>
                        <p class="text-sm text-orange-700">Products are showing but category information may be limited due to database join issues.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($simple_products as $product):
                            $product_price = floatval($product['price'] ?? 0);
                            $discount_price = !empty($product['discount_price']) ? floatval($product['discount_price']) : null;
                            $stock_quantity = intval($product['stock_quantity'] ?? 0);
                            $discount_percentage = calculateDiscountPercentage($product_price, $discount_price);

                            $image_path = '../assets/uploads/products/' . ($product['featured_image'] ?? '');
                            $image_exists = !empty($product['featured_image']) && file_exists($image_path);
                        ?>
                            <div class="overflow-hidden transition duration-300 bg-white rounded-lg shadow-md hover:shadow-xl product-card">
                                <div class="relative">
                                    <?php if ($image_exists): ?>
                                        <img src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                                            alt="<?= htmlspecialchars($product['name']) ?>"
                                            class="object-cover w-full h-48">
                                    <?php else: ?>
                                        <div class="flex items-center justify-center w-full h-48 bg-gray-200">
                                            <i class="text-4xl text-gray-400 fas fa-image"></i>
                                            <span class="ml-2 text-sm text-gray-600">No Image</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($discount_percentage > 0): ?>
                                        <span class="absolute px-2 py-1 text-sm font-semibold text-white bg-red-500 rounded top-3 left-3">
                                            -<?= $discount_percentage ?>%
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($stock_quantity == 0): ?>
                                        <span class="absolute px-2 py-1 text-sm font-semibold text-white bg-gray-500 rounded top-3 right-3">
                                            Out of Stock
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="p-4">
                                    <span class="text-xs tracking-wide text-gray-500 uppercase">
                                        Uncategorized
                                    </span>
                                    <h3 class="mb-2 text-lg font-semibold line-clamp-2">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </h3>
                                    <p class="mb-4 text-sm text-gray-600 line-clamp-2">
                                        <?= htmlspecialchars($product['short_description'] ?? $product['description'] ?? 'No description available') ?>
                                    </p>

                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-2">
                                            <?php if ($discount_price && $discount_price > 0): ?>
                                                <span class="text-lg font-bold text-blue-600">
                                                    <?= formatPrice($discount_price) ?>
                                                </span>
                                                <span class="text-sm text-gray-500 line-through">
                                                    <?= formatPrice($product_price) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-lg font-bold text-blue-600">
                                                    <?= formatPrice($product_price) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full 
                                            <?= $stock_quantity > 10 ? 'bg-green-100 text-green-800' : ($stock_quantity > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                            <?= $stock_quantity ?> in stock
                                        </span>
                                    </div>

                                    <a href="product-detail.php?slug=<?= $product['slug'] ?>"
                                        class="block w-full py-2 text-center text-white transition duration-300 bg-blue-600 rounded hover:bg-blue-700">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="py-16 text-center bg-white rounded-lg shadow-sm">
                        <i class="mb-4 text-6xl text-gray-300 fas fa-search"></i>
                        <h3 class="mb-2 text-xl font-semibold text-gray-600">No products found</h3>
                        <p class="mb-6 text-gray-500">
                            There are no active products in the database.
                        </p>
                        <a href="../admin/" class="px-6 py-3 text-white transition duration-300 bg-green-600 rounded-lg hover:bg-green-700">
                            Go to Admin Panel
                        </a>
                    </div>
                <?php endif; ?>
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