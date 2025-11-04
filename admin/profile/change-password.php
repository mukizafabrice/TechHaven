<?php
$page_title = "Change Password";
include '../../includes/admin-header.php';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $result = changeAdminPassword($pdo, $current_admin['id'], $current_password, $new_password, $confirm_password);

    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        // Use JavaScript redirect instead of header()
        echo '<script>window.location.href = "change-password.php";</script>';
        exit;
    } else {
        $error_message = $result['message'];
    }
}

// Display success message from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-4">
                <a href="index.php" class="hover:text-blue-600 transition duration-300">Profile</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 font-medium">Change Password</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-800">Change Password</h1>
            <p class="text-gray-600 mt-2">Update your account password for enhanced security</p>
        </div>

        <!-- Notifications -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span><?= $success_message ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span><?= $error_message ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Password Settings</h2>
                <p class="text-sm text-gray-600 mt-1">Create a strong, unique password to protect your account</p>
            </div>

            <div class="p-6">
                <form method="POST" id="passwordForm">
                    <div class="space-y-6">
                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Current Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="current_password" name="current_password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 pr-10"
                                    required
                                    placeholder="Enter your current password">
                                <button type="button"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                                    onclick="togglePasswordVisibility('current_password')">
                                    <i class="fas fa-eye" id="current_password_icon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                New Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="new_password" name="new_password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 pr-10"
                                    required
                                    placeholder="Enter new password"
                                    minlength="8">
                                <button type="button"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                                    onclick="togglePasswordVisibility('new_password')">
                                    <i class="fas fa-eye" id="new_password_icon"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <div class="flex items-center space-x-2 text-xs text-gray-500">
                                    <span id="length" class="flex items-center">
                                        <i class="fas fa-circle mr-1 text-gray-300"></i>
                                        At least 8 characters
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm New Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="confirm_password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300 pr-10"
                                    required
                                    placeholder="Confirm new password"
                                    minlength="8">
                                <button type="button"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                                    onclick="togglePasswordVisibility('confirm_password')">
                                    <i class="fas fa-eye" id="confirm_password_icon"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <span id="password_match" class="text-xs text-gray-500 flex items-center">
                                    <i class="fas fa-circle mr-1 text-gray-300"></i>
                                    Passwords must match
                                </span>
                            </div>
                        </div>

                        <!-- Password Strength Meter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Password Strength
                            </label>
                            <div class="bg-gray-200 rounded-full h-2">
                                <div id="password_strength" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <div id="password_feedback" class="text-xs text-gray-500 mt-1"></div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-4">
                            <a href="index.php"
                                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-300 font-medium">
                                Cancel
                            </a>
                            <button type="submit" name="change_password"
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 font-medium flex items-center">
                                <i class="fas fa-key mr-2"></i>
                                Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mt-8">
            <h3 class="font-semibold text-blue-800 flex items-center mb-3">
                <i class="fas fa-shield-alt mr-2"></i>
                Password Security Tips
            </h3>
            <ul class="text-sm text-blue-700 space-y-2">
                <li class="flex items-start">
                    <i class="fas fa-check text-blue-500 mr-2 mt-0.5"></i>
                    Use at least 8 characters with a mix of letters, numbers, and symbols
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check text-blue-500 mr-2 mt-0.5"></i>
                    Avoid using personal information like your name or birthdate
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check text-blue-500 mr-2 mt-0.5"></i>
                    Don't reuse passwords from other accounts
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check text-blue-500 mr-2 mt-0.5"></i>
                    Consider using a password manager for better security
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function checkPasswordStrength(password) {
        let strength = 0;
        const feedback = document.getElementById('password_feedback');
        const strengthBar = document.getElementById('password_strength');

        if (password.length >= 8) {
            strength += 25;
            document.getElementById('length').innerHTML = '<i class="fas fa-check-circle mr-1 text-green-500"></i>At least 8 characters';
        } else {
            document.getElementById('length').innerHTML = '<i class="fas fa-circle mr-1 text-gray-300"></i>At least 8 characters';
        }

        if (password.match(/[a-z]+/)) strength += 25;
        if (password.match(/[A-Z]+/)) strength += 25;
        if (password.match(/[0-9]+/)) strength += 25;
        if (password.match(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/)) strength += 25;

        // Update strength bar
        strengthBar.style.width = strength + '%';

        // Update colors and feedback
        if (strength < 50) {
            strengthBar.className = 'h-2 rounded-full bg-red-500 transition-all duration-300';
            feedback.textContent = 'Weak password';
            feedback.className = 'text-xs text-red-500 mt-1';
        } else if (strength < 75) {
            strengthBar.className = 'h-2 rounded-full bg-yellow-500 transition-all duration-300';
            feedback.textContent = 'Medium strength';
            feedback.className = 'text-xs text-yellow-500 mt-1';
        } else {
            strengthBar.className = 'h-2 rounded-full bg-green-500 transition-all duration-300';
            feedback.textContent = 'Strong password';
            feedback.className = 'text-xs text-green-500 mt-1';
        }
    }

    function checkPasswordMatch() {
        const password = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const matchElement = document.getElementById('password_match');

        if (confirmPassword === '') {
            matchElement.innerHTML = '<i class="fas fa-circle mr-1 text-gray-300"></i>Passwords must match';
            matchElement.className = 'text-xs text-gray-500 flex items-center';
        } else if (password === confirmPassword) {
            matchElement.innerHTML = '<i class="fas fa-check-circle mr-1 text-green-500"></i>Passwords match';
            matchElement.className = 'text-xs text-green-500 flex items-center';
        } else {
            matchElement.innerHTML = '<i class="fas fa-times-circle mr-1 text-red-500"></i>Passwords do not match';
            matchElement.className = 'text-xs text-red-500 flex items-center';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const newPasswordField = document.getElementById('new_password');
        const confirmPasswordField = document.getElementById('confirm_password');
        const passwordForm = document.getElementById('passwordForm');

        newPasswordField.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });

        confirmPasswordField.addEventListener('input', checkPasswordMatch);

        passwordForm.addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = newPasswordField.value;
            const confirmPassword = confirmPasswordField.value;

            if (!currentPassword) {
                e.preventDefault();
                alert('Please enter your current password.');
                return;
            }

            if (newPassword.length < 8) {
                e.preventDefault();
                alert('New password must be at least 8 characters long.');
                return;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match.');
                return;
            }
        });
    });
</script>

<?php include '../includes/admin_footer.php'; ?>