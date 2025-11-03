<?php
require_once '../includes/config.php';

echo "<h1>Category-Product Relationship Diagnostic</h1>";

// Check all products and their categories
echo "<h2>All Products and Their Categories:</h2>";
$products_sql = "
    SELECT 
        p.id,
        p.name as product_name,
        p.category_id,
        c.name as category_name,
        c.id as actual_category_id,
        p.is_active
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id
";

try {
    $products_stmt = $pdo->query($products_sql);
    $all_products = $products_stmt->fetchAll();

    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f8f9fa;'>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Category ID</th>
            <th>Category Name</th>
            <th>Actual Category ID</th>
            <th>Is Active</th>
            <th>Status</th>
          </tr>";

    foreach ($all_products as $product) {
        $status = $product['category_name'] ? "✅ Linked" : "❌ No Category";
        $status = $product['category_id'] && !$product['category_name'] ? "❌ Invalid Category ID" : $status;

        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['product_name']}</td>";
        echo "<td>{$product['category_id']}</td>";
        echo "<td>{$product['category_name']}</td>";
        echo "<td>{$product['actual_category_id']}</td>";
        echo "<td>" . ($product['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check all categories
echo "<h2>All Categories:</h2>";
$categories_sql = "SELECT id, name, slug FROM categories WHERE is_active = TRUE";
try {
    $categories_stmt = $pdo->query($categories_sql);
    $categories = $categories_stmt->fetchAll();

    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f8f9fa;'>
            <th>Category ID</th>
            <th>Category Name</th>
            <th>Slug</th>
          </tr>";

    foreach ($categories as $category) {
        echo "<tr>";
        echo "<td>{$category['id']}</td>";
        echo "<td>{$category['name']}</td>";
        echo "<td>{$category['slug']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check products in Cameras category specifically
echo "<h2>Products in 'Cameras' Category (ID: 3):</h2>";
$cameras_sql = "SELECT p.id, p.name FROM products p WHERE p.category_id = 3 AND p.is_active = TRUE";
try {
    $cameras_stmt = $pdo->query($cameras_sql);
    $cameras_products = $cameras_stmt->fetchAll();

    if (count($cameras_products) > 0) {
        echo "<p>Found " . count($cameras_products) . " products in Cameras category:</p>";
        echo "<ul>";
        foreach ($cameras_products as $product) {
            echo "<li>{$product['id']} - {$product['name']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>No products found in Cameras category (ID: 3)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Quick fix form
echo "<h2>Quick Fix:</h2>";
echo "<p>If products have wrong category IDs, you can fix them here:</p>";

// Get all products for the fix form
$all_products_fix = $pdo->query("SELECT id, name, category_id FROM products WHERE is_active = TRUE")->fetchAll();

echo "<form method='POST' action='fix_categories.php' style='background: #f0f8ff; padding: 20px; border-radius: 10px;'>";
echo "<h3>Update Product Categories:</h3>";

foreach ($all_products_fix as $product) {
    echo "<div style='margin: 10px 0; padding: 10px; background: white; border-radius: 5px;'>";
    echo "<strong>{$product['name']}</strong> (Current Category ID: {$product['category_id']})<br>";
    echo "New Category ID: <input type='number' name='product[{$product['id']}]' value='{$product['category_id']}' min='1' style='margin-left: 10px;'>";
    echo "</div>";
}

echo "<br><button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Update Categories</button>";
echo "</form>";

echo "<hr>";
echo "<h2>SQL to Check Category Relationships:</h2>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>
SELECT 
    p.id as product_id,
    p.name as product_name, 
    p.category_id,
    c.id as category_table_id,
    c.name as category_name
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id 
WHERE p.is_active = TRUE;
</pre>";
