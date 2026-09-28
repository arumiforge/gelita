"""Mastering musik & efek suara → MP3 di public/assets/audio/{music,sfx}/.

python master.py music|sfx [DEST_DIR]
"""
import subprocess
import sys
from pathlib import Path

import numpy as np
import pyloudnorm as pyln
import soundfile as sf

import paths

# core/audio.js memakai volume Howler dua kali (per bunyi × global; bawaan 0,8 × 0,8 ≈ −3,9 dB),
# sedangkan narasi (−16 LUFS) tidak. Target di bawah sudah memperhitungkannya: musik tetap
# ±8–9 LU di bawah narasi yang diputar di atasnya.
MUSIC_LUFS = {'map': -20.0, 'region': -20.5, 'challenge': -21.5}
# SFX diukur dengan hening dibantalkan ke 3 s agar bunyi pendek punya blok gating
SFX_LUFS = {'click': -24.0, 'lock': -20.0, 'wrong': -19.0, 'correct': -17.0, 'shard': -16.5, 'region-done': -15.0}


def true_peak(x: np.ndarray, sr: int) -> float:
    from scipy.signal import resample_poly
    up = resample_poly(x, 4, 1, axis=0)
    return float(np.max(np.abs(up)))


def limit(x: np.ndarray, ceiling: float) -> np.ndarray:
    """Limiter lembut dengan look-ahead; tanpa lengkung waktu (aman untuk loop karena gain ≈ 1)."""
    from scipy.ndimage import minimum_filter1d, uniform_filter1d
    sr = 44100
    peak = np.max(np.abs(x), axis=1)
    need = np.minimum(1.0, ceiling / np.maximum(peak, 1e-9))
    need = minimum_filter1d(need, size=int(0.01 * sr), mode='wrap')
    g = uniform_filter1d(need, size=int(0.01 * sr), mode='wrap')
    g = np.minimum(g, need)
    return x * g[:, None]


def glue(x: np.ndarray, sr: int, ratio: float = 2.5, above_db: float = 5.0) -> np.ndarray:
    """Kompresor lembut untuk loop: puncak (gong, kenong) diredam di atas median + above_db.

    Dihitung pada dua putaran berurutan dan diambil putaran kedua, sehingga
    keadaan kompresor di titik sambung sama dengan di awal (loop tetap mulus).
    """
    from scipy.ndimage import uniform_filter1d
    xx = np.concatenate([x, x])
    power = uniform_filter1d(np.mean(xx ** 2, axis=1), size=int(0.05 * sr), mode='wrap')
    level = 10 * np.log10(power + 1e-12)
    thresh = np.median(level[len(x):]) + above_db
    over = np.maximum(0.0, level - thresh)
    target = -over * (1 - 1 / ratio)
    # serangan 20 ms, pelepasan 450 ms
    a_att, a_rel = np.exp(-1 / (0.02 * sr)), np.exp(-1 / (0.45 * sr))
    g = np.empty_like(target)
    cur = 0.0
    for i, v in enumerate(target):
        cur = a_att * cur + (1 - a_att) * v if v < cur else a_rel * cur + (1 - a_rel) * v
        g[i] = cur
    gain = 10 ** (g[len(x):] / 20)
    return x * gain[:, None]


def encode(wav: Path, mp3: Path, kbps: int, mono: bool = False) -> None:
    mp3.parent.mkdir(parents=True, exist_ok=True)
    cmd = ['ffmpeg', '-hide_banner', '-loglevel', 'error', '-y', '-i', str(wav), '-map_metadata', '-1',
           '-ar', '44100', '-ac', '1' if mono else '2', '-codec:a', 'libmp3lame', '-b:a', f'{kbps}k',
           '-write_xing', '1', '-id3v2_version', '0', str(mp3)]
    subprocess.run(cmd, check=True)


def master_music(dest: Path) -> None:
    for name, target in MUSIC_LUFS.items():
        x, sr = sf.read(str(paths.OUT / 'music' / f'{name}.wav'), always_2d=True)
        x = glue(x, sr)
        meter = pyln.Meter(sr)
        # ukur pada dua putaran agar ekor loop ikut terhitung
        loud = meter.integrated_loudness(np.concatenate([x, x]))
        y = x * 10 ** ((target - loud) / 20)
        tp = true_peak(y, sr)
        if tp > 10 ** (-1.2 / 20):
            y = limit(y, 10 ** (-1.5 / 20))
        tmp = paths.OUT / 'master' / f'{name}.wav'
        tmp.parent.mkdir(parents=True, exist_ok=True)
        sf.write(str(tmp), y.astype(np.float32), sr, subtype='FLOAT')
        encode(tmp, dest / f'{name}.mp3', 128)
        print(f'music/{name}: {loud:.1f} → {meter.integrated_loudness(np.concatenate([y, y])):.1f} LUFS, '
              f'TP {20 * np.log10(true_peak(y, sr)):.1f} dBTP, {len(y) / sr:.1f} s')


def master_sfx(dest: Path) -> None:
    for name, target in SFX_LUFS.items():
        x, sr = sf.read(str(paths.OUT / 'sfx' / f'{name}.wav'), always_2d=True)
        meter = pyln.Meter(sr)
        padded = np.concatenate([x, np.zeros((max(0, 3 * sr - len(x)), 2))])
        loud = meter.integrated_loudness(padded)
        y = x * 10 ** ((target - loud) / 20)
        tp = true_peak(y, sr)
        if tp > 10 ** (-1.0 / 20):
            y = y * (10 ** (-1.0 / 20) / tp)
        tmp = paths.OUT / 'master' / f'sfx-{name}.wav'
        tmp.parent.mkdir(parents=True, exist_ok=True)
        sf.write(str(tmp), y.astype(np.float32), sr, subtype='FLOAT')
        encode(tmp, dest / f'{name}.mp3', 128)
        print(f'sfx/{name}: {len(y) / sr:.2f} s, TP {20 * np.log10(true_peak(y, sr)):.1f} dBTP')


if __name__ == '__main__':
    kind = sys.argv[1]
    dest = Path(sys.argv[2]) if len(sys.argv) > 2 else paths.PUBLIC_AUDIO / kind
    {'music': master_music, 'sfx': master_sfx}[kind](dest)
