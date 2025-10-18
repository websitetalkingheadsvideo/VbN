<?php
/**
 * Valley by Night - Header Component
 * Displays site title, logo, username, and version
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get username from session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Get version - check multiple locations for the constant
if (!defined('LOTN_VERSION')) {
    define('LOTN_VERSION', '0.2.4');
}
$version = LOTN_VERSION;

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valley by Night - A Vampire Tale</title>
    <?php
    // Determine the base path of the application
    $script_name = $_SERVER['SCRIPT_NAME'];
    
    // If we're in admin subfolder, go back one level for CSS
    $in_admin_folder = (strpos($script_name, '/admin/') !== false);
    $path_prefix = $in_admin_folder ? '../' : '';
    
    // Get the application root path (everything before the filename)
    // This handles /vbn/ on live server, root on localhost
    $app_root = dirname($script_name);
    // If in admin folder, remove /admin from path
    if ($in_admin_folder) {
        $app_root = dirname($app_root);
    }
    $app_root = rtrim($app_root, '/') . '/';
    ?>
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>css/global.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English:ital@0;1&family=IM+Fell+English+SC&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Nosifer&family=Source+Serif+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">
</head>
<body>
<div class="page-wrapper">
    <header class="valley-header">
        <div class="header-container">
            <!-- Logo and Title Section -->
            <div class="header-left">
                <div class="logo-placeholder" title="Valley by Night Logo">
                    <!-- Placeholder for logo - 80x80px -->
                    <div class="logo-frame">
                        <span class="logo-initial">VbN</span>
                    </div>
                </div>
                <div class="title-section">
                    <h1 class="site-title">
                        <a href="<?php echo $app_root; ?>index.php">Valley by Night</a>
                    </h1>
                    <p class="site-subtitle">A Vampire Tale</p>
                </div>
            </div>
            
            <!-- User Info Section -->
            <div class="header-right">
                <div class="user-info">
                    <span class="user-label">Kindred:</span>
                    <span class="username"><?php echo htmlspecialchars($username); ?></span>
                    <a href="<?php echo $app_root; ?>logout.php" class="logout-btn" title="Logout">Logout</a>
                </div>
                <div class="version-info">
                    <span class="version">v<?php echo htmlspecialchars($version); ?></span>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main content starts below header -->
    <main class="main-wrapper">

