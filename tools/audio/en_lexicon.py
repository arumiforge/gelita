"""Pelafalan nama Jawa/Indonesia di naskah Inggris (IPA untuk blok [[…]] Piper).

Dua varian: 'us' (espeak en-us, mis. joe) dan 'gb' (en / en-gb-x-rp: kristin, jenny).
Kunci dicocokkan sebagai kata utuh, peka huruf besar-kecil sesuai naskah.
"""
import re

# kata → (us, gb)
NAMES = {
    'Jaka': ('dʒˈɑːkə', 'dʒˈɑːkə'),
    "Jaka's": ('dʒˈɑːkəz', 'dʒˈɑːkəz'),
    'Mbah': ('əmbˈɑː', 'əmbˈɑː'),
    "Mbah's": ('əmbˈɑːz', 'əmbˈɑːz'),
    'Kedu': ('kədˈuː', 'kədˈuː'),
    'Temanggung': ('təmˈɑːŋɡʊŋ', 'təmˈɑːŋɡʊŋ'),
    "Temanggung's": ('təmˈɑːŋɡʊŋz', 'təmˈɑːŋɡʊŋz'),
    'Magelang': ('mˌɑːɡəlˈɑːŋ', 'mˌɑːɡəlˈɑːŋ'),
    'Wonosobo': ('wˌɑːnoʊsˈoʊboʊ', 'wˌɒnəʊsˈəʊbəʊ'),
    'Sindoro': ('sɪndˈoʊɹoʊ', 'sɪndˈəʊɹəʊ'),
    'Sumbing': ('sˈuːmbɪŋ', 'sˈuːmbɪŋ'),
    'Merapi': ('məɹˈɑːpi', 'məɹˈɑːpi'),
    'Merbabu': ('mɚbˈɑːbuː', 'mɜːbˈɑːbuː'),
    'Menoreh': ('mənˈoʊɹeɪ', 'mənˈəʊɹeɪ'),
    'Borobudur': ('bˌɔːɹoʊbuːdˈʊɹ', 'bˌɒɹəʊbuːdˈʊə'),
    "Borobudur's": ('bˌɔːɹoʊbuːdˈʊɹz', 'bˌɒɹəʊbuːdˈʊəz'),
    'Mendut': ('məndˈuːt', 'məndˈuːt'),
    'Pawon': ('pˈɑːwɑːn', 'pˈɑːwɒn'),
    'Tidar': ('tˈiːdɑːɹ', 'tˈiːdɑː'),
    'Dieng': ('diˈɛŋ', 'diˈɛŋ'),
    'Arjuna': ('ɑːɹdʒˈuːnə', 'ɑːdʒˈuːnə'),
    'Telaga': ('təlˈɑːɡə', 'təlˈɑːɡə'),
    'Warna': ('wˈɑːɹnə', 'wˈɑːnə'),
    'mie': ('mˈiː', 'mˈiː'),
    'ongklok': ('ˈɑːŋklɑːk', 'ˈɒŋklɒk'),
    'carica': ('tʃɑːɹˈiːkə', 'tʃɑːɹˈiːkə'),
    'Lengger': ('lˈɛŋɡɚ', 'lˈɛŋɡə'),
    'ruwatan': ('ɹuːwˈɑːtɑːn', 'ɹuːwˈɑːtɑːn'),
    'kentongan': ('kəntˈɑːŋɑːn', 'kəntˈɒŋɑːn'),
    'jaran': ('dʒˈɑːɹɑːn', 'dʒˈɑːɹɑːn'),
    'kepang': ('kəpˈɑːŋ', 'kəpˈɑːŋ'),
    'Topeng': ('tˈoʊpɛŋ', 'tˈəʊpɛŋ'),
    'Ireng': ('ˈiːɹəŋ', 'ˈiːɹəŋ'),
    'Liyangan': ('lɪjˈɑːŋɑːn', 'lɪjˈɑːŋɑːn'),
    'Pringapus': ('pɹɪŋˈɑːpʊs', 'pɹɪŋˈɑːpʊs'),
    'Gondosuli': ('ɡˌɑːndoʊsˈuːli', 'ɡˌɒndəʊsˈuːli'),
    'uceng': ('ˈuːtʃɛŋ', 'ˈuːtʃɛŋ'),
    'Parakan': ('pəɹˈɑːkɑːn', 'pəɹˈɑːkɑːn'),
    "Parakan's": ('pəɹˈɑːkɑːnz', 'pəɹˈɑːkɑːnz'),
    'Nandi': ('nˈɑːndi', 'nˈɑːndi'),
    'kupat': ('kˈuːpɑːt', 'kˈuːpɑːt'),
    'tahu': ('tˈɑːhuː', 'tˈɑːhuː'),
    'rigen': ('ɹˈiːɡɛn', 'ɹˈiːɡɛn'),
    'gamelan': ('ɡˈæməlæn', 'ɡˈæməlæn'),
    'Kabut': ('kˈɑːbʊt', 'kˈɑːbʊt'),
    'Brrr': ('bɹˈɜːɹ', 'bɹˈɜː'),
}

_PATTERN = re.compile(r"(?<![\w'])(" + '|'.join(sorted(map(re.escape, NAMES), key=len, reverse=True)) + r")(?![\w'])")


def apply(text: str, dialect: str) -> str:
    idx = 0 if dialect == 'us' else 1
    return _PATTERN.sub(lambda m: '[[' + NAMES[m.group(1)][idx] + ']]', text)


_PUNCT = {',': ', ', ';': ', ', ':': ', ', '.': '.', '!': '!', '?': '?'}


def phonemize(text: str, espeak, espeak_voice: str, dialect: str) -> list[str]:
    """Satu kalimat → daftar fonem Piper. Nama di NAMES memakai IPA; sisanya espeak.

    Berbeda dengan blok [[…]] bawaan Piper, tanda baca dan spasi tepat setelah
    nama tetap terbawa (mis. "Temanggung, my boy" tetap berjeda koma).
    """
    idx = 0 if dialect == 'us' else 1
    out: list[str] = []
    pos = 0
    pieces = []
    for m in _PATTERN.finditer(text):
        pieces.append(('text', text[pos:m.start()]))
        pieces.append(('name', m.group(1)))
        pos = m.end()
    pieces.append(('text', text[pos:]))
    for kind, piece in pieces:
        if kind == 'name':
            if out and out[-1] not in (' ',):
                out.append(' ')
            out.extend(NAMES[piece][idx])
            continue
        if not piece:
            continue
        lead = re.match(r'^[\s"“”‘’,;:.!?…]*', piece).group()
        body = piece[len(lead):]
        marks = [c for c in lead.replace('…', '.') if c in _PUNCT]
        if marks and out:
            mark = marks[0]
            out.extend(_PUNCT[mark])
            if mark in '.!?' and body:
                out.append(' ')
        elif lead.strip(' \"“”‘’') == '' and lead and out and body:
            out.append(' ')
        if body.strip(' "“”‘’'):
            sents = espeak.phonemize(espeak_voice, body)
            for k, sent in enumerate(sents):
                if k and out and out[-1] != ' ':
                    out.append(' ')
                out.extend(sent)
    while out and out[-1] == ' ':
        out.pop()
    return out
