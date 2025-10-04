<?php
// Simple test to debug the save issue
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Not logged in. User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set');
    exit();
}

echo "User ID: " . $_SESSION['user_id'] . "<br>";

// Database connection
include 'includes/connect.php';

if (!$conn) {
    echo "Database connection failed: " . mysqli_connect_error();
    exit();
}

echo "Database connected successfully<br>";

// Test if characters table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'characters'");
if (mysqli_num_rows($result) == 0) {
    echo "ERROR: characters table does not exist!<br>";
    echo "Available tables:<br>";
    $tables = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_array($tables)) {
        echo "- " . $row[0] . "<br>";
    }
} else {
    echo "characters table exists<br>";
}

// Test basic insert
$test_sql = "INSERT INTO characters (user_id, character_name, player_name, nature, demeanor, concept, clan, generation, pc, total_xp, spent_xp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $test_sql);

if (!$stmt) {
    echo "ERROR: Failed to prepare statement: " . mysqli_error($conn);
} else {
    echo "Statement prepared successfully<br>";
    
    $user_id = $_SESSION['user_id'];
    $character_name = "Test Character";
    $player_name = "Test Player";
    $nature = "Test Nature";
    $demeanor = "Test Demeanor";
    $concept = "Test Concept";
    $clan = "Brujah";
    $generation = 13;
    $pc = 1;
    $total_xp = 30;
    $spent_xp = 0;
    
    mysqli_stmt_bind_param($stmt, 'issssssiii', $user_id, $character_name, $player_name, $nature, $demeanor, $concept, $clan, $generation, $pc, $total_xp, $spent_xp);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Test character inserted successfully!<br>";
        $character_id = mysqli_insert_id($conn);
        echo "Character ID: " . $character_id . "<br>";
    } else {
        echo "ERROR: Failed to execute statement: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
