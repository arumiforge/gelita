"""Sintesis Indonesia lewat model VITS Jawa Mimic3 (jv_ID/google-gmu_low).

Model dilatih dengan fonem Epitran jav-Latn. Teks Indonesia dipetakan ke
inventaris fonem model itu oleh id_g2p.py; berkas ini hanya urusan id & ONNX.
"""
from __future__ import annotations

import itertools
from pathlib import Path

import numpy as np
import onnxruntime as ort

import paths

MODEL_DIR = paths.JV_MODEL
SAMPLE_RATE = 22050


class JvVoice:
    def __init__(self, model_dir: Path = MODEL_DIR):
        opts = ort.SessionOptions()
        opts.intra_op_num_threads = 4
        self.session = ort.InferenceSession(str(model_dir / 'generator.onnx'), sess_options=opts,
                                            providers=['CPUExecutionProvider'])
        self.phoneme_to_id: dict[str, int] = {}
        for line in (model_dir / 'phonemes.txt').read_text(encoding='utf-8').splitlines():
            idx, sym = line.split(' ', 1)
            self.phoneme_to_id[sym] = int(idx)
        self.speakers = [s.strip() for s in (model_dir / 'speakers.txt').read_text().splitlines() if s.strip()]

    def ids(self, words: list[list[str]]) -> list[int]:
        """phonemes2ids: bos, blank, [p _ p _ … # _] per kata, eos (tokens_and_words)."""
        p2i = self.phoneme_to_id
        blank, blank_word = p2i['_'], p2i['#']
        out: list[list[int]] = [[p2i['^']], [blank]]
        for word in words:
            wids = []
            for ph in word:
                if ph not in p2i:
                    raise KeyError(f'fonem tidak dikenal model: {ph!r} dalam {word}')
                wids.append(p2i[ph])
            if not wids:
                continue
            wids.append(blank_word)  # blank_at_end = True → juga untuk kata terakhir
            out.append(list(itertools.chain.from_iterable(zip(wids, itertools.repeat(blank, len(wids))))))
        out.append([p2i['$']])
        return list(itertools.chain.from_iterable(out))

    def synth_words(self, words: list[list[str]], speaker: int, length_scale: float = 1.0,
                    noise_scale: float = 0.333, noise_w: float = 0.333) -> np.ndarray:
        ids = self.ids(words)
        audio = self.session.run(None, {
            'input': np.array([ids], dtype=np.int64),
            'input_lengths': np.array([len(ids)], dtype=np.int64),
            'scales': np.array([noise_scale, length_scale, noise_w], dtype=np.float32),
            'sid': np.array([speaker], dtype=np.int64),
        })[0].squeeze()
        return audio.astype(np.float32)
