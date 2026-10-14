# Konteks proyek

Kondisi terdokumentasi: 12 Oktober 2026 (Asia/Bangkok). Paket `gelita-katalog` adalah katalog statis internal aset game edukasi kelas 5 tentang Temanggung, Magelang, dan Wonosobo. Buka melalui [index.html](../index.html); ini bukan source aplikasi GELITA lengkap.

## Batas paket

Tersedia: 590 entri, gambar, template HTML, skrip, provenance, laporan, dan arsip. Backend, database game, workbook bank soal, konfigurasi slot aplikasi, rekaman audio, dan pengujian integrasi game tidak disertakan. Dokumen lama menyebut 12 tantangan kelas 5; katalog memiliki 45 entri visual Tantangan, sehingga kedua angka mengukur hal berbeda.

## Layar aplikasi asli

Menurut keterangan pengguna pada 12 Oktober 2026, seluruh aset gambar katalog digunakan di aplikasi GELITA dengan rasio layar tetap **16:9** (landscape). Halaman aplikasi tidak mendukung tata letak responsif untuk rasio layar lain. Konteks ini menjadi acuan saat memahami komposisi latar, posisi karakter, dan penempatan elemen antarmuka pada proyek asli. Rasio 16:9 adalah rasio halaman aplikasi, bukan ketentuan bahwa setiap berkas gambar harus berasio 16:9; ukuran dan proporsi aset tetap mengikuti slot masing-masing.

## Sumber kebenaran

| Informasi | Sumber |
|---|---|
| Instruksi dan keputusan aktif | [AGENTS.md](../AGENTS.md), [MEMORY.md](MEMORY.md), [README.md](../README.md) |
| Entri, prompt aktif, target, jalur, status | `../data.json` |
| Isi/dimensi/alpha nyata | `../assets/` |
| Tampilan/interaksi | `../templates/catalog.template.html` |
| Hasil build | `../index.html`, `../reports/STATUS-GAMBAR.json`, `../reports/STYLE-AUDIT.*`, [PROMPTS-ENGLISH.md](PROMPTS-ENGLISH.md) |
| Prompt/output generasi sebelumnya | [provenance/](../provenance/README.md) |
| Bukti pekerjaan/pemeriksaan | [PROGRESS.md](PROGRESS.md), [reports/](../reports/README.md) |

Manifest bisa memuat lokasi gambar di komputer pembuat yang tidak tersedia di checkout lain; `file` di data menunjuk hasil yang disertakan. Hasil build tidak menggantikan sumber aktif.

## Kondisi visual dan audio

493 visual terdiri dari 440 WebP dan 53 SVG utama. Sembilan referensi hilang telah dibuat ulang sebagai gambar baru. Semua 83 Ikon sudah dibuat ulang. Pada 12 Oktober, 30 frame lama Jaka/Mbah Kedu diganti dan 210 ditambah: 10 key pose AI menjadi 240 frame gerak sekunder deterministik. Gambar di luar pekerjaan referensi/ikon/karakter/UI hasil memakai aset yang tersedia, dengan prompt aktif terbaru.

Semua visual “Sesuai gaya” berdasarkan `user_override`: 273 entri awal mempertahankan keputusan 11 Oktober dan 210 frame tambahan mengikuti permintaan 12 Oktober; 8 aset hasil mengikuti contoh UI pilihan pada 13 Oktober. Termasuk aset lama di luar karakter yang belum diperbaiki. Audit lama (68 sesuai, 141 belum sesuai, 3 perlu tinjau, 61 tidak berlaku) tetap di `previous_style_review`; angka itu bukan status saat ini. Semua 440 raster aktif tepat target. Resize mempertahankan proporsi melalui crop/padding, tidak menambah detail atau memperbaiki gaya.

97 brief audio (88 narasi, 3 musik, 6 efek suara) identik dengan baseline. Target 100 narasi per bahasa pada dokumen asal adalah kebutuhan aplikasi terdahulu, bukan rekaman yang tersedia dalam paket ini.

## Struktur dan sejarah

Pada 12 Oktober 2026, dokumentasi dipusatkan di `docs/`, laporan di `reports/`, manifest di `provenance/`, template di `templates/`, pemeliharaan di `scripts/`. README, AGENTS, data, index, dan pintu masuk build tetap di root. `sources/` dihapus setelah salinannya dipastikan ada di `archive/source-documents/`.

Arsip merekam kondisi lama, termasuk arahan 2D dan rencana katalog belum dibangun. Pernyataan itu telah digantikan keputusan terbaru. Rujukan aplikasi/workbook/tautan di isi arsip tidak membuktikan berkas tersebut tersedia di sini.


Revisi 13 Oktober: full UI hasil memakai skin panel/tombol khusus sesuai mockup pilihan. Enam WebP dibuat, lanskap yang sudah tersedia dipromosikan sebagai bg.result.temanggung, dan efek SVG dilapis di avatar.2. [Manifest](../provenance/result-ui-generation-results-20261013.json). Ini bahan katalog dan preview; halaman aplikasi asli belum diintegrasikan.

Bahasa mengikuti keputusan terbaru: icon.language-id menampilkan merah–putih Indonesia dan icon.language-en menampilkan bendera UK. Dua slot baru; reference.19/20 tetap utuh. Ikon dipakai pada HUD dan popover preview.

Keputusan cakupan hasil 13 Oktober: satu konsep UI “Tantangan selesai” digunakan bersama pada seluruh kota/wilayah, bukan khusus Temanggung. Kota pada screenshot hanya contoh. Implementasi memakai konteks wilayah dan hasil dinamis; pemilihan background mengikuti mapping aset aplikasi.
