<?php
require_once 'includes/connect.php';

echo "Checking database tables...\n";

try {
    // Check what tables exist
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Existing tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Check if disciplines table exists and has data
    if (in_array('disciplines', $tables)) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM disciplines");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "\nDisciplines table has $count records\n";
        
        if ($count > 0) {
            $stmt = $conn->query("SELECT id, name FROM disciplines LIMIT 5");
            $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Sample disciplines:\n";
            foreach ($samples as $sample) {
                echo "- ID {$sample['id']}: {$sample['name']}\n";
            }
        }
    } else {
        echo "\nDisciplines table does not exist!\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn = null;
?>
