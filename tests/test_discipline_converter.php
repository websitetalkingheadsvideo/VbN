<?php
/**
 * Test Discipline Converter
 * 
 * Tests the discipline conversion functions with various scenarios
 */

require_once __DIR__ . '/../data/disciplines_converter.php';

echo "=================================================================\n";
echo "Discipline Converter Test Suite\n";
echo "=================================================================\n\n";

// Test 1: Basic conversion with powers
echo "Test 1: Basic Conversion (Bayside Bob example)\n";
echo "------------------------------------------------\n";

$test_disciplines_1 = [
    [
        "name" => "Auspex",
        "level" => 3,
        "powers" => [
            ["level" => 1, "power" => "Heightened Senses"],
            ["level" => 2, "power" => "The Spirit's Touch"],
            ["level" => 3, "power" => "Psychic Projection"]
        ]
    ],
    [
        "name" => "Celerity",
        "level" => 2,
        "powers" => [
            ["level" => 1, "power" => "Supernatural Speed"],
            ["level" => 2, "power" => "Quicken Reflexes"]
        ]
    ],
    [
        "name" => "Presence",
        "level" => 3,
        "powers" => [
            ["level" => 1, "power" => "Awe"],
            ["level" => 2, "power" => "Dread Gaze"],
            ["level" => 3, "power" => "Entrancement"]
        ]
    ]
];

$result_1 = convertDisciplinesToDatabase($test_disciplines_1);
echo "Converted " . count($result_1) . " discipline levels\n";
echo "Expected: 8 levels (3 Auspex + 2 Celerity + 3 Presence)\n";
echo (count($result_1) === 8 ? "✅ PASS\n" : "❌ FAIL\n");
echo "\n";

// Display detailed results
foreach ($result_1 as $row) {
    echo "  - {$row['discipline_name']} level {$row['level']} (XP: {$row['xp_cost']})\n";
}
echo "\n";

// Test statistics
$stats_1 = getDisciplineStatistics($result_1);
echo "Statistics:\n";
echo "  Total XP: {$stats_1['total_xp']} (Expected: 0 - all free levels)\n";
echo "  Discipline breakdown:\n";
foreach ($stats_1['discipline_counts'] as $name => $count) {
    echo "    - $name: $count levels\n";
}
echo (count($stats_1['discipline_counts']) === 3 ? "✅ PASS\n" : "❌ FAIL\n");
echo "\n\n";

// Test 2: Discipline with XP costs (levels 4-5)
echo "Test 2: XP Cost Calculation (levels 4-5)\n";
echo "------------------------------------------------\n";

$test_disciplines_2 = [
    [
        "name" => "Potence",
        "level" => 5,
        "powers" => [
            ["level" => 1, "power" => "Potence 1"],
            ["level" => 2, "power" => "Potence 2"],
            ["level" => 3, "power" => "Potence 3"],
            ["level" => 4, "power" => "Potence 4"],
            ["level" => 5, "power" => "Potence 5"]
        ]
    ]
];

$result_2 = convertDisciplinesToDatabase($test_disciplines_2);
$stats_2 = getDisciplineStatistics($result_2);

echo "Potence 5 - XP breakdown:\n";
foreach ($result_2 as $row) {
    echo "  Level {$row['level']}: {$row['xp_cost']} XP\n";
}
echo "Total XP: {$stats_2['total_xp']} (Expected: 6 - 3+3 for levels 4-5)\n";
echo ($stats_2['total_xp'] === 6 ? "✅ PASS\n" : "❌ FAIL\n");
echo "\n\n";

// Test 3: Discipline without powers array (fallback to level field)
echo "Test 3: Fallback to Level Field (no powers defined)\n";
echo "------------------------------------------------\n";

$test_disciplines_3 = [
    [
        "name" => "Fortitude",
        "level" => 2
        // No powers array
    ]
];

$result_3 = convertDisciplinesToDatabase($test_disciplines_3);
echo "Converted " . count($result_3) . " discipline levels\n";
echo "Expected: 2 levels (sequential from 1 to 2)\n";
echo (count($result_3) === 2 ? "✅ PASS\n" : "❌ FAIL\n");
echo "\n";

foreach ($result_3 as $row) {
    echo "  - {$row['discipline_name']} level {$row['level']} (XP: {$row['xp_cost']})\n";
}
echo "\n\n";

// Test 4: Edge cases
echo "Test 4: Edge Cases\n";
echo "------------------------------------------------\n";

// Empty array
$result_4a = convertDisciplinesToDatabase([]);
echo "Empty array: " . (count($result_4a) === 0 ? "✅ PASS" : "❌ FAIL") . "\n";

// Invalid discipline (no name)
$result_4b = convertDisciplinesToDatabase([["level" => 3]]);
echo "Missing name: " . (count($result_4b) === 0 ? "✅ PASS" : "❌ FAIL") . "\n";

// Invalid level (> 5)
$result_4c = convertDisciplinesToDatabase([
    ["name" => "Test", "level" => 3, "powers" => [["level" => 6, "power" => "Test"]]]
]);
echo "Level > 5: " . (count($result_4c) === 0 ? "✅ PASS" : "❌ FAIL") . "\n";

// Level exactly 5 (max)
$result_4d = convertDisciplinesToDatabase([
    ["name" => "Test", "level" => 5, "powers" => [
        ["level" => 5, "power" => "Final Power"]
    ]]
]);
echo "Level 5 valid: " . (count($result_4d) === 1 && $result_4d[0]['level'] === 5 ? "✅ PASS" : "❌ FAIL") . "\n";
echo "\n\n";

// Test 5: Mixed scenario
echo "Test 5: Mixed Scenario (Real Character)\n";
echo "------------------------------------------------\n";

$test_disciplines_5 = [
    [
        "name" => "Animalism",
        "level" => 2,
        "powers" => [
            ["level" => 1, "power" => "Heightened Senses"],
            ["level" => 2, "power" => "The Beast's Call"]
        ]
    ],
    [
        "name" => "Protean",
        "level" => 4,
        "powers" => [
            ["level" => 1, "power" => "Eyes of the Beast"],
            ["level" => 2, "power" => "Heightened Senses"],
            ["level" => 3, "power" => "Weight of the Feather"],
            ["level" => 4, "power" => "Shape of the Beast's Wrath"]
        ]
    ]
];

$result_5 = convertDisciplinesToDatabase($test_disciplines_5);
$stats_5 = getDisciplineStatistics($result_5);

echo "Mixed disciplines:\n";
echo "  Total levels: {$stats_5['total_levels']} (Expected: 6)\n";
echo "  Total XP: {$stats_5['total_xp']} (Expected: 3)\n";
echo "  Disciplines: " . count($stats_5['discipline_counts']) . "\n";

echo (count($result_5) === 6 && $stats_5['total_xp'] === 3 ? "✅ PASS\n" : "❌ FAIL\n");
echo "\n";

foreach ($result_5 as $row) {
    echo "  - {$row['discipline_name']} level {$row['level']} (XP: {$row['xp_cost']})\n";
}
echo "\n\n";

// Test Summary
echo "=================================================================\n";
echo "Test Summary\n";
echo "=================================================================\n";
echo "All tests completed.\n";
echo "Review output above for pass/fail status.\n";
echo "=================================================================\n";





























