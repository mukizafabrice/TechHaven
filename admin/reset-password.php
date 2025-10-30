<?php
require_once '../includes/config.php';

// This is a one-time script to reset the admin password
// DELETE THIS FILE AFTER USE FOR SECURITY

if ($_POST) {
    $username = 'admin';
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the new password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the admin password
        $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
        if ($stmt->execute([$password_hash, $username])) {
            $success = "Password reset successfully!";
        } else {
            $error = "Error resetting password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-center mb-6">Reset Admin Password</h2>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $success ?>
                <p class="mt-2 text-sm">You can now login with the new password.</p>
                <a href="login.php" class="inline-block mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Go to Login
                </a>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                <input type="password" name="new_password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 focus:outline-none">
                Reset Password
            </button>
        </form>

        <div class="mt-4 text-center text-sm text-red-600">
            <strong>Security Notice:</strong> Delete this file after use!
        </div>
    </div>
</body>

</html>