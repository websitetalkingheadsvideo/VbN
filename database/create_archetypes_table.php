<?php
/**
 * Create Archetypes Table
 * 
 * Creates the archetypes table with id, name, and description columns
 * This table will store all personality archetypes used for nature and demeanor
 */

require_once __DIR__ . '/../includes/connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Archetypes Table</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            background: #1a0f0f; 
            color: #d4c4b0; 
        }
        .success { 
            color: #0d7a4a; 
            padding: 10px; 
            background: rgba(13, 122, 74, 0.2); 
            border: 1px solid #0d7a4a; 
            margin: 10px 0; 
        }
        .error { 
            color: #8B0000; 
            padding: 10px; 
            background: rgba(139, 0, 0, 0.2); 
            border: 1px solid #8B0000; 
            margin: 10px 0; 
        }
        .info { 
            color: #b8a090; 
            padding: 10px; 
            background: rgba(184, 160, 144, 0.2); 
            border: 1px solid #b8a090; 
            margin: 10px 0; 
        }
        .warning {
            color: #ffa500;
            padding: 10px;
            background: rgba(255, 165, 0, 0.2);
            border: 1px solid #ffa500;
            margin: 10px 0;
        }
        h2 { color: #d4c4b0; margin-top: 30px; }
        code { 
            background: rgba(0, 0, 0, 0.3); 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>📋 Create Archetypes Table</h1>

<?php

if (!$conn) {
    echo '<div class="error">❌ Database connection failed</div>';
    exit;
}

try {
    // Check if table already exists
    $check_table = "SHOW TABLES LIKE 'archetypes'";
    $result = mysqli_query($conn, $check_table);
    $table_exists = $result && mysqli_num_rows($result) > 0;
    
    if ($table_exists) {
        echo '<div class="warning">⚠️ Table "archetypes" already exists</div>';
        echo '<div class="info">If you want to recreate it, drop it first or use a different approach.</div>';
        
        // Show current structure
        $describe = "DESCRIBE archetypes";
        $desc_result = mysqli_query($conn, $describe);
        if ($desc_result) {
            echo '<h2>Current Table Structure:</h2>';
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse; margin: 10px 0;">';
            echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
            while ($row = mysqli_fetch_assoc($desc_result)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['Field']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Type']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Null']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Key']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Default'] ?? 'NULL') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            mysqli_free_result($desc_result);
        }
    } else {
        // Create the table
        echo '<h2>Creating Archetypes Table</h2>';
        
        $create_table = "CREATE TABLE IF NOT EXISTS archetypes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description VARCHAR(1000) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (mysqli_query($conn, $create_table)) {
            echo '<div class="success">✅ Archetypes table created successfully!</div>';
            echo '<div class="info">📋 Table structure:</div>';
            echo '<ul>';
            echo '<li><code>id</code> - INT AUTO_INCREMENT PRIMARY KEY</li>';
            echo '<li><code>name</code> - VARCHAR(50) NOT NULL UNIQUE</li>';
            echo '<li><code>description</code> - VARCHAR(1000) NULL</li>';
            echo '<li><code>created_at</code> - TIMESTAMP</li>';
            echo '<li><code>updated_at</code> - TIMESTAMP</li>';
            echo '</ul>';
        } else {
            throw new Exception("Failed to create table: " . mysqli_error($conn));
        }
    }
    
    echo '<h2>Next Steps</h2>';
    echo '<div class="info">1. Run <code>database/populate_archetypes.php</code> to populate the table with merged nature/demeanor values</div>';
    echo '<div class="info">2. Run <code>database/update_dropdowns_to_use_archetypes.php</code> to update the form dropdowns</div>';
    
} catch (Exception $e) {
    echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>

</body>
</html>

