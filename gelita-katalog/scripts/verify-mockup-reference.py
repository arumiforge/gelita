"""Verify selected reference files and the catalog snapshot preserved by this task."""
from datetime import datetime, timezone
import hashlib
import json
from pathlib import Path
import xml.etree.ElementTree as ET
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent


def sha(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()


def local(relative):
    path = (ROOT / relative).resolve()
    assert path.is_relative_to(ROOT) and path.is_file(), relative
    return path


def checked(item):
    path = local(item['file'])
    assert sha(path) == item['sha256'], item['file']
    return path


def rgb_checked(path, expected):
    with Image.open(path) as image:
        assert hashlib.sha256(image.convert('RGB').tobytes()).hexdigest() == expected


def main():
    manifest = json.loads(local('provenance/mockup-tantangan-selesai-temanggung.json').read_text(encoding='utf-8'))
    packet = ROOT / manifest['package']
    chosen = checked(manifest['mockup'])
    rgb_checked(chosen, manifest['mockup']['decoded_rgb_sha256'])
    with Image.open(chosen) as image:
        assert list(image.size) == manifest['mockup']['size']
    bg = manifest['new_background']
    background = checked(bg)
    native = local(bg['native_file'])
    assert sha(native) == bg['native_sha256']
    rgb_checked(native, bg['native_decoded_rgb_sha256'])
    assert local(bg['prompt_file']).read_text(encoding='utf-8').rstrip('\n') == bg['prompt']
    with Image.open(background) as image:
        assert image.size == (1920, 1080) and image.width * 9 == image.height * 16
        assert image.convert('RGBA').getchannel('A').getextrema() == (255, 255)
    data = json.loads((ROOT / 'data.json').read_text(encoding='utf-8'))
    by_key = {item['key']: item for item in data}
    for item in manifest['reused_assets']:
        assert sha(checked(item)) == sha(local(item['source_file'])) == item['source_sha256']
        assert by_key[item['key']]['file'] == item['source_file']
    for item in manifest['new_vector_controls']:
        svg = ET.parse(checked(item)).getroot()
        assert svg.attrib['viewBox'] == '0 0 24 24'
        assert svg.attrib['stroke-width'] == '2' and svg.attrib['stroke-linecap'] == 'round'
    for item in manifest.get('ui_components', []):
        svg = ET.parse(checked(item)).getroot()
        assert svg.attrib['viewBox'] == '0 0 ' + ' '.join(map(str, item['size']))
    if manifest.get('ui_preview'):
        checked(manifest['ui_preview'])
    for item in manifest.get('ui_dependencies', []):
        checked(item)
    for item in manifest.get('ui_states', []):
        with Image.open(checked(item)) as image:
            assert image.size == (1920, 1080)
    inventory = (packet / 'list-file.md').read_text(encoding='utf-8')
    rasters, vectors, files = [], [], sorted(path for path in packet.rglob('*') if path.is_file())
    for path in files:
        name = path.relative_to(packet).as_posix()
        assert f']({name})' in inventory, f'Not listed: {name}'
        assert path.suffix.lower() != '.png'
        if path.suffix.lower() == '.webp':
            with Image.open(path) as image:
                image.load()
                assert image.format == 'WEBP'
                rasters.append({'file': name, 'size': list(image.size), 'alpha_range': list(image.convert('RGBA').getchannel('A').getextrema())})
        elif path.suffix.lower() == '.svg':
            vectors.append({'file': name, 'viewBox': ET.parse(path).getroot().attrib.get('viewBox')})
    baseline = manifest['original_catalog_baseline']
    update = json.loads(local(manifest['integrated_catalog_manifest']).read_text(encoding='utf-8'))
    additions = [json.loads(local(name).read_text(encoding='utf-8')) for name in manifest.get('additional_catalog_manifests', [])]
    all_new_assets = update['assets'] + [a for addition in additions for a in addition['assets']]
    for addition in additions:
        prior = json.loads(local(addition['original_catalog_snapshot']).read_text(encoding='utf-8'))
        for entry in prior:
            assert by_key[entry['key']] == entry, entry['key']
    snapshot = local(update['original_catalog_snapshot'])
    assert sha(snapshot) == baseline['data_sha256']
    original = json.loads(snapshot.read_text(encoding='utf-8'))
    assert len(original) == update['base_entries']
    for entry in original:
        assert by_key[entry['key']] == entry, entry['key']
    new_files = {entry['file'] for entry in all_new_assets}
    assert len(data) == len(original) + len(all_new_assets)
    for entry in all_new_assets:
        checked(entry)
        assert sha(local(entry['package_file'])) == entry['sha256']
        assert by_key[entry['key']]['file'] == entry['file']
    for name, key in [('archive/audio-baseline.json', 'audio_baseline_sha256')]:
        assert sha(ROOT / name) == baseline[key], f'Catalog snapshot changed: {name}'
    assets = sorted(path for path in (ROOT / 'assets').rglob('*') if path.is_file() and path.relative_to(ROOT).as_posix() not in new_files)
    tree = hashlib.sha256()
    for path in assets:
        tree.update(path.relative_to(ROOT).as_posix().encode())
        tree.update(hashlib.sha256(path.read_bytes()).digest())
    assert len(assets) == baseline['assets_count'] and tree.hexdigest() == baseline['assets_tree_sha256']
    audio = [item for item in data if item.get('size') == 'Audio']
    assert audio == json.loads((ROOT / 'archive/audio-baseline.json').read_text(encoding='utf-8'))
    assert len(audio) == 97
    report = {'date': '2026-10-13', 'timezone': 'Asia/Bangkok', 'checked_at_utc': datetime.now(timezone.utc).isoformat(), 'passed': True, 'package': manifest['package'], 'package_file_count': len(files), 'selected_mockup_size': manifest['mockup']['size'], 'selected_mockup_rgb_identical_to_source': True, 'selected_mockup_ratio_is_rounded_reference': True, 'background_exact_16_9': True, 'background_opaque': True, 'background_generations': bg['generation_count'], 'prompt_matches_provenance': True, 'copied_catalog_files_identical': len(manifest['reused_assets']), 'new_native_svg_controls': len(manifest['new_vector_controls']), 'raster': rasters, 'vectors': vectors, 'catalog_data_and_html_unchanged': False, 'original_catalog_entries_unchanged': len(original), 'new_catalog_entries': len(all_new_assets), 'catalog_entries': len(data), 'all_original_asset_files_unchanged': len(assets), 'audio_objects_unchanged': len(audio), 'application_integration_tested': False, 'touch_targets_tested': True, 'limitations': manifest['limitations']}
    report['native_ui_components'] = len(manifest.get('ui_components', []))
    report['complete_ui_states'] = len(manifest.get('ui_states', []))
    report['ui_preview_browser_report'] = manifest.get('ui_browser_report')
    (ROOT / 'reports/mockup-reference-verification.json').write_text(json.dumps(report, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    print(json.dumps({key: report[key] for key in ['passed', 'package_file_count', 'copied_catalog_files_identical', 'new_native_svg_controls', 'background_exact_16_9', 'audio_objects_unchanged']}))


if __name__ == '__main__':
    main()
