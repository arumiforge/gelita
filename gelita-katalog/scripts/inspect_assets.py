import json
from pathlib import Path
from PIL import Image, ImageDraw

root=Path(__file__).resolve().parent.parent
data=json.loads((root/'data.json').read_text(encoding='utf-8-sig'))
out=root/'review'
out.mkdir(exist_ok=True)
assets=[a for a in data if a.get('preview') and (root/a['preview']).exists() and not a['preview'].endswith('.svg')]
for start in range(0,len(assets),30):
    page=Image.new('RGB',(1500,1200),'#e7e2d8')
    draw=ImageDraw.Draw(page)
    for i,a in enumerate(assets[start:start+30]):
        im=Image.open(root/a['preview']).convert('RGBA'); im.thumbnail((240,192))
        x=(i%6)*250;y=(i//6)*240
        page.paste(im,(x+(250-im.width)//2,y),im)
        draw.text((x+6,y+194),a['id']+' '+a['cat'],fill='black')
        draw.text((x+6,y+211),a['key'][:33],fill='black')
    page.save(out/f'contact-{start//30+1}.webp', 'WEBP', lossless=True)
