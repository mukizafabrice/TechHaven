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

// Get category data
try {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               (SELECT name FROM categories WHERE id = c.parent_id) as parent_name,
               COUNT(p.id) as product_count
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

$page_title = "Edit Category - " . htmlspecialchars($category['name']);
include '../../includes/admin-header.php';

// Get parent categories for dropdown (excluding current category and its children)
$parent_categories = getCategories($pdo);

$error = '';
$success = '';

if ($_POST) {
    try {
        // Sanitize input data
        $name = sanitizeInput($_POST['name']);
        $slug = generateSlug($_POST['slug'] ?: $_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Validate required fields
        if (empty($name)) {
            throw new Exception("Category name is required.");
        }

        // Check if slug already exists (excluding current category)
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $category_id]);
        if ($stmt->fetch()) {
            throw new Exception("Slug already exists. Please use a different slug.");
        }

        // Prevent circular parent relationships
        if ($parent_id == $category_id) {
            throw new Exception("Category cannot be its own parent.");
        }

        // Check if parent is a descendant (prevent infinite loops)
        if ($parent_id) {
            $stmt = $pdo->prepare("WITH RECURSIVE CategoryTree AS (
                SELECT id, parent_id FROM categories WHERE id = ?
                UNION ALL
                SELECT c.id, c.parent_id FROM categories c
                INNER JOIN CategoryTree ct ON c.parent_id = ct.id
            ) SELECT id FROM CategoryTree WHERE id = ?");
            $stmt->execute([$parent_id, $category_id]);
            if ($stmt->fetch()) {
                throw new Exception("Cannot set parent category. This would create a circular relationship.");
            }
        }

        // Handle category image upload
        $image_url = $category['image_url'];
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadImage($_FILES['image_url'], 'category');
            if (!$upload_result['success']) {
                throw new Exception($upload_result['error']);
            }
            $image_url = $upload_result['filename'];

            // Delete old image if it exists
            if ($category['image_url'] && file_exists(UPLOAD_PATH . CATEGORY_IMAGE_PATH . $category['image_url'])) {
                unlink(UPLOAD_PATH . CATEGORY_IMAGE_PATH . $category['image_url']);
            }
        }

        // Handle image removal
        if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            if ($category['image_url'] && file_exists(UPLOAD_PATH . CATEGORY_IMAGE_PATH . $category['image_url'])) {
                unlink(UPLOAD_PATH . CATEGORY_IMAGE_PATH . $category['image_url']);
            }
            $image_url = null;
        }

        // Update category
        $stmt = $pdo->prepare("
            UPDATE categories 
            SET name = ?, slug = ?, description = ?, image_url = ?, parent_id = ?, is_active = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $name,
            $slug,
            $description,
            $image_url,
            $parent_id,
            $is_active,
            $category_id
        ]);

        // Update category data after changes
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch();

        $success = "Category updated successfully!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Category</h1>
            <p class="text-gray-600 mt-1">Update category information</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Categories
            </a>
            <a href="../../public/products.php?category=<?= $category['slug'] ?>" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                <i class="fas fa-external-link-alt mr-2"></i> View Category
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
        </div>
    <?php endif; ?>

    <!-- Category Form -->
    <div class="dashboard-card p-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div class="form-group">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" id="name" name="name" required
                            value="<?= htmlspecialchars($category['name']) ?>"
                            class="form-control" placeholder="Enter category name">
                    </div>

                    <div class="form-group">
                        <label for="slug" class="form-label">Slug *</label>
                        <input type="text" id="slug" name="slug" required
                            value="<?= htmlspecialchars($category['slug']) ?>"
                            class="form-control" placeholder="category-url-slug">
                        <p class="text-sm text-gray-500 mt-1">URL-friendly version of the name</p>
                    </div>

                    <div class="form-group">
                        <label for="parent_id" class="form-label">Parent Category</label>
                        <select id="parent_id" name="parent_id" class="form-control">
                            <option value="">No Parent (Top Level)</option>
                            <?php foreach ($parent_categories as $parent_category):
                                // Skip current category and its descendants
                                if ($parent_category['id'] == $category_id) continue;
                            ?>
                                <option value="<?= $parent_category['id'] ?>" <?= $category['parent_id'] == $parent_category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($parent_category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Leave empty to make this a top-level category</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1"
                                    <?= $category['is_active'] ? 'checked' : '' ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-gray-700">Category is active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Category Image -->
                    <div class="form-group">
                        <label class="form-label">Category Image</label>

                        <!-- Current Image -->
                        <?php if ($category['image_url']): ?>
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                                <div class="flex items-center space-x-4">
                                    <img src="../../assets/uploads/categories/<?= $category['image_url'] ?>"
                                        alt="<?= htmlspecialchars($category['name']) ?>"
                                        class="w-32 h-32 object-cover rounded-lg border">
                                    <div>
                                        <label class="flex items-center text-sm text-red-600 cursor-pointer">
                                            <input type="checkbox" name="remove_image" value="1" class="hidden peer">
                                            <div class="w-5 h-5 border border-red-300 rounded mr-2 flex items-center justify-center peer-checked:bg-red-500 peer-checked:border-red-500 transition duration-200">
                                                <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                                            </div>
                                            Remove Image
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- New Image Upload -->
                        <div class="image-upload">
                            <div class="image-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition duration-200">
                                <input type="file" name="image_url" accept="image/*" class="hidden" id="category_image_input">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to <?= $category['image_url'] ? 'change' : 'upload' ?> category image</p>
                                <p class="text-sm text-gray-500 mt-1">Recommended: 400x400px, JPG, PNG, or WebP</p>
                            </div>
                            <img id="category_image_preview" class="image-preview hidden mt-4 max-w-full rounded-lg">
                        </div>
                    </div>

                    <!-- Category Stats -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Category Statistics</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Products in Category:</span>
                                <span class="font-medium"><?= $category['product_count'] ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Created:</span>
                                <span class="font-medium"><?= date('M j, Y', strtotime($category['created_at'])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Parent Category:</span>
                                <span class="font-medium"><?= $category['parent_name'] ?: 'None' ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="<?= $category['is_active'] ? 'text-green-600' : 'text-red-600' ?> font-medium">
                                    <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description"
                    class="form-control" rows="4"
                    placeholder="Describe this category"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t">
                <div>
                    <a href="?delete=<?= $category_id ?>"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 delete-btn"
                        onclick="return confirm('Are you sure you want to delete this category? This will make all products in this category uncategorized.')">
                        <i class="fas fa-trash mr-2"></i> Delete Category
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">
                        <i class="fas fa-save mr-2"></i> Update Category
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Category image preview
        const categoryImageInput = document.getElementById('category_image_input');
        const categoryImagePreview = document.getElementById('category_image_preview');
        const categoryImageArea = document.querySelector('.image-upload-area');

        categoryImageArea.addEventListener('click', () => categoryImageInput.click());

        categoryImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    categoryImagePreview.src = e.target.result;
                    categoryImagePreview.classList.remove('hidden');
                    categoryImageArea.querySelector('p').textContent = 'New image selected: ' + this.files[0].name;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Auto-generate slug from name
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        nameInput.addEventListener('blur', function() {
            if (this.value !== '<?= addslashes($category['name']) ?>' && !slugInput.dataset.manual) {
                const slug = this.value.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)+/g, '');
                slugInput.value = slug;
            }
        });

        // Mark slug as manually edited
        slugInput.addEventListener('input', function() {
            this.dataset.manual = 'true';
        });

        // Prevent self-parenting
        const parentSelect = document.getElementById('parent_id');
        parentSelect.addEventListener('change', function() {
            if (this.value === '<?= $category_id ?>') {
                alert('Category cannot be its own parent.');
                this.value = '';
            }
        });
    });
</script>

<?php include '../../includes/admin-footer.php'; ?>