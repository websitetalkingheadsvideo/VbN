<?php
// Test file to verify XAMPP is working with your VbN project
echo "<h1>XAMPP Test - LOTN Character Creator</h1>";
echo "<p>Current working directory: " . getcwd() . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
include 'includes/connect.php';

if ($conn) {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    echo "<p>Connected to: " . mysqli_get_server_info($conn) . "</p>";
    
    // Test if database exists
    $result = mysqli_query($conn, "SELECT DATABASE() as db_name");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<p>Current database: " . $row['db_name'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
}

echo "<h2>Project Files</h2>";
$files = glob('*.php');
echo "<ul>";
foreach ($files as $file) {
    echo "<li><a href='$file'>$file</a></li>";
}
echo "</ul>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If you see this page, XAMPP is working!</li>";
echo "<li>Click on 'lotn_char_create.php' to access the character creator</li>";
echo "<li>Make sure MySQL is running in XAMPP Control Panel</li>";
echo "<li>Run the database setup script in phpMyAdmin</li>";
echo "</ol>";
?>
