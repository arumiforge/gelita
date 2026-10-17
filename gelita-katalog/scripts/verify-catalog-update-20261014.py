"""Read-only verification of slot reuse, audio decoding and preserved originals."""
import hashlib
from datetime import datetime, timezone
import json
from pathlib import Path
import subprocess
import wave
import numpy as np
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
load = lambda p: json.loads((ROOT/p).read_text(encoding='utf-8'))
sha = lambda p: hashlib.sha256((ROOT/p).read_bytes()).hexdigest()

data = load('data.json')
before = load('archive/before-catalog-update-20261014/data.json')
baseline = load('archive/audio-baseline.json')
assert baseline == [a for a in before if a['size']=='Audio'], 'Historical audio baseline changed'
audio_manifest = load('provenance/nonnarration-audio-20261014.json')
icons = load('provenance/language-slot-update-20261014.json')
current = {a['key']: a for a in data}
assert len(data) == len(before) == 611
assert len(current) == 611 and len({a['id'] for a in data}) == 611
allowed = {a['key']: set(m['allowed_changed_fields']) for m in [icons,audio_manifest] for a in m['assets']}
for a in before:
    strip = lambda obj: {k: v for k,v in obj.items() if k not in allowed.get(a['key'],set())}
    assert strip(a) == strip(current[a['key']]), a['key']
for a in baseline:
    if a['cat'] == 'Narasi': assert current[a['key']] == a, a['key']
old_hashes = load('archive/before-catalog-update-20261014/asset-hashes.json')
changed_files = {a['file'] for a in icons['assets']} | {'assets/catalog/catalog.css','assets/catalog/catalog.js'}
for p,h in old_hashes.items():
    if p not in changed_files: assert sha(p) == h, p
for a in icons['assets']:
    assert sha(a['file']) == sha(a['source']) == a['sha256']
    assert sha(a['backup']) == a['previous_sha256']
    assert current[a['key']]['style_review'] == next(x for x in before if x['key']==a['key'])['style_review']
    with Image.open(ROOT/a['file']) as im:
        assert im.size == (256,256) and im.mode == 'RGBA'
        assert im.getchannel('A').getextrema() == (0,255)

measures=[]
for a in audio_manifest['assets']:
    assert current[a['key']]['file']==a['file']
    assert current[a['key']]['audio_provenance']['master']==a['master']
    assert sha(a['file']) == a['sha256'] and sha(a['master']) == a['master_sha256']
    with wave.open(str(ROOT/a['master']),'rb') as w:
        assert w.getnchannels()==2 and w.getsampwidth()==2 and w.getframerate()==44100
        duration=w.getnframes()/w.getframerate()
        assert abs(duration-a['duration_seconds'])<.0001
    def decode(rate):
        process=subprocess.run(['ffmpeg','-hide_banner','-loglevel','error','-i',str(ROOT/a['file']),'-ar',str(rate),'-f','f32le','pipe:1'],capture_output=True,check=True)
        return np.frombuffer(process.stdout,dtype='<f4').reshape(-1,2)
    samples=decode(44100)
    peak=float(np.max(np.abs(samples)))
    assert peak>0 and peak<.8913, a['key']+' sample peak'
    decoded_duration=len(samples)/44100
    assert abs(decoded_duration-duration)<.001, (a['key'], decoded_duration, duration)
    true_peak=float(np.max(np.abs(decode(176400))))
    assert true_peak<.8913, a['key']+' interpolated peak'
    delta=float(np.max(np.abs(samples[0]-samples[-1]))) if a['loop'] else None
    if a['loop']: assert delta<.025, (a['key'],delta)
    measures.append({'key':a['key'],'duration_seconds':duration,'decoded_duration_seconds':decoded_duration,'sample_peak_dbfs':float(20*np.log10(peak)),'oversampled_peak_dbtp_estimate':float(20*np.log10(true_peak)),'rms_dbfs':float(20*np.log10(np.sqrt(np.mean(samples**2)))),'loop_boundary_sample_delta':delta,'decoded':True})
assert len(measures)==9
mockups=load('mockup/catalog.json')
assert sha(mockups['pages'][0]['image'])==load('provenance/mockup-tantangan-selesai-temanggung.json')['mockup']['sha256'], 'Selected mockup changed'
assert len([p for p in mockups['pages'] if p['status']=='available'])==1
assert len([p for p in mockups['pages'] if p['status']=='planned'])==12
for p in mockups['pages']:
    for f in [p.get('brief'),p.get('prompt'),p.get('image'),p.get('preview')]+[s['image'] for s in p.get('states',[])]:
        if f: assert (ROOT/f).is_file(), f
    if p['status']=='planned': assert not p.get('image') and not p.get('preview')
report={'date':'2026-10-14','timezone':'Asia/Bangkok','checked_at_utc':datetime.now(timezone.utc).isoformat(),'passed':True,'entries':611,'visuals':514,'audio':97,'unchanged_narrations':88,'unchanged_entries':600,'changed_language_slots':2,'produced_audio':9,'unchanged_original_asset_files':len(old_hashes)-len(changed_files),'authorized_changed_files':sorted(changed_files),'original_asset_files':len(old_hashes),'selected_mockup_hash':sha(mockups['pages'][0]['image']),'mockups':{'available':1,'planned':12},'audio_measurements':measures,'limitations':['Oversampled peak is a four-times interpolation estimate, not certified BS.1770 metering','No subjective listening, traditional tuning or game mix validation','Preview and documents do not establish integration into the game']}
(ROOT/'reports/catalog-update-20261014.json').write_text(json.dumps(report,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
print(json.dumps({k:v for k,v in report.items() if k not in ['audio_measurements','limitations']}))
