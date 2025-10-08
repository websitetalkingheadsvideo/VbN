<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Include database connection
include 'includes/connect.php';

try {
    $user_id = $_SESSION['user_id'];
    
    // Query to get user's characters
    $sql = "SELECT 
                id,
                character_name,
                player_name,
                chronicle,
                nature,
                demeanor,
                concept,
                clan,
                generation,
                sire,
                pc,
                biography,
                equipment,
                total_xp,
                spent_xp,
                created_at,
                updated_at
            FROM characters 
            WHERE user_id = ? 
            ORDER BY created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $characters = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $characters[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => true,
        'characters' => $characters,
        'count' => count($characters)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
