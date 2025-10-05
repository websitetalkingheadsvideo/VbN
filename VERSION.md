# LOTN Character Creator - Version History

## Version 0.4.0 (Current)
**Date:** January 5, 2025

### Major Features Added:
- ✅ **Complete Backgrounds System** - All 10 background categories with full functionality
- ✅ **Background Selection Interface** - Interactive buttons with level selection (1-5)
- ✅ **Background Progress Tracking** - Real-time progress bars and count displays
- ✅ **Background XP Calculation** - First 5 background points free, additional points cost 2 XP each
- ✅ **Background Details System** - Text areas for players to describe their specific backgrounds
- ✅ **Auto-calculated Generation Background** - Generation background automatically calculated from Basic Info
- ✅ **Background Database Integration** - Backgrounds saved to database with detailed descriptions
- ✅ **Character Summary Integration** - Backgrounds included in character summary and sheet

### Background Categories:
- **Allies** - Friends, contacts, and people who will help you
- **Contacts** - Information networks, informants, and intelligence sources
- **Fame** - Public recognition, reputation, and celebrity status
- **Generation** - Auto-calculated from Basic Info generation selection
- **Herd** - Regular sources of blood and sustenance
- **Influence** - Political power and social influence
- **Mentor** - Teacher, guide, or patron providing knowledge and protection
- **Resources** - Money, property, equipment, and material wealth
- **Retainers** - Servants, assistants, and loyal followers
- **Status** - Social standing and position within Kindred or mortal society

### Technical Improvements:
- Added comprehensive backgrounds HTML structure with progress bars
- Implemented JavaScript functions for background selection and display updates
- Added CSS styling for backgrounds system with professional appearance
- Integrated backgrounds XP calculation into the main XP system
- Added background details textareas with event listeners
- Updated database schema to include background details column
- Enhanced character summary and sheet generation to include background details
- Fixed duplicate generation field by auto-calculating from Basic Info

### User Experience Improvements:
- Clear visual feedback for background selection with progress bars
- Helpful placeholder text with examples for each background type
- Real-time XP calculation updates as backgrounds are selected
- Professional styling consistent with the rest of the application
- Background details appear in character summary and sheet

### Next Development Priority:
- **Site Look & Design** - Improve overall visual appearance and user interface
- **Enhanced Character Saving** - Add remaining fields to save functionality
- **Character Loading/Editing** - Allow users to load and edit existing characters
- **PDF Generation** - Implement character sheet PDF download

---

## Version 0.3.0
**Date:** January 5, 2025

### Major Features Added:
- ✅ **Working Character Saving System** - Basic character saving functionality with database integration
- ✅ **Character Name Saving** - Characters can be saved with names and basic information
- ✅ **Database Integration** - Characters are saved to MySQL database with proper error handling
- ✅ **User Feedback System** - Success/error notifications for save operations
- ✅ **Simplified Validation** - Only character name required for saving, all other fields optional
- ✅ **User Display** - Shows logged-in username in header
- ✅ **Finalize Character System** - "Finalize Character" button with confirmation popup
- ✅ **Character Sheet Modal** - Modal for displaying and downloading character sheets

### Technical Improvements:
- Created working save script (`test_simple_insert.php`) for character saving
- Added comprehensive error handling and debugging for save operations
- Implemented notification system for user feedback
- Added user display in header with proper styling
- Created finalization modal system with character preview
- Enhanced JavaScript with robust error handling for API calls
- Simplified form validation to only require character name

### Current Status:
- ✅ Character creation form fully functional
- ✅ Database connection working
- ✅ Basic character saving working (name only)
- ✅ User authentication and display working
- ✅ All UI components functional
- 🔄 Complex field saving needs debugging (technical issue with SQL statements)

---

## Version 0.2.9
**Date:** January 4, 2025

### Major Features Added:
- ✅ **Database Integration for Discipline Powers** - Moved all discipline data from hardcoded JavaScript to MySQL database
- ✅ **Dynamic Data Loading** - Discipline powers, clans, and clan-discipline access now loaded from database via API
- ✅ **Fallback System** - Automatic fallback to hardcoded data if database is unavailable
- ✅ **Comprehensive Database Schema** - New tables for disciplines, discipline_powers, clans, and clan_disciplines
- ✅ **API Endpoints** - RESTful API for accessing discipline data with multiple query options
- ✅ **Data Population Scripts** - Automated scripts to populate database with all discipline data
- ✅ **Testing and Documentation** - Complete setup guide and test scripts for database integration

### Database Structure:
- **disciplines** - Master list of all 22 disciplines with categories
- **discipline_powers** - All 110 individual powers (5 per discipline) with descriptions
- **clans** - All 14 clans with descriptions, weaknesses, themes, and availability
- **clan_disciplines** - Complete mapping of which disciplines each clan can access
- **character_discipline_powers** - New table for storing character's selected discipline powers

### Technical Improvements:
- Added `loadDisciplineData()` function to fetch data from database
- Added `loadFallbackData()` function for offline operation
- Created `api_disciplines.php` with multiple endpoint options
- Created `populate_discipline_data.php` for database setup
- Created `test_database_integration.php` for verification
- Updated JavaScript to use async/await for data loading
- Enhanced error handling and logging

### Benefits:
- **Maintainability** - Discipline data can be updated without modifying JavaScript
- **Scalability** - Easy to add new disciplines, powers, or clans
- **Consistency** - Single source of truth for all discipline data
- **Admin Control** - Admins can modify discipline data through database
- **Performance** - Data is loaded once and cached in JavaScript

## Version 0.2.8
**Date:** January 4, 2025

### Major Features Added:
- ✅ **Individual Discipline Access Control** - Each discipline button is now enabled/disabled based on clan-specific access
- ✅ **Comprehensive Clan-Discipline Mapping** - Complete mapping of which disciplines each clan can access
- ✅ **Visual Feedback for Disabled Disciplines** - Disabled discipline buttons are grayed out with tooltips explaining why they're unavailable
- ✅ **Automatic Invalid Discipline Clearing** - When switching clans, any selected disciplines the new clan can't access are automatically removed

### Clan-Specific Discipline Access:
- **Assamite:** Animalism, Celerity, Obfuscate, Quietus
- **Brujah:** Celerity, Potence, Presence
- **Caitiff:** All disciplines (can access any discipline)
- **Followers of Set:** Animalism, Obfuscate, Presence, Serpentis
- **Gangrel:** Animalism, Fortitude, Protean
- **Giovanni:** Dominate, Fortitude, Necromancy, Mortis
- **Lasombra:** Dominate, Obfuscate, Obtenebration
- **Malkavian:** Auspex, Dementation, Obfuscate
- **Nosferatu:** Animalism, Fortitude, Obfuscate
- **Ravnos:** Animalism, Chimerstry, Fortitude
- **Toreador:** Auspex, Celerity, Presence
- **Tremere:** Auspex, Dominate, Thaumaturgy, Obtenebration, Daimoinon, Melpominee, Valeren
- **Tzimisce:** Animalism, Auspex, Dominate, Vicissitude
- **Ventrue:** Dominate, Fortitude, Presence

### Technical Improvements:
- Added `updateDisciplineButtonStates()` function to enable/disable individual discipline buttons
- Added `clearInvalidDisciplines()` function to remove disciplines the clan can't access
- Enhanced `handleClanChange()` function with comprehensive clan-discipline mapping
- Added visual feedback with opacity changes and tooltips for disabled disciplines
- Integrated discipline button state management into clan selection workflow

### User Experience Improvements:
- Clear visual indication of which disciplines are available to each clan
- Helpful tooltips explaining why certain disciplines are unavailable
- Automatic cleanup of invalid selections when switching clans
- Consistent behavior across all discipline categories

## Version 0.2.7
**Date:** January 4, 2025

### Major Features Added:
- ✅ **Dynamic Discipline Section Visibility** - Blood Sorcery and Advanced Disciplines sections now hide/show based on clan selection
- ✅ **Clan-Specific Discipline Access** - Only clans with access to specific discipline categories can see those sections
- ✅ **Automatic Discipline Clearing** - When switching clans, disciplines from hidden sections are automatically cleared
- ✅ **Real-time UI Updates** - Discipline sections update immediately when clan selection changes

### Discipline Access Logic:
- **Blood Sorcery:** Only Giovanni, Tremere, Caitiff can access
- **Advanced Disciplines:** Assamite, Followers of Set, Lasombra, Malkavian, Ravnos, Tremere, Tzimisce, Caitiff can access
- **Clan Disciplines:** All clans can access (always visible)

### Technical Improvements:
- Added `handleClanChange()` function to manage discipline section visibility
- Added `clearDisciplinesByCategory()` function to clear disciplines when sections are hidden
- Added `initializeDisciplineSections()` function for proper initialization
- Updated HTML with `onchange="handleClanChange()"` on clan dropdown
- Added `data-category` attributes to discipline sections for JavaScript targeting
- Integrated discipline visibility into DOMContentLoaded event

### Bug Fixes:
- Fixed Ravnos incorrectly seeing Blood Sorcery disciplines
- Fixed Giovanni incorrectly seeing Advanced Disciplines
- Ensured proper discipline clearing when switching between incompatible clans

## Version 0.2.6
**Date:** January 4, 2025

### Major Features Added:
- ✅ **Clan Availability System** - Updated clan guide to reflect PC vs Admin Approval requirements
- ✅ **PC Available Clans** - Brujah, Caitiff, Gangrel, Malkavian, Nosferatu, Toreador, Tremere, Ventrue
- ✅ **Admin Approval Clans** - Assamite, Followers of Set, Giovanni, Lasombra, Ravnos, Tzimisce
- ✅ **Visual Availability Indicators** - Color-coded availability status (green for PC Available, red for Admin Approval)
- ✅ **Updated Character Creation Tips** - Reflects new availability system for new vs experienced players

### Technical Improvements:
- Updated clan table header from "Difficulty" to "Availability"
- Added CSS classes for visual distinction between availability types
- Updated character creation tips to reflect Camarilla vs non-Camarilla clan availability
- Maintained all existing clan information while clarifying access requirements

### Game Balance:
- Clear distinction between PC-available Camarilla clans and admin-approval required clans
- Helps new players understand which clans are appropriate for their first characters
- Provides guidance for experienced players on advanced clan options

## Version 0.2.5
**Date:** January 4, 2025

### Major Features Added:
- ✅ **Complete Clan Guide System** - Comprehensive clan information with help modal
- ✅ **Clan Help Button** - "?" button next to clan dropdown in Basic Info tab
- ✅ **Clan Information Modal** - Complete clan table with disciplines, weaknesses, themes, and difficulty
- ✅ **Clan Icons in Dropdown** - Visual clan icons in the clan selection dropdown
- ✅ **Character Creation Tips** - Beginner and advanced player guidance in modal
- ✅ **Reusable Modal System** - Both clan and discipline guides use the same modal framework

### Clan Guide Features:
- **All 14 Clans:** Complete information for all core clans plus Caitiff
- **Comprehensive Table:** Disciplines, weaknesses, themes, and difficulty levels
- **Visual Icons:** Text-based clan symbols (⚔️ Assamite, ✊ Brujah, etc.)
- **Color-Coded Information:** Different colors for disciplines, weaknesses, themes, and difficulty
- **Character Creation Tips:** Separate guidance for new and experienced players
- **Responsive Design:** Works on all screen sizes with scrollable content

### Technical Improvements:
- Extended modal system to support multiple guides
- New CSS styles for clan table with color coding
- Updated JavaScript to handle both clan and discipline modals
- Enhanced user experience with visual clan identification
- Consistent design language across all help systems

---

## Version 0.2.4
**Date:** January 4, 2025

### Major Features Added:
- ✅ **Complete Disciplines System** - All 3 categories (Clan, Blood Sorcery, Advanced) with full functionality
- ✅ **Discipline Selection Interface** - Interactive buttons with multiple selection support
- ✅ **Discipline Progress Tracking** - Real-time progress bars and count displays
- ✅ **Discipline XP Calculation** - First 3 discipline dots free, 4-5 cost 3 XP each
- ✅ **Discipline Management** - Add/remove discipline dots with visual feedback
- ✅ **Clan Discipline Highlighting** - Special orange styling for clan disciplines
- ✅ **5-Dot Maximum Per Discipline** - Enforced maximum of 5 dots for any single discipline

### Discipline Categories:
- **Clan Disciplines:** 9 disciplines (Animalism, Auspex, Celerity, Dominate, Fortitude, Obfuscate, Potence, Presence, Protean)
- **Blood Sorcery:** 3 disciplines (Thaumaturgy, Necromancy, Koldunic Sorcery)
- **Advanced Disciplines:** 10 disciplines (Obtenebration, Chimerstry, Dementation, Quietus, Vicissitude, Serpentis, Daimoinon, Melpominee, Valeren, Mortis)

### Technical Improvements:
- Extended JavaScript functionality for disciplines
- New CSS styles for discipline interface with clan highlighting
- Updated form validation to include discipline requirements
- Enhanced XP tracking system with discipline costs
- Consistent UI/UX with existing trait and ability systems

---

## Version 0.2.3
**Date:** January 4, 2025

### Major Features Added:
- ✅ **Help Button System** - Optional discipline-ability guide in Abilities tab
- ✅ **Discipline Guide Modal** - Complete chart with all 22 disciplines and recommendations
- ✅ **Enhanced User Experience** - Optional reference without cluttering interface
- ✅ **Professional Modal Design** - Color-coded table with responsive design

### Help System Features:
- **Help Button** - Small "?" button in Abilities tab info box
- **Modal Window** - Full-screen overlay with discipline-ability chart
- **Color-Coded Table** - Easy-to-read recommendations for each discipline
- **Responsive Design** - Works on all screen sizes with scrollable content
- **Multiple Close Options** - X button, Close button, or click outside

### Technical Improvements:
- Modal CSS with professional styling
- JavaScript modal management functions
- Responsive table design with sticky headers
- Hover effects and smooth animations
- Integration with existing ability selection system

---

## Version 0.2.2
**Date:** January 4, 2025

### Major Features Added:
- ✅ **Complete Abilities System** - All 3 categories (Physical, Social, Mental) with full functionality, plus Optional abilities
- ✅ **Ability Selection Interface** - Interactive buttons with multiple selection support
- ✅ **Ability Progress Tracking** - Real-time progress bars and count displays
- ✅ **Ability XP Calculation** - First 3 ability dots free, 4-5 cost 2 XP each
- ✅ **Ability Management** - Add/remove ability dots with visual feedback
- ✅ **Updated XP System** - Integrated abilities into total XP tracking
- ✅ **5-Dot Maximum Per Ability** - Enforced maximum of 5 dots for any single ability

### Ability Categories:
- **Physical Abilities:** 8 abilities (Athletics, Brawl, Dodge, Firearms, Melee, Security, Stealth, Survival)
- **Social Abilities:** 9 abilities (Animal Ken, Empathy, Expression, Intimidation, Leadership, Subterfuge, Streetwise, Etiquette, Performance)
- **Mental Abilities:** 10 abilities (Academics, Computer, Finance, Investigation, Law, Linguistics, Medicine, Occult, Politics, Science)
- **Optional Abilities:** 5 abilities (Alertness, Awareness, Drive, Crafts, Firecraft)

### Technical Improvements:
- Extended JavaScript functionality for abilities
- New CSS styles for ability interface
- Updated form validation to include abilities
- Enhanced XP tracking system
- Consistent UI/UX with trait system

---

## Version 0.2.1
**Date:** January 4, 2025

### Major Features Added:
- ✅ **XAMPP Integration** - Complete local development environment setup
- ✅ **Python API Backend** - Flask API server for character management
- ✅ **Database Integration** - Full MySQL database with all character tables
- ✅ **User Authentication** - Login system with role-based access control
- ✅ **Character Ownership** - Users own their characters, admin sees all
- ✅ **Admin Panel** - Complete admin interface for character management
- ✅ **Database Setup Scripts** - Automated database creation and user setup

### Technical Infrastructure:
- **XAMPP Configuration** - Apache serving from project directory
- **Python Flask API** - RESTful endpoints for character CRUD operations
- **MySQL Database** - Complete schema with 12 character-related tables
- **User Management** - Admin and regular user roles with proper access control
- **Development Tools** - Automated setup scripts and configuration files

### Database Schema:
- **users** - User accounts with role-based access
- **characters** - Main character records with user ownership
- **character_traits** - Character trait assignments
- **character_abilities** - Character ability records
- **character_disciplines** - Vampire discipline tracking
- **character_backgrounds** - Character background points
- **character_merits_flaws** - Merits and flaws system
- **character_morality** - Humanity and virtue tracking
- **character_derangements** - Mental derangement records
- **character_equipment** - Equipment and items
- **character_influences** - Social influence tracking
- **character_rituals** - Thaumaturgy/ritual knowledge
- **character_status** - Current game status

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
- **Major (X.0.0):** Complete new features or major rewrites
- **Minor (0.X.0):** New functionality or significant improvements
- **Patch (0.0.X):** Bug fixes or small improvements

### Next Version (0.3.0):
- Complete remaining tabs (Abilities, Disciplines, Backgrounds, Morality, Merits & Flaws, Final Details)
- Character loading/editing functionality
- Database integration for character saving
