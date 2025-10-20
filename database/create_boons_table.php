<?php
/**
 * Create Boons Table for Vampire: The Masquerade Chronicle
 * Tracks favors, debts, and boons between vampires
 */

require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Create the boons table
$create_boons_table = "
CREATE TABLE IF NOT EXISTS boons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creditor_id INT NOT NULL COMMENT 'Character who is owed the boon',
    debtor_id INT NOT NULL COMMENT 'Character who owes the boon',
    boon_type ENUM('trivial', 'minor', 'major', 'life') NOT NULL COMMENT 'Type of boon based on VtM rules',
    description TEXT NOT NULL COMMENT 'Description of what the boon is for',
    status ENUM('active', 'fulfilled', 'cancelled', 'disputed') DEFAULT 'active' COMMENT 'Current status of the boon',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the boon was created',
    fulfilled_date TIMESTAMP NULL COMMENT 'When the boon was fulfilled',
    due_date DATE NULL COMMENT 'Optional due date for the boon',
    notes TEXT NULL COMMENT 'Additional notes about the boon',
    created_by INT NOT NULL COMMENT 'Admin who created this boon entry',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (creditor_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (debtor_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_creditor (creditor_id),
    INDEX idx_debtor (debtor_id),
    INDEX idx_status (status),
    INDEX idx_boon_type (boon_type),
    INDEX idx_created_date (created_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks boons and favors between vampires'
";

if (mysqli_query($conn, $create_boons_table)) {
    echo "✅ Boons table created successfully!\n";
} else {
    echo "❌ Error creating boons table: " . mysqli_error($conn) . "\n";
}

// Create a view for easier boon queries
$create_boons_view = "
CREATE OR REPLACE VIEW boons_detailed AS
SELECT 
    b.id,
    b.boon_type,
    b.description,
    b.status,
    b.created_date,
    b.fulfilled_date,
    b.due_date,
    b.notes,
    creditor.character_name as creditor_name,
    debtor.character_name as debtor_name,
    creator.username as created_by_name,
    DATEDIFF(COALESCE(b.fulfilled_date, NOW()), b.created_date) as days_outstanding
FROM boons b
JOIN characters creditor ON b.creditor_id = creditor.id
JOIN characters debtor ON b.debtor_id = debtor.id
JOIN users creator ON b.created_by = creator.id
ORDER BY b.created_date DESC
";

if (mysqli_query($conn, $create_boons_view)) {
    echo "✅ Boons detailed view created successfully!\n";
} else {
    echo "❌ Error creating boons view: " . mysqli_error($conn) . "\n";
}

// Insert some sample data for testing
$sample_boons = [
    [
        'creditor_name' => 'Duke Tiki',
        'debtor_name' => 'Bayside Bob',
        'boon_type' => 'minor',
        'description' => 'Information about the Prince\'s movements',
        'status' => 'active',
        'notes' => 'Bob owes Duke for the embrace'
    ],
    [
        'creditor_name' => 'Bayside Bob',
        'debtor_name' => 'Duke Tiki',
        'boon_type' => 'trivial',
        'description' => 'Art supplies and materials',
        'status' => 'fulfilled',
        'notes' => 'Bob provided high-quality carving tools'
    ]
];

echo "\n📝 Inserting sample boon data...\n";

foreach ($sample_boons as $boon) {
    // Get character IDs
    $creditor_query = "SELECT id FROM characters WHERE character_name = '" . mysqli_real_escape_string($conn, $boon['creditor_name']) . "'";
    $debtor_query = "SELECT id FROM characters WHERE character_name = '" . mysqli_real_escape_string($conn, $boon['debtor_name']) . "'";
    
    $creditor_result = mysqli_query($conn, $creditor_query);
    $debtor_result = mysqli_query($conn, $debtor_query);
    
    if ($creditor_result && $debtor_result && 
        mysqli_num_rows($creditor_result) > 0 && mysqli_num_rows($debtor_result) > 0) {
        
        $creditor = mysqli_fetch_assoc($creditor_result);
        $debtor = mysqli_fetch_assoc($debtor_result);
        
        // Get admin user ID (assuming first admin user)
        $admin_query = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
        $admin_result = mysqli_query($conn, $admin_query);
        $admin = mysqli_fetch_assoc($admin_result);
        
        $insert_boon = "INSERT INTO boons (creditor_id, debtor_id, boon_type, description, status, notes, created_by) 
                        VALUES ({$creditor['id']}, {$debtor['id']}, '{$boon['boon_type']}', 
                                '" . mysqli_real_escape_string($conn, $boon['description']) . "', 
                                '{$boon['status']}', 
                                '" . mysqli_real_escape_string($conn, $boon['notes']) . "', 
                                {$admin['id']})";
        
        if (mysqli_query($conn, $insert_boon)) {
            echo "✅ Sample boon created: {$boon['creditor_name']} → {$boon['debtor_name']}\n";
        } else {
            echo "❌ Error creating sample boon: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "⚠️  Could not find characters for sample boon: {$boon['creditor_name']} → {$boon['debtor_name']}\n";
    }
}

echo "\n🎉 Boon tracker database setup complete!\n";
echo "\n📋 Boon Types (VtM Rules):\n";
echo "  • Trivial: Small favors, information, minor services\n";
echo "  • Minor: Significant favors, protection, resources\n";
echo "  • Major: Major political support, life-threatening risks\n";
echo "  • Life: The ultimate boon - saving someone's unlife\n";

mysqli_close($conn);
?>
