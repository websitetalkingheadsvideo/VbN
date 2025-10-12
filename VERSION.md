# LOTN Character Creator - Version History

## Version 0.6.0 (Current)
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
- Added `status` ENUM column: 'draft', 'finalized', 'active', 'dead', 'missing'
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
