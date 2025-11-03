<?php
// Fix the config path
$config_path = __DIR__ . '/../includes/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    $config_path = __DIR__ . '/../config.php';
    require_once $config_path;
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Products Debug - TechHaven</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
</head>
<body class='p-6 bg-gray-50'>
    <div class='mx-auto max-w-7xl'>
        <h1 class='mb-6 text-3xl font-bold text-gray-800'>Products Page Debug</h1>";

// Test the exact query from products.php
echo "<div class='p-6 mb-6 bg-white rounded-lg shadow-md'>
        <h2 class='mb-4 text-2xl font-semibold text-gray-700'>Testing Products Query for Cameras</h2>";

$category_id = 3; // Cameras category ID
$limit = 12;
$offset = 0;

// Test 1: Exact query from products.php
echo "<h3 class='mb-3 text-lg font-semibold'>Test 1: Main Products Query</h3>";
$sql = "SELECT SQL_CALC_FOUND_ROWS 
               p.*, 
               c.name as category_name, 
               c.slug as category_slug
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = TRUE 
        AND p.category_id = ? 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category_id, $limit, $offset]);
    $products = $stmt->fetchAll();

    echo "<p><strong>Query:</strong> <code class='p-2 bg-gray-100 rounded'>" . htmlspecialchars($sql) . "</code></p>";
    echo "<p><strong>Parameters:</strong> category_id=$category_id, limit=$limit, offset=$offset</p>";
    echo "<p><strong>Products Found:</strong> " . count($products) . "</p>";

    if (count($products) > 0) {
        echo "<div class='mt-4 overflow-x-auto'>
                <table class='min-w-full bg-white border'>
                    <thead>
                        <tr class='bg-gray-100'>
                            <th class='px-4 py-2 border'>ID</th>
                            <th class='px-4 py-2 border'>Name</th>
                            <th class='px-4 py-2 border'>Category</th>
                            <th class='px-4 py-2 border'>Active</th>
                            <th class='px-4 py-2 border'>Slug</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($products as $product) {
            echo "<tr>
                    <td class='px-4 py-2 border'>{$product['id']}</td>
                    <td class='px-4 py-2 border'><strong>{$product['name']}</strong></td>
                    <td class='px-4 py-2 border'>{$product['category_name']}</td>
                    <td class='px-4 py-2 border'>" . ($product['is_active'] ? '✅ Yes' : '❌ No') . "</td>
                    <td class='px-4 py-2 border'>{$product['slug']}</td>
                  </tr>";
        }
        echo "</tbody></table></div>";
    } else {
        echo "<p class='font-semibold text-red-500'>❌ No products found with this query!</p>";
    }

    // Get total count
    $count_stmt = $pdo->query("SELECT FOUND_ROWS()");
    $total_products = $count_stmt->fetchColumn();
    echo "<p><strong>Total Products Count:</strong> $total_products</p>";
} catch (Exception $e) {
    echo "<p class='text-red-500'>Error: " . $e->getMessage() . "</p>";
}

// Test 2: Simple query without SQL_CALC_FOUND_ROWS
echo "<h3 class='mt-6 mb-3 text-lg font-semibold'>Test 2: Simple Query</h3>";
$simple_sql = "SELECT p.*, c.name as category_name 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id 
               WHERE p.is_active = TRUE 
               AND p.category_id = ?";
try {
    $simple_stmt = $pdo->prepare($simple_sql);
    $simple_stmt->execute([$category_id]);
    $simple_products = $simple_stmt->fetchAll();

    echo "<p><strong>Products Found:</strong> " . count($simple_products) . "</p>";
    if (count($simple_products) > 0) {
        foreach ($simple_products as $product) {
            echo "<p>✅ {$product['name']} - {$product['category_name']}</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='text-red-500'>Error: " . $e->getMessage() . "</p>";
}

// Test 3: Check if there's an issue with the database connection in products.php
echo "<h3 class='mt-6 mb-3 text-lg font-semibold'>Test 3: Database Connection Check</h3>";
try {
    $test_conn = $pdo->query("SELECT 1");
    echo "<p class='text-green-500'>✅ Database connection is working</p>";

    // Check if we can access the products table
    $test_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    echo "<p><strong>Total products in database:</strong> $test_products</p>";

    $test_active = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = TRUE")->fetchColumn();
    echo "<p><strong>Active products:</strong> $test_active</p>";

    $test_cameras = $pdo->query("SELECT COUNT(*) FROM products WHERE category_id = 3 AND is_active = TRUE")->fetchColumn();
    echo "<p><strong>Active cameras products:</strong> $test_cameras</p>";
} catch (Exception $e) {
    echo "<p class='text-red-500'>❌ Database connection error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test the actual products.php page output
echo "<div class='p-6 mb-6 bg-white rounded-lg shadow-md'>
        <h2 class='mb-4 text-2xl font-semibold text-gray-700'>Testing Actual Products Page</h2>
        <p class='mb-4'>Let's test what happens when we visit the actual products page with category filter:</p>
        <a href='products.php?category=cameras' 
           target='_blank'
           class='inline-flex items-center px-6 py-3 text-white transition duration-300 bg-blue-600 rounded-lg hover:bg-blue-700'>
            <i class='mr-2 fas fa-external-link-alt'></i>
            Test products.php?category=cameras
        </a>
        <p class='mt-2 text-sm text-gray-600'>Open this in a new tab and check if products show up.</p>
    </div>";

// Quick fix: Create a simple working version
echo "<div class='p-6 mb-6 bg-white rounded-lg shadow-md'>
        <h2 class='mb-4 text-2xl font-semibold text-gray-700'>Quick Working Fix</h2>
        <p class='mb-4'>If the main products page isn't working, try this simplified version:</p>
        <a href='simple_products.php' 
           class='inline-flex items-center px-6 py-3 text-white transition duration-300 bg-green-600 rounded-lg hover:bg-green-700'>
            <i class='mr-2 fas fa-play'></i>
            Try Simple Products Page
        </a>
    </div>";

echo "</div></body></html>";
