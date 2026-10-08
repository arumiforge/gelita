# Rencana: Paket Aset GELITA — unduh sekali di awal, lalu pindah layar dan ganti bahasa seketika

> **Status: proposal, belum dikerjakan** (disusun 7 Oktober 2026). Dokumen ini hanya rencana. Tidak ada kode yang diubah. Rencana ini selaras dengan [Dok 3 — Panggung 16:9](perombakan-kelas5/03_TAMPILAN_16x9_DAN_ADMIN.md), yang sedang dikerjakan sesi lain. Bagian yang menyentuh berkas Dok 3 baru dikerjakan sesudah bagian Dok 3 itu digabung ke `main` (§10). Keputusan yang menunggu pemilik ada di §12.

---

## Daftar isi

1. [Latar dan sasaran terukur](#1-latar-dan-sasaran-terukur)
2. [Keadaan sekarang, diukur dari repo](#2-keadaan-sekarang-diukur-dari-repo)
3. [Apa yang membuat perpindahan terasa lambat](#3-apa-yang-membuat-perpindahan-terasa-lambat)
4. [Pilihan arsitektur dan rekomendasi](#4-pilihan-arsitektur-dan-rekomendasi)
5. [Format dan versi paket](#5-format-dan-versi-paket)
6. [Alur admin: membuat dan menerbitkan versi paket](#6-alur-admin-membuat-dan-menerbitkan-versi-paket)
7. [Integritas data penelitian](#7-integritas-data-penelitian)
8. [Perangkat dan batasan peramban](#8-perangkat-dan-batasan-peramban)
9. [Keamanan dan privasi](#9-keamanan-dan-privasi)
10. [Tahapan implementasi](#10-tahapan-implementasi)
11. [Risiko, kriteria selesai, dan rencana uji](#11-risiko-kriteria-selesai-dan-rencana-uji)
12. [Pertanyaan terbuka untuk pemilik](#12-pertanyaan-terbuka-untuk-pemilik)
13. [Sumber perilaku peramban](#13-sumber-perilaku-peramban)

---

## 1. Latar dan sasaran terukur

### 1.1 Latar

GELITA adalah instrumen penelitian. Sebelum diuji ke siswa, isinya dibekukan, dan sesudah itu hampir semua aset tidak berubah. Perubahan hanya terjadi bila admin membuat **versi paket baru** yang menggantikan paket lama, biasanya berisi gambar asli pengganti gambar contoh atau rekaman suara oleh pengisi suara lain. Mekanisme game tidak berubah sesudah dibekukan.

Karena itu GELITA bisa diperlakukan seperti aplikasi yang **diunduh sekali**. Gambar, audio, huruf, CSS, dan JS disimpan di perangkat pada awal permainan. Sesudah itu perpindahan layar, animasi gambar, dan perpindahan bahasa tidak lagi menunggu jaringan.

### 1.2 Sasaran

Angka diukur di perangkat acuan (Chromebook 4 GB dan iPad generasi ke-9 atau setara) pada jaringan acuan "Wi-Fi sekolah lambat": **1 Mbps per perangkat, RTT 150 ms** (Playwright/DevTools *throttling*, §11.3). Semua angka adalah p95 dari sedikitnya 20 percobaan.

| # | Sasaran | Angka | Diukur dari → sampai |
|---|---|---|---|
| S1 | Tanggapan visual pindah layar | ≤ 100 ms | ketukan → transisi mulai bergerak (tidak ada layar kosong/putih) |
| S2 | Pindah layar selesai | ≤ 400 ms (Tahap 4); ≤ 150 ms untuk layar yang sudah diprefetch (Tahap 5) | ketukan → layar baru tergambar dengan semua gambarnya sudah ter-*decode* |
| S3 | Gambar tanpa kedip | 0 gambar yang muncul bertahap atau terlambat | layar baru tergambar → semua `<img>` di panggung `complete` |
| S4 | Ganti bahasa | ≤ 400 ms (Tahap 4); ≤ 150 ms (Tahap 5) | ketukan ID/EN → seluruh teks dan narasi berikutnya berbahasa baru |
| S5 | Ukuran paket penuh | ≤ 50 MB; ≤ 40 MB bila narasi dikodekan ulang (§12 P2) | jumlah `bytes` manifest |
| S6 | Ukuran paket inti (wajib sebelum mulai) | ≤ 15 MB | grup `inti` manifest |
| S7 | Unduh paket inti di jaringan acuan | ≤ 2,5 menit | ketuk "Unduh" → tombol Mulai aktif |
| S8 | Unduh paket penuh di jaringan acuan | ≤ 7 menit, berjalan di latar sambil bermain | ketuk "Unduh" → semua grup lengkap |
| S9 | Pembaruan ke versi paket baru | hanya berkas yang berubah yang diunduh | byte terunduh = jumlah berkas baru/berubah |
| S10 | Data penelitian | 100 % sesi tercatat dengan versi paket; 0 event hilang atau tergandakan | §7 |

S1–S4 berlaku sesudah paket lengkap. Selama paket belum lengkap atau tidak bisa disimpan (§8), game tetap berjalan seperti sekarang dengan aset dari jaringan.

### 1.3 Non-sasaran

- **Bermain sepenuhnya luring.** Setiap layar tetap dirender server (HTML berisi progres, token CSRF, dan `screen_viewed`), dan jawaban tantangan dinilai server (`/check`). Yang bisa luring hanya pengiriman event (antrean yang sudah ada) dan halaman `offline.html`. Membuat game luring penuh berarti memindahkan penilaian dan progres ke klien, dan itu tidak sepadan bagi instrumen penelitian.
- **Mengubah isi, mekanisme, atau tata letak.** Tata letak milik Dok 3; dokumen ini hanya mengubah cara aset dimuat.
- **Panel admin.** Admin tidak dipaketkan (bukan area panggung, butuh data segar).
- **Toko aplikasi / aplikasi native.** Tetap aplikasi web terpasang (PWA) yang sudah ada.
- **Video YouTube di Pustaka** tidak bisa masuk paket (pemutar pihak ketiga). Pilihannya ada di §12 P4.

---

## 2. Keadaan sekarang, diukur dari repo

Diukur dari `main` (`1ba4502`, 7 Oktober 2026) dengan `du -sb`, `find`, `gzip -9`, dan `ffprobe`.

### 2.1 Aset yang ada di repo

| Jenis | Lokasi | Berkas | Ukuran | Catatan |
|---|---|---:|---:|---|
| Narasi ID | `public/assets/audio/narasi/id/` | 100 | 14,15 MB | MP3 96 kbps mono 44,1 kHz; total durasi 19 menit 37 detik |
| Narasi EN | `public/assets/audio/narasi/en/` | 100 | 10,50 MB | MP3 96 kbps mono; total 14 menit 32 detik |
| Musik | `public/assets/audio/music/` | 3 | 3,21 MB | MP3 128 kbps, ±1 menit per lagu, diputar berulang |
| Efek suara | `public/assets/audio/sfx/` | 6 | 0,18 MB | 3–83 KB per berkas |
| CSS game | `public/assets/css/` (tanpa `admin.css`) | 6 + `noscript.css` | 164 KB (33 KB gzip) | Dok 3 menulis ulang berkas ini (`stage.css` baru, `layout.css` dihapus) |
| CSS admin | `admin.css` | 1 | 22 KB | tidak dipaketkan |
| JS game | `public/assets/js/` tanpa `admin*` | 32 modul | 181 KB (54 KB gzip) | modul ES tanpa build; impor relatif dan `import()` dinamis per layar |
| JS admin | `admin.js`, `admin/*` | 9 | 45 KB | tidak dipaketkan |
| Howler | `public/assets/vendor/howler.min.js` | 1 | 36 KB | |
| Huruf | `public/assets/fonts/*.woff2` | 9 | 118 KB | Cinzel, Plus Jakarta Sans, IBM Plex Mono (latin) |
| Ikon aplikasi | `public/assets/app/` | 5 | 372 KB | manifest dan layar utama; tidak perlu di paket |
| Data wilayah | `public/assets/data/wilayah-id.json` | 1 | 35 KB | hanya pendaftaran |
| Gambar | `public/assets/{bg,char,map,challenge,library,reward,ui}/` | 1 (`ui/placeholder.svg`) | 1 KB | **folder gambar kosong di repo** |

Jumlah aset statis di repo yang relevan untuk game: **±28,9 MB**, dan 97 % di antaranya audio.

### 2.2 Gambar: tidak ada di repo, diunggah admin

Semua gambar game diunggah admin ke `public/assets/uploads/` (di-*gitignore*, hanya ada di server dan ikut backup) atau dibuat `gelita:mockups` sebagai SVG gambar contoh di `public/assets/uploads/mockup/`. Ukurannya tidak bisa diukur dari repo. Yang bisa dihitung adalah **jumlah slot** dan **batas ukuran berkas** dari `docs/rencana-katalog-aset.md` §10.1, sehingga tabel di bawah adalah **batas atas**:

| Kelompok | Slot (sumber) | Batas per berkas | Batas atas |
|---|---|---:|---:|
| Latar umum `bg.welcome/auth/intro/map/reflection/loading` | 6 (`MediaAssetSeeder::SLOTS`) | 450 KB | 2,7 MB |
| Latar wilayah `bg.{wilayah}.region` | 3 | 450 KB | 1,35 MB |
| Peta `map.kedu`, `map.region.{wilayah}` | 4 | 600 KB | 2,4 MB |
| Frame tokoh `char.{jaka,kedu}.{pose}.{1–3}` | 30 (`Config\Gelita::$characterAnimations`: 10 pose × 3) | 250 KB | 7,5 MB |
| UI `ui.logo-hero`, `ui.btn-start(.en)`, `ui.logo` | 4 | 80–250 KB | 0,5 MB |
| Lencana `reward.badge.{wilayah}` | 3 | 60 KB | 0,18 MB |
| Gambar tantangan (Dok 2 §10.4) | 45: 3 adegan, 9 Susun Gambar, 30 benda Cari Objek, 3 pernyataan | 80–350 KB | 6,84 MB |
| Gambar Pustaka | 43 gambar + 5 poster video (`docs/pustaka`: 48 media, 5 di antaranya YouTube) | 200–250 KB | 11,75 MB |
| **Jumlah** | **138** | | **±33 MB** |

Slot opsional (`challenge.{ref}.bg`, latar dialog) belum dihitung. Bila 12 latar node diisi, batas atas bertambah 5,4 MB.

**Temuan penting tentang Pustaka:** ke-43 gambar Pustaka **tidak disimpan di server**. `library_media.external_url` berisi tautan Wikimedia Commons, dan `MediaLink::parse()` mengubahnya menjadi `commons.wikimedia.org/wiki/Special:FilePath/…?width=1280`, yang dimuat peramban langsung dari Wikimedia setiap kali halaman Pustaka dibuka. Lima video adalah sematan `youtube-nocookie.com` yang baru dimuat setelah siswa menekan Putar. Komentar di `MediaLink` sendiri menyarankan sekolah tanpa internet mengunggah salinannya.

### 2.3 Perkiraan ukuran paket

| Komponen | Sekarang | Bila narasi 48 kbps (§12 P2) |
|---|---:|---:|
| Audio (narasi dua bahasa, musik, efek) | 28,1 MB | ±15,6 MB |
| Gambar (batas atas §2.2, Pustaka disalin lokal) | ±33 MB | ±33 MB |
| Kode (CSS, JS, Howler, huruf) | 0,5 MB | 0,5 MB |
| **Paket penuh (batas atas)** | **±62 MB** | **±49 MB** |

Batas ukuran di katalog adalah batas atas. Gambar sungguhan biasanya lebih kecil, terutama SVG mockup dan PNG benda 240 × 240. Ukuran sebenarnya baru diketahui saat paket pertama dibangun di server (Tahap 1). Sasaran S5 (≤ 50 MB) realistis bila narasi dikodekan ulang atau gambar mengikuti anggaran.

### 2.4 Service worker, manifest, dan "pasang aplikasi"

- `public/sw.js` (cache `gelita-offline-v1`) **sengaja minimal**. Ia hanya menangani navigasi GET, memakai *navigation preload*, dan menampilkan `offline.html` bila jaringan putus. Ia tidak menyimpan aset, API, atau HTML. Komentarnya menyebut alasannya: tidak ada data penelitian di cache perangkat kelas yang dipakai bergantian.
- `tests/unit/InstallableAppTest.php` **mengunci keputusan itu**:
  - `testServiceWorkerOnlyCachesTheOfflinePage` mewajibkan tepat satu `cache.add(`, tanpa `cache.put(` dan `cache.addAll(`;
  - `testOfflinePageIsSelfContained` mewajibkan `offline.html` tanpa `src`/`href` luar;
  - `testOnlyGameLayoutIsInstallable` mewajibkan manifest hanya di `layouts/game.php`;
  - manifest mengunci `display: fullscreen` dan `orientation: landscape`, dengan ikon 192/512/maskable.

  Paket aset **mengubah invarian ini dengan sadar** (§9.1): uji diganti, bukan dilewati.
- `public/manifest.json`: `start_url`/`scope` `./`, `display_override: [fullscreen, standalone]`.
- `public/assets/js/game/install.js`: mendaftarkan `sw.js` di base URL. Di Chrome/Android `beforeinstallprompt` ditahan untuk tombol "Pasang GELITA", sedangkan iOS mendapat petunjuk Bagikan → Tambah ke Layar Utama. Tombol hanya muncul di perangkat sentuh yang belum terpasang.

### 2.5 Cara halaman dirender

- **MPA PHP.** Setiap layar game adalah rute GET tersendiri di `app/Config/Routes.php` (±20 rute game) yang dirender `layouts/game.php`. Setiap perpindahan memuat dokumen baru: HTML dari server, lalu 6 CSS, Howler, `game.js`, dan modul halaman.
- **Efek samping GET.** Beberapa GET mengubah data:
  - `ScreenViewFilter` mencatat `screen_viewed` di server untuk **setiap** GET grup `gameSession`;
  - `GET tantangan/{code}/{n}` membuka attempt baru;
  - `GET intro/selesai` menandai cerita selesai;
  - `GET keluar` mengakhiri sesi.

  Akibatnya HTML **tidak boleh** diprefetch atau diambil dari cache tanpa aturan khusus (§4.3).
- **Tirai** (`core/curtain.js`) menutupi perpindahan dan memuat aset `data-preload` dengan progres nyata. Lamanya 2,5–8 detik untuk tirai halaman, ±1,8 detik untuk "Menuju {wilayah}", dan ≤ 1,5 detik sebelum tantangan. Tirai ada karena aset dimuat dari jaringan.
- **Aset per halaman.** `game.js` memuat modul halaman lewat `import()` dinamis menurut `data-screen`. Latar dipasang sebagai `<img class="scene-img">`.

### 2.6 Ganti bahasa

- `components/lang-switch.php` adalah **form POST biasa** ke `/bahasa`. `HomeController::setLocale()` menyimpan locale di sesi PHP dan `game_sessions.locale`, lalu mengalihkan kembali ke URL yang sama. Seluruh halaman dimuat ulang.
- Ada juga `POST /api/session/locale` (`SessionApiController::setLocale`) yang belum dipakai JS.
- Teks UI JS dikirim per bahasa aktif di `js_config()` (`lang('Js.*')`), hanya satu bahasa.
- Narasi per bahasa: `audio_assets` per locale, berkas di `audio/narasi/{id,en}/` atau unggahan.
- Gambar bertulisan punya varian bahasa (`media_key_src_locale()`: `ui.btn-start.en`).

### 2.7 Versi dan cache yang sudah ada

| Mekanisme | Isi sekarang | Dipakai untuk |
|---|---|---|
| `Config\Gelita::$assetVersion` (`gelita.assetVersion` di `.env`, bawaan `'1'`) | satu angka manual | `asset_url_versioned()` menambah `?v=` pada 6 CSS, `howler.min.js`, dan `game.js` |
| Nginx (`deploy/*/nginx/gelita-https.conf`) | URL ber-`?v=` → `max-age=31536000, immutable`; aset lain → `no-cache` | modul JS yang diimpor, huruf, gambar, audio, dan unggahan **divalidasi ulang setiap kali dipakai** (304 bila tidak berubah) |
| `game_releases` | `release_code`, `app_version`, `content_version`, `asset_version`, `scoring_version`, `is_active` | `content_version` rilis aktif menjadi kunci cache konten `ContentRepository`. `asset_version` hanya teks bebas di halaman Rilis ("Versi gambar & suara") yang **tidak terhubung** ke `assetVersion` maupun berkas apa pun |
| `game_sessions.release_id` | rilis aktif saat sesi dibuat | menandai sesi dengan rilis; diekspor sebagai `release_code` |
| `game_sessions.app_client_version` | kolom ada, **tidak pernah diisi** (tidak ada JS yang mengirimnya) | — |
| `media_src()` / `media_key_src()` | `base_url(storage_path)` **tanpa versi** | `MediaStore` menimpa berkas dengan nama resmi yang sama (`challenge.tmg-4.scene.png`), jadi URL tetap dan hanya `no-cache` yang mencegah gambar lama tampil |

### 2.8 Antrean event luring

- `core/events.js`: dua kanal (`/api/events`, `/api/audio-events`), batch tiap 3 detik atau 20 event, maksimum 50 per permintaan, dan `sendBeacon` saat `pagehide`/`visibilitychange`. `client_event_id` (UUID) membuat pengiriman ulang dibalas `duplicate`.
- `core/storage.js`: batch yang gagal karena jaringan disimpan di `localStorage` (`gelita.queue.{kanal}`), ditandai `sessionTag` (HMAC id sesi, `session_tag()`). Total dibatasi **500 entri terbaru** (yang lebih lama dibuang). Antrean dikirim ulang saat halaman dimuat dan saat `online`.
- `occurred_at` = jam klien (`toISOString()`, milidetik). Server menyimpannya dalam DATETIME(6) (`EventTimeTest`). Klien **tidak** mengoreksi selisih jam: `server_time` dikirim di respons heartbeat dan locale, tetapi tidak dibaca JS.
- `sequence_no` adalah variabel modul `let sequence = 0`, sehingga **mulai lagi dari 1 setiap halaman dimuat**. Di MPA, urutan dalam satu sesi hanya bisa disusun dari `occurred_at` (lalu `sequence_no` per halaman).

---

## 3. Apa yang membuat perpindahan terasa lambat

Dari §2, waktu pindah layar sekarang terdiri dari:

1. **Satu perjalanan HTML** ke server (RTT + kerja PHP + query). Ini tak terhindarkan selama layar dirender server.
2. **Validasi ulang semua subsumber yang tidak ber-`?v=`.** Setiap modul JS yang diimpor (sampai 10 modul per layar), kedua huruf yang di-*preload*, setiap gambar, dan setiap audio bertanya ke server lebih dulu (`no-cache` → 304). Di RTT 150 ms dengan HTTP/2, permintaan ini berjalan paralel, tetapi rantai impor modul berjalan **berurutan bertingkat**: `game.js` → `core/*` → modul halaman → `engines/*`. Tiap tingkat menambah satu RTT.
3. **Unduh gambar dan audio** yang belum ada di cache HTTP. Ini dominan pada kunjungan pertama ke suatu layar.
4. **Decode gambar** (latar 1920 × 1080) dan **parse CSS/JS** dari awal, karena dokumen baru membuang semua yang sudah ter-decode.
5. **Tirai** sengaja menahan 1,5–8 detik untuk menyembunyikan butir 2–4.

Paket aset menghapus butir 2 dan 3 sepenuhnya, dan butir 4 sebagian. Butir 1 hanya bisa dihapus dengan merender layar di klien atau memprefetch HTML (§4.3). Butir 5 dipendekkan sesudah butir 2–4 hilang. Waktu tiap butir di jaringan acuan diukur di Tahap 0, sebelum keputusan Tahap 5.

---

## 4. Pilihan arsitektur dan rekomendasi

### 4.1 Penyimpanan paket: service worker + Cache Storage

| Pilihan | Keterangan | Putusan |
|---|---|---|
| **A. Service worker + Cache Storage, unduh eksplisit dengan layar progres** | SW melayani setiap URL paket dari cache (*cache-first*, URL tak berubah). Halaman "Siapkan GELITA" mengunduh manifest berkas demi berkas dengan progres nyata. | **Direkomendasikan** |
| B. Cache HTTP biasa + `immutable` | Hanya mengandalkan header. Tidak ada progres, tidak ada jaminan berkas tetap ada, dan peramban bebas menggusurnya kapan saja. | Tidak cukup sendiri, tetapi dipakai juga sebagai lapis kedua (§5.2) |
| C. IndexedDB berisi blob + `URL.createObjectURL` | Bisa tanpa SW, tetapi semua `<img src>` di view PHP harus diganti JS, dan blob URL hilang setiap pindah halaman. | Ditolak: bertentangan dengan MPA |
| D. Background Fetch API | Unduh tetap jalan saat tab ditutup, tetapi hanya Chromium; Safari dan Firefox tidak mendukung ([caniuse](https://caniuse.com/wf-background-fetch), dicek Oktober 2026). | Ditolak untuk sekarang |

Dengan A, `<img src="…">` di view PHP tidak perlu berubah selain URL-nya (§5.2). SW menjawab dari cache dalam hitungan milidetik tanpa menyentuh jaringan.

**Audio dari cache.** `<audio>` meminta berkas dengan header `Range`, jadi SW harus menjawab `206 Partial Content` dari salinan utuh di cache. Safari versi terbaru serta Chrome/Edge ≥ 87 menangani ini dengan benar bila SW menyusun respons 206 sendiri ([web.dev: range requests](https://web.dev/articles/sw-range-requests)). Kode pemotong `Range` (±40 baris) ditulis sendiri, tanpa Workbox, agar tetap "tanpa build". Howler (efek suara) memakai XHR/Web Audio tanpa `Range`.

### 4.2 Navigasi: tetap MPA atau shell SPA

| Pilihan | Pindah layar | Ganti bahasa | Biaya | Risiko |
|---|---|---|---|---|
| **M1. MPA + paket + View Transitions lintas dokumen** | 1 RTT HTML; subsumber dari cache; transisi silang menyembunyikan jeda | muat ulang dengan aset dari cache (1 RTT) | kecil: SW, URL, `@view-transition` | rendah; perilaku sekarang tetap |
| **M2. MPA + "navigasi dalam panggung"** (HTML layar berikutnya diambil `fetch`, isi `#stage` ditukar; gaya Turbo/htmx-boost) | 0 RTT bila sudah diprefetch, 1 RTT bila belum; musik dan audio tetap jalan; modul tidak di-parse ulang | tukar isi dari HTML bahasa lain yang sudah diprefetch → seketika | sedang: siklus hidup modul (`init`/`destroy`), prefetch aman, fokus dan riwayat | sedang; perlu uji seluruh layar |
| M3. Shell SPA (layar dirender klien dari JSON API) | 0 RTT untuk data yang sudah ada | seketika (kamus dua bahasa di klien) | besar: menulis ulang ±20 view PHP ke JS, `screen_viewed` dipindah ke klien, penjaga akses di klien | tinggi; bentrok langsung dengan Dok 3 yang sedang menulis ulang view yang sama |

**Rekomendasi:** kerjakan **M1 lebih dulu** (Tahap 1–4), lalu ukur. Kerjakan M2 (Tahap 5) hanya bila S2/S4 versi Tahap 5 tetap diminta sesudah pengukuran. M3 tidak sepadan: ia menghapus jeda HTML (±150–400 ms di jaringan acuan) dengan menulis ulang seluruh antarmuka yang baru saja dirombak Dok 3, dan memindahkan pencatatan navigasi penelitian ke klien yang lebih mudah dipalsukan (bandingkan Dok 1 catatan 22: `screen_viewed` sengaja hanya ditulis server).

Alasan M1 dan M2 cocok dengan Dok 3: panggung 1280 × 720 dp (`#stage`) adalah wadah tetap yang sama di semua layar. M1 cukup menambah `view-transition-name` pada panggung, HUD, dan latar. M2 menukar `main#app`, HUD, dan nav bar di dalam `#stage` yang sama tanpa menyentuh bingkai.

**View Transitions lintas dokumen** (`@view-transition { navigation: auto; }`) didukung Chrome ≥ 126 dan Safari ≥ 18.2 ([Chrome for Developers](https://developer.chrome.com/docs/web-platform/view-transitions)). Peramban lain berpindah tanpa animasi, jadi aman sebagai peningkatan bertahap. `prefers-reduced-motion` mematikan animasinya.

### 4.3 HTML: selalu dari jaringan, prefetch hanya yang aman

HTML **tidak pernah disimpan di Cache Storage**, karena:

- ia berisi nama siswa, progres, dan token CSRF (privasi di perangkat bersama, §9);
- server harus melihat setiap GET untuk `screen_viewed` dan penjaga akses (`gameSession`, `participantAuth`);
- isi berubah menurut progres.

Untuk M2 (dan secara opsional M1 lewat `<link rel="prefetch">`), prefetch HTML diizinkan **hanya** dengan aturan berikut:

1. Daftar putih rute tanpa efek samping: `peta`, `wilayah/*`, `misi/*`, `hasil/*`, `pustaka`, `pustaka/*`, `profil`, `refleksi`, `dialog/*`, `tuntas/*`, `penutup`. **Tidak pernah** `tantangan/*` (membuka attempt), `intro/selesai`, `keluar`, `gerbang/peta`, atau POST.
2. Permintaan prefetch membawa `Sec-Purpose: prefetch` (atau header `X-Gelita-Prefetch: 1`). `ScreenViewFilter` **tidak** mencatat `screen_viewed` untuk permintaan itu. Saat layar hasil prefetch benar-benar ditampilkan, klien mengirim satu event `screen_viewed` yang ditandai `via=prefetch`. Ini perubahan kecil pada aturan "hanya server" (Dok 1 catatan 22) dan perlu persetujuan (§12 P6). Alternatif tanpa perubahan aturan: server mencatat `screen_viewed` saat klien memanggil `POST /api/screen` dengan token sekali pakai yang dibuat ketika HTML prefetch dirender.
3. HTML hasil prefetch disimpan **di memori tab** (bukan Cache Storage), kedaluwarsa 60 detik, dan dibuang saat keluar.

### 4.4 Ganti bahasa tanpa memuat ulang

Kedua bahasa narasi dan gambar bertulisan ada di paket, jadi yang tersisa hanya **teks**, yang dirender server.

| Pilihan | Cara | Putusan |
|---|---|---|
| L1. Muat ulang dengan aset dari cache (M1) | Form POST `/bahasa` tetap; dokumen baru tergambar dengan semua aset lokal, ditutup View Transition | **Tahap 4**: memenuhi S4 ≤ 400 ms tanpa mengubah view |
| L2. Tukar isi dari HTML bahasa lain yang sudah diprefetch (M2) | Saat layar tampil dan peramban menganggur, HTML layar yang sama diprefetch dengan `?lang=en` (tanpa efek samping, §4.3). Ketukan EN memanggil `POST /api/session/locale` (sudah ada) dan menukar isi panggung dari HTML itu | **Tahap 5**: seketika |
| L3. Render kedua bahasa di setiap halaman, ditukar CSS `:lang()` | HTML dan view membengkak dua kali lipat; `aria` dan pembaca layar membingungkan | Ditolak |
| L4. Kamus dua bahasa di klien | Hanya masuk akal bersama M3 | Ditolak |

Tambahan yang berlaku untuk L1 dan L2:

- `js_config()` mengirim teks JS **kedua bahasa** (`text.id`, `text.en`; ±2 × ukuran sekarang) agar modal dan toast yang dibuat JS ikut berganti tanpa permintaan baru.
- **Di tengah tantangan**, ganti bahasa dengan L1 akan membuang isian yang belum dicek. Pilihannya: sembunyikan tombol bahasa selama tantangan (seperti tautan navigasi HUD sekarang), atau pakai L2 yang menyimpan status engine. Ini perlu diputuskan (§12 P5).
- Event `locale_changed` (sudah ada di `Config\Gelita::$eventTypes`) dicatat dengan `from`, `to`, dan `via`.

### 4.5 Pemuatan awal: decode gambar dan audio

Paket menghapus unduhan, tetapi gambar tetap harus di-*decode* saat dipakai. Latar JPEG 1920 × 1080 butuh puluhan milidetik di tablet murah, dan bila decode terjadi saat layar tampil, gambar muncul berkedip (melanggar S3).

- **Satu layar ke depan.** Setiap layar menyebut gambar layar berikutnya yang mungkin dituju (atribut `data-next-assets`, dari PHP yang tahu progres). Sesudah layar tampil dan peramban menganggur (`requestIdleCallback`), JS membuat `new Image()` lalu `await img.decode()` dan menahan rujukannya sampai pindah halaman. Ini tidak memakai jaringan karena berkas datang dari SW.
- **Latar layar sekarang** di-decode sebelum transisi selesai: `img.decode()` pada `#scene-a`, lalu kelas `is-visible`. Bila decode lebih lama dari 300 ms, transisi tetap jalan (tanpa blok).
- **Frame tokoh** (3 frame per pose) di-decode bersamaan saat pose dipakai, agar animasi tidak tersendat di siklus pertama.
- **Efek suara** sudah dimuat Howler dengan `preload: true`. Dari cache, memuatnya hampir tanpa biaya. Musik dan narasi tetap `preload="none"` sampai dibutuhkan, karena decode audio panjang memakan memori. Dari cache, `play()` mulai dalam puluhan milidetik.
- **Batas memori.** Paling banyak gambar layar sekarang + satu layar berikutnya yang ditahan ter-decode. Bila dibiarkan menumpuk, iPad dengan RAM 3 GB akan menutup tab.
- **Tirai** dipersingkat ketika paket lengkap: `curtain.js` memakai `minMs` 600 ms (bukan 2,5 detik) bila semua `data-preload` ada di cache paket. Durasi dramatik tirai cerita adalah keputusan desain, bukan teknis (§12 P8).

### 4.6 Ringkasan: yang benar-benar dibutuhkan untuk "seketika"

| Dibutuhkan | Tidak sepadan |
|---|---|
| Semua aset dari cache lokal tanpa validasi ulang (SW + URL tak berubah) | Shell SPA / render klien (M3) |
| Decode gambar layar berikutnya sebelum diketuk | Mengubah format gambar ke AVIF (dukungan iPad lama tidak pasti) |
| Transisi yang langsung bergerak (View Transitions) agar jeda HTML tidak terlihat kosong | Bermain luring penuh |
| Tirai yang dipersingkat bila aset sudah lokal | Background Fetch, Web Bundles, atau format paket biner sendiri |
| Untuk benar-benar 0 RTT dan ganti bahasa seketika: navigasi dalam panggung + prefetch HTML aman (M2) | Memprefetch semua layar sekaligus (efek samping, beban server di kelas 30 siswa) |

---

## 5. Format dan versi paket

### 5.1 Isi paket

| Grup | Isi | Wajib sebelum |
|---|---|---|
| `kode` | CSS game, JS game (semua modul termasuk `engines/*`), Howler, huruf, `offline.html` | layar pertama sesudah unduh |
| `inti` | efek suara, latar umum, frame tokoh, `map.kedu`, UI, narasi `intro-*` dan `peta-*` (dua bahasa), musik peta | tombol **Mulai** |
| `wilayah-temanggung`, `wilayah-magelang`, `wilayah-wonosobo` | latar, peta, dan lencana wilayah; 15 gambar tantangan; narasi `kenal-*`, `dialog-*`, `tuntas-*`, dan `petunjuk-*`; gambar Pustaka wilayah | masuk ke wilayah itu (`/wilayah/{code}`) |
| `penutup` | narasi `penutup-*`, musik tantangan/wilayah bila belum masuk grup lain | layar penutup |

Perkiraan dari §2 (batas atas, narasi 96 kbps): `kode` 0,5 MB; `inti` ±16 MB; tiap `wilayah-*` ±13 MB; `penutup` ±1,5 MB. Dengan narasi 48 kbps: `inti` ±14 MB, tiap wilayah ±10 MB. Grup diunduh berurutan `kode` → `inti` → wilayah yang terbuka berikutnya → sisanya, di latar sambil bermain. Bila siswa tiba di wilayah yang grupnya belum lengkap, tampil tirai progres grup itu (dengan aset dari jaringan sebagai cadangan sesudah 8 detik).

Tidak masuk paket: HTML, API, panel admin, ikon aplikasi (sudah ditangani manifest), `wilayah-id.json` (hanya pendaftaran; diambil dari jaringan), dan video YouTube (§12 P4).

### 5.2 Alamat berkas: berdasarkan isi (content-addressed)

- **Aset** (gambar, audio): disalin saat paket dibangun ke `public/assets/p/{sha256[0:16]}.{ext}`. Alamat ditentukan isi, jadi berkas yang sama di dua versi paket memakai URL yang sama dan **tidak diunduh ulang** (S9). Delta antarversi terjadi dengan sendirinya, tanpa format diff.
- **Kode** (CSS, JS): modul ES memakai impor relatif (`./core/config.js`), jadi nama per berkas tidak bisa di-*hash* tanpa build. Kode disalin utuh ke `public/assets/k/{app_version}/` (mis. `k/1ba4502/js/game.js`), dan impor relatif tetap benar. Alternatif import map (`<script type="importmap">`) dicatat di §12 P9.
- `media_src()`, `media_key_src()`, `asset_url_versioned()`, dan URL narasi membaca **peta kunci → URL paket** dari paket aktif (satu berkas PHP hasil build, dicache `ContentRepository`). View tidak berubah. Bila paket belum dibangun, helper kembali ke URL lama (`uploads/…?v=`).
- Nginx: `location ^~ /assets/p/` dan `/assets/k/` → `Cache-Control: public, max-age=31536000, immutable`. Cache HTTP biasa menjadi lapis kedua untuk peramban tanpa SW.
- Folder `public/assets/p/` dan `k/` di-*gitignore* dan ikut backup. Berkas yang tidak dirujuk paket mana pun yang masih disimpan dihapus `gelita:paket:bersihkan`.

### 5.3 Manifest

`GET /paket/{package_code}.json` (statis, immutable) dan `GET /api/paket/aktif` (kecil, `no-store`, berisi `package_code` dan `manifest_sha256` paket rilis aktif):

```json
{
  "format": 1,
  "package_code": "pkt-2026-10-20-01",
  "release_code": "kelas5-2026",
  "app_version": "1ba4502",
  "content_version": "kelas5-1",
  "created_at": "2026-10-20T09:14:22+07:00",
  "total_bytes": 41234567,
  "groups": { "kode": 512340, "inti": 14012345, "wilayah-temanggung": 9876543, "...": 0 },
  "files": [
    { "url": "assets/p/3f9a1c0e7b2d4a55.jpg", "sha256": "3f9a1c0e…", "bytes": 412340,
      "type": "image/jpeg", "group": "inti", "key": "bg.welcome", "locale": null },
    { "url": "assets/p/9b1e…mp3", "sha256": "9b1e…", "bytes": 109233,
      "type": "audio/mpeg", "group": "wilayah-temanggung", "key": "narasi.dialog-temanggung-03", "locale": "en" },
    { "url": "assets/k/1ba4502/js/game.js", "sha256": "…", "bytes": 3311,
      "type": "text/javascript", "group": "kode", "key": "js/game.js", "locale": null }
  ]
}
```

- `manifest_sha256` = sha256 berkas manifest. Ia dicatat di tabel paket dan dikirim `/api/paket/aktif`, sehingga klien memverifikasi manifest yang diunduhnya.
- `key` dipakai untuk peta helper dan untuk laporan kelengkapan, tidak untuk mengambil berkas.

### 5.4 Hubungan dengan rilis dan `content_version`

| Konsep | Isi | Diubah oleh |
|---|---|---|
| `game_releases.app_version` | versi kode (commit) = folder `assets/k/{app_version}` | deploy (`deploy.sh`/`deploy.ps1`) |
| `game_releases.asset_version` | **`package_code`** paket aset (sekarang teks bebas; diberi arti ini) | admin menerbitkan paket (§6) |
| `game_releases.content_version` | versi isi database (teks, soal, kunci) — tetap seperti sekarang, kunci cache `ContentRepository` | impor bank soal, penyuntingan konten |
| Tabel baru `asset_packages` | `id`, `package_code`, `app_version`, `manifest_sha256`, `total_bytes`, `file_count`, `status` (`dibangun`/`terbit`/`ditarik`), `built_by`, `built_at`, `published_at`, `notes` | `gelita:paket:bangun`, panel Rilis |

Satu rilis = satu `app_version` + satu `package_code` + satu `content_version`. Rilis baru dibuat saat salah satunya berubah. Mengaktifkan rilis adalah satu-satunya cara paket baru sampai ke siswa (§6).

**Paket aset dibanding pembaruan kode.** Keduanya tercantum di manifest yang sama (grup `kode` menunjuk `assets/k/{app_version}`). Pembaruan kode tetap lewat deploy (jarang sesudah dibekukan). Deploy membangun ulang paket dengan aset yang sama, sehingga hanya grup `kode` yang berubah dan aset tidak diunduh ulang.

### 5.5 Verifikasi integritas

- Setiap berkas yang diunduh diverifikasi `crypto.subtle.digest('SHA-256')` terhadap manifest **sebelum** `cache.put`. Yang tidak cocok diulang sekali; bila masih tidak cocok, unduhan berhenti dengan galat yang bisa dilaporkan guru ("berkas rusak: {key}").
- `crypto.subtle` hanya ada di konteks aman. GELITA memang harus HTTPS (SW juga butuh HTTPS).
- Saat paket diaktifkan di perangkat, SW memeriksa bahwa setiap URL manifest ada di cache (tanpa hash ulang, demi kecepatan). Pemeriksaan hash penuh dijalankan ulang dari layar "Periksa paket" untuk guru.
- Server: `gelita:paket:bangun` menghitung hash dari berkas sumber, dan `gelita:paket:periksa` mencocokkan ulang berkas di `assets/p/` dengan manifest (dipanggil `gelita:content:verify --siap-penelitian`).

### 5.6 Unduhan terputus dan dilanjutkan

- Satuan lanjut = **berkas**. Berkas terbesar ±1,2 MB (musik); di jaringan acuan itu ±10 detik, sehingga lanjut di tengah berkas (`Range`) tidak sepadan.
- Unduhan berjalan 4 berkas paralel dari halaman (bukan dari SW, agar progres dan galat mudah ditampilkan; SW hanya melayani). Berkas yang sudah ada di cache dengan URL sama dilewati, jadi menutup tab lalu membuka lagi melanjutkan dari sisa.
- Cache aset bersama `gelita-aset` (berdasarkan isi) dipakai semua versi. Penanda paket aktif disimpan di cache meta `gelita-meta` (`aktif.json`: `package_code`, `manifest_sha256`, grup yang lengkap).
- Paket baru baru **aktif** di perangkat setelah semua grup `kode` + `inti` lengkap dan terverifikasi. Sampai itu, paket lama tetap dipakai (pergantian atomik). Berkas lama dihapus sesudah paket baru aktif dan tidak ada tab yang masih memakai paket lama (`clients.matchAll`).
- Galat jaringan: coba ulang dengan jeda 2, 4, 8 detik, lalu tampil tombol **Coba lagi**. Galat kuota (`QuotaExceededError`): unduhan berhenti dan perangkat masuk mode daring (§8.4).

---

## 6. Alur admin: membuat dan menerbitkan versi paket

### 6.1 Langkah admin

1. **Ganti aset seperti biasa.** Unggah gambar asli di **Gambar & mockup**, atau unggah rekaman pengganti di **Narasi** lalu setujui. Tidak ada yang berubah bagi siswa pada tahap ini, karena siswa memakai paket yang terbit.
2. **Bangun paket.** Di **Rilis → Paket aset → Bangun paket baru** (atau `php spark gelita:paket:bangun`):
   - kumpulkan media aktif (`media_assets`), narasi yang disetujui (`audio_assets` per locale), musik/efek, dan kode;
   - unduh salinan lokal gambar Pustaka yang masih berupa tautan Commons (§12 P4) sebagai `media_assets` biasa, kredit tetap tercatat;
   - salin ke `assets/p/` dan `assets/k/`, lalu tulis manifest;
   - tampilkan ringkasan: jumlah berkas, ukuran per grup, **berkas baru/berubah dibanding paket terbit**, ukuran unduhan bagi perangkat yang sudah punya paket lama, peringatan (gambar contoh tersisa, narasi draft, berkas melebihi anggaran §2.2).

   Status paket: `dibangun`. Membangun tidak mengubah apa pun bagi siswa.
3. **Pratinjau.** Admin membuka game dengan `?paket={package_code}` (hanya staf) untuk melihat paket itu sebelum terbit.
4. **Terbitkan.** **Buat rilis dengan paket ini**, lalu **Aktifkan** (alur rilis yang sudah ada di `StudyController::activateRelease`). Bila ada studi berstatus *berjalan*, layar meminta konfirmasi dan alasan yang dicatat di audit (§7.2).
5. **Tarik kembali.** Mengaktifkan rilis sebelumnya. Paket lama masih ada di server (tidak dihapus selama dirujuk rilis), dan perangkat yang masih menyimpannya tidak perlu mengunduh apa pun.

### 6.2 Siswa yang sedang bermain saat paket baru terbit

- Respons API membawa header `X-Gelita-Paket: {package_code aktif}`. Klien membandingkannya dengan paket di perangkat.
- **Sesi yang sedang berjalan tidak berganti paket.** Paket baru diunduh di latar (hanya berkas berubah), lalu diaktifkan pada **awal sesi berikutnya** (masuk/daftar), bukan di tengah wilayah atau tantangan. Siswa tidak melihat apa pun berubah di tengah permainan.
- Server menerima event dari paket lama selama sesi itu masih aktif. Sesi tetap tercatat dengan paket yang benar-benar dipakai (§7.1).
- Bila berkas paket lama sudah tidak ada di perangkat (digusur), SW mengambil berkas dari server, karena `assets/p/` lama tetap disimpan selama dirujuk rilis mana pun.

---

## 7. Integritas data penelitian

### 7.1 Setiap sesi dan event tahu versi paket yang dipakai

- Saat masuk/daftar, klien mengirim `package_code` dan `package_mode` (`lengkap` | `sebagian` | `daring`) bersama sinyal perangkat (`core/device.js`, Dok 1 §11.3). Server mengisi `game_sessions.app_client_version` (kolom sudah ada, belum pernah diisi) dengan `{app_version}+{package_code}`. Kolom baru `game_sessions.package_mode` ditambahkan.
- Bila perangkat terpaksa berganti paket di tengah sesi (paket lama tergusur dan server sudah tidak menyimpannya), klien mengirim event `package_changed` (`from`, `to`, `reason`). Event ini ditambahkan ke `Config\Gelita::$eventTypes` dan lembar Navigasi ekspor.
- Ekspor lembar Sesi menambah kolom `package_code` dan `package_mode`, ditambah Kamus Data. Ini perubahan pada §9.2 Dok 3; dikerjakan sesudah sesi ekspor Dok 3 selesai.

### 7.2 Semua siswa dalam satu studi memakai paket yang sama

- `research_studies` mendapat kolom `package_code` yang dikunci saat studi diubah menjadi *berjalan* (sama dengan paket rilis aktif saat itu). **Daftar siap penelitian** (Dok 3 §8.3) menambah butir "paket terbit, lengkap, dan terverifikasi (`gelita:paket:periksa`)".
- Selama studi *berjalan*, mengaktifkan rilis dengan paket lain **ditolak** kecuali admin mencentang konfirmasi dan menulis alasan (audit `release_activate` berisi `package_from`, `package_to`, `reason`). Data sebelum dan sesudahnya tetap terpisah lewat `app_client_version`.
- Sesi dalam mode `daring` memakai berkas dari server yang dilayani dengan peta paket aktif yang sama, jadi isinya identik dengan paket. Bedanya hanya kecepatan, dan itu tercatat di `package_mode`.

### 7.3 Event luring: urutan dan waktu yang benar

Paket tidak membuat game bisa luring (§1.3), tetapi siswa tetap bisa kehilangan jaringan sebentar di tengah layar. Perbaikan antrean yang diusulkan:

1. **`sequence_no` berlanjut lintas halaman**: disimpan di `sessionStorage` per `sessionTag`, bukan dari 1 di setiap halaman. Dengan M2 penghitung ini sudah berlanjut dengan sendirinya. Urutan dalam satu sesi lalu dapat disusun dari `(session, sequence_no)` tanpa bergantung pada jam.
2. **Koreksi jam klien**: setiap batch membawa `client_sent_at`. Server mencatat `clock_offset_ms = server_received_at − client_sent_at` per batch (kolom baru di `game_event_logs`, atau dalam payload bila skema tidak boleh diubah). `occurred_at` mentah tidak diubah; peneliti bisa menghitung waktu terkoreksi. Event yang lama tertahan di antrean tetap benar karena selisih dihitung saat batch dikirim, bukan saat event dibuat.
3. **Antrean tidak membuang event**: batas 500 entri di `storage.js` dinaikkan (event berukuran ±300 B; 5.000 entri ±1,5 MB) dan event tertua tidak lagi dibuang diam-diam. Bila batas tercapai, `toast` peringatan untuk guru tampil dan penghitung `dropped` ikut dikirim di batch berikutnya. Memindahkan antrean ke IndexedDB tidak perlu selama ukurannya di bawah kuota `localStorage` (±5 MB).
4. **Idempotensi tetap**: `client_event_id` sudah menjamin event tidak tergandakan.
5. Semua ini menyentuh `core/events.js` dan `core/storage.js`, yang juga diubah Dok 3 §7 (`page_hidden`/`page_visible`). Kerjakan sesudah bagian Dok 3 itu digabung.

---

## 8. Perangkat dan batasan peramban

Perilaku di bawah dicek Oktober 2026 dan bisa berubah. Sumbernya ada di §13.

### 8.1 Kuota dan penggusuran (eviction)

| Peramban | Kuota per origin | Penggusuran | Catatan untuk GELITA |
|---|---|---|---|
| Chrome / Edge (Windows, Android, ChromeOS) | ±60 % total disk [S1] | LRU per origin saat disk penuh; seluruh data origin dihapus bersama [S1] | 50 MB tidak masalah. `navigator.storage.persist()` disetujui otomatis berdasarkan riwayat interaksi, tanpa dialog [S1] |
| Safari 17+ (macOS 14 / iOS 17 / iPadOS 17 ke atas) | ±60 % disk untuk peramban dan aplikasi Layar Utama [S2] | LRU saat kuota total atau ruang sistem habis; **plus penggusuran proaktif**: data skrip (Cache Storage, IndexedDB, registrasi SW) dihapus bila origin tidak berinteraksi selama 7 hari pemakaian Safari [S1][S3] | Aplikasi di Layar Utama punya penghitung hari sendiri yang hanya berjalan saat aplikasi itu dipakai, jadi praktis tidak pernah mencapai 7 hari [S3]. `persist()` dikabulkan berdasarkan heuristik, misalnya bila berjalan sebagai aplikasi Layar Utama [S2] |
| Safari 16 ke bawah | ±1 GB awal, lalu meminta izin [S1] | sama | iPad lama: uji kuota sungguhan |
| Firefox | min(10 % disk, 10 GiB) mode biasa [S1] | LRU | `persist()` memunculkan dialog [S1]; tidak diminta otomatis |

Kebijakan:

- Minta `navigator.storage.persist()` sesudah unduhan paket berhasil (Chrome/Safari tanpa dialog). Jangan memintanya di Firefox kecuali guru menekan "Simpan permanen".
- **iPad: pasang di Layar Utama** (petunjuk iOS di `install.js` sudah ada). Di tab Safari biasa, paket bisa hilang bila iPad dipakai 7 hari untuk situs lain tanpa membuka GELITA, misalnya libur sekolah. Layar unduh menyarankan pemasangan di iPad yang belum terpasang.
- Sebelum setiap pelajaran, guru membuka **Periksa paket** (§11.2) di tiap perangkat. Bila paket hilang, unduh ulang sebelum siswa datang.

### 8.2 Perangkat bersama di lab sekolah

- Paket berisi **aset publik saja** (§9), jadi satu salinan di satu profil peramban dipakai semua siswa yang bergantian di perangkat itu. Ini justru menguntungkan: satu unduhan per perangkat, bukan per siswa.
- **Chromebook dengan sesi tamu terkelola atau mode ephemeral** menghapus semua data penjelajahan saat keluar, jadi paket hilang di setiap pergantian siswa (lihat [ringkasan Google tentang sesi tamu terkelola](https://services.google.com/fh/files/misc/cros_managed_guest_sessions_one_pager.pdf); perilaku Cache Storage di mode ini perlu diuji langsung). Pilihannya: (a) sekolah memakai akun perangkat biasa untuk lab GELITA, (b) mode daring di perangkat itu, atau (c) server sekolah lokal (di luar dokumen ini). Ini perlu dicatat di panduan sekolah (§12 P7).
- Windows dengan profil yang dibersihkan saat logoff (*mandatory/roaming profile*): sama dengan ephemeral.

### 8.3 Mode privat

- Chrome Incognito: kuota ±5 % disk, dan data hilang saat jendela ditutup [S1][S4]. Paket bisa diunduh, tetapi tidak bertahan.
- Safari Privat: kuota sangat kecil atau nol untuk sebagian API penyimpanan [S4]. Anggap paket tidak tersedia.
- Deteksi: unduhan uji kecil + `navigator.storage.estimate()`. Bila kuota < kebutuhan grup `inti` × 1,2 → mode daring dengan pesan "Mode privat: GELITA tetap bisa dimainkan, tetapi lebih lambat."

### 8.4 Ruang penyimpanan tidak cukup

- Sebelum mengunduh: `estimate()` → bila `quota − usage` < `total_bytes × 1,2`, unduh hanya `kode` + `inti` + wilayah yang sedang dimainkan, dan sisanya daring (`package_mode = sebagian`).
- Bila `QuotaExceededError` terjadi di tengah unduhan: berhenti, pertahankan yang sudah lengkap, dan tampilkan pesan untuk guru ("Ruang penyimpanan perangkat ini kurang ±{n} MB"). Permainan tetap jalan.
- Kuota dihitung dari total disk, bukan ruang kosong [S1], sehingga `estimate()` bisa terlalu optimistis di perangkat yang hampir penuh. Karena itu galat kuota saat menulis tetap harus ditangani walau `estimate()` lolos.

### 8.5 iPhone tanpa Fullscreen API

- iPhone Safari tidak mendukung `Element.requestFullscreen()` untuk elemen selain `<video>` (iPad mendukung) [S5]. Dok 3 §1 sudah menyembunyikan tombol **Layar penuh** bila tidak didukung.
- Jalan satu-satunya di iPhone adalah **pasang di Layar Utama**. Manifest `display: fullscreen` diperlakukan iOS sebagai `standalone` (tanpa bilah Safari), dan sekaligus mendapat kuota serta penghitung 7 hari aplikasi Layar Utama (§8.1). Layar unduh di iPhone menampilkan petunjuk pemasangan lebih menonjol.
- Ponsel bukan perangkat utama penelitian (Dok 3 §1); iPhone cukup "berjalan", tidak harus memenuhi S1–S4.

### 8.6 Peramban tanpa service worker

Peramban sangat lama atau kebijakan sekolah yang mematikan SW: game berjalan seperti sekarang (`package_mode = daring`). URL paket tetap immutable, jadi cache HTTP biasa tetap membantu sesudah kunjungan pertama.

---

## 9. Keamanan dan privasi

### 9.1 Invarian baru pengganti `InstallableAppTest`

Invarian sekarang adalah "SW hanya menyimpan `offline.html`". Invarian baru:

1. SW **hanya** menyimpan URL yang tercantum di manifest paket (`assets/p/*`, `assets/k/*`) dan `offline.html`. Semua penulisan cache lewat satu fungsi yang menolak URL di luar daftar itu.
2. SW **tidak pernah** menyimpan navigasi (HTML), apa pun di bawah `/api/`, `/admin`, `/masuk`, `/daftar`, `/keluar`, POST, respons dengan `Set-Cookie`, atau `Cache-Control: private/no-store`.
3. SW meneruskan `/admin*` dan `/api/*` langsung ke jaringan tanpa `respondWith`, sehingga perilaku admin tidak berubah sama sekali.
4. Isi paket tidak memuat data siswa. Aset di `media_assets` dan `audio_assets` adalah konten publik yang sekarang pun dilayani tanpa login dari `public/assets/`. Paket tidak menambah paparan.

`InstallableAppTest` diganti dengan `AssetPackageServiceWorkerTest`, yang memeriksa butir 1–3 secara statis pada `sw.js` (pola URL yang diizinkan, tidak ada `cache.put` di jalur navigasi, `/api` dan `/admin` dilewati). Uji Playwright (§11.3) memeriksanya saat berjalan: sesudah satu sesi bermain, `caches.keys()` + isi setiap cache hanya berisi URL manifest.

### 9.2 Halaman yang butuh sesi masuk

- HTML semua layar tetap diambil dari jaringan dengan cookie sesi. Penjaga `participantAuth`, `gameSession`, dan `ScreenViewFilter` tetap bekerja seperti sekarang.
- Prefetch HTML (M2) hanya ke rute di daftar putih (§4.3), disimpan di memori tab, dan dibuang saat `keluar` atau saat respons berupa redirect ke `/masuk`.
- Saat keluar (`/keluar`): memori tab dibersihkan. Antrean event sudah terpisah per `sessionTag`. Cache paket **tidak** dihapus karena tidak berisi data pribadi; menghapusnya hanya memaksa siswa berikutnya mengunduh ulang.
- Pratinjau paket untuk staf (`?paket=`) dicek di server (sesi staf) dan tidak pernah mengubah paket aktif perangkat siswa.

### 9.3 Lain-lain

- Manifest dan berkas paket di-hash (§5.5); HTTPS wajib.
- Header `X-Gelita-Paket` hanya berisi kode paket, tidak ada identitas.
- Menyalin gambar Commons ke server menghapus permintaan langsung ke Wikimedia dari perangkat siswa (privasi lebih baik). Kredit dan lisensi tetap ditampilkan.

---

## 10. Tahapan implementasi

Besar pekerjaan dalam hari-orang (perkiraan kasar, termasuk uji). "Sebelum Dok 3" berarti tidak menyentuh berkas yang sedang dirombak tiga sesi Dok 3 (view game, `stage.css`, `core/stage.js`/`modal`/`dom`/`events`, admin, ekspor).

| Tahap | Isi | Hari | Ketergantungan | Kapan |
|---|---|---:|---|---|
| **0. Ukur dasar** | Skrip `tools/qa/nav-timing.mjs` (Playwright, jaringan acuan §1.2): waktu pindah layar, ganti bahasa, jumlah permintaan dan byte per layar. Angka ini dicatat di `CATATAN_IMPLEMENTASI.md` | 1–2 | — | sebelum Dok 3; diulang sesudah Dok 3 |
| **1. Pembangun paket (server)** | `asset_packages`, `gelita:paket:bangun/periksa/bersihkan`, folder `assets/p` + `assets/k`, peta kunci → URL di helper media, aturan Nginx immutable, `/api/paket/aktif`, salinan lokal Pustaka (bila P4 disetujui) | 4–5 | migration baru (tidak bentrok); helper `media_src` hanya mengganti URL | **sebelum Dok 3** (logika). UI admin di Tahap 6 |
| **1b. Kodekan ulang narasi** (bila P2 disetujui) | `tools/audio`: narasi 48 kbps mono, perbarui `produksi.json` (sha256) | 0,5 | — | kapan saja |
| **2. Service worker paket** | `sw.js` baru (cache-first `assets/p|k`, respons 206 untuk `Range`, daftar putih, pergantian atomik), pengunduh di halaman (paralel 4, verifikasi sha256, lanjut per berkas, kuota, mode daring), `AssetPackageServiceWorkerTest` | 4–6 | Tahap 1 | logika **sebelum Dok 3**; diuji di layar sekarang |
| **3. Layar "Siapkan GELITA" dan Periksa paket** | layar unduh berprogres per grup (dua bahasa), petunjuk pasang aplikasi, layar periksa untuk guru, tirai grup wilayah | 2–3 | Dok 3 §4 (tata letak halaman awal) | **sesudah Dok 3 §4** |
| **4. Transisi, decode, tirai pendek** | `@view-transition` + nama transisi di panggung/HUD/latar, `data-next-assets` + `img.decode()`, `curtain.js` minMs saat aset lokal, teks JS dua bahasa di `js_config()` | 2–4 | Dok 3 §3–§7 (struktur `#stage`, `core/curtain.js`) | **sesudah Dok 3** |
| **5. Integritas data** | `app_client_version` + `package_mode`, `package_changed`, `sequence_no` lintas halaman, `client_sent_at`/`clock_offset_ms`, antrean tanpa buang, kolom ekspor Sesi | 2–3 | Dok 3 §7 (`core/events.js`) dan §9 (ekspor) | **sesudah Dok 3 §7 dan §9** |
| **6. Panel Rilis → Paket aset** | bangun, ringkasan diff, pratinjau staf, terbitkan, kunci paket per studi, butir daftar siap penelitian | 2–3 | Dok 3 §8 (gaya admin baru) | **sesudah Dok 3 §8** |
| **7. (Opsional) Navigasi dalam panggung + ganti bahasa seketika** | M2/L2: penukar isi `#stage`, siklus hidup modul halaman, prefetch aman + perubahan `ScreenViewFilter`, riwayat/fokus | 8–15 | Tahap 4 + hasil ukur Tahap 0/4; keputusan P1 | sesudah semuanya, **hanya bila diputuskan** |

Jumlah Tahap 0–6: **±18–27 hari-orang**. Tahap 7 menambah 8–15 hari.

Urutan yang aman selama Dok 3 berjalan: Tahap 0 → 1 → 1b → 2 (dengan `sw.js` dan pengunduh yang belum dipanggil UI mana pun, diaktifkan lewat `gelita.paket.aktif=false` di `.env`). Sesudah Dok 3 digabung: 3 → 4 → 5 → 6, lalu ukur ulang dan putuskan Tahap 7.

---

## 11. Risiko, kriteria selesai, dan rencana uji

### 11.1 Risiko

| Risiko | Dampak | Penanganan |
|---|---|---|
| Unduhan 30 perangkat bersamaan di awal pelajaran memenuhi Wi-Fi sekolah | pelajaran tertunda | unduh paket di jam sebelumnya (layar Periksa paket untuk guru); hanya `inti` yang wajib; berkas immutable bisa dicache proksi sekolah |
| Safari menggusur paket (7 hari, tab biasa) | unduh ulang tak terduga | pasang di Layar Utama, `persist()`, cek sebelum pelajaran (§8.1) |
| Chromebook ephemeral | unduh setiap siswa | panduan sekolah; mode daring (§8.2) |
| SW bermasalah membuat game macet di semua perangkat | game tidak terbuka | SW tidak pernah menangani navigasi selain cadangan `offline.html`; sakelar `.env` `gelita.paket.aktif`; SW "pembunuh" (`sw.js` yang mendaftar ulang dirinya kosong) disiapkan di repo |
| Respons 206 salah untuk audio di Safari | narasi tidak berbunyi | uji di iPad sungguhan; cadangan: narasi tanpa SW (lewati cache untuk `audio/*`) bila uji gagal |
| Paket aktif berbeda antarperangkat dalam satu studi | data tidak sebanding | kunci paket per studi, `app_client_version` di setiap sesi (§7) |
| Prefetch HTML memicu efek samping atau `screen_viewed` palsu | data navigasi tercemar | daftar putih + header prefetch + uji `ScreenViewFilterTest` (§4.3); hanya di Tahap 7 |
| Memori iPad habis karena gambar ter-decode menumpuk | tab dimuat ulang | batas satu layar ke depan (§4.5) |
| Bentrok dengan tiga sesi Dok 3 | konflik merge | jadwal §10; Tahap 1–2 tidak menyentuh berkas Dok 3 |
| Lisensi saat menyalin gambar Commons | kredit hilang | `credit` disalin dari `library_media`; tampil sama seperti sekarang |

### 11.2 Kriteria selesai (Tahap 0–6)

- [ ] `gelita:paket:bangun` menghasilkan manifest sesuai §5.3; `gelita:paket:periksa` lolos; berkas immutable dilayani Nginx dengan `max-age=31536000, immutable`.
- [ ] Ukuran paket penuh ≤ S5 dan `inti` ≤ S6 pada paket pertama dengan gambar asli (atau selisihnya dicatat dan disetujui pemilik).
- [ ] Unduh `inti` ≤ S7 dan penuh ≤ S8 di jaringan acuan; unduhan yang diputus di 50 % lalu dibuka lagi melanjutkan tanpa mengunduh ulang berkas yang sudah ada.
- [ ] Paket baru yang hanya mengganti satu gambar mengunduh tepat satu berkas aset (+ manifest).
- [ ] Sesudah paket lengkap: 0 permintaan jaringan untuk aset saat berpindah di semua layar Dok 3 §4 (hanya HTML dan API); S1–S4 versi Tahap 4 terpenuhi di perangkat acuan.
- [ ] Mode daring (Incognito, kuota kecil, SW dimatikan) tetap bisa memainkan satu wilayah penuh.
- [ ] Setiap sesi uji punya `app_client_version` dan `package_mode`; ekspor Sesi memuatnya; event luring (jaringan dimatikan 2 menit di tengah tantangan) terkirim lengkap tanpa duplikat dengan `sequence_no` naik terus dan `clock_offset_ms` terisi.
- [ ] `AssetPackageServiceWorkerTest` hijau; pemeriksaan Playwright menunjukkan cache hanya berisi URL manifest dan `offline.html`, tanpa HTML atau `/api`.
- [ ] `docs/05_VIEW_UI.md`, `docs/06_JAVASCRIPT.md`, `docs/07_FEATURE_INTEGRATION.md`, dan `docs/08_DEPLOYMENT.md` diperbarui (paket, Nginx, panduan sekolah).

### 11.3 Rencana uji

**Otomatis (PHPUnit):**

- `AssetPackageBuilderTest`:
  - manifest memuat semua media aktif dan narasi yang disetujui per locale;
  - hash dan ukuran benar, URL berdasarkan isi;
  - berkas sama di dua versi → URL sama;
  - gambar contoh dan narasi draft memunculkan peringatan;
  - grup sesuai §5.1.
- `AssetPackageHelperTest`: `media_src()`/`media_key_src()` mengembalikan URL paket saat paket aktif dan URL lama saat belum ada paket.
- `AssetPackageServiceWorkerTest`: menggantikan `InstallableAppTest::testServiceWorkerOnlyCachesTheOfflinePage` (§9.1). Uji manifest/ikon lainnya tetap.
- `ReleasePackageLockTest`: aktivasi rilis dengan paket lain saat studi berjalan ditolak tanpa konfirmasi; audit berisi `package_from`/`package_to`.
- `EventOrderingTest`: `clock_offset_ms` dari `client_sent_at`; `sequence_no` diterima apa adanya; `package_changed` diterima dan diekspor.
- (Tahap 7) `ScreenViewFilterTest`: permintaan ber-`Sec-Purpose: prefetch` tidak menulis `screen_viewed`; rute bukan daftar putih menolak prefetch.

**Otomatis (Playwright, `tools/qa/`):**

- `nav-timing.mjs` (Tahap 0, diulang tiap tahap): jaringan acuan (1 Mbps, RTT 150 ms), Chromium; ukur S1–S4 di semua layar Dok 3 §4; simpan JSON + ringkasan p50/p95 ke `writable/qa/nav/`.
- `package-check.mjs`:
  - unduh paket, putus jaringan di tengah, lanjutkan;
  - verifikasi isi cache;
  - mainkan satu wilayah tanpa permintaan aset;
  - mode Incognito → `package_mode = daring`;
  - kuota kecil (`--storage-quota` di Chromium) → `sebagian`;
  - ganti paket di server di tengah sesi → paket lama tetap dipakai sampai sesi berikutnya.

**Manual:**

- iPad (Safari tab dan Layar Utama), Chromebook (akun biasa dan sesi tamu), Android tablet, laptop Windows, iPhone (Layar Utama): unduh, mainkan satu wilayah, ganti bahasa di tiap jenis layar, narasi berbunyi dari cache (206).
- Lab: 10+ perangkat mengunduh bersamaan dari satu titik akses; catat waktu dan beban server.
- Ulangi `nav-timing` di perangkat sungguhan dengan Wi-Fi sekolah nyata.

---

## 12. Pertanyaan terbuka untuk pemilik

| # | Pertanyaan | Usulan |
|---|---|---|
| P1 | Apakah "seketika" berarti S2/S4 ≤ 400 ms (Tahap 4, MPA + paket + transisi) cukup, atau harus ≤ 150 ms (Tahap 7, navigasi dalam panggung, +8–15 hari)? | putuskan sesudah angka Tahap 0 dan Tahap 4 ada |
| P2 | Narasi dikodekan ulang dari 96 ke 48 kbps mono (−12 MB)? Untuk ucapan anak, 48 kbps MP3 biasanya masih jernih, tetapi perlu didengar dulu | ya, setelah uji dengar 5 berkas per bahasa |
| P3 | Format gambar: tetap JPEG/PNG (katalog §10.1 menghindari WebP demi iPad lama), atau izinkan WebP? WebP didukung Safari sejak iOS/iPadOS 14 ([caniuse](https://caniuse.com/webp)) | tetap JPEG/PNG sampai daftar perangkat sekolah diketahui |
| P4 | Pustaka: (a) salin 43 gambar Commons ke server saat paket dibangun? (b) Lima video YouTube: tetap daring (butuh internet saat diputar), atau ganti MP4 lokal (menambah puluhan MB, perlu izin lisensi)? | (a) ya; (b) tetap daring dengan pesan bila luring |
| P5 | Tombol bahasa di tengah tantangan: sembunyikan, atau izinkan (perlu Tahap 7 agar isian tidak hilang)? | sembunyikan sampai Tahap 7 |
| P6 | Prefetch HTML (Tahap 7) memerlukan `screen_viewed` untuk layar hasil prefetch dicatat lewat token yang dibuat server. Boleh mengubah aturan Dok 1 catatan 22 dengan cara itu? | boleh, dengan token sekali pakai (§4.3 butir 2) |
| P7 | Perangkat lab dengan profil ephemeral: wajibkan sekolah memakai akun biasa, atau terima mode daring di perangkat itu? | terima mode daring dan catat `package_mode`; panduan sekolah menyarankan akun biasa |
| P8 | Tirai cerita: dipersingkat (600 ms) bila aset sudah lokal, atau tetap 2,5 detik sebagai jeda dramatik? | pendekkan tirai antarlayar; pertahankan tirai cerita pembuka |
| P9 | Kode: folder per versi (`assets/k/{app_version}/`), atau import map + nama ber-*hash* per berkas (unduhan delta untuk kode)? Kode hanya 0,5 MB | folder per versi (lebih sederhana) |
| P10 | Paket boleh diganti selama studi *berjalan* dengan konfirmasi, atau dilarang sama sekali (paket baru = studi baru)? | boleh dengan konfirmasi + alasan teraudit |
| P11 | Unduhan penuh wajib sebelum Mulai, atau cukup `inti` lalu sisanya di latar? | cukup `inti` |

---

## 13. Sumber perilaku peramban

Dicek 7 Oktober 2026. Perilaku peramban berubah; periksa ulang sebelum Tahap 2 dan sebelum uji lapangan.

- [S1] MDN, *Storage quotas and eviction criteria* — <https://developer.mozilla.org/en-US/docs/Web/API/Storage_API/Storage_quotas_and_eviction_criteria> (kuota Chrome/Firefox/Safari, LRU, penggusuran proaktif Safari 7 hari, `persist()`, kuota dari total disk).
- [S2] WebKit, *Updates to Storage Policy* (Safari 17) — <https://webkit.org/blog/14403/updates-to-storage-policy/> (60 %/80 % untuk peramban dan aplikasi Layar Utama, heuristik `persist()`).
- [S3] Search Engine Land, *What Safari's 7-day cap on script-writeable storage means for PWA developers* — <https://searchengineland.com/what-safaris-7-day-cap-on-script-writeable-storage-means-for-pwa-developers-332519> (penghitung hari terpisah untuk aplikasi Layar Utama).
- [S4] RxDB, *IndexedDB max storage limit* — <https://rxdb.info/articles/indexeddb-max-storage-limit.html> (Chrome Incognito ±5 % disk; Safari Privat). Sumber sekunder; uji langsung di Tahap 2.
- [S5] Apple Developer Forums, *Fullscreen API on a non-video element* — <https://developer.apple.com/forums/thread/133248> (iPhone hanya mendukung layar penuh untuk `<video>`).
- Chrome for Developers, *View Transition API* — <https://developer.chrome.com/docs/web-platform/view-transitions> (lintas dokumen: Chrome 126+, Safari 18.2+).
- web.dev, *Handle range requests in a service worker* — <https://web.dev/articles/sw-range-requests> (respons 206 dari cache).
- caniuse, *Background Fetch* — <https://caniuse.com/wf-background-fetch> (hanya Chromium); *WebP* — <https://caniuse.com/webp>.
- Google, *ChromeOS managed guest sessions* — <https://services.google.com/fh/files/misc/cros_managed_guest_sessions_one_pager.pdf> (data dihapus di akhir sesi).
