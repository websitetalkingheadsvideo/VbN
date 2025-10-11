<?php
/**
 * Import Items from JSON Database
 * Run this once to populate the items table with all available items
 */

require_once 'includes/connect.php';

// Read the JSON file
$jsonFile = 'Items Database.json';

if (!file_exists($jsonFile)) {
    die("❌ Error: Items Database.json not found!\n");
}

$jsonData = file_get_contents($jsonFile);
$items = json_decode($jsonData, true);

if (!$items) {
    die("❌ Error: Failed to parse JSON file!\n");
}

echo "📦 Starting import of " . count($items) . " items...\n\n";

$imported = 0;
$errors = 0;

foreach ($items as $item) {
    try {
        // Prepare statement
        $stmt = $conn->prepare("
            INSERT INTO items (name, type, category, damage, `range`, requirements, description, rarity, price, image, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Convert requirements array to JSON string
        $requirements = json_encode($item['requirements']);
        
        // Bind parameters
        $stmt->bind_param(
            'sssssssssss',
            $item['name'],
            $item['type'],
            $item['category'],
            $item['damage'],
            $item['range'],
            $requirements,
            $item['description'],
            $item['rarity'],
            $item['price'],
            $item['image'],
            $item['notes']
        );
        
        // Execute
        if ($stmt->execute()) {
            $imported++;
            echo "✅ Imported: {$item['name']} ({$item['category']})\n";
        } else {
            $errors++;
            echo "❌ Failed: {$item['name']} - " . $stmt->error . "\n";
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $errors++;
        echo "❌ Error importing {$item['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\n📊 Import Complete:\n";
echo "   ✅ Successfully imported: $imported items\n";
if ($errors > 0) {
    echo "   ❌ Errors: $errors items\n";
}

$conn->close();
?>

