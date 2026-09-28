"""Bantuan bersama pembuat audio GELITA: kenyaringan, puncak, dan MP3.

Dipakai build_narration.py dan build_music_sfx.py. MP3 dikodekan dengan
ffmpeg + libmp3lame dari paket pip imageio-ffmpeg, sehingga tidak perlu
ffmpeg sistem. Header Info/LAME ikut ditulis, jadi durasi terbaca tepat oleh
MediaStore::durationMs() dan browser dapat memangkas jeda encoder.
"""

from __future__ import annotations

import hashlib
import json
import subprocess
from fractions import Fraction
from pathlib import Path

import numpy as np
import pyloudnorm
from scipy.signal import resample_poly

ROOT = Path(__file__).resolve().parents[2]
AUDIO_DIR = ROOT / "public" / "assets" / "audio"
SR = 44100


def ffmpeg_exe() -> str:
    import imageio_ffmpeg

    return imageio_ffmpeg.get_ffmpeg_exe()


def resample(x: np.ndarray, sr_in: int, sr_out: int) -> np.ndarray:
    """Resample polifase (sumbu 0 = waktu)."""
    if sr_in == sr_out:
        return x.astype(np.float32)
    ratio = Fraction(sr_out, sr_in)
    return resample_poly(x, ratio.numerator, ratio.denominator, axis=0).astype(np.float32)


def loudness(x: np.ndarray, sr: int = SR) -> float:
    """Kenyaringan terpadu ITU-R BS.1770-4 (LUFS)."""
    return float(pyloudnorm.Meter(sr).integrated_loudness(x.astype(np.float64)))


def true_peak_db(x: np.ndarray) -> float:
    """Puncak sebenarnya (dBTP), oversampling 4×."""
    over = resample_poly(x, 4, 1, axis=0)
    peak = float(np.max(np.abs(over))) if over.size else 0.0
    return 20 * np.log10(max(peak, 1e-9))


def soft_limit(x: np.ndarray, ceiling_db: float) -> np.ndarray:
    """Pembatas lembut: sampel di atas ~80 % ambang dilengkungkan (tanh)."""
    ceiling = 10 ** (ceiling_db / 20)
    knee = 0.8 * ceiling
    y = x.copy()
    over = np.abs(y) > knee
    y[over] = np.sign(y[over]) * (knee + (ceiling - knee) * np.tanh((np.abs(y[over]) - knee) / (ceiling - knee)))
    return y


def normalize(x: np.ndarray, target_lufs: float, ceiling_dbtp: float = -1.0, sr: int = SR) -> np.ndarray:
    """Samakan kenyaringan ke target, lalu pastikan puncak ≤ ambang."""
    gain = 10 ** ((target_lufs - loudness(x, sr)) / 20)
    y = (x * gain).astype(np.float32)
    for _ in range(4):
        if true_peak_db(y) <= ceiling_dbtp:
            break
        y = soft_limit(y, ceiling_dbtp - 0.3)
    return y


def encode_mp3(path: Path, x: np.ndarray, bitrate_k: int, sr: int = SR, title: str | None = None) -> None:
    """Tulis float32 (mono: (n,), stereo: (n, 2)) sebagai MP3 CBR."""
    channels = 1 if x.ndim == 1 else x.shape[1]
    path.parent.mkdir(parents=True, exist_ok=True)
    cmd = [
        ffmpeg_exe(), "-hide_banner", "-loglevel", "error", "-y",
        "-f", "f32le", "-ar", str(sr), "-ac", str(channels), "-i", "pipe:0",
        "-c:a", "libmp3lame", "-b:a", f"{bitrate_k}k", "-ar", str(sr),
        "-map_metadata", "-1", "-fflags", "+bitexact", "-flags:a", "+bitexact",
    ]
    if title:
        cmd += ["-metadata", f"title={title}"]
    cmd.append(str(path))
    data = np.clip(x, -1.0, 1.0).astype("<f4").tobytes()
    subprocess.run(cmd, input=data, check=True)


def decode(path: Path, sr: int = SR, channels: int = 1) -> np.ndarray:
    """Dekode berkas audio apa pun ke float32 lewat ffmpeg."""
    cmd = [
        ffmpeg_exe(), "-hide_banner", "-loglevel", "error", "-i", str(path),
        "-f", "f32le", "-ar", str(sr), "-ac", str(channels), "pipe:1",
    ]
    raw = subprocess.run(cmd, capture_output=True, check=True).stdout
    x = np.frombuffer(raw, dtype="<f4").astype(np.float32)
    return x if channels == 1 else x.reshape(-1, channels)


def sha256(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def write_json(path: Path, data: object) -> None:
    path.write_text(json.dumps(data, ensure_ascii=False, indent=1) + "\n", encoding="utf-8")
