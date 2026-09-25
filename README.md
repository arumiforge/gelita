# GELITA

**GELITA** (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah game edukasi berbasis web untuk siswa SD/SMP sekaligus instrumen penelitian. Pemain berperan sebagai Jaka, dibimbing Mbah Kedu, menjelajahi tiga wilayah eks-Karesidenan Kedu — **Temanggung → Magelang → Wonosobo**, masing-masing lima tantangan — sambil belajar literasi membaca, budaya lokal, dan literasi keamanan digital. Setiap jawaban, perubahan jawaban, petunjuk, dan pemutaran audio dicatat sebagai data penelitian.

Aplikasi dibangun dengan **CodeIgniter 4.7** (PHP 8.2+, MySQL 8 / MariaDB 10.6+), dirender server dengan HTML5 + CSS3; JavaScript (ES modules, tanpa framework) hanya menambah perilaku. Area game dwibahasa (ID/EN), panel admin berbahasa Indonesia.

## Status pengembangan

**Kedelapan tahap selesai.** Aplikasi siap dipasang di server production mengikuti [`docs/08_DEPLOYMENT.md`](docs/08_DEPLOYMENT.md).

| Tahap | Isi | Status |
|---:|---|---|
| 1 | Database: 30 tabel domain + `ci_sessions`, 36 migration, 9 seeder | selesai |
| 2 | Fondasi: konfigurasi, filter, helper, berkas bahasa, `PasswordPolicy`, kerangka command | selesai |
| 3 | Model (30), Entity (12), Service (8) — skor, sesi, tantangan, event, analitik, impor bank soal | selesai |
| 4 | Route, controller, autentikasi siswa & staf, otorisasi berlapis tiga, API JSON seragam | selesai |
| 5 | View, UI, CSS: 3 layout, 20 komponen, 17 halaman game + 5 arena, 34 view admin, 6 berkas CSS | selesai |
| 6 | Perilaku JavaScript: lima mesin arena, antrean event offline, narasi + telemetry audio, chart ECharts, editor konten terpandu | selesai |
| 7 | Integrasi fitur: `ExportService` (XLSX streaming), `ReportService` (PDF), `RetentionService`, lima command `gelita:*` | selesai |
| 8 | Deployment & operasional: skrip deploy/backup/retensi untuk Linux (Bash) dan Windows + Laragon (PowerShell), konfigurasi Nginx, mode pemeliharaan | selesai |

Kelima arena kini dapat dimainkan penuh di browser — Periksa, petunjuk, keluar berkonfirmasi, narasi audio, dan lentera yang bertambah dari respons server — dengan seluruh event gameplay dan telemetry audio tercatat, termasuk saat koneksi sempat putus. Panel admin menggambar seluruh chart dengan ECharts dari `/api/admin/*`, dan filter mengubah isi halaman tanpa memuat ulang.

Yang sudah berfungsi penuh lewat HTTP: persetujuan dan registrasi dengan kata sandi kuat (termasuk dua metrik literasi keamanan digital), masuk/keluar, ganti sandi dan reset sandi oleh guru, ganti sandi sendiri untuk staf (`/admin/akun/sandi`, wajib setelah reset atau pembuatan akun oleh admin), peta Kedu dan peta wilayah dengan kunci berurutan, Kenali wilayah, dialog pembuka wilayah, kelima arena beserta seluruh API penilaiannya, layar selesai dan riwayat hasil, adegan wilayah tuntas dan penutup, Pustaka Kedu, profil, Balai Refleksi, pergantian bahasa tanpa kehilangan progres, serta seluruh halaman panel admin (dasbor, peserta, sesi, analitik, masukan, konten, impor bank soal, media & audio, studi & rilis, tata kelola, akun staf).

### Perombakan alur sinematik (Tahap 1–5) — ringkasan

Alur permainan kini bercerita dari awal sampai akhir, dengan satu naskah dwibahasa ([`docs/naskah-cerita.md`](docs/naskah-cerita.md), 88 baris) sebagai sumber teks, pose, efek, dan nama berkas rekaman:

| Tahap | Isi |
|---:|---|
| 1 | Halaman awal satu tombol **Mulai** → `/mulai` → daftar/masuk; cerita pembuka **wajib** bagi pemain baru (`intro_seen_at`); pemain lama memilih di `/gerbang` |
| 2 | Cerita pembuka sinematik dengan kartu "Ketuk untuk mulai", audio `autoplay`, tirai "Membuka Peta Kedu", narasi Jaka di peta; naskah di basis data (`gelita:story:update`) |
| 3 | **Kenali wilayah**, tirai wilayah dan tantangan, dialog wilayah dramatis, `/tuntas/{code}`, `/penutup` |
| 4 | Pustaka per wilayah (terbuka setelah wilayah tuntas), thumbnail video dari server |
| 5 | Impor rekaman narasi otomatis, persetujuan massal, halaman **Narasi**, **Kelengkapan aset**, dan audit alur ujung ke ujung |

Alurnya: halaman awal → `/mulai` → daftar → cerita pembuka → peta (tirai + narasi Jaka) → Kenali wilayah → tirai wilayah → dialog pembuka → peta wilayah → kartu misi (tirai tantangan) → tantangan → selesai → wilayah tuntas → Pustaka → wilayah berikutnya → … → penutup → Balai Refleksi.

### Pembaruan narasi & kelengkapan aset (Tahap 5)

- **Rekaman narasi dipasang otomatis.** Nama berkas = kode baris naskah (`intro-01.mp3`, `kenal-magelang-03.mp3`, `dialog-wonosobo-16.mp3`, …). `App\Libraries\NarrationImporter` mencocokkannya ke baris `dialogues` (kode wilayah dari tabel `levels`), membuat aset `audio.narasi.{id|en}.{kode}` dan `audio_assets` berstatus **draft** dengan transkrip = teks baris itu, lalu menautkannya. Dua sumber: folder `public/assets/audio/narasi/{id|en}/` (`php spark gelita:narration:import [--locale=id|en] [--dry-run]`) atau unggahan banyak berkas di panel. Rekaman yang berubah kembali ke draft; berkas yang sama persis dilewati sehingga persetujuan tidak hilang. Laporannya memuat nama tidak dikenal beserta saran nama terdekat dan baris yang belum punya rekaman.
- **Panel admin baru** (khusus admin): **Konten → Narasi** (`/admin/konten/narasi`: 88 baris per konteks dan wilayah, status audio ID/EN dengan pemutar kecil, tautan Sunting, kemajuan per bahasa, **Unduh daftar rekaman** XLSX untuk pengisi suara), **Unggah narasi** (batas unggahan PHP dijelaskan), tombol **Setujui semua narasi draft** per bahasa (hanya narasi naskah; audit `audio_approve_bulk`), dan **Media → Kelengkapan aset** (`/admin/media/kelengkapan`).
- **Audit alur Tahap 1–4** di MariaDB + Chromium: satu cacat diperbaiki (pemain baru dapat melewati cerita pembuka wajib lewat URL `/dialog/{code}` atau `/wilayah/{code}`); telemetry `dialogue_advanced` per konteks, `autoplay`, `started_at` setelah tirai tantangan, dan `library_opened` sudah benar ([`docs/07` → *Catatan Implementasi Tahap 5*](docs/07_FEATURE_INTEGRATION.md#catatan-implementasi-tahap-5-narasi--audit-alur)).
- `MediaStore` kini membaca durasi MP3 dan mengenali audio dari isi berkas. Tidak ada migration baru.

**Urutan pemasangan di server yang sudah berjalan** (sekali, setelah menarik kode terbaru; semua aman diulang):

```bash
php spark migrate                          # 003600 intro_seen_at, 003700 pose & efek dialog
php spark gelita:story:update              # naskah 88 baris ke tabel dialogues (--dry-run untuk melihat dulu)
php spark gelita:library:thumbnails        # poster video Pustaka (butuh HTTPS keluar)
php spark gelita:narration:import          # rekaman di public/assets/audio/narasi/{id,en}/ → audio draft
```

Lalu buka **Konten → Narasi**, dengarkan, dan setujui per bahasa. Rincian di [`docs/08` → *Memperbarui server yang sudah berjalan*](docs/08_DEPLOYMENT.md#memperbarui-server-yang-sudah-berjalan-ke-alur-sinematik-tahap-15). Tambahkan `max_file_uploads = 100` di `php.ini` agar satu unggahan narasi dapat memuat lebih dari 20 berkas.

**Aset yang masih perlu disiapkan** (permainan berjalan tanpanya dengan pengganti; daftar hidupnya ada di **Media → Kelengkapan aset**):

| Aset | Slot / lokasi | Ukuran |
|---|---|---|
| Logo landscape halaman awal | `ui.logo-hero` | 1600 × 600 px |
| Tombol Mulai bergambar ID & EN | `ui.btn-start`, `ui.btn-start.en` | 720 × 240 px |
| Logo panel & halaman masuk | `ui.logo` | bebas |
| Peta Karesidenan Kedu | `map.kedu` | bebas (mendatar) |
| Latar layar umum | `bg.loading`, `bg.welcome`, `bg.auth`, `bg.intro`, `bg.map`, `bg.reflection` | 1920 × 1080 px |
| Frame pose tokoh (3 frame per pose, PNG transparan) | Jaka: `idle`, `happy`, `bow`, `sad`, `afraid`, `determined`; Mbah Kedu: `idle`, `smile`, `worried`, `weak` → `char.jaka.{pose}.{1–3}`, `char.kedu.{pose}.{1–3}` | 700 × 900 px |
| Latar, peta, dan lencana tiap wilayah | `bg.{wilayah}.region`, `map.region.{wilayah}`, `reward.badge.{wilayah}` | 1920 × 1080, 1400 × 900, 320 × 320 px |
| Musik | `public/assets/audio/music/{map,region,challenge}.mp3` | MP3, berulang |
| Efek suara | `public/assets/audio/sfx/{click,correct,wrong,lock}.mp3` | MP3 pendek |
| Poster video Pustaka yang kosong | editor Pustaka, atau `gelita:library:thumbnails` untuk video tautan | 960 × 640 px |
| Rekaman narasi | 88 baris × 2 bahasa, [`docs/naskah-cerita.md`](docs/naskah-cerita.md) | MP3 mono 64–96 kbps |
| Gambar bank soal & Pustaka | [`docs/bank-soal/README.md`](docs/bank-soal/README.md) | per slot |

### Pembaruan cerita wilayah: Kenali wilayah, tirai, dialog dramatis, tuntas, penutup (Tahap 3)

- **Kenali wilayah.** Setiap pin dan kartu wilayah di Peta Kedu punya tombol lentera "Kenali {wilayah}" (juga untuk wilayah terkunci) yang membuka overlay layar penuh "Mengenal {wilayah}": 4 slide narasi Mbah Kedu dengan pose, efek, dan audio, langsung diputar karena ketukan tombol itu interaksinya. Slide akhir: "Masuk ke {wilayah}" bila terbuka, selain itu "Tutup". Lencana **"Belum didengar"** berdenyut sampai narasinya pernah didengar sampai slide terakhir; statusnya dihitung server dari event `dialogue_advanced` per peserta. Tanpa JavaScript overlay adalah target `#kenal-{code}`.
- **Tirai wilayah** "Menuju {wilayah}…" (±1,8 detik): jejak kaki kiri–kanan melintasi layar, tagline wilayah ("Di Antara Dua Gunung", "Tanah Candi Agung", "Negeri di Atas Awan"), chip tingkat kesulitan, dan pramuat aset halaman tujuan. Dipasang di pin, kartu wilayah, overlay Kenali, dan setiap tombol "Lanjut ke {wilayah berikutnya}".
- **Dialog wilayah dramatis.** Kartu bab "Bab {n} · {wilayah}" dengan tagline dan jejak kaki lanjutan, lalu 15–16 baris dengan audio otomatis, mesin ketik, efek, dan gambar tokoh yang berganti sesuai pose baris (cadangan pose `idle`, lalu monogram). Nav "Lewati" dan gerbang dialog tidak berubah.
- **Wilayah tuntas** — `/tuntas/{code}` ("Serpihan {wilayah} kembali!"): tantangan yang menuntaskan wilayah kini berlanjut ke adegan ini; slide akhirnya "Baca Pustaka {wilayah}" dan "Lanjut ke {wilayah berikutnya}", atau "Lanjut" ke **penutup** `/penutup` di wilayah terakhir. Penutup sinematik (5 slide) berakhir di Balai Refleksi dan dapat ditonton ulang lewat "Tonton penutup" di peta.
- **Tirai tantangan** (≤1,5 detik, ketukan melewatinya) diputar di kartu misi **sebelum** pindah ke `/tantangan`, dengan visual per jenis tantangan. Attempt baru dibuka saat halaman tantangan dirender, jadi tirai tidak menambah waktu attempt penelitian.
- **Pengiriman event lebih andal** saat berpindah halaman (`core/events.js`): batch yang sedang dikirim tidak lagi hilang bila halaman keburu berpindah.
- **Catatan peneliti:** `dialogue_advanced` dialog pembuka wilayah kini ber-`context` `level_open` (sebelumnya `region`); konteks baru `region_intro`, `level_done`, `ending` ([`docs/07` → *Catatan Implementasi Tahap 3*](docs/07_FEATURE_INTEGRATION.md#catatan-implementasi-tahap-3-alur-cerita)).
- Tidak ada migration baru. Rincian di [`docs/05_VIEW_UI.md`](docs/05_VIEW_UI.md) (*§Peta Kedu*, *§Dialog*, *§Wilayah tuntas*, *§Penutup*, *§Tirai*) dan [`docs/06_JAVASCRIPT.md`](docs/06_JAVASCRIPT.md).

### Pembaruan Pustaka per wilayah (Tahap 4)

- **Pustaka terkunci per wilayah.** Pustaka sebuah wilayah baru terbuka setelah kelima tantangannya selesai pada sesi pemain, apa pun unlock mode studinya. Sebelum itu `/pustaka/{code}` menampilkan halaman terkunci berisi progres x/5 dan tombol "Lanjutkan tantangan" (tetap terbaca tanpa JavaScript), dan `/api/library/{levelId}` membalas `LIBRARY_LOCKED`. **Catatan peneliti:** event `library_opened` kini hanya muncul setelah wilayahnya tuntas ([`docs/07` → FITUR 8](docs/07_FEATURE_INTEGRATION.md#fitur-8-pustaka-kedu)).
- **Nama sesuai tempat.** Di peta, tombol **Pustaka Kedu** membuka rak `/pustaka`: satu kartu per wilayah (terbuka → Baca, terkunci → progres + penjelasan, belum terbuka → keterangan). Di dalam wilayah namanya **Pustaka {wilayah}**, dan selama masih terkunci, tombolnya membuka modal penjelasan.
- **Momen "Pustaka {wilayah} terbuka!"** di layar selesai, hanya pada tantangan yang menuntaskan wilayah.
- **Buku yang lebih rapi.** Kredit pindah ke ikon ⓘ di pojok gambar (`<details>`, berjalan tanpa JS). Gambar dimuat dengan skeleton perkamen lalu memudar masuk, halaman berikutnya dipramuat, dan lightbox menampilkan pemutar tunggu. Video YouTube/Vimeo/Drive tampil sebagai facade berposter dengan tombol Putar besar.
- **Thumbnail video diunduh server** (`App\Libraries\VideoThumbnail`) saat Pustaka disimpan atau diimpor, lalu dipakai sebagai poster. Perangkat siswa tidak menghubungi domain luar sebelum Putar ditekan. Server butuh HTTPS keluar ke `i.ytimg.com`, `vimeo.com`, `i.vimeocdn.com`, dan `drive.google.com` ([`docs/08` → *Akses keluar server*](docs/08_DEPLOYMENT.md#akses-keluar-server-thumbnail-video-pustaka)). Untuk baris yang sudah ada: `php spark gelita:library:thumbnails [--force]`.

### Pembaruan cerita: naskah lengkap, cerita pembuka sinematik, tirai peta

- **Naskah lengkap di permainan.** Seluruh 88 baris [`docs/naskah-cerita.md`](docs/naskah-cerita.md) (cerita pembuka, narasi peta, kenali wilayah, dialog masuk wilayah, wilayah tuntas, penutup) kini satu sumber data: `app/Database/Seeds/data/story.php`, lengkap dengan tokoh, pose, efek, dan judul. Migration `003700` menambah `dialogues.pose` dan `dialogues.effect`.
- **Cerita pembuka sinematik.** Layar penuh dengan latar per slide (Ken Burns pelan), tokoh sesuai pose, kotak teks bergaya subtitle, dan efek layar. Kartu **"Ketuk untuk mulai"** membuka kunci audio (Safari iPad memblokir suara sampai halaman itu diketuk), lalu narasi diputar otomatis dengan efek mesin ketik dan maju sendiri setelah audio selesai (sakelar Otomatis). Tanpa audio yang disetujui, teks tetap tampil dan slide dilanjutkan manual.
- **Tirai "Membuka Peta Kedu".** Dari gerbang atau akhir cerita pembuka, peta dibuka dengan tirai yang memuat aset peta dengan progres nyata (2,5–8 detik); ketukannya memulai musik peta dan narasi Jaka di panduan peta. Kunjungan lain menampilkan narasi sebagai teks dengan tombol ▶. Tanpa JavaScript tirai tidak pernah menutupi halaman.
- **Telemetry:** putar otomatis dicatat sebagai `autoplay`, terpisah dari `play` (pemain menekan putar), dan ditampilkan terpisah di dasbor, profil peserta, dan laporan PDF.
- **Admin** menyunting keenam konteks naskah di `/admin/konten/dialog/{level}?konteks=…`, termasuk pose dan efek.
- Rincian di [`docs/05_VIEW_UI.md`](docs/05_VIEW_UI.md) (*§Tirai*, *§Pola ketuk-untuk-mulai*) dan [`docs/06_JAVASCRIPT.md`](docs/06_JAVASCRIPT.md) (`core/curtain.js`, `game/narrator.js`).

**Server yang sudah berjalan** perlu memindahkan naskah baru ke basis datanya, karena seeder tidak pernah menimpa baris yang sudah ada:

```bash
php spark migrate                       # 003700: kolom pose & efek
php spark gelita:story:update --dry-run # lihat dulu yang akan berubah
php spark gelita:story:update           # sisipkan baris baru, perbarui teks bawaan lama
```

Urutannya wajib `migrate` lalu `gelita:story:update` (perintah itu menulis kolom `pose`/`effect`). Baris yang teksnya masih teks seeder lama diperbarui; baris yang sudah disunting admin — teks, tokoh, pose, efek, atau judul — dilewati dan dilaporkan (tambahkan `--force` untuk menimpanya); baris di luar jumlah naskah dinonaktifkan, tidak dihapus. Audio dan latar yang sudah dipasang admin tidak pernah disentuh. Perintah ini aman dijalankan berulang.

### Pembaruan alur masuk: satu tombol Mulai, cerita pembuka wajib

- **Halaman awal hanya logo dan tombol Mulai.** Logo landscape dari slot `ui.logo-hero` (cadangan `ui.logo`, lalu judul teks), tombol dari gambar `ui.btn-start` beserta varian bahasa `ui.btn-start.en` (cadangan tombol CSS emas). Merek di HUD tidak diulang di halaman ini. Tautan panel guru dihapus: staf masuk lewat `/admin/login`.
- **`/mulai` memilah.** Belum login → "Saya baru" / "Sudah punya akun". Sudah login → `/gerbang`. Login dan ganti sandi kini berakhir di `/mulai` (tujuan tersimpan sebelum login tetap dihormati).
- **Cerita pembuka wajib bagi pemain baru.** Selama `participants.intro_seen_at` kosong (migration `003600`), `/gerbang` dan `/peta` mengarah ke `/intro`, dan tombol "Lewati" tidak ada. Slide terakhir menuju `/intro/selesai`, yang mengisi kolom itu dan mencatat event `intro_completed`. Peserta yang sudah punya progres saat migration dianggap sudah menonton.
- **Pemain lama memilih** di `/gerbang`: lihat cerita pembuka (dengan "Lewati") atau langsung ke peta. Jalan ke peta membawa flash `curtain=map` untuk tirai "Membuka Peta Kedu".
- Rincian di [`docs/05_VIEW_UI.md` → *Halaman Game*](docs/05_VIEW_UI.md#halaman-game) dan [`docs/04_CONTROLLER_ROUTE.md`](docs/04_CONTROLLER_ROUTE.md).

### Pembaruan orientasi layar: game hanya dimainkan mendatar

- **Aset cukup satu versi mendatar.** Peta, adegan cari, dan latar tidak perlu varian potret: kanvas mengunci rasio gambar, lalu pin dan objek diletakkan dalam persen.
- **Layar putar.** Ponsel dan tablet yang dipegang tegak melihat layar "Putar perangkatmu" di semua halaman game (`components/rotate-gate.php`), tanpa tombol lewati. Desktop/laptop dengan jendela sempit dan panel admin tidak terpengaruh. Rotasi tidak dapat dikunci dari web (iOS tidak mendukung, Android hanya dalam layar penuh), jadi layar ini juga menjelaskan cara menyalakan *Putar otomatis*.
- **Tata letak ponsel mendatar.** Tinggi ponsel mendatar hanya ±280–430px. HUD diringkas, navigasi bawah tidak lagi menutupi isi, kepala tantangan dan instruksi pindah ke kolom kiri, dan peta, adegan cari, serta papan puzzle diukur dari tinggi layar. Tampilan desktop, papan tulis, dan tablet mendatar tidak berubah.
- **Dapat dipasang di layar utama.** Manifest (`public/manifest.json`) mengunci posisi mendatar dan layar penuh untuk aplikasi terpasang di Android, sehingga ponsel mendapat tinggi penuh tanpa bilah alamat. Halaman awal menawarkan tombol **Pasang GELITA** (Android) atau petunjuk *Tambah ke Layar Utama* (iPhone/iPad). Service worker hanya menampilkan halaman offline, dan data penelitian tidak pernah di-cache. Ikon lentera di `public/assets/app/` masih sementara dan bisa ditimpa dengan ukuran yang sama.
- Aturan dan panduan asetnya ada di [`docs/05_VIEW_UI.md` → *Orientasi layar*](docs/05_VIEW_UI.md#orientasi-layar); langkah memasang di perangkat kelas ada di [`docs/08_DEPLOYMENT.md` → *Sebelum sesi kelas*](docs/08_DEPLOYMENT.md#sebelum-sesi-kelas).

### Pembaruan media, Pustaka Kedu, dan bank soal produksi

- **Unggah media langsung dari editor konten.** Gambar adegan & latar tantangan, gambar butir (keping puzzle, objek cari), gambar opsi, gambar bacaan, peta & latar & lencana wilayah, serta audio dialog dan narasi pembuka tantangan dipilih atau diunggah di formulir masing-masing (`App\Libraries\MediaStore`, komponen `media-field` / `audio-select`). Halaman Media menampilkan di mana setiap aset dipakai dan slot mana yang masih kosong (`App\Libraries\MediaUsage`).
- **Pustaka Kedu multi-media.** Setiap halaman boleh memuat banyak gambar/video (tabel `library_media`, migration `003500`): berkas unggahan atau tautan YouTube, Google Drive, Vimeo, Wikimedia Commons, dan berkas gambar/video langsung (`App\Libraries\MediaLink`). Isi halaman mendukung subjudul, daftar, kotak fakta, catatan sumber, tebal, dan miring (`rich_text()`).
- **Bank soal produksi siap impor**: [`docs/bank-soal/gelita-bank-soal-produksi.xlsx`](docs/bank-soal/gelita-bank-soal-produksi.xlsx) — 15 tantangan, 131 butir, 11 bacaan, 30 halaman Pustaka dwibahasa beserta 48 media dan rujukannya. Workbook dibangun dari data PHP yang dapat ditinjau; cara membangun ulang dan daftar gambar yang masih perlu diunggah ada di [`docs/bank-soal/README.md`](docs/bank-soal/README.md).
- **Templat impor berpanduan.** Workbook templat dan workbook produksi memuat sheet PETUNJUK dan KAMUS_KOLOM (arti setiap kolom dalam bahasa Indonesia), header berwarna menurut status wajib/bersyarat/opsional dengan catatan per kolom, serta dropdown untuk kolom berkode (`App\Libraries\BankWorkbookGuide`). asset_key media yang belum terdaftar dibuatkan slot kosong saat impor.
- Isi panel admin kini selebar layar.

### Tahap 8 — Deployment & operasional

Berkas operasional ada di [`deploy/`](deploy/); kode aplikasi tidak berubah. Langkah pemasangan, alasan tiap aturan, dan hasil uji ada di [`docs/08_DEPLOYMENT.md`](docs/08_DEPLOYMENT.md), terutama [*Catatan Implementasi Tahap 8*](docs/08_DEPLOYMENT.md#catatan-implementasi-tahap-8). Yang perlu diketahui:

- **Dua jalur setara.** Jalur L memakai `deploy/linux/` (Bash, Nginx + PHP-FPM, cron). Jalur W memakai `deploy/windows/` (PowerShell, Nginx Laragon + php-cgi, Task Scheduler).
- **Deploy satu perintah** (`deploy.sh` / `deploy.ps1`). Urutannya:
  1. menolak berjalan bila ada sesi kelas aktif dalam 10 menit terakhir;
  2. membuat backup;
  3. menyalakan mode pemeliharaan (Nginx menjawab 503 selama `writable/pemeliharaan.flag` ada);
  4. menarik kode dan dependency;
  5. menjalankan migration dengan akun `gelita_migrate` lewat `database_default_DSN`;
  6. menaikkan `gelita.assetVersion`;
  7. membuka situs kembali hanya bila `gelita:content:verify` lolos.
- **Backup** (`backup.sh` / `backup.ps1`) memuat database tanpa isi `ci_sessions`, aset unggahan, dan `.env`. Backup disimpan 30 hari lalu disalin ke lokasi kedua. Kegagalan salinan kedua terlihat lewat kode keluar 2.
- **Rollback: migration dulu, kode kemudian.** Setelah `git reset`, berkas migration rilis baru hilang, dan `migrate:rollback` gagal diam-diam dengan kode keluar 0.
- **Skrip `.ps1` hanya ASCII.** Windows PowerShell 5.1 membaca skrip tanpa BOM sebagai cp1252, sehingga tanda pisah `—` memutus string.

Semua skrip dijalankan di server uji (Ubuntu 24.04, MariaDB 10.11, Nginx 1.24, PHP-FPM 8.3, PowerShell 7.4) lewat jalur sukses, jalur gagal, dan penolakan saat ada kelas aktif. Yang masih butuh mesin Windows atau domain sungguhan tercatat di [*Masih belum teruji*](docs/08_DEPLOYMENT.md#masih-belum-teruji).

### Tahap 7 — Integrasi fitur

Ekspor, laporan, retensi, dan command kini berfungsi penuh. Rincian dan keputusannya ada di [`docs/07_FEATURE_INTEGRATION.md` → *Catatan Implementasi Tahap 7*](docs/07_FEATURE_INTEGRATION.md#catatan-implementasi-tahap-7). Yang perlu diketahui:

- **Ekspor XLSX** (`/admin/ekspor`): sepuluh sheet, ditulis bertahap oleh `App\Libraries\ExcelWriter` (XML SpreadsheetML + ZipArchive) sehingga 150.000 baris Raw Events hanya memakai ±18 MB memori. Berkas di `writable/exports/`, ber-SHA-256, kedaluwarsa setelah `gelita.exportRetentionDays`. Raw Events di atas `gelita.exportMaxRawEvents` (200.000) ditolak dengan permintaan mempersempit rentang.
- **Hak dibaca ulang di service.** `ExportService` mengambil role dan sekolah pemohon dari `staff_users`: guru selalu anonim, hanya sekolahnya, tanpa Raw Events dan tanpa kunci jawaban — apa pun isi formulirnya. Mode anonim tidak membuat kolom nama/nama pengguna/nama sekolah sama sekali; `school_ref` (`SCH-000012`) tetap ada untuk analisis per sekolah.
- **Laporan PDF** (mPDF): ringkasan studi dari halaman ekspor, dan laporan satu peserta dari `/admin/peserta/{id}`.
- **Penghapusan** (`/admin/tata-kelola`) dipindah ke `RetentionService`: cakupan peserta, sesi, atau studi (dipersempit fase & rentang tanggal); eksekusi dibatalkan bila jumlah baris berubah jauh sejak pratinjau.
- **Retensi** (`php spark gelita:retention:run`, cron harian): sesi menganggur → `paused`, attempt menggantung > 24 jam → `abandoned`, sesi `paused` > 30 hari → `abandoned`, berkas ekspor kedaluwarsa dibuang, dan data yang melewati `retention_days` **hanya** dibuatkan pratinjau penghapusan + peringatan di dasbor admin.
- **Command**: `gelita:content:verify`, `gelita:media:scan`, `gelita:score:recompute`, `gelita:bank:import`, `gelita:retention:run`, `gelita:staff:password`, `gelita:story:update`, `gelita:library:thumbnails`, `gelita:narration:import` — semuanya mengembalikan kode keluar 1 saat gagal sehingga dapat dipakai di skrip deploy.

### Tahap 6 — JavaScript

ES modules tanpa bundler di [`public/assets/js/`](public/assets/js/): lapisan inti (`core/`), perilaku halaman game (`game/`), lima mesin (`engines/`), dan panel admin (`admin/`). Rincian dan keputusannya ada di [`docs/06_JAVASCRIPT.md` → *Catatan Implementasi Tahap 6*](docs/06_JAVASCRIPT.md#catatan-implementasi-tahap-6). Yang perlu diketahui:

- **Client tidak pernah menilai.** Benar/salah, skor, bintang, serpihan, dan jumlah keping yang keliru datang dari respons server.
- **Antrean offline per sesi.** Event yang gagal terkirim disimpan di `localStorage` dengan penanda sesi buram (`sessionTag`, HMAC `game_session_id`) dan hanya dikirim ulang ke sesi yang sama — komputer kelas dipakai bergantian.
- **Kontrak server yang ditambahkan:** `elapsed_ms` di payload tantangan, `detail.pieces_correct` di respons `/check`, kode `CSRF_EXPIRED` / `INVALID_SESSION` saat token CSRF ditolak di API, kunci `chart` di API admin (`App\Libraries\ChartData`), dan `play_index` audio kini berarti "pemutaran ke-n".
- Teks yang ditulis JavaScript game ada di `app/Language/{id,en}/Js.php`.

### Audit pra-tahap 6

Sebelum tahap 6 dimulai, seluruh tahap 1–5 diaudit terhadap dokumen `docs/01`–`08`, dan alurnya ditelusuri ujung ke ujung di atas MariaDB 10.11 (registrasi → 15 node lintas lima engine → refleksi → reset sandi guru → seluruh halaman admin, 199 pemeriksaan). Cacat yang ditemukan dan diperbaiki:

- **Attempt `cari` tidak pernah dapat ditutup** — objek jebakan dihitung sebagai soal belum dijawab, sehingga node 4 Temanggung dan seluruh wilayah sesudahnya terkunci. Kini satu aturan (`ChallengeService::expectsAnswer()`) dipakai petunjuk, progres, dan syarat `/complete`.
- **Penilaian first-pass** — klik salah pertama di arena `cari` kini menutup first-pass petunjuk itu sebagai salah; `allow_retry = false` (arena `pilihan`) kini ditegakkan server sehingga jawaban kedua tidak menimpa yang pertama.
- **Kontrak data untuk tahap 6** — payload attempt memuat id petunjuk (`hints`); `/api/events` menerima `node_id`/`attempt_id`/`item_id`; `/api/audio-events` idempotent per `client_event_id`; waktu event klien tidak lagi kehilangan milidetik; linimasa diurutkan `occurred_at` lalu `sequence_no` dengan index `(session_id, occurred_at)` dari migration baru `003300`.
- **Keamanan** — Debug Toolbar di environment development menyimpan kata sandi mentah ke `writable/debugbar/`; `App\Filters\DebugToolbar` kini menyamarkan field kata sandi.
- Event `password_changed` tidak lagi hilang setelah reset guru; templat workbook bank soal lolos pratinjaunya sendiri; dokumentasi diselaraskan (mis. `unlock_mode = sequential | free`).

Rincian keputusan ada di dokumen tahap masing-masing; ringkasan UI di [`docs/05_VIEW_UI.md` → *Catatan Implementasi Tahap 5*](docs/05_VIEW_UI.md#catatan-implementasi-tahap-5).

### Keputusan penting

- **Server adalah sumber kebenaran.** Skor, bintang, status buka/kunci, dan serpihan dihitung server; kunci jawaban tidak pernah dikirim ke browser. Payload soal ditanam sebagai `<script type="application/json" id="challenge-data">`. Arena `cari` hanya mengirim token objek per attempt (HMAC dari `encryption.key`), sehingga objek jebakan tidak dapat dibedakan dari sumber halaman.
- **Wilayah yang baru terbuka selalu dibuka lewat dialog pembukanya**, juga bila URL di dalamnya diketik langsung; tanda "dialog sudah tampil" disimpan per sesi login.
- **Tanpa JavaScript tetap terbaca**: slide memakai `:target`, konfirmasi memakai `<details>`, setiap chart admin punya visual cadangan dari server.
- **Aset di-host sendiri**: ECharts 6.1.0, Howler.js 2.2.4, dan font Cinzel/Plus Jakarta Sans/IBM Plex Mono ada di repositori; tidak ada permintaan ke domain pihak ketiga. Selama gambar belum diunggah, view memakai pengganti (gradien, monogram). Satu-satunya pengecualian adalah media **tautan** di Pustaka Kedu: gambar Commons/Drive dimuat dari domain asalnya tanpa referrer, sedangkan pemutar YouTube/Vimeo/Drive baru dimuat setelah siswa menekan Putar. Media yang diunggah ke server tetap lokal.
- **Otorisasi berlapis tiga** (route → controller → service): guru hanya melihat sekolahnya, ekspor guru selalu anonim, dan halaman khusus admin membalas `404` untuk guru.
- **HEAD dilayani rute GET** dengan handler dan filter yang sama (`App\Libraries\HeadAsGetRouteCollection`), sehingga `curl -I` dan pemantau uptime tidak lagi mendapat 404, dan HEAD tidak pernah melewati filter login.

### Batas ruang lingkup saat ini

- **Narasi petunjuk arena `cari` belum bersuara**: payload `clues` belum membawa aset audio; teks petunjuk tampil dan diumumkan ke pembaca layar.
- **Ekspor dibangun sinkron** di request yang sama (batas 300 detik). Tahap 8 memutuskan tetap sinkron; ambang `gelita.exportMaxRawEvents` menjaga ukurannya. Alasannya ada di [`docs/08_DEPLOYMENT.md` → *Keputusan yang tercatat*](docs/08_DEPLOYMENT.md#keputusan-yang-tercatat).
- **Tugas terjadwal dipasang per server.** Baris cron (Linux) dan perintah pendaftaran Task Scheduler (Windows) untuk backup dan `gelita:retention:run` ada di [`docs/08_DEPLOYMENT.md` → *Tugas Terjadwal*](docs/08_DEPLOYMENT.md#tugas-terjadwal). Di mesin pengembangan, retensi dijalankan dari tombol di `/admin/tata-kelola`.
- `App\Libraries\HashedIpSessionHandler` **belum dipasang**; alasannya di bagian *Sesi dan CSRF* di bawah.

## Prasyarat lokal

- PHP 8.2 atau lebih baru (PHP 8.3 disarankan)
- Composer 2.6 atau lebih baru
- MySQL 8.0+ atau MariaDB 10.6+
- Web server lokal, atau server bawaan CodeIgniter untuk pengembangan
- Ekstensi PHP: `intl`, `mbstring`, `json`, `mysqli`/`mysqlnd`, `curl`, `gd`, `zip`, `fileinfo`, `openssl`, `iconv`, `dom`, `xml`, `xmlwriter`, dan `simplexml`

Di Windows, Laragon (PHP 8.3 + MySQL) memenuhi semua prasyarat. Pengaturan ekstensinya ada di [`docs/08_DEPLOYMENT.md` → Jalur W](docs/08_DEPLOYMENT.md#ekstensi-php-wajib). Perintah di bawah ditulis untuk Bash; padanan PowerShell diberikan bila sintaksnya berbeda.

## Menjalankan secara lokal

1. Pasang dependensi dari lockfile proyek:

   ```bash
   composer install
   ```

2. Salin templat environment, lalu sesuaikan isinya:

   ```bash
   cp env .env                 # PowerShell: Copy-Item env .env
   ```

   Berkas `env` adalah templat yang dilacak Git; `.env` hasil salinannya **tidak pernah** di-commit. Yang wajib diisi sebelum aplikasi dijalankan:

   | Kunci | Keterangan |
   |---|---|
   | `app.baseURL` | URL aplikasi lokal Anda |
   | `database.default.*` | kredensial database |
   | `encryption.key` | bangkitkan dengan `php spark key:generate`; wajib — token objek arena `cari` diturunkan darinya |
   | `gelita.ipSalt` | string acak panjang; garam ini yang membuat hash IP tidak dapat dibalik. Jangan dibiarkan kosong |

3. Buat database bila belum ada, jalankan migration, lalu isi data awal:

   ```bash
   mysql -u root -p -e "CREATE DATABASE gelita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   php spark migrate
   GELITA_ADMIN_PASSWORD='SandiKuatAnda123!' php spark db:seed DatabaseSeeder
   ```

   Di PowerShell, sintaks `VAR='…' perintah` tidak ada:

   ```powershell
   $env:GELITA_ADMIN_PASSWORD = 'SandiKuatAnda123!'; php spark db:seed DatabaseSeeder; Remove-Item Env:\GELITA_ADMIN_PASSWORD
   ```

   Tabel sesi `ci_sessions` sudah termasuk migration proyek — jangan menjalankan `php spark session:migration`. Pada `CI_ENVIRONMENT = development`, `DatabaseSeeder` juga menjalankan `SampleItemSeeder` (2 butir contoh per node) agar kelima belas tantangan dapat dimainkan sebelum bank soal diimpor.

4. Jalankan server pengembangan:

   ```bash
   php spark serve
   ```

   Akun staf awal: `admin` dengan sandi dari `GELITA_ADMIN_PASSWORD`, di `/admin/login`. Siswa mendaftar sendiri lewat `/mulai`.

## Migration

Migration dijalankan berurutan dari `000100` sampai `003700` (38 berkas) dan menghasilkan 32 tabel: 30 tabel domain, `ci_sessions`, dan `migrations`. `php spark migrate:rollback -b 0` mengembalikan database ke kosong.

`003100`–`003300` adalah koreksi, `003400`, `003600`, dan `003700` menambah kolom baru, `003500` menambah tabel baru. Perubahan skema selalu datang sebagai migration baru; migration lama tidak diubah.

| Versi | Peran |
|---|---|
| `002100` | membuat tabel `challenge_attempts` dengan nama kolom awal `first_pass_rate` / `final_rate` |
| `003100` | koreksi kanonik: mengganti nama kedua kolom itu menjadi `first_pass_accuracy` / `final_accuracy` dan menambahkan `check_count`, `answer_change_count`, `audio_use_count` |
| `003200` | menegakkan `NOT NULL` + default pada kelima kolom di atas (Forge membuatnya nullable saat `ALTER`), termasuk mengisi baris lama yang masih `NULL` |
| `003300` | menambahkan index wajib `game_event_logs (session_id, occurred_at)` yang tidak dibuat `002400` |
| `003400` | menambahkan `staff_users.must_change_password`: sandi sementara dari admin (reset / akun baru) wajib diganti sebelum panel terbuka |
| `003500` | membuat `library_media` (banyak gambar/video per halaman Pustaka Kedu, dari unggahan atau tautan YouTube/Drive/Vimeo/Commons) dan menyalin isi kolom lama `image_a`/`image_b`/`video` ke sana |
| `003600` | menambahkan `participants.intro_seen_at`: pemain baru wajib menonton cerita pembuka; peserta yang sudah punya progres diisi saat migration (backfill) |
| `003700` | menambahkan `dialogues.pose` dan `dialogues.effect` (pose tokoh dan efek layar dari naskah); isinya diisi `php spark gelita:story:update` |

`003200` memanggil `resetDataCache()` sebelum memeriksa kolom; tanpa itu seluruh pemeriksaan `fieldExists()` membaca daftar kolom versi sebelum `003100` pada proses `spark migrate` yang sama. Jangan melakukan rollback ke bawah `003100`: tahap 3 bergantung pada penamaan kolom hasil migration tersebut. Rincian lengkap di [`docs/01_DATABASE.md`](docs/01_DATABASE.md#koreksi-challenge_attempts-003100-dan-003200).

## Aset vendor

Library frontend di-host sendiri di [`public/assets/vendor/`](public/assets/vendor/); tidak ada CDN. Isinya `echarts.min.js` (ECharts 6.1.0) dan `howler.min.js` (Howler.js 2.2.4) dari paket rilis resmi, beserta lisensinya; versi, checksum, dan cara memperbaruinya ada di [`public/assets/vendor/README.md`](public/assets/vendor/README.md). Font lokal ada di `public/assets/fonts/`. Pola `.gitignore` sengaja `/vendor/` (khusus folder Composer di root) agar folder ini tetap dilacak Git.

## Sesi dan CSRF

- Sesi memakai `DatabaseHandler` dengan tabel `ci_sessions` (migration `003000`), cookie `gelita_session`, masa berlaku 4 jam, `regenerateDestroy` aktif, dan `matchIP` nonaktif.
- CSRF memakai mode `session` dengan token acak per-request (`tokenRandomize`) tetapi **tanpa** regenerasi per-submit (`regenerate = false`). Game mengirim banyak request AJAX beruntun; token yang berubah setiap request membuat request paralel gagal. JavaScript mengirim token lewat header `X-CSRF-TOKEN`.
- `App\Libraries\HashedIpSessionHandler` (penyimpan hash IP, bukan IP mentah) **belum dipasang**. Kelas itu mewarisi `MySQLiHandler` yang mengunci sesi dengan `GET_LOCK`, dan `Services::session()` hanya memetakan handler per-platform ketika driver persis `DatabaseHandler::class` — sehingga driver kustom melewati pemilihan platform dan gagal pada database uji SQLite. Perubahan yang diperlukan beserta buktinya dicatat di [`docs/02_PROJECT_FOUNDATION.md` § 3b](docs/02_PROJECT_FOUNDATION.md#3b-library-hashedipsessionhandler--belum-dipasang).

## Pengujian

Jalankan test suite tanpa laporan coverage:

```bash
vendor/bin/phpunit --no-coverage
```

Suite (161 test) memakai grup database `tests` (SQLite3 in-memory) dan hanya menjalankan migration bernamespace `Tests\Support`, bukan migration aplikasi — skema aplikasi memakai fitur MySQL/MariaDB (`DATETIME(6)`, `ON UPDATE CURRENT_TIMESTAMP(6)`) yang tidak ada di SQLite. Sesi di-mock dengan `ArrayHandler` oleh `CIUnitTestCase`. Yang dikunci suite antara lain:

- `RouteWiringTest` — auto-route tetap mati, setiap handler menunjuk kelas/method yang ada, setiap view yang disebut controller punya berkasnya, dan ganti sandi staf hanya berfilter `staffAuth` (terbuka untuk guru).
- `HeadRouteTest` — HEAD memakai rute GET beserta filternya; HEAD ke halaman staf tanpa login dialihkan ke `/admin/login`.
- `StaffPasswordGateTest` — staf bersandi sementara hanya dapat membuka Ubah sandi: halaman lain dialihkan, API membalas 403 `PASSWORD_CHANGE_REQUIRED`.
- `HuntPayloadTest` — payload arena `cari` tidak membedakan jebakan, jawaban hanya lewat token objek, dan objek jebakan tidak pernah diminta dijawab.
- `RegionEntryTest`, `DialogueGateTest` — wilayah baru selalu lewat dialog pembukanya.
- `RegionStoryTest` (Tahap 3) — `/tuntas/{code}` hanya untuk wilayah tuntas dan berakhir di Pustaka + wilayah berikutnya (atau penutup), `/penutup` hanya setelah semua node tuntas, overlay Kenali untuk setiap wilayah dengan lencana dari event server, tirai wilayah di pin/kartu, layar selesai penuntas menuju `/tuntas/{code}`, dialog bernarasi dengan kartu bab dan frame pose per baris, serta tirai tantangan yang pendek, dapat dilewati, dan diputar sebelum attempt dibuka.
- `LibraryLockTest` — Pustaka terbuka per wilayah hanya setelah wilayahnya tuntas (juga pada unlock mode `free`), halaman terkunci dirender tanpa mencatat `library_opened`, wilayah yang belum terbuka tetap ditolak, rak `/pustaka` dan tombol bergembok di peta wilayah, serta momen "Pustaka terbuka" hanya milik attempt penuntas.
- `VideoThumbnailTest` — alamat thumbnail per penyedia (YouTube maxres → hq, Vimeo oEmbed, Drive), respons HTML/non-gambar/SVG/terlalu besar ditolak, galat jaringan tidak melempar; memakai HTTP tiruan, tanpa jaringan.
- `JsConfigTest` — penanda sesi antrean offline buram dan per sesi, `#app-config` tanpa identitas siswa, dan setiap `t('…')` di JavaScript punya kunci `Js.*`.
- `ChartDataTest` — bentuk data chart admin dan kondisi kosongnya.
- `ExcelWriterTest` — workbook terbaca ulang PhpSpreadsheet, teks berawalan `=` tidak pernah menjadi rumus, berkas sementara dibersihkan.
- `BankWorkbookGuideTest` — setiap kolom yang dibaca importer punya penjelasan Indonesia di templat, header sheet data sama persis dengan importer, dan workbook produksi `docs/bank-soal/` ikut dibangun ulang bila kolom impor berubah.
- `MediaLinkTest`, `RichTextTest` — tautan media Pustaka hanya http(s) dan YouTube lewat domain nocookie; format teks Pustaka selalu meng-escape HTML lebih dulu.
- `ExportRulesTest` — mode anonim tanpa kolom identitas, rahasia tidak pernah diekspor, kunci jawaban hanya admin, guru tanpa Raw Events, cakupan sekolah dipaksa service, cakupan & penjaga drift penghapusan.
- `Stage7WiringTest` — kelima command `gelita:*` aktif, service baru terdaftar, dan setiap keluaran templat PDF lewat `esc()`.
- `StaffPasswordCommandTest` — `gelita:staff:password` terdaftar, menolak tanpa nama pengguna, dan memakai jalur reset yang sama dengan `/admin/staf`; sandi sementaranya lolos `PasswordPolicy`.
- `DeployFilesTest` — skrip Windows hanya ASCII, skrip Bash ber-LF dengan `set -E`, konfigurasi Nginx Linux dan Windows hanya berbeda di baris platform, tidak ada perintah yang merusak production, salinan `.env` di backup Windows tidak langsung terhapus retensi.
- `ScoringServiceTest`, `PasswordPolicyTest`, `ChallengeEntityTest`, `ViewHelperTest`, `LanguageFilesTest`, `EventTimeTest`, `DebugToolbarRedactionTest`.

Karena tabel aplikasi tidak dibuat di suite ini, **alur HTTP ujung ke ujung diverifikasi di atas MySQL/MariaDB sungguhan**. Gunakan database scratch terpisah. Kredensial untuk perintah CLI dioper lewat `database_default_DSN`, satu-satunya cara yang dapat menimpa `.env`:

```bash
database_default_DSN='MySQLi://user:sandi-url-encoded@localhost/gelita_scratch' php spark migrate
```

`env "database.default.database=…"` atau `database_default_username=…` **tidak** berlaku bila kunci itu sudah ada di `.env`, karena nilai `.env` dibaca lebih dulu. DSN ditulis tanpa port: CodeIgniter 4.7.4 meneruskan port dari DSN sebagai string, dan `mysqli` menolaknya. Rinciannya di [`docs/08_DEPLOYMENT.md` → *Kredensial migration lewat DSN*](docs/08_DEPLOYMENT.md#kredensial-migration-lewat-dsn).

lalu seed, jalankan `php spark serve`, dan telusuri alur siswa (registrasi → tantangan → refleksi) serta halaman admin. `php spark serve` membaca `.env`, jadi untuk penelusuran HTTP isi `.env` lokal dengan database scratch tersebut.

Perilaku JavaScript tahap 6 diverifikasi dengan cara yang sama di browser sungguhan (Playwright + Chromium): alur siswa 15 node beserta jalur galatnya, antrean offline, telemetry audio, dan seluruh halaman admin ber-chart. Konten dialog dan butir di-cache `ContentRepository`; setelah mengubah data langsung di database, jalankan `php spark cache:clear`.

## Dokumentasi

Dokumen spesifikasi per tahap ada di folder [`docs`](docs/) dan mencerminkan implementasi terkini:

1. [Database](docs/01_DATABASE.md)
2. [Fondasi proyek](docs/02_PROJECT_FOUNDATION.md)
3. [Model, entity, dan service](docs/03_MODEL_ENTITY.md)
4. [Route, controller, autentikasi, otorisasi](docs/04_CONTROLLER_ROUTE.md)
5. [View, UI, dan CSS](docs/05_VIEW_UI.md)
6. [JavaScript](docs/06_JAVASCRIPT.md)
7. [Integrasi fitur](docs/07_FEATURE_INTEGRATION.md)
8. [Deployment dan operasional](docs/08_DEPLOYMENT.md) — Linux dan Windows + Laragon (Nginx); skripnya di [`deploy/`](deploy/)

Daftar route lengkap dapat dilihat kapan saja tanpa membaca kode:

```bash
php spark routes
```

## Catatan keamanan

- Jangan pernah commit atau membagikan `.env`; berkas ini memuat kredensial dan material rahasia aplikasi. Yang dilacak Git hanyalah templat `env` tanpa nilai rahasia.
- `gelita.ipSalt` wajib diisi string acak panjang per pemasangan. Salt kosong membuat hash IP berupa SHA-256 tanpa garam yang dapat dibalik dengan pencarian menyeluruh ruang IPv4.
- Server sekolah wajib memakai `CI_ENVIRONMENT = production` (templat `env` berisi `development` untuk kerja lokal). Di development, Debug Toolbar menulis potret setiap request ke `writable/debugbar/`; field kata sandi disamarkan `App\Filters\DebugToolbar`, tetapi data lain tetap tersimpan.
- Gunakan akun database dengan hak akses seperlunya, terutama di production.
- Konfigurasi web server harus selalu mengarah ke folder [`public`](public/), bukan root proyek.
- Untuk deployment, gunakan dependensi yang terkunci melalui `composer install --no-dev --optimize-autoloader`.
