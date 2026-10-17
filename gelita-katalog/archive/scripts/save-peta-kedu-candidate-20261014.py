"""One-time packaging of the requested Peta Kedu candidate; no generation."""
import hashlib
import json
from pathlib import Path
from PIL import Image, ImageOps

ROOT = Path(__file__).resolve().parents[2]
SOURCE = Path(r"C:\Users\cenda\.codex\generated_images\01a13941-052e-7d82-ada6-57e8a0c2f92c\exec-972b2750-87cf-41f6-9f2a-0976dc029b1f.png")
FOLDER = ROOT / "mockup/gambar-mockup/peta-kedu/kandidat"
native_file = FOLDER / "mockup-peta-kedu-kandidat-01-native.webp"
final_file = FOLDER / "mockup-peta-kedu-kandidat-01-16-9.webp"
im = Image.open(SOURCE).convert("RGB")
im.save(native_file, "WEBP", lossless=True)
final = ImageOps.fit(im, (1920, 1080), method=Image.Resampling.LANCZOS, centering=(0.5, 0.5))
final.save(final_file, "WEBP", lossless=True)
def digest(p):
    return hashlib.sha256(p.read_bytes()).hexdigest()
def relative(p):
    return p.relative_to(ROOT).as_posix()
refs = ["assets/peta/map.kedu.webp","assets/latar/bg.map.webp","mockup/gambar-mockup/tantangan-selesai-temanggung/mockup-tantangan-selesai-temanggung-16-9.webp","assets/referensi/reference.01.webp"]
manifest = {
    "date": "2026-10-14", "timezone": "Asia/Bangkok",
    "page": "peta-kedu", "status": "candidate", "user_selected": False,
    "method": "built-in image_gen", "generation_calls": 1,
    "prompt_file": relative(FOLDER / "prompt-aktual.txt"),
    "prompt": (FOLDER / "prompt-aktual.txt").read_text(encoding="utf-8").strip(),
    "references": [{"file": p, "sha256": digest(ROOT / p)} for p in refs],
    "source_tool_output": str(SOURCE),
    "native": {"file": relative(native_file), "size": list(im.size), "sha256": digest(native_file)},
    "output": {"file": relative(final_file), "size": list(final.size), "format": "WebP",
               "alpha": False, "sha256": digest(final_file)},
    "normalization": "Proportional LANCZOS resize with tiny centered crop to exact 16:9; no new AI detail.",
    "example_state": {"region": "Temanggung", "available": ["Temanggung"],
                      "locked": ["Wonosobo", "Magelang"], "shards": "1 / 12", "label": "Data contoh"},
    "scope": "Full UI visual candidate only; no approved design, interactive preview or game integration.",
    "review_limits": ["Illustrative geography, not a verified scale map", "Culture and licensing not verified",
                      "HTML text, translations, touch targets and interaction not yet implemented"],
    "catalog_data_sha256": digest(ROOT / "data.json")
}
(ROOT / "provenance/peta-kedu-mockup-20261014.json").write_text(
    json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
print(json.dumps({"native": manifest["native"], "output": manifest["output"]}, indent=2))
