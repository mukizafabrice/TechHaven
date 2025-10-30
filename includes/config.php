<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'techhaven_db');
define('DB_USER', 'root'); // Change as per your setup
define('DB_PASS', ''); // Change as per your setup

// Site configuration
define('SITE_NAME', 'TechHaven');
define('SITE_URL', 'http://localhost/techhaven');
define('UPLOAD_PATH', dirname(__DIR__) . '/assets/uploads/');
define('PRODUCT_IMAGE_PATH', 'products/');
define('CATEGORY_IMAGE_PATH', 'categories/');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_PATH . PRODUCT_IMAGE_PATH)) {
    mkdir(UPLOAD_PATH . PRODUCT_IMAGE_PATH, 0777, true);
}
if (!file_exists(UPLOAD_PATH . CATEGORY_IMAGE_PATH)) {
    mkdir(UPLOAD_PATH . CATEGORY_IMAGE_PATH, 0777, true);
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Include other core files
require_once 'functions.php';
