import json
import pathlib
from collections import defaultdict
from datetime import datetime

ROOT = pathlib.Path(r"G:\\VbN")
ARCHIVE = ROOT / "archive"
MANIFEST_PATH = ARCHIVE / "cleanup_manifest.json"
REPORT_PATH = ARCHIVE / "image_duplicates.json"


def load_manifest() -> dict:
    return json.loads(MANIFEST_PATH.read_text(encoding="utf-8"))


def collect_duplicates(manifest: dict) -> list[dict]:
    entries = manifest.get("images", {}).get("duplicates", [])
    cleaned: list[dict] = []

    for group in entries:
        hash_value = group.get("hash")
        files = group.get("files", [])
        if not hash_value or not files:
            continue

        normalized_files: list[dict] = []
        for file_entry in files:
            rel = file_entry.get("relativePath")
            if not rel:
                continue
            normalized_files.append(
                {
                    "relativePath": rel.replace("\\", "/"),
                    "size": file_entry.get("size"),
                    "source": "archive" if rel.startswith("archive\\") else "live",
                }
            )

        if normalized_files:
            cleaned.append(
                {
                    "hash": hash_value,
                    "files": normalized_files,
                }
            )

    return cleaned


def build_summary(duplicates: list[dict]) -> dict:
    summary = defaultdict(int)

    for group in duplicates:
        summary["groups"] += 1
        summary["total_files"] += len(group["files"])
        for entry in group["files"]:
            summary[f"source_{entry['source']}"] += 1

    return summary


def write_report(duplicates: list[dict], summary: dict) -> None:
    payload = {
        "generatedAt": datetime.utcnow().isoformat() + "Z",
        "summary": summary,
        "groups": duplicates,
    }
    REPORT_PATH.write_text(
        json.dumps(payload, indent=2, ensure_ascii=False) + "\n",
        encoding="utf-8",
    )


def main() -> None:
    manifest = load_manifest()
    duplicates = collect_duplicates(manifest)
    summary = build_summary(duplicates)
    write_report(duplicates, summary)


if __name__ == "__main__":
    main()
