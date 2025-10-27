# NPC Player Name Standardization - Complete

## Problem
NPCs had inconsistent `player_name` values across the database:
- `"NPC"`
- `"ST/NPC"`
- `"ST / NPC"` (with spaces)
- `"Player Name or ST/NPC"` (template placeholder)
- blank/empty values

This caused the NPC Briefing page to only show characters with `player_name = 'ST/NPC'`, missing most NPCs and showing duplicates.

## Solution
**Standardized on: `"NPC"`** - Simple, clean, and what the purple badge displays.

## Files Changed

### 1. `admin/admin_npc_briefing.php`
- Changed filter from `WHERE player_name = 'ST/NPC'` to `WHERE player_name = 'NPC'` (line 122)
- Updated stats query from `'ST/NPC'` to `'NPC'` (line 43)

### 2. `admin/admin_panel.php`
- Changed NPC detection from `'ST/NPC'` to `'NPC'` (line 150)
- Updated stats query to properly count NPCs (line 39)

### 3. `database/normalize_npc_player_names.php` (NEW)
Migration script to normalize all existing NPC records to `"NPC"`

## Database Migration

Run this URL to normalize all existing NPC player names:
```
http://vbn.talkingheads.video/database/normalize_npc_player_names.php
```

This will:
- Find all characters with any NPC variation
- Update them all to `"NPC"`
- Show you what was changed
- Verify the results

## Result

After running the migration:
- ✅ All NPCs will have `player_name = 'NPC'`
- ✅ NPC Briefing page will show all NPCs (not just 2)
- ✅ No more duplicate entries
- ✅ Purple badge will appear for all NPCs in admin panel
- ✅ Stats will be accurate

## Testing

1. Run the migration script
2. Visit `admin/admin_npc_briefing.php`
3. Verify all NPCs appear in the list
4. Verify purple badges show correctly in `admin_panel.php`
5. Check stats numbers are accurate

## Future Character Imports

When importing new characters via JSON, ensure `player_name` is set to `"NPC"` for NPCs.

Example JSON:
```json
{
  "character_name": "Marcus Devereaux",
  "player_name": "NPC",
  "chronicle": "Valley by Night",
  ...
}
```

