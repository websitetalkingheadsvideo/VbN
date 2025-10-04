<?php 
error_reporting( 2 );
//session_start();
$servername = "vdb5.pit.pair.com";
$username = "working_64";
$password = "UUHDShqLYKasu8ds";
$dbname = "working_vbn";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>