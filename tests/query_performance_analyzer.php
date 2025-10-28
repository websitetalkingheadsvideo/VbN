<?php
/**
 * Query Performance Analyzer
 * Uses EXPLAIN to analyze query performance and identify optimization opportunities
 */

require_once __DIR__ . '/../includes/connect.php';

// ANSI color codes
$colors = [
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'cyan' => "\033[36m",
    'magenta' => "\033[35m",
    'reset' => "\033[0m"
];

function output($message, $color = 'reset') {
    global $colors;
    echo $colors[$color] . $message . $colors['reset'] . "\n";
}

function analyze_query($query, $title = null) {
    global $conn;
    
    output("\n" . str_repeat("=", 80), 'blue');
    output($title ?? "Query Analysis", 'blue');
    output(str_repeat("=", 80), 'blue');
    
    // Display the query
    output("\nQuery:", 'cyan');
    output($query, 'reset');
    
    // Run EXPLAIN
    $explain_query = "EXPLAIN " . $query;
    $result = mysqli_query($conn, $explain_query);
    
    if (!$result) {
        output("\n❌ Error running EXPLAIN: " . mysqli_error($conn), 'red');
        return false;
    }
    
    output("\nEXPLAIN Results:", 'cyan');
    output(str_repeat("-", 80), 'reset');
    
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    if (empty($rows)) {
        output("No EXPLAIN data available", 'yellow');
        return true;
    }
    
    $has_issues = false;
    
    foreach ($rows as $row) {
        output("\nTable: " . $row['table'], 'magenta');
        output("Type: " . $row['type']);
        output("Possible Keys: " . ($row['possible_keys'] ?? 'NONE'));
        output("Key Used: " . ($row['key'] ?? 'NONE'));
        output("Rows Examined: " . ($row['rows'] ?? 'N/A'));
        output("Extra: " . ($row['Extra'] ?? ''));
        
        // Identify performance issues
        if ($row['type'] === 'ALL') {
            output("⚠️  WARNING: Full table scan detected!", 'yellow');
            $has_issues = true;
        }
        
        if (empty($row['key'])) {
            output("⚠️  WARNING: No index used!", 'yellow');
            $has_issues = true;
        }
        
        if (isset($row['rows']) && $row['rows'] > 1000) {
            output("⚠️  WARNING: Large number of rows examined (" . $row['rows'] . ")", 'yellow');
            $has_issues = true;
        }
        
        if (isset($row['Extra']) && stripos($row['Extra'], 'Using filesort') !== false) {
            output("⚠️  WARNING: Filesort detected (consider adding index for ORDER BY)", 'yellow');
            $has_issues = true;
        }
        
        if (isset($row['Extra']) && stripos($row['Extra'], 'Using temporary') !== false) {
            output("⚠️  WARNING: Temporary table created", 'yellow');
            $has_issues = true;
        }
    }
    
    if (!$has_issues) {
        output("\n✅ Query looks well-optimized", 'green');
    } else {
        output("\n⚠️  Query has potential optimization opportunities", 'yellow');
    }
    
    return !$has_issues;
}

// Define queries to analyze
$queries = [
    'Character List with User Join' => "
        SELECT c.id, c.character_name, c.clan, c.generation, u.username 
        FROM characters c 
        LEFT JOIN users u ON c.user_id = u.id 
        ORDER BY c.character_name 
        LIMIT 50
    ",
    
    'Character Traits by Category' => "
        SELECT character_id, trait_name, trait_category 
        FROM character_traits 
        WHERE character_id = 1 AND trait_category = 'Physical'
    ",
    
    'Login Query (Optimized)' => "
        SELECT id, username, email, password_hash, role, email_verified, last_login 
        FROM users 
        WHERE username = 'testuser' AND email_verified = 1
    ",
    
    'Character with All Related Data' => "
        SELECT c.id, c.character_name, 
               COUNT(DISTINCT t.id) as trait_count,
               COUNT(DISTINCT a.id) as ability_count,
               COUNT(DISTINCT d.id) as discipline_count
        FROM characters c
        LEFT JOIN character_traits t ON c.id = t.character_id
        LEFT JOIN character_abilities a ON c.id = a.character_id
        LEFT JOIN character_disciplines d ON c.id = d.character_id
        WHERE c.id = 1
        GROUP BY c.id, c.character_name
    ",
    
    'Recent Characters' => "
        SELECT id, character_name, clan, created_at 
        FROM characters 
        ORDER BY created_at DESC 
        LIMIT 10
    ",
    
    'Equipment by Character' => "
        SELECT ce.id, ce.quantity, ce.equipped, i.name, i.type 
        FROM character_equipment ce 
        INNER JOIN items i ON ce.item_id = i.id 
        WHERE ce.character_id = 1
    ",
    
    'Locations by Type' => "
        SELECT id, name, type, district, status 
        FROM locations 
        WHERE type = 'Haven' AND status = 'Active' 
        ORDER BY name
    ",
    
    'Disciplines by Clan' => "
        SELECT d.id, d.name, d.category, c.name as clan_name 
        FROM disciplines d 
        LEFT JOIN clans c ON d.clan_restriction = c.id 
        WHERE d.category = 'Common' OR c.name = 'Tremere'
    "
];

// Run analysis
output("\n" . str_repeat("=", 80), 'magenta');
output("QUERY PERFORMANCE ANALYSIS", 'magenta');
output(str_repeat("=", 80), 'magenta');
output("\nAnalyzing " . count($queries) . " queries...\n");

$results = [];
$well_optimized = 0;
$needs_optimization = 0;

foreach ($queries as $title => $query) {
    $result = analyze_query($query, $title);
    $results[$title] = $result;
    
    if ($result) {
        $well_optimized++;
    } else {
        $needs_optimization++;
    }
}

// Summary
output("\n" . str_repeat("=", 80), 'magenta');
output("ANALYSIS SUMMARY", 'magenta');
output(str_repeat("=", 80), 'magenta');
output("\nTotal Queries Analyzed: " . count($queries));
output("Well Optimized: " . $well_optimized, 'green');
output("Needs Optimization: " . $needs_optimization, $needs_optimization > 0 ? 'yellow' : 'green');
output("Optimization Rate: " . round(($well_optimized / count($queries)) * 100, 2) . "%");

output("\n" . str_repeat("=", 80), 'blue');
output("OPTIMIZATION RECOMMENDATIONS", 'blue');
output(str_repeat("=", 80), 'blue');
output("
1. Ensure indexes exist on frequently queried columns:
   - characters: user_id, character_name, clan, created_at
   - character_traits: character_id, trait_category
   - character_abilities: character_id, ability_name
   - character_equipment: character_id, item_id
   - users: username, email
   - locations: type, status, district

2. Use LIMIT clauses for queries that might return many rows

3. Use INNER JOIN instead of LEFT JOIN when you know relationships exist

4. Create compound indexes for frequently combined WHERE conditions

5. Consider covering indexes for queries that only need certain columns

6. Use EXPLAIN for any new complex queries before deploying

7. Monitor slow query log for production performance issues
");

if ($needs_optimization > 0) {
    output("⚠️  Some queries need attention. Review warnings above.", 'yellow');
} else {
    output("✅ All analyzed queries are well-optimized!", 'green');
}

output("\n");

mysqli_close($conn);

