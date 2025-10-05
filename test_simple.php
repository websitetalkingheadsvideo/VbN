<?php
// Simple test to see if basic PHP works
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo json_encode(['success' => true, 'message' => 'PHP is working']);
?>
