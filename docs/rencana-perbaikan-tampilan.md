# Rencana: pembaruan tampilan GELITA — avatar, profil lebar, katalog gambar, animasi frame, pin/lentera kota, latar loading kota, toast gaya game, ramah sentuh

> Status: **rencana, belum dikerjakan** (disusun 29 September 2026). Jalankan per fase; keputusan pengguna sudah tercantum di bagian Konteks.

## Konteks

Permintaan pengguna: nomor 1–7 dan 9. Nomor 8 tidak ada. Temuan dari penelusuran kode:

| # | Keadaan sekarang | Bukti |
|---|---|---|
| 1 Avatar | Belum ada; yang tampil hanya huruf pertama nama. Tabel `participants` tidak punya kolomnya. | `game/profile.php:36`, `components/hud.php:43`, `Participant::toSafeArray()` (`app/Entities/Participant.php:70`) |
| 2 Profil | Lebar dibatasi `.screen-medium { max-width: 820px }`. Kelas ini dipakai 8 layar lain, jadi jangan diubah. | `public/assets/css/layout.css:91`, `profile.php:34` |
| 3 Aset | Slot `media_assets` sudah ada, tetapi daftarnya ditulis di 4 tempat (lihat catatan 3a). Ikon PWA dan favicon masih berkas statis yang dilacak git, padahal deploy memakai `git pull --ff-only`, jadi admin tidak boleh menimpanya. Slot `ui.placeholder` terdaftar tetapi tidak pernah dibaca. Latar per slide cerita (`dialogues.background_media_id`) tidak bisa diisi dari admin. Pratinjau di admin tidak bisa diperbesar, dan frame tokoh tidak diberi pratinjau. | `content_helper.php:136`, `admin/media/index.php:139`, `partials/cine-slides.php:27` |
| 4 Frame | Pemutar frame tidak pernah dibuat. `$characterAnimations` (frames/fps/loop) dan slot `char.*.{1..3}` ada, tetapi semua penampil memakai frame `.1` saja, dan `fps`/`loop` tidak dibaca di mana pun. Pose Mbah Kedu `happy` tidak sah (lihat catatan 4a). | `components/character.php:18`, `character_frame_src()`, `dialogue.js setPose()` |
| 5 Pin/lentera | SVG `icon()`, belum ada penggantian per kota | `map-kedu.php:144` (pin), `:154` (lentera Kenali), `lantern.php:24`, `curtain.php:92` |
| 6 Latar loading | **Belum ada pengaturan.** Tirai "Menuju {wilayah}…" hanya gradien dan jejak kaki; `bg.loading` hanya dipakai tirai "Membuka Peta Kedu". | `curtain.php:67` |
| 7 Toast | Pojok kanan bawah, bertumpuk, tanpa ikon di JS, tidak bisa ditutup. Di ponsel mendatar toast menutupi tombol Periksa. Flash "sandi berhasil diganti" hilang di redirect `/mulai`→`/gerbang`, dan pesan wajib ganti sandi tampil ganda. | `core/toast.js`, `components/toast.php`, `layout.css:127` |
| 9 Sentuh | Temuan audit ada di Fase F | — |

Catatan untuk tabel:

- **3a.** Keempat daftar slot itu: `MediaAssetSeeder::SLOTS`, `MediaUsage::SLOTS`, `AssetChecklist::UI_SLOTS`, dan `Config\Gelita::$assetSizes`.
- **4a.** Pose `happy` diminta untuk Mbah Kedu di `reflection.php:26` dan `challenge-finished.php:90`. Pose Mbah Kedu yang sah adalah `smile`.

**Keputusan pengguna:**

- Avatar dipilih di Profil dan di form daftar.
- Satu gambar pin per kota, dengan tanda status otomatis dan cadangan pin global.
- Ikon aplikasi diunggah per ukuran; tidak ada generator.

**Dipakai ulang:**

- `MediaStore::store()`: upsert, hapus berkas lama, flush cache, audit.
- `media_key_src()`: peta ber-cache.
- `AssetChecklist`.
- `ContentController::mediaFields()` dan komponen `media-field`.
- `curtain_attrs()` dan `GameProgress::regionCurtain()`.
- `core/dom.js` (`reducedMotion`, `el`, `$$`).
- Lightbox `<dialog>` di `game/library.js:156-191`.
- Pola radio-tile Balai Refleksi (`game.css:1410`).
- Pola pointer-capture di `admin/object-picker.js:63-78`.
- `safe_internal_url()`.
- Pola `<template data-modal-icon>` untuk ikon di JS.

Pekerjaan dilakukan di branch `claude/quirky-bardeen-fbaaoh`, satu commit per fase. Uji dijalankan tiap fase, lalu push.

---

## Fase A — Semua gambar bisa diganti dari admin (#3)

1. **Katalog tunggal `app/Libraries/ImageSlots.php`.**
   - Isinya grup → slot. Setiap slot punya kunci, label, keterangan "tampil di", ukuran, jenis berkas, dan penggantinya.
   - Kode wilayah diambil dari `Config\Gelita::$levelPrefixes`.
   - Kunci baru ditambahkan juga ke `MediaAssetSeeder::slots()`, `MediaUsage::SLOTS`, dan `$assetSizes`.
   - `tests/unit/ImageSlotsTest.php` memastikan setiap kunci katalog punya label di `MediaUsage`, slot seeder, dan ukuran di `$assetSizes` bila ukurannya wajib. Dengan begitu daftar-daftar itu tidak bisa berbeda lagi.
   - Slot per kota yang terhubung lewat FK (latar, peta, lencana wilayah, latar slide cerita) tampil dengan pratinjau dan tautan "Ubah di editor wilayah/dialog". Slot ini tidak diunggah lewat kunci, karena FK bisa menunjuk kunci lain.

   | Grup | Slot (baru dicetak **tebal**) | Ukuran |
   |---|---|---|
   | Ikon aplikasi & logo | **`app.favicon`** PNG, **`app.favicon-svg`** SVG (opsional), **`app.apple-touch`**, **`app.icon-192`**, **`app.icon-512`**, **`app.icon-maskable`**, `ui.logo-hero`, `ui.logo` | 48², —, 180², 192², 512², 512² (zona aman 80%) |
   | Ikon permainan | **`ui.pin`**, **`ui.lantern`** (HUD, tirai peta, cadangan Kenali), `ui.btn-start(.en)`, **`ui.placeholder`** (kini benar-benar dipakai) | 256², 256² |
   | Latar layar | `bg.welcome`, `bg.auth`, `bg.intro`, `bg.map`, `bg.reflection`, `bg.loading` | 1920×1080 |
   | Peta & kota | `map.kedu`, lalu per kota: **`map.pin.{kode}`**, **`map.lantern.{kode}`**, **`bg.loading.{kode}`**, serta latar, peta, dan lencana wilayah (FK `levels`) | —, 256², 256², 1920×1080 |
   | Tokoh | `char.{jaka|kedu}.{pose}.{1..3}` | 700×900 |
   | Avatar | **`avatar.1` … `avatar.10`** | 512² |
   | Cerita | Latar per slide `intro`/`ending` (FK `dialogues`) | 1920×1080 |

2. **Aturan jenis berkas per slot.** Slot `app.*` hanya menerima PNG, kecuali `app.favicon-svg` yang hanya menerima SVG. Dicek di `MediaStore` di samping cek ukuran yang sudah ada.

3. **Halaman "Tampilan permainan" `GET /admin/media/tampilan`.**
   - Dibuat lewat `MediaController::appearance()` dan `admin/media/appearance.php`.
   - Muncul sebagai sub-menu di bawah Media (`admin-sidebar.php`), dan ditautkan dari `/admin/media` serta Kelengkapan.
   - Isinya grid kartu per grup. Setiap kartu memuat:
     - pratinjau **kecil** 96×72 (`object-fit: contain`, latar kotak-kotak untuk PNG transparan);
     - label, "tampil di", kode, ukuran wajib;
     - status: ada, memakai pengganti, atau ukuran salah;
     - form kecil **Ganti gambar** yang POST ke `admin/media/unggah`;
     - tombol **Pakai bawaan** yang POST ke `…/nonaktif`.
   - Kedua aksi itu kini menerima `back` (hanya `admin/media` atau `admin/media/tampilan#slot-…`) dan kembali ke kartunya.
   - Grup Tokoh menampilkan 3 frame beserta pratinjau animasinya; pratinjau ini memakai `core/sprite.js` dari Fase D.
   - Semua berjalan tanpa JavaScript.

4. **Klik pratinjau untuk ukuran penuh.**
   - Pindahkan `initZoom()` dari `game/library.js` ke `core/lightbox.js` (delegasi `[data-zoom]`), dan pindahkan CSS `.lightbox*` ke `components.css`.
   - Dipakai di Pustaka (perilaku sama), kartu Tampilan, tabel `/admin/media` (termasuk `sprite_frame`), dan `components/media-field.php`.
   - Tanpa JavaScript, tautan membuka gambarnya langsung.

5. **Ikon aplikasi dinamis.**
   - Manifest:
     - Controller baru `Game\AppController::manifest()` melayani `GET manifest.json`, di URL yang sama supaya aplikasi yang sudah terpasang tetap diperbarui.
     - Isi dasarnya dipindah dari `public/manifest.json`; berkas statis itu dihapus. Nginx L/W dan `.htaccess` sudah meneruskan berkas yang tidak ada ke `index.php`.
     - Ikon memakai slot aktif dengan `?v={sha256:10}`. Bila slot kosong, dipakai berkas statis `assets/app/*.png`.
     - Header `application/manifest+json`.
   - Layout:
     - `app/Helpers/ui_helper.php` mendapat helper `app_icon_src($slot, $fallback)`, dipakai di ketiga layout untuk `<link rel=icon>` (PNG dan SVG) dan `apple-touch-icon`.
     - `public/favicon.ico` tetap sebagai cadangan.
   - `offline.html` tetap memakai SVG inline karena harus berjalan tanpa jaringan; hal ini dicatat di docs.

6. **Pengganti sementara.**
   - `media_src()` memakai `ui.placeholder`, lalu `assets/ui/placeholder.svg`.
   - Cek `str_ends_with(…'placeholder.svg')` di `GameProgress:308` diganti helper `media_is_placeholder()`.

7. **Latar per slide cerita pembuka & penutup.**
   - `admin/content/dialogues.php` (konteks `intro`/`ending`) mendapat `media-field` per slide, dan form-nya diubah menjadi multipart.
   - `ContentController::saveDialogues()` memanggil `mediaFields()` per baris.

8. **`bg.auth` benar-benar dipakai.** Halaman masuk, daftar, persetujuan, dan ganti sandi siswa memakai `bg.auth ?? bg.welcome`, sesuai labelnya.

## Fase B — Avatar (#1) dan profil lebar penuh (#2)

1. **Migration `2026-01-01-004000_AddParticipantAvatar.php`.**
   - Menambah `participants.avatar_no TINYINT UNSIGNED NULL`, mengikuti pola `003600`.
   - Ikut diubah: `ParticipantModel::$allowedFields` dan `toSafeArray()`.
   - Kolom ini tidak diekspor dan tidak dicatat sebagai event penelitian.
2. **Konfigurasi dan helper.**
   - `Config\Gelita::$avatarSlots = 10`.
   - Helper `avatar_choices()` mengembalikan hanya slot `avatar.N` yang aktif; helper `avatar_src(?int)`.
   - Komponen `components/avatar.php`: gambar bila ada, selain itu monogram. Dipakai di HUD `.user-avatar` dan profil `.profile-avatar`.
3. **Komponen `components/avatar-picker.php`.**
   - Grid lingkaran ≥64px: "Inisial" ditambah avatar aktif. Pilihan ditandai cincin emas dan ✓, bukan warna saja.
   - Bila belum ada avatar yang diunggah, komponen tidak dirender.
   - **Form daftar:** mode radio (opsional) di langkah 1. `RegisterController::store()` memvalidasi `permit_empty|in_list[…aktif]`, dan `old()` dipertahankan.
   - **Profil:** mode tombol-kirim, jadi satu ketukan langsung menyimpan.
     - Route: `POST profil/avatar`, masuk grup `gameSession`.
     - Ditangani `ProfileController::avatar()`, lalu flash `message` "Avatar tersimpan" dan kembali ke `/profil`.
   - Teks baru ada di `app/Language/{id,en}/Game.php`, dan tanpa JavaScript semuanya tetap berjalan.
4. **Profil lebar penuh.**
   - `profile.php` memakai `screen screen-full profile`, dan `layout.css` mendapat `.screen-full { max-width: none }`.
   - Pada `≥1100px`, grid dua kolom `minmax(320px, 400px) minmax(0, 1fr)`:
     - kiri: kartu identitas, pemilih avatar, lencana;
     - kanan: statistik (auto-fit, jadi lebih banyak kolom) dan tabel riwayat.
   - Ponsel dan layar sempit tetap satu kolom.

## Fase C — Pin & lentera per kota (#5), latar loading per kota (#6)

1. **Pin kota.**
   - Urutan sumber: `map.pin.{kode}` → `ui.pin` → SVG yang sekarang, lewat helper `region_art_src('pin'|'lantern', $kode)`.
   - Bila ada gambar, `.pin-icon.has-art` memakai `object-fit: contain` tanpa lingkaran emas.
   - Status ditandai otomatis:
     - terkunci: `grayscale` ditambah lencana 🔒 kecil;
     - tuntas: lencana ★;
     - terbuka: denyut `pin-pulse`.
2. **Lentera Kenali.**
   - Urutan sumber: `map.lantern.{kode}` → `ui.lantern` → SVG. Dipakai di `.kenal-btn-face` dan `.kenal-card-btn`.
   - `ui.lantern` juga dipakai lentera HUD (tahap nyala `flame-0..3` diterapkan dengan `drop-shadow`) dan lentera tirai peta.
   - **Perbaikan tumpang tindih:** area sentuh 44px tombol Kenali sekarang menutupi pojok pin. Tombol itu digeser ke luar lingkaran pin (`--pin-size`) agar ketukan di pin tidak membuka Kenali.
3. **Latar loading per kota.**
   - `regionCurtain()` menambah `'bg' => bg.loading.{kode} ?? bg.loading` dan menaruhnya pertama di `preload`.
   - `curtain_attrs()` mendapat parameter `?string $bg` yang menghasilkan `data-curtain-bg`.
   - Template `region` mendapat `<img class="curtain-bg" hidden>`. `curtain.js` mengisinya dan memudarkannya masuk setelah `load`.
   - Keempat pemanggil ikut mengoper `bg`: `map-kedu.php:49`, `region-intro.php:86`, `region-done.php:29`, `challenge-finished.php:120`.
   - `map.js preload()` memuat latar loading kota yang terbuka saat idle, jadi tirai ±1,8 detik langsung bergambar.
   - Tirai tantangan (`mission-brief.php:74`) memakai latar loading kotanya juga.
4. **Editor wilayah** (`admin/content/level.php`) mendapat 3 `media-field` berbasis kunci: Pin peta, Ikon lentera Kenali, Latar tirai pemuatan. Tidak perlu kolom FK baru.

## Fase D — Animasi frame tokoh (#4)

1. **Server.**
   - Helper `character_frames($who, $pose)` mengembalikan `{frames[], fps, loop}`. Hanya frame aktif yang diambil, dengan cadangan pose `idle`.
   - `character.php` menambahkan `data-frames`, `data-fps`, dan `data-loop` bila ada ≥2 frame.
   - `dialogue-scene.php` menambahkan `data-pose-frames`/`-fps`/`-loop` per baris. `data-pose-src` tetap frame 1, jadi tes lama tetap berlaku.
2. **`core/sprite.js` (baru).**
   - Cara kerja:
     - Menukar `src` pada `.character-img` yang sudah ada tanpa mengganti node, sehingga `cine-rise` tidak diputar ulang dan pencerminan sisi kanan tetap.
     - Frame dipramuat dengan `decode()` sebelum siklus dimulai.
     - `loop:false` diputar sekali lalu berhenti di frame terakhir.
   - Animasi dijeda saat `document.hidden`, saat slide bukan `.is-current`, dan saat figur di luar layar (IntersectionObserver).
   - Dengan `reducedMotion()`, hanya frame 1 yang tampil.
3. **Pemasangan.**
   - `game.js` memuat `sprite.js` hanya bila ada `.character[data-frames]`.
   - `dialogue.js setPose()` memulai ulang siklus dengan frame pose baru.
   - Tirai tetap hanya memuat frame 1; frame 2–3 dimuat saat idle.
4. **Perbaikan terkait.**
   - Pose `happy` diganti `smile` di `reflection.php:26` dan `challenge-finished.php:90`.
   - Teks bantuan Kelengkapan dan README diperbaiki agar sesuai perilaku sebenarnya.

## Fase E — Toast gaya game (#7)

Hanya di `body.game`; toast admin tidak berubah. API `toast(message, type, requestId)` tetap sama, jadi semua pemanggil tetap bekerja.

1. **Tampilan.**
   - Kartu/pita di tengah layar (sedikit di atas tengah), lebar `min(560px, 90vw)`.
   - Navy semi-transparan dengan bingkai emas bercahaya, lencana ikon bulat besar (✓ ! ✕ i) dari `<template data-toast-icon>` di `components/toast.php`, dan teks `--step-1`.
   - Animasi masuk "pop" (0.6→1.06→1), keluar memudar sambil naik. Dengan reduced-motion hanya memudar.
   - Lebih ringkas di ponsel mendatar, dan menghormati safe-area.
2. **Perilaku** (`core/toast.js`).
   - Antrean: satu toast tampil pada satu waktu (maks 3 antre). Pesan identik berturut-turut digabung.
   - Durasi 1,8–5 detik mengikuti panjang teks.
   - Ketuk kartu atau tekan Esc untuk menutup. Lapisan `pointer-events: none` di luar kartu, jadi permainan tidak terhalang.
   - Toast menunggu selama `isModalOpen()`, dan tidak memasang `has-modal`.
   - `bad` memakai `role=alert`, jenis lain `status`.
3. **Flash server tanpa JavaScript:** toast tengah dengan animasi CSS yang hilang sendiri ±3,5 detik. Dengan JavaScript, flash masuk ke antrean yang sama.
4. **Perbaikan terkait.**
   - Flash `error` di game (semuanya kunci wilayah/tantangan) ditampilkan sebagai `warn`, sama dengan toast kunci dari JS.
   - `HomeController::start()` memanggil `keepFlashdata('message')` sebelum mengalihkan ke `/gerbang`, sehingga "sandi berhasil diganti" kini tampil.
   - Toast ganda `Auth.mustChange` dihapus; alert di halaman sudah menjelaskannya.
   - Teks baru (mis. `toastDismiss`) masuk `Language/{id,en}/Js.php`.

## Fase F — Ramah sentuh di ponsel/tablet (#9)

**F1 — CSS** (`base.css`, `components.css`, `game.css`, `cinematic.css`, `layout.css`; hanya `body.game`)

1. **Dasar sentuh.**
   - `touch-action: manipulation` mematikan zoom ketuk-ganda iOS yang terpicu ketukan cepat di narator, keping, dan chip. Cubit-zoom tetap bisa.
   - `overscroll-behavior-y: none` di html/body game mencegah tarik-untuk-muat-ulang Android menghapus jawaban yang belum diperiksa.
   - `-webkit-tap-highlight-color: transparent`.
   - `user-select: none` dan `-webkit-touch-callout: none` di permukaan permainan (keping, objek, chip, peta, tokoh). Teks bacaan tetap bisa diseleksi.
   - `draggable="false"` pada gambar adegan, peta, dan tokoh.
2. **Umpan balik tekan.**
   - `:active` (skala .96 atau lebih terang) pada tombol, opsi, kata, rumpang, keping, objek, pin, dan kartu.
   - `game.js` memasang listener `touchstart` pasif, karena iOS baru menerapkan `:active` bila ada listener itu.
   - Efek `:hover` yang berupa transformasi atau garis tepi dibungkus `@media (hover: hover)` agar tidak "lengket" setelah diketuk. Yang dibungkus: `.object`, `.piece`, `.option`, `.map-pin`, `.card-link`, `.rating-star`, `.icon-btn`.
3. **Target ≥44px.**

   | Elemen | Sekarang | Lokasi |
   |---|---|---|
   | Kontrol narator ▶/⏸/↻/◀▶ | 40px, 32–34px di ponsel | `cinematic.css:257,935-936,960,976,997` |
   | Sakelar Otomatis (`.narrator-auto`) | 40px | `cinematic.css:268` |
   | Lanjut/Kembali cerita dan Kenali | 38px | `cinematic.css:933,984` |
   | `.btn-sm` di game (termasuk tombol keluar tantangan dan "Lihat hasil") | 36px | `components.css:49`, `_frame.php:63`, `map-level.php:95` |
   | Chip petunjuk | 36px | `game.css:623` |
   | `.chip.card-passage` | ±26px | `components.css:209-219` |
   | `.lang-opt` | 38px | `components.css:255` |
   | `summary` sumber | 36px | `game.css:887` |
   | Tombol rak (ponsel mendatar) | 38px | `game.css:1348` |
   | Pin di ponsel | 40px | `game.css:1564,1691` |

   Titik slide (14px, atau 9px di ponsel; `game.css:391`, `cinematic.css:1006`) tetap kecil secara visual, tetapi area sentuhnya diperluas lewat `::before`.
4. **Ukuran teks.** Di ponsel mendatar, narasi, instruksi, label pin, dan daftar cari naik dari `--step--1` ke `--step-0`. Ukuran tetap .68–.75rem (label Serpihan, lencana "Belum didengar", nomor keping) naik ke ≥.8rem.
5. **Safe-area.** Ditambahkan pada toast, bagian bawah layar tantangan (tombol Periksa tidak lagi berada di zona indikator home iPhone), dan lightbox.
6. **Bacaan tanpa gulir bersarang.** Di ponsel mendatar `.passage-body` tidak lagi `max-height: 30vh`, jadi hanya arena yang bergulir.
7. **Input.** Font-size ≥16px pada `pointer: coarse`, supaya iOS tidak zoom saat input difokus.
8. **Reduced-motion.** Celah yang tersisa ditutup: `start-glow` pada `.btn-start` dan pudar `.kenal.is-open`.

**F2 — JS**

1. **Seret dengan jari di puzzle.**
   - `core/drag.js` (baru) memakai pointer events, `setPointerCapture`, ambang 8px, dan gulir otomatis di tepi.
   - Mode susun: seret satu keping ke keping lain untuk menukar.
   - Mode urut: seret lewat pegangan ⋮⋮ yang ber-`touch-action: none`, sehingga daftar tetap bisa digulir.
   - Ketuk-tukar dan tombol ▲▼ tetap ada. Seret-lepas HTML5 dimatikan pada `pointer: coarse`.
   - Teks petunjuk puzzle menyebut "ketuk dua keping atau seret".
2. **Cari: tombol "Perbesar adegan"** (⤢, 44px).
   - Adegan menjadi layar penuh (`.hunt.is-expanded`), dengan petunjuk di pita atas; tutup lewat tombol atau Esc.
   - Koordinat objek dalam persen, jadi tidak ada perubahan data.
   - Ini menjawab masalah adegan yang hanya 180–364px di ponsel.
3. **Keyboard layar.**
   - Saat input atau textarea di arena difokus dan `visualViewport` mengecil, input digulir ke tengah area yang masih terlihat.
   - `interactive-widget=resizes-content` baru ditambahkan bila uji di perangkat menunjukkan perlu. Alasannya: opsi itu dapat memindahkan tablet ke tata letak ponsel selama mengetik.
   - Input rumpang dan nama pengguna diberi `autocorrect="off"`; `enterkeyhint` next/done.
4. **Rumpang.** `focus({preventScroll: true})` ditambah gulir `nearest` agar arena tidak melompat bolak-balik.
5. **Modal salah setelah 3× periksa.** `showModal` mendapat opsi `focus`, dan fokus awal diarahkan ke "Perbaiki jawaban". Sekarang Enter langsung memilih "Selesaikan dengan jawaban ini" (`challenge.js:157`).
6. **Usap kiri/kanan** untuk slide cerita, Kenali, dialog, dan halaman Pustaka (`slides.js`). Ambang 50px, gerak vertikal diabaikan.
7. **Musik tertunda sampai ketukan pertama** (`core/audio.js`). Sekarang musik tantangan dan wilayah tidak pernah berbunyi karena `music()` langsung pulang sebelum `unlock`.

**Disarankan, tidak dikerjakan sekarang** (mengubah alur/penelitian atau berisiko). Akan dijelaskan di ringkasan akhir:

- `pilihan` maju otomatis (1,1+1,4 detik) tanpa tombol Lanjut. Perubahan ini memengaruhi tempo dan durasi penelitian.
- Layar putar muncul keliru di iPad Split View.
- Celah breakpoint tablet 7–8" (tinggi 500–600px).
- Blur HUD di tablet murah.
- Modal belum `inert`.
- Gambar splash iOS.
- Pin pos tantangan di peta wilayah.
- `?v=` untuk semua media (performa).
- Menegakkan ukuran "disarankan" (`map.region`, `reward.badge`, `challenge.*`).

---

## Berkas utama

**Baru:**
- `app/Libraries/ImageSlots.php`
- `app/Controllers/Game/AppController.php`
- `app/Views/admin/media/appearance.php`
- `app/Views/components/{avatar,avatar-picker}.php`
- migration `004000`
- `public/assets/js/core/{lightbox,sprite,drag}.js`

**Diubah:**
- `MediaController`, `ContentController`, `MediaStore`, `MediaAssetSeeder`, `MediaUsage`
- `Config/{Gelita,Routes}.php`
- `content_helper.php`, `ui_helper.php`
- `GameProgress`, `ProfileController`, `RegisterController`, `HomeController`
- Participant model/entity
- Views: `layouts/*`, `hud`, `lantern`, `character`, `curtain`, `toast`, `map-kedu`, `profile`, `register`, `dialogue-scene`, `admin/content/{level,dialogues}`, `admin-sidebar`
- CSS: `layout`, `components`, `game`, `cinematic`, `admin`
- JS: `game.js`, `admin.js`, `core/{toast,curtain,audio,modal}.js`, `game/{library,dialogue,map,slides}.js`, `engines/{puzzle,cari,rumpang}.js`, `challenge.js`
- Bahasa: `Language/{id,en}/{Game,Js}.php`

**Dihapus:** `public/manifest.json` (diganti route).

**Docs:** `05_VIEW_UI`, `06_JAVASCRIPT`, `01_DATABASE`, `08_DEPLOYMENT` (server lama cukup `php spark migrate`; opsional `php spark gelita:media:scan` untuk mendaftarkan slot kosong baru), dan README.

## Verifikasi

1. **Uji otomatis.** Jalankan `composer install`, lalu `vendor/bin/phpunit` (SQLite).
   - Tes baru/diperbarui:
     - `ImageSlotsTest`
     - `AvatarTest`: helper, validasi `profil/avatar` & daftar, pemilih tersembunyi bila kosong, `toSafeArray`.
     - `CharacterFramesTest`: `data-frames` hanya bila ≥2, cadangan idle.
     - `InstallableAppTest`: manifest dari controller, ikon cadangan statis PNG ukuran benar, ikon slot + `?v`, `link rel` di layout.
     - `RegionStoryTest`: `data-curtain-bg`.
     - `RouteWiringTest`, `LanguageFilesTest`, `JsConfigTest`.
   - Semua PHP yang diubah juga dicek dengan `php -l`.
2. **Uji ujung ke ujung.** Pasang MariaDB lokal bila bisa, lalu `php spark migrate`, `db:seed`, dan `serve`. Jalankan Playwright (Chromium `/opt/pw-browsers`, `hasTouch`/`isMobile`) di 1920×1080, 1366×768, 844×390, dan 568×320. Yang diperiksa:
   - profil lebar;
   - pilih avatar di daftar dan profil, lalu cek HUD;
   - unggah pin/lentera/latar loading per kota, lalu cek peta dan tirai;
   - unggah 3 frame, lalu cek animasi dan berhentinya dengan reduced-motion;
   - toast tengah, antrean, dan ketuk-tutup;
   - lightbox admin;
   - ikon slot di `/manifest.json`;
   - seret puzzle, perbesar adegan cari, usap slide;
   - tidak ada tarik-muat-ulang.
3. **Cek di perangkat nyata** (dicatat di ringkasan): zoom ketuk-ganda iOS, `:active` di Safari, keyboard layar, dan tarik-muat-ulang Android.
4. Sebelum push, periksa diff sekali lagi secara kritis. Pull request dibuat hanya bila diminta.
