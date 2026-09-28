"""G2P bahasa Indonesia → inventaris fonem model VITS Jawa (Epitran jav-Latn).

Inventaris model: a b̤ d̪̤ d͡ e f h i j k l m n o p q r s t t͡ u v w z è é ŋ ɖ̤ ɡ̤ ɲ ʃ ʈ ʒ̤ ʔ
('e' polos = pepet, 'é'/'è' = taling), tanda baca ',' dan '.'.

Huruf e: pepet secara bawaan; kata ber-e taling ditulis ulang di LEXICON
memakai é (tertutup) / è (terbuka).
"""
from __future__ import annotations

import re
import unicodedata

# Ejaan ulang: pepet = e, taling = é/è. Hanya kata yang menyimpang dari aturan bawaan.
LEXICON = {
    'aneh': 'anèh', 'bedakan': 'bèdakan', 'berbeda': 'berbèda', 'membedakan': 'membèdakan', 'beda': 'bèda',
    'besok': 'bésok', 'boleh': 'bolèh', 'oleh': 'olèh', 'daerah': 'daérah', 'desa': 'désa', 'desanya': 'désanya',
    'dieng': 'dièng', 'dongeng': 'dongèng', 'geografis': 'géografis', 'hebat': 'hèbat', 'ide': 'idé',
    'kakek': 'kakèk', 'nenek': 'nènèk', 'kompleks': 'komplèks', 'kuliner': 'kulinèr', 'le': 'lè',
    'lengger': 'lènggèr', 'lentera': 'lentéra', 'lenteraku': 'lentéraku', 'lenteranya': 'lentéranya',
    'lewat': 'léwat', 'lewatkan': 'léwatkan', 'melewatkan': 'meléwatkan', 'masehi': 'maséhi',
    'material': 'matérial', 'menoreh': 'menorèh', 'mereka': 'meréka', 'mesin': 'mésin', 'modern': 'modèrn',
    'panel': 'panèl', 'pendek': 'pèndèk', 'pepaya': 'pépaya', 'ponsel': 'ponsèl', 'relief': 'rèlièf',
    'reliefnya': 'rèlièfnya', 'rigen': 'rigèn', 'teks': 'tèks', 'topeng': 'topèng', 'uceng': 'ucèng',
    'mie': 'mi', 'buddha': 'buda', 'sore': 'soré', 'enak': 'énak', 'ekor': 'ékor',
    'gimbal': 'gimbal', 'carica': 'carica', 'bebek': 'bèbèk', 'lele': 'lélé', 'kafe': 'kafé',
    'uhh': 'uuh', 'brrr': 'brrr', 'ohh': 'oh',
}

VOWELS = set('aiueoéè')

# Kata yang 'a' akhirnya dibaca [ɔ] oleh model Jawa (homograf kata Jawa: apa → "opo").
# Diberi 'h' agar suku akhirnya tertutup dan vokalnya tetap [a]. Didapat dari uji ASR.
H_WORDS = frozenset({'apa', 'para', 'kala', 'kata', 'bagaimana', 'mana', 'lentera', 'sana', 'daya', 'cahaya'})
ANGKA = ['nol', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan']


def number_words(n: int) -> str:
    if n < 10:
        return ANGKA[n]
    if n == 10:
        return 'sepuluh'
    if n == 11:
        return 'sebelas'
    if n < 20:
        return ANGKA[n - 10] + ' belas'
    if n < 100:
        t, u = divmod(n, 10)
        return ANGKA[t] + ' puluh' + (' ' + ANGKA[u] if u else '')
    if n < 1000:
        h, r = divmod(n, 100)
        head = 'seratus' if h == 1 else ANGKA[h] + ' ratus'
        return head + (' ' + number_words(r) if r else '')
    if n < 1_000_000:
        k, r = divmod(n, 1000)
        head = 'seribu' if k == 1 else number_words(k) + ' ribu'
        return head + (' ' + number_words(r) if r else '')
    raise ValueError(n)


def normalize(text: str) -> str:
    text = unicodedata.normalize('NFC', text)
    # 2.672 → 2672 (pemisah ribuan), lalu angka → kata
    text = re.sub(r'(\d)\.(\d{3})\b', r'\1\2', text)
    text = re.sub(r'\d+', lambda m: number_words(int(m.group())), text)
    text = text.replace('–', ', ').replace('—', ', ')
    text = re.sub(r'["“”‘’]', '', text)
    return text


def split_sentences(text: str) -> list[tuple[str, str]]:
    """[(kalimat, penutup)] — penutup: '.', '!', '?', '…' (elipsis), atau ''."""
    text = normalize(text)
    parts = re.findall(r'[^.!?…]+(?:\.\.\.|…|[.!?])*', text)
    out = []
    for p in parts:
        p = p.strip()
        if not p:
            continue
        m = re.search(r'(\.\.\.|…|[.!?]+)$', p)
        end = ''
        if m:
            end = m.group(1)
            p = p[: m.start()].strip()
            end = '…' if end in ('...', '…') else end[-1]
        if p:
            out.append((p, end))
    return out


# Fonem langsung untuk kata tertentu (menang atas semua aturan di bawah), dari uji ASR:
# 'ada' tanpa pengaman terdengar "odo"; 'Mbah' paling jelas sebagai "embah" [əmbah].
OVERRIDES: dict[str, list[str]] = {
    'ada': ['ʔ', 'a', 'd̪̤', 'a', 'h'],
    'mbah': ['e', 'm', 'b̤', 'a', 'h'],
}


def word_to_phonemes(word: str, final_a: str = 'plain', d_style: str = 'dental', glottal_onset: bool = False,
                     c_style: str = 'plain', h_words: frozenset = H_WORDS, overrides: dict | None = None) -> list[str]:
    """Satu kata (huruf kecil, tanpa tanda baca) → daftar grafem fonem model."""
    table = OVERRIDES if overrides is None else overrides
    if word in table:
        return list(table[word])
    w = LEXICON.get(word, word)
    ph: list[str] = []
    i = 0
    n = len(w)
    d_ph = 'd̪̤' if d_style == 'dental' else 'ɖ̤'

    def nxt(k: int = 1) -> str:
        return w[i + k] if i + k < n else ''

    while i < n:
        c = w[i]
        two = w[i:i + 2]
        three = w[i:i + 3]
        if two == 'ng':
            ph.append('ŋ')
            i += 2
            continue
        if two == 'ny':
            ph.append('ɲ')
            i += 2
            continue
        if two == 'sy':
            ph.append('ʃ')
            i += 2
            continue
        if two == 'kh':
            ph.append('h')
            i += 2
            continue
        if c == 'c':
            ph += ['t͡', 'ʃ'] if c_style == 'tie' else (['t', 'ʃ'] if c_style == 'plain' else ['ʃ'])
        elif c == 'j':
            ph += ['d͡', 'ʒ̤']
        elif c == 'b':
            ph.append('b̤')
        elif c == 'd':
            ph.append(d_ph)
        elif c == 'g':
            ph.append('ɡ̤')
        elif c == 'y':
            ph.append('j')
        elif c == 'v':
            ph.append('f')
        elif c == 'x':
            ph += ['k', 's']
        elif c == 'q':
            ph.append('k')
        elif c == 'k':
            after = nxt()
            if glottal_onset and (after == '' or (after not in VOWELS and after not in 'lrsh')):
                ph.append('ʔ')      # k di koda suku kata: tidak → tidaʔ
            else:
                ph.append('k')      # model Jawa sendiri membaca k-final sebagai [ʔ]
        elif c in VOWELS or c in 'bcdfghjklmnpqrstvwxyz':
            ph.append(c)
        i += 1
        _ = three

    # ʔ di antara dua vokal kembar (maaf, keemasan, kelupaan)
    if glottal_onset:
        out: list[str] = []
        for p in ph:
            if out and p in VOWELS and out[-1] == p:
                out.append('ʔ')
            out.append(p)
        ph = out

    # awal kata bervokal → ʔ (aturan post Epitran jav-Latn yang dipakai saat pelatihan)
    if glottal_onset and ph and ph[0] in VOWELS:
        ph.insert(0, 'ʔ')

    # 'a' di akhir kata: model Jawa cenderung membacanya [ɔ]
    if ph and ph[-1] == 'a' and word in h_words:
        ph.append('h')
    elif ph and ph[-1] == 'a' and final_a != 'plain':
        ph.append('h' if final_a == 'h' else 'ʔ')
    return ph


TOKEN_RE = re.compile(r"[a-zA-ZÀ-ÿ]+|[,;:]")


def sentence_to_words(sentence: str, end: str, **kw) -> list[list[str]]:
    """Kalimat → daftar kata fonem; tanda baca menempel ke kata sebelumnya (seperti Epitran)."""
    words: list[list[str]] = []
    for tok in TOKEN_RE.findall(sentence.replace('-', ' ')):
        if tok in ',;:':
            if words:
                words[-1] = words[-1] + [',']
            continue
        words.append(word_to_phonemes(tok.lower(), **kw))
    if words:
        words[-1] = words[-1] + ['.']
    return words
