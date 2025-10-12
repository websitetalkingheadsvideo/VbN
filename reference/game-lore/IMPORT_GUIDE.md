# Character Import Guide

## Quick Start

**To import any character JSON file:**

1. **Upload JSON file** to `data/` folder (e.g., `Character Name.json`)
2. **Run import script** via web browser:
   ```
   https://www.websitetalkingheads.com/vbn/data/import_character.php?file=Character%20Name.json
   ```
3. **Check results** - Script will show success or detailed error

## JSON File Format

Characters must be in JSON format matching `character-example.json`:

### Required Fields:
- `character_name` - Character's full name
- `player_name` - Player or "ST/NPC"
- `chronicle` - Campaign name
- `nature`, `demeanor` - Personality archetypes
- `concept` - One-line character concept
- `clan` - Vampire clan
- `generation` - Generation number (1-13+)
- `sire` - Who embraced them
- `pc` - 0 for NPC, 1 for PC
- `biography` - Full backstory
- `equipment` - Items and gear

### Character Stats:
- `traits` - Object with Physical/Social/Mental arrays
- `negativeTraits` - Object with Physical/Social/Mental arrays
- `abilities` - Array of {name, category, level}
- `specializations` - Object mapping ability names to specializations
- `disciplines` - Array of {name, level, powers: [{level, power}]}
- `backgrounds` - Object with name: level pairs
- `backgroundDetails` - Object with name: description pairs
- `morality` - Object with path_name, path_rating, virtues, willpower
- `merits_flaws` - Array of {name, type, category, cost, description}
- `status` - Object with xp_total, xp_available, blood_pool, notes

## Database Mapping

### Column Name Mappings:
- **characters table:**
  - JSON `xp_total` → DB `experience_total`
  - JSON `xp_available` → DB `experience_unspent`
  - JSON `blood_pool` → DB `blood_pool_current`

- **character_abilities table:**
  - No `ability_category` column (not stored)
  - `specialization` in same table (not separate)

- **character_disciplines table:**
  - Each **power is a separate row** (not child table)
  - `level` is ENUM: 'Basic', 'Intermediate', 'Advanced'
  - Power levels 1-2 = Basic, 3 = Intermediate, 4-5 = Advanced

- **character_backgrounds table:**
  - Column is `level` not `background_level`
  - Column is `description` not `background_details`

- **character_merits_flaws table:**
  - Columns: `name`, `type`, `category`, `point_value`, `point_cost`, `description`
  - `type` ENUM: 'Merit' or 'Flaw' (capitalized)

## Examples

### Import Rembrandt Jones:
```
https://www.websitetalkingheads.com/vbn/data/import_character.php?file=Rembrandt%20Jones.json
```

### Import Another Character:
1. Create `data/Alice Tremere.json` with character data
2. Visit: `https://www.websitetalkingheads.com/vbn/data/import_character.php?file=Alice%20Tremere.json`
3. Check output for success or errors

## Troubleshooting

### Common Issues:

1. **"No file specified"**
   - Add `?file=Filename.json` to URL

2. **"JSON file not found"**
   - Ensure file is uploaded to `data/` folder
   - Check filename spelling (case-sensitive)
   - URL-encode spaces as `%20`

3. **"Failed to parse JSON"**
   - Validate JSON at jsonlint.com
   - Check for missing commas or quotes
   - Ensure all required fields present

4. **Foreign key constraint error**
   - Character requires valid user_id (currently hardcoded to 1)
   - Check user ID 1 exists: `data/check_users.php`

## Files

- **`import_character.php`** - Generic import script (use this)
- **`import_rembrandt.php`** - Specific example for Rembrandt Jones
- **`character-example.json`** - Template/example format
- **`Rembrandt Jones.json`** - Complete example character
- **`list_characters.php`** - View all characters
- **`view_character.php`** - View individual character sheet
- **`delete_character.php`** - Delete character with confirmation

## Character Management

### View Characters
- **[Character List](https://www.websitetalkingheads.com/vbn/data/list_characters.php)** - See all characters

### Delete Characters
- Delete from character list (🗑️ Delete button)
- Delete from character sheet (top-right button)
- Two-step confirmation prevents accidents
- See `DELETE_GUIDE.md` for details

## Notes

- All imports use **user_id = 1** (admin/ST account)
- Imports are **transactional** - rolls back on error
- Character IDs auto-increment
- Deleting characters is permanent (no undo!)
- Database connection info in `includes/connect.php`
- Remote database at `vdb5.pit.pair.com`

