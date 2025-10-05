<?php
// Test script to verify database integration
require_once 'includes/connect.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=lotn_characters", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "<h1>LOTN Database Integration Test</h1>\n";

// Test 1: Check if tables exist
echo "<h2>Test 1: Database Tables</h2>\n";
$tables = ['disciplines', 'discipline_powers', 'clans', 'clan_disciplines', 'character_discipline_powers'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "✅ Table '$table' exists with $count records<br>\n";
    } catch (Exception $e) {
        echo "❌ Table '$table' does not exist or has errors: " . $e->getMessage() . "<br>\n";
    }
}

// Test 2: Check disciplines
echo "<h2>Test 2: Disciplines</h2>\n";
try {
    $stmt = $pdo->query("SELECT name, category FROM disciplines ORDER BY name");
    $disciplines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Found " . count($disciplines) . " disciplines:<br>\n";
    foreach ($disciplines as $discipline) {
        echo "  - {$discipline['name']} ({$discipline['category']})<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Error fetching disciplines: " . $e->getMessage() . "<br>\n";
}

// Test 3: Check discipline powers
echo "<h2>Test 3: Discipline Powers</h2>\n";
try {
    $stmt = $pdo->query("
        SELECT d.name as discipline_name, dp.level, dp.name as power_name
        FROM disciplines d
        JOIN discipline_powers dp ON d.id = dp.discipline_id
        ORDER BY d.name, dp.level
    ");
    $powers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Found " . count($powers) . " discipline powers:<br>\n";
    
    $currentDiscipline = '';
    foreach ($powers as $power) {
        if ($power['discipline_name'] !== $currentDiscipline) {
            $currentDiscipline = $power['discipline_name'];
            echo "<br><strong>$currentDiscipline:</strong><br>\n";
        }
        echo "  - Level {$power['level']}: {$power['power_name']}<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Error fetching discipline powers: " . $e->getMessage() . "<br>\n";
}

// Test 4: Check clans
echo "<h2>Test 4: Clans</h2>\n";
try {
    $stmt = $pdo->query("SELECT name, availability FROM clans ORDER BY name");
    $clans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Found " . count($clans) . " clans:<br>\n";
    foreach ($clans as $clan) {
        echo "  - {$clan['name']} ({$clan['availability']})<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Error fetching clans: " . $e->getMessage() . "<br>\n";
}

// Test 5: Check clan-discipline access
echo "<h2>Test 5: Clan-Discipline Access</h2>\n";
try {
    $stmt = $pdo->query("
        SELECT c.name as clan_name, d.name as discipline_name
        FROM clans c
        JOIN clan_disciplines cd ON c.id = cd.clan_id
        JOIN disciplines d ON cd.discipline_id = d.id
        ORDER BY c.name, d.name
    ");
    $access = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Found " . count($access) . " clan-discipline access links:<br>\n";
    
    $currentClan = '';
    foreach ($access as $link) {
        if ($link['clan_name'] !== $currentClan) {
            $currentClan = $link['clan_name'];
            echo "<br><strong>$currentClan:</strong><br>\n";
        }
        echo "  - {$link['discipline_name']}<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Error fetching clan-discipline access: " . $e->getMessage() . "<br>\n";
}

// Test 6: Test API endpoint
echo "<h2>Test 6: API Endpoint</h2>\n";
try {
    $url = 'http://localhost/api_disciplines.php?action=all';
    $response = file_get_contents($url);
    if ($response === false) {
        echo "❌ Could not fetch from API endpoint<br>\n";
    } else {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            echo "✅ API endpoint working correctly<br>\n";
            echo "  - Disciplines loaded: " . count($data['data']['disciplinePowers']) . "<br>\n";
            echo "  - Clans loaded: " . count($data['data']['clanDisciplineAccess']) . "<br>\n";
        } else {
            echo "❌ API endpoint returned error: " . ($data['error'] ?? 'Unknown error') . "<br>\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing API endpoint: " . $e->getMessage() . "<br>\n";
}

echo "<h2>Test Complete</h2>\n";
echo "<p>If all tests show ✅, the database integration is working correctly!</p>\n";
?>
