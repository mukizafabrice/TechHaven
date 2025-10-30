<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check admin authentication
checkAdminAuth();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$category_id = intval($_GET['id']);

// Get category data for confirmation
try {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(p.id) as product_count,
               (SELECT name FROM categories WHERE id = c.parent_id) as parent_name
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id AND p.is_active = TRUE
        WHERE c.id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();

    if (!$category) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Error fetching category: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

$page_title = "Delete Category - " . htmlspecialchars($category['name']);
include '../../includes/admin-header.php';

$error = '';
$success = '';

if ($_POST && isset($_POST['confirm_delete'])) {
    try {
        // Check if category has active products
        if ($category['product_count'] > 0) {
            $error = "Cannot delete category. There are {$category['product_count']} active products in this category. Please reassign or delete those products first.";
        } else {
            // Start transaction
            $pdo->beginTransaction();

            // Delete category image if exists
            if ($category['image_url'] && file_exists(UPLOAD_PATH . CATEGORY_IMAGE_PATH . $category['image_url'])) {
                unlink(UPLOAD_PATH . CATEGORY_IMAGE_PATH . $category['image_url']);
            }

            // Delete category
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);

            // Commit transaction
            $pdo->commit();

            $success = "Category deleted successfully!";

            // Redirect after 2 seconds
            header("Refresh: 2; URL=index.php");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error deleting category: " . $e->getMessage();
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Delete Category</h1>
            <p class="text-gray-600 mt-1">Permanently remove category from the system</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Categories
            </a>
        </div>
    </div>

    <!-- Notifications -->
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $success ?>
            <p class="ml-4 text-sm">Redirecting to categories list...</p>
        </div>
    <?php else: ?>
        <!-- Confirmation Card -->
        <div class="dashboard-card p-6">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>

                <h2 class="text-xl font-bold text-gray-900 mb-2">Confirm Deletion</h2>

                <!-- Warning Messages -->
                <?php if ($category['product_count'] > 0): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 max-w-md mx-auto">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                            <div class="text-left">
                                <h4 class="font-semibold text-yellow-800">Category Contains Products</h4>
                                <p class="text-yellow-700 text-sm mt-1">
                                    This category has <?= $category['product_count'] ?> active product(s).
                                    You cannot delete a category that contains products.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        You are about to permanently delete the category
                        <strong>"<?= htmlspecialchars($category['name']) ?>"</strong>.
                        This action cannot be undone.
                    </p>
                <?php endif; ?>

                <!-- Category Preview -->
                <div class="bg-gray-50 rounded-lg p-4 max-w-xs mx-auto mb-6">
                    <?php if ($category['image_url']): ?>
                        <img src="../../assets/uploads/categories/<?= $category['image_url'] ?>"
                            alt="<?= htmlspecialchars($category['name']) ?>"
                            class="w-32 h-32 object-cover rounded-lg mx-auto mb-3">
                    <?php else: ?>
                        <div class="w-32 h-32 bg-gray-200 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-folder text-gray-400 text-3xl"></i>
                        </div>
                    <?php endif; ?>
                    <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($category['name']) ?></h3>
                    <p class="text-sm text-gray-500 mt-1"><?= $category['parent_name'] ? "Child of: {$category['parent_name']}" : "Top-level category" ?></p>
                    <p class="text-sm text-gray-500">Products: <?= $category['product_count'] ?></p>
                </div>

                <?php if ($category['product_count'] == 0): ?>
                    <form method="POST" class="space-y-4">
                        <div class="flex items-center justify-center space-x-4">
                            <a href="edit.php?id=<?= $category_id ?>"
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                                <i class="fas fa-edit mr-2"></i> Edit Instead
                            </a>
                            <button type="submit" name="confirm_delete"
                                class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-semibold"
                                onclick="return confirm('Are you absolutely sure? This cannot be undone!')">
                                <i class="fas fa-trash mr-2"></i> Yes, Delete Permanently
                            </button>
                        </div>

                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            This will permanently remove the category and its image
                        </div>
                    </form>
                <?php else: ?>
                    <div class="space-y-4">
                        <div class="flex items-center justify-center space-x-4">
                            <a href="edit.php?id=<?= $category_id ?>"
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                                <i class="fas fa-edit mr-2"></i> Edit Category
                            </a>
                            <a href="../products/index.php?category=<?= $category_id ?>"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-boxes mr-2"></i> Manage Products
                            </a>
                        </div>
                        <p class="text-sm text-gray-500">
                            You must reassign or delete all products in this category before deletion.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/admin-footer.php'; ?>