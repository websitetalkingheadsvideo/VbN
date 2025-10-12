<?php
// Check database status and table structure
session_start();
$_SESSION['user_id'] = 1;

echo "<h1>Database Status Check</h1>";

include 'includes/connect.php';

if (!$conn) {
    echo "❌ Database connection failed<br>";
    exit;
}

echo "✅ Database connected<br>";

// Check database status
echo "<h2>Database Status:</h2>";
$result = mysqli_query($conn, "SHOW STATUS LIKE 'Threads_connected'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Threads connected: " . $row['Value'] . "<br>";
}

$result = mysqli_query($conn, "SHOW STATUS LIKE 'Threads_running'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Threads running: " . $row['Value'] . "<br>";
}

// Check table structure
echo "<h2>Characters Table Structure:</h2>";
$result = mysqli_query($conn, "DESCRIBE characters");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error describing table: " . mysqli_error($conn) . "<br>";
}

// Check for locks
echo "<h2>Checking for locks:</h2>";
$result = mysqli_query($conn, "SHOW PROCESSLIST");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Id</th><th>User</th><th>Host</th><th>db</th><th>Command</th><th>Time</th><th>State</th><th>Info</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Id'] . "</td>";
        echo "<td>" . $row['User'] . "</td>";
        echo "<td>" . $row['Host'] . "</td>";
        echo "<td>" . $row['db'] . "</td>";
        echo "<td>" . $row['Command'] . "</td>";
        echo "<td>" . $row['Time'] . "</td>";
        echo "<td>" . $row['State'] . "</td>";
        echo "<td>" . htmlspecialchars($row['Info']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error checking processlist: " . mysqli_error($conn) . "<br>";
}

// Test a simple query
echo "<h2>Testing simple query:</h2>";
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM characters");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "✅ Simple query works. Character count: " . $row['count'] . "<br>";
} else {
    echo "❌ Simple query failed: " . mysqli_error($conn) . "<br>";
}

// Test table locks
echo "<h2>Testing table locks:</h2>";
$result = mysqli_query($conn, "SHOW OPEN TABLES WHERE In_use > 0");
if ($result) {
    $count = mysqli_num_rows($result);
    if ($count == 0) {
        echo "✅ No tables are locked<br>";
    } else {
        echo "⚠️ " . $count . " tables are locked:<br>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "- " . $row['Table'] . " (In_use: " . $row['In_use'] . ")<br>";
        }
    }
} else {
    echo "❌ Error checking table locks: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
