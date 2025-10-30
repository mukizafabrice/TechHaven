<?php
session_start();
require_once '../includes/auth.php';

adminLogout();

// Redirect to login page
header('Location: login.php');
exit;
