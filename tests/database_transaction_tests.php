<?php
/**
 * Database Transaction Tests
 * Tests transaction functionality, rollback behavior, and data integrity
 */

require_once __DIR__ . '/../includes/connect.php';

// Test configuration
$TEST_USER_ID = 1; // Use admin user for tests
$CLEANUP_AFTER_TESTS = true; // Set to false to inspect test data

// ANSI color codes for terminal output
$colors = [
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'reset' => "\033[0m"
];

function test_output($message, $status = 'info') {
    global $colors;
    $color = $status === 'pass' ? 'green' : ($status === 'fail' ? 'red' : ($status === 'warn' ? 'yellow' : 'blue'));
    echo $colors[$color] . $message . $colors['reset'] . "\n";
}

function run_test($test_name, $test_function) {
    test_output("\n" . str_repeat("=", 70), 'blue');
    test_output("TEST: $test_name", 'blue');
    test_output(str_repeat("=", 70), 'blue');
    
    try {
        $result = $test_function();
        if ($result) {
            test_output("✅ PASS: $test_name", 'pass');
            return true;
        } else {
            test_output("❌ FAIL: $test_name", 'fail');
            return false;
        }
    } catch (Exception $e) {
        test_output("❌ ERROR: " . $e->getMessage(), 'fail');
        return false;
    }
}

// Test 1: Transaction Rollback on Error
function test_transaction_rollback() {
    global $conn, $TEST_USER_ID;
    
    test_output("Testing transaction rollback on error...");
    
    // Start transaction
    db_begin_transaction($conn);
    
    try {
        // Insert a test character
        $char_id = db_execute(
            "INSERT INTO characters (user_id, character_name, player_name, chronicle) VALUES (?, ?, ?, ?)",
            [$TEST_USER_ID, 'Rollback Test', 'Test Player', 'Test Chronicle'],
            'isss'
        );
        
        test_output("Inserted test character with ID: $char_id");
        
        // Intentionally cause an error (foreign key violation)
        try {
            db_execute(
                "INSERT INTO character_traits (character_id, trait_name, trait_category) VALUES (?, ?, ?)",
                [99999, 'Invalid Trait', 'Physical'], // Non-existent character_id
                'iss'
            );
        } catch (Exception $e) {
            test_output("Expected error occurred: " . $e->getMessage(), 'warn');
            db_rollback($conn);
        }
        
        // Verify character was rolled back
        $check = db_fetch_one(
            "SELECT id FROM characters WHERE character_name = ?",
            ['Rollback Test'],
            's'
        );
        
        if ($check === null) {
            test_output("✓ Character was successfully rolled back");
            return true;
        } else {
            test_output("✗ Character was NOT rolled back (data integrity error)", 'fail');
            // Cleanup
            db_execute("DELETE FROM characters WHERE id = ?", [$check['id']], 'i');
            return false;
        }
        
    } catch (Exception $e) {
        db_rollback($conn);
        throw $e;
    }
}

// Test 2: Transaction Commit on Success
function test_transaction_commit() {
    global $conn, $TEST_USER_ID, $CLEANUP_AFTER_TESTS;
    
    test_output("Testing transaction commit on success...");
    
    db_begin_transaction($conn);
    
    try {
        // Insert character
        $char_id = db_execute(
            "INSERT INTO characters (user_id, character_name, player_name, chronicle) VALUES (?, ?, ?, ?)",
            [$TEST_USER_ID, 'Commit Test', 'Test Player', 'Test Chronicle'],
            'isss'
        );
        
        test_output("Inserted character with ID: $char_id");
        
        // Insert traits
        $traits = ['Strong', 'Quick', 'Brawny'];
        foreach ($traits as $trait) {
            db_execute(
                "INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type) VALUES (?, ?, ?, ?)",
                [$char_id, $trait, 'Physical', 'positive'],
                'isss'
            );
        }
        
        test_output("Inserted " . count($traits) . " traits");
        
        db_commit($conn);
        
        // Verify data persisted
        $check_char = db_fetch_one("SELECT id FROM characters WHERE id = ?", [$char_id], 'i');
        $check_traits = db_fetch_all("SELECT trait_name FROM character_traits WHERE character_id = ?", [$char_id], 'i');
        
        $success = ($check_char !== null && count($check_traits) === count($traits));
        
        if ($success) {
            test_output("✓ Character and traits successfully committed");
        } else {
            test_output("✗ Data was not properly committed", 'fail');
        }
        
        // Cleanup
        if ($CLEANUP_AFTER_TESTS) {
            db_execute("DELETE FROM character_traits WHERE character_id = ?", [$char_id], 'i');
            db_execute("DELETE FROM characters WHERE id = ?", [$char_id], 'i');
            test_output("Cleaned up test data");
        }
        
        return $success;
        
    } catch (Exception $e) {
        db_rollback($conn);
        throw $e;
    }
}

// Test 3: Equipment Atomicity
function test_equipment_atomicity() {
    global $conn, $TEST_USER_ID, $CLEANUP_AFTER_TESTS;
    
    test_output("Testing equipment add operation atomicity...");
    
    // Create test character
    $char_id = db_execute(
        "INSERT INTO characters (user_id, character_name, player_name, chronicle) VALUES (?, ?, ?, ?)",
        [$TEST_USER_ID, 'Equipment Test', 'Test Player', 'Test Chronicle'],
        'isss'
    );
    
    // Get first item from items table
    $item = db_fetch_one("SELECT id FROM items LIMIT 1", [], '');
    
    if (!$item) {
        test_output("⚠ No items in database, skipping equipment test", 'warn');
        db_execute("DELETE FROM characters WHERE id = ?", [$char_id], 'i');
        return true; // Skip test
    }
    
    $item_id = $item['id'];
    
    // Test 1: Add new equipment
    mysqli_begin_transaction($conn);
    try {
        $check = db_fetch_one(
            "SELECT id, quantity FROM character_equipment WHERE character_id = ? AND item_id = ?",
            [$char_id, $item_id],
            'ii'
        );
        
        if ($check === null) {
            db_execute(
                "INSERT INTO character_equipment (character_id, item_id, quantity, equipped) VALUES (?, ?, ?, ?)",
                [$char_id, $item_id, 1, 0],
                'iiii'
            );
            test_output("✓ Added new equipment item");
        }
        
        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
    
    // Test 2: Update existing equipment quantity
    mysqli_begin_transaction($conn);
    try {
        $existing = db_fetch_one(
            "SELECT id, quantity FROM character_equipment WHERE character_id = ? AND item_id = ?",
            [$char_id, $item_id],
            'ii'
        );
        
        if ($existing) {
            $new_quantity = $existing['quantity'] + 5;
            db_execute(
                "UPDATE character_equipment SET quantity = ? WHERE id = ?",
                [$new_quantity, $existing['id']],
                'ii'
            );
            test_output("✓ Updated equipment quantity to $new_quantity");
        }
        
        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
    
    // Verify
    $final = db_fetch_one(
        "SELECT quantity FROM character_equipment WHERE character_id = ? AND item_id = ?",
        [$char_id, $item_id],
        'ii'
    );
    
    $success = ($final && $final['quantity'] == 6);
    
    // Cleanup
    if ($CLEANUP_AFTER_TESTS) {
        db_execute("DELETE FROM character_equipment WHERE character_id = ?", [$char_id], 'i');
        db_execute("DELETE FROM characters WHERE id = ?", [$char_id], 'i');
        test_output("Cleaned up test data");
    }
    
    return $success;
}

// Test 4: Character Deletion Atomicity
function test_character_deletion_atomicity() {
    global $conn, $TEST_USER_ID, $CLEANUP_AFTER_TESTS;
    
    test_output("Testing character deletion atomicity...");
    
    // Create test character with related data
    db_begin_transaction($conn);
    try {
        $char_id = db_execute(
            "INSERT INTO characters (user_id, character_name, player_name, chronicle) VALUES (?, ?, ?, ?)",
            [$TEST_USER_ID, 'Delete Test', 'Test Player', 'Test Chronicle'],
            'isss'
        );
        
        // Add related data
        db_execute(
            "INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type) VALUES (?, ?, ?, ?)",
            [$char_id, 'Test Trait', 'Physical', 'positive'],
            'isss'
        );
        
        db_execute(
            "INSERT INTO character_abilities (character_id, ability_name, level) VALUES (?, ?, ?)",
            [$char_id, 'Test Ability', 3],
            'isi'
        );
        
        db_commit($conn);
        test_output("Created test character with related data");
        
    } catch (Exception $e) {
        db_rollback($conn);
        throw $e;
    }
    
    // Now test deletion
    db_begin_transaction($conn);
    try {
        $tables = ['character_traits', 'character_abilities'];
        
        foreach ($tables as $table) {
            db_execute("DELETE FROM $table WHERE character_id = ?", [$char_id], 'i');
        }
        
        db_execute("DELETE FROM characters WHERE id = ?", [$char_id], 'i');
        
        db_commit($conn);
        test_output("✓ Executed deletion transaction");
        
    } catch (Exception $e) {
        db_rollback($conn);
        throw $e;
    }
    
    // Verify complete deletion
    $char_check = db_fetch_one("SELECT id FROM characters WHERE id = ?", [$char_id], 'i');
    $traits_check = db_fetch_all("SELECT id FROM character_traits WHERE character_id = ?", [$char_id], 'i');
    $abilities_check = db_fetch_all("SELECT id FROM character_abilities WHERE character_id = ?", [$char_id], 'i');
    
    $success = ($char_check === null && count($traits_check) === 0 && count($abilities_check) === 0);
    
    if ($success) {
        test_output("✓ All related data properly deleted");
    } else {
        test_output("✗ Some data was not deleted (orphaned records)", 'fail');
    }
    
    return $success;
}

// Test 5: Prepared Statement Parameter Binding
function test_prepared_statement_binding() {
    global $conn, $TEST_USER_ID, $CLEANUP_AFTER_TESTS;
    
    test_output("Testing prepared statement parameter binding...");
    
    // Test with various data types
    $char_id = db_execute(
        "INSERT INTO characters (user_id, character_name, player_name, chronicle, generation) VALUES (?, ?, ?, ?, ?)",
        [$TEST_USER_ID, "Test'Character", 'Test"Player', 'Test Chronicle', 13],
        'isssi'
    );
    
    test_output("Created character with special characters in name");
    
    // Verify data integrity
    $check = db_fetch_one("SELECT character_name, player_name, generation FROM characters WHERE id = ?", [$char_id], 'i');
    
    $success = (
        $check !== null &&
        $check['character_name'] === "Test'Character" &&
        $check['player_name'] === 'Test"Player' &&
        $check['generation'] == 13
    );
    
    if ($success) {
        test_output("✓ Special characters properly escaped");
    } else {
        test_output("✗ Data integrity issue with special characters", 'fail');
    }
    
    // Cleanup
    if ($CLEANUP_AFTER_TESTS) {
        db_execute("DELETE FROM characters WHERE id = ?", [$char_id], 'i');
        test_output("Cleaned up test data");
    }
    
    return $success;
}

// Main test runner
function run_all_tests() {
    test_output("\n" . str_repeat("=", 70), 'blue');
    test_output("DATABASE TRANSACTION TEST SUITE", 'blue');
    test_output(str_repeat("=", 70) . "\n", 'blue');
    
    $tests = [
        'Transaction Rollback on Error' => 'test_transaction_rollback',
        'Transaction Commit on Success' => 'test_transaction_commit',
        'Equipment Operation Atomicity' => 'test_equipment_atomicity',
        'Character Deletion Atomicity' => 'test_character_deletion_atomicity',
        'Prepared Statement Parameter Binding' => 'test_prepared_statement_binding'
    ];
    
    $results = [];
    $passed = 0;
    $failed = 0;
    
    foreach ($tests as $name => $function) {
        $result = run_test($name, $function);
        $results[$name] = $result;
        if ($result) {
            $passed++;
        } else {
            $failed++;
        }
    }
    
    // Summary
    test_output("\n" . str_repeat("=", 70), 'blue');
    test_output("TEST SUMMARY", 'blue');
    test_output(str_repeat("=", 70), 'blue');
    test_output("Total Tests: " . count($tests));
    test_output("Passed: $passed", 'pass');
    test_output("Failed: $failed", $failed > 0 ? 'fail' : 'pass');
    test_output("Success Rate: " . round(($passed / count($tests)) * 100, 2) . "%");
    
    if ($failed === 0) {
        test_output("\n🎉 ALL TESTS PASSED!", 'pass');
    } else {
        test_output("\n⚠️  SOME TESTS FAILED", 'fail');
    }
    
    return $failed === 0;
}

// Run tests
try {
    $success = run_all_tests();
    exit($success ? 0 : 1);
} catch (Exception $e) {
    test_output("\n❌ FATAL ERROR: " . $e->getMessage(), 'fail');
    test_output($e->getTraceAsString(), 'fail');
    exit(1);
}

