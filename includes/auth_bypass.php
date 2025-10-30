<?php
/**
 * Authentication Bypass Helper
 * Allows temporary bypass of authentication checks for site analysis/testing
 */

function isAuthBypassEnabled() {
    $bypassFile = __DIR__ . '/../config/auth_bypass.json';
    
    if (!file_exists($bypassFile)) {
        return false;
    }
    
    $config = json_decode(file_get_contents($bypassFile), true);
    if (!$config || !isset($config['enabled']) || $config['enabled'] !== true) {
        return false;
    }
    
    // Check if bypass period has expired
    if (isset($config['enabled_until'])) {
        $now = time();
        $until = strtotime($config['enabled_until']);
        if ($now >= $until) {
            // Expired - disable bypass
            $config['enabled'] = false;
            $config['enabled_until'] = null;
            file_put_contents($bypassFile, json_encode($config, JSON_PRETTY_PRINT));
            return false;
        }
    }
    
    return true;
}

function setupBypassSession() {
    // Set up mock session values for bypass mode
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 0; // Guest user ID
        $_SESSION['username'] = 'Guest';
        $_SESSION['role'] = 'player';
    }
}

