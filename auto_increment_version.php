<?php
/**
 * Auto Version Increment
 * Called by AI assistant when user says "increment version"
 * Automatically determines increment type based on context
 */

// Include version management
require_once __DIR__ . '/includes/version.php';

// Get current version
$currentVersion = LOTN_VERSION;
echo "Current version: $currentVersion\n";

// Parse current version
if (!preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $currentVersion, $matches)) {
    echo "Error: Could not parse current version format\n";
    exit(1);
}

$major = (int)$matches[1];
$minor = (int)$matches[2];
$patch = (int)$matches[3];

// Default to patch increment (most common)
$incrementType = 'patch';
$newVersion = "$major." . ($minor) . "." . ($patch + 1);

echo "Auto-incrementing PATCH version to: $newVersion\n";

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
    
    // Add new version entry
    $newEntry = "## Version $newVersion (Current)\n**Date:** " . date('F j, Y') . "\n\n### Changes:\n- Auto-increment patch version\n\n---\n\n";
    $newContent = preg_replace('/(# LOTN Character Creator - Version History\n)/', "$1\n$newEntry", $newContent);
    
    if (file_put_contents($versionFile, $newContent)) {
        echo "✅ Updated VERSION.md to version $newVersion\n";
        echo "✅ Version increment complete!\n";
    } else {
        echo "❌ Failed to update VERSION.md\n";
        exit(1);
    }
} else {
    echo "❌ Could not find version pattern in VERSION.md\n";
    exit(1);
}
?>
