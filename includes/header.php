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

// Include centralized version management
require_once __DIR__ . '/version.php';
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
    // This handles /vbn/ on live server, root on development
    $app_root = dirname($script_name);
    // If in admin folder, remove /admin from path
    if ($in_admin_folder) {
        $app_root = dirname($app_root);
    }
    $app_root = rtrim($app_root, '/') . '/';
    ?>
    <link rel="icon" href="<?php echo $path_prefix; ?>images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>css/global.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English:ital@0;1&family=IM+Fell+English+SC&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Nosifer&family=Source+Serif+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">
    <script src="<?php echo $path_prefix; ?>js/logo-animation.js"></script>
</head>
<body>
<div class="page-wrapper">
    <header class="valley-header">
        <div class="header-container">
            <!-- Logo and Title Section -->
            <div class="header-left">
                <div class="logo-placeholder" title="Valley by Night Logo">
                    <!-- SVG Logo with hover effects (inline for animation support) -->
                    <a href="<?php echo $app_root; ?>index.php" class="logo-link">
                        <svg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg" class="logo-svg">
                          <defs>
                            <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                              <stop offset="0%" style="stop-color:#2a1515;stop-opacity:1" />
                              <stop offset="100%" style="stop-color:#1a0f0f;stop-opacity:1" />
                            </linearGradient>
                            <filter id="shadow">
                              <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#000000" flood-opacity="0.8"/>
                            </filter>
                          </defs>
                          <!-- Background with gradient -->
                          <rect width="80" height="80" fill="url(#bgGradient)" rx="8"/>
                          <!-- Inner shadow effect -->
                          <rect x="3" y="3" width="74" height="74" fill="none" stroke="rgba(0,0,0,0.3)" stroke-width="1" rx="6"/>
                          <!-- Border -->
                          <rect width="80" height="80" fill="none" stroke="#8B0000" stroke-width="3" rx="8" class="logo-border" style="transition: stroke 0.3s ease, filter 0.3s ease;"/>
                          <!-- VbN Text -->
                          <text x="40" y="52" font-family="'IM Fell English', serif" font-size="28" fill="#8B0000" text-anchor="middle" font-weight="bold" letter-spacing="2" filter="url(#shadow)" class="logo-text" style="transition: fill 0.3s ease, filter 0.3s ease;">VbN</text>
                        </svg>
                    </a>
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

