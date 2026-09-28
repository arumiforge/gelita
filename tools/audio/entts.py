"""Sintesis Inggris lewat suara Piper (ONNX) dan espeak-ng bawaan paket piper-tts."""
from __future__ import annotations

from pathlib import Path

from piper import PiperVoice

import paths

_cache: dict[str, PiperVoice] = {}


def load(name: str) -> PiperVoice:
    """Suara Piper di models/piper/{name}/voice.onnx (+ voice.json)."""
    if name not in _cache:
        base: Path = paths.PIPER[name]
        _cache[name] = PiperVoice.load(str(base / 'voice.onnx'), config_path=str(base / 'voice.json'))
    return _cache[name]
