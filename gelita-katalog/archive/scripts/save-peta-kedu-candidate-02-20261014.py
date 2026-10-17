"""One-time packaging of candidate 02, not an image-generation or maintenance command."""
import hashlib
import json
from pathlib import Path
from PIL import Image, ImageOps, ImageChops

ROOT = Path(__file__).resolve().parents[2]
SOURCE = Path(r"C:\Users\cenda\.codex\generated_images\01a13941-052e-7d82-ada6-57e8a0c2f92c\exec-c1137ad0-04de-4263-84e6-f7df1a57a1e7.png")
FOLDER = ROOT / "mockup/gambar-mockup/peta-kedu/kandidat"
im = Image.open(SOURCE).convert("RGB")
native = FOLDER / "mockup-peta-kedu-kandidat-02-native.webp"
output = FOLDER / "mockup-peta-kedu-kandidat-02-16-9.webp"
im.save(native, "WEBP", lossless=True)
ImageOps.fit(im, (1920, 1080), method=Image.Resampling.LANCZOS, centering=(0.5, 0.5)).save(output, "WEBP", lossless=True)
def digest(p):
    return hashlib.sha256(p.read_bytes()).hexdigest()
def relative(p):
    return p.relative_to(ROOT).as_posix()
reference_files = [
    "provenance/peta-kedu-revision-02/referensi/screenshot-web-asli-01.webp",
    "provenance/peta-kedu-revision-02/referensi/screenshot-web-asli-02.webp",
    "assets/peta/map.kedu.webp",
    "mockup/gambar-mockup/tantangan-selesai-temanggung/mockup-tantangan-selesai-temanggung-16-9.webp",
    "assets/referensi/reference.01.webp"
]
prompt_file = FOLDER / "prompt-aktual-02.txt"
manifest = {
    "date": "2026-10-14", "timezone": "Asia/Bangkok", "page": "peta-kedu",
    "candidate": "02", "status": "candidate", "user_selected": False,
    "method": "built-in image_gen", "generation_calls": 1,
    "prompt_file": relative(prompt_file), "prompt": prompt_file.read_text(encoding="utf-8").strip(),
    "references": [{"file": p, "sha256": digest(ROOT / p)} for p in reference_files],
    "source_tool_output": str(SOURCE),
    "native": {"file": relative(native), "size": list(im.size), "sha256": digest(native)},
    "output": {"file": relative(output), "size": [1920, 1080], "format": "WebP", "alpha": False, "sha256": digest(output)},
    "normalization": "Proportional LANCZOS resize with tiny centered crop to exact 16:9; no new AI detail.",
    "inspection": {
        "url": "https://gelita.id/peta#app", "browser": "Existing user Chrome tab",
        "method": "Read-only DOM and screenshot through cua_repl; no game controls activated",
        "original_pin_style_percent": {"temanggung": {"left": 65, "top": 41}, "magelang": {"left": 56, "top": 71}, "wonosobo": {"left": 28, "top": 55}},
        "observed_progress": {"total_shards": "9/12", "temanggung": "Tuntas · 4/4", "magelang": "Tuntas · 4/4", "wonosobo": "Sedang dijelajahi · 1/4"},
        "functions": ["Two language choices", "Sound", "Fullscreen", "Home/map", "Profile", "Exit", "Three region routes", "Kenali per region", "Region difficulty/progress/stars", "Three-part Jaka story and previous/play/next controls", "Pustaka Kedu"]
    },
    "scope": "Limited visual revision preserving actual page structure; three compact cards and separate story bar. No total redesign or web changes.",
    "pin_layout_note": "AI raster positions approximate source CSS anchors. Wonosobo appears slightly lower; Magelang slightly right and lower; Temanggung retains its relative position. No production coordinates changed. See local posisi-titik.md.",
    "catalog_data_sha256": digest(ROOT / "data.json"),
    "review_limits": ["Illustrative geography not verified", "Interactive UI, text translations and hit areas not implemented", "Culture and licensing not verified", "Approval of candidate pending"]
}
(ROOT / "provenance/peta-kedu-mockup-02-20261014.json").write_text(json.dumps(manifest, ensure_ascii=False, indent=2)+"\n", encoding="utf-8")
assert ImageChops.difference(im, Image.open(native).convert("RGB")).getbbox() is None
final = Image.open(output)
assert final.size == (1920, 1080) and final.mode == "RGB"
print(json.dumps({"native": manifest["native"], "output": manifest["output"], "checks": ["Native RGB identical to tool", "Final RGB 1920x1080"]}, indent=2))
