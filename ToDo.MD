# ToDo List

## 🐛 Bug Fixes

### Character Management Issues
- **Character Management link** on http://vbn.talkingheads.video/admin/admin_panel.php - some character information is showing as `Null`
- **Home page NPCs** are reported as 0 instead of actual count

---

## 🎨 Feature Requests

### Add Character Portraits to Character Sheets
1. Use images in `G:\VbN\reference\Characters\Images`
2. Add new field to character database named `portrait` that stores the character name + image type
3. Add a portrait square to the character sheet
4. Allow players to add their own image for their characters
5. Display portraits in both compact and full versions of the character sheet

### Create Rules Database
1. Analyze PDFs in `G:\VbN\reference\Books`
2. Write a summary of each book
3. Create a system where if I ask a rules question, find the answer in the books

---

## 🔧 Technical Issues

### Character Creation System
- **Complete 8-tab character creation form** - Currently only Basic Info and partial Traits are implemented
- **Fix character loading/editing functionality** - Allow users to modify existing characters
- **Implement missing character creation tabs** - Abilities, Disciplines, Backgrounds, Morality, Merits & Flaws, Final Details
- **Fix rituals database** - `rituals_master` table is empty, needs LoTN ritual data
- **Complete XP tracking system** - Real-time XP validation and cost calculations

### Database & Data Issues
- **Fix missing database columns** - Several critical fields were missing and need verification
- **Validate character import system** - Ensure all JSON imports work correctly
- **Complete equipment system integration** - Recent equipment system needs testing
- **Fix Generation background calculations** - Some characters have incorrect generation background dots

## 🎨 User Experience Improvements

### Character Management
- **Add character search/filtering** - Find characters by name, clan, player, etc.
- **Implement character comparison** - Side-by-side character comparison feature
- **Add character export functionality** - Export characters to PDF or print-friendly format
- **Create character templates** - Pre-made character templates for quick creation
- **✅ NPC Agent Briefing System** - Admin page listing all NPCs with detailed briefing info for playing characters (nature/demeanor, traits, abilities, biography, agentNotes, actingNotes) - **COMPLETED v0.6.4**

### Admin Panel Enhancements
- **Fix admin panel character display** - Some character information shows as Null
- **Add bulk character operations** - Delete, export, or modify multiple characters
- **Implement character statistics dashboard** - Show clan distribution, generation stats, etc.

## 📋 Project Management

### Documentation Tasks
- Create a summary of the project's current development
- Create a list of pages that still need to be created
- **Document API endpoints** - Create comprehensive API documentation
- **Create user guide** - Step-by-step guide for players and storytellers
- **Add code comments** - Improve code documentation for future maintenance

### Testing & Quality Assurance
- **Create automated test suite** - Unit tests for critical functions
- **Add form validation testing** - Ensure all character creation forms work properly
- **Implement error logging system** - Better error tracking and debugging
- **Add performance monitoring** - Track page load times and database performance