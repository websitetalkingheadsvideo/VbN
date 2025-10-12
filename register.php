<?php
/**
 * Registration Page - Valley by Night
 * New user account creation with email verification
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
    <title>Register - Valley by Night</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English:ital@0;1&family=IM+Fell+English+SC&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Nosifer&family=Source+Serif+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-title">🦇 Join the Chronicle</h1>
            <p class="login-subtitle">Create your account to enter the night</p>
            
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="login-error">⚠️ ' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="login-success">✓ ' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            ?>
            
            <form action="register_process.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus 
                           minlength="3" maxlength="50"
                           pattern="[a-zA-Z0-9_]+"
                           title="Username must be 3-50 characters, letters, numbers, and underscores only">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           minlength="8"
                           title="Password must be at least 8 characters">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="login-btn">Create Account</button>
            </form>
            
            <div class="login-links">
                <p>Already have an account? <a href="login.php" class="link-primary">Sign In</a></p>
            </div>
        </div>
    </div>
</body>
</html>

