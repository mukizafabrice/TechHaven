<?php
// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Include config if not already included
if (!isset($pdo)) {
    require_once 'config.php';
}

$current_admin = getCurrentAdmin($pdo);
$current_page = basename($_SERVER['PHP_SELF']);

// Determine base path for admin navigation
$admin_base_path = './';
if (strpos($_SERVER['PHP_SELF'], '/admin/products/') !== false) {
    $admin_base_path = '../';
} elseif (strpos($_SERVER['PHP_SELF'], '/admin/categories/') !== false) {
    $admin_base_path = '../';
}

// Public site base path (always the same)
$public_base_path = '../public/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin - ' . SITE_NAME : 'Admin - ' . SITE_NAME; ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Admin CSS -->
    <style>
        .admin-sidebar {
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
        }

        .sidebar-link {
            transition: all 0.3s ease;
        }

        .sidebar-link.active {
            background-color: #3b82f6;
            color: white;
        }

        .sidebar-link:hover:not(.active) {
            background-color: #f3f4f6;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Mobile Menu Button -->
    <button id="mobileMenuButton" class="md:hidden fixed top-4 left-4 z-50 bg-blue-600 text-white p-3 rounded-lg shadow-lg">
        <i class="fas fa-bars"></i>
    </button>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="adminSidebar" class="admin-sidebar bg-gray-800 text-white w-64 fixed h-full z-40">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-700">
                <h1 class="text-xl font-bold text-white">TechHaven Admin</h1>
                <p class="text-sm text-gray-300 mt-1">Welcome, <?= htmlspecialchars($current_admin['full_name'] ?? 'Admin') ?></p>
            </div>

            <!-- Navigation -->
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="<?= $admin_base_path ?>index.php"
                            class="sidebar-link flex items-center space-x-3 px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300 <?php echo $current_page == 'index.php' ? 'active bg-blue-600' : ''; ?>">
                            <i class="fas fa-chart-line w-5"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $admin_base_path ?>products/index.php"
                            class="sidebar-link flex items-center space-x-3 px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300 <?php echo str_contains($_SERVER['REQUEST_URI'], '/products/') ? 'active bg-blue-600' : ''; ?>">
                            <i class="fas fa-box w-5"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $admin_base_path ?>categories/index.php"
                            class="sidebar-link flex items-center space-x-3 px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300 <?php echo str_contains($_SERVER['REQUEST_URI'], '/categories/') ? 'active bg-blue-600' : ''; ?>">
                            <i class="fas fa-tags w-5"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="pt-4 border-t border-gray-700">
                        <a href="<?= $admin_base_path ?>logout.php"
                            class="sidebar-link flex items-center space-x-3 px-4 py-3 text-gray-300 rounded-lg hover:bg-red-600 transition duration-300">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800"><?= $page_title ?? 'Dashboard' ?></h2>
                        <p class="text-gray-600 text-sm mt-1">Manage your e-commerce store</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700"><?= date('F j, Y') ?></span>
                        <a href="<?= $public_base_path ?>index.php" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                            <i class="fas fa-external-link-alt mr-2"></i>View Site
                        </a>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="p-6"></div>