# Discipline System Fix

## Summary

Fixed the discipline system in the character creation editor that was preventing disciplines from being saved and displayed properly.

## Changes Made

### 1. Enabled DisciplineSystem.js Module
**File:** `lotn_char_create.php` (line 1948)
- **Change:** Uncommented the DisciplineSystem.js script include
- **Before:** `<!-- <script src="js/modules/systems/DisciplineSystem.js"></script> -->`
- **After:** `<script src="js/modules/systems/DisciplineSystem.js"></script>`

### 2. Initialized DisciplineSystem in Main App
**File:** `js/modules/main.js` (lines 124-130)
- **Change:** Uncommented DisciplineSystem instantiation
- **Before:** Commented out DisciplineSystem initialization
- **After:** Active DisciplineSystem with proper dependencies

### 3. Fixed Array/Object Type Mismatch
**File:** `js/modules/systems/DisciplineSystem.js` (line 458)
- **Change:** Fixed disciplines type from object `{}` to array `[]`
- **Before:** `const disciplines = state.disciplines || {};`
- **After:** `const disciplines = state.disciplines || [];`

### 4. Fixed populateDisciplinesFromData Call
**File:** `js/modules/main.js` (line 578)
- **Change:** Pass disciplinePowers instead of disciplines array
- **Before:** `this.populateDisciplinesFromData(data.disciplines);`
- **After:** `this.populateDisciplinesFromData(data.disciplinePowers);`

### 5. Fixed Save Function to Collect Disciplines
**File:** `lotn_char_create.php` (lines 1984-2005)
- **Change:** Save function now collects disciplinePowers from state
- **Before:** Hardcoded `disciplines: []`
- **After:** `disciplinePowers: state?.disciplinePowers || {}`

### 6. Fixed Display to Show Only Disciplines With Powers
**File:** `js/modules/systems/DisciplineSystem.js` (lines 478-513)
- **Change:** Filter display to only show disciplines that have powers selected
- **Before:** Shows all disciplines in state, even without powers
- **After:** Only displays disciplines with at least one power, shows as "Discipline: Power Name Level"

### 7. Fixed Duplicate Display Across Categories
**File:** `js/modules/systems/DisciplineSystem.js` (lines 504-513)
- **Change:** Added category-specific filtering for Clan, Blood Sorcery, and Advanced disciplines
- **Before:** All disciplines appeared in all three lists
- **After:** Each discipline appears only in its correct category

### 8. Disabled Legacy selectPower Handler
**File:** `lotn_char_create.php` (lines 2447, 2506)
- **Change:** Commented out legacy selectPower onclick handler and disabled the function
- **Before:** Legacy inline handlers creating duplicate discipline entries
- **After:** Only DisciplineSystem.js handles power selection, no duplicates

### 9. Fixed Character Loading to Set characterId in State
**File:** `js/modules/core/StateManager.js` (line 10)
- **Change:** Added `characterId: null` to initial state
- **Reason:** Needed to detect edit mode vs. creation mode

### 10. Fixed Character Loading to Set characterId and playerName
**File:** `js/modules/main.js` (lines 497-517)
- **Change:** Explicitly set characterId and playerName in state when loading character
- **Before:** Missing characterId prevented detection of edit mode
- **After:** State includes characterId to identify editing vs. creation

### 11. Fixed populateDisciplinesFromData to Use Display Update
**File:** `js/modules/main.js` (lines 691-699)
- **Change:** Removed button-clicking logic, now just calls updateAllDisplays()
- **Before:** Looking for non-existent data-level attributes in HTML
- **After:** Simply triggers display refresh since data is already in state

### 12. Disabled Clan Restrictions for NPCs and Edit Mode
**File:** `js/modules/systems/DisciplineSystem.js` (lines 802-851)
- **Change:** Added checks for isNPC and isEditing to skip clan restrictions
- **Before:** All characters including NPCs had clan discipline restrictions applied
- **After:** NPCs and editing existing characters can select any discipline

### 13. Fixed clearInvalidDisciplines to Skip for NPCs/Edit Mode
**File:** `js/modules/systems/DisciplineSystem.js` (lines 856-863)
- **Change:** Added early return for NPCs and editing mode
- **Before:** Clearing disciplines not in clan list even for NPCs
- **After:** Never clears disciplines when editing or for NPCs

### 14. Fixed CSS for remove-power-btn and selected-power
**File:** `css/style.css` (lines 2196-2227)
- **Change:** Added CSS for `.remove-power-btn` and `.selected-power` classes
- **Before:** Remove buttons were oversized, making entire discipline rows square
- **After:** Properly sized buttons with flexbox layout

### 15. Added Complete Fallback Discipline Data
**File:** `js/modules/systems/DisciplineSystem.js` (lines 151-220)
- **Change:** Added complete power data for Dominate, Dementation, Potence, Presence, Obfuscate, Fortitude, Protean
- **Before:** Only Animalism, Auspex, and Celerity had fallback data
- **After:** All common clan disciplines now have fallback power names

## What This Fixes

1. **Disciplines Now Save Properly**: When editing a character and adding disciplines, they are now correctly saved to the database
2. **Disciplines Display in Admin Panel**: Previously imported characters (Pistol Pete, Sasha, Leo) now display their disciplines correctly
3. **Disciplines Load When Editing**: When reopening a character to edit, disciplines are properly loaded from the database
4. **Power Selection Works**: Users can select powers for each discipline through the UI

## Technical Details

The issue was that the `DisciplineSystem.js` module was commented out in two places:
1. In the script includes
2. In the module initialization

This meant that disciplines were never being collected into the state when saved. The save function was sending an empty `disciplines: []` array instead of the actual `disciplinePowers` object.

Additionally, there was a type mismatch where `disciplinePowers` is an object with discipline names as keys and arrays of power levels as values, but the populate function was being passed the disciplines array instead.

## Testing

To verify the fix works:

1. **Existing Characters**: Open Pistol Pete, Sasha, or Leo in the admin panel - their disciplines should now display
2. **New Characters**: Create a character, add disciplines, save - disciplines should be saved
3. **Editing**: Edit an existing character, add disciplines, save - changes should persist
4. **Loading**: Reload a saved character - disciplines should appear correctly

## Related Files

- `lotn_char_create.php` - Character creation/editing page
- `js/modules/main.js` - Main application controller
- `js/modules/systems/DisciplineSystem.js` - Discipline management system
- `js/modules/core/StateManager.js` - State management
- `save_character.php` - Save endpoint
- `load_character.php` - Load endpoint
- `admin/view_character_api.php` - Admin panel character view

## Database Structure

Disciplines are stored in the `character_disciplines` table:
- `character_id` - Foreign key to characters
- `discipline_name` - Name of the discipline (e.g., "Potence")
- `level` - Level (1-5)
- `xp_cost` - Experience cost (currently 0)

The disciplines are displayed in the admin panel via `getCharacterAllDisciplines()` function which retrieves disciplines and their associated powers from the master `discipline_powers` table.

