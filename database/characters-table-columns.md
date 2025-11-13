# Characters Table Columns

Authoritative column inventory for the `characters` table in the Valley by Night database. Sources cross-referenced from `database/create user table.php`, subsequent migration scripts, and `docs/DATABASE_SCHEMA.md`.

| Column | Type | Notes |
|--------|------|-------|
| `id` | `INT AUTO_INCREMENT` | Primary key. |
| `user_id` | `INT` | Foreign key to `users.id`; indexed by `idx_characters_user`. |
| `character_name` | `VARCHAR(100)` | Character display name; indexed (`idx_characters_name`). |
| `player_name` | `VARCHAR(100)` | Player attribution (NPCs usually “NPC”). |
| `chronicle` | `VARCHAR(100)` | Campaign name (defaults to “Valley by Night”). |
| `nature` | `VARCHAR(50)` | Nature archetype. |
| `demeanor` | `VARCHAR(50)` | Demeanor archetype. |
| `concept` | `VARCHAR(200)` | High-level character concept. |
| `clan` | `VARCHAR(50)` | Clan tag; indexed (`idx_characters_clan`). |
| `generation` | `INT` | Generation rating. |
| `sire` | `VARCHAR(100)` | Sire name (nullable). |
| `pc` | `BOOLEAN` (`TINYINT(1)`) | 1 = PC, 0 = NPC; indexed (`idx_characters_pc`). |
| `status` | `VARCHAR(20)` | Lifecycle status (default `active`); indexed (`idx_characters_status`). |
| `camarilla_status` | `VARCHAR(50)` | Political standing (default `Unknown`); indexed (`idx_characters_camarilla`). |
| `biography` | `TEXT` | Character background narrative. |
| `agentNotes` | `TEXT` | Storyteller/NPC briefing notes. |
| `actingNotes` | `TEXT` | Performance cues for NPC portrayals. |
| `appearance` | `TEXT` | Physical description block. |
| `notes` | `TEXT` | General-purpose notes (player/ST). |
| `character_image` | `VARCHAR(255)` | Relative path to portrait asset. |
| `equipment` | `TEXT` | Legacy equipment storage (superseded by `character_equipment`). |
| `total_xp` | `INT` | Total experience earned (defaults to 30). |
| `spent_xp` | `INT` | Experience already spent. |
| `created_at` | `TIMESTAMP` | Row creation time; indexed (`idx_characters_created`). |
| `updated_at` | `TIMESTAMP` | Auto-updated on modification. |
| `Coterie` | `JSON` | Cached coterie membership payloads. |
| `Relationships` | `JSON` | Cached relationship graph for quick lookups. |

## Related Migrations

- `database/add_character_status_fields.php` – adds `status`, `camarilla_status`, and supporting indexes.
- `database/add_character_description_fields.php` / `.sql` – introduces `appearance` and `notes`, updates `biography`.
- `database/add_npc_briefing_fields.php` – adds `agentNotes` and `actingNotes`.
- `database/add_character_image_column.php` – adds `character_image`.
- `database/migrate_coterie_relationships.php` – adds `Coterie` and `Relationships` JSON columns.

This file should be kept up to date whenever new columns are added to the `characters` table.

