<?php
/**
 * Get Character Names API
 * Returns list of all character names for dropdowns
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/includes/connect.php';

$response = ['success' => false, 'characters' => [], 'error' => ''];

try {
    $query = "SELECT id, character_name FROM characters ORDER BY character_name ASC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }
    
    $characters = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $characters[] = [
            'id' => $row['id'],
            'name' => $row['character_name']
        ];
    }
    
    $response['success'] = true;
    $response['characters'] = $characters;
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
mysqli_close($conn);
?>

