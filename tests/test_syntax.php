<?php
// Test syntax of save_character.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SYNTAX TEST ===\n";

// Test if save_character.php has valid syntax
$syntax_check = shell_exec('php -l save_character.php 2>&1');
echo "Syntax check result:\n";
echo $syntax_check . "\n";

echo "=== SYNTAX TEST COMPLETE ===\n";
?>
