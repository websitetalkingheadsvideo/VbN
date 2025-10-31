<?php
/**
 * Migration Script: Add Coterie and Relationships Fields
 * 
 * Creates database tables for character coteries and relationships
 * Based on analysis findings from JSON character files
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once __DIR__ . '/../includes/connect.php';

echo "=================================================================\n";
echo "Coterie & Relationships Migration\n";
echo "=================================================================\n\n";

try {
    $conn->begin_transaction();
    
    // Create character_coteries table
    echo "Creating character_coteries table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS character_coteries (
        id INT PRIMARY KEY AUTO_INCREMENT,
        character_id INT NOT NULL,
        coterie_name VARCHAR(255) NOT NULL,
        coterie_type VARCHAR(50) DEFAULT NULL,
        role VARCHAR(100) DEFAULT NULL,
        description TEXT DEFAULT NULL,
        source_field VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
        INDEX idx_character_coteries (character_id),
        INDEX idx_coterie_name (coterie_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "✅ character_coteries table created successfully\n\n";
    } else {
        throw new Exception("Failed to create character_coteries table: " . $conn->error);
    }
    
    // Create character_relationships table
    echo "Creating character_relationships table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS character_relationships (
        id INT PRIMARY KEY AUTO_INCREMENT,
        character_id INT NOT NULL,
        related_character_id INT DEFAULT NULL,
        related_character_name VARCHAR(255) NOT NULL,
        relationship_type VARCHAR(50) NOT NULL,
        relationship_subtype VARCHAR(50) DEFAULT NULL,
        strength VARCHAR(100) DEFAULT NULL,
        description TEXT DEFAULT NULL,
        source_field VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
        FOREIGN KEY (related_character_id) REFERENCES characters(id) ON DELETE SET NULL,
        INDEX idx_character_relationships (character_id),
        INDEX idx_related_character (related_character_id),
        INDEX idx_relationship_type (relationship_type),
        INDEX idx_related_name (related_character_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "✅ character_relationships table created successfully\n\n";
    } else {
        throw new Exception("Failed to create character_relationships table: " . $conn->error);
    }
    
    // Add Coterie and Relationships JSON columns to characters table
    echo "Adding Coterie and Relationships JSON columns to characters table...\n";
    
    // Check if Coterie column already exists
    $checkSql = "SHOW COLUMNS FROM characters LIKE 'Coterie'";
    $result = $conn->query($checkSql);
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE characters ADD COLUMN Coterie JSON DEFAULT NULL";
        if ($conn->query($sql)) {
            echo "✅ Added Coterie JSON column to characters table\n";
        } else {
            echo "⚠️  Warning: Could not add Coterie column: " . $conn->error . "\n";
        }
    } else {
        echo "⚠️  Coterie column already exists\n";
    }
    
    // Check if Relationships column already exists
    $checkSql = "SHOW COLUMNS FROM characters LIKE 'Relationships'";
    $result = $conn->query($checkSql);
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE characters ADD COLUMN Relationships JSON DEFAULT NULL";
        if ($conn->query($sql)) {
            echo "✅ Added Relationships JSON column to characters table\n\n";
        } else {
            echo "⚠️  Warning: Could not add Relationships column: " . $conn->error . "\n\n";
        }
    } else {
        echo "⚠️  Relationships column already exists\n\n";
    }
    
    $conn->commit();
    
    echo "=================================================================\n";
    echo "Migration completed successfully!\n";
    echo "=================================================================\n\n";
    echo "Next steps:\n";
    echo "1. Run the data extraction script to extract data from JSON files\n";
    echo "2. Run populate_coterie_relationships.php to import the data\n\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();

