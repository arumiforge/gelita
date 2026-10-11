import json
from pathlib import Path
from PIL import Image
root=Path(__file__).resolve().parent.parent
data=json.loads((root/'data.json').read_text(encoding='utf8'))
for a in data:
    if a['size']=='Audio': continue
    p=root/a['file']
    if p.suffix=='.svg': continue
    with Image.open(p) as im:
        a['actual']=f'{im.width} × {im.height}'
        a['actual_format']=im.format
        a['actual_alpha']='A' in im.getbands() and im.getchannel('A').getextrema()[0]<255
        a['dimension_status']='Sesuai target' if a['actual']==a['size'] else 'Berbeda dari target'
(root/'data.json').write_text(json.dumps(data,ensure_ascii=False,indent=2)+'\n',encoding='utf8')
