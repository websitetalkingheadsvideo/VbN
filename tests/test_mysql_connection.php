<?php
echo "Testing MySQL connection...\n";

$conn = mysqli_connect('vdb5.pit.pair.com', 'working_64', 'pcf577#1');

if ($conn) {
    echo "✅ MySQL connection: SUCCESS\n";
    
    // Check if our database exists
    $result = mysqli_query($conn, "SHOW DATABASES LIKE 'lotn_characters'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "✅ Database 'lotn_characters' exists\n";
        
        // Test connection to our database
        mysqli_select_db($conn, 'lotn_characters');
        $tables = mysqli_query($conn, "SHOW TABLES");
        if ($tables) {
            $table_count = mysqli_num_rows($tables);
            echo "✅ Database has $table_count tables\n";
        }
    } else {
        echo "❌ Database 'lotn_characters' not found\n";
    }
    
    mysqli_close($conn);
} else {
    echo "❌ MySQL connection: FAILED\n";
    echo "Error: " . mysqli_connect_error() . "\n";
}
?>

