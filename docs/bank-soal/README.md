# Bank Soal Produksi GELITA

Isi lengkap kelima belas tantangan dan Pustaka Kedu untuk Temanggung, Magelang, dan Wonosobo, dalam bahasa Indonesia dan Inggris, siap diimpor lewat **Panel Admin → Konten → Impor bank soal**.

| Berkas | Isi |
|---|---|
| [`gelita-bank-soal-produksi.xlsx`](gelita-bank-soal-produksi.xlsx) | workbook siap impor: sheet PETUNJUK, KAMUS_KOLOM, sepuluh sheet data, dan DAFTAR_MEDIA |
| [`data/temanggung.php`](data/temanggung.php), [`data/magelang.php`](data/magelang.php), [`data/wonosobo.php`](data/wonosobo.php) | sumber isi yang mudah ditinjau (satu wilayah per berkas) |
| [`build-workbook.php`](build-workbook.php) | penyusun workbook dari berkas data, beserta pemeriksaan aturan dasar |

Profil skoring tidak diatur di workbook; tantangan memakai profil bawaan studi.

## Isi

| Tantangan | Arena | Jenis butir | Bank | Per ronde |
|---|---|---|---:|---:|
| tmg-1 Susun Gambar Budaya Temanggung | puzzle | `puzzle_arrange` | 4 | 1 |
| tmg-2 Lengkapi Kalimat Temanggung | rumpang | `fill_blank_bank` + 7 pengecoh | 10 | 4 |
| tmg-3 Benar atau Salah? | boleh | `verdict_card` (benar/salah) | 16 | 8 |
| tmg-4 Temukan Budaya Temanggung | cari | `find_object` (8 target + 3 jebakan) | 11 | 4 |
| tmg-5 Temukan Jawabannya di Teks | pilihan | `single_choice` | 8 | 3 |
| mgl-1 Susun Gambar Magelang | puzzle | `puzzle_arrange` | 4 | 1 |
| mgl-2 Ketik Jawabanmu | rumpang | `fill_blank_free` | 10 | 4 |
| mgl-3 Benar, Salah, atau Pendapat? | boleh | `verdict_reason` (benar/salah/pendapat + alasan) | 12 | 6 |
| mgl-4 Cocokkan Fungsi & Asal | pilihan | `single_choice` | 8 | 4 |
| mgl-5 Bandingkan Gambar dan Teks | pilihan | `single_choice` | 6 | 3 |
| wnb-1 Susun Urutan yang Benar | puzzle | `ordering` | 4 | 1 |
| wnb-2 Lengkapi Kesimpulan | rumpang | `fill_blank_bank` + 7 pengecoh | 10 | 4 |
| wnb-3 Sumber Mana yang Benar? | boleh | `verdict_card` dengan kartu sumber | 12 | 6 |
| wnb-4 Pilih Sumber Terpercaya | pilihan | `source_trust` | 8 | 4 |
| wnb-5 Apa yang Sebaiknya Kamu Lakukan? | pilihan | `single_choice` (pilar literasi digital) | 8 | 3 |

Jumlahnya 131 butir, 152 opsi (masing-masing dengan umpan balik), 20 potongan urutan, 24 kartu sumber, 55 petunjuk, 14 kata pengecoh, dan 11 teks bacaan. Bank soal sengaja lebih besar dari jumlah per ronde agar siswa yang mengulang mendapat butir lain.

Jenjang kesulitan mengikuti urutan wilayah: Temanggung melatih informasi **tersurat**, Magelang **fakta vs pendapat** dan menghubungkan gambar–teks, Wonosobo **menyimpulkan** dan **menilai sumber** (literasi keamanan digital).

Pustaka Kedu: 10 halaman per wilayah (sejarah, alam, candi, tradisi, kuliner, dan satu halaman "Tips Jaka" yang terkait keterampilan membaca wilayah itu), masing-masing dengan catatan sumber dan 1–3 gambar/video. Media Pustaka memakai tautan Wikimedia Commons (berlisensi bebas; kreditnya di kolom `credit`) dan beberapa video YouTube dari kanal resmi atau lembaga.

## Status tinjauan

Semua butir diimpor berstatus **`draft`**, bukan `verified`. Fakta di setiap butir sudah dicocokkan dengan sumber di kolom `reference_source`, tetapi `verified` berarti sudah diperiksa guru atau ahli. Status ini tidak memengaruhi apakah butir dimainkan. Setelah meninjau, ubah statusnya di editor butir atau di kolom `review_status` lalu impor ulang.

## Mengimpor

1. Unggah gambar di Panel Admin → Media sesuai sheet **DAFTAR_MEDIA** (boleh dilakukan setelah impor; lihat bagian berikut).
2. Panel Admin → Konten → Impor bank soal → pilih `gelita-bank-soal-produksi.xlsx` → **Pratinjau**. Hasil yang diharapkan: 0 galat.
3. **Jalankan impor**. Seluruh isi masuk dalam satu transaksi.
4. Konten → Verifikasi konten, lalu mainkan tiap wilayah sebagai siswa.

Lewat server:

```bash
php spark gelita:bank:import docs/bank-soal/gelita-bank-soal-produksi.xlsx --dry-run
php spark gelita:bank:import docs/bank-soal/gelita-bank-soal-produksi.xlsx
php spark gelita:content:verify
```

Impor ulang aman: baris dicocokkan lewat kode (`node_ref`, `passage_key`, `item_key`, `option_key`, nomor halaman Pustaka) lalu diperbarui. Media Pustaka sebuah halaman diganti seluruhnya oleh baris `library_media` halaman itu. Kunci jawaban butir yang **sudah dijawab siswa** tidak dapat diubah — buat butir baru dengan kode baru.

Di server pengembangan, `SampleItemSeeder` sudah mengisi dua butir contoh per tantangan (kode berakhiran `-91` dan `-92`, mis. `tmg-2-91`). Butir contoh itu tidak dihapus oleh impor; nonaktifkan di editor butir bila tidak diinginkan.

## Gambar yang harus diunggah

Tantangan puzzle dan cari objek butuh gambar sungguhan. Impor membuat **slot kosong** untuk setiap `asset_key` yang belum terdaftar, dan Panel → Media menandai slot yang masih kosong. Begitu berkas diunggah dengan asset_key yang sama, gambar langsung tampil.

| asset_key | Dipakai di | Ukuran |
|---|---|---|
| `challenge.item.tmg-1-01` … `-04` | keping puzzle tmg-1 (Sindoro–Sumbing, kebun tembakau, Candi Pringapus, kuda lumping) | 900 × 900 px |
| `challenge.item.mgl-1-01` … `-04` | keping puzzle mgl-1 (stupa Borobudur, Topeng Ireng, Candi Mendut, getuk) | 900 × 900 px |
| `challenge.tmg-4.scene` | adegan cari objek tmg-4 (balai desa/pasar budaya di kaki Sindoro–Sumbing) | 1280 × 720 px |
| `challenge.item.tmg-4-01` … `-08` | 8 objek budaya Temanggung yang dicari | 240 × 240 px, latar transparan |
| `challenge.item.tmg-4-09` … `-11` | 3 objek jebakan dari daerah lain (angklung, ondel-ondel, pinisi) | 240 × 240 px, latar transparan |

Isi setiap gambar dan saran sumbernya dirinci di sheet DAFTAR_MEDIA. Selama gambar belum diunggah, puzzle menampilkan gambar pengganti dan objek cari tampil sama rupa — tantangan tetap dapat dimainkan tetapi belum layak untuk studi.

mgl-5 tidak butuh berkas: setiap butir memuat deskripsi gambar dalam teks sumbernya. Gambar pendamping per butir tetap dapat ditambahkan lewat editor butir.

**Sekolah tanpa internet.** Gambar Commons dan video YouTube di Pustaka dimuat dari internet oleh browser siswa. Untuk kelas luring, unduh berkasnya (patuhi lisensi di kolom `credit`), unggah di Panel → Media, lalu di editor Pustaka ganti sumber media dari *Tautan* ke *Berkas*.

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
'nodes' => [
    'tmg-2' => [
        'engine'          => 'rumpang',            // puzzle | rumpang | boleh | pilihan | cari
        'indicator'       => 'literasi',           // bawaan untuk butir: literasi | budaya | sikap
        'items_per_round' => 4,
        'use_word_bank'   => true,                 // rumpang
        'distractor_count'=> 2,                    // rumpang bank kata
        'verdict_options' => 'benar,salah',        // boleh
        'require_reason'  => true,                 // boleh dengan alasan
        'scene'           => ['key' => 'challenge.tmg-4.scene', 'note' => 'isi gambar', 'source' => 'saran sumber'],  // cari
        'title' => [...], 'instruction' => [...], 'description' => [...],
        'hints'       => [['petunjuk', 'hint'], ...],
        'distractors' => [['salju', 'snow'], ...],
        'note' => 'catatan tinjauan', 'ref' => 'sumber rujukan',
        'items' => [ /* lihat di bawah */ ],
    ],
],
```

Kunci butir di dalam `items`:

| Kunci | Dipakai oleh | Isi |
|---|---|---|
| `type` | semua | jenis interaksi, mis. `fill_blank_bank` |
| `prompt` | semua | pertanyaan/kalimat; rumpang wajib memuat `___` di kedua bahasa |
| `answer` | rumpang, boleh | `['Sumbing', 'Sumbing']` untuk rumpang; `'benar'`/`'salah'`/`'pendapat'` untuk boleh |
| `order` | `ordering` | urutan kode potongan, mis. `'b,a,d,c'` |
| `source`, `passage` | opsional | teks sumber di atas soal; kode bacaan dari `passages` |
| `options` | pilihan | `[label_id, label_en, benar?, umpan_balik_id, umpan_balik_en]` × 4, tepat satu benar |
| `pieces` | `ordering` | `[kode, teks_id, teks_en]` |
| `sources` | boleh/`source_trust` | `[label_id, label_en, kind, teks_id, teks_en]`, `kind` = `official` \| `anonymous` \| `chain_message` \| `blog` |
| `reason` | `verdict_reason` | contoh alasan yang baik |
| `x`, `y`, `w`, `decoy`, `wrong` | cari | posisi & lebar objek dalam persen adegan; jebakan wajib punya umpan balik `wrong` |
| `media` | puzzle, cari | `['key' => asset_key, 'note' => isi gambar, 'source' => saran sumber]` → masuk DAFTAR_MEDIA |
| `hints` | opsional | petunjuk khusus butir |
| `pillar` | opsional | pilar literasi digital (`digital_skills` \| `digital_ethics` \| `digital_safety` \| `digital_culture`) |
| `indicator`, `ref`, `note`, `status` | opsional | menimpa nilai bawaan tantangan |

Kode butir (`tmg-2-01`, …) dan opsi (`…-a` … `-d`) diberikan otomatis **sesuai urutan di berkas**. Setelah bank soal dipakai siswa, jangan menyisipkan butir di tengah daftar — tambahkan di akhir, agar kode butir lama tidak bergeser.

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

Pemeriksaan yang dijalankan `build-workbook.php` sebelum menulis:

- pilihan ganda: tepat 4 opsi dengan satu jawaban benar;
- rumpang: kalimat ID dan EN memuat `___`;
- rumpang bank kata: jawaban tidak kembar dalam satu tantangan, pengecoh tidak sama dengan jawaban mana pun, dan jumlah pengecoh ≥ `distractor_count`;
- boleh: kunci termasuk `verdict_options`;
- `ordering`: urutan memuat setiap kode potongan tepat sekali;
- cari: setiap jebakan punya umpan balik;
- setiap tantangan punya butir target ≥ `items_per_round`, dan `item_key` unik lintas wilayah.
