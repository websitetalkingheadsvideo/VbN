## Current State
- `G:\VbN\Agents\character_agent\` does not yet contain a runner script that queries characters for the Valley by Night chronicle.
- Database access is available through `G:\VbN\includes\connect.php`, but path usage from the agents directory is unverified.

## Target State
- A new read-only script at `G:\VbN\Agents\character_agent\run_character_agent.php` includes `connect.php`, queries Valley by Night characters, evaluates required report flags, and echoes dry-run results without persistence.
- Script reports per-character decisions plus aggregate totals and adheres to non-destructive constraints.

## Files To Modify
- `G:\VbN\Agents\character_agent\run_character_agent.php` (new file).

## Task Checklist
- [ ] Confirm relative include path from agents directory to `connect.php`.
- [ ] Inspect existing agent directory for conventions to align messaging/formatting.
- [ ] Implement query and evaluation logic per prompt, with strict typing and explicit error handling.
- [ ] Manually test script output in browser / CLI-equivalent web request and document instructions.
- [ ] Capture outstanding questions or missing schema details for the user.

