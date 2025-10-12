<?php
/**
 * Add Email Verification Columns
 * Simple script to add the three required columns
 */

require_once __DIR__ . '/../includes/connect.php';

echo "<h2>Adding Email Verification Columns...</h2>";

// Check if columns already exist
$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'email_verified'");
if (mysqli_num_rows($check) > 0) {
    echo "<p style='color: orange;'>⚠️ Columns already exist. Skipping.</p>";
    echo "<p><a href='check_users_table.php'>Check Table Structure</a></p>";
    exit();
}

// Add email_verified column
echo "<p>Adding email_verified column...</p>";
$sql1 = "ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER email";
if (mysqli_query($conn, $sql1)) {
    echo "<p style='color: green;'>✓ email_verified added</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
}

// Add verification_token column
echo "<p>Adding verification_token column...</p>";
$sql2 = "ALTER TABLE users ADD COLUMN verification_token VARCHAR(64) NULL AFTER email_verified";
if (mysqli_query($conn, $sql2)) {
    echo "<p style='color: green;'>✓ verification_token added</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
}

// Add verification_expires column
echo "<p>Adding verification_expires column...</p>";
$sql3 = "ALTER TABLE users ADD COLUMN verification_expires TIMESTAMP NULL AFTER verification_token";
if (mysqli_query($conn, $sql3)) {
    echo "<p style='color: green;'>✓ verification_expires added</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
}

// Add index for faster lookups
echo "<p>Adding index on verification_token...</p>";
$sql4 = "CREATE INDEX idx_verification_token ON users(verification_token)";
if (mysqli_query($conn, $sql4)) {
    echo "<p style='color: green;'>✓ Index added</p>";
} else {
    echo "<p style='color: red;'>✗ Error (may already exist): " . mysqli_error($conn) . "</p>";
}

// Verify existing users
echo "<p>Setting existing users as verified...</p>";
$sql5 = "UPDATE users SET email_verified = TRUE WHERE email_verified IS NULL OR email_verified = FALSE";
if (mysqli_query($conn, $sql5)) {
    $affected = mysqli_affected_rows($conn);
    echo "<p style='color: green;'>✓ Updated $affected existing users</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);

echo "<hr>";
echo "<h3>✅ Migration Complete!</h3>";
echo "<p><a href='check_users_table.php'>Verify Table Structure</a></p>";
echo "<p><a href='../register.php'>Go to Registration Page</a></p>";
?>

