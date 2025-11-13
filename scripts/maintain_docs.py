from __future__ import annotations

import argparse
import json
from collections import defaultdict
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Iterable, List, Sequence, Tuple


DOCS_KEEP_REFERENCE: Sequence[str] = (
    "DATABASE_SCHEMA.md",
    "DATABASE_HELPERS.md",
    "PREPARED_STATEMENT_PATTERNS.md",
    "QUERY_OPTIMIZATION_GUIDE.md",
    "DISCIPLINE_SYSTEM_FIX.md",
    "HTML2CANVAS_USAGE.md",
    "BOOTSTRAP_INTEGRATION.md",
    "ADMIN_PANEL_MODAL_REDESIGN.md",
    "CHAT_SESSION_SUMMARY_2025-02-05.md",
)

DOCS_KEEP_PROCESS: Sequence[str] = (
    "PHASE3_SECURITY_UPDATES.md",
    "PHASE4_ADMIN_API_UPDATES.md",
    "PHASE5_SELECT_OPTIMIZATION.md",
    "PHASE6_UTILITY_SCRIPTS_AUDIT.md",
    "PHASE7_TRANSACTION_IMPLEMENTATION.md",
    "PHASE8_TESTING_VALIDATION.md",
    "PHASE9_FINAL_DOCUMENTATION.md",
    "LAWS_AGENT.md",
)

DOCS_KEEP_CHAT_REPORTS: Sequence[str] = (
    "CHAT_REPORT_ARCHIVE_CLEANUP.mdc",
    "CHAT_REPORT_ADMIN_UI_IMPROVEMENTS.mdc",
    "CHAT_REPORT_AGENTS_UI_REFRESH.mdc",
    "CHAT_REPORT_CHARACTER_DATA_SYNC.mdc",
    "CHAT_REPORT_CHARACTER_DESCRIPTION_TAB.mdc",
    "CHAT_REPORT_CHARACTER_FIELD_SYNC.mdc",
    "CHAT_REPORT_FINAL_DETAILS_RELATIONSHIPS.mdc",
    "CHAT_REPORT_STATUS_FIELDS.mdc",
    "CHAT_REPORT_TRAIT_UPDATES.mdc",
    "CHAT_REPORT_ADMIN_PANEL_ACTIONS_ALIGNMENT.mdc",
    "CHAT_REPORT_ADMIN_PANEL_BOOTSTRAP_COLUMNS.mdc",
    "CHAT_REPORT_ADMIN_PANEL_TABLE_FIX.mdc",
    "CHAT_REPORT_ADMIN_PANEL_SORTING.mdc",
    "CHAT_REPORT_LAWS_AGENT.mdc",
    "CHAT_REPORT_GIT_WORKFLOW.mdc",
)

README_TEMPLATE = """# Documentation Overview

This index highlights the primary references that remain in `docs/` and notes where historical material has been archived.

## Active References
{references}

## Current Workflow & Change Logs
{workflow}

## Process Specs & Phased Guides
{processes}

## Additional Notes
- The generated inventory (`DOCS_INVENTORY.md`) lists every file with last-modified dates.
- Historical sprint notes and older chat reports are auto-archived to `archive/docs/legacy/`.
- Optional flags (`--archive-phases`, `--archive-prds`) relocate legacy phase guides and PRDs into the same archive.
- When new documents are created, update both this overview and the inventory to keep the directory tidy.

## Next Steps
- Consolidate recurring instructions from the phase guides into evergreen checklists (e.g., Admin UI checklist, Deployment checklist).
- When a chat report becomes outdated, move it from this index into `archive/docs/legacy/`.
- Update `DOCS_INVENTORY.md` after each docs cleanup pass to keep metadata current.
"""

DOCS_LEGACY_DIR = Path("archive/docs/legacy")
PURGE_LIST_PATH = Path("archive/_purge_list.txt")
DOCS_README_PATH = Path("docs/README.md")
DOCS_INVENTORY_PATH = Path("docs/DOCS_INVENTORY.md")
PHASE_PATTERN = "PHASE*.md"
PRD_ROOT = Path("docs/json-analysis")
PRD_EXTENSIONS = {".md", ".json"}
PRD_KEEP: Sequence[str] = (
    "json-analysis/DATABASE_MIGRATION_GUIDE.md",
    "json-analysis/IMPLEMENTATION_COMPLETE.md",
)


@dataclass
class MoveRecord:
    source: Path
    destination: Path
    category: str


def main() -> None:
    parser = argparse.ArgumentParser(description="Maintain documentation structure.")
    parser.add_argument("--dry-run", action="store_true", help="Show actions without modifying files.")
    parser.add_argument(
        "--archive-phases",
        action="store_true",
        help="Move PHASE*.md files (except protected ones) into the legacy docs archive.",
    )
    parser.add_argument(
        "--archive-prds",
        action="store_true",
        help="Move PRD/spec files (e.g., docs/json-analysis) into the legacy docs archive.",
    )
    args = parser.parse_args()

    repo_root = Path(__file__).resolve().parents[1]
    docs_root = repo_root / "docs"
    legacy_root = repo_root / DOCS_LEGACY_DIR

    legacy_root.mkdir(parents=True, exist_ok=True)

    moves: List[MoveRecord] = []
    moves.extend(move_chat_reports(docs_root, legacy_root, args.dry_run))
    if args.archive_phases:
        moves.extend(move_phase_guides(docs_root, legacy_root, args.dry_run))
    if args.archive_prds:
        moves.extend(move_prds(repo_root, legacy_root, args.dry_run))

    update_purge_list(moves, repo_root, args.dry_run)
    write_readme(repo_root, args.dry_run)
    write_inventory(repo_root, args.dry_run)

    summary = build_summary(moves, args.dry_run, args.archive_phases, args.archive_prds)
    print(summary)


def move_chat_reports(docs_root: Path, legacy_root: Path, dry_run: bool) -> List[MoveRecord]:
    keep_set = set(DOCS_KEEP_CHAT_REPORTS)
    moves: List[MoveRecord] = []

    for chat_report in docs_root.glob("CHAT_REPORT*.mdc"):
        if chat_report.name in keep_set:
            continue
        destination = legacy_root / chat_report.name
        moves.append(MoveRecord(source=chat_report, destination=destination, category="chat"))
        if dry_run:
            continue
        destination.parent.mkdir(parents=True, exist_ok=True)
        destination.write_bytes(chat_report.read_bytes())
        chat_report.unlink()

    return moves


def move_phase_guides(docs_root: Path, legacy_root: Path, dry_run: bool) -> List[MoveRecord]:
    keep_set = set(DOCS_KEEP_PROCESS)
    moves: List[MoveRecord] = []

    for phase_doc in docs_root.glob(PHASE_PATTERN):
        if phase_doc.name in keep_set:
            continue
        destination = legacy_root / phase_doc.name
        moves.append(MoveRecord(source=phase_doc, destination=destination, category="phase"))
        if dry_run:
            continue
        destination.parent.mkdir(parents=True, exist_ok=True)
        destination.write_bytes(phase_doc.read_bytes())
        phase_doc.unlink()

    return moves


def move_prds(repo_root: Path, legacy_root: Path, dry_run: bool) -> List[MoveRecord]:
    prd_root = repo_root / PRD_ROOT
    moves: List[MoveRecord] = []

    if not prd_root.exists():
        return moves

    keep_set = set(PRD_KEEP)

    docs_root = repo_root / "docs"

    for path in prd_root.rglob("*"):
        if not path.is_file() or path.suffix.lower() not in PRD_EXTENSIONS:
            continue
        rel_docs = path.relative_to(docs_root)
        rel_str = rel_docs.as_posix()
        if rel_str in keep_set:
            continue
        destination = legacy_root / rel_docs
        moves.append(MoveRecord(source=path, destination=destination, category="prd"))
        if dry_run:
            continue
        destination.parent.mkdir(parents=True, exist_ok=True)
        destination.write_bytes(path.read_bytes())
        path.unlink()

    return moves


def update_purge_list(moves: Iterable[MoveRecord], repo_root: Path, dry_run: bool) -> None:
    if not moves:
        return

    purge_path = repo_root / PURGE_LIST_PATH
    existing_lines: set[str] = set()

    if purge_path.exists():
        existing_lines = set(purge_path.read_text(encoding="utf-8").splitlines())

    new_lines = []
    for move in moves:
        try:
            src_rel = move.source.relative_to(repo_root).as_posix()
        except ValueError:
            src_rel = move.source.as_posix()
        try:
            dest_rel = move.destination.relative_to(repo_root).as_posix()
        except ValueError:
            dest_rel = move.destination.as_posix()
        line = f"[DOC] /{src_rel} -> /{dest_rel}"
        if line not in existing_lines:
            new_lines.append(line)
            existing_lines.add(line)

    if dry_run or not new_lines:
        return

    with purge_path.open("a", encoding="utf-8") as handle:
        for line in new_lines:
            handle.write(line + "\n")


def write_readme(repo_root: Path, dry_run: bool) -> None:
    readme_path = repo_root / DOCS_README_PATH

    references = format_markdown_list(sorted(DOCS_KEEP_REFERENCE))
    workflow = format_markdown_list(sorted(DOCS_KEEP_CHAT_REPORTS))
    processes = format_markdown_list(sorted(DOCS_KEEP_PROCESS))
    prd_list = format_markdown_list(sorted(PRD_KEEP))
    process_block = processes
    if prd_list:
        process_block = process_block + "\n\n### PRD & Migration Specs\n" + prd_list

    content = README_TEMPLATE.format(
        references=references,
        workflow=workflow,
        processes=process_block,
    ).strip() + "\n"

    if dry_run:
        return

    readme_path.write_text(content, encoding="utf-8")


def format_markdown_list(items: Sequence[str]) -> str:
    return "\n".join(f"- `{item}`" for item in items)


def write_inventory(repo_root: Path, dry_run: bool) -> None:
    docs_root = repo_root / "docs"

    entries: defaultdict[str, list[tuple[str, str, int]]] = defaultdict(list)

    for path in docs_root.rglob("*"):
        if not path.is_file():
            continue
        rel = path.relative_to(docs_root)
        if rel == DOCS_INVENTORY_PATH.relative_to("docs"):
            continue
        top = rel.parts[0] if len(rel.parts) > 1 else "."
        stat = path.stat()
        modified = datetime.fromtimestamp(stat.st_mtime).strftime("%Y-%m-%d")
        entries[top].append((str(rel), modified, stat.st_size))

    for key in entries:
        entries[key].sort(key=lambda item: item[0])

    lines = ["# Documentation Inventory"]
    lines.append(f"_Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}_")
    lines.append("")

    if "." in entries:
        lines.append("## Root Files")
        for rel, modified, size in entries["."]:
            lines.append(f"- `{rel}` (last updated {modified}, {size} bytes)")
        lines.append("")

    for top in sorted(key for key in entries if key != "."):
        lines.append(f"## {top}/")
        for rel, modified, size in entries[top]:
            lines.append(f"- `{rel}` (last updated {modified}, {size} bytes)")
        lines.append("")

    inventory_content = "\n".join(lines).strip() + "\n"

    if dry_run:
        return

    inventory_path = repo_root / DOCS_INVENTORY_PATH
    inventory_path.write_text(inventory_content, encoding="utf-8")


def build_summary(
    moves: Iterable[MoveRecord],
    dry_run: bool,
    phases_requested: bool,
    prds_requested: bool,
) -> str:
    move_list = list(moves)
    category_counts: defaultdict[str, int] = defaultdict(int)
    for move in move_list:
        category_counts[move.category] += 1

    mode = "DRY-RUN" if dry_run else "EXECUTED"
    parts = [f"[{mode}] Documentation maintenance complete."]
    parts.append(f"Chat reports moved: {category_counts.get('chat', 0)}")
    if phases_requested:
        parts.append(f"Phase guides moved: {category_counts.get('phase', 0)}")
    if prds_requested:
        parts.append(f"PRDs moved: {category_counts.get('prd', 0)}")
    return " ".join(parts)


if __name__ == "__main__":
    main()

