<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check admin authentication
checkAdminAuth();

$page_title = "Manage Products";
include '../../includes/admin-header.php';

// Handle bulk actions
if ($_POST && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $product_ids = $_POST['product_ids'] ?? [];

    if (!empty($product_ids)) {
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';

        switch ($action) {
            case 'delete':
                $stmt = $pdo->prepare("UPDATE products SET is_active = FALSE WHERE id IN ($placeholders)");
                $stmt->execute($product_ids);
                $success_message = count($product_ids) . " product(s) deleted successfully.";
                break;

            case 'activate':
                $stmt = $pdo->prepare("UPDATE products SET is_active = TRUE WHERE id IN ($placeholders)");
                $stmt->execute($product_ids);
                $success_message = count($product_ids) . " product(s) activated successfully.";
                break;

            case 'feature':
                $stmt = $pdo->prepare("UPDATE products SET is_featured = TRUE WHERE id IN ($placeholders)");
                $stmt->execute($product_ids);
                $success_message = count($product_ids) . " product(s) marked as featured.";
                break;

            case 'unfeature':
                $stmt = $pdo->prepare("UPDATE products SET is_featured = FALSE WHERE id IN ($placeholders)");
                $stmt->execute($product_ids);
                $success_message = count($product_ids) . " product(s) unfeatured.";
                break;
        }
    }
}

// Handle individual delete
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);

    try {
        $stmt = $pdo->prepare("UPDATE products SET is_active = FALSE WHERE id = ?");
        $stmt->execute([$product_id]);
        $success_message = "Product deleted successfully.";
    } catch (PDOException $e) {
        $error_message = "Error deleting product: " . $e->getMessage();
    }
}

// Get all products with categories
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manage Products</h1>
            <p class="text-gray-600 mt-1">Manage your product inventory and listings</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="add.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                <i class="fas fa-plus mr-2"></i> Add New Product
            </a>
        </div>
    </div>

    <!-- Notifications -->
    <?php if (isset($success_message)): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $success_message ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= $error_message ?>
        </div>
    <?php endif; ?>

    <!-- Products Table -->
    <div class="dashboard-card p-6">
        <!-- Bulk Actions -->
        <form method="POST" class="bulk-action-form mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <select name="bulk_action" class="bulk-action border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Activate</option>
                        <option value="delete">Delete</option>
                        <option value="feature">Mark as Featured</option>
                        <option value="unfeature">Remove Featured</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                        Apply
                    </button>
                </div>

                <div class="flex items-center space-x-4">
                    <input type="text" placeholder="Search products..."
                        class="table-search border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <span class="text-sm text-gray-600"><?= count($products) ?> product(s)</span>
                </div>
            </div>

            <!-- Products Table -->
            <div class="overflow-x-auto mt-6">
                <table class="data-table w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="w-12">
                                <input type="checkbox" class="select-all rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="text-left p-3 font-semibold text-gray-900">Product</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Category</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Price</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Stock</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Status</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Featured</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Date</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="p-3">
                                        <input type="checkbox" name="product_ids[]" value="<?= $product['id'] ?>"
                                            class="item-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center space-x-3">
                                            <img src="../../assets/uploads/products/<?= $product['featured_image'] ?>"
                                                alt="<?= htmlspecialchars($product['name']) ?>"
                                                class="w-10 h-10 object-cover rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></div>
                                                <div class="text-sm text-gray-500">SKU: <?= $product['sku'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 text-gray-900"><?= htmlspecialchars($product['category_name']) ?></td>
                                    <td class="p-3">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900"><?= formatPrice($product['price']) ?></span>
                                            <?php if ($product['discount_price']): ?>
                                                <span class="text-sm text-red-600 line-through"><?= formatPrice($product['discount_price']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $product['stock_quantity'] > 10 ? 'bg-green-100 text-green-800' : ($product['stock_quantity'] > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                            <?= $product['stock_quantity'] ?>
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <span class="status-badge <?= $product['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                            <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <?php if ($product['is_featured']): ?>
                                            <span class="status-badge status-featured">Featured</span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($product['created_at'])) ?>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center space-x-2">
                                            <a href="edit.php?id=<?= $product['id'] ?>"
                                                class="text-blue-600 hover:text-blue-900 transition duration-200"
                                                title="Edit Product">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?= $product['id'] ?>"
                                                class="text-red-600 hover:text-red-900 transition duration-200 delete-btn"
                                                title="Delete Product"
                                                onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="../../public/product-detail.php?slug=<?= $product['slug'] ?>"
                                                target="_blank"
                                                class="text-gray-600 hover:text-gray-900 transition duration-200"
                                                title="View Product">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-box-open text-4xl mb-3 text-gray-300"></i>
                                    <p>No products found. <a href="add.php" class="text-blue-600 hover:text-blue-700">Add your first product</a></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
    // Initialize bulk actions
    document.addEventListener('DOMContentLoaded', function() {
        // Select all functionality
        const selectAll = document.querySelector('.select-all');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        // Table search functionality
        const searchInput = document.querySelector('.table-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
</script>

<?php include '../../includes/admin-footer.php'; ?>