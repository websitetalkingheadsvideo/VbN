# Bootstrap Integration Documentation

## Overview

This document describes the Bootstrap 5.3.2 integration into the Valley by Night project. Bootstrap is integrated to streamline responsive layouts, improve accessibility, and reduce custom CSS while preserving pixel‑perfect visual fidelity with the existing design.

## Integration Date

Initial Integration: January 2025  
Active Conversion: November 4, 2024

## Bootstrap Version

**Bootstrap 5.3.2** (Latest stable at integration)

### CDN Sources (version‑pinned + SRI)

- Preconnect: `<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>`
- CSS: `<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="<SRI_HASH>" crossorigin="anonymous">`
- JS Bundle: `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="<SRI_HASH>" crossorigin="anonymous" defer></script>`

Notes:
- Replace `<SRI_HASH>` with the integrity value from the CDN page for the exact file/version.
- Keep versions pinned. Update intentionally (see Maintenance Notes).

## Files Modified

### Template Files
- `includes/header.php` - Added Bootstrap CSS CDN link
- `includes/footer.php` - Added Bootstrap JS bundle

### CSS Files
- `css/bootstrap-overrides.css` - **NEW FILE**: Minimal override layer (keep targeted)
- `css/global.css` - Notes on Bootstrap interaction and theme variables

### Backup Files
- `css/backup/pre-bootstrap/` - Complete backup of all CSS files before integration

## Integration Strategy

### Loading Order

1. **Bootstrap CSS** (CDN) – loaded first
2. **Bootstrap Overrides** (`bootstrap-overrides.css`) – minimal, targeted adjustments
3. **Custom CSS** (`global.css`, etc.) – project styles load last and take precedence

This order ensures:
- Bootstrap utilities are available
- Project styles override Bootstrap where needed (cascade priority)
- Overrides remain small; prefer theming via variables where possible

### Visual Fidelity

**Zero visual changes** – The integration maintains pixel‑for‑pixel fidelity through:
- Minimal override layer to align resets only where necessary
- Custom CSS loaded after Bootstrap
- Existing styles preserved exactly as they were

## Bootstrap Conversion Status

**Status:** In Progress  
**Last Updated:** November 4, 2024

### Completed Conversions
- ? Core components (header, footer)
- ? Login and registration pages
- ? Dashboard homepage
- ? Form components (labels, spacing)

See `docs/bootstrap-conversion-summary.md` for detailed conversion log.

## Available Bootstrap Utilities

Bootstrap utilities are **actively used** on converted components and available for future development:

### Layout Utilities
- `.container`, `.container-fluid` – page bounds
- `.row`, `.col-*` – flexbox grid (12‑column)
- `.g-*`, `.gx-*`, `.gy-*` – gutters
- `.d-flex`, `.d-grid`, `.d-block` – display utilities
- `.d-none`, `.d-md-block` – responsive visibility

### Spacing Utilities (v5 naming)
- Margins: `.m-*`, `.mt-*`, `.mb-*`, `.ms-*`, `.me-*`, `.mx-*`, `.my-*`
- Padding: `.p-*`, `.pt-*`, `.pb-*`, `.ps-*`, `.pe-*`, `.px-*`, `.py-*`

### Typography Utilities
- Alignment: `.text-start`, `.text-center`, `.text-end`
- Weight: `.fw-bold`, `.fw-semibold`, `.fw-normal`
- Size: `.fs-1` … `.fs-6`
- Color: `.text-*`

### Color & Borders
- Background: `.bg-*`
- Text: `.text-*`
- Border: `.border`, `.border-*`, `.border-0`

### Flexbox Utilities
- `.d-flex`, `.flex-row`, `.flex-column`
- `.justify-content-*`, `.align-items-*`, `.align-content-*`
- `.gap-*` – flex gap

## Custom CSS to Bootstrap Mapping

### Preferred Replacements

Prefer applying Bootstrap utilities in markup rather than `@extend`ing utility classes in Sass (avoids CSS bloat and specificity surprises).

#### Spacing
```html
<!-- Current: custom padding/margin in CSS -->
<!-- Future: utilities in markup -->
<div class="px-4 py-3">…</div>
```

#### Flexbox
```html
<!-- Current: .flex-container { display:flex; justify-content:space-between; } -->
<!-- Future: utilities in markup -->
<div class="d-flex justify-content-between align-items-center gap-2">…</div>
```

#### Grid
```html
<!-- Prefer Bootstrap’s flexbox grid via .row/.col-* -->
<div class="row g-3">
  <div class="col-12 col-md-6 col-xl-4">…</div>
  <div class="col-12 col-md-6 col-xl-4">…</div>
  <div class="col-12 col-md-6 col-xl-4">…</div>
</div>
```

Note: Bootstrap’s CSS Grid utilities are disabled by default in the CDN build. To use them, compile Sass with `$enable-cssgrid: true`.

## Responsive Breakpoints

Bootstrap breakpoints (min‑width):
- `<576px` (no infix, “xs” implicit)
- `sm` ≥ 576px
- `md` ≥ 768px
- `lg` ≥ 992px
- `xl` ≥ 1200px
- `xxl` ≥ 1400px

Existing custom breakpoints remain unchanged unless refactored:
- Mobile: `@media (max-width: 768px)`
- Tablet: `@media (max-width: 1023px)`
- Desktop: `@media (min-width: 1024px)`

## Bootstrap Components Available

The following Bootstrap components are available for future use:

- **Modals** – `.modal`, `.modal-dialog`, `.modal-content`
- **Dropdowns** – `.dropdown`, `.dropdown-menu`
- **Alerts** – `.alert`, `.alert-danger`, `.alert-success`
- **Buttons** – `.btn`, `.btn-primary`, `.btn-outline-*`
- **Forms** – `.form-label`, `.form-control`, `.form-check`, `.form-text` (no `.form-group` in v5)
- **Cards** – `.card`, `.card-body`, `.card-header`
- **Navbar** – `.navbar`, `.navbar-nav`, `.navbar-toggler`
- **Pagination** – `.pagination`, `.page-item`, `.page-link`

Important: Existing custom components remain unchanged. Use Bootstrap components for new features or during targeted refactors with verified visual parity.

JavaScript: Bootstrap 5 does not require jQuery. Prefer data attributes (`data-bs-*`) or the ES module/API.

## Testing & Verification

### Visual Fidelity Testing

All pages tested to ensure zero visual changes:
- ? `index.php` – Dashboard renders identically
- ? `dashboard.php` – Player/admin views unchanged
- ? `login.php` – Login page styling preserved
- ? `lotn_char_create.php` – Character creation form unchanged
- ? Header/footer – No layout shifts
- ? Responsive behavior – All breakpoints behave correctly

### JavaScript Compatibility

- ? All existing JavaScript functions work correctly
- ? Form interactions unchanged
- ? Modal/dropdown functionality preserved
- ? No conflicts with Bootstrap JS (no jQuery required)

### Forms & Validation (v5)

Structure:
```html
<form class="needs-validation" novalidate>
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input id="email" type="email" class="form-control" required aria-describedby="emailHelp">
    <div id="emailHelp" class="form-text">We’ll never share your email.</div>
    <div class="invalid-feedback">Please provide a valid email.</div>
  </div>
</form>
```

Behavior:
- Toggle `.was-validated` on submit and prevent default when invalid.
- Use `.is-invalid`/`.is-valid` as needed and pair feedback with `aria-describedby`.

## Rollback Procedure

If Bootstrap needs to be removed:

1. Remove CDN links:
   - Remove Bootstrap CSS link from `includes/header.php`
   - Remove Bootstrap JS script from `includes/footer.php`

2. Remove override file:
   - Remove `css/bootstrap-overrides.css` link from `includes/header.php`

3. Restore CSS backups:
   - Copy files from `css/backup/pre-bootstrap/` back to `css/`

4. Clean up Bootstrap notes:
   - Remove Bootstrap‑related comments from `css/global.css`

## Future Development Guidelines

### Using Bootstrap Utilities

When adding new features:
- ? Use Bootstrap utilities where appropriate
- ? Maintain gothic theme consistency
- ? Test responsive behavior
- ? Document utility usage in PRs

### Replacing Custom CSS

Before replacing existing custom CSS with Bootstrap:
- ? Verify pixel‑perfect visual match
- ? Test all responsive breakpoints
- ? Ensure JavaScript compatibility
- ? Update this documentation

### Adding Bootstrap Components

When using Bootstrap components:
- ? Customize colors to match gothic theme (CSS variables/Sass variables)
- ? Prefer variables over broad resets/overrides
- ? Maintain existing UX patterns
- ? Test accessibility (focus, labels, contrast)

## Maintenance Notes

### Bootstrap Updates

When updating Bootstrap:
1. Update pinned CDN links in `includes/header.php` and `includes/footer.php` (with SRI).
2. Review release notes; test for breaking changes.
3. Adjust override layer only if necessary (keep minimal).
4. Verify visual fidelity across pages and breakpoints.
5. Update this documentation with version and date.

### Custom CSS Changes

- Project styles load after Bootstrap and take precedence.
- Prefer utilities in markup; avoid `@extend`ing utility classes in Sass.
- For theme changes, prefer CSS variables or Sass variables over resets.

### Security & Performance

- Use `rel="preconnect"` to CDN and `defer` on Bootstrap JS.
- Lazy‑load images (`loading="lazy"`) and provide responsive sizes.
- Avoid jQuery for new code; Bootstrap 5 JS uses vanilla APIs.

## Support & Resources

- **Bootstrap Documentation**: https://getbootstrap.com/docs/5.3/
- **CDN Source**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/
- **Backup Location**: `css/backup/pre-bootstrap/`

---

**Last Updated**: November 2025  
**Integration Status**: ? Complete – Zero visual changes verified

