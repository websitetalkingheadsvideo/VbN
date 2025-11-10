# Cleanup Inventory Snapshot

## PHP Entry Points (Protected / Active)
- `G:\VbN\index.php`
- `G:\VbN\login.php`
- `G:\VbN\register.php`
- `G:\VbN\logout.php`
- `G:\VbN\questionnaire.php`
- `G:\VbN\lotn_char_create.php`
- `G:\VbN\character_sheet.php`
- `G:\VbN\admin\admin_panel.php`
- `G:\VbN\admin\agents.php`
- `G:\VbN\admin\admin_sire_childe.php`
- `G:\VbN\admin\admin_sire_childe_enhanced.php`
- `G:\VbN\admin\admin_equipment.php`
- `G:\VbN\admin\admin_items.php`
- `G:\VbN\admin\admin_locations.php`
- `G:\VbN\admin\questionnaire_admin.php`
- `G:\VbN\admin\admin_npc_briefing.php`
- `G:\VbN\admin\boon_ledger.php`
- `G:\VbN\admin\laws_agent.php`

## Shared Includes (Must Stay)
- `G:\VbN\includes\header.php`
- `G:\VbN\includes\footer.php`
- `G:\VbN\includes\connect.php`
- `G:\VbN\includes\auth_bypass.php`
- `G:\VbN\includes\version.php`
- `G:\VbN\includes\urls.php`

## Runtime Config Assets
- `G:\VbN\config\login_disable.json`
- `G:\VbN\config\auth_bypass.json`
- `G:\VbN\VERSION.md`

## CSS / JS Dependencies
- `G:\VbN\css\bootstrap-overrides.css`
- `G:\VbN\css\global.css`
- `G:\VbN\css\login.css`
- `G:\VbN\css\dashboard.css`
- `G:\VbN\css\admin-agents.css`
- `G:\VbN\css\admin_sire_childe.css`
- `G:\VbN\css\questionnaire.css`
- `G:\VbN\js\logo-animation.js`
- `G:\VbN\js\admin_panel.js`
- `G:\VbN\js\questionnaire.js`

## Live Assets
- `G:\VbN\images\favicon.svg`
- `G:\VbN\uploads\characters\*` (prime character images)
- `G:\VbN\Agents\character_agent\reports\*` (surfaced via `admin/agents.php`)

## Archive Candidates (Manual Review)
- `G:\VbN\archive\*.php` (already relocated)
- `G:\VbN\docs\*.md` (documentation only)
- `G:\VbN\reference\*` (rulebook data, check agent references)
- `G:\VbN\tests\*` (non-production utilities)
- `G:\VbN\scripts\*` (deployment + tooling, verify automation usage)

## Next Steps
- Sync `archive/cleanup_manifest.json` active/inactive sections with the protected inventory above.
- Trace any remaining PHP files flagged as inactive against navigation/menu trees.
- Split JSON/MD inventories into `referenced` vs `non-critical` buckets for Phase 3 moves.

