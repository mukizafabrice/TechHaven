<?php
// includes/admin-header.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config and functions
require_once 'config.php';
require_once 'functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/login.php');
    exit;
}

$current_admin = getCurrentAdmin($pdo);
if (!$current_admin) {
    header('Location: ../admin/login.php');
    exit;
}

// Get unread message count for the badge
try {
    $unread_stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM contact_messages WHERE status = 'new'");
    $unread_stmt->execute();
    $unread_count = $unread_stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
} catch (Exception $e) {
    $unread_count = 0;
}

// Determine base paths dynamically based on current location
$current_script = $_SERVER['PHP_SELF'];
$admin_base_path = '../admin/';
$public_base_path = '../public/';

// Adjust paths based on current directory
if (strpos($current_script, '/admin/profile/') !== false) {
    $admin_base_path = '../';
    $public_base_path = '../../public/';
} elseif (strpos($current_script, '/admin/products/') !== false) {
    $admin_base_path = '../';
    $public_base_path = '../../public/';
} elseif (strpos($current_script, '/admin/categories/') !== false) {
    $admin_base_path = '../';
    $public_base_path = '../../public/';
} elseif (strpos($current_script, '/admin/messages/') !== false) {
    $admin_base_path = '../';
    $public_base_path = '../../public/';
} elseif (strpos($current_script, '/admin/') !== false) {
    // For files directly in admin folder (like admin/index.php)
    $admin_base_path = './';
    $public_base_path = '../public/';
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin - TechHaven' : 'Admin - TechHaven'; ?></title>

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

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
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

                    <!-- Products Section -->
                    <li>
                        <a href="<?= $admin_base_path ?>products/index.php"
                            class="sidebar-link flex items-center space-x-3 px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300 <?php echo str_contains($_SERVER['REQUEST_URI'], '/products/') ? 'active bg-blue-600' : ''; ?>">
                            <i class="fas fa-box w-5"></i>
                            <span>Products</span>
                        </a>
                    </li>

                    <!-- Categories Section -->
                    <li>
                        <a href="<?= $admin_base_path ?>categories/index.php"
                            class="sidebar-link flex items-center space-x-3 px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300 <?php echo str_contains($_SERVER['REQUEST_URI'], '/categories/') ? 'active bg-blue-600' : ''; ?>">
                            <i class="fas fa-tags w-5"></i>
                            <span>Categories</span>
                        </a>
                    </li>

                    <!-- Messages Section -->
                    <li>
                        <a href="<?= $admin_base_path ?>messages/index.php"
                            class="sidebar-link flex items-center space-x-3 px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300 relative <?php echo str_contains($_SERVER['REQUEST_URI'], '/messages/') ? 'active bg-blue-600' : ''; ?>">
                            <i class="fas fa-envelope w-5"></i>
                            <span>Messages</span>
                            <?php if ($unread_count > 0): ?>
                                <span class="notification-badge"><?= $unread_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Profile Section -->
                    <li>
                        <a href="<?= $admin_base_path ?>profile/index.php"
                            class="sidebar-link flex items-center space-x-3 px-4 py-3 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300 <?php echo str_contains($_SERVER['REQUEST_URI'], '/profile/') ? 'active bg-blue-600' : ''; ?>">
                            <i class="fas fa-user-cog w-5"></i>
                            <span>Profile Settings</span>
                        </a>
                    </li>

                    <!-- Divider -->
                    <li class="pt-4 border-t border-gray-700">
                        <span class="text-xs uppercase text-gray-400 px-4 py-2 block">Account</span>
                    </li>

                    <!-- Logout -->
                    <li>
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
            <header class="bg-white shadow-sm border-b">
                <div class="flex items-center justify-between p-4">
                    <!-- Left Section: Page Title -->
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800"><?= $page_title ?? 'Dashboard' ?></h2>
                        <p class="text-gray-600 text-sm mt-1">Manage your e-commerce store</p>
                    </div>

                    <!-- Right Section: Navigation and User Menu -->
                    <div class="flex items-center space-x-6">
                        <!-- Quick Stats -->
                        <div class="hidden md:flex items-center space-x-4 text-sm text-gray-600">
                            <div class="text-center">
                                <div class="font-semibold text-blue-600"><?= $unread_count ?></div>
                                <div class="text-xs">New Messages</div>
                            </div>
                            <div class="h-6 w-px bg-gray-300"></div>
                            <div class="text-center">
                                <div class="font-semibold text-green-600"><?= date('H:i') ?></div>
                                <div class="text-xs">Local Time</div>
                            </div>
                        </div>

                        <!-- Quick Action Buttons -->
                        <div class="flex items-center space-x-2">
                            <!-- Messages Button -->
                            <a href="<?= $admin_base_path ?>messages/index.php"
                                class="relative bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition duration-300 flex items-center space-x-2">
                                <i class="fas fa-envelope"></i>
                                <span class="hidden sm:inline">Messages</span>
                                <?php if ($unread_count > 0): ?>
                                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                        <?= $unread_count ?>
                                    </span>
                                <?php endif; ?>
                            </a>

                            <!-- View Site Button -->
                            <a href="<?= $public_base_path ?>index.php" target="_blank"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center space-x-2">
                                <i class="fas fa-external-link-alt"></i>
                                <span class="hidden sm:inline">View Site</span>
                            </a>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative" id="userDropdown">
                            <button class="flex items-center space-x-3 bg-gray-100 hover:bg-gray-200 rounded-lg px-3 py-2 transition duration-300">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    <?= strtoupper(substr($current_admin['full_name'] ?? 'A', 0, 1)) ?>
                                </div>
                                <div class="hidden md:block text-left">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($current_admin['full_name'] ?? 'Admin') ?></div>
                                    <div class="text-xs text-gray-500">Administrator</div>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <a href="<?= $admin_base_path ?>profile/index.php"
                                    class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-300">
                                    <i class="fas fa-user-cog w-4 text-gray-400"></i>
                                    <span>Profile Settings</span>
                                </a>
                                <a href="<?= $admin_base_path ?>profile/change-password.php"
                                    class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-300">
                                    <i class="fas fa-key w-4 text-gray-400"></i>
                                    <span>Change Password</span>
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="<?= $public_base_path ?>index.php" target="_blank"
                                    class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-gray-100 transition duration-300">
                                    <i class="fas fa-store w-4 text-gray-400"></i>
                                    <span>Visit Store</span>
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="<?= $admin_base_path ?>logout.php"
                                    class="flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 transition duration-300">
                                    <i class="fas fa-sign-out-alt w-4"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secondary Navigation Bar -->
                <div class="bg-gray-50 border-t border-gray-200 px-4 py-2">
                    <div class="flex items-center justify-between">
                        <!-- Breadcrumb -->
                        <nav class="flex items-center space-x-2 text-sm text-gray-600">
                            <a href="<?= $admin_base_path ?>index.php" class="hover:text-blue-600 transition duration-300">
                                <i class="fas fa-home"></i>
                            </a>
                            <span class="text-gray-400">/</span>
                            <?php if (str_contains($_SERVER['REQUEST_URI'], '/products/')): ?>
                                <a href="<?= $admin_base_path ?>products/index.php" class="hover:text-blue-600 transition duration-300">Products</a>
                            <?php elseif (str_contains($_SERVER['REQUEST_URI'], '/categories/')): ?>
                                <a href="<?= $admin_base_path ?>categories/index.php" class="hover:text-blue-600 transition duration-300">Categories</a>
                            <?php elseif (str_contains($_SERVER['REQUEST_URI'], '/messages/')): ?>
                                <a href="<?= $admin_base_path ?>messages/index.php" class="hover:text-blue-600 transition duration-300">Messages</a>
                            <?php elseif (str_contains($_SERVER['REQUEST_URI'], '/profile/')): ?>
                                <a href="<?= $admin_base_path ?>profile/index.php" class="hover:text-blue-600 transition duration-300">Profile</a>
                            <?php else: ?>
                                <span class="text-gray-800 font-medium">Dashboard</span>
                            <?php endif; ?>

                            <!-- Current Page -->
                            <?php if ($current_page != 'index.php'): ?>
                                <span class="text-gray-400">/</span>
                                <span class="text-gray-800 font-medium"><?= $page_title ?? 'Page' ?></span>
                            <?php endif; ?>
                        </nav>

                        <!-- Quick Actions -->
                        <div class="flex items-center space-x-2">
                            <!-- Refresh Button -->
                            <button onclick="window.location.reload()"
                                class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded-lg hover:bg-gray-50 transition duration-300 flex items-center space-x-1 text-sm">
                                <i class="fas fa-redo-alt text-xs"></i>
                                <span>Refresh</span>
                            </button>

                            <!-- Help Button -->
                            <button
                                class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded-lg hover:bg-gray-50 transition duration-300 flex items-center space-x-1 text-sm">
                                <i class="fas fa-question-circle text-xs"></i>
                                <span>Help</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="p-6">