<?php
// Simple debug script to test save_character.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG SAVE SIMPLE ===\n";

// Test 1: Basic PHP
echo "1. PHP execution: OK\n";

// Test 2: Session
session_start();
echo "2. Session started, user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";

// Test 3: Database
include 'includes/connect.php';
if ($conn) {
    echo "3. Database connection: OK\n";
} else {
    echo "3. Database connection: FAILED\n";
    exit;
}

// Test 4: Check if we can include save_character.php
echo "4. Testing save_character.php inclusion...\n";

// Capture any output from save_character.php
ob_start();
$include_result = include 'save_character.php';
$output = ob_get_clean();

echo "5. Include result: " . ($include_result ? 'OK' : 'FAILED') . "\n";
echo "6. Output length: " . strlen($output) . "\n";
echo "7. Output: " . substr($output, 0, 200) . "\n";

echo "=== DEBUG COMPLETE ===\n";
?>
