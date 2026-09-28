"""Efek suara bernuansa gamelan → out/sfx/{nama}.wav (stereo)."""
import sys
from pathlib import Path

import numpy as np
import soundfile as sf

import gamelan as G
from gamelan import SR, Laras, Mixer
import paths

OUT = paths.OUT / 'sfx'
PELOG = Laras('pelog', 280.0)
SLENDRO = Laras('slendro', 262.0)


def finish(mix: Mixer, sends: dict, rt60: float, fade_ms: float = 30.0) -> np.ndarray:
    ir = G.make_ir(rt60=rt60, seed=3)
    out = mix.render(sends, ir, loop=False)
    out = G.highpass(out.T, 60).T
    k = int(fade_ms / 1000 * SR)
    out[-k:] *= np.linspace(1, 0, k)[:, None]
    return out


def trim_tail(x: np.ndarray, thresh_db: float = -60.0, pad_s: float = 0.02) -> np.ndarray:
    env = np.max(np.abs(x), axis=1)
    lim = np.max(env) * 10 ** (thresh_db / 20)
    idx = np.nonzero(env > lim)[0]
    end = min(len(x), idx[-1] + int(pad_s * SR)) if len(idx) else len(x)
    y = x[:end].copy()
    k = min(len(y), int(0.03 * SR))
    y[-k:] *= np.linspace(1, 0, k)[:, None]
    return y


def click() -> np.ndarray:
    m = Mixer(0.16)
    f = PELOG.hz(6, 1)
    wood = G.modal(f, 0.14, [(1.0, 1.0, 0.045), (3.92, 0.25, 0.02), (9.1, 0.05, 0.01)], att_ms=0.5)
    n = len(wood)
    wood += G.bandnoise(n, 2000, 7000) * G.env_exp(n, 0.004) * 0.25
    m.add('wood', wood, 0.0, 1.0, 0.0)
    return trim_tail(finish(m, {'wood': 0.03}, 0.25, 20), -45)


def correct() -> np.ndarray:
    m = Mixer(0.9)
    seq = [(5, 0), (6, 0), (1, 1)]
    for i, (note, octv) in enumerate(seq):
        at = i * 0.085
        v = 0.75 + 0.15 * i
        m.add('bonang', G.bonang(PELOG.hz(note, octv), v, kind='panerus'), at, 0.9, -0.2 + 0.2 * i)
    m.add('peking', G.saron(PELOG.hz(1, 2), 0.55, kind='peking'), 0.17, 0.5, 0.3)
    m.add('peking', G.saron(PELOG.hz(1, 1), 0.5, kind='barung'), 0.17, 0.45, -0.1)
    return trim_tail(finish(m, {'bonang': 0.25, 'peking': 0.3}, 0.9, 250), -55)


def wrong() -> np.ndarray:
    m = Mixer(0.9)
    m.add('saron', G.saron(SLENDRO.hz(2, -1), 0.9, kind='demung', damp=0.16), 0.0, 0.8, -0.1)
    m.add('saron', G.saron(SLENDRO.hz(6, -2), 0.85, kind='demung', damp=0.42), 0.17, 0.85, 0.1)
    m.add('kempul', G.kempul(SLENDRO.hz(6, -3), 0.35)[: int(0.7 * SR)] * np.linspace(1, 0, int(0.7 * SR)), 0.17, 0.5, 0.0)
    return trim_tail(finish(m, {'saron': 0.15, 'kempul': 0.1}, 0.6, 200), -50)


def lock() -> np.ndarray:
    m = Mixer(0.6)
    for i, at in enumerate((0.0, 0.11)):
        m.add('kethuk', G.kethuk(SLENDRO.hz(2, -1) * (1.0 if i == 0 else 0.94), 1.0 - 0.15 * i), at, 0.9, -0.1)
    # kecrek: lempeng logam beradu, redam
    n = int(0.12 * SR)
    t = np.arange(n) / SR
    metal = np.zeros(n)
    rng = np.random.default_rng(5)
    for f in rng.uniform(2500, 7500, 14):
        metal += np.sin(2 * np.pi * f * t + rng.uniform(0, 6)) * rng.uniform(0.3, 1.0)
    metal = metal / 14 * G.env_exp(n, 0.045) + G.bandnoise(n, 3000, 9000) * G.env_exp(n, 0.02) * 0.3
    m.add('kecrek', metal, 0.11, 0.35, 0.2)
    return trim_tail(finish(m, {'kethuk': 0.12, 'kecrek': 0.1}, 0.5), -50)


def shard() -> np.ndarray:
    m = Mixer(3.2)
    seq = [(1, 1), (2, 1), (3, 1), (5, 1), (6, 1), (1, 2)]
    for i, (note, octv) in enumerate(seq):
        at = i * 0.075
        m.add('peking', G.saron(PELOG.hz(note, octv), 0.55 + 0.07 * i, kind='peking'), at, 0.55, -0.4 + 0.16 * i)
        m.add('gender', G.gender(PELOG.hz(note, octv - 1), 0.5 + 0.05 * i), at + 0.01, 0.35, 0.3 - 0.12 * i)
    # kilau: dentingan tinggi acak
    rng = np.random.default_rng(9)
    for k in range(9):
        at = 0.42 + k * 0.06 + rng.uniform(-0.01, 0.01)
        note = [1, 2, 3, 5, 6][rng.integers(0, 5)]
        m.add('kilau', G.saron(PELOG.hz(note, 2), 0.25 * (1 - k / 11), kind='peking'), at, 0.4, rng.uniform(-0.8, 0.8))
    m.add('kenong', G.kenong(PELOG.hz(1, 0), 0.6), 0.45, 0.5, 0.2)
    return trim_tail(finish(m, {'peking': 0.35, 'gender': 0.3, 'kilau': 0.5, 'kenong': 0.25}, 1.8), -55)


def region_done() -> np.ndarray:
    m = Mixer(5.2)
    beat = 0.24
    # frasa suwuk: bonang & saron menuju nada 1, lalu kenong + kempul + gong bersamaan
    phrase = [(3, 0), (5, 0), (6, 0), (5, 0), (3, 0), (2, 0), (3, 0), (1, 0)]
    for i, (note, octv) in enumerate(phrase):
        at = i * beat
        m.add('saron', G.saron(PELOG.hz(note, octv), 0.8, kind='barung', damp=beat * 0.95 if i < len(phrase) - 1 else None), at, 0.55, 0.25)
        m.add('saron', G.saron(PELOG.hz(note, octv - 1), 0.8, kind='demung', damp=beat * 0.95 if i < len(phrase) - 1 else None), at, 0.5, -0.25)
        for k in range(2):
            at2 = at - beat / 2 + k * beat / 2
            if at2 >= 0:
                m.add('bonang', G.bonang(PELOG.hz(note if k else phrase[i - 1][0], 1), 0.7, kind='panerus'), at2, 0.3, 0.45)
    end = (len(phrase) - 1) * beat
    m.add('gong', G.gong_ageng(41.0, 1.0), end + 0.02, 1.0, -0.1)
    m.add('kenong', G.kenong(PELOG.hz(1, -1), 0.9), end, 0.6, 0.4)
    m.add('kempul', G.kempul(PELOG.hz(1, -2), 0.9), end, 0.6, -0.4)
    for o in (0, 1):
        m.add('bonang', G.bonang(PELOG.hz(1, o), 0.8, kind='barung'), end, 0.35, -0.3)
    rng = np.random.default_rng(4)
    for k in range(10):
        at = end + 0.05 + k * 0.07
        note = [1, 2, 3, 5, 6][rng.integers(0, 5)]
        m.add('kilau', G.saron(PELOG.hz(note, 2), 0.28 * (1 - k / 12), kind='peking'), at, 0.35, rng.uniform(-0.8, 0.8))
    x = finish(m, {'saron': 0.25, 'bonang': 0.3, 'gong': 0.12, 'kenong': 0.25, 'kempul': 0.15, 'kilau': 0.45}, 1.8, 1200)
    return trim_tail(x, -48)


SOUNDS = {'click': click, 'correct': correct, 'wrong': wrong, 'lock': lock, 'shard': shard, 'region-done': region_done}

if __name__ == '__main__':
    OUT.mkdir(parents=True, exist_ok=True)
    for name in sys.argv[1:] or SOUNDS:
        # seed per efek: hasil tidak bergantung pada efek lain yang dirender
        G.RNG = np.random.default_rng(1000 + list(SOUNDS).index(name))
        PELOG.detune.clear()
        SLENDRO.detune.clear()
        x = SOUNDS[name]()
        x = x / (np.max(np.abs(x)) + 1e-9) * 0.9
        sf.write(str(OUT / f'{name}.wav'), x.astype(np.float32), SR, subtype='FLOAT')
        print(name, f'{len(x) / SR:.2f} s')
