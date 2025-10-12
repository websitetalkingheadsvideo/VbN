<?php
require_once 'includes/connect.php';

echo "<h2>Database Status Check</h2>";

try {
    // Show all tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Existing Tables:</h3><ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check if disciplines table exists and show its structure
    if (in_array('disciplines', $tables)) {
        echo "<h3>Disciplines Table Structure:</h3>";
        $stmt = $conn->query("DESCRIBE disciplines");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
        }
        echo "</table>";
        
        // Count records
        $stmt = $conn->query("SELECT COUNT(*) as count FROM disciplines");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p>Disciplines table has $count records</p>";
    }
    
    // Check if discipline_powers table exists
    if (in_array('discipline_powers', $tables)) {
        echo "<h3>Discipline Powers Table:</h3>";
        $stmt = $conn->query("SELECT COUNT(*) as count FROM discipline_powers");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p>Discipline powers table has $count records</p>";
    } else {
        echo "<p>Discipline powers table does not exist</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

$conn = null;
?>
