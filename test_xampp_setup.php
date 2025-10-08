<?php
echo "<h1>VbN Character Creator - XAMPP Setup Test</h1>";

// Test 1: PHP is working
echo "<h2>✅ Test 1: PHP is working</h2>";
echo "PHP Version: " . phpversion() . "<br>";

// Test 2: Database connection
echo "<h2>Test 2: Database Connection</h2>";
include 'includes/connect.php';

if ($conn) {
    echo "✅ Database connection: SUCCESS<br>";
    
    // Test 3: Check if database has tables
    $result = mysqli_query($conn, "SHOW TABLES");
    if ($result) {
        $table_count = mysqli_num_rows($result);
        echo "✅ Database tables found: $table_count<br>";
        
        if ($table_count > 0) {
            echo "✅ Database is properly set up!<br>";
            
            // Show tables
            echo "<h3>Database Tables:</h3><ul>";
            while ($row = mysqli_fetch_array($result)) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "⚠️ Database exists but no tables found. Please run setup_xampp.sql<br>";
        }
    } else {
        echo "❌ Error checking tables: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "❌ Database connection: FAILED<br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
    echo "<p><strong>Solution:</strong> Make sure MySQL is running in XAMPP Control Panel</p>";
}

mysqli_close($conn);

// Test 4: File structure
echo "<h2>Test 3: File Structure</h2>";
$required_files = [
    'lotn_char_create.php',
    'includes/connect.php',
    'js/main.js',
    'css/style.css'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If all tests pass, your application should be accessible at: <a href='lotn_char_create.php'>http://localhost/VbN/lotn_char_create.php</a></li>";
echo "<li>If database tests fail, make sure MySQL is running in XAMPP</li>";
echo "<li>If file tests fail, make sure you're in the correct directory</li>";
echo "</ol>";
?>

