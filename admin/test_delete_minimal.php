<?php
/**
 * Minimal Test for Delete API
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing delete API...\n";

// Simulate the DELETE request
$_SERVER['REQUEST_METHOD'] = 'DELETE';
$_GET['id'] = '3';

// Include the API file
try {
    include 'api_admin_locations_crud.php';
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
?>
