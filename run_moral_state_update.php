<?php
// Run the moral state field update
include 'includes/connect.php';

// Read and execute the SQL file
$sql = file_get_contents('add_moral_state_field.sql');

if (mysqli_query($conn, $sql)) {
    echo "Moral state field added successfully!<br>";
} else {
    echo "Error adding moral state field: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
