<?php
/**
 * Registration Process Handler
 * Validates input, creates user account, sends verification email
 */
session_start();
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/email_helper_simple.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

// Username validation
if (empty($username)) {
    $errors[] = "Username is required";
} elseif (strlen($username) < 3 || strlen($username) > 50) {
    $errors[] = "Username must be between 3 and 50 characters";
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = "Username can only contain letters, numbers, and underscores";
}

// Email validation
if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

// Password validation
if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters";
}

// Confirm password
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// Check for existing username
if (empty($errors)) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Username already exists";
    }
    mysqli_stmt_close($stmt);
}

// Check for existing email
if (empty($errors)) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Email already registered";
    }
    mysqli_stmt_close($stmt);
}

// If validation failed, redirect back with errors
if (!empty($errors)) {
    $_SESSION['error'] = implode(". ", $errors);
    header("Location: register.php");
    exit();
}

// Generate verification token
$verification_token = bin2hex(random_bytes(32)); // 64 character hex string
$verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user into database
$stmt = mysqli_prepare($conn, 
    "INSERT INTO users (username, email, password, role, email_verified, verification_token, verification_expires, created_at) 
     VALUES (?, ?, ?, 'player', FALSE, ?, ?, NOW())"
);

mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $password_hash, $verification_token, $verification_expires);

if (!mysqli_stmt_execute($stmt)) {
    $error_message = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['error'] = "Registration failed: " . $error_message;
    header("Location: register.php");
    exit();
}

$user_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// Send verification email
$email_sent = send_verification_email($email, $username, $verification_token);

if ($email_sent) {
    $_SESSION['success'] = "Account created! Please check your email to verify your account.";
} else {
    $_SESSION['success'] = "Account created! Email verification is temporarily unavailable. Please contact support.";
}

mysqli_close($conn);

// Redirect to login page with success message
header("Location: login.php");
exit();
?>

