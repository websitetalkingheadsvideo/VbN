# Bootstrap Conversion Inventory

**Generated:** November 4, 2024  
**Project:** VbN (Vampire by Night)  
**Bootstrap Version:** 5.3.2 (already integrated via CDN)

## File Inventory

### PHP Templates (Root Directory)
- `index.php` - Home page with dashboard
- `login.php` - Login page
- `register.php` - Registration page
- `dashboard.php` - User dashboard
- `lotn_char_create.php` - Character creation form
- `questionnaire.php` - Character questionnaire
- `load_character.php` - Character loading page
- `save_character.php` - Character saving handler
- `users.php` - User management
- `chat.php` - Chat interface
- `cc.php` - Character creation (alternate)
- `404.php` - Error page
- `500.php` - Error page

### PHP Templates (Admin Directory)
- `admin/admin_panel.php` - Main admin panel
- `admin/admin_locations.php` - Location management
- `admin/admin_items.php` - Items management
- `admin/admin_equipment.php` - Equipment management
- `admin/laws_agent.php` - Laws Agent interface
- `admin/questionnaire_admin.php` - Questionnaire admin
- Plus 40+ additional admin PHP files

### Shared Components
- `includes/header.php` - Site header
- `includes/footer.php` - Site footer

### CSS Files (39 total)
- `css/global.css` - Global styles
- `css/header.css` - Header styles
- `css/footer.css` - Footer styles
- `css/login.css` - Login page styles
- `css/dashboard.css` - Dashboard styles
- `css/bootstrap-overrides.css` - Bootstrap neutralization
- Plus 33 additional CSS files

## Files with Most Custom CSS (Priority Candidates)

1. **`includes/header.php`** - Custom flex layouts, container max-width
2. **`includes/footer.php`** - Custom flex column layout
3. **`login.php`** - Custom container, inline styles, form groups
4. **`index.php`** - Custom grid systems, stat cards, action grids
5. **`css/dashboard.css`** - Extensive custom layouts (stats-panel, action-grid)
6. **`css/header.css`** - Custom flex containers, responsive breakpoints
7. **`css/login.css`** - Custom form styling, flex layouts

## Total Files to Audit
- **PHP Templates:** ~60 files (root + admin)
- **CSS Files:** 39 files
- **Total:** ~99 files requiring audit

## Bootstrap Integration Status
- ✅ Bootstrap 5.3.2 loaded via CDN
- ✅ Bootstrap utilities available
- ⚠️ Bootstrap defaults neutralized in `bootstrap-overrides.css`
- ❌ Custom CSS still heavily used instead of Bootstrap utilities

