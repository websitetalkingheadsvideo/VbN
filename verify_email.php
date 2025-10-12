<?php
/**
 * Email Verification Handler
 * Verifies user email address via token link
 */
session_start();
require_once __DIR__ . '/includes/connect.php';

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['error'] = "Invalid verification link";
    header("Location: login.php");
    exit();
}

// Look up user by token
$stmt = mysqli_prepare($conn, 
    "SELECT id, username, email, email_verified, verification_expires 
     FROM users 
     WHERE verification_token = ? 
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Check if token exists
if (!$user) {
    $_SESSION['error'] = "Invalid or expired verification link";
    header("Location: login.php");
    exit();
}

// Check if already verified
if ($user['email_verified']) {
    $_SESSION['success'] = "Email already verified. You can now login.";
    header("Location: login.php");
    exit();
}

// Check if token expired
$now = date('Y-m-d H:i:s');
if ($now > $user['verification_expires']) {
    $_SESSION['error'] = "Verification link has expired. Please contact support.";
    header("Location: login.php");
    exit();
}

// Mark email as verified
$stmt = mysqli_prepare($conn, 
    "UPDATE users 
     SET email_verified = TRUE, 
         verification_token = NULL, 
         verification_expires = NULL 
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user['id']);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);

if ($success) {
    $_SESSION['success'] = "Email verified successfully! You can now login to enter the chronicle.";
} else {
    $_SESSION['error'] = "Verification failed. Please try again or contact support.";
}

header("Location: login.php");
exit();
?>

