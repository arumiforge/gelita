"""Utilitas bersama: F0, ASR batch (Whisper small untuk QA), CER/WER."""
from __future__ import annotations

import json
import re
import subprocess
import unicodedata

import numpy as np
import parselmouth
from scipy.signal import resample_poly

import paths


def to16k(audio: np.ndarray, sr: int) -> np.ndarray:
    from math import gcd
    g = gcd(sr, 16000)
    return resample_poly(audio, 16000 // g, sr // g).astype(np.float32)


def f0_stats(audio: np.ndarray, sr: int, floor: float = 60, ceil: float = 500) -> dict:
    snd = parselmouth.Sound(audio.astype(np.float64), sampling_frequency=sr)
    pitch = snd.to_pitch(time_step=0.01, pitch_floor=floor, pitch_ceiling=ceil)
    f = pitch.selected_array['frequency']
    f = f[f > 0]
    if len(f) == 0:
        return {'median': 0.0, 'p10': 0.0, 'p90': 0.0, 'voiced': 0.0}
    return {'median': float(np.median(f)), 'p10': float(np.percentile(f, 10)),
            'p90': float(np.percentile(f, 90)), 'voiced': len(f) / max(1, pitch.n_frames)}


def run_asr(items: list[tuple[str, np.ndarray, int, str]], tag: str) -> dict[str, str]:
    """items: (id, audio, sr, 'indonesian'|'english') → {id: teks}."""
    tmp = paths.OUT / 'asr_tmp' / tag
    tmp.mkdir(parents=True, exist_ok=True)
    jobs = []
    for ident, audio, sr, lang in items:
        f32 = tmp / (re.sub(r'[^A-Za-z0-9_.-]', '_', ident) + '.f32')
        to16k(audio, sr).tofile(f32)
        jobs.append({'id': ident, 'f32': str(f32), 'lang': lang})
    jobs_file = tmp / 'jobs.json'
    out_file = tmp / 'out.json'
    jobs_file.write_text(json.dumps(jobs))
    subprocess.run(['node', str(paths.ASR_SCRIPT), str(jobs_file), str(out_file)], check=True,
                   cwd=str(paths.TOOLS), stderr=subprocess.DEVNULL)
    return json.loads(out_file.read_text())


EN_ONES = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve',
           'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen']
EN_TENS = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety']


def en_number_words(n: int) -> str:
    if n < 20:
        return EN_ONES[n]
    if n < 100:
        t, u = divmod(n, 10)
        return EN_TENS[t] + (' ' + EN_ONES[u] if u else '')
    if n < 1000:
        h, r = divmod(n, 100)
        return EN_ONES[h] + ' hundred' + (' ' + en_number_words(r) if r else '')
    k, r = divmod(n, 1000)
    return en_number_words(k) + ' thousand' + (' ' + en_number_words(r) if r else '')


def spell_numbers(t: str, lang: str) -> str:
    """Angka → kata (2.672 / 2,672 → dua ribu …), agar "15" dan "lima belas" dinilai sama."""
    from id_g2p import number_words
    t = re.sub(r'(\d)[.,](\d{3})\b', r'\1\2', t)
    conv = number_words if lang == 'id' else en_number_words
    return re.sub(r'\d+', lambda m: ' ' + conv(int(m.group())) + ' ', t)


def norm_text(t: str, lang: str | None = None) -> str:
    if lang:
        t = spell_numbers(t, lang)
    t = unicodedata.normalize('NFKD', t.lower())
    t = ''.join(ch for ch in t if not unicodedata.combining(ch))
    t = re.sub(r'[^a-z0-9 ]+', ' ', t)
    return re.sub(r'\s+', ' ', t).strip()


def levenshtein(a, b) -> int:
    prev = list(range(len(b) + 1))
    for i, ca in enumerate(a, 1):
        cur = [i]
        for j, cb in enumerate(b, 1):
            cur.append(min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (ca != cb)))
        prev = cur
    return prev[-1]


def cer(ref: str, hyp: str, lang: str | None = None) -> float:
    r, h = norm_text(ref, lang), norm_text(hyp, lang)
    return levenshtein(r, h) / max(1, len(r))


def wer(ref: str, hyp: str, lang: str | None = None) -> float:
    r, h = norm_text(ref, lang).split(), norm_text(hyp, lang).split()
    return levenshtein(r, h) / max(1, len(r))
