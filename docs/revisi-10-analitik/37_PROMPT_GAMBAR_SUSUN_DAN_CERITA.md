# Prompt Gambar Susun Gambar Baru dan Latar Cerita Wilayah

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 4 November 2026 · Lampiran [08](08_DRAF_BANK_SOAL.md) (Susun Gambar selama studi) dan [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) (naskah cerita) · Status: `draf pengembang`, `asal: pengembang` (disusun AI atas permintaan pengembang)

Dokumen ini berisi prompt siap tempel ke ChatGPT untuk dua gambar Susun Gambar baru yang masih ditunggu 08 (Wayang Kedu dan pengganti Candi Pawon) dan untuk semua latar slide cerita wilayah: Kenali (`region_intro`), dialog masuk wilayah (`level_open`), wilayah tuntas (`level_done`), dan cerita penghubung tantangan kelima (`scenario_open`). Isinya mengikuti naskah 12, bukan naskah yang terpasang sekarang, karena naskah 12 yang dipakai selama studi. Polanya sama dengan [32A](32A_PROMPT_GAMBAR_SITUASI.md) dan panduan gambar puzzle Katalog Aset (`gelita-katalog/update/gambar-puzzle/panduan-gambar-puzzle-gelita.md`).

## Ringkasan

- **Susun Gambar:** 2 prompt. `TMG-P11` Wayang Kedu (`challenge.item.tmg-1-k01`, 3 × 3) dan `MGL-P05R` Candi Pawon pengganti (`challenge.item.mgl-1-e01`, 4 × 4, kode slot tetap). Keterangan keduanya tidak berubah dari 08.
- **Latar cerita wilayah:** 29 gambar untuk 92 slot. Satu gambar dipakai untuk beberapa slide yang tempatnya sama; tokoh Jaka dan Mbah Kedu ditampilkan game di atas latar, jadi latar tidak memuat tokoh.
- **Belum termasuk:** latar `intro`, `ending`, dan Peta Kedu (sudah ada di katalog), serta gambar situasi Skenario Digital ([32A](32A_PROMPT_GAMBAR_SITUASI.md); 24 gambar sudah ada di `gambar/32a/`).

## Cara memakai

1. Buka percakapan baru di ChatGPT untuk setiap gambar. Lampirkan gambar acuan gaya `reference.06` atau `reference.07` dari `gelita-katalog/assets/referensi/` (pemandangan dengan hasil akhir GELITA). Untuk Susun Gambar dan gambar bertanda *foto acuan*, lampirkan juga foto berlisensi objeknya.
2. Tempel prompt di kotak kode apa adanya. Jangan menambah tokoh, tulisan, atau benda yang tidak disebut prompt.
3. Periksa hasilnya dengan [daftar periksa](#daftar-periksa-hasil). Kalau gagal, minta perbaikan di percakapan yang sama dengan menyebut butir yang gagal.
4. Simpan sebagai WebP dengan nama kode aset. Gambar latar cerita yang dipakai beberapa slide disimpan sekali per kode slot di tabelnya (salinan berkas yang sama).
5. Tim Katalog Aset menambahkan entri di `gelita-katalog/data.json` (kode, berkas di `assets/latar/` atau `assets/tantangan/`, ukuran) dan menjalankan `node build.mjs`, sesuai `gelita-katalog/AGENTS.md`. Pemasangan ke game memakai `php spark gelita:katalog:impor`, tidak pernah di server produksi tanpa izin pengembang.

Catatan pemasangan:

- Slot `bg.cerita-tuntas-{wilayah}-05` dan semua `bg.cerita-layar-*` baru punya slide setelah naskah 12 dipasang (tahap 3, [12 → Yang perlu disiapkan di tahap 3](12_CERITA_PENGHUBUNG_DAN_NARASI.md#yang-perlu-disiapkan-di-tahap-3) no. 1 dan 3). Sampai saat itu impor katalog melewati gambar untuk slide yang belum ada. Awalan `bg.cerita-layar-` belum dikenali `CatalogImporter`; itu dikerjakan bersama konteks `scenario_open` di bagian 6 ([32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md)).
- Catatan gambar (`note`) Candi Pawon di berkas data bank soal masih menyebut "stupa kecil di atap". Catatan itu diganti arahan baru saat gambar pengganti dipasang (berkas data, lalu `build-workbook.php`).

## Aturan bersama

**Untuk semua gambar**
- Gaya: blok `GELITA ACTIVE VISUAL DIRECTION — 2026-10-11` dari katalog, kata per kata.
- Tanpa tulisan, angka, logo, atau tampilan aplikasi yang bisa dibaca. Papan, layar, ponsel, dan tablet tampil kosong, buram, atau berkabut.
- Orang Jawa berpakaian sopan, tidak menatap kamera, bukan tokoh nyata. Anak-anak selalu dekat orang dewasa dan tidak melakukan hal berbahaya.

**Untuk latar cerita**
- **Tanpa Jaka dan Mbah Kedu.** Game menampilkan potret tokoh di atas latar.
- **Komposisi slide:** 16:9, 1920 × 1080; sepertiga bawah tenang dan agak gelap untuk panel teks; 8% atas tanpa detail penting; tetap terbaca di bawah lapisan navy 35–75% (sama dengan latar `intro`).
- **Kabut Lupa** selalu berwarna biru kelabu yang sama (#8C97AA–#5B6478), berlapis lembut, tidak pernah membentuk huruf, menyeramkan tetapi tidak menakutkan.
- **Tidak membocorkan butir.** Gambar hanya memuat yang disebut kalimat slidenya. Dialog masuk dan cerita penghubung diputar tepat sebelum tantangan, jadi latarnya tidak menampilkan benda, tanda bahaya, atau isi layar yang diuji butir (sama dengan [aturan menulis cerita di 12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#aturan-menulis-cerita-untuk-penelitian)). Kenali boleh memuat fakta budaya, karena selama studi Kenali baru terbuka setelah wilayahnya tuntas.
- **Wonosobo:** kabar berantai di naskah 12 adalah ramalan gempa. Latar tidak menampilkan kawah, asap, retakan, atau bangunan runtuh, supaya tidak menambah rasa takut dan tidak mengulang `wnb-3-b`.

**Untuk Susun Gambar**
- Satu pemandangan utuh tanpa kolase; setiap area kisi punya penanda yang berbeda; tanpa bidang polos; pagi atau siang, bukan malam (panduan gambar puzzle §2–§3).
- Lulus uji keping otomatis dan uji susun oleh dua orang, lalu dicek ulang terhadap butir 08 ([08 → keputusan Susun Gambar](08_DRAF_BANK_SOAL.md#keputusan-pengembang-26-oktober-2026)).

## Susun Gambar

### TMG-P11 · Wayang Kedu

| | |
|---|---|
| Status | Temanggung, variasi `b` (pengerjaan pertama selama studi) |
| Kode berkas | `challenge.item.tmg-1-k01` → simpan sebagai `challenge.item.tmg-1-k01.webp` |
| Kisi | 3 × 3 kotak (mudah) |
| Foto acuan | Disarankan: foto pertunjukan wayang kulit gaya Kedu Temanggungan (bentuk wayang, kelir, blencong) dari sumber berlisensi |

**Keterangan (dari 08, tidak berubah):**

> **ID:** Wayang Kedu adalah wayang kulit asli wilayah Kedu yang berkembang di Temanggung. Dalang memainkan wayang di balik kelir, yaitu kain putih yang disinari lampu, dengan iringan gamelan.
>
> **EN:** Wayang Kedu is the shadow puppetry native to the Kedu area that grew in Temanggung. The dalang, or puppeteer, plays the puppets behind the kelir, a white cloth lit by a lamp, to the sound of gamelan.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-k01  (TMG-P11 · Wayang Kedu)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A shadow-puppet (wayang kulit) performance in the open pendapa (pillared pavilion) of a Temanggung village, in warm late-afternoon light before the show (daylight, not night). A large white cloth screen (kelir) stretched in a wooden frame across the middle; an adult dalang (puppeteer) in a batik headcloth sits behind it, holding a carved, painted leather puppet in the Kedu style; a brass oil lamp (blencong) hangs above him; puppet shadows fall on the kelir; banana-trunk stems hold rows of puppets on both sides; gamelan players with bronze gongs and kettle gongs sit behind; village children sit cross-legged on mats in front, with adults among them.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) the carved roof beams of the pendapa with the hanging brass blencong lamp; (2) the top of the kelir with a puppet shadow; (3) a row of standing puppets on a banana trunk, right side.
• Row 2 — (4) gamelan gongs hanging in their carved frame; (5) the dalang behind the kelir holding a puppet; (6) kettle gongs (bonang) with a player.
• Row 3 (bottom) — (7) children sitting on a woven mat, seen from behind and the side; (8) the base of the kelir with the long banana trunk; (9) an adult with a child on her lap and a bamboo tray of snacks.

Accuracy — Keep the puppets in the Kedu style shown in the reference (do not copy Balinese or Javanese court-style faces); the kelir is plain white cloth; the dalang is an adult man; no mountains, coffee plantations, temples, kuda kepang, fish, or writing anywhere.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking. A bright, clear morning or warm late-afternoon light from the left.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-k01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — night sky, fire, smoke, text.
```

### MGL-P05R · Candi Pawon (pengganti)

| | |
|---|---|
| Status | Magelang, variasi `b` (pengerjaan pertama selama studi); mengganti gambar 14 Oktober 2026 |
| Kode berkas | `challenge.item.mgl-1-e01` → simpan sebagai `challenge.item.mgl-1-e01.webp` |
| Kisi | 4 × 4 kotak (sedang) |
| Foto acuan | **Wajib**: foto Candi Pawon dari sudut dekat ke tangga, pintu, dan panel berukir |

**Keterangan (dari 08, tidak berubah):**

> **ID:** Candi Pawon berdiri di antara Candi Mendut dan Candi Borobudur. Ketiga candi Buddha itu berada hampir pada satu garis lurus.
>
> **EN:** Pawon Temple stands between Mendut Temple and Borobudur Temple. These three Buddhist temples lie almost in one straight line.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-e01  (MGL-P05R · Candi Pawon (pengganti))
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A close, slightly low view of Candi Pawon, a small Buddhist temple of grey andesite on a raised base, in a neat village garden on a clear morning. The picture is filled by the stairway, the doorway with its carved frame and the carved wall panels (bodhisattva, kalpataru tree and kinnara reliefs, faithful to the real temple); the roof and its small crowning stupas are cut off by the top edge of the picture or only barely visible far behind leaves. Around the base: a garden path, flowers, a frangipani tree, a low stone wall; a red-roofed village house at one side.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) frangipani branches in bloom against the sky; (2) the upper wall with a carved kalpataru panel (no roof, no stupa); (3) the carved lintel above the doorway; (4) a red-roofed village house with a window.
• Row 2 — (5) a coconut palm trunk and a banana plant; (6) a carved wall panel with a standing figure; (7) the dark doorway with its carved frame; (8) a carved corner of the temple wall with moss.
• Row 3 — (9) a low stone garden wall with a potted plant; (10) the left side of the stairway with carved side wings; (11) the right side of the stairway with steps; (12) the temple base with a row of carved panels.
• Row 4 (bottom) — (13) a garden path of stepping stones; (14) a flower bed with red canna and marigolds; (15) neat grass with a garden tap and a bucket; (16) a bicycle leaning against the garden wall.

Accuracy — A small Buddhist temple, smaller than Mendut. No hollow (latticed) stupas, no Borobudur, no large stupa shapes anywhere in the picture. No Hindu elements.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs. Warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-e01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — people climbing, stupas in view, text.
```

## Latar cerita wilayah

Setiap gambar di bawah dipakai untuk semua slot di barisnya. Judul dan isi slide ada di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) dan `app/Database/Seeds/data/story.php`.

| Gambar | Wilayah | Tempat | Disimpan sebagai |
| --- | --- | --- | --- |
| T-K1 | Temanggung | Sindoro dan Sumbing berdampingan pada pagi cerah; mata air Umbul Jumprit di lereng. | `bg.cerita-kenal-temanggung-01` |
| T-K2 | Temanggung | Lereng subur: kebun kopi dan sayuran, petani bekerja, sungai jernih. | `bg.cerita-kenal-temanggung-02` |
| T-K3 | Temanggung | Situs Liyangan: fondasi batu kuno yang tergali di lereng Sindoro; candi kecil di kejauhan. | `bg.cerita-kenal-temanggung-03` |
| T-K4 | Temanggung | Pertunjukan kuda lumping di lapangan desa saat perayaan. | `bg.cerita-kenal-temanggung-04` |
| T-D1 | Temanggung | Senja dari bukit: desa dan ladang tertutup Kabut Lupa, lampu desa redup. | `bg.cerita-dialog-temanggung-01`, `bg.cerita-dialog-temanggung-02`, `bg.cerita-dialog-temanggung-03`, `bg.cerita-dialog-temanggung-04`, `bg.cerita-dialog-temanggung-05` |
| T-D2 | Temanggung | Persimpangan berkabut dengan papan petunjuk kayu tanpa tulisan terbaca; jalan kanan menuju jembatan bambu yang patah, jalan kiri aman. | `bg.cerita-dialog-temanggung-06`, `bg.cerita-dialog-temanggung-07`, `bg.cerita-dialog-temanggung-08`, `bg.cerita-dialog-temanggung-09`, `bg.cerita-dialog-temanggung-10`, `bg.cerita-dialog-temanggung-11` |
| T-D3 | Temanggung | Kabut di papan menipis; di balik bukit sebuah cahaya keemasan berkilau. | `bg.cerita-dialog-temanggung-12`, `bg.cerita-dialog-temanggung-13`, `bg.cerita-dialog-temanggung-14`, `bg.cerita-dialog-temanggung-15` |
| T-T1 | Temanggung | Pagi cerah: kabut tersibak, papan petunjuk bersih, warga berjalan pulang. | `bg.cerita-tuntas-temanggung-01`, `bg.cerita-tuntas-temanggung-02`, `bg.cerita-tuntas-temanggung-03`, `bg.cerita-tuntas-temanggung-04`, `bg.cerita-tuntas-temanggung-05` |
| T-L1 | Temanggung | Anak-anak duduk di bawah pohon besar memandang tablet; kabut tipis keluar dari layar. | `bg.cerita-layar-temanggung-01`, `bg.cerita-layar-temanggung-02`, `bg.cerita-layar-temanggung-03`, `bg.cerita-layar-temanggung-04`, `bg.cerita-layar-temanggung-05`, `bg.cerita-layar-temanggung-06` |
| M-K1 | Magelang | Candi Borobudur saat fajar di atas dataran hijau. | `bg.cerita-kenal-magelang-01` |
| M-K2 | Magelang | Lorong relief Borobudur: deretan panel berukir berurutan. | `bg.cerita-kenal-magelang-02` |
| M-K3 | Magelang | Pandangan tinggi: Candi Mendut, Candi Pawon, dan Borobudur hampir segaris di dataran; Gunung Tidar di kejauhan. | `bg.cerita-kenal-magelang-03` |
| M-K4 | Magelang | Penari Topeng Ireng dengan kuluk bulu warna-warni di halaman desa. | `bg.cerita-kenal-magelang-04` |
| M-D1 | Magelang | Borobudur diselimuti Kabut Lupa; panel relief tampak berantakan urutannya. | `bg.cerita-dialog-magelang-01`, `bg.cerita-dialog-magelang-02`, `bg.cerita-dialog-magelang-03`, `bg.cerita-dialog-magelang-04` |
| M-D2 | Magelang | Pelataran di kaki candi: dua bapak berdebat dari kejauhan, kabut tipis. | `bg.cerita-dialog-magelang-05`, `bg.cerita-dialog-magelang-06`, `bg.cerita-dialog-magelang-07`, `bg.cerita-dialog-magelang-08` |
| M-D3 | Magelang | Bangku batu di bawah pohon besar dekat candi; kabut menebal, suasana redup. | `bg.cerita-dialog-magelang-09`, `bg.cerita-dialog-magelang-10`, `bg.cerita-dialog-magelang-11`, `bg.cerita-dialog-magelang-12`, `bg.cerita-dialog-magelang-13`, `bg.cerita-dialog-magelang-14` |
| M-D4 | Magelang | Kabut mulai tersibak dari dinding relief; cahaya keemasan menyinari panel. | `bg.cerita-dialog-magelang-15`, `bg.cerita-dialog-magelang-16` |
| M-T1 | Magelang | Hari cerah: papan informasi tanpa tulisan terbaca di pelataran; warga membaca relief bersama. | `bg.cerita-tuntas-magelang-01`, `bg.cerita-tuntas-magelang-02`, `bg.cerita-tuntas-magelang-03`, `bg.cerita-tuntas-magelang-04`, `bg.cerita-tuntas-magelang-05` |
| M-L1 | Magelang | Rumah baca di ujung jalan desa; kabut tipis keluar dari layar ponsel anak-anak di dalamnya. | `bg.cerita-layar-magelang-01`, `bg.cerita-layar-magelang-02`, `bg.cerita-layar-magelang-03`, `bg.cerita-layar-magelang-04`, `bg.cerita-layar-magelang-05`, `bg.cerita-layar-magelang-06` |
| W-K1 | Wonosobo | Dataran Tinggi Dieng di atas awan; kabut lewat di antara rumah. | `bg.cerita-kenal-wonosobo-01` |
| W-K2 | Wonosobo | Kompleks Candi Arjuna di lapangan rumput Dieng; Telaga Warna kehijauan di kejauhan. | `bg.cerita-kenal-wonosobo-02` |
| W-K3 | Wonosobo | Kebun teh Tambi di lereng; warung mi ongklok berasap hangat dengan keranjang bambu kecil. | `bg.cerita-kenal-wonosobo-03` |
| W-K4 | Wonosobo | Penari Topeng Lengger dan pemain bundengan di pendapa; kabut tebal di tepi gambar. | `bg.cerita-kenal-wonosobo-04` |
| W-D1 | Wonosobo | Pagi dingin di Dieng; Kabut Lupa tebal menyelimuti desa. | `bg.cerita-dialog-wonosobo-01`, `bg.cerita-dialog-wonosobo-02` |
| W-D2 | Wonosobo | Jalan desa: kentongan di pos ronda, warga bergegas sambil memegang ponsel; tanpa gambar bencana. | `bg.cerita-dialog-wonosobo-03`, `bg.cerita-dialog-wonosobo-04`, `bg.cerita-dialog-wonosobo-05`, `bg.cerita-dialog-wonosobo-06` |
| W-D3 | Wonosobo | Tepi Telaga Warna berkabut; batu datar di tepi air tempat Mbah menunggu. | `bg.cerita-dialog-wonosobo-07`, `bg.cerita-dialog-wonosobo-08`, `bg.cerita-dialog-wonosobo-09`, `bg.cerita-dialog-wonosobo-10`, `bg.cerita-dialog-wonosobo-11`, `bg.cerita-dialog-wonosobo-12`, `bg.cerita-dialog-wonosobo-13` |
| W-D4 | Wonosobo | Jalan setapak naik ke puncak berkabut; cahaya samar di atas. | `bg.cerita-dialog-wonosobo-14`, `bg.cerita-dialog-wonosobo-15`, `bg.cerita-dialog-wonosobo-16` |
| W-T1 | Wonosobo | Pagi cerah di Telaga Warna; warga tenang; kabut di puncak menipis. | `bg.cerita-tuntas-wonosobo-01`, `bg.cerita-tuntas-wonosobo-02`, `bg.cerita-tuntas-wonosobo-03`, `bg.cerita-tuntas-wonosobo-04`, `bg.cerita-tuntas-wonosobo-05` |
| W-L1 | Wonosobo | Tepi Telaga Warna berkabut; di desa seberang, layar-layar kecil di rumah dan sekolah memancarkan kabut tipis. | `bg.cerita-layar-wonosobo-01`, `bg.cerita-layar-wonosobo-02`, `bg.cerita-layar-wonosobo-03`, `bg.cerita-layar-wonosobo-04`, `bg.cerita-layar-wonosobo-05`, `bg.cerita-layar-wonosobo-06` |

### Temanggung

#### T-K1 · Di Antara Dua Gunung

Slot: `bg.cerita-kenal-temanggung-01`. Sindoro dan Sumbing berdampingan pada pagi cerah; mata air Umbul Jumprit di lereng.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-temanggung-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A clear early morning over Temanggung: Mount Sindoro and Mount Sumbing stand side by side like an elder and younger sibling, their peaks sharp against a pale gold sky; on the lower slope of Sindoro a small, clear spring pool surrounded by old trees and ferns, its water flowing into a stream; terraced fields in between, warm sunlight from the left.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-temanggung-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### T-K2 · Tanah Para Petani

Slot: `bg.cerita-kenal-temanggung-02`. Lereng subur: kebun kopi dan sayuran, petani bekerja, sungai jernih.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-temanggung-02
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. Fertile mountain slopes in late morning: farmers in wide bamboo hats tend coffee shrubs with red cherries and neat rows of vegetables (cabbage, chilli, spring onion) on dark volcanic soil; a clear, shallow river with smooth stones runs through the valley; mid-ground figures only, calm working mood.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-temanggung-02.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### T-K3 · Jejak Masa Lalu

Slot: `bg.cerita-kenal-temanggung-03`. Situs Liyangan: fondasi batu kuno yang tergali di lereng Sindoro; candi kecil di kejauhan.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-temanggung-03
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. An archaeological site on the slope of Mount Sindoro: ancient stone house foundations, terraces and a small stone stairway partly uncovered from layers of grey volcanic sand and gravel, with a few archaeologists working calmly with brushes; a small, plain Hindu temple of grey stone far in the background; soft afternoon light, sense of time and care.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-temanggung-03.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### T-K4 · Pesan Mbah

Slot: `bg.cerita-kenal-temanggung-04`. Pertunjukan kuda lumping di lapangan desa saat perayaan.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-temanggung-04
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A village celebration on a grass field: dancers ride flat woven-bamboo hobby horses (kuda kepang) painted in black, red and gold, in a calm, graceful moment (no trance, no whip, no fire); a small gamelan group under a bamboo shelter; villagers watching from a distance; festive bamboo flags without writing; warm golden light.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-temanggung-04.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### T-D1 · Bukit di atas desa berkabut

Slot: `bg.cerita-dialog-temanggung-01`, `bg.cerita-dialog-temanggung-02`, `bg.cerita-dialog-temanggung-03`, `bg.cerita-dialog-temanggung-04`, `bg.cerita-dialog-temanggung-05`. Senja dari bukit: desa dan ladang tertutup Kabut Lupa, lampu desa redup.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-temanggung-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. Dusk seen from a hilltop footpath above Temanggung: the village and its fields below are swallowed by the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary; only a few village lamps glow faintly through it; the footpath in front fades into the mist after a few steps; grey, hushed mood with a thin line of warm light on the horizon.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-temanggung-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### T-D2 · Persimpangan dan papan petunjuk

Slot: `bg.cerita-dialog-temanggung-06`, `bg.cerita-dialog-temanggung-07`, `bg.cerita-dialog-temanggung-08`, `bg.cerita-dialog-temanggung-09`, `bg.cerita-dialog-temanggung-10`, `bg.cerita-dialog-temanggung-11`. Persimpangan berkabut dengan papan petunjuk kayu tanpa tulisan terbaca; jalan kanan menuju jembatan bambu yang patah, jalan kiri aman.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-temanggung-06
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A foggy crossroads between fields: a weathered wooden signpost with two blank boards, one arrow-shaped board pointing right, the board surface misted and unreadable; the right-hand path leads to a small bamboo bridge over a river with a visibly broken middle section and a simple bamboo barrier; the left-hand path continues safely along a field edge; the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-temanggung-06.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### T-D3 · Kabut menipis

Slot: `bg.cerita-dialog-temanggung-12`, `bg.cerita-dialog-temanggung-13`, `bg.cerita-dialog-temanggung-14`, `bg.cerita-dialog-temanggung-15`. Kabut di papan menipis; di balik bukit sebuah cahaya keemasan berkilau.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-temanggung-12
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. The same foggy crossroads a little later: the mist around the wooden signpost has thinned, showing its clean blank boards; behind a nearby hill a small golden glint of light (a light shard) shines through the thinning grey mist; hopeful mood, warm light returning from the left.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-temanggung-12.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### T-T1 · Temanggung cerah lagi

Slot: `bg.cerita-tuntas-temanggung-01`, `bg.cerita-tuntas-temanggung-02`, `bg.cerita-tuntas-temanggung-03`, `bg.cerita-tuntas-temanggung-04`, `bg.cerita-tuntas-temanggung-05`. Pagi cerah: kabut tersibak, papan petunjuk bersih, warga berjalan pulang.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-tuntas-temanggung-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A bright, clear morning in Temanggung after the mist has lifted: the wooden signpost at the crossroads stands clean in the sun; villagers walk home along the safe path in small groups; Mount Sindoro and Mount Sumbing are visible again; a few wisps of grey mist dissolve into golden light; joyful, calm mood.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-tuntas-temanggung-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### T-L1 · Kabut di Layar (Temanggung)

Slot: `bg.cerita-layar-temanggung-01`, `bg.cerita-layar-temanggung-02`, `bg.cerita-layar-temanggung-03`, `bg.cerita-layar-temanggung-04`, `bg.cerita-layar-temanggung-05`, `bg.cerita-layar-temanggung-06`. Anak-anak duduk di bawah pohon besar memandang tablet; kabut tipis keluar dari layar.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-layar-temanggung-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. Three village children sit on a mat under a large shady tree at the edge of a field, an adult relative sitting nearby, looking down at a tablet; a thin ribbon of the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary curls out of the tablet screen (the screen shows no readable content); the rest of the scene is clear and sunny, so the mist on the screen stands out.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls. Do NOT show tobacco plants, cigarettes or smoking.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-layar-temanggung-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

### Magelang

#### M-K1 · Tanah Candi Agung

Slot: `bg.cerita-kenal-magelang-01`. Candi Borobudur saat fajar di atas dataran hijau.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-magelang-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. Borobudur Temple at dawn: the great stepped stone monument with its rows of stupas rises above the green Kedu plain, light mist in the palm trees, Mount Merapi and Mount Merbabu on the eastern horizon; match the real temple to a verified reference photo; no people climbing.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-magelang-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### M-K2 · Buku dari Batu

Slot: `bg.cerita-kenal-magelang-02`. Lorong relief Borobudur: deretan panel berukir berurutan.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-magelang-02
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A gallery corridor of Borobudur: a long row of carved stone relief panels on the wall, figures and trees in low relief following each other like pages of a book, warm side light raking across the carvings; faithful to real Borobudur reliefs, no invented scenes; no people.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-magelang-02.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### M-K3 · Tiga Candi Segaris

Slot: `bg.cerita-kenal-magelang-03`. Pandangan tinggi: Candi Mendut, Candi Pawon, dan Borobudur hampir segaris di dataran; Gunung Tidar di kejauhan.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-magelang-03
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A high, gentle aerial view over the Kedu plain: Mendut Temple, small Pawon Temple and Borobudur stand almost in one straight line among rice fields and villages, a faint golden line of light connecting them; the small forested hill of Mount Tidar in the distance; calm morning; accurate shapes of the three temples.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-magelang-03.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### M-K4 · Pesan Mbah

Slot: `bg.cerita-kenal-magelang-04`. Penari Topeng Ireng dengan kuluk bulu warna-warni di halaman desa.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-magelang-04
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A group of Topeng Ireng dancers performing in a village yard: tall colourful feathered headdresses (kuluk), sparkling costumes with bells on the ankles, energetic but orderly formation; villagers watching from the edges; bright daylight; no masks covering faces.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-magelang-04.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### M-D1 · Borobudur berkabut

Slot: `bg.cerita-dialog-magelang-01`, `bg.cerita-dialog-magelang-02`, `bg.cerita-dialog-magelang-03`, `bg.cerita-dialog-magelang-04`. Borobudur diselimuti Kabut Lupa; panel relief tampak berantakan urutannya.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-magelang-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. Borobudur Temple seen from its lower terrace, wrapped in the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary; on the nearest wall the carved relief panels look jumbled, as if some panels were out of place, with mist drifting between them; muted grey-gold light.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-magelang-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### M-D2 · Dua bapak bertengkar

Slot: `bg.cerita-dialog-magelang-05`, `bg.cerita-dialog-magelang-06`, `bg.cerita-dialog-magelang-07`, `bg.cerita-dialog-magelang-08`. Pelataran di kaki candi: dua bapak berdebat dari kejauhan, kabut tipis.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-magelang-05
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. The stone plaza at the foot of Borobudur in light mist: in the middle distance two adult men argue with raised hands, facing each other (small figures, no faces in detail); other visitors keep their distance; a few palm trees; tense but not violent mood.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-magelang-05.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### M-D3 · Tempat beristirahat

Slot: `bg.cerita-dialog-magelang-09`, `bg.cerita-dialog-magelang-10`, `bg.cerita-dialog-magelang-11`, `bg.cerita-dialog-magelang-12`, `bg.cerita-dialog-magelang-13`, `bg.cerita-dialog-magelang-14`. Bangku batu di bawah pohon besar dekat candi; kabut menebal, suasana redup.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-magelang-09
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A quiet resting place near the temple: an old stone bench under a large banyan tree, a woven bag and a walking stick leaning on it; the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary thickening around the trunks; dim, tender light, a single warm patch of sunlight on the bench.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-magelang-09.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### M-D4 · Relief kembali bercerita

Slot: `bg.cerita-dialog-magelang-15`, `bg.cerita-dialog-magelang-16`. Kabut mulai tersibak dari dinding relief; cahaya keemasan menyinari panel.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-magelang-15
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. The relief gallery as the mist begins to lift: golden light sweeps along the carved panels, which now stand in a neat order; the last grey wisps flow away; hopeful late-afternoon light.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-magelang-15.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### M-T1 · Membaca relief bersama

Slot: `bg.cerita-tuntas-magelang-01`, `bg.cerita-tuntas-magelang-02`, `bg.cerita-tuntas-magelang-03`, `bg.cerita-tuntas-magelang-04`, `bg.cerita-tuntas-magelang-05`. Hari cerah: papan informasi tanpa tulisan terbaca di pelataran; warga membaca relief bersama.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-tuntas-magelang-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A clear, sunny day at Borobudur: a wooden information board stands at the plaza (its text area is blank and unreadable); a small group of adult visitors, among them two men who were arguing before, now look at a relief panel together and point calmly; children with their parents nearby; warm, peaceful mood.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-tuntas-magelang-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### M-L1 · Ponsel yang Terus Berbunyi

Slot: `bg.cerita-layar-magelang-01`, `bg.cerita-layar-magelang-02`, `bg.cerita-layar-magelang-03`, `bg.cerita-layar-magelang-04`, `bg.cerita-layar-magelang-05`, `bg.cerita-layar-magelang-06`. Rumah baca di ujung jalan desa; kabut tipis keluar dari layar ponsel anak-anak di dalamnya.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-layar-magelang-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A small village reading house (rumah baca) with an open veranda at the end of a lane: shelves of books, a mat, and a few children sitting inside with an adult volunteer, holding phones; thin ribbons of the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary rise from the phone screens (no readable content); the lane in front is quiet in late-afternoon light.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-layar-magelang-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

### Wonosobo

#### W-K1 · Negeri di Atas Awan

Slot: `bg.cerita-kenal-wonosobo-01`. Dataran Tinggi Dieng di atas awan; kabut lewat di antara rumah.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-wonosobo-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. The Dieng Plateau above the clouds at sunrise: a highland village with zinc and clay-tile roofs on steep terraced fields, a sea of white clouds in the valley below, thin mist drifting between the houses; crisp, cool golden light.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-wonosobo-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### W-K2 · Candi, Telaga, dan Tradisi

Slot: `bg.cerita-kenal-wonosobo-02`. Kompleks Candi Arjuna di lapangan rumput Dieng; Telaga Warna kehijauan di kejauhan.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-wonosobo-02
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. The Arjuna Temple complex on the grassy Dieng field: a row of small, dark andesite Hindu temples, Candi Semar facing Candi Arjuna, with low hills of terraced potato fields behind; far in the distance a lake with green-turquoise water; a few visitors in jackets; match the real temples to a verified reference photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-wonosobo-02.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### W-K3 · Hangatnya Budaya Pegunungan

Slot: `bg.cerita-kenal-wonosobo-03`. Kebun teh Tambi di lereng; warung mi ongklok berasap hangat dengan keranjang bambu kecil.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-wonosobo-03
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A cool highland morning: rows of tea bushes on the slopes of the Tambi tea garden in the background; in front, a small roadside food stall with steaming pots of mie ongklok and small bamboo baskets (ongklok) hanging above the boiling water, jars of yellow carica sweets on a shelf; people in jackets warming their hands; no brand names.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-wonosobo-03.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### W-K4 · Pesan Mbah

Slot: `bg.cerita-kenal-wonosobo-04`. Penari Topeng Lengger dan pemain bundengan di pendapa; kabut tebal di tepi gambar.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-kenal-wonosobo-04
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A village pavilion (pendapa) performance: a Topeng Lengger dancer in a carved wooden mask and traditional costume, a musician playing a bundengan (a large curved woven-bamboo duck-herder's hood fitted with strings), a small audience; at the edges of the picture the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary is thicker than anywhere else.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-kenal-wonosobo-04.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### W-D1 · Dieng yang dingin

Slot: `bg.cerita-dialog-wonosobo-01`, `bg.cerita-dialog-wonosobo-02`. Pagi dingin di Dieng; Kabut Lupa tebal menyelimuti desa.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-wonosobo-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A cold early morning in a Dieng village: frost-pale grass, cypress trees, houses with zinc roofs half hidden in the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary, the thickest mist of the whole journey; breath-cold, hushed mood.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-wonosobo-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### W-D2 · Warga panik

Slot: `bg.cerita-dialog-wonosobo-03`, `bg.cerita-dialog-wonosobo-04`, `bg.cerita-dialog-wonosobo-05`, `bg.cerita-dialog-wonosobo-06`. Jalan desa: kentongan di pos ronda, warga bergegas sambil memegang ponsel; tanpa gambar bencana.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-wonosobo-03
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A village lane in Dieng: a wooden kentongan (slit drum) hangs at a small guard post; villagers hurry in small groups holding their phones (screens unreadable), some looking worried; the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary hangs between the houses; no disaster imagery at all: no crater, smoke, cracks, fallen houses or volcano.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-wonosobo-03.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### W-D3 · Tepi Telaga Warna

Slot: `bg.cerita-dialog-wonosobo-07`, `bg.cerita-dialog-wonosobo-08`, `bg.cerita-dialog-wonosobo-09`, `bg.cerita-dialog-wonosobo-10`, `bg.cerita-dialog-wonosobo-11`, `bg.cerita-dialog-wonosobo-12`, `bg.cerita-dialog-wonosobo-13`. Tepi Telaga Warna berkabut; batu datar di tepi air tempat Mbah menunggu.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-wonosobo-07
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. The shore of Telaga Warna in the mist: green-turquoise water with yellowish patches near the edge, a flat stone seat by the shore, reeds and cypress trees; the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary drifting over the lake; quiet, tender light.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-wonosobo-07.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### W-D4 · Jalan ke atas

Slot: `bg.cerita-dialog-wonosobo-14`, `bg.cerita-dialog-wonosobo-15`, `bg.cerita-dialog-wonosobo-16`. Jalan setapak naik ke puncak berkabut; cahaya samar di atas.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-dialog-wonosobo-14
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A stone footpath climbing a hillside from the lake toward a misty peak, wooden handrails, cypress trees; at the top a faint warm glow shines through the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary; courageous, hopeful mood.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-dialog-wonosobo-14.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### W-T1 · Dieng tenang lagi

Slot: `bg.cerita-tuntas-wonosobo-01`, `bg.cerita-tuntas-wonosobo-02`, `bg.cerita-tuntas-wonosobo-03`, `bg.cerita-tuntas-wonosobo-04`, `bg.cerita-tuntas-wonosobo-05`. Pagi cerah di Telaga Warna; warga tenang; kabut di puncak menipis.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-tuntas-wonosobo-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. A clear morning at Telaga Warna after the mist has thinned: the lake shines green and turquoise, villagers walk calmly along the shore and chat, the misty peak behind now shows only a few thin wisps dissolving into golden light; peaceful, warm mood.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-tuntas-wonosobo-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

#### W-L1 · Layar Terakhir

Slot: `bg.cerita-layar-wonosobo-01`, `bg.cerita-layar-wonosobo-02`, `bg.cerita-layar-wonosobo-03`, `bg.cerita-layar-wonosobo-04`, `bg.cerita-layar-wonosobo-05`, `bg.cerita-layar-wonosobo-06`. Tepi Telaga Warna berkabut; di desa seberang, layar-layar kecil di rumah dan sekolah memancarkan kabut tipis.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.cerita-layar-wonosobo-01
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: do NOT draw Jaka, Mbah Kedu or any narrator — the game places them over this background. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
wide 16:9 cinematic composition, main subject inside the central 75% of the width and the central 80% of the height, lower third calm and slightly darker for text panels, top 8% free of important detail, mid-tone exposure that stays readable under a dark navy overlay of 35% at the top to 75% at the bottom, no people in the foreground. The misty shore of Telaga Warna at dusk, seen from a different side than before: across the water a village and a small school building, where many tiny screens glow in windows and each releases a thin ribbon of the Mist of Forgetting: cold desaturated blue-grey mist (#8C97AA to #5B6478) drifting in soft layered bands, faint abstract wisps that never form readable letters, eerie but never scary (no readable content); the flat stone seat by the shore in front; calm but serious mood.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Story safety — show only what this brief names. Every sign, board, banner, page, phone, tablet or screen carries no readable letters, numbers, icons or app layout: blank boards, soft blurred shapes or a faint mist only. Nothing frightening: no disaster, smoke plume, eruption, cracked ground, injury or crying.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Complete subject within canvas, proportional scaling only. Opaque background matching the scene brief. Deliver WebP for raster assets, named bg.cerita-layar-wonosobo-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
```

## Daftar periksa hasil

Semua gambar:
- [ ] Tidak ada tulisan, angka, logo, atau isi layar yang terbaca.
- [ ] Tidak ada Jaka, Mbah Kedu, atau tokoh terkenal; orang tidak menatap kamera; anak-anak bersama orang dewasa.
- [ ] Bentuk candi, kostum, dan alat musik sesuai foto acuan; tidak ada unsur agama atau daerah lain.
- [ ] WebP, ukuran sesuai, nama berkas sama dengan kode aset.

Latar cerita:
- [ ] Sepertiga bawah cukup tenang untuk panel teks; gambar masih terbaca di bawah lapisan gelap.
- [ ] Hanya memuat yang disebut kalimat slide; tidak ada benda atau tanda bahaya yang diuji butir tantangan wilayah itu.
- [ ] Kabut Lupa berwarna dan berbentuk sama di semua gambar; tidak menakutkan.

Susun Gambar:
- [ ] Setiap area kisi punya penanda berbeda; tidak ada bidang polos, kabur, atau gelap.
- [ ] `TMG-P11`: tanpa gunung, kebun kopi, candi, kuda kepang, ikan, dan tulisan. `MGL-P05R`: tanpa stupa dan tanpa Borobudur.
- [ ] Lulus uji keping otomatis dan uji susun oleh dua orang; dicek ulang terhadap butir 08 sebelum dipasang.

## Kebijakan perubahan

- Prompt ini `draf pengembang`. Gambar yang dihasilkan juga `draf pengembang` sampai dicek pengembang; pemeriksaan budaya oleh pengamat budaya (B.7) tetap berlaku untuk objek budaya.
- Bila naskah 12 berubah (jumlah slide atau tempat), tabel slot di atas ikut diubah. Bila keterangan Susun Gambar di 08 berubah, prompt Susun Gambar dicek ulang.
- Setelah versi dikunci di G4, gambar yang sudah dipakai siswa tidak diganti selama pengambilan data utama.
