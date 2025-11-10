# Add read-only narrative fields plan

## Current State
- Full Details view renders Status & Resources and then jumps straight to Custom Data.
- Narrative properties ppearance, iography, and 
otes are loaded with other character data but not displayed in Full Details mode.

## Final State
- Full Details mode shows read-only Bootstrap-styled fields for Appearance, Biography, and Notes between Status & Resources and Custom Data sections.
- Fields reuse existing styling conventions and escape content safely without adding fallbacks.

## Files To Change
- js/admin_panel.js

## Task Checklist
- [ ] Insert helper to render read-only textarea blocks with existing Bootstrap classes.
- [ ] Render Appearance, Biography, and Notes sections after Status & Resources.
- [ ] Verify markup respects escaping rules and no fallbacks are introduced.
