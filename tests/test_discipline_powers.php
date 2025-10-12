<?php
// Test script to check if discipline powers table was created
require_once 'includes/connect.php';

echo "Testing discipline powers table...\n";

try {
    // Check if table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'discipline_powers'");
    $table_exists = $stmt->rowCount() > 0;
    
    if ($table_exists) {
        echo "✓ discipline_powers table exists\n";
        
        // Count records
        $stmt = $conn->query("SELECT COUNT(*) as count FROM discipline_powers");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "✓ Found $count discipline powers\n";
        
        // Show sample records
        $stmt = $conn->query("SELECT dp.power_name, d.name as discipline_name, dp.power_level 
                             FROM discipline_powers dp 
                             JOIN disciplines d ON dp.discipline_id = d.id 
                             ORDER BY d.name, dp.power_level 
                             LIMIT 10");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nSample records:\n";
        foreach ($samples as $sample) {
            echo "- {$sample['discipline_name']} {$sample['power_level']}: {$sample['power_name']}\n";
        }
        
    } else {
        echo "✗ discipline_powers table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

$conn = null;
?>
