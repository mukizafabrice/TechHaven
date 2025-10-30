<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check admin authentication
checkAdminAuth();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = intval($_GET['id']);

// Get product data for confirmation
try {
    $stmt = $pdo->prepare("SELECT name, featured_image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Error fetching product: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

$page_title = "Delete Product - " . htmlspecialchars($product['name']);
include '../../includes/admin-header.php';

$error = '';
$success = '';

if ($_POST && isset($_POST['confirm_delete'])) {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Get product images for deletion
        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $gallery_images = $stmt->fetchAll();

        // Delete gallery images from filesystem
        foreach ($gallery_images as $image) {
            if (file_exists(UPLOAD_PATH . PRODUCT_IMAGE_PATH . $image['image_url'])) {
                unlink(UPLOAD_PATH . PRODUCT_IMAGE_PATH . $image['image_url']);
            }
        }

        // Delete featured image
        if ($product['featured_image'] && file_exists(UPLOAD_PATH . PRODUCT_IMAGE_PATH . $product['featured_image'])) {
            unlink(UPLOAD_PATH . PRODUCT_IMAGE_PATH . $product['featured_image']);
        }

        // Delete product images from database
        $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->execute([$product_id]);

        // Delete product views
        $stmt = $pdo->prepare("DELETE FROM product_views WHERE product_id = ?");
        $stmt->execute([$product_id]);

        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        // Commit transaction
        $pdo->commit();

        $success = "Product deleted successfully!";

        // Redirect after 2 seconds
        header("Refresh: 2; URL=index.php");
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error deleting product: " . $e->getMessage();
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Delete Product</h1>
            <p class="text-gray-600 mt-1">Permanently remove product from the system</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Products
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
            <p class="ml-4 text-sm">Redirecting to products list...</p>
        </div>
    <?php else: ?>
        <!-- Confirmation Card -->
        <div class="dashboard-card p-6">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>

                <h2 class="text-xl font-bold text-gray-900 mb-2">Confirm Deletion</h2>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    You are about to permanently delete the product
                    <strong>"<?= htmlspecialchars($product['name']) ?>"</strong>.
                    This action cannot be undone and will remove all associated data including images.
                </p>

                <!-- Product Preview -->
                <div class="bg-gray-50 rounded-lg p-4 max-w-xs mx-auto mb-6">
                    <img src="../../assets/uploads/products/<?= $product['featured_image'] ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        class="w-32 h-32 object-cover rounded-lg mx-auto mb-3">
                    <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
                </div>

                <form method="POST" class="space-y-4">
                    <div class="flex items-center justify-center space-x-4">
                        <a href="edit.php?id=<?= $product_id ?>"
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
                        This will also delete all gallery images and view statistics
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/admin-footer.php'; ?>