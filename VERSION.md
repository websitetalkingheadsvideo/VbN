# LOTN Character Creator - Version History

## Version 0.2.7 (Current)
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
