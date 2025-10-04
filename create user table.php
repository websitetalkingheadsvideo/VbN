<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
	<?php 
error_reporting(2);
include 'includes/connect.php';

// Create users table
$create_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
)";

if (mysqli_query($conn, $create_table)) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Create admin account
$admin_username = "admin";
$admin_password = "admin123"; // Change this!
$admin_email = "admin@example.com";

$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

$insert_admin = "INSERT INTO users (username, password, email, role) 
                 VALUES ('$admin_username', '$hashed_password', '$admin_email', 'admin')";

if (mysqli_query($conn, $insert_admin)) {
    echo "Admin account created successfully<br>";
    echo "Username: $admin_username<br>";
    echo "Password: $admin_password<br>";
} else {
    echo "Error creating admin: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
</body>
</html>