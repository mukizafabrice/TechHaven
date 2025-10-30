<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check admin authentication
checkAdminAuth();

$page_title = "Add New Product";
include '../../includes/admin-header.php';

// Get categories for dropdown
$categories = getCategories($pdo);

$error = '';
$success = '';

if ($_POST) {
    try {
        // Sanitize input data
        $name = sanitizeInput($_POST['name']);
        $slug = generateSlug($_POST['slug'] ?: $_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $short_description = sanitizeInput($_POST['short_description']);
        $price = floatval($_POST['price']);
        $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
        $category_id = intval($_POST['category_id']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $sku = sanitizeInput($_POST['sku']);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $meta_title = sanitizeInput($_POST['meta_title']);
        $meta_description = sanitizeInput($_POST['meta_description']);

        // Validate required fields
        if (empty($name) || empty($price) || empty($category_id) || empty($sku)) {
            throw new Exception("Please fill in all required fields.");
        }

        // Check if SKU already exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->execute([$sku]);
        if ($stmt->fetch()) {
            throw new Exception("SKU already exists. Please use a unique SKU.");
        }

        // Check if slug already exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            throw new Exception("Slug already exists. Please use a different slug.");
        }

        // Handle featured image upload
        $featured_image = '';
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadImage($_FILES['featured_image'], 'product');
            if (!$upload_result['success']) {
                throw new Exception($upload_result['error']);
            }
            $featured_image = $upload_result['filename'];
        } else {
            throw new Exception("Featured image is required.");
        }

        // Insert product
        $stmt = $pdo->prepare("
            INSERT INTO products (name, slug, description, short_description, price, discount_price, 
                                category_id, stock_quantity, sku, featured_image, is_featured, 
                                meta_title, meta_description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $slug,
            $description,
            $short_description,
            $price,
            $discount_price,
            $category_id,
            $stock_quantity,
            $sku,
            $featured_image,
            $is_featured,
            $meta_title,
            $meta_description
        ]);

        $product_id = $pdo->lastInsertId();

        // Handle gallery images
        if (!empty($_FILES['gallery_images']['name'][0])) {
            foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $upload_result = uploadImage([
                        'name' => $_FILES['gallery_images']['name'][$key],
                        'type' => $_FILES['gallery_images']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['gallery_images']['error'][$key],
                        'size' => $_FILES['gallery_images']['size'][$key]
                    ], 'product');

                    if ($upload_result['success']) {
                        $stmt = $pdo->prepare("
                            INSERT INTO product_images (product_id, image_url, display_order) 
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$product_id, $upload_result['filename'], $key]);
                    }
                }
            }
        }

        $success = "Product added successfully!";
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
            <h1 class="text-2xl font-bold text-gray-900">Add New Product</h1>
            <p class="text-gray-600 mt-1">Create a new product listing</p>
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
        </div>
    <?php endif; ?>

    <!-- Product Form -->
    <div class="dashboard-card p-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div class="form-group">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" id="name" name="name" required
                            value="<?= $_POST['name'] ?? '' ?>"
                            class="form-control" placeholder="Enter product name">
                    </div>

                    <div class="form-group">
                        <label for="slug" class="form-label">Slug *</label>
                        <input type="text" id="slug" name="slug"
                            value="<?= $_POST['slug'] ?? '' ?>"
                            class="form-control" placeholder="product-url-slug">
                        <p class="text-sm text-gray-500 mt-1">URL-friendly version of the name</p>
                    </div>

                    <div class="form-group">
                        <label for="sku" class="form-label">SKU (Stock Keeping Unit) *</label>
                        <input type="text" id="sku" name="sku" required
                            value="<?= $_POST['sku'] ?? '' ?>"
                            class="form-control" placeholder="PROD-001">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="price" class="form-label">Price ($) *</label>
                            <input type="number" id="price" name="price" required
                                value="<?= $_POST['price'] ?? '' ?>" step="0.01" min="0"
                                class="form-control" placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label for="discount_price" class="form-label">Discount Price ($)</label>
                            <input type="number" id="discount_price" name="discount_price"
                                value="<?= $_POST['discount_price'] ?? '' ?>" step="0.01" min="0"
                                class="form-control" placeholder="0.00">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="category_id" class="form-label">Category *</label>
                            <select id="category_id" name="category_id" required class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" required
                                value="<?= $_POST['stock_quantity'] ?? 0 ?>" min="0"
                                class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Featured Image -->
                    <div class="form-group">
                        <label class="form-label">Featured Image *</label>
                        <div class="image-upload">
                            <div class="image-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition duration-200">
                                <input type="file" name="featured_image" accept="image/*" required class="hidden" id="featured_image_input">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to upload featured image</p>
                                <p class="text-sm text-gray-500 mt-1">Recommended: 800x800px, JPG, PNG, or WebP</p>
                            </div>
                            <img id="featured_image_preview" class="image-preview hidden mt-4 max-w-full rounded-lg">
                        </div>
                    </div>

                    <!-- Gallery Images -->
                    <div class="form-group">
                        <label class="form-label">Gallery Images</label>
                        <div class="image-upload">
                            <div class="image-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition duration-200">
                                <input type="file" name="gallery_images[]" accept="image/*" multiple class="hidden" id="gallery_images_input">
                                <i class="fas fa-images text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to upload gallery images</p>
                                <p class="text-sm text-gray-500 mt-1">You can select multiple images</p>
                            </div>
                            <div id="gallery_preview" class="grid grid-cols-4 gap-2 mt-4"></div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="form-group">
                        <label class="form-label">Product Settings</label>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_featured" value="1"
                                    <?= ($_POST['is_featured'] ?? '') ? 'checked' : '' ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-gray-700">Feature this product</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descriptions -->
            <div class="form-group">
                <label for="short_description" class="form-label">Short Description *</label>
                <textarea id="short_description" name="short_description" required
                    class="form-control" rows="3"
                    placeholder="Brief description displayed in product listings"><?= $_POST['short_description'] ?? '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Full Description *</label>
                <textarea id="description" name="description" required
                    class="form-control rich-text-editor" rows="8"
                    placeholder="Detailed product description"><?= $_POST['description'] ?? '' ?></textarea>
            </div>

            <!-- SEO Fields -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Settings</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div class="form-group">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title"
                            value="<?= $_POST['meta_title'] ?? '' ?>"
                            class="form-control" placeholder="SEO title for search engines">
                    </div>

                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description"
                            class="form-control" rows="3"
                            placeholder="SEO description for search engines"><?= $_POST['meta_description'] ?? '' ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                <a href="index.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">
                    <i class="fas fa-save mr-2"></i> Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Featured image preview
        const featuredInput = document.getElementById('featured_image_input');
        const featuredPreview = document.getElementById('featured_image_preview');
        const featuredArea = document.querySelector('.image-upload-area');

        featuredArea.addEventListener('click', () => featuredInput.click());

        featuredInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    featuredPreview.src = e.target.result;
                    featuredPreview.classList.remove('hidden');
                    featuredArea.querySelector('p').textContent = this.files[0].name;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Gallery images preview
        const galleryInput = document.getElementById('gallery_images_input');
        const galleryPreview = document.getElementById('gallery_preview');
        const galleryArea = galleryInput.parentNode;

        galleryArea.addEventListener('click', () => galleryInput.click());

        galleryInput.addEventListener('change', function() {
            galleryPreview.innerHTML = '';

            Array.from(this.files).forEach(file => {
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-full h-20 object-cover rounded-lg';
                        galleryPreview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
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

        // Auto-generate SKU
        const skuInput = document.getElementById('sku');
        if (!skuInput.value) {
            skuInput.value = 'PROD-' + Date.now();
        }
    });
</script>

<?php include '../../includes/admin-footer.php'; ?>