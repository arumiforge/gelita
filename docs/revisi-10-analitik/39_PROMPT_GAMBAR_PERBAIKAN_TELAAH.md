# Prompt Gambar Perbaikan dari Telaah Gambar

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 15 November 2026, `R-12` sampai `R-33` ditambahkan 16 November 2026 · Lampiran `ai-studio/game-images/REVIEW.md` (telaah gambar oleh pengembang) · Status: `draf pengembang`, `asal: pengembang` (disusun AI atas permintaan pengembang)

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
| `R-17` | Benda Cari Objek `challenge.item.wnb-4-a01` (bundengan) | 270 | Tudung tergeletak telentang seperti kereta luncur, senar di atas; aslinya tegak, meruncing, senar di dalam, bertongkat bambu panjang. Foto dari peneliti |
| `R-18` | Susun Gambar Wonosobo `challenge.item.wnb-1-g01` (bundengan) | 242 | Tanpa tongkat panjang, jumbai ijuk, dan kisi lapisan luar; senar di luar seperti harpa. Juga foto unggahan Periksa Unggahan Wonosobo babak 2 variasi a. Foto dari peneliti |
| `R-19` | Avatar 10 `avatar.10` (bundengan) | 115 | Lambangnya tudung telentang seperti kereta luncur; hanya lambang yang diganti, cincin tetap. Foto dari peneliti |
| `R-20` | Benda Cari Objek `challenge.item.tmg-4-03` (kuda kepang, target) | 263 | Sudah pipih dan tampak samping, tetapi berpelana kain berumbai dan berperut seperti kuda goyang; dibuat ulang bersama `R-21` supaya serupa. Foto dari peneliti |
| `R-21` | Benda Cari Objek `challenge.item.mgl-4-a05` (kuda kepang, pengecoh) | 256 | Kepala kuda bulat bertongkat seperti mainan gaya Barat; kuda kepang berupa anyaman bambu pipih berbentuk kuda utuh, dicat, berbingkai rotan, tanpa tongkat. Foto dari peneliti |
| `R-22` | Benda Cari Objek `challenge.item.mgl-4-a04` (kendil gerabah) | 255 | Teko tanah liat bercerat dan bergagang; bank soal dan cek fakta menyebut kendil, periuk bulat bermulut lebar bertutup, tanpa cerat dan tanpa gagang. Foto kendil dari pengembang |
| `R-23` | Gambar Benar atau Salah Temanggung `challenge.item.tmg-3-b04` (arca Nandi di bilik Candi Pringapus) | — | Butir belum punya gambar (tampil gambar contoh); gambar lama `tmg-3-b03` arca Buddha dan `tmg-3-c03` kopi (247, 248) tidak dipakai butir mana pun dan dibuang. Foto dari peneliti |
| `R-24` | Benda Cari Objek `challenge.item.wnb-4-a07` (mi ongklok, pengecoh) | 276 | Satu-satunya benda berlatar persegi; dibuat ulang sebagai potongan tanpa latar |
| `R-25` | Tombol bahasa Inggris `public/assets/ui/round-lang-en.webp` | 62 | Bendera Inggris (salib merah); diganti Union Jack, bingkai tetap. Ikon 126 dan 127 dibuang |
| `R-26` | Pelat instruksi Isi Rumpang `public/assets/ui/rumpang-instruction-plate.webp` (baru) | — | Pelat `kenal-plate-navy` teregang di pita 66 dp; pelat baru berhias hanya di kedua ujung |
| `R-27` | Slide 1 cerita pembuka `bg.cerita-intro-01` | 190 | Dataran tinggi khayalan bertebing dan berair terjun; naskahnya dataran luas yang dikelilingi Sindoro, Sumbing, Merapi, Merbabu, dan Menoreh. Foto dari peneliti |
| `R-28` | Slide 6 cerita pembuka `bg.cerita-intro-06` | 195 | Lentera besar di depan dan gunung yang tidak dikenali; Jaka kini tampil sebagai avatar, latar menjadi jalan berangkat dari rumah penjaga. Foto dari peneliti |
| `R-29` | Slide 7 cerita pembuka `bg.cerita-intro-07` | 196 | Gambarnya isi slide 8 lama (papan, relief, kentongan); naskahnya lima serpihan di setiap wilayah. Foto dari peneliti |
| `R-30` | Slide 8 cerita pembuka `bg.cerita-intro-08` | 197 | Matahari terbit dengan jalan setapak; naskahnya tiga kunci untuk tiga wilayah. Foto dari peneliti |
| `R-31` | Avatar Jaka bertekad `ui.jaka-avatar.determined` (baru) | — | Tokoh di slide cerita kini avatar bundar; ekspresi untuk `intro-06` |
| `R-32` | Avatar Jaka berterima kasih `ui.jaka-avatar.bow` (baru) | — | Ekspresi untuk cerita penutup |
| `R-33` | Avatar Mbah Kedu lemah `ui.kedu-avatar.weak` (baru) | — | Ekspresi untuk `intro-05` |

## Aturan bersama

**Gaya yang berlaku (pengembang, 15 November 2026).** Seluruh antarmuka game memakai satu gaya, yaitu *emas berukir* yang sudah disetujui peneliti: bingkai emas tebal berukir dalam dengan hiasan ukel, bidang batu gelap atau biru tua berpola awan samar, simbol timbul dari emas yang sama, satu arah cahaya dari kiri atas, dirender sebagai benda 3D. Contohnya tombol bundar `public/assets/ui/round-*.webp` dan pelat `plate-gold-*.webp`. Hijau dan merah hanya menjadi warna bidang untuk benar dan salah, selalu bersama tanda centang atau silang. Pengembang juga meminta hiasan tepi bagian-bagian itu diseragamkan lagi; pesanan gambar antarmuka menyusul setelah ukuran setiap bagian diukur di layar Chromebook (`docs/PROGRESS.md` → *Asset catalogue audit and picture review*).

Gambar di dokumen ini bukan bagian antarmuka. Gayanya mengikuti kelompoknya masing-masing:

- **Gambar isi game** (`R-01` sampai `R-05`, `R-12` sampai `R-18`, `R-20` sampai `R-24`, `R-27` sampai `R-30`): blok `GELITA ACTIVE VISUAL DIRECTION — 2026-10-11` dari katalog, kata per kata, seperti di 37. Tanpa sapuan kuas dan tanpa tekstur kertas.
- **Avatar tokoh** (`R-31` sampai `R-33`): disunting dari avatar Jaka dan Mbah Kedu yang ada dengan masker di tokohnya saja; bingkai emas dan latar emasnya tetap, supaya hanya ekspresinya yang berganti di antara slide.
- **Avatar** (`R-19`): disunting, bukan digambar dari nol, seperti gambar tes: cincin medali dan cakramnya tetap, hanya lambang di tengah yang diganti, supaya kesepuluh avatar tetap satu keluarga sampai perombakan antarmuka mengganti cincin semuanya.
- **Gambar tes** (`R-06` sampai `R-11`): gambar garis hitam-putih seperti 23 gambar tes lainnya. Gaya ini disengaja: instrumen tes tampil netral dan tidak memakai gaya game.
- **Bagian antarmuka** (`R-25`, `R-26`): gaya *emas berukir* di atas. `R-25` disunting dari tombol yang ada, seperti gambar tes, supaya bingkainya sama dengan pasangannya.

**Tulisan di gambar.** Gambar isi game tidak memuat tulisan, kecuali peta `R-14` (nama tempat, daftar tetap). Gambar tes justru memuat tulisan, dan setiap kata harus sama persis dengan `app/Database/Seeds/data/instrumen/tes.php`. Tulisan di gambar dibuat oleh model gambar, jadi setiap hasil dibaca ulang kata per kata dengan daftar di promptnya.

**Gambar tes diperbaiki, bukan digambar dari nol.** Berbeda dari aturan `redo` di `ai-studio/chatgpt-images/AGENTS.md`: gambar tes yang ada dilampirkan dan hanya bagian yang salah yang diubah. Alasannya, semua tulisan lainnya sudah dicocokkan dengan `tes.php` pada 15 November 2026, dan buklet A dan B harus tetap serupa. Kalau hasil suntingan mengubah bagian lain, ulangi dari gambar lama, jangan dari hasil yang gagal.

**Paling banyak lima lampiran** (pengembang, 16 November 2026). ChatGPT menolak lebih dari lima berkas gambar dalam satu percakapan, jadi kolom *Lampiran* setiap baris memuat paling banyak lima berkas; "style" dihitung satu. Kalau rujukan lebih banyak, yang dipilih: foto benda yang sebenarnya lebih dulu, lalu gambar yang harus berbeda, lalu gambar untuk kesamaan hasil akhir.

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

Ditambahkan 16 November 2026. Pengembang menandai gambar ini `redo later` (telaah no. 312): gambarnya menampilkan menara candi tinggi seperti Prambanan, padahal halaman "Jejak Mataram Kuno" bercerita tentang **Situs Liyangan**, kampung Mataram Kuno yang terkubur material letusan Gunung Sindoro dan digali sejak 2008. Peneliti mengirim lima foto Liyangan yang berizin (`ai-studio/chatgpt-images/inbox/reference-Liyangan-*`, `-01` sampai `-05`). Isinya: talud batu panjang di teras berundak dengan jalan batu di depannya, alas candi kecil dengan yoni, satu bangunan candi kecil yang atapnya bertingkat di atas pelataran berpagar batu, dan Gunung Sindoro di belakang situs.

| | |
|---|---|
| Kode berkas | `library.temanggung.p5.1` → simpan sebagai `library.temanggung.p5.1.webp` |
| Kanvas | 960 × 640 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Liyangan-terrace-wall-and-road-02.jpg` (talud dan jalan batu), `ai-studio/chatgpt-images/inbox/reference-Liyangan-small-shrine-04.jpg` (candi kecil), `ai-studio/chatgpt-images/inbox/reference-Liyangan-sign-and-Sindoro-05.jpg` (Gunung Sindoro; papan namanya tidak digambar) |
| Halaman Pustaka | "Jejak Mataram Kuno", bagian *Situs Liyangan, kampung yang terkubur*: sisa rumah kayu yang hangus, candi dan talud, jalan batu, butir padi terbakar; semuanya terkubur material letusan Gunung Sindoro. Keterangan gambar setelah dipasang: "Situs Liyangan di lereng Gunung Sindoro" (keterangan asli halaman) |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: library.temanggung.p5.1  (R-16 · the Liyangan site, library picture)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all. The attached photos are factual references for the site only: follow the forms of the walls, the stone road, the small shrine and the mountain, render them in the GELITA finish, and do not copy a photo. Leave out everything modern in them: visitors, the sign and its letters, roofs, fences and tools.

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

### R-17 · Bundengan (benda Cari Objek Wonosobo)

Ditambahkan 16 November 2026. Telaah no. 270: tudung anyaman tergeletak telentang seperti kereta luncur, senarnya di atas. Bundengan yang asli berdiri tegak, meruncing di atas, senarnya di dalam cekungan, dengan satu tongkat bambu panjang di tepinya. Benda ini **target** Cari Objek, jadi bentuknya penentu: siswa mencarinya dari petunjuk "alat musik berbentuk tudung lengkung dari anyaman bambu". Peneliti mengirim lima foto bundengan yang berizin (`ai-studio/chatgpt-images/inbox/reference-bundengan-researcher-*`).

| | |
|---|---|
| Kode berkas | `challenge.item.wnb-4-a01` → simpan sebagai `challenge.item.wnb-4-a01.webp` |
| Kanvas | 240 × 240, latar transparan (buat pada 1024 × 1024, lalu perkecil) |
| Lampiran | Foto `ai-studio/chatgpt-images/inbox/reference-bundengan-researcher-player-indoors-01.webp` (bentuk, tongkat, senar di dalam), `ai-studio/chatgpt-images/inbox/reference-bundengan-researcher-players-on-stage-04.webp` (lapisan luar slumpring bergaris ijuk), `ai-studio/chatgpt-images/inbox/reference-bundengan-researcher-kowangan-against-wall-03.jpg` (anyaman dari sisi dalam). Untuk hasil akhir yang sama: `ai-studio/game-images/assets/tantangan/challenge.item.wnb-4-a08.webp` (angklung) dan `ai-studio/game-images/assets/tantangan/challenge.item.wnb-4-a09.webp` (sasando), keduanya pengecoh yang harus jelas berbeda |
| Butir | `wnb-4-x01`: "Temukan alat musik berbentuk tudung lengkung dari anyaman bambu. Dulu tudung itu melindungi penggembala bebek dari panas dan hujan, lalu diberi senar untuk dipetik." Pengecoh sesama alat musik: angklung (`a08`) dan sasando (`a09`) |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-4-a01  (R-17 · bundengan, hidden-object cut-out)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Match the render finish, lighting and outline quality of the attached angklung and sasando cut-outs exactly, so that the three instruments look like parts of one set — and make sure the bundengan cannot be mistaken for either of them.
The attached photos are factual references for the instrument only: follow its forms and materials, render them in the GELITA finish, and do not copy a photo. Leave out the people, the microphone, the stage and the walls.

SUBJECT AND COMPOSITION
One isolated object on a transparent background: a bundengan standing on its own, seen in three-quarter view from the front-left so that both the cream outer back and the open hollow with its strings are visible.
THE BUNDENGAN, exactly as in the attached photos — it is built from a kowangan, the hood duck herders once carried against sun and rain:
- An UPRIGHT hollow shell, taller than the seated player's head, shaped like a tall shield or half a boat standing on its end: broad and rounded at the bottom, narrowing to a POINT at the top, curving forward so it is hollow like a cupped hand.
- The shell is a frame of flat woven bamboo strips in a loose square weave, honey-brown. Its OUTSIDE (back) is covered with pale cream sheets of bamboo sheath (slumpring), held by dark brown palm-fibre (ijuk) cords that make a large square grid; a short tuft of black ijuk sticks out at the pointed top.
- ONE LONG straight bamboo pole, longer than the shell, runs along one edge from the floor and sticks out past the top.
- INSIDE the hollow: a few thin strings stretched from side to side, and some thin flat bamboo strips with small clips; the player plucks them with the fingers of both hands.
- It always stands on its rounded bottom edge, open side towards the player; it never lies on its back, has no neck, no tuning pegs, no sound hole, no tubes.

Accuracy — It must NOT read as a guitar, lute, harp, zither on legs, angklung (shaken bamboo tubes), sasando (a palm-leaf tube harp), basket, hat, shield or sled. It stands upright; the pointed top is the highest point of the shell, with the pole tip a little above it.
Hidden-object rule — identical lighting and quality for targets and decoys: no glow, no sparkle, no outline, no label, no arrow, nothing that marks this object as an answer.

DELIVERY
Generate at 1024 × 1024 and scale down proportionally to exactly 240 × 240 pixels; aspect 1:1. The whole instrument, pole included, inside the canvas with a small even margin; it is taller than wide, so it fills the height. Genuine transparent background outside the subject; preserve alpha; no cast shadow on a ground. Deliver WebP, named challenge.item.wnb-4-a01.webp.

CONSTRAINTS
No watermarks, signatures, text, people, hands, extra objects, cropped parts, blurry edges, neon colours, or fake checkerboard transparency. No painterly 2D, gouache or paper grain.
```

### R-18 · Bundengan (gambar Susun Gambar Wonosobo)

Ditambahkan 16 November 2026. Telaah no. 242: bentuknya paling dekat dengan yang asli (cangkang lengkung tegak, dimainkan sambil duduk), tetapi tanpa tongkat bambu panjang, tanpa jumbai ijuk dan garis kisi lapisan luar, dan senarnya di luar seperti harpa. Gambar ini juga **foto unggahan** Periksa Unggahan Wonosobo babak 2 variasi a ("Unggahan video bundengan"; keterangan unggahan sengaja keliru menyebut angklung), jadi alatnya harus jelas terbaca sebagai bundengan. Semua tulisan unggahan diberi oleh halaman, bukan oleh gambar. Tata letak 4 × 4 dari 37 (*WNB-P07*) tetap, hanya pemainnya kini duduk bersila di tikar seperti di foto.

| | |
|---|---|
| Kode berkas | `challenge.item.wnb-1-g01` → simpan sebagai `challenge.item.wnb-1-g01.webp` |
| Kanvas | 1200 × 1200 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-bundengan-researcher-player-indoors-01.webp` (cara duduk dan memetik), `ai-studio/chatgpt-images/inbox/reference-bundengan-researcher-players-on-stage-04.webp` (alat utuh dengan tongkat dan lapisan luar); gambar lama `ai-studio/game-images/assets/tantangan/challenge.item.wnb-1-g01.webp` (suasana dan tata letak) |
| Keterangan | "Bundengan adalah alat musik khas Wonosobo. Dulu benda ini berupa kowangan, tudung anyaman bambu yang melindungi penggembala bebek dari panas dan hujan. Tudung itu kemudian diberi senar dan dipetik; suaranya mirip gamelan." |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-g01  (R-18 · WNB-P07 · Bundengan)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. The attached photos are factual references for the instrument and how it is played: follow them, render in the GELITA finish, and do not copy a photo or its people. Keep the mood and layout of the attached old picture, but replace its instrument completely.

SUBJECT AND COMPOSITION
On the porch of a village house in Wonosobo, an adult musician in a batik shirt and a batik headcloth sits CROSS-LEGGED on a woven mat beside a bundengan, reaching into its hollow and plucking with both hands. Two children sit on the mat a little apart and listen. Beyond the porch, in a harvested rice field, a duck herder carries the same kind of bamboo hood on his back and walks behind a flock of ducks; misty hills rise in the distance.
THE BUNDENGAN, exactly as in the attached photos — it is built from a kowangan, the hood duck herders once carried against sun and rain:
- An UPRIGHT hollow shell, taller than the seated player's head, shaped like a tall shield or half a boat standing on its end: broad and rounded at the bottom, narrowing to a POINT at the top, curving forward so it is hollow like a cupped hand.
- The shell is a frame of flat woven bamboo strips in a loose square weave, honey-brown. Its OUTSIDE (back) is covered with pale cream sheets of bamboo sheath (slumpring), held by dark brown palm-fibre (ijuk) cords that make a large square grid; a short tuft of black ijuk sticks out at the pointed top.
- ONE LONG straight bamboo pole, longer than the shell, runs along one edge from the floor and sticks out past the top.
- INSIDE the hollow: a few thin strings stretched from side to side, and some thin flat bamboo strips with small clips; the player plucks them with the fingers of both hands.
- It always stands on its rounded bottom edge, open side towards the player; it never lies on its back, has no neck, no tuning pegs, no sound hole, no tubes.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) misty hills with thin mist; (2) the duck herder carrying a bamboo hood in the field; (3) a flock of white and brown ducks in the paddy; (4) the sky with a cloud.
• Row 2 — (5) the porch roof beam with hanging corn cobs; (6) the pointed top of the bundengan with its ijuk tuft and the tip of the long pole; (7) the musician's face and batik headcloth; (8) a window with a wooden frame.
• Row 3 — (9) two children sitting and listening; (10) the cream outer back of the bundengan with its dark square cord grid; (11) the open hollow with the strings, the bamboo strips and the musician's plucking hands; (12) a low table with a kettle.
• Row 4 (bottom) — (13) a woven floor mat; (14) the musician's crossed legs and bare feet; (15) the rounded bottom edge of the bundengan and the foot of the long pole on the mat; (16) a potted plant and a cat.

Accuracy — It must NOT read as a guitar, lute, harp, zither on legs, angklung (shaken bamboo tubes), sasando (a palm-leaf tube harp), basket, hat, shield or sled. The strings are INSIDE the hollow, never across the outside. The musician sits on the mat, not on a bench, and the instrument stands on the mat next to him.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Opaque, full-bleed scene. Deliver WebP, named challenge.item.wnb-1-g01.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, extra limbs, blurry edges or neon colours. No painterly 2D, gouache or paper grain.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces sit fully inside one area, away from the grid lines.
```

### R-19 · Avatar bundengan (avatar 10)

Ditambahkan 16 November 2026. Telaah no. 115: lambang di tengah avatar adalah tudung yang tergeletak telentang seperti kereta luncur, sama dengan benda Cari Objek lama. Yang diganti **hanya lambangnya**; cincin medali biru tua tetap seperti sembilan avatar lain, supaya kesepuluhnya tetap satu keluarga. Cincin semua avatar akan diganti bersama dalam perombakan antarmuka (pengembang, 16 November 2026: tepi bundar mengikuti `public/assets/ui/round-back.webp`), bukan di baris ini.

| | |
|---|---|
| Kode berkas | `avatar.10` → simpan sebagai `avatar.10.webp` |
| Kanvas | 512 × 512, latar transparan |
| Lampiran | Gambar yang diubah `ai-studio/game-images/assets/ikon/avatar.10.webp`; avatar lain untuk hasil akhir yang sama `ai-studio/game-images/assets/ikon/avatar.1.webp` dan `ai-studio/game-images/assets/ikon/avatar.2.webp`; foto `ai-studio/chatgpt-images/inbox/reference-bundengan-researcher-player-indoors-01.webp` dan `ai-studio/chatgpt-images/inbox/reference-bundengan-researcher-players-on-stage-04.webp` (bentuk alat) |

```text
EDIT the attached avatar.10.webp. Keep EVERYTHING outside the central emblem exactly as it is: the dark-navy circular medallion rim, its thin brass edging, the four small gold diamond points, the cream parchment disc, the size, position, lighting and the transparent exterior. Compare with the attached avatar.1 and avatar.2 so the result stays in the same family.
Use the image editor with a MASK over the central emblem only; the medallion rim, the diamond points and the cream disc stay outside the mask, locked. Do not regenerate the whole picture.

Replace ONLY the gold emblem in the middle with a new one: a bundengan from Wonosobo, as one large sculptural GOLD emblem, in the same raised gold finish as the emblems of the other avatars, readable at 48 pixels.
The bundengan, as in the attached photos: an UPRIGHT hollow shell shaped like a tall shield standing on its end, broad and rounded at the bottom, narrowing to a POINT at the top, curving forward so it is hollow; a loose square weave of flat bamboo strips; on its back a few cords forming a large square grid and a small tuft at the pointed top; ONE long straight pole along one edge, sticking out past the top; inside the hollow a few strings stretched from side to side. Seen in three-quarter view from the front-left so that both the back and the open hollow show. All of it in gold relief, no other colours added.

It must NOT read as a guitar, lute, harp, angklung, sasando, basket, hat, shield or sled; it never lies on its back. No person, no hands, no face, no lettering, no watermark.
Delivery: 512 × 512 WebP; preserve the transparent margins; whole medallion visible with padding, exactly as in the original.
```

### R-20 · Kuda kepang (benda Cari Objek Temanggung)

Ditambahkan 16 November 2026. Telaah no. 263. Benda ini **target** Cari Objek Temanggung babak 1: "Temukan kuda-kudaan dari anyaman bambu yang dipakai untuk menari kuda lumping." Gambar lama sudah mendekati (pipih, tampak samping, dicat), tetapi berpelana kain dengan rumbai emas dan berperut seperti kuda goyang. Pengembang memutuskan membuatnya ulang bersama `R-21` supaya kedua kuda kepang serupa. Peneliti mengirim lima foto kuda lumping yang berizin (`ai-studio/chatgpt-images/inbox/reference-Kuda-Lumping-researcher-*`): kuda kepang adalah anyaman bambu **pipih** berbentuk kuda utuh dari samping, dicat, berbingkai rotan, dan dijepit di antara kaki penari tanpa tongkat.

| | |
|---|---|
| Kode berkas | `challenge.item.tmg-4-03` → simpan sebagai `challenge.item.tmg-4-03.webp` |
| Kanvas | 240 × 240, latar transparan (buat pada 1024 × 1024, lalu perkecil) |
| Lampiran | foto `ai-studio/chatgpt-images/inbox/reference-Kuda-Lumping-researcher-craftspeople-painting-01.jpg` (kuda kepang pipih di dinding dan di meja perajin), `ai-studio/chatgpt-images/inbox/reference-Kuda-Lumping-researcher-kuda-kepang-close-watermarked-04.jpg` (anyaman, bingkai rotan, cat; tanda nama dan nomor teleponnya tidak boleh ikut), `ai-studio/chatgpt-images/inbox/reference-Kuda-Lumping-researcher-women-white-horse-14.jpg` (kuda kepang putih bercat merah-kuning, paling dekat dengan cat `R-20`). Untuk hasil akhir yang sama: `ai-studio/game-images/assets/tantangan/challenge.item.tmg-4-09.webp` (angklung) dan `ai-studio/game-images/assets/tantangan/challenge.item.tmg-4-12.webp` (sasando), pengecoh adegan yang sama |
| Butir | `tmg-4-x01`: "Temukan kuda-kudaan dari anyaman bambu yang dipakai untuk menari kuda lumping." Pengecoh: angklung, ondel-ondel, perahu pinisi, sasando |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-4-03  (R-20 · kuda kepang, hidden-object cut-out)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Match the render finish, lighting and outline quality of the attached angklung and sasando cut-outs exactly, so that the objects of this scene look like parts of one set.
The attached photos are factual references for the prop only: follow its form, weave and painting, render them in the GELITA finish, and do not copy a photo. Leave out the people, the paint tins, the walls, any name, logo, watermark or phone number in the photos.

SUBJECT AND COMPOSITION
One isolated object on a transparent background: one kuda kepang (jaran kepang), the dance horse of kuda lumping, standing upright on its lower edge and seen exactly from the side, so its flat face is towards the viewer.
THE KUDA KEPANG, exactly as in the attached photos:
- A FLAT cut-out of tightly WOVEN BAMBOO, about as thick as a finger, shaped like the side view of a WHOLE horse: the head with a pricked ear and an open mouth at the front, a tall neck curving up and forward like the letter S, and behind it a long, low body that ends in a rounded rump. No legs stand out from the outline, and there is no stick, no wheel and no rocker.
- The whole outline is edged with a rim of orange-brown rattan, bound at intervals with thin cord.
- The woven surface is painted white, with a black and red mane pattern along the neck, a red mouth, a large round eye ringed in black, and red and gold scroll ornaments on the body. The bamboo weave still shows through the paint.
- A short fringe of black horsehair hangs from the top of the neck as a mane, and a black horsehair tail hangs from the rump.

Accuracy — It must NOT read as a hobby horse (a round head on a stick), a rocking horse, a carousel horse, a toy on wheels, a horse statue or a real horse. It is flat like a board, not round. No rider, no saddle cloth, no tassels hanging under the belly.
Hidden-object rule — identical lighting and quality for targets and decoys: no glow, no sparkle, no outline, no label, no arrow, nothing that marks this object as an answer.

DELIVERY
Generate at 1024 × 1024 and scale down proportionally to exactly 240 × 240 pixels; aspect 1:1. The whole horse inside the canvas with a small even margin; it is wider than tall, so it fills the width. Genuine transparent background outside the subject; preserve alpha; no cast shadow on a ground. Deliver WebP, named challenge.item.tmg-4-03.webp.

CONSTRAINTS
No watermarks, signatures, text, numbers, logos, people, hands, extra objects, cropped parts, blurry edges, neon colours, or fake checkerboard transparency. No painterly 2D, gouache or paper grain.
```

### R-21 · Kuda kepang (benda Cari Objek Magelang, pengecoh)

Ditambahkan 16 November 2026. Telaah no. 256: kepala kuda bulat bertongkat, seperti mainan kuda-kudaan gaya Barat, bukan kuda kepang. Di Cari Objek Magelang babak 1 benda ini **pengecoh**, bukan target, tetapi tetap harus benar bentuknya. Bentuknya sama dengan `R-20`; catnya berbeda (kuda hitam berhias emas, seperti foto `-04`) supaya dua gambar tidak kembar. Jangan sampai mirip dadak merak Reog, pengecoh lain di adegan yang sama.

| | |
|---|---|
| Kode berkas | `challenge.item.mgl-4-a05` → simpan sebagai `challenge.item.mgl-4-a05.webp` |
| Kanvas | 240 × 240, latar transparan (buat pada 1024 × 1024, lalu perkecil) |
| Lampiran | foto `ai-studio/chatgpt-images/inbox/reference-Kuda-Lumping-researcher-craftspeople-painting-01.jpg` (kuda kepang pipih di dinding dan di meja perajin), `ai-studio/chatgpt-images/inbox/reference-Kuda-Lumping-researcher-kuda-kepang-close-watermarked-04.jpg` (anyaman, bingkai rotan, cat; tanda nama dan nomor teleponnya tidak boleh ikut), `ai-studio/chatgpt-images/inbox/reference-Kuda-Lumping-researcher-women-black-gold-horses-watermarked-10.jpg` (deretan kuda kepang hitam-emas bersurai rambut, paling dekat dengan cat `R-21`; tanda air fotografernya tidak boleh ikut). Untuk hasil akhir yang sama: `ai-studio/game-images/assets/tantangan/challenge.item.mgl-4-a07.webp` (dadak merak Reog, harus jelas berbeda) dan `ai-studio/game-images/assets/tantangan/challenge.item.mgl-4-a08.webp` (angklung) |
| Butir | Pengecoh di Cari Objek Magelang babak 1 (`mgl-4-x01` sampai `x04`; kuncinya stupa berlubang, kuluk Topeng Ireng, getuk, kendil gerabah) |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-4-a05  (R-21 · kuda kepang, hidden-object cut-out)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Match the render finish, lighting and outline quality of the attached dadak merak (Reog peacock mask) and angklung cut-outs exactly, so that the objects of this scene look like parts of one set.
The attached photos are factual references for the prop only: follow its form, weave and painting, render them in the GELITA finish, and do not copy a photo. Leave out the people, the paint tins, the walls, any name, logo, watermark or phone number in the photos.

SUBJECT AND COMPOSITION
One isolated object on a transparent background: one kuda kepang (jaran kepang), the dance horse of kuda lumping, standing upright on its lower edge and seen exactly from the side, so its flat face is towards the viewer.
THE KUDA KEPANG, exactly as in the attached photos:
- A FLAT cut-out of tightly WOVEN BAMBOO, about as thick as a finger, shaped like the side view of a WHOLE horse: the head with a pricked ear and an open mouth at the front, a tall neck curving up and forward like the letter S, and behind it a long, low body that ends in a rounded rump. No legs stand out from the outline, and there is no stick, no wheel and no rocker.
- The whole outline is edged with a rim of orange-brown rattan, bound at intervals with thin cord.
- The woven surface is painted glossy black, with gold ornaments along the neck and on the body, a gold bridle with a small star-shaped rosette on the cheek, a red mouth, and a large eye ringed in gold. The bamboo weave still shows through the paint.
- A short fringe of black horsehair hangs from the top of the neck as a mane, and a black horsehair tail hangs from the rump.

Accuracy — It must NOT read as a hobby horse (a round head on a stick), a rocking horse, a carousel horse, a toy on wheels, a horse statue or a real horse. It is flat like a board, not round. No rider, no saddle cloth, no tassels hanging under the belly.
Hidden-object rule — identical lighting and quality for targets and decoys: no glow, no sparkle, no outline, no label, no arrow, nothing that marks this object as an answer.

DELIVERY
Generate at 1024 × 1024 and scale down proportionally to exactly 240 × 240 pixels; aspect 1:1. The whole horse inside the canvas with a small even margin; it is wider than tall, so it fills the width. Genuine transparent background outside the subject; preserve alpha; no cast shadow on a ground. Deliver WebP, named challenge.item.mgl-4-a05.webp.

CONSTRAINTS
No watermarks, signatures, text, numbers, logos, people, hands, extra objects, cropped parts, blurry edges, neon colours, or fake checkerboard transparency. No painterly 2D, gouache or paper grain.
```

### R-22 · Kendil gerabah (benda Cari Objek Magelang)

Ditambahkan 16 November 2026. Telaah no. 255: gambarnya teko tanah liat bercerat **dan** bergagang, bukan kendil dan bukan kendi. Benda ini **target** Cari Objek Magelang babak 1: "Temukan benda dari tanah liat yang dibentuk lalu dibakar, buatan warga Dusun Klipoh." Bank soal dan cek fakta (25, MGL-39 dan MGL-40) menyebutnya **kendil**, periuk tanah liat untuk menanak nasi; kata itu juga dipakai kartu informasi Klipoh dan butir `mgl-2-c06`. Pengembang memutuskan gambarnya mengikuti bank: kendil, bukan kendi (16 November 2026). Peneliti mengirim tiga foto kendi; ketiganya tidak dilampirkan (bentuknya kendi, bukan kendil, dan satu tampak buatan AI), supaya lampiran tidak lebih dari lima berkas. Pengembang lalu mengirim dua foto kendil (16 November 2026): periuk bulat cokelat kemerahan tua dengan **tutup tanah liat bertombol**, salah satunya dengan dua kuping kecil di sisi. Prompt mengikuti foto itu, dan keduanya dilampirkan.

| | |
|---|---|
| Kode berkas | `challenge.item.mgl-4-a04` → simpan sebagai `challenge.item.mgl-4-a04.webp` |
| Kanvas | 240 × 240, latar transparan (buat pada 1024 × 1024, lalu perkecil) |
| Lampiran | Foto kendil `ai-studio/chatgpt-images/inbox/reference-kendil-researcher-two-lidded-with-lugs-01.jpg` (bentuk, tutup bertombol, warna) dan `ai-studio/chatgpt-images/inbox/reference-kendil-researcher-lidded-workshop-02.jpg` (perut bulat, bibir, tutup); `ai-studio/game-images/assets/tantangan/challenge.item.mgl-1-h01.webp` (Susun Gambar gerabah Klipoh: periuk bulat di keranjang); untuk hasil akhir yang sama `ai-studio/game-images/assets/tantangan/challenge.item.mgl-4-a10.webp` (panci logam, pengecoh yang harus jelas berbeda) dan `ai-studio/game-images/assets/tantangan/challenge.item.mgl-4-a08.webp` (angklung) |
| Butir | `mgl-4-x04`: "Temukan benda dari tanah liat yang dibentuk lalu dibakar, buatan warga Dusun Klipoh." Pengecoh: kuda kepang bambu, manisan carica, dadak merak Reog, angklung, bakpia, panci logam |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-4-a04  (R-22 · kendil, clay cooking pot, hidden-object cut-out)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Match the render finish, lighting and outline quality of the attached metal pot and angklung cut-outs exactly, so that the objects of this scene look like parts of one set, and make sure the kendil can never be mistaken for the metal pot.
The two attached kendil photos are the factual reference for the form, the lid and the colour: follow them, render them in the GELITA finish, and do not copy a photo or its background. The round pots in the basket of the attached puzzle picture are the same kind of kendil.

SUBJECT AND COMPOSITION
One isolated object on a transparent background: one kendil, the traditional Javanese earthenware cooking pot for steaming rice, made by the potters of Klipoh near Borobudur, seen from the front and a little above.
THE KENDIL:
- A ROUND, full belly, wider than it is tall, on a small round base.
- A short neck rising from the shoulder to a WIDE round mouth with a thick, slightly flared rim.
- Unglazed fired clay, deep reddish brown, softly burnished but never shiny like metal, with darker smoky patches from the open firing; a few fine wheel lines on the shoulder; nothing painted.
- A matching domed clay LID sits on the mouth, with a small round knob on top, the same clay and colour as the pot.
- NO spout, NO long handle, NO feet. No metal anywhere.

Accuracy — It must NOT read as a kendi or teapot (no spout, no tall narrow neck, no long handle), a vase, or a metal pot: unlike the attached metal pot it is round-bellied, made of matte brown clay, with no shine, no metal handles and no straight sides.
Hidden-object rule — identical lighting and quality for targets and decoys: no glow, no sparkle, no outline, no label, no arrow, nothing that marks this object as an answer.

DELIVERY
Generate at 1024 × 1024 and scale down proportionally to exactly 240 × 240 pixels; aspect 1:1. The whole pot inside the canvas with a small even margin. Genuine transparent background outside the subject; preserve alpha; no cast shadow on a ground. Deliver WebP, named challenge.item.mgl-4-a04.webp.

CONSTRAINTS
No watermarks, signatures, text, numbers, logos, people, hands, food, fire, extra objects, cropped parts, blurry edges, neon colours, or fake checkerboard transparency. No painterly 2D, gouache or paper grain.
```

### R-23 · Arca Nandi di bilik Candi Pringapus (gambar Benar atau Salah Temanggung)

Ditambahkan 16 November 2026. Butir `tmg-3-b04` belum punya gambar sama sekali dan selama ini menampilkan gambar contoh. Bunyinya: "Gambar ini menunjukkan arca yang ada di dalam ruang Candi Pringapus." (kunci: **benar**). Jadi gambarnya harus memang arca lembu Nandi duduk di dalam bilik Candi Pringapus. Gambar lama `tmg-3-b03` (arca Buddha, telaah no. 247) sengaja salah untuk butir lama dan kini tidak dipakai butir mana pun; pengembang memutuskan gambar baru dibuat ChatGPT dalam gaya game dari foto peneliti, dan no. 247 serta 248 dibuang (16 November 2026). Foto peneliti sudah dipasang sebagai gambar Pustaka `library.temanggung.p5.3`, jadi gambar butir ini dibuat sendiri, bukan foto yang sama. Benda Cari Objek no. 265 (Arca Nandi, `tmg-4-06`, tantangan keempat wilayah yang sama) berupa Nandi berhias kalung tebal menghadap kanan tanpa latar; gambar ini harus jelas lain: di dalam bilik, menghadap kiri, sederhana seperti aslinya.

| | |
|---|---|
| Kode berkas | `challenge.item.tmg-3-b04` → simpan sebagai `challenge.item.tmg-3-b04.webp` |
| Kanvas | 480 × 360 (4:3), tanpa latar transparan, sama dengan gambar Benar atau Salah lain |
| Lampiran | Foto peneliti `ai-studio/chatgpt-images/inbox/reference-Pringapus-researcher-Nandi-in-chamber-01.jpg` (bentuk arca, alas, dinding bilik), `ai-studio/chatgpt-images/inbox/reference-Pringapus-researcher-Nandi-close-carved-collar-03.jpg` (kalung berukir tipis, kepala), `ai-studio/chatgpt-images/inbox/reference-Pringapus-researcher-Nandi-through-doorway-sign-02.webp` (cahaya dari pintu); gaya `ai-studio/game-images/assets/tantangan/challenge.item.tmg-3-a03.webp` (gambar Benar atau Salah Temanggung yang sudah ada); pembeda `ai-studio/game-images/assets/tantangan/challenge.item.tmg-4-06.webp` (benda Cari Objek, jangan disamakan) |
| Butir | `tmg-3-b04` (kartu Benar atau Salah, kunci benar). Kartu memotong gambar: subjek di pita tengah mendatar |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-3-b04  (R-23 · the Nandi statue inside the chamber of Candi Pringapus, true/false card picture)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Match the render finish and light of the attached true/false card picture so that the cards of this challenge look like one set.
The attached photos of the researcher are the factual reference for the statue and its chamber: follow them, render them in the GELITA finish, and do not copy a photo. Leave out the small name sign on the floor in front of the statue. The attached Nandi cut-out is a different picture of the game: do NOT copy its pose, ornaments or direction.

SUBJECT AND COMPOSITION
The ancient stone statue of Nandi, the sacred bull, lying down inside the small stone chamber of Candi Pringapus, a 9th-century Hindu temple in Temanggung, Central Java. Three-quarter view from the front left at the height of a standing child; the statue faces LEFT.
THE STATUE:
- Grey volcanic andesite, weathered and slightly rough, with darker patches; matte stone, never shiny or gilded.
- The bull lies with its legs folded under the body: the front legs bent forward, the rear leg tucked beside the belly, the tail curling over the haunch.
- A large rounded hump behind the neck; short horns with one broken; a long calm face.
- Only a thin, simply carved collar around the neck; no heavy necklaces, bells or saddle cloth.
- It rests on a low, plain rectangular stone pedestal with straight edges.
THE CHAMBER:
- Walls of large fitted grey stone blocks, a plain stone-paved floor; small and narrow, the statue fills most of the space.
- Soft daylight comes in from a doorway out of frame on the left and falls across the statue; the corners stay in gentle shadow.
- Nothing else: no offerings, flowers, people, lamps, columns or other statues.
Keep the statue and its pedestal inside the middle horizontal band; the card crops the top and bottom edges.

DELIVERY
Generate at 1440 × 1080 and scale down proportionally to exactly 480 × 360 pixels; aspect 4:3. Opaque background (the chamber), no transparency. Deliver WebP, named challenge.item.tmg-3-b04.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, signs, numbers, logos, people, hands, extra statues, cropped head or horns, blurry edges, neon colours, or fake checkerboard. No painterly 2D, gouache or paper grain. Nothing that marks the picture as the answer.
```

### R-24 · Semangkuk mi ongklok (benda Cari Objek Wonosobo, pengecoh)

Ditambahkan 16 November 2026. Telaah no. 276: satu-satunya dari 30 benda Cari Objek yang berlatar persegi (kain batik dan nampan bambu, mangkuk terpotong di tepi, tampak atas dengan sate), sedangkan 29 benda lain potongan tanpa latar. Di adegan ia menonjol dan bisa dikira penanda jawaban. Pengembang memutuskan dibuat ulang sebagai potongan tanpa latar (16 November 2026). Benda ini **pengecoh** di Cari Objek Wonosobo babak 1.

| | |
|---|---|
| Kode berkas | `challenge.item.wnb-4-a07` → simpan sebagai `challenge.item.wnb-4-a07.webp` |
| Kanvas | 240 × 240, latar transparan (buat pada 1024 × 1024, lalu perkecil) |
| Lampiran | Gambar lama `ai-studio/game-images/assets/tantangan/challenge.item.wnb-4-a07.webp` (isi mangkuk: mi, kuah kental cokelat, kol, kucai, bawang goreng); untuk hasil akhir yang sama `ai-studio/game-images/assets/tantangan/challenge.item.wnb-4-a06.webp` (nampan bambu tanpa latar) |
| Butir | Benda pengecoh Cari Objek Wonosobo babak 1 ("Semangkuk mi ongklok") |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-4-a07  (R-24 · a bowl of mi ongklok, hidden-object cut-out)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Match the render finish, lighting, viewing angle and outline quality of the attached bamboo tray cut-out exactly, so that the objects of this scene look like parts of one set. The attached old picture shows what is in the bowl; keep its food, but NOT its top-down crop, its satay, its cloth or its tray.

SUBJECT AND COMPOSITION
One isolated object on a transparent background: one whole bowl of mi ongklok, the noodle dish of Wonosobo, seen from the front and a little above (about 30 degrees), the whole round bowl visible.
- A white ceramic bowl with a simple blue band at the rim.
- Inside: yellow noodles with pale green cabbage pieces and chopped chives (kucai), covered with a thick, glossy, brown starchy sauce; a little fried shallot on top.
- Nothing beside the bowl: no satay, no spoon, no chopsticks, no plate, no cloth, no tray.
Hidden-object rule — identical lighting and quality for targets and decoys: no glow, no sparkle, no outline, no steam, no label, no arrow, nothing that marks this object.

DELIVERY
Generate at 1024 × 1024 and scale down proportionally to exactly 240 × 240 pixels; aspect 1:1. The whole bowl inside the canvas with a small even margin. Genuine transparent background outside the subject; preserve alpha; no cast shadow on a ground. Deliver WebP, named challenge.item.wnb-4-a07.webp.

CONSTRAINTS
No watermarks, signatures, text, numbers, logos, people, hands, extra objects, cropped parts, blurry edges, neon colours, or fake checkerboard transparency. No painterly 2D, gouache or paper grain.
```

## Cerita pembuka (pengembang, 16 November 2026)

Ditambahkan 16 November 2026. Pengembang menelaah layar cerita pembuka (`/intro`) dan meminta empat latar slide dibuat ulang: slide 1, 6, 7, dan 8. Peneliti mengirim lima foto Dataran Kedu yang berizin, disimpan di `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-*`, `-01` sampai `-05`: Borobudur dari udara dengan dataran dan gunung di belakangnya (`-01`), dua gunung kembar Sindoro dan Sumbing saat matahari terbit (`-02`), dua gunung kembar dari punggung bukit dengan puncak-puncak jauh di kiri (`-03`), Gunung Sindoro di atas desa dataran tinggi berladang sayur (`-04`), dan Telaga Warna di Dieng (`-05`).

Yang salah dari gambar sekarang:

- **Slide 1** (`intro-01`, *Dataran yang Bercahaya*): dataran tinggi khayalan dengan tebing, air terjun, dan gunung berasap. Naskahnya menyebut dataran luas yang dikelilingi Gunung Sindoro, Sumbing, Merapi, Merbabu, dan Perbukitan Menoreh, dengan desa-desa yang diterangi Cahaya Kedu setiap malam.
- **Slide 6** (`intro-06`, *Jaka, Pembawa Lentera*): pendapa dengan lentera besar di depan dan deretan gunung yang tidak dikenali. Sejak hari yang sama tokohnya tampil sebagai avatar bundar di ujung kiri teks, jadi latar tidak perlu lagi menyisakan tempat untuk Jaka berdiri.
- **Slide 7** (`intro-07`, *Cara Mengumpulkan Serpihan*): joglo dengan papan penunjuk, relief, dan kentongan melayang. Itu isi slide 8 di prompt lama; naskah slide 7 bercerita tentang lima serpihan di setiap wilayah.
- **Slide 8** (`intro-08`, *Tiga Kunci Cahaya*): matahari terbit di atas dataran dengan jalan setapak. Naskahnya pesan Mbah Kedu tentang tiga kunci, satu untuk setiap wilayah.

Sejak hari yang sama layar cerita pembuka tidak memakai efek sinar dari atas atau samping, jadi gambar ini tidak boleh membawa berkas sinar sendiri. Seluruh rujukan di bawah dipakai untuk bentuk gunung, candi, dan telaga saja, tidak disalin.

Tokoh yang berbicara kini tampil sebagai avatar bundar dengan papan nama, seperti layar Kenali (pengembang, 16 November 2026). Avatar Jaka dan Mbah Kedu yang ada (wajah ceria dan tersenyum) dipakai untuk pose lain; tiga ekspresi baru dipesan untuk baris yang suasananya berbeda: `R-31` Jaka bertekad (`intro-06`), `R-32` Jaka berterima kasih (penutup), `R-33` Mbah Kedu lemah (`intro-05`). Papan nama memakai pelat biru tua yang sudah ada (`public/assets/ui/kenal-plate-navy.webp`), jadi tidak ada pesanan pelat baru. Layar *Ketuk untuk mendengar kisahnya* memakai latar cerita yang sudah ada (`bg.intro`, rumah joglo penjaga di malam hari), jadi juga tidak ada pesanan latar baru untuk layar itu.

### R-27 · Dataran Kedu yang bercahaya (slide 1 cerita pembuka)

| | |
|---|---|
| Kode berkas | `bg.cerita-intro-01` → simpan sebagai `bg.cerita-intro-01.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-twin-volcanoes-from-ridge-03.jpg` (dua gunung kembar dan puncak jauh), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` (bentuk Sindoro dan Sumbing), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (dataran berpohon kelapa dan gunung di kejauhan; candinya tidak digambar) |
| Naskah | "Dahulu kala, ada dataran luas bernama Dataran Kedu. Di sekelilingnya berdiri Gunung Sindoro, Sumbing, Merapi, Merbabu, dan Perbukitan Menoreh. Setiap malam, desa-desanya diterangi cahaya lembut yang disebut Cahaya Kedu." |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-intro-01  (R-27 · opening story, slide 1: the shining Kedu plain)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all. The attached photos are factual references for the land only: follow the shapes of the mountains and the plain, render them in the GELITA finish, and do not copy a photo.

SUBJECT AND COMPOSITION
Wide 16:9 cinematic landscape. Main subject inside the central 75% of the width and the central 80% of the height; horizon between 45% and 55% of the height; the lower third calm and darker for text panels; the top 8% free of important detail; mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom.
The Kedu plain in Central Java at blue hour, just after sunset, seen from the gentle slope of the Menoreh hills in the south, looking north across the plain.
- A wide, flat, fertile plain fills the middle: rice fields, coconut palms and small villages of Javanese houses with clay-tile roofs. In every village small warm golden lights glow softly from windows and lanterns, so the whole plain is dotted with gentle gold: the Light of Kedu.
- The plain is ringed by mountains, all clearly separate: on the LEFT, the twin volcanoes Mount Sumbing (broader) and Mount Sindoro (a clean symmetrical cone) side by side; on the RIGHT, Mount Merbabu (broad, with several low summits) and in front of it Mount Merapi (a steeper cone with a rough grey top). Smooth slopes, no smoke, no eruption, no lava.
- The foreground and the whole lower third: the dark, quiet slope of the Menoreh hills with soft rounded tree silhouettes, no bright detail.
- Sky: deep navy above, softening to violet and a thin warm band low behind the mountains; the first few stars. Wonder and calm.

Accuracy — No cliffs, canyons, waterfalls, sea, coast or islands; the plain is flat farmland between mountains. No temple or stupa in this picture. No light rays, sunbeams or god rays.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque, full-bleed picture. Deliver WebP, named bg.cerita-intro-01.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### R-28 · Jaka berangkat dari rumah penjaga (slide 6 cerita pembuka)

| | |
|---|---|
| Kode berkas | `bg.cerita-intro-06` → simpan sebagai `bg.cerita-intro-06.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; `ai-studio/game-images/assets/latar/bg.intro.webp` (rumah joglo penjaga: kayu dan ukirannya sama); foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` (dua gunung kembar saat pagi), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-over-highland-village-04.jpg` (lereng berladang dan desa) |
| Naskah | Jaka: "Biar aku yang pergi, Mbah! Aku akan mencari kelima belas serpihan itu satu per satu, sampai Lentera Kedu menyala lagi. Aku janji!" |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-intro-06  (R-28 · opening story, slide 6: setting out from the keeper's house)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all (the game shows the speaker as a round portrait). Attached GELITA pictures are for render finish and for the keeper's joglo house only; the photos are factual references for the mountains and the slopes; do not copy a photo.

SUBJECT AND COMPOSITION
Wide 16:9 cinematic landscape. Main subject inside the central 75% of the width and the central 80% of the height; horizon between 45% and 55% of the height; the lower third calm and slightly darker for text panels; the top 8% free of important detail; mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom.
Early dawn, seen from the front veranda of the keeper's old wooden joglo house on a hillside (the same house as the attached bg.intro): two carved teak pillars frame the left and right edges of the picture, and the dark wooden veranda floor fills the bottom.
- From the veranda steps a stone footpath winds down the green hillside, past terraced vegetable fields and a few clay-tile roofs, towards the wide valley.
- In the distance, filling the upper middle: the twin volcanoes Mount Sumbing (left, broader) and Mount Sindoro (right, a clean cone), soft blue in the morning haze, the first warm light touching their tops. No smoke.
- In the valley lies the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) in soft layered bands, faint wisps that dissolve like fading ink strokes and never form letters; eerie but never scary. Far away in three directions, three tiny warm golden glints shine through the mist where shards of light have fallen.
- Mood: hopeful, brave, the start of a journey.
- The lower third: the veranda floor and the top of the steps in calm shadow, no objects on them.

Accuracy — No lantern in the foreground, no people, no animals. No light rays, sunbeams or god rays. No tobacco plants or smoke.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque, full-bleed picture. Deliver WebP, named bg.cerita-intro-06.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### R-29 · Lima serpihan di setiap wilayah (slide 7 cerita pembuka)

| | |
|---|---|
| Kode berkas | `bg.cerita-intro-07` → simpan sebagai `bg.cerita-intro-07.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg` (Temanggung: dua gunung kembar), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (Magelang: Borobudur), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Telaga-Warna-Dieng-05.jpg` (Wonosobo: Telaga Warna), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-over-highland-village-04.jpg` (Wonosobo: desa dataran tinggi) |
| Naskah | "Setiap wilayah menyimpan lima serpihan, dan setiap serpihan dijaga oleh satu tantangan. Selesaikan tantangannya untuk mendapatkan serpihan dan bintang. Lihat papan Serpihan Cahaya di bagian atas layar: cahayanya bertambah setiap kali serpihan kembali." |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-intro-07  (R-29 · opening story, slide 7: five shards in every region)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all. The attached photos are factual references for the three lands only: follow the shapes of the mountains, the temple and the lake, render them in the GELITA finish, and do not copy a photo.

SUBJECT AND COMPOSITION
Wide 16:9 cinematic picture. Main subject inside the central 75% of the width and the central 80% of the height; the lower third calm and slightly darker for text panels; the top 10% free of important detail (the game shows its Shards of Light panel there); mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom.
A high bird's-eye view, like a softly lit 3D diorama, of three neighbouring lands side by side under a calm late-afternoon sky, each land clearly its own, joined by small roads and a river:
- LEFT, Temanggung: the twin volcanoes Mount Sumbing and Mount Sindoro side by side, with terraced coffee and vegetable fields and small villages on their lower slopes.
- MIDDLE, Magelang: the great stepped stupa temple of Borobudur on a low green hill among coconut palms and rice fields, the low Menoreh hills behind it.
- RIGHT, Wonosobo: a high cool plateau with a small turquoise lake (Telaga Warna) beside a smaller one, terraced vegetable fields and a highland village, green forest around.
In EACH land exactly FIVE small faceted crystal shards of warm golden light (#F0DFB8 core, #DFC087 edges) rest in five different places, each with a gentle glow and a few tiny sparkles: fifteen shards in all, five per land, easy to count. Thin patches of the Mist of Forgetting (cold blue-grey #8C97AA to #5B6478, soft layered bands, never shaped like letters) lie between and around the shards. Clear, inviting, a promise of adventure.
The lower third: soft green lowland and mist only, no buildings, no lake, no shards.

Accuracy — Exactly five shards in each land, no more, no fewer. Temanggung: no tobacco plants, cigarettes or smoke. Wonosobo: no crater, steam, smoke, cracked ground or disaster. No sea, coast, cliffs or waterfalls. No light rays, sunbeams or god rays.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque, full-bleed picture. Deliver WebP, named bg.cerita-intro-07.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, labels, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### R-30 · Tiga kunci cahaya (slide 8 cerita pembuka)

| | |
|---|---|
| Kode berkas | `bg.cerita-intro-08` → simpan sebagai `bg.cerita-intro-08.webp` |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; `ai-studio/game-images/assets/latar/bg.intro.webp` (rumah joglo penjaga); foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-twin-volcanoes-from-ridge-03.jpg` (Temanggung), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (Magelang), `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Telaga-Warna-Dieng-05.jpg` (Wonosobo) |
| Naskah | Mbah Kedu: "Ingat tiga kunci ini, Le. Di Temanggung, temukan informasi yang tertulis jelas. Di Magelang, hubungkan informasi dan bedakan fakta dari pendapat. Di Wonosobo, timbang setiap informasi dan dari mana asalnya." |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-intro-08  (R-30 · opening story, slide 8: three keys of light)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT include Jaka or Mbah Kedu, and no people at all (the game shows the speaker as a round portrait). Attached GELITA pictures are for render finish and for the keeper's joglo house only; the photos are factual references for the three lands; do not copy a photo.

SUBJECT AND COMPOSITION
Wide 16:9 cinematic picture. Main subject inside the central 75% of the width and the central 80% of the height; horizon between 40% and 50% of the height; the lower third calm and slightly darker for text panels; the top 8% free of important detail; mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom.
Golden hour, seen from the warm lantern-lit porch of the keeper's old wooden joglo house high on a hillside (the same house as the attached bg.intro); a low carved wooden railing runs along the bottom of the picture.
- Beyond the railing the whole Kedu land spreads out in three clearly separate parts, from left to right: Temanggung, the twin volcanoes Mount Sumbing and Mount Sindoro with terraced fields below them; Magelang, the stepped stupa temple of Borobudur on a low green hill in the middle distance; Wonosobo, a high plateau on the right with a small turquoise lake.
- Above EACH of the three lands floats one small antique brass key, softly glowing gold, the same size, shape and brightness as the other two: three keys, one over each land, gently shining like lanterns in the evening air.
- Thin bands of the Mist of Forgetting (cold blue-grey #8C97AA to #5B6478, never shaped like letters) still lie in the low valleys. Warm, wise, calm.
- The lower third: the dark porch floor and the railing in calm shadow, nothing on them.

Accuracy — Exactly three keys, all alike: no key brighter, bigger or nearer than another. Temanggung: no tobacco plants or smoke. Wonosobo: no crater, steam or smoke. No signpost, relief panel or drum. No light rays, sunbeams or god rays.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque, full-bleed picture. Deliver WebP, named bg.cerita-intro-08.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, labels, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### R-31 · Avatar Jaka bertekad (`ui.jaka-avatar.determined`)

Suntingan, bukan gambar baru, seperti `R-19`: bingkai emas dan latarnya sama persis dengan avatar Jaka, hanya wajah dan bahunya yang berganti ekspresi. Dipakai `intro-06` ("Biar aku yang pergi, Mbah! … Aku janji!").

| | |
|---|---|
| Kode berkas | `ui.jaka-avatar.determined` → simpan sebagai `ui.jaka-avatar.determined.webp` |
| Kanvas | 1200 × 1200, latar transparan, sama dengan avatar Jaka |
| Lampiran | Gambar yang diubah `ai-studio/game-images/mockup/gambar-mockup/kenali/aset/ui.jaka-avatar.webp`; lembar tokoh `ai-studio/game-images/assets/referensi/reference.01.webp` |

```text
EDIT the attached round avatar "ui.jaka-avatar.webp". Keep EVERYTHING outside the boy exactly as it is: the carved gold ring with its ornaments, the warm gold cloud background inside the ring, the size, the position, the lighting and the transparent exterior.
Use the image editor with a MASK over the boy's face, hair, neck and shoulders only; the ring and the background stay outside the mask, locked. Do not regenerate the whole picture.
Change ONLY his expression and a little of his pose: DETERMINED and brave. Eyebrows slightly lowered and drawn together, eyes bright and focused straight ahead, mouth closed in a firm, small confident smile, chin a little raised, shoulders squared. He is the same boy as in the attached character sheet: about 11 years old, tousled dark-brown hair with a cowlick, brown eyes, red scarf, cream shirt, leather backpack strap. Not angry, not shouting, no fist in the picture.
Output: 1200 × 1200 WebP, genuine transparent background outside the ring, preserve alpha. Named ui.jaka-avatar.determined.webp.
No text, letters, extra objects, glow or outline added.
```

### R-32 · Avatar Jaka berterima kasih (`ui.jaka-avatar.bow`)

Suntingan seperti `R-31`. Dipakai cerita penutup (pose `bow`).

| | |
|---|---|
| Kode berkas | `ui.jaka-avatar.bow` → simpan sebagai `ui.jaka-avatar.bow.webp` |
| Kanvas | 1200 × 1200, latar transparan, sama dengan avatar Jaka |
| Lampiran | Gambar yang diubah `ai-studio/game-images/mockup/gambar-mockup/kenali/aset/ui.jaka-avatar.webp`; lembar tokoh `ai-studio/game-images/assets/referensi/reference.01.webp` |

```text
EDIT the attached round avatar "ui.jaka-avatar.webp". Keep EVERYTHING outside the boy exactly as it is: the carved gold ring with its ornaments, the warm gold cloud background inside the ring, the size, the position, the lighting and the transparent exterior.
Use the image editor with a MASK over the boy's face, hair, neck and shoulders only; the ring and the background stay outside the mask, locked. Do not regenerate the whole picture.
Change ONLY his expression and a little of his pose: THANKFUL and respectful, the polite Javanese way of thanking an elder. His head bowed slightly forward, eyes softly half-closed or looking down, a warm gentle smile with the mouth closed. He is the same boy as in the attached character sheet: about 11 years old, tousled dark-brown hair with a cowlick, brown eyes, red scarf, cream shirt, leather backpack strap. Not sad, not sleepy, no hands in the picture.
Output: 1200 × 1200 WebP, genuine transparent background outside the ring, preserve alpha. Named ui.jaka-avatar.bow.webp.
No text, letters, extra objects, glow or outline added.
```

### R-33 · Avatar Mbah Kedu lemah (`ui.kedu-avatar.weak`)

Suntingan seperti `R-31`. Dipakai `intro-05` ("Aku Mbah Kedu, penjaga lentera ini. Kakiku sudah tua … Setiap kali cahayanya meredup, tubuhku ikut melemah.").

| | |
|---|---|
| Kode berkas | `ui.kedu-avatar.weak` → simpan sebagai `ui.kedu-avatar.weak.webp` |
| Kanvas | 1024 × 1024, latar transparan, sama dengan avatar Mbah Kedu |
| Lampiran | Gambar yang diubah `ai-studio/game-images/assets/referensi/reference.02.webp` (avatar Mbah Kedu) |

```text
EDIT the attached round avatar "reference.02.webp" (Mbah Kedu, the keeper). Keep EVERYTHING outside the old man exactly as it is: the carved gold ring with its ornaments, the warm gold cloud background inside the ring, the size, the position, the lighting and the transparent exterior.
Use the image editor with a MASK over his face, headcloth, beard and shoulders only; the ring and the background stay outside the mask, locked. Do not regenerate the whole picture.
Change ONLY his expression and a little of his pose: TIRED and WEAK, but still kind. Eyes heavy and half-closed, eyebrows slightly raised in the middle, a small weary but gentle smile, shoulders a little dropped, head tilted slightly down; the warm colours of his face a touch paler. He is the same man: brown batik headcloth, white moustache and short white beard, brown batik vest over a cream shirt. Not crying, not ill in bed, nothing frightening.
Output: 1024 × 1024 WebP, genuine transparent background outside the ring, preserve alpha. Named ui.kedu-avatar.weak.webp.
No text, letters, extra objects, glow or outline added.
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

## Bagian antarmuka

Dua bagian antarmuka dipesan lebih dulu, sebelum perombakan antarmuka (pengembang, 16 November 2026). Gayanya *emas berukir* (lihat *Aturan bersama*), bukan blok gambar isi game. Bagian antarmuka lain menunggu pengukuran di Chromebook dan menjadi baris `U-…`.

### R-25 · Tombol bahasa Inggris `round-lang-en` (bendera Union Jack)

Ditambahkan 16 November 2026. Telaah no. 62: tombol bundar bahasa Inggris memuat bendera Inggris (salib merah di atas putih), padahal teks slot `ui.lang.en` meminta Union Jack, dan ikon no. 126 sudah Union Jack. Tombol bundar inilah yang tampil di game (`hud-menu.php`), jadi no. 126 dan 127 tidak pernah terlihat. Pengembang memutuskan: tombol dibuat ulang dengan Union Jack, no. 126 dan 127 dibuang (16 November 2026). Disunting dari tombol yang ada, bukan digambar dari nol: bingkai emasnya harus sama persis dengan pasangannya `round-lang-id`.

| | |
|---|---|
| Berkas | `public/assets/ui/round-lang-en.webp` → simpan sebagai `round-lang-en.webp` |
| Kanvas | 256 × 256, latar transparan, sama dengan `round-lang-id.webp` |
| Lampiran | `public/assets/ui/round-lang-en.webp` (yang disunting), `public/assets/ui/round-lang-id.webp` (pasangannya: bingkai, kain, cahaya) |

```text
Edit the attached round button "round-lang-en.webp". Keep its carved gold ring exactly as it is: same size, same ornaments, same position, same light from the upper left. Replace ONLY the flag inside the ring.
Use the image editor with a MASK over the flag disc only; the gold ring stays outside the mask, locked. Do not regenerate the whole button.
New flag: the Union Jack of the United Kingdom, correct proportions and colours (navy blue field, white saltire, red saltire offset as on the real flag, white-bordered red cross), as softly waving fabric with the same cloth folds, sheen and inner shadow as the red-and-white flag in the attached "round-lang-id.webp".
The two buttons must look like one pair: same ring, same fabric, same brightness.
Output: 256 × 256 pixels, genuine transparent background outside the ring, preserve alpha. WebP, named round-lang-en.webp.
No text, letters, extra symbols, glow, outline or shadow on a ground. Not the flag of England (a single red cross on white).
```

### R-26 · Pelat instruksi Isi Rumpang yang tenang `rumpang-instruction-plate`

Ditambahkan 16 November 2026. Kartu instruksi Isi Rumpang memakai kulit `ui/kenal-plate-navy.webp` (459 × 131), yang digambar untuk pelat tipis satu baris. Sejak pita instruksi dibuat 66 dp pada 15 November 2026 (catatan implementasi), pelat itu teregang, dan hiasan emasnya jatuh di bawah tanda ⓘ dan kata terakhir baris pertama. Pengembang memesan pelat baru sekarang, tidak menunggu perombakan (16 November 2026). Pelat ini berkas baru; `kenal-plate-navy.webp` tetap dipakai Kenali dan tombol kembali. Hiasannya hanya di kedua ujung, bagian tengahnya polos, supaya pelat bisa dipasang dengan ujung tetap dan bagian tengah memanjang tanpa ada hiasan yang teregang.

| | |
|---|---|
| Berkas | baru: `public/assets/ui/rumpang-instruction-plate.webp` |
| Kanvas | 1600 × 132, latar transparan (dua kali ukuran pita 66 dp) |
| Lampiran | `public/assets/ui/kenal-plate-navy.webp` (pelat sekarang: warna, bingkai, bidang biru tua), `public/assets/ui/round-lang-id.webp` (emas berukir acuan) |

```text
A long, calm, horizontal interface plate for a children's game, in the carved-gold style of the attached pieces: a dark navy stone panel with a very faint cloud pattern, framed by a thin raised gold border, one light from the upper left, rendered as a 3D object.
Canvas exactly 1600 × 132 pixels, transparent outside the plate. The plate fills the canvas with an even 4-pixel margin.
ORNAMENTS ONLY AT THE TWO ENDS: each end carries a small carved gold ornament (a pointed cap with a curl, in the family of the attached plate's end caps) inside the first and last 120 pixels. Between them, from x = 120 to x = 1480, the panel is completely plain: the same thin gold border along the top and bottom edges and the same flat navy surface, with no ornament, no gem, no highlight spot, no gradient that changes along the length, so that this middle part can be stretched horizontally without any visible distortion.
The navy surface is darker and calmer than the attached plate, so light cream text of two lines reads clearly on it.
No glow line, no bright rim light, no text, letters, icons or symbols. WebP, named rumpang-instruction-plate.webp.
```

## Daftar periksa hasil

**Gambar isi game (`R-01` sampai `R-05`, `R-12` sampai `R-22`)**

- Tanpa tulisan, angka, atau logo, kecuali `R-14`.
- `R-01`: tepat dua gunung, tanpa candi, telaga, atau laut; tanpa tembakau; sepertiga bawah tenang.
- `R-02`: terbaca sebagai beberapa pepaya kecil, bukan belimbing dan bukan satu pepaya besar; latar transparan; tanpa cahaya atau garis tepi yang menandai jawaban.
- `R-12`: tepat tiga arca dari batu abu-abu kecokelatan; Buddha duduk dengan kedua kaki di alas teratai, tangan di depan dada; kedua arca pengapit duduk; tanpa singa, sesaji, pagar, dan orang. Cocokkan dengan kedua foto acuan.
- `R-13`: ikan kecil ramping sebesar jari, bukan ikan laut; seperempat bawah dari kiri hanya permukaan meja; tanpa tulisan, tombol putar, dan tampilan media sosial.
- `R-14`: setiap tulisan sama persis dengan daftar *All text*, tanpa tulisan lain; utara di atas; Dieng Kulon, Candi Arjuna, dan Kawah Sikidang di Banjarnegara, Dieng Wetan, Telaga Warna, dan Sikunir di Wonosobo; cocokkan letaknya dengan peta jalur wisata `-08`.
- `R-15`: belahan buah berdaging tipis krem, isi putih dengan banyak biji kecil cokelat tua; buah utuh kecil bergerigi lima dan berujung lancip; gaya 3D, bukan lukisan.
- `R-16`: tanpa menara candi tinggi; teras dengan talud batu polos, jalan batu, satu candi kecil beratap bertingkat, alas dengan yoni, Gunung Sindoro satu kerucut; tanpa orang.
- `R-17` sampai `R-19`: bundengan berdiri tegak dengan ujung runcing di atas, satu tongkat bambu panjang di tepinya yang mencuat melewati puncak, senar di dalam cekungan (bukan di luar); tidak mirip gitar, harpa, angklung, sasando, atau kereta luncur. Bandingkan dengan foto `-01` dan `-04`.
- `R-17`: latar transparan; hasil akhir sama dengan angklung `a08` dan sasando `a09`, tetapi jelas berbeda bentuk; tanpa orang dan tanpa tanda jawaban.
- `R-18`: 16 bidang berbeda; pemain duduk bersila di tikar; tanpa tulisan apa pun (tulisan unggahan diberi halaman).
- `R-19`: di luar lambang tidak ada yang berubah (cincin, titik intan, cakram krem, ukuran); bandingkan dengan avatar lama.
- `R-20` dan `R-21`: kuda kepang pipih seperti papan, bentuk kuda utuh dari samping (kepala, leher melengkung, badan), anyaman bambu yang tetap tampak di bawah cat, bingkai rotan; tanpa tongkat, roda, ayunan, pelana kain, atau rumbai di bawah perut; latar transparan; `R-20` putih berhias merah-hitam, `R-21` hitam berhias emas; `R-21` jelas berbeda dari dadak merak. Bandingkan dengan foto `-01` dan `-04`.
- `R-22`: kendil, periuk tanah liat bulat bermulut lebar dengan tutup tanah liat bertombol; tanpa cerat, gagang panjang, dan kaki; tanah liat bakar cokelat kemerahan, tidak mengilap seperti logam; jelas berbeda dari panci logam `a10`; latar transparan. Bandingkan dengan foto kendil dari pengembang.
- `R-03` sampai `R-05`: tokohnya Jaka (syal merah, kemeja krem, ransel, tanpa ikat kepala); ketiganya berlatar biru tua yang sama; medali simbol bergaya emas berukir.
- `R-27` sampai `R-30`: tanpa orang dan tanpa berkas sinar; sepertiga bawah tenang. `R-27`: dataran datar berdesa bercahaya keemasan, Sindoro dan Sumbing di kiri, Merbabu dan Merapi di kanan, tanpa tebing, air terjun, laut, dan candi. `R-28`: dua tiang joglo di tepi, jalan batu turun ke lembah, dua gunung kembar, tanpa lentera di depan. `R-29`: tiga wilayah yang jelas berbeda dan **tepat lima** serpihan di setiap wilayah (hitung). `R-30`: tepat tiga kunci kuningan yang sama besar dan sama terang, satu di atas setiap wilayah.
- `R-31` sampai `R-33`: di luar tokoh tidak ada yang berubah (cincin, latar emas, ukuran, posisi); bandingkan dengan avatar lama. Tokohnya tetap orang yang sama, ekspresinya terbaca di 196 dp.

**Gambar tes (`R-06` sampai `R-11`)**

- Setiap kata sama dengan daftar *All text* di promptnya. Baca seluruhnya, bukan hanya bagian yang diubah.
- Hanya bagian yang disebut prompt yang berubah. Bandingkan dengan gambar lama.
- Tidak ada tombol, tulisan, atau gambar yang lebih menonjol daripada bagian sejenisnya.
- Tetap hitam-putih.

## Pemasangan

- `R-01` sampai `R-05`, `R-12`, `R-14` sampai `R-22`, `R-24`: berkas dari `ai-studio/chatgpt-images/out/` **dipindahkan** (bukan disalin) ke tempat berkas lamanya di `ai-studio/game-images/assets/` dan menggantikannya; entri `data.json` tidak berubah. Lalu `php spark gelita:katalog:impor`. `R-13` adalah gambar baru: berkasnya dipindahkan ke `ai-studio/game-images/assets/tantangan/challenge.post.tmg-4-b.webp`, lalu entri baru `challenge.post.tmg-4-b` ditambahkan ke `data.json` (contoh: entri `challenge.item.tmg-1-h01`) dan satu baris ke `REVIEW.md`. `R-16`: entri `library.temanggung.p5.1` di `data.json` mendapat `caption` "Situs Liyangan di lereng Gunung Sindoro" / "The Liyangan Site on the slopes of Mount Sindoro". `R-14`: entri `library.wonosobo.p2.2` di `data.json` mendapat `caption` baru "Peta kawasan Dataran Tinggi Dieng" / "Map of the Dieng Plateau area".
- `R-06` sampai `R-11`: berkas dipindahkan ke `ai-studio/game-images/mockup/gambar-mockup/instrumen/aset/`. Berkas PNG induk di `docs/revisi-10-analitik/gambar/doc-34/` diganti dengan hasil yang sama, supaya induk dan gambar terpasang tidak berbeda.
- `R-23` adalah gambar baru untuk slot yang sudah ada di bank: berkasnya dipindahkan ke `ai-studio/game-images/assets/tantangan/challenge.item.tmg-3-b04.webp`, entri baru `challenge.item.tmg-3-b04` ditambahkan ke `data.json` (contoh: entri `challenge.item.tmg-3-a03`) dan satu baris ke `REVIEW.md`. Entri dan berkas `challenge.item.tmg-3-b03` dan `tmg-3-c03` dihapus.
- `R-27` sampai `R-30`: berkas dipindahkan ke `ai-studio/game-images/assets/latar/` menggantikan berkas lamanya; entri `data.json` mendapat prompt baru dari bagian ini. Lalu `php spark gelita:katalog:impor`.
- `R-31` sampai `R-33` gambar baru: berkas dipindahkan ke `ai-studio/game-images/assets/ikon/`, lalu entri baru `ui.jaka-avatar.determined`, `ui.jaka-avatar.bow`, dan `ui.kedu-avatar.weak` ditambahkan ke `data.json` (kategori Ikon, 512 × 512, alpha; slot `ui.jaka-avatar.*` dan `ui.kedu-avatar.*` sudah ada di `ImageSlots`) dan satu baris per gambar ke `REVIEW.md`. Lalu `php spark gelita:katalog:impor`. Tanpa gambar ini, slide memakai avatar biasa.
- `R-25` menggantikan `public/assets/ui/round-lang-en.webp` di tempatnya (URL-nya berversi lewat `asset_url_versioned()`). Entri `icon.language-en` dan `icon.language-id` beserta berkasnya dihapus dari `data.json` bila tidak ada kode yang membacanya lagi.
- `R-26` berkas baru `public/assets/ui/rumpang-instruction-plate.webp`; CSS kartu instruksi Isi Rumpang memakainya sebagai `border-image` dengan kedua ujung tetap (120 px dari 1600) dan tengah memanjang, diukur di Chromebook (1366 × 768).
- Setelah dipasang, lembar telaah dibangun ulang (`node ai-studio/game-images/review/build-sheets.mjs`) dan baris `REVIEW.md` diberi tanda baru oleh pengembang.

## Kebijakan perubahan

Teks butir dan stimulus di `tes.php` tidak diubah oleh dokumen ini; hanya gambarnya yang disesuaikan dengan teks. Kalau teks stimulus berubah kelak, daftar *All text* di prompt terkait ikut diubah pada hari yang sama.
