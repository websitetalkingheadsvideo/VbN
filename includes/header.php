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
    define('LOTN_VERSION', '0.4.0');
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
    <link rel="stylesheet" href="css/style_reorganized_structure.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English:ital@0;1&family=IM+Fell+English+SC&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Nosifer&family=Source+Serif+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">
    <style>
        /* Reset body to prevent column layout */
        body {
            display: block !important;
            flex-direction: column !important;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .page-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
    </style>
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
                        <a href="index.php">Valley by Night</a>
                    </h1>
                    <p class="site-subtitle">A Vampire Tale</p>
                </div>
            </div>
            
            <!-- User Info Section -->
            <div class="header-right">
                <div class="user-info">
                    <span class="user-label">Kindred:</span>
                    <span class="username"><?php echo htmlspecialchars($username); ?></span>
                </div>
                <div class="version-info">
                    <span class="version">v<?php echo htmlspecialchars($version); ?></span>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main content starts below header -->
    <main class="main-wrapper">

