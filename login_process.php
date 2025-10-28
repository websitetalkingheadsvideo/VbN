<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>login_process.php</title>
</head>

<body>
<?php
/**
 * Login Process Handler
 * Authenticates user credentials securely using prepared statements
 * MySQL Compliance: Uses prepared statements to prevent SQL injection
 */

session_start();
error_reporting(2);

include 'includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and password are required";
        header("Location: login.php");
        exit();
    }
    
    // Use prepared statement to prevent SQL injection
    $user = db_fetch_one($conn,
        "SELECT id, username, password, role, email_verified FROM users WHERE username = ?",
        "s",
        [$username]
    );
    
    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if email is verified
            if (!$user['email_verified']) {
                $_SESSION['error'] = "Please verify your email address before logging in. Check your inbox for the verification link.";
                header("Location: login.php");
                exit();
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login timestamp using prepared statement
            db_execute($conn,
                "UPDATE users SET last_login = NOW() WHERE id = ?",
                "i",
                [$user['id']]
            );
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Invalid password
            $_SESSION['error'] = "Invalid username or password";
            header("Location: login.php");
            exit();
        }
    } else {
        // User not found
        $_SESSION['error'] = "Invalid username or password";
        header("Location: login.php");
        exit();
    }
}

mysqli_close($conn);
?>
</body>
</html>