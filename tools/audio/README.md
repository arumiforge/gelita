# Generator audio GELITA

Skrip yang membangkitkan seluruh audio bawaan permainan: musik gamelan, efek suara, dan rekaman narasi Indonesia–Inggris. Isi dan keputusan desainnya dijelaskan di [`docs/audio.md`](../../docs/audio.md). Folder ini hanya perlu dipakai bila audio ingin **dibangkitkan ulang**, misalnya setelah teks naskah berubah. Permainan dan servernya tidak membutuhkannya.

| Skrip | Keluaran |
|---|---|
| `music.py` → `master.py music` | `public/assets/audio/music/{map,region,challenge}.mp3` |
| `sfx.py` → `master.py sfx` | `public/assets/audio/sfx/{click,correct,wrong,lock,shard,region-done}.mp3` |
| `narrate.py` → `publish.py` | `public/assets/audio/narasi/{id,en}/{kode}.mp3` + `narasi/produksi.json` |

Musik dan efek suara disintesis dari nol (tanpa sampel rekaman) oleh `gamelan.py` dan `compose.py`, jadi tidak butuh model apa pun. Narasi memakai model TTS yang diunduh `setup-models.sh`.

## Persiapan

Linux/WSL dengan Python 3.11+, Node 20+, PHP CLI, `ffmpeg`, `curl`, `unzip`, dan `zstd`.

```bash
cd tools/audio
python3 -m venv .venv && . .venv/bin/activate
pip install -r requirements.txt
bash setup-models.sh            # ±600 MB ke tools/audio/models/ dan node_modules/ (tidak di-commit)
```

`setup-models.sh` memeriksa sha256 setiap model, jadi model yang dipakai sama persis dengan yang membangkitkan audio di repositori. Musik dan efek suara dibangkitkan ulang identik (seed tetap per berkas; byte demi byte bila versi ffmpeg/LAME-nya sama). Narasi akan mirip tetapi tidak identik, karena sintesis VITS bersifat stokastis.

| Model | Dipakai untuk | Sumber | Lisensi |
|---|---|---|---|
| Mimic 3 `jv_ID/google-gmu_low` (VITS, 39 penutur) | narasi Indonesia | MycroftAI/mimic3-voices (Git LFS) | CC BY-SA 4.0, dari OpenSLR 41 (Google & UGM) |
| Piper `en_US-kristin-medium` | Narator (EN) | modul Go `piper-tts-go/piper-voice-kristin` | domain publik (LibriVox) |
| Piper `en_GB-jenny_dioco-medium` | Jaka (EN) | modul Go `piper-tts-go/piper-voice-jenny` | lisensi Jenny (Dioco): klip hasil bebas dipakai, atribusi dianjurkan |
| Piper `en_US-joe-medium` | Mbah Kedu (EN) | PyPI `joe-us-piper-voice` | CC0 |
| Whisper small (ONNX, q8) | QA kejelasan ucapan | npm `sts-whisper-small` | MIT |

## Menjalankan

```bash
# musik & efek suara (±1 menit)
python music.py && python master.py music
python sfx.py && python master.py sfx

# narasi: sintesis → efek tokoh → pilih kandidat terbaik via Whisper (±1–1,5 jam per bahasa)
python narrate.py --locale id --n 3
python narrate.py --locale en --n 1
# ulangi baris yang CER-nya tinggi (lihat out/narasi/{locale}/report.json) dengan lebih banyak kandidat
python narrate.py --locale en --codes dialog-wonosobo-10,petunjuk-tmg-4-07 --n 3
python publish.py                                  # MP3 + produksi.json

# hanya beberapa baris (mis. setelah teksnya disunting)
python narrate.py --locale id --codes dialog-magelang-09,peta-02 --n 3
python publish.py --locale id
```

Baris naskah dibaca langsung dari `app/Database/Seeds/data/story.php` dan petunjuk arena `cari` dari `docs/bank-soal/data/*.php` lewat `export_lines.php`, dengan aturan penomoran yang sama dengan `NarrationImporter`. Bila teks disunting lewat panel admin, ubah dulu naskahnya di repositori (lihat `docs/naskah-cerita.md`), baru bangkitkan ulang.

Setelah `publish.py`, pasang rekamannya di server seperti biasa: `php spark gelita:narration:import`, lalu setujui di **Konten → Narasi**.

## Cara kerja narasi

1. **Teks → fonem.**
   - Indonesia: `id_g2p.py` memetakan ejaan Indonesia ke inventaris fonem model Jawa (Epitran `jav-Latn`). Huruf *e* pepet/taling diatur leksikon kecil, misalnya *désa*, *meréka*, *lentéra*, *topèng*, *Maséhi*. Angka dieja, dan tidak ada glotal di awal kata (terdengar seperti "k" bagi pendengar). Kata homograf Jawa yang *a*-akhirnya dibaca [ɔ] (*apa* → "opo", *para* → "poro") diberi *h* penutup suku kata.
   - Inggris: `en_lexicon.py` memberi pelafalan IPA untuk nama-nama Jawa (*Magelang*, *Gondosuli*, *rigen*, *Mbah*, …). Tanpa leksikon ini, espeak membaca "MAYJ-lang".
2. **Sintesis per kalimat** dengan jeda antarkalimat menurut tanda baca dan tokoh. Kalimat tanya berbahasa Indonesia diberi intonasi naik, karena model Jawa tidak punya token "?".
3. **Efek tokoh** (`voicefx.py`, Praat): pitch, formant, rentang nada, dan tempo per tokoh dan pose.
   - Jaka: suara anak.
   - Mbah Kedu: suara lebih rendah dengan getar lansia. Pada pose `weak` getarnya lebih kuat dan napasnya berdesah.
   - Narasi Indonesia dipercepat 3–8% (Praat *Lengthen*, nada tetap), kecuali pose `weak`.
   - Loudness −16 LUFS per berkas (Mbah Kedu −16,5; beberapa pose sedikit berbeda), puncak sejati ≤ −1 dBTP lewat limiter look-ahead 2 ms, hening 0,3 detik di awal dan akhir.
4. **QA.** Setiap kandidat ditranskripsi Whisper small, lalu kandidat dengan CER terendah disimpan. CER dihitung setelah teks dinormalkan: huruf kecil, tanpa tanda baca, dan angka dieja ("15" sama dengan "lima belas"). Laporan per baris ada di `out/narasi/{locale}/report.json`. Hasil QA rekaman yang ada di repositori tersimpan di `qa-narasi.json`.

Pengaturan pemeran (penutur, pitch, tempo, pose) ada di `narrate.py`: `CAST`, `POSE`, dan `TEMPO`.
