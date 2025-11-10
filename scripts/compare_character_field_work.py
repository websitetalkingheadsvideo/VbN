#!/usr/bin/env python3
"""
Generate a structured diff between two character field-work JSON snapshots.

Usage:
    python scripts/compare_character_field_work.py \
        --baseline G:\path\to\characters_export.json \
        --candidate G:\path\to\character_field_work.json \
        --report   G:\path\to\diff_report.json
"""

from __future__ import annotations

import argparse
import json
from dataclasses import dataclass
from pathlib import Path
from typing import Dict, Iterable, List, Optional, Tuple


MANDATORY_FIELDS: Tuple[str, ...] = ("id", "character_name", "character_image", "appearance", "biography", "notes")
DIFF_FIELDS: Tuple[str, ...] = ("character_image", "appearance", "biography", "notes")


@dataclass(frozen=True)
class CharacterRecord:
    """Immutable view of a character narrative record."""

    id: int
    character_name: str
    character_image: str
    appearance: str
    biography: str
    notes: str

    @staticmethod
    def from_dict(payload: Dict[str, object], require_all_fields: bool) -> "CharacterRecord":
        mandatory: Tuple[str, ...] = ("id", "character_name")
        missing_core = [field for field in mandatory if field not in payload or payload[field] in (None, "")]
        if missing_core:
            name_hint = payload.get("character_name", "<unknown>")
            raise ValueError(f"Record '{name_hint}' missing required fields: {', '.join(missing_core)}")

        optional_defaults = {field: "" for field in DIFF_FIELDS}
        for field, default in optional_defaults.items():
            if payload.get(field) is None:
                payload[field] = default

        if require_all_fields:
            missing_optional = [field for field in DIFF_FIELDS if payload.get(field, "") == ""]
            if missing_optional:
                name_hint = payload.get("character_name", "<unknown>")
                raise ValueError(
                    f"Record '{name_hint}' missing narrative fields: {', '.join(missing_optional)}"
                )

        return CharacterRecord(
            id=int(payload["id"]),
            character_name=str(payload["character_name"]),
            character_image=str(payload.get("character_image", "")),
            appearance=str(payload.get("appearance", "")),
            biography=str(payload.get("biography", "")),
            notes=str(payload.get("notes", "")),
        )


def load_characters(path: Path, require_all_fields: bool) -> Tuple[Dict[int, CharacterRecord], List[Dict[str, object]]]:
    with path.open("r", encoding="utf-8") as handle:
        payload = json.load(handle)
    characters = payload.get("characters")
    if not isinstance(characters, list):
        raise ValueError(f"{path} does not contain a 'characters' array.")

    indexed: Dict[int, CharacterRecord] = {}
    duplicates: List[Dict[str, object]] = []
    for raw_record in characters:
        if not isinstance(raw_record, dict):
            raise ValueError(f"Unexpected record type in {path}: {raw_record!r}")
        record = CharacterRecord.from_dict(raw_record, require_all_fields=require_all_fields)
        if record.id in indexed:
            duplicates.append(
                {
                    "id": record.id,
                    "existing_character_name": indexed[record.id].character_name,
                    "duplicate_character_name": record.character_name,
                }
            )
            continue
        indexed[record.id] = record
    return indexed, duplicates


def detect_differences(
    baseline: Dict[int, CharacterRecord],
    candidate: Dict[int, CharacterRecord],
    baseline_duplicates: List[Dict[str, object]],
    candidate_duplicates: List[Dict[str, object]],
) -> Dict[str, object]:
    missing_ids: List[int] = sorted(set(baseline) - set(candidate))
    new_ids: List[int] = sorted(set(candidate) - set(baseline))

    changed: List[Dict[str, object]] = []
    for char_id in sorted(set(baseline).intersection(candidate)):
        base_entry = baseline[char_id]
        cand_entry = candidate[char_id]
        field_changes: Dict[str, Dict[str, str]] = {}
        for field in DIFF_FIELDS:
            base_value = getattr(base_entry, field)
            cand_value = getattr(cand_entry, field)
            if base_value != cand_value:
                field_changes[field] = {"baseline": base_value, "candidate": cand_value}

        if field_changes:
            changed.append(
                {
                    "id": char_id,
                    "character_name": cand_entry.character_name,
                    "changes": field_changes,
                }
            )

    return {
        "summary": {
            "baseline_total": len(baseline),
            "candidate_total": len(candidate),
            "missing_ids": missing_ids,
            "new_ids": new_ids,
            "changed_entries": len(changed),
            "baseline_duplicates": baseline_duplicates,
            "candidate_duplicates": candidate_duplicates,
        },
        "changed": changed,
    }


def parse_args(argv: Optional[Iterable[str]] = None) -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Compare character field-work JSON snapshots.")
    parser.add_argument("--baseline", required=True, help="Path to the exported characters JSON.")
    parser.add_argument("--candidate", required=True, help="Path to the working character_field_work.json.")
    parser.add_argument("--report", required=True, help="Path to write the diff report JSON.")
    return parser.parse_args(argv)


def main() -> None:
    args = parse_args()
    baseline_path = Path(args.baseline).resolve()
    candidate_path = Path(args.candidate).resolve()
    report_path = Path(args.report).resolve()

    for file_path in (baseline_path, candidate_path):
        if not file_path.exists():
            raise FileNotFoundError(f"{file_path} does not exist.")

    baseline_records, baseline_duplicates = load_characters(baseline_path, require_all_fields=False)
    candidate_records, candidate_duplicates = load_characters(candidate_path, require_all_fields=True)
    diff = detect_differences(
        baseline_records,
        candidate_records,
        baseline_duplicates,
        candidate_duplicates,
    )

    report_path.parent.mkdir(parents=True, exist_ok=True)
    with report_path.open("w", encoding="utf-8") as handle:
        json.dump(diff, handle, indent=4, ensure_ascii=False)

    print(json.dumps(diff["summary"], indent=4, ensure_ascii=False))  # noqa: T201


if __name__ == "__main__":
    main()


