<?php
/**
 * Run Email Verification Migration
 * Adds email verification columns to users table
 */

require_once __DIR__ . '/../includes/connect.php';

echo "<h2>Running Email Verification Migration...</h2>";

// Read and execute the migration SQL
$sql = file_get_contents(__DIR__ . '/migrations/007_add_email_verification.sql');

// Split into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue; // Skip empty lines and comments
    }
    
    echo "<p>Executing: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
    
    if (mysqli_query($conn, $statement)) {
        echo "<p style='color: green;'>✓ Success</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
        $error_count++;
    }
}

mysqli_close($conn);

echo "<hr>";
echo "<h3>Migration Complete!</h3>";
echo "<p><strong>Successful:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count === 0) {
    echo "<p style='color: green; font-weight: bold;'>All migrations executed successfully!</p>";
    echo "<p><a href='../register.php'>Go to Registration Page</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>Some migrations failed. Please check the errors above.</p>";
}
?>

