"""Narasi GELITA dengan text-to-speech offline: 111 baris × bahasa ID dan EN.

    python3 docs/audio/build_narration.py                 # semua baris, kedua bahasa
    python3 docs/audio/build_narration.py --locale en     # satu bahasa
    python3 docs/audio/build_narration.py --only intro-01,misi-tmg-1
    python3 docs/audio/build_narration.py --force         # buat ulang walau tidak berubah

Mesin suara: Kokoro v1.0 (Apache-2.0) lewat sherpa-onnx, sepenuhnya di
mesin sendiri. Model (±350 MB) diunduh sekali ke ~/.cache/gelita-audio/.
Bahasa Indonesia dibaca dari fonem espeak-ng `id`; bahasa Inggris dari
leksikon Kokoro (US atau GB sesuai suaranya).

Teks diambil dari sumbernya di repositori lewat export-lines.php (naskah
cerita, kartu misi, petunjuk arena cari), jadi kode berkas selalu sama
dengan yang dicari `php spark gelita:narration:import`.

Setiap tokoh punya profil suara per bahasa (VOICES). Pose baris menggeser
tempo dan nada sedikit; Mbah Kedu berpose `weak` bergetar pelan. Hasilnya:
MP3 mono 44,1 kHz 80 kbps, −16 LUFS, puncak ≤ −1 dBTP, jeda 0,3 detik di
awal dan akhir (docs/naskah-cerita.md → Konvensi berkas audio).

Di folder setiap bahasa ditulis `_produksi.json`: sha256, cara produksi
`tts`, dan profil suara setiap berkas. Importer memakainya untuk mengisi
`audio_assets.production_method` / `voice_profile`; berkas yang kemudian
diganti rekaman manusia otomatis tercatat `own_recording`. Baris yang teks
dan profil suaranya tidak berubah dilewati (kecuali --force), jadi isi
berkas lama dan persetujuannya di panel tetap.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import subprocess
import sys
import tarfile
import time
import urllib.request
from dataclasses import dataclass
from fractions import Fraction
from pathlib import Path

import numpy as np
from scipy.signal import resample_poly

from gelita_audio import AUDIO_DIR, ROOT, SR, encode_mp3, loudness, normalize, sha256, true_peak_db, write_json

CACHE = Path.home() / ".cache" / "gelita-audio"
MODEL = "kokoro-multi-lang-v1_0"
MODEL_URL = f"https://github.com/k2-fsa/sherpa-onnx/releases/download/tts-models/{MODEL}.tar.bz2"
ENGINE = "Kokoro v1.0 (sherpa-onnx)"
MANIFEST = "_produksi.json"
BITRATE = 80
PAD = 0.3
# Naikkan bila cara membuat audio berubah, agar semua baris dibuat ulang
RECIPE = 1

SPEAKERS = (
    "af_alloy af_aoede af_bella af_heart af_jessica af_kore af_nicole af_nova af_river af_sarah af_sky "
    "am_adam am_echo am_eric am_fenrir am_liam am_michael am_onyx am_puck am_santa "
    "bf_alice bf_emma bf_isabella bf_lily bm_daniel bm_fable bm_george bm_lewis ef_dora em_alex ff_siwis "
    "hf_alpha hf_beta hm_omega hm_psi if_sara im_nicola jf_alpha jf_gongitsune jf_nezumi jf_tebukuro jm_kumo "
    "pf_dora pm_alex pm_santa zf_xiaobei zf_xiaoni zf_xiaoxiao zf_xiaoyi zm_yunjian zm_yunxi zm_yunxia zm_yunyang em_santa"
).split()


@dataclass(frozen=True)
class Voice:
    speaker: str        # nama suara Kokoro
    speed: float = 1.0  # >1 lebih cepat
    semitones: float = 0.0  # geser nada ala pita (formant ikut bergeser)

    def describe(self, locale: str) -> str:
        shift = f" · nada {self.semitones:+g} st" if self.semitones else ""
        return f"{ENGINE} · {self.speaker} · {locale} · tempo {self.speed:g}{shift}"


# Profil suara per bahasa dan tokoh (docs/naskah-cerita.md → Tokoh dan arahan suara).
# Dipilih dari uji keterbacaan Whisper; lihat docs/audio/README.md.
VOICES: dict[str, dict[str, Voice]] = {
    "id": {
        "narator": Voice("jf_gongitsune", 0.95, -1.5),
        "jaka": Voice("jf_nezumi", 1.0, 1.0),
        "mbah_kedu": Voice("bm_george", 0.88, -1.5),
    },
    "en": {
        "narator": Voice("bf_emma", 0.95),
        "jaka": Voice("af_heart", 1.0, 3.0),
        "mbah_kedu": Voice("bm_george", 0.88, -1.5),
    },
}

# Pose → (pengali tempo, geser nada tambahan)
POSES = {
    "happy": (1.03, 0.3),
    "afraid": (1.06, 0.5),
    "sad": (0.93, -0.3),
    "determined": (1.0, 0.0),
    "worried": (0.96, 0.0),
    "weak": (0.9, -0.4),
}


# ------------------------------------------------------------------ teks


ID_ONES = ["nol", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"]
EN_ONES = ["zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten", "eleven", "twelve",
           "thirteen", "fourteen", "fifteen", "sixteen", "seventeen", "eighteen", "nineteen"]
EN_TENS = ["", "", "twenty", "thirty", "forty", "fifty", "sixty", "seventy", "eighty", "ninety"]


def id_number(n: int) -> str:
    def rest(prefix: str, r: int) -> str:
        return prefix + (" " + id_number(r) if r else "")

    if n < 12:
        return ID_ONES[n]
    if n < 20:
        return ID_ONES[n - 10] + " belas"
    if n < 100:
        return rest(ID_ONES[n // 10] + " puluh", n % 10)
    if n < 200:
        return rest("seratus", n - 100)
    if n < 1000:
        return rest(ID_ONES[n // 100] + " ratus", n % 100)
    if n < 2000:
        return rest("seribu", n - 1000)
    if n < 1_000_000:
        return rest(id_number(n // 1000) + " ribu", n % 1000)
    return rest(id_number(n // 1_000_000) + " juta", n % 1_000_000)


def en_number(n: int) -> str:
    if n < 20:
        return EN_ONES[n]
    if n < 100:
        return EN_TENS[n // 10] + ("-" + EN_ONES[n % 10] if n % 10 else "")
    if n < 1000:
        return EN_ONES[n // 100] + " hundred" + (" and " + en_number(n % 100) if n % 100 else "")
    if n < 1_000_000:
        r = n % 1000
        return en_number(n // 1000) + " thousand" + ((" and " if r < 100 else " ") + en_number(r) if r else "")
    return en_number(n // 1_000_000) + " million" + (" " + en_number(n % 1_000_000) if n % 1_000_000 else "")


def en_year(n: int) -> str:
    """832 → eight thirty-two; 1945 → nineteen forty-five."""
    head, tail = divmod(n, 100)
    if tail == 0:
        return en_number(n)
    return en_number(head) + " " + ("oh " + EN_ONES[tail] if tail < 10 else en_number(tail))


def speakable(text: str, locale: str) -> str:
    """Teks naskah → teks untuk mesin suara (angka dieja, tanda baca dirapikan)."""
    t = re.sub(r"[\"“”]", "", text)
    t = re.sub(r"\s+—\s+|—", ", ", t)
    t = re.sub(r"(?<=\w)–(?=\w)", " dan " if locale == "id" else " and ", t)
    t = t.replace("…", "...")
    if locale == "id":
        t = re.sub(r"\b\d{1,3}(?:\.\d{3})+\b", lambda m: id_number(int(m.group(0).replace(".", ""))), t)
        t = re.sub(r"\b\d+\b", lambda m: id_number(int(m.group(0))), t)
    else:
        t = re.sub(r"\b(\d{3,4}) CE\b", lambda m: en_year(int(m.group(1))) + " C E", t)
        t = re.sub(r"\b\d{1,3}(?:,\d{3})+\b", lambda m: en_number(int(m.group(0).replace(",", ""))), t)
        t = re.sub(r"\b\d+\b", lambda m: en_number(int(m.group(0))), t)
    return re.sub(r"\s+", " ", t).strip()


# ------------------------------------------------------------------ suara


def ensure_model() -> Path:
    path = CACHE / MODEL
    if (path / "model.onnx").is_file():
        return path
    CACHE.mkdir(parents=True, exist_ok=True)
    archive = CACHE / f"{MODEL}.tar.bz2"
    print(f"Mengunduh {MODEL_URL} (±350 MB) …", flush=True)
    urllib.request.urlretrieve(MODEL_URL, archive)
    with tarfile.open(archive) as tar:
        tar.extractall(CACHE, filter="data")
    archive.unlink()
    return path


class Engine:
    """Satu instans Kokoro per varian bahasa espeak-ng (id, en-us, en = Inggris British)."""

    def __init__(self) -> None:
        import sherpa_onnx

        self.sherpa = sherpa_onnx
        self.model = ensure_model()
        self.cache: dict[str, object] = {}

    def tts(self, lang: str):
        if lang not in self.cache:
            k = self.model
            lexicon = "lexicon-gb-en.txt" if lang == "en" else "lexicon-us-en.txt"
            so = self.sherpa
            self.cache[lang] = so.OfflineTts(so.OfflineTtsConfig(
                model=so.OfflineTtsModelConfig(
                    kokoro=so.OfflineTtsKokoroModelConfig(
                        model=str(k / "model.onnx"), voices=str(k / "voices.bin"), tokens=str(k / "tokens.txt"),
                        data_dir=str(k / "espeak-ng-data"), dict_dir=str(k / "dict"),
                        lexicon=f"{k / lexicon},{k / 'lexicon-zh.txt'}", lang=lang,
                    ),
                    num_threads=4,
                ),
                max_num_sentences=1,
            ))
        return self.cache[lang]

    def speak(self, text: str, voice: Voice, locale: str, pose: str | None) -> tuple[np.ndarray, int]:
        speed_mul, extra = POSES.get(pose or "", (1.0, 0.0))
        semis = voice.semitones + extra
        ratio = 2 ** (semis / 12)
        lang = "id" if locale == "id" else ("en" if voice.speaker[0] == "b" else "en-us")
        audio = self.tts(lang).generate(text, sid=SPEAKERS.index(voice.speaker), speed=voice.speed * speed_mul / ratio)
        x = np.asarray(audio.samples, dtype=np.float32)
        sr = audio.sample_rate
        if x.size == 0:
            raise RuntimeError(f"Kokoro tidak menghasilkan audio ({voice.speaker}, {lang}): {text[:60]}")
        if semis:
            # Geser nada ala pita: disintesis lebih lambat r kali, lalu diputar r kali lebih cepat
            frac = Fraction(ratio).limit_denominator(400)
            x = resample_poly(x, frac.denominator, frac.numerator).astype(np.float32)
        if pose == "weak":
            t = np.arange(len(x)) / sr
            x = x * (1 - 0.12 * (0.5 + 0.5 * np.sin(2 * np.pi * 5.2 * t)))
        return x, sr


def trim_and_pad(x: np.ndarray, sr: int) -> np.ndarray:
    """Buang hening di tepi (ambang −45 dB dari puncak), lalu beri jeda PAD detik."""
    frame = int(0.01 * sr)
    n = len(x) // frame
    if n == 0:
        return x
    rms = np.sqrt(np.mean(x[: n * frame].reshape(n, frame) ** 2, axis=1))
    loud = np.nonzero(rms > np.max(rms) * 10 ** (-45 / 20))[0]
    start = max(0, loud[0] * frame - int(0.02 * sr))
    end = min(len(x), (loud[-1] + 1) * frame + int(0.06 * sr))
    y = x[start:end].copy()
    fade = int(0.01 * sr)
    y[:fade] *= np.linspace(0, 1, fade)
    y[-fade:] *= np.linspace(1, 0, fade)
    pad = np.zeros(int(PAD * sr), dtype=np.float32)
    return np.concatenate([pad, y, pad])


def to_44k(x: np.ndarray, sr: int) -> np.ndarray:
    frac = Fraction(SR, sr)
    return resample_poly(x, frac.numerator, frac.denominator).astype(np.float32)


# ------------------------------------------------------------------ utama


def load_lines() -> list[dict]:
    out = subprocess.run(["php", str(ROOT / "docs" / "audio" / "export-lines.php")], capture_output=True, text=True, check=True)
    return json.loads(out.stdout)


def main() -> int:
    parser = argparse.ArgumentParser(description="Narasi GELITA dengan text-to-speech offline (Kokoro).")
    parser.add_argument("--locale", choices=["id", "en"], help="hanya satu bahasa")
    parser.add_argument("--only", help="kode berkas dipisah koma, mis. intro-01,misi-tmg-1")
    parser.add_argument("--force", action="store_true", help="buat ulang walau teks dan profil suara sama")
    args = parser.parse_args()

    lines = load_lines()
    only = set(args.only.split(",")) if args.only else None
    unknown = (only or set()) - {line["code"] for line in lines}
    if unknown:
        print("Kode tidak dikenal: " + ", ".join(sorted(unknown)), file=sys.stderr)
        return 1

    engine = Engine()

    for locale in [args.locale] if args.locale else ["id", "en"]:
        folder = AUDIO_DIR / "narasi" / locale
        folder.mkdir(parents=True, exist_ok=True)
        manifest_path = folder / MANIFEST
        manifest = json.loads(manifest_path.read_text(encoding="utf-8")) if manifest_path.is_file() else {}
        files = manifest.get("files", {})
        made = skipped = 0
        started = time.time()

        for line in lines:
            code = line["code"]
            if only and code not in only:
                continue
            voice = VOICES[locale][line["character"]]
            text = speakable(line["text"][locale], locale)
            name = f"{code}.mp3"
            path = folder / name
            source = hashlib.sha256(json.dumps([RECIPE, text, voice.describe(locale), line["pose"]]).encode()).hexdigest()
            entry = files.get(name)

            if not args.force and entry and entry.get("source") == source and path.is_file() and sha256(path) == entry.get("sha256"):
                skipped += 1
                continue

            x, sr = engine.speak(text, voice, locale, line["pose"])
            y = normalize(to_44k(trim_and_pad(x, sr), sr), -16.0, ceiling_dbtp=-1.5)
            encode_mp3(path, y, BITRATE)
            files[name] = {
                "sha256": sha256(path),
                "production_method": "tts",
                "voice_profile": voice.describe(locale) + (f" · pose {line['pose']}" if line["pose"] else ""),
                "source": source,
            }
            made += 1
            print(f"{locale}/{name:28s} {len(y) / SR:5.1f} dtk  {loudness(y):6.1f} LUFS  {true_peak_db(y):5.1f} dBTP", flush=True)

        # Hanya baris yang masih ada di naskah
        codes = {f"{line['code']}.mp3" for line in lines}
        files = {k: files[k] for k in sorted(files) if k in codes}
        write_json(manifest_path, {
            "keterangan": "Dibuat docs/audio/build_narration.py. Dibaca App\\Libraries\\NarrationImporter: "
                          "berkas yang sha256-nya cocok dicatat production_method=tts.",
            "files": files,
        })
        print(f"{locale}: {made} dibuat, {skipped} tidak berubah ({time.time() - started:.0f} dtk)", flush=True)

    return 0


if __name__ == "__main__":
    sys.exit(main())
