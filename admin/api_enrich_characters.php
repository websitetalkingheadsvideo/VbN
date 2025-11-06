<?php
/**
 * Admin API: Enrich Characters JSON from DB
 *
 * Requires authenticated admin session.
 * Reads reference/Characters/characters.json, fills clan/description/notes/traits
 * from the database, writes back to the same file, and returns a JSON summary.
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../includes/connect.php';

$root = realpath(__DIR__ . '/..');
$jsonPath = $root . DIRECTORY_SEPARATOR . 'reference' . DIRECTORY_SEPARATOR . 'Characters' . DIRECTORY_SEPARATOR . 'characters.json';

function respond(array $payload) {
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit();
}

if (!file_exists($jsonPath)) {
    respond(['success' => false, 'error' => 'JSON not found', 'path' => $jsonPath]);
}

$raw = file_get_contents($jsonPath);
if ($raw === false) {
    respond(['success' => false, 'error' => 'Failed to read JSON']);
}

$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    respond(['success' => false, 'error' => 'JSON parse error: ' . json_last_error_msg()]);
}

if (!isset($data['characters']) || !is_array($data['characters'])) {
    respond(['success' => false, 'error' => 'Invalid schema: missing characters array']);
}

// Detect traits tables presence
// Attempt queries directly (information_schema may be restricted)
$have_traits_table = true; // optimistic; will toggle off on prepare failure
$have_neg_traits_table = true; // optimistic; will toggle off on prepare failure

$updated = 0;
$missing = [];
$multiple = [];

// Optional debug mode
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';
$debug_rows = [];

foreach ($data['characters'] as $i => $c) {
    $name = $c['name'] ?? '';
    if ($name === '') { continue; }

    // Fetch character record (exact match on character_name)
    $stmt = $conn->prepare("SELECT id, character_name, clan, biography, notes FROM characters WHERE LOWER(character_name) = LOWER(?) ORDER BY id ASC LIMIT 2");
    if (!$stmt) {
        respond(['success' => false, 'error' => 'DB prepare failed: ' . $conn->error]);
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
        $multiple[] = $name;
    }

    $row = $rows[0];
    $charId = (int)$row['id'];

    // Update fields
    $data['characters'][$i]['clan'] = $row['clan'] ?? ($data['characters'][$i]['clan'] ?? '');
    $bio = $row['biography'] ?? '';
    $nts = $row['notes'] ?? '';
    if ($bio === '0') { $bio = ''; }
    if ($nts === '0') { $nts = ''; }
    $data['characters'][$i]['description'] = $bio !== '' ? $bio : ($data['characters'][$i]['description'] ?? '');
    $data['characters'][$i]['notes'] = $nts !== '' ? $nts : ($data['characters'][$i]['notes'] ?? '');

    // Traits collection (flat, deduped)
    $traits = [];
    $trait_count = 0;
    $neg_trait_count = 0;

    $collect_traits = function(int $cid) use ($conn, &$have_traits_table, &$have_neg_traits_table, &$traits, &$trait_count, &$neg_trait_count) {
        $traits_local = [];
        $tc = 0; $ntc = 0;
        if ($have_traits_table) {
            if ($stmt = $conn->prepare("SELECT trait_name FROM character_traits WHERE character_id = ? ORDER BY trait_category, trait_name")) {
                $stmt->bind_param('i', $cid);
                $stmt->execute();
                $rt = $stmt->get_result();
                while ($t = $rt->fetch_assoc()) { if (!empty($t['trait_name'])) { $traits_local[] = $t['trait_name']; $tc++; } }
                $stmt->close();
            } else { $have_traits_table = false; }
        }
        if ($have_neg_traits_table) {
            if ($stmt = $conn->prepare("SELECT trait_name FROM character_negative_traits WHERE character_id = ? ORDER BY trait_category, trait_name")) {
                $stmt->bind_param('i', $cid);
                $stmt->execute();
                $rt = $stmt->get_result();
                while ($t = $rt->fetch_assoc()) { if (!empty($t['trait_name'])) { $traits_local[] = $t['trait_name']; $ntc++; } }
                $stmt->close();
            } else { $have_neg_traits_table = false; }
        }
        return [$traits_local, $tc, $ntc];
    };

    // First attempt on selected ID
    [$traits, $trait_count, $neg_trait_count] = $collect_traits($charId);

    // Fallback: if no traits found, prefer another ID with same name that has traits
    if (($trait_count + $neg_trait_count) === 0) {
        if ($stmt = $conn->prepare("SELECT id FROM characters WHERE LOWER(character_name) = LOWER(?) ORDER BY id ASC")) {
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $resIds = $stmt->get_result();
            $bestId = $charId;
            $bestTotal = 0;
            while ($r = $resIds->fetch_assoc()) {
                $cid = (int)$r['id'];
                [$candTraits, $candTc, $candNtc] = $collect_traits($cid);
                $total = $candTc + $candNtc;
                if ($total > $bestTotal) {
                    $bestTotal = $total;
                    $bestId = $cid;
                    $traits = $candTraits;
                    $trait_count = $candTc;
                    $neg_trait_count = $candNtc;
                }
            }
            $stmt->close();

            if ($bestId !== $charId) {
                // Re-fetch core fields from the chosen bestId
                if ($stmt2 = $conn->prepare("SELECT clan, biography, notes FROM characters WHERE id = ? LIMIT 1")) {
                    $stmt2->bind_param('i', $bestId);
                    $stmt2->execute();
                    $res2 = $stmt2->get_result();
                    if ($core = $res2->fetch_assoc()) {
                        $data['characters'][$i]['clan'] = $core['clan'] ?? ($data['characters'][$i]['clan'] ?? '');
                        $bio2 = $core['biography'] ?? '';
                        $nts2 = $core['notes'] ?? '';
                        if ($bio2 === '0') { $bio2 = ''; }
                        if ($nts2 === '0') { $nts2 = ''; }
                        $data['characters'][$i]['description'] = $bio2 !== '' ? $bio2 : ($data['characters'][$i]['description'] ?? '');
                        $data['characters'][$i]['notes'] = $nts2 !== '' ? $nts2 : ($data['characters'][$i]['notes'] ?? '');
                    }
                    $stmt2->close();
                }
                $charId = $bestId; // update chosen ID
            }
        }
    }

    // Filter out numeric-only placeholders and dedupe case-insensitively
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

    if ($debug) {
        $debug_rows[] = [
            'name' => $name,
            'character_id' => $charId,
            'trait_count' => $trait_count,
            'neg_trait_count' => $neg_trait_count
        ];
    }

    $updated++;
}

$out = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($out === false) {
    respond(['success' => false, 'error' => 'Failed to encode JSON: ' . json_last_error_msg()]);
}

if (file_put_contents($jsonPath, $out) === false) {
    respond(['success' => false, 'error' => 'Failed to write updated JSON']);
}

respond([
    'success' => true,
    'updated' => $updated,
    'missing' => $missing,
    'multiple_matches' => $multiple,
    'have_traits_table' => $have_traits_table,
    'have_negative_traits_table' => $have_neg_traits_table,
    'json' => 'reference/Characters/characters.json',
    'debug' => $debug ? $debug_rows : null
]);
?>
