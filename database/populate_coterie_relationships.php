<?php
/**
 * Populate Coterie and Relationships Data
 * 
 * Reads extracted data from JSON analysis and populates the database tables
 * Usage: php populate_coterie_relationships.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once __DIR__ . '/../includes/connect.php';

$extractedDataFile = __DIR__ . '/../docs/json-analysis/extracted-data.json';

echo "=================================================================\n";
echo "Populate Coterie & Relationships Data\n";
echo "=================================================================\n\n";

// Check if extracted data file exists
if (!file_exists($extractedDataFile)) {
    echo "❌ Error: Extracted data file not found: $extractedDataFile\n";
    echo "   Please run: node scripts/json-analysis/extract-all.js\n";
    exit(1);
}

// Read extracted data
$extractedData = json_decode(file_get_contents($extractedDataFile), true);

if (!$extractedData || !isset($extractedData['characters'])) {
    echo "❌ Error: Invalid extracted data format\n";
    exit(1);
}

echo "Loaded extracted data for " . count($extractedData['characters']) . " characters\n\n";

// Get all existing characters for name matching
echo "Loading existing characters from database...\n";
$charactersSql = "SELECT id, character_name FROM characters";
$charactersResult = $conn->query($charactersSql);
$existingCharacters = [];
while ($row = $charactersResult->fetch_assoc()) {
    $existingCharacters[] = $row;
}
echo "Found " . count($existingCharacters) . " existing characters\n\n";

// Function to normalize character name for matching
function normalizeCharacterName($name) {
    return trim(preg_replace('/\s+/', ' ', preg_replace('/\([^)]+\)/', '', $name)));
}

// Function to find character ID by name
function findCharacterId($characterName, $existingCharacters) {
    $normalized = strtolower(normalizeCharacterName($characterName));
    
    foreach ($existingCharacters as $char) {
        $charNormalized = strtolower(normalizeCharacterName($char['character_name']));
        
        // Exact match
        if ($charNormalized === $normalized) {
            return $char['id'];
        }
        
        // Partial match
        if (strpos($normalized, $charNormalized) !== false || strpos($charNormalized, $normalized) !== false) {
            return $char['id'];
        }
        
        // Match last name
        $nameParts = explode(' ', $normalized);
        $charParts = explode(' ', $charNormalized);
        if (count($nameParts) > 1 && count($charParts) > 1) {
            if (end($nameParts) === end($charParts)) {
                return $char['id'];
            }
        }
    }
    
    return null;
}

try {
    $conn->begin_transaction();
    
    $coteriesInserted = 0;
    $relationshipsInserted = 0;
    $charactersProcessed = 0;
    
    foreach ($extractedData['characters'] as $characterData) {
        $characterName = $characterData['character_name'];
        $characterId = findCharacterId($characterName, $existingCharacters);
        
        if (!$characterId) {
            echo "⚠️  Character not found in database: $characterName\n";
            continue;
        }
        
        $charactersProcessed++;
        
        // Prepare JSON data for Coterie and Relationships columns
        $coterieJson = !empty($characterData['coteries']) ? json_encode($characterData['coteries'], JSON_UNESCAPED_UNICODE) : null;
        $relationshipsJson = !empty($characterData['relationships']) ? json_encode($characterData['relationships'], JSON_UNESCAPED_UNICODE) : null;
        
        // Update characters table with Coterie and Relationships JSON columns
        $updateSql = "UPDATE characters SET Coterie = ?, Relationships = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssi", $coterieJson, $relationshipsJson, $characterId);
        
        if ($stmt->execute()) {
            if ($coterieJson) {
                $coteriesInserted += count($characterData['coteries']);
            }
            if ($relationshipsJson) {
                $relationshipsInserted += count($characterData['relationships']);
            }
        } else {
            echo "⚠️  Warning: Could not update $characterName: " . $stmt->error . "\n";
        }
        $stmt->close();
        
        // Also insert into separate tables (optional - for querying)
        // Insert coteries into character_coteries table
        if (!empty($characterData['coteries'])) {
            foreach ($characterData['coteries'] as $coterie) {
                $sql = "INSERT INTO character_coteries 
                        (character_id, coterie_name, coterie_type, role, description, source_field)
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        coterie_type = VALUES(coterie_type),
                        role = VALUES(role),
                        description = VALUES(description),
                        source_field = VALUES(source_field)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssss",
                    $characterId,
                    $coterie['name'],
                    $coterie['type'],
                    $coterie['role'],
                    $coterie['description'],
                    $coterie['source']
                );
                
                if ($stmt->execute()) {
                    // Already counted above
                }
                $stmt->close();
            }
        }
        
        // Insert relationships into character_relationships table
        if (!empty($characterData['relationships'])) {
            foreach ($characterData['relationships'] as $relationship) {
                // Try to find related character ID
                $relatedCharacterId = null;
                if (!empty($relationship['character_name'])) {
                    $relatedCharacterId = findCharacterId($relationship['character_name'], $existingCharacters);
                }
                
                $sql = "INSERT INTO character_relationships 
                        (character_id, related_character_id, related_character_name, 
                         relationship_type, relationship_subtype, strength, description, source_field)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissssss",
                    $characterId,
                    $relatedCharacterId,
                    $relationship['character_name'],
                    $relationship['type'],
                    $relationship['subtype'] ?? null,
                    $relationship['strength'] ?? null,
                    $relationship['description'] ?? null,
                    $relationship['source']
                );
                
                if ($stmt->execute()) {
                    // Already counted above
                }
                $stmt->close();
            }
        }
        
        echo "✅ Processed $characterName: " . 
             count($characterData['coteries']) . " coteries, " . 
             count($characterData['relationships']) . " relationships\n";
    }
    
    $conn->commit();
    
    echo "\n=================================================================\n";
    echo "Population completed successfully!\n";
    echo "=================================================================\n\n";
    echo "Statistics:\n";
    echo "- Characters processed: $charactersProcessed\n";
    echo "- Coteries inserted: $coteriesInserted\n";
    echo "- Relationships inserted: $relationshipsInserted\n\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Population failed: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();

