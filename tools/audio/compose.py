"""Komposisi gending orisinal untuk GELITA dan garap otomatisnya.

Balungan ditulis per gatra (4 ketukan). Instrumen garap mengikuti aturan
sederhana gamelan Jawa: semua pola "jatuh" ke nada seleh di ketukan kuat,
pola bonang/peking mendahului balungan, kenong–kempul–gong menandai struktur.
"""
from __future__ import annotations

import numpy as np

import gamelan as G
from gamelan import SR, Laras, Mixer, parse_note

RNG = np.random.default_rng(7)


def jitter(ms: float = 7.0) -> float:
    return float(RNG.normal(0, ms / 1000))


def vel(base: float, spread: float = 0.08) -> float:
    return float(base * (1 + RNG.normal(0, spread)))


def parse_balungan(text: str) -> list[tuple[int, int] | None]:
    notes = []
    for gatra in text.split('|'):
        toks = gatra.split()
        if not toks:
            continue
        assert len(toks) == 4, f'gatra harus 4 ketukan: {gatra!r}'
        notes += [parse_note(t) for t in toks]
    return notes


def filled(notes: list) -> list:
    """Isi ketukan kosong (pin) dengan nada berikutnya — acuan untuk garap."""
    out = list(notes)
    nxt = None
    for i in range(len(out) - 1, -1, -1):
        if out[i] is None:
            out[i] = nxt
        else:
            nxt = out[i]
    # pin di akhir siklus → nada pertama siklus (melingkar)
    first = next(n for n in out if n is not None)
    return [n if n is not None else first for n in out]


def scale_steps(laras: Laras) -> list[int]:
    return [1, 2, 3, 5, 6]


def note_index(note: tuple[int, int], steps: list[int]) -> int:
    n, o = note
    return o * len(steps) + steps.index(n)


def index_note(idx: int, steps: list[int]) -> tuple[int, int]:
    o, k = divmod(idx, len(steps))
    return steps[k], o


class Piece:
    def __init__(self, laras: Laras, bpm: float, balungan: str, form: str, name: str):
        self.laras = laras
        self.beat = 60.0 / bpm
        self.notes = parse_balungan(balungan)
        self.full = filled(self.notes)
        self.n_beats = len(self.notes)
        self.form = form            # 'ladrang' | 'ketawang' | 'lancaran'
        self.name = name
        self.seconds = self.n_beats * self.beat
        self.mix = Mixer(self.seconds)
        self.steps = scale_steps(laras)
        self.cycle = {'ladrang': 32, 'ketawang': 16, 'lancaran': 16}[form]
        self.detune_inst: dict = {}

    # waktu ketukan i (0-based); ketukan ke-i berbunyi di akhir slot i
    def t(self, beat_index: float) -> float:
        return (beat_index + 1) * self.beat

    def hz(self, note, octave_shift: int = 0, inst: str | None = None) -> float:
        """Frekuensi nada; `inst` memberi selisih penalaan kecil per instrumen (ombak antarricikan)."""
        n, o = note
        f = self.laras.hz(n, o + octave_shift)
        if inst:
            key = (inst, n, o + octave_shift)
            if key not in self.detune_inst:
                self.detune_inst[key] = float(RNG.normal(0, 3.5))
            f *= 2 ** (self.detune_inst[key] / 1200)
        return f

    def add(self, bus, sig, at, gain=1.0, pan=0.0):
        self.mix.add(bus, sig, at, gain=gain, pan=pan, wrap=True)

    # ------------------------------------------------------------ struktur
    def structure(self, kenong_gain=0.55, kempul_gain=0.65, gong_gain=0.95, kethuk_gain=0.30,
                  gong_hz=41.0, kethuk_note=(2, -1)):
        c = self.cycle
        for i in range(self.n_beats):
            pos = i % c + 1                       # 1..cycle
            note = self.full[i]
            if self.form == 'ladrang':
                is_gong, is_kenong = pos == c, pos % 8 == 0
                is_kempul = pos in (12, 20, 28)
                is_kethuk = pos % 4 == 2
            elif self.form == 'ketawang':
                is_gong, is_kenong = pos == c, pos % 8 == 0
                is_kempul = pos == 12
                is_kethuk = pos % 4 == 2
            else:  # lancaran
                is_gong, is_kenong = pos == c, pos % 4 == 0
                is_kempul = pos in (6, 10, 14)
                is_kethuk = pos % 2 == 1
            at = self.t(i)
            if is_kenong:
                self.add('kenong', G.kenong(self.hz(note, -1, 'kenong'), vel(1)), at + jitter(5), kenong_gain, pan=0.45)
            if is_kempul:
                self.add('kempul', G.kempul(self.hz(note, -2, 'kempul'), vel(1)), at + jitter(5), kempul_gain, pan=-0.45)
            if is_gong:
                self.add('gong', G.gong_ageng(gong_hz, vel(1, 0.03)), at + 0.02, gong_gain, pan=-0.15)
            if is_kethuk:
                self.add('kethuk', G.kethuk(self.laras.hz(*kethuk_note), vel(1)), at + jitter(6), kethuk_gain, pan=0.25)

    # ------------------------------------------------------------ balungan
    def balungan(self, inst='saron', kind='barung', octave=0, gain=0.5, pan=0.0, damp=True, player: str = ''):
        prev_end = None
        for i, note in enumerate(self.notes):
            if note is None:
                continue
            at = self.t(i) + jitter(6)
            # redam saat nada berikutnya dipukul
            nxt = next((j for j in range(i + 1, self.n_beats) if self.notes[j] is not None), None)
            hold = ((nxt - i) * self.beat) if (damp and nxt is not None) else None
            f = self.hz(note, octave, inst=inst + kind + player)
            if inst == 'saron':
                sig = G.saron(f, vel(1), damp=hold, kind=kind)
            else:
                sig = G.slenthem(f, vel(1), damp=hold)
            self.add(inst + kind, sig, at + (0.012 if player else 0.0), gain, pan)
            prev_end = at
        return prev_end

    def peking(self, gain=0.3, pan=0.35, per_beat=2):
        """Nacah: tiap nada balungan dipukul dua kali, mendahului ketukannya."""
        for i in range(self.n_beats):
            note = self.full[i]
            for k in range(per_beat):
                at = self.t(i) - self.beat + self.beat * (k + 1) / per_beat + jitter(5)
                accent = 1.0 if k == per_beat - 1 else 0.8
                sig = G.saron(self.hz(note, 1, 'peking'), vel(accent), damp=self.beat / per_beat * 0.95, kind='peking')
                self.add('peking', sig, at, gain, pan)

    def bonang(self, kind='barung', gain=0.4, pan=-0.2, density=2, gembyang_every=4):
        """Mipil: pasangan nada (a b a b) di tiap setengah gatra, jatuh ke nada ketukan genap."""
        octave = 0 if kind == 'barung' else 1
        sub = self.beat / density
        for g in range(self.n_beats // 4):
            beats = self.full[g * 4:(g + 1) * 4]
            for half in range(2):
                a, b = beats[half * 2], beats[half * 2 + 1]
                start = self.t(g * 4 + half * 2) - self.beat      # awal slot dua ketukan
                count = 2 * density
                pattern = [a if (k % 2 == 0) else b for k in range(count)]
                last_gatra = (g % gembyang_every == gembyang_every - 1) and half == 1
                for k, note in enumerate(pattern):
                    at = start + sub * (k + 1) + jitter(6)
                    if last_gatra and k >= count - 2:
                        continue
                    accent = 1.0 if (k + 1) % 2 == 0 else 0.82
                    self.add('bonang' + kind, G.bonang(self.hz(note, octave, 'bonang' + kind), vel(accent), kind=kind), at, gain, pan)
                if last_gatra:
                    # gembyang: seleh dipukul dua oktaf bersamaan
                    at = self.t(g * 4 + 3) + jitter(4)
                    for o in (octave, octave + 1):
                        self.add('bonang' + kind, G.bonang(self.hz(beats[3], o, 'bonang' + kind), vel(0.9), kind=kind), at, gain * 0.8, pan)

    def gender(self, gain=0.35, pan=0.2, density=4, octave=0, seed=1):
        """Cengkok sederhana: alur naik/turun menuju seleh tiap dua ketukan, tangan kiri menyangga seleh."""
        rng = np.random.default_rng(seed)
        sub = self.beat / density
        steps = self.steps
        for g in range(self.n_beats // 2):
            i0 = g * 2
            target = self.full[i0 + 1]
            start_note = self.full[i0 - 1] if i0 > 0 else self.full[-1]
            ti = note_index((target[0], target[1] + octave), steps)
            si = note_index((start_note[0], start_note[1] + octave), steps)
            count = 2 * density
            # jalur: mulai dekat nada sebelumnya, berkelok, berakhir di target
            path = []
            cur = si
            for k in range(count):
                remaining = count - 1 - k
                gap = ti - cur
                if remaining == 0:
                    cur = ti
                elif abs(gap) > remaining:
                    cur += int(np.sign(gap))
                else:
                    cur += int(rng.choice([-1, 1, 0, 1, -1])) if remaining > 2 else int(np.sign(gap)) if gap else int(rng.choice([-1, 1]))
                path.append(cur)
            for k, idx in enumerate(path):
                at = self.t(i0 - 1) + sub * (k + 1) + jitter(8)
                note = index_note(idx, steps)
                accent = 1.0 if (k + 1) % density == 0 else 0.72
                sig = G.gender(self.laras.hz(note[0], note[1]), vel(accent), damp=sub * 1.8)
                self.add('gender', sig, at, gain, pan)
            # tangan kiri: seleh satu oktaf di bawah, dibiarkan berdengung
            at = self.t(i0 + 1) + jitter(6)
            sig = G.gender(self.hz(target, octave - 1, 'gender'), vel(0.85), damp=self.beat * 2 * 0.95)
            self.add('gender', sig, at, gain * 0.8, pan - 0.1)

    def gambang(self, gain=0.25, pan=-0.35, density=4, seed=2):
        """Gambang: larik cepat berganda oktaf yang selalu kembali ke nada tiap ketukan."""
        rng = np.random.default_rng(seed)
        sub = self.beat / density
        steps = self.steps
        for i in range(self.n_beats):
            target = self.full[i]
            prev = self.full[i - 1]
            ti = note_index(target, steps)
            cur = note_index(prev, steps)
            for k in range(density):
                remaining = density - 1 - k
                if remaining == 0:
                    cur = ti
                else:
                    gap = ti - cur
                    move = int(np.sign(gap)) if abs(gap) > remaining else int(rng.choice([-1, 1]))
                    cur += move
                at = self.t(i - 1) + sub * (k + 1) + jitter(5)
                n, o = index_note(cur, steps)
                acc = 1.0 if k == density - 1 else 0.75
                for oc in (0, 1):
                    self.add('gambang', G.gambang(self.laras.hz(n, o + oc), vel(acc)), at, gain * (0.8 if oc else 1), pan)

    def siter(self, gain=0.22, pan=0.4, density=2):
        sub = self.beat / density
        for i in range(self.n_beats):
            a, b = self.full[i - 1], self.full[i]
            for k in range(density):
                note = b if k % 2 == 1 else a
                at = self.t(i - 1) + sub * (k + 1) + jitter(6)
                self.add('siter', G.siter(self.hz(note, 1, 'siter'), vel(0.9)), at, gain, pan)

    def kendang(self, patterns: dict[str, str], gain=0.5, pan=0.0):
        """patterns: 'basic' / 'pre_gong' / 'gong' → 8 slot per gatra (setengah ketukan)."""
        n_gatra = self.n_beats // 4
        per_cycle = self.cycle // 4
        for g in range(n_gatra):
            pos = g % per_cycle
            key = 'pre_gong' if pos == per_cycle - 1 else ('after_gong' if pos == 0 and 'after_gong' in patterns else 'basic')
            pat = patterns[key].replace(' ', '')
            slot = self.beat * 4 / len(pat)
            for k, ch in enumerate(pat):
                if ch == '.':
                    continue
                at = self.t(g * 4 - 1) + slot * (k + 1) + jitter(6)
                self.add('kendang', G.kendang(ch, vel(1, 0.1)), at, gain, pan)

    def suling(self, phrases: list[tuple[float, list[tuple[str, float]]]], gain=0.3, pan=0.1):
        """phrases: (ketukan_mulai, [(nada, durasi_ketukan), …])."""
        for start_beat, seq in phrases:
            t0 = self.t(start_beat)
            notes = []
            cur = 0.0
            for tok, dur in seq:
                note = parse_note(tok)
                if note is None:
                    cur += dur * self.beat
                    continue
                notes.append((self.hz(note, 0), cur, dur * self.beat))
                cur += dur * self.beat
            self.add('suling', G.suling_phrase(notes, vel(1, 0.05)), t0, gain, pan)
