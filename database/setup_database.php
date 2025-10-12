<?php
// LOTN Character Creator - Database Setup Script
// Run this once to create the database and tables

echo "<h1>LOTN Character Creator - Database Setup</h1>";

// Include database connection
include 'includes/connect.php';

if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    echo "<p>Make sure MySQL is running in XAMPP Control Panel</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connection successful!</p>";

// Read and execute the SQL setup file
$sql_file = 'setup_xampp.sql';
if (!file_exists($sql_file)) {
    echo "<p style='color: red;'>❌ SQL file not found: $sql_file</p>";
    exit;
}

$sql_content = file_get_contents($sql_file);

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;

echo "<h2>Executing SQL Statements...</h2>";

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue; // Skip empty statements and comments
    }
    
    if (mysqli_query($conn, $statement)) {
        $success_count++;
        echo "<p style='color: green;'>✅ " . substr($statement, 0, 50) . "...</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
        echo "<p>Statement: " . substr($statement, 0, 100) . "...</p>";
    }
}

echo "<h2>Setup Complete!</h2>";
echo "<p>✅ Successful statements: $success_count</p>";
echo "<p>❌ Errors: $error_count</p>";

if ($error_count == 0) {
    echo "<h3>Default Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: <code>admin</code>, password: <code>password</code></li>";
    echo "<li><strong>Test User:</strong> username: <code>testuser</code>, password: <code>password</code></li>";
    echo "</ul>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='login.php'>Go to Login Page</a></li>";
    echo "<li><a href='lotn_char_create.php'>Go to Character Creator</a></li>";
    echo "<li><a href='test_xampp.php'>Back to Test Page</a></li>";
    echo "</ol>";
} else {
    echo "<p style='color: red;'>There were errors during setup. Please check the error messages above.</p>";
}

mysqli_close($conn);
?>
