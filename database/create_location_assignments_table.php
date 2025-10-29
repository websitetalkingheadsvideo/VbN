<?php
/**
 * Create Location Assignments Junction Table
 * Run this once to create the location_assignments table
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/connect.php';

echo "<h1>🏠 Creating Location Assignments Table</h1>";
echo "<pre>";

try {
    echo "Step 1: Creating location_assignments table...\n\n";
    
    $create_table = "CREATE TABLE IF NOT EXISTS location_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        location_id INT NOT NULL,
        character_id INT NOT NULL,
        assignment_type VARCHAR(50) DEFAULT 'Resident',
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        
        -- Indexes
        INDEX idx_location (location_id),
        INDEX idx_character (character_id),
        INDEX idx_assignment_type (assignment_type),
        
        -- Foreign Keys
        FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE,
        FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
        
        -- Unique constraint to prevent duplicate assignments
        UNIQUE KEY unique_assignment (location_id, character_id)
        
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_table)) {
        echo "✅ location_assignments table created successfully!\n\n";
    } else {
        throw new Exception("Failed to create location_assignments table: " . $conn->error);
    }
    
    echo "Step 2: Verifying table structure...\n";
    $verify = $conn->query("DESCRIBE location_assignments");
    echo "Columns created: " . $verify->num_rows . "\n\n";
    
    echo "🎉 SUCCESS! Location assignments table is ready!\n\n";
    echo "📋 Next steps:\n";
    echo "   1. Test location assignment functionality\n";
    echo "   2. Build location management interface\n";
    echo "   3. Test CRUD operations\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
$conn->close();
?>
