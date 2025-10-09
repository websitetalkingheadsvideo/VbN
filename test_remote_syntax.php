<?php
// Test what's wrong with save_character.php on remote server
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== REMOTE SYNTAX TEST ===\n";

// Test 1: Basic PHP
echo "1. PHP working\n";

// Test 2: Check PHP version
echo "2. PHP version: " . phpversion() . "\n";

// Test 3: Try to read save_character.php and check for issues
echo "3. Reading save_character.php...\n";
$content = file_get_contents('save_character.php');
echo "4. File size: " . strlen($content) . " bytes\n";

// Test 4: Check for common issues
if (strpos($content, '<?php') === false) {
    echo "5. ERROR: Missing opening PHP tag\n";
} else {
    echo "5. Opening PHP tag: OK\n";
}

if (strpos($content, '?>') === false) {
    echo "6. Missing closing PHP tag (this is OK)\n";
} else {
    echo "6. Closing PHP tag: Found\n";
}

// Test 5: Check for BOM or hidden characters
$first_chars = bin2hex(substr($content, 0, 10));
echo "7. First 10 bytes (hex): " . $first_chars . "\n";

// Test 6: Try to include it with error suppression
echo "8. Testing include...\n";
ob_start();
$include_result = @include 'save_character.php';
$output = ob_get_clean();

if ($include_result === false) {
    echo "9. Include FAILED\n";
} else {
    echo "9. Include result: " . ($include_result ? 'OK' : 'FALSE') . "\n";
}

echo "10. Output length: " . strlen($output) . "\n";
if (strlen($output) > 0) {
    echo "11. Output preview: " . substr($output, 0, 100) . "\n";
}

echo "=== TEST COMPLETE ===\n";
?>
