<?php
// Check existing users and create login accounts if needed
echo "<h1>User Account Check</h1>";

include 'includes/connect.php';

if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected!</p>";

// Check if users table exists and has data
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $user_count = $row['count'];
    echo "<p>Users in database: $user_count</p>";
    
    if ($user_count > 0) {
        echo "<h2>Existing Users:</h2>";
        $users_result = mysqli_query($conn, "SELECT id, username, email, role, created_at FROM users");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th></tr>";
        while ($user = mysqli_fetch_assoc($users_result)) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No users found in database!</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Error checking users: " . mysqli_error($conn) . "</p>";
}

echo "<h2>Creating/Updating Login Accounts...</h2>";

// Create admin user with proper password hash
$admin_username = "admin";
$admin_password = "password"; // Simple password for testing
$admin_email = "admin@example.com";
$admin_role = "admin";

$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if admin exists
$check_admin = mysqli_query($conn, "SELECT id FROM users WHERE username = '$admin_username'");
if (mysqli_num_rows($check_admin) > 0) {
    // Update existing admin
    $update_admin = "UPDATE users SET password = '$hashed_password', email = '$admin_email', role = '$admin_role' WHERE username = '$admin_username'";
    if (mysqli_query($conn, $update_admin)) {
        echo "<p style='color: green;'>✅ Updated admin user</p>";
    } else {
        echo "<p style='color: red;'>❌ Error updating admin: " . mysqli_error($conn) . "</p>";
    }
} else {
    // Create new admin
    $insert_admin = "INSERT INTO users (username, password, email, role) VALUES ('$admin_username', '$hashed_password', '$admin_email', '$admin_role')";
    if (mysqli_query($conn, $insert_admin)) {
        echo "<p style='color: green;'>✅ Created admin user</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating admin: " . mysqli_error($conn) . "</p>";
    }
}

// Create test user
$test_username = "testuser";
$test_password = "password";
$test_email = "test@example.com";
$test_role = "user";

$test_hashed_password = password_hash($test_password, PASSWORD_DEFAULT);

$check_test = mysqli_query($conn, "SELECT id FROM users WHERE username = '$test_username'");
if (mysqli_num_rows($check_test) > 0) {
    // Update existing test user
    $update_test = "UPDATE users SET password = '$test_hashed_password', email = '$test_email', role = '$test_role' WHERE username = '$test_username'";
    if (mysqli_query($conn, $update_test)) {
        echo "<p style='color: green;'>✅ Updated test user</p>";
    } else {
        echo "<p style='color: red;'>❌ Error updating test user: " . mysqli_error($conn) . "</p>";
    }
} else {
    // Create new test user
    $insert_test = "INSERT INTO users (username, password, email, role) VALUES ('$test_username', '$test_hashed_password', '$test_email', '$test_role')";
    if (mysqli_query($conn, $insert_test)) {
        echo "<p style='color: green;'>✅ Created test user</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating test user: " . mysqli_error($conn) . "</p>";
    }
}

echo "<h2>Login Credentials:</h2>";
echo "<ul>";
echo "<li><strong>Admin:</strong> username: <code>admin</code>, password: <code>password</code></li>";
echo "<li><strong>Test User:</strong> username: <code>testuser</code>, password: <code>password</code></li>";
echo "</ul>";

echo "<h2>Test Login:</h2>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";

mysqli_close($conn);
?>
