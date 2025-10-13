## Merits & Flaws Page Design for Laws of the Night Revised

Let me help you design this character creation page. In LotN Revised, Merits and Flaws are optional traits that give bonuses/penalties and affect your Freebie Points.

**Key Rules to Build Into Your System:**
- **Maximum 7 points** in Flaws
- Flaw points convert to Freebie Points (1:1)
- Merits cost Freebie Points
- Some Merits/Flaws have point costs (1-7 points)
- Categories: Physical, Mental, Social, Supernatural

## UI Layout Concept

```
═══════════════════════════════════════════════════
         MERITS & FLAWS SELECTION
═══════════════════════════════════════════════════

Freebie Points: [15] (+3 from Flaws)
Flaws Taken: [3/7 points maximum]

┌─────────────────────────────────────────────────┐
│ FILTER BY:  [All] [Physical] [Mental] [Social] │
│             [Supernatural]                       │
│                                                  │
│ SEARCH: [____________]  SHOW: [Merits] [Flaws] │
└─────────────────────────────────────────────────┘

┌──────── AVAILABLE ────────┐  ┌──── SELECTED ────┐
│                           │  │                   │
│ MERITS                    │  │ MERITS:           │
│ ☐ Acute Senses (1pt)     │  │ ✓ Ambidextrous(1) │
│ ☐ Ambidextrous (1pt)     │  │                   │
│ ☐ Catlike Balance (1pt)  │  │ FLAWS:            │
│ ☐ Eidetic Memory (2pt)   │  │ ✓ Bad Sight (1)   │
│ ☐ Lucky (3pt)            │  │ ✓ Nightmares (1)  │
│   [?] Info               │  │ ✓ Prey Exclusion(1)│
│                           │  │                   │
│ FLAWS                     │  │ Net: -1 Freebie   │
│ ☐ Bad Sight (1pt)        │  │                   │
│ ☐ Hard of Hearing (1pt)  │  │ [Remove Selected] │
│ ☐ Nightmares (1pt)       │  │                   │
│ ☐ Clan Enmity (2pt)      │  │                   │
└───────────────────────────┘  └───────────────────┘

[< Back to Backgrounds]  [Continue to Finishing >]
```

## Detailed Element Breakdown

**Info Popup (when clicking [?]):**
```
┌─────────────────────────────────────┐
│ AMBIDEXTROUS (1pt Merit)            │
├─────────────────────────────────────┤
│ Category: Physical                   │
│ Cost: 1 Freebie Point               │
│                                      │
│ You can use either hand equally     │
│ well. You suffer no penalties for   │
│ using your off-hand in combat or    │
│ other physical tasks.               │
│                                      │
│ Requirements: None                   │
│ Restrictions: Cannot take with      │
│ One Arm flaw                        │
│                                      │
│        [Add to Character]           │
└─────────────────────────────────────┘
```

## Key LotN Revised Merits & Flaws to Include

**PHYSICAL MERITS (1-4 pts):**
- Acute Senses (1pt)
- Ambidextrous (1pt)
- Catlike Balance (1pt)
- Bruiser (1pt)
- Daredevil (3pt)
- Efficient Digestion (3pt)
- Eat Food (1pt)

**PHYSICAL FLAWS (1-4 pts):**
- Bad Sight (1pt)
- Hard of Hearing (1pt)
- One Eye (2pt)
- Fourteenth Generation (2pt)
- Monstrous (3pt)
- Slow Healing (3pt)

**MENTAL MERITS (1-3 pts):**
- Common Sense (1pt)
- Concentration (1pt)
- Eidetic Memory (2pt)
- Iron Will (3pt)
- Light Sleeper (2pt)
- Time Sense (1pt)

**MENTAL FLAWS (1-5 pts):**
- Nightmares (1pt)
- Phobia (varies 1-3pt)
- Short Fuse (2pt)
- Soft-Hearted (1pt)
- Weak-Willed (2pt)

**SOCIAL MERITS (1-3 pts):**
- Natural Leader (1pt)
- Reputation (2pt)
- Prestigious Sire (1pt)

**SOCIAL FLAWS (1-3 pts):**
- Dark Secret (1pt)
- Prey Exclusion (1pt)
- Sire's Resentment (1pt)
- Clan Enmity (2pt)

**SUPERNATURAL MERITS (1-7 pts):**
- Oracular Ability (3pt)
- Medium (2pt)
- Magic Resistance (2pt)
- Lucky (3pt)
- Danger Sense (2pt)

**SUPERNATURAL FLAWS (1-7 pts):**
- Cursed (1-5pt)
- Haunted (3pt)
- Hunted (4pt)
- Grip of the Damned (4pt)

## Validation Logic You'll Need

```javascript
// Pseudo-code for validation
function validateMeritsFlaws(character) {
  let totalFlawPoints = 0;
  let meritCost = 0;
  
  // Calculate flaw points
  character.flaws.forEach(flaw => {
    totalFlawPoints += flaw.points;
  });
  
  // Check flaw maximum
  if (totalFlawPoints > 7) {
    return "Cannot take more than 7 points of Flaws";
  }
  
  // Calculate merit cost
  character.merits.forEach(merit => {
    meritCost += merit.points;
  });
  
  // Check if player has enough freebie points
  let availablePoints = character.freebiePoints + totalFlawPoints;
  if (meritCost > availablePoints) {
    return "Not enough Freebie Points for selected Merits";
  }
  
  // Check for conflicting merits/flaws
  if (hasConflicts(character.merits, character.flaws)) {
    return "Some Merits and Flaws conflict";
  }
  
  return true;
}
```

## Conflict Checking Examples

- Can't have **Ambidextrous** with **One Arm**
- Can't have **Blush of Health** with **Monstrous**
- Can't have **Eat Food** and **Efficient Digestion** together
- **Clan-specific** merits require correct clan
- **Generation-specific** merits require minimum generation

## Additional Features to Consider

**Sorting Options:**
- By Point Cost (Low to High / High to Low)
- Alphabetical
- Most Popular
- Recommended for Clan

**Visual Indicators:**
- 🔒 Locked (doesn't meet requirements)
- ⚠️ Warning (conflicts with selection)
- ⭐ Recommended (for your clan/concept)
- 🔥 Popular choice

**Tooltips on Hover:**
Quick preview of effect without full popup

Would you like me to create a full data structure for the Merits & Flaws list, or help you design the conflict-checking system in more detail?