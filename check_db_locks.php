<?php
// Check for database locks
include 'includes/connect.php';

if (!$conn) {
    echo "❌ Database connection failed<br>";
    exit;
}

echo "<h1>Database Lock Check</h1>";

// Check for locks
$result = mysqli_query($conn, "SHOW PROCESSLIST");
if ($result) {
    echo "<h2>Active Processes:</h2>";
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
}

// Check for table locks
$result = mysqli_query($conn, "SHOW OPEN TABLES WHERE In_use > 0");
if ($result) {
    echo "<h2>Locked Tables:</h2>";
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Database</th><th>Table</th><th>In_use</th><th>Name_locked</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['Database'] . "</td>";
            echo "<td>" . $row['Table'] . "</td>";
            echo "<td>" . $row['In_use'] . "</td>";
            echo "<td>" . $row['Name_locked'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>✅ No tables are locked</p>";
    }
}

// Check for long-running queries
$result = mysqli_query($conn, "SELECT * FROM INFORMATION_SCHEMA.PROCESSLIST WHERE TIME > 5 AND COMMAND != 'Sleep'");
if ($result) {
    echo "<h2>Long-running Queries (>5 seconds):</h2>";
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Id</th><th>User</th><th>Host</th><th>db</th><th>Command</th><th>Time</th><th>State</th><th>Info</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['ID'] . "</td>";
            echo "<td>" . $row['USER'] . "</td>";
            echo "<td>" . $row['HOST'] . "</td>";
            echo "<td>" . $row['DB'] . "</td>";
            echo "<td>" . $row['COMMAND'] . "</td>";
            echo "<td>" . $row['TIME'] . "</td>";
            echo "<td>" . $row['STATE'] . "</td>";
            echo "<td>" . htmlspecialchars($row['INFO']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>✅ No long-running queries</p>";
    }
}

mysqli_close($conn);
?>

