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

    <!-- Favicon -->
    <link rel="icon" href="../assets/images/logo.png" type="image/png">

    <!-- Custom CSS -->
    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .product-card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        /* Modern Header Styles */
        .header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .logo-text {
            font-weight: 800;
            font-size: 1.75rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            position: relative;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #fff;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            transition: all 0.3s ease;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        .search-btn {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateX(2px);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
        }

        .dropdown-menu {
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding-left: 1.5rem;
        }

        .mobile-menu-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            transition: all 0.3s ease;
        }

        .mobile-menu-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Modern Header -->
    <header class="sticky top-0 z-50 shadow-lg header-gradient">
        <div class="container px-4 mx-auto">
            <!-- Top Bar -->
            <div class="flex items-center justify-between py-4">
                <!-- Logo Section -->
                <div class="flex items-center flex-shrink-0">
                    <a href="index.php" class="flex items-center space-x-2 group">
                        <div class="p-2 rounded-lg" style="background: rgba(255, 255, 255, 0.15);">
                            <i class="text-2xl text-white fas fa-microchip"></i>
                        </div>
                        <span class="logo-text">Wima Store</span>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="flex-1 hidden max-w-xl mx-6 md:flex">
                    <form action="search.php" method="GET" class="flex w-full">
                        <input type="text" name="q" placeholder="Search electronics, phones, laptops..."
                            class="search-input flex-1 px-4 py-2.5 rounded-l-lg focus:outline-none text-sm">
                        <button type="submit" class="search-btn px-5 py-2.5 text-white rounded-r-lg hover:shadow-lg transition duration-300">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Desktop Navigation -->
                <nav class="items-center hidden space-x-1 lg:flex">
                    <a href="index.php" class="nav-link px-4 py-2 rounded-lg <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                        <i class="mr-2 fas fa-home"></i>Home
                    </a>
                    <div class="relative dropdown group">
                        <button class="nav-link px-4 py-2 rounded-lg flex items-center <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                            <i class="mr-2 fas fa-shopping-bag"></i>Products <i class="ml-1 text-xs transition fas fa-chevron-down group-hover:rotate-180"></i>
                        </button>
                        <div class="absolute left-0 z-50 hidden w-56 py-3 mt-0 bg-white shadow-2xl group-hover:block rounded-xl dropdown-menu top-full">
                            <div class="px-3 py-2 text-xs font-semibold tracking-wider text-gray-500 uppercase">Categories</div>
                            <?php foreach ($categories as $category): ?>
                                <a href="products.php?category=<?= $category['slug'] ?>"
                                    class="flex items-center px-4 py-3 text-gray-700 dropdown-item">
                                    <i class="w-4 mr-3 text-center text-gray-400 fas fa-tag"></i>
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <a href="contact.php" class="nav-link px-4 py-2 rounded-lg <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">
                        <i class="mr-2 fas fa-envelope"></i>Contact
                    </a>
                    <div class="mx-2 border-r border-white border-opacity-20"></div>
                    <a href="../admin/login.php" class="flex items-center px-4 py-2 rounded-lg nav-link hover:bg-white hover:bg-opacity-10">
                        <i class="mr-2 fas fa-user-cog"></i>Admin
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="p-2 rounded-lg lg:hidden mobile-menu-btn">
                    <i class="text-xl fas fa-bars"></i>
                </button>
            </div>

            <!-- Mobile Search Bar -->
            <div class="pb-4 md:hidden">
                <form action="search.php" method="GET" class="flex">
                    <input type="text" name="q" placeholder="Search products..."
                        class="flex-1 px-4 py-2 text-sm rounded-l-lg search-input focus:outline-none">
                    <button type="submit" class="px-4 py-2 text-white rounded-r-lg search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Mobile Navigation Menu -->
            <nav id="mobileMenu" class="hidden pb-4 border-t border-white lg:hidden border-opacity-20">
                <a href="index.php" class="nav-link block px-4 py-3 rounded-lg flex items-center <?php echo $current_page == 'index.php' ? 'active bg-white bg-opacity-10' : ''; ?>">
                    <i class="mr-3 fas fa-home"></i>Home
                </a>
                <a href="products.php" class="nav-link block px-4 py-3 rounded-lg flex items-center <?php echo $current_page == 'products.php' ? 'active bg-white bg-opacity-10' : ''; ?>">
                    <i class="mr-3 fas fa-shopping-bag"></i>Products
                </a>
                <div class="px-4 py-2">
                    <div class="mb-2 text-xs font-semibold tracking-wider text-white uppercase text-opacity-60">Categories</div>
                    <?php foreach ($categories as $category): ?>
                        <a href="products.php?category=<?= $category['slug'] ?>"
                            class="block px-4 py-2 text-sm text-white rounded-lg dropdown-item text-opacity-90">
                            → <?= htmlspecialchars($category['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <a href="contact.php" class="nav-link block px-4 py-3 rounded-lg flex items-center <?php echo $current_page == 'contact.php' ? 'active bg-white bg-opacity-10' : ''; ?>">
                    <i class="mr-3 fas fa-envelope"></i>Contact
                </a>
                <a href="../admin/login.php" class="flex items-center block px-4 py-3 pt-3 mt-2 border-t border-white rounded-lg nav-link border-opacity-20">
                    <i class="mr-3 fas fa-user-cog"></i>Admin Login
                </a>
            </nav>
        </div>
    </header>

    <!-- Mobile Menu Toggle Script -->
    <script>
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                this.innerHTML = mobileMenu.classList.contains('hidden') ?
                    '<i class="text-xl fas fa-bars"></i>' :
                    '<i class="text-xl fas fa-times"></i>';
            });
        }
    </script>

    <!-- Main Content -->
    <main class="min-h-screen">