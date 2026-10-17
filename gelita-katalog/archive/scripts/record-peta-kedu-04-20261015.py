"""Read-only asset verification and manifest recording; does not generate art."""
import json
import hashlib
from pathlib import Path
from datetime import datetime, timezone
from PIL import Image, ImageChops
ROOT = Path(__file__).resolve().parents[2]
SOURCE = Path(r"C:\Users\cenda\.codex\generated_images\01a13941-052e-7d82-ada6-57e8a0c2f92c\exec-9d608c7c-f6fa-4480-9a3a-01e4e6601c32.png")
PACKET = "mockup/gambar-mockup/peta-kedu/revisi-04/"
def sha(file):
    return hashlib.sha256((ROOT / file).read_bytes()).hexdigest()
def meta(file):
    im=Image.open(ROOT/file)
    return {"file":file,"size":list(im.size),"format":im.format,"alpha":im.mode=="RGBA" and im.getextrema()[-1][0]<255,"sha256":sha(file)}
master=PACKET+"aset/diorama.kedu-tiga-wilayah.webp"
native="provenance/peta-kedu-revision-04/diorama-native.webp"
assert ImageChops.difference(Image.open(SOURCE).convert("RGB"),Image.open(ROOT/master).convert("RGB")).getbbox() is None
assert sha(master)==sha(native)
skins=["assets/antarmuka/ui.result-"+name+".webp" for name in ["panel","button-primary","button-secondary","button-detail","hud-control","star"]]
source_manifest=json.loads((ROOT/"provenance/result-ui-generation-results-20261013.json").read_text(encoding="utf-8"))
for file in skins:
    before=next(a for a in source_manifest["assets"] if a["file"]==file)
    assert sha(file)==before["sha256"],file
preserved=[]
for file in ["peta-kedu-mockup-20261014.json","peta-kedu-mockup-02-20261014.json","peta-kedu-mockup-03-20261014.json"]:
    before=json.loads((ROOT/"provenance"/file).read_text(encoding="utf-8"))
    assert sha("data.json")==before["catalog_data_sha256"]
    for key in ["native","output"]:
        assert sha(before[key]["file"])==before[key]["sha256"]
        preserved.append(before[key]["file"])
inventory=json.loads((ROOT/"mockup/catalog.json").read_text(encoding="utf-8"))
page=next(p for p in inventory["pages"] if p["id"]=="peta-kedu")
assert page["status"]=="planned" and "04" in page["candidate"]["label"] and page["candidate"]["preview"]==PACKET+"preview.html"
renders=[meta(s["image"]) for s in page["candidate"]["states"] if s["kind"]=="coded-preview"]
assert len(renders)==4 and all(r["size"]==[1920,1080] for r in renders)
reuse=skins+["assets/logo/ui.logo-hero.webp","assets/latar/bg.result.temanggung.webp","assets/referensi/reference.03.webp"]+["assets/ikon/map.pin."+s+".webp" for s in ["temanggung","magelang","wonosobo"]]+["assets/ikon/icon."+s for s in ["language-id.webp","language-en.webp","lantern.svg","lock.svg","sound.svg","sound-off.svg","play.svg","pause.svg","menu.svg","book.svg","user.svg","home.svg","logout.svg"]]+["assets/audio/music/map.mp3","assets/audio/sfx/click.mp3","assets/audio/sfx/lock.mp3"]
source_files=[PACKET+s for s in ["preview.html","preview.css","preview.js","aset/icon.kenali.svg","aset/icon.previous.svg","aset/icon.next.svg"]]
browser=json.loads((ROOT/"reports/peta-kedu-04-browser-check.json").read_text(encoding="utf-8"))
assert browser["passed"]
manifest={
    "date":"2026-10-15","timezone":"Asia/Bangkok","page":"peta-kedu","candidate":"04","user_selected":False,
    "method":"One built-in image_gen diorama; full UI composed/rendered with local HTML/CSS/JS and existing catalog skins",
    "generation_calls":1,"prompt_file":PACKET+"prompt-diorama.txt","prompt":(ROOT/(PACKET+"prompt-diorama.txt")).read_text(encoding="utf-8").strip(),
    "input_reference":{"file":"mockup/gambar-mockup/peta-kedu/kandidat/mockup-peta-kedu-kandidat-03-16-9.webp","sha256":sha("mockup/gambar-mockup/peta-kedu/kandidat/mockup-peta-kedu-kandidat-03-16-9.webp")},
    "source_tool_output":str(SOURCE),"native":meta(native),"master":meta(master),"normalization":"RGB conversion and lossless WebP encoding only; no resize, native1774x887",
    "reuse":[{"file":f,"sha256":sha(f)} for f in reuse],"ui_sources":[{"file":f,"sha256":sha(f)} for f in source_files],"renders":renders,
    "slicing":"Three registered clip-path polygons, independently darkened/lightened in CSS; paths/pins/text overlaid, no UI baked into master",
    "preview":page["candidate"]["preview"],"states":page["candidate"]["states"],
    "copy_source":{"file":"update/naskah-cerita/naskah-cerita.md","sha256":sha("update/naskah-cerita/naskah-cerita.md"),"adaptation":"Only preview peta03 copy changes lantern instruction to Kenali button; source and88 narration briefs unchanged"},
    "audio":"Reuse existing3 non-narration MP3, only after click; no narration recording, Play controls typewriter",
    "catalog_data_sha256":sha("data.json"),"preserved_prior_candidates":preserved,"browser_report":"reports/peta-kedu-04-browser-check.json",
    "limits":["Candidate not selected or integrated into original game","Demo unlock fixtures do not establish gameplay rules","Device fullscreen and real-phone testing not performed","Illustrative geography/culture/licensing not verified"]
}
(ROOT/"provenance/peta-kedu-mockup-04-20261015.json").write_text(json.dumps(manifest,ensure_ascii=False,indent=2)+"\n",encoding="utf-8")
report={"date":"2026-10-15","checked_at_utc":datetime.now(timezone.utc).isoformat(),"passed":True,"checks":["Master RGB identical to image_gen output; native and shipped WebP hashes identical","Four full UI renders1920x1080","Six official skin hashes match original generation provenance","data.json and six native/final files for candidates01-03 unchanged","Candidate04 nested preview and7 states/history available without user-selection claim"],"data_sha256":sha("data.json"),"master":meta(master),"renders":renders,"reused_skin_files":skins,"preserved_files":preserved,"scope":"Local mockup/package preservation; browser measurements in separate report"}
(ROOT/"reports/peta-kedu-04-package-check.json").write_text(json.dumps(report,ensure_ascii=False,indent=2)+"\n",encoding="utf-8")
print(json.dumps({"passed":True,"master":meta(master),"skins_preserved":len(skins),"prior_files_preserved":len(preserved)},indent=2))
