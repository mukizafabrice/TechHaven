<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isset($_GET['slug'])) {
    header('Location: products.php');
    exit;
}

$product_slug = $_GET['slug'];
$product = getProductBySlug($pdo, $product_slug);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Track product view
trackProductView($pdo, $product['id']);

// Get product images and related products
$product_images = getProductImages($pdo, $product['id']);
$related_products = getProducts($pdo, $product['category_id'], null, 4);

$page_title = $product['name'] . " - " . SITE_NAME;
include '../includes/header.php';

$discount_percentage = calculateDiscountPercentage($product['price'], $product['discount_price']);
?>

<div class="container px-4 py-8 mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li><a href="index.php" class="transition duration-300 hover:text-blue-600">Home</a></li>
            <li><i class="text-xs fas fa-chevron-right"></i></li>
            <li><a href="products.php" class="transition duration-300 hover:text-blue-600">Products</a></li>
            <li><i class="text-xs fas fa-chevron-right"></i></li>
            <li><a href="products.php?category=<?= $product['category_slug'] ?>" class="transition duration-300 hover:text-blue-600"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li><i class="text-xs fas fa-chevron-right"></i></li>
            <li class="text-gray-400"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="overflow-hidden bg-white rounded-lg shadow-lg">
        <div class="grid grid-cols-1 gap-8 p-8 lg:grid-cols-2">
            <!-- Product Images -->
            <div>
                <!-- Main Image -->
                <div class="mb-4">
                    <img id="mainImage" src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        class="object-cover w-full rounded-lg h-96">
                </div>

                <!-- Thumbnail Gallery -->
                <?php if (!empty($product_images)): ?>
                    <div class="grid grid-cols-4 gap-2">
                        <div class="border-2 border-blue-500 rounded cursor-pointer">
                            <img src="../assets/uploads/products/<?= $product['featured_image'] ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                                class="object-cover w-full h-20 rounded"
                                onclick="changeMainImage(this.src)">
                        </div>
                        <?php foreach ($product_images as $image): ?>
                            <div class="transition duration-300 border border-gray-200 rounded cursor-pointer hover:border-blue-300">
                                <img src="../assets/uploads/products/<?= $image['image_url'] ?>"
                                    alt="<?= htmlspecialchars($image['alt_text'] ?? $product['name']) ?>"
                                    class="object-cover w-full h-20 rounded"
                                    onclick="changeMainImage(this.src)">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div>
                <span class="text-sm tracking-wide text-gray-500 uppercase"><?= htmlspecialchars($product['category_name']) ?></span>
                <h1 class="mb-4 text-3xl font-bold text-gray-800"><?= htmlspecialchars($product['name']) ?></h1>

                <!-- Price -->
                <div class="flex items-center mb-6 space-x-4">
                    <?php if ($product['discount_price']): ?>
                        <span class="text-3xl font-bold text-blue-600"><?= formatPrice($product['discount_price']) ?></span>
                        <span class="text-xl text-gray-500 line-through"><?= formatPrice($product['price']) ?></span>
                        <span class="px-2 py-1 text-sm font-semibold text-white bg-red-500 rounded">
                            Save <?= formatPrice($product['price'] - $product['discount_price']) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-3xl font-bold text-blue-600"><?= formatPrice($product['price']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Stock Status -->
                <div class="mb-6">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-green-800 bg-green-100 rounded-full">
                            <i class="mr-2 fas fa-check-circle"></i>
                            In Stock (<?= $product['stock_quantity'] ?> available)
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-red-800 bg-red-100 rounded-full">
                            <i class="mr-2 fas fa-times-circle"></i>
                            Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Short Description -->
                <p class="mb-6 text-lg text-gray-600"><?= htmlspecialchars($product['short_description']) ?></p>

                <!-- Actions -->
                <div class="mb-8 space-y-4">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <button onclick="shareOnWhatsApp('<?= $product['slug'] ?>', '<?= addslashes($product['name']) ?>')"
                            class="w-full px-6 py-3 font-semibold text-white transition duration-300 bg-green-600 rounded-lg hover:bg-green-700">
                            <i class="mr-2 fab fa-whatsapp"></i> Contact via WhatsApp
                        </button>
                    <?php else: ?>
                        <button disabled class="w-full px-6 py-3 font-semibold text-white bg-gray-400 rounded-lg cursor-not-allowed">
                            <i class="mr-2 fas fa-ban"></i> Currently Unavailable
                        </button>
                    <?php endif; ?>

                    <div class="flex space-x-4">
                        <button onclick="window.print()" class="flex-1 px-4 py-2 text-gray-700 transition duration-300 bg-gray-200 rounded hover:bg-gray-300">
                            <i class="mr-2 fas fa-print"></i> Print Details
                        </button>
                        <button onclick="shareProduct()" class="flex-1 px-4 py-2 text-gray-700 transition duration-300 bg-gray-200 rounded hover:bg-gray-300">
                            <i class="mr-2 fas fa-share-alt"></i> Share
                        </button>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="pt-6 border-t border-gray-200">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">SKU:</span>
                            <span class="font-medium"><?= $product['sku'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Category:</span>
                            <span class="font-medium"><?= htmlspecialchars($product['category_name']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Added:</span>
                            <span class="font-medium"><?= date('M j, Y', strtotime($product['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Description Tabs -->
        <div class="border-t border-gray-200">
            <div class="px-8">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <button id="description-tab" class="px-1 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-500 tab-button">
                        Description
                    </button>
                    <button id="specs-tab" class="px-1 py-4 text-sm font-medium text-gray-500 border-b-2 border-transparent tab-button hover:text-gray-700">
                        Specifications
                    </button>
                </nav>
            </div>

            <div class="px-8 py-6">
                <div id="description-content" class="tab-content">
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </div>
                </div>

                <div id="specs-content" class="hidden tab-content">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Add specifications here if available in database -->
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Brand</span>
                            <span class="font-medium">TechHaven</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Warranty</span>
                            <span class="font-medium">1 Year</span>
                        </div>
                        <!-- Add more specifications as needed -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <section class="mt-16">
            <h2 class="mb-8 text-2xl font-bold">Related Products</h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <?php foreach ($related_products as $related_product):
                    if ($related_product['id'] == $product['id']) continue;
                    $related_discount = calculateDiscountPercentage($related_product['price'], $related_product['discount_price']);
                ?>
                    <div class="overflow-hidden transition duration-300 bg-white rounded-lg shadow-md hover:shadow-xl product-card">
                        <div class="relative">
                            <img src="../assets/uploads/products/<?= $related_product['featured_image'] ?>"
                                alt="<?= htmlspecialchars($related_product['name']) ?>"
                                class="object-cover w-full h-48">
                            <?php if ($related_discount > 0): ?>
                                <span class="absolute px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded top-2 left-2">
                                    -<?= $related_discount ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="mb-2 text-lg font-semibold line-clamp-2"><?= htmlspecialchars($related_product['name']) ?></h3>

                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <?php if ($related_product['discount_price']): ?>
                                        <span class="text-lg font-bold text-blue-600"><?= formatPrice($related_product['discount_price']) ?></span>
                                        <span class="text-sm text-gray-500 line-through"><?= formatPrice($related_product['price']) ?></span>
                                    <?php else: ?>
                                        <span class="text-lg font-bold text-blue-600"><?= formatPrice($related_product['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <a href="product-detail.php?slug=<?= $related_product['slug'] ?>"
                                class="block w-full py-2 text-sm text-center text-white transition duration-300 bg-blue-600 rounded hover:bg-blue-700">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
    function changeMainImage(src) {
        document.getElementById('mainImage').src = src;
    }

    // Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        const descriptionTab = document.getElementById('description-tab');
        const specsTab = document.getElementById('specs-tab');
        const descriptionContent = document.getElementById('description-content');
        const specsContent = document.getElementById('specs-content');

        descriptionTab.addEventListener('click', function() {
            descriptionTab.classList.add('border-blue-500', 'text-blue-600');
            descriptionTab.classList.remove('border-transparent', 'text-gray-500');
            specsTab.classList.add('border-transparent', 'text-gray-500');
            specsTab.classList.remove('border-blue-500', 'text-blue-600');
            descriptionContent.classList.remove('hidden');
            specsContent.classList.add('hidden');
        });

        specsTab.addEventListener('click', function() {
            specsTab.classList.add('border-blue-500', 'text-blue-600');
            specsTab.classList.remove('border-transparent', 'text-gray-500');
            descriptionTab.classList.add('border-transparent', 'text-gray-500');
            descriptionTab.classList.remove('border-blue-500', 'text-blue-600');
            specsContent.classList.remove('hidden');
            descriptionContent.classList.add('hidden');
        });
    });

    function shareProduct() {
        if (navigator.share) {
            navigator.share({
                title: '<?= addslashes($product['name']) ?>',
                text: 'Check out this product from TechHaven',
                url: window.location.href
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('Product link copied to clipboard!');
            });
        }
    }
</script>

<style>
    .tab-button {
        transition: all 0.3s ease;
    }

    .tab-content {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<?php include '../includes/footer.php'; ?>