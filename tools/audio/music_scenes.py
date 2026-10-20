"""Background music for the narrated scenes and the account pages (owner, 19 October 2026).

python music.py welcome opening kenal-magelang … → out/music/{nama}.wav, then
python master.py music --only=welcome,opening,…

Twelve original gending in the same style as music.py, chosen for the mood of
each screen. The narration voice plays over them, so they stay simpler and
softer than the map music; core/audio.js also drops them to 20 % while a voice
speaks.

| Track | Screen | Mood | Laras · form · tempo |
|---|---|---|---|
| welcome | home, Mulai, login, sign-up, change password, gate | gentle welcome | slendro manyura · ketawang · 48 |
| opening | opening story (Kabut Lupa) | misty, mysterious | pelog barang · ketawang nibani · 36 |
| ending | ending story | warm, bright | slendro manyura · ladrang · 60 |
| kenal-* | Kenali overlay | calm storytelling | region laras · ketawang · 34–40 |
| dialog-* | region dialogue | light, on the way | region laras · ladrang/ketawang · 54–58 |
| tuntas-* | region done | festive | region laras · lancaran · 100–108 |

Regions: Temanggung slendro (hills between Sindoro and Sumbing), Magelang
pelog nem (the temples, stately, deep gong), Wonosobo pelog barang (the misty
Dieng plateau, airy, no kendang in Kenali).
"""
import numpy as np

from compose import Piece, index_note, note_index
from gamelan import Laras

BARANG = [2, 3, 5, 6, 7]


def kendang_lancaran() -> dict:
    return {
        'basic': '. t . d . t d D',
        'after_gong': '. . . d . t d D',
        'pre_gong': 't t d d t k d P',
    }


def token(note: tuple[int, int]) -> str:
    n, o = note
    return str(n) + ("'" * o if o > 0 else ',' * -o)


def suling_lines(p: Piece, starts: list[int], seed: int, octave: int = 1) -> list:
    """Phrases that settle on the balungan note about six beats after each start (its seleh)."""
    rng = np.random.default_rng(seed)
    shapes = [
        [(2, 0.5), (1, 0.5), (0, 2.0), (1, 0.5), (0, 2.5)],
        [(-1, 1.0), (0, 0.5), (1, 0.5), (2, 1.5), (1, 0.5), (0, 2.0)],
        [(3, 0.5), (2, 0.5), (1, 1.0), (2, 0.5), (1, 0.5), (0, 3.0)],
        [(1, 1.5), (2, 0.5), (1, 0.5), (0, 0.5), (-1, 0.5), (0, 2.5)],
    ]
    phrases = []
    for start in starts:
        seleh = p.full[(start + 6) % p.n_beats]
        target = note_index((seleh[0], seleh[1] + octave), p.steps)
        shape = shapes[int(rng.integers(len(shapes)))]
        phrases.append((start, [(token(index_note(target + d, p.steps)), dur) for d, dur in shape]))
    return phrases


# ------------------------------------------------------------------ home and story

def piece_welcome(kendang) -> Piece:
    """Ketawang slendro manyura, irama dadi — gentle welcome."""
    bal = (
        '3 5 3 2 | 6 5 3 2 | 5 6 5 3 | 2 1 2 6, |'
        '2 1 2 6, | 2 1 2 3 | 5 6 5 3 | 6 5 3 2 |'
        "5 6 1' 6 | 5 3 2 1 | 3 2 1 6, | 2 1 2 6, |"
    )
    p = Piece(Laras('slendro', 262.0), bpm=48, balungan=bal, form='ketawang', name='welcome')
    p.structure(kenong_gain=0.36, kempul_gain=0.42, gong_gain=0.7, kethuk_gain=0.14)
    p.balungan('slenthem', '', octave=-2, gain=0.36, pan=0.0)
    p.balungan('saron', 'barung', octave=0, gain=0.15, pan=0.35)
    p.gender(gain=0.28, pan=0.2, density=4, octave=0, seed=31)
    p.siter(gain=0.13, pan=-0.45, density=2)
    p.kendang(kendang('ketawang'), gain=0.24, pan=0.0)
    p.suling(suling_lines(p, [2, 14, 26, 38], seed=32), gain=0.11, pan=0.15)
    return p


def piece_opening(kendang) -> Piece:
    """Ketawang pelog barang, balungan nibani — the Kabut Lupa: misty and slow, no kendang."""
    bal = (
        '. 2 . 3 | . 2 . 7, | . 3 . 2 | . 7, . 6, |'
        '. 7, . 2 | . 3 . 5 | . 6 . 5 | . 3 . 2 |'
        '. 5 . 6 | . 7 . 6 | . 5 . 3 | . 2 . 3 |'
        '. 5 . 3 | . 2 . 7, | . 2 . 3 | . 7, . 6, |'
    )
    p = Piece(Laras('pelog', 262.0), bpm=36, balungan=bal, form='ketawang', name='opening', steps=BARANG)
    p.structure(kenong_gain=0.30, kempul_gain=0.36, gong_gain=0.62, kethuk_gain=0.0, gong_hz=38.0)
    p.balungan('slenthem', '', octave=-2, gain=0.40, pan=0.0)
    p.balungan('saron', 'demung', octave=-1, gain=0.12, pan=-0.3)
    p.gender(gain=0.24, pan=0.2, density=2, octave=0, seed=41)
    p.suling(suling_lines(p, [5, 21, 37, 53], seed=42), gain=0.10, pan=0.2)
    return p


def piece_ending(kendang) -> Piece:
    """Ladrang slendro manyura, irama tanggung — warm and bright: the light comes back."""
    bal = (
        "2 1 2 3 | 2 1 2 6, | 3 3 . . | 3 5 6 5 | 1' 6 5 3 | 2 3 5 6 | 3 5 3 2 | 1 2 1 6, |"
        "5 6 1' 6 | 5 3 2 1 | 6 5 3 5 | 6 1' 6 5 | 3 5 6 1' | 6 5 3 2 | 5 3 2 1 | 3 2 1 6, |"
    )
    p = Piece(Laras('slendro', 277.0), bpm=60, balungan=bal, form='ladrang', name='ending')
    p.structure(kenong_gain=0.46, kempul_gain=0.55, gong_gain=0.95, kethuk_gain=0.2)
    p.balungan('saron', 'demung', octave=-1, gain=0.34, pan=-0.3)
    p.balungan('saron', 'barung', octave=0, gain=0.22, pan=0.35)
    p.balungan('slenthem', '', octave=-2, gain=0.36, pan=0.0)
    p.peking(gain=0.10, pan=0.55, per_beat=2)
    p.bonang('barung', gain=0.17, pan=-0.45, density=2)
    p.bonang('panerus', gain=0.08, pan=0.45, density=4)
    p.gender(gain=0.20, pan=0.2, density=4, octave=0, seed=51)
    p.kendang(kendang('ladrang'), gain=0.38, pan=0.05)
    p.suling(suling_lines(p, [6, 22, 38, 54], seed=52), gain=0.12, pan=0.12)
    return p


# ------------------------------------------------------------------ regions

REGIONS = {
    'temanggung': dict(laras='slendro', hz=262.0, steps=None, gong=41.0,
                       kenal=('2 1 2 6, | 2 1 2 5, | 6, 1 2 3 | 2 1 6, 5, |'
                              '6, 1 2 1 | 6, 5, 2 1 | 3 2 1 6, | 2 1 6, 5, |'
                              '1 2 3 5 | 6 5 3 2 | 5 3 2 1 | 2 1 6, 5, |'),
                       kenal_bpm=40,
                       dialog=('3 2 3 1 | 3 2 3 6, | 3 5 3 2 | 6 5 3 2 | 5 6 5 3 | 2 1 2 3 | 6 5 3 2 | 1 2 1 6, |'
                               "2 3 5 6 | 1' 6 5 3 | 6 5 3 2 | 5 3 2 1 | 3 2 1 6, | 2 1 2 3 | 5 6 5 3 | 2 1 2 6, |"),
                       dialog_form='ladrang', dialog_bpm=58,
                       tuntas=('. 2 . 1 | . 2 . 1 | . 3 . 2 | . 1 . 6, |' * 2
                               + '. 3 . 5 | . 3 . 5 | . 6 . 5 | . 3 . 2 |' * 2
                               + '. 5 . 6 | . 5 . 3 | . 6 . 5 | . 3 . 2 |'
                               + '. 3 . 2 | . 3 . 1 | . 3 . 2 | . 1 . 6, |'),
                       tuntas_bpm=100),
    'magelang': dict(laras='pelog', hz=270.0, steps=None, gong=37.0,
                     kenal=('. 3 . 2 | . 6, . 5, | . 6, . 1 | . 2 . 3 |'
                            '. 5 . 6 | . 5 . 3 | . 2 . 1 | . 6, . 5, |'
                            '. 6, . 1 | . 3 . 2 | . 1 . 6, | . 2 . 3 |'),
                     kenal_bpm=36,
                     dialog=('2 1 2 3 | 2 1 2 6, | 3 3 . . | 6 5 3 2 | 5 3 2 1 | 2 3 5 6 | 5 3 2 1 | 3 2 1 6, |'
                             "5 5 . . | 5 6 1' 6 | 5 3 2 1 | 3 2 1 6, | 3 5 6 5 | 3 2 1 2 | 3 5 3 2 | 1 2 1 6, |"),
                     dialog_form='ladrang', dialog_bpm=56,
                     tuntas=('. 5 . 3 | . 5 . 3 | . 5 . 6 | . 5 . 3 |' * 2
                             + '. 6 . 5 | . 6 . 5 | . 2 . 3 | . 5 . 6 |' * 2
                             + ". 1' . 6 | . 5 . 3 | . 5 . 6 | . 3 . 2 |"
                             + '. 3 . 5 | . 6 . 5 | . 3 . 2 | . 1 . 6, |'),
                     tuntas_bpm=104),
    'wonosobo': dict(laras='pelog', hz=262.0, steps=BARANG, gong=40.0,
                     kenal=('. 7, . 2 | . 3 . 2 | . 7, . 6, | . 5, . 6, |'
                            '. 2 . 3 | . 5 . 6 | . 7 . 6 | . 5 . 3 |'
                            '. 6 . 5 | . 3 . 2 | . 7, . 2 | . 7, . 6, |'),
                     kenal_bpm=34,
                     dialog=('6 7 6 5 | 3 2 3 5 | 6 5 3 2 | 3 5 6 7 |'
                             "2' 7 6 5 | 6 7 2' 7 | 6 5 3 2 | 5 3 2 3 |"
                             '5 6 7 6 | 5 3 2 3 | 5 6 5 3 | 2 3 2 7, |'
                             '3 2 7, 6, | 7, 2 3 5 | 6 7 6 5 | 3 2 7, 6, |'),
                     dialog_form='ketawang', dialog_bpm=54,
                     tuntas=(". 6 . 5 | . 6 . 5 | . 2' . 7 | . 6 . 5 |" * 2
                             + '. 3 . 2 | . 3 . 2 | . 5 . 6 | . 3 . 2 |' * 2
                             + '. 5 . 6 | . 7 . 6 | . 5 . 3 | . 2 . 3 |'
                             + ". 5 . 6 | . 7 . 2' | . 7 . 6 | . 5 . 6 |"),
                     tuntas_bpm=108),
}


def piece_kenal(code: str, kendang) -> Piece:
    """Ketawang, slow and soft under Mbah Kedu's storytelling."""
    r = REGIONS[code]
    p = Piece(Laras(r['laras'], r['hz']), bpm=r['kenal_bpm'], balungan=r['kenal'], form='ketawang', name=f'kenal-{code}', steps=r['steps'])
    p.structure(kenong_gain=0.32, kempul_gain=0.38, gong_gain=0.62, kethuk_gain=0.10, gong_hz=r['gong'])
    p.balungan('slenthem', '', octave=-2, gain=0.38, pan=0.0)
    p.gender(gain=0.27, pan=0.2, density=4 if code != 'magelang' else 2, octave=0, seed=60 + len(code))
    if code == 'temanggung':
        p.balungan('saron', 'barung', octave=0, gain=0.13, pan=0.35)
        p.siter(gain=0.13, pan=-0.45, density=2)
        p.kendang(kendang('ketawang'), gain=0.20, pan=0.0)
    elif code == 'magelang':
        p.balungan('saron', 'demung', octave=-1, gain=0.16, pan=-0.3)
        p.gambang(gain=0.09, pan=-0.4, density=4, seed=61)
        p.kendang(kendang('ketawang'), gain=0.18, pan=0.0)
    else:  # Wonosobo: airy, no kendang
        p.siter(gain=0.15, pan=-0.45, density=2)
    p.suling(suling_lines(p, [3, 19, 35], seed=62 + len(code)), gain=0.10, pan=0.15)
    return p


def piece_dialog(code: str, kendang) -> Piece:
    """Ladrang or ketawang at a walking pace: Jaka and Mbah Kedu on the way."""
    r = REGIONS[code]
    p = Piece(Laras(r['laras'], r['hz']), bpm=r['dialog_bpm'], balungan=r['dialog'], form=r['dialog_form'], name=f'dialog-{code}', steps=r['steps'])
    p.structure(kenong_gain=0.40, kempul_gain=0.48, gong_gain=0.82, kethuk_gain=0.16, gong_hz=r['gong'])
    p.balungan('saron', 'demung', octave=-1, gain=0.26, pan=-0.3)
    p.balungan('saron', 'barung', octave=0, gain=0.19, pan=0.35)
    p.balungan('slenthem', '', octave=-2, gain=0.34, pan=0.0)
    p.peking(gain=0.08, pan=0.55, per_beat=2)
    p.gender(gain=0.20, pan=0.2, density=4, octave=0, seed=70 + len(code))
    if code != 'wonosobo':
        p.bonang('barung', gain=0.14, pan=-0.45, density=2)
    else:
        p.gambang(gain=0.09, pan=-0.4, density=4, seed=71)
    p.kendang(kendang(r['dialog_form']), gain=0.32, pan=0.05)
    p.suling(suling_lines(p, list(range(6, p.n_beats, 16)), seed=72 + len(code)), gain=0.10, pan=0.12)
    return p


def piece_tuntas(code: str, kendang) -> Piece:
    """Lancaran, festive: the shards have come back to the lantern."""
    r = REGIONS[code]
    p = Piece(Laras(r['laras'], r['hz']), bpm=r['tuntas_bpm'], balungan=r['tuntas'], form='lancaran', name=f'tuntas-{code}', steps=r['steps'])
    p.structure(kenong_gain=0.48, kempul_gain=0.55, gong_gain=0.95, kethuk_gain=0.2, gong_hz=r['gong'])
    p.balungan('saron', 'demung', octave=-1, gain=0.36, pan=-0.3)
    p.balungan('saron', 'barung', octave=0, gain=0.26, pan=0.35)
    p.balungan('saron', 'barung', octave=0, gain=0.22, pan=-0.15, player='2')
    p.balungan('slenthem', '', octave=-2, gain=0.34, pan=0.0)
    p.peking(gain=0.13, pan=0.55, per_beat=2)
    p.bonang('barung', gain=0.21, pan=-0.45, density=2)
    p.bonang('panerus', gain=0.11, pan=0.45, density=4)
    p.kendang(kendang_lancaran(), gain=0.44, pan=0.05)
    return p


def pieces(kendang) -> dict:
    """name → builder; `kendang` is music.kendang_patterns (ladrang/ketawang styles)."""
    out = {
        'welcome': lambda: piece_welcome(kendang),
        'opening': lambda: piece_opening(kendang),
        'ending': lambda: piece_ending(kendang),
    }
    for code in REGIONS:
        out[f'kenal-{code}'] = lambda c=code: piece_kenal(c, kendang)
        out[f'dialog-{code}'] = lambda c=code: piece_dialog(c, kendang)
        out[f'tuntas-{code}'] = lambda c=code: piece_tuntas(c, kendang)
    return out


# Seeds and room size per track (music.render); seeds keep each file reproducible on its own
SEEDS = {name: 400 + i * 10 for i, name in enumerate(
    ['welcome', 'opening', 'ending'] + [f'{ctx}-{code}' for code in REGIONS for ctx in ('kenal', 'dialog', 'tuntas')])}
RT60 = {'welcome': 1.8, 'opening': 2.6, 'ending': 1.6,
        **{f'kenal-{code}': 2.3 for code in REGIONS}, 'kenal-wonosobo': 2.7,
        **{f'dialog-{code}': 1.6 for code in REGIONS},
        **{f'tuntas-{code}': 1.3 for code in REGIONS}}
# Loudness before encoding (master.py); a little under the map music, which plays without a voice
LUFS = {'welcome': -20.5, 'opening': -21.5, 'ending': -20.0,
        **{f'kenal-{code}': -21.5 for code in REGIONS},
        **{f'dialog-{code}': -21.0 for code in REGIONS},
        **{f'tuntas-{code}': -20.0 for code in REGIONS}}
