<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../includes/connect.php';

$response = ['success' => false, 'characters' => [], 'error' => ''];

try {
    $query = "SELECT id, character_name, clan, player_name FROM characters ORDER BY character_name ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $characters = [];
    while ($row = $result->fetch_assoc()) {
        $characters[] = $row;
    }

    $response['success'] = true;
    $response['characters'] = $characters;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>