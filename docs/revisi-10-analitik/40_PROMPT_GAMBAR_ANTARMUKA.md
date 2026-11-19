# Prompt Gambar Antarmuka, Latar, dan Pustaka 3D

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 19 November 2026 · Lampiran telaah gambar (`ai-studio/game-images/REVIEW.md`) dan perombakan antarmuka (`docs/PROGRESS.md` → *Interface overhaul*) · Status: **`draf pengembang`**, `asal: pengembang` (disusun pengembang dari wawancara 18 November 2026; [04](04_CATATAN_KEPUTUSAN.md))

Pengembang meminta gambar baru untuk bagian antarmuka yang belum segaya dengan *emas berukir*, penanda status tantangan, ikon aplikasi, lentera dan pin wilayah, lencana, avatar, latar halaman awal, empat slide cerita penutup, dan gambar Pustaka yang masih bergaya lukisan. Dokumen ini memuat prompt siap tempel ke alat pembuat gambar (bahasa Inggris) dengan pola yang sama seperti [37](37_PROMPT_GAMBAR_SUSUN_DAN_CERITA.md), [38](38_PROMPT_PETA_LIMA_TANTANGAN_DAN_SKENARIO.md), dan [39](39_PROMPT_GAMBAR_PERBAIKAN_TELAAH.md). Daftar kerjanya di `ai-studio/chatgpt-images/TASKS.md`: baris `U-` (antarmuka), `S-` (latar dan cerita), dan `PK-` (Pustaka).

Telaah dan pesanan ini pekerjaan pengembang sendiri, bukan validasi ahli ([01 → Peran pengembang, peneliti, dan ahli](01_REVISI_10_ANALITIK.md)).

## Keputusan pengembang yang dipakai (18 November 2026)

1. **Satu keluarga emas berukir.** Semua bagian antarmuka di dokumen ini dibuat dari satu papan acuan gaya baru (`U-00`), yang digambar lebih dulu dan dilampirkan ke setiap baris `U-` lain. Acuan gaya lama (lembar telaah 19) tidak dipakai lagi untuk antarmuka; yang bergaya halus (17 sampai 24) ditandai `drop`.
2. **Satu pelat per warna.** Pelat emas, biru tua, dan hijau masing-masing satu gambar dengan hiasan hanya di kedua ujung dan bagian tengah polos, sehingga satu gambar muat di setiap lebar tanpa hiasan yang teregang. Satu bingkai jendela (hiasan hanya di keempat sudut) dan satu bilah perunggu. Setiap gambar mengisi semua slot lamanya (tabel *Pemasangan*).
3. **Penanda status tantangan** memakai cincin yang sama dengan tombol bundar `round-*`, dan setiap keadaan punya simbolnya sendiri, supaya warna tidak menjadi satu-satunya penanda: tuntas = centang emas di bidang hijau, terbuka = serpihan cahaya emas di bidang biru tua, terkunci = gembok emas timbul di bidang batu gelap.
4. **Penanda di peta wilayah** (sudah dibangun 18 November 2026): medali 40 dp di tepi depan setiap lapangan, di bawah gambar properti lapangan itu, dengan papan nama kecil; seluruh lapangan tetap bisa diketuk. Diganti 19 November 2026: medali di kiri papan nama dalam satu baris, di lapangan tepat di depan propertinya, dan mengetuknya memilih tantangan ([04](04_CATATAN_KEPUTUSAN.md)).
5. **Tombol Kenali** memakai lentera wilayah masing-masing (Temanggung: dua gunung dan daun kopi; Magelang: stupa; Wonosobo: candi gaya Arjuna). `icon-kenali` dibuang.
6. **Ikon aplikasi:** Lentera Kedu emas berukir di bidang biru tua, satu induk 1024 × 1024 untuk semua ukuran, ditambah versi sederhana untuk favicon.
7. **Latar halaman awal:** garis gunung Kedu yang sebenarnya dari foto peneliti, bagian tengah dan setengah bawah tenang dan gelap, suasana biru tua dan emas.
8. **Cerita penutup:** gambar slide 4 lama (Jaka mengangkat lentera di puncak bukit) pindah ke slide 1 karena cocok dengan naskahnya; slide 2, 3, 4, dan 5 digambar baru, tanpa orang.
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
| `U-07` | Bingkai Serpihan Cahaya (HUD) | 32 | Suntingan `hud-shards-frame`: hiasan diseragamkan, tata letak tetap; lubang kristal kosong, diisi `U-36` |
| `U-36` | Kristal Serpihan Cahaya (HUD) | 32 | Baru (19 November 2026): kristal tersendiri untuk lubang `U-07` |
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

Slide 1 cerita penutup tidak dipesan: berkas slide 4 lama dipindahkan ke slide 1 (*Pemasangan*). Dari enam gambar layar *Tantangan selesai*, lima diganti oleh `U-01`, `U-02`, `U-04`, dan `U-12`; yang keenam, alas tombol HUD `ui.result-hud-control` (no. 93), dibuang karena seluruhnya tertutup gambar tombol bundar di atasnya (45 px di bawah 51 px, diukur 18 November 2026). Medali serpihan di kiri panel hasil adalah `avatar.2`, jadi ikut berganti cincin lewat `U-25`.

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

**Gaya antarmuka (`U-00` sampai `U-36`, dan `SD-` di [41](41_RENCANA_TAMPILAN_SKENARIO_DAN_HUD.md#8-prompt-gambar-sd-)).** Gaya *emas berukir* yang disetujui peneliti ([39 → Aturan bersama](39_PROMPT_GAMBAR_PERBAIKAN_TELAAH.md)): bingkai emas tebal berukir dalam dengan hiasan ukel, bidang batu gelap atau biru tua berpola awan samar, simbol timbul dari emas yang sama, satu arah cahaya dari kiri atas, dirender sebagai benda 3D. Hijau dan merah hanya warna bidang, selalu bersama simbolnya. Setiap prompt `U-` dimulai dengan blok `GELITA CARVED GOLD UI — 2026-11-18` di bawah ini, kata per kata.

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

*Catatan pengembang 18 November 2026:* kalimat "the game draws the glowing crystal" di prompt ini keliru. Kristal selama ini digambar di dalam bingkai lama; permainan hanya menaruh cahaya di atasnya (`.lantern-gem` di `stage.css`, makin terang per wilayah tuntas). Hasil `U-07` (PR #420, ukiran baru, lubang kristal kosong) karena itu **ditahan**: dipasang sendirian, penghitung Serpihan Cahaya menampilkan lubang biru tua kosong. Pengembang memutuskan memesan **kristal tersendiri** di sesi lain: satu gambar transparan, kristal serpihan cahaya emas yang sama dengan `U-10`, mengisi lubang bingkai (di bingkai 1400 × 288: kira-kira x 98, y 45, 101 × 164 piksel, yaitu `left: 7%`, `top: 15.8%`, `width: 7.2%`, `height: 57%` dari `.lantern-gem`), kanvas sekitar 300 × 480, tanpa cahaya yang dilukis (cahayanya tetap dari permainan). Sesudah itu `U-07` dan kristal dipasang bersama: bingkai baru menggantikan `public/assets/ui/hud-shards-frame.webp`, kristal menjadi lapisan di bawah `.lantern-gem`.

*Catatan pengembang 19 November 2026:* prompt kristalnya `U-36` di bawah. Letak di atas diukur di bingkai lama; lubang bingkai baru (`ai-studio/chatgpt-images/out/hud-shards-frame.webp`, dari PR #420) diukur ulang: bidang biru tua lonjong x 74–221, y 51–238, yaitu 148 × 188 piksel di bingkai 1400 × 288. Kanvas `U-36` dibuat tepat tiga kali kotak itu, jadi kristal dipasang pada kotak lubang tanpa diukur lagi.

### U-36 · Kristal Serpihan Cahaya

| | |
|---|---|
| Berkas | baru: `public/assets/ui/hud-shards-crystal.webp` (lapisan di lubang `U-07`) |
| Kanvas | 444 × 564, latar transparan (tiga kali lubang bingkai, 148 × 188) |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/hud-shards-frame.webp` (`U-07`: bingkai baru dengan lubang kosong), `public/assets/ui/hud-shards-frame.webp` (bingkai lama: kristal di ujung kirinya), `public/assets/ui/status-progress.webp` (`U-10`: bentuk serpihan) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: hud-shards-crystal (U-36 · light shard crystal for the shard counter, separate layer)
ONE tall pointed crystal of golden light, the same shard shape as the crystal at the left end of the attached OLD shard counter and the embossed shard on the attached open-status medallion (U-10): faceted warm amber-gold crystal, softly lit from inside, its facets catching the one light from the upper left, rendered as a solid 3D object.
It is a separate layer that the game places inside the empty oval socket at the left end of the attached NEW shard counter frame (U-07); the canvas covers exactly that socket. Draw ONLY the crystal: no socket, no ring, no frame, no leaves, no background. The game lays its own glow over the crystal, so paint NO halo, rays, sparkles or light outside the crystal's own outline.
Canvas exactly 444 × 564 pixels, transparent around the crystal. The crystal stands upright in the centre with its tip pointing straight up, about 270 pixels wide and 470 pixels tall, so it sits inside the oval with a dark rim of socket visible all round. It must still read as a crystal when shown at 32 × 56 pixels. No text. WebP, named hud-shards-crystal.webp.
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
| Lampiran | `ai-studio/chatgpt-images/out/reference.27.webp`, `ai-studio/chatgpt-images/out/status-done.webp` (`U-09`: cincin yang sama), `public/assets/ui/hud-shards-frame.webp` (kristal di ujung kirinya: bentuk serpihan cahaya) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: status-progress (U-10 · challenge status: open, ready to play)
Exactly the same ring, size and light as the attached completed medallion (U-09). The face is deep navy stone. On the face, one large embossed LIGHT SHARD: a tall pointed gold crystal shard, like the crystal at the left end of the attached shard counter, glowing softly warm from inside, filling about half of the face.
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

Slide 1 *Lentera Menyala Kembali* memakai gambar slide 4 lama (Jaka di puncak bukit mengangkat lentera di atas Kedu yang bercahaya), karena itulah isi naskah slide 1. Gambar slide 1 sekarang (beranda di tepi laut) dibuang: Kedu tidak berpantai. Slide 2 sampai 5 digambar baru dengan aturan `R-27` sampai `R-30`: tanpa orang (tokoh tampil sebagai avatar bundar), tanpa berkas sinar, sepertiga bawah tenang. Keempatnya satu urutan: dari kabut yang tersibak ke pagi yang cerah, dan berakhir saat fajar.

### S-02 · Kabut Tersibak (slide 2 cerita penutup)

| | |
|---|---|
| Kode berkas | `bg.cerita-penutup-02` → simpan sebagai `bg.cerita-penutup-02.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-over-highland-village-04.jpg` (Temanggung), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (Magelang), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Telaga-Warna-Dieng-05.jpg` (Wonosobo), `ai-studio/game-images/assets/latar/bg.cerita-intro-07.webp` (tiga wilayah yang sama) |
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
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; `ai-studio/game-images/assets/latar/bg.intro.webp` (joglo rumah penjaga), `ai-studio/game-images/assets/ikon/ui.lantern.webp` (Lentera Kedu; setelah `U-14` dipasang, berkas barunya), foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` |
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

Tiga puluh dua gambar Pustaka yang masih bergaya lukisan (lembar telaah 14 dan 15) dibuat ulang dalam gaya 3D game, termasuk dua gambar bertanda `redo later` (284: Borobudur tergambar dua kali; 290: anak menyentuh stupa) dan tiga sampul video. Gambar yang sudah berupa foto asli dari peneliti, lambang kabupaten, Tips Jaka, dan gambar yang sudah dibetulkan dengan foto (`R-12` sampai `R-16`) tidak termasuk; 300 (Prasasti Canggal) sudah 3D.

- **Orang:** tidak ada anak berlurik yang muncul berulang; tempat atau bendanya yang menjadi pokok. Orang hanya ada bila topiknya membutuhkan (pemugaran, Waisak, Topeng Ireng, petani, festival), dilihat dari samping atau belakang.
- **Tembakau:** hanya daun hijau di ladang atau di nampan (309, 310); 311 diganti kebun kopi dengan keterangan baru. Tanpa rokok, asap, atau orang merokok.
- **Keterangan:** gambar mengikuti keterangan yang tampil di bawahnya. Lima gambar mendapat keterangan baru (`PK-01`, `PK-18`, `PK-20`, `PK-21`, `PK-29`); `PK-01` karena foto acuannya memperlihatkan Gunung Andong sendiri, bukan pemandangan dari puncaknya; tiga yang terakhir sebelumnya tidak punya keterangan.
- **Foto acuan:** foto Gunung Andong (`PK-01`), Gunung Tidar (`PK-09`), Umbul Jumprit (`PK-19`), dan Parakan (`PK-20`) dikirim pengembang 18 November 2026 dan ada di `ai-studio/chatgpt-images/inbox/` (sumber di `reference-sources-2026-11-18.json`). Foto Umbul Jumprit (`PK-19`) dan Parakan (`PK-20`) menyusul hari itu juga sebagai berkas; keempat baris kini bisa dikerjakan. Gambar lama dilampirkan hanya untuk isinya, bukan gayanya.
- **Kanvas:** 960 × 640 (3 : 2) seperti gambar Pustaka sekarang; sampul video 1280 × 720.

### PK-01 · Gunung Andong di atas lautan awan, Magelang (no. 282)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p1.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Andong-owner-sea-of-clouds-01.jpg` (the shape of Gunung Andong), `ai-studio/chatgpt-images/inbox/reference-Andong-owner-slopes-and-villages-02.webp` (its slopes and villages), `ai-studio/game-images/assets/pustaka/library.magelang.p1.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Gunung Andong di atas lautan awan, Magelang" (baru; EN "Mount Andong above a sea of clouds, Magelang") |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p1.1  (PK-01 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Gunung Andong di atas lautan awan, Magelang".

SUBJECT AND COMPOSITION
Mount Andong, a small steep pyramid-shaped mountain in Magelang, seen from a neighbouring ridge at sunrise: its sharp green summit ridge rises out of a sea of white morning cloud; terraced fields and small villages cover its lower slopes; far behind on the right, the broad shape of Mount Merbabu. A grassy ridge with low shrubs in the foreground.
People — No people.
Accuracy — Follow the attached photos for the shape of Andong (one sharp, slightly asymmetric pyramid peak). No temple, no hikers, no tents.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.magelang.p1.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-02 · Teras atas Candi Borobudur dengan stupa-stupanya (no. 284)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p2.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (Borobudur from the air: layout of the terraces), `ai-studio/chatgpt-images/inbox/reference-Borobudur-Kemenag-15.jpg` (gallery stupas and stone), `ai-studio/game-images/assets/pustaka/library.magelang.p2.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Teras atas Candi Borobudur dengan stupa-stupanya" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p2.1  (PK-02 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Teras atas Candi Borobudur dengan stupa-stupanya".

SUBJECT AND COMPOSITION
The upper part of Borobudur seen from the top of the square galleries: the three round terraces rise ahead in rings, each ring lined with bell-shaped perforated stupas, and the big closed main stupa crowns the top. Grey volcanic stone, early morning light, green hills and a far volcano behind.
People — No people.
Accuracy — Borobudur appears ONCE: only this upper part, never a second whole temple in the background. Lower two round terraces: stupas with diamond-shaped openings; top round terrace: stupas with square openings; the main stupa is closed with no openings.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.magelang.p2.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-03 · Stupa-stupa berlubang dan stupa induk di teras atas (no. 285)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p2.2.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (layout of the round terraces), `ai-studio/chatgpt-images/inbox/reference-Borobudur-Kemenag-15.jpg` (stone and carving), `ai-studio/game-images/assets/pustaka/library.magelang.p2.2.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Stupa-stupa berlubang dan stupa induk di teras atas" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p2.2  (PK-03 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Stupa-stupa berlubang dan stupa induk di teras atas".

SUBJECT AND COMPOSITION
A close, low view along one round terrace of Borobudur: a curving row of perforated bell-shaped stupas leads the eye up to the big closed main stupa at the summit. Through the diamond-shaped openings of the nearest stupa a seated stone Buddha statue can just be seen. Soft late-morning light, clear sky.
People — No people.
Accuracy — Openings: diamond-shaped on the lower round terraces, square on the top one; the main stupa is closed. One Borobudur only.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.magelang.p2.2.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-04 · Video UNESCO/NHK: Kompleks Candi Borobudur (no. 287)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p2.3.poster.webp` |
| Kanvas | 1280 × 720 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (Borobudur, the plain and a volcano), `ai-studio/chatgpt-images/inbox/reference-Borobudur-Kemenag-16.jpg` (relief stone), `ai-studio/game-images/assets/pustaka/library.magelang.p2.3.poster.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Video UNESCO/NHK: Kompleks Candi Borobudur" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p2.3.poster  (PK-04 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Video UNESCO/NHK: Kompleks Candi Borobudur".

SUBJECT AND COMPOSITION
Cover picture for a documentary video: the whole of Borobudur on its low hill above green trees at sunrise, seen from a little above, with the volcanoes Merapi and Merbabu soft in the mist behind. Wide, calm, majestic.
People — No people.
Accuracy — One Borobudur: square galleries below, three round terraces and the main stupa on top. No play button, no title.

DELIVERY
Target canvas 1280 × 720 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named library.magelang.p2.3.poster.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-05 · Stupa berlubang di teras bundar Borobudur (no. 290)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p3.3.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Borobudur-Kemenag-15.jpg` (stone and carving), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (the round terraces), `ai-studio/game-images/assets/pustaka/library.magelang.p3.3.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Stupa berlubang di teras bundar Borobudur" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p3.3  (PK-05 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Stupa berlubang di teras bundar Borobudur".

SUBJECT AND COMPOSITION
One perforated bell-shaped stupa on a round terrace of Borobudur, close and slightly from below: its diamond-shaped lattice openings, its square-based spire, and inside, through the openings, a seated stone Buddha statue in meditation. Behind it more stupas of the same ring and a pale morning sky.
People — No people.
Accuracy — Nobody touches the stupa. Lattice openings diamond-shaped (a lower round terrace).

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.magelang.p3.3.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-06 · Petugas membersihkan batu candi saat pemugaran Borobudur (no. 291)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p4.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Borobudur-Kemenag-05.jpg` (relief and stone), `ai-studio/chatgpt-images/inbox/reference-Borobudur-Kemenag-15.jpg` (gallery wall), `ai-studio/game-images/assets/pustaka/library.magelang.p4.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Petugas membersihkan batu candi saat pemugaran Borobudur" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p4.1  (PK-06 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Petugas membersihkan batu candi saat pemugaran Borobudur".

SUBJECT AND COMPOSITION
The great restoration of Borobudur in the 1970s: on the ground beside the temple wall, rows of numbered-looking but blank grey stone blocks are laid out on wooden pallets; two adult conservation workers in work clothes and helmets clean a carved relief stone with soft brushes and water; simple wooden scaffolding stands against the temple wall behind them.
People — Exactly two adult workers, seen from the side, looking at their work. Javanese Indonesians in modest local clothing, calm, never looking at the camera; no real or famous person; children only with adults.
Accuracy — No readable numbers or letters on the stones. Workers are adults, careful, with safe tools.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.magelang.p4.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-07 · Iring-iringan Waisak di dekat Candi Mendut (no. 292)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p5.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Mendut-front-02.png` (Candi Mendut), `ai-studio/chatgpt-images/inbox/reference-Mendut-corner-03.jpg` (Mendut stone), `ai-studio/game-images/assets/pustaka/library.magelang.p5.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Iring-iringan Waisak di dekat Candi Mendut" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p5.1  (PK-07 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Iring-iringan Waisak di dekat Candi Mendut".

SUBJECT AND COMPOSITION
The Waisak procession setting out from Candi Mendut: a line of Buddhist monks in saffron and maroon robes walks slowly along a path past the temple, followed by lay people in white carrying flowers and small offering trays; a few tall decorative paper umbrellas in yellow and white. Candi Mendut, with its tall stepped body and stupas on the roof, stands behind them. Late afternoon light.
People — About ten people, all adults, seen from the side or behind. Javanese Indonesians in modest local clothing, calm, never looking at the camera; no real or famous person; children only with adults.
Accuracy — Candi Mendut as in the attached photos (one temple, stairs on the front). Respectful, calm, no crowd pushing.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.magelang.p5.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-08 · Video: Kesenian khas Magelang, Tari Topeng Ireng (no. 298)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p6.3.poster.webp` |
| Kanvas | 1280 × 720 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Topeng-Ireng-researcher-dancers-row-04.png` (row of dancers), `ai-studio/chatgpt-images/inbox/reference-Topeng-Ireng-researcher-dancer-crown-portrait-01.jpg` (feather crown (kuluk)), `ai-studio/chatgpt-images/inbox/reference-Topeng-Ireng-researcher-low-angle-boots-07.jpg` (boots and bells), `ai-studio/game-images/assets/pustaka/library.magelang.p6.3.poster.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Video: Kesenian khas Magelang, Tari Topeng Ireng" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p6.3.poster  (PK-08 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Video: Kesenian khas Magelang, Tari Topeng Ireng".

SUBJECT AND COMPOSITION
Cover picture for a dance video: a row of Topeng Ireng dancers in daylight on a village field, mid-step, with tall colourful feather crowns (kuluk), fringed costumes, ankle bells and boots, as in the attached photos; a village of red-tile roofs and green hills behind.
People — Five adult dancers, seen from the side, faces turned away from the viewer. Javanese Indonesians in modest local clothing, calm, never looking at the camera; no real or famous person; children only with adults.
Accuracy — Costume exactly as the photos (feather crown, fringes, boots with bells); face paint only as in the photos. No play button, no title.

DELIVERY
Target canvas 1280 × 720 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named library.magelang.p6.3.poster.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-09 · Gunung Tidar, bukit hijau di tengah Kota Magelang (no. 299)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p7.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Tidar-owner-aerial-forest-town-02.jpg` (Gunung Tidar above the town), `ai-studio/chatgpt-images/inbox/reference-Tidar-owner-aerial-golf-course-01.jpg` (the hill from another side), `ai-studio/game-images/assets/pustaka/library.magelang.p7.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Gunung Tidar, bukit hijau di tengah Kota Magelang" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p7.1  (PK-09 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Gunung Tidar, bukit hijau di tengah Kota Magelang".

SUBJECT AND COMPOSITION
Mount Tidar, a rounded hill about 500 metres high covered in dense green forest, rising directly behind the red-tile roofs and tree-lined streets of Magelang city on a clear morning, seen from a little above.
People — No people.
Accuracy — Follow the attached photos for the hill's rounded shape and dense forest. Do not draw the golf course, military buildings or a grave.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.magelang.p7.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-10 · Getuk Magelang yang berwarna-warni (no. 301)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.magelang.p9.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/game-images/assets/pustaka/library.magelang.p9.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Getuk Magelang yang berwarna-warni" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p9.1  (PK-10 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Getuk Magelang yang berwarna-warni".

SUBJECT AND COMPOSITION
A still life on a wooden table: pieces of getuk, the cassava sweet of Magelang, in three colours (brown palm sugar, white and pale pandan green), cut into soft blocks and topped with grated coconut, on a banana leaf on a woven bamboo tray; beside it two round unglazed reddish-brown earthenware pots from the Klipoh potters near Borobudur. Warm daylight.
People — No people.
Accuracy — Getuk is soft, matte and dense, not shiny jelly. Pottery is plain, unglazed.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.magelang.p9.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-11 · Gunung Sindoro dan Gunung Sumbing dilihat dari sebuah desa di Temanggung (no. 303)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p1.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-over-highland-village-04.jpg` (a highland village under Sindoro), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` (the twin volcanoes), `ai-studio/game-images/assets/pustaka/library.temanggung.p1.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Gunung Sindoro dan Gunung Sumbing dilihat dari sebuah desa di Temanggung" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p1.1  (PK-11 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Gunung Sindoro dan Gunung Sumbing dilihat dari sebuah desa di Temanggung".

SUBJECT AND COMPOSITION
A Temanggung village of red clay-tile roofs among vegetable terraces and bamboo, seen from its edge; behind it the twin volcanoes Sindoro (a clean cone) and Sumbing (broader) stand side by side under a clear morning sky.
People — No people.
Accuracy — Two volcanoes, smooth slopes, no smoke. No tobacco plants.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p1.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-12 · Kabut tipis pagi hari di Temanggung (no. 304)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p1.2.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-over-highland-village-04.jpg` (highland village and slopes), `ai-studio/game-images/assets/pustaka/library.temanggung.p1.2.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Kabut tipis pagi hari di Temanggung" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p1.2  (PK-12 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Kabut tipis pagi hari di Temanggung".

SUBJECT AND COMPOSITION
Early morning in the Temanggung highlands: thin white natural morning mist lies in the valleys between rounded green hills and terraced fields; tree tops and a few red roofs rise out of it; soft pale-gold first light.
People — No people.
Accuracy — This is ordinary white morning mist, not the blue-grey Mist of Forgetting. No tobacco plants.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p1.2.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-13 · Gunung Sindoro dan Gunung Sumbing, si gunung kembar (no. 305)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p2.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` (the twin volcanoes), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-twin-volcanoes-from-ridge-03.jpg` (their shapes from a ridge), `ai-studio/game-images/assets/pustaka/library.temanggung.p2.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Gunung Sindoro dan Gunung Sumbing, si gunung kembar" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p2.1  (PK-13 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Gunung Sindoro dan Gunung Sumbing, si gunung kembar".

SUBJECT AND COMPOSITION
The twin volcanoes Sindoro and Sumbing seen together from far across green lowland at dawn: two big cones side by side with a saddle between them, pink-gold sky, a band of cloud at their feet.
People — No people.
Accuracy — Sindoro is the cleaner cone, Sumbing broader with a rougher top. No smoke.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p2.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-14 · Gunung Sindoro dilihat dari puncak Gunung Sumbing (no. 306)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p2.2.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-twin-volcanoes-from-ridge-03.jpg` (view from a high ridge), `ai-studio/game-images/assets/pustaka/library.temanggung.p2.2.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Gunung Sindoro dilihat dari puncak Gunung Sumbing" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p2.2  (PK-14 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Gunung Sindoro dilihat dari puncak Gunung Sumbing".

SUBJECT AND COMPOSITION
The view from the high grassy slopes near the summit of Mount Sumbing: in the foreground golden-brown alpine grass and rocks; across a sea of clouds, the clean cone of Mount Sindoro stands close and clear in the blue morning.
People — No people.
Accuracy — One cone (Sindoro) across the clouds; the viewer stands on Sumbing. No smoke.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p2.2.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-15 · Gunung Sumbing dari arah Temanggung (no. 307)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p2.3.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` (Sumbing's shape), `ai-studio/game-images/assets/pustaka/library.temanggung.p2.3.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Gunung Sumbing dari arah Temanggung" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p2.3  (PK-15 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Gunung Sumbing dari arah Temanggung".

SUBJECT AND COMPOSITION
Mount Sumbing alone, broad and massive, seen from the Temanggung side: its long slopes covered from the bottom with terraced vegetable and coffee fields, scattered villages, and forest higher up; a clear morning sky.
People — No people.
Accuracy — Only Sumbing (broad, rough top) fills the view. No tobacco plants, no smoke.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p2.3.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-16 · Daun tembakau dan buah kopi dari lereng gunung Temanggung (no. 309)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p4.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/game-images/assets/pustaka/library.temanggung.p4.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Daun tembakau dan buah kopi dari lereng gunung Temanggung" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p4.1  (PK-16 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Daun tembakau dan buah kopi dari lereng gunung Temanggung".

SUBJECT AND COMPOSITION
A still life on a wooden table by a field: a few large fresh green tobacco leaves laid flat on a bamboo tray, and beside them a woven basket of ripe red coffee cherries and a small heap of brown roasted coffee beans; the green slope of a mountain behind, soft daylight.
People — No people.
Accuracy — Tobacco only as fresh green leaves; no cigarettes, no dried rolled tobacco, no smoke, no pipe.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p4.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-17 · Petani memeriksa daun tembakau sebelum panen (no. 310)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p4.2.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/game-images/assets/pustaka/library.temanggung.p4.2.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Petani memeriksa daun tembakau sebelum panen" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p4.2  (PK-17 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Petani memeriksa daun tembakau sebelum panen".

SUBJECT AND COMPOSITION
An adult farmer in a wide straw hat and work clothes stands among tall tobacco plants on a terraced slope and gently checks one large green leaf with his hand; the mountain slope and terraces behind; morning light.
People — Exactly one adult farmer, seen from the side, looking at the leaf. Javanese Indonesians in modest local clothing, calm, never looking at the camera; no real or famous person; children only with adults.
Accuracy — Tobacco only as growing green plants; no cigarettes, no smoke, nobody smoking.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p4.2.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-18 · Kebun kopi di lereng Gunung Sumbing (no. 311)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p4.3.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` (Sumbing's shape), `ai-studio/game-images/assets/pustaka/library.temanggung.p4.3.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Kebun kopi di lereng Gunung Sumbing" (baru; EN "A coffee garden on the slopes of Mount Sumbing") |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p4.3  (PK-18 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Kebun kopi di lereng Gunung Sumbing".

SUBJECT AND COMPOSITION
A coffee garden on the lower slopes of Mount Sumbing: rows of coffee shrubs with glossy dark-green leaves and clusters of red and green cherries, shade trees between them, a narrow earth path; the broad mountain behind under a clear sky.
People — No people.
Accuracy — Coffee only: no tobacco plants in this picture.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p4.3.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-19 · Mata air Umbul Jumprit di lereng Gunung Sindoro (no. 315)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p6.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Jumprit-owner-spring-pool-day-03.webp` (the pool by day), `ai-studio/chatgpt-images/inbox/reference-Jumprit-owner-spring-pool-night-02.jpg` (pool, footbridge and split gate), `ai-studio/chatgpt-images/inbox/reference-Jumprit-owner-split-gate-01.webp` (the stone gate under big trees), `ai-studio/game-images/assets/pustaka/library.temanggung.p6.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Mata air Umbul Jumprit di lereng Gunung Sindoro" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p6.1  (PK-19 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Mata air Umbul Jumprit di lereng Gunung Sindoro".

SUBJECT AND COMPOSITION
The Umbul Jumprit spring on the slopes of Mount Sindoro: a clear, still pool of spring water in a shaded hollow under a mossy rock wall hung with ferns; a narrow flat stone footbridge runs along one side of the pool to a small terrace at the back, where a dark grey stone split gateway (candi bentar) with stepped sides stands; huge old trees above. Soft daylight through the leaves.
People — No people.
Accuracy — Follow the attached photos for the pool, the footbridge and the stone gateway. No offerings, flowers, incense, candles, cloth, statues, signs or people.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p6.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-20 · Parakan di kaki Gunung Sindoro (no. 316)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p7.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Parakan-owner-town-under-Sindoro-02.webp` (the town under Sindoro), `ai-studio/chatgpt-images/inbox/reference-Parakan-owner-Sindoro-above-fields-01.jpg` (the shape of Mount Sindoro only; its tobacco is not drawn), `ai-studio/game-images/assets/pustaka/library.temanggung.p7.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Parakan di kaki Gunung Sindoro" (baru; EN "Parakan at the foot of Mount Sindoro") |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p7.1  (PK-20 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Parakan di kaki Gunung Sindoro".

SUBJECT AND COMPOSITION
The small town of Parakan in Temanggung seen from a little above in the morning: a dense spread of red and grey tile roofs and low houses on a gently rising slope, a few trees between them, terraced fields at the edge of the town; the big cone of Mount Sindoro rises directly behind the town under a clear blue sky.
People — No people.
Accuracy — Follow the attached photos for the town and the mountain. No tobacco plants (although they grow near Parakan), no weapons, no signs with letters, no landmark buildings.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p7.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-21 · Kupat tahu dan uceng goreng khas Temanggung (no. 320)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.temanggung.p9.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/game-images/assets/tantangan/challenge.post.tmg-4-b.webp` (fried uceng fish), `ai-studio/game-images/assets/pustaka/library.temanggung.p9.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Kupat tahu dan uceng goreng khas Temanggung" (baru; EN "Kupat tahu and fried uceng fish from Temanggung") |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p9.1  (PK-21 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Kupat tahu dan uceng goreng khas Temanggung".

SUBJECT AND COMPOSITION
A still life on a wooden table: a plate of kupat tahu (cut rice cake, fried tofu and vegetables under a sweet brown peanut sauce) and a small plate of crisp fried uceng, slim finger-sized river fish, as in the attached picture; a small bowl of sambal; daylight, a green garden behind.
People — No people.
Accuracy — Uceng are small slim river fish, not sea fish. No steam.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.temanggung.p9.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-22 · Kota Wonosobo di antara bukit-bukit berkabut (no. 322)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p1.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-over-highland-village-04.jpg` (highland houses and slopes), `ai-studio/game-images/assets/pustaka/library.wonosobo.p1.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Kota Wonosobo di antara bukit-bukit berkabut" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p1.1  (PK-22 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Kota Wonosobo di antara bukit-bukit berkabut".

SUBJECT AND COMPOSITION
Wonosobo town in a green highland valley in the morning: many red and grey roofs and a few low buildings among trees, ringed by rounded hills with thin white morning mist in their folds.
People — No people.
Accuracy — No landmark buildings, no towers, no text. No crater, no steam.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p1.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-23 · Pemandangan Dataran Tinggi Dieng (no. 324)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p2.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Arjuna-researcher-complex-row-03.jpg` (the Arjuna temples on the plateau), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Telaga-Warna-Dieng-05.jpg` (Telaga Warna), `ai-studio/game-images/assets/pustaka/library.wonosobo.p2.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Pemandangan Dataran Tinggi Dieng" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p2.1  (PK-23 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Pemandangan Dataran Tinggi Dieng".

SUBJECT AND COMPOSITION
The Dieng plateau seen from a hillside: a wide high green basin with neat terraces of potato fields, the small dark-stone temples of the Arjuna group in a row on the flat grass, a village of tin and tile roofs, and green hills all round under a clear cool sky.
People — No people.
Accuracy — Arjuna temples small, as in the photo. No crater, no steam or smoke.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p2.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-24 · Telaga Warna di Dataran Tinggi Dieng (no. 326)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p3.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Telaga-Warna-Dieng-05.jpg` (Telaga Warna and Telaga Pengilon), `ai-studio/game-images/assets/pustaka/library.wonosobo.p3.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Telaga Warna di Dataran Tinggi Dieng" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p3.1  (PK-24 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Telaga Warna di Dataran Tinggi Dieng".

SUBJECT AND COMPOSITION
Telaga Warna and Telaga Pengilon seen from a wooded viewpoint above: the two lakes side by side, Telaga Warna turquoise-green and Telaga Pengilon a darker clear green, separated by a narrow wooded strip, forest all round, hills behind.
People — No people.
Accuracy — As the attached photo. No steam, no crater.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p3.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-25 · Warna air Telaga Warna yang tampak berubah-ubah (no. 327)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p3.2.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Telaga-Warna-Dieng-05.jpg` (the lake), `ai-studio/game-images/assets/pustaka/library.wonosobo.p3.2.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Warna air Telaga Warna yang tampak berubah-ubah" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p3.2  (PK-25 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Warna air Telaga Warna yang tampak berubah-ubah".

SUBJECT AND COMPOSITION
A closer view across the surface of Telaga Warna: the water shows bands of turquoise, green and pale blue that shift as a light breeze and cloud shadows move over it; reeds and trees at the near shore.
People — No people.
Accuracy — No steam, no smoke, no crater.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p3.2.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-26 · Telaga Warna dan pepohonan di sekelilingnya (no. 328)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p3.3.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Telaga-Warna-Dieng-05.jpg` (the lake and its forest), `ai-studio/game-images/assets/pustaka/library.wonosobo.p3.3.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Telaga Warna dan pepohonan di sekelilingnya" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p3.3  (PK-26 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Telaga Warna dan pepohonan di sekelilingnya".

SUBJECT AND COMPOSITION
The shore of Telaga Warna framed by tall trees and ferns: through the trunks, the turquoise lake glints, with the forested slopes reflected in it; soft morning light through the leaves.
People — No people.
Accuracy — No steam, no crater.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p3.3.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-27 · Matahari terbit dari Bukit Sikunir (no. 329)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p4.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` (Sindoro at sunrise), `ai-studio/game-images/assets/pustaka/library.wonosobo.p4.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Matahari terbit dari Bukit Sikunir" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p4.1  (PK-27 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Matahari terbit dari Bukit Sikunir".

SUBJECT AND COMPOSITION
Sunrise seen from the top of Sikunir hill above the village of Sembungan: the sun rises over a wide sea of clouds; across it the cone of Mount Sindoro stands dark against the pink-gold sky; dark silhouettes of hilltop trees and grass in the foreground.
People — No people.
Accuracy — No crater, no steam.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p4.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-28 · Golden sunrise di Sikunir (no. 330)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p4.2.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` (Sindoro and Sumbing), `ai-studio/game-images/assets/pustaka/library.wonosobo.p4.2.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Golden sunrise di Sikunir" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p4.2  (PK-28 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Golden sunrise di Sikunir".

SUBJECT AND COMPOSITION
A few minutes later than sunrise at Sikunir: golden light floods a sea of clouds; Mount Sindoro close on the left, the tops of Sumbing, Merbabu and Merapi far away rise above the clouds; warm gold everywhere.
People — No people.
Accuracy — Different from the plain sunrise picture: brighter, gold, several far peaks. No crater, no steam.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p4.2.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-29 · Embun upas di daun kentang Dieng (no. 331)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p5.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Arjuna-researcher-complex-row-03.jpg` (the plateau setting), `ai-studio/game-images/assets/pustaka/library.wonosobo.p5.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Embun upas di daun kentang Dieng" (baru; EN "Frost (embun upas) on potato leaves in Dieng") |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p5.1  (PK-29 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Embun upas di daun kentang Dieng".

SUBJECT AND COMPOSITION
Dawn in the dry season on the Dieng plateau: low potato plants and grass covered in thin white frost crystals (embun upas), sparkling in the first pale sun; the flat plateau and hills behind in cold blue morning air.
People — No people.
Accuracy — Thin frost, not snow. No crater, no steam, no smoke.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p5.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-30 · Video: Dieng Culture Festival 2025 "Back to the Culture" (no. 337)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p8.1.poster.webp` |
| Kanvas | 1280 × 720 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-Arjuna-researcher-complex-row-03.jpg` (the Arjuna temples), `ai-studio/chatgpt-images/inbox/reference-Arjuna-researcher-row-sunrise-retouched-01.jpg` (the row at dusk), `ai-studio/game-images/assets/pustaka/library.wonosobo.p8.1.poster.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Video: Dieng Culture Festival 2025 "Back to the Culture"" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p8.1.poster  (PK-30 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Video: Dieng Culture Festival 2025 "Back to the Culture"".

SUBJECT AND COMPOSITION
Cover picture for a culture-festival video: the Arjuna temple group of Dieng at dusk during the festival, softly lit, with many small paper lanterns hung on lines and held in hands by visitors in warm jackets; the temples stand calm in the middle.
People — About twelve people, adults and children together, all seen from behind. Javanese Indonesians in modest local clothing, calm, never looking at the camera; no real or famous person; children only with adults.
Accuracy — Temples as in the photos. No lanterns flying into the sky, no fire, no smoke. No play button, no title.

DELIVERY
Target canvas 1280 × 720 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p8.1.poster.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-31 · Mi ongklok dengan sate sapi (no. 338)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p9.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/game-images/assets/tantangan/challenge.item.wnb-4-a07.webp` (mi ongklok in its bowl), `ai-studio/game-images/assets/pustaka/library.wonosobo.p9.1.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Mi ongklok dengan sate sapi" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p9.1  (PK-31 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Mi ongklok dengan sate sapi".

SUBJECT AND COMPOSITION
A still life on a wooden table: a bowl of mi ongklok (yellow noodles, cabbage and chives under a thick glossy brown sauce), as in the attached picture, with a plate of beef satay under peanut sauce and a few pieces of tempe kemul (crisp battered tempeh); a small ongklok, the woven bamboo dipping basket, lies beside them.
People — No people.
Accuracy — No steam. Same bowl and food as the attached picture.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p9.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

### PK-32 · Es carica khas Dieng (no. 340)

| | |
|---|---|
| Berkas | pengganti `ai-studio/game-images/assets/pustaka/library.wonosobo.p9.3.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `ai-studio/chatgpt-images/inbox/reference-carica-peeled-for-sweets-14.jpg` (carica flesh for sweets), `ai-studio/chatgpt-images/inbox/reference-carica-whole-and-cut-12.jpg` (the fruit), `ai-studio/game-images/assets/pustaka/library.wonosobo.p9.3.webp` (gambar lama: hanya isi, bukan gayanya) |
| Keterangan | "Es carica khas Dieng" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p9.3  (PK-32 · Pustaka)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Library adaptation: an informative picture for a children's reading page. Daylight or the time named below; clear and calm, one main subject, real places, buildings, costumes and food keep their true shapes; only the rendering is stylized 3D (never an oil painting). Do NOT include Jaka, Mbah Kedu, or any recurring boy character. Use attached photos only as factual references; compose a new picture, do not copy a photo.
The picture sits above this caption: "Es carica khas Dieng".

SUBJECT AND COMPOSITION
A clear glass of es carica: pale yellow-orange pieces of carica flesh in light syrup with ice cubes, on a wooden table; beside it two small whole carica fruits and one cut open, as in the attached photos; a highland lake far behind.
People — No people.
Accuracy — Carica: small ridged fruit with thin pale flesh and many small seeds, not a big papaya. No steam.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Opaque, full-bleed. Deliver WebP, named library.wonosobo.p9.3.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache, oil-painting look or paper grain.
```

## Polesan halaman awal dan peta (pengembang, 19 November 2026)

Wawancara pengembang 19 November 2026 ([04](04_CATATAN_KEPUTUSAN.md)): halaman awal dibagi dua (logo, judul, ajakan, tiga wilayah, dan tombol MULAI di kiri; Jaka memegang lentera di kanan), dengan latar baru bernuansa *akan memulai perjalanan ke Kedu*, dan kristal Serpihan Cahaya yang sinarnya bertambah mengikuti kemajuan siswa. Tata letaknya sudah dibangun dengan gambar sementara (latar `S-W` dan Jaka melambai `jaka-gate.webp`); gambar di bawah ini menggantikannya setelah diterima pengembang. Tombol MULAI tidak dipesan: tulisannya dibuat halaman di atas pelat emas `U-01`.

| Kode | Gambar | Berkas | Kanvas |
|---|---|---|---|
| `PL-01` | Latar halaman awal: awal perjalanan ke Kedu | pengganti `ai-studio/game-images/assets/latar/bg.welcome.webp` (slot `bg.welcome`) | 1920 × 1080 |
| `PL-02` | Jaka memegang Lentera Kedu | baru: `public/assets/ui/jaka-home.webp` | 900 × 1400, latar transparan |
| `PL-03` | Kristal Serpihan Cahaya tahap 1 (Temanggung) | baru: `public/assets/ui/hud-shards-crystal-1.webp` | 444 × 564, latar transparan |
| `PL-04` | Kristal Serpihan Cahaya tahap 2 (Magelang) | baru: `public/assets/ui/hud-shards-crystal-2.webp` | 444 × 564, latar transparan |
| `PL-05` | Kristal Serpihan Cahaya tahap 3 (Wonosobo) | baru: `public/assets/ui/hud-shards-crystal-3.webp` | 444 × 564, latar transparan |
| `PL-06` | Kristal Serpihan Cahaya tahap 4 (semua tuntas) | baru: `public/assets/ui/hud-shards-crystal-4.webp` | 444 × 564, latar transparan |
| `PL-07` sampai `PL-09` | Tombol tenang emas, biru tua, hijau (putaran kedua, lihat di bawah) | baru: `public/assets/ui/ui-button-{gold,navy,green}.webp` | 960 × 160, latar transparan |
| `PL-10` sampai `PL-12` | Gambar halaman galat 404, 500, dan lainnya (putaran ketiga, lihat di bawah) | baru: `public/assets/ui/error-{404,500,400}.webp` | 960 × 720 |

**Tahap kristal.** Tahap 1 tampil selama belum ada wilayah yang tuntas (hanya Temanggung yang terbuka), tahap 2 setelah Temanggung tuntas (Magelang terbuka), tahap 3 setelah Magelang tuntas (Wonosobo terbuka), tahap 4 setelah ketiga wilayah tuntas. Keempatnya bentuk dan ukuran yang sama dengan `U-36`; yang berubah hanya cahaya di dalamnya, makin lama makin terang. Game memilih gambar menurut tahap; selama gambar suatu tahap belum terpasang, `U-36` yang tampil.

### PL-01 · Latar halaman awal: awal perjalanan ke Kedu

| | |
|---|---|
| Kode berkas | `bg.welcome` → simpan sebagai `bg.welcome.webp` (pengganti `ai-studio/game-images/assets/latar/bg.welcome.webp`, `S-W`) |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; latar sekarang `ai-studio/game-images/assets/latar/bg.welcome.webp` (`S-W`: suasana dan garis gunung); foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-twin-volcanoes-from-ridge-03.jpg`, `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg`; tata letak halaman `docs/revisi-10-analitik/gambar/40/halaman-awal-tata-letak.webp` |
| Dipakai | Halaman awal (logo, ajakan, tiga wilayah, dan MULAI di sepertiga kiri; Jaka berdiri di kanan, kakinya di tepi bawah), juga cadangan latar layar siapkan dan cerita pembuka |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.welcome  (PL-01 · start page: the start of a journey to Kedu)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: no people at all (the game places Jaka on top). The attached photos are factual references for the mountains only; do not copy a photo.

SUBJECT AND COMPOSITION
Wide 16:9 background for a start screen that says "your journey to Kedu begins here". The feeling: standing at the start of a path at first light, about to set out.
RIGHT HALF: a winding earth footpath begins in the lower right foreground (its near end wide, at the bottom edge between 60% and 90% of the width) and winds away into the distance toward the Kedu plain. A boy will stand on this near end of the path, so keep the area from 58% to 92% of the width and from 15% to 100% of the height free of tall objects (no tree trunk, post or rock in front of it); low grass at the path's edges is fine. Far along the path, small and distant, a carved wooden gate post or a tiny stone milestone marks where the journey starts.
BACKGROUND: the real skyline of the Kedu plain at dawn, as in the attached current background and photos: the twin volcanoes Mount Sumbing (broader) and Mount Sindoro (a clean cone) in the centre-right, a warm gold sunrise glow low behind them, mist lying in the valleys, terraced hills and a few small warm village lights far below. The mountains sit low, their peaks below 45% of the height from the top.
LEFT THIRD (0% to 40% of the width): calm and dark for the logo, text and START button: deep navy sky above, soft dark hillside shade and rounded tree silhouettes below, very low detail, no bright spots, no lights. A leafy branch may frame the top-left corner.
Mood: navy and gold, hopeful, the first warm light of the day; not a green daylight scene and not an orange sunset blaze.
Accuracy — No sea, coast, islands, cliffs, waterfalls, temples, stupas or castles. No light rays. No signs with writing.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named bg.welcome.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### PL-02 · Jaka memegang Lentera Kedu

| | |
|---|---|
| Berkas | baru: `public/assets/ui/jaka-home.webp` (halaman awal memakainya begitu berkasnya ada; sebelum itu `jaka-gate.webp`) |
| Kanvas | 900 × 1400, latar transparan |
| Lampiran | `public/assets/ui/jaka-gate.webp` (Jaka: wajah, rambut, pakaian, syal merah, ransel), `ai-studio/game-images/assets/ikon/ui.lantern.webp` (`U-14`: Lentera Kedu), acuan gaya `ai-studio/game-images/assets/referensi/reference.01.webp` |
| Dipakai | Halaman awal, kanan, tinggi tampil sekitar 640 dp dengan kaki di tepi bawah layar |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: jaka-home  (PL-02 · Jaka holding the Lantern of Kedu, start page)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 in finish; keep the exact subject specified. No painted brush or paper texture.

SUBJECT
Jaka, the same boy as in the attached picture (same face, hair, age about 11, white T-shirt, red scarf round his neck, batik sash at the waist, dark shorts, sandals, brown leather backpack): keep him exactly recognisable. Full body, standing, three-quarter view turned slightly toward the LEFT of the picture (where the game's title and START button are), ready to set out on a journey.
He holds the attached Lantern of Kedu (the carved gold lantern with glass panels, same design) raised in his right hand at about shoulder height, a little in front of him, its small flame glowing warm gold; his left hand holds the strap of his backpack. Confident, friendly, eager smile, looking toward the left and slightly up, as if saying "let's go".
The lantern's light falls warmly on his face, scarf and shirt from the front; his back stays in soft navy shade. A soft warm glow round the lantern only (no rays, no sparkles across the picture).

DELIVERY
Canvas exactly 900 × 1400 pixels, genuine transparent background (no fake checkerboard, no ground, no shadow plate). He fills the height: head about 40 pixels from the top, soles of his sandals on the bottom edge. The raised lantern stays inside the canvas with at least 30 pixels to spare. WebP with alpha, named jaka-home.webp.

CONSTRAINTS
No text, letters, numbers, logos, watermarks or frames. No extra characters, no background scenery. One still picture (no animation frames).
```

### PL-03 sampai PL-06 · Kristal Serpihan Cahaya, empat tahap

| | |
|---|---|
| Berkas | baru: `public/assets/ui/hud-shards-crystal-1.webp` sampai `-4.webp` (satu per tahap; `U-36` tetap sebagai cadangan) |
| Kanvas | 444 × 564, latar transparan, sama persis dengan `U-36` |
| Lampiran | `public/assets/ui/hud-shards-crystal.webp` (`U-36`: bentuk kristal), `public/assets/ui/hud-shards-frame.webp` (`U-07`: bingkai dengan lubang), `ai-studio/game-images/assets/referensi/reference.27.webp`; untuk `PL-04` sampai `PL-06` juga hasil tahap sebelumnya (`out/hud-shards-crystal-1.webp`, …) |
| Urutan | `PL-03` dulu; `PL-04`, `PL-05`, `PL-06` masing-masing melampirkan hasil tahap sebelumnya supaya bentuk dan ukurannya sama persis |

Blok bersama untuk keempat prompt (tempel di awal setiap prompt, lalu satu baris *Stage* dari tabel di bawahnya):

```text
GELITA CARVED GOLD UI — 2026-11-18
Interface part for GELITA, an educational game for Indonesian children aged 10–11, shown on a school Chromebook. One family with the attached GELITA style board: thick antique-gold frames carved in deep relief with Javanese ukel scroll ornaments, faces of dark navy stone (#0B1320 to #1B2740) with a very faint cloud pattern, symbols embossed in the same gold, one light from the upper left, soft contact shadow, rendered as a solid 3D object. Gold #DFC087 with highlights #F0DFB8 and warm brown shadows; no chrome, no neon, no glow line, no rainbow sheen. Ornaments are the same carving family on every part: the same scroll, the same small diamond studs, the same bead edge.
It is a user-interface element, not a scene: no background scenery, no text, letters, numbers or logos, no people. Genuine transparent background outside the object unless the brief says otherwise; no fake checkerboard.
Asset: hud-shards-crystal-N (light shard crystal, one of four stages of the same crystal)
EXACTLY the same crystal as the attached hud-shards-crystal.webp (U-36): same outline, same facets, same size and position on the 444 × 564 canvas, tip straight up. Only the light inside it changes from stage to stage, so the four pictures can replace each other in the same socket without the crystal jumping. Draw ONLY the crystal: no socket, no ring, no frame, no background. A soft glow may hug the crystal's outline (at most 24 pixels beyond it) but no rays, sparkles or halo across the canvas. It must still read at 32 × 56 pixels. No text. WebP with alpha.
```

| Kode | Baris *Stage* yang ditambahkan | Simpan sebagai |
|---|---|---|
| `PL-03` | `Stage 1 of 4 (the journey has just begun): the crystal is mostly asleep: deep amber-brown glass with only a small warm ember of gold light low in its core; facets dim; barely any glow at the outline.` | `hud-shards-crystal-1.webp` |
| `PL-04` | `Stage 2 of 4 (one region restored): warm amber-gold light fills the lower half of the crystal and rises toward the middle; the lower facets glow; a faint warm glow hugs the lower outline. Attach and match out/hud-shards-crystal-1.webp.` | `hud-shards-crystal-2.webp` |
| `PL-05` | `Stage 3 of 4 (two regions restored): bright gold light fills almost the whole crystal, a hint of soft turquoise in the inner facets; a clear warm glow hugs the whole outline. Attach and match out/hud-shards-crystal-2.webp.` | `hud-shards-crystal-3.webp` |
| `PL-06` | `Stage 4 of 4 (all of Kedu restored): the whole crystal shines radiant pale gold to white-gold from within, every facet lit, a delicate turquoise sparkle in the core, the strongest soft glow hugging the outline (still within 24 pixels). Attach and match out/hud-shards-crystal-3.webp.` | `hud-shards-crystal-4.webp` |

### PL-07 sampai PL-09 · Tombol tenang (emas, biru tua, hijau)

Wawancara pengembang 19 November 2026, putaran kedua ([04](04_CATATAN_KEPUTUSAN.md)): tombol yang berisi tulisan dan panah tidak lagi memakai pelat dengan ujung ukel yang ramai (`U-01` sampai `U-03`). Semua tombol bertulisan di game memakai satu keluarga *tombol tenang*: kapsul bersudut bulat yang halus dengan tepi manik tipis, tulisan di tengah, dan panah di kotak kecil di kanan (tulisan dan panah dibuat halaman). Gaya ini sudah dibangun dengan CSS; gambar di bawah ini memberi permukaan 3D yang segaya dengan keluarga emas berukir, tanpa hiasan di ujung, sehingga bisa diregang ke lebar berapa pun.

| Kode | Gambar | Berkas | Kanvas |
|---|---|---|---|
| `PL-07` | Tombol tenang emas (aksi utama: Mulai, Lanjut, Cek jawabanku) | baru: `public/assets/ui/ui-button-gold.webp` | 960 × 160, latar transparan |
| `PL-08` | Tombol tenang biru tua (Kembali, Batal, Kenali) | baru: `public/assets/ui/ui-button-navy.webp` | 960 × 160, latar transparan |
| `PL-09` | Tombol tenang hijau (Lihat hasil) | baru: `public/assets/ui/ui-button-green.webp` | 960 × 160, latar transparan |

| | |
|---|---|
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp` (acuan gaya), `public/assets/ui/ui-plate-gold.webp` (`U-01`: warna emas dan cahaya, tetapi TANPA ujung ukelnya), `docs/revisi-10-analitik/gambar/40/polesan/10-tombol-tenang.webp` (tombol CSS di game: bentuk dan proporsi); untuk `PL-08` dan `PL-09` juga `out/ui-button-gold.webp` |
| Urutan | `PL-07` dulu; `PL-08` dan `PL-09` melampirkan hasilnya supaya bentuk, tepi, dan sudutnya sama persis |

```text
GELITA CARVED GOLD UI — 2026-11-18
Interface part for GELITA, an educational game for Indonesian children aged 10–11, shown on a school Chromebook. One family with the attached GELITA style board: thick antique-gold frames carved in deep relief with Javanese ukel scroll ornaments, faces of dark navy stone (#0B1320 to #1B2740) with a very faint cloud pattern, symbols embossed in the same gold, one light from the upper left, soft contact shadow, rendered as a solid 3D object. Gold #DFC087 with highlights #F0DFB8 and warm brown shadows; no chrome, no neon, no glow line, no rainbow sheen. Ornaments are the same carving family on every part: the same scroll, the same small diamond studs, the same bead edge.
It is a user-interface element, not a scene: no background scenery, no text, letters, numbers or logos, no people. Genuine transparent background outside the object unless the brief says otherwise; no fake checkerboard.
Asset: ui-button-gold (PL-07 · quiet button face, gold)
EXCEPTION to the family's carving: this button has NO scroll ornaments, NO carved ends, NO studs and NO medallion. It is the calm member of the family, for buttons that carry their own label and arrow. A smooth, slightly domed rounded rectangle (corner radius about 28 pixels on a 160 pixel tall canvas), its face polished warm gold that runs from pale cream gold (#FFF4D2) at the top through #F2D58F to deeper gold (#CB9C48) at the bottom, lit from the upper left. A thin bright bead edge (2 to 3 pixels) runs all round just inside the outline, and a fine dark-brown outer line (#7E5A1E, 2 pixels) separates it from any background. A soft inner highlight along the top edge. The face is completely EVEN from end to end: no texture, pattern, mark or change of light along its length, so the game can stretch the middle to any width and nothing looks stretched.
Canvas exactly 960 × 160 pixels, transparent around the button; the button fills the canvas with about 10 pixels of soft contact shadow below. No text, no arrow, no icon. WebP with alpha, named ui-button-gold.webp.
```

Untuk `PL-08` dan `PL-09`, pakai prompt yang sama dengan baris *Asset* dan warna permukaan berikut:

| Kode | Baris yang diganti | Simpan sebagai |
|---|---|---|
| `PL-08` | `Asset: ui-button-navy (PL-08 · quiet button face, navy)` · permukaan biru tua halus dari #253A60 di atas ke #0F1B33 di bawah; tepi manik emas tipis (#DFC087, 2 piksel), garis luar gelap; *Attach and match out/ui-button-gold.webp: same shape, corners, bead edge and shadow.* | `ui-button-navy.webp` |
| `PL-09` | `Asset: ui-button-green (PL-09 · quiet button face, green)` · permukaan hijau daun tua halus dari #3F7D50 di atas ke #1D462C di bawah; tepi manik emas tipis (#DFC087, 2 piksel), garis luar gelap; *Attach and match out/ui-button-gold.webp.* | `ui-button-green.webp` |

**Pemasangan.** Gambar dipasang sebagai `border-image` dengan potongan sudut saja (seperti `ui-frame`), di bawah blok *QUIET BUTTONS* di `public/assets/css/cinematic.css`; tulisan dan kotak panah tetap dari halaman. Sebelum gambar diterima, permukaan CSS yang tampil.

### PL-10 sampai PL-12 · Gambar halaman galat (404, 500, lainnya)

Wawancara pengembang 19 November 2026, putaran ketiga ([04](04_CATATAN_KEPUTUSAN.md)): halaman galat ikut bergaya GELITA dan diberi gambar. Ketiga halaman memakai satu kartu bersama (`app/Views/errors/html/_gelita.php`): gambar di atas, lalu kode kecil, judul, kalimat, dan tombol *Kembali ke awal*. Gambar tampil begitu berkasnya terpasang; sebelum itu kartu tetap tanpa gambar.

| Kode | Halaman | Teks halaman (sudah ada) | Berkas | Kanvas |
|---|---|---|---|---|
| `PL-10` | 404 | *Jalan ini tidak ada* | baru: `public/assets/ui/error-404.webp` | 960 × 720 |
| `PL-11` | 500 (galat server) | *Lentera sedang padam* | baru: `public/assets/ui/error-500.webp` | 960 × 720 |
| `PL-12` | 400 dan galat lain (403, 405, 429, …) | *Jalan ini sedang tertutup* | baru: `public/assets/ui/error-400.webp` | 960 × 720 |

| | |
|---|---|
| Lampiran | `ai-studio/game-images/assets/referensi/reference.06.webp` (acuan gaya), `public/assets/ui/jaka-gate.webp` (Jaka, untuk `PL-10`), `ai-studio/game-images/assets/ikon/ui.lantern.webp` (`U-14`: Lentera Kedu, untuk `PL-11` dan `PL-12`) |
| Aturan bersama | Gambar suasana, bukan antarmuka: tanpa tulisan, angka, atau tanda; tepi kiri, kanan, dan bawah memudar lembut ke biru tua #0B1320 supaya menyatu dengan kartu; tenang dan ramah, tidak menakutkan untuk anak. |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Asset: error-404  (PL-10 · "this path does not exist")
Characters: Jaka, the same boy as in the attached picture (white T-shirt, red scarf, batik sash, dark shorts, sandals, brown backpack), small in the scene, seen from behind three-quarter.

SUBJECT AND COMPOSITION
A misty fork in a footpath at dusk in the Kedu hills. Jaka stands at the fork holding an unrolled paper map, scratching his head, puzzled but not scared. A wooden signpost with two BLANK arrow boards (no writing) points both ways; both paths fade into soft blue mist. Gentle warm light from a few distant village windows, fireflies. Calm and a little funny, never gloomy.
The lower third and the edges are calm and dark; the left, right and bottom edges fade softly into navy #0B1320.

DELIVERY
Canvas exactly 960 × 720 pixels (4:3). Opaque. WebP, named error-404.webp.

CONSTRAINTS
No text, letters, numbers, logos, watermarks or frames on the signpost or anywhere. No painterly 2D, gouache or paper grain.
```

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Asset: error-500  (PL-11 · "the lantern has gone out")
Characters: no people.

SUBJECT AND COMPOSITION
The attached Lantern of Kedu (same carved gold design) standing on a mossy stone at night, its flame just gone out: a single thin, soft curl of pale smoke rises from the wick, and a tiny glowing ember still shows inside, hopeful. A few small golden sparks of light drift near it, as if waiting to light it again. Soft moonlight, deep navy surroundings, a hint of bamboo leaves. Calm, gentle, a little sad but hopeful; not broken, not burning.
The edges fade softly into navy #0B1320.

DELIVERY
Canvas exactly 960 × 720 pixels (4:3). Opaque. WebP, named error-500.webp.

CONSTRAINTS
No text, letters, numbers, logos, watermarks or frames. No fire, no damage. No painterly 2D, gouache or paper grain.
```

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Asset: error-400  (PL-12 · "this path is closed for now")
Characters: no people.

SUBJECT AND COMPOSITION
A small carved wooden village gate (Javanese style, simple) across a stone footpath in the Kedu hills at dusk, its two doors gently closed, the attached Lantern of Kedu hanging from the gate's beam and glowing softly, welcoming. Behind the gate the path continues into misty terraced hills and the far silhouette of twin volcanoes. The feeling: closed for a moment, open again soon. Friendly and calm.
The edges fade softly into navy #0B1320.

DELIVERY
Canvas exactly 960 × 720 pixels (4:3). Opaque. WebP, named error-400.webp.

CONSTRAINTS
No text, letters, numbers, signs, logos, watermarks or frames. No painterly 2D, gouache or paper grain.
```

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
| `U-07` dan `U-36` (bersama) | `U-07`: `public/assets/ui/hud-shards-frame.webp`; `U-36`: `public/assets/ui/hud-shards-crystal.webp` | Kristal menjadi lapisan di `.hud-shards .lantern-toggle` pada kotak lubang (`left: 5.29%`, `top: 17.7%`, `width: 10.57%`, `height: 65.3%` dari bingkai), di bawah `.lantern-gem`; `.lantern-gem` dipindah ke kotak yang sama. Diperiksa di semua ukuran `--plate-w` dan keempat `data-stage` |
| `U-06`, `U-08`, `U-09` – `U-11`, `U-12` (`star.webp`), `U-13` | berkas yang sama di `public/assets/ui/` | `U-11`: hapus `icon('lock', 'badge-lock')` di `map-level.php` dan `lantern.php`, karena gemboknya kini di gambar. `U-12`: salin juga ke `ai-studio/game-images/assets/antarmuka/ui.result-star.webp` |
| `U-14` – `U-23` | `ai-studio/game-images/assets/ikon/` dan `…/lencana/` di tempat berkas lama | `php spark gelita:katalog:impor`. Tombol Kenali di Peta Kedu memakai `map.lantern.{wilayah}` (slot `map.lantern.*` di `ImageSlots` menjadi dipakai); `public/assets/ui/icon-kenali.webp` dihapus |
| `U-24` – `U-33` | `ai-studio/game-images/assets/ikon/avatar.{N}.webp` | `php spark gelita:katalog:impor` (`avatar.2` juga medali layar hasil) |
| — | `ui.result-hud-control` | Slot dan entri `data.json` dibuang; HUD hanya memakai gambar `round-*` |
| `U-34`, `U-35` | `ai-studio/game-images/assets/ikon/` | Buat `app.icon-512`, `app.icon-192`, `app.apple-touch-icon` (180), `app.icon-maskable-512`, `app.favicon` dengan perkecilan proporsional; `public/assets/app/*.png` diganti, `icon.svg` diganti PNG di manifest dan `<link>` |
| `S-W`, `S-02` – `S-05` | `ai-studio/game-images/assets/latar/` di tempat berkas lama | `bg.cerita-penutup-04.webp` lama dipindahkan lebih dulu menjadi `bg.cerita-penutup-01.webp` (menggantikan beranda tepi laut). Entri `data.json` mendapat prompt baru dari bagian ini; `php spark gelita:katalog:impor` |
| `PK-` | `ai-studio/game-images/assets/pustaka/` di tempat berkas lama | Entri yang keterangannya berubah mendapat `caption` (`id`, `en`) di `data.json`; `php spark gelita:katalog:impor` |

Setelah dipasang: lembar telaah dibangun ulang (`node ai-studio/game-images/review/build-sheets.mjs`), halaman katalog dibangun ulang (`php spark gelita:katalog:halaman`), dan tata letak per layar diperiksa lagi di Chromebook 1366 × 768 (langkah 3 perombakan antarmuka).

## Kebijakan perubahan

Prompt di dokumen ini boleh disesuaikan pengembang bila hasil gambar gagal pada pemeriksaan yang sama tiga kali; perubahannya dicatat di [04](04_CATATAN_KEPUTUSAN.md) dan baris `TASKS.md`-nya diberi status `redo:` dengan alasan baru. Naskah cerita tidak diubah oleh dokumen ini.
