# 📝 Character Import Guide

Use this guide to create JSON files for importing characters into the VbN database.

---

## 🎯 Quick Start

1. **Copy** `data/character-example.json`
2. **Edit** the values to match your character
3. **Import** using `data/import_all_tremere.php` (modify to point to your file)

---

## 📋 Required Fields

### Basic Info (All Required):
- `character_name` - Character's full name
- `player_name` - Player name or "ST/NPC"
- `chronicle` - Usually "Valley by Night"
- `nature` - Character's true nature
- `demeanor` - Character's outward personality
- `concept` - One-line character concept
- `clan` - Must be valid clan (Brujah, Gangrel, Malkavian, Nosferatu, Toreador, Tremere, Ventrue, Caitiff)
- `generation` - 1-13 (most PCs are 12-13)
- `sire` - Name of sire or "Unknown"
- `pc` - 0 for NPC, 1 for PC
- `biography` - Character backstory

### Complex Fields:
- `traits` - Object with Physical, Social, Mental arrays
- `negativeTraits` - Same structure as traits
- `abilities` - Array of {name, category, level} objects
- `disciplines` - Array with special handling for Thaumaturgy/Necromancy
- `backgrounds` - Object with background names and levels (0-5)
- `morality` - Object with all virtue values
- `merits_flaws` - Array of merit/flaw objects
- `status` - Object with XP, blood pool, health data
- `rituals` - Array of ritual strings

### Optional Fields:
- `equipment` - Text description of gear
- `backgroundDetails` - Object with background descriptions
- `specializations` - Object mapping abilities to specializations
- `research_notes` - Custom object for character-specific data
- `artifacts` - Array of artifact objects (for items like Ball of Truth)

---

## 🔮 Special Cases

### Blood Magic (Thaumaturgy/Necromancy)

For **Thaumaturgy paths**, use this format:
```json
{
  "name": "Thaumaturgy",
  "path": "Path of Blood",
  "level": 3,
  "powers": [
    {"level": 1, "power": "A Taste for Blood"},
    {"level": 2, "power": "Blood Rage"},
    {"level": 3, "power": "Blood of Potency"}
  ],
  "notes": "Optional notes about this path"
}
```

For **regular disciplines** (Auspex, Celerity, etc.):
```json
{
  "name": "Auspex",
  "level": 2,
  "powers": [
    {"level": 1, "power": "Heightened Senses"},
    {"level": 2, "power": "Aura Perception"}
  ]
}
```

**Important:** Each Thaumaturgy PATH is treated as a separate discipline in the database.

### Abilities

Categories: `Physical`, `Social`, `Mental`, `Optional`

**Common Abilities:**
- **Physical:** Athletics, Brawl, Dodge, Firearms, Melee, Security, Stealth, Survival
- **Social:** Animal Ken, Empathy, Expression, Intimidation, Leadership, Subterfuge, Streetwise, Etiquette, Performance
- **Mental:** Academics, Computer, Finance, Investigation, Law, Linguistics, Medicine, Occult, Politics, Science
- **Optional:** Alertness, Awareness, Drive, Crafts, Firecraft

### Specializations

**Abilities requiring specializations:**
Art, Expression, Crafts, Performance, Pilot, Academics, Area Knowledge, Esoterica, Science

Format:
```json
"specializations": {
  "Occult": "Blood magic and rituals",
  "Academics": "European history"
}
```

Bonus (+1) is automatically calculated if ability level >= 4.

### Rituals

**Format options:**
- `"Ritual Name (Level 3)"` - Parsed automatically
- `"Ritual Name"` - Level defaults to 0 (unknown)
- `"Custom Ritual (Level 2 - Custom)"` - Flagged as custom

**Type is auto-detected:**
- Tremere → Thaumaturgy
- Giovanni → Necromancy

### Merits & Flaws

Categories: `Physical`, `Social`, `Mental`, `Supernatural`

Types: `merit` or `flaw` (will be auto-capitalized)

```json
{
  "name": "Merit/Flaw Name",
  "type": "merit",
  "category": "Mental",
  "cost": 3,
  "description": "Full description of effect"
}
```

### Generation Background

**CRITICAL:** Generation background dots must match actual generation:
- 13th gen = 0 dots
- 12th gen = 1 dot
- 11th gen = 2 dots
- 10th gen = 3 dots
- 9th gen = 4 dots
- 8th gen = 5 dots
- etc.

**Example:** Gen 12 character should have `"Generation": 1` in backgrounds

---

## 🎨 Display Formats

After import, data displays as:

**Abilities:**
`Occult x4: Desert-based magic (+1 bonus), Science x3, Investigation x2`

**Traits:**
Duplicates allowed and displayed with multiplier: `Intelligent x2, Quick, Nimble`

**Rituals:**
Nested under Disciplines section (related to Thaumaturgy/Necromancy)

---

## 💡 Tips

### For Multiple Characters:
Wrap characters in an array:
```json
[
  { "character_name": "Character 1", ... },
  { "character_name": "Character 2", ... },
  { "character_name": "Character 3", ... }
]
```

### Custom Data:
Use `research_notes` or add custom objects for character-specific data:
```json
"research_notes": {
  "dehydrate_path_progress": "Working on Level 3 power...",
  "personal_goals": "Prove worth to the clan"
}
```

This gets stored in `characters.custom_data` JSON field.

### Artifacts:
For special items like James Whitmore's Ball of Truth:
```json
"artifacts": [
  {
    "name": "Ball of Truth",
    "type": "Magical Artifact",
    "description": "Full description",
    "requirements": {"Mental": 3},
    "rarity": "rare"
  }
]
```

Stored in custom_data, can be linked to items table later.

---

## 🚀 How to Import

### Step 1: Create/Edit JSON
Use `character-example.json` as template

### Step 2: Run Import
For single character: Modify `import_andrei.php` to point to your file
For multiple: Use `import_all_tremere.php`

### Step 3: Verify
Visit: `https://www.websitetalkingheads.com/vbn/data/verify_andrei.php`

---

## ⚠️ Common Mistakes

1. **Missing commas** between array/object items
2. **Wrong Generation background** - Must match actual generation
3. **Invalid ability categories** - Must be Physical, Social, Mental, or Optional
4. **Mixing Blood Magic formats** - Use "path" for Thaumaturgy, omit for regular disciplines
5. **Wrong merit/flaw types** - Must be "merit" or "flaw" (lowercase ok, auto-capitalized)
6. **Missing required fields** - All basic info fields are required

---

## 📞 Need Help?

See `IMPORT_SYSTEM_SUMMARY.md` for complete technical documentation and troubleshooting.

**Happy character creating!** 🧛

