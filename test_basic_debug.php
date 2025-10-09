<?php
// Basic debug - test each component individually
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== BASIC DEBUG TEST ===\n";

// Test 1: Basic PHP
echo "1. PHP working\n";

// Test 2: Session
try {
    session_start();
    echo "2. Session: OK (user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . ")\n";
} catch (Exception $e) {
    echo "2. Session ERROR: " . $e->getMessage() . "\n";
}

// Test 3: Database connection
try {
    include 'includes/connect.php';
    if ($conn) {
        echo "3. Database: OK\n";
    } else {
        echo "3. Database: FAILED\n";
    }
} catch (Exception $e) {
    echo "3. Database ERROR: " . $e->getMessage() . "\n";
}

// Test 4: Check if we can prepare a simple statement
try {
    if ($conn) {
        $test_sql = "SELECT 1 as test";
        $test_stmt = mysqli_prepare($conn, $test_sql);
        if ($test_stmt) {
            echo "4. Prepared statement: OK\n";
            mysqli_stmt_close($test_stmt);
        } else {
            echo "4. Prepared statement: FAILED - " . mysqli_error($conn) . "\n";
        }
    }
} catch (Exception $e) {
    echo "4. Prepared statement ERROR: " . $e->getMessage() . "\n";
}

echo "=== TEST COMPLETE ===\n";
?>
