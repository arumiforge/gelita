"""One-off, local procedural sound production; run explicitly with --apply.

These are synthesized preview sounds, not recordings of traditional instruments.
Uses only numpy, Python wave and local ffmpeg; no network or sampled recordings.
"""
import argparse
import hashlib
import json
import math
from pathlib import Path
import subprocess
import wave

import numpy as np

ROOT = Path(__file__).resolve().parents[1]
RATE = 44100
RNG = np.random.default_rng(20261014)


def strike(freq, length, kind="metal"):
    t = np.arange(round(length * RATE)) / RATE
    if kind == "wood":
        ratios, decays, weights = [1, 2.76, 5.4], [.045, .025, .014], [1, .27, .09]
    elif kind == "gong":
        ratios, decays, weights = [1, 1.48, 2.03, 2.72, 4.1], [2.9, 2.1, 1.5, 1.2, .55], [1, .38, .27, .12, .06]
    else:
        ratios, decays, weights = [1, 2.03, 2.79, 4.11, 5.43], [.7, .4, .26, .17, .1], [1, .38, .2, .1, .04]
    y = sum(w * np.sin(2 * np.pi * freq * r * t) * np.exp(-t / d) for r, d, w in zip(ratios, decays, weights))
    y *= 1 - np.exp(-t / .003)
    # Every tail reaches zero without a hard truncation.
    n = min(len(y), round(.03 * RATE))
    y[-n:] *= np.linspace(1, 0, n)
    return y


def add(buf, start, sound, level=.1, pan=0, loop=False):
    at = round(start * RATE)
    gains = np.array([math.sqrt((1 - pan) / 2), math.sqrt((1 + pan) / 2)])
    sound = sound[:, None] * gains[None, :] * level
    if loop:
        indices = (at + np.arange(len(sound))) % len(buf)
        np.add.at(buf, indices, sound)
    else:
        end = min(len(buf), at + len(sound))
        buf[at:end] += sound[:end-at]


def music(name, bpm, beats, scale, sparse=False):
    duration = beats * 60 / bpm
    buf = np.zeros((round(duration * RATE), 2))
    melody = [0, 1, 2, 1, 3, 2, 1, 4, 2, 3, 1, 0, 2, 1, 4, 0]
    for b in range(beats):
        pos = b * 60 / bpm
        note = scale[melody[b % len(melody)]]
        if not sparse or b % 2 == 1:
            add(buf, pos, strike(note, 1.7), .10 if not sparse else .065, -.2, True)
        if not sparse and b % 2 == 0:
            add(buf, pos + .5 * 60 / bpm, strike(scale[(b//2+2) % 5]*2, .7), .035, .3, True)
        if b % 8 == 7:
            add(buf, pos, strike(scale[2]/2, 2.2), .1 if not sparse else .06, .15, True)
        if b % 16 == 0:
            add(buf, pos, strike(65, 5, "gong"), .16 if not sparse else .09, 0, True)
        if not sparse and b % 4 == 2:
            add(buf, pos, strike(145, .3, "wood"), .065, -.1, True)
    # Circular tails preserve natural decay across the loop point. A 4 ms
    # bridge removes any remaining sample jump while keeping an exact cycle.
    n = round(.004 * RATE)
    buf[-n:] = np.linspace(buf[-n], buf[0], n)
    return buf, duration


def sfx(name, duration):
    buf = np.zeros((round(duration * RATE), 2))
    if name == "click":
        add(buf, 0, strike(720, duration, "wood"), .3)
    elif name == "correct":
        for i, f in enumerate([523, 620, 784, 1046]):
            add(buf, i*.15, strike(f, duration-i*.15), .18, (i-1.5)*.15)
    elif name == "wrong":
        for i, f in enumerate([310, 233]):
            add(buf, i*.23, strike(f, .55), .18)
    elif name == "lock":
        for i in range(2):
            add(buf, i*.12, strike(360, .18, "wood"), .22)
        add(buf, .25, strike(2100, .25), .025)
    elif name == "shard":
        for i, f in enumerate([392, 465, 523, 620, 784, 930, 1046, 1240]):
            add(buf, i*.19, strike(f, 1.6), .12, math.sin(i)*.4)
        add(buf, 1.8, strike(310, 1.4), .14)
    else:
        for start, f in zip([0, .3, .68, 1.15, 1.75, 2.5], [620, 523, 465, 392, 310, 196]):
            add(buf, start, strike(f, 1.5), .16)
        add(buf, 2.8, strike(65, 2.3, "gong"), .3)
    n = min(len(buf), round(.018*RATE))
    buf[-n:] *= np.linspace(1, 0, n)[:, None]
    return buf


def sha(file):
    return hashlib.sha256(file.read_bytes()).hexdigest()


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--apply", action="store_true")
    if not parser.parse_args().apply:
        parser.error("This dated production script needs an explicit --apply; not routine maintenance.")
    manifest_file = ROOT / "provenance/nonnarration-audio-20261014.json"
    if manifest_file.exists():
        raise SystemExit("Production already recorded; refusing to overwrite. Review the manifest first.")
    snapshot = ROOT / "archive/before-catalog-update-20261014"
    snapshot.mkdir(parents=True, exist_ok=True)
    catalog_file = ROOT / "data.json"
    (snapshot / "data.json").write_bytes(catalog_file.read_bytes())
    old_hashes = {str(p.relative_to(ROOT)).replace('\\', '/'): sha(p) for p in (ROOT / 'assets').rglob('*') if p.is_file()}
    (snapshot / 'asset-hashes.json').write_text(json.dumps(old_hashes, indent=2)+'\n', encoding='utf-8')
    data = json.loads(catalog_file.read_text(encoding="utf-8"))
    manifest = {"date": "2026-10-14", "authorization": "User requested non-narration audio and catalogue update", "method": "Local deterministic additive/modal synthesis; no voices, samples, recording or AI audio service", "script": "scripts/produce-nonnarration-audio-20261014.py", "original_catalog_snapshot": "archive/before-catalog-update-20261014/data.json", "baseline": "archive/audio-baseline.json", "allowed_changed_fields": ["file", "preview", "actual", "actual_format", "format", "status", "generation_status", "generated_by", "note", "production", "duration", "audio_provenance"], "limitations": ["Preview sound design; not authentic instrument recordings", "Approximate modal pitch set; slendro/pelog authenticity not certified", "Listening and game mix validation pending"], "assets": []}
    settings = {"map": (66, 64, [261, 302, 348, 400, 459]), "region": (42, 48, [261, 279, 329, 392, 440]), "challenge": (52, 64, [233, 269, 310, 356, 409])}
    durations = {"click": .13, "correct": .9, "wrong": .9, "lock": .5, "shard": 3.2, "region-done": 5.1}
    for a in data:
        if a['cat'] not in ('Musik', 'Efek suara'):
            continue
        kind, filename = a['key'].split('/')
        name = Path(filename).stem
        if kind == 'music':
            samples, duration = music(name, *settings[name], sparse=name=='challenge')
        else:
            duration = durations[name]
            samples = sfx(name, duration)
        # Music is deliberately quieter than one-shot feedback.
        target_peak = .26 if kind == 'music' else .56
        samples *= target_peak / np.max(np.abs(samples))
        pcm = np.round(samples * 32767).astype('<i2')
        master = ROOT / f'assets/audio/masters/{kind}/{name}.wav'
        output = ROOT / f'assets/audio/{kind}/{filename}'
        master.parent.mkdir(parents=True, exist_ok=True)
        output.parent.mkdir(parents=True, exist_ok=True)
        with wave.open(str(master), 'wb') as w:
            w.setnchannels(2); w.setsampwidth(2); w.setframerate(RATE); w.writeframes(pcm.tobytes())
        subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-y', '-i', str(master), '-c:a', 'libmp3lame', '-b:a', '128k', '-ar', str(RATE), str(output)], check=True)
        path = output.relative_to(ROOT).as_posix()
        a.update(file=path, preview=None, actual=f'{duration:.3f} s Â· stereo Â· 44.1 kHz', actual_format='MP3', format='MP3 stereo 44,1 kHz 128 kbps; master WAV PCM 16-bit', status='Audio tersedia â€” pratinjau sintetis', generation_status='Sintesis lokal nonnarasi', generated_by='Sintesis modal/aditif lokal deterministik', duration=f'{duration:.3f} s', note='Dibuat atas permintaan 14 Oktober 2026. Tanpa suara manusia. Bunyi sintetis terinspirasi perkusi logam/kayu, bukan rekaman gamelan autentik. Brief komposer asli tetap disimpan pada prompt dan baseline.', production='Siap didengarkan untuk review mockup; penilaian bunyi, tuning tradisional dan mixing dalam game belum dilakukan.', audio_provenance={"manifest": "provenance/nonnarration-audio-20261014.json", "master": master.relative_to(ROOT).as_posix(), "loop": kind=='music', "date": "2026-10-14"})
        manifest['assets'].append({"key": a['key'], "file": path, "master": master.relative_to(ROOT).as_posix(), "sha256": sha(output), "master_sha256": sha(master), "duration_seconds": len(samples)/RATE, "loop": kind=='music', "sample_rate": RATE, "channels": 2, "master_sample_peak_dbfs": float(20*np.log10(np.max(np.abs(pcm.astype(float)))/32768)), "loop_boundary_sample_delta": float(np.max(np.abs(pcm[0].astype(float)-pcm[-1].astype(float)))/32768) if kind=='music' else None})
    catalog_file.write_text(json.dumps(data, ensure_ascii=False, indent=2)+'\n', encoding='utf-8')
    manifest_file.write_text(json.dumps(manifest, ensure_ascii=False, indent=2)+'\n', encoding='utf-8')
    print(json.dumps({"created": len(manifest['assets']), "narrations_unchanged": 88, "manifest": manifest_file.relative_to(ROOT).as_posix()}))


if __name__ == '__main__':
    main()
