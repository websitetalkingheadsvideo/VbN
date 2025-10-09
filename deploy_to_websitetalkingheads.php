<?php
// Database connection for websitetalkingheads.com
// Update these values with your actual remote server credentials

$servername = "localhost";  // Usually localhost on shared hosting
$username = "your_db_username";      // Your database username
$password = "your_db_password";      // Your database password  
$dbname = "your_db_name";            // Your database name (might be different from lotn_characters)

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    // If database doesn't exist, try to create it
    $conn = mysqli_connect($servername, $username, $password);
    if ($conn) {
        $create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
        if (mysqli_query($conn, $create_db)) {
            mysqli_select_db($conn, $dbname);
        } else {
            die("Error creating database: " . mysqli_error($conn));
        }
    } else {
        die("Connection failed: " . mysqli_connect_error());
    }
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");
?>
