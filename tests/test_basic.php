<?php
echo "<h1>Basic Test</h1>";
echo "<p>PHP is working</p>";

// Test session
session_start();
echo "<p>Session started</p>";

// Test database connection without include
$servername = "vdb5.pit.pair.com";
$username = "root";
$password = "";
$dbname = "lotn_characters";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    echo "<p>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
} else {
    echo "<p>✅ Database connected</p>";
    mysqli_close($conn);
}

echo "<p>Test completed</p>";
?>
