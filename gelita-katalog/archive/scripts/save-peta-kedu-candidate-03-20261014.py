"""One-time mockup packaging and proposed geometry checks; no generation."""
import hashlib
import json
from pathlib import Path
from datetime import datetime, timezone
from PIL import Image, ImageOps, ImageChops

ROOT = Path(__file__).resolve().parents[2]
SOURCES = [Path(r"C:\Users\cenda\.codex\generated_images\01a13941-052e-7d82-ada6-57e8a0c2f92c\exec-61b57581-40ae-4651-b489-b8ba7926e8c3.png"), Path(r"C:\Users\cenda\.codex\generated_images\01a13941-052e-7d82-ada6-57e8a0c2f92c\exec-f17b6fc4-5831-4736-83da-e82248358710.png"), Path(r"C:\Users\cenda\.codex\generated_images\01a13941-052e-7d82-ada6-57e8a0c2f92c\exec-677e5d2e-a744-4a02-9b42-a50b3a64e79e.png")]
FOLDER = ROOT / "mockup/gambar-mockup/peta-kedu/kandidat"
HISTORY = ROOT / "provenance/peta-kedu-revision-03"
HISTORY.mkdir(exist_ok=True)
def digest(p):
    return hashlib.sha256(p.read_bytes()).hexdigest()
def relative(p):
    return p.relative_to(ROOT).as_posix()
drafts = []
for source, name in zip(SOURCES[:2], ["draf-awal.webp", "draf-koreksi-ukuran.webp"]):
    file = HISTORY / name
    im = Image.open(source).convert("RGB")
    im.save(file, "WEBP", lossless=True)
    drafts.append({"file": relative(file), "source_tool_output": str(source), "size": list(im.size), "sha256": digest(file)})
im = Image.open(SOURCES[-1]).convert("RGB")
native = FOLDER / "mockup-peta-kedu-kandidat-03-native.webp"
output = FOLDER / "mockup-peta-kedu-kandidat-03-16-9.webp"
im.save(native, "WEBP", lossless=True)
final = ImageOps.fit(im, (1920, 1080), method=Image.Resampling.LANCZOS, centering=(0.5, 0.5))
final.save(output, "WEBP", lossless=True)
assert ImageChops.difference(im, Image.open(native).convert("RGB")).getbbox() is None
assert Image.open(output).size == (1920, 1080) and Image.open(output).mode == "RGB"
refs = ["mockup/gambar-mockup/peta-kedu/kandidat/mockup-peta-kedu-kandidat-02-16-9.webp","mockup/gambar-mockup/tantangan-selesai-temanggung/mockup-tantangan-selesai-temanggung-16-9.webp","assets/referensi/reference.01.webp"]
prompt_files = [
    FOLDER / "prompt-aktual-03.txt", FOLDER / "prompt-koreksi-03.txt", FOLDER / "prompt-koreksi-jarak-03.txt"]
manifest = {
    "date": "2026-10-14", "timezone": "Asia/Bangkok", "page": "peta-kedu", "candidate": "03",
    "status": "candidate", "user_selected": False, "method": "built-in image_gen", "generation_calls": 3,
    "authorization": "User explicitly permitted free mockup redesign, larger touch UI and a new diorama proposal; user will implement system code later.",
    "jobs": [{"kind": k, "prompt_file": relative(f), "prompt": f.read_text(encoding="utf-8").strip()}
             for k, f in zip(["generation", "size-and-stars-correction", "story-footer-spacing-correction"], prompt_files)],
    "references": [{"file": f, "sha256": digest(ROOT / f)} for f in refs],
    "correction_inputs": drafts, "drafts": drafts,
    "source_tool_output": str(SOURCES[-1]),
    "native": {"file": relative(native), "size": list(im.size), "sha256": digest(native)},
    "output": {"file": relative(output), "size": [1920, 1080], "format": "WebP", "alpha": False, "sha256": digest(output)},
    "normalization": "Proportional LANCZOS resize with tiny center crop to exact16:9; no new AI detail.",
    "design": {"map": "New illustrative diorama proposal, not an existing map asset replacement",
               "selection": "Three large city groups feed one shared selected-region panel",
               "narration": "Teal circular Putar vs long gold Berikutnya; gray previous; lifted navy story panel and Jaka portrait",
               "hud": "Language/sound/menu as three large controls matching selected result; home/fullscreen/logout/profile accessible through planned menu",
               "stars": "Three gold display-only stars match previously observed example state",
               "kenali": "Large glowing lantern integrated into wide selected-region action, no tiny pin-adjacent control",
               "state": "9/12 shards; Wonosobo selected1/4; Temanggung and Magelang4/4"},
    "touch_plan": "mockup/gambar-mockup/peta-kedu/target-sentuh-03.json",
    "catalog_data_sha256": digest(ROOT / "data.json"),
    "scope": "Unselected visual candidate and implementation guidance only; no interactive Peta Kedu UI, audio production, application or database changes.",
    "limits": ["Proposed target rectangles are not measurements of image pixels or implemented DOM",
               "Real-device touch, language overlays and fullscreen not tested", "Diorama geography/culture/licensing not verified",
               "Diorama is embedded in full UI candidate; separate background not generated"]
}
(ROOT / "provenance/peta-kedu-mockup-03-20261014.json").write_text(json.dumps(manifest, ensure_ascii=False, indent=2)+"\n", encoding="utf-8")
plan = json.loads((ROOT / manifest["touch_plan"]).read_text(encoding="utf-8"))
targets = plan["targets"]
for t in targets:
    assert t["x"] >= 0 and t["y"] >= 0
    assert t["x"]+t["width"] <= 1920 and t["y"]+t["height"] <= 1080
for i, a in enumerate(targets):
    for b in targets[i+1:]:
        assert not (max(a["x"],b["x"]) < min(a["x"]+a["width"],b["x"]+b["width"]) and
                    max(a["y"],b["y"]) < min(a["y"]+a["height"],b["y"]+b["height"])), (a["id"], b["id"])
viewport_checks = []
qa = []
for v in plan["viewports"]:
    scale = min(v["width"]/1920, v["height"]/1080)
    minimum = min(min(t["width"],t["height"])*scale for t in targets)
    assert minimum >= plan["minimum_screen_target"]
    viewport_checks.append({"viewport": [v["width"],v["height"]], "scale": scale, "minimum_proposed_target_px": round(minimum,3)})
    file = ROOT / f"review/mockup-peta-kedu-03-{v['width']}x{v['height']}.webp"
    final.resize((v["width"],v["height"]), Image.Resampling.LANCZOS).save(file, "WEBP", lossless=True)
    qa.append(relative(file))
preserved = []
for file in ["peta-kedu-mockup-20261014.json", "peta-kedu-mockup-02-20261014.json"]:
    old = json.loads((ROOT / "provenance" / file).read_text(encoding="utf-8"))
    assert digest(ROOT / "data.json") == old["catalog_data_sha256"]
    for key in ["native", "output"]:
        assert digest(ROOT / old[key]["file"]) == old[key]["sha256"]
        preserved.append(old[key]["file"])
report = {"date": "2026-10-14", "checked_at_utc": datetime.now(timezone.utc).isoformat(),
          "passed": True, "scope": "Raster packaging, preservation and mathematical checks of proposed code targets; NOT DOM or device touch validation",
          "output": manifest["output"], "checks": ["Native RGB identical to tool", "Final opaque WebP1920x1080",
                    "Candidate01/02 final/native hashes unchanged", "data.json hash unchanged",
                    "14 proposed targets inside canvas and do not overlap", "Proposed target minimum44px in both requested landscape sizes"],
          "proposed_viewport_checks": viewport_checks, "proposed_story_footer_gap_base_px": 46,
          "proposed_avatar_footer_gap_base_px": 49, "phone_visual_previews": qa, "preserved_files": preserved,
          "limits": manifest["limits"]}
(ROOT / "reports/peta-kedu-candidate-03-check-20261014.json").write_text(json.dumps(report, ensure_ascii=False, indent=2)+"\n", encoding="utf-8")
print(json.dumps({"output": manifest["output"], "proposed_target_checks": viewport_checks, "phone_visual_previews": qa}, indent=2))
