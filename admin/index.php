<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check admin authentication
checkAdminAuth();

$page_title = "Dashboard";
include '../includes/admin-header.php';

// Get dashboard statistics
$stats = getDashboardStats($pdo);

// Get recent products
$recent_products = getProducts($pdo, null, null, 5);
$recent_views = getRecentViews($pdo, 10);
?>

<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Welcome back, <?= htmlspecialchars($_SESSION['admin_full_name']) ?>! 👋</h1>
                <p class="text-blue-100 mt-2">Here's what's happening with your store today.</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="text-blue-100">Last login: <?= date('M j, Y g:i A', strtotime($_SESSION['admin_last_login'] ?? 'now')) ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="dashboard-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-box text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Products</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?= $stats['total_products'] ?></h3>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-green-600 font-medium">
                    <i class="fas fa-arrow-up mr-1"></i> Active
                </span>
            </div>
        </div>

        <div class="dashboard-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <i class="fas fa-tags text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Categories</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?= $stats['total_categories'] ?></h3>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-blue-600 font-medium">
                    <i class="fas fa-layer-group mr-1"></i> Organized
                </span>
            </div>
        </div>

        <div class="dashboard-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <i class="fas fa-eye text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Views</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?= $stats['total_views'] ?></h3>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-purple-600 font-medium">
                    <i class="fas fa-chart-line mr-1"></i> Engagement
                </span>
            </div>
        </div>

        <div class="dashboard-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Low Stock</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?= $stats['low_stock'] ?></h3>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-orange-600 font-medium">
                    <i class="fas fa-clock mr-1"></i> Needs Attention
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Products -->
        <div class="dashboard-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Recent Products</h3>
                <a href="products/index.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="space-y-4">
                <?php if (!empty($recent_products)): ?>
                    <?php foreach ($recent_products as $product): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                            <div class="flex items-center space-x-4">
                                <img src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    class="w-12 h-12 object-cover rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></h4>
                                    <p class="text-sm text-gray-500"><?= formatPrice($product['price']) ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $product['stock_quantity'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $product['stock_quantity'] ?> in stock
                                </span>
                                <p class="text-xs text-gray-500 mt-1"><?= date('M j', strtotime($product['created_at'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-box-open text-4xl mb-3 text-gray-300"></i>
                        <p>No products added yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Recent Views</h3>
                <span class="text-sm text-gray-500">Last 10 views</span>
            </div>

            <div class="space-y-4">
                <?php if (!empty($recent_views)): ?>
                    <?php foreach ($recent_views as $view): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-900"><?= htmlspecialchars($view['product_name']) ?></h4>
                                <p class="text-sm text-gray-500">IP: <?= $view['ip_address'] ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-900"><?= date('M j, g:i A', strtotime($view['view_date'])) ?></p>
                                <p class="text-xs text-gray-500"><?= $view['user_agent_browser'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-eye-slash text-4xl mb-3 text-gray-300"></i>
                        <p>No views recorded yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="products/add.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition duration-200 group">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4 group-hover:bg-blue-200 transition duration-200">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Add Product</h4>
                    <p class="text-sm text-gray-500">Create new product</p>
                </div>
            </a>

            <a href="categories/add.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition duration-200 group">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4 group-hover:bg-green-200 transition duration-200">
                    <i class="fas fa-folder-plus"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Add Category</h4>
                    <p class="text-sm text-gray-500">Create new category</p>
                </div>
            </a>

            <a href="products/index.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition duration-200 group">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4 group-hover:bg-purple-200 transition duration-200">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Manage Products</h4>
                    <p class="text-sm text-gray-500">View all products</p>
                </div>
            </a>

            <a href="../index.php" target="_blank" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition duration-200 group">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4 group-hover:bg-orange-200 transition duration-200">
                    <i class="fas fa-external-link-alt"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">View Store</h4>
                    <p class="text-sm text-gray-500">Visit main site</p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php
// Helper function to get recent views
function getRecentViews($pdo, $limit = 10)
{
    try {
        $stmt = $pdo->prepare("
            SELECT pv.*, p.name as product_name,
                   SUBSTRING_INDEX(SUBSTRING_INDEX(pv.user_agent, ' ', 2), ' ', -1) as user_agent_browser
            FROM product_views pv
            LEFT JOIN products p ON pv.product_id = p.id
            ORDER BY pv.view_date DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching recent views: " . $e->getMessage());
        return [];
    }
}

include '../includes/admin-footer.php';
?>