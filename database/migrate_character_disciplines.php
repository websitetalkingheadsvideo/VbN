<?php
/**
 * Migrate existing character_disciplines data to normalized structure
 * 
 * Tasks:
 * 1. Aggregate multiple rows per discipline to single row with max level
 * 2. Convert ENUM levels ('Basic', 'Intermediate', 'Advanced') to INT (1-5)
 * 3. Remove power_name column references if they exist
 */

require_once __DIR__ . '/../includes/connect.php';

// Check if running via web browser or CLI
$is_web = php_sapi_name() !== 'cli';

if ($is_web) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Migrate Character Disciplines</title><style>
        body { font-family: monospace; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
        .error { color: #ff6b6b; }
        .success { color: #51cf66; }
        .warning { color: #ffd43b; }
    </style></head><body>";
    echo "<h1>🦇 Migrating Character Disciplines</h1><pre>";
    flush();
}

function log_message($message, $type = 'info') {
    global $is_web;
    $prefix = '';
    switch ($type) {
        case 'error':
            $prefix = $is_web ? '<span class="error">❌</span> ' : '❌ ';
            break;
        case 'success':
            $prefix = $is_web ? '<span class="success">✅</span> ' : '✅ ';
            break;
        case 'warning':
            $prefix = $is_web ? '<span class="warning">⚠️</span> ' : '⚠️ ';
            break;
        default:
            $prefix = $is_web ? '<span>ℹ️</span> ' : 'ℹ️ ';
    }
    echo $prefix . $message . "\n";
    if ($is_web) flush();
}

try {
    log_message("Starting character disciplines migration...", 'info');
    
    // Map ENUM values to numeric levels
    $level_map = [
        'Basic' => 2,      // Basic typically represents levels 1-2, use max
        'Intermediate' => 3,
        'Advanced' => 5,   // Advanced typically represents levels 4-5, use max
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5
    ];
    
    // First, handle duplicates aggressively - delete all but one per character-discipline
    log_message("Step 1: Removing duplicates...", 'info');
    $duplicate_query = "
        DELETE cd1 FROM character_disciplines cd1
        INNER JOIN character_disciplines cd2 
        WHERE cd1.character_id = cd2.character_id 
        AND cd1.discipline_name = cd2.discipline_name
        AND cd1.id > cd2.id
    ";
    
    if (mysqli_query($conn, $duplicate_query)) {
        $duplicates_removed = mysqli_affected_rows($conn);
        if ($duplicates_removed > 0) {
            log_message("Removed {$duplicates_removed} duplicate rows", 'success');
        }
    } else {
        log_message("Warning: Could not remove duplicates: " . mysqli_error($conn), 'warning');
    }
    
    // Now process all remaining rows - aggregate duplicates and convert levels
    log_message("Step 2: Processing character-discipline combinations...", 'info');
    
    // Get all unique character-discipline pairs
    $query = "SELECT character_id, discipline_name
              FROM character_disciplines
              GROUP BY character_id, discipline_name
              ORDER BY character_id, discipline_name";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Failed to query character disciplines: " . mysqli_error($conn));
    }
    
    $characters_processed = [];
    $disciplines_migrated = 0;
    $errors = 0;
    
    // Process each character-discipline combination
    while ($row = mysqli_fetch_assoc($result)) {
        $character_id = (int)$row['character_id'];
        $discipline_name = $row['discipline_name'];
        
        // Get all rows for this character-discipline combination
        $detail_query = "SELECT id, level, xp_cost FROM character_disciplines 
                         WHERE character_id = ? AND discipline_name = ?";
        $detail_stmt = mysqli_prepare($conn, $detail_query);
        mysqli_stmt_bind_param($detail_stmt, 'is', $character_id, $discipline_name);
        mysqli_stmt_execute($detail_stmt);
        $detail_result = mysqli_stmt_get_result($detail_stmt);
        
        $levels = [];
        $xp_costs = [];
        $row_ids = [];
        
        while ($detail_row = mysqli_fetch_assoc($detail_result)) {
            $row_ids[] = $detail_row['id'];
            $level_val = $detail_row['level'];
            
            // Convert level to INT
            if (is_string($level_val) && isset($level_map[$level_val])) {
                $levels[] = $level_map[$level_val];
            } elseif (is_numeric($level_val)) {
                $levels[] = (int)$level_val;
            } else {
                log_message("Warning: Unknown level format '{$level_val}' for character {$character_id}, discipline {$discipline_name}", 'warning');
                $levels[] = 1; // Default to 1
            }
            
            $xp_costs[] = (int)($detail_row['xp_cost'] ?? 0);
        }
        mysqli_stmt_close($detail_stmt);
        
        if (empty($levels)) {
            continue;
        }
        
        // Calculate max level and total XP cost
        $max_level = max($levels);
        $max_level = max(1, min(5, $max_level)); // Ensure 1-5 range
        $total_xp = array_sum($xp_costs);
        
        // If there are multiple rows, delete all and insert one
        if (count($row_ids) > 1) {
            $delete_stmt = mysqli_prepare($conn, "DELETE FROM character_disciplines WHERE character_id = ? AND discipline_name = ?");
            mysqli_stmt_bind_param($delete_stmt, 'is', $character_id, $discipline_name);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);
        }
        
        // Insert/update single row with max level
        $insert_stmt = mysqli_prepare($conn, 
            "INSERT INTO character_disciplines (character_id, discipline_name, level, xp_cost)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE level = VALUES(level), xp_cost = VALUES(xp_cost)");
        
        if (!$insert_stmt) {
            log_message("Failed to prepare insert for character {$character_id}, discipline {$discipline_name}: " . mysqli_error($conn), 'error');
            $errors++;
            continue;
        }
        
        mysqli_stmt_bind_param($insert_stmt, 'isii', $character_id, $discipline_name, $max_level, $total_xp);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $disciplines_migrated++;
            if (!in_array($character_id, $characters_processed)) {
                $characters_processed[] = $character_id;
            }
        } else {
            log_message("Failed to insert for character {$character_id}, discipline {$discipline_name}: " . mysqli_stmt_error($insert_stmt), 'error');
            $errors++;
        }
        
        mysqli_stmt_close($insert_stmt);
    }
    
    log_message("Migration complete!", 'success');
    log_message("  Characters processed: " . count($characters_processed), 'success');
    log_message("  Disciplines migrated: {$disciplines_migrated}", 'success');
    log_message("  Duplicate rows removed: {$duplicates_removed}", 'success');
    if ($errors > 0) {
        log_message("  Errors encountered: {$errors}", 'warning');
    }
    
    // Verify: Check for any remaining duplicates
    $verify_query = "SELECT character_id, discipline_name, COUNT(*) as cnt
                     FROM character_disciplines
                     GROUP BY character_id, discipline_name
                     HAVING cnt > 1";
    $verify_result = mysqli_query($conn, $verify_query);
    
    if (mysqli_num_rows($verify_result) > 0) {
        log_message("Warning: Found " . mysqli_num_rows($verify_result) . " remaining duplicate discipline entries", 'warning');
    } else {
        log_message("✅ No duplicate entries found - migration successful!", 'success');
    }
    
} catch (Exception $e) {
    log_message("Migration failed: " . $e->getMessage(), 'error');
    exit(1);
}

if ($is_web) {
    echo "</pre>";
    echo "<p style='margin-top: 20px;'><strong>Migration complete!</strong></p>";
    echo "</body></html>";
}

mysqli_close($conn);
?>

