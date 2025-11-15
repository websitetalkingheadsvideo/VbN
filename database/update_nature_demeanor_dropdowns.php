<?php
/**
 * Update Nature and Demeanor Dropdowns
 * 
 * This script scans the database, compares with existing dropdowns,
 * and automatically updates lotn_char_create.php with missing values
 */

require_once __DIR__ . '/../includes/connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Nature/Demeanor Dropdowns</title>
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
        ul { margin: 10px 0; padding-left: 30px; }
        li { margin: 5px 0; }
        code { 
            background: rgba(0, 0, 0, 0.3); 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: monospace;
        }
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #0d7a4a;
            color: #d4c4b0;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .action-btn:hover {
            background: #0a5d38;
        }
    </style>
</head>
<body>
    <h1>🔄 Update Nature and Demeanor Dropdowns</h1>

<?php

if (!$conn) {
    echo '<div class="error">❌ Database connection failed</div>';
    exit;
}

$form_file = __DIR__ . '/../lotn_char_create.php';
$backup_file = $form_file . '.backup.' . date('Y-m-d_H-i-s');

try {
    // Step 1: Get unique values from database
    echo '<h2>Step 1: Querying Database</h2>';
    
    $nature_query = "SELECT DISTINCT nature 
                     FROM characters 
                     WHERE nature IS NOT NULL 
                     AND nature != '' 
                     ORDER BY nature ASC";
    
    $nature_result = mysqli_query($conn, $nature_query);
    if (!$nature_result) {
        throw new Exception("Failed to query nature values: " . mysqli_error($conn));
    }
    
    $db_nature_values = [];
    while ($row = mysqli_fetch_assoc($nature_result)) {
        $db_nature_values[] = trim($row['nature']);
    }
    mysqli_free_result($nature_result);
    
    $demeanor_query = "SELECT DISTINCT demeanor 
                      FROM characters 
                      WHERE demeanor IS NOT NULL 
                      AND demeanor != '' 
                      ORDER BY demeanor ASC";
    
    $demeanor_result = mysqli_query($conn, $demeanor_query);
    if (!$demeanor_result) {
        throw new Exception("Failed to query demeanor values: " . mysqli_error($conn));
    }
    
    $db_demeanor_values = [];
    while ($row = mysqli_fetch_assoc($demeanor_result)) {
        $db_demeanor_values[] = trim($row['demeanor']);
    }
    mysqli_free_result($demeanor_result);
    
    echo '<div class="success">✅ Found ' . count($db_nature_values) . ' unique nature values</div>';
    echo '<div class="success">✅ Found ' . count($db_demeanor_values) . ' unique demeanor values</div>';
    
    // Step 2: Read and parse form file
    echo '<h2>Step 2: Parsing Form File</h2>';
    
    if (!file_exists($form_file)) {
        throw new Exception("Form file not found: " . $form_file);
    }
    
    $form_content = file_get_contents($form_file);
    $original_content = $form_content;
    
    // Extract nature select block
    preg_match('/(<select id="nature"[^>]*>)(.*?)(<\/select>)/is', $form_content, $nature_matches);
    if (empty($nature_matches)) {
        throw new Exception("Could not find nature select element");
    }
    
    $nature_select_start = $nature_matches[1];
    $nature_options_html = $nature_matches[2];
    $nature_select_end = $nature_matches[3];
    
    // Extract existing nature options
    preg_match_all('/<option value="([^"]+)"[^>]*>([^<]+)<\/option>/i', $nature_options_html, $nature_option_matches);
    $form_nature_values = [];
    $form_nature_options = [];
    foreach ($nature_option_matches[1] as $index => $value) {
        $value = trim($value);
        if ($value !== '' && $value !== 'Select Nature...') {
            $form_nature_values[] = $value;
            $form_nature_options[$value] = trim($nature_option_matches[2][$index]);
        }
    }
    
    // Extract demeanor select block
    preg_match('/(<select id="demeanor"[^>]*>)(.*?)(<\/select>)/is', $form_content, $demeanor_matches);
    if (empty($demeanor_matches)) {
        throw new Exception("Could not find demeanor select element");
    }
    
    $demeanor_select_start = $demeanor_matches[1];
    $demeanor_options_html = $demeanor_matches[2];
    $demeanor_select_end = $demeanor_matches[3];
    
    // Extract existing demeanor options
    preg_match_all('/<option value="([^"]+)"[^>]*>([^<]+)<\/option>/i', $demeanor_options_html, $demeanor_option_matches);
    $form_demeanor_values = [];
    $form_demeanor_options = [];
    foreach ($demeanor_option_matches[1] as $index => $value) {
        $value = trim($value);
        if ($value !== '' && $value !== 'Select Demeanor...') {
            $form_demeanor_values[] = $value;
            $form_demeanor_options[$value] = trim($demeanor_option_matches[2][$index]);
        }
    }
    
    echo '<div class="info">ℹ️ Found ' . count($form_nature_values) . ' nature options in form</div>';
    echo '<div class="info">ℹ️ Found ' . count($form_demeanor_values) . ' demeanor options in form</div>';
    
    // Step 3: Find missing values
    echo '<h2>Step 3: Identifying Missing Values</h2>';
    
    $missing_nature = array_diff($db_nature_values, $form_nature_values);
    $missing_demeanor = array_diff($db_demeanor_values, $form_demeanor_values);
    
    if (empty($missing_nature) && empty($missing_demeanor)) {
        echo '<div class="success">✅ All database values are already present in the dropdowns!</div>';
        echo '<div class="info">No updates needed.</div>';
        exit;
    }
    
    if (!empty($missing_nature)) {
        echo '<div class="warning">⚠️ Missing ' . count($missing_nature) . ' nature value(s):</div>';
        echo '<ul>';
        foreach ($missing_nature as $value) {
            echo '<li><code>' . htmlspecialchars($value) . '</code></li>';
        }
        echo '</ul>';
    }
    
    if (!empty($missing_demeanor)) {
        echo '<div class="warning">⚠️ Missing ' . count($missing_demeanor) . ' demeanor value(s):</div>';
        echo '<ul>';
        foreach ($missing_demeanor as $value) {
            echo '<li><code>' . htmlspecialchars($value) . '</code></li>';
        }
        echo '</ul>';
    }
    
    // Step 4: Create backup
    echo '<h2>Step 4: Creating Backup</h2>';
    
    if (!copy($form_file, $backup_file)) {
        throw new Exception("Failed to create backup file");
    }
    
    echo '<div class="success">✅ Backup created: ' . basename($backup_file) . '</div>';
    
    // Step 5: Update nature dropdown
    if (!empty($missing_nature)) {
        echo '<h2>Step 5: Updating Nature Dropdown</h2>';
        
        // Combine existing and missing values, sort alphabetically
        $all_nature_values = array_merge($form_nature_values, $missing_nature);
        $all_nature_values = array_unique($all_nature_values);
        sort($all_nature_values);
        
        // Build new options HTML
        $new_nature_options = '<option value="">Select Nature...</option>' . PHP_EOL;
        foreach ($all_nature_values as $value) {
            $display = isset($form_nature_options[$value]) ? $form_nature_options[$value] : $value;
            $new_nature_options .= '                            <option value="' . htmlspecialchars($value) . '">' . htmlspecialchars($display) . '</option>' . PHP_EOL;
        }
        
        // Replace the nature select block
        $new_nature_select = $nature_select_start . PHP_EOL . $new_nature_options . '                        ' . $nature_select_end;
        $form_content = str_replace($nature_matches[0], $new_nature_select, $form_content);
        
        echo '<div class="success">✅ Added ' . count($missing_nature) . ' nature value(s) to dropdown</div>';
    }
    
    // Step 6: Update demeanor dropdown
    if (!empty($missing_demeanor)) {
        echo '<h2>Step 6: Updating Demeanor Dropdown</h2>';
        
        // Combine existing and missing values, sort alphabetically
        $all_demeanor_values = array_merge($form_demeanor_values, $missing_demeanor);
        $all_demeanor_values = array_unique($all_demeanor_values);
        sort($all_demeanor_values);
        
        // Build new options HTML
        $new_demeanor_options = '<option value="">Select Demeanor...</option>' . PHP_EOL;
        foreach ($all_demeanor_values as $value) {
            $display = isset($form_demeanor_options[$value]) ? $form_demeanor_options[$value] : $value;
            $new_demeanor_options .= '                            <option value="' . htmlspecialchars($value) . '">' . htmlspecialchars($display) . '</option>' . PHP_EOL;
        }
        
        // Replace the demeanor select block
        $new_demeanor_select = $demeanor_select_start . PHP_EOL . $new_demeanor_options . '                        ' . $demeanor_select_end;
        $form_content = str_replace($demeanor_matches[0], $new_demeanor_select, $form_content);
        
        echo '<div class="success">✅ Added ' . count($missing_demeanor) . ' demeanor value(s) to dropdown</div>';
    }
    
    // Step 7: Write updated file
    echo '<h2>Step 7: Writing Updated File</h2>';
    
    if (file_put_contents($form_file, $form_content) === false) {
        throw new Exception("Failed to write updated file");
    }
    
    echo '<div class="success">✅ Successfully updated lotn_char_create.php</div>';
    
    // Summary
    echo '<h2>Summary</h2>';
    echo '<div class="success"><strong>✅ Update Complete!</strong></div>';
    echo '<div class="info">📊 Added ' . count($missing_nature) . ' nature value(s) and ' . count($missing_demeanor) . ' demeanor value(s)</div>';
    echo '<div class="info">💾 Backup saved as: ' . basename($backup_file) . '</div>';
    echo '<div class="info">🔍 You can now test by loading a character like Betty to verify the values display correctly</div>';
    
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

