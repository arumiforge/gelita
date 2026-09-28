"""Synthesizer gamelan Jawa sederhana (aditif + noise), stereo 44,1 kHz.

Semua bunyi dibangkitkan dari nol (tidak ada sampel rekaman), jadi hasilnya
bebas dipakai. Laras memakai pelog/slendro dengan oktaf sedikit melebar dan
penyimpangan kecil per bilah, seperti gamelan sungguhan yang tiap perangkatnya
punya "embat" sendiri.
"""
from __future__ import annotations

import math
from dataclasses import dataclass, field

import numpy as np
from scipy.signal import butter, fftconvolve, sosfilt

SR = 44100
RNG = np.random.default_rng(20260928)

# ------------------------------------------------------------------ laras

# sen relatif terhadap nada 1 (ji); oktaf melebar ±1212 sen
PELOG_CENTS = {1: 0, 2: 122, 3: 263, 4: 543, 5: 676, 6: 788, 7: 948}
SLENDRO_CENTS = {1: 0, 2: 236, 3: 474, 5: 717, 6: 956}
OCTAVE_CENTS = 1212.0


@dataclass
class Laras:
    name: str               # 'pelog' | 'slendro'
    base_hz: float          # nada 1 di oktaf tengah (saron barung)
    detune: dict = field(default_factory=dict)   # (nada, oktaf) → sen, "embat" per bilah

    def hz(self, note: int, octave: int = 0) -> float:
        table = PELOG_CENTS if self.name == 'pelog' else SLENDRO_CENTS
        key = (note, octave)
        if key not in self.detune:
            self.detune[key] = float(RNG.normal(0, 3.0))
        cents = table[note] + octave * OCTAVE_CENTS + self.detune[key]
        return self.base_hz * 2 ** (cents / 1200.0)


def parse_note(tok: str) -> tuple[int, int] | None:
    """'6' → (6,0); '6,' → (6,-1); '6,,' → (6,-2); "1'" → (1,1); '.' → None."""
    tok = tok.strip()
    if tok in ('.', '-', ''):
        return None
    note = int(tok[0])
    octave = tok.count("'") - tok.count(',')
    return note, octave


# ------------------------------------------------------------------ util DSP

def env_exp(n: int, t60: float) -> np.ndarray:
    t = np.arange(n) / SR
    return np.exp(-6.907755 * t / max(t60, 1e-3))


def attack(n: int, ms: float) -> np.ndarray:
    a = np.ones(n)
    k = max(1, int(SR * ms / 1000))
    k = min(k, n)
    a[:k] = 0.5 - 0.5 * np.cos(np.linspace(0, np.pi, k))
    return a


def damp_after(sig: np.ndarray, at_s: float | None, tau: float = 0.035) -> np.ndarray:
    """Redam bilah setelah `at_s` detik (tangan pemain menekan bilah)."""
    if at_s is None:
        return sig
    k = int(at_s * SR)
    if k >= len(sig):
        return sig
    out = sig.copy()
    t = np.arange(len(sig) - k) / SR
    out[k:] *= np.exp(-t / tau)
    cut = k + int(tau * 8 * SR)
    if cut < len(out):
        out[cut:] = 0.0
    return out


def bandnoise(n: int, lo: float, hi: float, order: int = 2) -> np.ndarray:
    sos = butter(order, [lo, min(hi, SR / 2 * 0.95)], btype='band', fs=SR, output='sos')
    return sosfilt(sos, RNG.standard_normal(n))


def lowpass(x: np.ndarray, hz: float, order: int = 2) -> np.ndarray:
    return sosfilt(butter(order, hz, btype='low', fs=SR, output='sos'), x)


def highpass(x: np.ndarray, hz: float, order: int = 2) -> np.ndarray:
    return sosfilt(butter(order, hz, btype='high', fs=SR, output='sos'), x)


def modal(freq: float, dur: float, modes: list[tuple[float, float, float]], att_ms: float = 2.0,
          beat_hz: float = 0.0, beat_depth: float = 0.0, phase_rand: bool = True) -> np.ndarray:
    """Jumlah mode: (rasio, amplitudo, t60)."""
    n = int(dur * SR)
    t = np.arange(n) / SR
    out = np.zeros(n)
    for ratio, amp, t60 in modes:
        f = freq * ratio
        if f >= SR * 0.45:
            continue
        ph = RNG.uniform(0, 2 * np.pi) if phase_rand else 0.0
        out += amp * np.sin(2 * np.pi * f * t + ph) * env_exp(n, t60)
    if beat_hz > 0:
        # ombak: dua mode berdekatan → denyut amplitudo
        out *= 1.0 - beat_depth * (0.5 - 0.5 * np.cos(2 * np.pi * beat_hz * t))
    return out * attack(n, att_ms)


# ------------------------------------------------------------------ instrumen

def saron(freq: float, vel: float = 1.0, damp: float | None = None, kind: str = 'barung') -> np.ndarray:
    t60 = {'demung': 3.8, 'barung': 2.6, 'peking': 1.5}[kind]
    hard = {'demung': 0.7, 'barung': 0.9, 'peking': 1.0}[kind]
    modes = [(1.0, 1.0, t60), (1.0 + RNG.uniform(0.001, 0.003), 0.35, t60 * 0.9),
             (2.74, 0.30 * hard, t60 * 0.35), (5.23, 0.10 * hard, t60 * 0.15), (8.7, 0.035 * hard, t60 * 0.07)]
    dur = min(t60 * 1.2, (damp + 0.4) if damp is not None else t60 * 1.2)
    sig = modal(freq, dur, modes, att_ms=1.2)
    n = len(sig)
    click = bandnoise(n, 1800, 7000) * env_exp(n, 0.012) * 0.10 * hard
    sig = sig + click
    return damp_after(sig, damp) * vel


def slenthem(freq: float, vel: float = 1.0, damp: float | None = None) -> np.ndarray:
    modes = [(1.0, 1.0, 6.0), (1.002, 0.5, 5.5), (2.0, 0.12, 2.0), (2.76, 0.05, 1.2)]
    dur = (damp + 0.5) if damp is not None else 7.0
    sig = modal(freq, dur, modes, att_ms=9)
    return damp_after(sig, damp, tau=0.08) * vel


def gender(freq: float, vel: float = 1.0, damp: float | None = None) -> np.ndarray:
    modes = [(1.0, 1.0, 4.5), (1.0015, 0.45, 4.0), (2.0, 0.10, 1.6), (2.78, 0.07, 0.9), (5.4, 0.02, 0.35)]
    dur = (damp + 0.4) if damp is not None else 5.0
    sig = modal(freq, dur, modes, att_ms=4)
    n = len(sig)
    sig += bandnoise(n, 900, 3500) * env_exp(n, 0.01) * 0.03
    return damp_after(sig, damp, tau=0.06) * vel


def bonang(freq: float, vel: float = 1.0, kind: str = 'barung', damp: float | None = None) -> np.ndarray:
    t60 = 1.9 if kind == 'barung' else 1.35
    modes = [(1.0, 1.0, t60), (1.004, 0.4, t60 * 0.9), (2.03, 0.42, t60 * 0.55), (2.94, 0.20, t60 * 0.35),
             (4.08, 0.10, t60 * 0.2), (5.62, 0.05, t60 * 0.12)]
    dur = t60 * 1.3 if damp is None else damp + 0.3
    n = int(dur * SR)
    t = np.arange(n) / SR
    # sedikit "tekukan" nada di awal pukulan (kettle)
    bend = 1.0 + 0.006 * np.exp(-t / 0.025)
    out = np.zeros(n)
    for ratio, amp, tt in modes:
        ph = np.cumsum(2 * np.pi * freq * ratio * bend / SR) + RNG.uniform(0, 6.28)
        out += amp * np.sin(ph) * env_exp(n, tt)
    out *= attack(n, 3.0)
    out += bandnoise(n, 1200, 5000) * env_exp(n, 0.008) * 0.06
    return damp_after(out, damp) * vel


def kenong(freq: float, vel: float = 1.0) -> np.ndarray:
    modes = [(1.0, 1.0, 4.2), (1.0 + 1.3 / freq, 0.55, 4.0), (1.98, 0.45, 2.2), (2.97, 0.16, 1.2), (4.2, 0.06, 0.6)]
    sig = modal(freq, 5.0, modes, att_ms=6)
    n = len(sig)
    sig += bandnoise(n, 300, 1800) * env_exp(n, 0.02) * 0.05
    return sig * vel


def kethuk(freq: float, vel: float = 1.0) -> np.ndarray:
    modes = [(1.0, 1.0, 0.28), (2.1, 0.3, 0.12), (3.1, 0.1, 0.06)]
    sig = modal(freq, 0.5, modes, att_ms=4)
    n = len(sig)
    sig += lowpass(RNG.standard_normal(n), 900) * env_exp(n, 0.02) * 0.2
    return sig * vel


def kempul(freq: float, vel: float = 1.0) -> np.ndarray:
    beat = RNG.uniform(1.6, 2.6)
    modes = [(1.0, 1.0, 6.0), (1.0 + beat / freq, 0.8, 5.5), (2.02, 0.5, 3.5), (2.95, 0.22, 2.0),
             (4.1, 0.08, 1.0), (5.5, 0.03, 0.6)]
    sig = modal(freq, 7.0, modes, att_ms=10)
    n = len(sig)
    sig += lowpass(RNG.standard_normal(n), 400) * env_exp(n, 0.03) * 0.15
    return sig * vel


def gong_ageng(freq: float = 41.0, vel: float = 1.0) -> np.ndarray:
    """Gong ageng: fundamental rendah, denyut 'ombak' lambat, dengung parsial ke-2 yang mengembang."""
    dur = 14.0
    n = int(dur * SR)
    t = np.arange(n) / SR
    f1 = freq
    out = np.zeros(n)
    # dua mode dekat → ombak ~2,5 Hz
    out += 1.0 * np.sin(2 * np.pi * f1 * t) * env_exp(n, 12.0)
    out += 0.85 * np.sin(2 * np.pi * (f1 + 2.4) * t + 1.0) * env_exp(n, 11.0)
    # parsial ke-2 mengembang pelan lalu meluruh ("dengung")
    swell = (1 - np.exp(-t / 0.35)) * env_exp(n, 9.0)
    out += 0.75 * np.sin(2 * np.pi * (2.02 * f1) * t + 0.3) * swell
    out += 0.5 * np.sin(2 * np.pi * (2.02 * f1 + 1.1) * t + 2.0) * swell
    out += 0.28 * np.sin(2 * np.pi * (2.97 * f1) * t) * env_exp(n, 5.0)
    out += 0.12 * np.sin(2 * np.pi * (4.13 * f1) * t) * env_exp(n, 3.0)
    out += 0.06 * np.sin(2 * np.pi * (5.6 * f1) * t) * env_exp(n, 2.0)
    out += 0.03 * np.sin(2 * np.pi * (7.9 * f1) * t) * env_exp(n, 1.2)
    out *= attack(n, 22)
    out += lowpass(RNG.standard_normal(n), 250) * env_exp(n, 0.06) * 0.25
    return out * vel


def gambang(freq: float, vel: float = 1.0) -> np.ndarray:
    modes = [(1.0, 1.0, 0.55), (3.92, 0.22, 0.16), (9.1, 0.05, 0.05)]
    sig = modal(freq, 0.8, modes, att_ms=0.8)
    n = len(sig)
    sig += bandnoise(n, 1500, 6000) * env_exp(n, 0.006) * 0.12
    return sig * vel


def siter(freq: float, vel: float = 1.0, dur: float = 2.2) -> np.ndarray:
    """Karplus–Strong (filter sisir IIR): siter/celempung dipetik kuku."""
    from scipy.signal import lfilter
    n = int(dur * SR)
    p = max(2, int(round(SR / freq - 0.5)))
    t60 = 1.8 * (220.0 / max(freq, 110)) ** 0.35
    g = 10 ** (-3 * (p + 0.5) / (t60 * SR))           # peluruhan per putaran
    exc = np.zeros(n)
    burst = lowpass(RNG.uniform(-1, 1, p), min(9000, freq * 10))
    exc[:p] = burst
    a = np.zeros(p + 2)
    a[0] = 1.0
    a[p] = -0.5 * g
    a[p + 1] = -0.5 * g
    out = lfilter([1.0], a, exc)
    out = highpass(out, 80) * attack(n, 1.0)
    return out / (np.max(np.abs(out)) + 1e-9) * vel * 0.7


def suling_phrase(notes: list[tuple[float, float, float]], vel: float = 1.0) -> np.ndarray:
    """notes: (hz, mulai_s, durasi_s) berurutan → satu frasa bertiup dengan portamento & vibrato."""
    end = max(s + d for _, s, d in notes) + 0.35
    n = int(end * SR)
    t = np.arange(n) / SR
    f = np.zeros(n)
    amp = np.zeros(n)
    for i, (hz, s, d) in enumerate(notes):
        a, b = int(s * SR), min(n, int((s + d) * SR))
        f[a:b] = hz
        # ruas nada: embusan naik, sustain, luruh
        seg = np.ones(b - a)
        k_in = min(len(seg), int(0.035 * SR))
        seg[:k_in] = np.linspace(0.55 if i else 0.0, 1.0, k_in)
        amp[a:b] = seg
    # isi celah dengan nada sebelumnya (legato)
    last = 0.0
    for i in range(n):
        if f[i] == 0:
            f[i] = last
        else:
            last = f[i]
    first = next((v for v in f if v > 0), 440.0)
    f[f == 0] = first
    # portamento
    glide = int(0.045 * SR)
    kern = np.ones(glide) / glide
    f = np.convolve(np.pad(f, (glide, glide), mode='edge'), kern, mode='same')[glide:-glide]
    # vibrato tertunda per nada
    vib = np.zeros(n)
    for hz, s, d in notes:
        a, b = int(s * SR), min(n, int((s + d) * SR))
        if d > 0.45:
            tt = np.arange(b - a) / SR
            depth = np.clip((tt - 0.25) / 0.4, 0, 1) * 0.012
            vib[a:b] = depth * np.sin(2 * np.pi * 5.3 * tt)
    f = f * (1 + vib)
    ph = np.cumsum(2 * np.pi * f / SR)
    tone = np.sin(ph) + 0.22 * np.sin(2 * ph + 0.4) + 0.07 * np.sin(3 * ph + 1.1)
    breath = lowpass(bandnoise(n, 900, 4500), 3500) * 0.035 + bandnoise(n, 300, 900) * 0.02
    # envelope keseluruhan frasa
    sm = int(0.02 * SR)
    amp = np.convolve(amp, np.ones(sm) / sm, mode='same')
    rel = np.ones(n)
    k = int(0.3 * SR)
    rel[-k:] = np.linspace(1, 0, k)
    out = (tone * 0.85 + breath * (0.6 + 0.4 * amp)) * amp * rel
    chiff = np.zeros(n)
    for hz, s, d in notes:
        a = int(s * SR)
        m = min(n - a, int(0.05 * SR))
        chiff[a:a + m] += bandnoise(m, 2000, 8000) * env_exp(m, 0.015) * 0.25
    return highpass(out + chiff, 200) * vel * 0.6


KENDANG = {
    'D': dict(f0=112, f1=84, t60=0.55, noise=0.25, nlo=60, nhi=500, amp=1.0),    # dhe (kendang ageng, bem)
    'd': dict(f0=190, f1=150, t60=0.32, noise=0.2, nlo=150, nhi=900, amp=0.8),   # dung (ciblon)
    'k': dict(f0=330, f1=300, t60=0.12, noise=0.35, nlo=800, nhi=3500, amp=0.55), # ket
    't': dict(f0=420, f1=380, t60=0.06, noise=0.9, nlo=1500, nhi=5000, amp=0.6),  # tak (tepuk)
    'h': dict(f0=150, f1=130, t60=0.10, noise=0.2, nlo=100, nhi=600, amp=0.3),    # hen (sentuhan)
    'P': dict(f0=95, f1=70, t60=0.7, noise=0.3, nlo=50, nhi=300, amp=1.1),        # bem ageng
}


def kendang(stroke: str, vel: float = 1.0) -> np.ndarray:
    p = KENDANG[stroke]
    dur = p['t60'] * 1.4 + 0.05
    n = int(dur * SR)
    t = np.arange(n) / SR
    f = p['f1'] + (p['f0'] - p['f1']) * np.exp(-t / 0.035)
    ph = np.cumsum(2 * np.pi * f / SR)
    body = (np.sin(ph) + 0.25 * np.sin(1.59 * ph + 0.5)) * env_exp(n, p['t60'])
    slap = bandnoise(n, p['nlo'], p['nhi']) * env_exp(n, 0.02) * p['noise'] * 3
    return (body + slap) * attack(n, 0.6) * p['amp'] * vel


# ------------------------------------------------------------------ reverb & mix

def make_ir(rt60: float = 1.4, pre_ms: float = 12.0, seed: int = 7) -> np.ndarray:
    """IR stereo sintetis: pantulan awal + ekor noise yang kian gelap (pendopo)."""
    rng = np.random.default_rng(seed)
    n = int((rt60 * 1.1 + 0.1) * SR)
    t = np.arange(n) / SR
    ir = np.zeros((n, 2))
    for ch in range(2):
        noise = rng.standard_normal(n)
        # peluruhan bergantung frekuensi: pecah ke dua pita
        lo = lowpass(noise, 2500) * np.exp(-6.9 * t / rt60)
        hi = highpass(noise, 2500) * np.exp(-6.9 * t / (rt60 * 0.45))
        tail = (lo + 0.5 * hi) * np.clip((t - pre_ms / 1000) / 0.03, 0, 1)
        ir[:, ch] = tail
        for _ in range(9):
            d = int(rng.uniform(pre_ms, 70) / 1000 * SR)
            ir[d, ch] += rng.uniform(0.2, 0.6) * rng.choice([-1, 1])
    ir /= np.sqrt(np.sum(ir ** 2, axis=0, keepdims=True))
    return ir


class Mixer:
    def __init__(self, seconds: float):
        self.n = int(seconds * SR)
        self.buses: dict[str, np.ndarray] = {}

    def add(self, bus: str, sig: np.ndarray, at_s: float, gain: float = 1.0, pan: float = 0.0,
            wrap: bool = False) -> None:
        """pan −1 (kiri) … +1 (kanan), hukum daya konstan. wrap=True: ekor melewati akhir dilipat ke awal."""
        if bus not in self.buses:
            self.buses[bus] = np.zeros((self.n, 2))
        buf = self.buses[bus]
        a = int(round(at_s * SR))
        th = (pan + 1) * np.pi / 4
        gl, gr = math.cos(th) * gain, math.sin(th) * gain
        if wrap:
            a %= self.n
        elif a < 0:
            sig = sig[-a:]
            a = 0
        if a >= self.n:
            return
        first = min(len(sig), self.n - a)
        buf[a:a + first, 0] += sig[:first] * gl
        buf[a:a + first, 1] += sig[:first] * gr
        rest = sig[first:]
        while wrap and len(rest):
            k = min(len(rest), self.n)
            buf[:k, 0] += rest[:k] * gl
            buf[:k, 1] += rest[:k] * gr
            rest = rest[k:]

    def render(self, sends: dict[str, float], ir: np.ndarray, bus_gain: dict[str, float] | None = None,
               loop: bool = True) -> np.ndarray:
        bus_gain = bus_gain or {}
        dry = np.zeros((self.n, 2))
        wet_in = np.zeros((self.n, 2))
        for name, buf in self.buses.items():
            g = bus_gain.get(name, 1.0)
            dry += buf * g
            wet_in += buf * g * sends.get(name, 0.2)
        wet = np.zeros((self.n, 2))
        for ch in range(2):
            if loop:
                # konvolusi melingkar: ekor reverb akhir loop masuk ke awal
                x = np.concatenate([wet_in[:, ch], wet_in[:, ch]])
                y = fftconvolve(x, ir[:, ch])[: 2 * self.n]
                wet[:, ch] = y[self.n: 2 * self.n]
            else:
                wet[:, ch] = fftconvolve(wet_in[:, ch], ir[:, ch])[: self.n]
        return dry + wet
