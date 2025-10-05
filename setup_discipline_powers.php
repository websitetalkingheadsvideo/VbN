<?php
// Setup script for discipline powers table
require_once 'includes/connect.php';

echo "Setting up discipline powers table...\n";

// Read the SQL file
$sql = file_get_contents('create_discipline_powers_table.sql');

if ($sql === false) {
    die("Error: Could not read create_discipline_powers_table.sql\n");
}

// Split the SQL into individual statements
$statements = explode(';', $sql);

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) {
        continue;
    }
    
    try {
        $result = $conn->exec($statement);
        $success_count++;
        echo "✓ Executed statement successfully\n";
    } catch (PDOException $e) {
        $error_count++;
        echo "✗ Error executing statement: " . $e->getMessage() . "\n";
        echo "Statement: " . substr($statement, 0, 100) . "...\n";
    }
}

echo "\n=== Setup Complete ===\n";
echo "Successful statements: $success_count\n";
echo "Failed statements: $error_count\n";

if ($error_count == 0) {
    echo "✓ Discipline powers table setup completed successfully!\n";
} else {
    echo "⚠ Some statements failed. Please check the errors above.\n";
}

$conn = null;
?>
