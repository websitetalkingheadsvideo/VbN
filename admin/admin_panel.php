<?php
// Admin Panel - View All Characters
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - LOTN Character Creator</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #8b0000; color: white; padding: 20px; margin-bottom: 20px; }
        .user-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #8b0000; color: white; }
        .admin-only { background: #ffe6e6; }
        .user-characters { background: #e6f3ff; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #8b0000; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔧 Admin Panel - Character Management</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! (<?php echo $_SESSION['role']; ?>)</p>
    </div>

    <div class="nav">
        <a href="dashboard.php">← Dashboard</a>
        <a href="lotn_char_create.php">Create Character</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="user-info">
        <h3>🔐 Access Control Summary</h3>
        <ul>
            <li><strong>Admin Access:</strong> You can see and manage ALL characters from ALL users</li>
            <li><strong>Regular Users:</strong> Can only see their own characters</li>
            <li><strong>Character Ownership:</strong> Each character is linked to the user who created it</li>
        </ul>
    </div>

    <?php
    // Get all characters with user information
    $sql = "SELECT c.*, u.username, u.email, u.role as user_role 
            FROM characters c 
            JOIN users u ON c.user_id = u.id 
            ORDER BY c.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
    } else {
        $characters = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        if (empty($characters)) {
            echo "<p>No characters found in the database.</p>";
        } else {
            echo "<h3>📊 All Characters (" . count($characters) . " total)</h3>";
            echo "<table>";
            echo "<tr>";
            echo "<th>Character ID</th>";
            echo "<th>Character Name</th>";
            echo "<th>Player Name</th>";
            echo "<th>Clan</th>";
            echo "<th>Generation</th>";
            echo "<th>Owner (User)</th>";
            echo "<th>User Role</th>";
            echo "<th>Created</th>";
            echo "<th>Actions</th>";
            echo "</tr>";
            
            foreach ($characters as $char) {
                $row_class = ($char['user_role'] === 'admin') ? 'admin-only' : 'user-characters';
                echo "<tr class='$row_class'>";
                echo "<td>" . $char['id'] . "</td>";
                echo "<td><strong>" . htmlspecialchars($char['character_name']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($char['player_name']) . "</td>";
                echo "<td>" . htmlspecialchars($char['clan']) . "</td>";
                echo "<td>" . $char['generation'] . "th</td>";
                echo "<td>" . htmlspecialchars($char['username']) . "</td>";
                echo "<td>" . $char['user_role'] . "</td>";
                echo "<td>" . date('Y-m-d H:i', strtotime($char['created_at'])) . "</td>";
                echo "<td>";
                echo "<a href='view_character.php?id=" . $char['id'] . "'>View</a> | ";
                echo "<a href='edit_character.php?id=" . $char['id'] . "'>Edit</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    // Show user statistics
    $user_stats = mysqli_query($conn, "SELECT role, COUNT(*) as count FROM users GROUP BY role");
    if ($user_stats) {
        echo "<h3>👥 User Statistics</h3>";
        echo "<table>";
        echo "<tr><th>Role</th><th>Count</th></tr>";
        while ($stat = mysqli_fetch_assoc($user_stats)) {
            echo "<tr><td>" . $stat['role'] . "</td><td>" . $stat['count'] . "</td></tr>";
        }
        echo "</table>";
    }

    mysqli_close($conn);
    ?>

    <div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-left: 4px solid #8b0000;">
        <h4>🔍 How Character Access Works:</h4>
        <ol>
            <li><strong>Character Creation:</strong> When a user creates a character, it's automatically linked to their user_id</li>
            <li><strong>Regular User Access:</strong> Users can only see characters where user_id matches their session user_id</li>
            <li><strong>Admin Access:</strong> Admins can see ALL characters regardless of user_id</li>
            <li><strong>Database Security:</strong> The foreign key relationship ensures data integrity</li>
        </ol>
    </div>
</body>
</html>
