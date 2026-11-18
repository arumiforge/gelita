# Prompt Gambar Antarmuka, Latar, dan Pustaka 3D

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 18 November 2026 · Lampiran telaah gambar (`ai-studio/game-images/REVIEW.md`) dan perombakan antarmuka (`docs/PROGRESS.md` → *Interface overhaul*) · Status: **`draf pengembang`**, `asal: pengembang` (disusun pengembang dari wawancara 18 November 2026; [04](04_CATATAN_KEPUTUSAN.md))

Pengembang meminta gambar baru untuk bagian antarmuka yang belum segaya dengan *emas berukir*, penanda status tantangan, ikon aplikasi, lentera dan pin wilayah, lencana, avatar, latar halaman awal, empat slide cerita penutup, dan gambar Pustaka yang masih bergaya lukisan. Dokumen ini memuat prompt siap tempel ke alat pembuat gambar (bahasa Inggris) dengan pola yang sama seperti [37](37_PROMPT_GAMBAR_SUSUN_DAN_CERITA.md), [38](38_PROMPT_PETA_LIMA_TANTANGAN_DAN_SKENARIO.md), dan [39](39_PROMPT_GAMBAR_PERBAIKAN_TELAAH.md). Daftar kerjanya di `ai-studio/chatgpt-images/TASKS.md`: baris `U-` (antarmuka), `S-` (latar dan cerita), dan `PK-` (Pustaka).

Telaah dan pesanan ini pekerjaan pengembang sendiri, bukan validasi ahli ([01 → Peran pengembang, peneliti, dan ahli](01_REVISI_10_ANALITIK.md)).

## Keputusan pengembang yang dipakai (18 November 2026)

1. **Satu keluarga emas berukir.** Semua bagian antarmuka di dokumen ini dibuat dari satu papan acuan gaya baru (`U-00`), yang digambar lebih dulu dan dilampirkan ke setiap baris `U-` lain. Acuan gaya lama (lembar telaah 19) tidak dipakai lagi untuk antarmuka; yang bergaya halus (17 sampai 24) ditandai `drop`.
2. **Satu pelat per warna.** Pelat emas, biru tua, dan hijau masing-masing satu gambar dengan hiasan hanya di kedua ujung dan bagian tengah polos, sehingga satu gambar muat di setiap lebar tanpa hiasan yang teregang. Satu bingkai jendela (hiasan hanya di keempat sudut) dan satu bilah perunggu. Setiap gambar mengisi semua slot lamanya (tabel *Pemasangan*).
3. **Penanda status tantangan** memakai cincin yang sama dengan tombol bundar `round-*`, dan setiap keadaan punya simbolnya sendiri, supaya warna tidak menjadi satu-satunya penanda: tuntas = centang emas di bidang hijau, terbuka = serpihan cahaya emas di bidang biru tua, terkunci = gembok emas timbul di bidang batu gelap.
4. **Penanda di peta wilayah** (sudah dibangun 18 November 2026): medali 40 dp di tepi depan setiap lapangan, di bawah gambar properti lapangan itu, dengan papan nama kecil; seluruh lapangan tetap bisa diketuk.
5. **Tombol Kenali** memakai lentera wilayah masing-masing (Temanggung: dua gunung dan daun kopi; Magelang: stupa; Wonosobo: candi gaya Arjuna). `icon-kenali` dibuang.
6. **Ikon aplikasi:** Lentera Kedu emas berukir di bidang biru tua, satu induk 1024 × 1024 untuk semua ukuran, ditambah versi sederhana untuk favicon.
7. **Latar halaman awal:** garis gunung Kedu yang sebenarnya dari foto peneliti, bagian tengah dan setengah bawah tenang dan gelap, suasana biru tua dan emas.
8. **Cerita penutup:** gambar slide 3 sekarang (Jaka mengangkat lentera di puncak bukit) pindah ke slide 1 karena cocok dengan naskahnya; slide 2, 3, 4, dan 5 digambar baru, tanpa orang.
9. **Pustaka:** semua gambar bergaya lukisan dibuat ulang dalam gaya 3D; tidak ada anak berlurik yang muncul berulang; orang hanya muncul bila topiknya membutuhkan; tembakau hanya sebagai daun di ladang, tanpa rokok atau asap.
10. **Lencana wilayah dan sepuluh avatar** ikut dalam keluarga ini (cincin emas berukir; lambang tetap, kecuali lencana Wonosobo dan avatar carica).

## Ringkasan

| Kode | Gambar | Nomor di telaah | Yang dibuat |
|---|---|---|---|
| `U-00` | Papan acuan gaya antarmuka (baru, `reference.27`) | — | Satu lembar contoh bagian emas berukir yang menjadi lampiran semua baris `U-` |
| `U-01` | Pelat emas | 27–30, 33, 91 | Pengganti `gold-pill-1` sampai `-4`, `kenal-plate-gold`, `ui.result-button-primary` |
| `U-02` | Pelat biru tua | 34, 90, 92 | Pengganti `kenal-plate-navy`, `ui.result-button-secondary`, `ui.result-button-detail` |
| `U-03` | Pelat hijau | 48 | Pengganti `plate-green` |
| `U-04` | Bingkai jendela dan panel hasil | 38, 39, 94 | Pengganti `modal-frame-16x9`, `modal-frame-4x3`, `ui.result-panel` |
| `U-05` | Bilah perunggu | 35, 37 | Pengganti `loading-track`, `map-progress-track` |
| `U-06` | Pelat judul peta wilayah | 36 | Pengganti `map-head-plate` |
| `U-07` | Bingkai Serpihan Cahaya (HUD) | 32 | Suntingan `hud-shards-frame`: hiasan diseragamkan, tata letak tetap |
| `U-08` | Isi bilah Serpihan Cahaya | 31 | Pengganti `hud-shards-fill` |
| `U-09` | Penanda status: tuntas | 52 | Pengganti `status-done` |
| `U-10` | Penanda status: terbuka | 54 | Pengganti `status-progress` |
| `U-11` | Penanda status: terkunci | 53 | Pengganti `status-open` |
| `U-12` | Bintang | 51, 96 | Pengganti `star`, `ui.result-star` |
| `U-13` | Panah kotak pilihan | 50 | Pengganti `select-arrow` |
| `U-14` | Lentera Kedu (induk) | 124 | Pengganti `ui.lantern`; induk lentera wilayah dan ikon aplikasi |
| `U-15` – `U-17` | Lentera Kenali Temanggung, Magelang, Wonosobo | 98–100 | Suntingan `U-14` dengan tanda wilayah di kaca |
| `U-18` – `U-20` | Pin peta Temanggung, Magelang, Wonosobo | 101–103 | Cincin emas berukir, lambang tetap |
| `U-21` – `U-23` | Lencana Temanggung, Magelang, Wonosobo | 82–84 | Cincin emas berukir; Wonosobo bercandi gaya Arjuna |
| `U-24` – `U-33` | Avatar 1 sampai 10 | 114–123 | Suntingan: cincin emas berukir, lambang tetap; avatar 7 (carica) lambangnya dibetulkan |
| `U-34` | Ikon aplikasi (induk) | 21–25, 108, 110–113 | Satu induk 1024 × 1024 untuk semua ukuran |
| `U-35` | Favicon | 109 | Versi sederhana yang terbaca di 16 px |
| `S-W` | Latar halaman awal & akun `bg.welcome` | — | Garis gunung Kedu, tenang di tengah dan bawah |
| `S-02` | Cerita penutup slide 2 *Kabut Tersibak* | 200 | Kabut terangkat dari tiga wilayah |
| `S-03` | Cerita penutup slide 3 *Mbah Kedu Pulih* | 201 | Joglo penjaga pagi hari, lentera menyala terang |
| `S-04` | Cerita penutup slide 4 *Janji Jaka* | 202 | Halaman SD di dataran tinggi Kedu |
| `S-05` | Cerita penutup slide 5 *Penjaga Lentera* | 203 | Dataran Kedu saat fajar dengan lentera kecil di setiap desa |
| `PK-01` – `PK-32` | Gambar Pustaka bergaya lukisan | 282–340 | Dibuat ulang dalam gaya 3D (bagian *Pustaka*) |

Slide 1 cerita penutup tidak dipesan: berkas slide 3 sekarang dipindahkan ke slide 1 (*Pemasangan*).

## Ukuran yang diukur (Chromebook 1366 × 768 dan 1366 × 657, 18 November 2026)

Diukur pengembang di instalasi pengembangan dengan peramban otomatis, per bagian: ukuran gambar seperti tampil di layar. Kanvas setiap pesanan kira-kira dua sampai tiga kali ukuran tampil terbesar, supaya tetap tajam di layar berkepadatan tinggi, dengan perbandingan sisi yang sama dengan kotaknya bila kotaknya tetap.

| Bagian | Tampil (1366 × 768) | Tampil (1366 × 657) | Catatan |
|---|---|---|---|
| Pelat emas (`gold-pill-*`, `kenal-plate-gold`) | tinggi 43–75 px, lebar 134–412 px | tinggi 37–58 px | Satu pelat, banyak lebar: hiasan hanya di ujung |
| Pelat biru tua (`kenal-plate-navy`, tombol hasil) | tinggi 38–68 px, lebar 132–398 px | — | Juga papan nama |
| Pelat hijau | 119 × 43 | 102 × 37 | |
| Bingkai jendela 4:3 | 597 × 344 | 511 × 294 | |
| Panel hasil | 1085 × 528 | — | Juga tepi keterangan kota di Peta Kedu (213 × 81) |
| Bilah perunggu | 191 × 13 (Pustaka), 329 × 19 (peta wilayah) | 163 × 11, 281 × 16 | |
| Pelat judul peta wilayah | 329 × 74 | 281 × 63 | Kotak tetap, perbandingan 4,45 : 1 |
| Bingkai Serpihan Cahaya | 461 × 95 (Peta Kedu), 384 × 79, 273 × 56 | — | Perbandingan 4,86 : 1 |
| Penanda status | 40 (peta wilayah, baru), 26 (daftar), 19 (lentera HUD) | 34, 22, 16 | Harus terbaca di 19 px |
| Bintang | 61 (hasil), 41 (Peta Kedu), 21–27 (daftar) | — | |
| Panah kotak pilihan | 23 × 21 | — | |
| Ikon Kenali | 43 × 43 | 37 × 37 | Kini lentera wilayah |
| Pin Peta Kedu | 124–134 | 106–114 | |
| Avatar | 68–77 (profil), 45 (HUD) | — | |

## Aturan bersama

**Gaya antarmuka (`U-00` sampai `U-35`).** Gaya *emas berukir* yang disetujui peneliti ([39 → Aturan bersama](39_PROMPT_GAMBAR_PERBAIKAN_TELAAH.md)): bingkai emas tebal berukir dalam dengan hiasan ukel, bidang batu gelap atau biru tua berpola awan samar, simbol timbul dari emas yang sama, satu arah cahaya dari kiri atas, dirender sebagai benda 3D. Hijau dan merah hanya warna bidang, selalu bersama simbolnya. Setiap prompt `U-` dimulai dengan blok `GELITA CARVED GOLD UI — 2026-11-18` di bawah ini, kata per kata.

```text
GELITA CARVED GOLD UI — 2026-11-18
Interface part for GELITA, an educational game for Indonesian children aged 10–11, shown on a school Chromebook. One family with the attached GELITA style board: thick antique-gold frames carved in deep relief with Javanese ukel scroll ornaments, faces of dark navy stone (#0B1320 to #1B2740) with a very faint cloud pattern, symbols embossed in the same gold, one light from the upper left, soft contact shadow, rendered as a solid 3D object. Gold #DFC087 with highlights #F0DFB8 and warm brown shadows; no chrome, no neon, no glow line, no rainbow sheen. Ornaments are the same carving family on every part: the same scroll, the same small diamond studs, the same bead edge.
It is a user-interface element, not a scene: no background scenery, no text, letters, numbers or logos, no people. Genuine transparent background outside the object unless the brief says otherwise; no fake checkerboard.
```

**Gambar isi (`S-`, `PK-`).** Blok `GELITA ACTIVE VISUAL DIRECTION — 2026-10-11` dari katalog, kata per kata, seperti di 37 dan 39. Tanpa sapuan kuas dan tanpa tekstur kertas. Latar cerita (`S-`) mengikuti aturan latar cerita di `ai-studio/chatgpt-images/AGENTS.md` (sepertiga bawah tenang, tanpa orang, tanpa berkas sinar seperti `R-27` sampai `R-30`).

**Suntingan, bukan gambar baru** (`U-07`, `U-15` sampai `U-17`, `U-24` sampai `U-33`): seperti `R-19` dan `R-25`, sunting dengan masker hanya di bagian yang disebut prompt; bagian lain terkunci. Mulai lagi dari gambar asal, jangan dari hasil yang gagal.

**Urutan.** `U-00` dibuat dan diterima pengembang lebih dulu; baris `U-` lain berstatus `blocked: waiting for U-00` sampai itu. `U-14` dibuat sebelum `U-15` sampai `U-17` dan `U-34`, `U-35`. Pustaka dikerjakan sesudah antarmuka dan cerita.

## Acuan gaya

### U-00 · Papan acuan gaya antarmuka emas berukir

| | |
|---|---|
| Berkas | baru: `ai-studio/game-images/assets/referensi/reference.27.webp` |
| Kanvas | 1536 × 1024, latar biru tua polos (bukan transparan) |
| Lampiran | `public/assets/ui/round-back.webp` (cincin dan simbol timbul acuan), `public/assets/ui/plate-gold-1.webp` (ujung pelat), `public/assets/ui/modal-frame-4x3.webp` (sudut bingkai), `public/assets/ui/ornate-crest.webp` (hiasan mahkota), `public/assets/ui/round-lang-id.webp` (emas berukir acuan) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: reference.27 (U-00 · carved gold interface style board)
Make ONE style board that shows the parts of a single carved-gold interface family laid out on a plain flat deep-navy (#0B1320) background, evenly spaced, with clear empty space between the parts. It will be attached to every later interface order, so every part must use exactly the same gold, the same carving, the same ornament family and the same light. Take the finish and depth from the attached round button and plates, and make their differing edge ornaments ALIKE: one scroll motif, one diamond stud, one bead edge for all.
Show, left to right and top to bottom:
1. A round button, 300 px across: thick carved gold ring with four small diamond studs at 12, 3, 6 and 9 o'clock, an empty dark navy stone face.
2. The same round button with an embossed gold right-pointing arrow on the face, to show how a symbol is embossed.
3. A short horizontal plate, about 600 × 120 px: navy stone face, thin raised gold border along the top and bottom, a carved gold end cap with one scroll curl at each end.
4. The same plate with a gold face (warm polished gold with a soft inner bevel) and the same end caps.
5. One corner of a dialog frame, about 300 × 300 px: a carved gold corner ornament where two plain gold borders meet, navy face inside.
6. A small carved gold crest, about 200 px wide, of the same scroll family.
Lighting from the upper left on every part. No text, letters, numbers, labels or colour swatches. WebP, 1536 × 1024, named reference.27.webp.
```

## Pelat, bingkai, dan bilah

### U-01 · Pelat emas

| | |
|---|---|
| Berkas | baru: `ui-plate-gold.webp` (pengganti `gold-pill-1` sampai `-4`, `kenal-plate-gold`, `ui.result-button-primary`) |
| Kanvas | 1600 × 200, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp` (`U-00`, setelah diterima), `public/assets/ui/gold-pill-1.webp` (pelat sekarang), `public/assets/ui/plate-gold-1.webp` |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: ui-plate-gold (U-01 · gold button plate, stretchable)
A long horizontal button plate with a warm polished GOLD face and a thin raised carved gold border, from the attached style board. Dark brown-navy text will be written on it later by the game.
Canvas exactly 1600 × 200 pixels, transparent outside the plate; the plate fills the canvas with an even 6-pixel margin.
ORNAMENTS ONLY AT THE TWO ENDS: each end carries the style board's carved end cap (a rounded pointed cap with one scroll curl and one small diamond stud) inside the first and last 200 pixels. From x = 200 to x = 1400 the plate is completely plain: the same border along the top and bottom and the same gold face with one soft even bevel, no ornament, no gem, no highlight spot and no gradient that changes along the length, so that this middle part can be stretched or shortened without any visible distortion.
The face is calm enough that text stays readable: no strong reflections across the middle. WebP, named ui-plate-gold.webp.
```

### U-02 · Pelat biru tua

| | |
|---|---|
| Berkas | baru: `ui-plate-navy.webp` (pengganti `kenal-plate-navy`, `ui.result-button-secondary`, `ui.result-button-detail`) |
| Kanvas | 1600 × 200, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `ai-studio/chatgpt-images/out/ui-plate-gold.webp` (`U-01`: bentuk dan ujung yang sama), `public/assets/ui/kenal-plate-navy.webp` |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: ui-plate-navy (U-02 · navy button plate, stretchable)
Exactly the same plate shape, border and end caps as the attached gold plate (U-01), but with a dark NAVY stone face with a very faint even cloud grain. Light cream text will be written on it later by the game.
Canvas exactly 1600 × 200 pixels, transparent outside the plate; even 6-pixel margin.
ORNAMENTS ONLY AT THE TWO ENDS, inside the first and last 200 pixels, identical to U-01. From x = 200 to x = 1400 the plate is completely plain (same border top and bottom, same navy face, no ornament, no highlight spot, no gradient along the length), so it can be stretched without visible distortion. WebP, named ui-plate-navy.webp.
```

### U-03 · Pelat hijau

| | |
|---|---|
| Berkas | baru: `ui-plate-green.webp` (pengganti `plate-green`) |
| Kanvas | 1600 × 200, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `ai-studio/chatgpt-images/out/ui-plate-navy.webp` (`U-02`), `public/assets/ui/status-done.webp` (hijau yang dipakai) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: ui-plate-green (U-03 · green button plate, stretchable)
Exactly the same plate shape, border and end caps as the attached navy plate (U-02), but with a deep forest-green stone face (#1E5A3A to #2F7A4F) with a very faint even grain. Light cream text will be written on it later by the game.
Canvas exactly 1600 × 200 pixels, transparent outside the plate; even 6-pixel margin. Ornaments only inside the first and last 200 pixels; plain between them, as U-02. WebP, named ui-plate-green.webp.
```

### U-04 · Bingkai jendela dan panel hasil

| | |
|---|---|
| Berkas | baru: `ui-frame.webp` (pengganti `modal-frame-4x3`, `modal-frame-16x9`, `ui.result-panel`) |
| Kanvas | 1600 × 1200, latar transparan di luar bingkai |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `public/assets/ui/modal-frame-4x3.webp` (bingkai sekarang), `ai-studio/game-images/assets/antarmuka/ui.result-panel.webp` (panel hasil sekarang) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: ui-frame (U-04 · dialog frame and result panel, nine-slice)
A rectangular dialog frame: a dark navy stone face inside a carved gold frame, from the attached style board. The game shows it at many sizes (from 600 × 340 to 1090 × 530 pixels), so it must work as a nine-slice picture.
Canvas exactly 1600 × 1200 pixels, transparent outside the frame; even 8-pixel margin.
ORNAMENTS ONLY IN THE FOUR CORNERS: each corner carries the style board's carved corner ornament inside a 260 × 260 pixel square. Between the corners the four edges are plain: one straight gold border of even thickness (about 34 pixels) with the bead edge, no ornament, no gem, no centre crest. The navy face inside is flat and even (very faint uniform grain only, no mountains, no clouds, no vignette, no light spot), so the edges and the face can be stretched without visible distortion.
WebP, named ui-frame.webp.
```

### U-05 · Bilah perunggu

| | |
|---|---|
| Berkas | baru: `ui-track.webp` (pengganti `loading-track`, `map-progress-track`) |
| Kanvas | 1600 × 96, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `public/assets/ui/map-progress-track.webp` (bilah sekarang) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: ui-track (U-05 · progress track, stretchable)
A long horizontal progress track: a carved gold rim around an EMPTY dark channel (deep navy, slightly recessed), into which the game paints the gold fill and the notches itself.
Canvas exactly 1600 × 96 pixels, transparent outside the track; even 4-pixel margin. The channel runs the whole length and is about 40 pixels tall, centred.
ORNAMENTS ONLY AT THE TWO ENDS: a small carved end cap inside the first and last 96 pixels. Between them the rim is plain (same thickness top and bottom, no ornament, no notches, no studs) and the channel is empty and even, so the middle can be stretched without visible distortion. WebP, named ui-track.webp.
```

### U-06 · Pelat judul peta wilayah

| | |
|---|---|
| Berkas | pengganti `public/assets/ui/map-head-plate.webp` |
| Kanvas | 1336 × 300, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `public/assets/ui/map-head-plate.webp` (pelat sekarang: bentuk dan isi), `ai-studio/chatgpt-images/out/ui-plate-navy.webp` (`U-02`) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: map-head-plate (U-06 · region title plate)
A wide title plate for a region name ("Temanggung", "Magelang", "Wonosobo" are written on it later by the game in large cream letters): a navy stone face in a carved gold frame, with the style board's small crest centred on the top edge and a carved end cap at the left and right ends.
Canvas exactly 1336 × 300 pixels, transparent outside the plate. The plate is shown at a fixed 329 × 74 size, so draw it at exactly this shape; do not leave empty margins larger than 8 pixels. The crest may rise above the plate's top edge but stays inside the canvas.
The face between the end caps is calm and even so the name reads clearly. WebP, named map-head-plate.webp.
```

### U-07 · Bingkai Serpihan Cahaya (suntingan)

| | |
|---|---|
| Berkas | pengganti `public/assets/ui/hud-shards-frame.webp` |
| Kanvas | 1400 × 288, latar transparan (sama dengan berkas sekarang) |
| Lampiran | `public/assets/ui/hud-shards-frame.webp` (gambar yang disunting), `ai-studio/chatgpt-images/out/reference.27.webp` |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: hud-shards-frame (U-07 · shard counter frame, EDIT)
Edit the attached shard counter frame; do not draw a new one. Keep EXACTLY the same canvas (1400 × 288), outline, the round crystal socket on the left at the same place and size, the empty groove at the same place and size, and the small empty strip above the groove where the game writes its label. Change ONLY the carving of the gold frame and its end ornaments so that they match the attached style board (same scroll, same diamond studs, same bead edge, same gold and light from the upper left).
The groove stays empty and dark; the crystal socket stays empty (the game draws the glowing crystal). No text. Transparent outside the frame. WebP, named hud-shards-frame.webp.
```

### U-08 · Isi bilah Serpihan Cahaya

| | |
|---|---|
| Berkas | pengganti `public/assets/ui/hud-shards-fill.webp` |
| Kanvas | 512 × 32, tanpa latar transparan |
| Lampiran | `public/assets/ui/hud-shards-fill.webp` (isi sekarang), `ai-studio/chatgpt-images/out/reference.27.webp` |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: hud-shards-fill (U-08 · shard bar fill, tileable)
A horizontal strip of warm liquid gold light that fills the shard bar: molten gold (#DFC087 to #F0DFB8) with a soft lighter band along the top third and tiny scattered sparkles.
Canvas exactly 512 × 32 pixels, filled edge to edge (no transparency, no border, no end caps). The strip must TILE SEAMLESSLY left to right: the left and right edges match exactly, and there is no light spot, gradient or feature that would repeat visibly. WebP, named hud-shards-fill.webp.
```

## Penanda, bintang, dan panah

Tiga penanda status adalah satu set: cincin, ukuran, dan cahayanya sama persis; hanya bidang dan simbolnya yang berbeda. Simbolnya besar dan tebal karena tampil sekecil 19 px. Nama berkas mengikuti kode lama: `status-open` adalah keadaan **terkunci**.

### U-09 · Penanda status tuntas

| | |
|---|---|
| Berkas | pengganti `public/assets/ui/status-done.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `public/assets/ui/round-back.webp` (cincin acuan), `public/assets/ui/status-done.webp` (penanda sekarang) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: status-done (U-09 · challenge status: completed)
A round status medallion with exactly the carved gold ring of the attached round button (same thickness, four diamond studs at 12, 3, 6 and 9 o'clock). The face is a deep green stone (#1E5A3A to #2F7A4F). On the face, one large, bold, embossed gold CHECK MARK that fills about half of the face, with a thick stroke.
It must read clearly at 19 pixels wide: no fine detail on the face, the check mark has strong contrast against the green. Canvas exactly 512 × 512, the medallion filling it with an even 12-pixel margin, transparent outside. WebP, named status-done.webp.
```

### U-10 · Penanda status terbuka

| | |
|---|---|
| Berkas | pengganti `public/assets/ui/status-progress.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `ai-studio/chatgpt-images/out/status-done.webp` (`U-09`: cincin yang sama), `ai-studio/game-images/assets/antarmuka/ui.result-medal.webp` (bentuk serpihan cahaya) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: status-progress (U-10 · challenge status: open, ready to play)
Exactly the same ring, size and light as the attached completed medallion (U-09). The face is deep navy stone. On the face, one large embossed LIGHT SHARD: a tall pointed gold crystal shard, like the one on the attached result medal, glowing softly warm from inside, filling about half of the face.
It must read clearly at 19 pixels wide and must not look like a check mark or a lock. Canvas exactly 512 × 512, even 12-pixel margin, transparent outside. WebP, named status-progress.webp.
```

### U-11 · Penanda status terkunci

| | |
|---|---|
| Berkas | pengganti `public/assets/ui/status-open.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `ai-studio/chatgpt-images/out/status-done.webp` (`U-09`), `public/assets/ui/round-lock.webp` (bentuk gembok) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: status-open (U-11 · challenge status: locked)
Exactly the same ring, size and light as the attached completed medallion (U-09), the ring slightly duller (less shine, same colour). The face is dark grey-brown stone. On the face, one large embossed gold PADLOCK, closed, with a thick shackle, filling about half of the face, like the attached lock button.
It must read clearly at 19 pixels wide. Canvas exactly 512 × 512, even 12-pixel margin, transparent outside. WebP, named status-open.webp.
```

### U-12 · Bintang

| | |
|---|---|
| Berkas | pengganti `public/assets/ui/star.webp` dan `ai-studio/game-images/assets/antarmuka/ui.result-star.webp` |
| Kanvas | 256 × 256, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `public/assets/ui/star.webp` (bintang sekarang) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: star (U-12 · game star)
One five-pointed star of solid carved gold: rounded points, a raised centre ridge on each arm, the style board's bead edge along the outline, light from the upper left, a soft warm inner glow. No face, no sparkle burst around it.
It must read at 21 pixels and look rich at 61 pixels. The game also shows it grey for an empty star, so its shape alone must be clear. Canvas exactly 256 × 256, the star filling it with an even 10-pixel margin, transparent outside. WebP, named star.webp.
```

### U-13 · Panah kotak pilihan

| | |
|---|---|
| Berkas | pengganti `public/assets/ui/select-arrow.webp` |
| Kanvas | 160 × 145, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `public/assets/ui/select-arrow.webp` (panah sekarang) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: select-arrow (U-13 · dropdown arrow)
One downward-pointing carved gold triangle with softly rounded corners and the style board's bead edge, a shallow embossed centre line, light from the upper left. Simple and bold: it is shown at 23 × 21 pixels.
Canvas exactly 160 × 145 pixels, the triangle filling it with an even 8-pixel margin, transparent outside. WebP, named select-arrow.webp.
```

## Lentera, pin, dan lencana

### U-14 · Lentera Kedu (induk)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/ikon/ui.lantern.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `ai-studio/game-images/assets/ikon/ui.lantern.webp` (lentera sekarang), `ai-studio/game-images/assets/referensi/reference.16.webp` (lentera logo) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: ui.lantern (U-14 · the Lantern of Kedu, master)
The one Lantern of Kedu of the story: an old Javanese hexagonal lantern of carved antique gold, front view, with a domed top and a round hanging ring, pierced scroll work in the style board's carving family, six clear glass panels, and a calm warm flame inside (not too bright: the game adds light for each finished region). Three glass panels face the viewer; the centre front panel is plain clear glass with nothing on it.
It will also become the app icon, so the outline must read at 32 pixels: strong silhouette, no thin wire parts.
Canvas exactly 512 × 512, the lantern centred and filling about 88% of the height, transparent outside. WebP, named ui.lantern.webp.
```

### U-15 · Lentera Kenali Temanggung (suntingan `U-14`)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/ikon/map.lantern.temanggung.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/ui.lantern.webp` (`U-14`, gambar yang disunting), `ai-studio/game-images/assets/ikon/map.pin.temanggung.webp` (lambang wilayah) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: map.lantern.temanggung (U-15 · Kenali lantern, Temanggung, EDIT)
Edit the attached Lantern of Kedu with a mask over the CENTRE FRONT GLASS PANEL only; everything else stays locked (shape, gold, flame, size, position). On that panel, add the Temanggung mark as a bold gold emblem on the glass, lit from behind by the flame: two side-by-side volcano peaks with one coffee leaf in front, as on the attached Temanggung pin. Simple and thick so it reads at 43 pixels. No text. Transparent outside. WebP, named map.lantern.temanggung.webp.
```

### U-16 · Lentera Kenali Magelang (suntingan `U-14`)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/ikon/map.lantern.magelang.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/ui.lantern.webp` (`U-14`), `ai-studio/game-images/assets/ikon/map.pin.magelang.webp` (lambang wilayah) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: map.lantern.magelang (U-16 · Kenali lantern, Magelang, EDIT)
Edit the attached Lantern of Kedu with a mask over the CENTRE FRONT GLASS PANEL only; everything else stays locked. On that panel, add the Magelang mark as a bold gold emblem on the glass, lit from behind by the flame: one Borobudur perforated bell-shaped stupa with a square-based spire, as on the attached Magelang pin. Simple and thick so it reads at 43 pixels. No text. Transparent outside. WebP, named map.lantern.magelang.webp.
```

### U-17 · Lentera Kenali Wonosobo (suntingan `U-14`)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/ikon/map.lantern.wonosobo.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/ui.lantern.webp` (`U-14`), `ai-studio/game-images/assets/ikon/map.pin.wonosobo.webp` (lambang wilayah), `ai-studio/chatgpt-images/inbox/reference-Arjuna-researcher-single-temple-portrait-02.jpg` (bentuk Candi Arjuna) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: map.lantern.wonosobo (U-17 · Kenali lantern, Wonosobo, EDIT)
Edit the attached Lantern of Kedu with a mask over the CENTRE FRONT GLASS PANEL only; everything else stays locked. On that panel, add the Wonosobo mark as a bold gold emblem on the glass, lit from behind by the flame: the front silhouette of Candi Arjuna of Dieng (a small square Hindu temple on a raised base, a stepped tiered roof with small corner towers, a front porch), as in the attached photo and pin. Simple and thick so it reads at 43 pixels. No text. Transparent outside. WebP, named map.lantern.wonosobo.webp.
```

### U-18 · Pin peta Temanggung

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/ikon/map.pin.temanggung.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/game-images/assets/ikon/map.pin.temanggung.webp` (pin sekarang: lambang), `ai-studio/chatgpt-images/out/reference.27.webp`, `ai-studio/chatgpt-images/out/status-done.webp` (`U-09`: cincin) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: map.pin.temanggung (U-18 · Peta Kedu pin, Temanggung)
A map pin in the shape of the attached pin (a round head with a short pointed tip below), redrawn with the carved gold ring of the attached medallion (same carving, diamond studs at 3, 9 and 12 o'clock, bead edge) and a deep navy face. Keep the emblem of the attached pin on the face: two side-by-side volcano peaks with green coffee leaves in front, now rendered in the same 3D finish. The pin's tip is a carved gold point.
Shown at 106–134 pixels: bold shapes. Canvas exactly 512 × 512, the pin filling about 92% of the height, centred, transparent outside. WebP, named map.pin.temanggung.webp.
```

### U-19 · Pin peta Magelang

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/ikon/map.pin.magelang.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/game-images/assets/ikon/map.pin.magelang.webp` (pin sekarang: lambang), `ai-studio/chatgpt-images/out/map.pin.temanggung.webp` (`U-18`: bentuk pin yang sama) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: map.pin.magelang (U-19 · Peta Kedu pin, Magelang)
Exactly the same pin shape, ring, tip, size and light as the attached Temanggung pin (U-18). Keep the emblem of the attached Magelang pin on the navy face: one Borobudur perforated bell-shaped stupa with diamond-shaped openings and a spire, in gold. Canvas exactly 512 × 512, same placement as U-18, transparent outside. WebP, named map.pin.magelang.webp.
```

### U-20 · Pin peta Wonosobo

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/ikon/map.pin.wonosobo.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | `ai-studio/game-images/assets/ikon/map.pin.wonosobo.webp` (pin sekarang: lambang), `ai-studio/chatgpt-images/out/map.pin.temanggung.webp` (`U-18`), `ai-studio/chatgpt-images/inbox/reference-Arjuna-researcher-single-temple-portrait-02.jpg` |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: map.pin.wonosobo (U-20 · Peta Kedu pin, Wonosobo)
Exactly the same pin shape, ring, tip, size and light as the attached Temanggung pin (U-18). Keep the emblem of the attached Wonosobo pin on the navy face: the Candi Arjuna temple on a soft band of clouds, its form checked against the attached photo (small square temple on a raised base, stepped tiered roof, front porch). Canvas exactly 512 × 512, same placement as U-18, transparent outside. WebP, named map.pin.wonosobo.webp.
```

### U-21 · Lencana Temanggung

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/lencana/reward.badge.temanggung.webp` |
| Kanvas | 640 × 640, latar transparan |
| Lampiran | `ai-studio/game-images/assets/lencana/reward.badge.temanggung.webp` (lencana sekarang: lambang), `ai-studio/chatgpt-images/out/reference.27.webp`, `ai-studio/chatgpt-images/out/status-done.webp` (`U-09`) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: reward.badge.temanggung (U-21 · region badge, Temanggung)
A round reward medal: a wide carved gold ring in the style board's family (scroll carving all around, four diamond studs), with a deep navy face. Keep the emblem of the attached badge, re-rendered in the same 3D finish: the twin volcanoes Sindoro and Sumbing with coffee leaves and red coffee cherries in front. No tobacco, no text, no ribbon with letters.
Canvas exactly 640 × 640, the medal filling it with an even 16-pixel margin, transparent outside. WebP, named reward.badge.temanggung.webp.
```

### U-22 · Lencana Magelang

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/lencana/reward.badge.magelang.webp` |
| Kanvas | 640 × 640, latar transparan |
| Lampiran | `ai-studio/game-images/assets/lencana/reward.badge.magelang.webp` (lambang), `ai-studio/chatgpt-images/out/reward.badge.temanggung.webp` (`U-21`: medali yang sama) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: reward.badge.magelang (U-22 · region badge, Magelang)
Exactly the same medal, ring, size and light as the attached Temanggung badge (U-21). Keep the emblem of the attached Magelang badge on the navy face, re-rendered in the same 3D finish (Borobudur stupa motif). No text. Canvas exactly 640 × 640, same placement, transparent outside. WebP, named reward.badge.magelang.webp.
```

### U-23 · Lencana Wonosobo

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/lencana/reward.badge.wonosobo.webp` |
| Kanvas | 640 × 640, latar transparan |
| Lampiran | `ai-studio/chatgpt-images/out/reward.badge.temanggung.webp` (`U-21`), `ai-studio/game-images/assets/ikon/map.pin.wonosobo.webp` (lambang candi), `ai-studio/chatgpt-images/inbox/reference-Arjuna-researcher-single-temple-portrait-02.jpg` |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: reward.badge.wonosobo (U-23 · region badge, Wonosobo)
Exactly the same medal, ring, size and light as the attached Temanggung badge (U-21). On the navy face, a NEW emblem: Candi Arjuna of Dieng in gold (small square temple on a raised base, stepped tiered roof, front porch), checked against the attached photo, standing above a soft band of clouds, with the round shape of a mountain lake glinting turquoise below. Not a split gate (candi bentar). No text. Canvas exactly 640 × 640, same placement, transparent outside. WebP, named reward.badge.wonosobo.webp.
```

## Avatar (suntingan cincin)

Sepuluh avatar dipilih siswa saat mendaftar dan tampil di profil (68–77 px) dan HUD (45 px). Setiap baris adalah **suntingan**: masker hanya di cincin; lambang dan cakram di tengah terkunci, kecuali `U-30` (carica), yang lambangnya juga diganti. `U-24` dibuat lebih dulu; sembilan lainnya melampirkan hasilnya supaya cincinnya sama.

| Kode | Avatar | Berkas (pengganti) | Lampiran |
|---|---|---|---|
| `U-24` | `avatar.1` Lentera Kedu | `ai-studio/game-images/assets/ikon/avatar.1.webp` | `ai-studio/game-images/assets/ikon/avatar.1.webp` (disunting), `ai-studio/chatgpt-images/out/status-done.webp` (`U-09`: cincin), `ai-studio/chatgpt-images/out/reference.27.webp` |
| `U-25` | `avatar.2` Serpihan Cahaya | `…/ikon/avatar.2.webp` | `avatar.2.webp` (disunting), `ai-studio/chatgpt-images/out/avatar.1.webp` (`U-24`) |
| `U-26` | `avatar.3` Sindoro–Sumbing | `…/ikon/avatar.3.webp` | `avatar.3.webp`, `out/avatar.1.webp` |
| `U-27` | `avatar.4` stupa Borobudur | `…/ikon/avatar.4.webp` | `avatar.4.webp`, `out/avatar.1.webp` |
| `U-28` | `avatar.5` kuda lumping | `…/ikon/avatar.5.webp` | `avatar.5.webp`, `out/avatar.1.webp` |
| `U-29` | `avatar.6` kuluk Topeng Ireng | `…/ikon/avatar.6.webp` | `avatar.6.webp`, `out/avatar.1.webp` |
| `U-30` | `avatar.7` buah carica | `…/ikon/avatar.7.webp` | `avatar.7.webp`, `out/avatar.1.webp`, `ai-studio/chatgpt-images/inbox/reference-carica-whole-and-cut-12.jpg`, `ai-studio/chatgpt-images/inbox/reference-carica-cut-halves-10.jpg` |
| `U-31` | `avatar.8` Telaga Warna | `…/ikon/avatar.8.webp` | `avatar.8.webp`, `out/avatar.1.webp` |
| `U-32` | `avatar.9` kendang | `…/ikon/avatar.9.webp` | `avatar.9.webp`, `out/avatar.1.webp` |
| `U-33` | `avatar.10` bundengan | `…/ikon/avatar.10.webp` | `avatar.10.webp` (`R-19`), `out/avatar.1.webp` |

Kanvas setiap avatar 512 × 512, latar transparan. Satu prompt untuk sepuluh baris; ganti `{N}` dengan nomor avatar.

### U-24 sampai U-33 · Cincin avatar emas berukir

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: avatar.{N} (U-24 to U-33 · player avatar, ring EDIT)
Edit the attached avatar with a mask over its OUTER RING only. The cream disc in the middle and the emblem on it stay locked: same emblem, same size, same position, same colours. Replace the smooth ring with the carved gold ring of the attached medallion (U-09 for avatar 1, the attached new avatar 1 for the others): same thickness, same scroll carving, four diamond studs at 12, 3, 6 and 9 o'clock, light from the upper left. The outer diameter of the medallion stays the same as in the attached avatar.
No text. Canvas exactly 512 × 512, transparent outside. WebP, named avatar.{N}.webp.
```

**Tambahan untuk `U-30` (carica)**, ditempel sesudah blok di atas:

```text
For avatar 7 ONLY: the mask also covers the emblem. Replace the papaya-like fruit with a carica (mountain papaya of Dieng) as in the attached photos: two or three small yellow fruits, each the size of a hen's egg, with five shallow ridges and a pointed tip, and one fruit cut lengthwise showing thin pale-cream flesh and a white centre full of many small dark-brown seeds. Not a large papaya with thick orange flesh and big black seeds. Same size and position as the old emblem.
```

## Ikon aplikasi

### U-34 · Ikon aplikasi (induk)

| | |
|---|---|
| Berkas | baru: `app-icon-master.webp`; dari sini dibuat `public/assets/app/icon-512.png`, `icon-192.png`, `apple-touch-icon.png` (180), `icon-maskable-512.png`, dan entri `app.*` katalog |
| Kanvas | 1024 × 1024, tidak transparan |
| Lampiran | `ai-studio/chatgpt-images/out/ui.lantern.webp` (`U-14`), `ai-studio/chatgpt-images/out/reference.27.webp`, `ai-studio/game-images/assets/ikon/app.icon-512.webp` (ikon katalog sekarang) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: app-icon-master (U-34 · app icon)
The app icon of GELITA: the attached Lantern of Kedu (U-14), unchanged in shape, standing centred on a square deep-navy field (#0B1320 at the edges to #1B2740 in the middle, a soft warm glow behind the lantern). Around the lantern, a thin carved gold ring of the style board family, its outer edge at 80% of the canvas width.
This picture is NOT transparent: the navy field fills the whole square to every edge, with square corners (the device rounds them). Keep everything important inside the central circle of 80% of the width, because Android crops the icon to a circle. The lantern fills about 56% of the canvas height.
No text, letters or logo. Canvas exactly 1024 × 1024. WebP, named app-icon-master.webp.
```

### U-35 · Favicon

| | |
|---|---|
| Berkas | baru: `app-favicon-master.webp`; dari sini dibuat `favicon.ico` (16, 32, 48) dan `ai-studio/game-images/assets/ikon/app.favicon.webp` |
| Kanvas | 256 × 256, tidak transparan |
| Lampiran | `ai-studio/chatgpt-images/out/app-icon-master.webp` (`U-34`) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: app-favicon-master (U-35 · browser tab icon)
A very simplified version of the attached app icon for a browser tab, where it is shown at 16 and 32 pixels: the same lantern silhouette as a bold flat-shaded gold shape (domed top, hanging ring, body, warm flame window) on the same deep-navy square, no ring, no fine carving, no sparkles. Thick shapes only; the lantern fills about 78% of the height.
Canvas exactly 256 × 256, navy to every edge. WebP, named app-favicon-master.webp.
```

## Latar halaman awal dan cerita penutup

### S-W · Latar halaman awal dan akun

| | |
|---|---|
| Kode berkas | `bg.welcome` → simpan sebagai `bg.welcome.webp` (pengganti `ai-studio/game-images/assets/latar/bg.welcome.webp`) |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-twin-volcanoes-from-ridge-03.jpg` (Sindoro–Sumbing dan puncak jauh), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg`, `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (dataran; candinya tidak digambar) |
| Dipakai | Halaman awal (logo besar dan tombol MULAI di tengah), pilih masuk atau daftar, gerbang, cadangan latar cerita pembuka |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.welcome  (S-W · start page and account screens)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: no people at all. The attached photos are factual references for the mountains and the plain only; do not copy a photo.

SUBJECT AND COMPOSITION
Wide 16:9 background behind the game's large logo (upper middle), the START button (centre) and account panels (centre). The centre and the whole lower half must stay calm, dark and low in detail so that gold and cream interface parts on top read clearly; detail lives in the top band and at the far left and right edges.
The real skyline of the Kedu plain at deep-blue dusk, seen from the south: on the LEFT the twin volcanoes Mount Sumbing (broader) and Mount Sindoro (a clean cone) side by side; on the RIGHT, smaller and further away, Mount Merbabu (broad, several low summits) with Mount Merapi (a steeper cone) in front. Smooth slopes, no smoke, no eruption. The mountains sit low, their peaks below 40% of the height from the top, so the logo has open sky.
Sky: deep navy above, a thin warm gold band low behind the mountains. Below the mountains, the plain falls away into soft dark navy shade with only a FEW small warm lights (at most twenty, small, scattered far apart), not a carpet of lights.
Lower half: smooth, dark, quiet slope of the Menoreh hills with soft rounded tree silhouettes; no lights, no paths, no water. The whole picture carries the navy-and-gold mood of the interface: no green daylight, no orange sunset blaze.
Accuracy — No sea, coast, islands, cliffs, waterfalls, temples, stupas or castles. No light rays.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named bg.welcome.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### Cerita penutup (pengembang, 18 November 2026)

Slide 1 *Lentera Menyala Kembali* memakai gambar slide 3 sekarang (Jaka di puncak bukit mengangkat lentera di atas Kedu yang bercahaya), karena itulah isi naskah slide 1. Gambar slide 1 sekarang (beranda di tepi laut) dibuang: Kedu tidak berpantai. Slide 2 sampai 5 digambar baru dengan aturan `R-27` sampai `R-30`: tanpa orang (tokoh tampil sebagai avatar bundar), tanpa berkas sinar, sepertiga bawah tenang. Keempatnya satu urutan: dari kabut yang tersibak ke pagi yang cerah, dan berakhir saat fajar.

### S-02 · Kabut Tersibak (slide 2 cerita penutup)

| | |
|---|---|
| Kode berkas | `bg.cerita-penutup-02` → simpan sebagai `bg.cerita-penutup-02.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-over-highland-village-04.jpg` (Temanggung), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (Magelang), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Telaga-Warna-Dieng-05.jpg` (Wonosobo), `ai-studio/chatgpt-images/out/bg.cerita-intro-07.webp` atau berkas terpasangnya `ai-studio/game-images/assets/latar/bg.cerita-intro-07.webp` (tiga wilayah yang sama) |
| Naskah | "Kabut Lupa tersibak dari Temanggung, Magelang, sampai Wonosobo. Papan petunjuk terbaca jelas, relief bercerita dengan urutan yang benar, dan layar-layar di rumah warga tidak lagi berkabut. Pesan berantai tidak lagi langsung dipercaya dan dibagikan begitu saja." |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-penutup-02  (S-02 · ending, slide 2: the mist clears)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: no people at all. The attached photos are factual references for the land only; do not copy a photo.

SUBJECT AND COMPOSITION
Wide 16:9 cinematic landscape. Main subject inside the central 75% of the width and the central 80% of the height; horizon between 45% and 55% of the height; the lower third calm and darker for text panels; the top 8% free of important detail.
Early morning, the moment the Mist of Forgetting (Kabut Lupa, blue-grey #8C97AA to #5B6478) lifts. One wide panorama that shows the three regions side by side, as in the attached slide 7 picture: on the LEFT, Temanggung's green terraced slopes under the twin volcanoes Sindoro and Sumbing, a wooden signpost at a crossroads with BLANK boards now clearly visible; in the MIDDLE, far away on the plain, the outline of Borobudur on its hill; on the RIGHT, the Dieng plateau with a small turquoise lake. The last thin ribbons of blue-grey mist drift up and away into the sky and thin out; where the mist has gone, the land is fresh and clear in soft gold morning light.
Lower third: a quiet, slightly darker grassy slope in the foreground with soft shapes only.
Accuracy — Blank signboards (no letters). No tobacco, no smoke or steam, no crater. No sea. No light rays, sunbeams or god rays. Mist never shaped like letters or faces.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named bg.cerita-penutup-02.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### S-03 · Mbah Kedu Pulih (slide 3 cerita penutup)

| | |
|---|---|
| Kode berkas | `bg.cerita-penutup-03` → simpan sebagai `bg.cerita-penutup-03.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; `ai-studio/game-images/assets/latar/bg.intro.webp` (joglo rumah penjaga), `ai-studio/chatgpt-images/out/ui.lantern.webp` atau `ai-studio/game-images/assets/ikon/ui.lantern.webp` (Lentera Kedu), foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` |
| Naskah | "Badan Mbah terasa segar lagi, Le! Tapi ingat, cahaya ini bukan hanya milik lentera. Cahaya ini hidup setiap kali seseorang membaca dengan teliti dan berbagi dengan bijak." |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-penutup-03  (S-03 · ending, slide 3: Mbah Kedu recovers)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: no people at all (Mbah Kedu appears as his round avatar over the picture). Keep the keeper's joglo of the attached background recognisable: same house, same carved wooden pillars and roof.

SUBJECT AND COMPOSITION
Wide 16:9 scene. Main subject inside the central 75% of the width and the central 80% of the height; the lower third calm and darker for text panels; the top 8% free of important detail.
The keeper's joglo on its hill on a fresh, clear morning after the long night. On the open front veranda, on a carved wooden stand, the Lantern of Kedu (the attached lantern) burns bright and steady with a warm golden flame; its glow falls softly on the wooden floor and pillars. Morning dew on the potted plants and on the grass of the front yard; a few small flowers have opened. Beyond the yard, far away, the twin volcanoes Sindoro and Sumbing stand clear against a pale blue sky.
Lower third: the calm, slightly darker yard with soft grass and stepping stones only.
Accuracy — No sea, no light rays, no smoke or steam, no text on anything.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named bg.cerita-penutup-03.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### S-04 · Janji Jaka (slide 4 cerita penutup)

| | |
|---|---|
| Kode berkas | `bg.cerita-penutup-04` → simpan sebagai `bg.cerita-penutup-04.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-over-highland-village-04.jpg` (desa dataran tinggi), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg`; `ai-studio/game-images/assets/ikon/ui.lantern.webp` (Lentera Kedu) |
| Naskah | "Aku akan terus menjaga cahaya ini, Mbah: saat membaca, saat memakai layar, dan saat budaya Kedu tampil di layar. Supaya Kabut Lupa tidak pernah kembali." |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-penutup-04  (S-04 · ending, slide 4: Jaka's promise)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: no people at all (Jaka appears as his round avatar over the picture). The attached photos are factual references for the land only.

SUBJECT AND COMPOSITION
Wide 16:9 scene. Main subject inside the central 75% of the width and the central 80% of the height; the lower third calm and darker for text panels; the top 8% free of important detail.
The yard of a small village primary school in the Kedu highlands on a bright morning. A simple one-storey Javanese school building with a red clay-tile roof, cream plastered walls, wooden doors and windows, set among green terraced fields; behind it the twin volcanoes Sindoro and Sumbing under a clear sky. In front of the building, a reading corner under a small tree: a bamboo bench with a few closed books, and a small woven bamboo shelf with more books. By the school gate, the Lantern of Kedu (attached) hangs from a wooden post, burning softly, as a sign that the light is kept here.
Lower third: the calm, slightly darker swept-earth yard with soft shadows only.
Accuracy — No flag, signboard or any lettering on the building or books (spines plain). No screens, no tobacco, no smoke. No light rays.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named bg.cerita-penutup-04.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### S-05 · Penjaga Lentera (slide 5 cerita penutup)

| | |
|---|---|
| Kode berkas | `bg.cerita-penutup-05` → simpan sebagai `bg.cerita-penutup-05.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-twin-volcanoes-from-ridge-03.jpg`, `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg`; `ai-studio/game-images/assets/latar/bg.cerita-intro-01.webp` (slide 1 cerita pembuka: harus jelas berbeda) |
| Naskah | "Mulai hari ini, kamulah Penjaga Lentera, Le. Kamu juga, penjelajah. Bawalah cahaya ini ke sekolahmu, ke rumahmu, dan ke mana pun kamu pergi." |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-penutup-05  (S-05 · ending, slide 5: keepers of the lantern)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: no people at all. The attached photos are factual references for the land only.

SUBJECT AND COMPOSITION
Wide 16:9 cinematic landscape. Main subject inside the central 75% of the width and the central 80% of the height; horizon between 45% and 55% of the height; the lower third calm and darker for text panels; the top 8% free of important detail.
The whole Kedu plain at golden dawn, seen from a high grassy ridge: the twin volcanoes Sindoro and Sumbing on the left, Merbabu and Merapi on the right, the green plain and villages between them in soft gold morning light. In every village, school and house yard one small lantern still glows warm gold, like many small copies of the Lantern of Kedu spread across the land, so the light is now carried everywhere. In the foreground on the left, on a flat stone of the ridge, the Lantern of Kedu itself stands burning calmly.
Unlike the attached opening slide (blue hour, lights in the dark), this is bright dawn: a light sky, gold sun low behind the right mountains, fresh green land.
Lower third: the quiet, slightly darker grassy ridge with soft shapes only.
Accuracy — No sea, coast, cliffs or waterfalls. No smoke or eruption. No light rays, sunbeams or god rays.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named bg.cerita-penutup-05.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

## Pustaka

PUSTAKA_PLACEHOLDER

## Daftar periksa hasil

**Antarmuka (`U-`)**

- Satu keluarga dengan `U-00`: ukiran, butir intan, tepi manik, emas, dan arah cahaya sama. Letakkan hasil baru di samping papan acuan dan tombol `round-back` untuk membandingkan.
- Pelat dan bingkai (`U-01` sampai `U-05`): bagian tengah benar-benar polos. Uji dengan meregangkan bagian tengah dua kali lipat: tidak ada yang tampak teregang.
- Penanda status (`U-09` sampai `U-11`): perkecil ke 19 px dan 26 px; ketiganya dapat dibedakan tanpa melihat warna (ubah ke hitam-putih: centang, serpihan, gembok tetap terbaca).
- Bintang dan panah: terbaca di ukuran tampil terkecil (21 px dan 23 × 21 px).
- Suntingan (`U-07`, `U-15` sampai `U-17`, `U-24` sampai `U-33`): di luar bagian bermasker tidak ada yang berubah; bandingkan dengan gambar asal.
- Ikon aplikasi: lingkaran 80% di tengah memuat seluruh lentera; favicon terbaca di 16 px.
- Tanpa tulisan, angka, atau logo di semua bagian.

**Latar (`S-`)**

- Tanpa orang dan tanpa berkas sinar; sepertiga bawah tenang (khusus `S-W`: tengah dan setengah bawah tenang).
- `S-W`: gunung kembar di kiri, Merbabu–Merapi lebih kecil di kanan; tidak lebih dari dua puluh titik cahaya; tanpa laut atau candi.
- `S-02`: tiga wilayah terbaca; papan petunjuk polos; kabut menipis ke atas.
- `S-03`: joglo sama dengan `bg.intro`; lentera menyala terang di beranda.
- `S-04`: tanpa bendera, papan nama, atau tulisan; lentera di tiang gerbang.
- `S-05`: fajar terang, jelas berbeda dari slide 1 cerita pembuka.

**Pustaka (`PK-`)**

- Sesuai keterangan di bawah gambar (kolom *Keterangan*); gaya 3D, bukan lukisan.
- Tanpa anak berlurik yang berulang; orang hanya bila topiknya membutuhkan, dilihat dari samping atau belakang, ada orang dewasa bila ada anak.
- Tembakau hanya daun di ladang atau di rak jemur; tanpa rokok, asap, atau orang merokok.
- Tidak ada orang yang menyentuh stupa atau relief.

## Pemasangan

Semua hasil dipindahkan (bukan disalin) dari `ai-studio/chatgpt-images/out/`. Bagian antarmuka di `public/assets/ui/` dipanggil lewat `asset_url_versioned()`, jadi berkas cukup diganti di tempatnya.

| Kode | Ke mana | Pekerjaan sesudahnya |
|---|---|---|
| `U-00` | `ai-studio/game-images/assets/referensi/reference.27.webp` | Entri baru `reference.27` di `data.json` (kategori Acuan) dan baris `REVIEW.md` lembar 19 |
| `U-01` | `public/assets/ui/ui-plate-gold.webp` | Ganti `gold-pill-1` sampai `-4`, `kenal-plate-gold`, `ui.result-button-primary` dengan satu `border-image` (ujung 200 dari 1600); hapus berkas lama setelah CSS tidak memakainya |
| `U-02` | `public/assets/ui/ui-plate-navy.webp` | Sama, untuk `kenal-plate-navy`, `ui.result-button-secondary`, `ui.result-button-detail`, papan nama |
| `U-03` | `public/assets/ui/ui-plate-green.webp` | Sama, untuk `plate-green` |
| `U-04` | `public/assets/ui/ui-frame.webp` | Satu `border-image` sembilan bagian (sudut 260 dari 1600 × 1200) untuk jendela 4:3 dan 16:9 dan panel hasil; `fitFrame()` di `core/modal.js` tidak perlu memilih berkas lagi |
| `U-05` | `public/assets/ui/ui-track.webp` | `--loading-track` dan `--progress-track` memakai berkas ini dengan ujung 96 |
| `U-06`, `U-07`, `U-08`, `U-09` – `U-11`, `U-12` (`star.webp`), `U-13` | berkas yang sama di `public/assets/ui/` | `U-11`: hapus `icon('lock', 'badge-lock')` di `map-level.php` dan `lantern.php`, karena gemboknya kini di gambar. `U-12`: salin juga ke `ai-studio/game-images/assets/antarmuka/ui.result-star.webp` |
| `U-14` – `U-23` | `ai-studio/game-images/assets/ikon/` dan `…/lencana/` di tempat berkas lama | `php spark gelita:katalog:impor`. Tombol Kenali di Peta Kedu memakai `map.lantern.{wilayah}` (slot `map.lantern.*` di `ImageSlots` menjadi dipakai); `public/assets/ui/icon-kenali.webp` dihapus |
| `U-24` – `U-33` | `ai-studio/game-images/assets/ikon/avatar.{N}.webp` | `php spark gelita:katalog:impor` |
| `U-34`, `U-35` | `ai-studio/game-images/assets/ikon/` | Buat `app.icon-512`, `app.icon-192`, `app.apple-touch-icon` (180), `app.icon-maskable-512`, `app.favicon` dengan perkecilan proporsional; `public/assets/app/*.png` diganti, `icon.svg` diganti PNG di manifest dan `<link>` |
| `S-W`, `S-02` – `S-05` | `ai-studio/game-images/assets/latar/` di tempat berkas lama | `bg.cerita-penutup-03.webp` lama dipindahkan lebih dulu menjadi `bg.cerita-penutup-01.webp` (menggantikan beranda tepi laut). Entri `data.json` mendapat prompt baru dari bagian ini; `php spark gelita:katalog:impor` |
| `PK-` | `ai-studio/game-images/assets/pustaka/` di tempat berkas lama | Entri yang keterangannya berubah mendapat `caption` (`id`, `en`) di `data.json`; `php spark gelita:katalog:impor` |

Setelah dipasang: lembar telaah dibangun ulang (`node ai-studio/game-images/review/build-sheets.mjs`), halaman katalog dibangun ulang (`php spark gelita:katalog:halaman`), dan tata letak per layar diperiksa lagi di Chromebook 1366 × 768 (langkah 3 perombakan antarmuka).

## Kebijakan perubahan

Prompt di dokumen ini boleh disesuaikan pengembang bila hasil gambar gagal pada pemeriksaan yang sama tiga kali; perubahannya dicatat di [04](04_CATATAN_KEPUTUSAN.md) dan baris `TASKS.md`-nya diberi status `redo:` dengan alasan baru. Naskah cerita tidak diubah oleh dokumen ini.
