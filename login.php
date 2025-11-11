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

// Check if login is disabled
$loginDisableFile = __DIR__ . '/config/login_disable.json';
$loginDisabled = false;
$disabledUntil = null;

if (file_exists($loginDisableFile)) {
    $config = json_decode(file_get_contents($loginDisableFile), true);
    if ($config && isset($config['disabled']) && $config['disabled'] === true) {
        $loginDisabled = true;
        $disabledUntil = $config['disabled_until'] ?? null;
        
        // Check if the disable period has expired
        if ($disabledUntil) {
            $now = time();
            $until = strtotime($disabledUntil);
            if ($now >= $until) {
                // Expired - re-enable login
                $config['disabled'] = false;
                $config['disabled_until'] = null;
                file_put_contents($loginDisableFile, json_encode($config, JSON_PRETTY_PRINT));
                $loginDisabled = false;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Valley by Night</title>
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
            <h1 class="login-title">🦇 Welcome to the Night</h1>
            <p class="login-subtitle">Enter your credentials to access the chronicle</p>
            
            <?php
            if ($loginDisabled):
                $untilTime = $disabledUntil ? date('F j, Y \a\t g:i A', strtotime($disabledUntil)) : '1 hour';
            ?>
                <div class="login-error login-disabled-alert" role="alert" aria-live="polite">
                    <h2 class="alert-title">🚫 Login Temporarily Disabled</h2>
                    <p class="alert-text">Login is currently disabled. It will be re-enabled after: <strong><?php echo htmlspecialchars($untilTime); ?></strong></p>
                </div>
            <?php else: ?>
                <?php
                if (isset($_SESSION['error'])) {
                    echo '<div class="login-error" role="alert" aria-live="polite">⚠️ ' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                
                <form action="login_process.php" method="POST" class="login-form d-flex flex-column gap-4 needs-validation" novalidate>
                <div class="form-group mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus aria-describedby="usernameHelp" autocomplete="username">
                    <div id="usernameHelp" class="form-text">Enter your account username.</div>
                    <div class="invalid-feedback">Username is required.</div>
                </div>
                
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                    <div class="invalid-feedback">Password is required.</div>
                </div>
                
                    <button type="submit" class="login-btn">Enter the Chronicle</button>
                </form>
                
                <div class="login-links">
                    <p>Don't have an account? <a href="register.php" class="link-primary">Create Account</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="js/form_validation.js"></script>
</body>
</html>
