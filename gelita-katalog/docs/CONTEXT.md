# Konteks proyek

Revisi Peta Kedu terbaru15Oktober: [kandidat04](../mockup/gambar-mockup/peta-kedu/revisi-04/README.md) memiliki preview HTML interaktif, efek ketik/dot progres/kontrol ikon, Pustaka/Profil dalam menu, ikon Kenali buku/kaca pembesar dan tombol bergambar katalog. Master diorama baru1774×887 dipotong tiga lapisan clip-path; wilayah terkunci meredup dan jalur putus terpisah. [Browser check](../reports/peta-kedu-04-browser-check.json) mengukur target DOM preview640×360/854×480, bukan perangkat fisik. Kandidat belum dipilih pengguna dan belum diintegrasikan ke web asli.

Kondisi terdokumentasi: 15 Oktober 2026 (Asia/Bangkok). Paket `gelita-katalog` adalah katalog statis internal aset game edukasi kelas 5 tentang Temanggung, Magelang, dan Wonosobo. Buka melalui [index.html](../index.html); ini bukan source aplikasi GELITA lengkap.

## Batas paket

Tersedia: 611 entri, gambar, template HTML, skrip, provenance, laporan, arsip, serta bahan revisi bank soal XLSX/naskah/puzzle di `update/`. Backend, database game, konfigurasi slot aplikasi, rekaman narasi, dan pengujian integrasi game tidak disertakan. Sembilan pratinjau audio sintetis nonnarasi tersedia. Dokumen lama menyebut 12 tantangan kelas 5; katalog memiliki 66 entri visual Tantangan, sehingga kedua angka mengukur hal berbeda.

## Layar aplikasi asli

Menurut keterangan pengguna pada 12 Oktober 2026, seluruh aset gambar katalog digunakan di aplikasi GELITA dengan rasio layar tetap **16:9** (landscape). Halaman aplikasi tidak mendukung tata letak responsif untuk rasio layar lain. Konteks ini menjadi acuan saat memahami komposisi latar, posisi karakter, dan penempatan elemen antarmuka pada proyek asli. Rasio 16:9 adalah rasio halaman aplikasi, bukan ketentuan bahwa setiap berkas gambar harus berasio 16:9; ukuran dan proporsi aset tetap mengikuti slot masing-masing.

## Sumber kebenaran

| Informasi | Sumber |
|---|---|
| Instruksi dan keputusan aktif | [AGENTS.md](../AGENTS.md), [MEMORY.md](MEMORY.md), [README.md](../README.md) |
| Entri, prompt aktif, target, jalur, status | `../data.json` |
| Isi/dimensi/alpha nyata | `../assets/` |
| Struktur/tampilan/interaksi | `../templates/catalog.template.html`, `../assets/catalog/catalog.css`, `../assets/catalog/catalog.js`; Bootstrap/font lokal di `../assets/vendor/` |
| Hasil build | `../index.html`, `../reports/STATUS-GAMBAR.json`, `../reports/STYLE-AUDIT.*`, [PROMPTS-ENGLISH.md](PROMPTS-ENGLISH.md) |
| Prompt/output generasi sebelumnya | [provenance/](../provenance/README.md) |
| Bukti pekerjaan/pemeriksaan | [PROGRESS.md](PROGRESS.md), [reports/](../reports/README.md) |

Manifest bisa memuat lokasi gambar di komputer pembuat yang tidak tersedia di checkout lain; `file` di data menunjuk hasil yang disertakan. Hasil build tidak menggantikan sumber aktif.

## Kondisi visual dan audio

514 visual terdiri dari 461 WebP dan 53 SVG utama. Sembilan referensi hilang telah dibuat ulang sebagai gambar baru. Semua 83 Ikon sudah dibuat ulang. Pada 12 Oktober, 30 frame lama Jaka/Mbah Kedu diganti dan 210 ditambah: 10 key pose AI menjadi 240 frame gerak sekunder deterministik. Gambar di luar pekerjaan referensi/ikon/karakter/UI hasil/puzzle memakai aset yang tersedia, dengan prompt aktif terbaru.

Semua visual “Sesuai gaya” berdasarkan `user_override`: 273 entri awal mempertahankan keputusan 11 Oktober dan 210 frame tambahan mengikuti permintaan 12 Oktober; 8 aset hasil mengikuti contoh UI pilihan pada 13 Oktober. Termasuk aset lama di luar karakter yang belum diperbaiki. Audit lama (68 sesuai, 141 belum sesuai, 3 perlu tinjau, 61 tidak berlaku) tetap di `previous_style_review`; angka itu bukan status saat ini. Semua 461 raster aktif tepat target. Resize mempertahankan proporsi melalui crop/padding, tidak menambah detail atau memperbaiki gaya.

97 entri audio: 88 narasi berupa brief identik baseline; 3 musik dan 6 efek suara memiliki MP3/master WAV sintetis atas permintaan 14 Oktober. Baseline 97 brief asli tetap utuh; validator mengizinkan hanya metadata produksi sembilan slot tersebut. Target 100 narasi per bahasa pada dokumen asal adalah kebutuhan aplikasi terdahulu, bukan rekaman yang tersedia dalam paket ini.

## Struktur dan sejarah

Pada 12 Oktober 2026, dokumentasi dipusatkan di `docs/`, laporan di `reports/`, manifest di `provenance/`, template di `templates/`, pemeliharaan di `scripts/`. README, AGENTS, data, index, dan pintu masuk build tetap di root. `sources/` dihapus setelah salinannya dipastikan ada di `archive/source-documents/`.

Arsip merekam kondisi lama, termasuk arahan 2D dan rencana katalog belum dibangun. Pernyataan itu telah digantikan keputusan terbaru. Rujukan aplikasi/workbook/tautan di isi arsip tidak membuktikan berkas tersebut tersedia di sini.


Revisi 13 Oktober: full UI hasil memakai skin panel/tombol khusus sesuai mockup pilihan. Enam WebP dibuat, lanskap yang sudah tersedia dipromosikan sebagai bg.result.temanggung, dan efek SVG dilapis di avatar.2. [Manifest](../provenance/result-ui-generation-results-20261013.json). Ini bahan katalog dan preview; halaman aplikasi asli belum diintegrasikan.

Bahasa mengikuti keputusan terbaru: icon.language-id menampilkan merah–putih Indonesia dan icon.language-en menampilkan bendera UK. Dua slot bendera ditambahkan 13 Oktober; pada 14 Oktober, reference.19/20 juga memakai salinan bendera terbaru, mempertahankan kode, ukuran, alpha dan riwayat gaya. Ikon dipakai pada HUD dan popover preview.

Keputusan cakupan hasil 13 Oktober: satu konsep UI “Tantangan selesai” digunakan bersama pada seluruh kota/wilayah, bukan khusus Temanggung. Kota pada screenshot hanya contoh. Implementasi memakai konteks wilayah dan hasil dinamis; pemilihan background mengikuti mapping aset aplikasi.

Revisi gambar puzzle 14 Oktober: menambah 21 slot d–j dan mengganti empat slot lama sesuai panduan revisi. Katalog kini memiliki 66 visual Tantangan, termasuk 30 gambar Susun Gambar (lima gambar lama lainnya dipertahankan). 25 gambar hasil generasi memakai prompt terbaru dan WebP opak 1200 × 1200. Sebanyak 21 entri baru mengikuti basis user_override dalam permintaan pengguna; riwayat semua entri lama dipertahankan. [Catatan paket](PUZZLE-UPDATE-20261014.md). Ini belum merupakan pemasangan ke aplikasi atau verifikasi penelitian.


Galeri mockup ditanam dari [inventaris halaman](../mockup/catalog.json), terpisah dari 611 aset/brief. Satu desain hasil tersedia dengan referensi pilihan dan empat render state; 12 paket halaman berikutnya masih status planned. Peta Kedu memiliki [kandidat full UI 1920 × 1080](../mockup/gambar-mockup/peta-kedu/README.md), belum dipilih pengguna; kandidat04 memiliki preview interaktif dan riwayat01–03; 11 halaman lain belum memiliki gambar baru. [Dokumen persiapan](../mockup/persiapan/README.md). Audio memakai sintesis modal lokal untuk review; bukan rekaman tradisional autentik. Speaker preview hasil kini memainkan musik wilayah, serpihan dan klik setelah diaktifkan pengguna.
