<?php
/**
 * Setup Items Database Tables
 * Run this once to create the necessary tables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🗄️ VbN Items Database Setup</h1>";
echo "<pre>";

try {
    // Load database connection
    require_once 'includes/connect.php';
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    echo "✅ Database connection successful\n\n";
    
    // Create items table
    echo "📦 Creating 'items' table...\n";
    
    $items_table = "CREATE TABLE IF NOT EXISTS items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(100) NOT NULL,
        category VARCHAR(100) NOT NULL,
        damage VARCHAR(100),
        `range` VARCHAR(100),
        requirements JSON,
        description TEXT,
        rarity VARCHAR(50),
        price INT,
        image VARCHAR(255),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_type (type),
        INDEX idx_category (category),
        INDEX idx_rarity (rarity),
        INDEX idx_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($items_table)) {
        echo "✅ 'items' table created successfully\n";
    } else {
        throw new Exception("Failed to create items table: " . $conn->error);
    }
    
    // Create character_equipment table
    echo "\n📦 Creating 'character_equipment' table...\n";
    
    $equipment_table = "CREATE TABLE IF NOT EXISTS character_equipment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        character_id INT NOT NULL,
        item_id INT NOT NULL,
        quantity INT DEFAULT 1,
        equipped TINYINT(1) DEFAULT 0,
        custom_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
        INDEX idx_character (character_id),
        INDEX idx_item (item_id),
        INDEX idx_equipped (equipped)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($equipment_table)) {
        echo "✅ 'character_equipment' table created successfully\n";
    } else {
        throw new Exception("Failed to create character_equipment table: " . $conn->error);
    }
    
    // Verify tables exist
    echo "\n🔍 Verifying tables...\n";
    
    $verify = $conn->query("SHOW TABLES LIKE 'items'");
    if ($verify->num_rows > 0) {
        echo "✅ 'items' table exists\n";
    }
    
    $verify = $conn->query("SHOW TABLES LIKE 'character_equipment'");
    if ($verify->num_rows > 0) {
        echo "✅ 'character_equipment' table exists\n";
    }
    
    echo "\n🎉 <strong>SUCCESS!</strong> Database tables created successfully!\n\n";
    echo "📋 Next steps:\n";
    echo "   1. Run import_items.php to populate the items table\n";
    echo "   2. Test the API at api_items.php\n\n";
    echo "✨ You can now safely delete this setup file.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "</pre>";

if (isset($conn)) {
    $conn->close();
}
?>

