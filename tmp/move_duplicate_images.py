import json
import pathlib
import shutil
from collections import Counter
from datetime import datetime, timezone

ROOT = pathlib.Path(r"G:\VbN")
ARCHIVE = ROOT / "archive"
MANIFEST_PATH = ARCHIVE / "cleanup_manifest.json"
PURGE_PATH = ARCHIVE / "_purge_list.txt"
LOG_PATH = ARCHIVE / "_cleanup_log.json"
REPORT_PATH = ARCHIVE / "image_duplicates.json"
SUMMARY_PATH = ARCHIVE / "image_duplicates_summary.json"
DUPLICATES_DIR = ARCHIVE / "images" / "duplicates"


def load_manifest() -> dict:
    return json.loads(MANIFEST_PATH.read_text(encoding="utf-8"))


def choose_retained(files: list[dict]) -> str | None:
    uploads = [entry for entry in files if "uploads\\characters" in entry.get("relativePath", "").lower()]
    if uploads:
        return uploads[0].get("relativePath")
    for entry in files:
        rel = entry.get("relativePath")
        if rel:
            return rel
    return None


def normalise_relative_path(value: str) -> str:
    return value.replace("\\", "/")


def ensure_unique_lines(path: pathlib.Path, new_lines: list[str]) -> None:
    if not new_lines:
        return

    existing = set()
    if path.exists():
        with path.open("r", encoding="utf-8") as handle:
            for line in handle:
                existing.add(line.rstrip("\n"))

    with path.open("a", encoding="utf-8") as handle:
        for line in new_lines:
            if line not in existing:
                handle.write(line + "\n")
                existing.add(line)


def load_existing_log() -> list[dict]:
    if not LOG_PATH.exists():
        return []

    content = LOG_PATH.read_text(encoding="utf-8")
    if not content.strip():
        return []

    datasets: list[dict] = []
    buffer = ""
    depth = 0

    for char in content:
        buffer += char
        if char == "[":
            depth += 1
        elif char == "]":
            depth -= 1
            if depth == 0:
                snippet = buffer.strip()
                if snippet:
                    try:
                        parsed = json.loads(snippet)
                        if isinstance(parsed, list):
                            datasets.extend(parsed)
                    except json.JSONDecodeError:
                        pass
                buffer = ""

    return datasets


def update_log(entries: list[dict]) -> None:
    if not entries:
        return

    log_data = load_existing_log()

    existing_keys = {
        (item.get("file"), item.get("archivePath"), item.get("action"), item.get("reason"))
        for item in log_data
    }

    changed = False
    for entry in entries:
        key = (entry["file"], entry["archivePath"], entry["action"], entry["reason"])
        if key not in existing_keys:
            log_data.append(entry)
            existing_keys.add(key)
            changed = True

    if changed:
        LOG_PATH.write_text(json.dumps(log_data, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")


def process_duplicates(manifest: dict) -> tuple[list[dict], Counter, list[str], list[dict]]:
    duplicates = manifest.get("images", {}).get("duplicates", [])
    report_groups: list[dict] = []
    summary_counter: Counter = Counter()
    purge_lines: list[str] = []
    log_entries: list[dict] = []

    generated_at = datetime.now(timezone.utc).isoformat()

    for group in duplicates:
        hash_value = group.get("hash")
        files = group.get("files", [])
        if not hash_value or not files:
            continue

        retained_rel = choose_retained(files)
        retained_path = ROOT / retained_rel if retained_rel else None
        retained_exists = retained_path.exists() if retained_path else False

        group_records: list[dict] = []

        for entry in files:
            rel = entry.get("relativePath")
            if not rel:
                continue

            rel_path = pathlib.Path(rel)
            source_path = ROOT / rel_path
            if retained_rel and rel == retained_rel:
                status = "retained" if retained_exists else "missing"
                group_records.append(
                    {
                        "relativePath": normalise_relative_path(rel),
                        "status": status,
                    }
                )
                summary_counter["retained"] += 1 if status == "retained" else 0
                continue

            destination_path = DUPLICATES_DIR / rel_path
            destination_path.parent.mkdir(parents=True, exist_ok=True)

            if source_path.exists():
                shutil.move(str(source_path), str(destination_path))
                status = "archived"
                purge_line = f"[IMAGE] /{normalise_relative_path(rel)} -> /archive/images/duplicates/{normalise_relative_path(rel)}"
                purge_lines.append(purge_line)
                log_entries.append(
                    {
                        "file": f"/{normalise_relative_path(rel)}",
                        "archivePath": f"/archive/images/duplicates/{normalise_relative_path(rel)}",
                        "category": "image",
                        "action": "archive-duplicate",
                        "timestamp": generated_at,
                        "reason": "Duplicate image archived",
                    }
                )
                summary_counter["archived"] += 1
            elif destination_path.exists():
                status = "archived"
                summary_counter["archived_existing"] += 1
            else:
                status = "missing"
                summary_counter["missing"] += 1

            record = {
                "relativePath": normalise_relative_path(rel),
                "status": status,
            }

            if destination_path.exists():
                record["destination"] = normalise_relative_path(str(destination_path.relative_to(ARCHIVE)))

            group_records.append(record)

        report_groups.append(
            {
                "hash": hash_value,
                "retained": {
                    "relativePath": normalise_relative_path(retained_rel) if retained_rel else None,
                    "exists": retained_exists,
                },
                "files": group_records,
            }
        )

    return report_groups, summary_counter, purge_lines, log_entries


def write_reports(groups: list[dict], summary_counter: Counter) -> None:
    generated_at = datetime.now(timezone.utc).isoformat()
    payload = {
        "generatedAt": generated_at,
        "groups": groups,
    }
    REPORT_PATH.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")

    summary_payload = {
        "generatedAt": generated_at,
        "summary": {
            "groups": len(groups),
            "totalFiles": sum(len(group["files"]) for group in groups),
            "retained": summary_counter.get("retained", 0),
            "archived": summary_counter.get("archived", 0) + summary_counter.get("archived_existing", 0),
            "missing": summary_counter.get("missing", 0),
        },
        "detailsSource": "image_duplicates.json",
    }
    SUMMARY_PATH.write_text(json.dumps(summary_payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")


def main() -> None:
    manifest = load_manifest()
    groups, summary_counter, purge_lines, log_entries = process_duplicates(manifest)
    ensure_unique_lines(PURGE_PATH, purge_lines)
    update_log(log_entries)
    write_reports(groups, summary_counter)


if __name__ == "__main__":
    main()
