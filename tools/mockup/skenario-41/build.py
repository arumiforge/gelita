"""Builds the clickable Skenario Digital mockup (plan 41, step A) as one self-contained HTML file.

Usage: python3 tools/mockup/skenario-41/build.py OUT.html  (plain Python 3, no packages)
The page is a design check, never served by the game. scenarios.json holds two scenarios copied from
docs/bank-soal/data/ (tmg-5-b01, and wnb-5-a04, the longest one); the plates are stand-ins from the
document 40 plates U-01/U-02 until the SD- plates of plan 41 section 5 exist.
"""
import base64, json, pathlib, sys

REPO = pathlib.Path(__file__).resolve().parents[3]
HERE = pathlib.Path(__file__).parent
OUT = pathlib.Path(sys.argv[1])


def uri(path, mime=None):
    p = REPO / path
    mime = mime or {'webp': 'image/webp', 'png': 'image/png', 'woff2': 'font/woff2'}[p.suffix[1:]]
    return f'data:{mime};base64,' + base64.b64encode(p.read_bytes()).decode()


G = 'ai-studio/game-images/assets/'


def plate(colour):
    # Waiting in chatgpt-images/out/ until step C of plan 41 installs it in public/assets/ui/
    for path in (f'public/assets/ui/ui-plate-{colour}.webp', f'ai-studio/chatgpt-images/out/ui-plate-{colour}.webp'):
        if (REPO / path).is_file():
            return path
    raise SystemExit(f'ui-plate-{colour}.webp not found')


img = {
    'bg_temanggung': uri(G + 'latar/bg.temanggung.region.webp'),
    'bg_wonosobo': uri(G + 'latar/bg.wonosobo.region.webp'),
    'logo': uri(G + 'logo/ui.logo-hero.webp'),
    'badge': uri('public/assets/ui/challenge-badge.webp'),
    'clock': uri('public/assets/ui/round-clock.webp'),
    'right': uri('public/assets/ui/round-right.webp'),
    'sound': uri('public/assets/ui/round-sound.webp'),
    'logout': uri('public/assets/ui/round-logout.webp'),
    'play': uri('public/assets/ui/round-play.webp'),
    'next': uri('public/assets/ui/round-next.webp'),
    'plate_back': uri('public/assets/ui/plate-back.webp'),
    'plate_next': uri('public/assets/ui/plate-next.webp'),
    'pause': uri('public/assets/ui/round-pause.webp'),
    'gold': uri(plate('gold')),
    'navy': uri(plate('navy')),
    'pic_tmg-5-b01': uri(G.replace('assets/', '') + 'mockup/gambar-mockup/skenario/aset/item.skenario.tmg-5-b01.webp'),
    'pic_wnb-5-a04': uri(G.replace('assets/', '') + 'mockup/gambar-mockup/skenario/aset/item.skenario.wnb-5-a04.webp'),
}
fonts = {
    'marcellus': uri('public/assets/fonts/marcellus-latin-400-normal.woff2'),
    'jakarta': uri('public/assets/fonts/plus-jakarta-sans-latin-400-normal.woff2'),
    'jakarta6': uri('public/assets/fonts/plus-jakarta-sans-latin-600-normal.woff2'),
    'jakarta7': uri('public/assets/fonts/plus-jakarta-sans-latin-700-normal.woff2'),
}
scen = json.loads((HERE / 'scenarios.json').read_text())
html = (HERE / 'template.html').read_text()
html = html.replace('/*IMG*/{}', json.dumps(img)).replace('/*SCEN*/{}', json.dumps(scen, ensure_ascii=False))
for k, v in fonts.items():
    html = html.replace('{{font_' + k + '}}', v)
html = html.replace('{{gold}}', img['gold']).replace('{{navy}}', img['navy']).replace('{{plate_back}}', img['plate_back']).replace('{{plate_next}}', img['plate_next']).replace('{{badge}}', img['badge'])
OUT.write_text(html)
print(OUT, round(OUT.stat().st_size / 1024), 'KB')
