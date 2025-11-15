<?php
/**
 * Scan Nature and Demeanor Values from Database
 * 
 * This script scans the characters table for all unique nature and demeanor values
 * and compares them with existing dropdown options in lotn_char_create.php
 */

require_once __DIR__ . '/../includes/connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Scan Nature/Demeanor Values</title>
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
    </style>
</head>
<body>
    <h1>🔍 Scanning Nature and Demeanor Values</h1>

<?php

if (!$conn) {
    echo '<div class="error">❌ Database connection failed</div>';
    exit;
}

try {
    // Step 1: Get unique nature values from database
    echo '<h2>Step 1: Querying Database for Nature Values</h2>';
    
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
    
    echo '<div class="success">✅ Found ' . count($db_nature_values) . ' unique nature values in database</div>';
    echo '<ul>';
    foreach ($db_nature_values as $value) {
        echo '<li><code>' . htmlspecialchars($value) . '</code></li>';
    }
    echo '</ul>';
    
    // Step 2: Get unique demeanor values from database
    echo '<h2>Step 2: Querying Database for Demeanor Values</h2>';
    
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
    
    echo '<div class="success">✅ Found ' . count($db_demeanor_values) . ' unique demeanor values in database</div>';
    echo '<ul>';
    foreach ($db_demeanor_values as $value) {
        echo '<li><code>' . htmlspecialchars($value) . '</code></li>';
    }
    echo '</ul>';
    
    // Step 3: Parse existing dropdown options from lotn_char_create.php
    echo '<h2>Step 3: Parsing Existing Dropdown Options</h2>';
    
    $form_file = __DIR__ . '/../lotn_char_create.php';
    
    if (!file_exists($form_file)) {
        throw new Exception("Form file not found: " . $form_file);
    }
    
    $form_content = file_get_contents($form_file);
    
    // Extract nature options using regex
    preg_match('/<select id="nature"[^>]*>(.*?)<\/select>/is', $form_content, $nature_matches);
    $nature_html = isset($nature_matches[1]) ? $nature_matches[1] : '';
    
    preg_match_all('/<option value="([^"]+)"[^>]*>/i', $nature_html, $nature_option_matches);
    $form_nature_values = array_filter(array_map('trim', $nature_option_matches[1]), function($v) {
        return $v !== '' && $v !== 'Select Nature...';
    });
    
    // Extract demeanor options using regex
    preg_match('/<select id="demeanor"[^>]*>(.*?)<\/select>/is', $form_content, $demeanor_matches);
    $demeanor_html = isset($demeanor_matches[1]) ? $demeanor_matches[1] : '';
    
    preg_match_all('/<option value="([^"]+)"[^>]*>/i', $demeanor_html, $demeanor_option_matches);
    $form_demeanor_values = array_filter(array_map('trim', $demeanor_option_matches[1]), function($v) {
        return $v !== '' && $v !== 'Select Demeanor...';
    });
    
    echo '<div class="info">ℹ️ Found ' . count($form_nature_values) . ' nature options in form</div>';
    echo '<div class="info">ℹ️ Found ' . count($form_demeanor_values) . ' demeanor options in form</div>';
    
    // Step 4: Compare and find missing values
    echo '<h2>Step 4: Identifying Missing Values</h2>';
    
    $missing_nature = array_diff($db_nature_values, $form_nature_values);
    $missing_demeanor = array_diff($db_demeanor_values, $form_demeanor_values);
    
    if (empty($missing_nature) && empty($missing_demeanor)) {
        echo '<div class="success">✅ All database values are present in the dropdowns!</div>';
    } else {
        if (!empty($missing_nature)) {
            echo '<div class="warning">⚠️ Missing ' . count($missing_nature) . ' nature value(s) in dropdown:</div>';
            echo '<ul>';
            foreach ($missing_nature as $value) {
                echo '<li><code>' . htmlspecialchars($value) . '</code></li>';
            }
            echo '</ul>';
        }
        
        if (!empty($missing_demeanor)) {
            echo '<div class="warning">⚠️ Missing ' . count($missing_demeanor) . ' demeanor value(s) in dropdown:</div>';
            echo '<ul>';
            foreach ($missing_demeanor as $value) {
                echo '<li><code>' . htmlspecialchars($value) . '</code></li>';
            }
            echo '</ul>';
        }
    }
    
    // Step 5: Check for case sensitivity issues
    echo '<h2>Step 5: Checking for Case Sensitivity Issues</h2>';
    
    $case_issues_nature = [];
    $case_issues_demeanor = [];
    
    foreach ($db_nature_values as $db_value) {
        foreach ($form_nature_values as $form_value) {
            if (strcasecmp($db_value, $form_value) === 0 && $db_value !== $form_value) {
                $case_issues_nature[] = ['db' => $db_value, 'form' => $form_value];
            }
        }
    }
    
    foreach ($db_demeanor_values as $db_value) {
        foreach ($form_demeanor_values as $form_value) {
            if (strcasecmp($db_value, $form_value) === 0 && $db_value !== $form_value) {
                $case_issues_demeanor[] = ['db' => $db_value, 'form' => $form_value];
            }
        }
    }
    
    if (empty($case_issues_nature) && empty($case_issues_demeanor)) {
        echo '<div class="success">✅ No case sensitivity issues found</div>';
    } else {
        if (!empty($case_issues_nature)) {
            echo '<div class="warning">⚠️ Case differences in nature values:</div>';
            echo '<ul>';
            foreach ($case_issues_nature as $issue) {
                echo '<li>DB: <code>' . htmlspecialchars($issue['db']) . '</code> vs Form: <code>' . htmlspecialchars($issue['form']) . '</code></li>';
            }
            echo '</ul>';
        }
        
        if (!empty($case_issues_demeanor)) {
            echo '<div class="warning">⚠️ Case differences in demeanor values:</div>';
            echo '<ul>';
            foreach ($case_issues_demeanor as $issue) {
                echo '<li>DB: <code>' . htmlspecialchars($issue['db']) . '</code> vs Form: <code>' . htmlspecialchars($issue['form']) . '</code></li>';
            }
            echo '</ul>';
        }
    }
    
    // Summary
    echo '<h2>Summary</h2>';
    echo '<div class="info">📊 Database contains ' . count($db_nature_values) . ' unique nature values</div>';
    echo '<div class="info">📊 Database contains ' . count($db_demeanor_values) . ' unique demeanor values</div>';
    echo '<div class="info">📋 Form contains ' . count($form_nature_values) . ' nature options</div>';
    echo '<div class="info">📋 Form contains ' . count($form_demeanor_values) . ' demeanor options</div>';
    
    if (!empty($missing_nature) || !empty($missing_demeanor)) {
        echo '<div class="warning"><strong>⚠️ Action Required:</strong> ' . (count($missing_nature) + count($missing_demeanor)) . ' value(s) need to be added to the dropdowns</div>';
    } else {
        echo '<div class="success"><strong>✅ All values are synchronized!</strong></div>';
    }
    
    // Store results for next step
    $_SESSION['scan_results'] = [
        'missing_nature' => array_values($missing_nature),
        'missing_demeanor' => array_values($missing_demeanor),
        'db_nature' => $db_nature_values,
        'db_demeanor' => $db_demeanor_values,
        'form_nature' => array_values($form_nature_values),
        'form_demeanor' => array_values($form_demeanor_values)
    ];
    
} catch (Exception $e) {
    echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>

</body>
</html>

