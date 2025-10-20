<?php
/**
 * Automated Version Increment Script
 * Follows the project's version increment rules from VERSION.md
 * 
 * Usage: php increment_version.php [patch|minor|major]
 * If no argument provided, defaults to patch increment
 */

// Include the version management
require_once __DIR__ . '/includes/version.php';

// Get increment type from command line
$incrementType = $argv[1] ?? 'patch';

// Validate increment type
if (!in_array($incrementType, ['patch', 'minor', 'major'])) {
    echo "Error: Increment type must be 'patch', 'minor', or 'major'\n";
    echo "Usage: php increment_version.php [patch|minor|major]\n";
    exit(1);
}

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

// Increment based on type
switch ($incrementType) {
    case 'patch':
        $patch++;
        break;
    case 'minor':
        $minor++;
        $patch = 0; // Reset patch to 0
        break;
    case 'major':
        $major++;
        $minor = 0; // Reset minor to 0
        $patch = 0; // Reset patch to 0
        break;
}

$newVersion = "$major.$minor.$patch";
echo "New version: $newVersion ($incrementType increment)\n";

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
    
    // Add new version entry to the top of the changelog
    $newEntry = "## Version $newVersion (Current)\n**Date:** " . date('F j, Y') . "\n\n### Changes:\n- Version increment ($incrementType)\n\n---\n\n";
    
    // Insert new entry after the title
    $newContent = preg_replace('/(# LOTN Character Creator - Version History\n)/', "$1\n$newEntry", $newContent);
    
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
echo "✅ Testing centralized version system...\n";
require_once 'includes/version.php';
echo "✅ Centralized version system working: " . LOTN_VERSION . "\n";

echo "\n🎉 Version increment complete!\n";
echo "Version updated from $currentVersion to $newVersion ($incrementType increment)\n";
echo "All files now read the new version from VERSION.md\n";

// Show version increment rules
echo "\n📋 Version Increment Rules:\n";
echo "• PATCH (Z): Bug fixes, small improvements, work-in-progress features\n";
echo "• MINOR (Y): New WORKING features, complete systems, major UI overhauls\n";
echo "• MAJOR (X): Only when explicitly requested\n";
?>
