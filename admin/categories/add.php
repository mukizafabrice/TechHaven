<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check admin authentication
checkAdminAuth();

$page_title = "Add New Category";
include '../../includes/admin-header.php';

// Get parent categories for dropdown
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

        // Check if slug already exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            throw new Exception("Slug already exists. Please use a different slug.");
        }

        // Handle category image upload
        $image_url = null;
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadImage($_FILES['image_url'], 'category');
            if (!$upload_result['success']) {
                throw new Exception($upload_result['error']);
            }
            $image_url = $upload_result['filename'];
        }

        // Insert category
        $stmt = $pdo->prepare("
            INSERT INTO categories (name, slug, description, image_url, parent_id, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $slug,
            $description,
            $image_url,
            $parent_id,
            $is_active
        ]);

        $success = "Category added successfully!";
        $_POST = []; // Clear form

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Category</h1>
            <p class="text-gray-600 mt-1">Create a new product category</p>
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
                            value="<?= $_POST['name'] ?? '' ?>"
                            class="form-control" placeholder="Enter category name">
                    </div>

                    <div class="form-group">
                        <label for="slug" class="form-label">Slug *</label>
                        <input type="text" id="slug" name="slug"
                            value="<?= $_POST['slug'] ?? '' ?>"
                            class="form-control" placeholder="category-url-slug">
                        <p class="text-sm text-gray-500 mt-1">URL-friendly version of the name</p>
                    </div>

                    <div class="form-group">
                        <label for="parent_id" class="form-label">Parent Category</label>
                        <select id="parent_id" name="parent_id" class="form-control">
                            <option value="">No Parent (Top Level)</option>
                            <?php foreach ($parent_categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= ($_POST['parent_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Leave empty to create a top-level category</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1"
                                    <?= ($_POST['is_active'] ?? 1) ? 'checked' : '' ?>
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
                        <div class="image-upload">
                            <div class="image-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition duration-200">
                                <input type="file" name="image_url" accept="image/*" class="hidden" id="category_image_input">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to upload category image</p>
                                <p class="text-sm text-gray-500 mt-1">Recommended: 400x400px, JPG, PNG, or WebP</p>
                            </div>
                            <img id="category_image_preview" class="image-preview hidden mt-4 max-w-full rounded-lg">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description"
                    class="form-control" rows="4"
                    placeholder="Describe this category"><?= $_POST['description'] ?? '' ?></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                <a href="index.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">
                    <i class="fas fa-save mr-2"></i> Save Category
                </button>
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
                    categoryImageArea.querySelector('p').textContent = this.files[0].name;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Auto-generate slug from name
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        nameInput.addEventListener('blur', function() {
            if (!slugInput.value) {
                const slug = this.value.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)+/g, '');
                slugInput.value = slug;
            }
        });
    });
</script>

<?php include '../../includes/admin-footer.php'; ?>