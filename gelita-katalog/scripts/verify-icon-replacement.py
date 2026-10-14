import hashlib
import json
from datetime import datetime, timezone
from pathlib import Path
from zipfile import ZipFile

root = Path(__file__).resolve().parent.parent
data = json.loads((root / 'data.json').read_text(encoding='utf8'))
replacement_keys = set(json.loads((root / 'reports/icon-update-report.json').read_text(encoding='utf8'))['changed_keys'])
icons = [a for a in data if a['key'] in replacement_keys]
assert len(icons) == len(replacement_keys) == 83
with ZipFile(root / 'archive/before-icons-20261011.zip') as backup:
    archived = {Path(name).name: name for name in backup.namelist() if not name.endswith('/')}
    checks = []
    for asset in icons:
        current = root / asset['file']
        original = backup.read(archived[current.name])
        old_hash = hashlib.sha256(original).hexdigest()
        new_hash = hashlib.sha256(current.read_bytes()).hexdigest()
        assert old_hash != new_hash, asset['key']
        checks.append({'key': asset['key'], 'previous_sha256': old_hash, 'current_sha256': new_hash})
png_files = list(root.rglob('*.png'))
assert not png_files, png_files
report = {'date': '2026-10-11', 'checked_at_utc': datetime.now(timezone.utc).isoformat(), 'scope': '83 icons replaced on 2026-10-11; later additions excluded', 'icons_replaced': len(checks), 'all_icon_files_differ_from_backup': True, 'loose_png_files': 0, 'checks': checks}
(root / 'reports/icon-replacement-verification.json').write_text(json.dumps(report, ensure_ascii=False, indent=2) + '\n', encoding='utf8')
print('Verified: all 83 icon files differ from the backup; no loose PNG files.')
