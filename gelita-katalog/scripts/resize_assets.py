"""Resize existing WebP assets to data.json targets; no image generation.

Preview: python scripts/resize_assets.py
Apply:   python scripts/resize_assets.py --apply
Requires Pillow. Originals are backed up before any asset is replaced.
"""
import argparse
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime, timezone
import json
from pathlib import Path
import re
import subprocess
import sys
import zipfile

from PIL import Image, ImageOps

ROOT = Path(__file__).resolve().parent.parent


def inspect(data):
    entries = {}
    for asset in data:
        if asset['size'] == 'Audio':
            continue
        for field in ('file', 'preview', 'generated_asset'):
            relative = asset.get(field)
            if not relative or Path(relative).suffix.lower() != '.webp':
                continue
            path = (ROOT / relative).resolve()
            if not path.is_relative_to(ROOT / 'assets'):
                raise ValueError(f'Asset outside assets/: {relative}')
            match = re.fullmatch(r'\s*(\d+)\s*[×x]\s*(\d+)\s*', asset['size'])
            if not match:
                raise ValueError(f'Invalid target: {asset["key"]}')
            target = tuple(map(int, match.groups()))
            if min(target) < 1:
                raise ValueError(f'Invalid dimensions: {target}')
            if relative in entries:
                if entries[relative]['target'] != list(target):
                    raise ValueError(f'Conflicting targets: {relative}')
                continue
            with Image.open(path) as image:
                image.load()
                if getattr(image, 'n_frames', 1) != 1:
                    raise ValueError(f'Animated asset requires separate handling: {relative}')
                alpha = 'A' in image.getbands() and image.getchannel('A').getextrema()[0] < 255
                same_ratio = image.width * target[1] == image.height * target[0]
                mode = 'resize' if same_ratio else ('contain-transparent' if alpha else 'cover-center')
                entries[relative] = dict(file=relative, before=list(image.size), target=list(target),
                                         method=mode, alpha=alpha, changed=image.size != target)
    return list(entries.values())


def resize(entry):
    path = ROOT / entry['file']
    target = tuple(entry['target'])
    temporary = path.with_name(path.name + '.resize-tmp')
    try:
        with Image.open(path) as source:
            image = source.convert('RGBA' if entry['alpha'] else 'RGB')
            if entry['method'] == 'contain-transparent':
                fitted = ImageOps.contain(image, target, Image.Resampling.LANCZOS)
                output = Image.new('RGBA', target, (0, 0, 0, 0))
                output.paste(fitted, ((target[0] - fitted.width) // 2, (target[1] - fitted.height) // 2))
            elif entry['method'] == 'cover-center':
                output = ImageOps.fit(image, target, Image.Resampling.LANCZOS, centering=(0.5, 0.5))
            else:
                output = image.resize(target, Image.Resampling.LANCZOS)
            options = {'icc_profile': source.info['icc_profile']} if source.info.get('icc_profile') else {}
            output.save(temporary, 'WEBP', lossless=True, method=4, **options)
        with Image.open(temporary) as check:
            check.load()
            if check.size != target or check.format != 'WEBP':
                raise ValueError(f'Invalid output: {path}')
            if entry['alpha'] and ('A' not in check.getbands() or check.getchannel('A').getextrema()[0] == 255):
                raise ValueError(f'Transparency lost: {path}')
        temporary.replace(path)
    finally:
        temporary.unlink(missing_ok=True)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--apply', action='store_true', help='Back up and resize mismatched assets in place')
    args = parser.parse_args()
    data = json.loads((ROOT / 'data.json').read_text(encoding='utf8'))
    entries = inspect(data)
    changes = [entry for entry in entries if entry['changed']]
    print(json.dumps({'raster_assets': len(entries), 'need_resize': len(changes), 'apply': args.apply}), flush=True)
    if not args.apply or not changes:
        return
    stamp = datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%S%fZ')
    backup = ROOT / 'archive' / f'before-resize-{stamp}.zip'
    with zipfile.ZipFile(backup, 'x', compression=zipfile.ZIP_STORED) as archive:
        archive.write(ROOT / 'data.json', 'data.json')
        for entry in changes:
            archive.write(ROOT / entry['file'], entry['file'])
    with zipfile.ZipFile(backup) as archive:
        if archive.testzip() is not None:
            raise ValueError('Backup verification failed')
    print(f'Backup verified: {backup.name}', flush=True)
    with ThreadPoolExecutor(max_workers=4) as pool:
        for index, _ in enumerate(pool.map(resize, changes), 1):
            if index % 25 == 0 or index == len(changes):
                print(f'Resized {index}/{len(changes)}', flush=True)
    remaining = [entry for entry in inspect(data) if entry['changed']]
    if remaining:
        raise ValueError(f'Dimensions still mismatch: {remaining}')
    subprocess.run([sys.executable, str(ROOT / 'scripts/measure_assets.py')], check=True)
    updated = json.loads((ROOT / 'data.json').read_text(encoding='utf8'))
    if [a for a in updated if a['size'] == 'Audio'] != [a for a in data if a['size'] == 'Audio']:
        raise ValueError('Audio changed')
    report = dict(timestamp_utc=stamp, backup=backup.relative_to(ROOT).as_posix(),
                  raster_assets=len(entries), resized=len(changes), already_correct=len(entries)-len(changes),
                  mismatches_after=0, entries=entries)
    (ROOT / 'reports/resize-report.json').write_text(json.dumps(report, ensure_ascii=False, indent=2)+'\n', encoding='utf8')
    subprocess.run(['node', str(ROOT / 'build.mjs')], check=True)
    subprocess.run(['node', str(ROOT / 'scripts/validate.mjs')], check=True)
    print('Resize, metadata, build, and validation completed.', flush=True)


if __name__ == '__main__':
    main()
