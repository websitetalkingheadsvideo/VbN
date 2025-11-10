import json
import pathlib
from collections import Counter
from datetime import datetime

ROOT = pathlib.Path(r"G:\\VbN")
ARCHIVE = ROOT / "archive"
PURGE_PATH = ARCHIVE / "_purge_list.txt"
MANIFEST_PATH = ARCHIVE / "cleanup_manifest.json"
LOG_PATH = ARCHIVE / "_cleanup_log.json"
REPORT_PATH = ARCHIVE / "_cleanup_report.md"

now = datetime.utcnow().strftime('%Y-%m-%d %H:%M:%S UTC')
reasons = {
    'PHP': 'inactive page archived',
    'JSON': 'unreferenced reference data archived',
    'IMAGE': 'duplicate image isolated'
}

lines = []
if PURGE_PATH.exists():
    lines = [line.strip() for line in PURGE_PATH.read_text(encoding='utf-8').splitlines() if line.strip()]

entries = []
for line in lines:
    if '] ' not in line or ' -> ' not in line:
        continue
    prefix, rest = line.split('] ', 1)
    category = prefix.strip('[]')
    source, dest = rest.split(' -> ', 1)
    entries.append({
        'file': source,
        'archivePath': dest,
        'category': category.lower(),
        'action': 'archived',
        'timestamp': now,
        'reason': reasons.get(category, 'archived')
    })

LOG_PATH.write_text(json.dumps(entries, indent=2, ensure_ascii=False), encoding='utf-8')

manifest = json.loads(MANIFEST_PATH.read_text(encoding='utf-8'))
summary = manifest.get('summary', {})
php_summary = summary.get('php', {})
json_summary = summary.get('json', {})
md_summary = summary.get('md', {})
image_summary = summary.get('images', {})

counts = Counter(entry['category'] for entry in entries)

report_lines = [
    f"# Cleanup Report",
    f"_Generated: {now}_",
    "",
    "## Snapshot",
    f"- PHP active: {php_summary.get('active', 0)}",
    f"- PHP archived: {php_summary.get('archived', 0)}",
    f"- PHP remaining inactive: {php_summary.get('inactive', 0)}",
    f"- JSON referenced: {json_summary.get('referenced', 0)}",
    f"- JSON unreferenced: {json_summary.get('unreferenced', 0)}",
    f"- Markdown referenced: {md_summary.get('referenced', 0)}",
    f"- Markdown unreferenced: {md_summary.get('unreferenced', 0)}",
    f"- Images total: {image_summary.get('total', 0)}",
    f"- Duplicate groups: {image_summary.get('duplicateGroups', 0)}",
    "",
    "## Actions Logged",
    f"- PHP archived this run: {counts.get('php', 0)}",
    f"- JSON archived this run: {counts.get('json', 0)}",
    f"- Duplicate images isolated: {counts.get('image', 0)}",
    "",
    "## Next Steps",
    "- Review archive/php/ for any files to restore or finalize for deletion.",
    "- Inspect /archive/images/duplicates for confirmation before purge.",
    "- Wire up admin/tools/archive_dashboard.php to surface these records."
]

REPORT_PATH.write_text('\n'.join(report_lines) + '\n', encoding='utf-8')
