<?php
// Test what's actually in the remote save_character_working.php
echo "=== REMOTE FILE TEST ===\n";

if (file_exists('save_character_working.php')) {
    $content = file_get_contents('save_character_working.php');
    echo "1. File exists: YES\n";
    echo "2. File size: " . strlen($content) . " bytes\n";
    
    // Find the bind_param line
    if (preg_match('/mysqli_stmt_bind_param\([^,]+,\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        $type_string = $matches[1];
        echo "3. Type string found: " . $type_string . "\n";
        echo "4. Type string length: " . strlen($type_string) . "\n";
    } else {
        echo "3. Type string: NOT FOUND\n";
    }
    
    // Count parameters in the bind_param call
    if (preg_match('/mysqli_stmt_bind_param\([^,]+,[^,]+,\s*([^)]+)\)/', $content, $matches)) {
        $params_text = $matches[1];
        $param_count = substr_count($params_text, '$');
        echo "5. Parameter count: " . $param_count . "\n";
    } else {
        echo "5. Parameter count: NOT FOUND\n";
    }
    
} else {
    echo "1. File exists: NO\n";
}

echo "=== TEST COMPLETE ===\n";
?>
