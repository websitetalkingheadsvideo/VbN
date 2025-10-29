<?php
/**
 * Locations API - Fetch Locations Data
 * Returns JSON array of locations for admin interface
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/../includes/connect.php';

try {
    // Get all locations with key fields
    $query = "SELECT 
        id, name, type, status, district, owner_type, faction, 
        security_level, description, summary, notes, created_at
        FROM locations 
        ORDER BY type, name";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }
    
    $locations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = $row;
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'locations' => $locations,
        'count' => count($locations)
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
