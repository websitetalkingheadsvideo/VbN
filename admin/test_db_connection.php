<?php
/**
 * Test Database Connection from Admin Directory
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔌 Test Database Connection</h1>";
echo "<pre>";

try {
    echo "Testing database connection from admin directory...\n\n";
    
    // Test the include path
    $include_path = __DIR__ . '/../includes/connect.php';
    echo "Include path: $include_path\n";
    echo "File exists: " . (file_exists($include_path) ? 'YES' : 'NO') . "\n\n";
    
    if (file_exists($include_path)) {
        require_once $include_path;
        
        if ($conn) {
            echo "✅ Database connection successful!\n";
            echo "Connection info: " . mysqli_get_server_info($conn) . "\n\n";
            
            // Test if locations table exists
            $result = mysqli_query($conn, "SHOW TABLES LIKE 'locations'");
            if (mysqli_num_rows($result) > 0) {
                echo "✅ Locations table exists\n";
                
                // Check if location ID 3 exists
                $result = mysqli_query($conn, "SELECT id, name FROM locations WHERE id = 3");
                if ($row = mysqli_fetch_assoc($result)) {
                    echo "✅ Location ID 3 exists: {$row['name']}\n";
                } else {
                    echo "❌ Location ID 3 does not exist\n";
                }
            } else {
                echo "❌ Locations table does not exist\n";
            }
            
        } else {
            echo "❌ Database connection failed: " . mysqli_connect_error() . "\n";
        }
    } else {
        echo "❌ Database connection file not found\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
