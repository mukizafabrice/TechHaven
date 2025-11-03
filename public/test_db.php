<?php
try {
    $config_path = __DIR__ . '/../includes/config.php';
    require_once $config_path;

    echo "<h1>Database Connection Test</h1>";

    // Test 1: Basic connection
    echo "<h2>Test 1: Basic Connection</h2>";
    $test = $pdo->query("SELECT 1 as test")->fetch();
    echo "<p style='color: green;'>✅ Database connection successful</p>";

    // Test 2: Check products table
    echo "<h2>Test 2: Products Table</h2>";
    $products_count = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch();
    echo "<p>Total products: " . $products_count['count'] . "</p>";

    $active_products = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active = TRUE")->fetch();
    echo "<p>Active products: " . $active_products['count'] . "</p>";

    // Test 3: Check cameras products specifically
    echo "<h2>Test 3: Cameras Products</h2>";
    $cameras_products = $pdo->query("
        SELECT p.id, p.name, p.category_id, p.is_active, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = 3 AND p.is_active = TRUE
    ")->fetchAll();

    echo "<p>Cameras products found: " . count($cameras_products) . "</p>";

    if (count($cameras_products) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Category ID</th><th>Category Name</th><th>Active</th></tr>";
        foreach ($cameras_products as $product) {
            echo "<tr>";
            echo "<td>{$product['id']}</td>";
            echo "<td>{$product['name']}</td>";
            echo "<td>{$product['category_id']}</td>";
            echo "<td>{$product['category_name']}</td>";
            echo "<td>" . ($product['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No camera products found despite diagnostic showing they exist!</p>";
    }

    // Test 4: Test the exact query from products.php
    echo "<h2>Test 4: Exact Products Query</h2>";
    $exact_sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = TRUE AND p.category_id = 3 ORDER BY p.created_at DESC";
    $exact_result = $pdo->query($exact_sql)->fetchAll();

    echo "<p>Exact query results: " . count($exact_result) . " products</p>";
    echo "<p><strong>Query:</strong> <code>$exact_sql</code></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
