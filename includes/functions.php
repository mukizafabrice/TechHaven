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


/**
 * Calculate discount percentage
 */
// function calculateDiscountPercentage($original_price, $discount_price)
// {
//     if ($discount_price && $original_price > 0) {
//         return round((($original_price - $discount_price) / $original_price) * 100);
//     }
//     return 0;
// }

/**
 * Ge
 * nerate WhatsApp share link
 */
// In config.php
define('WHATSAPP_NUMBER', '250780088390');
function getWhatsAppLink($product)
{
    // Get WhatsApp number from config
    $whatsapp_number = defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '250780088390';

    // Create the message
    $message = "Hi, I'm interested in: " . $product['name'] . " - " . SITE_URL . "/public/product-detail.php?slug=" . $product['slug'];

    // Encode the message
    $encoded_message = urlencode($message);

    // WhatsApp URL with phone number
    return "https://wa.me/" . $whatsapp_number . "?text=" . $encoded_message;
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
/**
 * Get products with optional filters - DEBUG VERSION
 */
/**
 * Get products with optional filters - DEBUG VERSION
 */
function getProducts($pdo, $category_id = null, $search = null, $limit = null, $featured = false)
{
    // Build the base query
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

    // FIX: Make sure featured filter works correctly
    if ($featured === true) {
        $sql .= " AND p.is_featured = TRUE";
        error_log("FEATURED FILTER APPLIED: Looking for featured products");
    }

    $sql .= " ORDER BY p.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = (int)$limit;
    }

    // DEBUG: Log the query and parameters
    error_log("Products Query: " . $sql);
    error_log("Query Params: " . implode(', ', $params));
    error_log("Featured parameter: " . ($featured ? 'TRUE' : 'FALSE'));

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        error_log("Found " . count($results) . " products");
        if (count($results) > 0) {
            error_log("First product: " . $results[0]['name'] .
                " (Featured: " . $results[0]['is_featured'] .
                ", Active: " . $results[0]['is_active'] . ")");
        } else {
            error_log("No products found with current filters");
        }

        return $results;
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        error_log("SQL Error: " . $e->getMessage());
        return [];
    }
}
/**
 * Format price with currency symbol
 */
function formatPrice($price)
{
    // Ensure price is a valid number
    $price = floatval($price);
    if ($price <= 0) {
        return '$0.00';
    }
    return '$' . number_format($price, 2);
}
function calculateDiscountPercentage($original_price, $discount_price)
{
    $original_price = floatval($original_price);
    $discount_price = floatval($discount_price);

    if ($discount_price > 0 && $original_price > 0 && $discount_price < $original_price) {
        return round((($original_price - $discount_price) / $original_price) * 100);
    }
    return 0;
}






/**
 * Get current admin data
 */
function getCurrentAdmin($pdo)
{
    if (!isset($_SESSION['admin_id'])) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ? AND is_active = TRUE");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting admin data: " . $e->getMessage());
        return null;
    }
}

/**
 * Update admin profile
 */
function updateAdminProfile($pdo, $admin_id, $full_name, $email)
{
    try {
        // Check if email already exists for another admin
        $check_stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $check_stmt->execute([$email, $admin_id]);

        if ($check_stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists for another admin.'];
        }

        // Update profile
        $stmt = $pdo->prepare("UPDATE admins SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$full_name, $email, $admin_id]);

        return ['success' => true, 'message' => 'Profile updated successfully!'];
    } catch (Exception $e) {
        error_log("Error updating admin profile: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()];
    }
}

/**
 * Change admin password
 */
function changeAdminPassword($pdo, $admin_id, $current_password, $new_password, $confirm_password)
{
    try {
        // Get current password hash
        $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            return ['success' => false, 'message' => 'Admin not found.'];
        }

        // Verify current password
        if (!password_verify($current_password, $admin['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        // Check if new passwords match
        if ($new_password !== $confirm_password) {
            return ['success' => false, 'message' => 'New passwords do not match.'];
        }

        // Validate new password strength
        if (strlen($new_password) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters long.'];
        }

        // Hash new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $update_stmt = $pdo->prepare("UPDATE admins SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $update_stmt->execute([$new_password_hash, $admin_id]);

        return ['success' => true, 'message' => 'Password changed successfully!'];
    } catch (Exception $e) {
        error_log("Error changing admin password: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error changing password: ' . $e->getMessage()];
    }
}

/**
 * Sanitize input data
 */
// function sanitizeInput($data)
// {
//     return htmlspecialchars(strip_tags(trim($data)));
// }
