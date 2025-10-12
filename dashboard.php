<?php
/**
 * Dashboard - Legacy page (replaced by index.php)
 * Redirects to home page
 */
define('LOTN_VERSION', '0.6.0');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect to new home page
header("Location: index.php");
exit();
?>