# Progres proyek

Diperbarui 12 Oktober 2026 (Asia/Bangkok). Urutan di bawah membedakan hasil terkini dari pemeriksaan historis. Keputusan aktif dirangkum dalam [MEMORY.md](MEMORY.md).

## 12 Oktober 2026 — dokumentasi dan struktur

- Membaca panduan/data/skrip dan memeriksa kondisi folder sebelum perapian.
- Memindahkan dokumentasi ke `docs/`, laporan ke `reports/`, manifest/pemetaan ke `provenance/`, template ke `templates/`, skrip aktif ke `scripts/`, dan lima skrip sekali pakai ke `archive/scripts/`.
- Mempertahankan README, AGENTS, data, index, dan pintu masuk `build.mjs` di root. `node build.mjs` tetap menjadi perintah build.
- Menghapus `sources/` setelah membandingkan kedua dokumennya dengan salinan identik di `archive/source-documents/`. Menghapus README.txt root yang mengulang panduan; salinan historis tetap tersedia.
- Memperbarui seluruh Markdown aktif dan memberi penanda pada dokumen Markdown historis; isi sumber lama dipertahankan sebagai bukti. Progres sebelum perapian ada di [arsip](../archive/before-2026-10-12/PROGRESS.md).
- Menambah panduan AI, skema data, pemeliharaan, serta README folder. Memperbaiki lokasi laporan, prompt, tautan HTML, dan pencarian root pada skrip aktif.
- Memisahkan tanggal keputusan gaya dari waktu build/validasi. Menghapus ketergantungan lokasi paket pribadi pada skrip browser/lembar ikon aktif; paket dapat diberikan melalui lingkungan.
- Build dan `scripts/validate.mjs` lulus: 370 ID/kode unik, 273 visual tersedia, data HTML identik, status override/riwayat utuh, 475 tujuan migrasi tersedia, dan audio identik. 16 Markdown aktif dengan 343 tautan serta 6 tautan HTML statis diperiksa; isi tautan dokumen arsip dikecualikan karena berasal dari repositori aplikasi lama. Sintaks tiga skrip JavaScript aktif yang diubah juga diperiksa; pemeriksaan whitespace Git lulus.
- Memeriksa hash sebelum/sesudah: `data.json` dan 283 berkas aset yang sudah ada identik byte demi byte. Seluruh 221 raster WebP berhasil dibaca, tepat target, dan metadata alpha cocok; tidak ada PNG lepas. Pemeriksaan resize tanpa perubahan menemukan 0 gambar perlu resize. Sintaks seluruh skrip Python aktif valid.
- Browser Edge desktop/mobile lulus pada 12 Oktober 2026: filter/pencarian, 18 frame Jaka, detail/Escape, ikon 512 px, 26 referensi, pembukaan WebP, ekspor JSON, 97 audio, hasil kosong/reset, tanpa luapan horizontal atau galat JavaScript. Startup di sandbox gagal; percobaan di luar sandbox lulus. Screenshot terbaru disimpan di `review/`; lembar kontak tidak dibuat ulang.
- Status Git repositori induk berhasil dibaca di luar sandbox setelah akses dari sandbox ditolak. Tidak membuat commit atau push. Daftar pemindahan dan bukti hash ada di [laporan perapian](../reports/reorganization-report.json); pemeriksaan data di [laporan validasi](../reports/validation-report.json), browser di [laporan browser](../reports/browser-check-report.json).
- Tidak ada generasi AI, rekaman, perubahan isi data, atau perubahan gambar aset dalam pekerjaan ini. Semua dokumentasi aktif kini mengikuti struktur dan kondisi terbaru; sejarah tetap diberi penanda.

## 11 Oktober 2026 — hasil terakhir sebelum perapian

- Seluruh 273 visual berstatus “Sesuai gaya” berdasarkan `user_override`; audit lama dipertahankan pada `previous_style_review`. Ini penetapan pengguna, bukan audit visual baru.
- 273 prompt aktif diselaraskan: ilustrasi 3D, identitas Jaka/Mbah Kedu, peta relief, alpha, label bahasa, dan SVG; media faktual tetap dokumenter. Audio identik dengan baseline.
- Seluruh 83 Ikon dibuat ulang: 20 generasi ilustratif terpilih, 4 turunan ukuran aplikasi, 51 SVG utama kategori Ikon, dan 8 kontrol SVG diekspor ke WebP. Ikon aplikasi memakai Lentera Kedu emas/navy; motif wilayah/avatar dipertahankan.
- Favicon 32 px diekspor dari SVG. Ikon aplikasi dan bundengan dikoreksi, diperiksa individual; lembar ikon diperiksa. Review ini bukan validasi budaya/animasi.
- Build/validator lulus saat itu: ID/kode, tautan, HTML/data, target, override, ikon, dan audio. Hash seluruh 83 ikon berbeda dari cadangan; tidak ada PNG lepas.
- Pemeriksaan browser lulus di Edge di luar sandbox: 273 kartu, filter, 18 frame Jaka, dialog/Escape, ikon 512 px, 26 referensi, pembukaan WebP, ekspor JSON, 97 audio, hasil kosong/reset, mobile tanpa luapan/galat. Percobaan startup Edge dalam sandbox sebelumnya gagal.
- Bukti: `reports/icon-update-report.json`, `reports/prompt-review-report.json`, `reports/icon-replacement-verification.json`, `provenance/icon-generation-results.json`, `provenance/generation-manifest.json`, dan `archive/before-icons-20261011.zip`.

## 11 Oktober 2026 — resize dan pembersihan format

- Menghapus 220 PNG lepas setelah memastikan WebP tersedia; membuat 11 WebP lossless untuk sembilan referensi dan dua screenshot. Kode resmi serta nama sumber historis tidak diubah.
- Resize deterministik tanpa generasi AI: 213 WebP diresize, 8 sudah tepat. Seluruh 221 raster aktif tepat target; 26 latar di folder latar berukuran 1920 × 1080.
- Mempertahankan proporsi dengan crop tengah atau padding transparan; backup ZIP diverifikasi sebelum penggantian. Pembesaran tidak menambahkan detail.
- Metadata/build/validator lulus; pemeriksaan ulang resize menemukan 0 perbedaan. SVG dan screenshot tidak ikut resize raster bertarget. bg.welcome dan ui.logo diperiksa individual; tidak ada audit ulang seluruh gambar saat resize.
- Bukti: `reports/resize-report.json`, `archive/before-resize-20261011T155118391929Z.zip`.

## 11 Oktober 2026 — pekerjaan awal (riwayat)

- Membuat ulang 9 referensi hilang: 02, 03, 05, 06, 07, 09, 10, 11, 12 sebagai gambar baru, bukan pemulihan asli.
- Menghapus folder tambahan jakahappy; frame resmi dipertahankan. Memetakan 475 nama lama ke nama berdasarkan kode/kelompok, termasuk varian format.
- Audit lembar kontak awal menghasilkan 68 sesuai, 141 belum sesuai, 3 perlu tinjau, dan 61 tidak berlaku. Angka ini **digantikan** keputusan penandaan semua visual sesuai; bukan status terkini.
- Pada tahap awal 213 raster berbeda dari target dan browser membuka PNG; kondisi tersebut digantikan pembersihan WebP/resize/pembaruan ikon berikutnya.
- Membuat katalog berkelompok dan dokumentasi lokal; membekukan 97 objek audio.

## Batas hasil

Gambar di luar pekerjaan referensi/ikon tidak dibuat ulang secara massal. Prompt terbaru serta status keputusan pengguna tidak membuktikan aset lama telah diperbaiki. Tidak ada validasi budaya/lisensi, rekaman audio, uji animasi, atau integrasi aplikasi game. Dokumen aplikasi/workbook di arsip bukan file yang disertakan dalam paket ini.
