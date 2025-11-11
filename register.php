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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap-overrides.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English:ital@0;1&family=IM+Fell+English+SC&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Nosifer&family=Source+Serif+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-box">
            <h1 class="login-title">🦇 Join the Chronicle</h1>
            <p class="login-subtitle">Create your account to enter the night</p>
            
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="login-error" role="alert" aria-live="polite">⚠️ ' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="login-success" role="alert" aria-live="polite">✓ ' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            ?>
            
            <form action="register_process.php" method="POST" class="login-form d-flex flex-column gap-4 needs-validation" novalidate>
                <div class="form-group mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus autocomplete="username" 
                           minlength="3" maxlength="50"
                           pattern="[a-zA-Z0-9_]+"
                           title="Username must be 3-50 characters, letters, numbers, and underscores only">
                    <div class="invalid-feedback">Username must be 3-50 chars (letters, numbers, underscores).</div>
                </div>
                
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required autocomplete="email">
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password" 
                           minlength="8"
                           title="Password must be at least 8 characters">
                    <div class="invalid-feedback">Password must be at least 8 characters.</div>
                </div>
                
                <div class="form-group mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
                    <div class="invalid-feedback">Please confirm your password.</div>
                </div>
                
                <button type="submit" class="login-btn">Create Account</button>
            </form>
            
            <div class="login-links">
                <p>Already have an account? <a href="login.php" class="link-primary">Sign In</a></p>
            </div>
        </div>
    </div>
    <script src="js/form_validation.js"></script>
    <script>
      // Client-side password match validation
      document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form.needs-validation');
        if (!form) return;
        const pwd = document.getElementById('password');
        const confirm = document.getElementById('confirm_password');
        if (!pwd || !confirm) return;

        // Ensure an invalid-feedback element exists for confirm password
        let fb = confirm.parentElement.querySelector('.invalid-feedback.mismatch');
        if (!fb) {
          fb = document.createElement('div');
          fb.className = 'invalid-feedback mismatch';
          fb.textContent = 'Passwords must match.';
          confirm.parentElement.appendChild(fb);
        }

        function validateMatch() {
          if (confirm.value && pwd.value !== confirm.value) {
            confirm.setCustomValidity('Passwords must match');
          } else {
            confirm.setCustomValidity('');
          }
        }

        pwd.addEventListener('input', validateMatch);
        confirm.addEventListener('input', validateMatch);
        form.addEventListener('submit', validateMatch);
      });
    </script>
</body>
</html>

