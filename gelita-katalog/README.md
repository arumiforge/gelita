# GELITA — Perpustakaan aset

Katalog statis internal untuk game edukasi kelas 5 tentang Temanggung, Magelang, dan Wonosobo. Status diperbarui 11 Oktober 2026 (Asia/Bangkok).

## Membuka katalog

Buka `index.html` langsung di browser. Tidak perlu akun, server, atau internet. Bawa seluruh folder `assets` saat membagikan katalog. Cari berdasarkan kode/nama, pilih koleksi, wilayah, atau status gaya. Detail menyediakan gambar, ukuran aktual/target, catatan pemeriksaan, prompt, dan unduhan teks. Saat dibuka langsung sebagai berkas lokal, gambar asli dibuka di tab baru untuk disimpan melalui browser; saat dihosting, tautan mendukung unduhan langsung. JSON/CSV mengekspor hasil filter.

## Keputusan visual aktif

- Gaya utama: render 3D bergaya animasi petualangan, material bervolume dan cahaya hangat.
- Acuan Jaka: `assets/referensi/reference.01.webp`. Rambut cokelat acak, kemeja krem, **syal merah**, celana cokelat, batik pinggang, sandal, ransel kulit. Tidak memakai lurik, iket, atau rompi biru.
- Avatar baru: `reference.03.webp`; Mbah Kedu: `reference.02.webp`. Syal merah hanya untuk Jaka, bukan semua tokoh.
- Ikon SVG/UI datar dan media faktual tidak wajib menjadi 3D. Akurasi isi budaya harus diperiksa tersendiri.
- Sembilan referensi yang hilang dibuat ulang sebagai gambar baru dengan built-in image_gen, bukan pemulihan berkas asli. Prompt generasi tersimpan dalam `generation-manifest.json`.

## Isi dan hasil audit

370 entri: **273 visual + 97 brief audio**. Keputusan terbaru pengguna: **semua 273 visual ditandai Sesuai gaya** meskipun gambar lama belum diperbaiki. Status memakai basis `user_override`; audit sebelumnya (68 sesuai, 141 belum sesuai, 3 perlu tinjau, 61 tidak berlaku) tetap tersimpan per entri pada `previous_style_review` dan di arsip. Angka menghitung entri unik, bukan varian format. Penandaan ini terpisah dari pemeriksaan visual, dimensi, budaya dan integrasi.

Seluruh **273 prompt visual aktif** diperiksa dan diperbarui: ilustrasi 3D, identitas Jaka/Mbah Kedu, peta relief 3D, transparansi ikon, label bahasa dan spesifikasi SVG. Foto/video faktual mempertahankan gaya dokumenter. Prompt generasi sebelumnya disimpan dalam arsip, bukan digunakan sebagai instruksi aktif. `PROMPTS-ENGLISH.md` dibangun dari prompt terbaru; `prompt-review-report.json` mencatat lingkup pemeriksaan.

Seluruh **83 entri Ikon** dibuat ulang: 20 generasi ilustratif melalui built-in image_gen (termasuk master ikon aplikasi), 4 turunan ukuran aplikasi, 51 SVG asli (50 kontrol + ikon aplikasi), dan 8 kontrol SVG yang diekspor ke WebP. Ikon aplikasi menggunakan Lentera Kedu di atas navy; ikon wilayah dan 10 avatar mempertahankan motif resmi masing-masing. Sumber kontrol SVG tersedia di `assets/ikon/vektor/`; prompt AI dan hasil berada di `icon-generation-results.json` dan `generation-manifest.json`. Semua raster tetap WebP; kode slot resmi tetap. Cadangan data dan ikon sebelumnya: `archive/before-icons-20261011.zip`.

97 brief audio (88 narasi, 3 musik, 6 efek suara) dipertahankan identik dengan data awal. Rekaman dan naskah narasi lengkap belum disertakan. Target 100 baris per bahasa berasal dari dokumen asal, bukan klaim sudah tersedia.

## Struktur

| Lokasi | Fungsi |
|---|---|
| `data.json` | Sumber data aktif; termasuk audit dan prompt terbaru |
| `catalog.template.html` | Template tampilan dan interaksi |
| `build.mjs` | Membangun HTML, status, audit, dan daftar prompt dari data |
| `assets/referensi/` | Acuan dan konsep awal dengan kode `reference.*` |
| `assets/karakter/` | Frame tokoh, misalnya `char.jaka.happy.1.webp` |
| `assets/latar/`, `peta/`, `tantangan/`, `pustaka/` | Kelompok gambar adegan dan materi |
| `assets/logo/`, `antarmuka/`, `ikon/`, `lencana/` | Kelompok identitas dan UI |
| `migration-map.json` | Pemetaan 475 nama lama → baru, termasuk varian format |
| `archive/` | Metadata, HTML, prompt, dan dokumen sumber sebelum perubahan; bukan berkas aktif |
| `sources/` | Sumber historis dengan penanda bahwa status lama sudah digantikan |
| `AGENTS.md`, `MEMORY.md`, `CONTEXT.md`, `PROGRESS.md` | Instruksi dan catatan lokal untuk kelanjutan pekerjaan |

Nama berkas mengikuti kode aset. Semua raster aktif dan tangkapan layar disimpan sebagai WebP; varian PNG dihapus atas permintaan pengguna. Kode slot resmi tetap dipertahankan (contoh: kode `app.icon-512.png` memakai berkas `app.icon-512.webp`). SVG dan format lain yang bukan PNG tetap tersedia. `framegambarkarakter/jakahappy` sudah dihapus sesuai permintaan; frame resmi di `assets/karakter` tetap disimpan.

## Memperbarui

1. Ubah `data.json` dan berkas aset yang relevan. Jangan mengubah kode slot resmi sembarangan.
2. Untuk memeriksa ukuran, jalankan `python resize_assets.py`. Terapkan dengan `python resize_assets.py --apply` (Python + Pillow). Skrip mengambil target dari `data.json`, mencadangkan WebP asli dalam `archive/before-resize-*.zip`, lalu mengganti aset di lokasi yang sama. Proporsi dipertahankan: gambar opak memakai crop tengah, gambar transparan memakai ruang transparan tambahan; ukuran yang sudah benar dilewati. Hasil disimpan sebagai WebP lossless, metadata dan katalog dibangun ulang, serta laporan ditulis ke `resize-report.json`. Pembesaran ukuran tidak menambahkan detail gambar asli. Untuk perubahan manual, jalankan `python measure_assets.py`.
3. Jalankan `node build.mjs`. Pemeriksaan menolak tautan gambar hilang, kode ganda, dan perubahan pada audio yang dibekukan.
4. Uji katalog desktop/mobile: filter, detail, keyboard, dan ekspor. `validate.mjs` memeriksa berkas dan data; `browser-check.cjs` menyediakan pemeriksaan browser bila Playwright tersedia.

`migrate.mjs` adalah migrasi satu kali dan menolak dijalankan ulang. Jangan mengedit `index.html` secara terpisah dari template/data. Arsip HTML bukan salinan mandiri yang siap dibuka: jalur lamanya didokumentasikan dalam migration-map.

## Pekerjaan lanjutan

Gambar di luar kategori Ikon dipertahankan, dengan prompt terbaru dan status sesuai atas keputusan pengguna. Bila diminta membuat ulang karakter/adegan lama, gunakan prompt aktif serta reference.01/reference.02. Repositori aplikasi, workbook soal, konfigurasi slot, serta pengujian integrasi game tidak termasuk paket ini.
