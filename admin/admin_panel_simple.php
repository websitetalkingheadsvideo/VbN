<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.5.0');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../includes/connect.php';

echo "<h1>Admin Panel Works</h1>";
echo "<p>Connection: " . ($conn ? 'Connected' : 'Failed') . "</p>";

// Test simple query
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM characters");
if ($result) {
    $count = mysqli_fetch_assoc($result);
    echo "<p>Characters in DB: " . $count['total'] . "</p>";
} else {
    echo "<p>Query error: " . mysqli_error($conn) . "</p>";
}
?>

