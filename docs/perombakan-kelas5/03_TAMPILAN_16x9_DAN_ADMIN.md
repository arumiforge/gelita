# Perombakan Kelas 5 · Dokumen 3 — Panggung 16:9 Tetap, Tantangan Lebar Penuh, Panel Admin SaaS, Ekspor Data Mentah, dan Rilis

> **Status: rencana siap dikerjakan** (disusun 5 Oktober 2026). Dokumen **3 dari 3**. Kerjakan setelah [Dokumen 1](01_FONDASI_DATA_DAN_RESET.md) dan [Dokumen 2](02_TANTANGAN_DAN_BANK_SOAL.md) selesai. Bagian terakhir (§13) adalah prosedur **satu kali** untuk memasang perombakan di server dan menjalankan reset total.

---

## Daftar isi

1. [Prinsip panggung 16:9](#1-prinsip-panggung-169)
2. [Satuan, token, dan tipografi game](#2-satuan-token-dan-tipografi-game)
3. [Kerangka panggung: HUD, area isi, navigasi, lapisan](#3-kerangka-panggung-hud-area-isi-navigasi-lapisan)
4. [Tata letak setiap layar game](#4-tata-letak-setiap-layar-game)
5. [Layar tantangan: lebar penuh, tanpa Jaka](#5-layar-tantangan-lebar-penuh-tanpa-jaka)
6. [Aset visual dan posisi yang diatur sekali](#6-aset-visual-dan-posisi-yang-diatur-sekali)
7. [JavaScript panggung](#7-javascript-panggung)
8. [Panel admin SaaS dengan tabel formal](#8-panel-admin-saas-dengan-tabel-formal)
9. [Ekspor data mentah](#9-ekspor-data-mentah)
10. [Laporan PDF](#10-laporan-pdf)
11. [Dokumentasi yang diperbarui](#11-dokumentasi-yang-diperbarui)
12. [Pengujian dan kriteria selesai Dokumen 3](#12-pengujian-dan-kriteria-selesai-dokumen-3)
13. [Rilis ke server: prosedur satu kali](#13-rilis-ke-server-prosedur-satu-kali)

---

## 1. Prinsip panggung 16:9

Permintaan: semua tampilan game dikunci pada rasio **16:9** berapa pun lebar layar; **tidak responsif**; layar putar tetap wajib; isi yang melebihi 16:9 digulir di dalam panelnya tanpa mengubah tampilan aset; posisi visual diatur sekali dan tidak berubah.

Keputusan:

1. **Panggung (stage).** Seluruh area game digambar di satu kotak logis berukuran **1280 × 720 dp** (dp = *design pixel*). Kotak ini diperbesar/diperkecil seragam agar pas di layar, lalu diletakkan di tengah. Sisa layar menjadi **bingkai gelap** (*letterbox*) di atas-bawah atau kiri-kanan.
2. **Satu skala, satu tata letak.** Semua ukuran di CSS game memakai `rem`, dan `1rem = 16 dp`. Karena `font-size` akar ikut lebar panggung, seluruh isi (teks, tombol, gambar, celah) membesar dan mengecil bersama. Tidak ada `@media` lebar/tinggi untuk tata letak, tidak ada `vw`/`vh` di dalam panggung, tidak ada `px` selain garis 1px.
3. **Posisi visual tetap.** Latar 1920 × 1080 mengisi panggung tepat (rasio sama, tidak pernah terpotong berbeda). Pin peta, benda Cari Objek, tokoh cerita, dan panel memakai koordinat persen atau dp di panggung, jadi posisinya sama di semua perangkat. Koordinat tata letak dikumpulkan di satu berkas (`stage.css`, §3.3).
4. **Gulir hanya di dalam panel.** Panggung tidak pernah bergulir. Panel yang isinya bisa panjang (bacaan, daftar pernyataan, formulir, Pustaka, riwayat) memakai kelas `.stage-scroll` dengan tinggi tetap.
5. **Putar layar tetap wajib.** Perangkat sentuh tegak (`orientation: portrait` + `pointer: coarse`) melihat layar "Putar perangkatmu" (`components/rotate-gate.php`, tidak berubah).
6. **Hanya area game.** Panel admin bukan bagian panggung; admin adalah dasbor SaaS yang mengikuti lebar layar (§8), karena tabel data mentah membutuhkan lebar dan tidak memuat aset visual game. Halaman masuk admin (`layouts/auth.php`) ikut gaya admin.

Contoh skala:

| Layar (CSS px) | Panggung | Bingkai | 1 rem |
|---|---|---|---:|
| 1920 × 1080 (PC/proyektor) | 1920 × 1080 | — | 24 px |
| 1366 × 768 laptop, jendela peramban ± 1366 × 657 | 1168 × 657 | kiri-kanan | 14,6 px |
| 1280 × 800 Chromebook | 1280 × 720 | atas-bawah 40 | 16 px |
| 1024 × 768 iPad mendatar | 1024 × 576 | atas-bawah 96 | 12,8 px |
| 2560 × 1080 layar lebar | 1920 × 1080 | kiri-kanan 320 | 24 px |
| 844 × 390 ponsel mendatar (layar penuh) | 693 × 390 | kiri-kanan 75 | 8,7 px |

Huruf isi 20 dp (1,25 rem) menjadi ± 11 px di ponsel mendatar — masih terbaca, tetapi kecil. Perangkat utama penelitian adalah PC/laptop/tablet; ponsel tetap dapat dipakai. Anjurkan memasang GELITA sebagai aplikasi layar penuh (fitur yang sudah ada) dan menyediakan tombol **Layar penuh** di menu HUD (Fullscreen API; disembunyikan bila tidak didukung, mis. iPhone).

---

## 2. Satuan, token, dan tipografi game

### 2.1 Ukuran panggung (CSS murni + JS kecil)

```css
/* tokens.css — lingkup game */
:root.game-stage {
  --app-w: 100vw;
  --app-h: 100vh;                         /* cadangan tanpa JS */
  --stage-w: min(var(--app-w), calc(var(--app-h) * 16 / 9));
  --stage-h: min(var(--app-h), calc(var(--app-w) * 9 / 16));
  font-size: calc(var(--stage-w) / 80);   /* 1rem = 16 dp */
  --letterbox: #05080F;
}
@supports (height: 100dvh) { :root.game-stage { --app-h: 100dvh; } }
```

`layouts/game.php` menambahkan `class="game-stage"` pada `<html>` dan skrip sebaris di `<head>` (sebelum CSS dimuat) yang mengisi `--app-w`/`--app-h` dari `visualViewport` (atau `innerWidth`/`innerHeight`) pada `load`, `resize`, dan `orientationchange`. Ini mengatasi bilah alamat ponsel yang membuat `vh` tidak akurat. Tanpa JavaScript, cadangan `vw`/`dvh` tetap menghasilkan panggung 16:9.

### 2.2 Satuan

- Desain ditulis dalam dp pada kanvas 1280 × 720; di CSS dibagi 16: 24 dp = `1.5rem`, 56 dp = `3.5rem`.
- Garis tipis: `max(1px, .0625rem)`.
- Posisi aset dari database (pin peta, benda cari): persen dari kotak gambar masing-masing.
- Dilarang di CSS game: `vw`, `vh`, `@media (max-width|min-width|max-height|min-height …)`, `clamp()` berbasis viewport — satu-satunya pengecualian adalah cadangan `--app-w`/`--app-h` di `tokens.css` (§2.1), yang berada di luar panggung. Yang boleh: `@media (orientation: portrait) and (pointer: coarse)` (layar putar), `prefers-reduced-motion`, `hover: hover`. `StageCssTest` (§12) memeriksanya.

### 2.3 Token baru (`tokens.css`, game)

| Token | dp | rem | Pakai |
|---|---:|---:|---|
| `--fs-caption` | 16 | 1 | label kecil, keterangan |
| `--fs-body` | 20 | 1.25 | teks umum, tombol |
| `--fs-reading` | 22 | 1.375 | **bacaan** (line-height 1.6) |
| `--fs-h3` | 24 | 1.5 | judul panel |
| `--fs-h2` | 32 | 2 | judul layar |
| `--fs-h1` | 40 | 2.5 | judul besar |
| `--fs-display` | 56 | 3.5 | judul sinematik |
| `--tap` | 48 | 3 | sasaran sentuh minimum |
| `--gutter` | 32 | 2 | tepi kiri-kanan isi |
| `--gap-1…6` | 4, 8, 12, 16, 24, 32 | .25 … 2 | celah |
| `--radius`, `--radius-lg` | 12, 20 | .75, 1.25 | sudut |
| `--hud-h` | 56 | 3.5 | tinggi HUD |
| `--nav-h` | 64 | 4 | tinggi bilah navigasi bawah |

Warna, huruf (Cinzel, Plus Jakarta Sans, IBM Plex Mono — tetap di-host sendiri), dan gaya perkamen/emas GELITA **tidak berubah**. Token `--step-*` berbasis `clamp(vw)` dan `--content-max`, `--arena-max` dihapus.

### 2.4 Berkas CSS game

| Berkas | Isi setelah perombakan |
|---|---|
| `tokens.css` | token §2.3 (game) — token admin dipindah ke `admin.css` |
| `base.css` | reset, tipografi dasar, fokus, `.visually-hidden` (tanpa ukuran viewport) |
| `stage.css` (**baru**) | bingkai, panggung, kerangka (HUD/area/nav), **koordinat tata letak semua layar** (§3.3, §4), `.stage-scroll` |
| `components.css` | tombol, kartu, chip, modal, toast, stepper, bintang, penanda kesempatan — dalam rem |
| `game.css` | isi tiap layar & engine — dalam rem, tanpa media query lebar |
| `cinematic.css` | tirai, cerita, kenali, tuntas, penutup — dalam rem/persen |
| `layout.css` | dihapus; isinya pindah ke `stage.css` (game) dan `admin.css` (admin) |

---

## 3. Kerangka panggung: HUD, area isi, navigasi, lapisan

### 3.1 Struktur HTML (`layouts/game.php`)

```html
<html class="game-stage">
<body class="game {bodyClass}">
  <!-- di luar panggung: layar putar (fixed, menutup seluruh layar) -->
  <?= rotate-gate ?>
  <div class="stage-frame">                <!-- mengisi layar, grid center, warna --letterbox -->
    <div class="stage" id="stage">         <!-- 80rem × 45rem = 1280 × 720 dp -->
      <div class="scene">…</div>           <!-- latar 100% × 100% panggung -->
      <?= overlay (tirai) ?>
      <?= hud ?>                            <!-- 0–56 dp -->
      <main id="app" class="stage-main">…</main>
      <?= nav-bar ?>                        <!-- 656–720 dp, bila ada -->
      <div id="modal-layer" class="modal-layer"></div>   <!-- absolute, di dalam panggung -->
      <div class="toast-layer"></div>                    <!-- absolute, di dalam panggung -->
    </div>
  </div>
</body>
```

`.stage { position: relative; width: 80rem; height: 45rem; overflow: hidden; }` — karena 1 rem = lebar panggung / 80, ukuran ini selalu sama dengan `--stage-w × --stage-h`.

### 3.2 Pita tetap

| Pita | Posisi dp (x, y, w, h) | Isi |
|---|---|---|
| HUD | 0, 0, 1280, 56 | kiri: lentera (serpihan *x*/12) + merek; tengah: nama wilayah (bila ada); kanan: bahasa, suara, layar penuh, profil/keluar. Saat tantangan (`body.is-challenge`) tautan navigasi disembunyikan (perilaku sekarang). Lentera tetap di **kiri atas** (dirujuk naskah `intro-07`). |
| Area isi | 0, 56, 1280, 600 | `main#app`; isi diletakkan dengan koordinat §4. Tanpa nav bar (layar tantangan, sinematik) area isi 0, 56, 1280, 664. |
| Nav bar | 0, 656, 1280, 64 | tombol kembali kiri, tombol utama kanan; latar gradien gelap |
| Modal | 0, 0, 1280, 720 | tirai gelap; kotak modal maks 720 × 560 dp di tengah, isi bergulir bila panjang |
| Toast | x 856, y 72, w 400 | pojok kanan atas di bawah HUD (tidak menutupi tombol utama di kanan bawah) |

### 3.3 Satu berkas koordinat

`stage.css` berisi variabel koordinat untuk setiap wilayah layar, mis.:

```css
.stage {
  --hud-h: 3.5rem;  --nav-h: 4rem;  --gutter: 2rem;
  /* tantangan */
  --ch-head-y: 4rem;   --ch-head-h: 3.5rem;
  --ch-arena-y: 8rem;  --ch-arena-h: 36rem;          /* 128–704 dp */
  /* peta */
  --map-x: 2rem; --map-y: 4.5rem; --map-w: 55rem;     /* 32, 72, 880 dp */
  /* … semua layar §4 … */
}
```

Layar lain hanya memakai variabel ini (`position: absolute; left: var(--map-x)…` atau grid berukuran tetap). Mengubah tata letak satu layar = mengubah angka di satu tempat.

---

## 4. Tata letak setiap layar game

Koordinat dp pada kanvas 1280 × 720 (x, y, lebar, tinggi). "Gulir" = panel `.stage-scroll`.

| Layar (view) | Elemen dan koordinat |
|---|---|
| Halaman awal `/` (`game/welcome`) | latar `bg.welcome`; logo `ui.logo-hero` 240, 120, 800 × 300; tombol Mulai `ui.btn-start` 460, 456, 360 × 120; petunjuk pasang aplikasi 896, 600, 352 × 40; HUD hanya bahasa & suara |
| Mulai `/mulai` (`game/start`) | Jaka 140, 196, 280 × 460; panel berornamen 432, 176, 560 × 400 berisi judul dan dua kartu baris ("Saya baru" utama, "Sudah punya akun"); HUD: Kembali kiri atas, logo tengah; tanpa nav bar (diubah 9 Okt 2026) |
| Masuk `/masuk` (`game/login`) | panel 360, 88, 560 × 544: nama pengguna, sandi, tombol Masuk; galat di atas isian |
| Daftar `/daftar/1…3` (`game/register/step-*`) | panel 200, 72, 880 × 576: stepper 0–64 di dalam panel; isi formulir **gulir** 64–496; bilah tombol 496–576 (Lanjut/Daftar kanan; Kembali pindah ke HUD kiri atas dengan logo di tengah HUD, 9 Okt 2026). Langkah 3: kotak "Mengapa sandi harus kuat?" dengan Jaka di kiri isi panel (220 × 300) |
| Ganti sandi `/ganti-sandi` | panel 360, 72, 560 × 576 (gulir) |
| Gerbang `/gerbang` (`game/start-choice`) | Jaka 140, 196, 280 × 460; panel berornamen 432, 160, 560 × 432 (sapaan, ajakan, dua kartu baris) |
| Cerita pembuka `/intro`, wilayah tuntas `/tuntas/{code}`, penutup `/penutup` (`cinematic.css`) | latar per slide 1280 × 720; tokoh kiri 64, 136, 300 × 520 / kanan 916, 136, 300 × 520; kotak teks 160, 520, 960 × 168 (**gulir** bila panjang); kontrol (otomatis/lewati) 1008, 72, 240 × 48; kartu "Ketuk untuk mulai" di tengah |
| Peta Kedu `/peta` (`game/map-kedu`) | peta `map.kedu` 32, 72, 880 × 566 (rasio 1400:900); pin wilayah & ikon lentera **persen** di peta; panel kanan 936, 72, 312 × 576 (**gulir**): kartu wilayah, Pustaka Kedu, Tonton penutup; narasi Jaka sebagai balon 48, 560, 600 × 96 |
| Kenali wilayah (overlay di `/peta`) | **isi tidak berubah**; overlay penuh 0, 0, 1280 × 720 dengan komposisi slide yang sama (tokoh kiri, teks kanan/bawah) dalam rem |
| Peta wilayah `/wilayah/{code}` (`game/map-level`) | peta `map.region.{code}` 32, 72, 880 × 566; 4 pin node persen; panel kanan 936, 72, 312 × 576: judul wilayah, fokus, progres *x*/4 tantangan, tombol Pustaka {wilayah}, Kenali |
| Dialog wilayah `/dialog/{code}` (`game/dialogue`) | sama dengan sinematik: tokoh kiri/kanan, kotak teks 160, 520, 960 × 168, kartu bab di tengah |
| Kartu misi `/misi/{code}/{n}` (`game/mission-brief`) | kartu 280, 96, 720 × 528: ikon jenis tantangan, judul, deskripsi (**gulir**), instruksi, hasil terbaik (bintang + "Berhasil pada Percobaan Pertama *x* dari *y*"), tombol **Mulai tantangan** |
| Tantangan `/tantangan/{code}/{n}` | §5 |
| Selesai `/selesai/{id}` (`game/challenge-finished`) | kartu 280, 72, 720 × 576 di tengah (**tanpa Jaka**): orb, judul, bintang, tiga statistik (Waktu · Berhasil pada Percobaan Pertama *x* dari *y* · Percobaan dipakai *n* dari 3 / Ketukan salah), baris serpihan, panel Mbah Kedu wilayah tuntas (bila ada), sorotan Pustaka terbuka (bila ada) — bagian tengah kartu **gulir**; tombol di bilah bawah kartu |
| Hasil `/hasil/{code}/{n}` (`game/challenge-result`) | panel 160, 72, 960 × 576: ringkasan terbaik + tabel pengerjaan (**gulir**): Pengerjaan, Mulai, Durasi, Percobaan, Berhasil pada Percobaan Pertama, Bintang; tombol Main lagi |
| Rak Pustaka `/pustaka` (`game/library-index`) | tiga kartu wilayah 88, 120, 352 × 440 · 464, 120 · 840, 120 |
| Pustaka `/pustaka/{code}` (`game/library`) | buku 64, 80, 1152 × 568: halaman kiri galeri 0–576 (gambar/video dengan rasio dijaga, panah), halaman kanan teks 576–1152 (**gulir**), navigasi halaman di bawah buku. **Isi, gambar, teks, video tidak berubah.** Lightbox penuh panggung. |
| Pustaka terkunci (`game/library-locked`) | kartu 320, 140, 640 × 420 |
| Profil `/profil` (`game/profile`) | kiri 32, 72, 400 × 576: inisial, nama, kode, empat ubin (serpihan, tantangan selesai, bintang, waktu); kanan 456, 72, 792 × 576: lencana wilayah (baris) + tabel riwayat (**gulir**). Tanpa skor. |
| Balai Refleksi `/refleksi` (`game/reflection`) | kiri 32, 72, 520 × 576: ringkasan (bintang per wilayah, Berhasil pada Percobaan Pertama per jenis), Mbah Kedu; kanan 576, 72, 672 × 576: formulir kritik & saran (**gulir**) dengan tombol Kirim tetap di bawah |
| Tirai (wilayah, tantangan, peta) | 0, 0, 1280 × 720 di atas semua lapisan kecuali modal |
| Layar putar | di luar panggung, `position: fixed`, seluruh layar (tidak berubah) |

---

## 5. Layar tantangan: lebar penuh, tanpa Jaka

### 5.1 Kerangka bersama (`game/challenge/_frame.php`)

- **Gambar Jaka dihapus** (`.challenge-character` dan `component('character', ['character' => 'jaka'])`). Isi tantangan memakai seluruh lebar panggung dan rata tengah.
- Kepala tantangan 32, 64, 1216 × 56: lencana nomor + *eyebrow* (wilayah · Tantangan *n* dari 4 · jenis) + judul (kiri); chip waktu, ✓/✗, **penanda kesempatan** (●●○), tombol 💡 Petunjuk, tombol Keluar (kanan).
- Arena 32, 128, 1216 × 576 (sampai y 704). Tidak ada nav bar di layar tantangan.
- Instruksi tampil sebagai baris pertama arena atau di panel kiri (lihat per engine).
- Judul variasi (`payload.variant.title`) tampil di atas bacaan/gambar.

### 5.2 Per engine

```
Susun Gambar                          Isi Rumpang
┌──────────┬──────────────┬─────────┐ ┌─────────────────────────────────────┐
│Instruksi │   PAPAN      │Ketera-  │ │  Judul variasi                      │
│& tips    │  504 × 504   │ngan     │ │  BACAAN (gulir) 1088 × 432          │
│332 × 512 │  (tengah)    │(setelah │ │                                     │
│          │              │ utuh)   │ ├─────────────────────────────────────┤
│          ├──────────────┤332 × 512│ │  BANK KATA (chip, terbungkus) 72    │
│          │●●○  [Cek]    │         │ ├─────────────────────────────────────┤
└──────────┴──────────────┴─────────┘ │  ●●○ Kesempatan      [Cek jawabanku]│
                                      └─────────────────────────────────────┘
Benar atau Salah                      Cari Objek
┌──────────────────┬────────────────┐ ┌─────────────────────────────────────┐
│ BACAAN (gulir)   │ PERNYATAAN     │ │ (Mbah) Petunjuk 2 dari 4: …  ▶ ●●○  │
│ 596 × 576        │ (gulir)        │ ├──────┬──────────────────────┬───────┤
│ judul + 1–3      │ 596 × 512      │ │Daftar│  ADEGAN 16:9         │Ketukan│
│ paragraf         │ kartu: teks,   │ │benda │  864 × 486           │salah, │
│                  │ gambar, B / S  │ │✓ ✗ • │  (benda posisi %)    │waktu  │
│                  ├────────────────┤ │164 w │                      │164 w  │
│                  │ ●●○ [Cek…]     │ └──────┴──────────────────────┴───────┘
└──────────────────┴────────────────┘
```

| Engine | Koordinat dp (x, y, w, h) |
|---|---|
| **Susun Gambar** | panel kiri 32, 128, 332 × 512 (instruksi + 3 tips + 💡); papan 388, 136, 504 × 504; bilah aksi di bawah papan 388, 648, 504 × 48 (●●○ + **Cek gambarku**); panel kanan 916, 128, 332 × 512 (keterangan gambar, muncul setelah utuh/kesempatan habis) |
| **Isi Rumpang** | judul variasi + bacaan 96, 128, 1088 × 432 (**gulir**, huruf `--fs-reading`, rumpang = tombol 120 × 44 dp sebaris teks); bank kata 96, 568, 1088 × 72 (chip 44 dp, terbungkus, rata tengah); bilah aksi 96, 648, 1088 × 56 (●●○ "Kesempatan 2 dari 3" kiri, **Cek jawabanku** kanan) |
| **Benar atau Salah** | bacaan 32, 128, 596 × 576 (**gulir**; judul bacaan, paragraf `--fs-reading`); pernyataan 652, 128, 596 × 512 (**gulir**; kartu: nomor, gambar 192 × 144 bila ada — ketuk untuk perbesar, teks, dua tombol radio **Benar** / **Salah** 48 dp); bilah aksi 652, 648, 596 × 56 |
| **Cari Objek** | bilah petunjuk 32, 128, 1216 × 56 (avatar Mbah Kedu 48 dp, "Petunjuk *n* dari 4", teks petunjuk, ▶ narasi, ●●○ kesempatan petunjuk ini); daftar benda 32, 192, 164 × 486 (4 baris: ✓ ditemukan, ✗ dilewati, • belum; ketuk untuk melompat); adegan 208, 192, 864 × 486 (16:9, benda diletakkan dengan persen); panel kanan 1084, 192, 164 × 486 (ketukan salah, waktu) |

Catatan:

- Avatar kecil **Mbah Kedu** di bilah petunjuk Cari Objek tetap (ia yang membacakan petunjuk). Permintaan hanya menghapus Jaka.
- Modal hasil percobaan muncul di tengah panggung; tombol utamanya di kanan bawah modal.
- Tanda benar/salah (hijau/merah, ✓/✗) dan penguncian soal benar mengikuti Dok 2 §5.3.
- Teks bacaan panjang tidak pernah mengecilkan huruf; panel bergulir. Event `reading_progress` dihitung dari posisi gulir panel bacaan.

### 5.3 Layar selesai

`game/challenge-finished.php`: hapus `component('character', ['character' => 'jaka', 'pose' => 'happy', 'class' => 'finished-jaka'])` dan kolom `.finished-layout` dua kolom; kartu tunggal di tengah (§4). Statistik "Skor" diganti "Percobaan dipakai *n* dari 3" (engine batch) atau "Ketukan salah *n*" (Cari Objek). `multiCheckNote` hanya tampil bila percobaan > 1 dan teksnya ramah (Dok 2 §12). Bila `completion_reason = tries_exhausted`, tampilkan baris lembut "Jawabanmu disimpan apa adanya. Kamu tetap mendapat serpihan cahaya!".

---

## 6. Aset visual dan posisi yang diatur sekali

| Aset | Ukuran berkas | Cara tampil di panggung |
|---|---|---|
| Latar layar (`bg.*`, `bg.{wilayah}.region`) | 1920 × 1080 | 1280 × 720 dp penuh, `object-fit: cover` = pas tepat (rasio sama), tidak terpotong |
| Peta Kedu, peta wilayah | 1400 × 900 | kotak 880 × 566 dp (rasio dijaga); pin persen |
| Adegan Cari Objek | 1280 × 720 | kotak 864 × 486 dp; benda persen (`x`, `y`, `w`; tinggi = `w × 16/9` karena benda persegi) |
| Benda Cari Objek | 240 × 240 transparan | lebar = `w%` adegan |
| Gambar Susun Gambar | persegi ≥ 1200 | papan 504 × 504 dp (keping dipotong `PuzzleCut`) |
| Gambar pernyataan | 480 × 360 | 192 × 144 dp di kartu; lightbox 640 × 480 dp |
| Tokoh (frame pose) | 700 × 900 | 300 × 386 dp (cerita), 220 × 283 dp (pendaftaran langkah 3) |
| Logo hero, tombol Mulai | 1600 × 600, 720 × 240 | 800 × 300 dp, 360 × 120 dp |

- Ukuran slot di `Config\Gelita::$assetSizes` dan halaman **Media** menyebut ukuran tampil dp di atas, agar ilustrator tahu skala akhirnya.
- Editor admin untuk pin peta dan benda Cari Objek (`admin/object-picker.js`) memakai gambar dengan rasio yang sama dengan panggung, sehingga apa yang diatur admin sama persis dengan yang dilihat siswa ("cukup sekali setting").

---

## 7. JavaScript panggung

| Modul | Perubahan |
|---|---|
| `core/stage.js` (**baru**) | mengisi `--app-w`/`--app-h` (juga dipanggil skrip sebaris), menyediakan `stageScale()` dan `stageRect()`, mengambil alih event `viewport_changed` dari `core/device.js` (Dok 1 §11.5) dan menambah `stage_w`, `stage_h`, `scale` (debounce 1 s, maks 1 per 5 s, termasuk saat layar putar tampil/hilang), tombol layar penuh |
| `core/modal.js`, `core/toast.js` | wadah di dalam `#stage`; fokus terkunci di modal tetap |
| `core/dom.js` | `scrollToCenter()` menggulir **panel `.stage-scroll` terdekat**, bukan `window` |
| `game/challenge.js` | penanda kesempatan, alur Dok 2 §5.3, `answer_selected`, ukur `first_input_ms`/`last_input_ms` per soal, `reading_progress`, `overlay_opened` (petunjuk, keluar) |
| `engines/*.js` | hapus cabang `matchMedia('(max-width…)')` (mis. lipat bacaan di `boleh.js`); koordinat dari CSS, bukan dari ukuran jendela |
| `game/map.js`, `game/narrator.js`, `game/library.js`, `core/curtain.js` | ukuran dari panggung; `overlay_opened` untuk Kenali dan lightbox |
| `core/events.js` | `page_hidden`/`page_visible` dari `visibilitychange` |
| `core/device.js` (**baru**, Dok 1 §11.3) | sinyal perangkat untuk formulir masuk/daftar |

Perhitungan posisi pointer (seret keping, klik benda) memakai `getBoundingClientRect()` yang sudah memperhitungkan skala, jadi tidak perlu dibagi skala secara manual.

---

## 8. Panel admin SaaS dengan tabel formal

### 8.1 Arah desain

Dasbor SaaS premium yang tenang dan formal, **tidak bertema GELITA** (tanpa navy-emas, Cinzel, perkamen, ikon lentera).

| Token (`admin.css`) | Nilai |
|---|---|
| Latar halaman | `#F5F6F8` |
| Permukaan (kartu, tabel) | `#FFFFFF`; permukaan 2 `#F9FAFB` |
| Garis | `#E4E7EC`; garis kuat `#D0D5DD` |
| Teks | `#101828`; sekunder `#344054`; redup `#667085` |
| Aksen utama | indigo `#4338CA` (hover `#3730A3`, lembut `#EEF2FF`) |
| Status | sukses `#067647` / `#ECFDF3`, peringatan `#B54708` / `#FFFAEB`, bahaya `#B42318` / `#FEF3F2`, info `#175CD3` / `#EFF8FF` |
| Sidebar | `#0F172A`, teks `#CBD5E1`, aktif `#1E293B` + teks putih + garis aksen kiri |
| Huruf | Plus Jakarta Sans 14/20 px (sudah di-host); angka `font-variant-numeric: tabular-nums`; kode (GLT-000123, item_key) IBM Plex Mono 13 px |
| Bentuk | radius 8 px (kontrol), 12 px (kartu); bayangan `0 1px 2px rgba(16,24,40,.05)` |

Tata letak: sidebar 248 px (menyempit jadi ikon 72 px di layar < 1280 px), topbar 56 px (breadcrumb, pencarian cepat kode/nama pengguna siswa, menu akun), isi selebar layar dengan padding 24 px. Tanpa chart (ECharts dihapus, Dok 1 §14).

### 8.2 Komponen

| Komponen | Spesifikasi |
|---|---|
| **Tabel data** (`components/data-table.php` + `admin/tables.js`) | header lengket, judul kolom kalimat biasa 12 px semibold redup + satuan ("Durasi (mdtk)"), baris 44 px, garis horizontal saja, hover `#F9FAFB`, angka rata kanan tabular, kode mono, status sebagai lencana, urut per kolom (server-side), pagination 25/50/100 + total baris, kolom banyak → gulir horizontal dengan kolom pertama lengket, sel kosong "—", tombol **Unduh CSV** (filter yang sama, `?format=csv`, nilai mentah). Durasi tampil `m:ss` dengan nilai milidetik di `title`. |
| Bilah filter | baris kontrol 40 px (pencarian, select, rentang tanggal), "Terapkan" & "Atur ulang", chip filter aktif |
| Kartu angka (KPI) | label 13 px redup, angka 28 px semibold tabular, keterangan kecil; **hanya hitungan** (bukan skor/rata-rata) |
| Kepala halaman | breadcrumb, judul 24 px, deskripsi 14 px, aksi di kanan |
| Tab, lencana, tombol (utama/sekunder/hantu/bahaya), formulir, modal konfirmasi, toast kanan atas, keadaan kosong | gaya seragam token §8.1 |

### 8.3 Menu dan halaman

| Grup | Halaman (rute) | Isi |
|---|---|---|
| — | **Ringkasan** `/admin` | KPI: siswa terdaftar · sesi aktif sekarang (≤ 10 menit) · pengerjaan selesai hari ini · kritik & saran. Peringatan: gambar contoh tersisa, narasi draft, verifikasi konten. Tabel "Aktivitas terbaru" (20 event terakhir: waktu, kode siswa, kejadian, layar/tantangan) dan "Progres siswa" (10 terbaru: kode, sekolah, wilayah saat ini, tantangan selesai *x*/12, bintang, aktif terakhir). |
| Data penelitian | **Siswa** `/admin/peserta` | tabel + filter (cari, sekolah, kab/kota, jenis kelamin, umur, status progres belum mulai/berjalan/selesai, tanggal daftar). Kolom: Kode, Nama*, Nama pengguna*, Sekolah, Kab/Kota, JK, Umur, Terdaftar, Setuju ikut, Tantangan selesai, Wilayah terakhir, Bintang, Aktif terakhir. (*admin saja) |
| | **Detail siswa** `/admin/peserta/{id}` | aksi: reset sandi, unduh data siswa (XLSX/CSV), cetak PDF. Tab: Profil · Sesi & perangkat · Progres (12 baris node) · Pengerjaan · Jawaban (baris dapat dibuka untuk riwayat percobaan) · Navigasi · Bantuan · Kritik & saran |
| | **Sesi bermain** `/admin/sesi`, `/admin/sesi/{id}` | tabel sesi; detail: ringkasan + linimasa event lengkap (tabel: urutan, waktu klien, waktu server, event, layar/tantangan/soal, payload JSON) |
| | **Data mentah** `/admin/data/{dimensi}` | tab = lembar ekspor §9.2: Jawaban & ketepatan (`jawaban`) · Riwayat percobaan (`percobaan`) · Pengerjaan (`pengerjaan`) · Progres (`progres`) · Penyelesaian (`selesai`) · Navigasi (`navigasi`) · Bantuan (`bantuan`) · Perangkat (`perangkat`). Dimensi **waktu** tidak punya tab sendiri: kolomnya (`started_at`, `completed_at`/`answered_at`, `duration_ms`, `first_input_ms`, `last_input_ms`) ada di tab Pengerjaan, Jawaban, dan Riwayat percobaan, serta di Sesi bermain (Dok 1 §12.1). Filter: sekolah, wilayah, tantangan, variasi, kode siswa, rentang tanggal. Kolom = kolom lembar ekspor terkait. |
| | **Kritik & saran** `/admin/masukan` | tabel: kode, sekolah, bintang (1–5), empat jawaban (dipotong, klik untuk lengkap), waktu |
| | **Unduh data** `/admin/ekspor` | formulir (format XLSX / CSV-ZIP; nilai berlabel / berkode; anonim — admin dapat mematikan; filter sekolah, wilayah, rentang tanggal) + tabel riwayat unduhan (status, baris, ukuran, sha256, kedaluwarsa, Unduh) |
| Konten (admin) | **Tantangan** `/admin/konten` | tabel 12 tantangan: wilayah, urutan, jenis, judul, variasi aktif (3/3), soal, gambar contoh tersisa, status verifikasi → editor: tab Info (judul, instruksi, kartu misi, petunjuk), Variasi (per a/b/c: judul, pengecoh, bacaan, tabel soal dengan Bloom/keterampilan/kunci), Posisi benda (Cari Objek, pemilih posisi) |
| | Bacaan, Impor bank soal, Narasi, Pustaka & dialog (editor wilayah) | fungsi tetap, gaya admin baru. Editor Pustaka dan Kenali **tidak mengubah isi** kecuali admin sendiri menyuntingnya. |
| | **Gambar & mockup** `/admin/media` | tabel/grid slot: pratinjau, kunci, label, isi yang diharapkan (`description`), ukuran tampil & berkas, status (Asli / **Gambar contoh** / Kosong), dipakai di; filter status; tombol **Unggah gambar asli** per slot |
| | Kelengkapan aset `/admin/media/kelengkapan` | daftar periksa (100 narasi per bahasa, 45 gambar tantangan, latar, tokoh) |
| Pengaturan (admin) | **Penelitian** `/admin/studi` | kode, nama, deskripsi, tahun ajaran, status (draf/berjalan/ditutup), retensi (hari), bahasa bawaan, mode buka wilayah. **Daftar siap penelitian**: 0 gambar contoh, narasi disetujui, `content:verify --siap-penelitian` lolos. Mengubah status ke *berjalan* saat daftar belum hijau meminta centang konfirmasi. |
| | Rilis, Sekolah, Staf, Tata kelola (penghapusan data + audit + retensi) | fungsi tetap, gaya baru; filter fase dihapus |
| Akun | Ubah sandi (menu akun di topbar) | tetap |

Dihapus: Analitik wilayah/tantangan/butir/indikator/pretest–posttest, Aturan skor, semua chart.

**Guru** tetap hanya melihat sekolahnya: Ringkasan, Siswa, Sesi, Data mentah, Kritik & saran, Unduh data (selalu anonim, tanpa Event Mentah dan tanpa kunci jawaban). Menu yang tidak berhak tidak dirender; rute membalas 404 (perilaku sekarang).

### 8.4 Layanan data

`App\Services\RawDataService` (pengganti `AnalyticsService`) — satu tempat untuk query setiap dimensi:

```php
public function query(string $dimension, array $filters, StaffScope $scope): BaseBuilder;   // dipakai tabel admin & ekspor
public function page(string $dimension, array $filters, int $page, int $perPage, ?string $sort): array;
public function streamCsv(string $dimension, array $filters, $handle): int;                   // tulis baris mentah ke handle
```

Dimensi = kunci lembar ekspor §9. Cakupan guru (sekolah, anonim) diterapkan di sini (lapis ketiga otorisasi, pola `ExportService` sekarang). `AnalyticsService` tinggal `summary()` (KPI hitungan), `participantProfile()`, dan `eventTimeline()`.

---

## 9. Ekspor data mentah

### 9.1 Prinsip

- Isi = **data mentah** tujuh dimensi (Dok 1 §12): tidak ada skor, persentase, indeks, peringkat, atau ringkasan demografi.
- Satu baris = satu kejadian/rekaman apa adanya; waktu dalam milidetik dan cap waktu ISO 8601 (zona Asia/Jakarta, dengan milidetik).
- Lembar **Kamus Data** wajib: setiap lembar dan kolom dengan arti, satuan, dan daftar nilai berkode.
- Dua format: **XLSX** (satu workbook, `ExcelWriter` streaming) dan **CSV-ZIP** (satu CSV per lembar, UTF-8 dengan BOM, pemisah koma, kode mentah — siap SPSS/R/Python).
- Pilihan nilai: **berlabel** (bawaan XLSX; mis. "Disimpan apa adanya", "Laki-laki") atau **berkode** (bawaan CSV; mis. `tries_exhausted`, `laki-laki`). Kamus Data memuat keduanya.
- Mode anonim (selalu untuk guru): kolom identitas (`display_name`, `username`, `school_name`, `school_npsn`) tidak dibuat; `school_ref` tetap.

### 9.2 Lembar

| Kode | Nama lembar | Kolom |
|---|---|---|
| `kamus` | Kamus Data | lembar, kolom, arti, satuan, nilai |
| `peserta` | Peserta | participant_code, display_name*, username*, school_name*, school_npsn*, school_ref, age, gender, class_level, country, province, district, registered_at, assented_at, intro_seen_at, pw_first_submit_criteria, pw_weak_submit_count |
| `sesi` | Sesi | session_code, participant_code, study_code, release_code, locale, status, started_at, last_active_at, ended_at, duration_ms, device_type, os_name, browser_name, screen_size, is_touch |
| `perangkat` | Perangkat | session_code, participant_code, via, created_at, device_type, device_brand, device_model, os_name, os_version, os_platform, browser_name, browser_version, browser_engine, is_bot, screen_w, screen_h, viewport_w, viewport_h, pixel_ratio, touch_points, orientation, is_standalone, language, timezone, corrected, detector, user_agent, client_hints (JSON) |
| `progres` | Progres | session_code, participant_code, current_level, current_node_ref, unlocked_level_sequence, completed_nodes, total_nodes, completed_levels, total_stars, updated_at |
| `pengerjaan` | Pengerjaan Tantangan | attempt_id, session_code, participant_code, level, node_ref, node_sequence, engine_type, variant_group, variant_title, attempt_no, status, completion_reason, scorable_items, first_pass_correct (Berhasil pada Percobaan Pertama), final_correct, check_count, max_tries, hint_count, answer_change_count, audio_use_count, stars, started_at, completed_at, duration_ms |
| `jawaban` | Jawaban Soal | attempt_id, session_code, participant_code, level, node_ref, variant_group, item_key, interaction_type, bloom_level, skill, indicator, prompt, display_order, status, try_count, first_answer, final_answer, answer_key**, first_pass_correct, is_correct, solved_on_try, change_count, hint_used, wrong_click_count, started_at, answered_at, duration_ms |
| `percobaan` | Riwayat Percobaan | attempt_id, session_code, participant_code, node_ref, variant_group, item_key, try_no, check_no, answer, is_correct, hint_opened_before, first_input_ms, last_input_ms, occurred_at |
| `navigasi` | Navigasi | session_code, participant_code, sequence_no, occurred_at, server_received_at, event_type, screen, from_screen, level, node_ref, attempt_id, payload (JSON) — event `screen_viewed`, `overlay_opened`, `dialogue_advanced`, `library_opened`, `library_page_viewed`, `locale_changed`, `page_hidden`, `page_visible`, `viewport_changed`, `reading_progress` |
| `bantuan` | Bantuan | session_code, participant_code, occurred_at, jenis (`petunjuk` \| `audio`), level, node_ref, attempt_id, item_key, hint_id, asset_key, audio_context, audio_locale, action, play_index, listened_ms, completed |
| `selesai` | Penyelesaian | session_code, participant_code, occurred_at, event_type (`challenge_completed`, `challenge_abandoned`, `challenge_tries_exhausted`, `clue_failed`, `level_completed`, `session_completed`, `intro_completed`, `feedback_submitted`), level, node_ref, attempt_id, payload (JSON) |
| `event` | Event Mentah (admin) | semua kolom `game_event_logs` (tanpa baris terhapus) |
| `masukan` | Kritik & Saran | participant_code, session_code, rating, liked_most, hardest_part, new_learning, suggestion, submitted_at |
| `butir` | Bank Soal | item_key, level, node_ref, variant_group, variant_title, sequence, interaction_type, bloom_level, skill, indicator, prompt, passage_key, answer_key**, decoy, scorable, is_active, media_asset_key |

\* hanya ekspor beridentitas (admin). \*\* hanya admin.

Dihapus dari ekspor lama: lembar *Levels*, *Indicators*, *Demographic Summary* (olahan), *Audio Usage* (masuk Bantuan), kolom `phase_code`, `consent_*`, `guardian_*`, `score`, `first_pass_accuracy`, `final_accuracy`, `independence`, `retry_count`, `reason_text`, `mastery_ratio`.

`App\Libraries\ExportLabels` diperbarui (judul kolom berbahasa Indonesia bersatuan, nilai berlabel, kamus). `ExportService::SHEETS`, `columns()`, `FILTER_KEYS` (`study_id`, `level_id`, `node_id`, `school_id`, `province_code`, `locale`, `date_from`, `date_to`) diperbarui. Batas `exportMaxRawEvents` tetap untuk lembar Event Mentah, Navigasi, dan Penyelesaian.

### 9.3 Ekspor per siswa

Detail siswa → **Unduh data siswa**: lembar yang sama, difilter satu peserta (anonim mengikuti hak pemohon).

---

## 10. Laporan PDF

- **Laporan studi PDF dihapus** (isinya analitik olahan).
- **Laporan satu siswa** tetap (mPDF), gaya admin baru, isi tabel formal mentah: identitas (sesuai hak), sesi & perangkat, progres 12 tantangan (status, variasi, percobaan, Berhasil pada Percobaan Pertama *x* dari *y*, bintang, durasi), daftar kritik & saran. Tanpa skor, indikator, atau kalimat penilaian. Bagian persetujuan (versi naskah, izin wali) dihapus.

---

## 11. Dokumentasi yang diperbarui

> **Catatan 6 Oktober 2026:** README dan `docs/01`–`08` sudah ditulis ulang untuk keadaan sesudah Dok 1–2 (tanpa riwayat pembaruan). Dok 3 cukup memperbarui bagian yang ia ubah; baris `08_DEPLOYMENT.md` di tabel ini tidak berlaku (lihat §13).

| Berkas | Perubahan |
|---|---|
| `README.md` | ringkasan baru di atas: kelas 5, 4 tantangan, 12 serpihan, satu tahap, data mentah, panggung 16:9, admin baru, perintah `gelita:reset` dan `gelita:mockups`; bagian "Pembaruan …" lama dipindah ke "Riwayat pembaruan" di bawah; jumlah migration/tabel/uji diperbarui |
| `docs/01_DATABASE.md` | tabel dihapus/ditambah, kolom baru, migration `004100`–`004600`, seeder baru, Derived Metrics diganti "Data mentah" |
| `docs/02_PROJECT_FOUNDATION.md` | `Config\Gelita` baru, filter `ClientHintsFilter`/`ScreenViewFilter`, dependency `matomo/device-detector` |
| `docs/03_MODEL_ENTITY.md`, `docs/04_CONTROLLER_ROUTE.md` | model/entity/service/rute baru dan yang dihapus, kontrak API (`tries_left`, `completed`, `redirect`, `variant`) |
| `docs/05_VIEW_UI.md` | bagian baru *Panggung 16:9* (§1–§6 dokumen ini) menggantikan *Orientasi layar* dan tata letak responsif lama |
| `docs/06_JAVASCRIPT.md` | `core/stage.js`, `core/device.js`, engine tanpa `pilihan`, aturan kesempatan |
| `docs/07_FEATURE_INTEGRATION.md` | ekspor data mentah, reset, mockup, pendaftaran 3 langkah |
| `docs/08_DEPLOYMENT.md` | prosedur §13 sebagai bagian "Memperbarui server ke perombakan kelas 5" |
| `docs/bank-soal/README.md`, `docs/naskah-cerita.md`, `docs/audio.md` | (Dok 2) bank kelas 5, 12 serpihan, 100 rekaman per bahasa |
| `docs/rencana-perbaikan-tampilan.md`, `docs/rencana-katalog-aset.md` | catatan di atas: bagian yang digantikan perombakan kelas 5, sisanya tetap ditunda |

---

## 12. Pengujian dan kriteria selesai Dokumen 3

### 12.1 Otomatis

- `StageCssTest` — berkas CSS game tidak memuat `vw`, `vh` (kecuali `--app-*` cadangan di `tokens.css`), `@media (max-width|min-width|max-height|min-height`, dan ukuran `px` selain garis 1px; `layouts/game.php` punya `html.game-stage`, `#stage`, modal & toast di dalam panggung.
- `ChallengeFrameTest` — `_frame.php` dan `challenge-finished.php` tidak merender tokoh Jaka; Cari Objek tetap punya avatar Mbah Kedu.
- `AdminTableTest` — tabel data merender kolom, urut, pagination, CSV mentah sama dengan isi tabel; guru tidak melihat kolom identitas.
- `RawExportTest` — lembar & kolom §9.2, kamus lengkap untuk setiap kolom, mode berlabel/berkode, CSV-ZIP terbaca (`ZipArchive` + `fgetcsv`), anonim tanpa kolom identitas, guru tanpa Event Mentah & kunci jawaban.
- `RouteWiringTest` — tidak ada rute analitik/skoring lama; rute `admin/data/*` ada.

### 12.2 Uji tampilan otomatis (Playwright)

Skrip `tools/qa/stage-check.mjs` (Chromium terpasang; jalankan terhadap `php spark serve` dengan database hasil `gelita:reset` + akun uji):

1. Masuk sebagai siswa uji, kunjungi semua layar §4 dan keempat tantangan di ketiga wilayah, buka modal hasil, Kenali, lightbox Pustaka.
2. Ukuran jendela: 1280 × 720, 1920 × 1080, 1366 × 657, 1024 × 768, 2560 × 1080, 844 × 390, dan 390 × 844 (harus layar putar, emulasi sentuh).
3. Periksa: rasio `#stage` 16:9 (±1 px); `document.scrollingElement` tidak bergulir; tidak ada elemen yang keluar dari `#stage` kecuali layar putar; elemen yang melebihi panelnya hanya ada di dalam `.stage-scroll`; posisi relatif (persen terhadap panggung) pin, benda, dan panel sama di semua ukuran (selisih < 0,5 %).
4. Simpan tangkapan layar ke `writable/qa/stage/{layar}-{ukuran}.png` untuk ditinjau.

### 12.3 Manual

- Mainkan satu wilayah penuh di laptop, tablet (Android/iPad), dan ponsel mendatar layar penuh.
- Tinjau panel admin di 1280, 1440, dan 1920 px: semua halaman §8.3, unduh XLSX dan CSV-ZIP, buka di LibreOffice dan R/Python (`read_csv`).
- Uji akses keyboard dan pembaca layar di layar tantangan (fokus, label rumpang/pernyataan/benda, `aria-live` petunjuk).

### 12.4 Kriteria selesai

- [ ] Semua layar game tampil 16:9 dengan bingkai di semua ukuran uji; tidak ada gulir halaman; gulir hanya di panel.
- [ ] Tidak ada gambar Jaka di layar tantangan dan layar selesai; isi tantangan selebar panggung dan di tengah.
- [ ] Pustaka dan Kenali wilayah tampil dengan isi yang sama persis seperti sebelum perombakan.
- [ ] Panel admin bergaya SaaS netral, tabel formal, tanpa chart; Data mentah tujuh dimensi + Perangkat; CSV per tabel.
- [ ] Ekspor XLSX/CSV-ZIP sesuai §9 dengan Kamus Data.
- [ ] Dokumentasi §11 diperbarui; seluruh uji hijau.

---

## 13. Rilis ke server: prosedur satu kali

> **Catatan 6 Oktober 2026: bagian ini tidak dikerjakan.** GELITA belum pernah berjalan di server, jadi pemilik memutuskan rilis = instalasi baru (`docs/08_DEPLOYMENT.md`); skrip `upgrade-kelas5` dan `gelita:story:update` tidak ada. Langkah panel di bawah (narasi, gambar asli, daftar siap penelitian, uji asap) tetap berlaku sesudah instalasi. Lihat `CATATAN_IMPLEMENTASI.md` → "Pembersihan — instalasi baru tanpa kompatibilitas lama".

Dikerjakan **sekali**, setelah ketiga dokumen selesai dan digabung ke `main`. Deploy biasa (`deploy.sh`/`deploy.ps1`) tidak cocok untuk langkah ini karena `gelita:content:verify` pada isi lama akan gagal sebelum reset. Sediakan skrip ASCII-only:

- `deploy/linux/upgrade-kelas5.sh`
- `deploy/windows/upgrade-kelas5.ps1`

Keduanya menolak berjalan bila berkas penanda `writable/kelas5-upgrade.done` sudah ada (mencegah reset ulang tak sengaja), lalu menjalankan:

| # | Langkah | Linux (sebagai `deploy`) |
|---:|---|---|
| 1 | Pastikan tidak ada kelas aktif (penjaga 10 menit) | (sama dengan `deploy.sh`) |
| 2 | Backup database + aset + `.env` | `bash deploy/linux/backup.sh` |
| 3 | Mode pemeliharaan | `touch writable/pemeliharaan.flag` |
| 4 | Kode & dependency | `git pull --ff-only origin main` · `composer install --no-dev --optimize-autoloader` |
| 5 | Migration (akun `gelita_migrate`) | `database_default_DSN=… php spark migrate` |
| 6 | Reset total (akun `gelita_migrate`, agar `TRUNCATE` dan kode siswa mulai dari GLT-000001) | `database_default_DSN=… php spark gelita:reset --ya` |
| 7 | Naskah 11 baris & rekaman baru | `php spark gelita:story:update` · `php spark gelita:narration:import` |
| 8 | Cache, versi aset, PHP-FPM | `php spark cache:clear` · naikkan `gelita.assetVersion` · `systemctl reload php8.3-fpm` |
| 9 | Verifikasi (boleh ada peringatan mockup) | `php spark gelita:content:verify` |
| 10 | Buka situs, tulis penanda | `rm writable/pemeliharaan.flag` · `touch writable/kelas5-upgrade.done` |

Setelah itu, di panel admin:

1. **Konten → Narasi** → *Setujui semua narasi draft* (ID dan EN) setelah didengarkan.
2. **Gambar & mockup** → unggah 45 gambar asli (daftar Dok 2 §10.4). Gambar asli yang sudah pernah diunggah dengan kunci lama otomatis terpakai.
3. **Penelitian** → periksa *Daftar siap penelitian* hijau (`php spark gelita:content:verify --siap-penelitian` lolos), lalu ubah status menjadi **berjalan**.
4. Uji asap: daftar satu akun uji lewat 3 langkah, mainkan satu tantangan tiap jenis, cek baris di **Data mentah**, lalu hapus akun uji itu lewat **Tata kelola** (atau jalankan `gelita:reset` sekali lagi **sebelum** siswa pertama bermain — hapus dulu penanda `kelas5-upgrade.done` secara sadar).

Deploy berikutnya kembali memakai `deploy.sh`/`deploy.ps1` biasa.

**Rollback** (bila langkah 5–9 gagal): situs tetap dalam pemeliharaan; pulihkan backup langkah 2 (database + aset), `git reset --hard <commit sebelum rilis>`, `composer install`, buka situs. Karena reset tidak dapat dibatalkan, backup langkah 2 adalah satu-satunya jalan kembali — jangan lewati.
