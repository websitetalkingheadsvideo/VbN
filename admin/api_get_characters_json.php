<?php
/**
 * Admin API: Get Characters JSON content
 * Returns the contents of reference/Characters/characters.json for verification.
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$root = realpath(__DIR__ . '/..');
$jsonPath = $root . DIRECTORY_SEPARATOR . 'reference' . DIRECTORY_SEPARATOR . 'Characters' . DIRECTORY_SEPARATOR . 'characters.json';

if (!file_exists($jsonPath)) {
    echo json_encode(['success' => false, 'error' => 'JSON not found']);
    exit();
}

$raw = file_get_contents($jsonPath);
if ($raw === false) {
    echo json_encode(['success' => false, 'error' => 'Failed to read JSON']);
    exit();
}

// Validate JSON to avoid returning corrupt content
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'JSON parse error: ' . json_last_error_msg()]);
    exit();
}

echo json_encode(['success' => true, 'json' => $data], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>

