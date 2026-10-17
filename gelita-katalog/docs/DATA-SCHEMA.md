# Skema data katalog

Sumber aktif `../data.json` adalah array 611 objek. Field berbeda menurut jenis entri; jangan menghapus field yang belum dipahami. Build menanam data yang sama persis di `index.html`.

## Identitas dan kelompok

| Field | Makna |
|---|---|
| `id` | String unik, termasuk nol awal pada sebagian ID |
| `key` | Kode slot unik, misalnya `char.jaka.happy.1` atau `app.icon-512.png` |
| `title`, `desc` | Nama/uraian yang ditampilkan |
| `cat` | Kategori isi; tidak selalu sama dengan folder aset |
| `priority`, `region` | Pengelompokan/filter tampilan |

Referensi berkode `reference.*` berada di `assets/referensi/` walaupun kategori dapat berupa Karakter, Background, atau lainnya. Navigasi dihitung oleh template, bukan field `group` di data.

| Kategori | Entri |
|---|---:|
| Karakter | 246 |
| Background | 33 |
| Peta | 5 |
| Logo | 5 |
| Antarmuka | 11 |
| Ikon | 85 |
| Tantangan | 66 |
| Lencana | 3 |
| Pustaka | 60 |
| Narasi | 88 |
| Musik | 3 |
| Efek suara | 6 |

## Berkas dan spesifikasi

| Field | Makna |
|---|---|
| `file` | Berkas visual atau audio utama relatif root katalog |
| `preview`, `generated_asset` | Pratinjau/hasil bila tersedia; sering sama dengan `file` |
| `vector_source` | Sumber SVG untuk kontrol yang diekspor menjadi WebP |
| `size`, `ratio`, `alpha`, `format` | Spesifikasi target; `size: "Audio"` menandai audio |
| `actual`, `actual_format`, `actual_alpha` | Metadata aktual tercatat; ukur ulang bila gambar berubah |
| `dimension_status` | Perbandingan ukuran raster dengan target, bukan kualitas |
| `legacy_file`, `source` | Riwayat nama/asal, bukan jalur yang wajib tersedia |

Nama dasar mengikuti kode setelah ekstensi resmi di ujung kode dilepas: `app.icon-512.png` → `assets/ikon/app.icon-512.webp`. Kode `.ico` juga boleh menunjuk WebP sesuai keputusan format; ini tidak berarti berkas ICO tersedia.

## Prompt, status, provenance

| Field | Makna |
|---|---|
| `prompt`, `negative` | Prompt/larangan aktif untuk pekerjaan baru |
| `prompt_mode`, `prompt_language`, `prompt_revision` | Cara, bahasa, tanggal revisi |
| `generation_prompt`, `generated_by`, `provenance`, `icon_provenance` | Riwayat pembuatan; berbeda dari prompt aktif |
| `style_review` | Status aktif; semua visual basis `user_override` |
| `previous_style_review` | Audit sebelum keputusan seluruh visual sesuai |
| `content_review`, `production`, `note` | Catatan isi/batas kesiapan produksi |
| `status`, `generation_status`, `style_target` | Label ketersediaan/generasi/arah, bukan pengganti status gaya |

88 Narasi tetap berupa brief tanpa rekaman dan identik baseline. Sembilan Musik/Efek suara sekarang memiliki berkas MP3, status pratinjau sintetis, durasi aktual dan audio_provenance (manifest, master WAV, loop, date). preview tetap null untuk audio; pemutar membaca file. Jangan menerapkan migrasi/prompt visual ke audio. Validator memeriksa baseline narasi penuh, identitas/urutan 97 audio dan semua field di luar perubahan produksi yang diizinkan.


## Metadata puzzle — 14 Oktober 2026

25 entri yang dibuat/diperbarui memiliki puzzle: prompt_code, item_key, grid (3 atau 4), description_id/en, review_status (needs_verification), tile_result dan human_assembly_test. puzzle_provenance menyimpan manifest, source_native, references, revision dan replaced. Ini metadata revisi; tidak mengubah identitas kode slot lama atau kunci butir workbook. Lima puzzle lama lainnya dipertahankan.


## Inventaris halaman mockup

[../mockup/catalog.json](../mockup/catalog.json) memuat revision, canvas dan pages; setiap halaman memiliki id/title/status/description/brief. Halaman available memiliki image, preview dan states (label, kind, image); planned memiliki prompt dan belum boleh mengisi image/preview utama sebagai hasil terpilih. Planned boleh memiliki objek candidate (label, image, brief, prompt, preview opsional dan states opsional), selalu berlabel belum dipilih. Kandidat04 memiliki preview interaktif dan tujuh state/riwayat atas permintaan pengguna; status halaman tetap planned. State candidate menggunakan coded-preview atau candidate-history, tidak selected-reference. Jenis state terpilih membedakan selected-reference dan coded-preview; default_state menentukan keadaan awal. Inventaris di-embed ke const MOCKUPS oleh build, bukan entri aset tambahan dan tidak mengambil JSON via fetch.
