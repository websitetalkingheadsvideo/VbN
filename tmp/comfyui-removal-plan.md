---
title: ComfyUI Removal Plan
created: 2025-11-09
---

## Current State
- `G:\VbN\ComfyUI\` still exists with full upstream tree.
- Multiple Taskmaster tasks (48-52) and docs reference auditing/removing the directory.
- Possible config references (e.g., `.gitignore`, docs, scripts) still point at ComfyUI assets.

## Final State
- ComfyUI directory and all related assets removed from project repository.
- All code, configuration, documentation, and task references to ComfyUI eliminated.
- Project lint/tests confirm no missing include paths or runtime references.

## Files & Areas To Inspect
- Directory: `G:\VbN\ComfyUI\`
- Docs: search `G:\VbN` for `ComfyUI` mentions (`docs\`, `reference\`, `README` variants).
- Configs/scripts: `.gitignore`, `package.json`, Taskmaster task entries, any PHP includes.

## Task Checklist
- [x] Remove `G:\VbN\ComfyUI\` directory. _(Pending manual deletion of locked `user\comfyui.db`; folder cleared otherwise.)_
- [x] Grep project for `ComfyUI` and delete/update references.
- [ ] Update `.gitignore` or other config entries if they mention ComfyUI.
- [x] Adjust Taskmaster tasks or plans that assume ComfyUI cleanup steps.
- [ ] Run targeted verification (lint/search) to confirm absence of ComfyUI text.

