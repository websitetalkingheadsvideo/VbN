<?php
/**
 * Centralized Version Management
 * Reads version from VERSION.md and provides it as a constant
 */

if (!defined('LOTN_VERSION')) {
    // Read version from VERSION.md file
    $versionFile = __DIR__ . '/../VERSION.md';
    
    if (file_exists($versionFile)) {
        $content = file_get_contents($versionFile);
        
        // Extract version from "## Version X.X.X (Current)" pattern
        if (preg_match('/## Version (\d+\.\d+\.\d+) \(Current\)/', $content, $matches)) {
            define('LOTN_VERSION', $matches[1]);
        } else {
            // Fallback version if parsing fails
            define('LOTN_VERSION', '0.3.1');
        }
    } else {
        // Fallback version if VERSION.md doesn't exist
        define('LOTN_VERSION', '0.3.1');
    }
}

// Also provide a function to get version info
function getVersionInfo() {
    return [
        'version' => LOTN_VERSION,
        'versionFile' => __DIR__ . '/../VERSION.md',
        'lastModified' => file_exists(__DIR__ . '/../VERSION.md') ? 
            date('Y-m-d H:i:s', filemtime(__DIR__ . '/../VERSION.md')) : null
    ];
}
?>
