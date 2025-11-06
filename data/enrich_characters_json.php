<?php
/**
 * Enrich Characters JSON from Database
 *
 * Reads reference/Characters/characters.json and fills in clan, description (biography),
 * notes, and traits (flat merged list) for each listed character from the database.
 *
 * Usage (CLI):
 *   php data/enrich_characters_json.php
 *
 * Requirements:
 *   - includes/connect.php must be configured and reachable
 *   - characters.json must exist at reference/Characters/characters.json
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$root = __DIR__ . '/..';
$jsonPath = $root . '/reference/Characters/characters.json';

require_once $root . '/includes/connect.php';

function fail($msg) {
    fwrite(STDERR, "ERROR: $msg\n");
    exit(1);
}

if (!file_exists($jsonPath)) {
    fail("JSON file not found: $jsonPath");
}

$raw = file_get_contents($jsonPath);
if ($raw === false) {
    fail("Failed to read JSON file: $jsonPath");
}

$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    fail('JSON parse error: ' . json_last_error_msg());
}

if (!isset($data['characters']) || !is_array($data['characters'])) {
    fail('JSON missing "characters" array.');
}

// Helper: check if table exists in current schema
function table_exists(mysqli $conn, string $table): bool {
    $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res && $res->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Avoid relying on information_schema (may be restricted on some hosts)
$have_traits_table = true; // will be toggled off if prepares fail
$have_neg_traits_table = true; // will be toggled off if prepares fail

$updated = 0;
$missing = [];
$multi = [];

foreach ($data['characters'] as $i => $c) {
    $name = $c['name'] ?? '';
    if ($name === '') { continue; }

    // Fetch character core record
    $sql = "SELECT id, character_name, clan, biography, notes FROM characters WHERE character_name = ? LIMIT 2";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        fail('DB prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) { $rows[] = $row; }
    $stmt->close();

    if (count($rows) === 0) {
        $missing[] = $name;
        continue;
    }
    if (count($rows) > 1) {
        $multi[] = $name;
    }

    $row = $rows[0];
    $charId = (int)$row['id'];

    // Update basic fields
    $data['characters'][$i]['clan'] = $row['clan'] ?? ($data['characters'][$i]['clan'] ?? '');
    $bio = $row['biography'] ?? '';
    $nts = $row['notes'] ?? '';
    if ($bio === '0') { $bio = ''; }
    if ($nts === '0') { $nts = ''; }
    $data['characters'][$i]['description'] = $bio !== '' ? $bio : ($data['characters'][$i]['description'] ?? '');
    $data['characters'][$i]['notes'] = $nts !== '' ? $nts : ($data['characters'][$i]['notes'] ?? '');

    // Collect traits from available tables
    $traits = [];

    if ($have_traits_table) {
        $sql = "SELECT trait_name FROM character_traits WHERE character_id = ? ORDER BY trait_category, trait_name";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $charId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($t = $res->fetch_assoc()) {
                if (!empty($t['trait_name'])) $traits[] = $t['trait_name'];
            }
            $stmt->close();
        } else { $have_traits_table = false; }
    }

    if ($have_neg_traits_table) {
        $sql = "SELECT trait_name FROM character_negative_traits WHERE character_id = ? ORDER BY trait_category, trait_name";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $charId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($t = $res->fetch_assoc()) {
                if (!empty($t['trait_name'])) $traits[] = $t['trait_name'];
            }
            $stmt->close();
        } else { $have_neg_traits_table = false; }
    }

    // Filter numeric-only placeholders, then dedupe while preserving order
    $filtered = [];
    foreach ($traits as $t) {
        $t = is_string($t) ? trim($t) : '';
        if ($t === '' || preg_match('/^\d+$/', $t)) { continue; }
        $filtered[] = $t;
    }

    $seen = [];
    $flat = [];
    foreach ($filtered as $t) {
        $key = mb_strtolower($t);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $flat[] = $t;
    }
    $data['characters'][$i]['traits'] = $flat;

    $updated++;
}

// Write back JSON
$out = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($out === false) {
    fail('Failed to encode JSON: ' . json_last_error_msg());
}

if (file_put_contents($jsonPath, $out) === false) {
    fail('Failed to write updated JSON to ' . $jsonPath);
}

// Report
echo "Updated entries: $updated\n";
if (!empty($missing)) {
    echo "Missing (not found in DB):\n - " . implode("\n - ", $missing) . "\n";
}
if (!empty($multi)) {
    echo "Multiple matches (took first):\n - " . implode("\n - ", $multi) . "\n";
}

// Close connection
if ($conn) { $conn->close(); }
?>
