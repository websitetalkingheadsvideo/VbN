# Query Optimization Guide

## Overview
This guide provides best practices and patterns for writing optimized database queries in the VbN project.

## Date Created
**Date:** January 2025

---

## Core Principles

### 1. Always Use Explicit Column Lists
**❌ Bad:**
```php
$result = $conn->query("SELECT * FROM characters");
```

**✅ Good:**
```php
$result = db_fetch_all(
    "SELECT id, character_name, clan, generation FROM characters",
    [],
    ''
);
```

**Why:**
- Reduces network traffic
- Prevents breaking changes when schema evolves
- Improves query cache efficiency
- Makes code more maintainable

---

### 2. Use Prepared Statements for All Dynamic Queries
**❌ Bad (SQL Injection Risk):**
```php
$username = $_POST['username'];
$query = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($query);
```

**✅ Good:**
```php
$username = $_POST['username'];
$user = db_fetch_one(
    "SELECT id, username, email, role FROM users WHERE username = ?",
    [$username],
    's'
);
```

**Why:**
- Prevents SQL injection attacks
- Better performance (query plan reuse)
- Automatic type handling and escaping

---

### 3. Use Appropriate JOIN Types

**INNER JOIN** - When relationship MUST exist:
```php
// Get equipment with item details (equipment MUST have item)
$equipment = db_fetch_all(
    "SELECT ce.id, ce.quantity, i.name, i.type 
     FROM character_equipment ce 
     INNER JOIN items i ON ce.item_id = i.id 
     WHERE ce.character_id = ?",
    [$character_id],
    'i'
);
```

**LEFT JOIN** - When relationship MAY exist:
```php
// Get characters with optional user info (some characters may be NPCs)
$characters = db_fetch_all(
    "SELECT c.id, c.character_name, u.username 
     FROM characters c 
     LEFT JOIN users u ON c.user_id = u.id",
    [],
    ''
);
```

**Why:**
- INNER JOIN is faster (fewer rows to process)
- LEFT JOIN prevents losing parent records
- Makes data relationships explicit

---

### 4. Add Indexes for Common Query Patterns

**Single Column Index:**
```sql
CREATE INDEX idx_username ON users(username);
```
**Use Case:** `WHERE username = ?`

**Compound Index:**
```sql
CREATE INDEX idx_char_traits ON character_traits(character_id, trait_category);
```
**Use Case:** `WHERE character_id = ? AND trait_category = ?`

**Covering Index:**
```sql
CREATE INDEX idx_char_name_clan ON characters(character_name, clan);
```
**Use Case:** `SELECT character_name, clan WHERE character_name = ?`

**Index Order Matters:**
```sql
-- Good: Most selective column first
CREATE INDEX idx_location ON locations(status, type, district);

-- WHERE status = 'Active' AND type = 'Haven' -- ✅ Uses index
-- WHERE type = 'Haven' -- ❌ May not use index efficiently
```

---

### 5. Use LIMIT for Large Result Sets

**❌ Bad:**
```php
$all_chars = db_fetch_all("SELECT id, character_name FROM characters", [], '');
```

**✅ Good:**
```php
$recent_chars = db_fetch_all(
    "SELECT id, character_name, created_at 
     FROM characters 
     ORDER BY created_at DESC 
     LIMIT 50",
    [],
    ''
);
```

**Why:**
- Prevents memory exhaustion
- Faster response times
- Better user experience (pagination)

---

### 6. Optimize Aggregation Queries

**❌ Slow:**
```php
// Counting traits separately for each category
$physical = db_fetch_one("SELECT COUNT(*) as cnt FROM character_traits WHERE character_id = ? AND trait_category = 'Physical'", [$id], 'i');
$social = db_fetch_one("SELECT COUNT(*) as cnt FROM character_traits WHERE character_id = ? AND trait_category = 'Social'", [$id], 'i');
$mental = db_fetch_one("SELECT COUNT(*) as cnt FROM character_traits WHERE character_id = ? AND trait_category = 'Mental'", [$id], 'i');
```

**✅ Fast:**
```php
// Single query with GROUP BY
$counts = db_fetch_all(
    "SELECT trait_category, COUNT(*) as cnt 
     FROM character_traits 
     WHERE character_id = ? 
     GROUP BY trait_category",
    [$character_id],
    'i'
);
```

**Why:**
- Single database round-trip
- Better query plan optimization
- Reduced connection overhead

---

### 7. Use Transactions for Multi-Step Operations

**❌ Bad (Not Atomic):**
```php
db_execute("INSERT INTO characters (...) VALUES (...)", [...], '...');
$char_id = $conn->insert_id;
db_execute("INSERT INTO character_traits (...) VALUES (...)", [$char_id, ...], '...');
db_execute("INSERT INTO character_abilities (...) VALUES (...)", [$char_id, ...], '...');
```

**✅ Good (Atomic):**
```php
db_begin_transaction($conn);
try {
    $char_id = db_execute("INSERT INTO characters (...) VALUES (...)", [...], '...');
    db_execute("INSERT INTO character_traits (...) VALUES (...)", [$char_id, ...], '...');
    db_execute("INSERT INTO character_abilities (...) VALUES (...)", [$char_id, ...], '...');
    db_commit($conn);
} catch (Exception $e) {
    db_rollback($conn);
    throw $e;
}
```

**Why:**
- All-or-nothing guarantee
- Data integrity preserved
- No orphaned records

---

## Common Query Patterns

### 1. Character Lookup
```php
// Single character with explicit columns
$character = db_fetch_one(
    "SELECT id, character_name, player_name, clan, generation, sire, 
            biography, experience_total, experience_unspent 
     FROM characters 
     WHERE id = ?",
    [$character_id],
    'i'
);
```

### 2. Character List with Pagination
```php
$offset = ($page - 1) * $per_page;
$characters = db_fetch_all(
    "SELECT c.id, c.character_name, c.clan, c.generation, u.username 
     FROM characters c 
     LEFT JOIN users u ON c.user_id = u.id 
     ORDER BY c.character_name 
     LIMIT ? OFFSET ?",
    [$per_page, $offset],
    'ii'
);
```

### 3. Character Traits by Category
```php
$traits = db_fetch_all(
    "SELECT id, trait_name, trait_category, trait_type 
     FROM character_traits 
     WHERE character_id = ? AND trait_category = ? 
     ORDER BY trait_name",
    [$character_id, $category],
    'is'
);
```

### 4. Character Abilities with Specializations
```php
$abilities = db_fetch_all(
    "SELECT a.id, a.ability_name, a.level, 
            GROUP_CONCAT(s.specialization_name) as specializations 
     FROM character_abilities a 
     LEFT JOIN character_ability_specializations s ON a.id = s.ability_id 
     WHERE a.character_id = ? 
     GROUP BY a.id, a.ability_name, a.level 
     ORDER BY a.ability_name",
    [$character_id],
    'i'
);
```

### 5. Equipment with Item Details
```php
$equipment = db_fetch_all(
    "SELECT ce.id, ce.quantity, ce.equipped, 
            i.name, i.type, i.category, i.damage, i.range 
     FROM character_equipment ce 
     INNER JOIN items i ON ce.item_id = i.id 
     WHERE ce.character_id = ? 
     ORDER BY i.category, i.name",
    [$character_id],
    'i'
);
```

### 6. Location Search
```php
$locations = db_fetch_all(
    "SELECT id, name, type, district, status, security_level 
     FROM locations 
     WHERE status = ? AND type = ? 
     ORDER BY name 
     LIMIT ?",
    ['Active', $location_type, 50],
    'ssi'
);
```

### 7. User Authentication
```php
$user = db_fetch_one(
    "SELECT id, username, email, password_hash, role, email_verified, last_login 
     FROM users 
     WHERE username = ? AND email_verified = 1",
    [$username],
    's'
);
```

### 8. Character Creation with Related Data
```php
db_begin_transaction($conn);
try {
    // Insert character
    $char_id = db_execute(
        "INSERT INTO characters (user_id, character_name, player_name, chronicle, clan) 
         VALUES (?, ?, ?, ?, ?)",
        [$user_id, $name, $player, $chronicle, $clan],
        'issss'
    );
    
    // Insert traits
    foreach ($traits as $trait) {
        db_execute(
            "INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type) 
             VALUES (?, ?, ?, ?)",
            [$char_id, $trait['name'], $trait['category'], $trait['type']],
            'isss'
        );
    }
    
    // Insert abilities
    foreach ($abilities as $ability) {
        db_execute(
            "INSERT INTO character_abilities (character_id, ability_name, level) 
             VALUES (?, ?, ?)",
            [$char_id, $ability['name'], $ability['level']],
            'isi'
        );
    }
    
    db_commit($conn);
    return $char_id;
} catch (Exception $e) {
    db_rollback($conn);
    throw $e;
}
```

---

## Performance Optimization Checklist

### Before Writing a Query
- [ ] Identify which columns are actually needed
- [ ] Check if indexes exist on WHERE/JOIN columns
- [ ] Determine if INNER or LEFT JOIN is appropriate
- [ ] Consider if LIMIT is needed
- [ ] Plan for prepared statement parameters

### After Writing a Query
- [ ] Run EXPLAIN to check query plan
- [ ] Verify index usage (`key` column in EXPLAIN)
- [ ] Check rows examined vs rows returned
- [ ] Test with realistic data volume
- [ ] Ensure prepared statement is used

### Common Red Flags
- [ ] `type = ALL` in EXPLAIN (full table scan)
- [ ] `key = NULL` in EXPLAIN (no index used)
- [ ] `Extra: Using filesort` (missing ORDER BY index)
- [ ] `Extra: Using temporary` (complex GROUP BY)
- [ ] Rows examined >>  rows returned (inefficient)

---

## Tools for Query Analysis

### 1. EXPLAIN Command
```sql
EXPLAIN SELECT id, character_name FROM characters WHERE clan = 'Tremere';
```

**Key Columns:**
- `type`: Join type (const > eq_ref > ref > range > index > ALL)
- `possible_keys`: Indexes that could be used
- `key`: Index actually used
- `rows`: Estimated rows examined
- `Extra`: Additional information (filesort, temporary, etc.)

### 2. Performance Analyzer
```bash
php tests/query_performance_analyzer.php
```

Analyzes common queries and provides optimization recommendations.

### 3. Slow Query Log
```sql
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
```

Logs queries taking longer than 1 second.

---

## Index Strategy

### Characters Table
```sql
PRIMARY KEY (id)
INDEX idx_user_id (user_id)
INDEX idx_character_name (character_name)
INDEX idx_clan (clan)
INDEX idx_created_at (created_at)
FOREIGN KEY (user_id) REFERENCES users(id)
```

### Character Traits Table
```sql
PRIMARY KEY (id)
INDEX idx_character (character_id)
INDEX idx_category (trait_category)
INDEX idx_type (trait_type)
INDEX idx_char_category (character_id, trait_category)
FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
```

### Character Equipment Table
```sql
PRIMARY KEY (id)
INDEX idx_character (character_id)
INDEX idx_item (item_id)
INDEX idx_char_item (character_id, item_id)
FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
```

### Users Table
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_username (username)
UNIQUE INDEX idx_email (email)
INDEX idx_email_verified (email_verified)
INDEX idx_role (role)
```

---

## Anti-Patterns to Avoid

### 1. SELECT * in Production Code
```php
// ❌ Never do this
$result = $conn->query("SELECT * FROM characters");
```

### 2. String Concatenation in Queries
```php
// ❌ SQL Injection risk
$query = "SELECT * FROM users WHERE username = '{$_POST['username']}'";
```

### 3. N+1 Query Problem
```php
// ❌ Fetches character, then traits in loop
$characters = db_fetch_all("SELECT id, character_name FROM characters", [], '');
foreach ($characters as $char) {
    $traits = db_fetch_all("SELECT * FROM character_traits WHERE character_id = ?", [$char['id']], 'i');
}

// ✅ Single query with JOIN
$data = db_fetch_all(
    "SELECT c.id, c.character_name, t.trait_name, t.trait_category 
     FROM characters c 
     LEFT JOIN character_traits t ON c.id = t.character_id 
     ORDER BY c.id, t.trait_name",
    [],
    ''
);
```

### 4. Not Using Transactions for Multi-Step Operations
```php
// ❌ Can result in partial updates
db_execute("INSERT INTO characters ...", [...], '...');
db_execute("INSERT INTO character_traits ...", [...], '...');
// If second query fails, character exists without traits
```

### 5. Using OR in WHERE with Different Columns
```php
// ❌ Prevents index usage
$query = "SELECT id, name FROM locations WHERE district = ? OR type = ?";

// ✅ Use UNION if both conditions need different indexes
$query = "
    (SELECT id, name FROM locations WHERE district = ?)
    UNION
    (SELECT id, name FROM locations WHERE type = ?)
";
```

---

## Related Documentation
- **Database Helpers:** `docs/DATABASE_HELPERS.md`
- **Schema Documentation:** `docs/DATABASE_SCHEMA.md`
- **Testing Guide:** `docs/PHASE8_TESTING_VALIDATION.md`
- **Transaction Implementation:** `docs/PHASE7_TRANSACTION_IMPLEMENTATION.md`

---

## Summary

**Key Takeaways:**
1. Always use explicit column lists
2. Always use prepared statements
3. Choose appropriate JOIN types
4. Add indexes for common queries
5. Use LIMIT for large result sets
6. Use transactions for atomic operations
7. Analyze queries with EXPLAIN
8. Avoid anti-patterns

**Performance Goals:**
- Simple lookups: <10ms
- Complex queries: <100ms
- Use indexes for 100% of production queries
- Zero SQL injection vulnerabilities
- All multi-step operations atomic

