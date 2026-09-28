"""Musik latar dan efek suara GELITA, disintesis dari nol.

    python3 docs/audio/build_music_sfx.py            # semua
    python3 docs/audio/build_music_sfx.py --only map,click

Tidak memakai sampel atau rekaman siapa pun: setiap bunyi dibangun dari
parsial sinus, derau, dan selubung (envelope), jadi hasilnya bebas lisensi
dan sama persis setiap kali dijalankan (RNG ber-seed tetap).

Nuansanya gamelan Jawa, selaras dengan cerita Kedu:
- laras slendro (map, challenge) dan pelog pathet nem (region);
- saron/demung/peking (bilah logam, parsial inharmonik), bonang dan kenong
  (pencon berombak), kempul dan gong ageng dengan ombak lambat, kendang,
  gender berbilah lembut, dan suling;
- struktur kolotomik lancaran 16 ketukan: kethuk di ketukan ganjil, kenong
  tiap 4 ketukan, kempul di 6/10/14, gong di 16.

Musik dirender melingkar: gaung nada di ujung loop dijumlahkan ke awalnya
dan reverb dihitung sebagai konvolusi melingkar, sehingga sambungan loop
mulus. Berkas dimulai tepat pada pukulan gong, jadi jeda kecil encoder MP3
di awal tertutup serangan gong.

Keluaran: public/assets/audio/music/{map,region,challenge}.mp3 dan
public/assets/audio/sfx/{click,correct,wrong,lock,shard,region-done}.mp3.
"""

from __future__ import annotations

import argparse
from dataclasses import dataclass, field

import numpy as np
from scipy.signal import butter, sosfilt

from gelita_audio import AUDIO_DIR, SR, encode_mp3, loudness, normalize, soft_limit, true_peak_db

TAU = 2 * np.pi

# Laras (sen per nada 1 2 3 5 6). Slendro nyaris rata (±240 sen); pelog nem
# memakai nada 1 2 3 5 6 dari tujuh nada pelog.
SLENDRO = {"1": 0, "2": 234, "3": 474, "5": 709, "6": 954}
PELOG_NEM = {"1": 0, "2": 122, "3": 272, "5": 673, "6": 788}


def freq(token: str, scale: dict[str, int], base: float) -> float:
    """Nada notasi kepatihan: `5`, `_6` (oktaf bawah), `1'` (oktaf atas)."""
    octave = token.count("'") - token.count("_")
    tone = token.strip("_'")
    return base * 2 ** ((scale[tone] + 1200 * octave) / 1200)


def notes(text: str) -> list[str]:
    """Balungan per gatra: '3 5 3 2 | 6 5 3 2' → ['3','5','3','2','6',…]; '.' = pin."""
    return [t for t in text.replace("|", " ").split() if t]


# ------------------------------------------------------------------ sintesis


def band(x: np.ndarray, lo: float, hi: float, order: int = 2) -> np.ndarray:
    sos = butter(order, [lo, min(hi, SR * 0.45)], btype="band", fs=SR, output="sos")
    return sosfilt(sos, x)


def lowpass(x: np.ndarray, hz: float, order: int = 2) -> np.ndarray:
    sos = butter(order, hz, btype="low", fs=SR, output="sos")
    return sosfilt(sos, x, axis=0)


def highpass(x: np.ndarray, hz: float, order: int = 2) -> np.ndarray:
    sos = butter(order, hz, btype="high", fs=SR, output="sos")
    return sosfilt(sos, x, axis=0)


@dataclass
class Voice:
    """Resep satu instrumen berparsial: (rasio, amplitudo, T60 detik)."""

    parts: list[tuple[float, float, float]]
    length: float
    attack: float = 0.002
    beat_hz: float = 0.0          # ombak: pasangan parsial dasar yang sedikit sumbang
    beat_depth: float = 0.0
    noise: tuple[float, float, float, float] | None = None  # (lo, hi, dur, amp) derau pukulan
    thump: float = 0.0            # dentum rendah pemukul lunak (gong, kempul)


INSTRUMENTS: dict[str, Voice] = {
    # Bilah saron: parsial inharmonik bilah bebas, cepat meluruh di atas
    "saron": Voice([(1, 1, 1.9), (2.76, 0.26, 0.55), (4.95, 0.09, 0.22), (7.6, 0.035, 0.1)], 2.6,
                   noise=(2200, 6500, 0.004, 0.12)),
    "demung": Voice([(1, 1, 2.6), (2.76, 0.2, 0.8), (4.95, 0.06, 0.3)], 3.2, attack=0.003,
                    noise=(1200, 4000, 0.005, 0.08)),
    "peking": Voice([(1, 1, 1.1), (2.76, 0.3, 0.35), (4.95, 0.1, 0.15)], 1.6,
                    noise=(3000, 9000, 0.003, 0.1)),
    # Pencon (bonang, kenong, kethuk): parsial nyaris harmonis, ombak halus
    "bonang": Voice([(1, 1, 1.2), (1.98, 0.2, 0.5), (2.91, 0.1, 0.28), (4.2, 0.04, 0.14)], 1.8,
                    attack=0.003, beat_hz=1.3, beat_depth=0.25, noise=(700, 2600, 0.006, 0.06)),
    "kenong": Voice([(1, 1, 3.2), (2.02, 0.16, 1.1), (3.1, 0.07, 0.45)], 3.6, attack=0.004,
                    beat_hz=0.9, beat_depth=0.35, noise=(500, 1800, 0.008, 0.04)),
    "kethuk": Voice([(1, 1, 0.16), (2.1, 0.2, 0.07)], 0.3, attack=0.002, noise=(300, 1200, 0.01, 0.08)),
    # Gong gantung: dasar rendah + ombak lambat, dentum lunak
    "kempul": Voice([(1, 1, 4.6), (2.0, 0.35, 2.2), (2.74, 0.12, 1.0), (4.1, 0.05, 0.5)], 4.8,
                    attack=0.012, beat_hz=1.6, beat_depth=0.5, thump=0.25),
    "gong": Voice([(1, 1, 9.0), (2.0, 0.55, 6.0), (2.93, 0.2, 3.2), (4.1, 0.1, 1.8), (5.6, 0.05, 1.0)], 9.5,
                  attack=0.03, beat_hz=0.75, beat_depth=0.6, thump=0.5),
    # Gender: bilah tipis bertabung resonansi, pemukul berlapis kain
    "gender": Voice([(1, 1, 3.4), (2.74, 0.06, 0.7), (5.1, 0.015, 0.25)], 3.6, attack=0.007,
                    beat_hz=0.55, beat_depth=0.3),
}


def strike(name: str, f: float, vel: float, rng: np.random.Generator, damp: float | None = None) -> np.ndarray:
    """Satu pukulan instrumen berparsial pada frekuensi f."""
    v = INSTRUMENTS[name]
    n = int(v.length * SR)
    t = np.arange(n) / SR
    y = np.zeros(n)

    for i, (ratio, amp, t60) in enumerate(v.parts):
        fr = f * ratio * (1 + rng.normal(0, 0.0008))
        if fr >= SR * 0.45:
            continue
        env = np.exp(-6.9078 * t / t60)
        y += amp * np.sin(TAU * fr * t + rng.uniform(0, TAU)) * env
        # Ombak: kembaran sumbang parsial dasar dan kedua
        if v.beat_depth and i < 2:
            y += amp * v.beat_depth * np.sin(TAU * (fr + v.beat_hz * ratio) * t + rng.uniform(0, TAU)) * env

    a = max(1, int(v.attack * SR))
    y[:a] *= np.linspace(0, 1, a) ** 1.5

    if v.noise:
        lo, hi, dur, amp = v.noise
        m = int(dur * SR) + 64
        burst = band(rng.normal(0, 1, m), lo, hi) * np.exp(-np.arange(m) / (dur * SR / 3))
        y[:m] += amp * burst / (np.max(np.abs(burst)) + 1e-9)

    if v.thump:
        m = int(0.08 * SR)
        th = lowpass(rng.normal(0, 1, m), 160) * np.exp(-np.arange(m) / (0.02 * SR))
        y[:m] += v.thump * th / (np.max(np.abs(th)) + 1e-9)

    if damp is not None and damp < v.length:
        # Tangan menahan bilah (pathet): nada lama diredam saat nada baru dipukul
        d0 = int(damp * SR)
        d1 = min(n, d0 + int(0.06 * SR))
        y[d0:d1] *= np.linspace(1, 0, d1 - d0)
        y[d1:] = 0

    return (vel * y).astype(np.float32)


def kendang(kind: str, vel: float, rng: np.random.Generator) -> np.ndarray:
    """Kendang: dhung (rendah), tung (sedang), tak (tepak), ket (tutup)."""
    n = int(0.45 * SR)
    t = np.arange(n) / SR
    if kind in ("dhung", "tung"):
        f0, f1, t60 = (125, 84, 0.38) if kind == "dhung" else (235, 205, 0.26)
        f = f1 + (f0 - f1) * np.exp(-t / 0.035)
        y = np.sin(TAU * np.cumsum(f) / SR) * np.exp(-6.9078 * t / t60)
        skin = lowpass(rng.normal(0, 1, n), 900) * np.exp(-t / 0.012)
        y = y + 0.25 * skin / (np.max(np.abs(skin)) + 1e-9)
    elif kind == "tak":
        slap = band(rng.normal(0, 1, n), 1100, 4200) * np.exp(-t / 0.018)
        y = slap / (np.max(np.abs(slap)) + 1e-9) + 0.5 * np.sin(TAU * 430 * t) * np.exp(-t / 0.03)
    else:  # ket
        y = np.sin(TAU * 310 * t) * np.exp(-t / 0.025) + 0.3 * band(rng.normal(0, 1, n), 600, 2400) * np.exp(-t / 0.01)
    return (vel * 0.8 * y).astype(np.float32)


def suling(f: float, dur: float, vel: float, rng: np.random.Generator) -> np.ndarray:
    """Suling bambu: nada hampir sinus, vibrato, embusan napas, luncuran awal."""
    n = int((dur + 0.25) * SR)
    t = np.arange(n) / SR
    vib = 1 + 0.0075 * np.sin(TAU * 5.1 * t + rng.uniform(0, TAU)) * np.clip((t - 0.18) / 0.3, 0, 1)
    scoop = 2 ** (-35 / 1200 * np.exp(-t / 0.05))
    inst = f * vib * scoop
    phase = TAU * np.cumsum(inst) / SR
    y = np.sin(phase) + 0.14 * np.sin(2 * phase) + 0.04 * np.sin(3 * phase)
    env = np.clip(t / 0.08, 0, 1) * np.clip((dur + 0.2 - t) / 0.2, 0, 1)
    breath = band(rng.normal(0, 1, n), 1400, 6000)
    y = y * env + 0.035 * breath / (np.max(np.abs(breath)) + 1e-9) * env
    return (vel * y).astype(np.float32)


# ------------------------------------------------------------------ mixing


@dataclass
class Loop:
    """Bus stereo melingkar sepanjang satu loop."""

    seconds: float
    rng: np.random.Generator
    buf: np.ndarray = field(init=False)

    def __post_init__(self) -> None:
        self.n = int(round(self.seconds * SR))
        self.buf = np.zeros((self.n, 2), dtype=np.float64)

    def add(self, t: float, y: np.ndarray, pan: float = 0.0, jitter: float = 0.006) -> None:
        """Tempatkan y pada detik t (melingkar), pan -1 … 1 (hukum daya tetap)."""
        start = int(round((t + self.rng.normal(0, jitter)) * SR)) % self.n
        left, right = np.cos((pan + 1) * np.pi / 4), np.sin((pan + 1) * np.pi / 4)
        pos = 0
        while pos < len(y):
            seg = min(len(y) - pos, self.n - (start + pos) % self.n)
            at = (start + pos) % self.n
            self.buf[at:at + seg, 0] += y[pos:pos + seg] * left
            self.buf[at:at + seg, 1] += y[pos:pos + seg] * right
            pos += seg


def reverb_ir(rt60: float, rng: np.random.Generator, predelay: float = 0.018) -> np.ndarray:
    """Tanggapan impuls ruang stereo: derau meluruh eksponensial, makin gelap."""
    n = int((rt60 * 1.1 + predelay) * SR)
    t = np.arange(n) / SR
    ir = rng.normal(0, 1, (n, 2)) * np.exp(-6.9078 * t / rt60)[:, None]
    ir = lowpass(ir, 5200) * 0.6 + lowpass(ir, 1800) * 0.4
    ir[: int(predelay * SR)] = 0
    return ir / np.sqrt(np.sum(ir ** 2, axis=0, keepdims=True))


def circular_reverb(x: np.ndarray, rt60: float, mix: float, rng: np.random.Generator) -> np.ndarray:
    """Reverb sebagai konvolusi melingkar: ekor di ujung loop jatuh ke awalnya."""
    ir = reverb_ir(rt60, rng)
    n = len(x)
    padded = np.zeros((n, 2))
    m = min(len(ir), n)
    padded[:m] = ir[:m]
    wet = np.fft.irfft(np.fft.rfft(x, axis=0) * np.fft.rfft(padded, axis=0), n=n, axis=0)
    return (1 - mix) * x + mix * wet


def circular_highpass(x: np.ndarray, hz: float) -> np.ndarray:
    """High-pass tanpa lonjakan di sambungan: filter atas tiga salinan, ambil tengah."""
    n = len(x)
    return highpass(np.concatenate([x, x, x]), hz)[n:2 * n]


# ------------------------------------------------------------------ musik


def colotomic(loop: Loop, bal: list[str], scale: dict[str, int], beat: float, gongan: int, vel: float,
              gong_f: float, kempul_base: float, kenong_base: float, kethuk: bool = True) -> None:
    """Lancaran 16 ketukan: kethuk ganjil, kenong 4/8/12/16, kempul 6/10/14, gong 16."""
    for g in range(gongan):
        for b in range(1, 17):
            i = g * 16 + b - 1
            t = (i + 1) * beat          # loop dimulai pada gong (ketukan 16 gongan terakhir)
            tone = next(n for n in (bal[i], bal[i - 1], bal[i - 2]) if n != ".")
            if kethuk and b % 2 == 1:
                loop.add(t, strike("kethuk", freq("2", scale, kenong_base), 0.35 * vel, loop.rng), pan=-0.25)
            if b % 4 == 0:
                loop.add(t, strike("kenong", freq(tone, scale, kenong_base), 0.55 * vel, loop.rng), pan=0.3)
            if b in (6, 10, 14):
                loop.add(t, strike("kempul", freq(tone, scale, kempul_base), 0.6 * vel, loop.rng), pan=-0.1)
            if b == 16:
                loop.add(t, strike("gong", gong_f, 1.0 * vel, loop.rng), pan=0.0, jitter=0)


def balungan(loop: Loop, bal: list[str], scale: dict[str, int], beat: float, base: float, vel: list[float],
             demung: bool = True, peking: bool = True, bonang: bool = True, bonang_vel: float = 0.45) -> None:
    """Saron (+demung) pada ketukan, peking ganda, bonang mipil dua kali lipat."""
    n = len(bal)
    for i, tone in enumerate(bal):
        t = (i + 1) * beat
        v = vel[i // 16]
        if tone != ".":
            loop.add(t, strike("saron", freq(tone, scale, base), 0.55 * v, loop.rng, damp=beat * 1.02), pan=-0.35)
            if demung:
                loop.add(t, strike("demung", freq("_" + tone, scale, base), 0.5 * v, loop.rng, damp=beat * 1.02), pan=0.1)
            if peking:
                for dt in (-beat / 2, 0.0):
                    loop.add(t + dt, strike("peking", freq(tone + "'", scale, base), 0.22 * v, loop.rng, damp=beat / 2), pan=0.45)
        if bonang:
            # Mipil: nada ketukan ini, lalu mendahului nada berikutnya; gatra ditutup gembyang (dua oktaf)
            nxt = next((bal[(i + k) % n] for k in range(1, 4) if bal[(i + k) % n] != "."), tone)
            here = tone if tone != "." else nxt
            loop.add(t, strike("bonang", freq(here, scale, base), bonang_vel * v, loop.rng), pan=0.35)
            loop.add(t + beat / 2, strike("bonang", freq(nxt, scale, base), bonang_vel * 0.8 * v, loop.rng), pan=0.35)
            if (i + 1) % 4 == 0:
                loop.add(t, strike("bonang", freq(here + "'", scale, base), bonang_vel * 0.7 * v, loop.rng), pan=0.4)


def melody_suling(loop: Loop, phrase: list[tuple[float, str, float]], scale: dict[str, int], beat: float,
                  base: float, vel: float) -> None:
    """Frasa suling: (ketukan mulai, nada, lama dalam ketukan)."""
    for start, tone, length in phrase:
        loop.add(start * beat, suling(freq(tone, scale, base), length * beat, vel, loop.rng), pan=0.05, jitter=0.02)


def finish_music(loop: Loop, rt60: float, mix: float, target_lufs: float) -> np.ndarray:
    x = circular_highpass(loop.buf, 35)
    x = circular_reverb(x, rt60, mix, loop.rng)
    x = normalize(x.astype(np.float32), target_lufs, ceiling_dbtp=-1.5)
    return x


def music_map(rng: np.random.Generator) -> np.ndarray:
    """Peta Kedu: tenang dan lapang, slendro, 60 BPM, empat gongan (64 detik)."""
    beat = 1.0
    bal = notes("""
        3 5 3 2 | 6 5 3 2 | 5 6 5 3 | 2 1 2 6
        3 3 . . | 6 5 3 2 | 5 3 2 1 | 3 2 1 6
        2 1 2 6 | 2 1 2 6 | 3 5 6 1' | 6 5 3 2
        5 6 5 3 | 2 1 2 3 | 5 3 2 1 | 3 2 1 6
    """)
    loop = Loop(64 * beat, rng)
    colotomic(loop, bal, SLENDRO, beat, 4, 0.9, gong_f=47.5, kempul_base=138.0, kenong_base=277.0)
    balungan(loop, bal, SLENDRO, beat, base=277.0, vel=[0.75, 0.9, 0.95, 0.82], peking=False, bonang_vel=0.38)
    # Suling di gongan kedua dan ketiga, berakhir pada nada gong
    phrase_b = [(17.5, "6", 1.5), (19, "1'", 1.0), (20, "6", 2.0), (22.5, "5", 1.5), (24, "3", 1.0), (25, "2", 3.0),
                (28.5, "3", 0.5), (29, "5", 1.0), (30, "6", 1.5), (31.5, "5", 0.5), (32, "6", 3.0)]
    phrase_c = [(34, "2'", 1.0), (35, "1'", 1.0), (36, "6", 2.0), (38.5, "1'", 0.5), (39, "2'", 1.0), (40, "1'", 1.0),
                (41, "6", 2.0), (43.5, "5", 0.5), (44, "6", 2.0), (46, "5", 1.0), (47, "3", 1.0), (48, "2", 3.5)]
    melody_suling(loop, phrase_b + phrase_c, SLENDRO, beat, base=554.0, vel=0.2)
    return finish_music(loop, rt60=2.2, mix=0.32, target_lufs=-22.0)


def music_region(rng: np.random.Generator) -> np.ndarray:
    """Peta wilayah: lebih hidup, pelog nem, lancaran 84 BPM dengan kendang (±46 detik)."""
    beat = 60 / 84
    bal = notes("""
        . 3 . 2 | . 3 . 5 | . 6 . 5 | . 3 . 2
        . 5 . 6 | . 5 . 3 | . 5 . 3 | . 2 . 1
        . 2 . 1 | . 2 . 3 | . 5 . 6 | . 5 . 3
        . 5 . 6 | . 1' . 6 | . 5 . 3 | . 2 . 1
    """)
    # Lancaran nibani: saron mengisi ketukan kosong dengan nada berikutnya (imbal sederhana)
    filled = [tone if tone != "." else next(bal[(i + k) % len(bal)] for k in range(1, 3) if bal[(i + k) % len(bal)] != ".") for i, tone in enumerate(bal)]
    loop = Loop(64 * beat, rng)
    colotomic(loop, filled, PELOG_NEM, beat, 4, 0.85, gong_f=52.0, kempul_base=147.0, kenong_base=294.0)
    balungan(loop, bal, PELOG_NEM, beat, base=294.0, vel=[0.85, 0.95, 0.95, 0.9], bonang_vel=0.42)
    # Kendang lancaran: pola per gatra, penutup gongan lebih ramai
    pattern = ["tak", None, "dhung", "ket", "tak", "tung", "dhung", None]
    closing = ["tak", "tung", "tak", "tung", "dhung", "ket", "dhung", None]
    for g in range(4):
        for q in range(4):
            pat = closing if q == 3 else pattern
            for k, kind in enumerate(pat):
                if kind:
                    t = (g * 16 + q * 4 + k / 2 + 0.5) * beat
                    loop.add(t, kendang(kind, 0.42 if kind in ("tak", "ket") else 0.5, rng), pan=-0.15, jitter=0.004)
    return finish_music(loop, rt60=1.5, mix=0.24, target_lufs=-21.0)


def music_challenge(rng: np.random.Generator) -> np.ndarray:
    """Tantangan: jarang dan redup, gender slendro rendah, tanpa melodi atas (±58 detik)."""
    beat = 60 / 66
    loop = Loop(64 * beat, rng)
    # Pola gender: arpeggio lembut (nada, nada atas tetangga, oktaf, tetangga), berganti tiap gongan
    progressions = [["6", "2", "3", "2"], ["5", "3", "2", "1"], ["2", "1", "2", "6"], ["3", "5", "3", "2"]]
    up = {"1": "2", "2": "3", "3": "5", "5": "6", "6": "1'"}
    for g, prog in enumerate(progressions):
        for q, tone in enumerate(prog):
            figure = ["_" + tone, "_" + up[tone], tone, "_" + up[tone]]
            for k, note in enumerate(figure):
                t = (g * 16 + q * 4 + k + 1) * beat
                loop.add(t, strike("gender", freq(note, SLENDRO, 277.0), 0.32 if k != 2 else 0.24, rng), pan=0.2 if k % 2 == 0 else -0.2, jitter=0.01)
        # Kempul lembut di tengah dan gong kecil di ujung gongan
        loop.add((g * 16 + 8) * beat, strike("kempul", freq(prog[1], SLENDRO, 138.0), 0.28, rng), pan=0.0)
        loop.add((g * 16 + 16) % 64 * beat, strike("gong", 47.5, 0.55, rng), pan=0.0, jitter=0)
        for b in range(2, 17, 2):
            loop.add((g * 16 + b) * beat, strike("kethuk", freq("2", SLENDRO, 277.0), 0.12, rng), pan=-0.3)
    x = loop.buf
    # Redupkan kecerahan agar tidak mengganggu membaca
    n = len(x)
    x = lowpass(np.concatenate([x, x, x]), 2600)[n:2 * n]
    loop.buf = x
    return finish_music(loop, rt60=2.6, mix=0.38, target_lufs=-25.0)


# ------------------------------------------------------------------ efek suara


def place(total: float, events: list[tuple[float, np.ndarray, float]]) -> np.ndarray:
    """Tempatkan bunyi mono ke bus stereo linear (bukan melingkar)."""
    n = int(total * SR)
    out = np.zeros((n, 2))
    for t, y, pan in events:
        s = int(t * SR)
        m = min(len(y), n - s)
        left, right = np.cos((pan + 1) * np.pi / 4), np.sin((pan + 1) * np.pi / 4)
        out[s:s + m, 0] += y[:m] * left
        out[s:s + m, 1] += y[:m] * right
    return out


def finish_sfx(x: np.ndarray, peak_db: float, fade: float = 0.03, room: float = 0.0, rng: np.random.Generator | None = None,
               hp: float = 60.0) -> np.ndarray:
    if room:
        ir = reverb_ir(0.9, rng or np.random.default_rng(0))
        wet = np.stack([np.convolve(x[:, c], ir[:, c])[: len(x)] for c in range(2)], axis=1)
        x = (1 - room) * x + room * wet
    x = highpass(x, hp)
    f = int(fade * SR)
    x[-f:] *= np.linspace(1, 0, f)[:, None] ** 2
    x = x / (np.max(np.abs(x)) + 1e-9) * 10 ** (peak_db / 20)
    return soft_limit(x.astype(np.float32), -1.0)


def sfx_click(rng: np.random.Generator) -> np.ndarray:
    """Ketuk kayu bambu singkat."""
    n = int(0.12 * SR)
    t = np.arange(n) / SR
    y = np.sin(TAU * 1850 * t) * np.exp(-t / 0.012) + 0.45 * np.sin(TAU * 2930 * t) * np.exp(-t / 0.007)
    y += 0.5 * band(rng.normal(0, 1, n), 1500, 5000) * np.exp(-t / 0.003)
    return finish_sfx(place(0.12, [(0.0, y, 0.0)]), peak_db=-9.0, fade=0.02)


def sfx_correct(rng: np.random.Generator) -> np.ndarray:
    """Dua nada bonang naik (5 → 1') dengan kilau peking."""
    ev = [
        (0.0, strike("bonang", freq("5", SLENDRO, 554.0), 0.8, rng), -0.1),
        (0.13, strike("bonang", freq("1'", SLENDRO, 554.0), 1.0, rng), 0.1),
        (0.13, strike("peking", freq("1''", SLENDRO, 554.0), 0.25, rng), 0.3),
    ]
    return finish_sfx(place(0.9, ev), peak_db=-4.0, fade=0.25, room=0.18, rng=rng)


def sfx_wrong(rng: np.random.Generator) -> np.ndarray:
    """Dua pukulan kempul turun yang lembut, bukan dengung keras."""
    a = strike("kempul", freq("2", SLENDRO, 220.0), 0.7, rng)[: int(0.7 * SR)]
    b = strike("kempul", freq("_6", SLENDRO, 220.0), 0.85, rng)[: int(0.7 * SR)]
    return finish_sfx(place(0.62, [(0.0, a, -0.05), (0.14, b, 0.05)]), peak_db=-5.0, fade=0.2, room=0.12, rng=rng)


def sfx_lock(rng: np.random.Generator) -> np.ndarray:
    """Klak gerendel logam: dua ketuk teredam dan denting kecil."""
    n = int(0.08 * SR)
    t = np.arange(n) / SR
    clank = band(rng.normal(0, 1, n), 2500, 8000) * np.exp(-t / 0.004)
    clank = 0.6 * clank / (np.max(np.abs(clank)) + 1e-9) + 0.4 * np.sin(TAU * 3400 * t) * np.exp(-t / 0.02)
    ev = [
        (0.0, strike("kethuk", 330.0, 0.9, rng), 0.0),
        (0.0, clank, 0.1),
        (0.09, strike("kethuk", 294.0, 0.8, rng), 0.0),
        (0.09, 0.7 * clank, -0.1),
    ]
    return finish_sfx(place(0.36, ev), peak_db=-6.0, fade=0.08)


def sfx_shard(rng: np.random.Generator) -> np.ndarray:
    """Kilau serpihan cahaya: arpeggio peking cepat naik dan gaung lembut."""
    run = ["3'", "5'", "6'", "1''", "2''"]
    ev = [(0.045 * i, strike("peking", freq(tone, SLENDRO, 277.0), 0.55 + 0.1 * i, rng), -0.4 + 0.2 * i) for i, tone in enumerate(run)]
    ev.append((0.2, strike("gender", freq("1''", SLENDRO, 277.0), 0.35, rng)[: int(0.8 * SR)], 0.0))
    return finish_sfx(place(1.0, ev), peak_db=-4.0, fade=0.3, room=0.3, rng=rng)


def sfx_region_done(rng: np.random.Generator) -> np.ndarray:
    """Wilayah tuntas: gong, run bonang 1 2 3 5 6 1', ditutup kenong dan peking."""
    run = ["1", "2", "3", "5", "6", "1'"]
    ev = [(0.0, strike("gong", 47.5, 1.0, rng), 0.0)]
    ev += [(0.12 + 0.085 * i, strike("bonang", freq(tone, SLENDRO, 277.0), 0.55 + 0.05 * i, rng), -0.3 + 0.12 * i) for i, tone in enumerate(run)]
    end = 0.12 + 0.085 * len(run) + 0.05
    ev += [
        (end, strike("kenong", freq("1'", SLENDRO, 277.0), 0.7, rng), 0.2),
        (end, strike("peking", freq("1''", SLENDRO, 277.0), 0.35, rng), 0.4),
        (end, strike("saron", freq("1'", SLENDRO, 277.0), 0.5, rng), -0.3),
    ]
    return finish_sfx(place(3.0, ev), peak_db=-3.0, fade=0.8, room=0.25, rng=rng, hp=30.0)


MUSIC = {"map": music_map, "region": music_region, "challenge": music_challenge}
SFX = {
    "click": sfx_click, "correct": sfx_correct, "wrong": sfx_wrong, "lock": sfx_lock,
    "shard": sfx_shard, "region-done": sfx_region_done,
}


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__.split("\n")[0])
    parser.add_argument("--only", help="nama dipisah koma, mis. map,click")
    args = parser.parse_args()
    only = set(args.only.split(",")) if args.only else None

    for i, (name, build) in enumerate({**MUSIC, **SFX}.items()):
        if only and name not in only:
            continue
        rng = np.random.default_rng(20260927 + i)
        x = build(rng)
        folder = "music" if name in MUSIC else "sfx"
        path = AUDIO_DIR / folder / f"{name}.mp3"
        encode_mp3(path, x, 128, title=f"GELITA {folder} {name}")
        lufs = loudness(x) if len(x) > SR * 0.5 else float("nan")
        print(f"{folder}/{name}.mp3  {len(x) / SR:6.2f} s  {lufs:6.1f} LUFS  {true_peak_db(x):5.1f} dBTP  {path.stat().st_size // 1024} KB")


if __name__ == "__main__":
    main()
