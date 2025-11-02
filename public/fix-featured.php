<?php
require_once '../includes/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix Featured Products - TechHaven</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='min-h-screen p-8 bg-gray-100'>
    <div class='max-w-4xl p-8 mx-auto bg-white rounded-lg shadow-lg'>
        <h1 class='mb-6 text-3xl font-bold text-gray-800'>🔧 Fix Featured Products</h1>";

try {
    // Check current status
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(is_featured) as featured, SUM(is_active) as active FROM products");
    $stats = $stmt->fetch();

    echo "<div class='grid grid-cols-1 gap-4 mb-6 md:grid-cols-3'>
            <div class='p-4 rounded-lg bg-blue-50'>
                <div class='text-2xl font-bold text-blue-600'>{$stats['total']}</div>
                <div class='text-gray-600'>Total Products</div>
            </div>
            <div class='p-4 rounded-lg bg-green-50'>
                <div class='text-2xl font-bold text-green-600'>{$stats['active']}</div>
                <div class='text-gray-600'>Active Products</div>
            </div>
            <div class='p-4 rounded-lg bg-purple-50'>
                <div class='text-2xl font-bold text-purple-600'>{$stats['featured']}</div>
                <div class='text-gray-600'>Featured Products</div>
            </div>
        </div>";

    // Mark some products as featured if none are featured
    if ($stats['featured'] == 0 && $stats['active'] > 0) {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET is_featured = TRUE 
            WHERE is_active = TRUE 
            ORDER BY id ASC 
            LIMIT 4
        ");
        $stmt->execute();

        $updated = $stmt->rowCount();
        echo "<div class='px-4 py-3 mb-6 text-green-700 bg-green-100 border border-green-400 rounded'>
                <strong>✅ Success!</strong> Marked $updated products as featured!
            </div>";

        // Show which products were updated
        $stmt = $pdo->query("SELECT id, name, is_featured FROM products WHERE is_featured = TRUE");
        $featured = $stmt->fetchAll();

        echo "<h2 class='mb-4 text-xl font-semibold'>Featured Products Now:</h2>
              <div class='p-4 rounded-lg bg-gray-50'>";
        foreach ($featured as $product) {
            echo "<div class='flex items-center justify-between py-2 border-b border-gray-200'>
                    <span>📱 {$product['name']}</span>
                    <span class='px-2 py-1 text-sm text-white bg-green-500 rounded'>Featured</span>
                  </div>";
        }
        echo "</div>";
    } else {
        echo "<div class='px-4 py-3 mb-6 text-blue-700 bg-blue-100 border border-blue-400 rounded'>
                Some products are already featured. No changes needed.
            </div>";
    }

    echo "<div class='flex gap-4 mt-8'>
            <a href='index.php' class='px-6 py-3 text-white transition duration-300 bg-blue-500 rounded-lg hover:bg-blue-600'>
                ← Back to Homepage
            </a>
            <a href='products.php' class='px-6 py-3 text-white transition duration-300 bg-green-500 rounded-lg hover:bg-green-600'>
                View All Products
            </a>
        </div>";
} catch (PDOException $e) {
    echo "<div class='px-4 py-3 mb-6 text-red-700 bg-red-100 border border-red-400 rounded'>
            <strong>❌ Error:</strong> " . $e->getMessage() . "
          </div>";
}

echo "</div></body></html>";
