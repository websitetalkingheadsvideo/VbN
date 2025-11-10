---
title: Valley by Night Archive Cleanup Plan
created: 2025-11-09
---

## Current State
- Non-essential `.php`, `.json`, `.md`, and assorted image assets are scattered across `G:\VbN\`.
- `G:\VbN\archive\` already contains legacy content but lacks consistent manifest and reporting.
- Duplicate character images likely exist across `G:\VbN\uploads\characters\` and `G:\VbN\reference\Characters\Images\`.
- No centralized tooling exists to review archived files or restore them.

## Final State
- All inactive reference files relocated into structured archive subfolders with manifest entries.
- Duplicate images isolated, with `G:\VbN\uploads\characters\` retaining the authoritative copy.
- Generated artifacts: `G:\VbN\archive\cleanup_manifest.json`, `G:\VbN\archive\_purge_list.txt`, `G:\VbN\archive\_cleanup_report.md`, `G:\VbN\archive\_cleanup_log.json`, `G:\VbN\archive\image_duplicates.json`.
- New admin tool `G:\VbN\admin\tools\archive_dashboard.php` lists archived files and provides restore/approval actions.

## Files & Directories To Touch
- `G:\VbN\archive\` (create subdirectories, reports, logs).
- `G:\VbN\data\`, `G:\VbN\reference\`, `G:\VbN\docs\`, `G:\VbN\admin\` (source locations for archive candidates).
- `G:\VbN\uploads\characters\` and other image sources for deduplication.
- `G:\VbN\admin\tools\archive_dashboard.php` (new file).
- Supporting includes/utilities under `G:\VbN\includes\` if shared helpers are required.

## Task Checklist
- [ ] Inventory relevant files and flag active vs. inactive assets.
- [ ] Generate `cleanup_manifest.json` with active/inactive/duplicate metadata.
- [ ] Ensure archive folder structure exists with correct nesting.
- [ ] Move inactive assets into archive while logging each relocation.
- [ ] Compute image hashes to identify duplicates and retain primary copies.
- [ ] Produce duplicate report and update purge list entries.
- [ ] Build `_cleanup_report.md` and `_cleanup_log.json` with structured data.
- [ ] Implement `archive_dashboard.php` UI and supporting logic.
- [ ] Smoke test live site to confirm zero regressions after archival.

