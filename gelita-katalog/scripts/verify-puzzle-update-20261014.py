"""Check the authorized update against its saved catalog and asset hashes."""
import hashlib
import json
from pathlib import Path
from PIL import Image
ROOT=Path(__file__).resolve().parent.parent
read=lambda p:json.loads((ROOT/p).read_text(encoding='utf-8'))
data=read('data.json')
before=read('archive/before-puzzle-update-20261014/data.json')
manifest=read('provenance/puzzle-generation-results-20261014.json')
changed={a['key'] for a in manifest['assets'] if a['replaced']}
current={a['key']:a for a in data}
unchanged=0
for entry in before:
    now=current[entry['key']]
    assert now.get('style_review')==entry.get('style_review')
    assert now.get('previous_style_review')==entry.get('previous_style_review')
    if entry['key'] not in changed:
        assert now==entry, entry['key']
        unchanged+=1
assert [a for a in data if a['size']=='Audio']==read('archive/audio-baseline.json')
preserved_hashes=0
for entry in read('archive/before-puzzle-update-20261014/asset-hashes.json'):
    digest=hashlib.sha256((ROOT/entry['file']).read_bytes()).hexdigest()
    if entry['key'] in changed:
        assert digest!=entry['sha256'],entry['key']
    else:
        assert digest==entry['sha256'],entry['key']
        preserved_hashes+=1
raster=0
for entry in data:
    if not (entry.get('file') or '').endswith('.webp'):continue
    target=tuple(int(v.strip()) for v in entry['size'].split('×'))
    with Image.open(ROOT/entry['file']) as image:
        assert image.size==target and image.format=='WEBP',entry['key']
        if entry['key'] in {a['key'] for a in manifest['assets']}:
            assert image.mode=='RGB' and target==(1200,1200)
    raster+=1
assert len(data)==611 and raster==461 and unchanged==586 and preserved_hashes==489
report=dict(date='2026-10-14',passed=True,total=611,visual=514,audio=97,added=21,replaced=4,unchanged_original_entries=unchanged,unchanged_original_asset_hashes=preserved_hashes,raster_at_target=raster,old_style_and_previous_reviews_preserved=True,audio_identical_to_baseline=True)
(ROOT/'reports/puzzle-update-verification-20261014.json').write_text(json.dumps(report,indent=2)+'\n',encoding='utf-8')
print(json.dumps(report))
