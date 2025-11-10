# Agents Page Styling + Navigation Plan

## Current State
- `G:\VbN\admin\agents.php` uses a lightweight Bootstrap layout with inline styles and lacks the rich admin theme classes seen in `G:\VbN\admin\admin_panel.php`.
- Admin navigation buttons are defined within `G:\VbN\admin\admin_panel.php`; no entry exists for the Agents page.

## Target State
- Agents page reuses the shared admin container, typography, and card/button styles so it visually matches the dashboard.
- An \"Agents\" summary row sits between the character stats and Story Questionnaire sections, housing cards/links for each agent (initially the Agents dashboard itself plus Laws Agent, with room for future entries).
- Header no longer contains a Laws Agent link; those links live in the new Agents row instead.
- Admin navigation includes a new “Agents” button that routes to `admin/agents.php` and mirrors existing button markup and ordering.

## Files to Update
- `G:\VbN\admin\agents.php`
- `G:\VbN\admin\admin_panel.php`

## Task Checklist
- [ ] Taskmaster #53 — Audit layout differences
  - [ ] Subtask 53.1 — Capture admin panel layout patterns
  - [ ] Subtask 53.2 — Document agents page discrepancies
- [ ] Taskmaster #54 — Implement approved adjustments
  - [ ] Subtask 54.1 — Refactor agents page layout and agent summary row
  - [ ] Subtask 54.2 — Add Agents nav button and remove header Laws Agent link
  - [ ] Subtask 54.3 — Verify parity and routing


