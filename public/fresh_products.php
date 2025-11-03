<?php
$page_title = "Products - TechHaven";
include '../includes/header.php';

// Simple debug function
function debug($message)
{
    echo "<!-- DEBUG: $message -->\n";
}

// Get parameters
$category_slug = $_GET['category'] ?? '';
$search_query = $_GET['q'] ?? '';

debug("Category: $category_slug, Search: $search_query");

// Get current category
$current_category = null;
if ($category_slug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = TRUE");
    $stmt->execute([$category_slug]);
    $current_category = $stmt->fetch();
    debug("Current category: " . ($current_category ? $current_category['name'] : 'not found'));
}

// SIMPLE DIRECT QUERY THAT CANNOT FAIL
try {
    debug("Starting products query...");

    // Build the query step by step
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $params = [];

    debug("Base SQL: $sql");

    // Always filter active products
    $sql .= " AND p.is_active = TRUE";
    debug("Added active filter");

    // Add category filter if specified
    if ($current_category) {
        $sql .= " AND p.category_id = ?";
        $params[] = $current_category['id'];
        debug("Added category filter: " . $current_category['id']);
    }

    // Add search filter if specified
    if ($search_query) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $search_term = "%$search_query%";
        $params[] = $search_term;
        $params[] = $search_term;
        debug("Added search filter: $search_query");
    }

    $sql .= " ORDER BY p.created_at DESC";
    debug("Final SQL: $sql");
    debug("Parameters: " . implode(', ', $params));

    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    debug("Products found: " . count($products));

    // If no products found, let's check what's in the database
    if (empty($products)) {
        debug("No products found with current filters, checking database...");

        // Check total active products
        $total_active = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = TRUE")->fetchColumn();
        debug("Total active products in DB: $total_active");

        // Check products in current category
        if ($current_category) {
            $cat_products = $pdo->query("SELECT COUNT(*) FROM products WHERE category_id = {$current_category['id']} AND is_active = TRUE")->fetchColumn();
            debug("Products in category {$current_category['name']}: $cat_products");
        }

        // Show some sample products to debug
        $sample = $pdo->query("SELECT id, name, category_id, is_active FROM products LIMIT 3")->fetchAll();
        foreach ($sample as $product) {
            debug("Sample product: {$product['name']} (ID: {$product['id']}, Category: {$product['category_id']}, Active: {$product['is_active']})");
        }
    }
} catch (Exception $e) {
    debug("ERROR: " . $e->getMessage());
    $products = [];
}

// Get categories
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = TRUE ORDER BY name")->fetchAll();
debug("Categories found: " . count($categories));
?>

<div class="container px-4 py-8 mx-auto">
    <!-- Debug Info -->
    <div class="p-4 mb-6 border border-blue-200 rounded-lg bg-blue-50">
        <h3 class="mb-2 font-semibold text-blue-800">Debug Information (View Page Source to See Details):</h3>
        <div class="text-sm text-blue-700">
            <div>Products Found: <span class="font-bold"><?= count($products) ?></span></div>
            <div>Current Category: <span class="font-bold"><?= $current_category ? $current_category['name'] : 'None' ?></span></div>
            <div>Search Query: <span class="font-bold">"<?= htmlspecialchars($search_query) ?>"</span></div>
        </div>
    </div>

    <h1 class="mb-2 text-3xl font-bold text-gray-800">
        <?php if ($current_category): ?>
            <?= htmlspecialchars($current_category['name']) ?> Products
        <?php elseif ($search_query): ?>
            Search Results for "<?= htmlspecialchars($search_query) ?>"
        <?php else: ?>
            All Products
        <?php endif; ?>
    </h1>

    <div class="flex flex-col gap-8 lg:flex-row">
        <!-- Sidebar -->
        <aside class="lg:w-64">
            <div class="sticky p-6 bg-white rounded-lg shadow top-24">
                <h3 class="mb-4 text-lg font-semibold">Categories</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="fresh_products.php" class="block py-2 px-3 rounded <?= !$current_category ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' ?>">
                            All Categories
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="fresh_products.php?category=<?= $category['slug'] ?>"
                                class="block py-2 px-3 rounded <?= $current_category && $current_category['id'] == $category['id'] ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1">
            <?php if (!empty($products)): ?>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($products as $product): ?>
                        <div class="overflow-hidden bg-white rounded-lg shadow-md">
                            <div class="flex items-center justify-center h-48 bg-gray-200">
                                <?php if (!empty($product['featured_image'])): ?>
                                    <img src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        class="object-cover w-full h-full">
                                <?php else: ?>
                                    <i class="text-4xl text-gray-400 fas fa-image"></i>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <span class="px-2 py-1 text-xs text-blue-600 rounded bg-blue-50"><?= $product['category_name'] ?></span>
                                <h3 class="mt-2 text-lg font-bold"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars($product['short_description'] ?? 'No description') ?></p>
                                <div class="flex items-center justify-between mt-4">
                                    <span class="text-xl font-bold text-blue-600">$<?= number_format($product['price'], 2) ?></span>
                                    <a href="product-detail.php?slug=<?= $product['slug'] ?>"
                                        class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="py-16 text-center bg-white rounded-lg shadow">
                    <i class="mb-4 text-6xl text-gray-300 fas fa-search"></i>
                    <h3 class="mb-2 text-xl font-semibold text-gray-600">No products found</h3>

                    <!-- Detailed error information -->
                    <div class="max-w-md p-4 mx-auto mt-4 text-left rounded-lg bg-gray-50">
                        <h4 class="mb-2 font-semibold">Troubleshooting Info:</h4>
                        <?php
                        $total_products = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = TRUE")->fetchColumn();
                        $cameras_count = $pdo->query("SELECT COUNT(*) FROM products WHERE category_id = 3 AND is_active = TRUE")->fetchColumn();
                        ?>
                        <p class="text-sm">• Total active products: <?= $total_products ?></p>
                        <p class="text-sm">• Active cameras products: <?= $cameras_count ?></p>
                        <p class="text-sm">• Current category ID: <?= $current_category ? $current_category['id'] : 'None' ?></p>

                        <?php if ($current_category && $cameras_count > 0): ?>
                            <p class="mt-2 text-sm font-semibold text-red-500">
                                ⚠️ There are <?= $cameras_count ?> camera products in the database, but the query returned none.
                                This suggests a database connection or query issue.
                            </p>
                        <?php endif; ?>
                    </div>

                    <a href="fresh_products.php" class="inline-block px-6 py-3 mt-6 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Show All Products
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>