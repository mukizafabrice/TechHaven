<?php

/**
 * Admin authentication functions
 */

// Check if admin is logged in
function checkAdminAuth()
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Admin login function
function adminLogin($pdo, $username, $password)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_full_name'] = $admin['full_name'];

            // Update last login
            $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$admin['id']]);

            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Admin login error: " . $e->getMessage());
        return false;
    }
}

// Admin logout function
function adminLogout()
{
    session_destroy();
    session_start();
}

// Get current admin info
function getCurrentAdmin($pdo)
{
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching admin data: " . $e->getMessage());
        return false;
    }
}
