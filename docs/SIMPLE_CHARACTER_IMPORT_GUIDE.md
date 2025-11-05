# Simple Character Import System

## Overview

This system imports character JSON files that use a **simple discipline format** where disciplines are stored as key-value pairs with numeric levels.

## Character JSON Format

### Simple Format (Pistol Pete, Sasha, Leo)

```json
{
  "name": "Character Name",
  "clan": "Clan Name",
  "generation": 11,
  "concept": "Character concept",
  "nature": "Nature",
  "demeanor": "Demeanor",
  "affiliation": "Affiliation",
  "embrace_info": "Background info",
  "haven": "Haven description",
  "goal": "Character goal",
  "traits": {
    "willpower": 6,
    "blood_pool": 12,
    "health_levels": 7,
    "morality": "Humanity",
    "abilities": {
      "physical": 4,
      "social": 2,
      "mental": 2
    }
  },
  "disciplines": {
    "potence": 3,
    "celerity": 2,
    "presence": 1
  },
  "merits_flaws": [
    {
      "name": "Merit/Flaw Name",
      "type": "merit",
      "cost": 2,
      "description": "Description"
    }
  ]
}
```

### Key Differences from Detailed Format (Rembrandt Jones)

| Feature | Simple Format | Detailed Format |
|---------|--------------|-----------------|
| **Disciplines** | `{"potence": 3}` | `[{"name": "Potence", "level": 3, "powers": [...]}]` |
| **Abilities** | Not imported | Fully imported with specializations |
| **Backgrounds** | Not imported | Fully imported with descriptions |
| **Traits** | Not imported | Fully imported by category |
| **Character Name** | `"name"` | `"character_name"` |
| **Player** | Always "ST/NPC" | `"player_name"` |

## Import Files

### Individual Import
- **File**: `data/import_simple_character.php`
- **Usage**: `https://vbn.talkingheads.video/data/import_simple_character.php?file=Pistol%20Pete.json`
- **CLI**: `php data/import_simple_character.php "Pistol Pete.json"`

### Batch Import
- **File**: `data/batch_import_simple_characters.php`
- **Usage**: `https://vbn.talkingheads.video/data/batch_import_simple_characters.php`
- **CLI**: `php data/batch_import_simple_characters.php`
- **Imports**: Pistol Pete, Sasha, Leo

### UI Interface
- **File**: `data/import_simple_characters_ui.php`
- **Usage**: `https://vbn.talkingheads.video/data/import_simple_characters_ui.php`
- **Features**: Links to individual imports and batch import

## What Gets Imported

### ✅ Always Imported
1. **Character Record**
   - Name, clan, generation, concept
   - Nature, demeanor, affiliation
   - Embrace info, haven, goal (in notes)

2. **Disciplines**
   - All disciplines with levels 1-5
   - Names are auto-capitalized (potence → Potence)

3. **Merits & Flaws**
   - All merits and flaws with cost and description

4. **Basic Stats**
   - Blood pool, willpower
   - Health levels

### ❌ Not Imported
1. **Abilities** - Not in simple format JSON
2. **Backgrounds** - Not in simple format JSON
3. **Traits** - Not in simple format JSON
4. **Specializations** - Not in simple format JSON
5. **Rituals** - Not in simple format JSON

## Discipline Handling

### Automatic Capitalization
The import script automatically capitalizes discipline names for database consistency:
- `potence` → `Potence`
- `auspex` → `Auspex`
- `celerity` → `Celerity`

### Validation
- Level must be 1-5
- Duplicate disciplines are handled with `ON DUPLICATE KEY UPDATE`
- Each character can only have one entry per discipline

### Database Storage
Disciplines are stored in `character_disciplines` table:
```sql
- character_id INT
- discipline_name VARCHAR(100)
- level INT (1-5)
- xp_cost INT (default 0)
- UNIQUE KEY (character_id, discipline_name)
```

## Example Characters

### Pistol Pete (Brujah)
```json
{
  "disciplines": {
    "potence": 3,
    "celerity": 2,
    "presence": 1
  }
}
```

### Sasha (Malkavian)
```json
{
  "disciplines": {
    "auspex": 3,
    "dementation": 3,
    "dominate": 1
  }
}
```

### Leo (Nosferatu)
```json
{
  "disciplines": {
    "obfuscate": 3,
    "potence": 1,
    "animalism": 1
  }
}
```

## File Location

Character JSON files must be in:
```
reference/Characters/Added to Database/
```

## Transaction Safety

All imports use database transactions:
- ✅ Success: Commits all data
- ❌ Failure: Rolls back everything
- 🔒 Safe: No partial imports

## Error Handling

The import will fail and rollback if:
- JSON file is missing or malformed
- Database connection fails
- Required fields are missing
- SQL errors occur

## After Import

Characters are assigned:
- **user_id**: 1 (ST/Admin)
- **pc**: 0 (NPC)
- **chronicle**: "Valley by Night"

You can view imported characters:
- Admin Panel: `https://vbn.talkingheads.video/admin/admin_panel.php`
- Character API: `https://vbn.talkingheads.video/admin/api_get_characters.php`

## Next Steps

After importing simple characters, you may want to:
1. Add abilities manually via admin panel
2. Add backgrounds if needed
3. Update character images
4. Add to coterie/relationships
5. Create sire-child relationships

## Troubleshooting

### Character Imported but No Disciplines
- Check discipline names match database (capitalized)
- Verify discipline names exist in `disciplines` table
- Check `character_disciplines` table for errors

### Duplicate Character
- Delete existing character first
- Or update via `ON DUPLICATE KEY UPDATE`

### Discipline Level Wrong
- Verify JSON discipline value is 1-5
- Check database for MAX(level) if duplicates exist

## Related Files

- `data/import_character_fixed.php` - Detailed format import (Rembrandt Jones)
- `data/import_rembrandt.php` - Rembrandt Jones specific import
- `database/migrate_discipline_schema.php` - Schema updates
- `admin/api_disciplines.php` - Discipline API

## Support

For issues or questions:
1. Check error messages in import output
2. Review database connection in `includes/connect.php`
3. Verify JSON file is valid
4. Check discipline names against `disciplines` table

