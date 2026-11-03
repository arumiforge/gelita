# Bank Soal GELITA — Kelas 5 SD/MI

*(Rewritten in English for the research bank of revision 10, 3 November 2026; sections below the image table are the old Indonesian text.)*

The research bank of revision 10: the items of [`08_DRAF_BANK_SOAL.md`](../revisi-10-analitik/08_DRAF_BANK_SOAL.md) with the English AI draft of [`36`](../revisi-10-analitik/36_TERJEMAHAN_BANK_SOAL.md), in the format of [`31_IMPLEMENTASI_BANK_SOAL_BARU.md`](../revisi-10-analitik/31_IMPLEMENTASI_BANK_SOAL_BARU.md) §6. **15 challenges** (5 per region), **116 digital literacy items** (Isi Rumpang 30, Benar atau Salah 32 + 6 anchor statements, Periksa Unggahan 24, Skenario Digital 24) and **12 PBK clues**, 18 decoy objects, 31 Susun Gambar pictures. Every item of 08 is `needs_verification` until expert validation, and its English text is marked "terjemahan belum ada" until the developer checks the AI draft. The old bank is archived in [`docs/arsip/bank-soal-lama/`](../arsip/bank-soal-lama/README.md).

| File | Content |
|---|---|
| [`gelita-bank-soal-kelas5.xlsx`](gelita-bank-soal-kelas5.xlsx) | import workbook: PETUNJUK, KAMUS_KOLOM, the data sheets below, DAFTAR_MEDIA. Imported by `DatabaseSeeder` and `php spark gelita:reset`. |
| [`data/temanggung.php`](data/temanggung.php), [`data/magelang.php`](data/magelang.php), [`data/wonosobo.php`](data/wonosobo.php) | the edited source, one region per file (31 K1) |
| [`build-workbook.php`](build-workbook.php) | builds the workbook from the data files, derives the error-code matrices, and runs every rule |
| [`tools/import_08.py`](tools/import_08.py), [`tools/old-bank-json.php`](tools/old-bank-json.php) | the converter that writes the data files from 08/36 and the old bank. Since 3 November 2026 08 is the source again: edit 08 (and 36), rerun the converter, rebuild the workbook. Hand edits to the data files are overwritten, so make every content change in 08/36 |

The workbook has no `library`/`library_media` sheets, so importing it never touches Pustaka Kedu.

## Challenges

| seq | Challenge | engine | Item types | Temanggung (mudah) | Magelang (sedang) | Wonosobo (sulit) |
|---:|---|---|---|---|---|---|
| 1 | Susun Gambar | `puzzle` | `puzzle_arrange` | 3 × 3, 11 variants (`a`–`k`) | 4 × 4, 10 variants | 4 × 4 jigsaw, 10 variants |
| 2 | Isi Rumpang | `rumpang` | `fill_blank_bank` | 4 blanks + 2 decoy words | 5 + 2 | 6 + 2 |
| 3 | Benar atau Salah | `boleh` | `verdict_card` | 4 statements (3 D1a, 1 D1b), key 2 : 2, + anchor card `x` (2) | 6 (3 D1a, 3 D1b), 3 : 3, + 2 | 6 (2 D1a, 4 D1b), 3 : 3, + 2 |
| 4 | Cari Objek | `cari` | `find_object` (round 1), `find_part` (round 2) | round 1 `x`: 4 clues + 4 decoys; round 2 `a`/`b`: 4 clues, 8 tap parts, 2 main lures | 4 + 6; 4, 10, 3 | 4 + 8; 4, 12, 3 |
| 5 | Skenario Digital | `skenario` (engine: part 6) | `scenario_choice` | 4 scenarios, 3 actions, 3 reasons | 4; 4, 4 | 4; 4, 5, two steps |

The fifth challenge's data is checked and in the workbook, but its rows are only written once part 6 installs the challenge in the game; until then the import warns and sets them aside.

## Rules per level

One source for the blueprint counts: [`app/Libraries/DifficultyRules.php`](../../app/Libraries/DifficultyRules.php) (31 §8.1). Bloom has one range per region (Temanggung C1–C3, Magelang C2–C4, Wonosobo C2–C5; [19](../revisi-10-analitik/19_TELAAH_LABEL_BLOOM.md)); AKM levels and text genres expected per region are there too. A revised blueprint changes one constant there; revised content only changes the data files (K2).

## Variants and booklets

- Every scored challenge has exactly variants `a` and `b`; `x` holds the items every booklet shares (the anchor card of Benar atau Salah, Cari Objek round 1) and joins every attempt (K11).
- **First attempt** of challenges 2–5: from the student's booklet B1–B4 when research mode (`research_studies.field_mode`, part 2) is on and the study is active (`App\Libraries\BookletRules`, table of 06). Without a booklet the challenge does not open (`BOOKLET_MISSING`, K10). Field mode off: random `a`/`b`. Susun Gambar is always random `a`/`b` (K12); `c`… are for retakes. Retakes take a variant not served yet. `challenge_attempts.variant_source` records `booklet`, `random`, or `retake`, and `booklet_at_open` the booklet (`App\Libraries\VariantPicker`).
- Three tries as before: one Cek per challenge for Isi Rumpang and Benar atau Salah (both cards in one Cek), one tap per clue in each Cari Objek round.

## Data file shape

Texts are pairs `[Indonesia, English]`. Per item: `key`, `type`, the labels `dim` (D1, D2, D3, PBK), `sub` (D1a, D1b), `akm` (L1–L3), `pirls`, `bloom`, and `labels` => `'expert'` (or `['akm' => 'expert']`) once the blind labelling replaces the developer labels; `review`.

- Isi Rumpang: segments with one `___` each; `'trap' => ['pasir' => 'INF-HUB']` on the blank a decoy word targets; `'special' => ['agar' => ['wnb-2-a02' => 'INF-HUB']]` for the "Kode khusus" of 08.
- Benar atau Salah: `'code' => 'INF-LIT'` per statement; `'passage'` with `genre` and `layout`; the node-level `'anchor' => ['passage' => …, 'items' => …]` becomes variant `x`.
- Cari Objek: `'round1' => ['items' => …]` (decoys with `'code' => 'INF-BUD'`); `'round2' => ['a' => ['scene' => …, 'parts' => […], 'items' => […]], 'b' => …]`; each part a box `x`, `y`, `w`, `h` in percent of the 16:9 scene (K8); each clue `answer` (part key), `clue_type` (ATR, REP, IZN, ETK), and `'main' => ['A1' => 'BUD-ATR', …]`.
- Skenario Digital: `'scenarios' => ['a' => [['key', 'topic', 'steps', 'situation', 'actions' => [[text, score, code, feedback]], 'reasons' => [[text, code]], 'explanation'], …]]`.
- Line markers of digital texts (decided here, 31 §21 no. 5): `>> ` header line (screen title, account, search result title), `~~ ` small meta line (byline, likes, "no maker"), `:: ` entry (comment, chat message); a blank line starts a new block.

The builder derives the full matrices from the three fixed rules of 08 (K6): another blank's answer → INF-REL, a decoy at its target blank → its `trap` code, a decoy at another blank → INF-LIT; round 1: a decoy → its code, another clue's target → INF-LIT; round 2: main lures → their code, every other part → the code of the clue type.

## Workbook sheets

Old sheets keep their columns; new columns and sheets (31 §6):

| Sheet | New columns |
|---|---|
| `passages` | `text_genre, layout` |
| `items` | `round, dimension, subaspect, akm_level, pirls_process, label_source, testlet_key, error_code, d3_clue_type, d2_topic` |
| `cloze_matrix` | `node_ref, variant_group, item_key, word_id, error_code` |
| `object_codes` | `item_key, object_key, error_code` |
| `post_scenes` | `node_ref, variant_group, title_id, title_en, text_genre, media_asset_key, media_label, media_note, reference_source` |
| `post_parts` | `node_ref, variant_group, part_key, kind, label_id, label_en, text_id, text_en, x, y, w, h` |
| `part_codes` | `item_key, part_key, error_code, main` |
| `scenarios` | `item_key, d2_topic, steps, explanation_id, explanation_en` |
| `scenario_options` | `item_key, kind, option_key, sequence, text_id, text_en, score, code, feedback_id, feedback_en` |
| `indicators` | `code, name_id, name_en, description_id, description_en, domain` |

## Checks

[`app/Libraries/BankRules.php`](../../app/Libraries/BankRules.php), used by the builder, the importer, `gelita:content:verify`, and the tests (`BankKelas5DataTest`, `BankRulesNewBankTest`).

**Errors** (no workbook / import refused): counts per variant and anchor (§8.1); balanced keys; exactly one error code per wrong choice, of the kind that fits, never `*-LAIN` or `NON-*`; scenario scores (one 2, one 1) and reason sets; every round-2 post has the four clue types and its keys; tap areas inside the scene, not overlapping, at least 24 dp apart in both rounds (`HuntGeometry`, K9); addresses only `.example`, phone numbers only `08xx-xxxx-xxxx`; references for every reading, card, post, and clue; labels; `round` only in `cari`, `x` only for anchors and round 1.

**Warnings** (errors with `--siap-penelitian`): a/b equivalence (genre, length ±15 %, sub-aspects and AKM levels, code spread), region code coverage, Bloom or AKM outside what the region expects, Susun Gambar captions a/b that contain a key of this region or a later one, fully alternating key order, English equal to Indonesian or still "terjemahan belum ada".

## Import and revisions

- `php spark gelita:reset` imports this workbook (development and tests); the production server gets the new bank only when the developer decides (K3).
- Revisions from validation: edit the data files → `php docs/bank-soal/build-workbook.php` → `php spark gelita:bank:import … --dry-run` → import → `php spark gelita:content:verify`. Error codes and labels may change after items were answered (stored tries keep their own code; the audit `content_import` counts changed codes); keys may not (including `find_part` keys and scenario action scores).
- Surface load (`challenge_items.surface_json`, `SurfaceLoad`) is computed at import and frozen with the bank.

## Gambar tantangan (daftar bank lama)

*(Note, 3 November 2026.)* This table lists the 66 slots of the old bank. The authoritative list for the new bank is the sheet DAFTAR_MEDIA of the workbook (67 slots: Susun Gambar 31 with the new `challenge.item.tmg-1-k01` Wayang Kedu, 3 scenes, 30 objects, 2 statement pictures, 1 new post photo `challenge.post.tmg-4-b`). Media keys did not change when the Susun Gambar letters were remapped.

Setiap slot langsung berisi **gambar contoh** (SVG bertulisan "GAMBAR CONTOH", `media_assets.is_placeholder = 1`) dari `php spark gelita:mockups`, agar sistem dapat diuji ujung ke ujung. Admin menggantinya satu per satu lewat **Konten → Gambar & mockup** (`/admin/media`) dengan kode berkas yang sama; gambar asli tidak pernah ditimpa gambar contoh. Karena gambar contoh menuliskan nama benda, Cari Objek dan pernyataan bergambar **belum layak untuk pengambilan data** sebelum gambar asli dipasang.

| Kunci | Nama | Wilayah · tantangan | Isi gambar asli | Ukuran |
|---|---|---|---|---|
| `challenge.item.tmg-1-01` | Gunung Sindoro dan Sumbing | TMG Susun Gambar | Sindoro (kanan) dan Sumbing (kiri) dilihat dari ladang bertingkat di Temanggung; awan khas dan burung di celah puncak; petani dan kebun kopi di latar depan | persegi 1200 × 1200 px |
| `challenge.item.tmg-1-03` | Candi Pringapus | TMG Susun Gambar | Candi Pringapus tampak depan | persegi 1200 × 1200 px |
| `challenge.item.tmg-1-04` | Kuda Lumping | TMG Susun Gambar | penari kuda lumping dengan kuda anyaman bambu berwarna | persegi 1200 × 1200 px |
| `challenge.item.tmg-1-d01` | Situs Liyangan | TMG Susun Gambar | situs ekskavasi di lereng Sindoro: talud batu bertingkat, kaki candi, jalan batu kuno, dinding galian berlapis, peneliti dewasa bekerja | persegi 1200 × 1200 px |
| `challenge.item.tmg-1-e01` | Prasasti Gondosuli | TMG Susun Gambar | situs Prasasti Gondosuli: batu andesit besar beraksara kuno di halaman situs, susunan pelindung dan pagar sesuai aslinya, batu-batu candi kuno di sekitarnya | persegi 1200 × 1200 px |
| `challenge.item.tmg-1-f01` | Umbul Jumprit | TMG Susun Gambar | mata air keluar dari bawah batu berlumut dan pohon besar, kolam jernih berkerikil, aliran awal Sungai Progo, pinus, monyet ekor panjang, gapura batu | persegi 1200 × 1200 px |
| `challenge.item.tmg-1-g01` | Panen Kopi di Lereng Sindoro–Sumbing | TMG Susun Gambar | panen kopi arabika di lereng bertingkat: petani dewasa memetik buah merah, pohon peneduh, buah dijemur di depan pondok, Sindoro di latar | persegi 1200 × 1200 px |
| `challenge.item.tmg-1-h01` | Ikan Uceng dan Sungai Jernih | TMG Susun Gambar | sungai pegunungan jernih berbatu, pencari ikan dengan serok kecil, ikan uceng kecil di atas kerikil, warung tepi sungai dengan uceng goreng | persegi 1200 × 1200 px |
| `challenge.item.tmg-1-i01` | Pasar Papringan | TMG Susun Gambar | pasar pagi di bawah rumpun bambu: lapak bambu, jajanan di tampah daun pisang, mainan kayu, pembeli membayar dengan keping bambu, tanpa plastik | persegi 1200 × 1200 px |
| `challenge.item.tmg-1-j01` | Perajin Kuda Kepang | TMG Susun Gambar | halaman bengkel perajin: menganyam dan mengecat kuda kepang, kuda jadi digantung, tumpukan bilah bambu, remaja siap berlatih, Sumbing di latar | persegi 1200 × 1200 px |
| `challenge.item.tmg-3-a03` | Butir padi hangus | TMG Benar atau Salah (gambar pernyataan) | butir padi hangus kehitaman (temuan arkeologi) | 480 × 360 |
| `challenge.item.tmg-3-b03` | Arca Buddha duduk | TMG Benar atau Salah (gambar pernyataan) | arca Buddha duduk dari batu (sengaja tidak sesuai bacaan) | 480 × 360 |
| `challenge.item.tmg-3-c03` | Biji kopi sangrai | TMG Benar atau Salah (gambar pernyataan) | biji kopi sangrai berwarna cokelat | 480 × 360 |
| `challenge.item.tmg-4-02` | Biji kopi | TMG Cari Objek (benda) | segenggam biji kopi | 240 × 240, latar transparan |
| `challenge.item.tmg-4-03` | Kuda kepang bambu | TMG Cari Objek (benda) | kuda kepang anyaman bambu berwarna (tanpa penari) | 240 × 240, latar transparan |
| `challenge.item.tmg-4-04` | Ikan uceng | TMG Cari Objek (benda) | beberapa ikan uceng kecil memanjang | 240 × 240, latar transparan |
| `challenge.item.tmg-4-06` | Arca Nandi | TMG Cari Objek (benda) | arca lembu Nandi duduk dari batu | 240 × 240, latar transparan |
| `challenge.item.tmg-4-09` | Angklung | TMG Cari Objek (benda) | angklung bambu | 240 × 240, latar transparan |
| `challenge.item.tmg-4-10` | Ondel-ondel | TMG Cari Objek (benda) | ondel-ondel kecil | 240 × 240, latar transparan |
| `challenge.item.tmg-4-11` | Perahu pinisi | TMG Cari Objek (benda) | perahu pinisi berlayar | 240 × 240, latar transparan |
| `challenge.item.tmg-4-12` | Sasando | TMG Cari Objek (benda) | sasando | 240 × 240, latar transparan |
| `challenge.tmg-4.scene` | Pasar Budaya Temanggung | TMG Cari Objek (adegan) | halaman balai desa/pasar budaya di kaki Sindoro–Sumbing, tiga meja panjang kosong berjajar ke depan (permukaan meja ± 47 %, 61 %, 76 % tinggi gambar) | 1280 × 720 |
| `challenge.item.mgl-1-01` | Stupa Borobudur | MGL Susun Gambar | teras bundar Borobudur saat fajar: stupa berlubang pada berbagai jarak, arca Buddha terlihat dari lubang stupa terdekat, stupa induk, Merapi dan Merbabu di cakrawala | persegi 1200 × 1200 px |
| `challenge.item.mgl-1-02` | Penari Topeng Ireng | MGL Susun Gambar | penari Topeng Ireng dengan kuluk bulu warna-warni | persegi 1200 × 1200 px |
| `challenge.item.mgl-1-03` | Candi Mendut | MGL Susun Gambar | Candi Mendut tampak depan dengan tangga masuk | persegi 1200 × 1200 px |
| `challenge.item.mgl-1-d01` | Relief Borobudur | MGL Susun Gambar | lorong galeri Borobudur: panel relief (kapal bercadik, orang di pantai, pohon, hewan) dengan cahaya samping, pemandu menunjuk tanpa menyentuh, dua anak mengamati | persegi 1200 × 1200 px |
| `challenge.item.mgl-1-e01` | Candi Pawon | MGL Susun Gambar | Candi Pawon di taman desa di antara sawah: tangga, panel berukir, stupa kecil di atap, rumah beratap merah, Perbukitan Menoreh di latar | persegi 1200 × 1200 px |
| `challenge.item.mgl-1-f01` | Gunung Tidar | MGL Susun Gambar | tangga batu mendaki di hutan Gunung Tidar, keluarga dan orang berolahraga, celah pohon memperlihatkan atap kota Magelang dan Gunung Sumbing | persegi 1200 × 1200 px |
| `challenge.item.mgl-1-g01` | Getuk Magelang | MGL Susun Gambar | lapak pasar dari sudut agak tinggi (bukan tampak atas): getuk tiga warna di tampah, singkong, lumpang dan alu, dandang beruap, latar pasar tajam | persegi 1200 × 1200 px |
| `challenge.item.mgl-1-h01` | Gerabah Klipoh | MGL Susun Gambar | halaman perajin gerabah Klipoh: membentuk tanah liat di alat putar tangan, kendil/cobek/kuali dijemur, pembakaran terbuka kecil, anggota keluarga belajar | persegi 1200 × 1200 px |
| `challenge.item.mgl-1-i01` | Perjalanan Waisak | MGL Susun Gambar | prosesi Waisak yang tenang di jalan desa: biksu membawa air suci dan api, umat berbaju putih dengan bunga teratai, bendera Buddhis, penonton tertib di balik tali, Borobudur di kejauhan | persegi 1200 × 1200 px |
| `challenge.item.mgl-1-j01` | Borobudur di Antara Dua Pasang Gunung Kembar | MGL Susun Gambar | fajar dari bukit di barat Borobudur: candi terlihat jelas di tengah, kabut tipis hanya di cekungan, Merbabu dan Merapi di cakrawala timur, dek pandang dengan pengunjung | persegi 1200 × 1200 px |
| `challenge.item.mgl-4-a01` | Stupa berlubang | MGL Cari Objek (benda) | stupa kecil berlubang (miniatur) | 240 × 240, latar transparan |
| `challenge.item.mgl-4-a02` | Kuluk Topeng Ireng | MGL Cari Objek (benda) | kuluk bulu warna-warni Topeng Ireng | 240 × 240, latar transparan |
| `challenge.item.mgl-4-a03` | Getuk | MGL Cari Objek (benda) | getuk tiga warna di atas daun pisang | 240 × 240, latar transparan |
| `challenge.item.mgl-4-a04` | Kendil gerabah | MGL Cari Objek (benda) | kendil gerabah cokelat kemerahan | 240 × 240, latar transparan |
| `challenge.item.mgl-4-a05` | Kuda kepang bambu | MGL Cari Objek (benda) | kuda kepang anyaman bambu | 240 × 240, latar transparan |
| `challenge.item.mgl-4-a06` | Manisan carica dalam stoples | MGL Cari Objek (benda) | stoples manisan carica | 240 × 240, latar transparan |
| `challenge.item.mgl-4-a07` | Dadak merak Reog | MGL Cari Objek (benda) | dadak merak Reog Ponorogo | 240 × 240, latar transparan |
| `challenge.item.mgl-4-a08` | Angklung | MGL Cari Objek (benda) | angklung bambu | 240 × 240, latar transparan |
| `challenge.item.mgl-4-a09` | Bakpia | MGL Cari Objek (benda) | beberapa bakpia | 240 × 240, latar transparan |
| `challenge.item.mgl-4-a10` | Panci logam | MGL Cari Objek (benda) | panci logam | 240 × 240, latar transparan |
| `challenge.mgl-4.scene` | Galeri Budaya Magelang | MGL Cari Objek (adegan) | galeri/pendapa budaya, tiga meja pajang kayu di kiri dan tiga di kanan dengan lorong lantai di tengah (permukaan meja ± 55 %, 60 %, 67 % tinggi gambar), stupa & Gunung Tidar di latar | 1280 × 720 |
| `challenge.item.wnb-1-05` | Telaga Warna | WNB Susun Gambar | Telaga Warna dari titik pandang tinggi: air hijau, biru, dan kekuningan berbelerang, Telaga Pengilon yang lebih gelap, hutan cemara, jalan papan dengan pengunjung kecil | persegi 1200 × 1200 px |
| `challenge.item.wnb-1-06` | Matahari Terbit di Sikunir | WNB Susun Gambar | fajar dari puncak Sikunir: lautan awan bertekstur (emas dekat matahari, ungu-biru menjauh), kerucut Sindoro menembus awan, pengunjung berjaket di balik pagar, anak memegang senter | persegi 1200 × 1200 px |
| `challenge.item.wnb-1-07` | Mi Ongklok | WNB Susun Gambar | warung mi ongklok dari sudut 3/4 (bukan tampak atas): mangkuk mi ongklok, sate sapi, tempe kemul, juru masak mencelup keranjang ongklok, jendela ke perbukitan berkabut | persegi 1200 × 1200 px |
| `challenge.item.wnb-1-d01` | Kompleks Candi Arjuna | WNB Susun Gambar | deretan candi Hindu kecil dari andesit gelap di lapangan rumput Dieng, Candi Semar menghadap Candi Arjuna, bukit berteras kentang, pengunjung berjaket | persegi 1200 × 1200 px |
| `challenge.item.wnb-1-e01` | Kawah di Dieng: Tetap di Balik Pagar | WNB Susun Gambar | kawah beruap di Dieng: kolam lumpur abu-abu, endapan belerang kuning, pagar pengaman dan jalan papan, rambu bergambar tanpa tulisan, petugas, pengunjung di balik pagar | persegi 1200 × 1200 px |
| `challenge.item.wnb-1-f01` | Carica, Pepaya Gunung | WNB Susun Gambar | kebun carica di teras Dieng: pohon carica berbuah kuning kecil bergerigi, petani berjaket memetik, teras rumah dengan buah dikupas dan stoples manisan carica | persegi 1200 × 1200 px |
| `challenge.item.wnb-1-g01` | Bundengan | WNB Susun Gambar | pemusik dewasa memetik bundengan di teras rumah, dua anak mendengarkan; di sawah, penggembala bebek memakai kowangan (tudung bambu) di punggung | persegi 1200 × 1200 px |
| `challenge.item.wnb-1-h01` | Kebun Teh Tambi | WNB Susun Gambar | kebun teh Tambi: deretan teh di lereng, pemetik dewasa bercaping dengan keranjang, pohon peneduh, truk kecil di jalan berkelok, bangunan pabrik lama, Sindoro di latar | persegi 1200 × 1200 px |
| `challenge.item.wnb-1-i01` | Tari Topeng Lengger | WNB Susun Gambar | pentas Topeng Lengger di pendapa desa pada sore hari: penari bertopeng kayu dan penari berkebaya dengan sampur, pemain gamelan, penonton berjaket, bukit berkabut | persegi 1200 × 1200 px |
| `challenge.item.wnb-1-j01` | Embun Upas | WNB Susun Gambar | pagi berembun beku di Dieng: es tipis putih menutupi sebagian rumput dan daun kentang, petani berjaket memeriksa tanaman, pengunjung memotret, siluet candi kecil di latar | persegi 1200 × 1200 px |
| `challenge.item.wnb-4-a01` | Bundengan | WNB Cari Objek (benda) | bundengan (tudung bambu lengkung bersenar) | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a02` | Buah carica | WNB Cari Objek (benda) | beberapa buah carica kuning kecil | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a03` | Keranjang ongklok | WNB Cari Objek (benda) | keranjang ongklok bambu kecil bergagang | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a04` | Daun teh | WNB Cari Objek (benda) | daun teh segar (pucuk) | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a05` | Pepaya biasa | WNB Cari Objek (benda) | pepaya biasa (besar) | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a06` | Rigen | WNB Cari Objek (benda) | rigen (anyaman bambu persegi panjang, kosong) | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a07` | Semangkuk mi ongklok | WNB Cari Objek (benda) | semangkuk mi ongklok | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a08` | Angklung | WNB Cari Objek (benda) | angklung bambu | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a09` | Sasando | WNB Cari Objek (benda) | sasando | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a10` | Biji kopi | WNB Cari Objek (benda) | segenggam biji kopi | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a11` | Kuluk Topeng Ireng | WNB Cari Objek (benda) | kuluk bulu Topeng Ireng | 240 × 240, latar transparan |
| `challenge.item.wnb-4-a12` | Getuk | WNB Cari Objek (benda) | getuk | 240 × 240, latar transparan |
| `challenge.wnb-4.scene` | Pasar Oleh-oleh Dieng | WNB Cari Objek (adegan) | pasar oleh-oleh Dieng berkabut tipis, rak bambu beratap, meja kayu, dan bangku bambu di depan (permukaan ± 43 %, 58 %, 75 % tinggi gambar), kebun teh & candi di latar | 1280 × 720 |

Daftar yang sama ada di sheet DAFTAR_MEDIA workbook (dengan kode soal pemakainya dan saran sumber).
