<?php
echo "<h1>Database Connection Test</h1>";

// Test basic MySQL connection
$servername = "vdb5.pit.pair.com";
$username = "root";
$password = "";
$dbname = "lotn_characters";

echo "<h2>Step 1: Basic MySQL Connection</h2>";
$conn = mysqli_connect($servername, $username, $password);

if ($conn) {
    echo "✅ MySQL connection successful<br>";
    
    echo "<h2>Step 2: Check if database exists</h2>";
    $result = mysqli_query($conn, "SHOW DATABASES LIKE '$dbname'");
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Database '$dbname' exists<br>";
        
        echo "<h2>Step 3: Connect to database</h2>";
        mysqli_select_db($conn, $dbname);
        
        echo "<h2>Step 4: Check tables</h2>";
        $result = mysqli_query($conn, "SHOW TABLES");
        if ($result) {
            $table_count = mysqli_num_rows($result);
            echo "✅ Found $table_count tables<br>";
            
            if ($table_count > 0) {
                echo "<h3>Tables:</h3><ul>";
                while ($row = mysqli_fetch_array($result)) {
                    echo "<li>" . $row[0] . "</li>";
                }
                echo "</ul>";
            } else {
                echo "⚠️ No tables found - database needs to be set up<br>";
            }
        } else {
            echo "❌ Error checking tables: " . mysqli_error($conn) . "<br>";
        }
        
    } else {
        echo "❌ Database '$dbname' does not exist<br>";
        echo "<p>Need to create database and run setup_xampp.sql</p>";
    }
    
} else {
    echo "❌ MySQL connection failed: " . mysqli_connect_error() . "<br>";
    echo "<p>Make sure XAMPP MySQL is running</p>";
}

mysqli_close($conn);
?>
