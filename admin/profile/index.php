<?php
// Include necessary files for POST handling
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$current_admin = getCurrentAdmin($pdo);
if (!$current_admin) {
    header('Location: ../login.php');
    exit;
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);

    $result = updateAdminProfile($pdo, $current_admin['id'], $full_name, $email);

    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        // Refresh admin data
        $current_admin = getCurrentAdmin($pdo);
        header('Location: index.php');
        exit;
    } else {
        $error_message = $result['message'];
    }
}

$page_title = "Profile Settings";
include '../../includes/admin-header.php';

// Display success message from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<div class="container px-4 py-6 mx-auto">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Profile Settings</h1>
            <p class="mt-2 text-gray-600">Manage your account information and preferences</p>
        </div>

        <!-- Notifications -->
        <?php if (isset($success_message)): ?>
            <div class="flex items-center px-4 py-3 mb-6 text-green-700 border border-green-200 rounded-lg bg-green-50">
                <i class="mr-3 text-green-500 fas fa-check-circle"></i>
                <span><?= $success_message ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="flex items-center px-4 py-3 mb-6 text-red-700 border border-red-200 rounded-lg bg-red-50">
                <i class="mr-3 text-red-500 fas fa-exclamation-circle"></i>
                <span><?= $error_message ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Left Column - Profile Information -->
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Personal Information</h2>
                        <p class="mt-1 text-sm text-gray-600">Update your basic profile information</p>
                    </div>

                    <div class="p-6">
                        <form method="POST" id="profileForm">
                            <div class="space-y-6">
                                <!-- Full Name -->
                                <div>
                                    <label for="full_name" class="block mb-2 text-sm font-medium text-gray-700">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="full_name" name="full_name"
                                        value="<?= htmlspecialchars($current_admin['full_name']) ?>"
                                        class="w-full px-4 py-3 transition duration-300 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                        placeholder="Enter your full name">
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block mb-2 text-sm font-medium text-gray-700">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" id="email" name="email"
                                        value="<?= htmlspecialchars($current_admin['email']) ?>"
                                        class="w-full px-4 py-3 transition duration-300 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                        placeholder="Enter your email address">
                                </div>

                                <!-- Username (Read-only) -->
                                <div>
                                    <label for="username" class="block mb-2 text-sm font-medium text-gray-700">
                                        Username
                                    </label>
                                    <input type="text" id="username"
                                        value="<?= htmlspecialchars($current_admin['username']) ?>"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg cursor-not-allowed bg-gray-50"
                                        disabled>
                                    <p class="mt-2 text-sm text-gray-500">
                                        <i class="mr-1 fas fa-info-circle"></i>
                                        Username cannot be changed for security reasons.
                                    </p>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end pt-4">
                                    <button type="submit" name="update_profile"
                                        class="flex items-center px-8 py-3 font-medium text-white transition duration-300 bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <i class="mr-2 fas fa-save"></i>
                                        Update Profile
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="mt-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Account Information</h2>
                        <p class="mt-1 text-sm text-gray-600">Your account details and activity</p>
                    </div>

                    <div class="p-6">
                        <dl class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <dt class="flex items-center text-sm font-medium text-gray-500">
                                    <i class="mr-2 fas fa-user-shield"></i>
                                    Admin ID
                                </dt>
                                <dd class="font-mono text-sm text-gray-900">#<?= $current_admin['id'] ?></dd>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <dt class="flex items-center text-sm font-medium text-gray-500">
                                    <i class="mr-2 fas fa-calendar-plus"></i>
                                    Member since
                                </dt>
                                <dd class="text-sm text-gray-900"><?= date('F j, Y', strtotime($current_admin['created_at'])) ?></dd>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <dt class="flex items-center text-sm font-medium text-gray-500">
                                    <i class="mr-2 fas fa-clock"></i>
                                    Last login
                                </dt>
                                <dd class="text-sm text-gray-900">
                                    <?= $current_admin['last_login'] ?
                                        date('F j, Y g:i A', strtotime($current_admin['last_login'])) :
                                        '<span class="text-gray-400">Never logged in</span>' ?>
                                </dd>
                            </div>

                            <div class="flex items-center justify-between py-3">
                                <dt class="flex items-center text-sm font-medium text-gray-500">
                                    <i class="mr-2 fas fa-circle"></i>
                                    Account status
                                </dt>
                                <dd class="text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="mr-1 fas fa-check"></i>
                                        Active
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Right Column - Quick Actions -->
            <div class="lg:col-span-1">
                <!-- Profile Card -->
                <div class="p-6 mb-6 text-center bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 text-2xl font-bold text-white rounded-full bg-gradient-to-br from-blue-500 to-purple-600">
                        <?= strtoupper(substr($current_admin['full_name'], 0, 1)) ?>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($current_admin['full_name']) ?></h3>
                    <p class="mt-1 text-sm text-gray-600">Administrator</p>
                    <p class="mt-2 text-xs text-gray-500"><?= htmlspecialchars($current_admin['email']) ?></p>
                </div>

                <!-- Quick Actions -->
                <div class="space-y-4">
                    <a href="change-password.php"
                        class="flex items-center p-4 transition duration-300 bg-white border border-gray-200 shadow-sm rounded-xl hover:shadow-md hover:border-blue-300 group">
                        <div class="flex items-center justify-center w-12 h-12 transition duration-300 bg-blue-100 rounded-lg group-hover:bg-blue-200">
                            <i class="text-lg text-blue-600 fas fa-key"></i>
                        </div>
                        <div class="ml-4 text-left">
                            <h3 class="font-semibold text-gray-900">Change Password</h3>
                            <p class="mt-1 text-sm text-gray-500">Update your account password</p>
                        </div>
                        <i class="ml-auto text-gray-400 transition duration-300 fas fa-chevron-right group-hover:text-blue-600"></i>
                    </a>

                    <a href="../messages/index.php"
                        class="flex items-center p-4 transition duration-300 bg-white border border-gray-200 shadow-sm rounded-xl hover:shadow-md hover:border-green-300 group">
                        <div class="flex items-center justify-center w-12 h-12 transition duration-300 bg-green-100 rounded-lg group-hover:bg-green-200">
                            <i class="text-lg text-green-600 fas fa-envelope"></i>
                        </div>
                        <div class="ml-4 text-left">
                            <h3 class="font-semibold text-gray-900">View Messages</h3>
                            <p class="mt-1 text-sm text-gray-500">Check customer inquiries</p>
                        </div>
                        <i class="ml-auto text-gray-400 transition duration-300 fas fa-chevron-right group-hover:text-green-600"></i>
                    </a>

                    <a href="../index.php"
                        class="flex items-center p-4 transition duration-300 bg-white border border-gray-200 shadow-sm rounded-xl hover:shadow-md hover:border-purple-300 group">
                        <div class="flex items-center justify-center w-12 h-12 transition duration-300 bg-purple-100 rounded-lg group-hover:bg-purple-200">
                            <i class="text-lg text-purple-600 fas fa-tachometer-alt"></i>
                        </div>
                        <div class="ml-4 text-left">
                            <h3 class="font-semibold text-gray-900">Dashboard</h3>
                            <p class="mt-1 text-sm text-gray-500">Back to main dashboard</p>
                        </div>
                        <i class="ml-auto text-gray-400 transition duration-300 fas fa-chevron-right group-hover:text-purple-600"></i>
                    </a>
                </div>

                <!-- Security Tips -->
                <div class="p-4 mt-6 border border-yellow-200 bg-yellow-50 rounded-xl">
                    <h4 class="flex items-center font-semibold text-yellow-800">
                        <i class="mr-2 fas fa-shield-alt"></i>
                        Security Tips
                    </h4>
                    <ul class="mt-2 space-y-1 text-sm text-yellow-700">
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-2 mt-0.5 text-xs"></i>
                            Use a strong, unique password
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-2 mt-0.5 text-xs"></i>
                            Never share your login credentials
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-2 mt-0.5 text-xs"></i>
                            Log out when not using the admin panel
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileForm = document.getElementById('profileForm');

        profileForm.addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();

            // Basic validation
            if (!fullName) {
                e.preventDefault();
                showNotification('Please enter your full name.', 'error');
                return;
            }

            if (!email) {
                e.preventDefault();
                showNotification('Please enter your email address.', 'error');
                return;
            }

            if (!isValidEmail(email)) {
                e.preventDefault();
                showNotification('Please enter a valid email address.', 'error');
                return;
            }
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showNotification(message, type) {
            // You can enhance this with a proper notification system
            alert(message);
        }
    });
</script>

<?php include '../../includes/admin-footer.php'; ?>