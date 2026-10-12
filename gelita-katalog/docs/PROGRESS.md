# Progres proyek

Diperbarui 12 Oktober 2026 (Asia/Bangkok). Urutan di bawah membedakan hasil terkini dari pemeriksaan historis. Keputusan aktif dirangkum dalam [MEMORY.md](MEMORY.md).

## 12 Oktober 2026 — perbaikan konflik sebelum push GitHub

- Memeriksa PowerShell lokal dan Git repositori induk. Setelah `git fetch origin`, branch `main` dan `origin/main` sama-sama berada pada `8b4d358`; tidak ada perubahan lokal atau staging pada awal pemeriksaan.
- Menghapus penanda konflik merge yang sudah tersimpan dalam PROGRESS dengan mempertahankan kedua catatan pekerjaan. Menjalankan `node build.mjs` untuk membuat ulang HTML dan laporan status dari `data.json`, lalu `node scripts/validate.mjs` untuk membuat ulang laporan validasi. Tidak mengubah sumber data, aset, atau 97 objek audio.
- Build dan validator lulus: 580 ID/kode unik, 483 visual tersedia, 240 frame karakter, data HTML identik, audio identik dengan baseline, 563 tautan Markdown dan 6 tautan HTML statis tersedia. Sintaks satu blok JavaScript HTML dan parsing kedua laporan JSON lulus; keempat berkas bebas penanda konflik. Pemeriksaan whitespace Git lulus. Pemeriksaan browser tidak dijalankan dalam pekerjaan ini.
- Persiapan commit/push dibatasi pada `gelita-katalog/`: PROGRESS, index, laporan status, dan laporan validasi. Commit lama yang sudah ada di GitHub tidak diubah.

## 12 Oktober 2026 — 24 frame setiap pose Jaka dan Mbah Kedu

- Mengikuti permintaan pengguna: enam pose Jaka (idle, happy, bow, sad, afraid, determined) dan empat pose Mbah Kedu (idle, smile, worried, weak), masing-masing 24 frame. Total 240 WebP transparan 700 × 900 mengganti 30 file lama dan menambah 210. Kode frame 1–3 dipertahankan; frame ditampilkan dalam urutan angka per pose.
- Membuat dan memeriksa individual 10 key pose dengan image_gen, memakai reference.01/Jaka syal merah dan reference.02/Mbah Kedu. Sepuluh gambar dasar diberi gerak sekunder deterministik bertahap dengan kaki tetap; bukan 240 generasi AI terpisah, perubahan ekspresi antarframe, atau animasi rig sendi. Satu percobaan generasi ekspresi ditolak penyaring gambar; deskripsi ekspresi sederhana berhasil pada percobaan berikutnya.
- Mencadangkan data awal dan 30 frame lama di `archive/before-character-frames-20261012/`. Menyimpan prompt/sumber di [provenance karakter](../provenance/character-generation-results.json), key pose WebP lokal di `provenance/character-keyposes/`, dan parameter gerak/hash frame di [laporan frame](../reports/character-frame-report.json).
- Memperbarui data, build, validator, pemeriksaan browser, jumlah aktif dalam dokumentasi, serta panduan/provenance. Kini ada 580 entri: 483 visual (431 WebP, 52 SVG) dan 97 audio. `style_review` berbasis `user_override` serta `previous_style_review` seluruh 273 visual awal identik; 210 frame baru memiliki catatan asal sendiri.
- Build dan validator lulus: 580 ID/kode unik, 483 visual tersedia, 10 pose × 24 slot, data HTML identik, 475 jalur migrasi tersedia, dan 97 objek audio identik. Tautan 16 Markdown aktif serta 6 tautan HTML statis lulus.
- [Verifikasi frame](../reports/character-frame-verification.json) lulus: 240 berkas dengan hash berbeda, dimensi/alpha benar, piksel kaki yang terlihat dan alpha pada baris 853–899 identik per pose, serta 30 file lama benar-benar diganti. Seluruh 340 entri lain identik; 253 hash aset baseline lain tetap identik. Semua 431 raster aktif tepat target. Alpha transparan diperiksa untuk frame baru; ini tidak mengesahkan transparansi aset lama di luar karakter.
- Pemeriksaan kaki pertama membandingkan RGB piksel transparan dan gagal akibat pilihan encoder WebP; setelah RGB yang tidak terlihat dinormalisasi, pemeriksaan piksel terlihat/alpha lulus. Percobaan memperluas pemeriksaan alpha ke aset lama menemukan metadata alpha lama tidak sama dengan interpretasi Sharp untuk gambar opak `challenge.item.wnb-4-a07`; berkas/data itu tidak diubah dan pemeriksaan alpha dibatasi pada 240 frame baru. Dimensi seluruh raster tetap diperiksa.
- Browser Edge di sandbox gagal saat startup; kedua pemeriksaan di luar sandbox lulus. [Katalog](../reports/browser-check-report.json): 483 visual, 144 frame Jaka, detail/Escape, referensi, WebP asli, ekspor, 97 audio, pencarian/reset, mobile tanpa luapan/galat. [Karakter](../reports/character-browser-check-report.json): 24 kartu berurutan setiap pose, frame 24 semua pose terbuka pada 700 × 900, sepuluh pilihan pose/slider/playback preview, desktop/mobile tanpa luapan atau galat JavaScript.
- Membuat sepuluh lembar kontak frame dan [preview gerak lokal](../review/character-animation.html). Memeriksa seluruh key pose dan lembar frame happy/bow/weak; bukan review individual 240 gambar atau validasi budaya, lisensi, rig, maupun integrasi game.
- [Daftar nama gaya lama](../reports/remaining-legacy-style.txt) berisi 101 aset yang belum diganti berdasarkan `previous_style_review`; tiga aset lain masih tidak pasti di [laporan](../reports/remaining-legacy-style.json). Membaca enam lembar kontak sebelum penggantian dan membuat lima lembar kontak terbaru setelah karakter dikecualikan. Status katalog tidak diubah dan daftar ini bukan audit visual baru. Lembar keenam sementara yang sudah kedaluwarsa dihapus.
- Sintaks tujuh skrip JavaScript baru/diubah dan pemeriksaan whitespace Git lulus. Validator akhir lulus dengan 563 tautan Markdown; satu percobaan sempat gagal menulis laporan dengan galat filesystem `UNKNOWN`, lalu percobaan ulang berhasil. Tidak membuat commit atau push; staging yang sudah ada tidak diubah oleh agen. Audio tidak diubah dan tidak ada rekaman baru.

## 11 October 2026 (evening) — logo and start buttons from the references, game import

*(New section, English, written from the GELITA app repo; branch `pbb/nifty-faraday-fcw9r6`.)*

- **Owner decisions** in the app repo's decision log (`docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`): `ui.logo-hero` ← `reference.09`, `ui.logo` ← `reference.16`, start buttons as finished images styled like `reference.10`.
- **Replaced four images** (Pillow, Lanczos, lossy WebP q92 with full alpha):
  - `assets/logo/ui.logo-hero.webp`: `reference.09` padded with transparency to 1867 × 700 (8:3), scaled to 1600 × 600.
  - `assets/logo/ui.logo.webp`: `reference.16` scaled to 512 × 512.
  - `assets/antarmuka/ui.btn-start.webp`: `reference.10` padded to 960 × 320 (3:1), scaled to 720 × 240.
  - `assets/antarmuka/ui.btn-start.en.webp`: START made from `reference.10`. The MULAI letters were removed by rebuilding the enamel between two clean columns, then START was drawn in Cinzel Bold (the app's font) with the gold sampled from the original letters, a dark outline, a shadow, and a light bevel. Interim, until a real START exists.
- **Data:** in `data.json`, the four entries got a new `desc`, `generated_by`, `content_review`, and `actual`. The old ornament-only logo files and the flat SVG-rendered buttons are replaced; `ui.btn-start*.svg` stay as before.
- **Checks:** `node build.mjs` and `node scripts/validate.mjs` pass. No other image, prompt, or audio changed. The browser check was not run.
- **Game import:** the app now installs this catalogue's images with `php spark gelita:katalog:impor`, which re-encodes them as lossy WebP for the game. Images here stay lossless.

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

Gambar di luar pekerjaan referensi/ikon/karakter tidak dibuat ulang secara massal. Prompt terbaru serta status keputusan pengguna tidak membuktikan aset lama telah diperbaiki. Pemeriksaan preview gerak sekunder bukan validasi rig atau integrasi aplikasi game. Tidak ada validasi budaya/lisensi atau rekaman audio baru. Dokumen aplikasi/workbook di arsip bukan file yang disertakan dalam paket ini.
