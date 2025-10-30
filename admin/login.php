<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_POST) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    // Debug: Check what's being received
    error_log("Login attempt - Username: $username, Password length: " . strlen($password));

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Debug: Check if user exists
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_active = TRUE");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin) {
                error_log("User found: " . $admin['username']);
                error_log("Stored hash: " . $admin['password_hash']);
                error_log("Input password: " . $password);

                // Debug password verification
                $password_verified = password_verify($password, $admin['password_hash']);
                error_log("Password verification result: " . ($password_verified ? 'true' : 'false'));

                if ($password_verified) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_full_name'] = $admin['full_name'];

                    // Update last login
                    $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$admin['id']]);

                    header('Location: index.php');
                    exit;
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                error_log("User not found or inactive: $username");
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Database error. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TechHaven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-white text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Admin Login</h2>
            <p class="text-gray-600 mt-2">Access the TechHaven admin panel</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" id="username" name="username" required
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" id="password" name="password" required
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your password">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-700">Remember me</label>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 font-semibold">
                Sign In
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="../index.php" class="text-blue-600 hover:text-blue-500 text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Main Site
            </a>
        </div>

        <!-- Temporary debug link - remove in production -->
        <div class="mt-4 text-center">
            <a href="reset-password.php" class="text-red-600 hover:text-red-500 text-sm">
                Reset Password (Debug)
            </a>
        </div>
    </div>
</body>

</html>