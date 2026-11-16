# Prompt Gambar Perbaikan dari Telaah Gambar

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 15 November 2026, `R-12` ditambahkan 16 November 2026 · Lampiran `ai-studio/game-images/REVIEW.md` (telaah gambar oleh pengembang) · Status: `draf pengembang`, `asal: pengembang` (disusun AI atas permintaan pengembang)

Pengembang menelaah semua gambar game pada 15 November 2026 dan menandai gambar yang harus dibuat ulang. Dokumen ini memuat prompt siap tempel ke ChatGPT (bahasa Inggris) untuk sepuluh gambar bertanda `redo`, ditambah satu gambar pasangan yang menunggu persetujuan pengembang. Gambar `redo later` menjadi baris baru setelah pengembang mengirim foto acuannya; yang pertama `R-12` (16 November 2026). Polanya sama dengan [37](37_PROMPT_GAMBAR_SUSUN_DAN_CERITA.md) dan [38](38_PROMPT_PETA_LIMA_TANTANGAN_DAN_SKENARIO.md). Daftar kerjanya ada di `ai-studio/chatgpt-images/TASKS.md` (baris berkode `R-`).

Telaah ini pemeriksaan pengembang sendiri, bukan validasi ahli ([01 → Peran pengembang, peneliti, dan ahli](01_REVISI_10_ANALITIK.md)).

## Ringkasan

| Kode | Gambar | Nomor di telaah | Yang diperbaiki |
|---|---|---|---|
| `R-01` | Latar wilayah Temanggung `bg.temanggung.region` | 213 | Berkasnya tertukar dengan Wonosobo; gambar sementara menampilkan Dataran Kedu saat senja, bukan Temanggung |
| `R-02` | Benda Cari Objek `challenge.item.wnb-4-a02` (buah carica) | 271 | Gambarnya belimbing, padahal petunjuknya "mirip pepaya, tetapi ukurannya kecil" |
| `R-03` | Pustaka Magelang, Tips Jaka `library.magelang.p10.1` | 302 | Tokohnya bukan Jaka; latarnya krem, dua lainnya biru tua |
| `R-04` | Pustaka Temanggung, Tips Jaka `library.temanggung.p10.1` | 321 | Tokohnya bukan Jaka |
| `R-05` | Pustaka Wonosobo, Tips Jaka `library.wonosobo.p10.1` | 341 | Tokohnya bukan Jaka |
| `R-06` | Gambar tes `instr.tes-ae-B01` | 374 | Tombol tertulis "Piih foto" |
| `R-07` | Gambar tes `instr.tes-bd-A-G1` | 382 | Tombol "Kirim pesan ke galeri" lebih tebal daripada tiga tombol lain dan menunjuk kunci `tes-bd-A03` |
| `R-08` | Gambar tes `instr.tes-bd-A-G2` | 383 | Emoji keliru; tulisan "Video: Sanggar Lima Purnama" terlalu kecil |
| `R-09` | Gambar tes `instr.tes-bd-B-G1` | 384 | Tombol "Kirim pesan ke galeri" lebih tebal; motif kain tampak seperti tulisan Arab yang bisa dibaca |
| `R-10` | Gambar tes `instr.tes-mb-A-M4` | 387 | Nama akun tertulis "@panitia.kaghaianak" |
| `R-11` | Gambar tes `instr.tes-bd-B-G2` | 385 | Disetujui pengembang 16 November 2026. Pasangan `R-08` di buklet B: tulisan "Video: Sanggar Tujuh Pelita" sama kecilnya. Dikerjakan hanya kalau pengembang setuju, supaya buklet A dan B tetap setara |
| `R-12` | Pustaka Magelang, arca Buddha Mendut `library.magelang.p5.2` | 293 | Buddha duduk bersila (aslinya kedua kaki menjuntai); dua arca pengapit berdiri (aslinya duduk); ada singa dan anak yang menyembah. Foto acuan dari pengembang, lisensi didapat peneliti |
| `R-13` | Foto unggahan Cari Objek Temanggung `challenge.post.tmg-4-b` (uceng goreng) | — | Belum ada fotonya, dan pemasang katalog belum mengenal gambar unggahan; slot `challenge.post.*` ditambahkan 16 November 2026 |
| `R-14` | Pustaka Wonosobo, peta kawasan Dieng `library.wonosobo.p2.2` | 325 | Halaman butuh peta kawasan Dieng, bukan pemandangan. Lima peta acuan dari pengembang, lisensi didapat peneliti. Satu-satunya gambar isi game bertulisan (nama tempat) |
| `R-15` | Pustaka Wonosobo, buah carica `library.wonosobo.p9.2` | 339 | Belahan buah berdaging jingga tebal dengan biji hitam besar seperti pepaya; carica berdaging tipis krem dengan isi putih berbiji kecil banyak. Bergaya lukisan. Foto acuan dari pengembang |
| `R-16` | Pustaka Temanggung, Situs Liyangan `library.temanggung.p5.1` | 312 | Menara candi tinggi seperti Prambanan; Liyangan adalah kampung yang digali: teras bertalud batu, jalan batu, candi kecil, yoni, Gunung Sindoro. Foto dari peneliti |

## Aturan bersama

**Gaya yang berlaku (pengembang, 15 November 2026).** Seluruh antarmuka game memakai satu gaya, yaitu *emas berukir* yang sudah disetujui peneliti: bingkai emas tebal berukir dalam dengan hiasan ukel, bidang batu gelap atau biru tua berpola awan samar, simbol timbul dari emas yang sama, satu arah cahaya dari kiri atas, dirender sebagai benda 3D. Contohnya tombol bundar `public/assets/ui/round-*.webp` dan pelat `plate-gold-*.webp`. Hijau dan merah hanya menjadi warna bidang untuk benar dan salah, selalu bersama tanda centang atau silang. Pengembang juga meminta hiasan tepi bagian-bagian itu diseragamkan lagi; pesanan gambar antarmuka menyusul setelah ukuran setiap bagian diukur di layar Chromebook (`docs/PROGRESS.md` → *Asset catalogue audit and picture review*).

Gambar di dokumen ini bukan bagian antarmuka. Gayanya mengikuti kelompoknya masing-masing:

- **Gambar isi game** (`R-01` sampai `R-05`, `R-12` sampai `R-16`): blok `GELITA ACTIVE VISUAL DIRECTION — 2026-10-11` dari katalog, kata per kata, seperti di 37. Tanpa sapuan kuas dan tanpa tekstur kertas.
- **Gambar tes** (`R-06` sampai `R-11`): gambar garis hitam-putih seperti 23 gambar tes lainnya. Gaya ini disengaja: instrumen tes tampil netral dan tidak memakai gaya game.

**Tulisan di gambar.** Gambar isi game tidak memuat tulisan, kecuali peta `R-14` (nama tempat, daftar tetap). Gambar tes justru memuat tulisan, dan setiap kata harus sama persis dengan `app/Database/Seeds/data/instrumen/tes.php`. Tulisan di gambar dibuat oleh model gambar, jadi setiap hasil dibaca ulang kata per kata dengan daftar di promptnya.

**Gambar tes diperbaiki, bukan digambar dari nol.** Berbeda dari aturan `redo` di `ai-studio/chatgpt-images/AGENTS.md`: gambar tes yang ada dilampirkan dan hanya bagian yang salah yang diubah. Alasannya, semua tulisan lainnya sudah dicocokkan dengan `tes.php` pada 15 November 2026, dan buklet A dan B harus tetap serupa. Kalau hasil suntingan mengubah bagian lain, ulangi dari gambar lama, jangan dari hasil yang gagal.

**Tidak membocorkan kunci.** Tidak ada bagian gambar yang lebih tebal, lebih terang, atau lebih besar daripada bagian sejenisnya, kecuali diminta prompt.

## Gambar isi game

### R-01 · Latar wilayah Temanggung

| | |
|---|---|
| Kode berkas | `bg.temanggung.region` → simpan sebagai `bg.temanggung.region.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `reference.06` atau `reference.07`; `ai-studio/game-images/assets/latar/bg.magelang.region.webp` sebagai contoh latar wilayah yang sudah benar (untuk suasana dan komposisi saja) |
| Dipakai di | Dialog masuk wilayah Temanggung dan peta wilayahnya; judul Kenali pertama: "Di Antara Dua Gunung" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.temanggung.region  (R-01 · Temanggung region background)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all. Attached GELITA pictures are for render finish, mood and composition only; do not copy their subject.

SUBJECT AND COMPOSITION
Wide 16:9 cinematic landscape. Main subject inside the central 75% of the width and the central 80% of the height; horizon between 55% and 65% of the height; the lower third calm and slightly darker for text panels; the top 8% free of important detail; mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom.
Early morning in Temanggung, Central Java, seen from the east. Exactly TWO volcanoes stand side by side and fill the upper middle of the picture: Mount Sindoro on the right, a clean symmetrical cone, and Mount Sumbing on the left, broader and a little taller. No third or fourth peak anywhere on the horizon. Below them: terraced coffee and vegetable fields on dark volcanic soil, bamboo groves, a small village of Javanese houses with red clay-tile roofs, and one wooden signpost at a crossroads whose boards are blank. A thin glint of a river in the valley. Soft gold sunrise light from the left.
The Mist of Forgetting lies in the valley and half hides the signpost: cold desaturated blue-grey mist (#8C97AA to #5B6478) in soft layered bands, with faint wisps that dissolve like fading ink strokes and never form letters; eerie but never scary.

Accuracy — This is Temanggung, not Dieng and not Magelang: no temple, no stupa, no lake, no sea of clouds below the viewer, no sea or coast. No tobacco plants, cigarettes or smoke. No people, animals or vehicles in the foreground.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque, full-bleed picture. Deliver WebP, named bg.temanggung.region.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain. No night sky: this is morning.
```

### R-02 · Buah carica (benda Cari Objek Wonosobo)

| | |
|---|---|
| Kode berkas | `challenge.item.wnb-4-a02` → simpan sebagai `challenge.item.wnb-4-a02.webp` |
| Kanvas | 240 × 240, latar transparan (buat pada 1024 × 1024, lalu perkecil) |
| Lampiran | `ai-studio/game-images/assets/tantangan/challenge.item.wnb-1-f01.webp` (pohon carica yang sudah benar: bentuk dan warna buah); `challenge.item.wnb-4-a05.webp` (pepaya biasa: untuk hasil akhir yang sama dan supaya kedua benda jelas berbeda). Foto carica dari pengembang (16 November 2026): `inbox/reference-carica-whole-pile-13.webp` dan `inbox/reference-carica-on-tree-11.jpg` (bentuk dan warna buah utuh) |
| Butir | `wnb-4-x02`: "Temukan buah pegunungan yang mirip pepaya, tetapi ukurannya kecil. Buah ini tumbuh baik di udara sejuk dan sering dibuat manisan." Pengecohnya `wnb-4-x05`, pepaya biasa |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-4-a02  (R-02 · carica fruit, hidden-object cut-out)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Match the render finish, lighting and outline quality of the attached papaya cut-out exactly, so that the two objects look like parts of one set.

SUBJECT AND COMPOSITION
One isolated object on a transparent background: a small cluster of FOUR whole carica fruits (mountain papaya, Vasconcellea pubescens, the fruit of the Dieng highlands) with one green leaf behind them. Each fruit is small and plump, shaped like a short, rounded papaya about as long as a child's hand: an oval body with a blunt stem end and a gently pointed tip, smooth golden-yellow skin with a touch of green towards the tip on one fruit, and five clear lengthwise ribs whose edges are ROUNDED, never sharp. The fruits lie together at slightly different angles, one resting on another. Follow the attached carica photos for shape and colour, and the fruits on the tree in the attached garden picture for the finish.

Accuracy — This must NOT read as star fruit (belimbing): no sharp wing-like ridges, no star-shaped cross-section, no waxy translucent look. It must NOT read as an ordinary papaya either: several small fruits, not one large long fruit. Do not show a cut fruit, seeds, a slice, a jar, a basket, a plate or a knife.
Hidden-object rule — identical lighting and quality for targets and decoys: no glow, no sparkle, no outline, no label, no arrow, nothing that marks this object as an answer.

DELIVERY
Generate at 1024 × 1024 and scale down proportionally to exactly 240 × 240 pixels; aspect 1:1. The whole cluster inside the canvas with a small even margin. Genuine transparent background outside the subject; preserve alpha; no cast shadow on a ground. Deliver WebP, named challenge.item.wnb-4-a02.webp.

CONSTRAINTS
No watermarks, signatures, text, extra objects, cropped fruit, blurry edges, neon colours, or fake checkerboard transparency. No painterly 2D, gouache or paper grain.
```

### R-03 · Tips Jaka, Magelang

| | |
|---|---|
| Kode berkas | `library.magelang.p10.1` → simpan sebagai `library.magelang.p10.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.01.webp` (lembar karakter Jaka) dan `public/assets/ui/jaka-wave.webp` (Jaka yang tampil di game); `public/assets/ui/round-eye.webp` sebagai contoh medali emas berukir |
| Halaman Pustaka | "Tips Jaka: Fakta, Pendapat, dan Menghubungkan Informasi" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p10.1  (R-03 · Jaka's tips, Magelang)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Jaka: this picture MUST show Jaka exactly as on the attached character sheet and game sprite: an approximately 11-year-old Javanese boy, tousled dark-brown hair with a cowlick, brown eyes, cream short-sleeve shirt, RED SCARF around his neck, brown knee-length shorts, orange-brown batik waist cloth, sandals, leather backpack. He wears NO headcloth (no iket, no blangkon), no striped lurik jacket, no vest and no long batik cloth. Same face, same proportions, same 3D finish as the attachments.

SUBJECT AND COMPOSITION
Jaka stands behind a low wooden table, whole figure visible including his feet, three-quarter view facing screen right, smiling at the two cards he holds up: the card in his right hand shows a magnifying glass with a check mark, the card in his left hand shows a speech bubble with a small heart. On the table, side by side, lie a small landscape picture and a sheet with plain grey lines in place of writing.
Background: one plain deep-navy backdrop (#1B2740 falling to #0B1320 at the edges) with a soft warm glow behind Jaka; no room, no scenery. The symbols on the two cards are embossed gold on a dark face, in the carved-gold finish of the attached round button.
Soft warm key light from the upper left and a faint cool rim light from the right.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Jaka and the table centred, complete within the canvas, with even margins. Opaque background. Deliver WebP, named library.magelang.p10.1.webp.

CONSTRAINTS
No letters, numbers or words anywhere, also not on the cards or the sheet. No second person. No watermarks, signatures, extra limbs, cropped features, blurry edges or neon colours. No painterly 2D, gouache, paper grain or cream paper background.
```

### R-04 · Tips Jaka, Temanggung

| | |
|---|---|
| Kode berkas | `library.temanggung.p10.1` → simpan sebagai `library.temanggung.p10.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | Sama dengan `R-03` |
| Halaman Pustaka | "Tips Jaka: Membaca Informasi Tersurat" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p10.1  (R-04 · Jaka's tips, Temanggung)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Jaka: this picture MUST show Jaka exactly as on the attached character sheet and game sprite: an approximately 11-year-old Javanese boy, tousled dark-brown hair with a cowlick, brown eyes, cream short-sleeve shirt, RED SCARF around his neck, brown knee-length shorts, orange-brown batik waist cloth, sandals, leather backpack. He wears NO headcloth (no iket, no blangkon), no striped lurik jacket, no vest and no long batik cloth. Same face, same proportions, same 3D finish as the attachments.

SUBJECT AND COMPOSITION
Jaka sits on a bamboo stool at a bamboo table, whole figure visible including his feet, three-quarter view facing screen right. He reads a single sheet of paper that carries plain grey lines in place of writing, and points at one line with his finger. A magnifying glass lies beside the sheet. His backpack rests against the stool.
Four round medallions float around him in an arc, two on each side, all the same size: an eye, a magnifying glass, a pointing finger, a check mark. Each medallion has the carved-gold rim and dark stone face of the attached round button, with the symbol embossed in gold.
Background: one plain deep-navy backdrop (#1B2740 falling to #0B1320 at the edges) with a soft warm glow behind Jaka; no room, no scenery.
Soft warm key light from the upper left and a faint cool rim light from the right.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Jaka, the table and the four medallions complete within the canvas, with even margins. Opaque background. Deliver WebP, named library.temanggung.p10.1.webp.

CONSTRAINTS
No letters, numbers or words anywhere, also not on the sheet. No second person. No watermarks, signatures, extra limbs, cropped features, blurry edges or neon colours. No painterly 2D, gouache or paper grain.
```

### R-05 · Tips Jaka, Wonosobo

| | |
|---|---|
| Kode berkas | `library.wonosobo.p10.1` → simpan sebagai `library.wonosobo.p10.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | Sama dengan `R-03` |
| Halaman Pustaka | "Tips Jaka: Cek Dulu Sebelum Percaya dan Berbagi" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p10.1  (R-05 · Jaka's tips, Wonosobo)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Jaka: this picture MUST show Jaka exactly as on the attached character sheet and game sprite: an approximately 11-year-old Javanese boy, tousled dark-brown hair with a cowlick, brown eyes, cream short-sleeve shirt, RED SCARF around his neck, brown knee-length shorts, orange-brown batik waist cloth, sandals, leather backpack. He wears NO headcloth (no iket, no blangkon), no striped lurik jacket, no vest and no long batik cloth. Same face, same proportions, same 3D finish as the attachments.

SUBJECT AND COMPOSITION
Jaka stands, whole figure visible including his feet, three-quarter view facing screen right. He holds a smartphone in one hand and looks at it thoughtfully, the other hand at his chin. The phone screen shows only soft abstract shapes.
Four round medallions float around him, two on each side, all the same size: a public building with columns and a flag pole, a clock, a magnifying glass, a shield. Each medallion has the carved-gold rim and dark stone face of the attached round button, with the symbol embossed in gold. The clock face has hands and tick marks but no numerals.
Background: one plain deep-navy backdrop (#1B2740 falling to #0B1320 at the edges) with a soft warm glow behind Jaka; no room, no scenery.
Soft warm key light from the upper left and a faint cool rim light from the right.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. Jaka and the four medallions complete within the canvas, with even margins. Opaque background. Deliver WebP, named library.wonosobo.p10.1.webp.

CONSTRAINTS
No letters, numbers or words anywhere, also not on the phone, the building or the clock. No second person. No watermarks, signatures, extra limbs, cropped features, blurry edges or neon colours. No painterly 2D, gouache or paper grain.
```

### R-12 · Arca Buddha di dalam Candi Mendut (Pustaka Magelang)

Ditambahkan 16 November 2026. Pengembang menandai gambar ini `redo later` (telaah no. 293): arcanya duduk bersila, padahal arca Buddha Mendut duduk dengan kedua kaki menjuntai. Pengembang lalu mengirim empat foto acuan yang lisensinya sudah didapat peneliti; foto itu ada di `ai-studio/chatgpt-images/inbox/reference-Mendut-*` dan tercatat di `reference-sources-2026-11-15.json`. Dicocokkan dengan foto, gambar lama keliru di empat hal: (1) Buddha duduk bersila; (2) dua arca pengapit berdiri, padahal keduanya duduk; (3) ada dua singa di depan singgasana yang tidak ada di ruang Mendut; (4) ada anak yang menyembah di depan arca. Gambar baru tidak menampilkan orang: di halaman Pustaka tentang tempat ibadah agama lain, anak cukup melihat arcanya, tidak ikut digambarkan beribadah.

| | |
|---|---|
| Kode berkas | `library.magelang.p5.2` → simpan sebagai `library.magelang.p5.2.webp` |
| Kanvas | 960 × 640 |
| Lampiran | Acuan gaya `reference.06` atau `reference.07`; foto acuan `inbox/reference-Mendut-Buddha-04.jpg` (Buddha dari dekat: sikap, tangan, lingkaran kepala, sandaran) dan `inbox/reference-Mendut-interior-Buddha-and-bodhisattvas-01.jpg` (susunan tiga arca dan ruangnya). Foto hanya untuk kebenaran bentuk; jangan menyalin pagar kayu, sesaji modern, atau warna fotonya |
| Halaman Pustaka | "Mendut, Pawon, dan Perjalanan Waisak", bagian *Candi Mendut*: "Di dalam ruang Candi Mendut duduk arca Buddha setinggi sekitar tiga meter dari batu andesit, diapit dua arca lain." Keterangan gambar (tetap): "Arca Buddha di dalam Candi Mendut" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.magelang.p5.2  (R-12 · the Buddha inside Candi Mendut, library picture)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all. The attached photos are factual references for the statues and the room only: follow their forms closely, but render them in the GELITA finish, and do not copy the railing, offerings, colours or camera of the photos.

SUBJECT AND COMPOSITION
The inner chamber of Candi Mendut, Magelang, Central Java, seen from the doorway, eye level of a standing child, looking straight at the back wall. Dim, calm, respectful; soft daylight falls in from the doorway behind the viewer and lights the statues from the front and slightly left.
Centre: the large Buddha statue of grey-brown andesite, about three times the height of a person, on a plain stepped stone pedestal. He sits UPRIGHT ON A THRONE WITH BOTH FEET DOWN, like a person sitting on a chair: knees apart and level, lower legs vertical and parallel, BOTH bare feet resting flat side by side on a small round lotus footstool on the pedestal. He does NOT sit cross-legged and no leg is folded. Both hands are raised together in front of the chest in the teaching gesture (dharmacakra mudra): the thumb and index finger of each hand form a small ring, the two hands touching. A plain monastic robe so smooth it looks like bare stone, covering the left shoulder and leaving the right shoulder bare; short tight curls on the head with a rounded top knot; calm face with lowered eyes; long earlobes. Behind his head and shoulders a large pointed, leaf-shaped halo carved in the wall, and on both sides of his backrest a carved upright mythical creature in relief.
Left of the Buddha, on its own lower pedestal: a seated bodhisattva statue (Avalokitesvara), richly crowned and jewelled, seated with one leg folded and one leg hanging down. Right of the Buddha, the same: a seated bodhisattva (Vajrapani), crowned and jewelled, one leg folded and one leg hanging down. Both are smaller than the Buddha and turned slightly towards him. All three statues are the same dark grey-brown andesite, weathered, never gold, never painted.
Walls: large grey andesite blocks, darker towards the corners, the corner of the chamber visible on both sides. The floor is worn stone in the foreground, empty.

Accuracy — Exactly three statues. The Buddha sits with both feet down on the footstool: no lotus position, no crossed legs, no legs hidden under the robe. The two side statues are SEATED, not standing. No lions, elephants or guardian animals in front of the pedestal. No candles, flowers, incense, offerings, railing or signs. No people. No bright sunbeam rays.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. The Buddha centred; all three statues complete within the canvas, including the Buddha's feet and footstool, with even margins. Opaque background. Deliver WebP, named library.magelang.p5.2.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, cropped statues, extra limbs or hands, blurry edges, neon colours or gold-painted statues. No painterly 2D, gouache or paper grain.
```

### R-13 · Foto unggahan uceng goreng (Cari Objek Temanggung, babak 2, variasi b)

Ditambahkan 16 November 2026. Unggahan `tmg-4-b` ("Unggahan video ikan uceng", 08 → *Variasi b*) belum punya foto, dan sampai hari ini pemasang katalog tidak bisa memasang gambar unggahan. Pengembang memilih foto baru, bukan potongan gambar Susun Gambar `tmg-1-h01`; slot `challenge.post.*` ditambahkan untuk itu (1280 × 720). Foto tampil di kotak 16:9 di kiri adegan. Tulisan "Video: Warung Tepi Kali Bu Tini" (bagian ketuk B2) ditempel permainan di atas seperempat bawah foto, dari tepi kiri sampai kira-kira tiga perempat lebarnya, jadi bagian itu harus tenang.

| | |
|---|---|
| Kode berkas | `challenge.post.tmg-4-b` → simpan sebagai `challenge.post.tmg-4-b.webp` |
| Kanvas | 1280 × 720 |
| Lampiran | Acuan gaya `reference.06` atau `reference.07`; `ai-studio/game-images/assets/tantangan/challenge.item.tmg-1-h01.webp` (piring uceng goreng di pojok kiri bawah: bentuk ikan dan hasil akhir) |
| Butir | `tmg-4-b02`: papan informasi "Ikan uceng adalah ikan sungai kecil, makanan khas Temanggung." melawan keterangan "Uceng goreng, ikan laut khas Temanggung." Foto tidak boleh membantu atau menyesatkan: uceng kecil biasa, tanpa laut dan tanpa sungai |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.post.tmg-4-b  (R-13 · fried uceng, photo inside a social-media post)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people or hands at all. The attached puzzle picture shows the same food in the corner: match the fish and the finish, not its river scene.

SUBJECT AND COMPOSITION
A food picture of the kind people post from a small village food stall: seen from above at a slight angle, on a wooden table. In the centre-right: one round woven rattan plate lined with a banana leaf, heaped with crispy fried uceng — many SMALL, SLENDER fish, each about as long as a finger, fried whole until golden-brown and dry, with crisp little tails. Beside the plate: a small bowl of red sambal, a few leaves of fresh lalapan (cucumber slices, basil), and a glass of warm tea. Warm morning light from the left.
The lower quarter of the picture, from the left edge to about three quarters across, is plain wooden table surface only: a caption strip will be laid over it. Keep the plate and the bowl out of that band.

Accuracy — Uceng are tiny freshwater fish of Temanggung: small and slender like a finger, never big fish, never fish steaks or fillets. No sea food at all: no large sea fish, no prawns, squid, crab or shells. No sea, beach, river or boat in the picture. No brand, packaging or menu.

DELIVERY
Target canvas 1280 × 720 pixels; aspect 16:9. The plate complete within the canvas, its centre a little right of the middle. Opaque, full-bleed picture. Deliver WebP, named challenge.post.tmg-4-b.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, play buttons, frames, social-media interface, emoji, blurry edges or neon colours. No painterly 2D, gouache or paper grain.
```

### R-14 · Peta kawasan Dataran Tinggi Dieng (Pustaka Wonosobo)

Ditambahkan 16 November 2026. Pengembang menandai gambar ini `redo later` (telaah no. 325): halaman Pustaka "Dieng, Tempat Para Dewa" membutuhkan peta kawasan Dieng, bukan pemandangan (gambar sekarang juga menampilkan anak berikat kepala lurik). Pengembang mengirim lima peta acuan yang lisensinya sudah didapat peneliti (`ai-studio/chatgpt-images/inbox/reference-Dieng-*` dan `reference-Wonosobo-*`, dicatat di `reference-sources-2026-11-15.json`). Letak tempat diambil dari peta jalur wisata Dinas Pariwisata dan Kebudayaan Wonosobo (`-08`); peta lain untuk batas kabupaten (`-06`, `-09`) dan untuk rupa peta timbul (`-05`, `-07`; `-07` tampak buatan AI, jadi bukan sumber fakta).

**Pengecualian tulisan.** Peta tidak berguna tanpa nama tempat, jadi gambar ini satu-satunya gambar isi game yang memuat tulisan. Tulisannya hanya daftar *All text* di prompt, dan setiap kata dibaca ulang seperti gambar tes. Halaman ini menerangkan bahwa Dieng terbagi antara beberapa kabupaten; peta menunjukkan hal itu.

| | |
|---|---|
| Kode berkas | `library.wonosobo.p2.2` → simpan sebagai `library.wonosobo.p2.2.webp` |
| Kanvas | 960 × 640 |
| Lampiran | Acuan gaya `reference.06` atau `reference.07`; `inbox/reference-Dieng-route-map-Wonosobo-tourism-office-08.jpg` (letak tempat dan jalan); `inbox/reference-Dieng-map-dieng-net-05.jpg` (contoh peta timbul) |
| Halaman Pustaka | "Dieng, Tempat Para Dewa", bagian *Satu dataran, beberapa kabupaten*: Kecamatan Kejajar (Wonosobo), Kecamatan Batur dan sebagian Pejawaran (Banjarnegara), sebagian Kabupaten Batang; pusat wisata Desa Dieng Kulon (Banjarnegara) dan Dieng Wetan (Wonosobo). Keterangan gambar setelah dipasang: "Peta kawasan Dataran Tinggi Dieng" (keterangan asli halaman) |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p2.2  (R-14 · map of the Dieng Plateau area, library picture)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all. The attached route map is the factual source for where each place lies; the attached relief map shows the kind of picture wanted. Do not copy their text, logos, colours or layout boxes.

SUBJECT AND COMPOSITION
A pictorial relief map of the Dieng Plateau, Central Java, seen from above at a gentle angle, like a small clay diorama lying on a dark navy table: green highland with hills and two volcano peaks, winding roads as thin cream lines, small dark-blue lakes, and steam rising from one crater. North is at the top.
Three regencies share the plateau. Show them as three areas with soft, slightly different green tones, separated by thin dashed gold border lines: BANJARNEGARA on the left (west) part, WONOSOBO on the right and bottom (east and south) part, and BATANG as a narrow strip along the top (north) edge.
Places, each a small 3D landmark with its label on a small cream tag, positions following the attached route map:
- Dieng Kulon: a small village of red-roofed houses in the upper middle, inside Banjarnegara, just west of the border.
- Dieng Wetan: a small village just east of the border, inside Wonosobo, next to Dieng Kulon.
- Candi Arjuna: a small group of dark-grey stone temples next to Dieng Kulon.
- Telaga Warna: a lake of green-turquoise water close to the border, a little south-east of Candi Arjuna.
- Kawah Sikidang: a grey crater with a white steam plume, west of Telaga Warna, inside Banjarnegara.
- Sikunir: a hill with a viewpoint, south-east of Telaga Warna, inside Wonosobo.
- Gunung Prau: a long green ridge in the north-east, near the Batang strip.
A small north arrow in the top right corner, marked with the letter U.

All text — exactly these words and nothing else, each spelled exactly so: "BANJARNEGARA", "WONOSOBO", "BATANG" (area names, large, gold capitals), "Dieng Kulon", "Dieng Wetan", "Candi Arjuna", "Telaga Warna", "Kawah Sikidang", "Sikunir", "Gunung Prau" (place tags, small dark letters on cream), "U" (north arrow). No title, no legend, no other words, numbers or road names.

Accuracy — Dieng Kulon, Candi Arjuna and Kawah Sikidang are in Banjarnegara; Dieng Wetan, Telaga Warna and Sikunir are in Wonosobo. No sea, no city, no temple other than Candi Arjuna. No tobacco.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. The whole map complete within the canvas, with an even margin of dark navy table around it. Opaque background. Deliver WebP, named library.wonosobo.p2.2.webp.

CONSTRAINTS
No watermarks, signatures, logos, frames, misspelled or extra words, blurry edges or neon colours. No painterly 2D, gouache or paper grain.
```

### R-15 · Buah carica di pohon dan dibelah (Pustaka Wonosobo)

Ditambahkan 16 November 2026. Pengembang menandai gambar ini `redo later` (telaah no. 339): buah yang dibelah tampak seperti pepaya biasa. Pengembang mengirim lima foto carica (`ai-studio/chatgpt-images/inbox/reference-carica-*`; lisensinya belum disebut saat dikirim, lihat `reference-sources-2026-11-15.json`). Dicocokkan dengan foto: buah utuh di gambar lama sudah mirip carica, tetapi belahannya berdaging jingga tebal dengan biji hitam besar seperti pepaya. Carica yang dibelah berdaging tipis kuning krem, dan rongganya penuh isi putih berair dengan banyak biji kecil cokelat tua. Gambar lama juga bergaya lukisan, bukan 3D.

| | |
|---|---|
| Kode berkas | `library.wonosobo.p9.2` → simpan sebagai `library.wonosobo.p9.2.webp` |
| Kanvas | 960 × 640 |
| Lampiran | Acuan gaya `reference.06` atau `reference.07`; foto `inbox/reference-carica-on-tree-11.jpg` (buah di pohon), `inbox/reference-carica-cut-halves-10.jpg` dan `inbox/reference-carica-whole-and-cut-12.jpg` (isi buah) |
| Halaman Pustaka | "Rasa Wonosobo", bagian *Carica, pepaya gunung*: buah kecil dan harum, tumbuh di ketinggian 1.400–2.400 meter seperti Dieng, biasanya dibuat manisan dalam sirup. Keterangan gambar (tetap): "Buah carica atau pepaya gunung" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.wonosobo.p9.2  (R-15 · carica, the mountain papaya, library picture)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all. The attached photos are factual references for the fruit and the tree only: follow their forms closely, render them in the GELITA finish, and do not copy the photos.

SUBJECT AND COMPOSITION
Left half: the upper part of a slim carica tree (mountain papaya, Vasconcellea pubescens) with a grey-brown trunk, a crown of large lobed leaves, and a dense cluster of fruit growing straight from the trunk just below the leaves: several ripe golden-yellow fruits and some green unripe ones. Each fruit is small, about the size of a fist, oval with a pointed tip and five clear lengthwise ribs with rounded edges.
Right half, in the foreground on a flat grey stone: two whole ripe fruits and one fruit cut in half lengthwise, the two halves open towards the viewer. Inside the cut fruit: a THIN layer of pale cream-yellow flesh under the skin, and a large hollow filled with a white, juicy, slightly translucent pulp in which MANY SMALL dark-brown seeds sit packed together. The flesh is never thick and never orange; the seeds are small and many, never a few large black balls.
Background: the cool Dieng highland on a clear morning: green terraced vegetable fields on steep slopes, a little mist in the valley, one volcano peak far away. Soft gold light from the left.

Accuracy — This is carica, not an ordinary papaya: small ribbed fruits, cream flesh, white seed pulp. No orange flesh, no large black seeds, no papaya slices, no star fruit. No jars or syrup, no people.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. The tree crown and the cut fruit complete within the canvas, with even margins. Opaque, full-bleed picture. Deliver WebP, named library.wonosobo.p9.2.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, blurry edges or neon colours. No painterly 2D, gouache, oil-painting look or paper grain.
```

### R-16 · Situs Liyangan (Pustaka Temanggung)

Ditambahkan 16 November 2026. Pengembang menandai gambar ini `redo later` (telaah no. 312): gambarnya menampilkan menara candi tinggi seperti Prambanan, padahal halaman "Jejak Mataram Kuno" bercerita tentang **Situs Liyangan**, kampung Mataram Kuno yang terkubur material letusan Gunung Sindoro dan digali sejak 2008. Peneliti mengirim foto Liyangan yang berizin; satu berkas tersimpan (`ai-studio/chatgpt-images/inbox/reference-Liyangan-excavation-wall-01.jpg`), empat lainnya hanya terlihat di percakapan dan isinya dituliskan di prompt ini: talud batu panjang di teras berundak dengan jalan batu di depannya, alas candi kecil dengan yoni, satu bangunan candi kecil yang atapnya bertingkat di atas pelataran berpagar batu, dan Gunung Sindoro di belakang situs.

| | |
|---|---|
| Kode berkas | `library.temanggung.p5.1` → simpan sebagai `library.temanggung.p5.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Liyangan-excavation-wall-01.jpg` (dinding batu teras yang digali) |
| Halaman Pustaka | "Jejak Mataram Kuno", bagian *Situs Liyangan, kampung yang terkubur*: sisa rumah kayu yang hangus, candi dan talud, jalan batu, butir padi terbakar; semuanya terkubur material letusan Gunung Sindoro. Keterangan gambar setelah dipasang: "Situs Liyangan di lereng Gunung Sindoro" (keterangan asli halaman) |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p5.1  (R-16 · the Liyangan site, library picture)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all. The attached photo is a factual reference for the stone walls only: follow its forms, render them in the GELITA finish, and do not copy the photo.

SUBJECT AND COMPOSITION
The excavated archaeological site of Liyangan on the lower slope of Mount Sindoro, Temanggung, Central Java, on a calm, slightly hazy morning, seen from a low rise at the edge of the dig. This is an ancient village buried by a volcanic eruption and dug out again, NOT a temple city.
- Middle ground, filling most of the picture: several low TERRACES of grey-brown earth, each held up by a long, straight retaining wall (talud) of neatly cut grey andesite blocks, about as high as a grown-up's chest, the top row slightly rounded. One wall runs diagonally from the lower left towards the middle.
- In front of that wall: an old road paved with rounded river stones, running along the wall.
- On the upper terrace, right of centre: one SMALL stone shrine on a square walled platform with a few steps, its roof made of three or four small stepped tiers; it is not taller than two people. Next to it, the low square base of another shrine with a yoni (a square stone basin with a spout) on top.
- Here and there: loose cut stones lying in rows on the ground, and the shallow straight-edged pits of the excavation.
- Background: Mount Sindoro, ONE large, smooth, symmetrical volcanic cone, filling the upper middle, with green bamboo groves, a few tall trees and vegetable fields between the site and the mountain. Soft light from the left.

Accuracy — No tall temple towers, no Prambanan, no Borobudur, no stupas, no guardian statues, no carved relief walls. All walls are plain cut stone. No wooden houses standing (they are gone), no tents, signs, fences, vehicles or tools. No people.

DELIVERY
Target canvas 960 × 640 pixels; aspect 3:2. The walls, the road and the small shrine complete within the canvas, the mountain top not cut off. Opaque, full-bleed picture. Deliver WebP, named library.temanggung.p5.1.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, blurry edges or neon colours. No painterly 2D, gouache, oil-painting look or paper grain.
```

## Gambar tes

Lampirkan gambar tes yang sekarang dari `ai-studio/game-images/mockup/gambar-mockup/instrumen/aset/` dan minta gambar yang sama dengan perubahan yang disebut saja. Setelah itu baca setiap kata di hasilnya dan cocokkan dengan daftar *All text* di prompt.

### R-06 · `instr.tes-ae-B01` (kotak isian gambar mewarnai)

| | |
|---|---|
| Kode berkas | simpan sebagai `instr.tes-ae-B01.webp` |
| Kanvas | 1536 × 1024 |
| Lampiran | `instr.tes-ae-B01.webp` yang sekarang; `instr.tes-ae-A01.webp` (pasangannya di buklet A, yang tulisannya benar) |

```text
Edit the attached picture "instr.tes-ae-B01". Keep everything exactly as it is: the black-and-white line-art style, the browser window, the dinosaur drawing, the layout, the fonts and every word. Change ONE thing only: the button under "Foto wajah" must read "Pilih foto" (it now reads "Piih foto").

All text in the finished picture, to be checked word by word:
- Browser tab: Gambar Mewarnai Dinosaurus
- Address bar: warnai-yuk.example
- Heading: Gambar Mewarnai Dinosaurus
- Field labels, in this order: Nama lengkap / Alamat rumah / Nomor HP orang tua / Foto wajah
- Button under the last label: Pilih foto
- Line above the main button: Semua wajib diisi
- Main button: Unduh

No other text, no colour, no new objects. Deliver at exactly 1536 × 1024 pixels as WebP, named instr.tes-ae-B01.webp.
```

### R-07 · `instr.tes-bd-A-G1` (unggahan kain karawo)

| | |
|---|---|
| Kode berkas | simpan sebagai `instr.tes-bd-A-G1.webp` |
| Kanvas | 1536 × 1024 |
| Lampiran | `instr.tes-bd-A-G1.webp` yang sekarang |
| Butir terkait | `tes-bd-A03`: kuncinya meminta izin ke galeri. Tombol "Kirim pesan ke galeri" tidak boleh lebih menonjol daripada tombol lain |

```text
Edit the attached picture "instr.tes-bd-A-G1". Keep everything exactly as it is: the black-and-white line-art style, the post, the cloth photo, the information card on the right, the layout, the fonts and every word. Change ONE thing only: the four buttons at the bottom of the post must look alike. Give "Suka", "Komentar", "Bagikan" and "Kirim pesan ke galeri" the same thin outline, the same height, the same fill and the same font weight. The fourth button may be wider because its label is longer, but nothing else about it may differ.

All text in the finished picture, to be checked word by word:
- App name: Kedugram
- Account line: @rak.kain.tia · 2 hari
- Credit on the photo: Foto: Galeri Kain Kenari Biru
- Likes: 126 suka (with an outlined heart)
- Caption: Kain karawo dari Gorontalo. Motifnya digambar pakai spidol, ya! (followed by a crayon emoji)
- Comment 1: Citra — Cantik sekali! Terima kasih sudah berbagi (followed by a clapping-hands emoji)
- Comment 2: Danu — Motifnya kayak sarang laba-laba, wkwk (followed by a tongue-out face emoji)
- Buttons: Suka / Komentar / Bagikan / Kirim pesan ke galeri
- Card heading: Kain Karawo · Gorontalo
- Card text: Kain ini disulam dengan tangan. Sebelum disulam, benang kainnya diiris dan dicabut mengikuti pola. Karawo berasal dari kata mokarawo, artinya mengiris. (the words "Karawo" at the start of the last sentence and "mokarawo" are in italics)

No other text, no colour, no new objects. Deliver at exactly 1536 × 1024 pixels as WebP, named instr.tes-bd-A-G1.webp.
```

### R-08 · `instr.tes-bd-A-G2` (unggahan video Tari Gantar)

| | |
|---|---|
| Kode berkas | simpan sebagai `instr.tes-bd-A-G2.webp` |
| Kanvas | 1536 × 1024 |
| Lampiran | `instr.tes-bd-A-G2.webp` yang sekarang |
| Butir terkait | `tes-bd-A05`: jawabannya dibaca dari tulisan "Video: Sanggar Lima Purnama". Tulisan itu harus terbaca di layar kecil. Pilihan C `tes-bd-A08` memakai emoji 🎣 yang sama dengan keterangan unggahan |

```text
Edit the attached picture "instr.tes-bd-A-G2". Keep everything exactly as it is: the black-and-white line-art style, the post, the video frame with the five dancers, the festival information board on the right, the layout, the fonts and every word. Change TWO things only:
1. In the caption, the emoji after "menangkap ikan" must be a fishing rod with a fish on the line (it now looks like headphones).
2. The credit on the video frame, "Video: Sanggar Lima Purnama", must be larger: white letters on a dark strip at the bottom left of the video frame, with letters as tall as the letters of the comments below. Do not make it bold, and do not make it larger than the caption.

All text in the finished picture, to be checked word by word:
- App name: Kedugram
- Account line: @kamera.ryan · 2 hari
- Credit on the video: Video: Sanggar Lima Purnama
- Likes: 9.400 suka (with an outlined heart)
- Caption: Tari Gantar dari Kalimantan Timur, tarian nelayan menangkap ikan (fishing-rod emoji) Video rekamanku sendiri, lho!  ("Tari Gantar" in bold)
- Comment 1: Kevin — Oh, tari nelayan, ya. Aku baru tahu! (followed by a heart-eyes emoji)
- Comment 2: Tasya — Keliru, tuh. Baca papannya dulu, dong, jangan asal tulis! (followed by a rolling-eyes emoji)
- Comment 3: Wina — Yang suka ribuan, berarti benar dong (followed by a thumbs-up emoji)
- Buttons: Suka / Komentar / Bagikan / Unduh (all four alike)
- Board heading: Papan Informasi Festival
- Board text: Tari Gantar berasal dari Kutai Barat, Kalimantan Timur. Gerakannya menggambarkan orang menanam padi. Tongkat panjang dipakai untuk melubangi tanah. Bambu pendek berisi biji-bijian menggambarkan benih padi.

No other text, no colour, no new objects. Deliver at exactly 1536 × 1024 pixels as WebP, named instr.tes-bd-A-G2.webp.
```

### R-09 · `instr.tes-bd-B-G1` (unggahan kain besurek)

| | |
|---|---|
| Kode berkas | simpan sebagai `instr.tes-bd-B-G1.webp` |
| Kanvas | 1536 × 1024 |
| Lampiran | `instr.tes-bd-B-G1.webp` yang sekarang; `instr.tes-bd-A-G1.webp` (pasangannya di buklet A, untuk bentuk avatar komentar) |
| Butir terkait | `tes-bd-B03`: kuncinya meminta izin ke galeri |
| Catatan | `tes.php` menyebut gambar ini "motif hiasan mirip huruf Arab dan bunga besar". Motif di gambar harus hiasan, bukan tulisan yang bisa dibaca. Hasilnya tetap diperiksa orang yang bisa membaca huruf Arab sebelum dikirim ke validator |

```text
Edit the attached picture "instr.tes-bd-B-G1". Keep everything exactly as it is: the black-and-white line-art style, the post, the information card on the right, the layout, the fonts and every word. Change THREE things only:
1. The four buttons at the bottom of the post must look alike. Give "Suka", "Komentar", "Bagikan" and "Kirim pesan ke galeri" the same thin outline, the same height, the same fill and the same font weight. The fourth button may be wider because its label is longer, but nothing else about it may differ.
2. Redraw the motif on the cloth. It is a besurek batik cloth: large five-petal flowers and leafy vines, and between them flowing ornamental strokes that only SUGGEST calligraphy. The strokes must be pure decoration: abstract curls, loops and dots that do NOT form any real Arabic letter, word or sacred phrase, and that cannot be read in any script. When in doubt, make the strokes more plant-like.
3. Give the two comments round avatars with the initials L (Lusi) and B (Bimo), like the avatars with initials in the attached form A picture.

All text in the finished picture, to be checked word by word:
- App name: Kedugram
- Account line: @lemari.ilham · 2 hari
- Credit on the photo: Foto: Galeri Kain Camar Putih
- Likes: 131 suka (with an outlined heart)
- Caption: Kain besurek dari Bengkulu. Motifnya disemprot pakai cat, ya! (followed by a painter's-palette emoji)
- Comment 1: Lusi — Bagus sekali! Terima kasih sudah berbagi (followed by a clapping-hands emoji)
- Comment 2: Bimo — Motifnya kayak coretan anak TK, wkwk (followed by a tongue-out face emoji)
- Buttons: Suka / Komentar / Bagikan / Kirim pesan ke galeri
- Card heading: Kain Besurek · Bengkulu
- Card text: Kain ini dibatik dengan tangan. Motifnya digambar dengan canting berisi lilin panas, lalu kain dicelup ke pewarna. Besurek artinya bertulisan. (the word "Besurek" at the start of the last sentence is in italics)

No other text, no colour, no new objects. Deliver at exactly 1536 × 1024 pixels as WebP, named instr.tes-bd-B-G1.webp.
```

### R-10 · `instr.tes-mb-A-M4` (pesan terusan dan pengumuman lomba kaghati)

| | |
|---|---|
| Kode berkas | simpan sebagai `instr.tes-mb-A-M4.webp` |
| Kanvas | 1024 × 1536 |
| Lampiran | `instr.tes-mb-A-M4.webp` yang sekarang |

```text
Edit the attached picture "instr.tes-mb-A-M4". Keep everything exactly as it is: the black-and-white line-art style, the forwarded group message at the top, the Kedugram post in the middle, the box with the three children at the bottom, the layout, the fonts and every word. Change ONE thing only: the account name under "Panitia Lomba Kaghati Anak" must read "@panitia.kaghatianak" (it now reads "@panitia.kaghaianak": the letter t is missing).

All text in the finished picture, to be checked word by word:
- Group name: Grup Pencinta Layang-Layang
- Label: Diteruskan
- Group message: PENTING!! Lomba kaghati hari Sabtu jam 7 pagi. Layang-layang plastik juga boleh, yang penting terbang tinggi. Kabari teman-temanmu!
- App name: Kedugram
- Account line: Panitia Lomba Kaghati Anak (verified tick) akun resmi — date at the right: 20 Juli
- Account name: @panitia.kaghatianak
- Post text: Lomba Kaghati Anak diadakan hari Minggu, 9 Agustus, pukul 14.00. Kaghati adalah layang-layang tradisional dari Muna, Sulawesi Tenggara. Layarnya dari daun kolope, yaitu daun ubi hutan. Rangkanya dari bambu, dan talinya dari serat nanas hutan. Karena itu, layang-layang plastik tidak ikut dinilai.
- Box heading: Niko, Lala, dan Rendi membaca kedua kabar itu.
- Niko: "Pesan di grup itu bisa dipercaya. Ada tulisan PENTING dan dua tanda seru."
- Lala: "Pesan di grup tidak menyebut siapa pembuatnya. Isinya perlu dicek dulu."
- Rendi: "Satu pesan di grup ternyata keliru. Berarti semua pesan di grup pasti bohong."

No other text, no colour, no new objects. Deliver at exactly 1024 × 1536 pixels as WebP, named instr.tes-mb-A-M4.webp.
```

### R-11 · `instr.tes-bd-B-G2` (unggahan video Tari Maengket), menunggu persetujuan pengembang

Pengembang menyetujui baris ini pada 16 November 2026 (judul bagian ini tetap, karena dirujuk dari `TASKS.md`). Hasil `R-08` sudah terpasang di `ai-studio/game-images/mockup/gambar-mockup/instrumen/aset/instr.tes-bd-A-G2.webp`; lampirkan berkas itu sebagai contoh ukuran dan letak tulisan sumber video.

| | |
|---|---|
| Kode berkas | simpan sebagai `instr.tes-bd-B-G2.webp` |
| Kanvas | 1536 × 1024 |
| Lampiran | `instr.tes-bd-B-G2.webp` yang sekarang; hasil `R-08` yang sudah diterima (supaya ukuran tulisan kreditnya sama) |
| Butir terkait | `tes-bd-B05`: jawabannya dibaca dari tulisan "Video: Sanggar Tujuh Pelita" |

```text
Edit the attached picture "instr.tes-bd-B-G2". Keep everything exactly as it is: the black-and-white line-art style, the post, the video frame with the dancers, the festival information board on the right, the layout, the fonts and every word. Change ONE thing only: the credit on the video frame, "Video: Sanggar Tujuh Pelita", must be larger: white letters on a dark strip at the bottom left of the video frame, with letters as tall as the letters of the comments below, the same size as the credit in the attached form A picture. Do not make it bold, and do not make it larger than the caption.

All text in the finished picture, to be checked word by word:
- App name: Kedugram
- Account line: @tontonan.dina · 2 hari
- Credit on the video: Video: Sanggar Tujuh Pelita
- Likes: 9.100 suka (with a heart)
- Caption: Tari Maengket dari Sulawesi Utara, tarian untuk menyambut raja (crown emoji) Video rekamanku sendiri, lho!  ("Tari Maengket" in bold)
- Comment 1: Jihan — Oh, tari menyambut raja, ya. Aku baru tahu! (followed by a heart-eyes emoji)
- Comment 2: Bonar — Ngawur, tuh. Makanya baca papannya dulu, jangan asal tulis! (followed by a rolling-eyes emoji)
- Comment 3: Ratih — Yang suka ribuan, pasti benar dong (followed by a thumbs-up emoji)
- Buttons: Suka / Komentar / Bagikan / Unduh (all four alike)
- Board heading: Papan Informasi Festival
- Board text: Tari Maengket berasal dari Minahasa, Sulawesi Utara. Para penari menari sambil bernyanyi, dipimpin seorang pemimpin lagu. Awalnya, tari ini adalah ungkapan syukur atas hasil panen padi.

No other text, no colour, no new objects. Deliver at exactly 1536 × 1024 pixels as WebP, named instr.tes-bd-B-G2.webp.
```

## Daftar periksa hasil

**Gambar isi game (`R-01` sampai `R-05`, `R-12` sampai `R-16`)**

- Tanpa tulisan, angka, atau logo, kecuali `R-14`.
- `R-01`: tepat dua gunung, tanpa candi, telaga, atau laut; tanpa tembakau; sepertiga bawah tenang.
- `R-02`: terbaca sebagai beberapa pepaya kecil, bukan belimbing dan bukan satu pepaya besar; latar transparan; tanpa cahaya atau garis tepi yang menandai jawaban.
- `R-12`: tepat tiga arca dari batu abu-abu kecokelatan; Buddha duduk dengan kedua kaki di alas teratai, tangan di depan dada; kedua arca pengapit duduk; tanpa singa, sesaji, pagar, dan orang. Cocokkan dengan kedua foto acuan.
- `R-13`: ikan kecil ramping sebesar jari, bukan ikan laut; seperempat bawah dari kiri hanya permukaan meja; tanpa tulisan, tombol putar, dan tampilan media sosial.
- `R-14`: setiap tulisan sama persis dengan daftar *All text*, tanpa tulisan lain; utara di atas; Dieng Kulon, Candi Arjuna, dan Kawah Sikidang di Banjarnegara, Dieng Wetan, Telaga Warna, dan Sikunir di Wonosobo; cocokkan letaknya dengan peta jalur wisata `-08`.
- `R-15`: belahan buah berdaging tipis krem, isi putih dengan banyak biji kecil cokelat tua; buah utuh kecil bergerigi lima dan berujung lancip; gaya 3D, bukan lukisan.
- `R-16`: tanpa menara candi tinggi; teras dengan talud batu polos, jalan batu, satu candi kecil beratap bertingkat, alas dengan yoni, Gunung Sindoro satu kerucut; tanpa orang.
- `R-03` sampai `R-05`: tokohnya Jaka (syal merah, kemeja krem, ransel, tanpa ikat kepala); ketiganya berlatar biru tua yang sama; medali simbol bergaya emas berukir.

**Gambar tes (`R-06` sampai `R-11`)**

- Setiap kata sama dengan daftar *All text* di promptnya. Baca seluruhnya, bukan hanya bagian yang diubah.
- Hanya bagian yang disebut prompt yang berubah. Bandingkan dengan gambar lama.
- Tidak ada tombol, tulisan, atau gambar yang lebih menonjol daripada bagian sejenisnya.
- Tetap hitam-putih.

## Pemasangan

- `R-01` sampai `R-05`, `R-12`, `R-14` sampai `R-16`: berkas dari `ai-studio/chatgpt-images/out/` **dipindahkan** (bukan disalin) ke tempat berkas lamanya di `ai-studio/game-images/assets/` dan menggantikannya; entri `data.json` tidak berubah. Lalu `php spark gelita:katalog:impor`. `R-13` adalah gambar baru: berkasnya dipindahkan ke `ai-studio/game-images/assets/tantangan/challenge.post.tmg-4-b.webp`, lalu entri baru `challenge.post.tmg-4-b` ditambahkan ke `data.json` (contoh: entri `challenge.item.tmg-1-h01`) dan satu baris ke `REVIEW.md`. `R-16`: entri `library.temanggung.p5.1` di `data.json` mendapat `caption` "Situs Liyangan di lereng Gunung Sindoro" / "The Liyangan Site on the slopes of Mount Sindoro". `R-14`: entri `library.wonosobo.p2.2` di `data.json` mendapat `caption` baru "Peta kawasan Dataran Tinggi Dieng" / "Map of the Dieng Plateau area".
- `R-06` sampai `R-11`: berkas dipindahkan ke `ai-studio/game-images/mockup/gambar-mockup/instrumen/aset/`. Berkas PNG induk di `docs/revisi-10-analitik/gambar/doc-34/` diganti dengan hasil yang sama, supaya induk dan gambar terpasang tidak berbeda.
- Setelah dipasang, lembar telaah dibangun ulang (`node ai-studio/game-images/review/build-sheets.mjs`) dan baris `REVIEW.md` diberi tanda baru oleh pengembang.

## Kebijakan perubahan

Teks butir dan stimulus di `tes.php` tidak diubah oleh dokumen ini; hanya gambarnya yang disesuaikan dengan teks. Kalau teks stimulus berubah kelak, daftar *All text* di prompt terkait ikut diubah pada hari yang sama.
