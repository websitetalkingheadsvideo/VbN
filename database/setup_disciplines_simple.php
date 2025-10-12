<?php
require_once 'includes/connect.php';

echo "Setting up disciplines and discipline_powers tables...\n";

try {
    // Create disciplines table
    $sql = "CREATE TABLE IF NOT EXISTS disciplines (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        category ENUM('Clan', 'BloodSorcery', 'Advanced') NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "✓ Created disciplines table\n";
    
    // Insert disciplines
    $disciplines = [
        // Clan Disciplines
        ['Animalism', 'Clan', 'The ability to communicate with and control animals'],
        ['Auspex', 'Clan', 'The power of supernatural perception and awareness'],
        ['Celerity', 'Clan', 'The ability to move and react at superhuman speeds'],
        ['Dominate', 'Clan', 'The power to control the minds of others'],
        ['Fortitude', 'Clan', 'The ability to resist damage and endure hardship'],
        ['Obfuscate', 'Clan', 'The power to hide from sight and become invisible'],
        ['Potence', 'Clan', 'The ability to possess superhuman physical strength'],
        ['Presence', 'Clan', 'The power to influence and charm others through sheer presence'],
        ['Protean', 'Clan', 'The ability to change form and shape'],
        
        // Blood Sorcery Disciplines
        ['Thaumaturgy', 'BloodSorcery', 'The art of blood magic and ritual'],
        ['Necromancy', 'BloodSorcery', 'The power to communicate with and control the dead'],
        ['Koldunic Sorcery', 'BloodSorcery', 'Elemental magic tied to specific locations'],
        
        // Advanced Disciplines
        ['Obtenebration', 'Advanced', 'The power to control and manipulate shadows'],
        ['Chimerstry', 'Advanced', 'The ability to create and maintain illusions'],
        ['Dementation', 'Advanced', 'The power to drive others to madness'],
        ['Quietus', 'Advanced', 'The art of silent assassination and poison'],
        ['Vicissitude', 'Advanced', 'The ability to reshape flesh and bone'],
        ['Serpentis', 'Advanced', 'The power of the serpent and hypnotic abilities'],
        ['Daimoinon', 'Advanced', 'The power to summon and control infernal entities'],
        ['Melpominee', 'Advanced', 'The power of song and musical influence'],
        ['Valeren', 'Advanced', 'The power of healing and spiritual guidance'],
        ['Mortis', 'Advanced', 'The power over death and decay']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO disciplines (name, category, description) VALUES (?, ?, ?)");
    foreach ($disciplines as $discipline) {
        $stmt->execute($discipline);
    }
    echo "✓ Inserted " . count($disciplines) . " disciplines\n";
    
    // Create discipline_powers table
    $sql = "CREATE TABLE IF NOT EXISTS discipline_powers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        discipline_id INT NOT NULL,
        power_level INT NOT NULL CHECK (power_level BETWEEN 1 AND 5),
        power_name VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        prerequisites JSON DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (discipline_id) REFERENCES disciplines(id) ON DELETE CASCADE,
        UNIQUE KEY unique_discipline_power (discipline_id, power_level)
    )";
    $conn->exec($sql);
    echo "✓ Created discipline_powers table\n";
    
    // Get discipline IDs for power insertion
    $stmt = $conn->query("SELECT id, name FROM disciplines ORDER BY id");
    $discipline_ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $discipline_ids[$row['name']] = $row['id'];
    }
    
    echo "✓ Found " . count($discipline_ids) . " disciplines in database\n";
    echo "Setup completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn = null;
?>
