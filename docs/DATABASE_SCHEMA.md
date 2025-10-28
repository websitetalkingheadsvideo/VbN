# VbN Database Schema Documentation

## Overview
This document provides comprehensive documentation of the VbN (Vampire by Night) database schema, optimized for MySQL best practices.

**Character Set:** utf8mb4  
**Collation:** utf8mb4_unicode_ci  
**Engine:** InnoDB

---

## Core Tables

### Users Table
**Purpose:** Stores user accounts (players and admin)

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique user identifier |
| username | VARCHAR(50) | INDEX, UNIQUE | User login name |
| email | VARCHAR(100) | INDEX, UNIQUE | User email address |
| password | VARCHAR(255) | - | Hashed password |
| role | VARCHAR(20) | INDEX | User role (user/admin) |
| email_verified | BOOLEAN | - | Email verification status |
| verification_token | VARCHAR(64) | INDEX | Email verification token |
| verification_expires | TIMESTAMP | - | Token expiration time |
| created_at | TIMESTAMP | - | Account creation timestamp |
| last_login | TIMESTAMP | INDEX | Last login timestamp |

**Indexes:**
- `idx_users_email` - Fast email lookups for login
- `idx_users_username` - Fast username lookups for login
- `idx_users_role` - Filter by user role
- `idx_users_last_login` - Sort by activity
- `idx_verification_token` - Fast token verification

---

### Characters Table
**Purpose:** Main character information for both PCs and NPCs

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique character identifier |
| user_id | INT | INDEX, FOREIGN KEY | Owner user ID |
| character_name | VARCHAR(100) | INDEX | Character's name |
| player_name | VARCHAR(100) | - | Player's name |
| chronicle | VARCHAR(100) | - | Campaign name |
| nature | VARCHAR(50) | - | Character's nature |
| demeanor | VARCHAR(50) | - | Character's demeanor |
| concept | VARCHAR(200) | - | Character concept |
| clan | VARCHAR(50) | INDEX | Vampire clan |
| generation | INT | - | Generation (distance from Caine) |
| sire | VARCHAR(100) | - | Sire's name |
| pc | BOOLEAN | INDEX | Is player character (vs NPC) |
| biography | TEXT | - | Character background |
| character_image | VARCHAR(255) | - | Profile image path |
| equipment | TEXT | - | Equipment notes (deprecated - use character_equipment) |
| notes | TEXT | - | General notes |
| total_xp | INT | - | Total XP earned |
| spent_xp | INT | - | XP already spent |
| created_at | TIMESTAMP | INDEX | Creation timestamp |
| updated_at | TIMESTAMP | - | Last update timestamp |

**Foreign Keys:**
- `user_id` → `users(id)` ON DELETE CASCADE

**Indexes:**
- `idx_characters_user` - Find user's characters
- `idx_characters_clan` - Filter/sort by clan
- `idx_characters_pc` - Separate PCs from NPCs
- `idx_characters_name` - Search by name
- `idx_characters_created` - Sort by creation date

---

### Character Traits Table
**Purpose:** Physical, Social, and Mental traits

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique trait identifier |
| character_id | INT | INDEX, FOREIGN KEY | Character ID |
| trait_name | VARCHAR(100) | INDEX | Trait name |
| trait_category | ENUM | INDEX | Physical/Social/Mental |
| trait_type | ENUM | INDEX | positive/negative |
| xp_cost | INT | - | XP spent on trait |
| created_at | TIMESTAMP | - | Creation timestamp |

**Foreign Keys:**
- `character_id` → `characters(id)` ON DELETE CASCADE

**Indexes:**
- `idx_traits_character` - Get all character traits
- `idx_traits_category` - Filter by category
- `idx_traits_type` - Filter positive/negative
- `idx_traits_name` - Search by trait name

---

### Character Abilities Table
**Purpose:** Skills and knowledge abilities

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique ability identifier |
| character_id | INT | INDEX, FOREIGN KEY | Character ID |
| ability_name | VARCHAR(100) | INDEX | Ability name |
| specialization | VARCHAR(100) | - | Specialization if any |
| level | INT | INDEX | Ability level (1-5) |
| xp_cost | INT | - | XP spent on ability |
| created_at | TIMESTAMP | - | Creation timestamp |

**Foreign Keys:**
- `character_id` → `characters(id)` ON DELETE CASCADE

**Indexes:**
- `idx_abilities_character` - Get character abilities
- `idx_abilities_name` - Search by ability
- `idx_abilities_level` - Filter by level

---

### Character Disciplines Table
**Purpose:** Vampire powers and disciplines

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique discipline identifier |
| character_id | INT | INDEX, FOREIGN KEY | Character ID |
| discipline_name | VARCHAR(100) | INDEX | Discipline name |
| level | INT | INDEX | Discipline level (1-5) |
| xp_cost | INT | - | XP spent on discipline |
| created_at | TIMESTAMP | - | Creation timestamp |

**Foreign Keys:**
- `character_id` → `characters(id)` ON DELETE CASCADE

**Indexes:**
- `idx_disciplines_character` - Get character disciplines
- `idx_disciplines_name` - Search by discipline
- `idx_disciplines_level` - Filter by level

---

### Character Backgrounds Table
**Purpose:** Resources, contacts, allies, status, etc.

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique background identifier |
| character_id | INT | INDEX, FOREIGN KEY | Character ID |
| background_name | VARCHAR(100) | INDEX | Background name |
| level | INT | - | Background level (1-5) |
| xp_cost | INT | - | XP spent |
| created_at | TIMESTAMP | - | Creation timestamp |

**Foreign Keys:**
- `character_id` → `characters(id)` ON DELETE CASCADE

**Indexes:**
- `idx_backgrounds_character` - Get character backgrounds
- `idx_backgrounds_name` - Search by background

---

### Character Merits & Flaws Table
**Purpose:** Special advantages and disadvantages

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique merit/flaw identifier |
| character_id | INT | INDEX, FOREIGN KEY | Character ID |
| name | VARCHAR(100) | - | Merit/flaw name |
| type | ENUM | INDEX | merit/flaw |
| point_value | INT | - | Point cost/bonus |
| description | TEXT | - | Description |
| xp_bonus | INT | - | XP bonus/cost |
| created_at | TIMESTAMP | - | Creation timestamp |

**Foreign Keys:**
- `character_id` → `characters(id)` ON DELETE CASCADE

**Indexes:**
- `idx_merits_character` - Get character merits/flaws
- `idx_merits_type` - Filter by type

---

### Character Morality Table
**Purpose:** Humanity, virtues, willpower tracking

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique morality record |
| character_id | INT | INDEX, FOREIGN KEY | Character ID |
| path_name | VARCHAR(50) | INDEX | Path name (Humanity, etc) |
| path_rating | INT | - | Current path rating (1-10) |
| conscience | INT | - | Conscience virtue (1-5) |
| self_control | INT | - | Self-control virtue (1-5) |
| courage | INT | - | Courage virtue (1-5) |
| willpower_permanent | INT | - | Permanent willpower (1-10) |
| willpower_current | INT | - | Current willpower |
| humanity | INT | - | Humanity rating (1-10) |
| created_at | TIMESTAMP | - | Creation timestamp |
| updated_at | TIMESTAMP | - | Last update timestamp |

**Foreign Keys:**
- `character_id` → `characters(id)` ON DELETE CASCADE

**Indexes:**
- `idx_morality_character` - Get character morality
- `idx_morality_path` - Filter by path

---

## Equipment & Items

### Items Table
**Purpose:** Master list of all available items

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique item identifier |
| name | VARCHAR(255) | INDEX | Item name |
| type | VARCHAR(100) | INDEX | Item type |
| category | VARCHAR(100) | INDEX | Item category |
| damage | VARCHAR(100) | - | Damage rating |
| range | VARCHAR(100) | - | Range if applicable |
| requirements | JSON | - | Requirements to use |
| description | TEXT | - | Item description |
| rarity | VARCHAR(50) | INDEX | Rarity level |
| price | INT | - | Cost in resources |
| image | VARCHAR(255) | - | Item image path |
| notes | TEXT | - | Additional notes |
| created_at | TIMESTAMP | - | Creation timestamp |

**Indexes:**
- `idx_type` - Filter by item type
- `idx_category` - Filter by category
- `idx_rarity` - Filter by rarity
- `idx_name` - Search by name

---

### Character Equipment Table
**Purpose:** Junction table linking characters to items

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique equipment record |
| character_id | INT | INDEX, FOREIGN KEY | Character ID |
| item_id | INT | INDEX, FOREIGN KEY | Item ID |
| quantity | INT | - | Number owned |
| equipped | TINYINT(1) | INDEX | Currently equipped flag |
| custom_notes | TEXT | - | Custom notes |
| created_at | TIMESTAMP | - | Creation timestamp |
| updated_at | TIMESTAMP | - | Last update timestamp |

**Foreign Keys:**
- `character_id` → `characters(id)` ON DELETE CASCADE
- `item_id` → `items(id)` ON DELETE CASCADE

**Indexes:**
- `idx_character` - Get character equipment
- `idx_item` - Find item owners
- `idx_equipped` - Filter equipped items

---

## Social & Political

### Boons Table
**Purpose:** Track favors and debts between vampires

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique boon identifier |
| creditor_id | INT | INDEX, FOREIGN KEY | Character owed the boon |
| debtor_id | INT | INDEX, FOREIGN KEY | Character who owes |
| boon_type | ENUM | INDEX | trivial/minor/major/life |
| description | TEXT | - | What the boon is for |
| status | ENUM | INDEX | active/fulfilled/cancelled/disputed |
| created_date | TIMESTAMP | INDEX | When boon was created |
| fulfilled_date | TIMESTAMP | - | When fulfilled |
| due_date | DATE | - | Optional due date |
| notes | TEXT | - | Additional notes |
| created_by | INT | FOREIGN KEY | Admin who created entry |
| updated_at | TIMESTAMP | - | Last update timestamp |

**Foreign Keys:**
- `creditor_id` → `characters(id)` ON DELETE CASCADE
- `debtor_id` → `characters(id)` ON DELETE CASCADE
- `created_by` → `users(id)` ON DELETE CASCADE

**Indexes:**
- `idx_creditor` - Find boons owed to character
- `idx_debtor` - Find boons character owes
- `idx_status` - Filter by status
- `idx_boon_type` - Filter by type
- `idx_created_date` - Sort by date

---

### Locations Table
**Purpose:** Track important locations in the chronicle

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique location identifier |
| name | VARCHAR(255) | - | Location name |
| type | VARCHAR(100) | INDEX | Location type |
| summary | VARCHAR(500) | - | Brief summary |
| description | TEXT | - | Full description |
| notes | TEXT | - | GM notes |
| status | VARCHAR(50) | INDEX | Active/Destroyed/etc |
| status_notes | VARCHAR(255) | - | Status details |
| district | VARCHAR(100) | INDEX | Geographic district |
| address | VARCHAR(255) | - | Street address |
| latitude | DECIMAL(10,8) | - | GPS latitude |
| longitude | DECIMAL(11,8) | - | GPS longitude |
| owner_type | VARCHAR(50) | INDEX | Ownership type |
| owner_notes | TEXT | - | Owner details |
| faction | VARCHAR(50) | INDEX | Controlling faction |
| access_control | VARCHAR(50) | - | Access restrictions |
| access_notes | TEXT | - | Access details |
| security_level | INT | - | Overall security (1-10) |
| [security_*] | TINYINT(1) | - | Various security features |
| [utility_*] | TINYINT(1) | - | Various utility features |
| [supernatural_*] | Various | - | Supernatural features |
| parent_location_id | INT | INDEX, FOREIGN KEY | Parent location if nested |
| relationship_type | VARCHAR(50) | - | Relationship to parent |
| relationship_notes | TEXT | - | Relationship details |
| image | VARCHAR(255) | - | Location image |
| created_at | TIMESTAMP | - | Creation timestamp |
| updated_at | TIMESTAMP | - | Last update timestamp |

**Foreign Keys:**
- `parent_location_id` → `locations(id)` ON DELETE SET NULL

**Indexes:**
- `idx_type` - Filter by type
- `idx_status` - Filter by status
- `idx_district` - Filter by district
- `idx_owner_type` - Filter by ownership
- `idx_faction` - Filter by faction
- `idx_parent` - Get sub-locations

---

### NPC Tracker Table
**Purpose:** Track NPC concepts and development

| Column | Type | Indexes | Description |
|--------|------|---------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Unique NPC tracker ID |
| character_name | VARCHAR(255) | INDEX | NPC name |
| clan | VARCHAR(100) | INDEX | Vampire clan |
| linked_to | VARCHAR(255) | INDEX | Linked to PC/other |
| introduced_in | VARCHAR(255) | - | Where introduced |
| status | VARCHAR(50) | INDEX | Development status |
| summary | TEXT | - | NPC summary |
| plot_hooks | TEXT | - | Plot hook ideas |
| mentioned_details | TEXT | - | Details mentioned |
| npc_briefing | TEXT | - | GM briefing |
| npc_briefing_visible | TINYINT(1) | - | Briefing visibility |
| submitted_by | INT | FOREIGN KEY | User who submitted |
| created_at | DATETIME | - | Creation timestamp |
| updated_at | DATETIME | - | Last update timestamp |

**Foreign Keys:**
- `submitted_by` → `users(id)` ON DELETE SET NULL

**Indexes:**
- `idx_npc_tracker_character` - Search by name
- `idx_npc_tracker_clan` - Filter by clan
- `idx_npc_tracker_status` - Filter by status
- `idx_npc_tracker_linked` - Find linked NPCs

---

## Performance Optimization Tips

### Query Optimization
1. **Always use indexes for WHERE clauses:**
   ```sql
   -- Good: Uses idx_characters_user
   SELECT id, name, clan FROM characters WHERE user_id = ?
   
   -- Bad: No index on player_name
   SELECT * FROM characters WHERE player_name LIKE '%Smith%'
   ```

2. **Use EXPLAIN to analyze queries:**
   ```sql
   EXPLAIN SELECT c.id, c.character_name, u.username
   FROM characters c
   JOIN users u ON c.user_id = u.id
   WHERE c.clan = 'Ventrue';
   ```

3. **Specify columns explicitly (avoid SELECT *):**
   ```sql
   -- Good
   SELECT id, name, clan, generation FROM characters WHERE id = ?
   
   -- Bad
   SELECT * FROM characters WHERE id = ?
   ```

### Common Query Patterns

**Get user's characters with traits:**
```sql
SELECT 
    c.id, c.character_name, c.clan,
    GROUP_CONCAT(DISTINCT t.trait_name) as traits
FROM characters c
LEFT JOIN character_traits t ON c.character_id = t.character_id
WHERE c.user_id = ?
GROUP BY c.id;
```

**Get character's full equipment:**
```sql
SELECT 
    i.id, i.name, i.type, i.category,
    ce.quantity, ce.equipped, ce.custom_notes
FROM character_equipment ce
JOIN items i ON ce.item_id = i.id
WHERE ce.character_id = ?
ORDER BY ce.equipped DESC, i.name;
```

**Find boons between characters:**
```sql
SELECT 
    b.id, b.boon_type, b.status, b.description,
    cr.character_name as creditor,
    db.character_name as debtor
FROM boons b
JOIN characters cr ON b.creditor_id = cr.id
JOIN characters db ON b.debtor_id = db.id
WHERE b.status = 'active'
AND (b.creditor_id = ? OR b.debtor_id = ?)
ORDER BY b.created_date DESC;
```

---

## Maintenance Scripts

### Run Schema Update
```bash
php database/update_schema_mysql_compliance.php
```

This script will:
- Convert all tables to utf8mb4_unicode_ci
- Add missing indexes
- Fix foreign key references
- Optimize table structures

### Backup Database
```bash
mysqldump -u username -p working_vbn > backup_$(date +%Y%m%d).sql
```

### Check Table Status
```sql
SHOW TABLE STATUS WHERE Engine='InnoDB';
SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA='working_vbn';
```

---

## Related Documentation
- [Database Helper Functions](DATABASE_HELPERS.md)
- [MySQL Best Practices Rule](.cursor/rules/mysql.mdc)

