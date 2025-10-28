# NPC Character Template Instructions

## Overview
This JSON template is designed to match the database structure for the VbN (Vampire by Night) character system. Fill out all fields with the character information.

## Required Fields (Must Fill Out)

### Basic Information
- **character_name**: The character's full name
- **nature**: The character's true nature (e.g., "Architect", "Monster")
- **demeanor**: The character's apparent personality (e.g., "Bon Vivant", "Gallant")
- **concept**: A brief character concept
- **clan**: Character's clan (e.g., "Toreador", "Ghoul (Domitor: Name)", "Caitiff")
- **generation**: Number (0 for ghouls, 6-15 for vampires)
- **sire**: Who embraced or ghouled them

### Biography & Equipment
- **biography**: Full character backstory
- **equipment**: List of items they carry/have access to

## Traits

### Positive Traits
List all positive traits in the format: `["TraitName", "TraitName"]` (repeated for each dot)
- **Physical**: e.g., `["Graceful", "Quick", "Coordinated"]`
- **Social**: e.g., `["Charismatic", "Charismatic", "Persuasive"]`
- **Mental**: e.g., `["Observant", "Observant", "Intelligent"]`

### Negative Traits
Same structure as positive traits, or leave as empty arrays `[]`

## Abilities

List all abilities in this format:
```json
{"name": "AbilityName", "category": "Physical|Social|Mental", "level": 1-5}
```

Common abilities:
- Physical: Alertness, Athletics, Brawl, Dodge, Firearms, Melee, Security, Stealth
- Social: Animal Ken, Expression, Intimidation, Leadership, Streetwise, Subterfuge
- Mental: Academics, Computer, Finance, Investigation, Law, Linguistics, Medicine, Occult, Politics, Science

## Disciplines

For each discipline, list the level and all powers at that level:
```json
{
  "name": "DisciplineName",
  "level": 1-5,
  "powers": [
    {"level": 1, "power": "PowerName"},
    {"level": 2, "power": "PowerName"}
  ],
  "notes": "Optional description"
}
```

Common disciplines: Auspex, Celerity, Dominate, Fortitude, Obfuscate, Presence, Potence, Protean, etc.

## Backgrounds

Fill in the levels (0-5) for each background. The `backgroundDetails` section provides space for description of each background.

### Background Points
Total 10-15 points for starting characters (more for older NPCs)

Example:
```json
"backgrounds": {
  "Generation": 6,
  "Resources": 4,
  "Influence": 3,
  "Allies": 2,
  "Contacts": 2,
  "Retainers": 1,
  "Status": 3
}
```

## Morality

Default values shown are starting points. Adjust for the character:
- **path_name**: Usually "Humanity" (can be Path of)
- **path_rating**: 1-10 (how much they follow their path)
- **conscience**: 1-5
- **self_control**: 1-5
- **courage**: 1-5
- **willpower_permanent**: Starting willpower (1-10)
- **willpower_current**: Current willpower (usually same as permanent)
- **humanity**: Current humanity rating (1-10)

## Merits & Flaws

List any merits or flaws:
```json
{
  "name": "Merit/FlawName",
  "type": "merit|flaw",
  "category": "Physical|Social|Mental|Supernatural",
  "cost": point_value (positive for merits, negative for flaws),
  "description": "What it does"
}
```

## Status & Notes

- **created_date**: Date as "YYYY-MM-DD"
- **xp_total**: Total XP earned (if advanced)
- **xp_spent**: XP spent
- **xp_available**: Remaining XP
- **blood_pool**: Blood points (10-30 typically)
- **blood_per_turn**: Blood per turn (generation-based)
- **health_levels**: Usually 7
- **notes**: Important character notes for ST

## Optional Sections

- **rituals**: Leave as empty array `[]` unless character has Thaumaturgy rituals
- **specializations**: For abilities with specializations
- **relationships**: Other characters they have defined relationships with
- **quotes**: Memorable quotes from/for the character

## Examples

See these files for reference:
- `reference/Characters/Étienne.json` - Elder NPC
- `reference/Characters/Sofia Alvarez.json` - Ghoul retainer

## Database Compatibility

All fields in this template directly map to database tables:
- `characters` table
- `character_traits` table
- `character_abilities` table
- `character_disciplines` table
- `character_backgrounds` table
- `character_morality` table
- `character_merits_flaws` table
- `character_equipment` table
- `character_influences` table

The JSON file can be imported directly into the database system.

## Tips

1. **Keep it simple**: For minor NPCs, you don't need to fill every field
2. **Be consistent**: Use the same trait names that appear in the system
3. **Power levels**: Make sure discipline powers match their level (don't list level 4 power at discipline level 2)
4. **Balance**: NPCs should fit their role (elder = powerful, neonate = weak)
5. **Story first**: Focus on biography and concept, stats come second
