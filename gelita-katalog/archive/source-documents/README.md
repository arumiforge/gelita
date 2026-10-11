> ARSIP HISTORIS — bukan instruksi atau status aktif. Katalog telah tersedia; arahan kini 3D/Jaka syal merah, raster WebP, dan seluruh 273 status visual mengikuti keputusan pengguna, bukan audit baru.
> Isi asli berikut dipertahankan sebagai bukti. Klaim katalog belum dibangun, arahan 2D, lokasi file, serta tautan aplikasi/workbook dalam isi lama tidak berlaku untuk paket ini.
> Panduan: [README proyek](../../README.md), [konteks aktif](../../docs/CONTEXT.md), [progres aktif](../../docs/PROGRESS.md). Pembaruan penanda: 12 Oktober 2026.

# Bank Soal GELITA — Kelas 5 SD/MI

Bank soal final penelitian kelas 5 yang disetujui peneliti (Oktober 2026): **12 tantangan** (4 per wilayah), **126 soal** (108 dinilai + 18 benda pengecoh), **30 variasi**, 9 bacaan Benar atau Salah, 9 bacaan Isi Rumpang, 36 petunjuk, dan 12 kata pengecoh bank kata. Isinya ditetapkan di [`docs/perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md`](../perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md) §9; jangan diubah kecuali untuk memperbaiki salah ketik yang jelas.

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
| 2 | Isi Rumpang | `rumpang` | `fill_blank_bank` | `tmg-2` · 3 rumpang, 0 pengecoh | `mgl-2` · 5 rumpang, 2 pengecoh | `wnb-2` · 7 rumpang, 2 pengecoh |
| 3 | Benar atau Salah | `boleh` | `verdict_card` | `tmg-3` · 1 paragraf, 3 pernyataan | `mgl-3` · 2 paragraf, 5 pernyataan | `wnb-3` · 3 paragraf, 6 pernyataan |
| 4 | Cari Objek | `cari` | `find_object` | `tmg-4` · 4 dicari + 4 pengecoh | `mgl-4` · 4 dicari + 6 pengecoh | `wnb-4` · 4 dicari + 8 pengecoh |

12 tantangan = 12 Serpihan Cahaya.

## Aturan per tingkat

Satu-satunya sumber aturan: [`app/Libraries/DifficultyRules.php`](../../app/Libraries/DifficultyRules.php). Taksonomi Bloom revisi (Anderson & Krathwohl): C1 Mengingat, C2 Memahami, C3 Menerapkan, C4 Menganalisis, C5 Mengevaluasi, C6 Mencipta. Penanda Bloom wajib untuk rumpang, pernyataan Benar atau Salah, dan benda yang dicari; Susun Gambar dan benda pengecoh tanpa Bloom.

| Tingkat | Isi Rumpang (Bloom) | Benar atau Salah (Bloom · keterampilan) | Cari Objek (Bloom petunjuk) |
|---|---|---|---|
| mudah | C1–C2 | C1–C2 · `informasi_tersurat`, `mengenali_fakta`, `mengenali_gambar` | C1–C2 |
| sedang | C2–C3 | C2–C4 · `gagasan_pokok`, `menghubungkan_informasi`, `mengklasifikasikan_tindakan` | C2–C3 |
| sulit | C3–C4 | C4–C5 · `membandingkan_informasi`, `menilai_pilihan`, `menentukan_solusi` | C3–C4 |

Jumlah rumpang dan pengecoh **tepat**, bukan rentang, agar ketiga variasi satu wilayah setara.

## Variasi dan pengacakan

- Satu **variasi** = satu kelompok soal yang dimainkan utuh dalam satu pengerjaan. Susun Gambar, Isi Rumpang, dan Benar atau Salah punya tepat 3 variasi (`a`, `b`, `c`); Cari Objek satu adegan tetap (`a`), karena posisi benda diatur sekali.
- Variasi dipilih acak sungguhan setiap pengerjaan baru; pengerjaan ulang memilih variasi yang belum pernah didapat siswa itu (bila semua sudah, semua boleh lagi). Muat ulang halaman memakai pengerjaan yang sama.
- Bank kata Isi Rumpang diacak dengan benih id pengerjaan (tetap saat dimuat ulang) dan selalu berbeda dari urutan rumpang; di Temanggung (3 kata tanpa pengecoh) tidak ada kata yang berada di posisi rumpangnya sendiri. Urutan petunjuk Cari Objek juga diacak per pengerjaan. Pernyataan Benar atau Salah dan posisi benda tidak diacak.
- Setiap pengerjaan punya **3 kesempatan** (`Config\Gelita::$maxTries`): di Susun Gambar, Isi Rumpang, dan Benar atau Salah setiap **Cek jawabanku** memakai satu kesempatan untuk semua soal sekaligus (soal yang sudah benar terkunci); di Cari Objek setiap petunjuk punya 3 kesempatan sendiri. Bila kesempatan habis, jawaban terakhir disimpan apa adanya, kunci jawaban tidak ditampilkan, dan tantangan tetap selesai dengan serpihannya.

## Bentuk berkas data

Teks berpasangan `[Indonesia, English]`. Kode dibuat otomatis oleh `build-workbook.php` menurut urutan di berkas:

- kode soal `{node}-{variasi}{nn}` (mis. `tmg-2-a01`, `wnb-4-a12`), dua digit per variasi;
- kode bacaan Benar atau Salah `{node}-{variasi}` (mis. `mgl-3-a`);
- `sequence` soal = urutan global di tantangan (a01…, b01…, c01…);
- judul variasi Benar atau Salah = judul bacaannya.

Jangan menyisipkan atau menghapus soal di tengah variasi setelah bank dipakai siswa: kode soal sesudahnya akan bergeser dan jawaban siswa yang tersimpan tidak tersambung lagi.

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

Arti setiap kolom ada di sheet KAMUS_KOLOM dan di catatan header workbook. Semua soal `review_status = verified`. Jenis soal selain empat di atas ditolak ("Jenis soal tidak dikenal.").

## Pemeriksaan

Satu sumber aturan, [`app/Libraries/BankRules.php`](../../app/Libraries/BankRules.php), dipakai `build-workbook.php`, importer, `php spark gelita:content:verify`, dan uji `BankKelas5DataTest`.

**Galat** (workbook tidak ditulis / impor ditolak / verifikasi gagal): tiap wilayah tepat 4 tantangan aktif berurutan; tepat 3 variasi (Cari Objek ≥ 1); Susun Gambar satu soal bergambar per variasi; Isi Rumpang jumlah rumpang sesuai tingkat, tepat satu `___` per potongan (ID dan EN), tidak diawali `___`, jawaban unik, jumlah pengecoh = `distractor_count`, pengecoh ≠ jawaban, tanpa kata dobel; Benar atau Salah satu bacaan per variasi dengan jumlah paragraf dan pernyataan sesuai tingkat, kunci `benar`/`salah`, keterampilan sah; Cari Objek 4 dicari + pengecoh sesuai tingkat, umpan balik pengecoh, semua benda bergambar, posisi di dalam 0–100 % dan tumpukan ≤ 20 %; Bloom wajib; `item_key` unik.

**Peringatan**: Bloom di luar rentang tingkat; variasi tanpa pernyataan `benar` atau tanpa `salah`; gambar masih gambar contoh — menjadi galat dengan `php spark gelita:content:verify --siap-penelitian`.

## Mengimpor dan mengubah isi

- Instalasi: `php spark db:seed DatabaseSeeder` mengimpor workbook ini (lewat `KontenKelas5Seeder`) lalu memasang gambar contoh. `php spark gelita:reset` (mengosongkan data uji coba sebelum penelitian) mengimpornya lagi.
- Impor manual: menu panel **Konten → Impor bank soal** (`/admin/konten/impor-bank`: unggah, pemeriksaan tanpa menulis, lalu **Simpan soal ke permainan**), atau `php spark gelita:bank:import docs/bank-soal/gelita-bank-soal-kelas5.xlsx --dry-run` lalu tanpa `--dry-run`. Impor ulang aman: data dicocokkan lewat kodenya. Kunci jawaban soal yang sudah dijawab siswa tidak dapat diubah.
- Mengubah isi (hanya salah ketik yang jelas): sunting berkas data, jalankan `php docs/bank-soal/build-workbook.php`, lalu commit workbook-nya. `BankWorkbookGuideTest` gagal bila workbook belum dibangun ulang.

## Gambar tantangan (45 slot)

Setiap slot langsung berisi **gambar contoh** (SVG bertulisan "GAMBAR CONTOH", `media_assets.is_placeholder = 1`) dari `php spark gelita:mockups`, agar sistem dapat diuji ujung ke ujung. Admin menggantinya satu per satu lewat **Konten → Gambar & mockup** (`/admin/media`) dengan kode berkas yang sama; gambar asli tidak pernah ditimpa gambar contoh. Karena gambar contoh menuliskan nama benda, Cari Objek dan pernyataan bergambar **belum layak untuk pengambilan data** sebelum gambar asli dipasang.

| Kunci | Nama | Wilayah · tantangan | Isi gambar asli | Ukuran |
|---|---|---|---|---|
| `challenge.item.tmg-1-01` | Gunung Sindoro dan Sumbing | TMG Susun Gambar | lanskap Gunung Sindoro & Sumbing, langit cerah, kebun di kaki gunung | persegi ≥ 1200 px |
| `challenge.item.tmg-1-03` | Candi Pringapus | TMG Susun Gambar | Candi Pringapus tampak depan | persegi ≥ 1200 px |
| `challenge.item.tmg-1-04` | Kuda Lumping | TMG Susun Gambar | penari kuda lumping dengan kuda anyaman bambu berwarna | persegi ≥ 1200 px |
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
| `challenge.tmg-4.scene` | Pasar Budaya Temanggung | TMG Cari Objek (adegan) | halaman balai desa/pasar budaya di kaki Sindoro–Sumbing, tiga deret rak kosong (permukaan rak ± 27 %, 61 %, 93 % tinggi gambar) | 1280 × 720 |
| `challenge.item.mgl-1-01` | Stupa Borobudur | MGL Susun Gambar | stupa berlubang teras atas Borobudur, stupa induk di latar | persegi ≥ 1200 px |
| `challenge.item.mgl-1-02` | Penari Topeng Ireng | MGL Susun Gambar | penari Topeng Ireng dengan kuluk bulu warna-warni | persegi ≥ 1200 px |
| `challenge.item.mgl-1-03` | Candi Mendut | MGL Susun Gambar | Candi Mendut tampak depan dengan tangga masuk | persegi ≥ 1200 px |
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
| `challenge.mgl-4.scene` | Galeri Budaya Magelang | MGL Cari Objek (adegan) | galeri/pendapa budaya, dua deret meja pajang kayu, stupa & Gunung Tidar di latar | 1280 × 720 |
| `challenge.item.wnb-1-05` | Telaga Warna | WNB Susun Gambar | Telaga Warna hijau kebiruan | persegi ≥ 1200 px |
| `challenge.item.wnb-1-06` | Matahari Terbit di Sikunir | WNB Susun Gambar | matahari terbit dari puncak Sikunir, lautan awan | persegi ≥ 1200 px |
| `challenge.item.wnb-1-07` | Mi Ongklok | WNB Susun Gambar | semangkuk mi ongklok dengan sate sapi, tampak atas | persegi ≥ 1200 px |
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
| `challenge.wnb-4.scene` | Pasar Oleh-oleh Dieng | WNB Cari Objek (adegan) | pasar oleh-oleh Dieng berkabut tipis, tiga deret rak bambu/meja kayu, kebun teh & candi di latar | 1280 × 720 |

Daftar yang sama ada di sheet DAFTAR_MEDIA workbook (dengan kode soal pemakainya dan saran sumber).
