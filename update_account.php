<?php
/**
 * Update Account Handler — Valley by Night
 * Handles email updates and password changes with CSRF + validation
 */
session_start();
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/email_helper_simple.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

function fail($msg) {
    $_SESSION['error'] = $msg;
    header('Location: account.php');
    exit();
}

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    fail('Invalid session token. Please try again.');
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'email') {
    $new_email = trim($_POST['new_email'] ?? '');
    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        fail('Please provide a valid email address.');
    }

    $current = db_fetch_one($conn, 'SELECT username, email FROM users WHERE id = ?', 'i', [$user_id]);
    if (!$current) fail('Account not found.');
    if (strcasecmp($current['email'], $new_email) === 0) {
        fail('New email matches your current email.');
    }

    // Ensure unique
    $exists = db_fetch_one($conn, 'SELECT id FROM users WHERE email = ?', 's', [$new_email]);
    if ($exists) fail('That email is already registered.');

    // Set verification token and mark unverified
    $verification_token = bin2hex(random_bytes(32));
    $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $ok = db_execute(
        $conn,
        'UPDATE users SET email = ?, email_verified = 0, verification_token = ?, verification_expires = ? WHERE id = ?',
        'sssi',
        [$new_email, $verification_token, $verification_expires, $user_id]
    );
    if ($ok === false) fail('Failed to update email.');

    // Send verification email
    $sent = send_verification_email($new_email, $current['username'], $verification_token);
    $_SESSION['success'] = $sent
        ? 'Email updated. Please check your inbox to verify your new address.'
        : 'Email updated. Verification email could not be sent; please contact support to verify.';

    header('Location: account.php');
    exit();
}

if ($action === 'password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        fail('All password fields are required.');
    }
    if (strlen($new_password) < 8) fail('New password must be at least 8 characters.');
    if ($new_password !== $confirm_password) fail('New passwords do not match.');

    $user = db_fetch_one($conn, 'SELECT password FROM users WHERE id = ?', 'i', [$user_id]);
    if (!$user) fail('Account not found.');
    if (!password_verify($current_password, $user['password'])) fail('Current password is incorrect.');

    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $ok = db_execute($conn, 'UPDATE users SET password = ? WHERE id = ?', 'si', [$hash, $user_id]);
    if ($ok === false) fail('Failed to change password.');

    $_SESSION['success'] = 'Password updated successfully.';
    header('Location: account.php');
    exit();
}

fail('Unknown action.');

