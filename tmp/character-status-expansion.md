# Character Status Expansion Plan

## Current State
- `characters` table lacks `status` and `camarilla_status` columns; application and JSON schema only know existing fields.
- Admin/UI and exports do not expose or persist the new fields.

## Final State
- Database includes `status` (default `active`) and `camarilla_status` (default `Unknown`) columns populated for all rows.
- JSON character template and exports include the new fields with matching defaults.
- Admin/editor UI supports viewing and editing both fields, persisting changes end-to-end.

## Files To Touch
- Database migration scripts under `G:\VbN\database` or equivalent schema management location.
- Character JSON template in `G:\VbN\reference` or data export utilities.
- Admin panel code (likely PHP/JS under `G:\VbN\admin` or related directories).

## Task Checklist
- [ ] Inspect current `characters` table schema.
- [ ] Implement migration to add/populate columns safely.
- [ ] Update JSON template and related serialization code.
- [ ] Expose new fields in admin/editor UI and persistence layers.
- [ ] Verify new and existing records through tests/exports.
