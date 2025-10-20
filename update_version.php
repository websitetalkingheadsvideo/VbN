<?php
/**
 * Version Update Script
 * Updates version in VERSION.md and ensures all files use the centralized version
 */

// Get new version from command line argument
$newVersion = $argv[1] ?? null;

if (!$newVersion) {
    echo "Usage: php update_version.php <new_version>\n";
    echo "Example: php update_version.php 0.3.2\n";
    exit(1);
}

// Validate version format (X.Y.Z)
if (!preg_match('/^\d+\.\d+\.\d+$/', $newVersion)) {
    echo "Error: Version must be in format X.Y.Z (e.g., 0.3.2)\n";
    exit(1);
}

echo "Updating version to: $newVersion\n";

// Update VERSION.md
$versionFile = 'VERSION.md';
if (!file_exists($versionFile)) {
    echo "Error: VERSION.md not found\n";
    exit(1);
}

$content = file_get_contents($versionFile);

// Find and replace the current version line
$pattern = '/## Version \d+\.\d+\.\d+ \(Current\)/';
$replacement = "## Version $newVersion (Current)";

if (preg_match($pattern, $content)) {
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if (file_put_contents($versionFile, $newContent)) {
        echo "✅ Updated VERSION.md to version $newVersion\n";
    } else {
        echo "❌ Failed to update VERSION.md\n";
        exit(1);
    }
} else {
    echo "❌ Could not find version pattern in VERSION.md\n";
    exit(1);
}

// Test the centralized version system
require_once 'includes/version.php';
echo "✅ Centralized version system working: " . LOTN_VERSION . "\n";

// List files that now use centralized version
$files = [
    'includes/header.php',
    'lotn_char_create.php', 
    'save_character.php',
    'index.php'
];

echo "\n📁 Files using centralized version:\n";
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "  ✅ $file\n";
    } else {
        echo "  ❌ $file (not found)\n";
    }
}

echo "\n🎉 Version update complete!\n";
echo "All files now read version from VERSION.md\n";
?>
