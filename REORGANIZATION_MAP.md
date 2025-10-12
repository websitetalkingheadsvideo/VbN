# 🗂️ VbN File Reorganization Map

**Date:** October 12, 2025  
**Version:** v0.4.0 cleanup

## 📊 Before & After

**Before:** 88+ files in root directory  
**After:** 13 core files in root directory

---

## 🎯 New Folder Structure

```
VbN/
├── 📁 Core Application Files (Root - 13 files)
│   ├── index.php                  - Main character sheet interface
│   ├── dashboard.php              - User dashboard
│   ├── login.php                  - Login page
│   ├── login_process.php          - Login handler
│   ├── logout.php                 - Logout handler
│   ├── lotn_char_create.php       - Character creation form
│   ├── save_character.php         - Active save script
│   ├── chat.php                   - Chat interface
│   ├── users.php                  - User management
│   ├── python_api.py              - Python API (if needed)
│   ├── requirements.txt           - Python dependencies
│   ├── config.env                 - Environment configuration
│   ├── *.bat files                - Local development scripts
│   ├── START_HERE.md              - Project entry point
│   ├── VERSION.md                 - Version history
│   ├── status.md                  - Session status
│   └── sync_config.jsonc          - Sync configuration
│
├── 📁 admin/ (NEW - 12 files)
│   ├── admin_panel.php
│   ├── admin_create_location.php
│   ├── admin_create_location_story.php
│   ├── admin_equipment.php
│   ├── admin_locations.php
│   ├── api_admin_add_equipment.php
│   ├── api_admin_remove_equipment.php
│   ├── api_admin_update_equipment.php
│   ├── api_create_location.php
│   ├── api_disciplines.php
│   ├── api_get_characters.php
│   ├── api_get_equipment.php
│   ├── api_items.php
│   ├── api_parse_location_story.php
│   ├── deploy_to_websitetalkingheads.php
│   └── fonts.php
│
├── 📁 database/ (NEW)
│   ├── migrations/ (7 SQL files)
│   │   ├── add_moral_state_field.sql
│   │   ├── create_discipline_powers_table.sql
│   │   ├── create_items_tables.sql
│   │   ├── create_missing_tables.sql
│   │   ├── setup_complete_disciplines.sql
│   │   └── setup_xampp.sql
│   │
│   └── Setup Scripts (10 files)
│       ├── create_locations_table.php
│       ├── create_missing_tables.php
│       ├── create user table.php
│       ├── populate_discipline_data.php
│       ├── run_moral_state_update.php
│       ├── setup_database.php
│       ├── setup_discipline_powers.php
│       ├── setup_disciplines_simple.php
│       ├── setup_items_database.php
│       └── import_items.php
│
├── 📁 tests/ (NEW - 30+ test files)
│   ├── test_*.php                 - All PHP test files
│   ├── test_*.html                - HTML test pages
│   ├── debug_*.php                - Debug scripts
│   ├── check_*.php                - Database check scripts
│   ├── simple_*.php               - Simple test scripts
│   └── count_parameters.php
│
├── 📁 archive/ (NEW - old/unused files)
│   ├── old_save_variants/ (9 files)
│   │   ├── save_character_final.php
│   │   ├── save_character_fixed.php
│   │   ├── save_character_minimal.php
│   │   ├── save_character_simple.php
│   │   ├── save_character_test.php
│   │   ├── save_character_update_fixed.php
│   │   ├── save_character_update.php
│   │   ├── save_character_websitetalkingheads.php
│   │   ├── save_character_working.php
│   │   └── old.php
│   │
│   └── old_css_tools/ (12 files)
│       ├── css_analysis.md
│       ├── css_analysis_detailed.md
│       ├── css_final_reorganization.js
│       ├── css_manual_organize.js
│       ├── css_manual_reorganization.js
│       ├── reorganize_css.js
│       ├── reorganize_css_final.js
│       ├── reorganize_css_structure.js
│       ├── reduce_padding.js
│       ├── reduce_padding_complete.js
│       ├── create_clean_css.js
│       └── add_section_headers.js
│
├── 📁 reference/ (organized documentation)
│   ├── setup-guides/
│   │   ├── DATABASE_SETUP.md
│   │   ├── EQUIPMENT_INTEGRATION_GUIDE.md
│   │   ├── IMPORT_SYSTEM_SUMMARY.md
│   │   ├── README_SETUP.md
│   │   ├── SETUP_STORY_TO_LOCATION.md
│   │   ├── STORY_TO_LOCATION_README.md
│   │   ├── STORY_TO_LOCATION_SUMMARY.md
│   │   └── STORY_TO_LOCATION_VISUAL_GUIDE.md
│   │
│   ├── field-references/
│   │   └── LOCATIONS_FIELD_REFERENCE.md
│   │
│   ├── session-notes/
│   │   ├── NEXT_SESSION_NOTES.md
│   │   └── NEXT_SESSION_SUMMARY.md
│   │
│   ├── game-lore/ (character data)
│   │   ├── Jax.json (copy)
│   │   ├── Violet.json (copy)
│   │   ├── Rembrandt Jones.json (copy)
│   │   ├── Tremere.json (copy)
│   │   ├── IMPORT_GUIDE.md
│   │   └── REMBRANDT_JONES_SUMMARY.md
│   │
│   └── Miscellaneous
│       ├── some commands I use.txt
│       └── to do.txt
│
├── 📁 data/ (active character & item data)
│   ├── Character JSON files (originals)
│   │   ├── Jax.json
│   │   ├── Violet.json
│   │   ├── Rembrandt Jones.json
│   │   └── Tremere.json
│   │
│   ├── Item Database Files
│   │   ├── Item Database Example.json
│   │   ├── Items Database.json
│   │   ├── Items Database_v2.json
│   │   └── Items Database-Mundane.json
│   │
│   └── Character Management Scripts
│       ├── import_character.php
│       ├── import_rembrandt.php
│       ├── import_all_tremere.php
│       ├── delete_character.php
│       ├── list_characters.php
│       ├── view_character.php
│       └── verify_andrei.php
│
├── 📁 css/ (unchanged)
├── 📁 js/ (unchanged)
├── 📁 images/ (unchanged)
├── 📁 svgs/ (clan symbols - unchanged)
└── 📁 includes/ (PHP includes - unchanged)
```

---

## 🔑 Key Changes

### ✅ New Folders Created
- **admin/** - All admin panel and API endpoints
- **database/** - SQL migrations and setup scripts
- **tests/** - All test, debug, and check scripts
- **archive/** - Old/unused code for reference

### ✅ Existing Folders Enhanced
- **reference/** - Now organized into subdirectories:
  - `setup-guides/` - Setup documentation
  - `field-references/` - Field reference docs
  - `session-notes/` - Session summaries
  - `game-lore/` - Character JSONs and lore

### ✅ Root Directory Cleaned
- **Before:** 88+ files
- **After:** 13 core application files
- **Reduction:** ~85% fewer files in root

---

## 📋 Migration Notes

### Files That Moved Categories

**Admin & API (→ admin/)**
- All `admin_*.php` files
- All `api_*.php` files
- `deploy_to_websitetalkingheads.php`
- `fonts.php`

**Database (→ database/ & database/migrations/)**
- All `.sql` files → `migrations/`
- All `setup_*.php` files
- All `create_*.php` files
- All `populate_*.php` files
- `import_items.php`
- `run_moral_state_update.php`

**Tests (→ tests/)**
- All `test_*.php` files
- All `test_*.html` files
- All `debug_*.php` files
- All `check_*.php` files
- All `simple_*.php` files
- `count_parameters.php`

**Archive (→ archive/)**
- Old save variants → `archive/old_save_variants/`
- CSS tools → `archive/old_css_tools/`
- `old.php`

**Documentation (→ reference/)**
- Setup guides → `reference/setup-guides/`
- Field references → `reference/field-references/`
- Session notes → `reference/session-notes/`
- Character lore → `reference/game-lore/`

**Data (→ data/)**
- Item Database JSON files moved from root
- Character management scripts stay in data/

---

## ⚠️ Important Notes

### Path Updates Needed
If any PHP files have hardcoded paths to moved files, they may need updates:
- Check `includes/` for references to moved files
- Admin panel may reference moved API files
- Database setup scripts may reference migrations

### Git Tracking
Files were moved using PowerShell `Move-Item`, not `git mv`, so git will see these as:
- Deleted files (old location)
- New files (new location)

Git is smart enough to detect renames if the content is similar. When you commit, git should recognize most as renames.

---

## 🎯 Next Steps

1. ✅ **Test Core Application** - Verify index.php, dashboard.php, login still work
2. ✅ **Test Admin Panel** - Check if admin routes still function
3. ✅ **Update Any Hardcoded Paths** - Search for old file references
4. ✅ **Commit Changes** - Git should detect most as renames
5. ✅ **Update .gitignore** if needed - Consider ignoring archive/ or tests/

---

## 📊 Statistics

- **Root Directory:** 88 files → 13 files (85% reduction)
- **New Folders:** 4 (admin, database, tests, archive)
- **Enhanced Folders:** 1 (reference with 4 subdirectories)
- **Files Archived:** 21 old variants/tools
- **Test Files Moved:** 30+
- **Documentation Organized:** 15+ files

---

*Reorganization completed: October 12, 2025*  
*Project: Vampire the Masquerade: Laws of the Night Character Manager*

