<?php
/**
 * Temporary Login Disable/Enable Script & Auth Bypass Control
 * Admin utility to disable login and enable authentication bypass
 */

session_start();
require_once __DIR__ . '/../includes/connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'storyteller')) {
    die('Access denied. Admin only.');
}

$loginDisableFile = __DIR__ . '/../config/login_disable.json';
$authBypassFile = __DIR__ . '/../config/auth_bypass.json';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'disable') {
        $hours = intval($_POST['hours'] ?? 1);
        $disabledUntil = date('Y-m-d H:i:s', time() + ($hours * 3600));
        
        $config = [
            'disabled' => true,
            'disabled_until' => $disabledUntil
        ];
        
        file_put_contents($loginDisableFile, json_encode($config, JSON_PRETTY_PRINT));
        $message = "Login disabled for {$hours} hour(s). Will be re-enabled at " . date('g:i A', strtotime($disabledUntil));
        $messageType = 'success';
    } else if ($action === 'enable') {
        $config = [
            'disabled' => false,
            'disabled_until' => null
        ];
        
        file_put_contents($loginDisableFile, json_encode($config, JSON_PRETTY_PRINT));
        $message = "Login re-enabled immediately.";
        $messageType = 'success';
    } else if ($action === 'enable_bypass') {
        $hours = intval($_POST['bypass_hours'] ?? 1);
        $enabledUntil = date('Y-m-d H:i:s', time() + ($hours * 3600));
        
        $config = [
            'enabled' => true,
            'enabled_until' => $enabledUntil
        ];
        
        file_put_contents($authBypassFile, json_encode($config, JSON_PRETTY_PRINT));
        $message = "Authentication bypass enabled for {$hours} hour(s). Site accessible without login until " . date('g:i A', strtotime($enabledUntil));
        $messageType = 'success';
    } else if ($action === 'disable_bypass') {
        $config = [
            'enabled' => false,
            'enabled_until' => null
        ];
        
        file_put_contents($authBypassFile, json_encode($config, JSON_PRETTY_PRINT));
        $message = "Authentication bypass disabled. Login required again.";
        $messageType = 'success';
    }
}

// Read current login disable status
$currentStatus = [
    'disabled' => false,
    'disabled_until' => null
];

if (file_exists($loginDisableFile)) {
    $config = json_decode(file_get_contents($loginDisableFile), true);
    if ($config) {
        $currentStatus = $config;
        
        // Check if expired
        if ($currentStatus['disabled'] && $currentStatus['disabled_until']) {
            $now = time();
            $until = strtotime($currentStatus['disabled_until']);
            if ($now >= $until) {
                $currentStatus['disabled'] = false;
                $currentStatus['disabled_until'] = null;
            }
        }
    }
}

// Read current auth bypass status
$currentBypassStatus = [
    'enabled' => false,
    'enabled_until' => null
];

if (file_exists($authBypassFile)) {
    $config = json_decode(file_get_contents($authBypassFile), true);
    if ($config) {
        $currentBypassStatus = $config;
        
        // Check if expired
        if ($currentBypassStatus['enabled'] && $currentBypassStatus['enabled_until']) {
            $now = time();
            $until = strtotime($currentBypassStatus['enabled_until']);
            if ($now >= $until) {
                $currentBypassStatus['enabled'] = false;
                $currentBypassStatus['enabled_until'] = null;
                file_put_contents($authBypassFile, json_encode($currentBypassStatus, JSON_PRETTY_PRINT));
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disable Login - Admin</title>
    <link rel="stylesheet" href="../css/global.css">
    <style>
        .admin-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: linear-gradient(135deg, rgba(42, 21, 21, 0.9) 0%, rgba(26, 15, 15, 0.9) 100%);
            border: 2px solid #8b0000;
            border-radius: 10px;
        }
        .status-box {
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            background: rgba(139, 0, 0, 0.2);
            border: 2px solid #8b0000;
        }
        .status-box.disabled {
            background: rgba(139, 0, 0, 0.4);
        }
        .status-box.enabled {
            background: rgba(42, 128, 42, 0.2);
            border-color: #2a802a;
        }
        .form-group {
            margin: 20px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #c9a96e;
            font-weight: 600;
        }
        .form-group input[type="number"] {
            width: 100px;
            padding: 8px;
            background: rgba(26, 15, 15, 0.8);
            border: 1px solid #8b0000;
            border-radius: 5px;
            color: #f5e6d3;
            font-size: 1em;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            font-family: 'Source Serif Pro', serif;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-disable {
            background: linear-gradient(135deg, #8b0000, #600000);
            color: #f5e6d3;
        }
        .btn-disable:hover {
            background: linear-gradient(135deg, #a00000, #800000);
        }
        .btn-enable {
            background: linear-gradient(135deg, #2a802a, #1f5f1f);
            color: #f5e6d3;
        }
        .btn-enable:hover {
            background: linear-gradient(135deg, #35a035, #2a802a);
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        .message.success {
            background: rgba(42, 128, 42, 0.3);
            border: 2px solid #2a802a;
            color: #a0ffa0;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1 style="color: #c9a96e; text-align: center; margin-bottom: 30px;">🔐 Authentication Control</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <h2 style="color: #c9a96e; border-bottom: 2px solid rgba(139, 0, 0, 0.3); padding-bottom: 10px; margin-top: 30px;">Authentication Bypass</h2>
        <p style="color: #b8a090; font-size: 0.95em; margin-bottom: 20px;">
            Enable this to allow site access without login (for analysis/testing). Users can browse the site as "Guest".
        </p>
        
        <div class="status-box <?php echo $currentBypassStatus['enabled'] ? 'enabled' : 'disabled'; ?>">
            <h2 style="margin: 0 0 10px 0; color: <?php echo $currentBypassStatus['enabled'] ? '#66ff66' : '#ff6666'; ?>;">
                Bypass: <?php echo $currentBypassStatus['enabled'] ? 'ENABLED' : 'DISABLED'; ?>
            </h2>
            <?php if ($currentBypassStatus['enabled'] && $currentBypassStatus['enabled_until']): ?>
                <p style="margin: 0; color: #f5e6d3;">
                    Authentication bypass active until: <strong><?php echo date('F j, Y \a\t g:i A', strtotime($currentBypassStatus['enabled_until'])); ?></strong>
                </p>
            <?php else: ?>
                <p style="margin: 0; color: #f5e6d3;">Authentication required - bypass is disabled.</p>
            <?php endif; ?>
        </div>
        
        <?php if (!$currentBypassStatus['enabled']): ?>
        <form method="POST">
            <input type="hidden" name="action" value="enable_bypass">
            <div class="form-group">
                <label for="bypass_hours">Enable Bypass For (hours):</label>
                <input type="number" id="bypass_hours" name="bypass_hours" value="1" min="1" max="24" required>
            </div>
            <button type="submit" class="btn btn-enable">✅ Enable Authentication Bypass</button>
        </form>
        <?php else: ?>
        <form method="POST">
            <input type="hidden" name="action" value="disable_bypass">
            <button type="submit" class="btn btn-disable">🚫 Disable Bypass Immediately</button>
        </form>
        <?php endif; ?>
        
        <h2 style="color: #c9a96e; border-bottom: 2px solid rgba(139, 0, 0, 0.3); padding-bottom: 10px; margin-top: 40px;">Login Disable (Legacy)</h2>
        <p style="color: #b8a090; font-size: 0.95em; margin-bottom: 20px;">
            Disable the login functionality entirely (users can't log in even with valid credentials).
        </p>
        
        <div class="status-box <?php echo $currentStatus['disabled'] ? 'disabled' : 'enabled'; ?>">
            <h2 style="margin: 0 0 10px 0; color: <?php echo $currentStatus['disabled'] ? '#ff6666' : '#66ff66'; ?>;">
                Login: <?php echo $currentStatus['disabled'] ? 'DISABLED' : 'ENABLED'; ?>
            </h2>
            <?php if ($currentStatus['disabled'] && $currentStatus['disabled_until']): ?>
                <p style="margin: 0; color: #f5e6d3;">
                    Login disabled until: <strong><?php echo date('F j, Y \a\t g:i A', strtotime($currentStatus['disabled_until'])); ?></strong>
                </p>
            <?php else: ?>
                <p style="margin: 0; color: #f5e6d3;">Login is currently enabled.</p>
            <?php endif; ?>
        </div>
        
        <?php if (!$currentStatus['disabled']): ?>
        <form method="POST">
            <input type="hidden" name="action" value="disable">
            <div class="form-group">
                <label for="hours">Disable Login For (hours):</label>
                <input type="number" id="hours" name="hours" value="1" min="1" max="24" required>
            </div>
            <button type="submit" class="btn btn-disable">🚫 Disable Login</button>
        </form>
        <?php else: ?>
        <form method="POST">
            <input type="hidden" name="action" value="enable">
            <button type="submit" class="btn btn-enable">✅ Re-enable Login Immediately</button>
        </form>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(139, 0, 0, 0.3);">
            <p style="color: #b8a090; font-size: 0.9em; text-align: center;">
                <a href="../admin/admin_panel.php" style="color: #c9a96e;">← Back to Admin Panel</a>
            </p>
        </div>
    </div>
</body>
</html>

