<?php
/**
 * Create Locations Database Table
 * Run this once to create the locations table
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/connect.php';

echo "<h1>🗺️ Creating Locations Table</h1>";
echo "<pre>";

try {
    echo "Step 1: Creating locations table...\n\n";
    
    $create_table = "CREATE TABLE IF NOT EXISTS locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        
        -- Basic Information
        name VARCHAR(255) NOT NULL,
        type VARCHAR(100) NOT NULL,
        summary VARCHAR(500),
        description TEXT,
        notes TEXT,
        status VARCHAR(50) NOT NULL DEFAULT 'Active',
        status_notes VARCHAR(255),
        
        -- Geography
        district VARCHAR(100),
        address VARCHAR(255),
        latitude DECIMAL(10,8),
        longitude DECIMAL(11,8),
        
        -- Ownership & Control
        owner_type VARCHAR(50) NOT NULL,
        owner_notes TEXT,
        faction VARCHAR(50),
        access_control VARCHAR(50) NOT NULL,
        access_notes TEXT,
        
        -- Security Features
        security_level INT DEFAULT 3,
        security_locks TINYINT(1) DEFAULT 0,
        security_alarms TINYINT(1) DEFAULT 0,
        security_guards TINYINT(1) DEFAULT 0,
        security_hidden_entrance TINYINT(1) DEFAULT 0,
        security_sunlight_protected TINYINT(1) DEFAULT 0,
        security_warding_rituals TINYINT(1) DEFAULT 0,
        security_cameras TINYINT(1) DEFAULT 0,
        security_reinforced TINYINT(1) DEFAULT 0,
        security_notes TEXT,
        
        -- Utility Features
        utility_blood_storage TINYINT(1) DEFAULT 0,
        utility_computers TINYINT(1) DEFAULT 0,
        utility_library TINYINT(1) DEFAULT 0,
        utility_medical TINYINT(1) DEFAULT 0,
        utility_workshop TINYINT(1) DEFAULT 0,
        utility_hidden_caches TINYINT(1) DEFAULT 0,
        utility_armory TINYINT(1) DEFAULT 0,
        utility_communications TINYINT(1) DEFAULT 0,
        utility_notes TEXT,
        
        -- Social Features
        social_features TEXT,
        capacity INT,
        prestige_level INT DEFAULT 0,
        
        -- Supernatural Features
        has_supernatural TINYINT(1) DEFAULT 0,
        node_points INT,
        node_type VARCHAR(50),
        ritual_space TEXT,
        magical_protection TEXT,
        cursed_blessed TEXT,
        
        -- Relationships
        parent_location_id INT,
        relationship_type VARCHAR(50),
        relationship_notes TEXT,
        
        -- Media
        image VARCHAR(255),
        
        -- Meta
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- Indexes
        INDEX idx_type (type),
        INDEX idx_status (status),
        INDEX idx_district (district),
        INDEX idx_owner_type (owner_type),
        INDEX idx_faction (faction),
        INDEX idx_parent (parent_location_id),
        
        -- Foreign Key
        FOREIGN KEY (parent_location_id) REFERENCES locations(id) ON DELETE SET NULL
        
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($create_table)) {
        echo "✅ locations table created successfully!\n\n";
    } else {
        throw new Exception("Failed to create locations table: " . $conn->error);
    }
    
    echo "Step 2: Verifying table structure...\n";
    $verify = $conn->query("DESCRIBE locations");
    echo "Columns created: " . $verify->num_rows . "\n\n";
    
    echo "🎉 SUCCESS! Locations table is ready!\n\n";
    echo "📋 Next steps:\n";
    echo "   1. Create junction tables (location_ownership, location_items)\n";
    echo "   2. Test creating a location via admin_create_location.php\n";
    echo "   3. Build location listing page\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
$conn->close();
?>

