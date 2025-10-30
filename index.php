<?php

/**
 * TechHaven - Auto Redirect to Public Directory
 * This file automatically redirects visitors to the main website
 */

// Permanent redirect to public directory
header("HTTP/1.1 301 Moved Permanently");
header("Location: public/");
exit;

// If redirect fails, show message
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting - TechHaven</title>
    <script>
        window.location.href = "public/";
    </script>
</head>
<body>
    <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
        <h1>TechHaven</h1>
        <p>Redirecting to main website...</p>
        <p>If you are not redirected automatically, <a href="public/">click here</a>.</p>
    </div>
</body>
</html>';
