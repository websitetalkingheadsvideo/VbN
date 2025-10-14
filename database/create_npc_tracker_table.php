<?php
/**
 * Create NPC Tracker Table
 * Run this once to set up the database table for tracking NPCs
 */

require_once '../includes/connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS npc_tracker (
        id INT PRIMARY KEY AUTO_INCREMENT,
        character_name VARCHAR(255) NOT NULL,
        clan VARCHAR(100),
        linked_to VARCHAR(255) NOT NULL,
        introduced_in VARCHAR(255),
        status VARCHAR(50) DEFAULT '💡 Concept Only',
        summary TEXT,
        plot_hooks TEXT,
        mentioned_details TEXT,
        submitted_by INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    echo "✅ NPC Tracker table created successfully!<br>";
    echo "You can now use the NPC Tracker pages in /admin/<br>";
    echo "<a href='../admin/npc_tracker.php'>View NPC Tracker</a>";
    
} catch (PDOException $e) {
    echo "❌ Error creating table: " . $e->getMessage();
}
?>

