# VbN Cleanup Plan

## Current State
- Protected pages not yet enumerated.
- Archive manifest and reporting files absent or outdated.
- No inventory of PHP, JSON, MD, or image assets for cleanup.

## Final State
- Complete protected link graph saved under `G:\VbN\archive\cleanup_manifest.json`.
- Classified asset inventory distinguishing protected, active, inactive, and duplicate candidates.
- Archive reports/logs scaffolded without moving files yet.

## Files To Touch
- `G:\VbN\archive\cleanup_manifest.json`
- `G:\VbN\archive\_cleanup_report.md`
- `G:\VbN\archive\_cleanup_log.json`
- `G:\VbN\archive\_purge_list.txt` (planned)
- Supporting scripts or notes as needed.

## Task Checklist
- [ ] Crawl public root and admin panel to capture recursive link graph.
- [ ] Map URLs to local filesystem paths and mark protected entries.
- [ ] Enumerate project files by extension class and compare with protected set.
- [ ] Draft manifest sections for protected, active, inactive, and potential duplicates.
- [ ] Outline archive report/log structures without relocating files.

