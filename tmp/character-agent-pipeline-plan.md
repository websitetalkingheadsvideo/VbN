# Character Agent Pipeline Plan

## Current State
- No automated script pulls Valley by Night characters directly from the database.
- `agents/character_agent/run_character_agent.php` performs a dry-run listing (no file output) but already queries MySQL via `includes/connect.php`.
- Reports directory exists with placeholder folders (`daily`, `weekly`, `continuity`) but no canonical schema enforced.
- Prompts (`prompts/*.md`) target Markdown briefs with character summary + hooks; no JSON structure defined.
- No logging, scheduling hooks, or CLI entry point wired to `includes/connect.php`.

- `agents/character_agent/pipeline.php` script connects via `includes/connect.php` and streams characters in batches with prepared statements.
- Character data normalized into immutable value objects before processing.
- Report writer outputs JSON summaries into `agents/character_agent/reports/` using timestamped filenames.
- CLI flags manage dry runs, character filters, and output paths; logging writes to `agents/character_agent/logs/`.
- Configuration isolated in `agents/character_agent/config/pipeline.php`.
- Schema contract documented (`tmp/character-agent-schema.md`) and enforced before writing reports.

## Files To Touch
- `agents/character_agent/pipeline.php` (new)
- `agents/character_agent/config/pipeline.php` (new)
- `agents/character_agent/src/character_fetcher.php` (new)
- `agents/character_agent/src/character_processor.php` (new)
- `agents/character_agent/src/report_writer.php` (new)
- `agents/character_agent/src/logger.php` (new)
- `agents/character_agent/reports/.gitkeep` (ensure present/updated)
- `agents/character_agent/logs/.gitkeep` (new)
- `composer.json` / autoload if needed (verify existing setup)

## Report & Payload Schema
- **Markdown Brief (`reports/daily|weekly/{character_slug}.md`)**
  - Header fields: `Character`, `Clan`, `Faction`, `Location`, `Player`
  - Sections: `Summary`, `Key Traits` (3 bullet points), `Plot Hooks` (3 enumerated hooks), footer signature
- **Missing Field JSON (`reports/daily/{timestamp}_{character_slug}_missing.json`)**
  ```json
  {
    "character_id": 0,
    "character_name": "",
    "detected_at": "2025-11-12T00:00:00Z",
    "missing_fields": [
      {
        "field": "biography",
        "status": "missing",
        "recommendation": "Add 2-3 paragraph chronicle summary."
      }
    ]
  }
  ```
- **Continuity Warning JSON (`reports/continuity/{timestamp}_{character_slug}.json`)**
  ```json
  {
    "character_id": 0,
    "issues": [
      {
        "type": "relationship_inconsistent",
        "details": "Reported sire differs from sire field.",
        "severity": "warning"
      }
    ],
    "generated_at": "2025-11-12T00:00:00Z"
  }
  ```
- **Run Metadata (`logs/agent_activity.log`)**
  - Append single-line JSON per event: `{"timestamp":"...","character_id":1,"events":["brief_written","missing_fields:biography"]}`
- **History Compilation (`reports/history/{timestamp}_city_history.md`)**
  - Template-driven Markdown merging `biography`, `notes`, `agentNotes` grouped by coterie.

## Task Checklist
- [x] Audit existing agent assets and confirm output schema requirements.
- [ ] Establish config structure and environment safeguards.
- [x] Implement fetcher using `$wpdb` style abstraction or mysqli from `connect.php`.
- [x] Build pure transformation pipeline for character entities.
- [x] Implement report writer with deterministic filenames and validation.
- [x] Add CLI interface with argument parsing and logging.
- [x] Expose authenticated web entry point for pipeline execution.
- [ ] Document run instructions in `agents/character_agent/README.md`.

