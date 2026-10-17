# Bank Soal GELITA — Kelas 5 SD/MI

Bank soal penelitian kelas 5, revisi Oktober 2026 (draf 2): **12 tantangan** (4 per wilayah), **151 baris soal** — 147 aktif (129 dinilai + 18 benda pengecoh) dan 4 diistirahatkan — **51 variasi** (Susun Gambar 10 per wilayah), 9 bacaan Benar atau Salah, 9 bacaan Isi Rumpang, 36 petunjuk, dan 15 kata pengecoh bank kata. Dasar isinya [`docs/perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md`](../perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md) §9, direvisi lewat [`gelita-katalog/update/`](../../gelita-katalog/update/catatan-revisi-gelita.md) (telaah dan catatan revisi). Soal yang diubah atau ditambahkan berstatus `needs_verification` sampai dicek peneliti.

| Berkas | Isi |
|---|---|
| [`gelita-bank-soal-kelas5.xlsx`](gelita-bank-soal-kelas5.xlsx) | workbook siap impor: sheet PETUNJUK, KAMUS_KOLOM, enam sheet data (`nodes`, `variants`, `distractors`, `passages`, `items`, `hints`), dan DAFTAR_MEDIA. Diimpor saat instalasi oleh `DatabaseSeeder` dan lagi oleh `php spark gelita:reset`. |
| [`data/temanggung.php`](data/temanggung.php), [`data/magelang.php`](data/magelang.php), [`data/wonosobo.php`](data/wonosobo.php) | sumber isi yang mudah ditinjau (satu wilayah per berkas, format di bawah) |
| [`build-workbook.php`](build-workbook.php) | penyusun workbook dari berkas data, termasuk seluruh pemeriksaan aturan |

Workbook ini **tidak** memuat sheet `library`/`library_media`, jadi impornya (termasuk lewat `gelita:reset`) tidak pernah menyentuh Pustaka Kedu. Isi Pustaka untuk pemasangan baru ada di [`docs/pustaka/`](../pustaka/README.md).

## Tantangan

Urutan sama di ketiga wilayah; wilayah terbuka berurutan (Temanggung → Magelang → Wonosobo), tantangan di dalam wilayah juga.

| seq | Tantangan | engine | Jenis soal | Temanggung (mudah) | Magelang (sedang) | Wonosobo (sulit) |
|---:|---|---|---|---|---|---|
| 1 | Susun Gambar | `puzzle` | `puzzle_arrange` | `tmg-1` · 3 × 3 kotak | `mgl-1` · 4 × 4 kotak | `wnb-1` · 4 × 4 jigsaw |
| 2 | Isi Rumpang | `rumpang` | `fill_blank_bank` | `tmg-2` · 3 rumpang, 1 pengecoh | `mgl-2` · 5 rumpang, 2 pengecoh | `wnb-2` · 7 rumpang, 2 pengecoh |
| 3 | Benar atau Salah | `boleh` | `verdict_card` | `tmg-3` · 1 paragraf, 3 pernyataan | `mgl-3` · 2 paragraf, 5 pernyataan | `wnb-3` · 3 paragraf, 6 pernyataan |
| 4 | Cari Objek | `cari` | `find_object` | `tmg-4` · 4 dicari + 4 pengecoh | `mgl-4` · 4 dicari + 6 pengecoh | `wnb-4` · 4 dicari + 8 pengecoh |

12 tantangan = 12 Serpihan Cahaya.

## Aturan per tingkat

Satu-satunya sumber aturan: [`app/Libraries/DifficultyRules.php`](../../app/Libraries/DifficultyRules.php). Taksonomi Bloom revisi (Anderson & Krathwohl): C1 Mengingat, C2 Memahami, C3 Menerapkan, C4 Menganalisis, C5 Mengevaluasi, C6 Mencipta. Penanda Bloom wajib untuk rumpang, pernyataan Benar atau Salah, dan benda yang dicari; Susun Gambar dan benda pengecoh tanpa Bloom.

| Tingkat | Isi Rumpang (Bloom) | Benar atau Salah (Bloom · keterampilan) | Cari Objek (Bloom petunjuk) |
|---|---|---|---|
| mudah | C1–C2 | C1–C2 · `informasi_tersurat`, `mengenali_fakta`, `mengenali_gambar` | C1–C2 |
| sedang | C2–C3 | C2–C4 · `gagasan_pokok`, `menghubungkan_informasi`, `mengklasifikasikan_tindakan`, `membedakan_fakta_pendapat` | C2–C3 |
| sulit | C3–C4 | C4–C5 · `membandingkan_informasi`, `menilai_pilihan`, `menentukan_solusi` | C3–C4 |

Jumlah rumpang dan pengecoh **tepat**, bukan rentang, agar ketiga variasi satu wilayah setara.

## Variasi dan pengacakan

- Satu **variasi** = satu kelompok soal yang dimainkan utuh dalam satu pengerjaan. Isi Rumpang dan Benar atau Salah punya tepat 3 variasi (`a`, `b`, `c`); Susun Gambar paling sedikit 3 dan paling banyak 10 (`a` sampai `j`; bank ini memakai 10); Cari Objek satu adegan tetap (`a`), karena posisi benda diatur sekali.
- Variasi dipilih acak sungguhan setiap pengerjaan baru. **Pengerjaan pertama** selalu memakai variasi `a`–`c` (set penelitian yang dinilai; `BankRules::FIRST_ATTEMPT_GROUPS`), jadi variasi Susun Gambar `d`–`j` hanya muncul pada pengerjaan ulang. Pengerjaan ulang memilih variasi yang belum pernah didapat siswa itu (bila semua sudah, semua boleh lagi). Muat ulang halaman memakai pengerjaan yang sama.
- Bank kata Isi Rumpang diacak dengan benih id pengerjaan (tetap saat dimuat ulang) dan urutan jawabannya selalu berbeda dari urutan rumpang (ketiga wilayah kini punya kata pengecoh). Urutan petunjuk Cari Objek juga diacak per pengerjaan. Pernyataan Benar atau Salah dan posisi benda tidak diacak.
- Setiap pengerjaan punya **3 kesempatan** (`Config\Gelita::$maxTries`): di Susun Gambar, Isi Rumpang, dan Benar atau Salah setiap **Cek jawabanku** memakai satu kesempatan untuk semua soal sekaligus (soal yang sudah benar terkunci); di Cari Objek setiap petunjuk punya 3 kesempatan sendiri. Bila kesempatan habis, jawaban terakhir disimpan apa adanya, kunci jawaban tidak ditampilkan, dan tantangan tetap selesai dengan serpihannya.

## Bentuk berkas data

Teks berpasangan `[Indonesia, English]`. Kode dibuat otomatis oleh `build-workbook.php` menurut urutan di berkas:

- kode soal `{node}-{variasi}{nn}` (mis. `tmg-2-a01`, `wnb-4-a12`), dua digit per variasi;
- kode bacaan Benar atau Salah `{node}-{variasi}` (mis. `mgl-3-a`);
- `sequence` soal = urutan global di tantangan (a01…, b01…, c01…);
- judul variasi Benar atau Salah = judul bacaannya.

Jangan menyisipkan atau menghapus soal di tengah variasi tanpa kode eksplisit: kode soal sesudahnya akan bergeser dan jawaban siswa yang tersimpan tidak tersambung lagi.

### Explicit keys, retired items, review status (draft 2)

Three optional item fields (`App\Libraries\BankSource`):

- `'key' => 'mgl-2-a06'`: an explicit item key. Use it when an item is inserted or moved inside a variant; the automatic key counts the item's position in the list, retired items included. The key must start with `{node}-{variant}`.
- `'active' => false`: a retired item. It stays in the workbook with `is_active = 0`, so stored answers keep their item, and gets sequence 91, 92, … per challenge. The rule checks skip it. Retire an item instead of deleting it. Draft 2 retires `mgl-2-a04`, `mgl-2-c04`, `wnb-2-c05`, and `wnb-2-c07`.
- `'review' => ['status' => 'needs_verification', 'note' => '…']`: overrides the default `verified` status and note. Revised items keep `needs_verification` until the researcher checks them; the importer and `gelita:content:verify` warn about each one.

Active items take their `sequence` from file order, so list them in play order.

```php
$ref = ['geo' => 'Pemkab Temanggung; BPS; PVMBG', /* … rujukan §9.4 … */];

return [
    'difficulty' => 'mudah',                       // sama dengan LevelSeeder
    'nodes' => [
        'tmg-1' => [
            'engine' => 'puzzle', 'variant' => 'susun_gambar', 'indicator' => 'budaya',
            'title' => [ID, EN], 'instruction' => [ID, EN], 'description' => [ID, EN],   // description = kartu misi
            'hints' => [[ID, EN], [ID, EN], [ID, EN]],
            'variants' => [
                'a' => [
                    'title' => [ID, EN], 'ref' => $ref['geo'],
                    'items' => [[
                        'type' => 'puzzle_arrange', 'prompt' => [keterangan ID, caption EN],
                        'media' => ['key' => 'challenge.item.tmg-1-01', 'label' => 'Gunung Sindoro dan Sumbing', 'note' => 'isi gambar asli', 'source' => 'saran sumber'],
                    ]],
                ],
            ],
        ],
        'tmg-2' => [   // Isi Rumpang
            'engine' => 'rumpang', 'variant' => 'rumpang_bacaan', 'indicator' => 'literasi', 'distractor_count' => 0,
            'variants' => ['a' => [
                'title' => [ID, EN], 'ref' => …, 'distractors' => [],          // [['enam', 'six'], …]
                'items' => [['type' => 'fill_blank_bank', 'bloom' => 'C1', 'prompt' => ['… Gunung ___.', '… Mount ___.'], 'answer' => ['Sumbing', 'Sumbing'], 'bloom_reason' => '…']],
            ]],
        ],
        'tmg-3' => [   // Benar atau Salah
            'engine' => 'boleh', 'variant' => 'benar_salah_bacaan', 'indicator' => 'literasi',
            'variants' => ['a' => [
                'passage' => ['title' => [ID, EN], 'body' => [ID, EN], 'ref' => …],   // paragraf dipisah "\n\n"
                'items' => [['type' => 'verdict_card', 'bloom' => 'C1', 'skill' => 'informasi_tersurat', 'answer' => 'benar', 'prompt' => [ID, EN],
                             'media' => [/* hanya pernyataan bergambar */]]],
            ]],
        ],
        'tmg-4' => [   // Cari Objek
            'engine' => 'cari', 'variant' => 'cari_objek', 'indicator' => 'budaya',
            'scene' => ['key' => 'challenge.tmg-4.scene', 'label' => …, 'note' => …],
            'variants' => ['a' => [
                'title' => [ID, EN], 'ref' => …,
                'items' => [
                    ['type' => 'find_object', 'bloom' => 'C1', 'x' => 84, 'y' => 6, 'w' => 12, 'prompt' => [petunjuk ID, clue EN], 'media' => […]],
                    ['type' => 'find_object', 'decoy' => true, 'x' => 74, 'y' => 40, 'w' => 12, 'prompt' => ['Angklung (pengecoh)', 'Angklung (decoy)'], 'wrong' => [ID, EN], 'media' => […]],
                ],
            ]],
        ],
    ],
];
```

- Potongan Isi Rumpang ditulis persis seperti disimpan: tepat satu `___` per potongan dan **tidak** di awal potongan (spasi sebelum rumpang hilang saat disambung). Jeda paragraf ditulis `\n\n` di tengah potongan **sesudah** `___`; importer memangkas tepi sel, jadi jeda di tepi akan hilang.
- `media.label` (nama pendek, ≤ 120 huruf) dan `media.note` (isi gambar asli) mengisi `media_assets.label`/`description` dan dipakai halaman Media serta gambar contoh. `bloom_reason` hanya catatan penyusun (tidak ikut workbook).
- Posisi benda Cari Objek dalam persen adegan 16:9: `x`, `y` dari kiri/atas, `w` lebar; tinggi benda = `w × 16/9`.

## Sheet workbook

| Sheet | Kolom |
|---|---|
| `nodes` | `node_ref, title_id, title_en, instruction_id, instruction_en, description_id, description_en, distractor_count, scene_media_key, scene_media_label, scene_media_note, background_media_key, variant_code, indicator` |
| `variants` | `node_ref, variant_group, title_id, title_en` |
| `distractors` | `node_ref, variant_group, text_id, text_en` |
| `passages` | `passage_key, level_code, title_id, title_en, body_id, body_en, media_asset_key, reference_source` |
| `items` | `item_key, node_ref, variant_group, sequence, interaction_type, indicator, bloom_level, skill, prompt_id, prompt_en, passage_key, answer_id, answer_en, x, y, w, decoy, wrong_feedback_id, wrong_feedback_en, media_asset_key, media_label, media_note, scorable, is_active, review_status, review_note, reference_source` |
| `hints` | `node_ref, item_key, sequence, text_id, text_en` |

Arti setiap kolom ada di sheet KAMUS_KOLOM dan di catatan header workbook. Soal yang tidak diubah berstatus `review_status = verified`; soal revisi Oktober 2026 berstatus `needs_verification`. Jenis soal selain empat di atas ditolak ("Jenis soal tidak dikenal.").

## Pemeriksaan

Satu sumber aturan, [`app/Libraries/BankRules.php`](../../app/Libraries/BankRules.php), dipakai `build-workbook.php`, importer, `php spark gelita:content:verify`, dan uji `BankKelas5DataTest`.

**Galat** (workbook tidak ditulis / impor ditolak / verifikasi gagal): tiap wilayah tepat 4 tantangan aktif berurutan; tepat 3 variasi untuk Isi Rumpang dan Benar atau Salah, 3–10 untuk Susun Gambar dengan `a`, `b`, `c` wajib ada, Cari Objek ≥ 1; Susun Gambar satu soal bergambar per variasi; Isi Rumpang jumlah rumpang sesuai tingkat, tepat satu `___` per potongan (ID dan EN), tidak diawali `___`, jawaban unik, jumlah pengecoh = `distractor_count`, pengecoh ≠ jawaban, tanpa kata dobel; Benar atau Salah satu bacaan per variasi dengan jumlah paragraf dan pernyataan sesuai tingkat, kunci `benar`/`salah`, keterampilan sah; Cari Objek 4 dicari + pengecoh sesuai tingkat, umpan balik pengecoh, semua benda bergambar, posisi di dalam 0–100 % dan tumpukan ≤ 20 %; Bloom wajib; `item_key` unik.

**Peringatan**: Bloom di luar rentang tingkat; variasi tanpa pernyataan `benar` atau tanpa `salah`; gambar masih gambar contoh — menjadi galat dengan `php spark gelita:content:verify --siap-penelitian`.

## Mengimpor dan mengubah isi

- Instalasi: `php spark db:seed DatabaseSeeder` mengimpor workbook ini (lewat `KontenKelas5Seeder`) lalu memasang gambar contoh. `php spark gelita:reset` (mengosongkan data uji coba sebelum penelitian) mengimpornya lagi.
- Impor manual: menu panel **Konten → Impor bank soal** (`/admin/konten/impor-bank`: unggah, pemeriksaan tanpa menulis, lalu **Simpan soal ke permainan**), atau `php spark gelita:bank:import docs/bank-soal/gelita-bank-soal-kelas5.xlsx --dry-run` lalu tanpa `--dry-run`. Impor ulang aman: data dicocokkan lewat kodenya. Kunci jawaban soal yang sudah dijawab siswa tidak dapat diubah.
- Mengubah isi: sunting berkas data, jalankan `php docs/bank-soal/build-workbook.php`, lalu commit workbook-nya. `BankWorkbookGuideTest` gagal bila workbook belum dibangun ulang.

## Gambar tantangan (66 slot)

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
