# LOTN Character Creator - Version History

## Version 0.3.0 (Current)
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
