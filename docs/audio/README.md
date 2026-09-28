# `docs/audio/` — pembuat audio GELITA

Semua audio permainan di `public/assets/audio/` dibuat oleh skrip di folder ini, tanpa layanan daring dan tanpa rekaman atau sampel pihak lain:

| Keluaran | Skrip | Isi |
|---|---|---|
| `music/{map,region,challenge}.mp3` | `build_music_sfx.py` | Musik latar bernuansa gamelan, loop mulus |
| `sfx/{click,correct,wrong,lock,shard,region-done}.mp3` | `build_music_sfx.py` | Efek suara |
| `narasi/{id,en}/{kode}.mp3` + `_produksi.json` | `build_narration.py` | 111 baris narasi per bahasa (text-to-speech) |

Narasi TTS adalah **pengisi sementara**: jelas terdengar, tetapi bukan akting pengisi suara. Setiap berkas dapat diganti rekaman manusia dengan nama yang sama (lihat [`docs/naskah-cerita.md`](../naskah-cerita.md)); impor berikutnya otomatis mencatatnya sebagai `own_recording`.

## Menjalankan

```bash
pip install -r docs/audio/requirements.txt       # Python 3.10+, juga butuh `php` di PATH
python3 docs/audio/build_music_sfx.py            # ±5–10 menit; --only map,click untuk sebagian
python3 docs/audio/build_narration.py            # ±30–60 menit di CPU 4 inti
python3 docs/audio/check_audio.py                # periksa format, kenyaringan, jeda, manifest, loop
python3 docs/audio/check_audio.py --wer          # + uji keterbacaan Whisper (lambat, unduh ±560 MB)
```

Lalu commit berkasnya dan jalankan `php spark gelita:narration:import` di server (narasi saja; musik dan efek suara langsung dipakai `core/audio.js`).

`build_narration.py` hanya membuat ulang baris yang teks atau profil suaranya berubah (`--force` untuk semua, `--locale en`, `--only intro-01,misi-tmg-1`), jadi rekaman lain dan persetujuannya di panel tidak tersentuh. Model disimpan di `~/.cache/gelita-audio/`, tidak di repositori.

## Sumber teks

`export-lines.php` membaca teks langsung dari sumbernya, dengan urutan dan kode berkas yang sama dengan `App\Libraries\NarrationImporter`:

| Kelompok | Sumber | Baris |
|---|---|---:|
| Naskah cerita | `app/Database/Seeds/data/story.php` (= `docs/naskah-cerita.md`) | 88 |
| Kartu misi `misi-{node}` | deskripsi tantangan (atau instruksi) di `docs/bank-soal/data/*.php` | 15 |
| Petunjuk arena cari `petunjuk-{node}-NN` | butir `find_object` bukan jebakan di `docs/bank-soal/data/temanggung.php` | 8 |

Teks yang disunting admin langsung di panel tidak ikut. Ubah berkas sumbernya dulu, lalu buat ulang rekamannya.

Sebelum dibacakan, teks dirapikan (`speakable()`): angka dieja (`2.672` → "dua ribu enam ratus tujuh puluh dua", `832 CE` → "eight thirty-two C E"), tanda kutip dibuang, tanda pisah panjang menjadi jeda koma, dan `Sindoro–Sumbing` menjadi "Sindoro dan Sumbing". Transkrip di basis data tetap teks aslinya.

## Profil suara

Mesin: **Kokoro v1.0** (82M parameter, [hexgrad/Kokoro-82M](https://huggingface.co/hexgrad/Kokoro-82M), **Apache-2.0**) lewat [sherpa-onnx](https://github.com/k2-fsa/sherpa-onnx), dari rilis `tts-models/kokoro-multi-lang-v1_0`. Bahasa Indonesia tidak termasuk bahasa latih Kokoro, jadi teks Indonesia diubah menjadi fonem oleh espeak-ng (`lang='id'`) lalu dibacakan suara Kokoro. Suara yang aksen bawaannya paling cocok dipilih lewat uji Whisper (di bawah).

| Tokoh | Arahan ([naskah](../naskah-cerita.md#tokoh-dan-arahan-suara)) | ID | EN |
|---|---|---|---|
| Narator | hangat, pendongeng | `jf_gongitsune`, tempo 0,95, nada −1,5 st | `bf_emma`, tempo 0,95 |
| Jaka | anak laki-laki ±11 tahun, bersemangat | `jf_nezumi`, nada +1 st | `af_heart`, nada +3 st |
| Mbah Kedu | kakek lembut, pelan | `bm_george`, tempo 0,88, nada −1,5 st | `bm_george`, tempo 0,88, nada −1,5 st |

- **Geser nada** ala pita: baris disintesis lebih lambat lalu diputar lebih cepat (atau sebaliknya), jadi formant ikut bergeser. Nada naik membuat suara terdengar lebih muda, nada turun lebih tua.
- **Pose** menggeser sedikit tempo dan nada (`happy`, `afraid` lebih cepat dan tinggi; `sad`, `weak` lebih pelan dan rendah). Mbah Kedu berpose `weak` diberi getaran volume 5 Hz.
- **Format**: MP3 mono 44,1 kHz CBR 80 kbps, −16 LUFS (BS.1770), puncak ≤ −1,5 dBTP, hening 0,3 detik di awal dan akhir.
- Mengganti suara cukup dengan mengubah `VOICES` di `build_narration.py` lalu menjalankannya lagi: hanya tokoh itu yang dibuat ulang.

### Mengapa bukan Piper `id_ID-news_tts`

Suara Piper Indonesia terdengar paling alami (WER 0,07), tetapi lisensinya tidak jelas. Model card-nya menyebut dataset korpus Malayalam (salah tempel dari model lain), dan model itu di-finetune dari suara `lessac`, yang datanya berlisensi penggunaan terbatas. Karena audio ini ikut didistribusikan di repositori, dipilih Kokoro yang berlisensi Apache-2.0.

### Uji keterbacaan

Delapan kalimat naskah ID dari berbagai konteks ditranskripsi Whisper turbo. Angkanya WER rata-rata dan jumlah kalimat yang terdeteksi sebagai bahasa Indonesia; "Mbah" dan "Le" hampir selalu ditulis lain, jadi WER tidak pernah 0.

| Suara (ID) | WER | Terdeteksi `id` | F0 |
|---|---:|---:|---:|
| `bm_george` tempo 0,9 | 0,054 | 8/8 | 143 Hz |
| `am_santa` tempo 0,9 | 0,056 | 6/8 | 184 Hz |
| `jf_gongitsune` | 0,080 | 8/8 | 238 Hz |
| `jf_nezumi` | 0,081 | 8/8 | 246 Hz |
| `em_alex` | 0,087 | 7/8 | 135 Hz |
| `hf_alpha` | 0,101 | 7/8 | 213 Hz |
| `af_nova` / `bf_alice` / `bf_lily` (suara perempuan Inggris) | 0,17–0,45 | 2–6/8 | — |

Hasil uji seluruh 222 berkas: `check_audio.py --wer` (lihat bagian Hasil pemeriksaan).

## Musik dan efek suara

`build_music_sfx.py` membangun setiap bunyi dari parsial sinus, derau, dan selubung, dengan RNG ber-seed tetap: hasilnya bebas lisensi dan sama setiap kali dijalankan.

- **Instrumen**:
  - saron, demung, peking: bilah logam dengan parsial inharmonik 1 : 2,76 : 4,95 dan redaman tangan saat nada berganti
  - bonang, kenong, kethuk: pencon dengan ombak (pelayangan) halus
  - kempul dan gong ageng 47,5 Hz: ombak lambat
  - kendang: dhung, tung, tak, ket
  - gender: bilah lembut
  - suling: vibrato dan desah napas
- **Laras**: slendro (sen 0, 234, 474, 709, 954) untuk `map` dan `challenge`; pelog pathet nem untuk `region`.
- **Bentuk**: lancaran 16 ketukan. Kethuk di ketukan ganjil, kenong tiap 4 ketukan, kempul di 6, 10, dan 14, gong di 16.
- **Loop**: dirender melingkar. Gaung nada dan reverb di ujung loop dijumlahkan ke awalnya, dan berkas dimulai tepat pada pukulan gong, sehingga sambungannya mulus.

| Berkas | Isi | Durasi | Kenyaringan |
|---|---|---:|---:|
| `music/map.mp3` | slendro 60 BPM, suling di gongan 2–3 | 64 dtk | −22 LUFS |
| `music/region.mp3` | pelog nem 84 BPM, kendang, peking | 46 dtk | −21 LUFS |
| `music/challenge.mp3` | gender redup tanpa melodi atas, agar tidak mengganggu membaca | 58 dtk | −25 LUFS |
| `sfx/click.mp3` | ketuk kayu | 0,12 dtk | puncak −9 dBFS |
| `sfx/correct.mp3` | bonang naik 5 → 1' | 0,9 dtk | −4 dBFS |
| `sfx/wrong.mp3` | kempul turun, lembut | 0,6 dtk | −5 dBFS |
| `sfx/lock.mp3` | klak gerendel logam | 0,36 dtk | −6 dBFS |
| `sfx/shard.mp3` | kilau peking naik | 1,0 dtk | −4 dBFS |
| `sfx/region-done.mp3` | gong + run bonang + kenong | 3,0 dtk | −3 dBFS |

Musik dibuat lebih pelan daripada narasi (−16 LUFS), supaya suara tokoh tetap jelas walau keduanya berbunyi bersamaan.

## Lisensi

- Musik, efek suara, dan skrip di folder ini adalah bagian proyek GELITA (lisensi repositori).
- Narasi dibuat dengan model Kokoro v1.0 (Apache-2.0) dan espeak-ng sebagai pengubah teks ke fonem (GPL-3.0, hanya dijalankan, tidak didistribusikan). Keluaran audio tidak memuat kode keduanya.
- Whisper (MIT) hanya dipakai untuk pemeriksaan, tidak untuk membuat audio.
