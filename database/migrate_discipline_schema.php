<?php
/**
 * Migrate discipline schema to normalized structure
 * 
 * Tasks:
 * 1. Ensure discipline_powers table has correct structure
 * 2. Standardize character_disciplines.level to INT(1-5)
 * 3. Remove power_name column if it exists
 * 4. Add appropriate constraints and indexes
 */

require_once __DIR__ . '/../includes/connect.php';

// Check if running via web browser or CLI
$is_web = php_sapi_name() !== 'cli';

if ($is_web) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Discipline Schema Migration</title><style>
        body { font-family: monospace; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
        .error { color: #ff6b6b; }
        .success { color: #51cf66; }
        .warning { color: #ffd43b; }
    </style></head><body>";
    echo "<h1>🦇 Migrating Discipline Schema</h1><pre>";
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
    log_message("Starting discipline schema migration...", 'info');
    
    // 1. Ensure discipline_powers table structure is correct
    log_message("Step 1: Verifying discipline_powers table structure...", 'info');
    
    $check_powers = mysqli_query($conn, "SHOW TABLES LIKE 'discipline_powers'");
    if (mysqli_num_rows($check_powers) == 0) {
        log_message("Creating discipline_powers table...", 'info');
        $sql = "CREATE TABLE discipline_powers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            discipline_id INT NOT NULL,
            power_level INT NOT NULL,
            power_name VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            prerequisites JSON DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (discipline_id) REFERENCES disciplines(id) ON DELETE CASCADE,
            UNIQUE KEY unique_discipline_power (discipline_id, power_level),
            INDEX idx_discipline_id (discipline_id),
            INDEX idx_power_level (power_level)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (mysqli_query($conn, $sql)) {
            log_message("Created discipline_powers table", 'success');
        } else {
            throw new Exception("Failed to create discipline_powers: " . mysqli_error($conn));
        }
    } else {
        log_message("discipline_powers table already exists", 'success');
        
        // Check if power_level has CHECK constraint (MySQL 8.0.16+)
        $check_constraint = mysqli_query($conn, "
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'discipline_powers' 
            AND CONSTRAINT_TYPE = 'CHECK'
        ");
        
        if (mysqli_num_rows($check_constraint) == 0) {
            log_message("Note: CHECK constraint not supported or not found (MySQL version may not support it)", 'warning');
        }
    }
    
    // 2. Check and update character_disciplines table
    log_message("Step 2: Verifying character_disciplines table structure...", 'info');
    
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'character_disciplines'");
    if (mysqli_num_rows($check_table) == 0) {
        log_message("Creating character_disciplines table with INT level...", 'info');
        $sql = "CREATE TABLE character_disciplines (
            id INT AUTO_INCREMENT PRIMARY KEY,
            character_id INT NOT NULL,
            discipline_name VARCHAR(100) NOT NULL,
            level INT NOT NULL DEFAULT 1,
            xp_cost INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
            UNIQUE KEY unique_character_discipline (character_id, discipline_name),
            INDEX idx_character (character_id),
            INDEX idx_discipline (discipline_name),
            INDEX idx_level (level)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (mysqli_query($conn, $sql)) {
            log_message("Created character_disciplines table", 'success');
        } else {
            throw new Exception("Failed to create character_disciplines: " . mysqli_error($conn));
        }
    } else {
        // Check current structure
        $columns = mysqli_query($conn, "DESCRIBE character_disciplines");
        $column_info = [];
        while ($row = mysqli_fetch_assoc($columns)) {
            $column_info[$row['Field']] = $row;
        }
        
        // Check if level column exists and its type
        if (isset($column_info['level'])) {
            $level_type = $column_info['level']['Type'];
            
            if (stripos($level_type, 'enum') !== false || stripos($level_type, 'varchar') !== false) {
                log_message("Converting level column from {$level_type} to INT...", 'info');
                
                // Check if there's any data to convert
                $count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM character_disciplines");
                $count_row = mysqli_fetch_assoc($count_result);
                $has_data = $count_row['cnt'] > 0;
                
                if ($has_data) {
                    log_message("Found existing data. Converting ENUM values to INT before changing column type...", 'info');
                    
                    // Map ENUM values to numeric levels
                    $update_sql = "UPDATE character_disciplines SET level = CASE 
                        WHEN level = 'Basic' THEN 2
                        WHEN level = 'Intermediate' THEN 3
                        WHEN level = 'Advanced' THEN 5
                        ELSE CAST(level AS UNSIGNED)
                    END
                    WHERE level IN ('Basic', 'Intermediate', 'Advanced') OR level NOT REGEXP '^[0-9]+$'";
                    
                    if (mysqli_query($conn, $update_sql)) {
                        $affected = mysqli_affected_rows($conn);
                        log_message("Converted {$affected} rows from ENUM to numeric values", 'success');
                    } else {
                        log_message("Warning: Could not update values: " . mysqli_error($conn), 'warning');
                    }
                }
                
                // Now safe to change column type
                $alter_sql = "ALTER TABLE character_disciplines 
                              MODIFY COLUMN level INT NOT NULL DEFAULT 1";
                
                if (mysqli_query($conn, $alter_sql)) {
                    log_message("Converted level column to INT", 'success');
                } else {
                    log_message("Warning: Could not convert level column: " . mysqli_error($conn), 'warning');
                }
            } else {
                log_message("Level column is already numeric type: {$level_type}", 'success');
            }
        } else {
            log_message("Adding level column...", 'info');
            $alter_sql = "ALTER TABLE character_disciplines 
                          ADD COLUMN level INT NOT NULL DEFAULT 1 AFTER discipline_name";
            if (mysqli_query($conn, $alter_sql)) {
                log_message("Added level column", 'success');
            } else {
                throw new Exception("Failed to add level column: " . mysqli_error($conn));
            }
        }
        
        // Check if power_name column exists and remove it
        if (isset($column_info['power_name'])) {
            log_message("Removing power_name column (no longer needed)...", 'info');
            $alter_sql = "ALTER TABLE character_disciplines DROP COLUMN power_name";
            if (mysqli_query($conn, $alter_sql)) {
                log_message("Removed power_name column", 'success');
            } else {
                log_message("Warning: Could not remove power_name column: " . mysqli_error($conn), 'warning');
            }
        }
        
        // Ensure UNIQUE constraint on (character_id, discipline_name)
        $unique_check = mysqli_query($conn, "
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'character_disciplines' 
            AND CONSTRAINT_TYPE = 'UNIQUE'
            AND CONSTRAINT_NAME LIKE '%character_discipline%'
        ");
        
        if (mysqli_num_rows($unique_check) == 0) {
            log_message("Checking for duplicate entries before adding UNIQUE constraint...", 'info');
            
            // First check if there are duplicates
            $duplicates = mysqli_query($conn, "
                SELECT character_id, discipline_name, COUNT(*) as cnt
                FROM character_disciplines
                GROUP BY character_id, discipline_name
                HAVING cnt > 1
            ");
            
            if (mysqli_num_rows($duplicates) > 0) {
                log_message("Found duplicate entries. Attempting to consolidate automatically...", 'info');
                
                // Try to consolidate duplicates by keeping one row per character-discipline (highest level, or any if same level)
                // First pass: remove rows with lower levels
                $consolidate_sql = "
                    DELETE cd1 FROM character_disciplines cd1
                    INNER JOIN character_disciplines cd2 
                    WHERE cd1.id > cd2.id 
                    AND cd1.character_id = cd2.character_id 
                    AND cd1.discipline_name = cd2.discipline_name
                    AND cd1.level <= cd2.level
                ";
                
                $removed_pass1 = 0;
                if (mysqli_query($conn, $consolidate_sql)) {
                    $removed_pass1 = mysqli_affected_rows($conn);
                }
                
                // Second pass: if there are still duplicates (same level), keep only the lowest ID
                $consolidate_pass2_sql = "
                    DELETE cd1 FROM character_disciplines cd1
                    INNER JOIN character_disciplines cd2 
                    WHERE cd1.id > cd2.id 
                    AND cd1.character_id = cd2.character_id 
                    AND cd1.discipline_name = cd2.discipline_name
                ";
                
                $removed_pass2 = 0;
                if (mysqli_query($conn, $consolidate_pass2_sql)) {
                    $removed_pass2 = mysqli_affected_rows($conn);
                }
                
                $total_removed = $removed_pass1 + $removed_pass2;
                if ($total_removed > 0) {
                    log_message("Removed {$total_removed} duplicate rows (kept highest level, lowest ID for each discipline)", 'success');
                }
                
                // Try again to add constraint
                $alter_sql = "ALTER TABLE character_disciplines 
                              ADD UNIQUE KEY unique_character_discipline (character_id, discipline_name)";
                if (mysqli_query($conn, $alter_sql)) {
                    log_message("Added UNIQUE constraint", 'success');
                } else {
                    log_message("Warning: Could not add UNIQUE constraint after consolidation: " . mysqli_error($conn), 'warning');
                    log_message("Run migrate_character_disciplines.php to fully resolve duplicates", 'warning');
                }
            } else {
                // No duplicates, safe to add constraint
                $alter_sql = "ALTER TABLE character_disciplines 
                              ADD UNIQUE KEY unique_character_discipline (character_id, discipline_name)";
                if (mysqli_query($conn, $alter_sql)) {
                    log_message("Added UNIQUE constraint", 'success');
                } else {
                    log_message("Warning: Could not add UNIQUE constraint: " . mysqli_error($conn), 'warning');
                }
            }
        } else {
            log_message("UNIQUE constraint already exists", 'success');
        }
    }
    
    // 3. Verify indexes
    log_message("Step 3: Verifying indexes...", 'info');
    $indexes = mysqli_query($conn, "SHOW INDEXES FROM character_disciplines");
    $has_char_idx = false;
    $has_disc_idx = false;
    $has_level_idx = false;
    
    while ($idx = mysqli_fetch_assoc($indexes)) {
        if ($idx['Key_name'] === 'idx_character') $has_char_idx = true;
        if ($idx['Key_name'] === 'idx_discipline') $has_disc_idx = true;
        if ($idx['Key_name'] === 'idx_level') $has_level_idx = true;
    }
    
    if (!$has_char_idx) {
        mysqli_query($conn, "CREATE INDEX idx_character ON character_disciplines(character_id)");
        log_message("Created index on character_id", 'success');
    }
    if (!$has_disc_idx) {
        mysqli_query($conn, "CREATE INDEX idx_discipline ON character_disciplines(discipline_name)");
        log_message("Created index on discipline_name", 'success');
    }
    if (!$has_level_idx) {
        mysqli_query($conn, "CREATE INDEX idx_level ON character_disciplines(level)");
        log_message("Created index on level", 'success');
    }
    
    log_message("Schema migration completed successfully!", 'success');
    log_message("Next steps:", 'info');
    log_message("  1. Run database/populate_discipline_powers.php to populate power data", 'info');
    log_message("  2. Run database/migrate_character_disciplines.php to migrate existing character data", 'info');
    
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

