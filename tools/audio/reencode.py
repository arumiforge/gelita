"""Re-encode the bundled narration MP3s to the current publish bitrate (asset package Stage 1b).

python reencode.py [--locale id|en] [--dry-run]

The WAV masters (out/narasi/) are not committed, so this script transcodes the
96 kbps MP3s in public/assets/audio/narasi/{id,en}/ in place to the settings of
publish.py (MP3 mono 44.1 kHz, BITRATE CBR, Xing header) and rewrites the
sha256, byte size, and bitrate of each file in produksi.json. File names and
paths stay the same. Voice profiles are kept as they are.

Files that are already at the target bitrate are skipped, so running it twice
does not stack a second lossy generation. When the masters are regenerated
with narrate.py, use publish.py instead: it encodes from WAV with the same
settings.
"""
from __future__ import annotations

import argparse
import json
import subprocess
from pathlib import Path

import paths
from publish import BITRATE_KBPS, TRUE_PEAK_CEILING, encode, manifest_entry, write_manifest


def bitrate_kbps(mp3: Path) -> int:
    out = subprocess.run(['ffprobe', '-v', 'error', '-select_streams', 'a:0', '-show_entries', 'stream=bit_rate',
                          '-of', 'csv=p=0', str(mp3)], check=True, capture_output=True, text=True).stdout
    return round(int(out.strip()) / 1000)


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument('--locale', choices=['id', 'en'])
    ap.add_argument('--dry-run', action='store_true', help='only report what would be re-encoded')
    args = ap.parse_args()

    folder = paths.PUBLIC_AUDIO / 'narasi'
    manifest = json.loads((folder / 'produksi.json').read_text())
    files = manifest['files']

    for locale in [args.locale] if args.locale else ['id', 'en']:
        before = after = done = 0
        for mp3 in sorted((folder / locale).glob('*.mp3')):
            code = mp3.stem
            entry = files.get(locale, {}).get(code)
            if entry is None:
                raise SystemExit(f'{locale}/{code}: not in produksi.json (own recording?); refusing to re-encode it')
            size = mp3.stat().st_size
            before += size
            if bitrate_kbps(mp3) == BITRATE_KBPS:
                after += size
                files[locale][code] = manifest_entry(mp3, entry['voice_profile'])
                continue
            if args.dry_run:
                print(f'{locale}/{code}: {size} B → re-encode')
                continue
            tmp = mp3.with_suffix('.tmp.mp3')
            trim, quality = encode(mp3, tmp)
            tmp.replace(mp3)
            if trim or quality != 2:
                print(f'{locale}/{code}: gain {trim:+.1f} dB, LAME -q {quality} (true peak <= {TRUE_PEAK_CEILING} dBTP)')
            after += mp3.stat().st_size
            files[locale][code] = manifest_entry(mp3, entry['voice_profile'])
            done += 1
        print(f'{locale}: {done} re-encoded, {before} B → {after} B')

    if not args.dry_run:
        write_manifest(folder / 'produksi.json', files)


if __name__ == '__main__':
    main()
