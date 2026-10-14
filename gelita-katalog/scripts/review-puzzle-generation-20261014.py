"""Measure candidate outputs, run guide tile metrics, create regional contact sheets."""
import contextlib
import io
import json
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont
from check_puzzle_tiles import periksa
ROOT = Path(__file__).resolve().parent.parent
BASE = ROOT / 'provenance/puzzle-generation-20261014'
records = json.loads((BASE / 'results.json').read_text(encoding='utf-8'))
results = []
for record in records:
    file = ROOT / record['candidate_file']
    with Image.open(file) as image:
        assert image.size == (1200,1200) and image.format == 'WEBP' and image.mode == 'RGB', file
    grid = 3 if record['code'].startswith('TMG') else 4
    with contextlib.redirect_stdout(io.StringIO()):
        result = periksa(str(file), grid, str(ROOT / 'review/puzzle-20261014/tiles'))
    results.append(dict(key=record['key'], code=record['code'], file=record['candidate_file'], grid=grid, size=[1200,1200], alpha=False, **result))
    print(record['code'], result['putusan'], 'polos=',result['polos'], 'kembar=',len(result['kembar']), 'periksa=',len(result['luas'])+len(result['mirip'])+len(result['pola'])+len(result['warna']))
review = ROOT / 'review/puzzle-20261014'
review.mkdir(parents=True, exist_ok=True)
font = ImageFont.truetype('C:/Windows/Fonts/arial.ttf', 16)
for region in ('TMG','MGL','WNB'):
    rows = [record for record in records if record['code'].startswith(region)]
    sheet = Image.new('RGB', (1000, ((len(rows)+3)//4)*290), '#eee9df')
    draw = ImageDraw.Draw(sheet)
    for n, record in enumerate(rows):
        x, y = n%4*250, n//4*290
        with Image.open(ROOT / record['candidate_file']) as image:
            sheet.paste(image.resize((240,240),Image.Resampling.LANCZOS),(x+5,y+5))
        draw.text((x+8,y+249),record['code'],font=font,fill='#14213d')
        draw.text((x+8,y+268),record['title'][:27],font=font,fill='#14213d')
    sheet.save(review / ('contact-'+region.lower()+'.webp'), 'WEBP',quality=94)
report = dict(date='2026-10-14',total=len(results), failures=sum(bool(r['polos'] or r['kembar']) for r in results), warnings=sum(r['putusan']=='LULUS DENGAN CATATAN' for r in results), source='Guide section 9.2; thresholds unchanged', human_assembly_test=False, cultural_validation=False, assets=results)
(ROOT / 'reports/puzzle-tile-review-20261014.json').write_text(json.dumps(report,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
