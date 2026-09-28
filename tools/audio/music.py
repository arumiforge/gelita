"""Tiga gending loop GELITA: map (Peta Kedu), region (peta wilayah), challenge (tantangan).

python music.py [map|region|challenge ...] → out/music/{nama}.wav (stereo, loop mulus)

Gending orisinal (bukan salinan gending tradisional), laras dan bentuknya
mengikuti karawitan Jawa gaya Surakarta/Yogyakarta: ladrang slendro manyura
(peta), ketawang pelog nem (peta wilayah), ketawang slendro sanga (tantangan).
"""
import sys
from pathlib import Path

import numpy as np
import soundfile as sf

import compose
import gamelan as G
from compose import Piece
from gamelan import SR, Laras
import paths

OUT = paths.OUT / 'music'


def kendang_patterns(style: str) -> dict:
    if style == 'ladrang':
        return {
            'basic': '. t . d . t k D',
            'after_gong': '. . . d . t . D',
            'pre_gong': 't t . d t k d P',
        }
    if style == 'ketawang':
        return {
            'basic': '. . h . . d . D',
            'after_gong': '. . . . . d . D',
            'pre_gong': '. h . d . k d P',
        }
    raise ValueError(style)


def piece_map() -> Piece:
    """Ladrang slendro manyura, irama tanggung — megah, menjelajah."""
    laras = Laras('slendro', 262.0)
    bal = (
        # gongan A
        '6 5 3 2 | 5 3 2 1 | 3 2 1 6, | 2 3 1 2 | 3 5 6 5 | 1 2 3 5 | 6 5 3 2 | 1 2 1 6, |'
        # gongan B
        "5, 6, 1 2 | 3 5 3 2 | 5 6 1' 6 | 5 3 2 3 | 5 3 2 1 | 2 3 5 6 | 3 2 1 2 | 3 1 2 6,"
    )
    p = Piece(laras, bpm=66, balungan=bal, form='ladrang', name='map')
    p.structure(kenong_gain=0.50, kempul_gain=0.60, gong_gain=1.0, kethuk_gain=0.22)
    p.balungan('saron', 'demung', octave=-1, gain=0.42, pan=-0.3)
    p.balungan('saron', 'barung', octave=0, gain=0.26, pan=0.35)
    p.balungan('saron', 'barung', octave=0, gain=0.24, pan=-0.15, player='2')
    p.balungan('slenthem', '', octave=-2, gain=0.40, pan=0.0)
    p.peking(gain=0.13, pan=0.55, per_beat=2)
    p.bonang('barung', gain=0.20, pan=-0.45, density=2)
    p.bonang('panerus', gain=0.10, pan=0.45, density=4)
    p.kendang(kendang_patterns('ladrang'), gain=0.42, pan=0.05)
    # suling masuk di gongan B, berakhir di seleh kenong
    p.suling([
        (33.0, [("3'", 1.0), ("5'", 0.5), ("6'", 2.5), ("5'", 0.5), ("3'", 0.5), ("2'", 3.0)]),
        (41.0, [("6'", 0.5), ("1''", 0.5), ("6'", 2.0), ("5'", 1.0), ("3'", 0.5), ("5'", 0.5), ("3'", 2.5)]),
        (49.5, [("2'", 1.0), ("3'", 0.5), ("5'", 2.0), ("6'", 0.5), ("5'", 0.5), ("3'", 1.0), ("2'", 0.5), ("1'", 2.5)]),
        (57.0, [("3'", 0.5), ("2'", 0.5), ("1'", 1.5), ("2'", 1.0), ("6", 0.5), ("1'", 0.5), ("6", 3.5)]),
    ], gain=0.13, pan=0.12)
    return p


def piece_region() -> Piece:
    """Ketawang pelog nem, irama dadi — tenang, pedesaan di lereng gunung."""
    laras = Laras('pelog', 280.0)
    bal = (
        '2 1 2 3 | 5 3 2 1 | 3 2 1 6, | 2 1 6, 5, |'
        '6, 1 2 3 | 5 6 5 3 | 2 1 2 3 | 5 3 2 1 |'
        "3 5 6 1' | 6 5 3 2 | 5 3 2 1 | 2 1 6, 5,"
    )
    p = Piece(laras, bpm=42, balungan=bal, form='ketawang', name='region')
    p.structure(kenong_gain=0.45, kempul_gain=0.55, gong_gain=0.95, kethuk_gain=0.18, gong_hz=43.0,
                kethuk_note=(6, -2))
    p.balungan('saron', 'barung', octave=0, gain=0.20, pan=0.35)
    p.balungan('slenthem', '', octave=-2, gain=0.40, pan=0.0)
    p.gender(gain=0.30, pan=0.25, density=4, octave=0, seed=11)
    p.gambang(gain=0.13, pan=-0.4, density=4, seed=12)
    p.siter(gain=0.14, pan=0.5, density=2)
    p.kendang(kendang_patterns('ketawang'), gain=0.30, pan=0.0)
    p.suling([
        (1.0, [("3'", 1.0), ("5'", 0.5), ("3'", 0.5), ("2'", 2.0), ("1'", 0.5), ("2'", 0.5), ("3'", 2.5)]),
        (8.5, [("5'", 0.5), ("6'", 0.5), ("5'", 1.5), ("3'", 1.0), ("2'", 0.5), ("1'", 0.5), ("6", 2.0), ("5", 1.5)]),
        (25.0, [("6'", 1.0), ("1''", 0.5), ("6'", 0.5), ("5'", 2.0), ("3'", 1.0), ("5'", 0.5), ("3'", 2.5)]),
        (35.0, [("1''", 1.0), ("6'", 1.0), ("5'", 2.0), ("6'", 0.5), ("5'", 0.5), ("3'", 1.0), ("2'", 2.5)]),
        (42.5, [("3'", 0.5), ("2'", 0.5), ("1'", 1.5), ("2'", 0.5), ("1'", 0.5), ("6", 1.5), ("5", 2.5)]),
    ], gain=0.12, pan=0.1)
    return p


def piece_challenge() -> Piece:
    """Ketawang slendro sanga, balungan nibani, tanpa kendang — hening untuk membaca."""
    laras = Laras('slendro', 247.0)
    bal = (
        '. 2 . 1 | . 2 . 6, | . 5, . 6, | . 1 . 5, |'
        '. 6, . 5, | . 2 . 1 | . 3 . 2 | . 1 . 6, |'
        '. 1 . 2 | . 3 . 5 | . 6 . 5 | . 3 . 2 |'
        '. 3 . 1 | . 2 . 6, | . 1 . 2 | . 6, . 5,'
    )
    p = Piece(laras, bpm=52, balungan=bal, form='ketawang', name='challenge')
    p.structure(kenong_gain=0.34, kempul_gain=0.36, gong_gain=0.55, kethuk_gain=0.12, gong_hz=40.0,
                kethuk_note=(2, -1))
    p.balungan('slenthem', '', octave=-2, gain=0.42, pan=0.0)
    p.balungan('saron', 'demung', octave=-1, gain=0.16, pan=-0.3)
    p.gender(gain=0.26, pan=0.2, density=2, octave=0, seed=21)
    return p


PIECES = {'map': piece_map, 'region': piece_region, 'challenge': piece_challenge}
SENDS = {'gong': 0.10, 'kempul': 0.18, 'kenong': 0.25, 'kethuk': 0.2, 'sarondemung': 0.22, 'saronbarung': 0.22,
         'slenthem': 0.2, 'peking': 0.28, 'bonangbarung': 0.26, 'bonangpanerus': 0.3, 'kendang': 0.15,
         'gender': 0.3, 'gambang': 0.25, 'siter': 0.3, 'suling': 0.35}


SEEDS = {'map': 101, 'region': 202, 'challenge': 303}


def render(name: str) -> Path:
    # seed per gending: hasil tidak bergantung pada urutan/gending lain yang dirender
    G.RNG = np.random.default_rng(SEEDS[name])
    compose.RNG = np.random.default_rng(SEEDS[name] + 1)
    p = PIECES[name]()
    ir = G.make_ir(rt60={'map': 1.5, 'region': 1.8, 'challenge': 2.2}[name])
    mix = p.mix.render(SENDS, ir, loop=True)
    mix = G.highpass(mix.T, 28, order=2).T
    mix /= np.max(np.abs(mix)) + 1e-9
    OUT.mkdir(parents=True, exist_ok=True)
    path = OUT / f'{name}.wav'
    sf.write(str(path), mix.astype(np.float32) * 0.89, SR, subtype='FLOAT')
    print(name, f'{p.seconds:.1f} s', path)
    return path


if __name__ == '__main__':
    for name in sys.argv[1:] or PIECES:
        render(name)
