"""Pengolahan suara tokoh dengan Praat (parselmouth): gender/usia, getar, intonasi tanya, loudness."""
from __future__ import annotations

import numpy as np
import parselmouth
import pyloudnorm as pyln
from parselmouth.praat import call
from scipy.signal import butter, resample_poly, sosfilt

OUT_SR = 44100


def to_sound(x: np.ndarray, sr: int) -> parselmouth.Sound:
    return parselmouth.Sound(np.asarray(x, dtype=np.float64), sampling_frequency=sr)


def change_gender(x: np.ndarray, sr: int, floor: float, ceil: float, formant: float, median: float,
                  range_factor: float, duration: float = 1.0) -> np.ndarray:
    """Praat 'Change gender' (PSOLA): formant geser, median pitch baru, rentang pitch, durasi."""
    snd = to_sound(x, sr)
    out = call(snd, 'Change gender', floor, ceil, formant, median, range_factor, duration)
    y = out.values[0]
    if int(out.sampling_frequency) != sr:
        y = resample_poly(y, sr, int(out.sampling_frequency))
    return y.astype(np.float32)


def lengthen(x: np.ndarray, sr: int, floor: float, ceil: float, factor: float) -> np.ndarray:
    """Ubah tempo tanpa mengubah nada (PSOLA): factor < 1 mempercepat."""
    snd = to_sound(x, sr)
    out = call(snd, 'Lengthen (overlap-add)', floor, ceil, factor)
    return out.values[0].astype(np.float32)


def pitch_edit(x: np.ndarray, sr: int, floor: float, ceil: float, formula: str) -> np.ndarray:
    """Terapkan rumus Praat ke PitchTier (x = waktu dalam detik, self = Hz), resintesis overlap-add."""
    snd = to_sound(x, sr)
    manip = call(snd, 'To Manipulation', 0.01, floor, ceil)
    tier = call(manip, 'Extract pitch tier')
    call(tier, 'Formula', formula)
    call([tier, manip], 'Replace pitch tier')
    out = call(manip, 'Get resynthesis (overlap-add)')
    return out.values[0].astype(np.float32)


def tremor(x: np.ndarray, sr: int, floor: float, ceil: float, rate: float, f0_depth: float,
           amp_depth: float, seed: int = 0) -> np.ndarray:
    """Getar suara lanjut usia: modulasi F0 & amplitudo pelan dengan laju sedikit berubah-ubah."""
    rng = np.random.default_rng(seed)
    ph = rng.uniform(0, 6.28)
    wobble = rng.uniform(0.15, 0.35)
    y = pitch_edit(x, sr, floor, ceil,
                   f'self * (1 + {f0_depth} * sin(2*pi*{rate}*x + {ph} + {wobble}*sin(2*pi*0.7*x)))')
    t = np.arange(len(y)) / sr
    y = y * (1 + amp_depth * np.sin(2 * np.pi * rate * t + ph + 0.8))
    return y.astype(np.float32)


def question_rise(x: np.ndarray, sr: int, floor: float, ceil: float, amount: float = 0.22,
                  tail_s: float = 0.45) -> np.ndarray:
    """Naikkan nada di ujung kalimat tanya (model Jawa tidak punya token '?')."""
    dur = len(x) / sr
    # cari ujung bagian bersuara
    snd = to_sound(x, sr)
    pitch = snd.to_pitch(time_step=0.01, pitch_floor=floor, pitch_ceiling=ceil)
    f = pitch.selected_array['frequency']
    voiced = np.nonzero(f > 0)[0]
    end = (voiced[-1] + 1) * 0.01 if len(voiced) else dur
    start = max(0.0, end - tail_s)
    return pitch_edit(x, sr, floor, ceil,
                      f'if x < {start} then self else self * (1 + {amount} * ((min(x, {end}) - {start}) / {tail_s})^1.5) fi')


def exclaim(x: np.ndarray, sr: int, floor: float, ceil: float, range_factor: float = 1.18,
            lift: float = 1.04) -> np.ndarray:
    """Kalimat seru: rentang nada diperlebar sedikit di sekitar mediannya."""
    snd = to_sound(x, sr)
    pitch = snd.to_pitch(time_step=0.01, pitch_floor=floor, pitch_ceiling=ceil)
    f = pitch.selected_array['frequency']
    f = f[f > 0]
    if len(f) < 5:
        return x
    med = float(np.median(f))
    return pitch_edit(x, sr, floor, ceil, f'({med} + (self - {med}) * {range_factor}) * {lift}')


def breath_noise(x: np.ndarray, sr: int, level_db: float = -30.0, seed: int = 0) -> np.ndarray:
    """Desah napas lemah: noise pita tinggi mengikuti selubung ucapan."""
    rng = np.random.default_rng(seed)
    sos = butter(2, [1500, min(7000, sr / 2 * 0.9)], btype='band', fs=sr, output='sos')
    noise = sosfilt(sos, rng.standard_normal(len(x)))
    env = np.abs(x)
    k = int(0.03 * sr)
    env = np.convolve(env, np.ones(k) / k, mode='same')
    env /= env.max() + 1e-9
    noise = noise / (np.sqrt(np.mean(noise ** 2)) + 1e-9)
    rms = np.sqrt(np.mean(x ** 2)) + 1e-9
    return (x + noise * env * rms * 10 ** (level_db / 20) * 3).astype(np.float32)


def shelf(x: np.ndarray, sr: int, lowpass_hz: float | None = None, highpass_hz: float = 70.0) -> np.ndarray:
    y = sosfilt(butter(2, highpass_hz, btype='high', fs=sr, output='sos'), x)
    if lowpass_hz:
        y = sosfilt(butter(2, lowpass_hz, btype='low', fs=sr, output='sos'), y)
    return y.astype(np.float32)


def trim_silence(x: np.ndarray, sr: int, thresh_db: float = -45.0) -> np.ndarray:
    k = int(0.01 * sr)
    frames = len(x) // k
    if frames == 0:
        return x
    rms = np.sqrt(np.mean(x[: frames * k].reshape(frames, k) ** 2, axis=1))
    lim = rms.max() * 10 ** (thresh_db / 20)
    idx = np.nonzero(rms > lim)[0]
    if len(idx) == 0:
        return x
    a = max(0, idx[0] * k - int(0.02 * sr))
    b = min(len(x), (idx[-1] + 1) * k + int(0.06 * sr))
    return x[a:b]


def finalize(x: np.ndarray, sr: int, lufs: float = -16.0, pad_s: float = 0.3, peak_db: float = -1.0) -> np.ndarray:
    """Resample ke 44,1 kHz, hening ±0,3 s di awal & akhir, loudness `lufs`, puncak sejati ≤ `peak_db` dBTP.

    Limiter hanya menjinakkan puncak sesaat, jadi loudness dicapai bertahap: ukur, beri gain,
    batasi puncak, ukur lagi. Suara berat dengan puncak glotal tinggi, seperti Mbah Kedu,
    kehilangan sebagian loudness di limiter pada putaran pertama.
    """
    if sr != OUT_SR:
        from math import gcd
        g = gcd(sr, OUT_SR)
        x = resample_poly(x, OUT_SR // g, sr // g)
    x = trim_silence(np.asarray(x, dtype=np.float64), OUT_SR)
    fade = int(0.01 * OUT_SR)
    x[:fade] *= np.linspace(0, 1, fade)
    x[-fade:] *= np.linspace(1, 0, fade)
    meter = pyln.Meter(OUT_SR)
    pad = np.zeros(int(pad_s * OUT_SR))
    lim = 10 ** (peak_db / 20)

    def loudness(y: np.ndarray) -> float:
        # diukur seperti berkas jadinya (dengan hening), sama dengan alat ukur loudness pada MP3-nya
        return meter.integrated_loudness(np.concatenate([pad, y, pad]))

    gain_db, prev = lufs - loudness(x), None
    for _ in range(8):
        y = x * 10 ** (gain_db / 20)
        if np.max(peak_envelope(y)) > lim:
            y = soft_limit(y, lim)
        loud = loudness(y)
        if abs(lufs - loud) < 0.05:
            break
        # langkah sekan: tambahan gain yang tersisa setelah limiter (dL/dG) makin kecil bila puncaknya tinggi
        slope = 1.0
        if prev is not None and abs(gain_db - prev[0]) > 1e-6:
            slope = min(1.0, max(0.2, (loud - prev[1]) / (gain_db - prev[0])))
        prev = (gain_db, loud)
        gain_db += (lufs - loud) / slope
    return np.concatenate([pad, y, pad]).astype(np.float32)


def peak_envelope(x: np.ndarray, oversample: int = 4) -> np.ndarray:
    """|x| termasuk puncak antarsampel (true peak): maksimum sinyal yang di-oversample 4× di sekitar
    tiap sampel. Desis kuat (mis. suara kristin) bisa berpuncak ±1,5 dB di atas sampelnya."""
    up = np.abs(resample_poly(x, oversample, 1)[: len(x) * oversample]).reshape(len(x), oversample)
    return np.maximum(np.abs(x), up.max(axis=1))


def soft_limit(x: np.ndarray, ceiling: float, look_ms: float = 2.0, release_ms: float = 20.0) -> np.ndarray:
    """Limiter true peak dengan look-ahead: gain turun landai selama `look_ms` sebelum puncak lalu pulih
    dengan konstanta waktu `release_ms`. Cukup cepat agar suku kata di sekitar puncak tidak ikut
    melemah, cukup lambat (±2 periode suara pria ±100 Hz) agar tidak membentuk ulang tiap denyut glotal."""
    from scipy.ndimage import minimum_filter1d
    look = max(1, int(look_ms / 1000 * OUT_SR))
    need = np.minimum(1.0, ceiling / np.maximum(peak_envelope(x), 1e-9))
    # min-hold ±look, lalu rata-rata look sampel ke belakang: gain menurun landai sebelum puncak
    # dan tetap ≤ kebutuhan tepat di puncaknya (semua nilai yang dirata-rata ≤ kebutuhan itu)
    need = minimum_filter1d(need, size=2 * look + 1)
    need = np.convolve(np.concatenate([np.ones(look - 1), need]), np.ones(look) / look, mode='valid')
    g = np.empty_like(need)
    rel = np.exp(-1.0 / (release_ms / 1000 * OUT_SR))
    cur = 1.0
    for i, v in enumerate(need):
        cur = v if v < cur else v + (cur - v) * rel
        g[i] = cur
    return x * g
