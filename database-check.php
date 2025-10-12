<?php
session_start();
require_once 'includes/connect.php';

if ($conn) {
    echo "✅ Database connection successful!<br>";
    echo "Connected to: working_vbn on vdb5.pit.pair.com<br><br>";
    
    // Check session info
    echo "Session Info:<br>";
    echo "- user_id: " . ($_SESSION['user_id'] ?? 'not set') . "<br>";
    echo "- username: " . ($_SESSION['username'] ?? 'not set') . "<br>";
    echo "- role: " . ($_SESSION['role'] ?? 'not set') . "<br><br>";
    
    // Check all users and their roles
    echo "All Users in Database:<br>";
    $users = mysqli_query($conn, "SELECT id, username, role FROM users");
    while ($user = mysqli_fetch_assoc($users)) {
        echo "- ID: " . $user['id'] . " | Username: " . $user['username'] . " | Role: " . $user['role'] . "<br>";
    }
    echo "<br>";
    
    // Test the exact query from index.php
    $stats_query = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN player_name = 'ST/NPC' THEN 1 ELSE 0 END) as npcs,
        SUM(CASE WHEN player_name != 'ST/NPC' THEN 1 ELSE 0 END) as pcs
        FROM characters";
    
    $stats_result = mysqli_query($conn, $stats_query);
    
    if ($stats_result) {
        $stats = mysqli_fetch_assoc($stats_result);
        echo "Query Results:<br>";
        echo "Total Characters: " . $stats['total'] . "<br>";
        echo "PCs: " . $stats['pcs'] . "<br>";
        echo "NPCs: " . $stats['npcs'] . "<br>";
    } else {
        echo "❌ Query failed: " . mysqli_error($conn) . "<br><br>";
        
        // Show table structure
        echo "Characters table columns:<br>";
        $columns = mysqli_query($conn, "DESCRIBE characters");
        while ($col = mysqli_fetch_assoc($columns)) {
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
        }
    }
} else {
    echo "❌ Database connection failed!<br>";
    echo "Error: " . mysqli_connect_error();
}
?>

