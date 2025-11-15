<?php
/**
 * Update Dropdowns to Use Archetypes Table
 * 
 * Replaces hardcoded nature and demeanor dropdown options in lotn_char_create.php
 * with dynamic PHP code that queries the archetypes table
 */

require_once __DIR__ . '/../includes/connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Dropdowns to Use Archetypes</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            background: #1a0f0f; 
            color: #d4c4b0; 
        }
        .success { 
            color: #0d7a4a; 
            padding: 10px; 
            background: rgba(13, 122, 74, 0.2); 
            border: 1px solid #0d7a4a; 
            margin: 10px 0; 
        }
        .error { 
            color: #8B0000; 
            padding: 10px; 
            background: rgba(139, 0, 0, 0.2); 
            border: 1px solid #8B0000; 
            margin: 10px 0; 
        }
        .info { 
            color: #b8a090; 
            padding: 10px; 
            background: rgba(184, 160, 144, 0.2); 
            border: 1px solid #b8a090; 
            margin: 10px 0; 
        }
        .warning {
            color: #ffa500;
            padding: 10px;
            background: rgba(255, 165, 0, 0.2);
            border: 1px solid #ffa500;
            margin: 10px 0;
        }
        h2 { color: #d4c4b0; margin-top: 30px; }
        code { 
            background: rgba(0, 0, 0, 0.3); 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: monospace;
        }
        pre {
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>🔄 Update Dropdowns to Use Archetypes Table</h1>

<?php

if (!$conn) {
    echo '<div class="error">❌ Database connection failed</div>';
    exit;
}

$form_file = __DIR__ . '/../lotn_char_create.php';
$backup_file = $form_file . '.backup.' . date('Y-m-d_H-i-s');

try {
    // Step 1: Check if archetypes table exists and has data
    echo '<h2>Step 1: Verifying Archetypes Table</h2>';
    
    $check_table = "SHOW TABLES LIKE 'archetypes'";
    $result = mysqli_query($conn, $check_table);
    $table_exists = $result && mysqli_num_rows($result) > 0;
    
    if (!$table_exists) {
        echo '<div class="error">❌ Archetypes table does not exist. Please run <code>database/create_archetypes_table.php</code> first.</div>';
        exit;
    }
    
    $count_query = "SELECT COUNT(*) as count FROM archetypes";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $archetype_count = $count_row['count'];
    
    if ($archetype_count == 0) {
        echo '<div class="error">❌ Archetypes table is empty. Please run <code>database/populate_archetypes.php</code> first.</div>';
        exit;
    }
    
    echo '<div class="success">✅ Archetypes table exists with ' . $archetype_count . ' archetype(s)</div>';
    
    // Step 2: Read form file
    echo '<h2>Step 2: Reading Form File</h2>';
    
    if (!file_exists($form_file)) {
        throw new Exception("Form file not found: " . $form_file);
    }
    
    $form_content = file_get_contents($form_file);
    $original_content = $form_content;
    
    // Check if already updated
    if (strpos($form_content, 'SELECT name FROM archetypes') !== false) {
        echo '<div class="warning">⚠️ Form appears to already use archetypes table. Skipping update.</div>';
        exit;
    }
    
    echo '<div class="success">✅ Form file loaded</div>';
    
    // Step 3: Check if form includes connect.php
    echo '<h2>Step 3: Checking Database Connection</h2>';
    
    $has_connect = (strpos($form_content, "includes/connect.php") !== false);
    
    if (!$has_connect) {
        echo '<div class="warning">⚠️ Database connection not found. The form may need to include connect.php.</div>';
    } else {
        echo '<div class="success">✅ Database connection already included</div>';
    }
    
    // Step 4: Generate PHP code for dropdown options
    echo '<h2>Step 4: Generating Dynamic Dropdown Code</h2>';
    
    $dropdown_php_code = '<?php
// Load archetypes from database for nature/demeanor dropdowns
$archetypes_query = "SELECT name FROM archetypes ORDER BY name ASC";
$archetypes_result = mysqli_query($conn, $archetypes_query);
$archetypes = [];
if ($archetypes_result) {
    while ($row = mysqli_fetch_assoc($archetypes_result)) {
        $archetypes[] = $row[\'name\'];
    }
    mysqli_free_result($archetypes_result);
}
?>';
    
    // Step 5: Replace nature dropdown
    echo '<h2>Step 5: Updating Nature Dropdown</h2>';
    
    // Find the nature select block
    preg_match('/(<select id="nature"[^>]*>)(.*?)(<\/select>)/is', $form_content, $nature_matches);
    if (empty($nature_matches)) {
        throw new Exception("Could not find nature select element");
    }
    
    $nature_select_start = $nature_matches[1];
    $nature_select_end = $nature_matches[3];
    
    // Build new nature dropdown with PHP loop
    $new_nature_dropdown = $nature_select_start . PHP_EOL;
    $new_nature_dropdown .= '                            <option value="">Select Nature...</option>' . PHP_EOL;
    $new_nature_dropdown .= '                            <?php foreach ($archetypes as $archetype): ?>' . PHP_EOL;
    $new_nature_dropdown .= '                                <option value="<?php echo htmlspecialchars($archetype); ?>"><?php echo htmlspecialchars($archetype); ?></option>' . PHP_EOL;
    $new_nature_dropdown .= '                            <?php endforeach; ?>' . PHP_EOL;
    $new_nature_dropdown .= '                        ' . $nature_select_end;
    
    $form_content = str_replace($nature_matches[0], $new_nature_dropdown, $form_content);
    
    echo '<div class="success">✅ Nature dropdown updated to use archetypes table</div>';
    
    // Step 6: Replace demeanor dropdown
    echo '<h2>Step 6: Updating Demeanor Dropdown</h2>';
    
    // Find the demeanor select block
    preg_match('/(<select id="demeanor"[^>]*>)(.*?)(<\/select>)/is', $form_content, $demeanor_matches);
    if (empty($demeanor_matches)) {
        throw new Exception("Could not find demeanor select element");
    }
    
    $demeanor_select_start = $demeanor_matches[1];
    $demeanor_select_end = $demeanor_matches[3];
    
    // Build new demeanor dropdown with PHP loop
    $new_demeanor_dropdown = $demeanor_select_start . PHP_EOL;
    $new_demeanor_dropdown .= '                            <option value="">Select Demeanor...</option>' . PHP_EOL;
    $new_demeanor_dropdown .= '                            <?php foreach ($archetypes as $archetype): ?>' . PHP_EOL;
    $new_demeanor_dropdown .= '                                <option value="<?php echo htmlspecialchars($archetype); ?>"><?php echo htmlspecialchars($archetype); ?></option>' . PHP_EOL;
    $new_demeanor_dropdown .= '                            <?php endforeach; ?>' . PHP_EOL;
    $new_demeanor_dropdown .= '                        ' . $demeanor_select_end;
    
    $form_content = str_replace($demeanor_matches[0], $new_demeanor_dropdown, $form_content);
    
    echo '<div class="success">✅ Demeanor dropdown updated to use archetypes table</div>';
    
    // Step 7: Add PHP code before the form (find a good insertion point)
    echo '<h2>Step 7: Adding Archetypes Query Code</h2>';
    
    // Check if archetypes query code already exists
    if (strpos($form_content, 'SELECT name FROM archetypes') !== false) {
        echo '<div class="info">ℹ️ Archetypes query code already exists</div>';
    } else {
        // Find a good insertion point - after the database connection include
        $insert_marker = "include 'includes/connect.php';";
        $insert_pos = strpos($form_content, $insert_marker);
        
        if ($insert_pos !== false) {
            // Find the end of that line
            $line_end = strpos($form_content, "\n", $insert_pos);
            if ($line_end === false) {
                $line_end = strlen($form_content);
            } else {
                $line_end++; // Move past the newline
            }
            
            // Insert the PHP code after the connect include
            $form_content = substr_replace($form_content, PHP_EOL . $dropdown_php_code, $line_end, 0);
            echo '<div class="success">✅ Added archetypes query code after database connection</div>';
        } else {
            // Fallback: insert before the nature label
            $insert_marker = '<label for="nature">Nature *</label>';
            $insert_pos = strpos($form_content, $insert_marker);
            
            if ($insert_pos !== false) {
                // Find the start of the line
                $line_start = strrpos(substr($form_content, 0, $insert_pos), "\n");
                if ($line_start === false) {
                    $line_start = 0;
                } else {
                    $line_start++; // Move past the newline
                }
                
                $form_content = substr_replace($form_content, $dropdown_php_code . PHP_EOL . PHP_EOL, $line_start, 0);
                echo '<div class="success">✅ Added archetypes query code before nature dropdown</div>';
            } else {
                echo '<div class="warning">⚠️ Could not find insertion point, but dropdowns were updated</div>';
            }
        }
    }
    
    // Step 8: Create backup
    echo '<h2>Step 8: Creating Backup</h2>';
    
    if (!copy($form_file, $backup_file)) {
        throw new Exception("Failed to create backup file");
    }
    
    echo '<div class="success">✅ Backup created: ' . basename($backup_file) . '</div>';
    
    // Step 9: Write updated file
    echo '<h2>Step 9: Writing Updated File</h2>';
    
    if (file_put_contents($form_file, $form_content) === false) {
        throw new Exception("Failed to write updated file");
    }
    
    echo '<div class="success">✅ Successfully updated lotn_char_create.php</div>';
    
    // Summary
    echo '<h2>Summary</h2>';
    echo '<div class="success"><strong>✅ Update Complete!</strong></div>';
    echo '<div class="info">📋 Both nature and demeanor dropdowns now query from the archetypes table</div>';
    echo '<div class="info">💾 Backup saved as: ' . basename($backup_file) . '</div>';
    echo '<div class="info">🔄 The form will now automatically show all archetypes from the database</div>';
    echo '<div class="info">📝 You can add/edit archetypes in the database and they will appear in both dropdowns</div>';
    
} catch (Exception $e) {
    echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    if (isset($backup_file) && file_exists($backup_file)) {
        echo '<div class="info">💾 Backup file was created before error: ' . basename($backup_file) . '</div>';
    }
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>

</body>
</html>

