# Valley by Night – Relationship, Faction, and Haven System Implementation Plan

This document describes the full implementation plan for adding a dynamic **Relationship**, **Faction**, and **Haven** system to the *Valley by Night* RPG project. It is designed as a Cursor AI task file for step-by-step development.

---

## 🧩 Overview

You are building three connected systems:

1. **NPC–Player Relationship System**
2. **Faction Rating System** (Camarilla, Anarch, Independent; Sabbat later)
3. **Dynamic Haven System** — unlockable havens based on clan, faction rating, and quest outcomes.

Code should be Bootstrap 5–friendly, database-driven (MySQL), and integrate cleanly with the existing admin panel.

---

## 1. Database Schema

### players
```sql
CREATE TABLE IF NOT EXISTS players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120),
  clan VARCHAR(60),
  current_haven_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### npc_relationships
```sql
CREATE TABLE IF NOT EXISTS npc_relationships (
  id INT AUTO_INCREMENT PRIMARY KEY,
  player_id INT NOT NULL,
  npc_id INT NOT NULL,
  score INT DEFAULT 0,
  emotion JSON NULL,
  last_event VARCHAR(255) NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY player_npc (player_id, npc_id)
);
```

### factions
```sql
CREATE TABLE IF NOT EXISTS factions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) UNIQUE,
  code VARCHAR(40) UNIQUE
);
```
Seed:
- Camarilla
- Anarch
- Independent

### player_faction_ratings
```sql
CREATE TABLE IF NOT EXISTS player_faction_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  player_id INT NOT NULL,
  faction_id INT NOT NULL,
  rating INT DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY player_faction (player_id, faction_id)
);
```

### havens
```sql
CREATE TABLE IF NOT EXISTS havens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150),
  clan VARCHAR(60) NULL,
  faction_code VARCHAR(40) NULL,
  description TEXT,
  min_faction_rating INT DEFAULT 0,
  base_background_dots INT DEFAULT 1
);
```

### player_haven_unlocks
```sql
CREATE TABLE IF NOT EXISTS player_haven_unlocks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  player_id INT NOT NULL,
  haven_id INT NOT NULL,
  unlocked_by VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY player_haven (player_id, haven_id)
);
```

---

## 2. Backend Logic (PHP)

### Utility Class: `lib/GameState.php`
```php
class GameState {
    public static function getPlayer($playerId) { ... }
    public static function getPlayerFactionRatings($playerId) { ... }
    public static function getPlayerRelationships($playerId) { ... }
    public static function getAvailableHavens($playerId) { ... }
}
```

### Relationship Update Endpoint
`api/update_relationship.php`
- Input: `player_id`, `npc_id`, `delta_score`, `emotion`
- Upsert and cap `score` between -100 and 100

### Faction Update Endpoint
`api/update_faction.php`
- Input: `player_id`, `faction_code`, `delta`
- Adjust rating between -100 and 100

### Haven Unlock Endpoint
`api/unlock_haven.php`
- Input: `player_id`, `haven_id`, `source`
- Add to unlocks, set as current if none exists

---

## 3. Admin Panel (Bootstrap)

Create page `admin/player_politics.php`:

- Player info (name, clan, current haven)
- Faction standings as progress bars
- Relationship table of NPCs
- Haven list (with “Set as current” buttons)

No scrollbars — use `.table-responsive` and Bootstrap grid.

---

## 4. Quest Hook Integration

`api/complete_quest.php`
- Input: `player_id`, `quest_code`
- Unlock havens or adjust faction based on quest outcome.

Example:
```php
if ($quest_code === "NOS_ANARCH_SHELTER") unlockHaven($playerId, "Storm Drain Collective");
```

---

## 5. Seed Data

### Havens
```sql
INSERT INTO havens (name, clan, faction_code, description, min_faction_rating, base_background_dots)
VALUES
('Storm Drain Collective', 'Nosferatu', 'anarch',
 'Hidden tunnels linked to Anarch Nosferatu under Phoenix.', 40, 3),
('Utility Hub', 'Nosferatu', 'camarilla',
 'Abandoned municipal service hub claimed by Camarilla Nosferatu.', 40, 3),
('Downtown Loft', 'Toreador', 'camarilla',
 'Renovated space above an art gallery.', 20, 2),
('Mesa Nightclub Backroom', 'Toreador', 'independent',
 'Private greenroom in Setite theater nightclub.', 30, 3);
```

### Factions
```sql
INSERT IGNORE INTO factions (name, code) VALUES
('Camarilla', 'camarilla'),
('Anarch', 'anarch'),
('Independent', 'independent');
```

---

## 6. Haven Background Rating

Use *Laws of the Night* “Haven” Background to define quality and security.
- 1 dot: unsafe or temporary
- 2 dots: modest, private space
- 3 dots: secure, locked
- 4 dots: fortified, guarded
- 5 dots: legendary, mystical protections

Store as `base_background_dots` and optional `players.haven_bonus_dots`.

---

## 7. Deliverables Checklist

| Task | File | Description |
|------|------|--------------|
| ✅ DB Setup | `schema.sql` | All new tables and seeds |
| ✅ GameState Class | `lib/GameState.php` | Fetch player data and unlock logic |
| ✅ Endpoints | `/api/*.php` | REST-style updates and unlocks |
| ✅ Admin Page | `/admin/player_politics.php` | Manage factions, relationships, and havens |
| ✅ Seed Data | SQL Inserts | Populate factions and haven examples |
| ✅ Quest Hook | `/api/complete_quest.php` | Unlock havens via story events |

---

**End of Plan**  
*Prepared for Cursor AI implementation for Valley by Night.*
