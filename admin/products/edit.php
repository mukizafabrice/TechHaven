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

// Get product data
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: index.php');
        exit;
    }

    // Get product images
    $product_images = getProductImages($pdo, $product_id);
} catch (PDOException $e) {
    error_log("Error fetching product: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

$page_title = "Edit Product - " . htmlspecialchars($product['name']);
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
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $meta_title = sanitizeInput($_POST['meta_title']);
        $meta_description = sanitizeInput($_POST['meta_description']);

        // Validate required fields
        if (empty($name) || empty($price) || empty($category_id) || empty($sku)) {
            throw new Exception("Please fill in all required fields.");
        }

        // Check if SKU already exists (excluding current product)
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
        $stmt->execute([$sku, $product_id]);
        if ($stmt->fetch()) {
            throw new Exception("SKU already exists. Please use a unique SKU.");
        }

        // Check if slug already exists (excluding current product)
        $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $product_id]);
        if ($stmt->fetch()) {
            throw new Exception("Slug already exists. Please use a different slug.");
        }

        // Handle featured image upload
        $featured_image = $product['featured_image'];
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadImage($_FILES['featured_image'], 'product');
            if (!$upload_result['success']) {
                throw new Exception($upload_result['error']);
            }
            $featured_image = $upload_result['filename'];

            // Delete old featured image
            if ($product['featured_image'] && file_exists(UPLOAD_PATH . PRODUCT_IMAGE_PATH . $product['featured_image'])) {
                unlink(UPLOAD_PATH . PRODUCT_IMAGE_PATH . $product['featured_image']);
            }
        }

        // Update product
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = ?, slug = ?, description = ?, short_description = ?, price = ?, discount_price = ?,
                category_id = ?, stock_quantity = ?, sku = ?, featured_image = ?, is_featured = ?, 
                is_active = ?, meta_title = ?, meta_description = ?, updated_at = NOW()
            WHERE id = ?
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
            $is_active,
            $meta_title,
            $meta_description,
            $product_id
        ]);

        // Handle gallery images upload
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
                        $display_order = count($product_images) + $key + 1;
                        $stmt = $pdo->prepare("
                            INSERT INTO product_images (product_id, image_url, display_order) 
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$product_id, $upload_result['filename'], $display_order]);
                    }
                }
            }
        }

        // Handle gallery image deletion
        if (!empty($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $image_id) {
                $image_id = intval($image_id);

                // Get image filename before deletion
                $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE id = ?");
                $stmt->execute([$image_id]);
                $image = $stmt->fetch();

                if ($image) {
                    // Delete from database
                    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
                    $stmt->execute([$image_id]);

                    // Delete physical file
                    if (file_exists(UPLOAD_PATH . PRODUCT_IMAGE_PATH . $image['image_url'])) {
                        unlink(UPLOAD_PATH . PRODUCT_IMAGE_PATH . $image['image_url']);
                    }
                }
            }
        }

        // Update product data after changes
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        $product_images = getProductImages($pdo, $product_id);

        $success = "Product updated successfully!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
            <p class="text-gray-600 mt-1">Update product information</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Products
            </a>
            <a href="../../public/product-detail.php?slug=<?= $product['slug'] ?>" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                <i class="fas fa-external-link-alt mr-2"></i> View Product
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
                            value="<?= htmlspecialchars($product['name']) ?>"
                            class="form-control" placeholder="Enter product name">
                    </div>

                    <div class="form-group">
                        <label for="slug" class="form-label">Slug *</label>
                        <input type="text" id="slug" name="slug" required
                            value="<?= htmlspecialchars($product['slug']) ?>"
                            class="form-control" placeholder="product-url-slug">
                        <p class="text-sm text-gray-500 mt-1">URL-friendly version of the name</p>
                    </div>

                    <div class="form-group">
                        <label for="sku" class="form-label">SKU (Stock Keeping Unit) *</label>
                        <input type="text" id="sku" name="sku" required
                            value="<?= htmlspecialchars($product['sku']) ?>"
                            class="form-control" placeholder="PROD-001">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="price" class="form-label">Price ($) *</label>
                            <input type="number" id="price" name="price" required
                                value="<?= $product['price'] ?>" step="0.01" min="0"
                                class="form-control" placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label for="discount_price" class="form-label">Discount Price ($)</label>
                            <input type="number" id="discount_price" name="discount_price"
                                value="<?= $product['discount_price'] ?>" step="0.01" min="0"
                                class="form-control" placeholder="0.00">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="category_id" class="form-label">Category *</label>
                            <select id="category_id" name="category_id" required class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" required
                                value="<?= $product['stock_quantity'] ?>" min="0"
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
                                <input type="file" name="featured_image" accept="image/*" class="hidden" id="featured_image_input">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to change featured image</p>
                                <p class="text-sm text-gray-500 mt-1">Recommended: 800x800px, JPG, PNG, or WebP</p>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                                <img src="../../assets/uploads/products/<?= $product['featured_image'] ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    class="image-preview max-w-full rounded-lg border">
                            </div>
                        </div>
                    </div>

                    <!-- Gallery Images -->
                    <div class="form-group">
                        <label class="form-label">Gallery Images</label>

                        <!-- Current Gallery Images -->
                        <?php if (!empty($product_images)): ?>
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">Current Gallery Images:</p>
                                <div class="grid grid-cols-4 gap-2">
                                    <?php foreach ($product_images as $image): ?>
                                        <div class="relative group">
                                            <img src="../../assets/uploads/products/<?= $image['image_url'] ?>"
                                                alt="Gallery image"
                                                class="w-full h-20 object-cover rounded-lg border">
                                            <label class="absolute top-1 right-1">
                                                <input type="checkbox" name="delete_images[]" value="<?= $image['id'] ?>"
                                                    class="hidden peer">
                                                <div class="w-6 h-6 bg-white rounded border border-gray-300 flex items-center justify-center peer-checked:bg-red-500 peer-checked:border-red-500 cursor-pointer transition duration-200">
                                                    <i class="fas fa-times text-xs peer-checked:text-white text-gray-400"></i>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Check the X to delete images</p>
                            </div>
                        <?php endif; ?>

                        <!-- New Gallery Images Upload -->
                        <div class="image-upload">
                            <div class="image-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition duration-200">
                                <input type="file" name="gallery_images[]" accept="image/*" multiple class="hidden" id="gallery_images_input">
                                <i class="fas fa-images text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to add more gallery images</p>
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
                                    <?= $product['is_featured'] ? 'checked' : '' ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-gray-700">Feature this product</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1"
                                    <?= $product['is_active'] ? 'checked' : '' ?>
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-gray-700">Product is active</span>
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
                    placeholder="Brief description displayed in product listings"><?= htmlspecialchars($product['short_description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Full Description *</label>
                <textarea id="description" name="description" required
                    class="form-control rich-text-editor" rows="8"
                    placeholder="Detailed product description"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <!-- SEO Fields -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Settings</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div class="form-group">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title"
                            value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>"
                            class="form-control" placeholder="SEO title for search engines">
                    </div>

                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" name="meta_description"
                            class="form-control" rows="3"
                            placeholder="SEO description for search engines"><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Product Stats -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Statistics</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="font-semibold text-gray-900">Created</div>
                        <div class="text-gray-600"><?= date('M j, Y', strtotime($product['created_at'])) ?></div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="font-semibold text-gray-900">Last Updated</div>
                        <div class="text-gray-600"><?= date('M j, Y', strtotime($product['updated_at'])) ?></div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="font-semibold text-gray-900">Views</div>
                        <div class="text-gray-600">
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_views WHERE product_id = ?");
                            $stmt->execute([$product_id]);
                            echo $stmt->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="font-semibold text-gray-900">Status</div>
                        <div class="<?= $product['is_active'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t">
                <div>
                    <a href="?delete=<?= $product_id ?>"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 delete-btn"
                        onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                        <i class="fas fa-trash mr-2"></i> Delete Product
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">
                        <i class="fas fa-save mr-2"></i> Update Product
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Featured image preview
        const featuredInput = document.getElementById('featured_image_input');
        const featuredArea = document.querySelector('.image-upload-area');

        featuredArea.addEventListener('click', () => featuredInput.click());

        featuredInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.image-preview');
                    preview.src = e.target.result;
                    featuredArea.querySelector('p').textContent = 'New image selected: ' + this.files[0].name;
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
            Array.from(this.files).forEach(file => {
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-full h-20 object-cover rounded-lg border';
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
            if (this.value !== '<?= addslashes($product['name']) ?>' && !slugInput.dataset.manual) {
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
    });
</script>

<?php include '../../includes/admin-footer.php'; ?>