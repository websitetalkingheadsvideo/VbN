# 🧛 Character Import Process Documentation

## 📋 Overview
This document outlines the process for importing character JSON files into the VbN database system.

## 🔄 Workflow Process

### 1. **Create Character JSON Files**
- Place new character JSON files in: `reference/Characters/`
- Use the full character sheet format (like Bayside Bob.json)
- Ensure JSON is valid (no markdown syntax like ```)

### 2. **Import Characters**
- Use the import script: `http://vbn.talkingheads.video/data/import_character.php?file=CharacterName.json`
- URL encode spaces: `Bayside%20Bob.json`
- Check the output for successful import

### 3. **Archive Processed Files**
- **After successful import**, move the JSON file to: `reference/Characters/Added to Database/`
- This folder serves as an archive of characters already in the database
- **DO NOT** import from the "Added to Database" folder

### 4. **Verify Import**
- Check the admin panel: `http://vbn.talkingheads.video/admin/admin_panel.php`
- Verify character appears in the database
- Note the Character ID for reference

## 📁 Folder Structure

```
reference/Characters/
├── CharacterName.json          ← NEW characters to import
├── AnotherCharacter.json       ← NEW characters to import
├── Added to Database/          ← ARCHIVE of imported characters
│   ├── Bayside Bob.json        ← Already imported
│   ├── Leo.json                ← Already imported
│   └── ...
└── CHARACTER_IMPORT_PROCESS.md ← This documentation
```

## 🚨 Important Notes

- **Never import from "Added to Database" folder** - this creates duplicates
- **Always move files to "Added to Database" after successful import**
- **Check for JSON syntax errors** before importing
- **URL encode spaces** in character names (e.g., `Bayside%20Bob.json`)

## 🔧 Import Script Details

**Script Location:** `data/import_character.php`
**Usage:** `http://vbn.talkingheads.video/data/import_character.php?file=CharacterName.json`

**What it imports:**
- ✅ Character basic info (name, clan, generation, etc.)
- ✅ Positive traits
- ✅ Negative traits  
- ✅ Abilities with specializations
- ✅ Disciplines with powers
- ✅ Backgrounds
- ✅ Morality data
- ✅ Merits and flaws

## 📊 Success Indicators

Look for this output pattern:
```
=================================================================
Import Complete!
=================================================================
✅ [Character Name] imported successfully
   Character ID: [ID Number]
=================================================================
🎉 Character is ready to use!
```

## 🐛 Troubleshooting

### JSON Parse Error
- Check for markdown syntax (```) in JSON files
- Validate JSON format
- Ensure proper closing braces

### Database Errors
- Check if character already exists
- Verify database connection
- Check for missing required fields

### Missing Data Warnings
- Some characters may have different JSON structures
- Check which data was imported successfully
- Manual entry may be needed for missing traits/abilities

### Format Mismatch Issues
- **Problem**: Character JSON files in narrative/descriptive format won't import
- **Solution**: Convert to full character sheet format before importing
- **Required fields**: `character_name`, `player_name`, `chronicle`, `pc`, `biography`, `status`, `morality`
- **Required structures**: 
  - `traits` as arrays by category
  - `abilities` as array of objects with name/category/level
  - `disciplines` as array with powers structure
  - `backgrounds` as key-value pairs
  - `merits_flaws` as array of objects

## 📈 Future Scaling

With plans for dozens of characters:
- Consider batch import scripts for multiple characters
- Implement duplicate checking in import script
- Create character validation tools
- Maintain this documentation as process evolves

---

## 📝 Import Session Notes

### Session 1 (January 11, 2025)
- **Characters Imported:** 6 total
  - Leo (ID: 55) - Partial import due to format differences
  - Bayside Bob (ID: 56) - Full successful import
  - Betty, Étienne, Lucien Marchand, Sofia Alvarez - All successful
  - Piston - Required format conversion from narrative to character sheet format

### Key Lessons Learned
1. **Two JSON formats exist:**
   - **Full character sheet format** (like Bayside Bob.json) - imports cleanly
   - **Narrative format** (like original Piston.json) - requires conversion

2. **Import script location:** `data/import_character.php`
3. **Script looks in:** `reference/Characters/` (main folder, not "Added to Database")
4. **Archive system:** Move files to "Added to Database" after successful import

### Format Conversion Template
When converting narrative format to character sheet format:
```json
{
  "character_name": "Character Name",
  "player_name": "NPC",
  "chronicle": "Valley by Night",
  "nature": "Nature",
  "demeanor": "Demeanor", 
  "concept": "Concept",
  "clan": "Clan",
  "generation": 10,
  "sire": "Sire Name",
  "pc": 0,
  "biography": "Condensed biography text",
  "equipment": "Equipment list",
  "traits": {
    "Physical": ["trait1", "trait2"],
    "Social": ["trait1", "trait2"],
    "Mental": ["trait1", "trait2"]
  },
  "negativeTraits": {
    "Physical": [],
    "Social": [],
    "Mental": []
  },
  "abilities": [
    {"name": "Ability Name", "category": "Category", "level": 3}
  ],
  "disciplines": [
    {
      "name": "Discipline",
      "level": 3,
      "powers": [
        {"level": 1, "power": "Power Name"}
      ]
    }
  ],
  "backgrounds": {
    "Contacts": 3,
    "Resources": 2
  },
  "morality": {
    "path_name": "Humanity",
    "path_rating": 7,
    "conscience": 3,
    "self_control": 3,
    "courage": 3,
    "willpower_permanent": 5,
    "willpower_current": 5,
    "humanity": 7
  },
  "merits_flaws": [
    {
      "name": "Merit/Flaw Name",
      "type": "merit",
      "category": "Category",
      "cost": 2,
      "description": "Description"
    }
  ],
  "status": {
    "created_date": "2025-01-11",
    "xp_total": 0,
    "xp_spent": 0,
    "xp_available": 0,
    "blood_pool": 10,
    "blood_per_turn": 1,
    "health_levels": 7,
    "notes": "Character notes"
  }
}
```

---

**Last Updated:** January 11, 2025  
**Process Version:** 1.1  
**Characters Imported:** 6 (Leo, Bayside Bob, Betty, Étienne, Lucien Marchand, Sofia Alvarez, Piston)
