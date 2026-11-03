#!/usr/bin/env python3
"""
One-off converter: research bank draft 08 (+ English draft 36) and the old
data files -> docs/bank-soal/data/{temanggung,magelang,wonosobo}.php in the
format of 31 §6.4 (research revision 10, part 5, code wave D, 3 November 2026).

    php docs/bank-soal/tools/old-bank-json.php docs/arsip/bank-soal-lama/data > old.json
    python3 -I docs/bank-soal/tools/import_08.py old.json docs/bank-soal/data

After the first run the data files are the edited source (31 K1): revise them
by hand, rebuild the workbook, and import. Running this script again would
overwrite those edits; it is kept to show where every line came from.

What comes from where:
- tantangan 2-5 (texts, keys, codes, labels): 08; English: 36 (AI draft);
- Susun Gambar: old data files, letters remapped (31 §14), caption fixes of
  25 -> Akibat ke dokumen lain no. 4, new Wayang Kedu variant (08);
- Cari Objek round 1: old data files (positions, images, decoy feedback),
  clue texts and Bloom from 08/36, positions of Magelang and Wonosobo re-laid
  for the 24 dp rule (31 §9.2);
- Cari Objek round 2 tap-part boxes: a fixed post layout per part count (K8);
- mission cards of the changed and new challenges: 12 -> Kartu misi.
"""
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
DOC08 = (ROOT / 'docs/revisi-10-analitik/08_DRAF_BANK_SOAL.md').read_text(encoding='utf-8')
DOC36 = (ROOT / 'docs/revisi-10-analitik/36_TERJEMAHAN_BANK_SOAL.md').read_text(encoding='utf-8')

REGIONS = [('temanggung', 'tmg', 'mudah', 'Temanggung'), ('magelang', 'mgl', 'sedang', 'Magelang'), ('wonosobo', 'wnb', 'sulit', 'Wonosobo')]
REVIEW_NOTE = ('Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. '
               'Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.')
REVIEW = {'status': 'needs_verification', 'note': REVIEW_NOTE}

# ---------------------------------------------------------------- markdown


def section(doc, heading, level):
    """Text under one heading up to the next heading of the same or higher level."""
    lines = doc.split('\n')
    out, inside = [], False
    for line in lines:
        m = re.match(r'^(#+) ', line)
        if m:
            if inside and len(m.group(1)) <= level:
                break
            if line.strip() == heading.strip():
                inside = True
                continue
        if inside:
            out.append(line)
    if not inside:
        raise SystemExit(f'heading not found: {heading}')
    return '\n'.join(out)


def sub_sections(text, level):
    """[(heading text, body)] for every heading of exactly this level."""
    parts, cur, body = [], None, []
    for line in text.split('\n'):
        m = re.match(r'^(#+) (.*)$', line)
        if m and len(m.group(1)) == level:
            if cur is not None:
                parts.append((cur, '\n'.join(body)))
            cur, body = m.group(2), []
        elif cur is not None:
            body.append(line)
    if cur is not None:
        parts.append((cur, '\n'.join(body)))
    return parts


def tables(text):
    """Every markdown table as a list of rows (list of cells), header first."""
    out, cur = [], []
    for line in text.split('\n'):
        if line.startswith('|'):
            cells = [c.strip() for c in line.strip().strip('|').split('|')]
            if all(re.fullmatch(r':?-+:?', c) for c in cells):
                continue
            cur.append(cells)
        elif cur:
            out.append(cur)
            cur = []
    if cur:
        out.append(cur)
    return out


def plain(s):
    """Markdown inline -> plain text (bold, italics, code)."""
    s = re.sub(r'\*\*(.+?)\*\*', r'\1', s)
    s = re.sub(r'(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])', r'\1', s)
    s = s.replace('`', '')
    return s.strip()


def unquote(s):
    s = s.strip()
    if len(s) >= 2 and s[0] in '"“' and s[-1] in '"”':
        return s[1:-1]
    return s


def quote_blocks(text):
    """Consecutive blockquote lines -> list of blocks; each block a list of lines ('' = blank quote line)."""
    blocks, cur = [], []
    for line in text.split('\n'):
        if line.startswith('>'):
            cur.append(line[1:].lstrip(' ') if len(line) > 1 else '')
        elif cur:
            blocks.append(cur)
            cur = []
    if cur:
        blocks.append(cur)
    return blocks


def field(text, label):
    m = re.search(r'^\*\*' + re.escape(label) + r'(?:\s*\([^)]*\))?:\*\*\s*(.+)$', text, re.M)
    return m.group(1).strip() if m else None


def digital_text(lines):
    """Blockquote lines of a digital text -> body with line markers (31 §6.2, decided here):
    `>> ` header line, `~~ ` small meta line, `:: ` entry (comment, chat message,
    search result); blank line = new block; other lines are plain text."""
    out = []
    for raw in lines:
        if raw.strip() == '':
            out.append('')
            continue
        s = raw.strip()
        stripped = re.sub(r'^[^\w*`\[(]+\s*', '', s)  # leading emoji
        if re.match(r'^💬\s*\*\*[^*]+:\*\*', s):
            out.append(':: ' + plain(s))
        elif stripped.startswith('**') or re.match(r'^\*\*', s):
            out.append('>> ' + plain(s))
        elif re.match(r'^(Dipasang oleh|Ditulis oleh|Put up by|Written by)', s) or re.fullmatch(r'\*\(.*\)\*', s) or s.startswith('❤️'):
            out.append('~~ ' + plain(s))
        else:
            out.append(plain(s))
    text = '\n'.join(out)
    text = re.sub(r'\n{3,}', '\n\n', text)
    return text.strip()


def prose(lines):
    """Blockquote lines of a plain reading -> paragraphs separated by a blank line."""
    paras, cur = [], []
    for raw in lines:
        if raw.strip() == '':
            if cur:
                paras.append(' '.join(cur))
                cur = []
        else:
            cur.append(raw.strip())
    if cur:
        paras.append(' '.join(cur))
    return '\n\n'.join(paras)


def words(text):
    return [t for t in text.split() if re.search(r'\w', t)]


# ------------------------------------------------------------ Isi Rumpang


def cloze_segments(lines):
    """Blockquote of an Isi Rumpang reading -> one segment per blank (___), 31 §6.4.

    Header lines of a site article (🌐 address, title, byline) become one first
    paragraph without the title (the variant title shows it). A paragraph
    break between two blanks is written inside the segment of the earlier
    blank, after its ___ (the importer trims cell edges), and the next
    segment starts with the last sentence before its own blank.
    """
    header, body = [], []
    for raw in lines:
        s = raw.strip()
        if not body and s and (s.startswith('🌐') or re.fullmatch(r'\*\*[^*]+\*\*', s) or re.match(r'^(Ditulis oleh|Written by)', s)):
            if not re.fullmatch(r'\*\*[^*]+\*\*', s):
                header.append(plain(s))
            continue
        if not body and s == '':
            continue
        body.append(raw)
    text = prose(body)
    pieces = re.split(r'\*\*\((\d+)\)\*\*', text)
    # pieces = [before1, '1', between12, '2', ..., after_last]
    texts = pieces[0::2]
    count = len(pieces[1::2])
    segs = []
    carry = texts[0]
    for k in range(count):
        between = texts[k + 1]
        if k == count - 1:
            segs.append(carry + '___' + between)
            break
        if '\n\n' in between:
            before, after = between.split('\n\n', 1)
            head, tail = split_last_clause(after)
            segs.append(carry + '___' + before + '\n\n' + head)
            carry = tail
        else:
            head, tail = split_last_clause(between)
            segs.append(carry + '___' + head)
            carry = tail
    if header:
        segs[0] = ' · '.join(header) + '\n\n' + segs[0]
    return [s.strip() for s in segs]


def split_last_clause(text):
    """'A. B. C ' -> ('A. B.', ' C ') so the next segment starts with text."""
    m = list(re.finditer(r'[.!?]\s+', text))
    if m:
        cut = m[-1].end()
        if text[cut:].strip():
            return text[:cut].rstrip(), ' ' + text[cut:].lstrip()
    if text.strip() == '':
        return '', text
    # no sentence boundary: keep the text with the next blank
    return '', text


def pengecoh_ids(text):
    m = re.search(r'Pengecoh bank kata: ([^·\n]+)', text)
    return [plain(w).strip() for w in m.group(1).split(',')] if m else []


def pengecoh_en(text):
    m = re.search(r'^Pengecoh: (.+?)\.?\s*(Lihat .*)?$', text, re.M)
    out = {}
    for pair in m.group(1).split(','):
        a, b = pair.split('→')
        out[plain(a).strip().lower()] = plain(b).strip()
    return out


def references(text):
    """**Rujukan:** variasi a: X. Variasi b: Y. -> {'a': X, 'b': Y} (or {'*': text})."""
    ref = field(text, 'Rujukan')
    if ref is None:
        return {}
    ref = plain(ref)
    m = re.match(r'(?i)variasi a: (.+?)\s*Variasi b: (.+)$', ref)
    if m:
        return {'a': m.group(1).rstrip('. '), 'b': m.group(2).rstrip('. ')}
    return {'*': ref.rstrip('. ')}


def pirls(akm, prefix):
    if akm == 'L1':
        return 'retrieve'
    if akm == 'L3':
        return 'evaluate'
    return 'infer' if prefix == 'mgl' else 'integrate'


def rumpang(prefix, region, old):
    ref = f'{prefix}-2'
    sec = section(DOC08, f'## `{ref}` Isi Rumpang', 2)
    sec_en = section(DOC36, f'## `{ref}` Isi Rumpang', 2)
    instr = unquote(field(sec, 'Instruksi'))
    instr_en = unquote(field(sec_en, 'Instruction'))
    refs = references(sec)
    variants = {}
    v_en = {h.split(':')[0].strip().split()[-1]: (h, b) for h, b in sub_sections(sec_en, 3)}
    for heading, body in sub_sections(sec, 3):
        g = heading.split(':')[0].strip().split()[-1]
        title = heading.split(':', 1)[1].strip()
        h_en, b_en = v_en[g]
        title_en = plain(h_en.split(':', 1)[1])
        segs = cloze_segments(quote_blocks(body)[0])
        segs_en = cloze_segments(quote_blocks(b_en)[0])
        rows = tables(body)[0][1:]
        rows_en = {r[0]: r for r in tables(b_en)[0][1:]}
        distractors = pengecoh_ids(body)
        d_en = pengecoh_en(b_en)
        special = {}
        sm = re.search(r'Kode khusus: \*([^*]+)\* di rumpang \(([\d) (dan,]+)\) → ([A-Z-]+)', body)
        items = []
        for n, r in enumerate(rows):
            key, _, answer, sub, akm, bloom, trap, note = r[:8]
            item = {
                'key': key, 'type': 'fill_blank_bank', 'prompt': [segs[n], segs_en[n]],
                'answer': [answer, rows_en[key][3]],
                'dim': 'D1', 'sub': sub, 'akm': akm, 'pirls': pirls(akm, prefix), 'bloom': bloom,
            }
            if trap != '—':
                tw, tc = trap.split('→')
                item['trap'] = {plain(tw).lower(): tc.strip()}
            item['note'] = plain(note)
            item['review'] = REVIEW
            items.append(item)
        if sm:
            word = sm.group(1).lower()
            for num in re.findall(r'\d+', sm.group(2)):
                special.setdefault(word, {})[f'{ref}-{g}{int(num):02d}'] = sm.group(3)
        var = {
            'title': [title, title_en],
            'ref': refs.get(g, refs.get('*', '')),
            'distractors': [[w, d_en[w.lower()]] for w in distractors],
        }
        if special:
            var['special'] = special
        var['items'] = items
        variants[g] = var
    node = {
        'engine': 'rumpang', 'variant': 'rumpang_bacaan', 'indicator': 'D1a', 'distractor_count': 2,
        'genre': None,
        'title': old['title'], 'instruction': [instr, instr_en],
        'description': mission(ref, old), 'hints': hints_for(ref, old), 'variants': variants,
    }
    return node


# ------------------------------------------------------- Benar atau Salah

LAYOUT_BY_GENRE = {
    'papan_info': 'info_screen', 'unggahan_resmi': 'post', 'dua_sumber': 'chat',
    'pesan_grup': 'chat', 'hasil_pencarian': 'search', 'unggahan_bersponsor': 'post',
}
GENRES = {
    'tmg': ('artikel_pustaka', 'papan_info', 'pesan_grup'),
    'mgl': ('artikel_majalah', 'unggahan_resmi', 'hasil_pencarian'),
    'wnb': ('situs_wisata', 'dua_sumber', 'unggahan_bersponsor'),
}
ANCHOR_REF = {
    'tmg': 'Pesan grup kelas rekaan (kartu jangkar); tanpa klaim fakta budaya',
    'mgl': 'GNFI (2024) (Topeng Ireng dari Desa Tuksongo, dicek di 05); hasil pencarian, iklan, dan situs rekaan',
    'wnb': 'Unggahan bersponsor rekaan (kartu jangkar); tanpa klaim fakta budaya',
}
STATEMENT_MEDIA = {
    'tmg-3-a04': {'key': 'challenge.item.tmg-3-a03', 'label': 'Butir padi hangus', 'note': 'butir padi hangus kehitaman (temuan arkeologi)'},
    'tmg-3-b04': {'key': 'challenge.item.tmg-3-b04', 'label': 'Arca lembu duduk', 'note': 'arca lembu Nandi duduk dari batu', 'source': 'Baru (08 → Gambar yang dibutuhkan): gambar lama tmg-3-b03 sengaja arca Buddha'},
}


def skill_for(sub, code, text, media):
    """Developer label for `skill` (08 gives none): reading skill of the statement."""
    t = text.lower()
    if media:
        return 'mengenali_gambar'
    if sub == 'D1b':
        if code == 'INF-FOP':
            return 'membedakan_fakta_pendapat'
        if 'iklan' in t or 'bersponsor' in t:
            return 'mengenali_iklan'
        if 'daripada' in t:
            return 'membandingkan_sumber'
        if 'pemasang' in t or 'pembuat' in t:
            return 'mengenali_penanda_sumber'
        return 'menilai_kredibilitas'
    if code == 'INF-REL':
        return 'gagasan_pokok'
    if code == 'INF-HUB':
        return 'menghubungkan_informasi'
    if code == 'INF-GEN':
        return 'menilai_pilihan'
    return 'informasi_tersurat'


def statements(rows, rows_en, testlet):
    items = []
    for r in rows:
        key, text, answer, sub, akm, bloom, code = r[:7]
        media = None
        m = re.match(r'\[Gambar: ([^\]]+)\]\s*(.*)$', text)
        if m:
            text = m.group(2)
            media = STATEMENT_MEDIA[key]
        en = re.sub(r'^\[Picture: [^\]]+\]\s*', '', rows_en[key][1])
        item = {
            'key': key, 'type': 'verdict_card', 'prompt': [plain(text), plain(en)], 'answer': answer,
            'code': code, 'dim': 'D1', 'sub': sub, 'akm': akm, 'pirls': pirls(akm, testlet[:3]), 'bloom': bloom,
            'skill': skill_for(sub, code, text, media),
        }
        if media:
            item['media'] = media
        item['review'] = REVIEW
        items.append(item)
    return items


def boleh(prefix, old):
    ref = f'{prefix}-3'
    sec = section(DOC08, f'## `{ref}` Benar atau Salah', 2)
    sec_en = section(DOC36, f'## `{ref}` Benar atau Salah', 2)
    instr = unquote(field(sec, 'Instruksi'))
    instr_en = unquote(field(sec_en, 'Instruction'))
    refs = references(sec)
    genre = GENRES[prefix][1]
    v_en = {}
    for h, b in sub_sections(sec_en, 3):
        v_en['x' if h.startswith('Kartu jangkar') else h.split(':')[0].split()[-1]] = (h, b)
    variants, anchor = {}, None
    for heading, body in sub_sections(sec, 3):
        if heading.startswith('Kartu jangkar'):
            h_en, b_en = v_en['x']
            anchor = {
                'passage': {
                    'title': ['', ''],
                    'body': [digital_text(quote_blocks(body)[0]), digital_text(quote_blocks(b_en)[0])],
                    'genre': GENRES[prefix][2], 'layout': LAYOUT_BY_GENRE[GENRES[prefix][2]],
                    'ref': ANCHOR_REF[prefix],
                },
                'items': statements(tables(body)[0][1:], {r[0]: r for r in tables(b_en)[0][1:]}, ref),
            }
            continue
        g = heading.split(':')[0].strip().split()[-1]
        h_en, b_en = v_en[g]
        quote = quote_blocks(body)
        quote_en = quote_blocks(b_en)
        # the whole first blockquote is the text (multi-part texts are one block with blank lines)
        variants[g] = {
            'passage': {
                'title': [heading.split(':', 1)[1].strip(), plain(h_en.split(':', 1)[1])],
                'body': [digital_text(quote[0]), digital_text(quote_en[0])],
                'genre': genre, 'layout': LAYOUT_BY_GENRE[genre],
                'ref': refs.get(g, refs.get('*', '')),
            },
            'items': statements(tables(body)[0][1:], {r[0]: r for r in tables(b_en)[0][1:]}, ref),
        }
    return {
        'engine': 'boleh', 'variant': 'benar_salah_bacaan', 'indicator': 'D1a',
        'title': old['title'], 'instruction': [instr, instr_en],
        'description': mission(ref, old), 'hints': hints_for(ref, old), 'variants': variants, 'anchor': anchor,
    }


# ------------------------------------------------------------- Cari Objek

# Round-1 positions re-laid for the 24 dp rule (31 §9.2), on the catalogue
# scenes' tables (media key -> x, y, w). Temanggung already passes.
RELAID = {
    'mgl': {
        'challenge.item.mgl-4-a02': (1.0, 58.44, 6.5), 'challenge.item.mgl-4-a05': (10.5, 58.44, 6.5),
        'challenge.item.mgl-4-a09': (20.0, 58.44, 6.5), 'challenge.item.mgl-4-a07': (29.5, 45.33, 6.0),
        'challenge.item.mgl-4-a01': (38.3, 45.33, 6.0), 'challenge.item.mgl-4-a03': (55.7, 45.33, 6.0),
        'challenge.item.mgl-4-a06': (64.5, 45.33, 6.0), 'challenge.item.mgl-4-a08': (73.0, 58.44, 6.5),
        'challenge.item.mgl-4-a04': (82.5, 58.44, 6.5), 'challenge.item.mgl-4-a10': (92.0, 58.44, 6.5),
    },
    'wnb': {
        'challenge.item.wnb-4-a01': (25.0, 28.8, 8.0), 'challenge.item.wnb-4-a08': (47.0, 28.8, 8.0),
        'challenge.item.wnb-4-a12': (69.0, 28.8, 8.0), 'challenge.item.wnb-4-a09': (14.0, 43.8, 8.0),
        'challenge.item.wnb-4-a03': (36.0, 43.8, 8.0), 'challenge.item.wnb-4-a11': (58.0, 43.8, 8.0),
        'challenge.item.wnb-4-a02': (80.0, 43.8, 8.0), 'challenge.item.wnb-4-a06': (4.0, 63.1, 8.0),
        'challenge.item.wnb-4-a07': (23.0, 63.1, 8.0), 'challenge.item.wnb-4-a05': (42.0, 63.1, 8.0),
        'challenge.item.wnb-4-a04': (61.0, 63.1, 8.0), 'challenge.item.wnb-4-a10': (80.0, 63.1, 8.0),
    },
}
SCENE_NOTES = {
    'mgl': 'galeri/pendapa budaya, meja pajang panjang di depan kiri dan kanan (permukaan ± 70 % tinggi gambar) dan meja kecil di belakangnya dekat lorong tengah (permukaan ± 56 %), stupa & Gunung Tidar di latar; benda ditata ulang 3 November 2026 supaya berjarak paling sedikit 24 dp',
    'wnb': 'pasar oleh-oleh Dieng berkabut tipis: rak bambu beratap (permukaan ± 43 %), meja kayu (± 58 %), dan bangku bambu di depan (± 77 %), kebun teh & candi di latar; benda ditata ulang 3 November 2026 supaya berjarak paling sedikit 24 dp',
}
CLUE_TYPE = {'Atribusi': 'ATR', 'Representasi': 'REP', 'Izin': 'IZN', 'Menghargai tradisi': 'ETK'}
POST_MEDIA = {
    ('tmg', 'a'): {'key': 'challenge.item.tmg-1-04', 'label': 'Kuda Lumping'},
    ('tmg', 'b'): {'key': 'challenge.post.tmg-4-b', 'label': 'Uceng goreng di piring', 'note': 'ikan uceng goreng kering di atas piring, foto makanan dari atas; tanpa tulisan', 'source': 'Baru (08 → Gambar yang dibutuhkan), atau potongan gambar Susun Gambar tmg-1-h01'},
    ('mgl', 'a'): {'key': 'challenge.item.mgl-1-02', 'label': 'Penari Topeng Ireng'},
    ('mgl', 'b'): {'key': 'challenge.item.mgl-1-h01', 'label': 'Gerabah Klipoh'},
    ('wnb', 'a'): {'key': 'challenge.item.wnb-1-g01', 'label': 'Bundengan'},
    ('wnb', 'b'): {'key': 'challenge.item.wnb-1-07', 'label': 'Mi Ongklok'},
}
POST_TITLE_EN = {
    'Unggahan foto kuda lumping': 'A kuda lumping photo post', 'Unggahan video ikan uceng': 'An uceng fish video post',
    'Unggahan video Topeng Ireng': 'A Topeng Ireng video post', 'Unggahan foto gerabah Klipoh': 'A Klipoh pottery photo post',
    'Unggahan video bundengan': 'A bundengan video post', 'Unggahan video mi ongklok': 'An ongklok noodle video post',
}


def part_kind(label):
    l = label.lower()
    if l.startswith('nama akun'):
        return 'account'
    if l.startswith('tulisan kecil'):
        return 'credit'
    if l.startswith('keterangan'):
        return 'caption'
    if 'papan' in l or 'kartu informasi' in l:
        return 'board'
    if l.startswith('komentar'):
        return 'comment'
    if l.startswith('tombol'):
        return 'button'
    if l.startswith('jumlah'):
        return 'meta'
    raise SystemExit('unknown part kind: ' + label)


def part_text(s):
    s = s.strip()
    m = re.match(r'^\*\*([^*]+)\*\*:\s*(.+)$', s)
    if m:
        return m.group(1).strip() + ': ' + unquote(m.group(2))
    return unquote(plain(s))


def post_layout(kinds):
    """Fixed post layout (K8): boxes x, y, w, h in percent of the 16:9 scene.

    Left column: account, the photo with its credit, caption, likes, buttons.
    Right column: information board, comments. Every pair of boxes keeps
    at least 24 dp (2.78 % of the width, 4.94 % of the height) apart.
    """
    boxes = {}
    buttons = [k for k, kind in kinds if kind == 'button']
    comments = [k for k, kind in kinds if kind == 'comment']
    for k, kind in kinds:
        if kind == 'account':
            boxes[k] = (3, 3, 38, 8)
        elif kind == 'credit':
            boxes[k] = (5, 42, 26, 7)
        elif kind == 'caption':
            boxes[k] = (3, 55, 38, 12)
        elif kind == 'meta':
            boxes[k] = (3, 73, 14, 7)
        elif kind == 'board':
            boxes[k] = (46, 3, 51, 22)
    # comments stacked under the board, 9 % high with 5 % gaps
    for i, k in enumerate(comments):
        boxes[k] = (46, 30 + i * 14, 51, 9)
    # buttons in one row at the bottom
    x = 3
    widths = {'Bagikan': 13, 'Unduh': 12}
    for k in buttons:
        w = widths.get(kinds_text[k], 25)
        boxes[k] = (x, 87, w, 9)
        x += w + 3
    return boxes


kinds_text = {}


def cari(prefix, region, old):
    ref = f'{prefix}-4'
    sec = section(DOC08, f'## `{ref}` Cari Objek', 2)
    sec_en = section(DOC36, f'## `{ref}` Cari Objek', 2)
    r1 = dict(sub_sections(sec, 3))
    r1_en = dict(sub_sections(sec_en, 3))
    k1 = next(k for k in r1 if k.startswith('Babak 1'))
    k2 = next(k for k in r1 if k.startswith('Babak 2'))
    k1e = next(k for k in r1_en if k.startswith('Babak 1'))
    k2e = next(k for k in r1_en if k.startswith('Babak 2'))
    clues = tables(r1[k1])[0][1:]
    clues_en = {r[0]: r[1] for r in tables(r1_en[k1e])[0][1:]}
    old_items = old['variants']['a']['items']
    targets = [i for i in old_items if not i.get('decoy')]
    decoys = [i for i in old_items if i.get('decoy')]
    hunt_ref = old['variants']['a'].get('ref', '')
    items = []
    for n, (key, text, answer, bloom) in enumerate(clues):
        o = targets[n]
        x, y, w = RELAID.get(prefix, {}).get(o['media']['key'], (o['x'], o['y'], o['w']))
        items.append({
            'key': key, 'type': 'find_object', 'prompt': [plain(text), plain(clues_en[key])],
            'x': x, 'y': y, 'w': w, 'dim': 'PBK', 'bloom': bloom, 'answer_label': plain(answer),
            'media': o['media'], 'review': REVIEW,
        })
    for n, o in enumerate(decoys):
        x, y, w = RELAID.get(prefix, {}).get(o['media']['key'], (o['x'], o['y'], o['w']))
        items.append({
            'key': f'{ref}-x{len(clues) + n + 1:02d}', 'type': 'find_object', 'decoy': True,
            'code': 'INF-BUD', 'x': x, 'y': y, 'w': w, 'prompt': o['prompt'], 'wrong': o['wrong'],
            'media': o['media'], 'review': REVIEW,
        })
    scene = dict(old['scene'])
    if prefix in SCENE_NOTES:
        scene['note'] = SCENE_NOTES[prefix]
    round2 = {}
    v_en = {h.split(':')[0].split()[-1]: b for h, b in sub_sections(r1_en[k2e], 4)}
    refs = references(sec)
    ref_text = refs.get('*', '')
    for heading, body in sub_sections(r1[k2], 4):
        g = heading.split(':')[0].split()[-1]
        title = heading.split(':', 1)[1].strip()
        tbl = tables(body)
        tbl_en = tables(v_en[g])
        parts_rows = tbl[0][1:]
        parts_en = {r[0]: r for r in tbl_en[0][1:]}
        kinds = []
        for r in parts_rows:
            kinds.append((r[0], part_kind(r[1])))
            kinds_text[r[0]] = part_text(r[2])
        boxes = post_layout(kinds)
        parts = []
        for r in parts_rows:
            k = r[0]
            x, y, w, h = boxes[k]
            parts.append({
                'key': k, 'kind': part_kind(r[1]), 'label': [r[1], parts_en[k][1]],
                'text': [part_text(r[2]), part_text(parts_en[k][2])], 'x': x, 'y': y, 'w': w, 'h': h,
            })
        clue_en = {r[0]: r[1] for r in tbl_en[1][1:]}
        clue_items = []
        for r in tbl[1][1:]:
            key, kind, text, answer, main, bloom = r[:6]
            mains = {}
            for m in main.split(';'):
                p, c = m.split('→')
                mains[p.strip()] = c.strip()
            clue_items.append({
                'key': key, 'type': 'find_part', 'prompt': [plain(text), plain(clue_en[key])], 'answer': answer.strip(),
                'clue_type': CLUE_TYPE[kind], 'main': mains, 'dim': 'D3', 'akm': AKM_EQUIV[prefix], 'bloom': bloom,
                'review': REVIEW,
            })
        round2[g] = {
            'scene': {'title': [title, POST_TITLE_EN[title]], 'media': POST_MEDIA[(prefix, g)], 'ref': ref_text},
            'parts': parts,
            'items': clue_items,
        }
    instr = old['instruction']
    return {
        'engine': 'cari', 'variant': 'cari_objek', 'indicator': 'D3',
        'title': old['title'], 'instruction': instr, 'description': mission(ref, old), 'scene': scene,
        'hints': old['hints'],
        'round1': {'title': old['variants']['a']['title'], 'ref': hunt_ref, 'items': items},
        'round2': round2,
    }


AKM_EQUIV = {'tmg': 'L1', 'mgl': 'L2', 'wnb': 'L3'}

# -------------------------------------------------------- Skenario Digital

TOPIC = [('Data pribadi', 'PRI'), ('Kata sandi', 'SAN'), ('Izin', 'IZN'), ('Kabar bohong', 'ASG'), ('Pesan berantai', 'ASG')]


def scenario_blocks(text, english=False):
    """Split a variant body into scenarios: [(key, topic, situation, body)]."""
    out = []
    pattern = re.compile(r'^\*\*([a-z]{3}-5-[ab]\d\d) · ([^*]+?)\.\*\*\s*(.+)$', re.M)
    matches = list(pattern.finditer(text))
    for i, m in enumerate(matches):
        end = matches[i + 1].start() if i + 1 < len(matches) else len(text)
        out.append((m.group(1), m.group(2), m.group(3).strip(), text[m.end():end]))
    return out


def skenario(prefix, region):
    ref = f'{prefix}-5'
    sec = section(DOC08, f'## `{ref}` Skenario Digital', 2)
    sec_en = section(DOC36, f'## `{ref}` Skenario Digital', 2)
    bloom_line = field(sec, 'Bloom')
    default_bloom = re.match(r'(C\d)', bloom_line).group(1)
    exceptions = dict((k, b) for k, b in re.findall(r'`([a-z]{3}-5-[ab]\d\d)`(?: dan `([a-z]{3}-5-[ab]\d\d)`)? (C\d)', bloom_line) and [])
    exc = {}
    m = re.search(r'kecuali (.+)$', bloom_line)
    if m:
        level = re.search(r'(C\d)\s*$', m.group(1)).group(1)
        for k in re.findall(r'`([a-z]{3}-5-[ab]\d\d)`', m.group(1)):
            exc[k] = level
    scen = {}
    v_en = {h.split()[-1]: b for h, b in sub_sections(sec_en, 3)}
    for heading, body in sub_sections(sec, 3):
        g = heading.split()[-1]
        en_blocks = {k: (t, s, b) for k, t, s, b in scenario_blocks(v_en[g], True)}
        lst = []
        for key, topic, situation, rest in scenario_blocks(body):
            code = next(c for label, c in TOPIC if topic.startswith(label))
            t_en, s_en, rest_en = en_blocks[key]
            acts = tables(rest)[0][1:]
            acts_en = tables(rest_en)[0][1:]
            reasons = tables(rest)[1][1:]
            reasons_en = tables(rest_en)[1][1:]
            expl = plain(field(rest, 'Penjelasan setelah kesempatan habis'))
            expl_en = plain(field(rest_en, 'Explanation after tries run out'))
            actions = []
            for n, a in enumerate(acts):
                text, score, c, fb = a[:4]
                item = {'text': [plain(text), plain(acts_en[n][0])], 'score': int(score)}
                if c != '—':
                    item['code'] = c
                item['feedback'] = [plain(fb), plain(acts_en[n][1])]
                actions.append(item)
            rs = [{'text': [plain(r[0]), plain(reasons_en[n][0])], 'code': r[1]} for n, r in enumerate(reasons)]
            lst.append({
                'key': key, 'topic': code, 'steps': 2 if prefix == 'wnb' else 1,
                'situation': [plain(situation), plain(s_en)], 'actions': actions, 'reasons': rs,
                'explanation': [expl, expl_en], 'dim': 'D2', 'akm': AKM_EQUIV[prefix],
                'bloom': exc.get(key, default_bloom), 'review': REVIEW,
            })
        scen[g] = lst
    instr = section(DOC08, '## `tmg-5` Skenario Digital', 2)
    instr_en = section(DOC36, '## `tmg-5` Skenario Digital', 2)
    return {
        'engine': 'skenario', 'variant': 'skenario_digital', 'indicator': 'D2',
        'title': MISSION_TITLES[ref], 'instruction': [unquote(field(instr, 'Instruksi')), unquote(field(instr_en, 'Instruction'))],
        'description': MISSION[ref], 'scenarios': scen,
    }


# ------------------------------------------------------ mission cards (12)

MISSION_TITLES = {
    'tmg-5': ['Skenario Digital Temanggung', 'Digital Scenarios: Temanggung'],
    'mgl-5': ['Skenario Digital Magelang', 'Digital Scenarios: Magelang'],
    'wnb-5': ['Skenario Digital Wonosobo', 'Digital Scenarios: Wonosobo'],
}


def load_missions():
    sec = section((ROOT / 'docs/revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md').read_text(encoding='utf-8'), '## Kartu misi', 2)
    out = {}
    for t in tables(sec):
        head = t[0]
        for r in t[1:]:
            ref = r[0].strip('`')
            cell = r[2] if head[1] in ('Judul', 'Kenapa diubah') else r[1]
            ids, en = cell.split(' / ', 1)
            out[ref] = [plain(ids), plain(en)]
    return out


MISSION = load_missions()


def mission(ref, old):
    return MISSION.get(ref, old.get('description'))


HINT_FIX = {
    'Ada satu kata pengecoh. Setiap kata hanya dipakai satu kali.': ['Ada dua kata pengecoh. Setiap kata hanya dipakai satu kali.', 'There are two decoy words. Each word is used only once.'],
}


def hints_for(ref, old):
    out = []
    for h in old.get('hints', []):
        out.append(HINT_FIX.get(h[0], h))
    return out


# ------------------------------------------------------------ Susun Gambar

# new letter <- old letter (31 §14); None = new picture
LETTERS = {
    'tmg': {'a': 'i', 'b': None, 'c': 'a', 'd': 'b', 'e': 'c', 'f': 'd', 'g': 'e', 'h': 'f', 'i': 'g', 'j': 'h', 'k': 'j'},
    'mgl': {'a': 'c', 'b': 'e', 'c': 'a', 'd': 'b', 'e': 'd', 'f': 'f', 'g': 'g', 'h': 'h', 'i': 'i', 'j': 'j'},
    'wnb': {'a': 'd', 'b': 'i', 'c': 'a', 'd': 'b', 'e': 'c', 'f': 'e', 'g': 'f', 'h': 'g', 'i': 'h', 'j': 'j'},
}
# 25 -> Akibat ke dokumen lain no. 4 (old letters): [search, replace] on [ID, EN]
CAPTION_FIX = {
    ('tmg', 'a'): [('Gunung Sindoro (3.136 m) dan Gunung Sumbing (3.371 m)', 'Gunung Sindoro (sekitar 3.150 m) dan Gunung Sumbing (sekitar 3.370 m)'),
                   ('Mount Sindoro (3,136 m) and Mount Sumbing (3,371 m)', 'Mount Sindoro (about 3,150 m) and Mount Sumbing (about 3,370 m)')],
    ('tmg', 'c'): [('Di Temanggung ada lebih dari 1.500 kelompok kuda lumping.', 'Di Temanggung ada ratusan kelompok kuda lumping.'),
                   ('Temanggung has more than 1,500 kuda lumping groups.', 'Temanggung has hundreds of kuda lumping groups.')],
    ('tmg', 'e'): [('Prasasti Gondosuli dibuat pada tahun 832 Masehi.', 'Prasasti Gondosuli diperkirakan dibuat sekitar tahun 832 Masehi.'),
                   ('The Gondosuli Inscription was made in the year 832 CE.', 'The Gondosuli Inscription is thought to have been made around 832 CE.')],
    ('tmg', 'h'): [('Ikan ini hidup di sungai yang airnya jernih dan biasanya digoreng kering.', 'Ikan ini hidup di sungai berarus, misalnya Sungai Progo, dan biasanya digoreng kering.'),
                   ('It lives in clear rivers and is usually fried until crispy.', 'It lives in flowing rivers, such as the Progo River, and is usually fried until crispy.')],
    ('mgl', 'g'): [('singkong yang dikukus,', 'singkong yang direbus atau dikukus,'),
                   ('cassava that is steamed,', 'cassava that is boiled or steamed,')],
    ('wnb', 'b'): [('desa tertinggi di Pulau Jawa', 'yang disebut-sebut sebagai desa tertinggi di Pulau Jawa'),
                   ('the highest village in Java', 'often called the highest village in Java')],
    ('wnb', 'd'): [('Kompleks Candi Arjuna di Dataran Tinggi Dieng adalah salah satu kompleks candi Hindu tertua di Jawa.',
                    'Kompleks Candi Arjuna ada di Desa Dieng Kulon, Banjarnegara, tetangga Wonosobo di Dataran Tinggi Dieng. Kompleks ini salah satu kompleks candi Hindu tertua di Jawa.'),
                   ('The Arjuna Temple complex on the Dieng Plateau is one of the oldest Hindu temple complexes in Java.',
                    "The Arjuna Temple complex is in Dieng Kulon Village, Banjarnegara, Wonosobo's neighbour on the Dieng Plateau. It is one of the oldest Hindu temple complexes in Java.")],
    ('wnb', 'h'): [('Kebun teh Tambi di Wonosobo sudah ada sejak tahun 1865.', 'Kebun teh Tambi di Wonosobo sudah ada sejak zaman Belanda, lebih dari 150 tahun lalu.'),
                   ('The Tambi tea estate in Wonosobo has been there since 1865.', 'The Tambi tea estate in Wonosobo has been there since Dutch colonial times, more than 150 years ago.')],
    ('wnb', 'j'): [('suhu pagi di Dataran Tinggi Dieng bisa turun di bawah 0 °C', 'suhu di dekat tanah di Dataran Tinggi Dieng bisa turun di bawah 0 °C pada pagi hari'),
                   ('early-morning temperatures on the Dieng Plateau can drop below 0 °C', 'the temperature near the ground on the Dieng Plateau can drop below 0 °C in the early morning')],
}
CAPTION_FIX_2 = {
    ('wnb', 'j'): [('Upas berarti racun, sebab es ini bisa merusak tanaman kentang.', 'Upas berarti racun. Es ini memang bisa merusak tanaman kentang.'),
                   ('Upas means poison, because the ice can damage potato plants.', 'Upas means poison. This ice can indeed damage potato plants.')],
}
WAYANG = {
    'title': ['Wayang Kedu', 'Wayang Kedu'],
    'ref': '08 → Keputusan pengembang (26 Oktober 2026): asal Wayang Kedu Temanggungan dikonfirmasi pengembang',
    'items': [{
        'type': 'puzzle_arrange',
        'prompt': [
            'Wayang Kedu adalah wayang kulit asli wilayah Kedu yang berkembang di Temanggung. Dalang memainkan wayang di balik kelir, yaitu kain putih yang disinari lampu, dengan iringan gamelan.',
            'Wayang Kedu is the shadow puppetry native to the Kedu area that grew in Temanggung. The dalang, or puppeteer, plays the puppets behind the kelir, a white cloth lit by a lamp, to the sound of gamelan.',
        ],
        'media': {
            'key': 'challenge.item.tmg-1-k01', 'label': 'Wayang Kedu',
            'note': 'pertunjukan wayang kulit di pendapa desa: dalang dewasa di balik kelir yang disinari lampu, bayangan wayang di kelir, pemain gamelan, anak-anak menonton sambil duduk; tanpa gunung, kebun kopi, candi, kuda kepang, ikan, dan tulisan',
            'source': 'Baru: usulan prompt TMG-P11 (08 → Susun Gambar selama studi). Gambar dari tim katalog, lalu dicek ulang terhadap butir; sampai ada, gelita:mockups memasang gambar contoh.',
        },
        'review': {'status': 'needs_verification', 'note': 'Revisi 10 (31 §14): variasi baru b Temanggung (Wayang Kedu, keterangan dari 08, tanpa status Warisan Budaya Takbenda). Teks Inggris: draf AI, terjemahan belum ada yang diperiksa pengembang.'},
    }],
}
PUZZLE_NOTE = 'Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.'


def puzzle(prefix, old):
    variants = {}
    for new, oldg in LETTERS[prefix].items():
        if oldg is None:
            variants[new] = WAYANG
            continue
        v = json.loads(json.dumps(old['variants'][oldg]))
        it = v['items'][0]
        for fixes in (CAPTION_FIX.get((prefix, oldg)), CAPTION_FIX_2.get((prefix, oldg))):
            if not fixes:
                continue
            for lang in (0, 1):
                a, b = fixes[lang]
                if a not in it['prompt'][lang]:
                    raise SystemExit(f'caption fix not found: {prefix}-1-{oldg}: {a}')
                it['prompt'][lang] = it['prompt'][lang].replace(a, b)
        note = it.get('review', {}).get('note', '')
        note = re.sub(r'\s*\|?\s*Revisi v2 \(17 Okt 2026\): Penelitian: pengerjaan pertama Susun Gambar memakai variasi a–c[^|]*', '', note).strip(' |')
        if (prefix, oldg) == ('mgl', 'i'):
            note = note.replace(' Fakta Mrapen perlu dicek.', '')
        if (prefix, oldg) in CAPTION_FIX:
            note = (note + ' | ' if note else '') + 'Revisi 10: keterangan diperbaiki dari cek fakta 25.'
        if (prefix, oldg) == ('mgl', 'e'):
            note = (note + ' | ' if note else '') + 'Revisi 10: gambar diganti (stupa kecil di atap mirip sasaran mgl-4-x01); gambar baru dari tim katalog.'
        status = 'needs_verification' if (prefix, oldg) in CAPTION_FIX or (prefix, oldg) == ('mgl', 'e') else it.get('review', {}).get('status', 'verified')
        it['review'] = {'status': status, 'note': (note + ' | ' if note else '') + PUZZLE_NOTE}
        variants[new] = v
    if prefix == 'tmg':
        geo = 'Global Volcanism Program (Sindoro, Sumbing); Pemkab Temanggung (profil daerah)'
        variants['c']['ref'] = geo
    desc = old['description']
    if prefix == 'wnb':
        desc = ['Kabut tebal Dieng memotong-motong sebuah lukisan Wonosobo dan Dieng seperti kepingan puzzle. Susun kembali kepingnya, lalu kenali tempat di gambar itu.',
                'The thick Dieng mist has cut a painting of Wonosobo and Dieng into jigsaw pieces. Put the pieces back together, then get to know the place in the picture.']
    node = {k: old[k] for k in ('engine', 'variant', 'indicator', 'title', 'instruction')}
    node['indicator'] = ''
    node['description'] = desc
    node['hints'] = old['hints']
    node['variants'] = variants
    return node


# ----------------------------------------------------------------- output


def php(value, indent=0):
    pad = '    ' * indent
    if value is None:
        return 'null'
    if value is True:
        return 'true'
    if value is False:
        return 'false'
    if isinstance(value, (int, float)):
        return repr(value) if isinstance(value, int) or value != int(value) else str(int(value))
    if isinstance(value, str):
        return "'" + value.replace('\\', '\\\\').replace("'", "\\'") + "'" if '\n' not in value else '"' + value.replace('\\', '\\\\').replace('"', '\\"').replace('$', '\\$').replace('\n', '\\n') + '"'
    if isinstance(value, list):
        if all(isinstance(v, str) for v in value) and len(value) == 2 and sum(len(v) for v in value) < 110:
            return '[' + ', '.join(php(v) for v in value) + ']'
        if all(isinstance(v, (str, int, float)) for v in value) and sum(len(str(v)) for v in value) < 60:
            return '[' + ', '.join(php(v) for v in value) + ']'
        inner = ''.join(f'{pad}    {php(v, indent + 1)},\n' for v in value)
        return '[\n' + inner + pad + ']'
    if isinstance(value, dict):
        if not value:
            return '[]'
        width = max(len(php(k)) for k in value)
        inner = ''.join(f'{pad}    {php(k).ljust(width)} => {php(v, indent + 1)},\n' for k, v in value.items())
        return '[\n' + inner + pad + ']'
    raise TypeError(type(value))


HEADER = """<?php

/**
 * Bank soal kelas 5 — {name} (wilayah {seq}, tingkat {difficulty}).
 *
 * Research bank of revision 10 (docs/revisi-10-analitik/08_DRAF_BANK_SOAL.md;
 * English: 36, AI draft), format of 31 §6.4, written by
 * docs/bank-soal/tools/import_08.py on 3 November 2026 and edited by hand
 * from then on. Every item of 08 is `needs_verification` until expert
 * validation. The old bank is archived in docs/arsip/bank-soal-lama/.
 *
 * Shape (docs/bank-soal/README.md → Bentuk berkas data): texts are pairs
 * [Indonesia, English]; labels per item `dim`, `sub`, `akm`, `pirls`,
 * `bloom` (developer labels; `labels` => 'expert' once they come from the
 * blind labelling); `trap`/`special` (Isi Rumpang), `code` (statements and
 * round-1 decoys), `main` (round-2 lures). build-workbook.php derives the
 * full error-code matrices (K6). Line markers of digital texts: `>> ` header,
 * `~~ ` small meta line, `:: ` entry (comment, message, search result).
 */

return """


def main():
    old_all = json.load(open(sys.argv[1], encoding='utf-8'))
    out_dir = Path(sys.argv[2])
    for seq, (region, prefix, difficulty, name) in enumerate(REGIONS, 1):
        old = old_all[region]['nodes']
        data = {
            'difficulty': difficulty,
            'nodes': {
                f'{prefix}-1': puzzle(prefix, old[f'{prefix}-1']),
                f'{prefix}-2': rumpang(prefix, region, old[f'{prefix}-2']),
                f'{prefix}-3': boleh(prefix, old[f'{prefix}-3']),
                f'{prefix}-4': cari(prefix, region, old[f'{prefix}-4']),
                f'{prefix}-5': skenario(prefix, region),
            },
        }
        text = HEADER.format(name=name.upper(), seq=seq, difficulty=difficulty) + php(data) + ';\n'
        (out_dir / f'{region}.php').write_text(text, encoding='utf-8')
        print('written', region)


if __name__ == '__main__':
    main()
