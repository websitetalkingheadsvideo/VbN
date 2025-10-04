<?php 
error_reporting(2);
//session_start();

// Database configuration - XAMPP local setup
$servername = "localhost";
$username = "root";
$password = "";  // XAMPP default is empty password
$dbname = "lotn_characters";

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
?>