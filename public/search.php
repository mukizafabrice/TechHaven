<?php
// Include config first to define constants
require_once '../includes/config.php';

$search_query = $_GET['q'] ?? '';
$page_title = "Search Results for \"$search_query\" - " . SITE_NAME;
include '../includes/header.php';

if (empty($search_query)) {
    header('Location: products.php');
    exit;
}

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Search products
$sql = "SELECT SQL_CALC_FOUND_ROWS p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = TRUE 
        AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ? OR c.name LIKE ?)
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";

$search_term = "%$search_query%";
$params = [$search_term, $search_term, $search_term, $search_term, $limit, $offset];

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Get total count
    $total_products = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
    $total_pages = ceil($total_products / $limit);
} catch (PDOException $e) {
    error_log("Search error: " . $e->getMessage());
    $products = [];
    $total_products = 0;
    $total_pages = 1;
}

$categories = getCategories($pdo);
?>

<div class="container px-4 py-8 mx-auto">
    <!-- Search Header -->
    <div class="mb-8">
        <h1 class="mb-2 text-3xl font-bold text-gray-800">Search Results</h1>
        <p class="text-gray-600">
            <?php if ($total_products > 0): ?>
                Found <?= $total_products ?> product<?= $total_products !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($search_query) ?>"
            <?php else: ?>
                No products found for "<?= htmlspecialchars($search_query) ?>"
            <?php endif; ?>
        </p>

        <!-- Search Box -->
        <div class="max-w-2xl mt-6">
            <form action="search.php" method="GET" class="flex">
                <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>"
                    placeholder="Search products..."
                    class="flex-1 px-4 py-3 text-lg border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button type="submit" class="px-6 py-3 font-semibold text-white transition duration-300 bg-blue-600 rounded-r-lg hover:bg-blue-700">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </div>

    <div class="flex flex-col gap-8 lg:flex-row">
        <!-- Filters Sidebar -->
        <aside class="flex-shrink-0 lg:w-64">
            <div class="sticky p-6 bg-white rounded-lg shadow-sm top-24">
                <h3 class="mb-4 text-lg font-semibold">Refine Search</h3>

                <!-- Categories -->
                <div class="mb-6">
                    <h4 class="mb-3 font-medium text-gray-700">Categories</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="search.php?q=<?= urlencode($search_query) ?>"
                                class="block px-3 py-2 text-gray-700 transition duration-300 rounded hover:bg-gray-50">
                                All Categories
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="search.php?q=<?= urlencode($search_query) ?>&category=<?= $category['slug'] ?>"
                                    class="block px-3 py-2 text-gray-700 transition duration-300 rounded hover:bg-gray-50">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Price Range -->
                <div>
                    <h4 class="mb-3 font-medium text-gray-700">Price Range</h4>
                    <div class="space-y-2">
                        <a href="search.php?q=<?= urlencode($search_query) ?>&price=0-50" class="block px-3 py-2 text-gray-700 transition duration-300 rounded hover:bg-gray-50">
                            Under $50
                        </a>
                        <a href="search.php?q=<?= urlencode($search_query) ?>&price=50-100" class="block px-3 py-2 text-gray-700 transition duration-300 rounded hover:bg-gray-50">
                            $50 - $100
                        </a>
                        <a href="search.php?q=<?= urlencode($search_query) ?>&price=100-500" class="block px-3 py-2 text-gray-700 transition duration-300 rounded hover:bg-gray-50">
                            $100 - $500
                        </a>
                        <a href="search.php?q=<?= urlencode($search_query) ?>&price=500-1000" class="block px-3 py-2 text-gray-700 transition duration-300 rounded hover:bg-gray-50">
                            $500 - $1000
                        </a>
                        <a href="search.php?q=<?= urlencode($search_query) ?>&price=1000" class="block px-3 py-2 text-gray-700 transition duration-300 rounded hover:bg-gray-50">
                            Over $1000
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Search Results -->
        <main class="flex-1">
            <?php if (!empty($products)): ?>
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($products as $product):
                        $discount_percentage = calculateDiscountPercentage($product['price'], $product['discount_price']);
                    ?>
                        <div class="overflow-hidden transition duration-300 bg-white rounded-lg shadow-md hover:shadow-xl product-card">
                            <div class="relative">
                                <img src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    class="object-cover w-full h-48">
                                <?php if ($discount_percentage > 0): ?>
                                    <span class="absolute px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded top-2 left-2">
                                        -<?= $discount_percentage ?>%
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <span class="text-xs tracking-wide text-gray-500 uppercase"><?= htmlspecialchars($product['category_name']) ?></span>
                                <h3 class="mb-2 text-lg font-semibold line-clamp-2"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="mb-4 text-sm text-gray-600 line-clamp-2"><?= htmlspecialchars($product['short_description']) ?></p>

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
                <div class="py-16 text-center bg-white rounded-lg shadow-sm">
                    <i class="mb-4 text-6xl text-gray-300 fas fa-search"></i>
                    <h3 class="mb-4 text-xl font-semibold text-gray-600">No products found for "<?= htmlspecialchars($search_query) ?>"</h3>
                    <p class="mb-6 text-gray-500">Try adjusting your search terms or browse our categories</p>
                    <div class="flex flex-col justify-center gap-4 sm:flex-row">
                        <a href="products.php" class="px-6 py-3 text-white transition duration-300 bg-blue-600 rounded-lg hover:bg-blue-700">
                            Browse All Products
                        </a>
                        <a href="index.php" class="px-6 py-3 text-gray-700 transition duration-300 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Return Home
                        </a>
                    </div>
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