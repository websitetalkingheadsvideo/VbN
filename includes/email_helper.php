<?php
/**
 * Email Helper Functions
 * Handles email sending via SMTP for Valley by Night
 */

/**
 * Send email using SMTP settings from config.env
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body HTML email body
 * @param string $from_name Sender name (default: Valley by Night)
 * @return bool Success status
 */
function send_email($to, $subject, $body, $from_name = 'Valley by Night') {
    // Load environment variables
    $env_file = __DIR__ . '/../config.env';
    if (!file_exists($env_file)) {
        error_log("Email Error: config.env not found");
        return false;
    }
    
    $env = parse_ini_file($env_file);
    
    // Check if SMTP is configured
    if (empty($env['SMTP_HOST']) || empty($env['SMTP_USER']) || empty($env['SMTP_PASS'])) {
        error_log("Email Error: SMTP not configured in config.env");
        return false;
    }
    
    $smtp_host = $env['SMTP_HOST'];
    $smtp_port = $env['SMTP_PORT'] ?? 587;
    $smtp_user = $env['SMTP_USER'];
    $smtp_pass = $env['SMTP_PASS'];
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $from_name <$smtp_user>" . "\r\n";
    
    // Use PHP's mail() function with SMTP settings
    // Note: This requires proper SMTP configuration in php.ini or using a library
    // For production, consider using PHPMailer or similar
    
    // Simple mail() approach (requires server SMTP config)
    $success = mail($to, $subject, $body, $headers);
    
    if (!$success) {
        error_log("Email Error: Failed to send email to $to");
    }
    
    return $success;
}

/**
 * Send verification email to new user
 * 
 * @param string $email User's email address
 * @param string $username User's username
 * @param string $token Verification token
 * @return bool Success status
 */
function send_verification_email($email, $username, $token) {
    // Get base URL (handles localhost and live server)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_path = dirname($_SERVER['SCRIPT_NAME']);
    $base_url = $protocol . '://' . $host . $base_path;
    $base_url = rtrim($base_url, '/') . '/';
    
    $verification_link = $base_url . "verify_email.php?token=" . urlencode($token);
    
    $subject = "🦇 Verify Your Valley by Night Account";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: 'Georgia', serif;
                background: linear-gradient(135deg, #0d0606 0%, #1a0f0f 50%, #0d0606 100%);
                color: #d4c4b0;
                padding: 20px;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
                border: 3px solid #8B0000;
                border-radius: 10px;
                padding: 40px;
            }
            h1 {
                font-family: 'Georgia', serif;
                color: #f5e6d3;
                font-size: 2em;
                text-align: center;
                margin-bottom: 10px;
            }
            p {
                line-height: 1.6;
                margin: 15px 0;
            }
            .verify-btn {
                display: inline-block;
                background: linear-gradient(135deg, #8B0000 0%, #600000 100%);
                color: #f5e6d3;
                padding: 15px 35px;
                border: 2px solid #b30000;
                border-radius: 5px;
                text-decoration: none;
                font-weight: 600;
                font-size: 1.1em;
                text-align: center;
                margin: 20px 0;
            }
            .footer {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid rgba(139, 0, 0, 0.3);
                font-size: 0.9em;
                color: #888;
                text-align: center;
            }
            .link {
                color: #b8a090;
                word-break: break-all;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <h1>🦇 Welcome to the Night</h1>
            <p>Greetings, <strong>" . htmlspecialchars($username) . "</strong>,</p>
            
            <p>Your account has been created for <strong>Valley by Night</strong>. To complete your registration and enter the chronicle, please verify your email address.</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . htmlspecialchars($verification_link) . "' class='verify-btn'>Verify Email Address</a>
            </div>
            
            <p>Or copy and paste this link into your browser:</p>
            <p class='link'>" . htmlspecialchars($verification_link) . "</p>
            
            <p><strong>This verification link will expire in 24 hours.</strong></p>
            
            <p>If you did not create this account, you may safely ignore this email.</p>
            
            <div class='footer'>
                <p>Valley by Night - A Vampire Tale</p>
                <p>&copy; 2025 All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_email($email, $subject, $body);
}
?>

