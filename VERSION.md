# LOTN Character Creator - Version History

## Version 0.6.4 (Current)
**Date:** October 26, 2025

### NPC Agent Briefing System:
- ✅ **Complete NPC Management System** - Dedicated admin page for viewing and managing all NPCs
- ✅ **Agent Briefing Modal** - Comprehensive character information display for playing NPCs during sessions
- ✅ **Notes Management** - Two editable note fields: Agent Notes (AI-formatted briefing) and Acting Notes (post-session notes)
- ✅ **Database Migration** - Added agentNotes and actingNotes TEXT columns to characters table
- ✅ **NPC List with Filtering** - Sortable table with clan filter, name search, and pagination (20/50/100 per page)
- ✅ **Notes-Only Edit Modal** - Quick edit modal for agentNotes and actingNotes without full character editor
- ✅ **NPC Player Name Standardization** - Fixed all NPCs to use player_name = 'NPC' (purple badge) instead of inconsistent variations

### Briefing Features:
- **Complete Character Info** - Core identity (nature, demeanor, concept, clan, generation, sire)
- **Traits by Category** - Physical, Social, Mental traits organized and displayed clearly
- **Abilities & Disciplines** - Key abilities and disciplines formatted as comma-separated lists with levels
- **Backgrounds Display** - Social and resource backgrounds shown with levels
- **Biography** - Full character biography for context
- **AI-Formatted Agent Notes** - Structured briefing information for playing the character
- **Post-Session Acting Notes** - Storyteller observations and developments after playing

### Database Changes:
- Created `database/add_npc_briefing_fields.php` - Migration to add agentNotes and actingNotes columns
- Created `database/normalize_npc_player_names.php` - Normalizes all NPC player_name values to 'NPC'
- Updated all NPC queries to use `player_name = 'NPC'` instead of 'ST/NPC'

### Admin Panel Integration:
- Created `admin/admin_npc_briefing.php` - Main NPC briefing page with table and modals
- Created `admin/api_npc_briefing.php` - API endpoint to fetch comprehensive NPC data
- Created `admin/api_update_npc_notes.php` - API endpoint to save notes
- Created `js/admin_npc_briefing.js` - Client-side interactions and modal handlers
- Updated admin panel navigation on all admin pages to include NPC Briefing link
- Purple badge displays correctly for all NPCs in admin panel

### User Experience Improvements:
- Quick access to all NPC information needed for sessions
- Dedicated space for structured agent briefings
- Easy note-taking for post-session observations
- Consistent NPC identification across the system
- Smooth modal interactions with keyboard shortcuts (ESC to close)

### Files Created:
- `admin/admin_npc_briefing.php` - Main NPC briefing page
- `admin/api_npc_briefing.php` - Briefing data API
- `admin/api_update_npc_notes.php` - Notes save API
- `js/admin_npc_briefing.js` - Client-side logic
- `database/add_npc_briefing_fields.php` - Database migration
- `database/normalize_npc_player_names.php` - NPC normalization
- `NPC_BRIEFING_IMPLEMENTATION.md` - Technical documentation
- `NPC_BRIEFING_QUICK_START.md` - User guide
- `NPC_PLAYER_NAME_FIX.md` - Fix documentation

### Files Modified:
- `admin/admin_panel.php` - Added NPC Briefing nav, updated player_name filter
- `admin/admin_sire_childe.php` - Added NPC Briefing nav
- `admin/admin_sire_childe_enhanced.php` - Added NPC Briefing nav
- `ToDo.MD` - Marked NPC Briefing as complete

---

## Version 0.6.3
**Date:** January 26, 2025

### Logo & Branding System:
- ✅ **SVG Logo Creation** - Created custom VbN logo with gothic vampire theme
- ✅ **Animated Logo Hover Effects** - Logo scales up 10% and glows bright red on hover with smooth transitions
- ✅ **SVG Favicon System** - Created favicon.svg for modern browser support
- ✅ **Favicon Generator Tool** - Built create_favicon.html to generate PNG versions for .ico conversion
- ✅ **Header Integration** - Replaced text placeholder with inline SVG logo in includes/header.php
- ✅ **JavaScript Animation** - Added js/logo-animation.js for smooth hover effects
- ✅ **External CSS Styling** - Logo animations in css/header.css with !important flags for specificity

### Logo Features:
- **Gothic Design** - Dark red/black gradient background with blood-red "VbN" text
- **Hover Animations** - Border glows, text glows, logo scales up with 0.3s smooth transitions
- **Scalable SVG** - Vector format looks perfect at any size
- **Clickable** - Logo links to homepage for easy navigation
- **Favicon Support** - SVG favicon for modern browsers, PNG generator for traditional .ico

### HTML2Canvas Integration (Free Image Export):
- ✅ **Zero-Cost Solution** - Implemented html2canvas library (100% free, no API keys needed)
- ✅ **Client-Side Processing** - All image generation happens in browser, no server required
- ✅ **Integration Script** - Created js/html2canvas-integration.js with three core functions
- ✅ **Test Page** - Built test_html2canvas.html to verify functionality
- ✅ **Documentation** - Comprehensive docs/HTML2CANVAS_USAGE.md with examples and tips

### Image Export Features:
- **convertToImage()** - Download character sheets as PNG images
- **getImageAsBase64()** - Get image data for server upload
- **shareCharacterSheet()** - Share via Web Share API (mobile support)
- **Unlimited Usage** - No costs, no limits, works completely offline
- **Quality Control** - Adjustable scale (1-3) for different quality/file size needs

### Technical Improvements:
- Created images/vbn-logo.svg (80x80 header logo)
- Created images/favicon.svg (32x32 favicon)
- Created js/html2canvas-integration.js (image export functions)
- Created js/logo-animation.js (logo hover animations)
- Updated css/header.css (logo animation styles with !important)
- Created test_html2canvas.html (testing tool)
- Created create_favicon.html (favicon generator)
- Created docs/HTML2CANVAS_USAGE.md (complete documentation)
- Updated includes/header.php (inline SVG logo + favicon link)

### Files Created:
- images/vbn-logo.svg
- images/favicon.svg
- js/html2canvas-integration.js
- js/logo-animation.js
- test_html2canvas.html
- create_favicon.html
- docs/HTML2CANVAS_USAGE.md

### Files Modified:
- includes/header.php (added favicon link, inline SVG logo, animation script)
- css/header.css (logo animation styles)

### User Experience Improvements:
- Professional logo with smooth hover animations
- Favicon appears in browser tabs and bookmarks
- Character sheet image export capability (ready for future implementation)
- Free, unlimited image generation with no external dependencies
- Beautiful gothic branding throughout the site

---

## Version 0.6.2
**Date:** January 11, 2025

### Folder Reorganization & Clan Logo Path Updates:
- ✅ **Renamed and Moved Folder** - `svgs/` → `images/Clan Logos/`
- ✅ **Updated Code References** - Fixed all JavaScript paths in `js/questionnaire.js` (2 locations)
- ✅ **Updated Task References** - Fixed reference in `.taskmaster/tasks/tasks.json`
- ✅ **Database Schema Documentation** - Documented character storage structure (pc BOOLEAN, status ENUM, clan VARCHAR)

### Technical Changes:
- Moved 16 clan logo files from root `svgs/` to `images/Clan Logos/`
- Updated logo paths from `svgs/LogoClan[Name].webp` to `images/Clan Logos/LogoClan[Name].webp`
- Updated `displayClanResult()` and `showTestClanResults()` functions
- Removed empty `svgs` directory

### Documented Character Storage:
- **NPC Flag:** `pc` BOOLEAN (0=NPC, 1=PC, default TRUE)
- **Status:** `status` ENUM (`'draft'`, `'finalized'`, `'active'`, `'dead'`, `'baseline'`, default `'draft'`)
- **Clan:** `clan` VARCHAR(50) - stored as string (not foreign key)

---

### Admin Panel Clan Filtering System:
- ✅ **Clan Sorting Dropdown** - Added comprehensive clan filter dropdown to admin panel
- ✅ **Multi-Filter Support** - Clan filter works alongside existing PC/NPC filters and search
- ✅ **Real-time Filtering** - Characters filter instantly when clan is selected
- ✅ **Comprehensive Clan List** - Includes all major vampire clans plus Ghoul option
- ✅ **Data Attribute Integration** - Added data-clan attributes for reliable JavaScript filtering
- ✅ **Pagination Integration** - Filtered results respect pagination system
- ✅ **Debug Logging** - Added console logging for troubleshooting

### Clan Filter Features:
- **Complete Clan Coverage** - Assamite, Brujah, Caitiff, Followers of Set, Gangrel, Giovanni, Lasombra, Malkavian, Nosferatu, Ravnos, Toreador, Tremere, Tzimisce, Ventrue, Ghoul
- **Multi-Filter Support** - Works with PC/NPC filters and name search simultaneously
- **Real-time Updates** - Instant filtering without page reload
- **Consistent UI** - Matches existing admin panel gothic theme
- **Pagination Aware** - Filtered results properly paginate

### Technical Improvements:
- Added `data-clan` attributes to character table rows for reliable JavaScript access
- Updated `applyFilters()` function to include clan filtering logic
- Added `initializeClanFilter()` function for dropdown event handling
- Enhanced CSS styling for clan filter dropdown
- Added debug console logging for troubleshooting

### Files Modified:
- `admin/admin_panel.php` - Added clan filter dropdown and data attributes
- `js/admin_panel.js` - Added clan filtering functionality and debug logging

---

## Version 0.4.3
**Date:** October 23, 2025

### Changes:
- Auto-increment patch version

---


## Version 0.6.2 (Current)
**Date:** January 4, 2025

### Questionnaire System Enhancements:
- ✅ **Category Reassignment** - Removed "Pre-Embrace" and "Post-Embrace" categories, reassigned all questions to appropriate existing categories
- ✅ **Visual Styling Updates** - Changed question-category color to dark-red (#8b0000) for better visual hierarchy
- ✅ **Scoring Logic Fix** - Clan scores now only update when "Next Question" button is clicked, not immediately on answer selection
- ✅ **Results Page Redesign** - Enhanced clan logo display with 350px width, 25px padding, dark drop shadow, and gold border
- ✅ **Testing Mode Implementation** - Added direct results page access for Brujah, Tremere, and Gangrel clans via URL parameters
- ✅ **Header Management** - Questionnaire header now hidden on results page for cleaner presentation
- ✅ **Clan Logo Container Styling** - Fixed width (400px), gold border, dark-red background with radial gradient, rounded corners (20px), and drop shadows

### Technical Improvements:
- Updated populate_complete_39_questions.php to reassign question categories
- Enhanced questionnaire.php with testing mode support for multiple clans
- Improved js/questionnaire.js with generic showTestClanResults() function
- Updated css/questionnaire.css with enhanced results page styling
- Fixed index.php links to point to correct questionnaire.php file

### User Experience Improvements:
- Cleaner category organization with more logical groupings
- Better visual feedback with dark-red category headers
- More intuitive scoring system that doesn't change until user confirms
- Enhanced results page with professional clan logo presentation
- Easy testing access for multiple clans without going through full questionnaire

### Files Modified:
- `populate_complete_39_questions.php` - Category reassignment
- `questionnaire.php` - Testing mode implementation
- `js/questionnaire.js` - Scoring logic and testing functions
- `css/questionnaire.css` - Visual styling updates
- `index.php` - Fixed questionnaire links

---

## Version 0.4.1
**Date:** January 4, 2025

### CSS Refactoring & NPC Count Fix:
- ✅ **CSS File Optimization** - Reduced CSS file sizes by 75-85% through consolidation and optimization
- ✅ **NPC Count Display Fix** - Fixed dashboard statistics showing 0 NPCs by correcting player_name query
- ✅ **External CSS/JS Organization** - Moved all inline styles and scripts to external files per workspace rules
- ✅ **Performance Improvements** - Smaller CSS files load faster and improve site performance
- ✅ **Code Maintainability** - Cleaner, more organized CSS structure for easier maintenance

### CSS Refactoring Results:
- **questionnaire.css**: 437 lines → 65 lines (85% reduction)
- **dashboard.css**: 378 lines → 95 lines (75% reduction)  
- **admin_questionnaire.css**: 129 lines → 25 lines (81% reduction)
- **admin_sire_childe.css**: 615 lines → 95 lines (85% reduction)

### NPC Count Fix:
- **Root Cause**: Queries were using `player_name = 'ST/NPC'` instead of `player_name = 'NPC'`
- **Files Fixed**: index.php, admin/admin_panel.php, admin/admin_sire_childe.php, admin/admin_sire_childe_enhanced.php
- **Result**: Dashboard now correctly displays actual NPC count instead of 0

### Technical Improvements:
- Consolidated CSS properties into single lines where appropriate
- Removed unnecessary whitespace and line breaks
- Used CSS shorthand properties for efficiency
- Maintained readability with logical grouping and comments
- Fixed database queries to use correct NPC identification method

### Files Modified:
- `css/questionnaire.css` - Refactored and optimized
- `css/dashboard.css` - Refactored and optimized
- `css/admin_questionnaire.css` - Refactored and optimized
- `css/admin_sire_childe.css` - Refactored and optimized
- `index.php` - Fixed NPC count query
- `admin/admin_panel.php` - Fixed NPC count query
- `admin/admin_sire_childe.php` - Fixed NPC identification
- `admin/admin_sire_childe_enhanced.php` - Fixed NPC identification

---

## Version 0.4.0
**Date:** January 4, 2025

### Database-Driven Questionnaire System:
- ✅ **Complete 39-Question System** - All questions from Questions_1, Questions_2, and Questions_3 markdown files
- ✅ **Database Table Creation** - questionnaire_questions table with ID, category, question, answers, clan weights
- ✅ **Cinematic Category Display** - Beautiful animated category headers with descriptions for each question type
- ✅ **Admin Management Interface** - Full CRUD system for managing questions and clan scoring weights
- ✅ **Production URL Migration** - Replaced all localhost references with http://vbn.talkingheads.video/
- ✅ **Admin Panel Integration** - Added questionnaire management link to admin navigation
- ✅ **Question Population Scripts** - Automated scripts to populate database with all 39 questions

### Questionnaire Features:
- **17 Categories** - Pre-Embrace, Post-Embrace, Embrace, Personality, Perspective, Powers, Motivation, Supernatural, Secrets, Fears, Scenario, Workplace, Family, Social, Moral, Power, Life
- **Cinematic Headers** - Animated category displays with dramatic titles and descriptions
- **Database-Driven** - All questions stored in database for easy management
- **Admin Interface** - Add, edit, delete questions with clan scoring weight management
- **Clan Scoring System** - Proper clan weight distribution across all 39 questions
- **Production Ready** - All URLs updated for production deployment

### Technical Improvements:
- Created questionnaire_questions database table
- Built populate_complete_39_questions.php script
- Created questionnaire_admin.php management interface
- Updated character_questionnaire_database.php with cinematic categories
- Added questionnaire_database.js for database-driven functionality
- Integrated questionnaire admin link into admin panel
- Replaced all localhost references with production URLs

### Files Created:
- create_questionnaire_table.php (database table creation)
- populate_complete_39_questions.php (39-question population)
- questionnaire_admin.php (admin management interface)
- character_questionnaire_database.php (database-driven questionnaire)
- js/questionnaire_database.js (database-driven JavaScript)
- update_categories.php (category management)
- show_categories.php (category display)
- populate_all_questions.php (complete question set)

### Database Changes:
- questionnaire_questions table with 10 fields
- 39 questions across 17 categories
- Proper clan scoring weights for all answers
- Admin management system integration

### User Experience Improvements:
- Cinematic category headers enhance immersion
- Database-driven system allows easy question management
- Admin interface provides full control over questionnaire
- Production-ready deployment with proper URLs
- Integrated admin panel access for questionnaire management

---

### Character Loading/Saving System Fixes:
- ✅ **Fixed Character Loading System** - Resolved disciplines.map error and data structure issues
- ✅ **Fixed Character Saving System** - Resolved 500 errors and database field mapping issues
- ✅ **Fixed PC Checkbox Validation** - Properly shows PC/NPC status when loading characters
- ✅ **Fixed Form Validation** - Next button now enables correctly when loading character data
- ✅ **Fixed Sync Issues** - Resolved Dreamweaver/Cursor conflicts and file locking problems
- ✅ **Improved Error Handling** - Added comprehensive error logging and debugging
- ✅ **Simplified Save System** - Streamlined character saving to basic fields for stability

### Technical Improvements:
- Fixed JavaScript disciplines.map error by converting objects to arrays
- Fixed AbilitySystem initialization with safety checks for undefined data
- Fixed save_character.php 500 errors with simplified database operations
- Fixed PC checkbox selector from #isPC to #pc
- Added form validation triggers after character data loading
- Removed Dreamweaver sync conflicts by excluding .cursor directory
- Added comprehensive error logging and debugging throughout

### Files Modified:
- `js/modules/main.js` - Fixed PC checkbox selector and added form validation triggers
- `js/modules/systems/DisciplineSystem.js` - Fixed disciplines.map error
- `js/modules/systems/AbilitySystem.js` - Added safety checks for undefined data
- `save_character.php` - Simplified to working basic character creation
- `load_character.php` - Added is_pc field for proper PC/NPC detection
- `sync_config.jsonc` - Excluded .cursor directory from sync

---

## Version 0.2.2
**Date:** January 14, 2025

### Character Creation Questionnaire System:
- ✅ **Complete Questionnaire Interface** - 5-question character creation questionnaire with gothic theme
- ✅ **Clan Scoring System** - Real-time clan score tracking with SessionStorage persistence
- ✅ **Multiple Selection Support** - Personality traits allow selecting exactly 3 options
- ✅ **Admin Debug Panel** - Real-time clan score display for testing (admin-only)
- ✅ **Clan Logo Integration** - Square clan logos with text overlay in results section
- ✅ **Session Management** - Quiz session tracking with automatic reset functionality
- ✅ **Login System Integration** - Questionnaire requires authentication
- ✅ **Responsive Design** - Mobile-friendly layout with gothic styling

### Questionnaire Features:
- **Question Navigation** - Previous/Next buttons with progress tracking
- **Answer Persistence** - SessionStorage maintains answers across page refreshes
- **Clan Recommendation** - Scoring matrix determines winning clan based on answers
- **Visual Feedback** - Progress bar, selection counters, and clan result display
- **Admin Testing** - Debug panel shows real-time clan scores and answers
- **Quiz Reset** - Retake functionality clears session and starts fresh

### Files Created:
```
character_questionnaire.php (main questionnaire page)
css/questionnaire.css (gothic styling)
js/questionnaire.js (interactive functionality)
```

### Technical Implementation:
- **Clan Scoring Matrix** - Maps answers to clan points for 7 major clans
- **SessionStorage Management** - Persistent quiz state with session tracking
- **Admin Access Control** - URL parameter or username-based admin detection
- **Logo Asset Integration** - Uses existing clan logos from svgs/ folder
- **Responsive Layout** - Mobile-optimized with touch-friendly interactions

### User Experience:
- **Gothic Theme** - Matches existing site styling and atmosphere
- **Intuitive Navigation** - Clear question flow with progress indication
- **Visual Results** - Clan logo with overlay text for dramatic presentation
- **Admin Testing** - Real-time score tracking for questionnaire validation
- **Session Persistence** - Answers maintained during browser session

---

## Version 0.2.1
**Date:** January 14, 2025

### NPC Creation Tracker System:
- ✅ **Database-Driven NPC Tracker** - Web-based system for tracking characters mentioned in backstories that need full sheets
- ✅ **Admin Interface** - Two-page system: view all NPCs organized by status, add/edit NPCs via form
- ✅ **Character Relationship Tracking** - Links NPCs to their source characters (PCs/major NPCs)
- ✅ **Status Management** - Ready for Sheet, Concept Only, Sheet Complete, On Hold categories
- ✅ **Source Document Links** - Direct links to character files where NPCs were introduced
- ✅ **Plot Hook Tracking** - Space for story relevance and potential conflicts/alliances
- ✅ **Collaboration Ready** - Remote collaborator can add NPCs via web form without Git knowledge

### NPC Tracker Features:
- **Quick Stats Dashboard** - Live counts of NPCs by status category
- **Expandable Details** - Click character name to view full information
- **Form Validation** - Required fields and error handling
- **Edit Functionality** - Update existing NPC entries
- **Gothic Theme Integration** - Matches existing site styling
- **Mobile Responsive** - Works on all devices

### Files Created:
```
admin/
  ├── npc_tracker.php (view all NPCs)
  ├── npc_tracker_submit.php (add/edit form)
  └── setup_npc_tracker.php (setup page with links)

database/
  └── create_npc_tracker_table.php (database setup)

reference/Characters/
  └── NPC-Creation-Tracker.md (markdown reference)
```

### Database Changes:
- Created `npc_tracker` table with character tracking fields
- Links to users table for submission tracking
- Supports all NPC relationship and plot hook data

### User Experience Improvements:
- Remote collaborators can contribute without technical knowledge
- Organized tracking of background NPCs from character backstories
- Clear workflow: identify NPCs → track them → create sheets when ready
- Source document links for easy reference

---

## Version 0.6.1
**Date:** October 13, 2025

### File Organization & Project Structure:
- ✅ **Reference Folder Reorganization** - Sorted all reference files into appropriate subfolders
- ✅ **Created mechanics/clans/** - New subfolder for clan-specific game mechanics
- ✅ **Moved to Project Root** - session-notes/ and setup-guides/ for easier access
- ✅ **File Cleanup** - Deleted redundant utility files (some commands I use.txt, status.txt)
- ✅ **Taskmaster Integration** - Migrated todo.txt content to taskmaster system
- ✅ **Added Todo** - Character name duplicate validation task

### Files Organized:
```
reference/
├── mechanics/
│   ├── clans/ (NEW)
│   │   ├── Caitiff_Description.MD
│   │   ├── Clan_Complete_Guide.MD
│   │   ├── Clan_Disciplines.MD
│   │   ├── Clan_Quick_Reference.MD
│   │   └── clans.MD
│   ├── Character Sheet Summary.txt
│   ├── Humanity Reference
│   ├── Merits and Flaws Database.MD
│   ├── Merits and Flaws.MD
│   ├── Morality.txt
│   ├── sample character sheet.MD
│   └── Willpower.MD
├── game-lore/
│   ├── Setting.txt
│   └── Starting statement.txt
└── Items/
    └── Items.txt

Root directory:
├── session-notes/ (MOVED from reference/)
└── setup-guides/ (MOVED from reference/)
```

### Files Deleted:
- some commands I use.txt
- status.txt
- to do.txt (migrated to taskmaster)

---

## Version 0.6.0
**Date:** October 12, 2025

### Admin Panel - Character Management System:
- ✅ **Complete Admin Panel Rebuild** - Gothic themed character management interface
- ✅ **Character List Table** - Displays all 26 characters with sortable columns
- ✅ **Filter System** - Toggle between All/PCs/NPCs with active state styling
- ✅ **Real-Time Search** - Instant character name filtering
- ✅ **Pagination System** - 20/50/100 per page with page navigation
- ✅ **Character Statistics** - Live counts for Total, PCs, NPCs
- ✅ **Status Column** - Draft/Finalized/Active/Dead/Missing tracking
- ✅ **View Modal** - Popup with Compact/Full toggle showing complete character details
- ✅ **Edit Integration** - Links to character creator for editing
- ✅ **Delete System** - Confirmation modal with CASCADE delete of all related data
- ✅ **Universal Paths** - Header/footer links work from any subfolder

### Files Created:
```
admin/
  ├── admin_panel.php (rebuilt with gothic theme)
  ├── view_character_api.php (loads character data)
  └── delete_character_api.php (safe deletion with transactions)

database/migrations/
  └── add_character_status.sql (status ENUM field)
```

### Database Changes:
- Added `status` ENUM column: 'draft', 'finalized', 'active', 'dead', 'baseline'
- Default value: 'draft'

---

## Version 0.5.0
**Date:** October 12, 2025

### Home Page Rebuild - Gothic Dashboard System:
- ✅ **Gothic Header/Footer Components** - Reusable includes/header.php and includes/footer.php with Valley by Night branding
- ✅ **Role-Based Home Dashboard** - New index.php with separate player and admin views
- ✅ **Admin Statistics Panel** - Live character counts (Total, PCs, NPCs) with gothic styling
- ✅ **Player Character List** - Shows user's characters with finalized status badges and edit links
- ✅ **Session Authentication** - Proper role detection (admin/storyteller vs player)
- ✅ **Chronicle Information Display** - Phoenix 1994 tagline and chronicle summary
- ✅ **Responsive Gothic Theme** - Dark red backgrounds, cream text, blood red accents, fully mobile-responsive
- ✅ **Admin Action Links** - Create Character, AI Locations, Items Database, Character List, AI Plots (coming soon)
- ✅ **Chat Room Integration** - Updated chat.php with gothic theme and header/footer
- ✅ **Logout Button** - Header includes logout functionality with gothic button styling

### Technical Improvements:
- **File Reorganization** - 88 root files reduced to 18 (80% reduction)
  - New folders: admin/, database/, tests/, archive/
  - Enhanced reference/ with subdirectories for docs
- **Character Sheet Preserved** - index.php renamed to character_sheet.php
- **Database Query Fix** - Statistics query now uses player_name='ST/NPC' instead of is_npc column
- **Session Variable Fix** - Corrected $_SESSION['role'] vs $_SESSION['user_role'] mismatch
- **Vertical Layout Fix** - Fixed 3-column horizontal layout issue with page-wrapper flexbox structure

### File Structure:
```
includes/
  ├── header.php (NEW - gothic header with logo, username, version, logout)
  ├── footer.php (NEW - gothic footer with copyright)
  └── connect.php (existing)

css/
  └── header.css (NEW - gothic styling for header/footer/page layout)

index.php (NEW - role-based home dashboard)
character_sheet.php (RENAMED from old index.php)
chat.php (UPDATED with gothic theme)
dashboard.php (UPDATED to redirect to index.php)
login_process.php (UPDATED to redirect to index.php)
```

### Bug Fixes:
- Fixed session role variable mismatch preventing admin view
- Fixed is_npc column reference (doesn't exist, use player_name check)
- Fixed horizontal 3-column layout (header|content|footer) to proper vertical stack
- Fixed logout button visibility (changed from emoji to text)

---

## Version 0.4.0
**Date:** January 11, 2025

### Character JSON Import System:
- ✅ **Database Migration System** - Comprehensive SQL migration for character import support
- ✅ **Blood Magic Path Support** - parent_discipline field enables Thaumaturgy/Necromancy paths as individual disciplines
- ✅ **Abilities Master Table** - Centralized ability definitions with categories (Physical, Social, Mental, Optional)
- ✅ **Multiple Specializations** - character_ability_specializations table tracks multiple specs per ability with bonus tracking
- ✅ **Enhanced Character Schema** - Added notes, custom_data JSON, total_xp, spent_xp fields
- ✅ **Merit/Flaw Categories** - Added category field (Physical, Social, Mental, Supernatural)
- ✅ **Ritual System** - Enhanced with is_custom flag and rituals_master validation table
- ✅ **Character Import Script** - PHP script transforms JSON to database format with all special cases handled
- ✅ **Import Verification** - Beautiful HTML verification page to review imported character data
- ✅ **3 Tremere NPCs Imported** - Andrei Radulescu, Dr. Margaret Ashford, James Whitmore

### Import System Features:
- **JSON to Database** - Complete transformation handling for complex character data
- **Blood Magic Paths** - Treats Thaumaturgy paths (Path of Blood, Dehydrate Path, etc.) as separate disciplines
- **Specialization Tracking** - Handles multiple specializations per ability with +1 bonus calculation
- **Ritual Parsing** - Extracts level from ritual names, flags custom rituals, defaults to "unknown"
- **Custom Data Storage** - JSON field for research notes, discipline notes, artifacts, and character-specific data
- **Trait Duplication** - Properly stores duplicate traits (e.g., "Intelligent x2")
- **Generation Calculations** - Auto-calculates blood pool max and blood per turn from generation
- **Transaction Safety** - All imports use transactions with rollback on error

### Database Enhancements:
- disciplines.parent_discipline (Thaumaturgy/Necromancy path system)
- abilities_master (35+ abilities with categories)
- character_ability_specializations (multiple specs tracking)
- characters.notes (ST/gameplay notes separate from biography)
- characters.custom_data (JSON for flexible character data)
- characters.total_xp, characters.spent_xp (experience tracking)
- character_traits.trait_category, trait_type (proper categorization)
- character_abilities.level (ability dot tracking)
- character_merits_flaws.category, point_value (categorization and point tracking)
- character_rituals.is_custom (custom ritual flagging)
- character_status.health_levels, blood_pool_current, blood_pool_maximum (status tracking)
- rituals_master (validation table for LoTN rituals)

### Technical Improvements:
- Created character_import_migration.sql (complete schema updates)
- Created run_migration.php (safe migration runner with error handling)
- Created import_andrei.php (test import for single character)
- Created import_all_tremere.php (batch import for multiple characters)
- Created verify_andrei.php (HTML verification with gothic styling)
- Fixed character creation database compatibility issues

### Files Created:
- data/Tremere.json (3 Tremere NPCs: Andrei, Dr. Ashford, James Whitmore)
- data/character_import_migration.sql (database migration)
- data/run_migration.php (migration runner)
- data/import_andrei.php (single character test import)
- data/import_all_tremere.php (batch import script)
- data/verify_andrei.php (beautiful HTML verification page)
- data/ability specializations.md (specialization rules reference)

### User Experience Improvements:
- Character import now supports complex JSON data structures
- Database properly handles Blood Magic path system
- All character creation fields now have proper database support
- Character verification with beautiful, readable HTML display
- Ability display: "Occult x4: Desert-based magic (+1 bonus)" format
- Rituals nested under Disciplines section
- Merits and Flaws properly separated and categorized

---

## Version 0.3.0
**Date:** January 8, 2025

### Chat System with Character Selection:
- ✅ **Character Selection Interface** - Grid layout showing all user's characters as interactive cards
- ✅ **Character Information Display** - Detailed character info display with Name, Clan, Generation, Concept, Nature, Demeanor
- ✅ **API Endpoint** - Created `api_get_characters.php` to fetch user characters from database
- ✅ **Enhanced Chat UI** - Professional styling with responsive design and smooth interactions
- ✅ **Session Security** - Chat system requires login and validates user sessions
- ✅ **Character Loading** - Automatically loads all characters for the logged-in user
- ✅ **Visual Selection** - Click character cards to select for chat with visual feedback

### Chat System Features:
- **Character Cards** - Grid layout with hover effects and selection highlighting
- **Character Details** - Comprehensive character information display
- **API Integration** - Secure database queries with error handling
- **Responsive Design** - Works on desktop and mobile devices
- **Smooth UX** - Animated transitions and visual feedback
- **Security** - Session validation and user authentication

### Technical Improvements:
- Created `chat.php` with character selection interface
- Created `api_get_characters.php` for secure character data retrieval
- Enhanced `dashboard.php` with Chat link in Communication section
- Added comprehensive CSS styling for character cards and selection
- Implemented JavaScript for character loading and selection
- Added error handling for no characters scenario

### User Experience Improvements:
- Users can now select which character to chat as
- Visual character selection with detailed information
- Professional chat interface ready for roleplay
- Seamless integration with existing character system
- Mobile-responsive design for all devices

## Version 0.2.9
**Date:** January 5, 2025

### Resources System & Cash Calculation:
- ✅ **Dynamic Cash Calculation** - Real-time cash calculation based on character choices
- ✅ **Multi-Factor System** - Resources background, clan, concept, and merits/flaws affect starting cash
- ✅ **Smart Update Timing** - Cash only recalculates when Final Details tab is clicked
- ✅ **Detailed Console Logging** - Shows exactly why cash values change with breakdown
- ✅ **Visual Cash Display** - Cash shown in character preview and Final Details tab
- ✅ **Poverty Flaw Override** - Special handling for poverty flaw that overrides other factors
- ✅ **UI Improvements** - Fixed Final Details tab alignment and select element styling

### Cash System Features:
- **Base Cash** - Everyone starts with $100
- **Resources Background** - Primary factor (0-5 dots, $0-$200,000 range)
- **Concept/Profession** - Secondary factor (Business Executive, Street Thug, etc.)
- **Clan Modifiers** - Small influence (Ventrue +$200-500, Caitiff -$100-200, etc.)
- **Merits/Flaws** - Poverty flaw overrides everything, other merits provide bonuses
- **Realistic Ranges** - Cash amounts reflect "pocket money" rather than total net worth

### Technical Improvements:
- Fixed Final Details tab content alignment issue (was appearing outside page)
- Improved select element styling with darker backgrounds
- Added comprehensive cash calculation system with detailed logging
- Enhanced character preview with cash display
- Optimized update timing to prevent constant recalculation

## Version 0.2.7
**Date:** January 5, 2025

### Character Sheet Mode Toggle:
- ✅ **Full/Compact Toggle** - Users can switch between detailed and condensed character preview
- ✅ **Radio Button Interface** - Clean toggle controls in the character preview header
- ✅ **Compact Mode Styling** - Reduced font sizes, spacing, and padding for condensed view
- ✅ **Mobile Responsive Toggle** - Toggle controls work perfectly on mobile devices
- ✅ **Smooth Transitions** - Animated transitions between full and compact modes

### Sheet Mode Features:
- **Full Mode** - Complete character details with full spacing and typography
- **Compact Mode** - Condensed view with smaller fonts and tighter spacing
- **Real-time Switching** - Instant toggle between modes without page reload
- **Consistent Styling** - Maintains gothic theme in both modes
- **Mobile Optimized** - Toggle controls adapt to mobile screen sizes

## Version 0.2.6
**Date:** January 5, 2025

### Mobile Responsiveness System - COMPLETE:
- ✅ **Mobile-First Design** - Responsive layout that works on all device sizes
- ✅ **Touch-Friendly Interface** - Optimized buttons and interactions for mobile devices
- ✅ **Collapsible Sections** - Trait/ability categories can be collapsed on mobile for better navigation
- ✅ **Responsive Tabs** - Horizontal scrolling tabs with custom scrollbars for mobile
- ✅ **Touch Feedback** - Visual feedback on touch interactions with scale animations
- ✅ **Mobile Typography** - Optimized font sizes and spacing for mobile readability
- ✅ **Fixed Tab Functionality** - Resolved JavaScript execution issues on mobile devices
- ✅ **Working Navigation** - Previous/Next buttons and tab switching work perfectly on mobile

### Mobile Features:
- **Responsive Layout** - Single column layout on mobile with sidebar below main content
- **Touch Interactions** - 44px minimum touch targets and visual feedback
- **Collapsible Categories** - Click headers to expand/collapse trait and ability sections
- **Smooth Scrolling** - Custom scrollbars and smooth tab navigation
- **iOS Optimization** - Prevents zoom on form inputs and improves touch handling
- **Tablet Support** - Intermediate responsive design for tablet devices
- **Full Functionality** - All features work identically on mobile and desktop

## Version 0.2.5
**Date:** January 5, 2025

### Mobile Responsiveness System (Initial Implementation):
- ✅ **Mobile-First Design** - Responsive layout that works on all device sizes
- ✅ **Touch-Friendly Interface** - Optimized buttons and interactions for mobile devices
- ✅ **Collapsible Sections** - Trait/ability categories can be collapsed on mobile for better navigation
- ✅ **Responsive Tabs** - Horizontal scrolling tabs with custom scrollbars for mobile
- ✅ **Touch Feedback** - Visual feedback on touch interactions with scale animations
- ✅ **Mobile Typography** - Optimized font sizes and spacing for mobile readability

### Mobile Features:
- **Responsive Layout** - Single column layout on mobile with sidebar below main content
- **Touch Interactions** - 44px minimum touch targets and visual feedback
- **Collapsible Categories** - Click headers to expand/collapse trait and ability sections
- **Smooth Scrolling** - Custom scrollbars and smooth tab navigation
- **iOS Optimization** - Prevents zoom on form inputs and improves touch handling
- **Tablet Support** - Intermediate responsive design for tablet devices

## Version 0.2.4
**Date:** January 5, 2025

### Live Character Preview System:
- ✅ **Real-time Character Preview** - Live character sheet updates as users build their characters
- ✅ **Preview Card Design** - Gothic-themed preview card with animated glow effects
- ✅ **Dynamic Content Updates** - Real-time updates for traits, abilities, disciplines, and basic info
- ✅ **Event-driven Updates** - Automatic preview updates on all character changes
- ✅ **Visual Feedback** - Enhanced preview styling with hover effects and animations
- ✅ **Comprehensive Coverage** - Preview covers all major character creation aspects

### Preview Features:
- **Live Updates** - Character preview updates instantly as selections are made
- **Gothic Styling** - Preview card matches the overall gothic theme
- **Animated Effects** - Subtle glow animation and hover effects
- **Comprehensive Display** - Shows traits, abilities, disciplines, and basic character info
- **Visual Hierarchy** - Clear organization with proper typography and spacing
- **Responsive Design** - Preview adapts to different content amounts

## Version 0.2.3
**Date:** January 5, 2025

### Visual Hierarchy & Progress System:
- ✅ **Progress Indicator Bar** - Animated progress bar showing character creation completion
- ✅ **Enhanced Tab Styling** - Improved active tab indicators with shimmer effects
- ✅ **Better Spacing & Contrast** - Enhanced visual hierarchy throughout the interface
- ✅ **Enhanced Button Styling** - Improved button interactions with hover effects
- ✅ **Enhanced Input Styling** - Better form element styling with focus states
- ✅ **Enhanced Sidebar** - Improved sidebar styling with better visual hierarchy

### Visual Improvements:
- **Progress Bar** - Animated progress indicator with shimmer effects
- **Enhanced Typography** - Improved text shadows and contrast for better readability
- **Button Interactions** - Smooth hover effects and visual feedback
- **Form Elements** - Enhanced input styling with focus states and better contrast
- **Visual Separators** - Better section organization with decorative dividers
- **Consistent Styling** - Unified design language across all interface elements

## Version 0.2.2
**Date:** January 5, 2025

### Card-Based Layout Implementation:
- ✅ **Card Structure** - Each tab now displays as a styled card with shadows and borders
- ✅ **Enhanced Visual Hierarchy** - Card headers with titles, subtitles, and decorative elements
- ✅ **Improved Tab Styling** - Active tab indicators with arrows and enhanced visual feedback
- ✅ **Section Organization** - Better content organization within cards with dividers and icons
- ✅ **Form Integration** - Enhanced form styling within card context

### Visual Improvements:
- **Card Design** - Gradient backgrounds, shadows, and border styling for each tab
- **Tab Indicators** - Active tab arrows and enhanced visual feedback
- **Section Dividers** - Decorative elements and better content separation
- **Icon Integration** - Visual hierarchy with symbols and indicators
- **Enhanced Spacing** - Better padding, margins, and content organization

## Version 0.2.1
**Date:** January 5, 2025

### Styling Improvements:
- ✅ **Typography System** - Professional font system with 4 carefully chosen fonts
- ✅ **Font Preview System** - Dedicated fonts.php page for testing and comparing fonts
- ✅ **Brand Class Implementation** - IM Fell English font with darker red color for branding
- ✅ **Gothic Color Scheme** - Sophisticated gradient background and color palette
- ✅ **Typography Hierarchy** - Clear font usage guidelines for different content types

### Font System:
- **Libre Baskerville** - Headers, titles, and section names (classic serif)
- **Source Serif Pro** - Body text, descriptions, and general content (professional)
- **IM Fell English** - Branding elements, game titles, and special headers (Old English)
- **Nosifer** - Warnings, errors, and special effects (blood-dripping with #ff0000)

### Design Improvements:
- **Gradient Background** - Four-color gradient (#000713, #2E5740, #17212D, #1D2523)
- **Color Palette** - Main red #780606, darker red #5a0202 for branding
- **Professional Header** - Copied header structure from main character creator
- **Font Preview Page** - Clean, focused preview of chosen font system
- **Typography Guidelines** - Clear usage instructions for each font

### Technical Improvements:
- Created comprehensive font preview system
- Implemented brand class with IM Fell English font
- Added gradient background system
- Updated color scheme throughout
- Created font usage documentation
- Enhanced visual hierarchy and readability

### User Experience Improvements:
- Professional typography system
- Clear visual hierarchy
- Consistent branding elements
- Better readability and contrast
- Gothic atmosphere with modern usability

### Next Development Priority:
- **Apply Font System to Main Character Creator** - Implement the new typography system
- **Enhanced Character Saving** - Add remaining fields to save functionality
- **Character Loading/Editing** - Allow users to load and edit existing characters
- **PDF Generation** - Implement character sheet PDF download

---

## Version 0.2.0
**Date:** January 4, 2025

### Major Features Added:
- ✅ **Complete Trait System** - All 3 categories (Physical, Social, Mental) with expanded trait lists
- ✅ **Negative Traits System** - +4 XP per negative trait with separate tracking
- ✅ **Physical Trait Categories** - Sidebar tracking for Agility, Strength, Dexterity, Reflexes, Appearance
- ✅ **Multiple Trait Selection** - Can select same trait multiple times
- ✅ **Real-time XP Tracking** - Live calculation and display
- ✅ **Trait Management** - Add/remove traits with visual feedback

### Trait Counts:
- **Physical:** 28 traits (organized by category)
- **Social:** 29 traits (organized by function)
- **Mental:** 35 traits (organized by mental function)
- **Negative Traits:** 13 total (Physical: 3, Social: 4, Mental: 6)

### Technical Improvements:
- External JavaScript organization (`js/script.js`)
- External CSS organization (`css/style.css`)
- Modular trait selection system
- Real-time progress tracking
- Responsive design elements

---

## Version 0.1.0
**Date:** Initial Development

### Features:
- Basic authentication system
- Database structure
- Character creation form UI (8 tabs)
- Basic trait selection (Physical only)
- XP tracking system

---

## Version Increment Guidelines:
- **Major (X.0.0):** Only when explicitly told by user
- **Minor (0.X.0):** New WORKING features that are fully functional
- **Patch (0.0.X):** Bug fixes, small improvements, or work-in-progress features

### Key Principle:
**If it doesn't work, it's a patch. If it works completely, it's a minor.**

### Next Development Priority:
- **Apply Font System to Main Character Creator** - Implement the new typography system
- **Enhanced Character Saving** - Add remaining fields to save functionality
- **Character Loading/Editing** - Allow users to load and edit existing characters
- **PDF Generation** - Implement character sheet PDF download