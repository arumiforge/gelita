# Skema data katalog

Sumber aktif `../data.json` adalah array 590 objek. Field berbeda menurut jenis entri; jangan menghapus field yang belum dipahami. Build menanam data yang sama persis di `index.html`.

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
| Tantangan | 45 |
| Lencana | 3 |
| Pustaka | 60 |
| Narasi | 88 |
| Musik | 3 |
| Efek suara | 6 |

## Berkas dan spesifikasi

| Field | Makna |
|---|---|
| `file` | Berkas visual utama relatif root katalog |
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

Audio berupa brief, bukan bukti rekaman. Jangan menerapkan migrasi/prompt visual ke audio. Validator membandingkan seluruh objek audio dengan baseline, bukan hanya jumlah.

