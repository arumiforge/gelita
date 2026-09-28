"""Kode narasi WAV (out/narasi/{id,en}/) ke MP3 permainan + manifest produksi.

python publish.py [--locale id|en]

- MP3 mono 44,1 kHz 96 kbps (CBR, header Xing untuk durasi) di
  public/assets/audio/narasi/{locale}/{kode}.mp3;
- public/assets/audio/narasi/produksi.json: sha256 + profil suara setiap
  berkas, sehingga `php spark gelita:narration:import` mencatatnya sebagai
  production_method = tts. Berkas yang kemudian diganti rekaman pengisi
  suara (isi berbeda) otomatis tercatat sebagai rekaman sendiri.
"""
from __future__ import annotations

import argparse
import datetime
import hashlib
import json
import subprocess

import paths
from narrate import CAST

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


# LAME mode CBR ≤ 160 kbps mengalikan sinyal 0,95× (kolom `scale` tabel presetnya, −0,45 dB).
# Dikompensasi agar MP3 yang didekode sama keras dengan WAV-nya (puncak −1 dBFS tetap −1 dBFS).
LAME_CBR_SCALE = 0.95


def encode(wav, mp3) -> None:
    mp3.parent.mkdir(parents=True, exist_ok=True)
    subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-y', '-i', str(wav), '-map_metadata', '-1',
                    '-af', f'volume={1 / LAME_CBR_SCALE:.6f}', '-ac', '1', '-ar', '44100', '-codec:a', 'libmp3lame',
                    '-b:a', '96k', '-write_xing', '1', '-id3v2_version', '0', str(mp3)], check=True)


def main() -> None:
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
            entries[code] = {'sha256': hashlib.sha256(mp3.read_bytes()).hexdigest(), 'voice_profile': profile}
        print(f'{locale}: {len(report)} berkas → {folder / locale}')

    manifest = {
        'keterangan': 'Rekaman narasi bawaan GELITA: suara sintetis (text-to-speech), dibangkitkan tools/audio. '
                      'Berkas yang isinya sama dengan sha256 di sini diimpor gelita:narration:import sebagai '
                      'production_method = tts; rekaman pengganti (isi berbeda) tercatat sebagai rekaman sendiri.',
        'dibuat': datetime.date.today().isoformat(),
        'files': {loc: dict(sorted(v.items())) for loc, v in sorted(files.items())},
    }
    manifest_path.write_text(json.dumps(manifest, indent=1, ensure_ascii=False) + '\n')


if __name__ == '__main__':
    main()
