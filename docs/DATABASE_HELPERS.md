# Database Helper Functions - MySQL Best Practices

## Overview
The `includes/connect.php` file now includes helper functions that enforce MySQL best practices for security, performance, and data integrity.

## Character Set Configuration
The connection automatically sets `utf8mb4` character set with `utf8mb4_unicode_ci` collation for proper Unicode support (including emojis, special characters, and international text).

```php
// Character set is automatically set on connection
mysqli_set_charset($conn, "utf8mb4");
```

---

## Transaction Helper Functions

### `db_begin_transaction($connection)`
Starts a database transaction.

**Parameters:**
- `$connection` (mysqli): Database connection

**Returns:** bool - Success status

**Example:**
```php
db_begin_transaction($conn);
```

### `db_commit($connection)`
Commits the current transaction.

**Parameters:**
- `$connection` (mysqli): Database connection

**Returns:** bool - Success status

**Example:**
```php
db_commit($conn);
```

### `db_rollback($connection)`
Rolls back the current transaction.

**Parameters:**
- `$connection` (mysqli): Database connection

**Returns:** bool - Success status

**Example:**
```php
db_rollback($conn);
```

### `db_transaction($connection, callable $callback)`
Executes a callback within a transaction with automatic rollback on error.

**Parameters:**
- `$connection` (mysqli): Database connection
- `$callback` (callable): Function containing transaction operations

**Returns:** mixed - Result from callback on success

**Throws:** Exception - On transaction failure

**Example:**
```php
try {
    $character_id = db_transaction($conn, function($conn) use ($data) {
        // Insert character
        $char_id = db_execute($conn, 
            "INSERT INTO characters (name, clan) VALUES (?, ?)",
            "ss",
            [$data['name'], $data['clan']]
        );
        
        // Insert traits
        db_execute($conn,
            "INSERT INTO character_traits (character_id, trait_name) VALUES (?, ?)",
            "is",
            [$char_id, $data['trait']]
        );
        
        return $char_id;
    });
    
    echo "Character created with ID: $character_id";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## Prepared Statement Helper Functions

### `db_select($connection, $query, $types, $params)`
Executes a prepared SELECT query and returns results.

**Parameters:**
- `$connection` (mysqli): Database connection
- `$query` (string): SQL query with ? placeholders
- `$types` (string): Parameter types (i=integer, d=double, s=string, b=blob)
- `$params` (array): Parameters to bind

**Returns:** mysqli_result|false - Query result or false on error

**Example:**
```php
$result = db_select($conn, 
    "SELECT id, name, clan FROM characters WHERE user_id = ?",
    "i",
    [$user_id]
);

while ($row = mysqli_fetch_assoc($result)) {
    echo $row['name'] . "\n";
}
mysqli_free_result($result);
```

### `db_execute($connection, $query, $types, $params)`
Executes a prepared INSERT/UPDATE/DELETE query.

**Parameters:**
- `$connection` (mysqli): Database connection
- `$query` (string): SQL query with ? placeholders
- `$types` (string): Parameter types
- `$params` (array): Parameters to bind

**Returns:** int|false - Insert ID for INSERT, affected rows for UPDATE/DELETE, false on error

**Example (INSERT):**
```php
$character_id = db_execute($conn,
    "INSERT INTO characters (name, clan, user_id) VALUES (?, ?, ?)",
    "ssi",
    [$name, $clan, $user_id]
);
```

**Example (UPDATE):**
```php
$affected = db_execute($conn,
    "UPDATE characters SET name = ?, clan = ? WHERE id = ?",
    "ssi",
    [$new_name, $new_clan, $character_id]
);
```

**Example (DELETE):**
```php
$deleted = db_execute($conn,
    "DELETE FROM characters WHERE id = ? AND user_id = ?",
    "ii",
    [$character_id, $user_id]
);
```

### `db_fetch_one($connection, $query, $types, $params)`
Executes a query and returns a single row.

**Parameters:**
- `$connection` (mysqli): Database connection
- `$query` (string): SQL query with ? placeholders
- `$types` (string): Parameter types
- `$params` (array): Parameters to bind

**Returns:** array|null - Single row as associative array or null if no results

**Example:**
```php
$character = db_fetch_one($conn,
    "SELECT id, name, clan, generation FROM characters WHERE id = ?",
    "i",
    [$character_id]
);

if ($character) {
    echo "Character: " . $character['name'];
} else {
    echo "Character not found";
}
```

### `db_fetch_all($connection, $query, $types, $params)`
Executes a query and returns all rows.

**Parameters:**
- `$connection` (mysqli): Database connection
- `$query` (string): SQL query with ? placeholders
- `$types` (string): Parameter types
- `$params` (array): Parameters to bind

**Returns:** array - Array of rows as associative arrays

**Example:**
```php
$characters = db_fetch_all($conn,
    "SELECT id, name, clan FROM characters WHERE user_id = ?",
    "i",
    [$user_id]
);

foreach ($characters as $char) {
    echo $char['name'] . " - " . $char['clan'] . "\n";
}
```

### `db_escape($connection, $value)`
Escapes user input for cases where prepared statements can't be used.

**WARNING:** Always prefer prepared statements when possible!

**Parameters:**
- `$connection` (mysqli): Database connection
- `$value` (string): Value to escape

**Returns:** string - Escaped value

**Example:**
```php
// Only use when prepared statements are not possible
$safe_value = db_escape($conn, $_POST['input']);
```

---

## Parameter Type Reference

When using prepared statements, specify the correct type for each parameter:

- `i` - integer
- `d` - double (float)
- `s` - string
- `b` - blob (binary data)

**Examples:**
```php
// Single integer
"i" with [$id]

// Multiple parameters
"ssi" with [$name, $clan, $user_id]  // string, string, integer

// Mixed types
"sdis" with [$name, $price, $quantity, $description]  // string, double, integer, string
```

---

## Migration Guide: Converting Existing Code

### Before (INSECURE):
```php
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM characters WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
```

### After (SECURE):
```php
$user_id = $_SESSION['user_id'];
$result = db_select($conn,
    "SELECT id, name, clan, generation FROM characters WHERE user_id = ?",
    "i",
    [$user_id]
);
```

### Before (INSECURE):
```php
$name = $_POST['name'];
$clan = $_POST['clan'];
$sql = "INSERT INTO characters (name, clan) VALUES ('$name', '$clan')";
mysqli_query($conn, $sql);
$character_id = mysqli_insert_id($conn);
```

### After (SECURE):
```php
$name = $_POST['name'];
$clan = $_POST['clan'];
$character_id = db_execute($conn,
    "INSERT INTO characters (name, clan) VALUES (?, ?)",
    "ss",
    [$name, $clan]
);
```

---

## Best Practices

1. **Always use prepared statements** - Prevents SQL injection attacks
2. **Specify columns explicitly** - Avoid `SELECT *`, list exact columns needed
3. **Use transactions for multi-step operations** - Ensures data consistency
4. **Use utf8mb4 charset** - Properly handles all Unicode characters
5. **Create indexes** - For columns used in WHERE, JOIN, and ORDER BY clauses
6. **Use foreign keys** - Maintains referential integrity
7. **Log errors** - Helper functions automatically log SQL errors

---

## Common Patterns

### Fetch Single User
```php
$user = db_fetch_one($conn,
    "SELECT id, username, email FROM users WHERE id = ?",
    "i",
    [$user_id]
);
```

### Fetch All Characters for User
```php
$characters = db_fetch_all($conn,
    "SELECT id, name, clan FROM characters WHERE user_id = ? ORDER BY name",
    "i",
    [$user_id]
);
```

### Create Character with Transaction
```php
try {
    $character_id = db_transaction($conn, function($conn) use ($data) {
        // Create character
        $char_id = db_execute($conn,
            "INSERT INTO characters (name, clan, user_id) VALUES (?, ?, ?)",
            "ssi",
            [$data['name'], $data['clan'], $data['user_id']]
        );
        
        // Add initial traits
        foreach ($data['traits'] as $trait) {
            db_execute($conn,
                "INSERT INTO character_traits (character_id, trait_name) VALUES (?, ?)",
                "is",
                [$char_id, $trait]
            );
        }
        
        return $char_id;
    });
    
    return ['success' => true, 'id' => $character_id];
} catch (Exception $e) {
    return ['success' => false, 'error' => $e->getMessage()];
}
```

### Update with Validation
```php
$affected = db_execute($conn,
    "UPDATE characters SET name = ?, updated_at = NOW() WHERE id = ? AND user_id = ?",
    "sii",
    [$new_name, $character_id, $user_id]
);

if ($affected > 0) {
    echo "Character updated successfully";
} else {
    echo "No changes made or character not found";
}
```

### Delete with Authorization Check
```php
$deleted = db_execute($conn,
    "DELETE FROM characters WHERE id = ? AND user_id = ?",
    "ii",
    [$character_id, $user_id]
);

if ($deleted > 0) {
    echo "Character deleted";
} else {
    echo "Character not found or unauthorized";
}
```

---

## Testing Helper Functions

Create a test file to verify the helpers work correctly:

```php
<?php
require_once 'includes/connect.php';

// Test db_fetch_all
$users = db_fetch_all($conn, "SELECT id, username FROM users LIMIT 5", "", []);
var_dump($users);

// Test db_fetch_one
$user = db_fetch_one($conn, "SELECT id, username FROM users WHERE id = ?", "i", [1]);
var_dump($user);

// Test transaction
try {
    db_transaction($conn, function($conn) {
        echo "Transaction test successful!";
    });
} catch (Exception $e) {
    echo "Transaction test failed: " . $e->getMessage();
}
?>
```

---

## Error Handling

All helper functions include error logging. Check your PHP error log for details when queries fail:

```php
// Errors are automatically logged with details
// Check: /var/log/php_errors.log or your configured error log

// Example log entry:
// [2025-01-05] Prepare failed: Unknown column 'bad_column' in 'field list' Query: SELECT bad_column FROM users
```

