<?php
require_once 'config.php';

echo "<h1>Category Relationship Fix</h1>";

// Check current state
echo "<h2>Current Category Relationships:</h2>";
$sql = "SELECT 
    p.id as product_id, 
    p.name as product_name, 
    p.category_id,
    c.id as cat_id,
    c.name as category_name
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id 
WHERE p.is_active = TRUE";
$stmt = $pdo->query($sql);
$relationships = $stmt->fetchAll();

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Product ID</th><th>Product Name</th><th>Category ID</th><th>Category Name</th><th>Status</th></tr>";
foreach ($relationships as $row) {
    $status = $row['cat_id'] ? "✅ Linked" : "❌ No Category";
    echo "<tr>";
    echo "<td>{$row['product_id']}</td>";
    echo "<td>{$row['product_name']}</td>";
    echo "<td>{$row['category_id']}</td>";
    echo "<td>{$row['category_name']}</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

// Show available categories
echo "<h2>Available Categories:</h2>";
$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = TRUE")->fetchAll();
echo "<ul>";
foreach ($categories as $category) {
    echo "<li>{$category['id']} - {$category['name']}</li>";
}
echo "</ul>";

echo "<h2>Quick Fix:</h2>";
echo "<p>If products have NULL category_id, you need to assign them to categories in the admin panel.</p>";
echo "<a href='../admin/' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Go to Admin Panel</a>";
