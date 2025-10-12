<?php
include 'includes/connect.php';

echo "Testing database connection...\n";

if ($conn) {
    echo "✅ Database connection: SUCCESS\n";
    
    // Test if database exists and has tables
    $result = mysqli_query($conn, "SHOW TABLES");
    if ($result) {
        $table_count = mysqli_num_rows($result);
        echo "✅ Database tables found: $table_count\n";
        
        if ($table_count > 0) {
            echo "✅ Database is properly set up!\n";
        } else {
            echo "⚠️  Database exists but no tables found. Run setup_xampp.sql\n";
        }
    } else {
        echo "❌ Error checking tables: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "❌ Database connection: FAILED\n";
    echo "Error: " . mysqli_connect_error() . "\n";
}

mysqli_close($conn);
?>