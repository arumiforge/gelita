# Audio GELITA

> **14 November 2026:** the generator `tools/audio/` was removed from the repository (owner: audio is now produced outside the repo). The MP3 files under `public/assets/audio/` stay. Everything below about `tools/audio/` describes how those files were made and is history.
>
> **14 November 2026, narration:** voice actors (real voices) stay the first choice. ElevenLabs is the backup, and no other synthetic voice is used (owner; `docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md`). The backup takes are made in `ai-studio/elevenlabs-voice/` from `lines.csv` (`php spark gelita:narration:list`) and must be installed with a `produksi.json` manifest so they are recorded as `tts`; see that folder's `README.md`. None exist yet.

Audio bawaan permainan yang ikut di repositori: **3 musik gamelan** (plus 12 scene and account tracks since 19 October 2026) dan **6 efek suara**, dibangkitkan oleh [`tools/audio/`](../tools/audio/README.md). Narasi (± 187 rekaman × 2 bahasa since research revision 10, part 6; 103 before) tidak lagi ikut di repositori: rekaman suara sintetis lama dihapus (17 Oktober 2026) dan diganti rekaman pengisi suara (lihat *Voice-over narration* di bawah).

- Musik dan efek suara disintesis dari nol, tanpa sampel rekaman, dengan penalaan pelog–slendro. Gendingnya komposisi orisinal.
- Narasi direkam pengisi suara (suara asli, bukan sintetis) dan diunggah di panel **Rekaman narasi**. Bagian tentang suara sintetis di bawah tinggal sebagai catatan sejarah.

| Kelompok | Lokasi | Isi | Dipakai oleh |
|---|---|---|---|
| Musik | `public/assets/audio/music/` | `map`, `region`, `challenge` (MP3 stereo 128 kbps, loop); since 19 October 2026 also `welcome`, `opening`, `ending`, and `kenal-`/`dialog-`/`tuntas-{wilayah}` (96 kbps, see *Scene and account music*) | `core/audio.js` → `Sfx.music()` |
| Efek suara | `public/assets/audio/sfx/` | `click`, `correct`, `wrong`, `lock`, `shard`, `region-done` | `core/audio.js` → `Sfx.play()` |
| Narasi | `public/assets/audio/narasi/{id,en}/` (kosong di repositori) | folder impor rekaman pengisi suara: 112 baris naskah, 12 petunjuk Cari Objek babak 1, 24 petunjuk *Periksa Unggahan*, 24 situasi Skenario Digital, 15 instruksi tantangan per bahasa | `gelita:narration:import` → `dialogues` / `challenge_items` |

## Musik gamelan

Tiga gending pendek yang berulang tanpa jeda. Karakter bunyinya gamelan Jawa Tengah: saron, demung, peking, bonang, slenthem, kenong, kethuk, kempul, gong ageng, dan kendang, ditambah gender, gambang, siter, dan suling untuk gending yang lebih lembut.

| Berkas | Suasana | Laras · bentuk · tempo | Ricikan | Loop |
|---|---|---|---|---|
| `map.mp3` | megah dan mengajak menjelajah; diputar di Peta Kedu | slendro manyura · ladrang (2 gongan × 32 ketukan) · irama tanggung, 66 ketukan/menit | demung, 2 saron barung, peking, bonang barung & panerus (mipil, gembyang), slenthem, kenong, kethuk, kempul, gong ageng, kendang, suling di gongan kedua | 58,2 s |
| `region.mp3` | tenang, suasana desa di lereng gunung; diputar di peta wilayah | pelog nem · ketawang (3 gongan × 16 ketukan) · irama dadi, 42 ketukan/menit | gender, gambang, siter, saron, slenthem, kenong, kethuk, kempul, gong, kendang lirih, suling | 68,6 s |
| `challenge.mp3` | hening agar siswa tetap fokus membaca; diputar selama tantangan | slendro sanga · ketawang, balungan nibani (4 gongan) · 52 ketukan/menit | slenthem, demung lirih, gender, kenong, kethuk, kempul, gong; tanpa kendang dan suling | 73,8 s |

Catatan teknis:

- **Laras.**
  - Pelog dan slendro memakai jarak sen khas gamelan Jawa dengan oktaf sedikit melebar (1212 sen).
  - Tiap bilah diberi selisih penalaan kecil, dan tiap ricikan sedikit berbeda dari ricikan lain. Hasilnya adalah *ombak* (denyut) seperti gamelan sungguhan yang punya embat sendiri.
- **Garap.**
  - Bonang ber-*mipil* dan peking ber-*nacah* mendahului balungan dan jatuh di ketukan kuat.
  - Gender dan gambang berkelok menuju *seleh*.
  - Kenong, kempul, kethuk, dan gong menandai struktur ladrang/ketawang.
  - Gong ageng (±41 Hz) punya ombak ±2,4 Hz dan dengung parsial kedua yang mengembang.
- **Loop mulus.** Ekor gema dan nada di akhir putaran dilipat ke awal berkas. Pukulan gong jatuh tepat di titik sambung. Header LAME menyimpan jeda encoder, sehingga panjang hasil dekode sama persis dengan panjang loop (diperiksa dengan ffmpeg).
- **Kenyaringan.** Musik sengaja lebih pelan daripada narasi (−16 LUFS), karena narasi Jaka dan Mbah Kedu diputar di atas musik Peta Kedu dan petunjuk arena `cari` di atas musik tantangan. `core/audio.js` juga memakai volume Howler dua kali (per bunyi × global, bawaan 0,8 × 0,8), sehingga musik dan efek terdengar ±4 dB lebih pelan daripada angka di bawah. Narasi tidak terkena pengurangan ini. Hasilnya, musik tetap ±8–9 LU di bawah narasi. Kompresor lembut meredam pukulan gong.

  | Berkas (MP3 didekode) | Kenyaringan terpadu | Puncak momentari | Puncak sejati |
  |---|---|---|---|
  | `map` | −20,4 LUFS | −15,6 LUFS | −5,8 dBTP |
  | `region` | −20,9 LUFS | −16,6 LUFS | −6,8 dBTP |
  | `challenge` | −21,9 LUFS | −16,4 LUFS | −7,2 dBTP |

## Scene and account music (19 October 2026)

Owner request: Javanese gamelan under every narrated scene and on the home and account pages, matching the mood and never covering the voice. Interview answers: one track per scene type **and per region**, synthesized now (a recording can replace any file under the same name), music down to **about 20 %** while a voice speaks, and the mission card plays the challenge music. Jaka's story bar on Peta Kedu keeps the map music.

The twelve gending are in `tools/audio/music_scenes.py` and use the same instruments, garap, and mastering as the three above (`python music.py welcome …`, `python master.py music --only=…`).

| File | Screen | Mood | Laras · form · tempo | Loop |
|---|---|---|---|---|
| `welcome` | `/`, `/mulai`, `/masuk`, `/daftar/1–3`, `/ganti-sandi`, `/gerbang` | gentle welcome | slendro manyura · ketawang · 48 | 60 s |
| `opening` | `/intro` (the Kabut Lupa) | misty, mysterious; no kendang | pelog barang · ketawang nibani · 36 | 107 s |
| `ending` | `/penutup` | warm, bright | slendro manyura · ladrang · 60 | 64 s |
| `kenal-{wilayah}` | Kenali overlay on Peta Kedu | calm storytelling | Temanggung slendro sanga 40, Magelang pelog nem 36 (deep gong), Wonosobo pelog barang 34 (airy, no kendang) · ketawang | 72–85 s |
| `dialog-{wilayah}` | `/dialog/{code}` | light, on the way | same laras · ladrang (Temanggung, Magelang) or ketawang (Wonosobo) · 54–58 | 66–71 s |
| `tuntas-{wilayah}` | `/tuntas/{code}` | festive | same laras · lancaran · 100–108 | 53–58 s |
| `challenge` (existing) | `/misi/…` and the challenge | quiet for reading | — | — |

- **Which track plays.** A page names its track in the layout section `music` (`<body data-music>`), started by `game.js` on the first tap. Peta Kedu keeps `map` and `game/map.js` switches to `kenal-{wilayah}` while the Kenali overlay is open. A new track crossfades in over 0.9 s.
- **Under the voice.** `core/audio.js` drops the music to 20 % of its level (0.35 s fade) whenever a narration player starts, and brings it back (0.9 s) only after the voice has been quiet for 1.2 s, so it does not pump between two narrated slides. This applies to every narration, Jaka's story bar on Peta Kedu included.
- **Loudness.** −20 to −21.5 LUFS before encoding, the calm Kenali and opening tracks the quietest. Under a voice they sit another 14 dB lower.
- **Package.** `welcome` and `opening` travel in the core group of the asset package; each region's three tracks in that region's group; `ending` with the ending.
- **Checks.** `tools/qa/music-check.mjs` (crossfade, ducking, release after a pause), `BackgroundMusicTest`.

## Efek suara

| Berkas | Bunyi | Durasi | Diputar saat |
|---|---|---|---|
| `click.mp3` | ketukan kayu gambang, pendek dan lembut | 0,13 s | tombol suara HUD (saat dinyalakan), keping puzzle, kata rumpang, kartu boleh |
| `correct.mp3` | tiga nada bonang naik (pelog 5–6–1́) + dentang peking | 0,9 s | jawaban benar (semua arena) |
| `wrong.mp3` | dua nada demung turun yang diredam, dengung kempul tipis; tidak menghukum | 0,9 s | jawaban salah |
| `lock.mp3` | "tuk-tuk" kethuk + gemerincing kecrek yang diredam | 0,5 s | wilayah, tantangan, atau Pustaka yang masih terkunci |
| `shard.mp3` | arpeggio peking & gender naik, kilau dentingan, kenong | 3,2 s | layar selesai: serpihan kembali |
| `region-done.mp3` | frasa suwuk bonang–saron ditutup gong ageng, kenong, dan kempul | 5,1 s | layar selesai tantangan penuntas wilayah |

`shard` dan `region-done` dipanggil `game/finished.js`. Ketiga musik dan keenam efek suara tercantum di **Kelengkapan aset** (`/admin/media/kelengkapan`, `App\Libraries\AssetChecklist::SOUNDS`).

## Narasi

### Pemeran suara

| Tokoh | Bahasa Indonesia | English | Arahan naskah yang dikejar |
|---|---|---|---|
| Narator | Mimic 3 `jv_ID/google-gmu_low` penutur #36 (perempuan), tanpa pengolahan nada | Piper `kristin` (perempuan, gaya buku audio LibriVox) | hangat dan tenang seperti pendongeng |
| Jaka | penutur #6 → median F0 ±276 Hz, formant ×1,07 | Piper `jenny` → F0 ±262 Hz, formant ×1,12 | anak laki-laki ±11 tahun, bersemangat |
| Mbah Kedu | penutur #38 (laki-laki) → F0 ±104 Hz, formant ×0,95, getar lansia ringan | Piper `joe` → F0 ±92 Hz, formant ×0,98, getar lansia | kakek Jawa, lembut, pelan, logat Jawa halus |

Pose baris naskah ikut mengatur suara:

- **Jaka**
  - `happy`: rentang nada lebar, sedikit lebih cepat.
  - `afraid`: lebih tinggi, bergetar.
  - `determined`: lebih tegas.
  - `sad`: lebih rendah, pelan, lebih lirih.
  - `bow`: tenang.
- **Mbah Kedu**
  - `smile`: sedikit lebih cerah.
  - `worried`: lebih rendah dan pelan.
  - `weak`: lambat, bergetar, desah napas tipis, lebih lirih.

Kalimat tanya berbahasa Indonesia diberi intonasi naik, dan kalimat seru rentang nada yang lebih lebar.

**Logat Jawa.** Narasi Indonesia dibacakan model suara bahasa Jawa (OpenSLR 41). Model TTS bahasa Indonesia yang terbuka, seperti Piper `id_ID-news_tts` atau MMS, tersimpan di Hugging Face yang tidak dapat diakses dari lingkungan pembuatan audio ini. Hasilnya bahasa Indonesia berlogat Jawa (*medok*): konsonan *b/d/g/j* "berat" khas Jawa, dan sebagian kata berakhiran *-a* condong ke [ɔ]. Untuk Mbah Kedu, sesuai arahan naskah ("logat Jawa halus"), dan untuk Jaka, anak Kedu, logat ini justru cocok. Kata yang paling mudah tertukar dengan kata Jawa dikoreksi di `tools/audio/id_g2p.py`, yaitu *apa* ("opo"), *para* ("poro"), *ada* ("odo"), dan *sana* ("sono"). Sapaan *Mbah* dilafalkan [əmbah], seperti lazimnya diucapkan.

**Nama Jawa di narasi Inggris** dilafalkan dengan IPA dari `tools/audio/en_lexicon.py`: *Temanggung* /təˈmɑːŋɡʊŋ/, *Magelang* /mɑːɡəˈlɑːŋ/, *Mbah* /əmˈbɑː/, dan seterusnya.

### Spesifikasi berkas

- MP3 mono 44,1 kHz, 48 kbps CBR dengan header Xing, sehingga `MediaStore` membaca durasinya. Sampai Oktober 2026 narasi dikodekan 96 kbps (anjuran [`naskah-cerita.md`](naskah-cerita.md) 64–96 kbps dan [`08_DEPLOYMENT.md`](08_DEPLOYMENT.md) 96–128 kbps); paket aset Tahap 1b menurunkannya ke 48 kbps (keputusan P2 di [`rencana-paket-aset.md`](rencana-paket-aset.md)), lihat bagian *Narration at 48 kbps* di bawah.
- Kenyaringan −16 LUFS per berkas, diukur bersama heningnya.
  - Mbah Kedu −16,5 LUFS. Puncak glotal suara beratnya tinggi, jadi −16 hanya tercapai dengan limiter yang terdengar menekan.
  - Pose yang digeser: Jaka `determined` −15,5, `afraid` −16,5, dan `sad` −17; Mbah Kedu `weak` −18, supaya terdengar lirih.
- Puncak sejati (*true peak*) ≤ −1 dBTP pada master WAV lewat limiter look-ahead 2 ms yang pulih dalam ±20 ms. Setelah dikode ke MP3, puncak sejati paling tinggi −0,5 dBTP (dijaga `publish.py`, `TRUE_PEAK_CEILING`).
  - Pada Narator dan Jaka, limiter hanya menyentuh puncak tunggal (≤ 0,1% durasi, paling banyak 1,3 dB).
  - Pada Mbah Kedu, limiter menekan lebih dari 1 dB di ±5% durasi (paling banyak ±23%). Penekanan terdalam per baris bermedian 1,3 dB, paling banyak 3,5 dB.
- LAME pada mode CBR mengalikan sinyal 0,95× (−0,45 dB). `publish.py` mengompensasinya, sehingga kenyaringan MP3 sama dengan master WAV-nya.
- Hening 0,3 detik di awal dan akhir.
- Satu berkas satu baris, nama = kode baris (`intro-01.mp3`, `petunjuk-tmg-4-03.mp3`, …). Transkripnya persis teks naskah pada bahasa itu.

### Pemeriksaan kejelasan (QA)

Setiap baris dipilih dari beberapa kandidat sintesis: Indonesia 3–5 kandidat, Inggris 1 kandidat, dan 3–6 kandidat tambahan untuk baris yang diulang. Tiap kandidat ditranskripsi Whisper small tanpa diberi teks naskahnya, lalu transkripnya dibandingkan dengan naskah memakai *character error rate* (CER): huruf kecil, tanpa tanda baca, dan angka dieja. Kandidat dengan CER terendah yang dipakai. Rekaman final, sesudah pengolahan kenyaringan, ditranskripsi ulang dengan cara yang sama:

| Bahasa | Median CER | Rata-rata CER | Median CER per tokoh | Baris dengan CER ≤ 10% | Durasi total |
|---|---|---|---|---|---|
| Indonesia | 6,0% | 6,3% | Narator 6,2% · Jaka 6,0% · Mbah Kedu 5,9% | 85/100 | 19,5 menit |
| Inggris | 3,7% | 4,1% | Narator 2,3% · Jaka 5,7% · Mbah Kedu 2,2% | 95/100 | 14,5 menit |

Hasil per baris, termasuk transkrip Whisper, ada di [`tools/audio/qa-narasi.json`](../tools/audio/qa-narasi.json).

Perombakan kelas 5 (Oktober 2026) membangkitkan ulang 23 baris per bahasa: 11 baris naskah yang jumlah serpihannya berubah dan 12 petunjuk Cari Objek (`tmg-4`, `mgl-4`, `wnb-4`). Setiap baris dipilih dari 3 kandidat (6 untuk `tuntas-magelang-03` dan `petunjuk-tmg-4-04` EN). Untuk ke-23 baris itu, angka di `qa-narasi.json` berasal dari kandidat terpilih sebelum dikodekan ke MP3, bukan dari transkripsi ulang rekaman final. Rekaman lama `petunjuk-tmg-4-05…08` dihapus.

**Tempo.** Narasi Indonesia sempat dipercepat 3–8% agar lebih lincah: Narator 0,96, Jaka 0,92, Mbah Kedu 0,97. Uji A/B dengan Whisper pada 96 baris yang sama, dengan pengolahan lain yang identik, menunjukkan percepatan itu menurunkan kejelasan:

| Tokoh | CER rata-rata dengan percepatan | CER rata-rata tanpa percepatan | Wilcoxon |
|---|---|---|---|
| Jaka | 10,0% | 7,5% | p = 0,001 |
| Mbah Kedu | 6,9% | 5,8% | p = 0,007 |

Karena GELITA melatih membaca, narasi memakai tempo asli model (`TEMPO` di `tools/audio/narrate.py` bernilai 1).

CER di sini lebih tinggi daripada salah dengar yang sebenarnya:

- **Logat Jawa.** Whisper menuliskan ucapan berlogat Jawa dengan ejaan yang ia kira, misalnya *Mbah* → "Embah". Huruf yang berbeda itu terhitung salah.
- **Nama Jawa di narasi Inggris** ditulis Whisper dengan ejaan Inggris, misalnya *Kedu* → "Kudu".
- **Baris pendek.** Satu kata yang meleset pada baris seperti *"Aku takut, Mbah…"* langsung membuat CER-nya puluhan persen.

QA otomatis tidak menggantikan mendengarkan. Setujui narasi di **Rekaman narasi** setelah didengarkan. Baris dengan CER tertinggi layak didengarkan lebih dulu:

- Indonesia: `dialog-wonosobo-10`, `dialog-temanggung-07`, `tuntas-magelang-02`.
- Inggris: `dialog-wonosobo-10`, `dialog-magelang-07`, `tuntas-magelang-03` (Whisper mendengar *Eight shards* sebagai "HRs" di keenam kandidat).

### Memasang di server

```bash
php spark gelita:narration:import --dry-run   # 100 baris per bahasa dikenali
php spark gelita:narration:import             # audio draft, ditautkan ke naskah & petunjuk cari
```

Lalu buka **Rekaman narasi**, dengarkan, dan tekan **Setujui semua rekaman Indonesia / Inggris**.

Narasi petunjuk Cari Objek butuh bank soal kelas 5 yang sudah diimpor saat instalasi (lihat [`08_DEPLOYMENT.md`](08_DEPLOYMENT.md)). Nomor `petunjuk-{node}-NN` mengikuti urutan target aktif tiap node `cari`.

Berkas yang sha256-nya tercantum di `produksi.json` tercatat `production_method = tts`, dan `voice_profile` menyebut model, penutur, dan pengolahannya. Peneliti dapat membedakan narasi sintetis dari rekaman pengisi suara di tabel `audio_assets`.

### Mengganti dengan rekaman pengisi suara

Rekaman baru cukup memakai nama berkas yang sama, lalu dipasang seperti biasa: timpa berkas di folder narasi lalu `gelita:narration:import`, atau unggah lewat **Rekaman narasi → Unggah rekaman**. Karena isinya berbeda dari manifest, rekaman itu otomatis tercatat sebagai rekaman sendiri dan kembali berstatus draft. Arahan suara tiap tokoh ada di [`naskah-cerita.md`](naskah-cerita.md).

## Narration at 48 kbps (asset package Stage 1b, October 2026)

All 200 narration files (100 per language) were re-encoded from 96 kbps to 48 kbps to shrink the offline asset package (owner decision P2 in [`rencana-paket-aset.md`](rencana-paket-aset.md) §12; Stage 1b in §10). Paths and file names did not change.

**How.** The WAV masters (`tools/audio/out/`) are not in the repository, so the 96 kbps MP3s were transcoded in place:

```bash
cd tools/audio && python3 reencode.py      # needs only ffmpeg/ffprobe; no TTS venv
```

`reencode.py` decodes each MP3, encodes it with the same `encode()` as `publish.py`, and rewrites `produksi.json` (`sha256`, `bytes`, `bitrate_kbps`; `voice_profile` unchanged). Files already at 48 kbps are skipped, so a second run is a no-op instead of a second lossy generation. When narration is regenerated from WAV, `publish.py` now produces 48 kbps directly. The output is byte-identical between runs with the same ffmpeg/LAME build (checked with ffmpeg 6.1.1).

**Encoder settings.** Same codec and container as before: MPEG-1 Layer III, mono, 44.1 kHz, CBR, Xing header, no ID3 tag, `volume=1/0.95` to compensate LAME's CBR scale. Two additions:

- LAME quality `-q 2` (`-compression_level 2`, LAME's "-h") instead of the ffmpeg default.
- A true-peak guard (`TRUE_PEAK_CEILING = -0.5` dBTP, the highest peak of the 96 kbps files). At 48 kbps, coding noise on strong 8–11 kHz sibilants overshoots the source peak: with ffmpeg defaults `en/intro-04` reached +3.3 dBTP (inter-sample values above full scale). The overshoot moves erratically with tiny gain changes, so `encode()` walks the gain down in 0.1 dB steps and tries `-q 2`, then `-q 0`, at each step; the first encode under the ceiling wins. 172 of 200 files pass at once with `-q 2`, 17 pass with `-q 0` at unchanged gain, 10 need a trim of 0.1–0.5 dB, and `en/intro-04` (Narrator, among the strongest sibilance in the set) needs −1.3 dB.

The sample rate stayed at 44.1 kHz. 22.05 or 24 kHz (MPEG-2) would have lowered the overshoot somewhat but changes the stream format the browsers, `MediaStore`, and the asset checklist were tested with; the speech band (< 11 kHz) is kept either way.

**Result** (`du -sb`):

| | 96 kbps | 48 kbps |
|---|---|---|
| `narasi/id` | 14,153,097 B (14.15 MB) | 7,079,075 B (7.08 MB) |
| `narasi/en` | 10,498,049 B (10.50 MB) | 5,251,547 B (5.25 MB) |
| narration total | 24,651,146 B (24.65 MB) | 12,330,622 B (12.33 MB) |
| `public/assets/audio` (with music, sound effects, manifest) | 28,086,394 B (28.09 MB) | 15,774,689 B (15.77 MB) |

**Checks on every file** (decoded with ffmpeg, compared with the 96 kbps file):

- Decoded length is sample-exact (the Xing/LAME header carries the encoder delay), so durations stored by `MediaStore` do not change.
- Integrated loudness within ±0.1 LU of the 96 kbps file, except the trimmed `en/intro-02` (−0.5 LU) and `en/intro-04` (−1.4 LU, −17.4 LUFS).
- True peak ≤ −0.5 dBTP.
- Spectrum: LAME's lowpass at 48 kbps removes content above ±11.5 kHz. In the 96 kbps files that band held at most −34 dB of a file's energy (`en/dialog-temanggung-02`); the speech band below it is kept.

The Whisper CER figures above were measured on the 96 kbps (or WAV) files and were not re-run at 48 kbps; the ASR models are not available in this environment.

## Lisensi dan kredit

- **Musik dan efek suara**: komposisi dan sintesis orisinal untuk GELITA (`tools/audio/gamelan.py`, `compose.py`, `music_scenes.py`), tanpa sampel atau rekaman pihak ketiga.
- **Narasi Indonesia**: dibangkitkan dengan model Mimic 3 `jv_ID/google-gmu_low` (MycroftAI, **CC BY-SA 4.0**). Model itu dilatih dari *High quality TTS data for Javanese* (OpenSLR 41, Google & Universitas Gadjah Mada, CC BY-SA 4.0). Untuk amannya, perlakukan rekaman ini dengan syarat yang sama: cantumkan atribusi di atas dan bagikan turunannya dengan lisensi serupa.
- **Narasi Inggris**: suara Piper `en_US-kristin-medium` (domain publik, dataset LibriVox), `en_GB-jenny_dioco-medium` (suara "Jenny (Dioco)"; klip hasil boleh dipakai, termasuk komersial, atribusi dianjurkan), dan `en_US-joe-medium` (CC0).
- **Whisper small** (MIT) hanya dipakai untuk memeriksa kejelasan, bukan bagian dari permainan.

## Voice-over narration (17 October 2026)

Owner decision: narration is recorded by voice actors (real voices, not TTS). The 200 bundled TTS files and `produksi.json` were removed from the repository; the TTS sections above stay as history, and `tools/audio/` still builds music and sound effects.

1. **Script.** In **Rekaman narasi**, *Naskah untuk pengisi suara* downloads a printable PDF (`/admin/konten/narasi/naskah-pdf?bahasa=id|en|semua`, `App\Libraries\NarrationScriptPdf`): A4 portrait, 7 mm margins, condensed 8.5 pt type, one table in game order with a tick box, the file name, the speaker with pose/effect as a delivery cue, and the text. One language is about 5 pages; both side by side about 7.
2. **Old recordings.** On a server that still has the TTS takes, press **Batalkan semua rekaman** once per language (`NarrationCatalog::cancelAll()`). Every line and clue goes back to "belum ada". Imported `audio.narasi.*` recordings become `rejected` (bulk approval skips them) and their files are deactivated and deleted; other audio picked by hand is only unlinked. The rows themselves stay, because `audio_usage_events` refers to them.
3. **New recordings.** Name each take after its code (`intro-01.mp3`, `petunjuk-tmg-4-01.mp3`) and upload it in **Unggah rekaman**, or put it in `public/assets/audio/narasi/{id,en}/` and run `php spark gelita:narration:import`. A cancelled line comes back as a draft (`production_method = own_recording`); listen to it, then approve it.

## Research revision 10, part 6: new recording groups (4 November 2026)

Plan: `docs/revisi-10-analitik/32_IMPLEMENTASI_SKENARIO_DIGITAL.md` §8–§9; script: `12_CERITA_PENGHUBUNG_DAN_NARASI.md`. Per language there are now **± 187 recordings** (`NarrationImporter::lines()` lists 187 on the grade 5 bank):

| Group | File code | Speaker | Count | Text from | Played |
|---|---|---|---:|---|---|
| Story script (8 contexts) | `intro-`, `peta-`, `kenal-`, `dialog-`, `layar-` (new), `tuntas-`, `penutup-`, `peta-tuntas-` | Narator, Jaka, Mbah Kedu | 112 | `docs/naskah-cerita.md` | on its own, as before |
| Temukan Benda clues (round 1) | `petunjuk-{node}-NN` | Mbah Kedu | 12 | bank, items `*-4-x01` … `x04` | ▶ only |
| *Periksa Unggahan* clues (round 2) | `unggahan-{item code}` | Mbah Kedu | 24 | bank, `find_part` items | ▶ only, context `post_clue` |
| Skenario Digital situations | `skenario-{item code}` | Narator | 24 | bank, `scenario_choice` items | ▶ *Dengarkan* only, context `scenario_situation` |
| Challenge instructions | `instruksi-{node}` | Narator | 15 | bank, `challenge_nodes.instruction_*` | ▶ beside the instruction only, context `challenge_instruction` |

- Item files carry the item code, never a number, so variants a and b cannot swap. `petunjuk-*` numbers count round-1 clues only.
- Never read out: Isi Rumpang and Benar atau Salah items and the reading passages. The importer refuses a file named after one of them (`Bacaan dan pernyataan tidak dibacakan.`), and the item form refuses a recording for them.
- Voice direction for `skenario-*` and `unggahan-*`: flat and the same for every situation; quoted messages read plainly; web addresses read as written; key part names not stressed (12 → *Arahan suara*). Approving such a recording needs the tick *Nada datar dan sama untuk setiap situasi*, in the audio list and in the bulk approval (without it the bulk approval leaves them as drafts); the audit records `tone_checked`.
- `gelita:reset` rebuilds items and challenges, then relinks every registered item and instruction recording (`NarrationImporter::relinkClues()`).
- Every tap is logged in `audio_usage_events` with the attempt id; `instruction_replays` (part 9) counts all `challenge_instruction` plays, since none play on their own.

