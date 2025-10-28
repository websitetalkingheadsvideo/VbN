# Prepared Statement Patterns

## Overview
This guide documents the standardized patterns for using prepared statements in the VbN project, using the helper functions from `includes/connect.php`.

## Date Created
**Date:** January 2025

---

## Helper Functions Reference

The project uses centralized helper functions for database operations:

### Query Functions
- `db_select($sql, $params = [], $types = '')` - Execute SELECT, return result object
- `db_execute($sql, $params = [], $types = '')` - Execute INSERT/UPDATE/DELETE, return affected rows/ID
- `db_fetch_one($sql, $params = [], $types = '')` - Fetch single row as associative array
- `db_fetch_all($sql, $params = [], $types = '')` - Fetch all rows as array of arrays

### Transaction Functions
- `db_begin_transaction($conn)` - Start transaction
- `db_commit($conn)` - Commit transaction
- `db_rollback($conn)` - Rollback transaction
- `db_transaction($conn, $callback)` - Execute callback in transaction with auto-rollback on error

### Utility Functions
- `db_escape($value)` - Escape string (fallback, prefer prepared statements)

---

## Parameter Type Codes

| Code | MySQL Type | PHP Type | Example |
|------|------------|----------|---------|
| `i` | INTEGER | int | `123`, `-45` |
| `d` | DOUBLE | float | `3.14`, `-2.5` |
| `s` | STRING | string | `"Hello"`, `"O'Neill"` |
| `b` | BLOB | binary | Binary data |

### Type String Examples
```php
'i'      // Single integer
's'      // Single string
'isss'   // Integer, followed by 3 strings
'ii'     // Two integers
'isssi'  // Integer, 3 strings, integer
```

---

## Pattern 1: Simple SELECT Query

### Single Row Fetch
```php
// Get one character by ID
$character = db_fetch_one(
    "SELECT id, character_name, clan, generation 
     FROM characters 
     WHERE id = ?",
    [$character_id],
    'i'
);

if ($character) {
    echo "Character: " . $character['character_name'];
} else {
    echo "Character not found";
}
```

### Multiple Rows Fetch
```php
// Get all traits for a character
$traits = db_fetch_all(
    "SELECT id, trait_name, trait_category 
     FROM character_traits 
     WHERE character_id = ? 
     ORDER BY trait_name",
    [$character_id],
    'i'
);

foreach ($traits as $trait) {
    echo "{$trait['trait_name']} ({$trait['trait_category']})\n";
}
```

### No Parameters
```php
// Get all clans
$clans = db_fetch_all(
    "SELECT id, name, description 
     FROM clans 
     ORDER BY name",
    [],
    ''
);
```

---

## Pattern 2: INSERT Queries

### Simple INSERT
```php
// Insert new character
try {
    $character_id = db_execute(
        "INSERT INTO characters (user_id, character_name, player_name, chronicle) 
         VALUES (?, ?, ?, ?)",
        [$user_id, $char_name, $player_name, $chronicle],
        'isss'
    );
    
    echo "Character created with ID: $character_id";
} catch (Exception $e) {
    error_log("Failed to create character: " . $e->getMessage());
    throw $e;
}
```

### INSERT with Multiple Data Types
```php
// Insert character with various fields
$character_id = db_execute(
    "INSERT INTO characters (
        user_id, character_name, clan, generation, 
        experience_total, experience_unspent, blood_pool_current
     ) VALUES (?, ?, ?, ?, ?, ?, ?)",
    [
        $user_id,              // int
        $character_name,       // string
        $clan,                 // string
        $generation,           // int
        $xp_total,             // int
        $xp_unspent,           // int
        $blood_pool            // int
    ],
    'issiii'
);
```

### Bulk INSERT (in transaction)
```php
db_begin_transaction($conn);
try {
    $trait_data = [
        ['Strong', 'Physical'],
        ['Quick', 'Physical'],
        ['Charismatic', 'Social']
    ];
    
    foreach ($trait_data as [$name, $category]) {
        db_execute(
            "INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type) 
             VALUES (?, ?, ?, ?)",
            [$character_id, $name, $category, 'positive'],
            'isss'
        );
    }
    
    db_commit($conn);
} catch (Exception $e) {
    db_rollback($conn);
    throw $e;
}
```

---

## Pattern 3: UPDATE Queries

### Simple UPDATE
```php
// Update character name
$affected_rows = db_execute(
    "UPDATE characters 
     SET character_name = ? 
     WHERE id = ?",
    [$new_name, $character_id],
    'si'
);

if ($affected_rows > 0) {
    echo "Character updated successfully";
} else {
    echo "No character found with that ID";
}
```

### Multiple Column UPDATE
```php
// Update multiple character fields
db_execute(
    "UPDATE characters 
     SET character_name = ?, clan = ?, generation = ?, experience_total = ? 
     WHERE id = ?",
    [$name, $clan, $generation, $xp, $character_id],
    'ssiii'
);
```

### Conditional UPDATE
```php
// Update last login timestamp
db_execute(
    "UPDATE users 
     SET last_login = NOW() 
     WHERE id = ? AND email_verified = 1",
    [$user_id],
    'i'
);
```

### INCREMENT/DECREMENT
```php
// Add experience points
db_execute(
    "UPDATE characters 
     SET experience_total = experience_total + ? 
     WHERE id = ?",
    [$xp_gained, $character_id],
    'ii'
);
```

---

## Pattern 4: DELETE Queries

### Simple DELETE
```php
// Delete character trait
$affected = db_execute(
    "DELETE FROM character_traits 
     WHERE id = ?",
    [$trait_id],
    'i'
);
```

### DELETE with Multiple Conditions
```php
// Delete all traits of specific category for character
db_execute(
    "DELETE FROM character_traits 
     WHERE character_id = ? AND trait_category = ?",
    [$character_id, $category],
    'is'
);
```

### Cascade DELETE (with transaction)
```php
// Delete character and all related data
db_begin_transaction($conn);
try {
    $tables = [
        'character_traits',
        'character_abilities',
        'character_disciplines',
        'character_equipment'
    ];
    
    foreach ($tables as $table) {
        db_execute(
            "DELETE FROM $table WHERE character_id = ?",
            [$character_id],
            'i'
        );
    }
    
    db_execute(
        "DELETE FROM characters WHERE id = ?",
        [$character_id],
        'i'
    );
    
    db_commit($conn);
} catch (Exception $e) {
    db_rollback($conn);
    throw $e;
}
```

---

## Pattern 5: Complex Queries with JOINs

### INNER JOIN
```php
// Get equipment with item details
$equipment = db_fetch_all(
    "SELECT ce.id, ce.quantity, ce.equipped, 
            i.name, i.type, i.category, i.damage 
     FROM character_equipment ce 
     INNER JOIN items i ON ce.item_id = i.id 
     WHERE ce.character_id = ? 
     ORDER BY i.category, i.name",
    [$character_id],
    'i'
);
```

### LEFT JOIN
```php
// Get characters with optional user information
$characters = db_fetch_all(
    "SELECT c.id, c.character_name, c.clan, u.username 
     FROM characters c 
     LEFT JOIN users u ON c.user_id = u.id 
     WHERE c.clan = ? 
     ORDER BY c.character_name",
    [$clan],
    's'
);
```

### Multiple JOINs
```php
// Get character with creator and last editor
$character = db_fetch_one(
    "SELECT c.id, c.character_name, 
            u1.username as created_by, 
            u2.username as updated_by 
     FROM characters c 
     LEFT JOIN users u1 ON c.user_id = u1.id 
     LEFT JOIN users u2 ON c.updated_by = u2.id 
     WHERE c.id = ?",
    [$character_id],
    'i'
);
```

---

## Pattern 6: Aggregation Queries

### COUNT
```php
// Count traits by category
$counts = db_fetch_all(
    "SELECT trait_category, COUNT(*) as count 
     FROM character_traits 
     WHERE character_id = ? 
     GROUP BY trait_category",
    [$character_id],
    'i'
);
```

### SUM
```php
// Total experience spent
$result = db_fetch_one(
    "SELECT SUM(xp_cost) as total_spent 
     FROM character_purchases 
     WHERE character_id = ?",
    [$character_id],
    'i'
);

$total_xp_spent = $result['total_spent'] ?? 0;
```

### GROUP BY with HAVING
```php
// Find clans with more than 5 characters
$popular_clans = db_fetch_all(
    "SELECT clan, COUNT(*) as count 
     FROM characters 
     GROUP BY clan 
     HAVING COUNT(*) > ? 
     ORDER BY count DESC",
    [5],
    'i'
);
```

---

## Pattern 7: Authentication & Security

### User Login
```php
// Secure login query
$user = db_fetch_one(
    "SELECT id, username, email, password_hash, role, email_verified, last_login 
     FROM users 
     WHERE username = ? AND email_verified = 1",
    [$username],
    's'
);

if ($user && password_verify($password, $user['password_hash'])) {
    // Update last login
    db_execute(
        "UPDATE users SET last_login = NOW() WHERE id = ?",
        [$user['id']],
        'i'
    );
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
}
```

### Password Reset
```php
// Verify reset token
$user = db_fetch_one(
    "SELECT id, username, email 
     FROM users 
     WHERE reset_token = ? AND reset_expiry > NOW()",
    [$token],
    's'
);

if ($user) {
    // Update password and clear token
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    db_execute(
        "UPDATE users 
         SET password_hash = ?, reset_token = NULL, reset_expiry = NULL 
         WHERE id = ?",
        [$new_hash, $user['id']],
        'si'
    );
}
```

---

## Pattern 8: Transaction Patterns

### Basic Transaction
```php
db_begin_transaction($conn);
try {
    // Multiple operations
    $char_id = db_execute("INSERT INTO characters ...", [...], '...');
    db_execute("INSERT INTO character_traits ...", [$char_id, ...], '...');
    
    db_commit($conn);
    return $char_id;
} catch (Exception $e) {
    db_rollback($conn);
    throw $e;
}
```

### Transaction with Callback
```php
$character_id = db_transaction($conn, function() use ($conn, $data) {
    // Insert character
    $char_id = db_execute(
        "INSERT INTO characters (user_id, character_name, clan) VALUES (?, ?, ?)",
        [$data['user_id'], $data['name'], $data['clan']],
        'iss'
    );
    
    // Insert traits
    foreach ($data['traits'] as $trait) {
        db_execute(
            "INSERT INTO character_traits (character_id, trait_name, trait_category) 
             VALUES (?, ?, ?)",
            [$char_id, $trait['name'], $trait['category']],
            'iss'
        );
    }
    
    return $char_id;
});
```

---

## Pattern 9: Dynamic Queries (Advanced)

### Variable WHERE Clauses
```php
// Build query dynamically based on filters
function getFilteredCharacters($filters) {
    $where = ["1=1"]; // Always true base condition
    $params = [];
    $types = '';
    
    if (!empty($filters['clan'])) {
        $where[] = "clan = ?";
        $params[] = $filters['clan'];
        $types .= 's';
    }
    
    if (!empty($filters['min_generation'])) {
        $where[] = "generation >= ?";
        $params[] = $filters['min_generation'];
        $types .= 'i';
    }
    
    if (!empty($filters['player_name'])) {
        $where[] = "player_name LIKE ?";
        $params[] = "%{$filters['player_name']}%";
        $types .= 's';
    }
    
    $sql = "SELECT id, character_name, clan, generation 
            FROM characters 
            WHERE " . implode(" AND ", $where) . " 
            ORDER BY character_name";
    
    return db_fetch_all($sql, $params, $types);
}
```

### Variable Column Updates
```php
// Update only provided fields
function updateCharacter($char_id, $updates) {
    if (empty($updates)) {
        return 0;
    }
    
    $sets = [];
    $params = [];
    $types = '';
    
    $allowed_fields = ['character_name' => 's', 'clan' => 's', 'generation' => 'i'];
    
    foreach ($updates as $field => $value) {
        if (isset($allowed_fields[$field])) {
            $sets[] = "$field = ?";
            $params[] = $value;
            $types .= $allowed_fields[$field];
        }
    }
    
    if (empty($sets)) {
        return 0;
    }
    
    // Add character_id to end
    $params[] = $char_id;
    $types .= 'i';
    
    $sql = "UPDATE characters SET " . implode(", ", $sets) . " WHERE id = ?";
    
    return db_execute($sql, $params, $types);
}
```

---

## Pattern 10: Error Handling

### Basic Error Handling
```php
try {
    $char_id = db_execute(
        "INSERT INTO characters (user_id, character_name) VALUES (?, ?)",
        [$user_id, $name],
        'is'
    );
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    
    // Check for specific errors
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        throw new Exception("Character name already exists");
    }
    
    throw new Exception("Failed to create character");
}
```

### Transaction with Error Handling
```php
db_begin_transaction($conn);
try {
    // Operations that might fail
    $char_id = db_execute("INSERT ...", [...], '...');
    
    // Validate data before committing
    if ($char_id <= 0) {
        throw new Exception("Invalid character ID");
    }
    
    db_commit($conn);
    return $char_id;
    
} catch (Exception $e) {
    db_rollback($conn);
    error_log("Transaction failed: " . $e->getMessage());
    
    // Return user-friendly error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save character'
    ]);
    exit;
}
```

---

## Common Mistakes to Avoid

### ❌ Don't Mix Prepared and Direct Queries
```php
// BAD: Direct interpolation (SQL injection risk!)
$result = $conn->query("SELECT * FROM users WHERE username = '$username'");

// GOOD: Use prepared statement
$user = db_fetch_one("SELECT id, username FROM users WHERE username = ?", [$username], 's');
```

### ❌ Don't Forget Type Codes
```php
// BAD: Empty or wrong type string
db_fetch_one("SELECT * FROM characters WHERE id = ?", [$id], '');

// GOOD: Correct type code
db_fetch_one("SELECT id, name FROM characters WHERE id = ?", [$id], 'i');
```

### ❌ Don't Use SELECT * with Prepared Statements
```php
// BAD: Still wasteful even with prepared statements
db_fetch_all("SELECT * FROM characters WHERE clan = ?", [$clan], 's');

// GOOD: Explicit columns
db_fetch_all("SELECT id, character_name, generation FROM characters WHERE clan = ?", [$clan], 's');
```

### ❌ Don't Skip Transactions for Multi-Step Operations
```php
// BAD: Not atomic
$char_id = db_execute("INSERT INTO characters ...", [...], '...');
db_execute("INSERT INTO character_traits ...", [$char_id, ...], '...');
// If second query fails, character exists without traits

// GOOD: Atomic transaction
db_begin_transaction($conn);
try {
    $char_id = db_execute("INSERT INTO characters ...", [...], '...');
    db_execute("INSERT INTO character_traits ...", [$char_id, ...], '...');
    db_commit($conn);
} catch (Exception $e) {
    db_rollback($conn);
    throw $e;
}
```

---

## Testing Prepared Statements

### Test Special Characters
```php
// Ensure special characters are properly escaped
$test_names = [
    "O'Brien",              // Single quote
    'Character "Test"',     // Double quotes
    "Test\nNewline",        // Newline
    "Test\tTab",            // Tab
    "Test\\Backslash"       // Backslash
];

foreach ($test_names as $name) {
    $id = db_execute(
        "INSERT INTO characters (user_id, character_name) VALUES (?, ?)",
        [1, $name],
        'is'
    );
    
    $retrieved = db_fetch_one(
        "SELECT character_name FROM characters WHERE id = ?",
        [$id],
        'i'
    );
    
    assert($retrieved['character_name'] === $name);
}
```

### Test NULL Values
```php
// NULL handling
$char_id = db_execute(
    "INSERT INTO characters (user_id, character_name, sire) VALUES (?, ?, ?)",
    [1, 'Test Character', null],
    'iss'
);

$char = db_fetch_one("SELECT sire FROM characters WHERE id = ?", [$char_id], 'i');
assert($char['sire'] === null);
```

---

## Related Documentation
- **Database Helpers:** `docs/DATABASE_HELPERS.md`
- **Query Optimization:** `docs/QUERY_OPTIMIZATION_GUIDE.md`
- **Testing:** `docs/PHASE8_TESTING_VALIDATION.md`
- **Transactions:** `docs/PHASE7_TRANSACTION_IMPLEMENTATION.md`

---

## Summary

**Key Principles:**
1. Always use helper functions (never raw mysqli)
2. Always provide correct type codes
3. Always use explicit column lists
4. Always wrap multi-step operations in transactions
5. Always handle exceptions properly

**Helper Function Hierarchy:**
- `db_fetch_one()` - Single row expected
- `db_fetch_all()` - Multiple rows expected
- `db_execute()` - INSERT/UPDATE/DELETE
- `db_select()` - Complex result processing

**Security Benefits:**
- Zero SQL injection vulnerabilities
- Automatic parameter escaping
- Type safety
- Consistent error handling
- Transaction support

