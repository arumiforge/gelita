# Rencana: Katalog Aset GELITA — halaman statis berisi semua gambar, ikon, audio, dan narasi beserta prompt pembuatannya

> Status: **rencana, belum dikerjakan** (disusun 29 September 2026). Dokumen ini punya dua bagian:
>
> - **Bagian I** (§1–§8) adalah rencana pengembangan halaman statisnya.
> - **Bagian II** (§9–§15) adalah isi katalog: panduan gaya, ukuran dan rasio, serta prompt setiap aset. Isi ini menjadi berkas data pada Fase B.
>
> Rencana ini bersinggungan dengan [`rencana-perbaikan-tampilan.md`](rencana-perbaikan-tampilan.md) (katalog slot `ImageSlots`, pemutar frame tokoh, pin per kota, avatar). Titik temunya dijelaskan di §5 Fase F.

# Bagian I — Rencana pengembangan

## 1. Ringkasan

**Apa yang dibuat.** Satu halaman HTML statis, `docs/katalog-aset/index.html`. Halaman ini dibangun oleh `php docs/katalog-aset/build.php` dari sumber yang sama dengan permainan (konfigurasi, seeder, naskah, bank soal, audio). Isinya:

| Kelompok | Jumlah butir | Isi setiap butir |
|---|---:|---|
| Gambar utama (logo, tombol, ikon aplikasi, latar, peta, lencana, frame tokoh, gambar tantangan) | 77 | kode berkas, tempat tampil, ukuran px, rasio, format, zona aman, prompt, prompt negatif, langkah pasca-produksi, cek budaya |
| Gambar opsional (latar slide cerita, latar node, gambar rumpang/`mgl-5`, media Pustaka, poster video) | 93 | sama, ditambah alasan mengapa opsional |
| Gambar yang direncanakan `rencana-perbaikan-tampilan` (pin, lentera, latar pemuatan per kota, avatar) | 21 | sama, ditandai "menunggu fase X" |
| Ikon vektor (`icon()`, jejak kaki, layar putar) | 50 | pratinjau SVG, makna, tempat pakai, deskripsi bentuk, spesifikasi garis |
| Musik gamelan & efek suara | 9 | pemutar, durasi, loop, target kenyaringan, prompt musik AI, brief komposer |
| Narasi (88 baris naskah + 8 petunjuk arena `cari`) × 2 bahasa | 192 | teks, tokoh–pose–efek, durasi acuan, arahan suara, pemutar rekaman bawaan, lafal khusus |

**Untuk siapa.** Ilustrator, pengisi suara, komposer, dan tim konten. Guru dan peneliti juga dapat meninjau kecocokan budaya dan fakta. Pemakainya tidak perlu akun admin, database, atau server.

**Terpisah dari web dan panel admin.**

- Halaman tidak berada di `public/`, tidak punya route, tidak membaca database, dan tidak butuh login.
- Halaman dibuka dari salinan repositori, atau dibagikan sebagai folder/ZIP ke tim.
- Kode aplikasi hanya berubah lewat dua refaktor kecil tanpa perubahan perilaku (§5 Fase A), agar daftar ikon dan posisi pin dapat dibaca tanpa menyalinnya. Satu kalimat bantuan di panel bersifat opsional (§5 Fase F).

**Mengapa perlu.**

- Daftar kebutuhan aset kini tersebar di README, `docs/05`, `docs/bank-soal/README.md`, `docs/naskah-cerita.md`, `docs/audio.md`, dan **Media → Kelengkapan aset**.
- Tidak ada satu tempat pun yang memberi tahu cara membuat aset itu: prompt, rasio, dan zona aman yang sesuai dengan cara CSS memotong gambar.

## 2. Konteks dan temuan dari penelusuran kode

### 2.1 Sumber kebenaran yang dibaca katalog

| Data | Sumber di repositori | Catatan |
|---|---|---|
| Ukuran wajib slot | `Config\Gelita::$assetSizes` (`app/Config/Gelita.php:120`) | ditegakkan `MediaStore::store()` lewat `fnmatch()` (`app/Libraries/MediaStore.php:82`) |
| Pose, frame, fps, loop | `Config\Gelita::$characterPoses`, `$characterAnimations`, `$dialogueEffects` | 10 pose × 3 frame |
| Slot resmi + nama berkas bawaan | `MediaAssetSeeder::SLOTS` + slot wilayah + slot frame (`app/Database/Seeds/MediaAssetSeeder.php:43,113`) | konstanta `private` → dibaca dengan Reflection |
| Label "tampil di" | `MediaUsage::SLOTS`, `AssetChecklist::UI_SLOTS` | |
| Musik & efek suara | `AssetChecklist::SOUNDS` + `SOUND_SIZES`; `public/assets/audio/{music,sfx}/` | |
| Posisi pin wilayah | `LevelSeeder` (`map_x`/`map_y`: Temanggung 65/41, Magelang 56/71, Wonosobo 28/55) | kini array lokal di `run()` → refaktor Fase A |
| Posisi 5 pos tantangan | `ContentSeeder::NODE_POSITIONS` (18/72, 34/48, 50/68, 66/44, 82/62) | konstanta `private` → Reflection |
| Naskah 88 baris | `app/Database/Seeds/data/story.php` (= `docs/naskah-cerita.md`) | |
| 8 petunjuk arena `cari` | `docs/bank-soal/data/temanggung.php` (aturan penomoran `tools/audio/export_lines.php`) | |
| Media tantangan & Pustaka | `docs/bank-soal/data/{temanggung,magelang,wonosobo}.php` | 20 media tantangan, 48 media Pustaka |
| Durasi, CER, LUFS narasi | `tools/audio/qa-narasi.json` (192 baris) | durasi acuan per baris |
| Profil suara & sha256 TTS | `public/assets/audio/narasi/produksi.json` | |
| Lafal nama Jawa (EN) | `tools/audio/en_lexicon.py` (`NAMES`) | dibaca dengan regex |
| Ikon SVG | `app/Helpers/ui_helper.php:15` (48 ikon) | kini variabel `static` di dalam `icon()` → refaktor Fase A |
| Teks UI (tagline, judul tirai) | `app/Language/{id,en}/Game.php` | |

### 2.2 Temuan yang memengaruhi spesifikasi aset

Temuan ini harus tercermin di katalog. Sebagian adalah ketidakcocokan kecil yang layak diperbaiki kemudian, tetapi tidak dikerjakan di rencana ini.

| # | Temuan | Bukti | Akibat untuk katalog |
|---|---|---|---|
| 1 | Daftar slot ditulis di empat tempat | `MediaAssetSeeder::SLOTS`, `MediaUsage::SLOTS`, `AssetChecklist::UI_SLOTS`, `$assetSizes` | katalog menggabungkan keempatnya; bila `ImageSlots` (rencana lain, Fase A) sudah ada, katalog membacanya saja |
| 2 | Ukuran hanya ditegakkan untuk pola yang cocok persis: `bg.*`, `char.*`, `ui.logo-hero`, `ui.btn-start(.*)` | `$assetSizes` memakai kunci `map.region`, `reward.badge`, `challenge.scene`, `library.image` yang tidak pernah cocok dengan kunci nyata seperti `map.region.temanggung` | setiap butir diberi label **wajib** (ditolak bila beda) atau **disarankan** |
| 3 | `map.kedu` tertulis "bebas (mendatar)", padahal kanvas mengunci rasio `1400 / 900` dan pin memakai persen | `app/Views/game/map-kedu.php:130`, `game.css:455,470` | katalog menetapkan 1400 × 900 (14:9) |
| 4 | Poster video Pustaka di Kelengkapan disebut 960 × 640, padahal bingkai video 16:9 | `AssetChecklist.php:273`, `game.css:1181,1193`; `VideoThumbnail` mengambil 1280 × 720 | katalog menetapkan 1280 × 720 |
| 5 | `bg.auth` saat ini hanya dipakai login staf (opasitas 35%); halaman masuk/daftar siswa memakai `bg.welcome` | `layouts/auth.php:8`, `layout.css:317–324`; `game/login.php:18` | prompt dibuat tenang dan kontras rendah |
| 6 | `ui.logo` selalu tampil di samping tulisan "GELITA" (kepala panel setinggi 36 px, login staf lebar 120 px) | `layouts/admin.php:36`, `layouts/auth.php:31`, `layout.css:231` | `ui.logo` berupa **emblem tanpa tulisan**, 512 × 512 |
| 7 | Hanya frame `.1` tokoh yang ditampilkan; pemutar frame belum ada | `components/character.php:18`, `content_helper.php:204` | frame 1 wajib terbaca sendiri sebagai pose itu |
| 8 | Mbah Kedu dicerminkan di panggung dialog | `game.css:416` `.stage-right .character-img { transform: scaleX(-1) }` | semua tokoh digambar menghadap **kanan layar**; tanpa tulisan atau lambang asimetris di pakaian |
| 9 | Pose `happy` untuk Mbah Kedu diminta, padahal tidak sah (jatuh ke `idle`) | `reflection.php:26`, `challenge-finished.php:90` | tidak dibuat `char.kedu.happy`; rencana lain menggantinya dengan `smile` |
| 10 | `docs/05` menyebut "ilustrasi Jaka memegang kunci" di form daftar, tetapi kode memakai pose `bow` | `register.php:220` | tidak ada slot baru; dicatat sebagai pertanyaan terbuka §8 |
| 11 | Objek jebakan arena `cari` dirender sama persis dengan target | `game/challenge/cari.php:5`, `HuntPayloadTest` | 11 objek wajib satu gaya; katalog memuat tanda jebakan, jadi halaman bersifat **internal** |
| 12 | Latar sinematik diberi Ken Burns (skala 1,02 → 1,14 dan geser −1,5% / −1%) | `cinematic.css:380–399`, `:359` | zona aman sinematik lebih sempit dari zona aman latar biasa |
| 13 | Gambar kartu `boleh` dipotong `object-fit: cover` dengan tinggi maksimal 160 px | `game.css:831` | subjek harus berada di pita tengah mendatar |
| 14 | Butir papan puzzle memuat nomor di pojok kiri atas keping | `game.css:691` | detail penting tidak boleh di pojok kiri atas tiap sepertiga gambar |
| 15 | `core/audio.js` menyebut efek `page` dan musik `library`, tetapi tidak ada pemanggilnya | `core/audio.js:106,112` | tidak dibuat; dicatat sebagai calon aset |
| 16 | Posisi pin dan pos dapat diubah admin (`levels.map_x/map_y`, `challenge_nodes.map_x/map_y`) | editor wilayah & node | katalog memakai nilai seeder; bila admin memindahkan pin, gambar peta diperiksa ulang |
| 17 | Narasi TTS baru dari mesin lain akan tercatat sebagai "rekaman sendiri" bila sha256-nya tidak ada di `produksi.json` | `NarrationImporter::production()` | alur produksi narasi memuat langkah memperbarui manifest (§15.3) |

## 3. Keputusan desain

1. **HTML statis satu berkas, dibangun dari repositori.**
   - CSS dan JavaScript ditulis inline.
   - Data katalog ditanam sebagai `<script type="application/json" id="katalog">`, dan dari situ pula tombol unduh CSV/JSON bekerja.
   - Tanpa CDN dan tanpa permintaan ke domain luar, sesuai aturan 10 di `docs/05`. Tautan Wikimedia Commons dan YouTube hanya berupa `<a>` yang diklik manual.
2. **Lokasi `docs/katalog-aset/`**, bersebelahan dengan `docs/bank-soal/` dan `docs/sekolah/`. Polanya sama: berkas data yang ditinjau lewat git, skrip build, dan hasil build yang di-commit.
   - Folder `docs/` tidak dilayani Nginx (web root = `public/`).
   - Uji memastikan tidak ada berkas katalog di `public/`.
3. **Pembangun berupa PHP CLI biasa**, seperti `docs/bank-soal/build-workbook.php`.
   - Memakai `vendor/autoload.php`, membaca kelas dengan Reflection atau `require`, tanpa boot CodeIgniter dan tanpa database.
   - Hasilnya deterministik (urutan tetap, tanpa tanggal), sehingga uji dapat membandingkan byte demi byte.
4. **Prompt ditulis dalam bahasa Inggris**, karena hampir semua generator gambar dan musik paling patuh pada bahasa Inggris. Penjelasan, brief, dan daftar periksa ditulis dalam bahasa Indonesia.
   - Arahan narasi baris Indonesia ditulis dalam bahasa Indonesia; arahan baris Inggris dalam bahasa Inggris.
5. **Blok gaya dipakai ulang.** Prompt memakai penanda `{GAYA}`, `{NEGATIF}`, `{LATAR}`, `{TRANSPARAN}`, `{FRAME}`, `{JAKA}`, `{KEDU}`, `{LENTERA}`, `{KABUT}`, dan `{SERPIHAN}` (§9.2, §9.4).
   - Ada juga penanda lokal yang diisi dari kolom tabel butir itu sendiri, misalnya `{POSE}`, `{ISI}`, dan `{TOPIK}`.
   - Pembangun mengembangkan semuanya, jadi tombol **Salin prompt** selalu menyalin prompt utuh, dan perubahan gaya cukup dilakukan di satu tempat.
6. **Foto asli diutamakan untuk isi faktual** (Pustaka, dan opsi foto untuk puzzle). Ilustrasi AI dipakai untuk seni permainan: tokoh, latar, peta, lencana, dan objek `cari`. Ilustrasi AI tidak boleh menampilkan situs bersejarah dengan bentuk karangan: selalu memakai foto rujukan.
7. **Setiap aset hasil AI dicatat**: alat, model/versi, prompt final, seed, tanggal, suntingan manual, dan lisensi. Pencatatannya ada di lembar produksi `docs/katalog-aset/produksi.csv` (§15.4). Ini sejalan dengan pencatatan `production_method = tts` pada narasi, karena GELITA adalah instrumen penelitian.
8. **Halaman bersifat internal.** Halaman memuat tanda objek jebakan dan deskripsi gambar soal.
   - Ada banner peringatan dan `<meta name="robots" content="noindex, nofollow">`.
   - README menjelaskan bahwa halaman hanya dibagikan ke tim, tidak dipasang di server sekolah maupun situs publik.

## 4. Arsitektur halaman statis

### 4.1 Berkas

```text
docs/katalog-aset/
├── README.md        cara membangun, membuka, membagikan; aturan distribusi internal
├── build.php        php docs/katalog-aset/build.php [--check] [--bundle=DIR]
├── lib/
│   ├── Sources.php  membaca sumber kode & data (tabel §2.1)
│   ├── Catalog.php  menggabungkan sumber + data prompt → daftar butir; validasi
│   ├── Guides.php   membangkitkan templat SVG (§4.5)
│   └── Page.php     merender index.html (CSS/JS inline, JSON tertanam)
├── data/            ditulis manusia, ditinjau lewat git (isi Bagian II dokumen ini)
│   ├── gaya.php     blok gaya, palet, negatif umum, lembar tokoh, parameter alat
│   ├── gambar.php   prompt per asset_key (atau pola, mis. char.jaka.*)
│   ├── ikon.php     deskripsi & prompt ikon
│   ├── audio.php    musik & efek suara
│   └── narasi.php   arahan suara per tokoh/pose/efek + catatan per baris
├── produksi.csv     lembar produksi (opsional; dibaca untuk status "sudah diproduksi")
├── templat/         *.svg hasil build — jangan disunting manual
└── index.html       hasil build — jangan disunting manual
```

### 4.2 Alur build

1. **`Sources`** mengumpulkan *butir yang diharapkan* dari kode (tabel §2.1):
   - setiap slot gambar beserta ukurannya;
   - setiap frame tokoh;
   - setiap media tantangan dan Pustaka;
   - setiap bunyi;
   - setiap baris narasi (88 + 8, dihitung dari data, tidak ditulis sebagai angka tetap);
   - setiap ikon.
2. **`Catalog`** memasangkan butir itu dengan `data/*.php`. Build **gagal** (kode keluar 1, daftar kekurangan dicetak) bila:
   - ada butir tanpa prompt atau arahan, kecuali ditandai `'lewati' => 'alasan'`;
   - ukuran di data berbeda dengan `$assetSizes` untuk slot wajib;
   - rasio tidak sesuai ukuran;
   - ada penanda blok `{…}` yang tidak dikenal;
   - ada teks `TODO`.
   Kunci data yang tidak cocok dengan butir mana pun dilaporkan sebagai **peringatan** (data usang).
3. **`Guides`** menulis templat SVG dari angka yang sama (posisi pin, kotak objek, garis tanah tokoh), jadi templat tidak bisa berbeda dari kode.
4. **`Page`** menulis `index.html`. Status "ada di repo" dihitung dengan `is_file()` pada path bawaan `public/assets/…`. Aset yang diunggah lewat panel hanya ada di server, jadi halaman menyebut jelas bahwa status server dilihat di **Media → Kelengkapan aset**.

Opsi:

- `--check`: tidak menulis apa pun. Keluar 1 bila validasi gagal atau `index.html`/`templat/*.svg` tidak sama dengan hasil build.
- `--bundle=DIR`: menulis salinan siap bagi.
  - Isinya `index.html`, `templat/`, dan `aset/` (salinan pratinjau yang ada di repo: audio musik/efek/narasi, ikon aplikasi, placeholder).
  - Tautan di dalamnya ditulis ulang dari `../../public/assets/…` menjadi `aset/…`.
  - Folder ini di-ZIP lalu dibagikan secara internal; `build/` masuk `.gitignore`.

### 4.3 Tampilan halaman

- **Kepala.**
  - Judul, banner "Dokumen internal", dan hash masukan (sha256 gabungan semua sumber, untuk mencocokkan versi).
  - Hitungan per kelompok: wajib, opsional, direncanakan, dan ada di repo.
- **Navigasi.** Tautan jangkar tetap di atas: Panduan gaya · Ukuran & rasio · Gambar UI · Latar · Cerita · Peta · Wilayah · Tokoh · Tantangan · Pustaka · Direncanakan · Ikon · Musik & efek · Narasi · Alur produksi.
- **Bilah saring** (JavaScript): kotak cari; chip jenis (gambar, ikon, musik, efek, narasi); wilayah; prioritas (wajib, opsional, direncanakan); status repo; bahasa narasi.
- **Kartu gambar:**
  - kepala: label, kode berkas, status, prioritas;
  - kisi spesifikasi: ukuran px, rasio, orientasi, format, transparansi, batas ukuran berkas, penegakan (wajib/disarankan), tempat tampil, nama berkas bawaan atau kode unggah;
  - pratinjau rasio: SVG tergambar sesuai rasio dengan zona aman, garis pin, atau kotak objek;
  - prompt (EN) dan prompt negatif, masing-masing dengan tombol **Salin**;
  - parameter per alat dalam `<details>`: Midjourney, SDXL, Flux, gpt-image;
  - langkah pasca-produksi (perintah ImageMagick siap salin);
  - daftar periksa budaya/fakta.
- **Kartu narasi:**
  - kode; tokoh · pose · efek; durasi acuan ID/EN dari QA; teks ID dan EN;
  - pemutar rekaman bawaan ID/EN (`<audio controls preload="none">`);
  - arahan suara ID/EN dengan tombol **Salin**;
  - profil suara TTS dari `produksi.json`; tanda CER tinggi dari `qa-narasi.json`; lafal khusus untuk baris Inggris.
- **Kartu musik/efek:** pemutar, durasi, loop, target kenyaringan, prompt musik AI, brief komposer, dan perintah pasca-produksi.
- **Kartu ikon:**
  - pratinjau 24 px dan 48 px di latar gelap dan terang (hasil `icon()` yang sama dengan permainan);
  - nama, makna, contoh tempat pakai (dihitung dengan memindai `app/Views`);
  - deskripsi bentuk dan sumber SVG untuk disalin.
- **Ekspor:**
  - **Unduh CSV** (satu baris per butir: kode, ukuran, rasio, prompt, negatif, status);
  - **Unduh JSON** (untuk skrip pembuat massal);
  - **Salin semua prompt di kelompok ini**;
  - **Unduh templat lembar produksi**.
- **Tanpa JavaScript.** Semua isi tetap tampil: `<details>` untuk bagian panjang dan jangkar untuk navigasi. Teks prompt dapat diseleksi dan disalin manual.
- **Cetak.** Stylesheet cetak menyembunyikan saringan dan pemutar, dan satu kartu tidak terpotong antarhalaman. Tim dapat mencetak satu kelompok ke PDF untuk ilustrator.
- **Tema.** Navy + emas seperti permainan: token `tokens.css` disalin sebagai nilai tetap, tidak ditautkan. Font memakai `../../public/assets/fonts/` bila ada; tanpa font itu, halaman memakai font sistem.

### 4.4 Distribusi dan keamanan

- Buka langsung dari salinan repo: `docs/katalog-aset/index.html` di browser (`file://`). Pratinjau audio memakai path relatif ke `public/assets/audio/`.
- Untuk pihak luar tim teknis (ilustrator, pengisi suara): pakai `--bundle`, lalu bagikan ZIP lewat kanal privat tim.
- **Jangan** diletakkan di `public/`, di server sekolah, atau di hosting publik. Halaman memuat tanda objek jebakan arena `cari` dan deskripsi gambar soal `mgl-5`.
- Halaman tidak memuat data siswa, kredensial, atau isi `.env`.

### 4.5 Templat panduan (SVG, dibangkitkan)

Templat dipakai sebagai lapisan acuan di aplikasi gambar (Krita, Photoshop, Figma, Inkscape) atau sebagai gambar kontrol (ControlNet *lineart/canny*, *image prompt*).

| Berkas | Isi |
|---|---|
| `templat/latar-1920x1080.svg` | zona aman semua perangkat (x 240–1680, y 96–984); zona aman sinematik (x 340–1600, y 160–930); pita HUD 0–72; pita navigasi 1004–1080; perkiraan kotak teks sinematik (x 736–1716, y ≥ 700); kolom tokoh sinematik (x ≤ 540) |
| `templat/peta-kedu-1400x900.svg` | titik pin Temanggung (910, 369), Magelang (784, 639), Wonosobo (392, 495); zona tenang ±130 × ±90 px; garis rute; usulan letak bentang alam (§11.5) |
| `templat/peta-wilayah-1400x900.svg` | 5 pos: (252, 648), (476, 432), (700, 612), (924, 396), (1148, 558); zona tenang ±110 × ±80 px; rute; usulan letak vinyet per pos |
| `templat/adegan-cari-1280x720.svg` | 11 kotak objek 154 × 154 px beserta lingkaran objek; garis rak y 197 / 442 / 672 (§11.8.2) |
| `templat/tokoh-700x900.svg` | garis tanah y 876; puncak kepala Jaka y 156; puncak blangkon Mbah Kedu y 66; sumbu x 350; margin 40 px; panah arah hadap (kanan) |
| `templat/logo-hero-1600x600.svg` | margin 48 px; pita tinggi huruf 260–300 px; kotak pratinjau ukuran tampil 880 × 330 |
| `templat/tombol-mulai-720x240.svg` | badan tombol 688 × 208 (margin 16); zona teks 520 × 110 |
| `templat/lencana-320.svg` | lingkaran Ø 312 px |
| `templat/ikon-aplikasi-512.svg` | zona aman *maskable* Ø 409,6 px; margin ikon *any* 32 px |
| `templat/pustaka-960x640.svg` | potongan 4:3 (x 53–907); potongan sampul rak 16:9 (y 50–590) |
| `templat/kartu-480x360.svg` | pita yang tampil pada kartu `boleh` (y 70–290) |
| `templat/puzzle-900x900.svg` | kisi 3 × 3 (300 px); area nomor keping 50 × 38 px di pojok kiri atas tiap keping |

## 5. Fase pengerjaan

Kerjakan per fase, satu commit per fase. Jalankan uji setiap fase, lalu push.

### Fase A — Sumber tunggal yang dapat dibaca tanpa boot aplikasi

1. **`app/Helpers/ui_helper.php`: pisahkan data ikon.** Tambahkan `icon_paths(): array` yang mengembalikan peta nama → path; `icon()` membacanya.
   - Perilaku dan keluaran `icon()` tidak berubah (dijaga `ViewHelperTest`).
   - Katalog memanggil `icon_paths()` untuk mendaftar 48 ikon, sehingga ikon baru otomatis masuk katalog.
2. **`LevelSeeder`: jadikan data wilayah konstanta publik** `LevelSeeder::LEVELS`. `run()` membacanya; isi dan perilaku seeder sama.
3. **`ContentSeeder::NODE_POSITIONS` dan `MediaAssetSeeder::SLOTS`** dibiarkan `private` dan dibaca dengan `ReflectionClassConstant`. Uji Fase E mengunci bahwa keduanya tetap ada.
4. `docs/katalog-aset/lib/Sources.php` beserta uji kelengkapan sumber (lihat Fase E): 191 butir gambar, 50 ikon, 9 bunyi, 96 baris narasi. Angka ini dihitung dari data, tidak ditulis tetap.

### Fase B — Data katalog (isi Bagian II)

1. Tulis `docs/katalog-aset/data/gaya.php` dari §9 dan §10: blok gaya, palet, lembar tokoh, parameter alat, dan perintah pasca-produksi.
2. `data/gambar.php` dari §11.
   - Kunci per `asset_key`. Pola `char.jaka.happy.*` berbagi prompt dasar, dengan catatan per frame.
   - Nilai: `label`, `prioritas` (`wajib` | `opsional` | `direncanakan`), `ukuran`, `format`, `transparan`, `batas_kb`, `zona`, `prompt`, `negatif`, `pasca`, `cek`, `sumber_foto` (bila ada), `catatan`.
3. `data/ikon.php` dari §12, `data/audio.php` dari §13, `data/narasi.php` dari §14.
4. Setiap berkas data mengembalikan array biasa, seperti `docs/bank-soal/data/*.php`, dan ditinjau lewat git.

### Fase C — Pembangun dan halaman

1. `lib/Catalog.php`: penggabungan, pengembangan blok `{…}`, validasi, dan hash masukan.
2. `lib/Guides.php`: 12 templat SVG di §4.5.
3. `lib/Page.php`: HTML sesuai §4.3.
   - Seluruh teks lewat `htmlspecialchars()`. Data untuk JavaScript hanya lewat `<script type="application/json">`, sama dengan aturan 1 di `docs/05`.
   - JavaScript kira-kira 250 baris, tanpa pustaka: saringan, salin (Clipboard API dengan cadangan seleksi), unduh CSV/JSON (`Blob`), dan pemutar audio bawaan.
4. `build.php`: argumen `--check` dan `--bundle=DIR`, kode keluar 0/1, laporan ringkas.
5. Bangun lalu commit `index.html` + `templat/`. Tambahkan `build/` ke `.gitignore`.

### Fase D — Templat dan pratinjau

1. Periksa setiap templat di Inkscape/Krita pada skala 100%. Pastikan angka di templat sama dengan pratinjau rasio di kartu.
2. Tambahkan contoh "tampil di layar" untuk tiga aset yang paling sering salah potong. Contoh ini berupa SVG statis di dalam kartu:
   - latar pada layar 4:3 dan 2,16:1;
   - kartu `boleh` yang terpotong;
   - frame tokoh yang dicerminkan.

### Fase E — Uji dan penjaga

`tests/unit/AssetCatalogTest.php` (grup unit, tanpa database):

1. `Catalog::validate()` tidak mengembalikan kesalahan: setiap slot, frame, media tantangan, halaman Pustaka, bunyi, baris narasi, dan ikon punya isi.
2. Ukuran di katalog sama dengan `$assetSizes` untuk slot wajib. Teks rasio dihitung dari ukuran (1920 × 1080 → 16:9).
3. Jumlah baris narasi = baris `story.php` + petunjuk target `find_object`, dengan kode yang sama persis dengan aturan `NarrationImporter`.
4. `index.html` dan `templat/*.svg` sama byte demi byte dengan hasil build di memori. Polanya sama dengan `BankWorkbookGuideTest`, yang gagal bila workbook belum dibangun ulang.
5. `index.html` tidak memuat `src=`/`href=` ke `http(s)://` pada `<script>`, `<link>`, `<img>`, `<audio>`, atau `<iframe>`. Tautan luar hanya boleh berupa `<a>` ke `commons.wikimedia.org`, `www.youtube.com`, dan `upload.wikimedia.org`.
6. Tidak ada berkas bernama `katalog-aset*` di bawah `public/`.
7. `ReflectionClassConstant` untuk `ContentSeeder::NODE_POSITIONS` dan `MediaAssetSeeder::SLOTS` ada. Uji ini mencegah refaktor kelak diam-diam mematahkan katalog.
8. `icon_paths()` berisi setiap nama yang dipanggil `icon('…')` di `app/Views` (pemindaian regex).

### Fase F — Dokumentasi dan titik temu

1. `docs/katalog-aset/README.md`: cara membangun, membuka, membagikan; aturan internal; alur produksi singkat (§15).
2. Tautan ke katalog dari `README.md` (bagian *Aset yang masih perlu disiapkan*), `docs/05_VIEW_UI.md` §Aset, `docs/audio.md`, `docs/naskah-cerita.md`, dan `docs/bank-soal/README.md`.
3. **Opsional, disetujui dulu:** kalimat bantuan di **Media → Kelengkapan aset**, misalnya "Prompt dan ukuran lengkap ada di Katalog Aset (docs/katalog-aset) yang dibagikan tim teknis". Hanya teks, tanpa tautan, karena katalog tidak dilayani server.
4. **Titik temu dengan `rencana-perbaikan-tampilan.md`:**
   - Bila Fase A rencana itu (`App\Libraries\ImageSlots`) selesai lebih dulu, `Sources` membaca `ImageSlots`. Butir "direncanakan" (§11.10) otomatis menjadi butir biasa.
   - Bila rencana ini selesai lebih dulu, butir itu tetap bertanda **menunggu** dengan rujukan fasenya.
   - Setelah pemutar frame (Fase D rencana itu) ada, catatan "hanya frame 1 yang tampil" di kartu tokoh dihapus dari `data/gaya.php`.

## 6. Verifikasi

1. **Build dan uji.**
   - `composer install`, lalu `php docs/katalog-aset/build.php`, lalu `php docs/katalog-aset/build.php --check` (harus 0).
   - `vendor/bin/phpunit --no-coverage` (SQLite). Semua PHP yang diubah dicek `php -l`.
2. **Uji di browser** (Playwright + Chromium `/opt/pw-browsers`, `file://`):
   - tidak ada permintaan jaringan ke luar (rute `**/*` dicatat; hanya `file://` yang boleh);
   - tangkapan layar 1366 × 768 dan 390 × 844;
   - saringan, salin, unduh CSV/JSON, dan pemutar narasi bekerja;
   - dengan JavaScript dimatikan, semua kartu tetap terbaca;
   - cetak ke PDF satu kelompok (Tokoh) tanpa kartu terpotong.
3. **Uji `--bundle`**: buka `build/katalog-aset/index.html` dari folder lain. Pemutar audio dan ikon aplikasi harus tetap tampil.
4. **Uji templat**: impor `templat/peta-kedu-1400x900.svg` ke Inkscape. Titik pin harus tepat di (910, 369), (784, 639), (392, 495). Bandingkan dengan tangkapan layar `/peta` saat `map.kedu` diisi templat itu sebagai gambar sementara.
5. Sebelum push, periksa diff sekali lagi secara kritis. Pull request dibuat hanya bila diminta.

## 7. Berkas utama

**Baru:**
- `docs/katalog-aset/README.md`, `build.php`
- `docs/katalog-aset/lib/{Sources,Catalog,Guides,Page}.php`
- `docs/katalog-aset/data/{gaya,gambar,ikon,audio,narasi}.php`
- `docs/katalog-aset/templat/*.svg` dan `index.html` (hasil build)
- `docs/katalog-aset/produksi.csv` (kepala kolom saja)
- `tests/unit/AssetCatalogTest.php`

**Diubah (tanpa perubahan perilaku):** `app/Helpers/ui_helper.php` (`icon_paths()`), `app/Database/Seeds/LevelSeeder.php` (`LEVELS`), dan `.gitignore` (`/build/`).

**Docs:** `README.md`, `docs/05_VIEW_UI.md`, `docs/audio.md`, `docs/naskah-cerita.md`, `docs/bank-soal/README.md` (tautan).

## 8. Risiko dan pertanyaan terbuka

| Risiko / pertanyaan | Penanganan |
|---|---|
| Gaya berbeda-beda antaralat AI | Satu alat utama per kelompok (§10.3); `--sref`/referensi gaya dan seed yang sama; lembar tokoh dibuat dan disetujui lebih dulu |
| Keakuratan budaya (pakaian Jawa, candi, tarian, upacara) | Setiap kartu punya daftar periksa; guru atau budayawan setempat meninjau sebelum aset diunggah (kolom `pemeriksa` di lembar produksi) |
| Validitas penelitian: mengganti gambar puzzle, objek `cari`, atau menambah gambar `mgl-5` dapat mengubah tingkat kesulitan butir | Diputuskan peneliti. Perubahan pada butir yang sudah dipakai dicatat sebagai rilis baru (`game_releases`), seperti aturan kunci jawaban di `docs/bank-soal/README.md` |
| Lisensi keluaran alat AI dan lisensi Commons (atribusi, *share-alike*) | Ketentuan layanan alat diperiksa untuk distribusi pendidikan; kredit Commons wajib ditulis di kolom `credit`; tanpa nama seniman hidup di prompt; tanpa kloning suara orang nyata |
| Tanda jebakan bocor ke siswa | Halaman internal, `noindex`, tidak di `public/`, tidak dipasang di server sekolah |
| iPad lama tidak menampilkan WebP | Keluaran JPG/PNG; media Pustaka `.webp` (lambang Wonosobo) dikonversi ke PNG saat dilokalkan |
| Tirai "Membuka Peta Kedu" berhenti menunggu setelah 8 detik | Anggaran ukuran berkas di §10.1 (latar ≤ 450 KB, peta ≤ 600 KB, frame tokoh ≤ 250 KB) |
| **Pertanyaan:** perlukah ilustrasi khusus "Jaka memegang kunci" untuk kotak "Mengapa kata sandi harus kuat?" (`docs/05` menyebutnya, kode memakai pose `bow`)? | Bila ya, perlu slot baru dan perubahan `register.php`; di luar rencana ini |
| **Pertanyaan:** gaya blangkon Mbah Kedu (mondolan gaya Yogyakarta atau trepes gaya Surakarta) dan motif jarik | Diputuskan bersama budayawan sebelum lembar tokoh dibuat (§9.4) |
| **Pertanyaan:** perlukah efek `page` dan musik `library` yang disebut `core/audio.js` tetapi tidak dipanggil? | Tidak dibuat sampai ada fiturnya |

---

# Bagian II — Isi katalog

Bagian ini menjadi isi `docs/katalog-aset/data/*.php` pada Fase B. Semua ukuran, posisi, dan aturan potong diturunkan dari kode (§2). Bila kode berubah, bagian ini ikut diperbarui, dan uji Fase E menandainya.

## 9. Panduan gaya visual

Identitas GELITA adalah **navy gelap + emas**: judul serif klasik (Cinzel), isi sans humanis (Plus Jakarta Sans), angka mono (`docs/05`). Suasana cerita adalah dataran Kedu pada malam hingga fajar, diterangi lentera, diselimuti kabut. Gambar permainan mengikuti suasana itu: ilustrasi buku cerita bergaya lukisan, hangat, tenang, dan hormat pada budaya Jawa.

### 9.1 Palet warna

Nilai heksadesimal diambil dari `public/assets/css/tokens.css`, sehingga gambar menyatu dengan antarmuka.

| Token | Hex | Pemakaian dalam gambar |
|---|---|---|
| `--navy-900` | `#0B1320` | bayangan terdalam, langit malam |
| `--navy-800` | `#121C2E` | langit malam, latar belakang jauh |
| `--navy-700` | `#1B2740` | gunung jauh, kabut malam |
| `--navy-600` | `#26355A` | langit senja/fajar |
| `--gold-500` | `#DFC087` | cahaya lentera, sorotan utama |
| `--gold-600` | `#C7A265` | emas antik, logam kuningan |
| `--gold-300` | `#F0DFB8` | inti cahaya, kilau |
| `--parchment` | `#F4E7CB` | kertas, perkamen, kain terang |
| `--brown-700` | `#4A3218` | kayu jati, batik sogan gelap |
| `--brown-500` | `#8A6A32` | kayu, tanah, anyaman bambu |
| `--ok` | `#8FD14F` | aksen hijau daun (hemat) |
| `--warn` | `#F0A868` | cahaya matahari terbit |
| `--bad` | `#D9704A` | terakota, genteng (hemat) |
| (kabut) | `#8C97AA` → `#5B6478` | Kabut Lupa: biru-kelabu dingin |

Aturan: satu sumber cahaya hangat utama (lentera atau fajar); warna jenuh hanya untuk titik fokus; latar tidak pernah hitam pekat, karena selalu ditimpa gradien navy 35% di atas → 55% → 75% di bawah (`layout.css:39`, `.scene::after`).

### 9.2 Blok prompt yang dipakai ulang

Pembangun mengganti penanda `{…}` dengan teks di bawah. Ubah gaya di sini saja.

`{GAYA}`

```text
storybook illustration for an Indonesian children's educational game (ages 8–14), painterly 2D digital gouache with soft brush texture and subtle paper grain, clean readable silhouettes, gentle volumetric light, Central Javanese Kedu highlands, warm lantern-gold highlights (#DFC087, #F0DFB8) against deep night navy and twilight blue (#0B1320, #1B2740, #26355A), earthy browns (#4A3218, #8A6A32), soft blue-grey mist, calm and hopeful mood, culturally respectful, no text
```

`{NEGATIF}`

```text
text, letters, numbers, writing, caption, watermark, signature, logo, user interface, frame, border, photorealistic, photograph, 3D render, CGI, plastic look, anime, chibi, manga, horror, creepy, gore, blood, weapon pointed at viewer, smoking, cigarette, alcohol, neon colours, oversaturated, harsh black shadows, blurry, low resolution, jpeg artifacts, deformed hands, extra fingers, extra limbs, duplicate, cropped subject, European castle, Chinese or Japanese architecture, snow
```

`{LATAR}` (latar layar 16:9)

```text
wide 16:9 cinematic landscape composition, main subject inside the central 75% of the width and the central 80% of the height, horizon between 55% and 65% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground
```

`{TRANSPARAN}` (objek, lencana, ikon raster, tokoh)

```text
single isolated subject, centred, whole subject visible with even margins, plain flat light grey background (#D9D9D9) for clean background removal, no cast shadow, no ground, no scenery
```

Pada gpt-image (OpenAI), ganti kalimat latar abu-abu dengan parameter `background: "transparent"` dan format PNG.

`{LENTERA}`

```text
the Lentera Kedu: an antique hexagonal brass hand lantern about 30 cm tall with six clear glass panes, a pierced brass cap with a kawung pattern, a ring handle on top, and a warm golden flame inside casting soft light
```

`{KABUT}`

```text
the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that dissolve like fading ink strokes and never form readable letters, eerie but never scary
```

`{SERPIHAN}`

```text
Shards of Light: small faceted crystal shards of warm golden light (#F0DFB8 core, #DFC087 edges) with a gentle glow and tiny sparkles
```

`{JAKA}` dan `{KEDU}` ada di §9.4. `{FRAME}` (khusus frame tokoh):

```text
full-body character, three-quarter view facing screen right, standing on an invisible ground line near the bottom, whole figure visible including feet and headwear with even side margins, soft warm key light from the upper left and a faint cool rim light from the right, clean painterly edges suitable for a cut-out, no scenery, no cast shadow
```

### 9.3 Motif cerita

| Motif | Arti dalam cerita (`docs/naskah-cerita.md`) | Wujud visual | Muncul di |
|---|---|---|---|
| Cahaya Kedu | cahaya ilmu dan budaya | titik-titik cahaya keemasan hangat di jendela rumah, kunang-kunang cahaya | latar pembuka, penutup, `bg.welcome` |
| Lentera Kedu | wadah cahaya; dibawa Jaka | `{LENTERA}`. Varian "retak": satu kaca retak rambut, nyala lemah berkedip | ikon aplikasi, emblem, tokoh Jaka, slide pembuka 2–5, penutup, Balai Refleksi |
| Kabut Lupa | lupa, membaca terburu-buru, percaya kabar tanpa memeriksa | `{KABUT}`; di wilayah: kabut menutup papan petunjuk (Temanggung), relief (Magelang), dan puncak kawah (Wonosobo) | slide pembuka 3–4, peta, latar wilayah, tirai |
| Serpihan Cahaya | 15 hadiah tantangan | `{SERPIHAN}`; 5 per wilayah | slide pembuka 4 & 7, tirai peta, adegan tuntas |

### 9.4 Lembar tokoh

Buat dan setujui **lembar tokoh** (turnaround + ekspresi) sebelum membuat frame pose apa pun. Lembar ini hanya acuan, tidak diunggah ke permainan. Simpan di `docs/katalog-aset/referensi/` supaya semua pembuat memakai rujukan yang sama.

**Jaka** — kode `jaka`, slot `char.jaka.*`. Anak laki-laki ±11 tahun, bersemangat dan jujur, Pembawa Lentera.

`{JAKA}`

```text
Jaka, an 11-year-old Javanese boy from the Kedu highlands of Central Java, slim, about five and a half heads tall, warm light-brown (sawo matang) skin, round-oval face, big dark-brown eyes, thick black eyebrows, short slightly messy black hair with a small cowlick, a brown-and-gold batik kawung head cloth (iket) tied with a small knot at the back, a short-sleeved indigo-and-cream striped lurik shirt with a mandarin collar and two wooden buttons, knee-length earth-brown shorts, simple brown leather sandals, a small woven cloth satchel on a strap across his chest, carrying the Lentera Kedu by its ring handle in his right hand
```

**Mbah Kedu** — kode `mbah_kedu`, slot `char.kedu.*`. Kakek Jawa ±75 tahun, lembut dan bijak, penjaga lentera; tenaganya ikut melemah bila cahaya meredup.

`{KEDU}`

```text
Mbah Kedu, a kind elderly Javanese man of about 75, thin and slightly stooped, about seven heads tall, warm brown wrinkled skin, gentle eyes with smile lines, short white moustache and short white beard, bushy white eyebrows, a sogan-brown batik blangkon head wrap, a long-sleeved dark-brown and cream striped lurik surjan jacket, a sogan-brown batik jarik with a small truntum pattern wrapped to mid-shin, brown leather slippers (selop), a smooth dark teak walking stick with a simple carved knob in his right hand
```

**Aturan yang dikunci untuk semua frame.** Angka ini juga digambar di `templat/tokoh-700x900.svg`.

| Aturan | Jaka | Mbah Kedu | Alasan dari kode |
|---|---|---|---|
| Kanvas | 700 × 900 px, PNG transparan | sama | `$assetSizes['char.*']`, ditolak bila beda |
| Arah hadap | tiga-perempat, **menghadap kanan layar** | sama | Mbah Kedu dicerminkan di dialog (`game.css:416`) sehingga keduanya berhadapan; di layar lain tokoh berdiri di kiri, menghadap teks |
| Garis tanah (telapak kaki) | y = 876 | y = 876 | tokoh dirender `max-height`, jadi posisi kaki harus sama agar tidak "melompat" saat pose berganti |
| Tinggi figur | ±720 px (puncak kepala y ±156) | ±810 px (puncak blangkon y ±66) | kedua gambar ditampilkan setinggi sama; beda tinggi di kanvas yang membuat Jaka tampak anak-anak |
| Tinggi kepala (dagu → puncak iket/blangkon) | ±130 px | ±115 px | ukuran yang tidak berubah antarpose, jadi dipakai untuk menyamakan skala (§10.5), juga pada pose membungkuk |
| Sumbu badan | x = 350 ± 20 | x = 350 ± 20 | dicerminkan tanpa bergeser |
| Margin sisi | ≥ 40 px | ≥ 40 px | `drop-shadow` dan gerak tangan tidak terpotong |
| Tulisan/lambang | tidak ada | tidak ada | dicerminkan di dialog |
| Cahaya | kunci hangat dari kiri atas, pinggir dingin dari kanan | sama | konsisten antar pose |
| Frame 1 | **pose kunci yang terbaca sendiri** | sama | saat ini hanya frame 1 yang tampil (`components/character.php:18`) |

**Prompt lembar putar (acuan, 2048 × 1024):**

```text
{GAYA} character design turnaround sheet of {JAKA}, four full-body views in a row: front, three-quarter facing right, side facing right, back; neutral standing pose, arms relaxed, identical outfit and proportions in every view, flat even lighting, plain warm off-white background, generous spacing between views, no text
```

Ganti `{JAKA}` dengan `{KEDU}` untuk lembar Mbah Kedu.

**Prompt lembar ekspresi (acuan):**

```text
{GAYA} expression sheet of {JAKA}, six head-and-shoulders portraits in a 3×2 grid: calm curious (idle), joyful grin (happy), respectful calm with lowered eyes (bow), sad and sorry (sad), frightened wide eyes (afraid), confident determined (determined); same face, same hair and head cloth in every portrait, three-quarter view facing right, plain warm off-white background, no text
```

Untuk Mbah Kedu: *gentle calm (idle), warm smile (smile), worried frown looking into the distance (worried), exhausted and pale with half-closed eyes (weak)*, dalam kisi 2 × 2.

**Menjaga konsistensi:**

- **Midjourney:** `--cref <URL lembar putar>` dengan `--cw 100` untuk wajah dan pakaian (v6), atau *Omni Reference* `--oref` (v7); `--sref <URL gaya yang disetujui>`; seed tetap.
- **SDXL:** IP-Adapter (Plus/FaceID) dari lembar putar, ControlNet OpenPose dari sketsa pose, dan opsional LoRA yang dilatih dari 15–30 gambar tokoh yang sudah disetujui.
- **Flux:** Kontext/Redux untuk mengubah pose dengan identitas tetap.
- **gpt-image:** sunting dengan gambar rujukan.
- **Paling andal:** ilustrator melukis frame dari lembar tokoh. AI dipakai hanya untuk sketsa awal.
- **Pertanyaan budaya sebelum lembar tokoh dibuat:**
  - gaya blangkon Mbah Kedu: mondolan (Yogyakarta) atau trepes (Surakarta);
  - motif jarik: usulan *truntum*, bermakna tuntunan;
  - motif iket Jaka: usulan *kawung*.

  Putuskan bersama budayawan atau guru setempat.

### 9.5 Aturan budaya, fakta, dan keselamatan anak

Setiap kartu memuat daftar periksa dari aturan ini. Kolom `pemeriksa` di lembar produksi wajib diisi sebelum aset diunggah.

1. **Tembakau hanya sebagai tanaman.** Tidak ada orang merokok, rokok, atau asap (bank soal: "tanpa orang merokok"; Pustaka: "tembakau adalah hasil kebun untuk orang dewasa").
2. **Tempat ibadah dihormati.**
   - Tidak ada orang memanjat stupa atau candi, atau menyentuh relief (Pustaka Magelang dan butir `wnb-3` mengajarkannya).
   - Prosesi Waisak digambar khidmat.
   - Arca digambar sesuai foto rujukan.
3. **Bentuk situs nyata tidak dikarang.** Candi Borobudur, Mendut, Pawon, Pringapus, dan kompleks Arjuna, juga Telaga Warna dan Gunung Sindoro–Sumbing:
   - ilustrasi memakai foto Commons di bank soal sebagai rujukan bentuk;
   - AI tidak boleh "menambah" tingkat, stupa, atau ornamen.
4. **Pakaian dan kesenian.** Kuda lumping, Topeng Ireng (kuluk bulu, garis hitam di atas bedak putih, sepatu bot, lonceng kaki), dan Topeng Lengger digambar sesuai deskripsi di bank soal dan Pustaka, lalu ditinjau guru.
5. **Anak rambut gimbal (Dieng) tidak digambar sebagai wajah yang dapat dikenali.** Pustaka sendiri meminta tidak memotret anak tanpa izin keluarganya.
6. **Tokoh anak** digambar sopan dan aman, tidak dalam bahaya nyata, dan ketakutannya tidak berlebihan (pose `afraid` = kaget/cemas, bukan ngeri).
7. **Tanpa teks di gambar.** Pengecualian: tombol Mulai dan logo, yang tulisannya disusun manual dengan font repo (§11.2). Nama tempat, label peta, dan angka ditulis antarmuka, karena permainan dwibahasa.
8. **Jebakan arena `cari` tidak boleh berbeda rupa** (§11.8.2).
9. **Tanpa nama seniman hidup** di prompt, dan tanpa meniru gaya studio tertentu.

## 10. Ukuran, rasio, dan cara membuatnya dengan tepat

### 10.1 Tabel induk

"Wajib" berarti `MediaStore` menolak unggahan yang ukurannya berbeda. "Disarankan" berarti tidak ditolak, tetapi ukuran itulah yang cocok dengan cara CSS menampilkannya.

| Jenis | Kode berkas (pola) | Ukuran (px) | Rasio | Format | Transparan | Batas berkas | Penegakan | Cara tampil (CSS) |
|---|---|---|---|---|---|---:|---|---|
| Latar layar | `bg.*` (termasuk `bg.{wilayah}.region`, `bg.loading`, `bg.cerita-*`) | 1920 × 1080 | 16:9 | JPG q80, sRGB | tidak | 450 KB | wajib | layar penuh `object-fit: cover`, gradien navy 35–75% |
| Latar node tantangan | `challenge.{ref}.bg` | 1920 × 1080 | 16:9 | JPG q80 | tidak | 450 KB | disarankan | idem |
| Peta Kedu | `map.kedu` | 1400 × 900 | 14:9 | JPG q85 / PNG-24 | tidak | 600 KB | disarankan | kanvas dikunci `--map-ratio: 1400 / 900`, `cover`, tidak terpotong |
| Peta wilayah | `map.region.{wilayah}` | 1400 × 900 | 14:9 | JPG q85 / PNG-24 | tidak | 600 KB | disarankan (Kelengkapan menandai "ukuran salah") | idem, bingkai maks. 900 px |
| Frame tokoh | `char.{jaka,kedu}.{pose}.{1–3}` | 700 × 900 | 7:9 | PNG-32 | ya | 250 KB | wajib | `width: auto`, tinggi 90–560 px, `drop-shadow`; Mbah Kedu dicerminkan di dialog |
| Logo halaman awal | `ui.logo-hero` | 1600 × 600 | 8:3 | PNG-32 | ya | 250 KB | wajib | maks. 880 px × 46vh (40vh di ponsel) |
| Emblem logo | `ui.logo` | 512 × 512 | 1:1 | PNG-32 | ya | 80 KB | bebas | tinggi 36 px (kepala panel), lebar 120 px (login staf), maks. 560 px (cadangan halaman awal) |
| Tombol Mulai | `ui.btn-start`, `ui.btn-start.en` | 720 × 240 | 3:1 | PNG-32 | ya | 80 KB | wajib | lebar `min(360px, 64vw)` |
| Lencana wilayah | `reward.badge.{wilayah}` | 320 × 320 | 1:1 | PNG-32 | ya (sudut) | 60 KB | disarankan | 72 × 72 di atas cakram emas |
| Adegan tantangan | `challenge.{ref}.scene` | 1280 × 720 | 16:9 | JPG q80 | tidak | 350 KB | disarankan | `cari`: kanvas 16:9 tanpa potong; `rumpang`: kolom kiri 34% |
| Gambar puzzle | `challenge.item.{tmg,mgl}-1-0N` | 900 × 900 | 1:1 | JPG q85 | tidak | 350 KB | disarankan | papan persegi maks. 480 px, 3 × 3 keping |
| Objek cari | `challenge.item.tmg-4-NN` | 240 × 240 | 1:1 | PNG-32 | ya | 80 KB | disarankan | tombol bundar selebar 12% adegan (±154 px pada 1280), `contain` |
| Gambar soal pilihan | `challenge.item.{ref}-NN` | 960 × 640 | 3:2 | JPG q80 | tidak | 200 KB | bebas | tinggi maks. 200 px, tanpa potong |
| Gambar kartu boleh | `challenge.item.{ref}-NN` | 480 × 360 | 4:3 | JPG q80 | tidak | 80 KB | disarankan (`challenge.card`) | lebar kartu, tinggi maks. 160 px, `cover` → pita |
| Gambar opsi | `challenge.option.{butir}-{a–d}` | 400 × 400 | 1:1 | PNG-32 | ya | 80 KB | disarankan (`challenge.option`) | tinggi maks. 140 px, `contain` |
| Gambar bacaan | `passage.{kunci}` | 960 × 640 | 3:2 | JPG q80 | tidak | 200 KB | bebas | tinggi maks. 180 px |
| Media Pustaka | `library.{wilayah}.p{n}.{i}` | 960 × 640 | 3:2 | JPG q80 (PNG untuk lambang) | tidak | 250 KB | disarankan (`library.image`) | media pertama 3:2; berikutnya 4:3 `cover`; sampul rak 16:9 |
| Poster video Pustaka | `library.{wilayah}.p{n}.{i}.poster` | 1280 × 720 | 16:9 | JPG q80 | tidak | 200 KB | bebas | bingkai 16:9 `cover` |
| Ikon aplikasi | `public/assets/app/*`, `public/favicon.ico` | 512 / 192 / 180 / 48-32-16 | 1:1 | PNG / ICO / SVG | *any*: ya; *maskable* & Apple: tidak | 60 KB | berkas statis | manifest, layar utama, tab |
| Pengganti | `ui.placeholder` | 400 × 300 (SVG) | 4:3 | SVG | — | 4 KB | — | `media_src()` saat aset belum ada |
| Direncanakan | `ui.pin`, `ui.lantern`, `map.pin.*`, `map.lantern.*` | 256 × 256 | 1:1 | PNG-32 | ya | 40 KB | — | pin 56 px, lentera 28–30 px |
| Direncanakan | `avatar.1`–`avatar.10` | 512 × 512 | 1:1 | PNG-32 | ya | 80 KB | — | lingkaran ≥ 64 px |

Anggaran ukuran berkas punya alasan: tirai "Membuka Peta Kedu" memuat peta, latar, dan frame tokoh dengan progres nyata, lalu berhenti menunggu setelah 8 detik (`docs/05` §Tirai). Anjuran JPEG q80 dan PNG ≤ 400 × 400 untuk objek berasal dari `docs/08` §Ukuran aset. Hindari WebP: iPad lama belum menampilkannya.

### 10.2 Zona aman menurut cara gambar dipotong

| Jenis | Yang terjadi di layar | Zona aman (px pada ukuran asli) |
|---|---|---|
| Latar `bg.*` biasa | `cover`. Tablet 4:3 memotong kiri–kanan 12,5%; ponsel mendatar (844 × 390, 2,16:1) memotong atas–bawah ±9%. HUD 72 px di atas, navigasi 76 px di bawah | subjek penting di x 240–1680, y 96–984 |
| Latar sinematik (`bg.intro`, `bg.cerita-*`, dan latar layout di `body.is-cinematic`) | ditambah Ken Burns 1,02 → 1,14 dan geser (`cinematic.css:398`); kotak teks di bawah (±x 736–1716, y ≥ 700); tokoh di kolom kiri (x ≤ 540); gradien menggelapkan bagian bawah | subjek di x 340–1600, y 160–930; sepertiga bawah tenang; kolom kiri tenang pada slide bertokoh |
| Latar wilayah di overlay Kenali | opasitas 55% + gradien atas–bawah (`cinematic.css:602`) | detail halus hilang; pakai bentuk besar yang tetap terbaca samar |
| Latar tirai `bg.loading` | opasitas 45% + Ken Burns (`cinematic.css:42`); lentera, orbit serpihan, judul, dan status di tengah | zona aman sinematik; tengah tenang tanpa detail; keseluruhan bernada sedang, bukan gelap, agar tetap tampak pada 45% |
| Peta Kedu / wilayah | tidak terpotong; sudut membulat 12 px; label pin berlatar `--veil-strong` | zona tenang ±130 × ±90 px (Kedu) / ±110 × ±80 px (wilayah) di sekitar setiap titik pin; tepi 24 px bebas detail penting |
| Adegan `cari` | tidak terpotong; 11 tombol objek bundar di posisi tetap | kotak objek di §11.8.2 kosong dari benda; area lain bebas "benda palsu" |
| Puzzle | tidak terpotong; 3 × 3 keping bercelah 3 px; nomor keping di pojok kiri atas | pojok kiri atas 50 × 38 px tiap keping (300 × 300) tanpa detail penting; setiap keping punya ciri pembeda |
| Frame tokoh | tidak terpotong; dicerminkan (Mbah Kedu di dialog) | §9.4 |
| Logo & tombol | tidak terpotong; `drop-shadow` + kilau | margin 48 px (logo), 16 px (tombol) |
| Lencana | 72 px di atas cakram emas | lingkaran medali Ø 312 px memenuhi kanvas |
| Pustaka | media pertama 3:2 utuh; media ke-2 dan seterusnya dipotong 4:3; sampul rak (media pertama halaman 1) dipotong 16:9 | subjek di x 53–907 dan y 50–590 |
| Kartu `boleh` | dipotong menjadi pita ±2,25:1 | subjek di y 70–290 |
| Opsi | `contain`, maks. 140 px | satu benda besar, siluet tegas |

### 10.3 Alat dan parameter

| Kelompok | Alat utama yang disarankan | Alasan |
|---|---|---|
| Latar, latar slide, peta | Midjourney atau Flux, lalu *paintover* manual untuk zona pin | gaya lukisan kuat; posisi pin perlu disunting tangan |
| Frame tokoh | SDXL/Flux + ControlNet OpenPose + IP-Adapter/LoRA, atau ilustrator | tiga frame harus identik kecuali gerak kecil |
| Objek `cari`, lencana, ikon raster, avatar | gpt-image (`background: transparent`) atau Flux + penghapus latar | butuh transparansi bersih dan gaya seragam |
| Logo & tombol Mulai | vektor (Inkscape, Figma, Illustrator) dengan font repo; AI hanya untuk ornamen | teks AI tidak andal |
| Media Pustaka | foto Wikimedia Commons (sudah di bank soal) | isi faktual |

Parameter awal: seed tetap per seri, dan satu *style reference* yang disetujui untuk seluruh seri.

- **Midjourney:** `--ar W:H --style raw --stylize 100–250 --seed N`.
- **SDXL:** 30–40 langkah, CFG 5–7, DPM++ 2M Karras.
- **Flux dev:** 28–35 langkah, *guidance* 3–3,5.
- **gpt-image:** `quality: "high"`, dengan `size` dari tabel §10.4.

Periksa ulang dokumentasi alat sebelum produksi, karena ukuran dan fitur alat sering berubah.

### 10.4 Ukuran generate → ukuran target

Alat AI jarang menghasilkan ukuran target secara langsung. Buat pada rasio yang sama atau sedekat mungkin, lalu potong (atau tambah bidang transparan) dan ubah ukuran.

| Target | Rasio | Midjourney | SDXL | Flux (kelipatan 16) | gpt-image | Langkah akhir |
|---|---|---|---|---|---|---|
| 1920 × 1080 | 16:9 | `--ar 16:9` → upscale | 1344 × 768 → potong 1344 × 756 → ×1,4286 | 1920 × 1088 → potong 4 px atas & bawah | 1536 × 1024 → potong 1536 × 864 → ×1,25 | JPG q80 |
| 1280 × 720 | 16:9 | `--ar 16:9` | 1344 × 768 → potong 1344 × 756 → ×0,9524 | 1280 × 720 langsung | 1536 × 1024 → potong 1536 × 864 → ×0,8333 | JPG q80 |
| 1400 × 900 | 14:9 | `--ar 14:9` | 1344 × 864 → ×1,0417 | 1792 × 1152 → ×0,78125 | 1536 × 1024 → potong 1536 × 987 → 1400 × 900 | JPG q85 |
| 700 × 900 | 7:9 | `--ar 7:9` | 896 × 1152 → transformasi §10.5 | 896 × 1152 → transformasi §10.5 | 1024 × 1536 transparan → transformasi §10.5 | PNG-32 |
| 1600 × 600 | 8:3 | `--ar 8:3` | 1536 × 576 → ×1,0417 | 2048 × 768 → ×0,78125 | 1536 × 1024 → potong 1536 × 576 → ×1,0417 | vektor lebih baik |
| 720 × 240 | 3:1 | `--ar 3:1` | 1536 × 512 → ×0,46875 | 1440 × 480 → ×0,5 | 1536 × 1024 → potong 1536 × 512 → ×0,46875 | vektor lebih baik |
| 960 × 640 | 3:2 | `--ar 3:2` | 1152 × 768 → ×0,8333 | 960 × 640 langsung | 1536 × 1024 → ×0,625 | JPG q80 |
| 480 × 360 | 4:3 | `--ar 4:3` | 1152 × 864 → ×0,4167 | 1024 × 768 → ×0,46875 | 1536 × 1024 → potong 1365 × 1024 → ×0,3516 | JPG q80 |
| 900, 512, 400, 320, 256, 240 persegi | 1:1 | `--ar 1:1` | 1024 × 1024 → ubah ukuran | 1024 × 1024 → ubah ukuran | 1024 × 1024 → ubah ukuran | PNG-32 / JPG |

### 10.5 Pasca-produksi (ImageMagick 7, pngquant, oxipng)

Latar dan peta (potong tengah ke ukuran tepat, sRGB, tanpa metadata):

```bash
magick masuk.png -resize 1920x1080^ -gravity center -extent 1920x1080 \
  -colorspace sRGB -strip -sampling-factor 4:2:0 -quality 80 -interlace JPEG bg-welcome.jpg
```

Frame tokoh. Ketiga frame satu pose memakai **transformasi yang sama**: skala dari tinggi kepala dan jangkar di telapak kaki. Jangan `-trim` per frame, karena kaki akan bergeser dan skala tokoh berubah antarpose.

```bash
# 1) latar sudah transparan (gpt-image) atau dihapus dulu (rembg / Krita / GIMP)
# 2) ukur sekali pada frame 1 mentah (px):
#    X = sumbu badan di telapak kaki, KAKI = y telapak kaki,
#    KEPALA = tinggi kepala dari dagu sampai puncak iket (Jaka) / blangkon (Mbah Kedu)
X=512; KAKI=1478; KEPALA=268                  # contoh angka
S=$(echo "130 / $KEPALA" | bc -l)             # Jaka 130 px; Mbah Kedu 115 px
# 3) titik (X, KAKI) dipindah ke (350, 876) dengan skala S, sama untuk ketiga frame
for n in 1 2 3; do
  magick jaka-happy-$n.png -background none -virtual-pixel transparent \
    -define distort:viewport=700x900+0+0 -distort SRT "$X,$KAKI $S 0 350,876" \
    +repage -strip PNG32:out/jaka-happy-$n.png
done
pngquant --quality=75-92 --skip-if-larger --force --ext .png out/*.png && oxipng -o 4 --strip safe out/*.png
```

Objek `cari` (240 × 240, bentuk utama di dalam lingkaran Ø 216):

```bash
magick objek.png -trim +repage -resize 204x204 -background none -gravity center -extent 240x240 -strip PNG32:tmg-4-01.png
```

Lencana (320 × 320, medali Ø 312):

```bash
magick lencana.png -trim +repage -resize 312x312 -background none -gravity center -extent 320x320 -strip PNG32:badge-temanggung.png
```

Periksa semua hasil:

```bash
magick identify -format "%f  %wx%h  %[colorspace]  %[channels]  %b\n" out/*
```

### 10.6 Daftar periksa mutu gambar

- [ ] Ukuran piksel tepat dan rasio tepat (wajib untuk `bg.*`, `char.*`, `ui.logo-hero`, `ui.btn-start*`).
- [ ] Tidak ada huruf, angka, atau "tulisan palsu" buatan AI. Periksa papan, kain, relief, dan langit.
- [ ] Tidak ada tanda air atau tanda tangan.
- [ ] Zona aman dipatuhi: tumpangkan templat §4.5 pada 50% opasitas.
- [ ] Gaya cocok dengan aset yang sudah disetujui (bandingkan berdampingan).
- [ ] Tokoh: menghadap kanan, kaki di y 876, tinggi figur sesuai, pakaian persis lembar tokoh, jari lengkap, frame 1 terbaca sendiri.
- [ ] Transparansi bersih, tanpa halo putih atau hijau. Cek di atas navy `#0B1320` **dan** perkamen `#F4E7CB`.
- [ ] Budaya dan fakta diperiksa guru atau budayawan (§9.5).
- [ ] Ukuran berkas dalam anggaran §10.1; sRGB; metadata dan lokasi GPS dibuang (`-strip`).

## 11. Katalog gambar

### 11.1 Cara membaca butir

Setiap butir di bawah menjadi satu kartu di halaman. Bentuknya:

- **Kode berkas.** Untuk slot resmi, `asset_key` sekaligus nama berkas bawaan bila di-commit ke repositori (`MediaAssetSeeder` mencocokkan path tanpa ekstensi). Untuk konten, kode berkas diunggah lewat editor atau **Gambar & suara**.
- **Spesifikasi.** Ukuran, rasio, format, dan penegakan mengikuti §10.1. Butir hanya menyebut hal yang berbeda atau khusus.
- **Prompt.** Bahasa Inggris dengan penanda blok §9.2. **Negatif** selalu `{NEGATIF}` ditambah frasa khusus butir.
- **Cek.** Pemeriksaan khusus butir, di luar daftar umum §10.6.

Aset slot resmi dapat dipasang dengan dua cara (`docs/08` §Assets):

- commit ke `public/assets/{ui,bg,char,map,reward,challenge,library}/`, lalu `php spark gelita:media:scan`;
- unggah lewat panel dengan kode berkas yang sama.

### 11.2 Logo, tombol, dan pengganti (5)

#### `ui.logo-hero` — Logo landscape halaman awal · **wajib**

- **Tampil di:** `/` (`game/welcome.php`), maks. 880 px × 46vh (40vh di ponsel mendatar), dengan `drop-shadow`.
- **Spesifikasi:**
  - 1600 × 600 px (8:3), PNG-32 transparan, ≤ 250 KB;
  - berkas bawaan `public/assets/ui/logo-hero.png`.
- **Isi:** hanya kata **GELITA**, sama untuk kedua bahasa. Slot ini tidak punya varian bahasa, dan tagline sudah dirender sebagai teks tersembunyi (`welcome.php:33`).
- **Cara membuat:** tata huruf vektor, bukan AI.
  - Cinzel Bold (OFL, ada di `public/assets/fonts/cinzel-latin-700-normal.woff2`), jarak huruf +8%, tinggi huruf kapital 260–300 px.
  - Huruf **I** diganti siluet `{LENTERA}` bernyala.
  - Isi huruf gradien emas `#F0DFB8 → #DFC087 → #C7A265`, garis dalam cokelat `#4A3218`, cahaya luar `rgba(223,192,135,.35)`.
  - Ornamen AI dipakai hanya sebagai lapisan di belakang huruf.
- **Prompt ornamen:**

```text
{GAYA} ornamental title decoration for a game logo, wide 8:3 banner, large empty space in the centre reserved for a wordmark, delicate antique-gold filigree inspired by Javanese batik kawung and lung-lungan vine motifs curling from both sides, a small glowing brass lantern at the top centre, soft golden light rays, thin wisps of blue-grey mist along the bottom edge, perfectly symmetrical, isolated on a plain flat #0B1320 background for extraction, no letters
```

- **Negatif:** `{NEGATIF}, letters, wordmark, busy centre, asymmetry`
- **Cek:**
  - terbaca pada tinggi 150 px (ponsel);
  - margin 48 px bebas;
  - tidak ada tagline;
  - tepi kilau tetap halus di atas `bg.welcome`.

#### `ui.logo` — Emblem logo (panel, login staf, cadangan halaman awal) · bebas

- **Tampil di:**
  - kepala panel admin, tinggi 36 px, **di samping tulisan GELITA** (`layouts/admin.php:36`);
  - login staf, lebar 120 px di atas tulisan GELITA (`layouts/auth.php:31`);
  - cadangan halaman awal bila `ui.logo-hero` belum ada.
- **Spesifikasi:**
  - 512 × 512 px (1:1), PNG-32 transparan, ≤ 80 KB;
  - berkas bawaan `public/assets/ui/logo-gelita.png`.
- **Isi:** emblem **tanpa tulisan**, karena tulisan sudah ada di sebelahnya.

```text
{GAYA} circular emblem for the GELITA game: {LENTERA} glowing at the centre inside an octagonal antique-gold frame, a ring of tiny kawung batik motifs around it, three small golden shards orbiting the lantern, deep navy enamel background inside the frame, bold simple shapes that stay readable at 36 pixels, symmetrical, {TRANSPARAN}
```

- **Negatif:** `{NEGATIF}, letters, thin details, gradient mesh noise`
- **Cek:** terbaca pada 36 px dan 16 px (uji perkecil); tidak tertukar dengan ikon aplikasi, tetapi sekeluarga.

#### `ui.btn-start` dan `ui.btn-start.en` — Tombol Mulai bergambar · **wajib**

- **Tampil di:** `/`, lebar `min(360px, 64vw)`, berdenyut kilau; `:active` mengecil.
  - Locale `en` memakai `ui.btn-start.en` bila ada (`media_key_src_locale()`).
- **Spesifikasi:**
  - 720 × 240 px (3:1), PNG-32 transparan, ≤ 80 KB;
  - berkas `public/assets/ui/btn-start.png` dan `btn-start-en.png`.
- **Isi:** badan tombol 688 × 208 px di tengah (margin 16 px untuk kilau).
  - Tulisan **MULAI** / **START**: Plus Jakarta Sans ExtraBold (800, font repo), tinggi huruf ±84 px, warna `#0B1320`, disusun manual, tidak dibuat AI.
  - Siluet lentera kecil di kiri tulisan.
- **Prompt badan tombol (tanpa teks):**

```text
{GAYA} a single wide pill-shaped game button seen straight on, polished antique-gold surface with a soft bevel and a subtle kawung batik pattern engraved near both ends, warm inner glow, a thin darker gold rim, generous empty area in the middle for a label, perfectly symmetrical, {TRANSPARAN}
```

- **Negatif:** `{NEGATIF}, letters, text, icons in the centre, perspective tilt`
- **Cek:**
  - kontras tulisan ≥ 4,5:1;
  - kedua bahasa berukuran dan berletak sama;
  - tampak tajam pada layar 2×.

#### `ui.placeholder` — Gambar pengganti · sudah ada

- Berkas `public/assets/ui/placeholder.svg` (400 × 300): bingkai putus-putus emas, lentera, dan "Gambar belum tersedia". Tidak perlu dibuat ulang.
- Bila diganti, tetap SVG ≤ 4 KB dan tanpa berkas luar. Teksnya satu bahasa, karena hanya muncul saat aset belum diunggah.

### 11.3 Ikon aplikasi dan favicon (6 berkas statis)

Berkas ini ada di repositori dan masih "sementara" (`docs/05` §Aplikasi terpasang). Timpa dengan ukuran yang sama, tanpa perubahan kode. Bila Fase A `rencana-perbaikan-tampilan` selesai, berkas yang sama menjadi slot `app.*`.

| Berkas | Ukuran | Isi |
|---|---|---|
| `public/assets/app/icon.svg` | viewBox 512 | sumber vektor; lentera emas di langit malam |
| `public/assets/app/icon-512.png` | 512 × 512, RGBA, *purpose any* | lentera dengan margin 32 px; sudut boleh transparan |
| `public/assets/app/icon-192.png` | 192 × 192, RGBA | turunan 512, dipertajam |
| `public/assets/app/icon-maskable-512.png` | 512 × 512, **tanpa transparansi** | latar penuh navy; lentera di dalam lingkaran aman Ø 409,6 px (80%) |
| `public/assets/app/apple-touch-icon.png` | 180 × 180, **tanpa transparansi** | latar penuh; iOS membulatkan sudut sendiri |
| `public/favicon.ico` | 16 + 32 (+ 48) | glif lentera disederhanakan, tanpa gradien halus |

```text
{GAYA} app icon: {LENTERA} glowing warmly at the centre, front view, bold simplified shapes with thick outlines, radial deep-navy background from #26355A at the centre to #0B1320 at the edges, a soft golden halo behind the lantern, the lantern fits inside the central 70% circle, square 1:1, no text
```

- **Negatif:** `{NEGATIF}, thin lines, tiny details, photo`
- **Pasca:** susun ulang sebagai vektor di `icon.svg`, lalu ekspor:

```bash
rsvg-convert -w 512 -h 512 icon.svg -o icon-512.png
rsvg-convert -w 192 -h 192 icon.svg -o icon-192.png
rsvg-convert -w 512 -h 512 icon-maskable.svg -o icon-maskable-512.png   # versi latar penuh
rsvg-convert -w 180 -h 180 icon-maskable.svg -o apple-touch-icon.png
rsvg-convert -w 32 -h 32 favicon.svg -o f32.png && rsvg-convert -w 16 -h 16 favicon.svg -o f16.png
magick f16.png f32.png ../favicon.ico
```

- **Cek:**
  - uji *maskable* di maskable.app: lentera utuh pada topeng lingkaran;
  - `InstallableAppTest` tetap lulus;
  - perangkat yang sudah memasang perlu pasang ulang untuk melihat ikon baru.

### 11.4 Latar layar

#### 11.4.1 Latar umum (6) · semua **wajib** 1920 × 1080 (16:9), JPG q80, ≤ 450 KB

| Kode · berkas bawaan | Tampil di | Arah komposisi |
|---|---|---|
| `bg.welcome` · `assets/bg/bg-welcome.jpg` | halaman awal, `/mulai`, `/gerbang`, persetujuan, daftar, masuk, ganti sandi | langit tengah-atas kosong untuk logo; tombol di tengah bawah |
| `bg.auth` · `bg-auth.jpg` | login staf (opasitas 35%); direncanakan juga masuk/daftar siswa | tenang, kontras rendah, di balik panel form 440 px |
| `bg.intro` · `bg-intro.jpg` | cerita pembuka & penutup (slide tanpa latar sendiri) | zona aman sinematik; kiri dan bawah tenang |
| `bg.map` · `bg-map.jpg` | Peta Kedu, profil, rak Pustaka | tepi gelap; tengah akan tertutup bingkai peta |
| `bg.reflection` · `bg-reflection.jpg` | Balai Refleksi | hangat; panel Mbah Kedu dan formulir di atasnya |
| `bg.loading` · `bg-loading.jpg` | tirai "Membuka Peta Kedu" (opasitas 45%, Ken Burns) | tengah tenang tanpa detail untuk lentera, orbit serpihan, dan judul; nada sedang, bukan gelap |

`bg.welcome`

```text
{GAYA} {LATAR} magical dawn panorama of the Kedu plain seen from a gentle hill, looking north: Mount Sindoro and Mount Sumbing as silhouettes on the left, Merbabu and Merapi on the right, terraced rice and tobacco fields and small village roofs with warm golden window lights, thin blue-grey mist in the valleys, a deep navy sky warming to soft gold at the horizon with a few fading stars, tiny floating golden light motes; the upper middle is open calm sky reserved for a logo; the centre bottom is calm for a start button
```

`bg.auth`

```text
{GAYA} {LATAR} quiet interior of a Javanese pendopo reading pavilion at night, carved teak pillars, low bookshelves and rolled palm-leaf manuscripts in soft shadow, a single brass lantern glowing on a low table far to one side, very low contrast, soft focus, calm and safe atmosphere, large calm area in the centre
```

`bg.intro`

```text
{GAYA} {LATAR} the keeper's old wooden joglo house on a hillside at night, warm lantern light glowing through the carved door and windows, the misty Kedu plain spreading below with scattered golden village lights, a starry navy sky, gentle cinematic mood; the left third and the lower third are calm and uncluttered
```

`bg.map`

```text
{GAYA} {LATAR} deep night sky high above the Kedu highlands, soft bands of cloud and blue-grey mist, faint constellations, mountain silhouettes only along the very bottom edge, strong soft vignette towards the edges, very calm and low contrast, centre intentionally plain because a framed map will cover it
```

`bg.reflection`

```text
{GAYA} {LATAR} warm golden-hour view of an open Javanese pendopo hall with carved teak pillars and woven pandan mats, {LENTERA} restored and shining brightly on a low table, beyond the pillars the clear Kedu plain with the five volcanoes and no mist at all, peaceful proud accomplished mood, calm centre
```

`bg.loading`

```text
{GAYA} {LATAR} indigo night filled with slowly swirling {KABUT} in mid-tone blue-grey, a soft circular clearing of calm, detail-free space in the centre, a few {SERPIHAN} glinting at the edges of the clearing, gentle vignette, minimal detail, suspenseful but gentle, overall mid-tone rather than dark because the image is shown at 45% opacity
```

- **Negatif (keenam):** `{NEGATIF}, people, characters, bright white sky`
- **Cek:**
  - Tumpangkan `templat/latar-1920x1080.svg`.
  - Uji tampil di 1366 × 768, 1024 × 768, dan 844 × 390.
  - Gambar tetap terbaca di bawah lapisan gelap.
  - `bg.loading`: tidak berebut perhatian dengan lentera CSS di tengah.

#### 11.4.2 Latar per slide cerita pembuka dan penutup (14) · opsional

- **Tampil di:** `partials/cine-slides.php`. Kolom `dialogues.background_media_id` digambar sebagai `.cine-bg` dengan Ken Burns 30 detik. Tanpa kolom itu, slide memakai `bg.intro`.
- **Kode yang diusulkan:** `bg.cerita-intro-01` … `bg.cerita-intro-09` dan `bg.cerita-penutup-01` … `-05`. Nama berkas `assets/bg/bg-cerita-intro-01.jpg` menjadi kunci itu lewat `MediaAssetSeeder::keyFromPath()`, dan pola `bg.*` menegakkan 1920 × 1080.
- **Pemasangan:** kolom FK belum dapat diisi dari panel. Tunggu Fase A7 `rencana-perbaikan-tampilan` (`media-field` per slide), atau minta petugas teknis mengisi kolomnya.
- Semua slide memakai zona aman sinematik.
  - Slide bertokoh: kolom kiri (x ≤ 540) tenang, karena gambar tokoh berdiri di sana.
  - Slide narator: teks di tengah bawah.

| Slide · tokoh | Judul | Prompt (setelah `{GAYA} {LATAR}`) |
|---|---|---|
| `intro-01` · narator · *glow* | Dataran yang Bercahaya | `night panorama of the Kedu plain ringed by Mount Sindoro, Sumbing, Merapi, Merbabu and the Menoreh hills, every village glowing with a gentle golden light called the Light of Kedu, fireflies of light rising softly, wonder` |
| `intro-02` · narator | Cahaya dari Ilmu dan Budaya | `inside a joglo pendopo at night {LENTERA} stands on a carved teak stand; ribbons of its golden light drift upward and gently form faint silhouettes of a grandmother telling tales, a Borobudur-style relief panel, farmers singing in terraced fields, and a child reading, warm and reverent` |
| `intro-03` · narator · *fog* | Kabut Lupa | `the same glowing valley as village lights fade one by one, {KABUT} rising from the valley floor and swallowing the fields, a few loose blank pages and faded relief shapes dissolving into the mist, cold and hushed` |
| `intro-04` · narator · *shake* | Malam Lentera Retak | `close view of {LENTERA} on its stand, one glass pane cracking with a bright fracture line, {KABUT} coiling around it, fifteen {SERPIHAN} bursting upward into the night sky towards three distant lands, dramatic but not violent` |
| `intro-05` · Mbah Kedu *weak* · *dim* | Penjaga yang Menua | `dim interior of the keeper's joglo, the cracked lantern with a weak flickering flame on a low table in the right half, cool shadows, an empty calm left side where the keeper will stand, quiet and sad` |
| `intro-06` · Jaka *determined* · *glow* | Jaka, Pembawa Lentera | `the carved doorway of the joglo opening onto a misty dawn, a stone path leading down into the valley, hopeful golden rim light streaming in, calm left side for a standing boy` |
| `intro-07` · narator | Cara Mengumpulkan Serpihan | `bird's-eye storybook view of three lands under patches of mist: twin volcanoes with tobacco terraces, a great stepped stupa temple on a green hill, and a high cloud-covered plateau with lakes; five tiny golden sparks hidden in each land, clear and inviting` |
| `intro-08` · Mbah Kedu *smile* | Tiga Kunci Cahaya | `warm lantern-lit porch of the joglo, three softly glowing symbols floating in the light on the right side — a wooden signpost, a stone relief panel, and a bamboo kentongan drum — calm left side for the keeper` |
| `intro-09` · narator · *glow* | Perjalanan Dimulai | `sunrise over the Kedu plain seen from the top of a footpath, the path winding down into golden mist towards three distant regions, a small glow of lantern light leading the way, adventurous and bright` |
| `penutup-01` · narator · *glow* | Lentera Menyala Kembali | `hilltop at night, a small boy seen from behind as a silhouette lifting {LENTERA} high, fifteen {SERPIHAN} joining into it, a wave of golden light pouring across the whole plain, triumphant` |
| `penutup-02` · narator · *fog-lift* | Kabut Tersibak | `bright morning panorama as the last mist lifts: the twin volcanoes and tobacco terraces on the left, the great stupa temple in the centre, the cloud plateau and colourful lake on the right, clear blue-gold sky, relief and joy` |
| `penutup-03` · Mbah Kedu *smile* · *glow* | Mbah Kedu Pulih | `the keeper's porch at golden hour, the restored lantern shining brightly on its stand in the right half, flowers and green plants around, warm and healthy mood, calm left side` |
| `penutup-04` · Jaka *bow* | Janji Jaka | `a quiet village schoolyard under a big banyan tree at golden hour, children's books neatly stacked on a bamboo bench, soft lantern light, calm and sincere, calm left side` |
| `penutup-05` · Mbah Kedu *smile* | Penjaga Lentera | `dusk over the Kedu plain where many small lanterns glow in homes, a village school, and along the paths, light spreading from house to house, hopeful ending, calm left side` |

- **Negatif:** `{NEGATIF}, close-up faces, identifiable real people`
- **Cek:**
  - Siluet Jaka di `penutup-01` cocok dengan lembar tokoh: iket dan lentera.
  - Tidak ada wajah yang dapat dikenali.

### 11.5 Peta Kedu (1)

#### `map.kedu` — Peta Karesidenan Kedu · disarankan 1400 × 900

- **Tampil di:** `/peta`. Kanvas mengunci rasio `1400 / 900` (`map-kedu.php:130`).
  - Tiga pin (ikon Ø 56 px + label) berpusat di titik persen `levels.map_x/map_y`.
  - Garis rute emas putus-putus menghubungkan pin sesuai urutan wilayah.
  - Tirai "Membuka Peta Kedu" memuat gambar ini lebih dulu.
- **Spesifikasi:**
  - 1400 × 900 px (14:9), JPG q85 atau PNG-24, ≤ 600 KB;
  - berkas bawaan `public/assets/map/map-kedu.png`.
- **Titik pin (nilai seeder; admin dapat memindahkan):**

| Wilayah | Persen | Piksel (x, y) | Zona tenang (±130 × ±90) |
|---|---|---|---|
| Temanggung | 65%, 41% | 910, 369 | x 780–1040, y 279–459 |
| Magelang | 56%, 71% | 784, 639 | x 654–914, y 549–729 |
| Wonosobo | 28%, 55% | 392, 495 | x 262–522, y 405–585 |

- **Tata letak bentang alam yang diusulkan.** Peta bergaya, bukan peta navigasi, tetapi arah mata angin tetap masuk akal (utara di atas). Semua ada di `templat/peta-kedu-1400x900.svg`.

| Unsur | Letak kira-kira (px) | Catatan |
|---|---|---|
| Dataran Tinggi Dieng + Telaga Warna + candi kecil | 250–400, 200–330 | utara–barat laut Wonosobo; awan tipis |
| Gunung Sindoro (puncak) | 600, 250 | kerucut simetris, di utara pelana Kledung |
| Gunung Sumbing (puncak) | 640, 430 | lebih lebar dan sedikit lebih tinggi; di selatan Sindoro; kaki gunung tidak masuk zona Magelang |
| Kebun tembakau & kopi berteras | 800–1050, 150–270 | di atas zona pin Temanggung |
| Gunung Merbabu / Merapi | 1180, 380 / 1230, 610 | timur; Merapi dengan kepulan asap tipis |
| Bukit Tidar | 970, 600 | kecil, di timur pin Magelang, di luar zona tenang |
| Candi Borobudur | 700, 800 | selatan Magelang, di luar zona tenang |
| Perbukitan Menoreh | 330–650, 780–870 | selatan–barat daya |
| Sungai Progo | dari lereng Sindoro (±700, 300) ke tenggara di sisi barat zona Temanggung (x ±760), lalu ke selatan di sisi barat zona Magelang (x ±630), melewati sisi timur Borobudur sampai tepi bawah (±780, 900) | garis biru tipis, tidak melintasi zona tenang |
| Kabut Lupa | sudut kiri atas dan tepi kanan | tidak menutup pin maupun rute |

```text
{GAYA} illustrated storybook map of the Kedu region of Central Java seen from above at a slight angle, painted on dark indigo parchment with antique-gold ink linework and soft watercolour terrain, north at the top; the Dieng plateau with a tiny turquoise lake and tiny temples in the upper left, the twin volcanoes Sindoro (north) and Sumbing (south) in the upper centre, terraced tobacco and coffee fields to their east, Merbabu and a gently smoking Merapi on the right edge, a small green Tidar hill, the great stupa temple Borobudur in the lower centre, the Menoreh hills along the lower left, a thin blue Progo river winding from the volcano slopes down to the bottom edge, tiny glowing village dots, soft {KABUT} only in the top-left corner and along the right edge, three calm open areas of plain terrain around the region markers, an ornamental compass rose without letters in the lower right corner
```

- **Negatif:** `{NEGATIF}, labels, place names, compass letters, grid lines, modern roads, cars, satellite photo`
- **Pasca:** *paintover* zona tenang agar datar dan gelap sedang, lalu cek dengan templat. Label pin berlatar gelap `--veil-strong`, jadi zona tenang tidak perlu terang.
- **Cek:**
  - tidak ada tulisan;
  - tidak ada bentang alam di bawah pin;
  - rute putus-putus emas tetap terlihat di atasnya;
  - letak tiga wilayah masuk akal bagi guru setempat.

### 11.6 Wilayah: latar, peta, lencana (9)

Tautannya disimpan di kolom FK `levels.*_media_id`. Unggah dari **Konten → wilayah**, atau commit lalu `gelita:media:scan` (seeder menautkan kolom yang masih kosong).

#### 11.6.1 Latar wilayah `bg.{wilayah}.region` · **wajib** 1920 × 1080

- **Tampil di:**
  - dialog pembuka dan wilayah tuntas;
  - peta wilayah, kartu misi, dan layar tantangan (bila node tanpa latar sendiri);
  - Pustaka dan layar selesai;
  - overlay Kenali (opasitas 55%);
  - dipramuat tirai wilayah.
- **Berkas bawaan:** `assets/bg/bg-{temanggung|magelang|wonosobo}.jpg`.
- Latar ini paling sering dilihat, jadi harus tenang: bentuk besar, kontras sedang, fokus di tengah-atas.

| Kode | Suasana (tagline dari `region_intro`) | Prompt (setelah `{GAYA} {LATAR}`) |
|---|---|---|
| `bg.temanggung.region` | pagi, "Di Antara Dua Gunung" | `early morning in Temanggung seen from the east: Mount Sindoro (the symmetrical cone, right) and Mount Sumbing (broader and taller, left) rising side by side above terraced tobacco and coffee fields, a small village with tiled roofs, a wooden signpost at a crossroads half hidden in thin {KABUT}, a glint of the Progo river, soft gold sunrise` |
| `bg.magelang.region` | fajar berkabut tipis, "Tanah Candi Agung" | `the classic sunrise view from the Menoreh hills: Borobudur temple on its green hill rising out of morning mist above palm forest, Merapi (gently smoking) and Merbabu behind it, the ring of mountains around the plain, calm and majestic, no people on the temple` |
| `bg.wonosobo.region` | fajar dingin, "Negeri di Atas Awan" | `the Dieng plateau above a sea of clouds at cold dawn, the small Arjuna temple complex in the middle distance, the green-turquoise Telaga Warna lake, terraced potato fields on steep slopes, drifting {KABUT} between the hills, Mount Sindoro far away, cool blue light with the first warm sun on the peaks` |

- **Negatif:** `{NEGATIF}, people climbing temples, crowds, smoking, modern billboards`
- **Cek:** bentuk candi sesuai foto rujukan (Borobudur: 6 teras persegi, 3 teras bundar, stupa induk; Arjuna: candi kecil berderet).

#### 11.6.2 Peta wilayah `map.region.{wilayah}` · disarankan 1400 × 900

- **Tampil di:** `/wilayah/{kode}`, bingkai maks. 900 px, kanvas 14:9 tanpa potong.
  - Lima pos berupa ikon Ø 48 px + label ("1. Susun Gambar", bintang).
  - Rute emas putus-putus menghubungkan pos 1→5.
- **Berkas bawaan:** `assets/map/map-{wilayah}.png`.
- **Posisi pos** (`ContentSeeder::NODE_POSITIONS`, sama untuk ketiga wilayah):

| Pos | Persen | Piksel | Zona tenang (±110 × ±80) |
|---|---|---|---|
| 1 | 18%, 72% | 252, 648 | x 142–362, y 568–728 |
| 2 | 34%, 48% | 476, 432 | x 366–586, y 352–512 |
| 3 | 50%, 68% | 700, 612 | x 590–810, y 532–692 |
| 4 | 66%, 44% | 924, 396 | x 814–1034, y 316–476 |
| 5 | 82%, 62% | 1148, 558 | x 1038–1258, y 478–638 |

- **Vinyet per pos.** Setiap pos diberi penanda tempat kecil yang menggemakan cerita tantangannya (deskripsi node di bank soal). Letaknya di **atas** zona tenang (±120–160 px di atas titik), karena label pos berada di bawah ikon.

| Wilayah | Pos 1 | Pos 2 | Pos 3 | Pos 4 | Pos 5 | Latar jauh |
|---|---|---|---|---|---|---|
| Temanggung | gubuk bambu dengan kuda-kuda lukis dan potongan lukisan tertiup angin (*Susun Gambar*) | papan petunjuk di persimpangan, genangan hujan, jembatan rusak di kanan (*Lengkapi Kalimat*) | lapak pasar beratap terpal (*Benar atau Salah?*) | pendopo balai desa dengan meja pajangan (*Temukan Budaya*) | Candi Pringapus kecil di atas batur (*Temukan Jawabannya*) | Sindoro–Sumbing di atas; teras tembakau dan kopi |
| Magelang | batu-batu relief berserakan di halaman pemugaran (*Susun Gambar*) | gubuk juru kunci dengan buku catatan (*Ketik Jawabanmu*) | pelataran pengunjung dengan bangku (*Benar, Salah, atau Pendapat?*) | gedung museum kecil, gerabah Klipoh di depannya (*Cocokkan Fungsi & Asal*) | Candi Mendut dan Pawon berderet (*Bandingkan Gambar dan Teks*) | Borobudur di tengah atas; Merapi–Merbabu; Menoreh |
| Wonosobo | warung mi ongklok beruap dengan keranjang ongklok (*Susun Urutan*) | Telaga Warna dan Telaga Pengilon berdampingan (*Lengkapi Kesimpulan*) | kompleks Candi Arjuna dengan papan pengumuman kayu (*Sumber Mana yang Benar?*) | pos ronda dengan kentongan tergantung (*Pilih Sumber Terpercaya*) | puncak Bukit Sikunir saat matahari terbit (*Apa yang Sebaiknya Kamu Lakukan?*) | Dieng di atas awan; kebun teh; Sindoro di kejauhan |

Contoh prompt (Temanggung). Dua lainnya mengganti daftar vinyet dan latar jauh dari tabel.

```text
{GAYA} illustrated storybook map of the Temanggung highlands seen from above at a slight angle, painted on dark indigo parchment with antique-gold linework and soft watercolour terrain; Mount Sindoro and Mount Sumbing along the top; terraced tobacco and coffee fields; an earthen footpath winding from the lower left up and down across the map to the right side, passing five small landmarks placed just above the path: a bamboo hut with a painting easel, a wooden signpost at a crossroads with rain puddles and a broken bridge, a small market with tarp-covered stalls, a village-hall pendopo with display tables, and a small Hindu temple on a stone base; plain calm terrain directly on the path at each stop, soft {KABUT} at the far edges only
```

- **Negatif:** `{NEGATIF}, labels, numbers, place names, markers, modern roads, cars`
- **Cek:**
  - tumpangkan `templat/peta-wilayah-1400x900.svg`;
  - tidak ada vinyet di bawah titik pos;
  - jalur setapak melewati kelima titik secara berurutan.

#### 11.6.3 Lencana wilayah `reward.badge.{wilayah}` · disarankan 320 × 320

- **Tampil di:** profil (`profile.php:62`), 72 × 72 di atas **cakram emas**, hanya setelah wilayah tuntas.
- **Berkas bawaan:** `assets/reward/badge-{wilayah}.png`, PNG-32, ≤ 60 KB.
- **Bentuk:** medali bundar Ø 312 px memenuhi kanvas (margin 4 px), tepi emas tebal, isi enamel navy, satu simbol besar. Tanpa teks. Harus terbaca pada 72 px.

| Kode | Simbol |
|---|---|
| `reward.badge.temanggung` | dua puncak gunung kembar dengan sehelai daun tembakau di depannya |
| `reward.badge.magelang` | siluet stupa Borobudur dengan tiga baris stupa kecil |
| `reward.badge.wonosobo` | gerbang candi kecil Arjuna di atas gumpalan awan, dengan tetes air telaga berwarna hijau-biru |

```text
{GAYA} round achievement medal, thick polished antique-gold rim with a fine kawung bead pattern, deep navy enamel centre, one bold golden emblem in the middle: {SIMBOL}, soft golden glow, simple shapes readable at 72 pixels, the medal fills the whole square edge to edge, front view, {TRANSPARAN}
```

`{SIMBOL}` diambil dari tabel. Seperti `{POSE}` dan `{FRAME-N}` di §11.7, ini penanda lokal yang diisi pembangun per kode.

- **Negatif:** `{NEGATIF}, text, ribbon, thin lines, 3D perspective`
- **Cek:** ketiga lencana satu keluarga (rim sama, hanya simbol berbeda); terbaca pada 72 px.

### 11.7 Tokoh (30 frame)

- **Kode:** `char.{jaka|kedu}.{pose}.{1|2|3}`.
- **Berkas bawaan:** `assets/char/{jaka|kedu}-{pose}-{n}.png`.
- **Spesifikasi:** 700 × 900 px, PNG-32 transparan, ≤ 250 KB, **wajib**. Semua aturan §9.4 berlaku.
- **Pemutaran:** `fps` dan `loop` dari `Config\Gelita::$characterAnimations`.
  - Pose tanpa *loop* berhenti di frame 3, jadi frame 3 adalah pose yang "ditahan".
  - Frame 1 tetap harus terbaca sendiri: saat ini hanya frame 1 yang tampil, dan juga dengan `prefers-reduced-motion`.
  - Perbedaan antarframe kecil: ≤ 12 px pada tubuh, lebih besar hanya pada tangan atau kepala.

Prompt setiap frame:

```text
{GAYA} {FRAME} {JAKA}, {POSE}, {FRAME-N}, {TRANSPARAN}
```

Untuk Mbah Kedu, `{JAKA}` diganti `{KEDU}`.

- **Negatif:** `{NEGATIF}, background scenery, second character, cropped feet, facing left, text on clothes`

**Jaka** (`char.jaka.*`)

| Pose · fps · loop · dipakai | `{POSE}` | Frame 1 | Frame 2 | Frame 3 |
|---|---|---|---|---|
| `idle` · 4 · loop · 5 baris naskah + panggung dialog, layar tantangan, peta (cadangan) | `relaxed neutral stance, arms loose, curious friendly half-smile, looking slightly ahead` | pose dasar | tarik napas: bahu & dada naik 2 px, lentera berayun 2° ke kanan | buang napas: turun 1 px, lentera berayun 2° ke kiri |
| `happy` · 6 · sekali · 8 baris + layar selesai | `joyful, big open grin, eyes bright` | senyum lebar, lentera terangkat sedikit | lompatan kecil (kaki naik 8 px) | kepal tangan kiri terangkat gembira, mata sabit (ditahan) |
| `bow` · 6 · sekali · `penutup-04` + halaman masuk & daftar | `respectful Javanese bow with hands clasped in front at the waist (ngapurancang), lantern held with both hands in front, calm sincere smile` | tegak, tangan tertangkup di depan | membungkuk ±15° | membungkuk ±30°, kepala tertunduk (ditahan) |
| `sad` · 4 · sekali · 3 baris | `sad and sorry, shoulders dropped, eyebrows raised inward, small frown, lantern hanging low` | bahu turun | kepala menunduk | memandang ke bawah, jari memilin tali tas (ditahan) |
| `afraid` · 6 · sekali · 7 baris | `startled and anxious (not terrified), wide eyes, raised eyebrows, leaning slightly back, lantern pulled close to chest` | kaget, badan condong ke belakang | kedua tangan dekat dada | sedikit merunduk, bahu gemetar (ditahan) |
| `determined` · 6 · sekali · 11 baris | `confident and brave, chin up, firm smile, one foot forward, lantern lifted to chest height` | dagu terangkat | kepal tangan kiri di depan dada | berdiri mantap satu kaki di depan, lentera setinggi dada (ditahan) |

**Mbah Kedu** (`char.kedu.*`)

| Pose · fps · loop · dipakai | `{POSE}` | Frame 1 | Frame 2 | Frame 3 |
|---|---|---|---|---|
| `idle` · 3 · loop · 10 baris + panggung dialog, arena `cari` | `calm wise stance, both hands resting on top of the walking stick, gentle expression` | pose dasar | tarik napas halus | anggukan kecil (kepala turun 3 px) |
| `smile` · 4 · sekali · 22 baris | `warm kind smile, eyes crinkled, free hand raised with the palm up in a gentle explaining gesture` | senyum lembut | mata menyipit tersenyum | tangan kiri terangkat telapak ke atas (ditahan) |
| `worried` · 4 · sekali · 9 baris | `worried and concerned, brows knit, looking into the distance, slightly hunched` | alis bertaut | tangan mengusap jenggot | menatap jauh, bahu sedikit membungkuk (ditahan) |
| `weak` · 3 · sekali · 4 baris | `exhausted and weakened, leaning heavily on the walking stick, other hand on his chest, knees slightly bent, pale cooler skin tone, half-closed eyes` | bersandar pada tongkat | lutut menekuk sedikit | lebih merosot, mata setengah terpejam (ditahan) |

`{FRAME-N}` diisi per frame, misalnya `frame 2 of 3: …`, dari kolom Frame di tabel.

**Cara membuat yang disarankan:**

1. Buat frame 1 dari lembar tokoh, dengan ControlNet OpenPose dari sketsa pose di atas `templat/tokoh-700x900.svg`.
2. Buat frame 2 dan 3 dengan *inpainting*/*img2img* berdenoise rendah (0,25–0,4) pada bagian yang bergerak saja, atau ilustrator menyuntingnya di Krita.
3. Samakan skala dan jangkar ketiganya dengan transformasi yang sama (§10.5).

**Cek:**
- Putar ketiga frame berurutan pada fps-nya (GIF uji). Kaki tidak bergeser (kecuali lompatan kecil frame 2 `happy`), pakaian dan warna tidak berubah.
- Mbah Kedu yang dicerminkan tetap wajar: tongkat pindah tangan tidak masalah.
- Tidak ada `char.kedu.happy`: pose itu tidak sah (§2.2 temuan 9).

### 11.8 Tantangan

Kode berkas butir mengikuti editor (`ContentController`):

- `challenge.item.{kode_butir}` untuk gambar butir;
- `challenge.{ref}.scene` dan `challenge.{ref}.bg` untuk node;
- `challenge.option.{kode_butir}-{a–d}` untuk opsi;
- `passage.{kunci}` untuk bacaan.

Impor bank soal membuat slot kosong untuk setiap `asset_key` di bank. Begitu berkas diunggah dengan kode yang sama, gambar langsung tampil (`docs/bank-soal/README.md`).

**Catatan penelitian.** Mengganti gambar puzzle, objek `cari`, atau menambah gambar `mgl-5` dapat mengubah tingkat kesulitan butir. Putuskan bersama peneliti. Untuk butir yang sudah dijawab siswa, catat sebagai rilis baru.

#### 11.8.1 Gambar puzzle (8) · 900 × 900 (1:1), JPG q85, ≤ 350 KB

- **Tampil di:** papan 3 × 3 (maks. 480 px, celah 3 px). Setiap butir membawa teks keterangan (`prompt`) berisi fakta tentang gambarnya, jadi gambar harus sesuai fakta itu.
- Setiap keping (300 × 300 px) harus punya ciri pembeda. Hindari sepertiga atas yang seluruhnya langit polos.
- Nomor keping menutup pojok kiri atas tiap keping (±50 × 38 px).
- **Dua jalur:**
  - **A. Foto Commons** dari bank soal, dipotong persegi. Layar puzzle tidak punya tempat kredit, sedangkan foto CC BY/BY-SA wajib diberi atribusi. Jadi pilih foto CC0/domain publik, atau cantumkan kreditnya di halaman Pustaka wilayah yang sama.
  - **B. Ilustrasi `{GAYA}` (disarankan).** Gayanya seragam dan tanpa urusan atribusi. Pakai beberapa foto sebagai rujukan bentuk, jangan menjiplak satu foto.

| Kode | Isi (bank soal) | Foto rujukan (Commons) | Prompt ilustrasi (setelah `{GAYA}` + `square 1:1 painting,`) |
|---|---|---|---|
| `challenge.item.tmg-1-01` | Sindoro & Sumbing dari arah Temanggung/Kledung | `File:Sindoro_sumbing.jpg` | `Mount Sindoro and Mount Sumbing standing side by side under a clear morning sky seen from the Kledung pass, terraced vegetable and tobacco fields at their feet, a few tiled village roofs, small white clouds; distinct details spread across the whole picture` |
| `challenge.item.tmg-1-02` | kebun tembakau hijau di lereng (tanpa orang merokok) | `File:Perkebunan_Tembakau_di_Lereng_Gunung_Sindoro.jpg` | `lush green tobacco plants in neat rows on terraced slopes of Mount Sindoro, a farmer's bamboo hut, a drying rack, morning light, the volcano above` |
| `challenge.item.tmg-1-03` | Candi Pringapus tampak depan | `File:Candi_Pringapus_Temanggung_Jateng.jpg` | `Pringapus temple, a small 9th-century Hindu stone temple on a square base with a single chamber and tiered roof, in a grassy courtyard with trees, front view, shape faithful to reference photographs` |
| `challenge.item.tmg-1-04` | penari kuda lumping, kuda bambu warna-warni | `File:Kuda_lumping_Temanggung.jpg` | `a kuda lumping (jaran kepang) dancer from Temanggung riding a colourful painted woven-bamboo horse, traditional costume, lively dance pose, festive village setting with gamelan players softly behind` |
| `challenge.item.mgl-1-01` | stupa berlubang teras atas Borobudur + stupa induk | `File:Borobudur_stupas_on_upper_terrace.jpg` | `the perforated bell-shaped stupas on the upper round terraces of Borobudur, one lattice opening showing the seated Buddha statue inside, the large main stupa behind, soft morning light, no people climbing` |
| `challenge.item.mgl-1-02` | penari Topeng Ireng, kuluk bulu warna-warni | `File:Penari_Topeng_Ireng_03.jpg` | `a Topeng Ireng dancer from Magelang with a tall colourful feather headdress (kuluk), golden tassels, black stripes painted over white face powder, boots and small ankle bells, strong stamping dance pose` |
| `challenge.item.mgl-1-03` | Candi Mendut tampak depan dengan tangga | `File:Mendut_Temple.jpg` | `Mendut temple, a Buddhist temple with a tall square body on a high base and a stairway to the entrance at the front, stone reliefs on the walls, trees around, front view, shape faithful to reference photographs` |
| `challenge.item.mgl-1-04` | getuk Magelang cokelat, putih, hijau | `File:Getuk_Magelang.JPG` | `colourful getuk Magelang: soft cassava cakes in brown, white and green cut into pieces on a banana leaf on a woven bamboo tray, a whole cassava root beside it` |

- **Negatif:** `{NEGATIF}, cigarettes, smoke, people climbing the temple, tourists` (tambahkan `blank sky covering the top third`).
- **Cek:** susun uji di papan. Setiap keping harus dapat dibedakan tanpa nomor, dan gambar cocok dengan fakta di keterangan butir.

#### 11.8.2 Arena cari `tmg-4` — adegan (1) dan objek (11)

**`challenge.tmg-4.scene`** — 1280 × 720 (16:9), JPG q80, ≤ 350 KB.

- **Tampil di:** kanvas 16:9 tanpa potong (`game.css:955`). Di atasnya, 11 tombol objek bundar (lebar 12% = ±154 px, rasio 1:1, bingkai putus-putus) di posisi persen butir.
- Benda **tidak** digambar di adegan. Bank soal menegaskannya: benda ditempel terpisah sebagai gambar objek.

| Objek | Posisi (x, y, lebar %) | Kotak piksel (x1–x2, y1–y2) | Baris |
|---|---|---|---|
| `tmg-4-01` | 4, 6, 12 | 51–205, 43–197 | atas |
| `tmg-4-02` | 44, 6, 12 | 563–717, 43–197 | atas |
| `tmg-4-11` | 64, 6, 12 | 819–973, 43–197 | atas |
| `tmg-4-03` | 84, 6, 12 | 1075–1229, 43–197 | atas |
| `tmg-4-04` | 14, 40, 12 | 179–333, 288–442 | tengah |
| `tmg-4-05` | 54, 40, 12 | 691–845, 288–442 | tengah |
| `tmg-4-09` | 74, 40, 12 | 947–1101, 288–442 | tengah |
| `tmg-4-06` | 4, 72, 12 | 51–205, 518–672 | bawah |
| `tmg-4-10` | 24, 72, 12 | 307–461, 518–672 | bawah |
| `tmg-4-07` | 44, 72, 12 | 563–717, 518–672 | bawah |
| `tmg-4-08` | 84, 72, 12 | 1075–1229, 518–672 | bawah |

Tiga baris objek duduk di tiga rak: permukaan rak di y ±197, ±442, dan ±672 (27%, 61%, 93% tinggi gambar).

```text
{GAYA} front view of a wide traditional display rack of teak wood and bamboo inside an open village-hall pendopo at a culture fair in Temanggung, three long empty shelves running across the whole picture with their top surfaces at about 27%, 61% and 93% of the image height, carved pillars at both sides, batik cloth hanging behind the rack, and the twin volcanoes Sindoro and Sumbing seen in soft morning light through the openings above the top shelf; every shelf completely empty, evenly lit, uncluttered, 16:9
```

- **Negatif:** `{NEGATIF}, any objects on the shelves, food, musical instruments, puppets, boats, statues, baskets, people, crowd`
- **Pasca:** tumpangkan `templat/adegan-cari-1280x720.svg`. Rapikan tinggi rak agar sama dengan garis templat. Tikar pandan kecil di bawah tiap kotak boleh dilukis tangan.
- **Cek:** tidak ada benda lain yang tampak "dapat diklik"; di ponsel mendatar adegan hanya ±300–500 px lebar.

**Objek `challenge.item.tmg-4-NN`** — 240 × 240 (1:1), PNG-32 transparan, ≤ 80 KB.

- **Tampil di:** `contain` di dalam tombol bundar. Bentuk utama di dalam lingkaran Ø 216 px.
- Harus terbaca pada ±60 px (ponsel mendatar) sampai 154 px.

**Aturan terpenting:** ketiga objek jebakan (`-09`, `-10`, `-11`) digambar **persis sama gayanya** dengan kedelapan target: skala, cahaya, kejenuhan, ketebalan tepi, dan tanpa bayangan. Yang diuji adalah pengetahuan budaya, bukan kejelian melihat beda gaya (`HuntPayloadTest`, `cari.php:5`). Buat kesebelas objek dalam satu sesi dengan seed dan referensi gaya yang sama. Tanda "jebakan" di bawah hanya untuk tim.

Prompt tiap objek:

```text
{GAYA} a single {ISI}, three-quarter view from slightly above, centred and filling about 80% of a circle, the same soft warm key light from the upper left and the same painterly finish as the rest of this object set, {TRANSPARAN}
```

| Kode | `{ISI}` | Negatif tambahan |
|---|---|---|
| `tmg-4-01` | `broad fresh green tobacco leaf, one leaf, visible veins, slightly curled edges` | `cigarette, smoke, dried tobacco` |
| `tmg-4-02` | `small heap of glossy roasted brown coffee beans` | `cup, drink` |
| `tmg-4-03` | `flat woven-bamboo hobby horse for the kuda lumping (jaran kepang) dance, painted black and red with gold ornaments, colourful cloth tassels as a mane, no rider` | `person, rider` |
| `tmg-4-04` | `three small slender freshwater loach fish (ikan uceng), olive-brown with faint dark bands, lying side by side` | `big fish, sea fish` |
| `tmg-4-05` | `one sharpened bamboo spear (bambu runcing) shown diagonally, a small red-and-white ribbon tied below the sharpened tip, a respectful historical symbol` | `blood, soldiers, violence, pointing at viewer` |
| `tmg-4-06` | `small grey andesite stone statue of the sacred bull Nandi lying down with folded legs, weathered ancient Javanese temple style` | `cow photo, colourful paint` |
| `tmg-4-07` | `plate of kupat tahu: sliced rice cakes (ketupat), fried tofu cubes and bean sprouts drizzled with dark sweet soy and peanut sauce, sprinkled with crushed peanuts and fried shallots, on a simple enamel plate` | `spoon, table` |
| `tmg-4-08` | `rectangular woven bamboo drying tray (rigen) with golden-brown shredded tobacco spread thinly on it` | `cigarette, smoke` |
| `tmg-4-09` · jebakan | `bamboo angklung musical instrument from West Java, bamboo tubes in a bamboo frame` | `people playing` |
| `tmg-4-10` · jebakan | `small ondel-ondel Betawi giant puppet, head and upper body, friendly smiling face, colourful flower-crown headdress, patterned cloth shawl` | `scary face, horror` |
| `tmg-4-11` · jebakan | `traditional Bugis pinisi wooden sailing ship from South Sulawesi with two masts and white sails, side view` | `sea, waves, modern boat` |

- **Cek:**
  - sebelas objek dijajarkan satu baris: jebakan tidak boleh tampak "lain sendiri";
  - dikenali pada 60 px;
  - tidak ada teks;
  - `tmg-4-05` tidak tampak mengancam.

#### 11.8.3 Opsional

**Latar node `challenge.{ref}.bg`** (15) — 1920 × 1080, JPG q80. Tampil di layar tantangan dan kartu misi. Tanpa latar node, layar memakai latar wilayah (`media_first($node->background_media_id, $level->background_media_id)`), jadi slot ini tidak wajib. Bila dibuat:

```text
{GAYA} {LATAR} {IDE}, calmer and darker than the region background, large quiet areas, because a reading panel covers most of the screen
```

| Node | `{IDE}` | Node | `{IDE}` | Node | `{IDE}` |
|---|---|---|---|---|---|
| `tmg-1` | `a bamboo hut with a painting easel on a windy hillside below Sindoro and Sumbing` | `mgl-1` | `carved relief stones laid out on cloth at a Borobudur restoration yard` | `wnb-1` | `a steaming ongklok noodle stall in the cold Dieng morning` |
| `tmg-2` | `a rainy veranda with a wooden signpost at a crossroads beyond` | `mgl-2` | `the temple keeper's small wooden hut near Borobudur` | `wnb-2` | `Telaga Warna and Telaga Pengilon side by side at dawn` |
| `tmg-3` | `a small village market with tarp-covered stalls` | `mgl-3` | `a visitor plaza with benches at the temple gate` | `wnb-3` | `the Arjuna temple complex with a wooden notice board` |
| `tmg-4` | `the courtyard of a village-hall pendopo during a culture fair` | `mgl-4` | `a small museum hall with display cases and Klipoh pottery` | `wnb-4` | `a village guard post with a hanging bamboo kentongan` |
| `tmg-5` | `Pringapus temple in a quiet courtyard at golden hour` | `mgl-5` | `Mendut and Pawon temples seen in a line at morning` | `wnb-5` | `the top of Sikunir hill at golden sunrise above the clouds` |

**Adegan rumpang `challenge.{tmg-2,mgl-2,wnb-2}.scene`** (3) — 1280 × 720, JPG q80.

- Tampil di kolom kiri arena rumpang (lebar 34%, tanpa potong). `rumpang.php:18` memakai adegan node, lalu gambar butir pertama.
- Gambar ini dekoratif: **tanpa tulisan yang terbaca**, apalagi jawaban.

| Kode | Deskripsi node (bank soal) | Prompt (setelah `{GAYA}`) |
|---|---|---|
| `challenge.tmg-2.scene` | catatan perjalanan Jaka terhapus hujan | `Jaka's small travel notebook lying open on a bamboo table on a rainy veranda in Temanggung, the handwriting smudged into unreadable blurs by raindrops, a wooden pencil, the twin volcanoes grey in the rain beyond, 16:9` |
| `challenge.mgl-2.scene` | catatan juru kunci candi yang belum selesai | `an old temple keeper's wooden desk near Borobudur with an unfinished notebook of unreadable pencil marks, a small carved relief fragment and a clay oil lamp, soft morning light, 16:9` |
| `challenge.wnb-2.scene` | Jaka menyimpulkan apa yang ia pelajari di Wonosobo | `a cosy wooden table in a Dieng homestay at dawn with an open notebook of unreadable marks, a cup of hot tea, a bowl of yellow carica fruit, frost on the window glass and Telaga Warna outside, 16:9` |

- **Negatif:** `{NEGATIF}, readable words, letters, numbers`

**Gambar soal `mgl-5` (Bandingkan Gambar dan Teks)** (6) — `challenge.item.mgl-5-01` … `-06`, 960 × 640 (3:2), JPG q80.

- Tampil sebagai gambar soal (tinggi maks. 200 px).
- Setiap butir sudah memuat deskripsi gambar dalam teks sumbernya ("Gambar: …"), jadi gambar **wajib sesuai deskripsi itu** dan tidak boleh memuat label yang membocorkan jawaban.
- Keputusan menambahkannya ada di tangan peneliti: butir ini menguji menghubungkan gambar dengan teks.
- `-03` dan `-04` berupa diagram, jadi sebaiknya digambar vektor (AI sering salah menghitung).

| Kode | "Gambar:" di butir | Prompt (setelah `{GAYA}`) | Cek wajib |
|---|---|---|---|
| `-01` | lonceng berlubang berjajar melingkar; di salah satunya tampak arca duduk | `rows of perforated bell-shaped stupas arranged in a circle on a round stone terrace, through the lattice of one stupa a seated statue is visible` | arca terlihat di dalam satu stupa |
| `-02` | penari berhias kepala bulu warna-warni, wajah bergaris hitam, sepatu bot, benda kecil mengilap di pergelangan kaki | `a Topeng Ireng dancer with a colourful feather headdress, black stripes over a white-powdered face, boots, and small shiny bells clearly visible around the ankles` | lonceng kaki jelas terlihat |
| `-03` | peta kecil, tiga titik candi berjajar timur–barat; titik tengah paling kecil | `simple flat map diagram: a thin east–west line with three round temple dots in a row, the middle dot clearly the smallest, the left (west) dot the largest, the right (east) dot medium; a small arrow compass mark with no letters; no labels, no names` | **tanpa nama candi**; ukuran titik sesuai |
| `-04` | denah bertingkat: 6 lapis persegi, 3 lingkaran, 1 stupa besar di puncak | `simple flat stepped diagram seen from the side: exactly six square layers at the bottom, exactly three round layers above them, exactly one large bell-shaped stupa on top` | hitung: **6 + 3 + 1** tepat |
| `-05` | perajin membentuk tanah liat di meja putar; pot, cobek, kuali cokelat kemerahan | `a potter from Klipoh shaping clay on a hand-turned wheel, beside them rows of reddish-brown round pots, stone-like grinding bowls and woks` | bahan jelas tanah liat |
| `-06` | kue cokelat, putih, hijau di atas daun pisang; di belakangnya umbi panjang berkulit cokelat | `soft pieces of getuk cake in brown, white and green on a banana leaf, behind them one long cassava root with rough brown bark-like skin and white flesh at a cut end` | umbi jelas **singkong**, bukan ubi jalar, talas, atau kentang (itu pengecohnya) |

**Templat tanpa isi bawaan.** Bank soal produksi belum memakai slot ini, jadi katalog hanya memberi pola:

- **Opsi** `challenge.option.*` (400 × 400, PNG transparan): `{GAYA} a single {BENDA}, bold simple silhouette readable at 140 px, {TRANSPARAN}`.
- **Kartu boleh** (480 × 360): `{GAYA} {ADEGAN}, wide composition with the subject inside the middle horizontal band (the top and bottom 20% will be cropped)`.
- **Bacaan** `passage.*` (960 × 640): `{GAYA} illustration for a short reading text about {TOPIK}, calm, no text`.

Aturan untuk ketiganya: gambar **tidak boleh memberi isyarat jawaban**, misalnya gambar kartu yang menandakan BENAR atau SALAH.

### 11.9 Pustaka (55 butir, semuanya opsional)

Halaman Pustaka memakai 43 foto Wikimedia Commons dan 5 video YouTube dari bank soal. Semuanya dimuat dari domain asalnya **tanpa referrer**, dan video baru dimuat setelah siswa menekan Putar (`docs/05` §Pustaka). Katalog tidak membuat ulang foto faktual itu. Katalog memberi tiga hal: cara melokalkannya untuk kelas tanpa internet, ilustrasi untuk halaman yang belum bermedia, dan poster video.

**Melokalkan foto** (untuk kelas luring; `docs/bank-soal/README.md`):

1. Buka halaman berkas Commons. Catat pengarang dan lisensinya; kolom `credit` bank soal sudah menyebut "lisensi di halaman berkas".
2. Unduh ukuran asli. Potong dan ubah ukuran ke 960 × 640 (3:2):
   - media pertama setiap halaman tampil utuh 3:2, dan media pertama halaman 1 juga menjadi sampul rak 16:9 (subjek di y 50–590);
   - media ke-2 dan seterusnya dipotong 4:3 (subjek di x 53–907).
3. Lambang daerah PNG/WebP → PNG (iPad lama tidak menampilkan WebP). Hapus metadata (`-strip`).
4. Unggah di editor Pustaka sebagai kode di tabel. Ganti sumber dari **Tautan** ke **Berkas**, dan tulis kredit lengkap: pengarang, lisensi, "via Wikimedia Commons". Kredit tampil lewat ikon ⓘ.

| Wilayah | Hlm | Judul | Jenis | Kode berkas | Berkas Commons / video |
|---|---:|---|---|---|---|
| temanggung | 1 | Selamat Datang di Temanggung | gambar | `library.temanggung.p1.1` | Taman_Wisata_Alam_Posong,_Temanggung.jpg |
| temanggung | 1 | | gambar | `library.temanggung.p1.2` | Kabut_Tipis_Temanggung.jpg |
| temanggung | 2 | Si Kembar Sindoro dan Sumbing | gambar | `library.temanggung.p2.1` | Sindoro_sumbing.jpg |
| temanggung | 2 | | gambar | `library.temanggung.p2.2` | Gunung_Sindoro_dari_puncak_Gunung_Sumbing.jpg |
| temanggung | 2 | | gambar | `library.temanggung.p2.3` | Gunung_Sumbing_Temanggung.jpg |
| temanggung | 3 | Asal-usul Nama dan Sejarah Temanggung | gambar | `library.temanggung.p3.1` | Lambang_Kabupaten_Temanggung.png |
| temanggung | 4 | Tembakau Srintil dan Kopi Temanggung | gambar | `library.temanggung.p4.1` | Perkebunan_Tembakau_di_Lereng_Gunung_Sindoro.jpg |
| temanggung | 4 | | gambar | `library.temanggung.p4.2` | Petani_Tembakau_memeriksa_daun_Tembakau_yang_akan_dipanen.jpg |
| temanggung | 4 | | gambar | `library.temanggung.p4.3` | Tobacco_Fields_on_the_slopes_of_Mt._Sumbing.jpg |
| temanggung | 5 | Jejak Mataram Kuno | gambar | `library.temanggung.p5.1` | Situs_liyangan.jpg |
| temanggung | 5 | | gambar | `library.temanggung.p5.2` | Candi_Pringapus_Temanggung_Jateng.jpg |
| temanggung | 5 | | gambar | `library.temanggung.p5.3` | Arca_Nandi_dalam_bilik_Candi_Pringapus,_Mei_2022.jpg |
| temanggung | 6 | Umbul Jumprit, Hulu Kali Progo | gambar | `library.temanggung.p6.1` | Segoro_Wedi_Gunung_Sindoro_dengan_latar_Gunung_Sumbing.jpg |
| temanggung | 8 | Kuda Lumping dan Tradisi Temanggung | gambar | `library.temanggung.p8.1` | Kuda_lumping_Temanggung.jpg |
| temanggung | 8 | | video | `library.temanggung.p8.2` | youtube.com/watch?v=IXDk03NgHqQ |
| magelang | 1 | Selamat Datang di Magelang | gambar | `library.magelang.p1.1` | Pemandangan_Gunung_Andong_Magelang.jpg |
| magelang | 1 | | gambar | `library.magelang.p1.2` | Lambang_Kabupaten_Magelang.jpg |
| magelang | 2 | Candi Borobudur, Mahakarya Dunia | gambar | `library.magelang.p2.1` | Borobudur_temple_from_above.jpg |
| magelang | 2 | | gambar | `library.magelang.p2.2` | Borobudur_stupas_on_upper_terrace.jpg |
| magelang | 2 | | video | `library.magelang.p2.3` | youtube.com/watch?v=txujqGtB_6g |
| magelang | 3 | Tiga Alam di Borobudur | gambar | `library.magelang.p3.1` | Borobudur_-_Karmawibhangga_-_013_Karmavibhanga_Section_Uncovered_(11831905453).jpg |
| magelang | 3 | | gambar | `library.magelang.p3.2` | Relief_Di_Candi_Borobudur.jpg |
| magelang | 3 | | gambar | `library.magelang.p3.3` | Stupa_Borobudur.jpg |
| magelang | 4 | Menemukan dan Memugar Borobudur | gambar | `library.magelang.p4.1` | Borobudur-Temple-Park_Indonesia_Stupas-of-Borobudur-01.jpg |
| magelang | 5 | Mendut, Pawon, dan Perjalanan Waisak | gambar | `library.magelang.p5.1` | Mendut_Temple.jpg |
| magelang | 5 | | gambar | `library.magelang.p5.2` | Buddha's_statue_inside_candi_Mendut.JPG |
| magelang | 5 | | gambar | `library.magelang.p5.3` | Candi_Pawon_6.jpg |
| magelang | 6 | Topeng Ireng, Tarian Penuh Semangat | gambar | `library.magelang.p6.1` | Penari_Topeng_Ireng_03.jpg |
| magelang | 6 | | gambar | `library.magelang.p6.2` | Topeng_Ireng_(4).jpg |
| magelang | 6 | | video | `library.magelang.p6.3` | youtube.com/watch?v=CicQwPmwCWQ |
| magelang | 7 | Gunung Tidar, Paku Tanah Jawa | gambar | `library.magelang.p7.1` | Makam_Syekh_Subakir.jpg |
| magelang | 9 | Rasa dan Karya Magelang | gambar | `library.magelang.p9.1` | Getuk_Magelang.JPG |
| wonosobo | 1 | Selamat Datang di Wonosobo | gambar | `library.wonosobo.p1.1` | Malam_di_Wonosobo.jpg |
| wonosobo | 1 | | gambar | `library.wonosobo.p1.2` | Lambang_Kabupaten_Wonosobo.webp |
| wonosobo | 2 | Dieng, Tempat Para Dewa | gambar | `library.wonosobo.p2.1` | Dieng_Plateau_view_from_complex_of_Candi_Arjuna.JPG |
| wonosobo | 2 | | gambar | `library.wonosobo.p2.2` | Peta_Kawasan_Dataran_Tinggi_Dieng.jpg |
| wonosobo | 3 | Telaga Warna dan Telaga Pengilon | gambar | `library.wonosobo.p3.1` | Telaga_Warna_Dieng_Jawa_Tengah.jpg |
| wonosobo | 3 | | gambar | `library.wonosobo.p3.2` | Telaga_Warna_in_The_Dieng_Plateau.jpg |
| wonosobo | 3 | | gambar | `library.wonosobo.p3.3` | Telaga_Warna_Dieng_02.jpg |
| wonosobo | 4 | Sembungan dan Sikunir | gambar | `library.wonosobo.p4.1` | Sunrise_di_Bukit_Sikunir.jpg |
| wonosobo | 4 | | gambar | `library.wonosobo.p4.2` | The_Golden_Sunrise_Sikunir.jpg |
| wonosobo | 6 | Candi-candi Dieng | gambar | `library.wonosobo.p6.1` | Kompleks_Candi_Arjuna_-_Dieng_Plateau.jpg |
| wonosobo | 6 | | gambar | `library.wonosobo.p6.2` | Pagi_Candi_Arjuna_Dieng.jpg |
| wonosobo | 7 | Tari Topeng Lengger dan Bundengan | video | `library.wonosobo.p7.1` | youtube.com/watch?v=id_j81YL5QI |
| wonosobo | 8 | Tradisi Rambut Gimbal Dieng | video | `library.wonosobo.p8.1` | youtube.com/watch?v=om5yK2B3DII |
| wonosobo | 9 | Rasa Wonosobo | gambar | `library.wonosobo.p9.1` | Mi_ongklok_sate_sapi_Wonosobo.JPG |
| wonosobo | 9 | | gambar | `library.wonosobo.p9.2` | Mountain_papaya_(Vasconcellea_pubescens).jpg |
| wonosobo | 9 | | gambar | `library.wonosobo.p9.3` | Es_carica_dieng_wonosobo.jpg |

Di halaman, tabel ini dibangkitkan dari `docs/bank-soal/data/*.php`, jadi selalu sama dengan bank soal.

**Video YouTube tidak diunduh.** Ketentuan layanannya melarang itu. Untuk kelas luring:

- biarkan video tanpa jaringan tersembunyi, atau
- ganti dengan rekaman sendiri: MP4 H.264, 720p, ≤ 1,5 Mbps, ≤ 60 detik (`docs/08` §Ukuran aset).

**Poster video** `library.{wilayah}.p{n}.{i}.poster` (5) — 1280 × 720 (16:9), JPG q80.

- Biasanya diambil otomatis oleh `php spark gelita:library:thumbnails` (butuh HTTPS keluar).
- Bila server luring, buat poster ilustrasi. Tombol Putar besar digambar antarmuka, jadi tengah poster tenang dan tanpa ikon putar.

```text
{GAYA} 16:9 poster illustration about {TOPIK}, calm open centre because a large play button will be drawn on top, no text, no play icon
```

| Kode | `{TOPIK}` |
|---|---|
| `library.temanggung.p8.2.poster` | `craftspeople in Temanggung painting woven-bamboo hobby horses for the jaran kepang dance` |
| `library.magelang.p2.3.poster` | `Borobudur temple at sunrise, a world heritage site` |
| `library.magelang.p6.3.poster` | `a line of Topeng Ireng dancers with colourful feather headdresses performing at a village festival` |
| `library.wonosobo.p7.1.poster` | `a bundengan, a woven bamboo duck-herder's hood turned into a plucked string instrument, resting on a mat` |
| `library.wonosobo.p8.1.poster` | `the Dieng Culture Festival at dusk with paper lanterns rising over the Arjuna temples, no identifiable faces` |

**Ilustrasi untuk halaman tanpa media** (7) — `library.{wilayah}.p{n}.1`, 960 × 640 (3:2), JPG q80.

- Isi Pustaka bersifat faktual, jadi ilustrasi hanya dipakai bila foto berlisensi bebas tidak ada.
- Beri kredit "Ilustrasi GELITA (dibuat dengan bantuan AI)".

| Kode | Halaman | Prompt (setelah `{GAYA}`) | Catatan |
|---|---|---|---|
| `library.temanggung.p7.1` | Parakan, Kota Bambu Runcing | `a quiet street of old heritage houses and shophouses in the town of Parakan in the morning, a symbolic bundle of sharpened bamboo spears tied with a red-and-white ribbon displayed respectfully in front, no people` | simbolis, bukan monumen tertentu |
| `library.temanggung.p9.1` | Rasa Temanggung | `food illustration on a woven bamboo table: a plate of kupat tahu with peanut sauce, a plate of crispy fried uceng fish, and a bowl of spicy mangut beong` | tanpa kopi atau rokok di meja |
| `library.temanggung.p10.1` | Tips Jaka: Membaca Informasi Tersurat | `{JAKA} sitting at a bamboo table, reading a short paper text and pointing at one line with his finger, a magnifying glass beside him, four small step symbols floating around him: an eye, a magnifier, a pointing finger and a check mark, no letters` | tokoh sesuai lembar |
| `library.magelang.p8.1` | Prasasti Canggal dan Awal Mataram Kuno | **utamakan foto:** cari di Commons dengan kata kunci "Prasasti Canggal" atau "Canggal inscription" dan periksa lisensinya. Bila tidak ada: `an ancient grey stone inscription slab in a museum display case, its carved script worn and unreadable` | jangan membuat aksara Pallawa palsu yang terbaca |
| `library.magelang.p10.1` | Tips Jaka: Fakta, Pendapat, dan Menghubungkan Informasi | `{JAKA} holding two cards, one showing a magnifier with a check mark symbol and the other a speech bubble with a small heart symbol, a picture and a page of text laid side by side on the table, no letters` | |
| `library.wonosobo.p5.1` | Embun Upas, Es di Pulau Tropis | **utamakan foto:** Commons "embun upas Dieng" atau "frost Dieng". Bila tidak ada: `early-morning frost covering grass and potato leaves on the Dieng plateau, tiny white ice crystals on the leaves, cold blue light with the first warm sunlight, the Arjuna temples faint in the mist` | hapus `snow` dari negatif |
| `library.wonosobo.p10.1` | Tips Jaka: Cek Dulu Sebelum Percaya dan Berbagi | `{JAKA} looking thoughtfully at a smartphone, four small symbols floating around him: an official building, a clock, a magnifier and a shield, the phone screen shows only abstract shapes` | layar tanpa teks |

- **Negatif:** `{NEGATIF}, readable text on screens or paper`

### 11.10 Direncanakan: menunggu `rencana-perbaikan-tampilan.md` (21)

Slot ini belum dibaca kode. Kartunya bertanda **menunggu** beserta fase rencana itu. Setelah fasenya selesai dan `ImageSlots` ada, tandanya hilang sendiri (§5 Fase F).

| Kode | Ukuran | Fase | Tampil di | Prompt (setelah `{GAYA}`) |
|---|---|---|---|---|
| `ui.pin` | 256 × 256 PNG transparan | C1 | pin Peta Kedu (56 px); status terkunci/tuntas/terbuka ditambahkan CSS, jangan digambar | `round antique-gold map-pin medallion with a small navy lantern glyph in the centre, thick rim, bold and simple, readable at 56 px, {TRANSPARAN}` |
| `ui.lantern` | 256 × 256 | C2 | lentera HUD (tahap nyala lewat `drop-shadow`), tirai peta, cadangan Kenali | `{LENTERA} front view as a simplified icon, bold outlines, bright flame, readable at 28 px, {TRANSPARAN}` |
| `map.pin.temanggung` | 256 × 256 | C1 | pin Temanggung | `round gold map-pin medallion with twin volcano peaks and a small tobacco leaf, navy enamel, readable at 56 px, {TRANSPARAN}` |
| `map.pin.magelang` | 256 × 256 | C1 | pin Magelang | `round gold map-pin medallion with a bell-shaped stupa silhouette, navy enamel, readable at 56 px, {TRANSPARAN}` |
| `map.pin.wonosobo` | 256 × 256 | C1 | pin Wonosobo | `round gold map-pin medallion with a small temple above a puff of cloud, navy enamel, readable at 56 px, {TRANSPARAN}` |
| `map.lantern.temanggung` | 256 × 256 | C2 | tombol Kenali Temanggung (30 px) | `{LENTERA} as a bold icon with a soft green-gold flame, readable at 30 px, {TRANSPARAN}` |
| `map.lantern.magelang` | 256 × 256 | C2 | tombol Kenali Magelang | `{LENTERA} as a bold icon with a warm stone-gold flame, readable at 30 px, {TRANSPARAN}` |
| `map.lantern.wonosobo` | 256 × 256 | C2 | tombol Kenali Wonosobo | `{LENTERA} as a bold icon with a cool turquoise-gold flame, readable at 30 px, {TRANSPARAN}` |
| `bg.loading.temanggung` | 1920 × 1080 JPG | C3 | tirai "Menuju Temanggung…" (judul, tagline, jejak kaki di tengah) | `{LATAR} a footpath climbing between tobacco terraces towards the twin volcanoes at dawn, calm centre for a title` |
| `bg.loading.magelang` | 1920 × 1080 | C3 | tirai "Menuju Magelang…" | `{LATAR} a path through palm trees towards the silhouette of Borobudur in morning mist, calm centre` |
| `bg.loading.wonosobo` | 1920 × 1080 | C3 | tirai "Menuju Wonosobo…" | `{LATAR} a winding mountain road rising into a sea of clouds towards the Dieng plateau, calm centre` |
| `avatar.1` … `avatar.10` | 512 × 512 PNG transparan | B | pemilih avatar (lingkaran ≥ 64 px), HUD, profil | `round avatar medallion with a thick deep-navy rim and warm parchment centre, one bold golden emblem: {MOTIF}, readable at 48 px, fills the square edge to edge, {TRANSPARAN}` |

`{MOTIF}` avatar dipilih netral (tanpa wajah), supaya setiap anak dapat memilih tanpa soal rupa atau jenis kelamin:

1. Lentera Kedu
2. Serpihan Cahaya
3. gunung kembar Sindoro–Sumbing
4. stupa Borobudur
5. kuda lumping
6. kuluk bulu Topeng Ireng
7. buah carica
8. Telaga Warna
9. kendang
10. bundengan

Rim navy membedakan avatar dari lencana wilayah yang berim emas.

## 12. Katalog ikon

### 12.1 Spesifikasi ikon antarmuka

Ikon ada di `icon()` (`app/Helpers/ui_helper.php`). Semuanya SVG inline dengan spesifikasi berikut:

- `viewBox 0 0 24 24`, `stroke-width 2`, ujung dan sambungan bulat (`round`), warna `currentColor`, `fill="none"`.
  - Pengecualian: `play` berisi `fill="currentColor"`, dan `pause` bergaris tebal 3.
- Selalu dekoratif (`aria-hidden`): makna ada di teks atau `aria-label` induknya.
- Tampil 16–32 px, termasuk di layar proyektor. Beri ruang optis 2 px dari tepi viewBox.

Bila ikon diganti gayanya, **ganti ke-48 ikon sekaligus**, jangan dicampur. Cara paling andal: gambar ulang di Figma/Inkscape pada kisi 24 px, lalu salin atribut `d` ke `icon_paths()` (Fase A).

Prompt AI (hanya untuk eksplorasi gaya, hasilnya tetap digambar ulang sebagai vektor):

```text
a consistent set of simple line icons on a 24 by 24 pixel grid, 2 px rounded strokes, round line caps and joins, no fill, single colour, friendly and slightly rounded shapes suitable for a children's educational game inspired by Javanese heritage, pixel-perfect alignment, plain white background, no text
```

### 12.2 Daftar ikon (48)

"Tempat" dihitung ulang oleh pembangun dengan memindai `icon('…')` di `app/Views`. Kolom di bawah hanya contoh.

| Nama | Makna | Contoh tempat | Bentuk (brief menggambar) |
|---|---|---|---|
| `home` | beranda / Peta Kedu | HUD | atap pelana dari kiri ke kanan, badan rumah dengan pintu di tengah |
| `left` / `right` | kembali / lanjut | navigasi slide, tombol bawah | chevron tunggal |
| `up` / `down` | naik / turun | urutan kartu puzzle `ordering` | chevron tunggal |
| `sound` | suara menyala | HUD, kartu ketuk | pengeras suara + dua gelombang |
| `sound-off` | suara mati | HUD | pengeras suara + tanda silang |
| `logout` | keluar | HUD, panel, keluar tantangan | kusen pintu + panah keluar ke kanan |
| `lock` | terkunci | pin, kartu wilayah, Pustaka | gembok dengan lengkung |
| `check` | benar / selesai | pos selesai, modal, toast | centang |
| `cross` | salah / tutup | modal, overlay Kenali | silang |
| `star` | bintang / tuntas | bintang skor, pin tuntas, lencana tanpa gambar | bintang lima sudut (diisi CSS saat diraih) |
| `book` | Pustaka | nav peta, layar selesai | buku dengan garis pembatas |
| `map` | peta | nav, layar selesai | peta lipat tiga panel |
| `play` | putar | narasi, pemutar | segitiga penuh |
| `pause` | jeda | narasi, petunjuk cari | dua batang tegak |
| `replay` | ulangi | narasi, hasil | panah melingkar berlawanan jarum jam |
| `text` | transkrip | pemutar audio | tiga garis, terakhir lebih pendek |
| `hint` | petunjuk | tombol Petunjuk di layar tantangan | bola lampu |
| `clock` | waktu | statistik, panel | jam dengan dua jarum |
| `building` | sumber resmi / sekolah | kartu sumber `official`, panel sekolah | gedung beratap dengan pintu |
| `question` | tidak jelas / anonim | kartu sumber `anonymous`, modal | lingkaran + tanda tanya |
| `user` | profil | nav peta, refleksi | kepala + bahu |
| `users` | peserta | dasbor | dua orang |
| `lantern` | lentera / serpihan | HUD, tirai, Kenali, orb selesai | kait, badan trapesium, alas, garis nyala |
| `download` | unduh / pasang aplikasi | ekspor, halaman awal | panah turun ke baki |
| `upload` | unggah | media, impor | panah naik dari baki |
| `eye` | lihat sandi | kolom sandi | mata dengan pupil |
| `menu` | menu | kepala panel | tiga garis |
| `search` | cari | saringan panel | kaca pembesar |
| `info` | informasi | flash, modal, layar putar | lingkaran + huruf i |
| `warn` | peringatan | flash, form | segitiga + tanda seru |
| `grid` | kisi | media, rilis | empat kotak |
| `chart` | grafik | analitik | diagram batang |
| `list` | daftar | panel | daftar berpoin |
| `target` | ketepatan | statistik | tiga lingkaran konsentris |
| `trend` | tren | pretest–posttest | garis naik + panah |
| `message` | masukan / narasi | masukan, Narasi | balon bicara |
| `image` | gambar | media | bingkai + matahari + gunung |
| `flask` | penelitian | studi, verifikasi | labu kimia |
| `shield` | keamanan | tata kelola, ekspor | perisai |
| `key` | kata sandi | ganti sandi | kunci |
| `edit` | sunting | panel | pensil |
| `trash` | hapus | panel | tempat sampah |
| `puzzle` | puzzle | kartu misi, tombol Periksa gambar | keping puzzle |
| `sparkle` | istimewa | Balai Refleksi, Tonton penutup | garis memancar delapan arah |
| `link` | tautan | editor Pustaka | dua mata rantai |
| `video` | video | Pustaka | kamera video |

### 12.3 Ikon vektor khusus (2)

| Ikon | Berkas | Spesifikasi | Brief |
|---|---|---|---|
| Jejak kaki | `partials/footsteps.php` | viewBox 24 × 40, **isi** `currentColor`; kaki kanan dicerminkan dan diputar CSS | telapak lonjong + lima jari bulat mengecil ke kelingking; dipakai tirai wilayah dan kartu bab |
| Layar putar | `components/rotate-gate.php` | viewBox 120 × 120, garis 4, `round` | ponsel tegak (rect 36 × 64, rx 7) dengan panah lengkung yang mengajak memutar ke mendatar |

### 12.4 Bukan aset gambar

Berikut dibuat oleh CSS atau teks. Katalog mencantumkannya agar tidak dipesan sebagai gambar:

- nyala lentera HUD `flame-0…3`, orb layar selesai, konfeti;
- visual tirai tantangan (`partials/challenge-art.php`: keping, huruf K-E-D-U, kartu ✓/✗, kartu mengipas, sorot lentera);
- lapisan efek narasi (`fog`, `fog-lift`, `glow`, `flash`, `shake`, `dim`);
- simbol teks ★ ✓ ✗ ⓘ di panel;
- lentera di `offline.html`, yang memakai path ikon `lantern` yang sama.

## 13. Katalog musik dan efek suara

### 13.1 Spesifikasi umum

- **Lokasi dan cara pakai:**
  - berkasnya `public/assets/audio/music/{map,region,challenge}.mp3` dan `public/assets/audio/sfx/{click,correct,wrong,lock,shard,region-done}.mp3`;
  - dibaca langsung oleh `core/audio.js` (Howler), bukan `media_assets`;
  - diganti dengan menimpa berkas bernama sama lewat git, tidak lewat panel (`AssetChecklist::SOUNDS`);
  - baru berbunyi setelah ketukan pertama (`Sfx.unlock()`).
- **Format** (`tools/audio/master.py`): MP3 44,1 kHz stereo 128 kbps CBR, header Xing (`-write_xing 1`), tanpa ID3 (`-id3v2_version 0`).
- **Kenyaringan terpadu:** target per berkas ada di tabel di bawah; puncak sejati ≤ −1 dBTP.
  - Volume Howler dipakai dua kali (per bunyi × global, bawaan 0,8 × 0,8), sehingga musik dan efek terdengar ±4 dB lebih pelan daripada targetnya. Narasi tidak terkena pengurangan ini.
  - Hasilnya, musik tetap ±8–9 LU di bawah narasi (−16 LUFS). Ini perlu karena narasi Jaka dan Mbah Kedu diputar di atas musik peta, dan petunjuk `cari` di atas musik tantangan.
- **Aturan bunyi:**
  - gamelan Jawa Tengah dengan laras pelog/slendro;
  - tanpa vokal, tanpa alat musik Barat, tanpa drum set;
  - komposisi orisinal: tanpa sampel atau rekaman pihak ketiga yang tidak berlisensi;
  - bila merekam pengrawit, minta izin tertulis dan catat di lembar produksi.

| Kelompok | Target kenyaringan |
|---|---|
| Musik (diukur dua putaran) | `map` −20, `region` −20,5, `challenge` −21,5 LUFS |
| Efek (diukur setelah diberi hening sampai 3 detik) | `click` −24, `lock` −20, `wrong` −19, `correct` −17, `shard` −16,5, `region-done` −15 LUFS |

Alat AI musik (mis. generator lagu atau efek suara) sering meleset pada laras dan struktur gongan. Ada tiga jalur:

1. bangkitkan ulang dengan `tools/audio` (deterministik, byte demi byte);
2. rekam pengrawit sungguhan;
3. pakai AI, lalu sunting titik loop dan kenyaringannya.

### 13.2 Musik (3)

| Berkas | Diputar di | Suasana | Laras · bentuk · tempo | Ricikan | Loop |
|---|---|---|---|---|---|
| `music/map.mp3` | Peta Kedu (`map.js`) | megah, mengajak menjelajah | slendro manyura · ladrang, 2 gongan × 32 ketukan · irama tanggung ±66 ketukan/menit | demung, 2 saron barung, peking, bonang barung & panerus (mipil, gembyang), slenthem, kenong, kethuk, kempul, gong ageng, kendang; suling di gongan kedua | 58,2 s |
| `music/region.mp3` | peta wilayah | tenang, desa di lereng gunung | pelog nem · ketawang, 3 gongan × 16 ketukan · irama dadi ±42 ketukan/menit | gender, gambang, siter, saron, slenthem, kenong, kethuk, kempul, gong, kendang lirih, suling | 68,6 s |
| `music/challenge.mp3` | layar tantangan (`challenge.js`) | hening, menjaga fokus membaca | slendro sanga · ketawang, balungan nibani, 4 gongan · ±52 ketukan/menit | slenthem, demung lirih, gender, kenong, kethuk, kempul, gong; **tanpa** kendang dan suling | 73,8 s |

`map.mp3`

```text
Instrumental Central Javanese gamelan, ladrang form in slendro manyura tuning, majestic and inviting, like setting out across a misty highland plain at dawn; demung and saron play the balungan melody, peking and bonang barung and panerus play interlocking elaborations, slenthem, kenong, kethuk, kempul and a deep gong ageng mark the cycle, kendang leads gently, a bamboo suling enters in the second cycle; about 66 beats per minute, exactly two gong cycles of 32 beats (about 58 seconds) that loop seamlessly with the gong stroke on the loop point; no vocals, no western instruments, no drum kit, no synth pads, warm natural room sound
```

`region.mp3`

```text
Instrumental Central Javanese gamelan, ketawang form in pelog nem tuning, calm and pastoral, a quiet village on a mountain slope in the morning; gender, gambang and siter play flowing ornaments, saron and slenthem carry the melody, soft kendang, gentle suling, kenong, kethuk, kempul and gong mark three gong cycles of 16 beats at about 42 beats per minute (about 69 seconds), seamless loop; no vocals, no western instruments, soft dynamics
```

`challenge.mp3`

```text
Minimal instrumental Central Javanese gamelan for concentration, ketawang form in slendro sanga tuning, sparse nibani balungan on slenthem and soft demung, gentle gender phrases, kenong, kethuk, kempul and gong only at structural points, four gong cycles at about 52 beats per minute (about 74 seconds), seamless loop; no kendang, no suling, no vocals, very low melodic density so that children can read while it plays
```

- **Brief komposer:** tabel di atas, ditambah catatan teknis `docs/audio.md`:
  - embat 1212 sen per oktaf, ombak antarbilah;
  - bonang mipil dan peking nacah jatuh di ketukan kuat;
  - gong ageng ±41 Hz dengan ombak ±2,4 Hz.
- **Pasca-produksi loop:**
  - potong tepat di akhir gongan terakhir, lalu lipat ekor gema ke awal berkas (*crossfade* 50–150 ms);
  - putar tiga kali berturut-turut untuk mendengar sambungan;
  - kompresor lembut untuk meredam pukulan gong;
  - kenyaringan diukur pada dua putaran;
  - enkode dengan Xing agar panjang dekode sama persis dengan panjang loop.
- **Cek:** narasi di atasnya tetap jelas; tidak ada klik di titik loop; laras tidak terdengar "piano".

### 13.3 Efek suara (6)

| Berkas | Durasi | Diputar saat | Prompt |
|---|---|---|---|
| `sfx/click.mp3` | 0,13 s | tombol HUD, keping puzzle, kata rumpang, kartu boleh | `a single soft wooden gambang key tap, very short (0.13 seconds), gentle, dry, no reverb tail` |
| `sfx/correct.mp3` | 0,9 s | jawaban benar (semua arena) | `three rising bonang pot-gong notes in pelog (5, 6, high 1) followed by a bright peking chime, 0.9 seconds, encouraging, clean natural decay` |
| `sfx/wrong.mp3` | 0,9 s | jawaban salah | `two soft falling demung notes damped by hand with a faint low kempul hum, 0.9 seconds, gentle and non-punishing, not a buzzer` |
| `sfx/lock.mp3` | 0,5 s | wilayah, tantangan, atau Pustaka terkunci (`map.js`) | `a dry double tuk-tuk of a kethuk followed by a small muted kecrek jingle, 0.5 seconds` |
| `sfx/shard.mp3` | 3,2 s | layar selesai: serpihan kembali (`finished.js`) | `a rising sparkling arpeggio on peking and gender with a soft shimmer, ending on a kenong stroke, 3.2 seconds, magical and rewarding` |
| `sfx/region-done.mp3` | 5,1 s | layar selesai tantangan penuntas wilayah (`finished.js`) | `a short suwuk ending phrase on bonang and saron slowing down, closed by a deep gong ageng together with kenong and kempul, 5.1 seconds, triumphant and warm` |

- **Pasca-produksi:**
  - hening di awal ≤ 5 ms, supaya `click` terasa responsif;
  - akhir dipudarkan;
  - kenyaringan per target §13.1 (ukur setelah diberi hening sampai 3 detik, seperti `master.py`);
  - puncak sejati ≤ −1 dBTP; MP3 stereo 128 kbps.
- **Cek:**
  - `wrong` tidak terdengar menghukum;
  - `correct` dan `shard` tidak lebih keras dari narasi;
  - putar cepat berulang `click` tanpa klik digital.

**Membangkitkan ulang yang bawaan:**

```bash
cd tools/audio && python music.py && python master.py music
python sfx.py && python master.py sfx
```

**Tidak dibuat:** `sfx/page` dan `music/library` disebut komentar `core/audio.js`, tetapi tidak dipanggil di mana pun (§2.2 temuan 15).

## 14. Katalog narasi

### 14.1 Spesifikasi berkas

Sumber: `docs/naskah-cerita.md` §Konvensi dan `docs/audio.md` §Spesifikasi.

- **Jumlah:** 96 baris per bahasa, 192 berkas:
  - 88 baris naskah: `intro` 9, `map_intro` 3, `region_intro` 12, `level_open` 47, `level_done` 12, `ending` 5;
  - 8 petunjuk arena `cari`.
- **Folder dan nama:** `public/assets/audio/narasi/{id|en}/{kode}.mp3`. Nama = kode baris, huruf besar/kecil bebas; satu berkas satu baris.
- **Format:** MP3 **mono** 44,1 kHz, 96 kbps CBR dengan header Xing (agar `MediaStore` membaca durasinya). Impor juga menerima M4A, OGG, dan WAV.
- **Kenyaringan −16 LUFS per berkas**, diukur bersama heningnya. Pengecualian:
  - Mbah Kedu −16,5;
  - Jaka `determined` −15,5, `afraid` −16,5, `sad` −17;
  - Mbah Kedu `weak` −18.

  Puncak sejati ≤ −1 dBTP.
- **Hening 0,3 detik** di awal dan akhir. Suara saja, tanpa musik atau efek.
- **Transkrip** = teks baris persis. Teks tampil di layar, dan layar maju otomatis 1,2 detik setelah audio selesai.
- **Durasi acuan:** durasi rekaman bawaan dari `tools/audio/qa-narasi.json` (kolom di §14.6). Rekaman baru sebaiknya dalam ±15%. Total ±18,4 menit (ID) dan ±13,8 menit (EN).

### 14.2 Pemeran suara (profil)

Profil ini dipakai sebagai brief pengisi suara, sebagai prompt *voice design* mesin TTS, atau sebagai instruksi gaya TTS berbasis bahasa. Rekaman bawaan saat ini adalah TTS: Mimic 3 Javanese untuk ID; Piper kristin, jenny, dan joe untuk EN (`docs/audio.md`).

| Tokoh | Profil (ID) | Profile (EN) |
|---|---|---|
| Narator | Perempuan dewasa 30–45 tahun, pendongeng hangat dan tenang. Bahasa Indonesia baku dengan logat Jawa halus yang ringan. Artikulasi sangat jelas untuk anak SD, tempo sedang, jeda pada koma dan titik. Kata kunci cerita (Cahaya Kedu, Lentera Kedu, Kabut Lupa, Serpihan Cahaya) ditekankan lembut. Tidak tampil sebagai gambar. | Adult female storyteller in her 30s–40s, warm and calm, clear neutral English, very clear articulation for 8–12-year-olds, medium pace, natural pauses at commas and full stops, gently stressing the story's key words (the Light of Kedu, the Lantern of Kedu, the Mist of Forgetting, the Shards of Light); Javanese names as in §14.5. |
| Jaka | Anak laki-laki ±11 tahun, atau pengisi suara dewasa yang meyakinkan sebagai anak. Bersemangat dan jujur, nada tinggi alami. Logat Jawa ringan wajar, karena ia anak Kedu. Suaranya naik saat senang atau takut, pelan saat menyesal. | A boy of about 11, or an adult actor convincingly playing a child; eager and honest, naturally high pitch; a light Indonesian accent is welcome as long as every word stays clear; the voice rises when happy or scared and softens when sorry; says "Mbah" as /əmˈbɑː/. |
| Mbah Kedu | Kakek Jawa ±75 tahun, lembut dan bijak. Tempo pelan, logat Jawa halus (*medok* halus). "Le" (dari *thole*) dibaca pendek. Suara rendah hangat dengan getar lansia ringan. Pada pose `weak`: lemah, bergetar, desah napas. | Elderly Javanese grandfather of about 75, gentle and wise, slow pace, soft warm low voice with a slight tremor of age, a light Javanese accent is welcome; "my boy" said tenderly; in the weak pose: faint, trembling, breathy. |

Suara orang nyata tidak boleh dikloning tanpa izin tertulisnya. Periksa lisensi mesin TTS untuk distribusi pendidikan.

### 14.3 Pengubah menurut pose dan efek

| Pose | Arahan (ID) | Direction (EN) |
|---|---|---|
| Jaka `idle` | wajar, ingin tahu | natural, curious |
| Jaka `happy` | cerah, rentang nada lebar, sedikit lebih cepat | bright, wide pitch range, slightly faster |
| Jaka `afraid` | lebih tinggi, sedikit bergetar, napas cepat, tidak berteriak | higher, slightly shaky, quick breath, never screaming |
| Jaka `determined` | tegas, mantap, sedikit lebih keras | firm, steady, slightly louder |
| Jaka `sad` | lebih rendah, pelan, lirih | lower, slower, softer |
| Jaka `bow` | tenang, sopan, tulus | calm, respectful, sincere |
| Mbah Kedu `idle` | tenang, hangat, pelan | calm, warm, slow |
| Mbah Kedu `smile` | sedikit lebih cerah, senyum terdengar | slightly brighter, an audible smile |
| Mbah Kedu `worried` | lebih rendah dan pelan, prihatin, tidak menakutkan | lower and slower, concerned, never frightening |
| Mbah Kedu `weak` | sangat pelan, bergetar, desah napas, lirih | very slow, trembling, breathy, quiet |
| Narator | hangat bercerita | warm storytelling |

| Efek | Arahan (ID) | Direction (EN) |
|---|---|---|
| `glow` | hangat, takjub, sedikit tersenyum | warm, full of wonder, a slight smile |
| `fog` | merendah, misterius, agak berbisik | hushed, mysterious, slightly whispered |
| `fog-lift` | lega, makin cerah | relieved, brightening |
| `shake` | tegang, mendesak, puncak dramatis | tense, urgent, a dramatic peak |
| `dim` | meredup, lirih, pelan | fading, soft, slow |
| (kosong) | ikuti pose | follow the pose |

`flash` sah di `Config\Gelita::$dialogueEffects`, tetapi tidak dipakai naskah saat ini.

### 14.4 Bentuk prompt per baris

Pembangun menyusun prompt setiap baris dari profil tokoh, pose, efek, catatan baris, durasi acuan, dan teks. Contoh untuk `dialog-temanggung-08`:

**ID**

```text
Tokoh: Mbah Kedu — kakek Jawa ±75 tahun, lembut dan bijak, tempo pelan, logat Jawa halus, "Le" dibaca pendek, suara rendah hangat dengan getar lansia ringan.
Pose (worried): lebih rendah dan pelan, prihatin, tidak menakutkan.
Efek (shake): tegang, mendesak, puncak dramatis.
Arahan baris: mendesak, memanggil keras tetapi tidak marah pada "Tunggu, Le! Berhenti!", lalu tegas menasihati.
Durasi acuan: ±8,9 detik (±15%). Satu berkas, suara saja, tanpa musik, hening 0,3 detik di awal dan akhir.
Teks: "Tunggu, Le! Berhenti! Jangan hanya melihat panahnya. Baca dulu seluruh tulisannya."
```

**EN**

```text
Character: Mbah Kedu (Grandpa Kedu) — elderly Javanese grandfather of about 75, gentle and wise, slow pace, soft warm low voice with a slight tremor of age; "my boy" said tenderly.
Pose (worried): lower and slower, concerned, never frightening.
Effect (shake): tense, urgent, a dramatic peak.
Line direction: urgent, calling out loudly but not angry on "Wait, my boy! Stop!", then firm advice.
Reference length: about 6.7 seconds (±15%). One file, voice only, no music, 0.3 s of silence at the start and end.
Text: "Wait, my boy! Stop! Don't just look at the arrow. Read all the words first."
```

Untuk mesin TTS yang menerima SSML, halaman juga menampilkan kerangka yang dapat disunting:

```xml
<speak>
  <prosody rate="-8%" pitch="-2st">Tunggu, Le!</prosody><break time="250ms"/>
  <prosody rate="-8%" pitch="-2st">Berhenti!</prosody><break time="400ms"/>
  <prosody rate="-12%">Jangan hanya melihat panahnya. Baca dulu seluruh tulisannya.</prosody>
</speak>
```

### 14.5 Lafal nama Jawa di narasi Inggris

Diambil dari `tools/audio/en_lexicon.py` (`NAMES`). Halaman membacanya langsung dari berkas itu.

| Kata | IPA (AS) | IPA (Inggris) | Kata | IPA (AS) | IPA (Inggris) |
|---|---|---|---|---|---|
| Jaka | dʒˈɑːkə | dʒˈɑːkə | Dieng | diˈɛŋ | diˈɛŋ |
| Mbah | əmbˈɑː | əmbˈɑː | Arjuna | ɑːɹdʒˈuːnə | ɑːdʒˈuːnə |
| Kedu | kədˈuː | kədˈuː | Telaga | təlˈɑːɡə | təlˈɑːɡə |
| Temanggung | təmˈɑːŋɡʊŋ | təmˈɑːŋɡʊŋ | Warna | wˈɑːɹnə | wˈɑːnə |
| Magelang | mˌɑːɡəlˈɑːŋ | mˌɑːɡəlˈɑːŋ | mie | mˈiː | mˈiː |
| Wonosobo | wˌɑːnoʊsˈoʊboʊ | wˌɒnəʊsˈəʊbəʊ | ongklok | ˈɑːŋklɑːk | ˈɒŋklɒk |
| Sindoro | sɪndˈoʊɹoʊ | sɪndˈəʊɹəʊ | carica | tʃɑːɹˈiːkə | tʃɑːɹˈiːkə |
| Sumbing | sˈuːmbɪŋ | sˈuːmbɪŋ | Lengger | lˈɛŋɡɚ | lˈɛŋɡə |
| Merapi | məɹˈɑːpi | məɹˈɑːpi | ruwatan | ɹuːwˈɑːtɑːn | ɹuːwˈɑːtɑːn |
| Merbabu | mɚbˈɑːbuː | mɜːbˈɑːbuː | kentongan | kəntˈɑːŋɑːn | kəntˈɒŋɑːn |
| Menoreh | mənˈoʊɹeɪ | mənˈəʊɹeɪ | jaran | dʒˈɑːɹɑːn | dʒˈɑːɹɑːn |
| Borobudur | bˌɔːɹoʊbuːdˈʊɹ | bˌɒɹəʊbuːdˈʊə | kepang | kəpˈɑːŋ | kəpˈɑːŋ |
| Mendut | məndˈuːt | məndˈuːt | Topeng | tˈoʊpɛŋ | tˈəʊpɛŋ |
| Pawon | pˈɑːwɑːn | pˈɑːwɒn | Ireng | ˈiːɹəŋ | ˈiːɹəŋ |
| Tidar | tˈiːdɑːɹ | tˈiːdɑː | Liyangan | lɪjˈɑːŋɑːn | lɪjˈɑːŋɑːn |
| Pringapus | pɹɪŋˈɑːpʊs | pɹɪŋˈɑːpʊs | Gondosuli | ɡˌɑːndoʊsˈuːli | ɡˌɒndəʊsˈuːli |
| uceng | ˈuːtʃɛŋ | ˈuːtʃɛŋ | Parakan | pəɹˈɑːkɑːn | pəɹˈɑːkɑːn |
| Nandi | nˈɑːndi | nˈɑːndi | kupat | kˈuːpɑːt | kˈuːpɑːt |
| tahu | tˈɑːhuː | tˈɑːhuː | rigen | ɹˈiːɡɛn | ɹˈiːɡɛn |
| gamelan | ɡˈæməlæn | ɡˈæməlæn | Kabut | kˈɑːbʊt | kˈɑːbʊt |

### 14.6 Arahan per baris (96)

Durasi acuan dalam detik (ID / EN) diambil dari `qa-narasi.json`. "CER tinggi" menandai baris yang di rekaman bawaan paling sering salah dengar oleh Whisper (`docs/audio.md`). Dengarkan baris itu lebih dulu dan jaga artikulasinya.

**Cerita pembuka (`intro`)**

| Kode | Tokoh · pose · efek | Durasi | Arahan (ID) | Direction (EN) |
|---|---|---|---|---|
| `intro-01` | Narator · — · glow | 19,2 / 14,7 | Pembuka dongeng: lambat, hangat, penuh takjub; jeda kecil setelah deretan nama gunung; "Cahaya Kedu" ditekankan lembut | Story opening: slow, warm, full of wonder; a small pause after the list of mountains; gently stress "the Light of Kedu" |
| `intro-02` | Narator · — · — | 25,3 / 15,9 | Mengalir tenang; deret "dari dongeng…, dari relief…, dari tembang…, dari setiap tulisan…" berirama; "Lentera Kedu" ditekankan | Calm and flowing; give the list "from grandparents' tales…, from the reliefs…, from the songs…, from every piece of writing…" a steady rhythm; stress "the Lantern of Kedu" |
| `intro-03` | Narator · — · fog | 18,6 / 14,8 | Merendah dan agak berbisik sejak "Namun perlahan"; misterius tetapi tidak menakutkan; perlambat pada "Kabut Lupa" | Lower and slightly hushed from "But slowly"; mysterious, not scary; slow down on "the Mist of Forgetting" |
| `intro-04` | Narator · — · shake | 19,5 / 17,4 | Tegang dan membangun; percepat menuju "lalu cahayanya pecah!"; dramatis tetapi terkendali pada "Lima belas Serpihan Cahaya melesat…" | Building tension; speed up towards "and then its light shattered!"; dramatic but controlled on "Fifteen Shards of Light shot…" |
| `intro-05` | Mbah Kedu · weak · dim | 21,5 / 16,8 | Lemah, napas pendek, bergetar; jeda sebelum "tubuhku ikut melemah"; kalimat terakhir sedikit menguat, penuh harap | Weak, short of breath, trembling; pause before "my body grows weaker too"; the last sentence a little stronger, hopeful |
| `intro-06` | Jaka · determined · glow | 11,2 / 8,1 | Berani dan bersemangat; "Biar aku yang pergi, Mbah!" tegas; "Aku janji!" mantap sambil tersenyum | Brave and eager; "Let me go, Mbah!" firm; "I promise!" confident, with a smile |
| `intro-07` | Narator · — · — | 21,9 / 14,9 | Menjelaskan aturan seperti guru yang ramah; runtut; tekankan "lima serpihan", "satu tantangan", "pojok kiri atas" | Explaining the rules like a friendly teacher; orderly; stress "five shards", "a challenge", "top-left corner" |
| `intro-08` | Mbah Kedu · smile · — | 18,6 / 15,0 | Hangat menasihati; tiga kunci dibaca berjeda jelas per wilayah; senyum terdengar | Warm advice; a clear pause for each region's key; an audible smile |
| `intro-09` | Narator · — · glow | 17,1 / 13,1 | Mengajak, makin bersemangat; "Bacalah… pikirkan… putuskan…" berirama; akhiri dengan seruan hangat | Inviting and growing in energy; "Read… think… decide…" rhythmic; end with a warm call to action |

**Narasi Peta Kedu (`map_intro`)**

| Kode | Tokoh · pose · efek | Durasi | Arahan (ID) | Direction (EN) |
|---|---|---|---|---|
| `peta-01` | Jaka · idle · fog | 12,2 / 9,2 | Terpesona lalu prihatin; jeda pada "Sekarang lihatlah…"; nada turun di kalimat terakhir | Amazed, then concerned; pause at "Now look…"; drop the pitch on the last sentence |
| `peta-02` | Jaka · determined · — | 13,2 / 9,6 | Mantap merencanakan; tiga wilayah disebut berurutan dengan jeda | Firm, planning ahead; name the three regions in order with pauses |
| `peta-03` | Jaka · happy · glow | 13,5 / 10,3 | Ceria memberi petunjuk; "Ketuk ikon lentera…" jelas; akhiri riang "Ayo, kita berangkat!" | Cheerful guide; "Tap the lantern icon…" clear; end brightly "Come on, let's go!" |

**Kenali wilayah (`region_intro`)**

| Kode | Tokoh · pose · efek | Durasi | Arahan (ID) | Direction (EN) |
|---|---|---|---|---|
| `kenal-temanggung-01` | Mbah Kedu · smile · — | 15,9 / 12,9 | Bangga dan lembut; perumpamaan "kakak dan adik" penuh sayang | Proud and gentle; the "older and younger sibling" image full of affection |
| `kenal-temanggung-02` | Mbah Kedu · idle · — | 14,5 / 13,4 | Bercerita santai; "tembakau, kopi, dan sayuran" jelas; senyum pada "harum" | Relaxed storytelling; "tobacco, coffee, and vegetables" clear; a smile on "aroma" |
| `kenal-temanggung-03` | Mbah Kedu · idle · — | 21,0 / 16,2 | Berkisah sejarah; nama tempat dan angka tahun pelan dan jelas | Telling history; place names and the year slow and clear |
| `kenal-temanggung-04` | Mbah Kedu · smile · glow | 21,8 / 17,4 | Riang pada jaran kepang (sedikit berirama), lalu serius memberi pesan; tekankan "dengan teliti" | Lively on the jaran kepang (a little rhythmic), then earnest advice; stress "read carefully" |
| `kenal-magelang-01` | Mbah Kedu · smile · — | 14,2 / 11,5 | Kagum dan bangga; tekankan "terbesar di dunia"; pelan pada "batu demi batu" | Awed and proud; stress "the largest … in the world"; slow on "stone by stone" |
| `kenal-magelang-02` | Mbah Kedu · idle · — | 20,4 / 14,3 | Angka 2.672 jelas; "buku raksasa dari batu" terdengar menakjubkan | Say 2,672 clearly; make "a giant book made of stone" sound wonderful |
| `kenal-magelang-03` | Mbah Kedu · idle · — | 16,6 / 12,7 | Menjelaskan letak; "hampir segaris" jelas; "paku tanah Jawa" bernada cerita rakyat | Explaining places; "almost in a straight line" clear; "the nail of the land of Java" in a folk-tale tone |
| `kenal-magelang-04` | Mbah Kedu · smile · glow | 19,4 / 14,4 | Bersemangat pada Topeng Ireng ("gagah"), lalu tegas soal fakta dan pendapat | Enthusiastic about Topeng Ireng ("bold"), then clear and firm about facts and opinions |
| `kenal-wonosobo-01` | Mbah Kedu · smile · fog | 15,4 / 9,7 | Takjub, agak berbisik pada "negeri di atas awan"; bayangkan kabut lewat di antara rumah | Awed, almost whispering "the land above the clouds"; imagine mist drifting between houses |
| `kenal-wonosobo-02` | Mbah Kedu · idle · — | 17,7 / 14,3 | Deskriptif; "belerang" jelas; tiga warna berirama | Descriptive; "sulphur" clear; the three colours rhythmic |
| `kenal-wonosobo-03` | Mbah Kedu · idle · — | 19,2 / 15,6 | Buka dengan menggigil ringan ("dingin sekali"); nama makanan jelas; hormat saat menyebut ruwatan | Open with a slight shiver ("very cold"); food names clear; respectful on the ruwatan |
| `kenal-wonosobo-04` | Mbah Kedu · worried · fog | 15,2 / 11,8 | Serius dan waspada tetapi menenangkan; "Periksa…, timbang…, lalu putuskan…" pelan dan tegas | Serious and watchful but reassuring; "Check…, weigh…, then decide…" slow and firm |

**Dialog masuk wilayah (`level_open`) — Temanggung: *Papan Petunjuk yang Memudar***

| Kode | Tokoh · pose · efek | Durasi | Arahan (ID) | Direction (EN) |
|---|---|---|---|---|
| `dialog-temanggung-01` | Mbah Kedu · worried · fog | 16,3 / 11,3 | Sedih mengenang; pelan; "Semuanya kelabu" hampir berbisik | A sad memory; slow; "Everything is grey" almost a whisper |
| `dialog-temanggung-02` | Jaka · afraid · — | 7,1 / 4,8 | Cemas, sedikit bergetar, nada agak tinggi | Anxious, slightly shaky, a little higher |
| `dialog-temanggung-03` | Mbah Kedu · worried · — | 12,9 / 9,4 | Muram; "Dengar itu?" berbisik sambil menyimak; iba di kalimat terakhir | Grave; "Do you hear that?" whispered, listening; pity on the last sentence |
| `dialog-temanggung-04` | Jaka · idle · — | 8,5 / 4,9 | Heran dan polos; intonasi tanya naik | Puzzled and innocent; rising question intonation |
| `dialog-temanggung-05` | Mbah Kedu · worried · — | 15,8 / 11,0 | Menjelaskan dengan prihatin; "Satu tebakan keliru…" pelan dan tegas | A concerned explanation; "One wrong guess…" slow and firm |
| `dialog-temanggung-06` | Jaka · determined · glow | 9,9 / 6,9 | Tergesa-gesa dan bersemangat (inilah kesalahannya); cepat dan yakin | Eager and hasty (this is his mistake); quick and confident |
| `dialog-temanggung-07` | Jaka · happy · — | 7,5 / 5,7 | Girang dan terburu-buru, tetapi tetap jelas (**CER tinggi** ID) | Excited and hurried but still clear (**high CER** in ID) |
| `dialog-temanggung-08` | Mbah Kedu · worried · shake | 8,9 / 6,7 | Mendesak, memanggil keras tetapi tidak marah: "Tunggu, Le! Berhenti!"; lalu tegas menasihati | Urgent, calling out loudly but not angry: "Wait, my boy! Stop!"; then firm advice |
| `dialog-temanggung-09` | Jaka · sad · — | 11,2 / 7,5 | Membaca tulisan papan pelan dan kaget, lalu "Aduh…" lirih menyesal | Read the sign slowly, startled, then a soft, regretful "Oh no…" |
| `dialog-temanggung-10` | Mbah Kedu · smile · — | 11,1 / 8,5 | Bijak dan tersenyum; kalimat terakhir sedikit jenaka | Wise and smiling; the last sentence slightly humorous |
| `dialog-temanggung-11` | Jaka · sad · — | 6,4 / 5,0 | Lirih, menyesal, jujur | Soft, sorry, sincere |
| `dialog-temanggung-12` | Mbah Kedu · smile · glow | 17,7 / 13,5 | Menghibur dan hangat; berubah takjub pada "serpihan cahaya pertama mulai berkilau" | Comforting and warm; turn to wonder on "the first shard of light is starting to sparkle" |
| `dialog-temanggung-13` | Jaka · determined · — | 9,9 / 6,1 | Sadar dan tegas; "kata demi kata" pelan menegaskan | Understanding and firm; "word by word" slow and deliberate |
| `dialog-temanggung-14` | Mbah Kedu · idle · — | 10,9 / 8,0 | Tenang memberi tugas; "Lima serpihan" jelas | Calmly giving the task; "Five shards" clear |
| `dialog-temanggung-15` | Jaka · determined · fog-lift | 6,1 / 4,6 | Seruan penuh semangat, lantang ke kejauhan | A rousing call, projected into the distance |

**Magelang: *Relief yang Teracak***

| Kode | Tokoh · pose · efek | Durasi | Arahan (ID) | Direction (EN) |
|---|---|---|---|---|
| `dialog-magelang-01` | Jaka · happy · glow | 9,0 / 6,9 | Bangga dan riang, sedikit terlalu yakin ("pasti lebih mudah!") | Proud and cheerful, a little overconfident ("will be easy!") |
| `dialog-magelang-02` | Mbah Kedu · idle · — | 7,5 / 6,0 | Menegur lembut, pelan, penuh arti | A gentle warning, slow, meaningful |
| `dialog-magelang-03` | Jaka · idle · — | 10,2 / 8,2 | Penasaran; kagum pada "Megah sekali…", lalu heran | Curious; awed on "It's magnificent…", then puzzled |
| `dialog-magelang-04` | Mbah Kedu · worried · fog | 15,6 / 11,7 | Prihatin menjelaskan; kalimat terbalik "Awal cerita ada di akhir…" jelas | A concerned explanation; the reversed sentence "The beginning … at the end…" clear |
| `dialog-magelang-05` | Jaka · afraid · — | 6,4 / 4,2 | Berbisik cemas; "Mbah, dengar…" pelan | An anxious whisper; "Mbah, listen…" soft |
| `dialog-magelang-06` | Mbah Kedu · worried · — | 17,2 / 10,8 | Menirukan dua suara secara ringan pada kutipan "Katanya…", lalu kembali prihatin | Lightly imitate two voices on the "They say…" quotes, then return to concern |
| `dialog-magelang-07` | Jaka · idle · — | 2,9 / 2,4 | Pertanyaan pendek dan polos; artikulasi jelas (**CER tinggi** EN) | A short, innocent question; articulate clearly (**high CER** in EN) |
| `dialog-magelang-08` | Mbah Kedu · smile · — | 13,8 / 10,2 | Bijak; pisahkan jelas "Pendapat boleh berbeda, tapi fakta bisa dibuktikan" | Wise; clearly separate "Opinions may differ, but facts can be proven" |
| `dialog-magelang-09` | Mbah Kedu · weak · dim | 12,0 / 11,0 | Lemah, erangan pelan "Uhh…", napas berat, maaf lirih | Weak, a soft groan "Ohh…", heavy breath, a quiet apology |
| `dialog-magelang-10` | Jaka · afraid · shake | 5,2 / 4,0 | Panik dan khawatir; cepat, nada naik | Panicked and worried; fast, rising pitch |
| `dialog-magelang-11` | Mbah Kedu · weak · — | 15,9 / 12,2 | Menenangkan meski lemah; jeda dan napas; akhir penuh harap | Reassuring though weak; pauses and breaths; a hopeful ending |
| `dialog-magelang-12` | Jaka · determined · — | 7,7 / 4,2 | Sigap dan penuh sayang; mantap | Quick to help and caring; steady |
| `dialog-magelang-13` | Mbah Kedu · smile · — | 7,3 / 4,2 | Bangga dan haru, pelan | Proud and moved, slow |
| `dialog-magelang-14` | Jaka · happy · — | 6,4 / 4,7 | Ceria berterima kasih; "Baca dengan teliti dulu, baru bertindak!" berirama seperti semboyan | Cheerful and grateful; "Read carefully first, then act!" rhythmic like a motto |
| `dialog-magelang-15` | Mbah Kedu · smile · glow | 11,1 / 9,2 | Menasihati; tiga pesan dibaca berjeda | Advising; the three pieces of advice with pauses |
| `dialog-magelang-16` | Jaka · determined · fog-lift | 7,8 / 5,8 | Berangkat penuh tekad, lantang | Setting off with determination, projected |

**Wonosobo: *Pesan Berantai di Atas Awan***

| Kode | Tokoh · pose · efek | Durasi | Arahan (ID) | Direction (EN) |
|---|---|---|---|---|
| `dialog-wonosobo-01` | Jaka · afraid · fog | 6,6 / 5,4 | Menggigil kedinginan ("Brrr…" bergetar), setengah takut | Shivering with cold ("Brrr…" trembling), half scared |
| `dialog-wonosobo-02` | Mbah Kedu · worried · — | 11,1 / 8,9 | Serius dan rendah; tekankan "jantung Kabut Lupa" | Serious and low; stress "the heart of the Mist of Forgetting" |
| `dialog-wonosobo-03` | Jaka · afraid · shake | 7,8 / 6,2 | Panik mendengar kentongan; cepat, nada naik | Alarmed by the kentongan; fast, rising pitch |
| `dialog-wonosobo-04` | Mbah Kedu · worried · — | 15,0 / 10,6 | Membacakan pesan berantai dengan nada panik yang dikutip, lalu kembali tenang dan kritis: "Tak ada nama pengirim…" | Read the chain message in a quoted, panicky tone, then return calm and critical: "There is no sender's name…" |
| `dialog-wonosobo-05` | Jaka · afraid · — | 7,8 / 5,3 | Khawatir sungguh-sungguh, bertanya serius | Genuinely worried, asking seriously |
| `dialog-wonosobo-06` | Mbah Kedu · idle · — | 12,7 / 10,0 | Menghargai pertanyaan; tenang dan mantap; kalimat terakhir sebagai pelajaran | Valuing the question; calm and steady; the last sentence as a lesson |
| `dialog-wonosobo-07` | Mbah Kedu · weak · dim | 11,9 / 8,2 | Sangat lemah, terengah; jeda antarbagian | Very weak, out of breath; pauses between phrases |
| `dialog-wonosobo-08` | Jaka · sad · — | 8,6 / 5,2 | Menolak dengan sedih, hampir menangis tetapi tidak berlebihan | Refusing sadly, close to tears but not overdone |
| `dialog-wonosobo-09` | Mbah Kedu · smile · — | 10,5 / 6,6 | Menenangkan dengan lembut; janji menunggu | Gently reassuring; a promise to wait |
| `dialog-wonosobo-10` | Jaka · afraid · — | 2,1 / 1,9 | Pendek, lirih, jujur takut, tetapi jelas (**CER tertinggi** di kedua bahasa) | Short, soft, honestly afraid, but clear (**highest CER** in both languages) |
| `dialog-wonosobo-11` | Mbah Kedu · smile · glow | 9,3 / 7,1 | Menguatkan; hangat, pelan, yakin | Encouraging; warm, slow, certain |
| `dialog-wonosobo-12` | Mbah Kedu · idle · — | 14,1 / 10,3 | Nasihat tiga bagian, berirama seperti pepatah | Three-part advice, rhythmic like a proverb |
| `dialog-wonosobo-13` | Jaka · idle · — | 9,6 / 7,2 | Mengulang pelan seperti menghafal, jeda pada tiap "…", lalu yakin | Repeating slowly as if memorising, pausing at each "…", then confident |
| `dialog-wonosobo-14` | Jaka · determined · — | 10,2 / 5,4 | Dewasa dan tegas; kalimat terakhir mantap | Mature and firm; the last sentence steady |
| `dialog-wonosobo-15` | Mbah Kedu · smile · — | 8,8 / 6,8 | Bangga; "Pergilah, Le" lembut melepas | Proud; "Go, my boy" a gentle send-off |
| `dialog-wonosobo-16` | Jaka · determined · fog-lift | 7,9 / 6,7 | Janji penuh semangat, lantang | A spirited promise, projected |

**Wilayah tuntas (`level_done`)**

| Kode | Tokoh · pose · efek | Durasi | Arahan (ID) | Direction (EN) |
|---|---|---|---|---|
| `tuntas-temanggung-01` | Mbah Kedu · smile · fog-lift | 10,8 / 8,3 | Lega dan gembira; "Lihat, Le!" berseri | Relieved and happy; "Look, my boy!" beaming |
| `tuntas-temanggung-02` | Jaka · happy · glow | 5,9 / 4,3 | Girang dan hangat | Delighted and warm |
| `tuntas-temanggung-03` | Mbah Kedu · smile · — | 8,7 / 6,9 | Memuji tulus; ajakan membaca ramah | Sincere praise; a friendly invitation to read |
| `tuntas-temanggung-04` | Jaka · determined · — | 4,0 / 3,6 | Singkat dan bersemangat | Short and eager |
| `tuntas-magelang-01` | Mbah Kedu · smile · fog-lift | 11,6 / 8,4 | Lega; "Dengar, Le…" lembut, lalu gembira | Relieved; "Listen, my boy…" soft, then joyful |
| `tuntas-magelang-02` | Jaka · happy · — | 4,9 / 3,2 | Terkejut gembira; artikulasi jelas (**CER tinggi** ID) | Happily surprised; articulate clearly (**high CER** in ID) |
| `tuntas-magelang-03` | Mbah Kedu · smile · glow | 9,1 / 6,6 | Bertenaga kembali, hangat | Strength returning, warm |
| `tuntas-magelang-04` | Jaka · determined · — | 5,1 / 3,9 | Bersemangat; seruan "Wonosobo, kami datang!" | Eager; the call "Wonosobo, here we come!" |
| `tuntas-wonosobo-01` | Jaka · happy · fog-lift | 11,2 / 8,0 | Sangat gembira dan bangga; laporannya tetap jelas | Overjoyed and proud; the report still clear |
| `tuntas-wonosobo-02` | Mbah Kedu · smile · glow | 11,5 / 7,3 | Haru dan bangga, pelan | Moved and proud, slow |
| `tuntas-wonosobo-03` | Jaka · happy · — | 4,6 / 3,6 | Takjub, sedikit tak percaya, lalu bahagia | Amazed, a little disbelieving, then happy |
| `tuntas-wonosobo-04` | Mbah Kedu · smile · — | 9,5 / 6,6 | Hangat mengajak pulang; menutup babak | Warmly inviting home; closing the chapter |

**Penutup (`ending`)**

| Kode | Tokoh · pose · efek | Durasi | Arahan (ID) | Direction (EN) |
|---|---|---|---|---|
| `penutup-01` | Narator · — · glow | 13,7 / 10,1 | Megah dan hangat, klimaks; perlambat pada "cahaya keemasan memancar" | Grand and warm, the climax; slow down on "a golden light poured out" |
| `penutup-02` | Narator · — · fog-lift | 15,7 / 13,9 | Lega dan cerah; tiga pemulihan berirama | Relieved and bright; the three recoveries rhythmic |
| `penutup-03` | Mbah Kedu · smile · glow | 13,8 / 9,5 | Segar dan bertenaga (kontras dengan baris `weak` sebelumnya); pesan akhir bermakna | Fresh and strong (a contrast with the earlier weak lines); a meaningful closing message |
| `penutup-04` | Jaka · bow · — | 12,2 / 8,3 | Khidmat, tulus, tenang: sebuah janji | Solemn, sincere, calm: a promise |
| `penutup-05` | Mbah Kedu · smile · — | 13,2 / 9,9 | Upacara pelantikan yang hangat; "Kamu juga, penjelajah" ditujukan ke pemain; akhir menggugah | A warm ceremony; "And so are you, explorer" addressed to the player; an uplifting end |

**Petunjuk arena `cari` (`hunt_clue`)**

- Semua dibacakan Mbah Kedu, tanpa pose.
- Arahan umum: jelas, ramah, sedikit lebih lambat; tekankan nama benda yang dicari; nada menantang yang menyenangkan. EN: *clear, friendly, slightly slower; stress the object's name; a playful, inviting challenge.*
- Teksnya berasal dari bank soal (`prompt` butir `find_object` target). Teks terkini ada di daftar rekaman XLSX **Konten → Narasi**, karena admin dapat menyuntingnya.

| Kode | Durasi | Kata yang ditekankan (ID / EN) |
|---|---|---|
| `petunjuk-tmg-4-01` | 5,7 / 4,5 | daun tembakau / tobacco leaf |
| `petunjuk-tmg-4-02` | 4,4 / 4,2 | biji kopi / coffee beans |
| `petunjuk-tmg-4-03` | 5,4 / 4,1 | kuda-kudaan anyaman bambu / woven bamboo horse |
| `petunjuk-tmg-4-04` | 7,8 / 6,8 | ikan uceng / uceng fish |
| `petunjuk-tmg-4-05` | 5,0 / 4,5 | bambu runcing / sharpened bamboo spear |
| `petunjuk-tmg-4-06` | 5,7 / 4,7 | arca lembu Nandi / statue of Nandi the bull |
| `petunjuk-tmg-4-07` | 5,5 / 4,8 | kupat tahu / kupat tahu |
| `petunjuk-tmg-4-08` | 5,7 / 4,3 | rigen / rigen, /ˈriːɡɛn/ (**CER tinggi** EN) |

Nomor `NN` mengikuti urutan butir target aktif (`NarrationImporter`). Menonaktifkan atau menyisipkan butir target menggeser nomor. Halaman membangkitkan daftar ini dari bank soal dengan aturan yang sama.

### 14.7 Mengganti rekaman bawaan

1. Rekaman baru memakai nama berkas yang sama.
2. Pasang dengan salah satu cara:
   - timpa berkas di `narasi/{id|en}/`, commit, deploy, lalu `php spark gelita:narration:import`;
   - unggah di **Konten → Narasi → Unggah narasi**.
3. Rekaman yang isinya berubah kembali ke **draft**. Dengarkan, lalu **Setujui semua narasi draft**.
4. Pencatatan TTS:
   - **Suara manusia** otomatis tercatat sebagai rekaman sendiri, karena sha256-nya tidak ada di `produksi.json`.
   - **TTS dari mesin lain**: tambahkan `sha256` dan `voice_profile` (mesin, suara, pengolahan) ke `public/assets/audio/narasi/produksi.json` sebelum impor, agar tercatat `production_method = tts`. Data penelitian membedakan keduanya.

## 15. Alur produksi, pemasangan, dan pencatatan

### 15.1 Gambar

1. Buka kartu di katalog. Baca spesifikasi, zona aman, dan daftar periksa budaya.
2. Salin prompt dan negatif, lalu pilih alat (§10.3).
   - Pakai referensi gaya dan lembar tokoh yang sudah disetujui.
   - Buat ≥ 4 kandidat per butir, dengan seed dicatat.
3. Pilih satu kandidat, lalu sunting manual:
   - hapus teks palsu;
   - rapikan zona pin, objek, dan rak;
   - perbaiki tangan dan tepi transparansi.
4. Olah ke ukuran tepat (§10.5). Tumpangkan templat §4.5.
5. Jalankan daftar periksa §10.6. Guru atau budayawan memeriksa isi budaya dan fakta.
6. Pasang:
   - **Slot resmi** (`ui.*`, `bg.*`, `char.*`, `map.*`, `reward.*`): commit ke path bawaan di `public/assets/…`, deploy, lalu `php spark gelita:media:scan`. Atau unggah di **Gambar & suara** (`/admin/media`) dengan kode berkas yang sama.
   - **Konten** (`challenge.*`, `library.*`, `passage.*`): unggah lewat editor node, butir, atau Pustaka, atau halaman Media dengan kode berkas persis.
   - **Ikon aplikasi:** timpa berkas di `public/assets/app/` dan `public/favicon.ico`, commit, deploy.
7. Periksa **Media → Kelengkapan aset**. Mainkan layarnya di 1366 × 768, 1024 × 768, dan 844 × 390.
8. Catat di lembar produksi (§15.4).

### 15.2 Musik dan efek suara

1. Buat dari prompt atau brief (§13), bangkitkan ulang dengan `tools/audio`, atau rekam pengrawit.
2. Master sesuai target §13.1: kenyaringan, puncak sejati, loop mulus, MP3 stereo 128 kbps dengan Xing.
3. Timpa berkas bernama sama di `public/assets/audio/{music,sfx}/`, commit, deploy. Tidak lewat panel.
4. Uji di permainan setelah ketukan pertama. Narasi harus tetap jelas di atas musik.
5. Catat lisensi dan izin.

### 15.3 Narasi

1. Unduh **daftar rekaman XLSX** dari **Konten → Narasi**: teks terkini dari database, termasuk suntingan admin. Atau pakai kartu narasi di katalog (teks repositori).
2. Rekam atau bangkitkan per baris dengan prompt §14.4. Satu berkas satu baris.
3. Olah sesuai §14.1: potong, hening 0,3 detik, kenyaringan, MP3 mono 96 kbps dengan Xing.
4. Beri nama `{kode}.mp3`, lalu pasang dan setujui (§14.7).
5. Bila TTS, perbarui `produksi.json` (§14.7).
6. Catat di lembar produksi.

### 15.4 Lembar produksi `docs/katalog-aset/produksi.csv`

Satu baris per versi aset. Pembangun membacanya untuk menampilkan status di kartu: *belum*, *draf*, *disetujui*, atau *terpasang*. Butir berstatus *terpasang* tanpa `pemeriksa_budaya` dilaporkan sebagai peringatan.

| Kolom | Isi |
|---|---|
| `kode` | kode berkas / kode baris narasi / nama berkas bunyi |
| `versi` | 1, 2, … |
| `jenis` | gambar, ikon, musik, efek, narasi |
| `pembuat` | nama orang atau tim |
| `alat` | mis. Midjourney, SDXL, Flux, gpt-image, Krita, rekaman studio, `tools/audio` |
| `model_versi` | versi model atau suara TTS |
| `prompt_final` | prompt persis yang dipakai, setelah suntingan |
| `negatif` | prompt negatif yang dipakai |
| `seed` | seed atau nomor kandidat |
| `tanggal` | tanggal dibuat (YYYY-MM-DD) |
| `suntingan_manual` | ringkasan suntingan tangan |
| `lisensi_kredit` | lisensi keluaran alat, atau kredit Commons |
| `pemeriksa_budaya` | nama guru/budayawan yang memeriksa |
| `tanggal_periksa` | YYYY-MM-DD |
| `status` | draf, disetujui, terpasang |
| `catatan` | bebas |
