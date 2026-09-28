"""Bangkitkan rekaman narasi GELITA per baris naskah.

python narrate.py --locale id|en [--codes intro-01,peta-02] [--n 2]

Tiap baris: N kandidat (VITS bersifat stokastis) → efek tokoh & pose → ASR Whisper
→ kandidat dengan CER terendah disimpan sebagai WAV 44,1 kHz (loudness per tokoh & pose,
hening 0,3 s) di out/narasi/{locale}/. Laporan per baris: out/narasi/{locale}/report.json.
Setelah itu `python publish.py` mengode MP3 ke public/assets/audio/narasi/.
"""
from __future__ import annotations

import argparse
import json
import re
import subprocess
from pathlib import Path

import numpy as np
import soundfile as sf

import paths
import voicefx as fx
from common import cer, f0_stats, run_asr, wer

# ------------------------------------------------------------------ pemeran

CAST = {
    'id': {
        'narator': dict(speaker=36, ls=1.07, ns=0.333, nw=0.333, floor=100, ceil=500,
                        gender=None, pause=1.25),
        'jaka': dict(speaker=6, ls=0.97, ns=0.40, nw=0.40, floor=120, ceil=650,
                     gender=dict(formant=1.07, median=276.0), pause=0.85),
        'mbah_kedu': dict(speaker=38, ls=1.13, ns=0.333, nw=0.333, floor=60, ceil=320,
                          gender=dict(formant=0.95, median=104.0), pause=1.25),
    },
    'en': {
        'narator': dict(voice='kristin', ls=1.06, ns=0.60, nw=0.75, floor=80, ceil=450,
                        gender=None, pause=1.2),
        'jaka': dict(voice='jenny', ls=0.97, ns=0.667, nw=0.8, floor=110, ceil=650,
                     gender=dict(formant=1.12, median=262.0), pause=0.85),
        'mbah_kedu': dict(voice='joe', ls=1.10, ns=0.60, nw=0.75, floor=55, ceil=300,
                          gender=dict(formant=0.98, median=92.0), pause=1.2),
    },
}

# pose → (pengali length_scale, faktor rentang nada, pengali median, getar F0, getar amp, loudness)
POSE = {
    'jaka': {
        None: (1.0, 1.10, 1.00, 0.0, 0.0, -16.0),
        'idle': (1.0, 1.10, 1.00, 0.0, 0.0, -16.0),
        'happy': (0.95, 1.35, 1.04, 0.0, 0.0, -16.0),
        'afraid': (0.95, 1.20, 1.05, 0.012, 0.04, -16.5),
        'determined': (0.97, 1.18, 1.01, 0.0, 0.0, -15.5),
        'sad': (1.08, 0.78, 0.94, 0.0, 0.0, -17.0),
        'bow': (1.04, 0.92, 0.98, 0.0, 0.0, -16.0),
    },
    # Mbah Kedu sedikit di bawah −16: puncak glotal suara beratnya tinggi, jadi −16 hanya tercapai
    # dengan limiter yang terdengar menekan
    'mbah_kedu': {
        None: (1.0, 0.95, 1.00, 0.010, 0.03, -16.5),
        'idle': (1.0, 0.95, 1.00, 0.010, 0.03, -16.5),
        'smile': (0.98, 1.08, 1.03, 0.010, 0.03, -16.5),
        'worried': (1.04, 0.90, 0.98, 0.012, 0.04, -16.5),
        'weak': (1.18, 0.75, 1.00, 0.025, 0.08, -18.0),
    },
    'narator': {None: (1.0, 1.0, 1.0, 0.0, 0.0, -16.0)},
}

# faktor durasi akhir (Praat 'Lengthen (overlap-add)', pitch tetap): < 1 = lebih cepat.
# Pose 'weak' Mbah Kedu tidak dipercepat.
TEMPO = {
    'id': {'narator': 0.96, 'jaka': 0.92, 'mbah_kedu': 0.97},
    'en': {'narator': 1.0, 'jaka': 1.0, 'mbah_kedu': 1.0},
}

# jeda antarkalimat (detik) menurut penutup kalimat
PAUSES = {'.': 0.42, '!': 0.38, '?': 0.42, '…': 0.62, '': 0.3}

# ------------------------------------------------------------------ mesin TTS

_jv = None


def jv_voice():
    global _jv
    if _jv is None:
        from jvtts import JvVoice
        _jv = JvVoice()
    return _jv


def synth_id(text: str, cfg: dict, ls_mul: float) -> tuple[np.ndarray, int]:
    from id_g2p import sentence_to_words, split_sentences
    from jvtts import SAMPLE_RATE
    voice = jv_voice()
    parts = []
    sents = split_sentences(text)
    for k, (sent, end) in enumerate(sents):
        words = sentence_to_words(sent, end)
        audio = voice.synth_words(words, cfg['speaker'], length_scale=cfg['ls'] * ls_mul,
                                  noise_scale=cfg['ns'], noise_w=cfg['nw'])
        audio = fx.trim_silence(audio, SAMPLE_RATE, -40)
        if end == '?':
            audio = fx.question_rise(audio, SAMPLE_RATE, cfg['floor'], cfg['ceil'], amount=0.22)
        elif end == '!':
            audio = fx.exclaim(audio, SAMPLE_RATE, cfg['floor'], cfg['ceil'])
        parts.append(audio)
        if k < len(sents) - 1:
            parts.append(np.zeros(int(PAUSES[end] * cfg['pause'] * SAMPLE_RATE), dtype=np.float32))
    return np.concatenate(parts), SAMPLE_RATE


def split_en(text: str) -> list[tuple[str, str]]:
    text = text.replace('…', '...')
    out = []
    for m in re.finditer(r'.+?(?:\.\.\.|[.!?])+(?=\s|$)|.+$', text.strip()):
        s = m.group().strip()
        if not s:
            continue
        end = '…' if s.endswith('...') else (s[-1] if s[-1] in '.!?' else '')
        out.append((s, end))
    return out


_espeak = None


def espeak_phonemizer(voice):
    global _espeak
    if _espeak is None:
        from piper.phonemize_espeak import EspeakPhonemizer
        _espeak = EspeakPhonemizer(voice.espeak_data_dir)
    return _espeak


def synth_en(text: str, cfg: dict, ls_mul: float) -> tuple[np.ndarray, int]:
    from en_lexicon import phonemize
    from entts import load
    from piper import SynthesisConfig
    voice = load(cfg['voice'])
    sr = voice.config.sample_rate
    dialect = 'us' if voice.config.espeak_voice == 'en-us' else 'gb'
    syn = SynthesisConfig(length_scale=cfg['ls'] * ls_mul, noise_scale=cfg['ns'], noise_w_scale=cfg['nw'],
                          normalize_audio=False)
    parts = []
    sents = split_en(text)
    for k, (sent, end) in enumerate(sents):
        phon = phonemize(sent, espeak_phonemizer(voice), voice.config.espeak_voice, dialect)
        audio = np.asarray(voice.phoneme_ids_to_audio(voice.phonemes_to_ids(phon), syn), dtype=np.float32)
        audio = fx.trim_silence(audio, sr, -40)
        parts.append(audio)
        if k < len(sents) - 1:
            parts.append(np.zeros(int(PAUSES[end] * cfg['pause'] * sr), dtype=np.float32))
    return np.concatenate(parts), sr


# ------------------------------------------------------------------ satu baris

def render_line(row: dict, locale: str, seed: int) -> tuple[np.ndarray, dict]:
    who = row['character']
    cfg = CAST[locale][who]
    pose_tab = POSE[who]
    pose = row['pose'] if row['pose'] in pose_tab else None
    ls_mul, rng_f, med_mul, f0_trem, amp_trem, lufs = pose_tab[pose]
    text = row['text_' + locale]
    if locale == 'id':
        audio, sr = synth_id(text, cfg, ls_mul)
    else:
        audio, sr = synth_en(text, cfg, ls_mul)

    g = cfg['gender']
    if g is not None:
        audio = fx.change_gender(audio, sr, cfg['floor'], cfg['ceil'], g['formant'], g['median'] * med_mul, rng_f)
    elif rng_f != 1.0 or med_mul != 1.0:
        med = f0_stats(audio, sr, cfg['floor'], cfg['ceil'])['median']
        audio = fx.change_gender(audio, sr, cfg['floor'], cfg['ceil'], 1.0, med * med_mul, rng_f)
    if f0_trem > 0:
        audio = fx.tremor(audio, sr, cfg['floor'] * 0.8, cfg['ceil'], rate=5.4 if who == 'mbah_kedu' else 7.0,
                          f0_depth=f0_trem, amp_depth=amp_trem, seed=seed)
    if who == 'mbah_kedu':
        audio = fx.shelf(audio, sr, lowpass_hz=7000 if pose != 'weak' else 6000)
        if pose == 'weak':
            audio = fx.breath_noise(audio, sr, level_db=-32, seed=seed)
    else:
        audio = fx.shelf(audio, sr)
    tempo = TEMPO[locale][who] if pose != 'weak' else 1.0
    if tempo != 1.0:
        audio = fx.lengthen(audio, sr, cfg['floor'], cfg['ceil'], tempo)
    out = fx.finalize(audio, sr, lufs=lufs)
    return out, {'pose': pose, 'lufs': lufs}


def load_lines() -> list[dict]:
    """Baris naskah + petunjuk arena cari, langsung dari data repositori (export_lines.php)."""
    out = subprocess.run(['php', str(paths.TOOLS / 'export_lines.php')], check=True, capture_output=True, text=True)
    return json.loads(out.stdout)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--locale', required=True, choices=['id', 'en'])
    ap.add_argument('--codes', default='')
    ap.add_argument('--n', type=int, default=3)
    ap.add_argument('--out', default=str(paths.OUT / 'narasi'))
    ap.add_argument('--tag', default='')
    args = ap.parse_args()

    rows = load_lines()
    if args.codes:
        want = set(args.codes.split(','))
        rows = [r for r in rows if r['code'] in want]
    out_dir = Path(args.out) / args.locale
    cand_dir = out_dir / '_cand'
    cand_dir.mkdir(parents=True, exist_ok=True)

    items, cands = [], {}
    for row in rows:
        for k in range(args.n):
            audio, info = render_line(row, args.locale, seed=k)
            ident = f"{row['code']}#{k}"
            cands[ident] = (row, audio, info)
            items.append((ident, audio, fx.OUT_SR, 'indonesian' if args.locale == 'id' else 'english'))
            sf.write(str(cand_dir / f"{row['code']}.{k}.wav"), audio, fx.OUT_SR, subtype='PCM_16')
        print('rendered', row['code'], flush=True)

    hyps = run_asr(items, f'narr-{args.locale}{args.tag}')
    report_path = out_dir / 'report.json'
    report = json.loads(report_path.read_text()) if report_path.exists() else {}
    for row in rows:
        scored = []
        for k in range(args.n):
            ident = f"{row['code']}#{k}"
            ref = row['text_' + args.locale]
            hyp = hyps.get(ident, '')
            scored.append((cer(ref, hyp, args.locale), wer(ref, hyp, args.locale), k, hyp))
        scored.sort()
        best = scored[0]
        _, audio, info = cands[f"{row['code']}#{best[2]}"]
        sf.write(str(out_dir / f"{row['code']}.wav"), audio, fx.OUT_SR, subtype='PCM_16')
        report[row['code']] = {
            'character': row['character'], 'pose': info['pose'], 'cer': round(best[0], 4), 'wer': round(best[1], 4),
            'all_cer': [round(s[0], 4) for s in sorted(scored, key=lambda s: s[2])],
            'asr': best[3], 'text': row['text_' + args.locale], 'seconds': round(len(audio) / fx.OUT_SR, 2),
        }
        print(f"{row['code']:24s} CER={best[0]:.3f} WER={best[1]:.3f} | {best[3]}")
    report_path.write_text(json.dumps(report, indent=1, ensure_ascii=False))


if __name__ == '__main__':
    main()
