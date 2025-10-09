<?php
// Simple test that works with any method
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = file_get_contents('php://input');
$data = json_decode($input, true);

echo json_encode([
    'success' => true,
    'method' => $method,
    'has_input' => !empty($input),
    'input_length' => strlen($input),
    'json_valid' => $data !== null,
    'data_keys' => $data ? array_keys($data) : []
]);
?>