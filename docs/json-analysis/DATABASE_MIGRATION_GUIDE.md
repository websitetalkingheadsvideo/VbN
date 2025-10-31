# Database Migration Guide - Coterie & Relationships

## Overview

This guide explains how to add Coterie and Relationships data to your database after running the JSON analysis.

## Prerequisites

- ✅ JSON analysis completed (`node scripts/json-analysis/extract-all.js`)
- ✅ Extracted data file exists: `docs/json-analysis/extracted-data.json`
- ✅ Database access (phpMyAdmin, MySQL CLI, or web interface)

## Step 1: Run Database Migration

### Option A: Via SQL File (Recommended)

1. Open `database/migrate_coterie_relationships.sql` in a text editor
2. Copy the SQL commands
3. Run them in phpMyAdmin or your database management tool
4. This creates the `character_coteries` and `character_relationships` tables

### Option B: Via PHP Script

If you have command-line PHP access:

```bash
php database/migrate_coterie_relationships.php
```

Or via web browser (if hosted):
```
https://vbn.talkingheads.video/database/migrate_coterie_relationships.php
```

## Step 2: Populate Data

### Option A: Via PHP Script (Recommended)

If you have command-line PHP access:

```bash
php database/populate_coterie_relationships.php
```

Or via web browser (if hosted):
```
https://vbn.talkingheads.video/database/populate_coterie_relationships.php
```

### Option B: Manual Import via SQL

If the PHP script doesn't work, you can manually import the data:

1. Open `docs/json-analysis/extracted-data.json`
2. For each character, create INSERT statements based on the extracted data
3. Match character names to existing `character_id` values in your database

Example SQL:

```sql
-- Example: Insert coterie for Bayside Bob (assuming character_id = 1)
INSERT INTO character_coteries (character_id, coterie_name, coterie_type, role, description, source_field)
VALUES (1, 'Anarch faction', 'faction', 'member', 'De facto gathering place for Anarchs', 'biography');

-- Example: Insert relationship for Sebastian (assuming character_id = 2)
INSERT INTO character_relationships (character_id, related_character_id, related_character_name, relationship_type, relationship_subtype, strength, description, source_field)
VALUES (2, 3, 'Sabine', 'twin', 'sister', 'inseparable', 'Twin sister Sabine (inseparable)', 'backgroundDetails.Allies');
```

## Verification

After migration and population, verify the data:

```sql
-- Check coteries
SELECT COUNT(*) AS total_coteries FROM character_coteries;
SELECT * FROM character_coteries LIMIT 5;

-- Check relationships
SELECT COUNT(*) AS total_relationships FROM character_relationships;
SELECT * FROM character_relationships LIMIT 5;

-- Check by character
SELECT c.character_name, 
       COUNT(DISTINCT cc.id) AS coterie_count,
       COUNT(DISTINCT cr.id) AS relationship_count
FROM characters c
LEFT JOIN character_coteries cc ON c.id = cc.character_id
LEFT JOIN character_relationships cr ON c.id = cr.character_id
GROUP BY c.id, c.character_name
ORDER BY coterie_count DESC, relationship_count DESC;
```

## Troubleshooting

### Connection Issues

If you get "Access denied" errors:
- Verify database credentials in `includes/connect.php`
- Check if your IP is whitelisted for the remote database
- Consider running scripts via web interface instead of CLI

### Character Name Matching

If relationships aren't linking to existing characters:
- The script uses fuzzy matching, but some names may not match
- Check `extracted-data.json` for character names that need manual linking
- Update `related_character_id` manually in the database if needed

### Duplicate Data

If you run the population script multiple times:
- The script doesn't check for duplicates before inserting
- Manually delete duplicates or add `ON DUPLICATE KEY UPDATE` logic

## Expected Results

After successful migration:
- ✅ `character_coteries` table created with ~18 rows
- ✅ `character_relationships` table created with ~74 rows
- ✅ Relationships linked to existing characters where name matches

## Next Steps

Once data is populated:
1. Test queries to retrieve coteries and relationships
2. Build UI components to display this data in character sheets
3. Add search/filter functionality for relationships
4. Implement reciprocal relationship detection (if A is sire of B, show B as childe of A)

