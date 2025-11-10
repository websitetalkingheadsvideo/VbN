# Character Field Work Merge Plan

## Current State
- `G:\VbN\reference\Characters\character_field_work.json` contains 40+ character stubs exported by `G:\VbN\database\export_character_field_workplan.php`. Several entries now have refreshed appearance, biography, notes, and image names that do not exist in MySQL yet.
- `G:\VbN\database\character_field_commit.php` can update a single character record when called with an `id` and payload, but there is no batching flow tied to `character_field_work.json`.
- `G:\VbN\reference\Characters\CHARACTER_IMPORT_PROCESS.md` documents how to import single-character sheets but does not cover feeding the consolidated field-work JSON back into MySQL.
- Existing characters originally imported live under `G:\VbN\reference\Characters\Added to Database\`, so we need to avoid re-import duplication when performing the merge.

## Final State
- A reproducible merge pipeline promotes updated narrative fields from `character_field_work.json` into the `characters` table, with MySQL-safe validation, transaction handling, and logging.
- The process supports a dry-run diff mode, executes real updates via prepared statements, copies missing character images into `G:\VbN\uploads\characters\`, and synchronizes the reference `characters.json` file.
- Documentation in `G:\VbN\reference\Characters\CHARACTER_IMPORT_PROCESS.md` describes the merge workflow, including how to trigger `https://vbn.talkingheads.video/database/character_field_merge.php` (planned) and post-merge verification steps in the admin UI.

## Files / Endpoints to Touch
- Source data: `G:\VbN\reference\Characters\character_field_work.json`
- Merge handler (new): `G:\VbN\database\character_field_merge.php`
- Existing helpers: `G:\VbN\database\character_field_commit.php`, `G:\VbN\database\export_character_field_workplan.php`, `G:\VbN\process_characters.py`
- Reference assets: `G:\VbN\reference\Characters\Images\` and `G:\VbN\uploads\characters\`
- Documentation: `G:\VbN\reference\Characters\CHARACTER_IMPORT_PROCESS.md`

## Task Checklist
1. **Diff & Backup**
   - Run `https://vbn.talkingheads.video/database/export_character_field_workplan.php?download=1` to capture a pre-merge snapshot.
   - Build a comparison script (Python or PHP) that highlights row-level differences between the download and `character_field_work.json`, flagging missing IDs or brand-new records.
   - Store both JSON files under `G:\VbN\reference\Characters\backups\YYYYMMDD\` (folder will be created by the script).

2. **Validate Merge Input**
   - Add a validator that ensures each JSON entry includes `id`, non-empty `appearance`, `biography`, `notes`, and a deployable `character_image` filename.
   - Normalize newlines, strip accidental markdown, and fail fast if any record violates schema expectations defined in `mysql.mdc` (no fallback behaviour).

3. **Implement Bulk Merge Endpoint**
   - Create `G:\VbN\database\character_field_merge.php` that loads the JSON, iterates entries, and updates records using prepared statements and a single transaction.
   - Allow a `dry_run=1` query flag that logs pending updates without committing.
   - Reuse the image-copy guard from `character_field_commit.php` and reuse `load_reference_json()` style helpers to sync `G:\VbN\reference\Characters\characters.json`.

4. **Execute Dry Run & Review Logs**
   - Invoke `https://vbn.talkingheads.video/database/character_field_merge.php?dry_run=1` and capture the JSON response.
   - Review field-by-field diffs, confirm IDs match expectations, and ensure no record attempts to overwrite populated MySQL fields without approval.

5. **Commit Merge & Verify**
   - Run the merge without `dry_run`, monitor success/failure output, and persist an execution log (timestamped JSON) beside the backups.
   - Spot-check the admin panel at `https://vbn.talkingheads.video/admin/admin_panel.php` for a few characters to confirm the narrative fields populated correctly.

6. **Document & Archive**
   - Update `CHARACTER_IMPORT_PROCESS.md` with explicit instructions for the field-work merge loop, including the diff/backup steps and new endpoint usage.
   - Move the processed `character_field_work.json` into a dated archive folder and regenerate a fresh export for the next editing cycle.


