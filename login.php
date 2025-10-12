<?php
/**
 * Login Page - Valley by Night
 * Themed login interface with gothic styling
 */
define('LOTN_VERSION', '0.5.0');
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Valley by Night</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English:ital@0;1&family=IM+Fell+English+SC&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Nosifer&family=Source+Serif+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-title">🦇 Welcome to the Night</h1>
            <p class="login-subtitle">Enter your credentials to access the chronicle</p>
            
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="login-error">⚠️ ' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            
            <form action="login_process.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">Enter the Chronicle</button>
            </form>
            
            <div class="login-links">
                <p>Don't have an account? <a href="register.php" class="link-primary">Create Account</a></p>
            </div>
        </div>
    </div>
</body>
</html>