# Bank Soal Produksi GELITA

Isi lengkap kelima belas tantangan dan Pustaka Kedu untuk Temanggung, Magelang, dan Wonosobo, dalam bahasa Indonesia dan Inggris, siap diimpor lewat **Panel GELITA → Impor soal (Excel)** (juga dari halaman Konten permainan → *Impor soal dari Excel*).

| Berkas | Isi |
|---|---|
| [`gelita-bank-soal-produksi.xlsx`](gelita-bank-soal-produksi.xlsx) | workbook siap impor: sheet PETUNJUK, KAMUS_KOLOM, sepuluh sheet data, dan DAFTAR_MEDIA |
| [`data/temanggung.php`](data/temanggung.php), [`data/magelang.php`](data/magelang.php), [`data/wonosobo.php`](data/wonosobo.php) | sumber isi yang mudah ditinjau (satu wilayah per berkas) |
| [`build-workbook.php`](build-workbook.php) | penyusun workbook dari berkas data, beserta pemeriksaan aturan dasar |

Aturan penilaian (profil skoring) tidak diatur di workbook; tantangan memakai aturan bawaan penelitian.

## Isi

| Tantangan | Arena | Jenis butir | Aktif / semua | Per permainan |
|---|---|---|---:|---:|
| tmg-1 Susun Gambar Budaya Temanggung | puzzle | `puzzle_arrange`, papan 3 × 3 kotak | 3 / 4 | 1 |
| tmg-2 Lengkapi Bacaan: Gunung Kembar | rumpang | bacaan `fill_blank_bank`: 3 rumpang, tanpa pengecoh | 3 / 13 | semua (3) |
| tmg-3 Benar atau Salah? | boleh | `verdict_card` (benar/salah) | 16 / 16 | 8 |
| tmg-4 Temukan Budaya Temanggung | cari | `find_object` (8 target + 3 jebakan) | 11 / 11 | 4 |
| tmg-5 Temukan Jawabannya di Teks | pilihan | `single_choice` | 8 / 8 | 3 |
| mgl-1 Susun Gambar Magelang | puzzle | `puzzle_arrange`, papan 4 × 4 kotak | 3 / 7 | 1 |
| mgl-2 Lengkapi Bacaan: Candi Borobudur | rumpang | bacaan `fill_blank_bank`: 5 rumpang + 2 pengecoh | 5 / 15 | semua (5) |
| mgl-3 Benar, Salah, atau Pendapat? | boleh | `verdict_reason` (benar/salah/pendapat + alasan) | 12 / 12 | 6 |
| mgl-4 Cocokkan Fungsi & Asal | pilihan | `single_choice` | 8 / 8 | 4 |
| mgl-5 Bandingkan Gambar dan Teks | pilihan | `single_choice` | 6 / 6 | 3 |
| wnb-1 Susun Gambar Wonosobo | puzzle | `puzzle_arrange`, papan 4 × 4 jigsaw | 3 / 7 | 1 |
| wnb-2 Lengkapi Bacaan: Negeri di Atas Awan | rumpang | bacaan `fill_blank_bank`: 7 rumpang + 2 pengecoh | 7 / 17 | semua (7) |
| wnb-3 Sumber Mana yang Benar? | boleh | `verdict_card` dengan kartu sumber | 12 / 12 | 6 |
| wnb-4 Pilih Sumber Terpercaya | pilihan | `source_trust` | 8 / 8 | 4 |
| wnb-5 Apa yang Sebaiknya Kamu Lakukan? | pilihan | `single_choice` (pilar literasi digital) | 8 / 8 | 3 |

Jumlahnya 152 butir (113 aktif, 39 butir lama diistirahatkan), 152 opsi (masing-masing dengan umpan balik), 20 potongan urutan (milik butir wnb-1 lama), 24 kartu sumber, 55 petunjuk, 4 kata pengecoh, dan 11 teks bacaan. Di luar Susun Gambar dan Isi Rumpang, bank soal sengaja lebih besar dari jumlah per permainan agar siswa yang mengulang mendapat butir lain.

Jenjang kesulitan mengikuti urutan wilayah: Temanggung (**mudah**) melatih informasi **tersurat**, Magelang (**sedang**) **fakta vs pendapat** dan menghubungkan gambar–teks, Wonosobo (**sulit**) **menyimpulkan** dan **menilai sumber** (literasi keamanan digital).

## Pembaruan kelas 5 SD (Oktober 2026)

Sasaran penelitian kini siswa kelas 5 SD/MI. Susun Gambar dan Isi Rumpang disusun ulang per tingkat kesulitan wilayah. Aturannya ada di satu tempat, `app/Libraries/DifficultyRules.php`:

| Wilayah | Tingkat | Susun Gambar | Isi Rumpang | Tingkat berpikir (Bloom) |
|---|---|---|---|---|
| Temanggung | mudah | 3 × 3, keping kotak | 1 paragraf pendek, 3 rumpang, 3 kata tanpa pengecoh | C1–C2 (mengingat, memahami) |
| Magelang | sedang | 4 × 4, keping kotak | bacaan singkat, 4–5 rumpang, jawaban + 1–3 pengecoh (kini 5 + 2 = 7 kata) | C2–C3 (memahami, menerapkan) |
| Wonosobo | sulit | 4 × 4, keping jigsaw (bertonjolan, dipotong acak) | bacaan singkat, 6–7 rumpang, jawaban + 1–3 pengecoh (kini 7 + 2 = 9 kata) | C3–C4 (menerapkan, menganalisis) |

**Susun Gambar.** Setiap wilayah punya 3 gambar aktif; satu gambar diambil acak setiap kali bermain. Admin cukup mengunggah **satu gambar utuh**: permainan memotongnya sendiri menjadi keping 3 × 3, 4 × 4, atau jigsaw. Gambar yang tidak persegi dipotong bagian tengahnya. Keping tidak bernomor di semua tingkat. Keterangan gambar (kolom `prompt`) muncul setelah gambar utuh.

- Ukuran papan **dibekukan per butir** saat butir dibuat (impor atau editor), dari tingkat wilayahnya. Butir yang sudah ada tidak dihitung ulang, karena kunci jawaban butir yang sudah dijawab siswa tidak boleh berubah.
- Karena itu butir Magelang lama (3 × 3) diistirahatkan dan diganti butir baru `mgl-1-05` … `-07`. Butir baru memakai gambar yang sama (`challenge.item.mgl-1-01` … `-03`), jadi tidak perlu unggah ulang.
- Wonosobo tantangan 1 berganti dari *Susun Urutan* menjadi Susun Gambar jigsaw dengan 3 gambar baru (`wnb-1-05` … `-07`). Indikator tantangannya kini **budaya**. Kartu urutan lama diistirahatkan dan tetap tercatat dengan indikator semula (literasi/sikap).
- Butir tmg-1-02 (kebun tembakau) diistirahatkan karena kurang cocok untuk anak kelas 5.

**Isi Rumpang sebagai bacaan** (`reading` di berkas data, kolom `as_reading = 1` di workbook):

- Setiap butir `fill_blank_bank` adalah **satu potongan bacaan dengan tepat satu `___`**. Potongan-potongan disambung menurut urutannya menjadi bacaan utuh, dan **semua** butir aktif tampil sekaligus (`items_per_round` diisi sama dengan jumlah rumpang).
- Bank kata berisi semua jawaban ditambah `distractor_count` kata pengecoh pertama. Urutan kata diacak sekali per percobaan dan tetap sama saat halaman dimuat ulang.
- **Jeda paragraf** ditulis `\n\n` (baris kosong) **di tengah potongan, sesudah `___`**. Importer memangkas spasi dan baris kosong di tepi sel, jadi jeda di awal atau akhir potongan akan hilang.
- Setiap butir diberi tingkat Bloom (`bloom`/kolom `bloom_level`, C1–C6). Pemeriksaan memberi peringatan bila tingkatnya di luar anjuran wilayah.

**Mengistirahatkan butir, bukan menghapus.** Kode butir mengikuti urutan di berkas data, jadi butir lama tidak boleh dihapus. Beri `'active' => false`: workbook menulis `is_active = 0`, dan importer menonaktifkan butir itu tanpa mengubah kunci maupun jawaban siswa yang sudah tersimpan. Sel `is_active` yang kosong tidak mengubah status butir yang sudah ada.

Draf ketiga bacaan berstatus **`needs_verification`**. Tinjau bahasa, kunci, dan tingkat Bloom-nya bersama guru, lalu ubah menjadi `verified`.

### Tingkat Bloom per rumpang

**tmg-2 — Gunung Kembar** (C1–C2)

| No | Jawaban | Bloom | Alasan |
|---|---|---|---|
| 1 | Sumbing | C1 Mengingat | Nama gunung sudah dikenalkan di Pustaka dan Susun Gambar tmg-1. |
| 2 | kembar | C2 Memahami | Disimpulkan dari petunjuk "bentuknya mirip". |
| 3 | subur | C2 Memahami | Dijelaskan oleh sebab "berasal dari abu gunung api" dan akibat "dapat menanam". |

**mgl-2 — Candi Borobudur** (C2–C3; pengecoh *enam*, *arca*)

| No | Jawaban | Bloom | Alasan |
|---|---|---|---|
| 1 | sembilan | C3 Menerapkan | Menjumlahkan 6 + 3 dari bacaan; pengecoh *enam* menjebak pembaca yang tergesa. |
| 2 | stupa | C3 Menerapkan | Menerapkan definisi stupa pada bangunan di puncak. |
| 3 | relief | C2 Memahami | Istilah dimaknai dari penjelasan "pahatan gambar yang bercerita". |
| 4 | dinding | C3 Menerapkan | Memakai informasi letak relief untuk tugas baru: di mana harus melihat. |
| 5 | dilarang | C2 Memahami | Memahami hubungan sebab–akibat (batu rusak → dilarang). |

**wnb-2 — Negeri di Atas Awan** (C3–C4; pengecoh *panas*, *agar*)

| No | Jawaban | Bloom | Alasan |
|---|---|---|---|
| 1 | sehingga | C4 Menganalisis | Menentukan arah hubungan sebab → akibat; *karena* di sini membalik sebabnya. |
| 2 | karena | C4 Menganalisis | Hubungan akibat ← sebab. |
| 3 | hangat | C3 Menerapkan | Informasi suhu dipakai untuk keputusan baru (bekal pakaian). |
| 4 | tetapi | C4 Menganalisis | Hubungan pertentangan dua keadaan. |
| 5 | dapat | C3 Menerapkan | Menerapkan rentang 1.400–2.400 m pada ketinggian 2.300 m. |
| 6 | sulit | C3 Menerapkan | Pantai = dataran rendah panas; aturan dipakai pada kasus baru. |
| 7 | sejuk | C4 Menganalisis | Kesimpulan dari beberapa kalimat; pengecoh *panas* menguji ketelitian. |

Setiap pengecoh sudah dicek tidak cocok untuk rumpang mana pun, dan setiap jawaban hanya cocok di satu tempat. `tests/unit/BankReadingDataTest.php` memastikan ketiga bacaan memenuhi aturan wilayahnya tanpa peringatan.

Pustaka Kedu: 10 halaman per wilayah (sejarah, alam, candi, tradisi, kuliner, dan satu halaman "Tips Jaka" yang terkait keterampilan membaca wilayah itu), masing-masing dengan catatan sumber dan 1–3 gambar/video. Media Pustaka memakai tautan Wikimedia Commons (berlisensi bebas; kreditnya di kolom `credit`) dan beberapa video YouTube dari kanal resmi atau lembaga.

## Status tinjauan

Semua butir diimpor berstatus **`draft`**, bukan `verified`. Fakta di setiap butir sudah dicocokkan dengan sumber di kolom `reference_source`, tetapi `verified` berarti sudah diperiksa guru atau ahli. Status ini tidak memengaruhi apakah butir dimainkan. Setelah meninjau, ubah statusnya di editor butir atau di kolom `review_status` lalu impor ulang.

## Mengimpor

1. Unggah gambar di Panel GELITA → **Gambar & suara** sesuai sheet **DAFTAR_MEDIA** (boleh dilakukan setelah impor; lihat bagian berikut).
2. Panel GELITA → **Impor soal (Excel)** → pilih `gelita-bank-soal-produksi.xlsx` → **Periksa berkas**. Hasil yang diharapkan: 0 kesalahan.
3. **Simpan soal ke permainan**. Seluruh isi masuk dalam satu transaksi: satu baris gagal, tidak ada yang berubah.
4. **Konten permainan → Periksa kelengkapan**, lalu mainkan tiap wilayah sebagai siswa.

Lewat server:

```bash
php spark gelita:bank:import docs/bank-soal/gelita-bank-soal-produksi.xlsx --dry-run
php spark gelita:bank:import docs/bank-soal/gelita-bank-soal-produksi.xlsx
php spark gelita:content:verify
```

Impor ulang aman: baris dicocokkan lewat kode (`node_ref`, `passage_key`, `item_key`, `option_key`, nomor halaman Pustaka) lalu diperbarui. Media Pustaka sebuah halaman diganti seluruhnya oleh baris `library_media` halaman itu. Kunci jawaban butir yang **sudah dijawab siswa** tidak dapat diubah — buat butir baru dengan kode baru, lalu istirahatkan butir lama dengan `is_active = 0`.

Di server pengembangan, `SampleItemSeeder` sudah mengisi butir contoh per tantangan (kode berakhiran `-91` ke atas, mis. `tmg-2-91`). Butir contoh itu tidak dihapus oleh impor; nonaktifkan di editor butir bila tidak diinginkan. Pada bacaan Isi Rumpang, butir contoh yang masih aktif ikut tersambung ke bacaan.

## Gambar yang harus diunggah

Tantangan puzzle dan cari objek butuh gambar sungguhan. Impor membuat **slot kosong** untuk setiap `asset_key` (di panel disebut *kode berkas*) yang belum terdaftar, dan halaman Gambar & suara menandai slot yang masih kosong. Begitu berkas diunggah dengan kode berkas yang sama, gambar langsung tampil.

Untuk Susun Gambar, unggah gambar utuh; kepingnya dibuat otomatis. Gambar persegi paling rapi. Gambar yang tidak persegi dipotong bagian tengahnya.

| asset_key | Dipakai di | Ukuran |
|---|---|---|
| `challenge.item.tmg-1-01`, `-03`, `-04` | Susun Gambar tmg-1, 3 × 3 kotak (Sindoro–Sumbing, Candi Pringapus, kuda lumping) | persegi, minimal 900 × 900 px |
| `challenge.item.mgl-1-01` … `-03` | Susun Gambar mgl-1, 4 × 4 kotak, dipakai butir `mgl-1-05` … `-07` (stupa Borobudur, Topeng Ireng, Candi Mendut) | persegi, minimal 1200 × 1200 px |
| `challenge.item.wnb-1-05` … `-07` | Susun Gambar wnb-1, 4 × 4 jigsaw (Telaga Warna, matahari terbit Bukit Sikunir, mi ongklok) — **gambar baru** | persegi, minimal 1200 × 1200 px |
| `challenge.tmg-4.scene` | adegan cari objek tmg-4 (balai desa/pasar budaya di kaki Sindoro–Sumbing) | 1280 × 720 px |
| `challenge.item.tmg-4-01` … `-08` | 8 objek budaya Temanggung yang dicari | 240 × 240 px, latar transparan |
| `challenge.item.tmg-4-09` … `-11` | 3 objek jebakan dari daerah lain (angklung, ondel-ondel, pinisi) | 240 × 240 px, latar transparan |

Isi setiap gambar dan saran sumbernya dirinci di sheet DAFTAR_MEDIA. Selama gambar belum diunggah, puzzle menampilkan gambar pengganti dan objek cari tampil sama rupa — tantangan tetap dapat dimainkan tetapi belum layak untuk studi.

mgl-5 tidak butuh berkas: setiap butir memuat deskripsi gambar dalam teks sumbernya. Gambar pendamping per butir tetap dapat ditambahkan lewat editor butir.

**Sekolah tanpa internet.** Gambar Commons dan video YouTube di Pustaka dimuat dari internet oleh browser siswa. Untuk kelas luring, unduh berkasnya (patuhi lisensi di kolom `credit`), unggah di Panel GELITA → Gambar & suara, lalu di editor Pustaka ganti sumber media dari *Tautan* ke *Berkas*.

## Mengubah isi

Ada dua cara, dan importer menerima keduanya:

- **Sunting workbook langsung di Excel.** Cocok untuk guru; panduan kolom ada di sheet PETUNJUK dan KAMUS_KOLOM.
- **Sunting berkas data lalu bangun ulang** — cocok untuk tinjauan lewat git:

  ```bash
  php docs/bank-soal/build-workbook.php            # menimpa gelita-bank-soal-produksi.xlsx
  php docs/bank-soal/build-workbook.php keluar.xlsx
  ```

  Bila ada aturan yang dilanggar, skrip berhenti tanpa menulis workbook dan menyebut butir yang salah.

`tests/unit/BankWorkbookGuideTest.php` gagal bila kolom impor berubah tetapi workbook ini belum dibangun ulang.

### Bentuk berkas data

Setiap berkas `data/*.php` mengembalikan array `passages`, `nodes`, dan `library`. Teks dwibahasa ditulis berpasangan `['Indonesia', 'English']`.

```php
'difficulty' => 'sedang',                          // tingkat wilayah: mudah | sedang | sulit (sama dengan LevelSeeder)
'nodes' => [
    'mgl-2' => [
        'engine'          => 'rumpang',            // puzzle | rumpang | boleh | pilihan | cari
        'variant'         => 'rumpang_bacaan',     // kode variasi tampilan
        'indicator'       => 'literasi',           // indikator tantangan, juga bawaan butir: literasi | budaya | sikap
        'items_per_round' => 5,                    // bacaan: sama dengan jumlah rumpang aktif
        'reading'         => true,                 // rumpang: semua butir tampil sebagai satu bacaan
        'use_word_bank'   => true,                 // rumpang
        'distractor_count'=> 2,                    // rumpang bank kata; 0 = tanpa pengecoh
        'verdict_options' => 'benar,salah',        // boleh
        'require_reason'  => true,                 // boleh dengan alasan
        'scene'           => ['key' => 'challenge.tmg-4.scene', 'note' => 'isi gambar', 'source' => 'saran sumber'],  // cari
        'title' => [...], 'instruction' => [...], 'description' => [...],
        'hints'       => [['petunjuk', 'hint'], ...],
        'distractors' => [['enam', 'six'], ...],
        'note' => 'catatan tinjauan', 'ref' => 'sumber rujukan',
        'items' => [ /* lihat di bawah */ ],
    ],
],
```

Kunci butir di dalam `items`:

| Kunci | Dipakai oleh | Isi |
|---|---|---|
| `type` | semua | jenis interaksi, mis. `fill_blank_bank` |
| `active` | semua | `false` = butir diistirahatkan (`is_active = 0`); kosong = aktif |
| `bloom` | opsional, wajib untuk bacaan | tingkat Bloom `C1` … `C6` |
| `prompt` | semua | pertanyaan/kalimat; rumpang wajib memuat `___` di kedua bahasa (bacaan: tepat satu) |
| `answer` | rumpang, boleh | `['Sumbing', 'Sumbing']` untuk rumpang; `'benar'`/`'salah'`/`'pendapat'` untuk boleh |
| `order` | `ordering` | urutan kode potongan, mis. `'b,a,d,c'` |
| `source`, `passage` | opsional | teks sumber di atas soal; kode bacaan dari `passages` |
| `options` | pilihan | `[label_id, label_en, benar?, umpan_balik_id, umpan_balik_en]` × 4, tepat satu benar |
| `pieces` | `ordering` | `[kode, teks_id, teks_en]` |
| `sources` | boleh/`source_trust` | `[label_id, label_en, kind, teks_id, teks_en]`, `kind` = `official` \| `anonymous` \| `chain_message` \| `blog` |
| `reason` | `verdict_reason` | contoh alasan yang baik |
| `x`, `y`, `w`, `decoy`, `wrong` | cari | posisi & lebar objek dalam persen adegan; jebakan wajib punya umpan balik `wrong` |
| `media` | puzzle, cari | `['key' => asset_key, 'note' => isi gambar, 'source' => saran sumber]` → masuk DAFTAR_MEDIA (hanya butir aktif). Butir Susun Gambar tidak diberi `answer`: kuncinya dibuat importer dari ukuran papan wilayah |
| `hints` | opsional | petunjuk khusus butir |
| `pillar` | opsional | pilar literasi digital (`digital_skills` \| `digital_ethics` \| `digital_safety` \| `digital_culture`) |
| `indicator`, `ref`, `note`, `status` | opsional | menimpa nilai bawaan tantangan |

Kode butir (`tmg-2-01`, …) dan opsi (`…-a` … `-d`) diberikan otomatis **sesuai urutan di berkas**. Setelah bank soal dipakai siswa, jangan menyisipkan atau menghapus butir di tengah daftar — tambahkan di akhir dan istirahatkan yang lama (`'active' => false`), agar kode butir lama tidak bergeser.

Halaman Pustaka:

```php
'library' => [
    [
        'title' => ['Judul', 'Title'],
        'body'  => ["Paragraf …\n\n## Subjudul\n- butir\n> Tahukah kamu? …\nSumber: …", "…"],
        'media' => [
            ['kind' => 'image', 'url' => 'https://commons.wikimedia.org/wiki/File:….jpg',
             'caption' => ['Keterangan', 'Caption'], 'credit' => 'Penulis, CC BY-SA 4.0 via Wikimedia Commons'],
            ['kind' => 'video', 'url' => 'https://www.youtube.com/watch?v=…', 'caption' => [...], 'credit' => 'Kanal …'],
        ],
    ],
],
```

Format isi Pustaka: baris kosong = paragraf baru, `## ` subjudul, `- ` daftar, `> ` kotak "Tahukah kamu?", baris berawalan `Sumber:` menjadi catatan rujukan, `**tebal**`, dan `*miring*` untuk istilah daerah/asing. HTML tidak pernah dirender.

Pemeriksaan yang dijalankan `build-workbook.php` sebelum menulis (butir yang diistirahatkan hanya diperiksa kode Bloom-nya):

- pilihan ganda: tepat 4 opsi dengan satu jawaban benar;
- rumpang: kalimat ID dan EN memuat `___`;
- rumpang bank kata: jawaban tidak kembar dalam satu tantangan, pengecoh tidak sama dengan jawaban mana pun, dan jumlah pengecoh ≥ `distractor_count`;
- bacaan: tepat satu `___` per potongan, `items_per_round` sama dengan jumlah rumpang, serta jumlah rumpang, pengecoh, dan tingkat Bloom sesuai tingkat wilayah (yang terakhir ini peringatan, bukan galat);
- Susun Gambar: `items_per_round` = 1;
- boleh: kunci termasuk `verdict_options`;
- `ordering`: urutan memuat setiap kode potongan tepat sekali;
- cari: setiap jebakan punya umpan balik;
- setiap tantangan punya butir target aktif ≥ `items_per_round`, dan `item_key` unik lintas wilayah.
