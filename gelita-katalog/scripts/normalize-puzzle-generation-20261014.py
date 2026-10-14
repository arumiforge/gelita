"""Save native outputs as WebP and normalize square candidates; no AI generation."""
import hashlib
import json
from pathlib import Path
from PIL import Image, ImageOps

ROOT = Path(__file__).resolve().parent.parent
BASE = ROOT / 'provenance/puzzle-generation-20261014'
records = json.loads((BASE / 'results.json').read_text(encoding='utf-8'))
by_key = {record['key']: record for record in records}
for file in sorted((BASE / 'calls').glob('*.json'), key=lambda p: json.loads(p.read_text(encoding='utf-8')).get('revision', 1)):
    record = json.loads(file.read_text(encoding='utf-8'))
    by_key[record['key']] = record
records = sorted(by_key.values(), key=lambda r: r['code'])
for folder in ('native', 'candidates'):
    (BASE / folder).mkdir(exist_ok=True)
for record in records:
    if record.get('revision', 1) > 1:
        record['input_paths'] = record['references']
        record['references'] = [f'provenance/puzzle-generation-20261014/native/{record["key"]}.webp'] + [Path(p).relative_to(ROOT).as_posix() for p in record['references'][1:]] if not record.get('input_paths_normalized') else record['references']
        record['input_paths_normalized'] = True
    source = Path(record['source_file'])
    image = ImageOps.exif_transpose(Image.open(source)).convert('RGB')
    if image.width != image.height:
        raise ValueError('Expected square image: ' + record['key'])
    version = '-v' + str(record['revision']) if record.get('revision', 1) > 1 else ''
    native = BASE / 'native' / (record['key'] + version + '.webp')
    candidate = BASE / 'candidates' / (record['key'] + '.webp')
    created = not native.exists()
    if created:
        image.save(native, 'WEBP', lossless=True, method=6)
    if not candidate.exists() or created:
        image.resize((1200, 1200), Image.Resampling.LANCZOS).save(candidate, 'WEBP', lossless=True, method=6)
    record.update(native_file=native.relative_to(ROOT).as_posix(), candidate_file=candidate.relative_to(ROOT).as_posix(), native_size=list(image.size), size=[1200,1200], alpha=False)
    for label, file in [('source', source), ('native', native), ('candidate', candidate)]:
        record[label + '_sha256'] = hashlib.sha256(file.read_bytes()).hexdigest()
(BASE / 'results.json').write_text(json.dumps(records, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
print(f'{len(records)} native WebP and 1200 x 1200 candidates saved')
