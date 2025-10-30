<?php

/**
 * Get all active categories
 */
function getCategories($pdo, $parent_id = null)
{
    $sql = "SELECT * FROM categories WHERE is_active = TRUE";
    $params = [];

    if ($parent_id === null) {
        $sql .= " AND parent_id IS NULL";
    } else {
        $sql .= " AND parent_id = ?";
        $params[] = $parent_id;
    }

    $sql .= " ORDER BY name ASC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Get category by ID
 */
function getCategoryById($pdo, $category_id)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND is_active = TRUE");
        $stmt->execute([$category_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching category: " . $e->getMessage());
        return false;
    }
}

/**
 * Get products with optional filters
 */
function getProducts($pdo, $category_id = null, $search = null, $limit = null, $featured = false)
{
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = TRUE";

    $params = [];

    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }

    if ($search) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($featured) {
        $sql .= " AND p.is_featured = TRUE";
    }

    $sql .= " ORDER BY p.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = (int)$limit;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

/**
 * Get single product by slug
 */
function getProductBySlug($pdo, $slug)
{
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.slug = ? AND p.is_active = TRUE");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching product: " . $e->getMessage());
        return false;
    }
}

/**
 * Get product images
 */
function getProductImages($pdo, $product_id)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY display_order ASC");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching product images: " . $e->getMessage());
        return [];
    }
}

/**
 * Track product view
 */
function trackProductView($pdo, $product_id)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    try {
        $stmt = $pdo->prepare("INSERT INTO product_views (product_id, ip_address, user_agent) VALUES (?, ?, ?)");
        $stmt->execute([$product_id, $ip, $user_agent]);
        return true;
    } catch (PDOException $e) {
        error_log("Error tracking product view: " . $e->getMessage());
        return false;
    }
}

/**
 * Format price with currency symbol
 */
function formatPrice($price)
{
    return '$' . number_format($price, 2);
}

/**
 * Calculate discount percentage
 */
function calculateDiscountPercentage($original_price, $discount_price)
{
    if ($discount_price && $original_price > 0) {
        return round((($original_price - $discount_price) / $original_price) * 100);
    }
    return 0;
}

/**
 * Generate WhatsApp share link
 */
function getWhatsAppLink($product)
{
    $message = "Hi, I'm interested in: " . $product['name'] . " - " . SITE_URL . "/public/product-detail.php?slug=" . $product['slug'];
    return "https://wa.me/?text=" . urlencode($message);
}

/**
 * Generate SEO-friendly slug
 */
function generateSlug($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

/**
 * Upload image with validation
 */
function uploadImage($file, $type = 'product')
{
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error'];
    }

    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'Only JPG, PNG, and WebP images are allowed'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File size must be less than 5MB'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;

    if ($type === 'product') {
        $upload_path = UPLOAD_PATH . PRODUCT_IMAGE_PATH . $filename;
    } else {
        $upload_path = UPLOAD_PATH . CATEGORY_IMAGE_PATH . $filename;
    }

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

/**
 * Get dashboard statistics
 */
function getDashboardStats($pdo)
{
    $stats = [];

    try {
        // Total products
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE is_active = TRUE");
        $stats['total_products'] = $stmt->fetch()['total'];

        // Total categories
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories WHERE is_active = TRUE");
        $stats['total_categories'] = $stmt->fetch()['total'];

        // Total views
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM product_views");
        $stats['total_views'] = $stmt->fetch()['total'];

        // Low stock products
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity < 10 AND is_active = TRUE");
        $stats['low_stock'] = $stmt->fetch()['total'];
    } catch (PDOException $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        $stats = ['total_products' => 0, 'total_categories' => 0, 'total_views' => 0, 'low_stock' => 0];
    }

    return $stats;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is admin
 */
function isAdmin()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Redirect to specified URL
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}
