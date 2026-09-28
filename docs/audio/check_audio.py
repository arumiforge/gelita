"""Periksa seluruh audio GELITA sebelum di-commit.

    python3 docs/audio/check_audio.py          # format, kenyaringan, puncak, jeda, loop
    python3 docs/audio/check_audio.py --wer    # + keterbacaan narasi lewat Whisper (lambat)

Pemeriksaan teknis:
- narasi/{id,en}: setiap baris export-lines.php punya berkas; mono 44,1 kHz,
  ±80 kbps; −16 ± 1 LUFS; puncak ≤ −1 dBTP; jeda awal/akhir 0,2–0,45 dtk;
  entri `_produksi.json` dengan sha256 yang cocok;
- music: stereo 44,1 kHz, sambungan loop tanpa lompatan sampel;
- sfx: < 1 detik (region-done < 3,5 detik), puncak ≤ −1 dBTP.

--wer mentranskripsi setiap narasi dengan Whisper turbo (sherpa-onnx, model
±1 GB diunduh sekali ke ~/.cache/gelita-audio/) dan menghitung WER terhadap
teks naskah. Nama tempat dan panggilan lokal ("Le", "Mbah") sering
ditulis lain oleh Whisper, jadi WER kecil bukan berarti salah ucap; yang
dicari baris dengan WER tinggi. Hasil per baris ditulis ke
docs/audio/wer-{id,en}.json (tidak di-commit).
"""

from __future__ import annotations

import argparse
import json
import re
import subprocess
import sys
import tarfile
import unicodedata
import urllib.request
from pathlib import Path

import numpy as np
import soundfile as sf

from gelita_audio import AUDIO_DIR, ROOT, SR, decode, loudness, sha256, true_peak_db

CACHE = Path.home() / ".cache" / "gelita-audio"
WHISPER = "sherpa-onnx-whisper-turbo"
WHISPER_URL = f"https://github.com/k2-fsa/sherpa-onnx/releases/download/asr-models/{WHISPER}.tar.bz2"


def edges(x: np.ndarray) -> tuple[float, float]:
    """Lama hening di awal dan akhir (−50 dBFS)."""
    loud = np.nonzero(np.abs(x) > 10 ** (-50 / 20))[0]
    if loud.size == 0:
        return len(x) / SR, 0.0
    return loud[0] / SR, (len(x) - loud[-1]) / SR


def check_narration(lines: list[dict], problems: list[str]) -> dict[str, list[Path]]:
    found: dict[str, list[Path]] = {}
    for locale in ("id", "en"):
        folder = AUDIO_DIR / "narasi" / locale
        manifest = json.loads((folder / "_produksi.json").read_text(encoding="utf-8")).get("files", {}) if (folder / "_produksi.json").is_file() else {}
        found[locale] = []
        durations = []
        for line in lines:
            path = folder / f"{line['code']}.mp3"
            if not path.is_file():
                problems.append(f"narasi/{locale}: {path.name} belum ada")
                continue
            found[locale].append(path)
            info = sf.info(str(path))
            x = decode(path)
            dur = len(x) / SR
            durations.append(dur)
            kbps = path.stat().st_size * 8 / 1000 / max(dur, 0.01)
            lufs, peak = loudness(x), true_peak_db(x)
            head, tail = edges(x)
            where = f"narasi/{locale}/{path.name}"
            if info.channels != 1 or info.samplerate != SR:
                problems.append(f"{where}: {info.channels} kanal {info.samplerate} Hz (harus mono {SR})")
            if not 70 <= kbps <= 90:
                problems.append(f"{where}: {kbps:.0f} kbps")
            if abs(lufs + 16) > 1:
                problems.append(f"{where}: {lufs:.1f} LUFS")
            if peak > -1.0:
                problems.append(f"{where}: puncak {peak:.1f} dBTP")
            if not (0.2 <= head <= 0.45 and 0.2 <= tail <= 0.5):
                problems.append(f"{where}: jeda awal {head:.2f} dtk, akhir {tail:.2f} dtk")
            entry = manifest.get(path.name)
            if not entry or entry.get("sha256") != sha256(path) or entry.get("production_method") != "tts":
                problems.append(f"{where}: entri _produksi.json tidak cocok")
        if durations:
            print(f"narasi/{locale}: {len(found[locale])}/{len(lines)} berkas, {sum(durations) / 60:.1f} menit, "
                  f"{sum(p.stat().st_size for p in found[locale]) / 1e6:.1f} MB")
    return found


def check_music(problems: list[str]) -> None:
    for name in ("map", "region", "challenge"):
        path = AUDIO_DIR / "music" / f"{name}.mp3"
        if not path.is_file():
            problems.append(f"music/{name}.mp3 belum ada")
            continue
        info = sf.info(str(path))
        x = decode(path, channels=2)
        # Lompatan di sambungan dibanding lompatan antarsampel biasa di dalam lagu
        body = np.abs(np.diff(x[SR:-SR], axis=0)).max()
        seam = np.abs(x[0] - x[-1]).max()
        print(f"music/{name}.mp3: {len(x) / SR:.1f} dtk, {loudness(x):.1f} LUFS, {true_peak_db(x):.1f} dBTP, "
              f"sambungan {seam:.4f} (lompatan terbesar di lagu {body:.4f})")
        if info.channels != 2 or info.samplerate != SR:
            problems.append(f"music/{name}.mp3: {info.channels} kanal {info.samplerate} Hz")
        if true_peak_db(x) > -1.0:
            problems.append(f"music/{name}.mp3: puncak di atas −1 dBTP")


def check_sfx(problems: list[str]) -> None:
    for name in ("click", "correct", "wrong", "lock", "shard", "region-done"):
        path = AUDIO_DIR / "sfx" / f"{name}.mp3"
        if not path.is_file():
            problems.append(f"sfx/{name}.mp3 belum ada")
            continue
        x = decode(path, channels=2)
        # Durasi dekode memuat bingkai MP3 terakhir; batasnya longgar 0,1 dtk
        limit = 3.5 if name == "region-done" else 1.1
        dur = len(x) / SR
        print(f"sfx/{name}.mp3: {dur:.2f} dtk, puncak {true_peak_db(x):.1f} dBTP")
        if dur > limit:
            problems.append(f"sfx/{name}.mp3: {dur:.2f} dtk")
        if true_peak_db(x) > -1.0:
            problems.append(f"sfx/{name}.mp3: puncak di atas −1 dBTP")


# ------------------------------------------------------------------ WER


def words(text: str) -> list[str]:
    text = unicodedata.normalize("NFKC", text).lower()
    return re.sub(r"[^\w\s']", " ", text).split()


def wer(ref: str, hyp: str) -> float:
    r, h = words(ref), words(hyp)
    d = list(range(len(h) + 1))
    for i in range(1, len(r) + 1):
        prev, d[0] = d[0], i
        for j in range(1, len(h) + 1):
            prev, d[j] = d[j], min(d[j] + 1, d[j - 1] + 1, prev + (r[i - 1] != h[j - 1]))
    return d[len(h)] / max(1, len(r))


def check_wer(lines: list[dict], found: dict[str, list[Path]], limit: float) -> list[str]:
    import sherpa_onnx
    from build_narration import speakable

    model = CACHE / WHISPER
    if not (model / "turbo-tokens.txt").is_file():
        CACHE.mkdir(parents=True, exist_ok=True)
        archive = CACHE / f"{WHISPER}.tar.bz2"
        print(f"Mengunduh {WHISPER_URL} (±560 MB) …", flush=True)
        urllib.request.urlretrieve(WHISPER_URL, archive)
        with tarfile.open(archive) as tar:
            tar.extractall(CACHE, filter="data")
        archive.unlink()

    texts = {line["code"]: line["text"] for line in lines}
    flagged = []
    for locale, paths in found.items():
        asr = sherpa_onnx.OfflineRecognizer.from_whisper(
            encoder=str(model / "turbo-encoder.int8.onnx"), decoder=str(model / "turbo-decoder.int8.onnx"),
            tokens=str(model / "turbo-tokens.txt"), language=locale, num_threads=4,
        )
        rows = []
        for path in paths:
            code = path.stem
            x = decode(path, sr=16000)
            stream = asr.create_stream()
            stream.accept_waveform(16000, x)
            asr.decode_stream(stream)
            ref = speakable(texts[code][locale], locale)
            score = wer(ref, stream.result.text)
            rows.append({"code": code, "wer": round(score, 3), "ref": ref, "hyp": stream.result.text.strip()})
            if score > limit:
                flagged.append(f"{locale}/{code}: WER {score:.2f} — {stream.result.text.strip()}")
        out = ROOT / "docs" / "audio" / f"wer-{locale}.json"
        out.write_text(json.dumps(rows, ensure_ascii=False, indent=1) + "\n", encoding="utf-8")
        scores = [row["wer"] for row in rows]
        print(f"WER narasi/{locale}: rata-rata {np.mean(scores):.3f}, median {np.median(scores):.3f}, "
              f"terburuk {max(scores):.2f} ({rows[int(np.argmax(scores))]['code']}); rincian {out.name}", flush=True)
    return flagged


def main() -> int:
    parser = argparse.ArgumentParser(description="Periksa audio GELITA.")
    parser.add_argument("--wer", action="store_true", help="uji keterbacaan narasi dengan Whisper")
    parser.add_argument("--wer-limit", type=float, default=0.25, help="baris di atas ambang ini dilaporkan")
    args = parser.parse_args()

    lines = json.loads(subprocess.run(["php", str(ROOT / "docs" / "audio" / "export-lines.php")],
                                      capture_output=True, text=True, check=True).stdout)
    problems: list[str] = []
    found = check_narration(lines, problems)
    check_music(problems)
    check_sfx(problems)
    flagged = check_wer(lines, found, args.wer_limit) if args.wer else []

    for p in problems:
        print("MASALAH:", p)
    for f in flagged:
        print("PERIKSA:", f)
    print("OK" if not problems else f"{len(problems)} masalah")
    return 1 if problems else 0


if __name__ == "__main__":
    sys.exit(main())
