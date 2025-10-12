<?php
/**
 * Simple Email Helper - Uses Server's Built-in Mail
 * For hosts that have mail() configured
 */

/**
 * Send email using server's built-in mail() function
 * Uses vbn.talkingheads.video email
 */
function send_email($to, $subject, $body, $from_email = 'admin@vbn.talkingheads.video') {
    $from = "Valley by Night <$from_email>";
    
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: ' . $from . "\r\n" .
                'Reply-To: ' . $from . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
    
    $success = mail($to, $subject, $body, $headers);
    
    if (!$success) {
        error_log("Email Error: Failed to send email to $to");
    }
    
    return $success;
}

/**
 * Send verification email to new user
 */
function send_verification_email($email, $username, $token) {
    // Get base URL
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
            body { font-family: Georgia, serif; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: #2a1515; border: 3px solid #8B0000; border-radius: 10px; padding: 40px; }
            h1 { color: #f5e6d3; font-size: 2em; text-align: center; }
            .btn { display: inline-block; background: #8B0000; color: #f5e6d3; padding: 15px 35px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
            .link { color: #b8a090; word-break: break-all; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🦇 Welcome to the Night</h1>
            <p>Greetings, <strong>" . htmlspecialchars($username) . "</strong>,</p>
            <p>Your account has been created for <strong>Valley by Night</strong>. To complete your registration and enter the chronicle, please verify your email address.</p>
            <div style='text-align: center;'>
                <a href='" . htmlspecialchars($verification_link) . "' class='btn'>Verify Email Address</a>
            </div>
            <p>Or copy this link: <span class='link'>" . htmlspecialchars($verification_link) . "</span></p>
            <p><strong>This link expires in 24 hours.</strong></p>
            <p>If you didn't create this account, ignore this email.</p>
        </div>
    </body>
    </html>
    ";
    
    return send_email($email, $subject, $body);
}
?>

