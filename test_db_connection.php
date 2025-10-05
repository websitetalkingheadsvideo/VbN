<?php
// Test database connection and table structure
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Test database connection
include 'includes/connect.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Test if characters table exists and show its structure
$result = mysqli_query($conn, "DESCRIBE characters");
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Characters table does not exist or error: ' . mysqli_error($conn)]);
    exit();
}

$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row;
}

echo json_encode([
    'success' => true, 
    'message' => 'Database connection successful',
    'characters_table_columns' => $columns
]);
?>
