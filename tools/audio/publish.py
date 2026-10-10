"""Kode narasi WAV (out/narasi/{id,en}/) ke MP3 permainan + manifest produksi.

python publish.py [--locale id|en]

- MP3 mono 44,1 kHz 48 kbps (CBR, header Xing untuk durasi) di
  public/assets/audio/narasi/{locale}/{kode}.mp3;
- public/assets/audio/narasi/produksi.json: sha256, ukuran, bitrate, dan
  profil suara setiap berkas, sehingga `php spark gelita:narration:import` mencatatnya sebagai
  production_method = tts. Berkas yang kemudian diganti rekaman pengisi
  suara (isi berbeda) otomatis tercatat sebagai rekaman sendiri.
"""
from __future__ import annotations

import argparse
import datetime
import hashlib
import json
import re
import subprocess

import paths

PROFILES = {
    'id': {
        'narator': 'TTS · Mimic 3 jv_ID/google-gmu_low (CC BY-SA 4.0) penutur #{speaker} · Narator',
        'jaka': 'TTS · Mimic 3 jv_ID/google-gmu_low (CC BY-SA 4.0) penutur #{speaker}, pitch & formant anak · Jaka',
        'mbah_kedu': 'TTS · Mimic 3 jv_ID/google-gmu_low (CC BY-SA 4.0) penutur #{speaker}, pitch/formant/getar lansia · Mbah Kedu',
    },
    'en': {
        'narator': 'TTS · Piper en_US-kristin-medium (domain publik) · Narator',
        'jaka': 'TTS · Piper en_GB-jenny_dioco-medium (Jenny, Dioco), pitch & formant anak · Jaka',
        'mbah_kedu': 'TTS · Piper en_US-joe-medium (CC0), pitch/formant/getar lansia · Mbah Kedu',
    },
}


# Narration bitrate. 96 kbps until October 2026; 48 kbps mono since asset package
# Stage 1b (owner decision P2 in docs/rencana-paket-aset.md, about -12 MB).
BITRATE_KBPS = 48

# LAME in CBR mode at <= 160 kbps scales the signal by 0.95 (the `scale` column of its
# preset table, -0.45 dB). Compensated so the decoded MP3 is as loud as its source
# (a -1 dBFS peak stays -1 dBFS). Applies to any source: WAV master or a decoded MP3.
LAME_CBR_SCALE = 0.95


# Highest true peak allowed in a published MP3 (the 96 kbps files peaked at -0.5 dBTP).
# At 48 kbps, coding noise on strong 8-11 kHz sibilants can overshoot the source peak.
TRUE_PEAK_CEILING = -0.5


def true_peak(mp3) -> float:
    """True peak of the decoded file in dBTP (ffmpeg ebur128, 4x oversampled)."""
    log = subprocess.run(['ffmpeg', '-hide_banner', '-nostats', '-i', str(mp3), '-af', 'ebur128=peak=true',
                          '-f', 'null', '-'], check=True, capture_output=True, text=True).stderr
    return float(re.findall(r'Peak:\s+(-?[\d.]+|-inf) dBFS', log)[-1])


def encode(src, mp3) -> tuple[float, int]:
    """Encode to MP3 under TRUE_PEAK_CEILING; returns (gain trim in dB, LAME quality) that was used.

    The overshoot of low-bitrate MP3 changes erratically with tiny gain changes, so instead of
    scaling by the measured excess this walks down in 0.1 dB steps and, at each step, tries LAME
    -q 2 ("-h") and then -q 0. The first encode under the ceiling wins; most files pass at once.
    """
    mp3.parent.mkdir(parents=True, exist_ok=True)
    for step in range(31):
        trim = -step / 10
        for quality in (2, 0):
            gain = 1 / LAME_CBR_SCALE * 10 ** (trim / 20)
            subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-y', '-i', str(src),
                            '-map_metadata', '-1', '-af', f'volume={gain:.6f}', '-ac', '1', '-ar', '44100',
                            '-codec:a', 'libmp3lame', '-b:a', f'{BITRATE_KBPS}k', '-compression_level', str(quality),
                            '-write_xing', '1', '-id3v2_version', '0', '-f', 'mp3', str(mp3)], check=True)
            if true_peak(mp3) <= TRUE_PEAK_CEILING:
                return trim, quality
    raise RuntimeError(f'{mp3}: true peak still above {TRUE_PEAK_CEILING} dBTP after trimming 3 dB')


def manifest_entry(mp3, profile: str) -> dict:
    return {'sha256': hashlib.sha256(mp3.read_bytes()).hexdigest(), 'bytes': mp3.stat().st_size,
            'bitrate_kbps': BITRATE_KBPS, 'voice_profile': profile}


def write_manifest(manifest_path, files: dict) -> None:
    manifest = {
        'keterangan': 'Rekaman narasi bawaan GELITA: suara sintetis (text-to-speech), dibangkitkan tools/audio. '
                      'Berkas yang isinya sama dengan sha256 di sini diimpor gelita:narration:import sebagai '
                      'production_method = tts; rekaman pengganti (isi berbeda) tercatat sebagai rekaman sendiri.',
        'dibuat': datetime.date.today().isoformat(),
        'files': {loc: dict(sorted(v.items())) for loc, v in sorted(files.items())},
    }
    manifest_path.write_text(json.dumps(manifest, indent=1, ensure_ascii=False) + '\n')


def main() -> None:
    from narrate import CAST  # needs the TTS venv; imported here so reencode.py runs with plain Python

    ap = argparse.ArgumentParser()
    ap.add_argument('--locale', choices=['id', 'en'])
    args = ap.parse_args()

    folder = paths.PUBLIC_AUDIO / 'narasi'
    manifest_path = folder / 'produksi.json'
    manifest = json.loads(manifest_path.read_text()) if manifest_path.exists() else {}
    files = manifest.get('files', {})

    for locale in [args.locale] if args.locale else ['id', 'en']:
        report = json.loads((paths.OUT / 'narasi' / locale / 'report.json').read_text())
        entries = files.setdefault(locale, {})
        for code, info in sorted(report.items()):
            wav = paths.OUT / 'narasi' / locale / f'{code}.wav'
            mp3 = folder / locale / f'{code}.mp3'
            encode(wav, mp3)
            who = info['character']
            profile = PROFILES[locale][who].format(**CAST[locale][who])
            entries[code] = manifest_entry(mp3, profile)
        print(f'{locale}: {len(report)} berkas → {folder / locale}')

    write_manifest(manifest_path, files)


if __name__ == '__main__':
    main()
