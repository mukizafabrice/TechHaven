<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check admin authentication
checkAdminAuth();

$page_title = "Manage Categories";
include '../../includes/admin-header.php';

// Handle bulk actions
if ($_POST && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $category_ids = $_POST['category_ids'] ?? [];

    if (!empty($category_ids)) {
        $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';

        switch ($action) {
            case 'delete':
                $stmt = $pdo->prepare("UPDATE categories SET is_active = FALSE WHERE id IN ($placeholders)");
                $stmt->execute($category_ids);
                $success_message = count($category_ids) . " category(s) deleted successfully.";
                break;

            case 'activate':
                $stmt = $pdo->prepare("UPDATE categories SET is_active = TRUE WHERE id IN ($placeholders)");
                $stmt->execute($category_ids);
                $success_message = count($category_ids) . " category(s) activated successfully.";
                break;
        }
    }
}

// Handle individual delete
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);

    try {
        // Check if category has products
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = TRUE");
        $stmt->execute([$category_id]);
        $product_count = $stmt->fetchColumn();

        if ($product_count > 0) {
            $error_message = "Cannot delete category. There are $product_count active products in this category.";
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET is_active = FALSE WHERE id = ?");
            $stmt->execute([$category_id]);
            $success_message = "Category deleted successfully.";
        }
    } catch (PDOException $e) {
        $error_message = "Error deleting category: " . $e->getMessage();
    }
}

// Get all categories with product counts
try {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(p.id) as product_count,
               (SELECT name FROM categories WHERE id = c.parent_id) as parent_name
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id AND p.is_active = TRUE
        GROUP BY c.id 
        ORDER BY c.parent_id IS NULL DESC, c.name ASC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manage Categories</h1>
            <p class="text-gray-600 mt-1">Organize your products into categories</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="add.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                <i class="fas fa-plus mr-2"></i> Add New Category
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

    <!-- Categories Table -->
    <div class="dashboard-card p-6">
        <!-- Bulk Actions -->
        <form method="POST" class="bulk-action-form mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <select name="bulk_action" class="bulk-action border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Activate</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                        Apply
                    </button>
                </div>

                <div class="flex items-center space-x-4">
                    <input type="text" placeholder="Search categories..."
                        class="table-search border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <span class="text-sm text-gray-600"><?= count($categories) ?> category(s)</span>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="overflow-x-auto mt-6">
                <table class="data-table w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="w-12">
                                <input type="checkbox" class="select-all rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="text-left p-3 font-semibold text-gray-900">Category</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Parent</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Products</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Slug</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Status</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Date</th>
                            <th class="text-left p-3 font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <tr class="hover:bg-gray-50 transition duration-150 <?= $category['parent_id'] ? 'bg-gray-50' : '' ?>">
                                    <td class="p-3">
                                        <input type="checkbox" name="category_ids[]" value="<?= $category['id'] ?>"
                                            class="item-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center space-x-3">
                                            <?php if ($category['image_url']): ?>
                                                <img src="../../assets/uploads/categories/<?= $category['image_url'] ?>"
                                                    alt="<?= htmlspecialchars($category['name']) ?>"
                                                    class="w-10 h-10 object-cover rounded-lg">
                                            <?php else: ?>
                                                <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-folder text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-medium text-gray-900">
                                                    <?= $category['parent_id'] ? '↳ ' : '' ?>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </div>
                                                <div class="text-sm text-gray-500 truncate max-w-xs">
                                                    <?= htmlspecialchars($category['description'] ?? 'No description') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 text-gray-900">
                                        <?= $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '<span class="text-gray-400">-</span>' ?>
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= $category['product_count'] ?> products
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-500">
                                        <?= $category['slug'] ?>
                                    </td>
                                    <td class="p-3">
                                        <span class="status-badge <?= $category['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                            <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($category['created_at'])) ?>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center space-x-2">
                                            <a href="edit.php?id=<?= $category['id'] ?>"
                                                class="text-blue-600 hover:text-blue-900 transition duration-200"
                                                title="Edit Category">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?= $category['id'] ?>"
                                                class="text-red-600 hover:text-red-900 transition duration-200 delete-btn"
                                                title="Delete Category"
                                                onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                    <p>No categories found. <a href="add.php" class="text-blue-600 hover:text-blue-700">Create your first category</a></p>
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