<?php
// Include config if not already included
if (!isset($pdo)) {
    require_once 'config.php';
}

$categories = getCategories($pdo);
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .product-card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="container px-4 mx-auto">
            <div class="flex items-center justify-between py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <a href="index.php" class="text-2xl font-bold text-blue-600">TechHaven</a>
                </div>

                <!-- Search Bar -->
                <div class="flex-1 max-w-2xl mx-8">
                    <form action="search.php" method="GET" class="flex">
                        <input type="text" name="q" placeholder="Search products..."
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" class="px-6 py-2 text-white transition duration-300 bg-blue-600 rounded-r-lg hover:bg-blue-700">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Navigation -->
                <nav class="flex items-center space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 transition duration-300 <?php echo $current_page == 'index.php' ? 'text-blue-600 font-semibold' : ''; ?>">
                        Home
                    </a>
                    <div class="relative dropdown">
                        <a href="products.php" class="text-gray-700 hover:text-blue-600 transition duration-300 <?php echo $current_page == 'products.php' ? 'text-blue-600 font-semibold' : ''; ?>">
                            Products <i class="ml-1 text-xs fas fa-chevron-down"></i>
                        </a>
                        <div class="absolute z-50 hidden w-48 py-2 mt-2 bg-white rounded-lg shadow-lg dropdown-menu">
                            <?php foreach ($categories as $category): ?>
                                <a href="products.php?category=<?= $category['slug'] ?>"
                                    class="block px-4 py-2 text-gray-700 transition duration-300 hover:bg-blue-50 hover:text-blue-600">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <a href="contact.php" class="text-gray-700 hover:text-blue-600 transition duration-300 <?php echo $current_page == 'contact.php' ? 'text-blue-600 font-semibold' : ''; ?>">
                        Contact
                    </a>

                    <!-- Admin Login Link (visible to all) -->
                    <a href="../admin/login.php" class="text-gray-700 transition duration-300 hover:text-blue-600">
                        <i class="fas fa-user-cog"></i> Admin
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen">