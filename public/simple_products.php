<?php
// Simple guaranteed working products page
$config_path = __DIR__ . '/../includes/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    $config_path = __DIR__ . '/../config.php';
    require_once $config_path;
}

$page_title = "Products - TechHaven";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <!-- Simple Header -->
    <header class="bg-white border-b shadow-sm">
        <div class="container px-4 py-4 mx-auto">
            <div class="flex items-center justify-between">
                <a href="index.php" class="text-2xl font-bold text-blue-600">TechHaven</a>
                <nav class="flex space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600">Home</a>
                    <a href="products.php" class="font-semibold text-blue-600">Products</a>
                    <a href="contact.php" class="text-gray-700 hover:text-blue-600">Contact</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container px-4 py-8 mx-auto">
        <h1 class="mb-2 text-3xl font-bold text-gray-800">Our Products</h1>
        <p class="mb-8 text-gray-600">Simple working version</p>

        <?php
        // SIMPLE GUARANTEED QUERY
        $category_slug = $_GET['category'] ?? '';
        $search_query = $_GET['q'] ?? '';

        try {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.is_active = TRUE";

            $params = [];

            if ($category_slug) {
                // Get category ID from slug
                $cat_stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND is_active = TRUE");
                $cat_stmt->execute([$category_slug]);
                $category = $cat_stmt->fetch();

                if ($category) {
                    $sql .= " AND p.category_id = ?";
                    $params[] = $category['id'];
                }
            }

            if ($search_query) {
                $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
                $search_term = "%$search_query%";
                $params[] = $search_term;
                $params[] = $search_term;
            }

            $sql .= " ORDER BY p.created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();
        } catch (Exception $e) {
            $products = [];
            echo "<div class='px-4 py-3 mb-6 text-red-700 bg-red-100 border border-red-400 rounded'>Error: " . $e->getMessage() . "</div>";
        }
        ?>

        <!-- Simple Category Filter -->
        <div class="p-4 mb-6 bg-white rounded-lg shadow">
            <h3 class="mb-3 font-semibold">Categories:</h3>
            <div class="flex flex-wrap gap-2">
                <a href="simple_products.php" class="px-4 py-2 text-white bg-blue-600 rounded">All</a>
                <?php
                $cats = $pdo->query("SELECT id, name, slug FROM categories WHERE is_active = TRUE")->fetchAll();
                foreach ($cats as $cat) {
                    $active = ($category_slug == $cat['slug']) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700';
                    echo "<a href='simple_products.php?category={$cat['slug']}' class='px-4 py-2 rounded {$active}'>{$cat['name']}</a>";
                }
                ?>
            </div>
        </div>

        <!-- Products Grid -->
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
                            <p class="mt-2 text-sm text-gray-600 line-clamp-2"><?= htmlspecialchars($product['short_description'] ?? 'No description') ?></p>
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
            <div class="py-12 text-center bg-white rounded-lg shadow">
                <i class="mb-4 text-6xl text-gray-300 fas fa-box-open"></i>
                <h3 class="text-xl font-semibold text-gray-600">No products found</h3>
                <p class="mt-2 text-gray-500">Try a different category or search term.</p>
                <a href="simple_products.php" class="inline-block px-6 py-2 mt-4 text-white bg-blue-600 rounded hover:bg-blue-700">
                    Show All Products
                </a>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</body>

</html>