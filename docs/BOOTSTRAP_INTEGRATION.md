# Bootstrap Integration Documentation

## Overview

This document describes the Bootstrap 5.3.2 integration into the Valley by Night project. Bootstrap has been integrated as a foundation for future CSS maintenance while maintaining pixel-for-pixel visual fidelity with the existing design.

## Integration Date

Initial Integration: January 2025  
Active Conversion: November 4, 2024

## Bootstrap Version

**Bootstrap 5.3.2** (Latest stable as of integration)

### CDN Sources

- **CSS**: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css`
- **JS**: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js`

## Files Modified

### Template Files
- `includes/header.php` - Added Bootstrap CSS CDN link
- `includes/footer.php` - Added Bootstrap JS bundle

### CSS Files
- `css/bootstrap-overrides.css` - **NEW FILE**: Bootstrap compatibility layer
- `css/global.css` - Added Bootstrap compatibility comments

### Backup Files
- `css/backup/pre-bootstrap/` - Complete backup of all CSS files before integration

## Integration Strategy

### Loading Order

1. **Bootstrap CSS** (CDN) - Loaded first
2. **Bootstrap Overrides** (`bootstrap-overrides.css`) - Neutralizes Bootstrap reset
3. **Custom CSS** (`global.css`, etc.) - Existing styles take precedence

This order ensures:
- Bootstrap utilities are available
- Bootstrap's default styles don't interfere with existing design
- Custom styles override Bootstrap where needed

### Visual Fidelity

**Zero visual changes** - The integration maintains pixel-for-pixel fidelity with the original design through:
- Bootstrap override layer neutralizing reset styles
- Custom CSS loaded after Bootstrap (cascade priority)
- Existing styles preserved exactly as they were

## Bootstrap Conversion Status

**Status:** In Progress  
**Last Updated:** November 4, 2024

### Completed Conversions
- ✅ Core components (header, footer)
- ✅ Login and registration pages
- ✅ Dashboard homepage
- ✅ Form components (labels, spacing)

See `docs/bootstrap-conversion-summary.md` for detailed conversion log.

## Available Bootstrap Utilities

Bootstrap utilities are **actively being used** on converted components and available for future development:

### Layout Utilities
- `.container`, `.container-fluid` - For new layouts
- `.row`, `.col-*` - Grid system (12-column)
- `.d-flex`, `.d-grid`, `.d-block` - Display utilities
- `.d-none`, `.d-md-block` - Responsive visibility

### Spacing Utilities
- `.m-*`, `.mt-*`, `.mb-*`, `.ml-*`, `.mr-*` - Margins
- `.p-*`, `.pt-*`, `.pb-*`, `.pl-*`, `.pr-*` - Padding
- `.mx-*`, `.my-*`, `.px-*`, `.py-*` - Axis-specific spacing

### Typography Utilities
- `.text-center`, `.text-left`, `.text-right` - Alignment
- `.text-muted`, `.text-primary` - Colors
- `.fw-bold`, `.fw-normal` - Font weight
- `.fs-1` through `.fs-6` - Font sizes

### Color Utilities
- `.bg-*` - Background colors
- `.text-*` - Text colors
- `.border-*` - Border colors

### Flexbox Utilities
- `.d-flex`, `.flex-row`, `.flex-column`
- `.justify-content-*`, `.align-items-*`
- `.gap-*` - Gap spacing

## Custom CSS to Bootstrap Mapping

### Potential Future Replacements

The following custom CSS patterns could potentially be replaced with Bootstrap utilities in future refactoring:

#### Spacing Patterns
```css
/* Current: Custom padding/margin */
.element { padding: 15px 30px; }

/* Future: Bootstrap utility */
.element { @extend .px-4; @extend .py-3; }
/* Or: <div class="px-4 py-3"> */
```

#### Flexbox Patterns
```css
/* Current: Custom flex */
.flex-container { display: flex; justify-content: space-between; }

/* Future: Bootstrap utility */
.flex-container { @extend .d-flex; @extend .justify-content-between; }
/* Or: <div class="d-flex justify-content-between"> */
```

#### Grid Patterns
```css
/* Current: CSS Grid */
.grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); }

/* Future: Bootstrap grid */
/* <div class="row">
     <div class="col-md-6 col-lg-4">...</div>
   </div> */
```

**Note**: These are examples for future consideration. No replacements have been made during initial integration.

## Responsive Breakpoints

Bootstrap breakpoints (available but not conflicting):
- `xs`: < 576px
- `sm`: ≥ 576px
- `md`: ≥ 768px
- `lg`: ≥ 992px
- `xl`: ≥ 1200px
- `xxl`: ≥ 1400px

Existing custom breakpoints remain unchanged:
- Mobile: `@media (max-width: 768px)`
- Tablet: `@media (max-width: 1023px)`
- Desktop: `@media (min-width: 1024px)`

## Bootstrap Components Available

The following Bootstrap components are available for future use:

- **Modals** - `.modal`, `.modal-dialog`, etc.
- **Dropdowns** - `.dropdown`, `.dropdown-menu`
- **Alerts** - `.alert`, `.alert-success`, etc.
- **Buttons** - `.btn`, `.btn-primary`, etc.
- **Forms** - `.form-control`, `.form-group`, etc.
- **Cards** - `.card`, `.card-body`, etc.
- **Navbar** - `.navbar`, `.navbar-nav`, etc.
- **Pagination** - `.pagination`, `.page-item`, etc.

**Important**: Existing custom components remain unchanged. Bootstrap components should only be used for new features.

## Testing & Verification

### Visual Fidelity Testing

All pages tested to ensure zero visual changes:
- ✅ `index.php` - Dashboard renders identically
- ✅ `dashboard.php` - Player/admin views unchanged
- ✅ `login.php` - Login page styling preserved
- ✅ `lotn_char_create.php` - Character creation form unchanged
- ✅ Header/footer rendering - No layout shifts
- ✅ Responsive behavior - All breakpoints work correctly

### JavaScript Compatibility

- ✅ All existing JavaScript functions work correctly
- ✅ Form interactions unchanged
- ✅ Modal/dropdown functionality preserved
- ✅ No conflicts with Bootstrap JS

## Rollback Procedure

If Bootstrap needs to be removed:

1. **Remove CDN links**:
   - Remove Bootstrap CSS link from `includes/header.php`
   - Remove Bootstrap JS script from `includes/footer.php`

2. **Remove override file**:
   - Delete `css/bootstrap-overrides.css` link from `includes/header.php`

3. **Restore CSS backups**:
   - Copy files from `css/backup/pre-bootstrap/` back to `css/`

4. **Remove Bootstrap comments**:
   - Clean up Bootstrap-related comments from `css/global.css`

## Future Development Guidelines

### Using Bootstrap Utilities

When adding new features:
- ✅ Use Bootstrap utilities where appropriate
- ✅ Maintain gothic theme consistency
- ✅ Test responsive behavior
- ✅ Document utility usage

### Replacing Custom CSS

Before replacing existing custom CSS with Bootstrap:
- ✅ Verify visual match (pixel-perfect)
- ✅ Test all responsive breakpoints
- ✅ Ensure JavaScript compatibility
- ✅ Update this documentation

### Adding Bootstrap Components

When using Bootstrap components:
- ✅ Customize colors to match gothic theme
- ✅ Override Bootstrap defaults with custom CSS
- ✅ Maintain existing UX patterns
- ✅ Test accessibility

## Maintenance Notes

### Bootstrap Updates

When updating Bootstrap version:
1. Update CDN links in `header.php` and `footer.php`
2. Test for breaking changes
3. Update override layer if needed
4. Verify visual fidelity
5. Update this documentation

### Custom CSS Changes

When modifying custom CSS:
- Existing styles take precedence over Bootstrap
- Bootstrap utilities won't interfere with custom styles
- New styles can leverage Bootstrap utilities if desired

## Support & Resources

- **Bootstrap Documentation**: https://getbootstrap.com/docs/5.3/
- **CDN Source**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/
- **Backup Location**: `css/backup/pre-bootstrap/`

---

**Last Updated**: January 2025  
**Integration Status**: ✅ Complete - Zero visual changes verified

