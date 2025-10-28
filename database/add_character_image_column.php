<?php
/**
 * Add character_image column to characters table
 * Stores file paths for character portraits
 */

require_once __DIR__ . '/../includes/connect.php';

// SQL to add the column
$sql = "ALTER TABLE characters ADD COLUMN character_image VARCHAR(255) DEFAULT NULL COMMENT 'Path to character portrait image'";

try {
    if ($conn->query($sql)) {
        echo "✅ Successfully added 'character_image' column to 'characters' table.\n";
    } else {
        echo "⚠️ Error: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    // Column might already exist
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "ℹ️ Column 'character_image' already exists. Skipping.\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

$conn->close();
?>

