<?php
/**
 * Debug Delete Location API
 * Test script to debug the delete functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug Delete Location API</h1>";
echo "<pre>";

try {
    // Include database connection
    require_once 'includes/connect.php';
    
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    
    echo "✅ Database connection successful\n\n";
    
    // Test location ID (use ID 3 as mentioned in error)
    $location_id = 3;
    
    echo "Testing delete for location ID: $location_id\n\n";
    
    // Check if location exists
    $check_query = "SELECT id, name FROM locations WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $location_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $location = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$location) {
        echo "❌ Location ID $location_id not found\n";
        exit;
    }
    
    echo "✅ Found location: {$location['name']} (ID: {$location['id']})\n\n";
    
    // Check if location_assignments table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'location_assignments'");
    
    if (mysqli_num_rows($table_check) > 0) {
        echo "✅ location_assignments table exists\n";
        
        // Check for assignments
        $assignment_query = "SELECT COUNT(*) as assignment_count FROM location_assignments WHERE location_id = ?";
        $stmt = mysqli_prepare($conn, $assignment_query);
        mysqli_stmt_bind_param($stmt, "i", $location_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $assignment_data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        echo "📊 Character assignments: {$assignment_data['assignment_count']}\n";
        
        if ($assignment_data['assignment_count'] > 0) {
            echo "⚠️ Cannot delete - has character assignments\n";
            exit;
        }
    } else {
        echo "ℹ️ location_assignments table does not exist - skipping assignment check\n";
    }
    
    // Test the delete query
    echo "\nTesting delete query...\n";
    
    mysqli_begin_transaction($conn);
    
    try {
        $delete_query = "DELETE FROM locations WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        
        if (!$stmt) {
            throw new Exception("Delete prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $location_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Delete execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affected_rows === 0) {
            throw new Exception("No rows affected - location not found");
        }
        
        mysqli_commit($conn);
        
        echo "✅ Delete successful! Affected rows: $affected_rows\n";
        echo "🎉 Location '{$location['name']}' has been deleted\n";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>
