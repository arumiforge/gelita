# Dokumen PDF Penting

Per 1 November 2026 · Bagian dari [revisi 10 analitik](../00_INDEKS.md)

Folder ini menyimpan dokumen PDF yang dibagikan ke luar tim: instrumen validasi ahli untuk tahap 2, dan dokumen lain yang perlu dicetak atau dikirim, termasuk berkas serah terima pengembang untuk pertemuan dengan peneliti. Setiap PDF dibuat dari sumber HTML di `src/`, jadi perubahan dilakukan di HTML, lalu PDF dibuat ulang. Keputusan yang mengubah isinya dicatat di [04_CATATAN_KEPUTUSAN.md](../04_CATATAN_KEPUTUSAN.md).

## Isi

| Berkas | Isi | Status |
| --- | --- | --- |
| [instrumen-validasi-ahli-materi-v2.1.pdf](instrumen-validasi-ahli-materi-v2.1.pdf) | Instrumen Validasi Ahli Materi v2.1, 43 halaman (diperbarui 27 Oktober 2026, dan 1 November 2026 untuk model ADDIE dan putaran penilaian ulang tanpa batas): panduan dan teori ukur, lembar penilaian 66 butir, pelabelan buta (B.5a), relevansi per butir (B.5b), telaah Skenario Digital, kode kesalahan, skala sikap, tes awal–akhir dan angket, analisis, lampiran | Disetujui pengembang 27 Oktober 2026; kotak status di sampul dihapus |
| [instrumen-validasi-pengamat-budaya-v2.1.pdf](instrumen-validasi-pengamat-budaya-v2.1.pdf) | Instrumen Validasi Pengamat Budaya v2.1, 34 halaman (diperbarui 27 Oktober 2026 dengan cek fakta [25](../25_CEK_FAKTA_BUDAYA.md), dan 1 November 2026 untuk model ADDIE dan putaran penilaian ulang tanpa batas): panduan, lembar penilaian 53 butir, matriks 46 objek budaya, telaah *Periksa Unggahan* dan butir PBK, konfirmasi cek fakta, skala sikap, analisis, lampiran | Disetujui pengembang 27 Oktober 2026; kotak status di sampul dihapus |
| [instrumen-validasi-ahli-media-v1.1.pdf](instrumen-validasi-ahli-media-v1.1.pdf) | Instrumen Validasi Ahli Media v1.1 (langkah 1), 30 halaman (diperbarui 1 November 2026 untuk model ADDIE dan putaran penilaian ulang tanpa batas): panduan, lembar penilaian 67 butir, uji perangkat, catatan temuan, telaah layar game sekarang, telaah 27 mock-up layar baru, analisis, lampiran | Disetujui pengembang 27 Oktober 2026; kotak status di sampul dihapus; teks token disamakan dengan 28 ("6 huruf dan angka", tanpa O, 0, I, 1, L) dan PDF dibuat ulang 30 Oktober 2026 atas permintaan pengembang |
| [serah-terima-peneliti.pdf](serah-terima-peneliti.pdf) | Berkas Serah Terima ke Peneliti v1.5, 37 halaman (2 November 2026; v1.1 mencatat tujuh ketidaksesuaian yang sudah dibetulkan, v1.2 tiga lagi: daftar kode sekolah, pemegang akun staf, narahubung data; v1.3 memasukkan keputusan peneliti tentang model ADDIE dan tiga uji dengan siswa, dengan bagian baru A.5 *Yang berubah sejak v1.2*; v1.4: nomor pasal PP No. 33 Tahun 2026 dicocokkan dengan PDF salinan resmi dan target persetujuan etik dipertahankan pengembang; v1.5: aturan pekerjaan sistem 1 November 2026 menggantikan "sistem baru diubah setelah G2"): pegangan pengembang untuk pertemuan dengan peneliti. Posisi pekerjaan, daftar periksa enam paket dokumen, isian dan tanda tangan yang hanya bisa diberikan peneliti, keputusan terbuka, keputusan yang sudah diterapkan beserta alasannya (alasan yang disimpulkan, bukan dari 04, ditandai ◆), tanya jawab, ketidaksesuaian antardokumen, `needs_verification`, keterbatasan, agenda, dan lembar catatan keputusan. Dokumen internal, tidak dikirim ke ahli atau komite | `draf pengembang`, `asal: pengembang` |
| [`berkas-validator/`](berkas-validator/) | Berkas per butir untuk validator, dibuat skrip dari 03, 08, 14–18 dan tabel instrumen: daftar buta B.5a (lembar kerja dan buklet PDF), daftar B.5b, lembar kerja B.6–B.9 ahli materi, B.7 versi buta, lembar kerja B.5–B.8 pengamat budaya, lembar kerja B.8 ahli media, `LAPORAN.md`; kunci B.5a di `khusus-peneliti/` (tidak dikirim). Penjelasannya di [21](../21_BERKAS_BUTIR_VALIDATOR.md) | Dibuat ulang 27 Oktober 2026 dari instrumen yang disetujui; dibuat ulang sekali lagi tepat sebelum dikirim ([20](../20_PAKET_VALIDASI.md), prasyarat no. 7) |
| [`cetak/`](cetak/) | PDF A4 siap cetak untuk setiap dokumen bernomor 00–30, dibuat `src/cetak.mjs` dari Markdown-nya, dengan `manifest.json` (sumber, *commit*, jumlah halaman per PDF). Lihat [Versi cetak semua dokumen](#versi-cetak-semua-dokumen) | Dibuat ulang saat akan dikirim ke peneliti atau atas permintaan pengembang (sejak 14 November 2026; sebelumnya setiap dokumen berubah); kaki halaman menyebut *commit* dokumennya |
| [lembar-lapangan-kosong.pdf](lembar-lapangan-kosong.pdf) | Lembar lapangan L0–L8 kosong untuk dicetak dan diisi tangan, 14 halaman A4 (20 November 2026): halaman isi (lembar, pengisi, halaman; lembar yang dicetak per pertemuan; cara mengisi), lalu satu halaman per tabel lembar di [02](../02_LEMBAR_LAPANGAN.md) (L0 dua halaman), tegak atau mendatar seperti ditetapkan 02, dengan kepala halaman yang diisi tangan (kode sesi lapangan sebagai sekolah–rombel–pertemuan, tanggal, pengisi, "Halaman __ dari __"), daftar kode, dan baris kosong sebanyak yang ditetapkan 02. Kolomnya sama dengan templat ketik di panel. Ditawarkan untuk diunduh di panduan staf. Lihat [Membuat ulang lembar lapangan kosong](#membuat-ulang-lembar-lapangan-kosong) | Dibuat dari `lembar-1.0`; dibuat ulang setiap kali tabel lembar di 02 berubah |
| `src/` | Sumber HTML ketiga instrumen dan berkas serah terima, gaya cetak bersama `_instrumen.css`, skrip `render.mjs`, skrip versi cetak `cetak.mjs` dengan gaya `_cetak.css`, skrip berkas validator `berkas-validator.php` (dengan folder `berkas-validator/` dan `berkas-validator-pdf.mjs`), dan skrip lembar lapangan kosong `lembar-kosong.mjs` dengan gaya `_lembar.css` | — |
| `arsip/` | Draf asli pengembang yang menjadi dasar instrumen di atas: `instrumen-validasi-ahli-gelita-r10-v2.0-draf.pdf` (ahli materi dan pengamat budaya dalam satu berkas, 23 Oktober 2026) dan `instrumen-validasi-ahli-media-gelita-v1.0-draf.pdf` (23 Oktober 2026) | Arsip, tidak diubah |

Ringkasan isi instrumen dan bedanya dari usulan telaah ada di [07 → Instrumen versi 2.1 dan 1.1](../07_TELAAH_INSTRUMEN_VALIDASI.md#instrumen-versi-21-dan-11).

## Sebelum dikirim ke validator

1. ~~Pengembang menyetujui isi instrumen.~~ Sudah (27 Oktober 2026).
2. ~~Hapus kotak *STATUS* di sampul setiap HTML, lalu buat ulang PDF-nya.~~ Sudah (27 Oktober 2026).
3. Lampiran "Perubahan dari versi sebelumnya" (D.4) untuk peneliti; tidak perlu dicetak untuk validator.
4. Lembar kerja (*spreadsheet*) untuk lembar per butir disiapkan dari kode butir di bank soal, tes, dan angket.
5. Ikuti prasyarat dan urutan kirim di [20](../20_PAKET_VALIDASI.md).

## Membuat ulang PDF

Dari akar repo:

```
node docs/revisi-10-analitik/dokumen-pdf/src/render.mjs                 # semua
node docs/revisi-10-analitik/dokumen-pdf/src/render.mjs instrumen-validasi-ahli-media-v1.1   # satu berkas
node docs/revisi-10-analitik/dokumen-pdf/src/render.mjs serah-terima-peneliti                 # berkas serah terima
```

Skrip memakai Playwright Chromium yang sudah terpasang (jangan menjalankan `playwright install`) dan huruf Inter serta Liberation Serif dari sistem. Ukuran halaman diatur di `_instrumen.css`: A4 tegak, dan A4 mendatar untuk lembar per butir yang lebar (bagian dengan `class="wide"`). Teks kaki halaman diambil dari `<meta name="pdf-footer">` di setiap HTML. Setelah membuat ulang, periksa bahwa tidak ada tabel yang menyisakan satu atau dua baris di halaman berikutnya.

## Versi cetak semua dokumen

Folder [`cetak/`](cetak/) berisi PDF A4 siap cetak untuk setiap dokumen bernomor (00–30), dibuat skrip `src/cetak.mjs` dari berkas Markdown-nya (31 Oktober 2026, atas permintaan pengembang). Kaki halaman menyebut *commit* terakhir yang mengubah dokumen itu beserta tanggalnya, jadi salinan cetak bisa dicocokkan dengan versinya. Tabel yang terlalu lebar untuk halaman tegak pindah ke halaman mendatar bersama judul di atasnya. Tautan ke berkas lain menjadi teks biasa; tautan di dalam dokumen yang sama tetap bisa diklik.

Dari akar repo, setelah perubahan Markdown di-*commit*:

```
node docs/revisi-10-analitik/dokumen-pdf/src/cetak.mjs           # hanya dokumen yang berubah
node docs/revisi-10-analitik/dokumen-pdf/src/cetak.mjs --all     # semua dokumen
node docs/revisi-10-analitik/dokumen-pdf/src/cetak.mjs 04 22     # dokumen tertentu, menurut nomor
```

Lalu *commit* PDF yang berubah bersama `cetak/manifest.json`. Dokumen yang masih punya perubahan belum di-*commit* ditolak, karena kaki halamannya tidak bisa menyebut *commit*; `--allow-dirty` tetap membuatnya dengan tanda "belum di-commit" (untuk dicek saja, jangan di-*commit*). `manifest.json` mencatat sumber, *commit*, dan jumlah halaman setiap PDF, sehingga dokumen yang tidak berubah tidak dibuat ulang. Skrip butuh `pandoc` dan Playwright Chromium yang sudah terpasang (jangan menjalankan `playwright install`). Tampilannya memakai `_instrumen.css` ditambah `_cetak.css`; mengubah salah satunya, atau skripnya, membuat semua PDF dibuat ulang pada jalankan berikutnya.

## Membuat ulang lembar lapangan kosong

Dari akar repo, setelah `composer install`:

```
node docs/revisi-10-analitik/dokumen-pdf/src/lembar-kosong.mjs                         # menulis lembar-lapangan-kosong.pdf
node docs/revisi-10-analitik/dokumen-pdf/src/lembar-kosong.mjs --kolom=kolom.json      # memakai spesifikasi kolom yang sudah ditulis
node docs/revisi-10-analitik/dokumen-pdf/src/lembar-kosong.mjs --html=periksa.html     # menyimpan juga halamannya untuk diperiksa
```

Kolom setiap lembar diambil dari spesifikasi lembar yang sama dengan templat ketik dan unggahan di panel (`FieldSheetSpec`, lewat `php spark gelita:lembar:templat --kolom`, yang dijalankan skrip dalam mode pengembangan). Yang dicetak adalah judul kolom di kertas menurut 02, termasuk isian gabungan seperti *Headset* dan *Pakai bareng* yang di templat menjadi dua kolom. Sisanya dibaca dari [02](../02_LEMBAR_LAPANGAN.md): pengisi dan waktunya (*Daftar lembar*), tegak atau mendatar dan jumlah baris kosong per halaman (*Cara mencetak dan mengisi*), kepala halaman yang ditulis di 02, kolom *Cara mengisi* L1 bagian atas dan kolom *Isian* L8, daftar kode L1, L3, L4, L6, dan L7, tabel cek buklet L2, pertanyaan L5, dan daftar periksa L0. Bila bagian 02 yang dibaca berubah bentuk, atau judul kolom di 02 berbeda dari spesifikasi, skrip berhenti dengan pesan dan tidak menulis PDF.

Baris kosong direntangkan sampai memenuhi halaman. Skrip menolak halaman yang meluap dan baris yang lebih rendah dari 4,8 mm; lembar mendatar dengan 30 baris (L2, L4, L6 status per siswa) mendapat sekitar 5 mm per baris. Jumlah baris L4 pelatihan pengamat (10) dan L6 catatan sesi tes (8) tidak disebut 02 dan ditetapkan skrip. Kaki setiap halaman menyebut versi spesifikasi lembar dan *commit* terakhir yang mengubah 02 atau `FieldSheetSpec`; perubahan yang belum di-*commit* ditolak, kecuali dengan `--allow-dirty` (untuk dicek saja, jangan di-*commit*). Buat ulang setiap kali tabel lembar di 02 berubah (versi lembar naik), lalu *commit* PDF-nya. Skrip butuh PHP dengan `vendor/` dan Playwright Chromium yang sudah terpasang (jangan menjalankan `playwright install`).

## Unduhan di panduan

Sejak 20 November 2026 (keputusan pengembang) sebagian PDF di folder ini bisa diunduh tanpa masuk dari panduan di panel GELITA: panduan validator menawarkan instrumen setiap panel dan buklet B.5a, panduan staf menawarkan lembar lapangan kosong dan versi cetak 02, 03, 11, 13, 14, 15, 16, 17, 23, dan 24. Daftarnya tetap di `app/Libraries/GuideDownloads.php`; berkas lain tidak bisa diunduh lewat panduan. Kunci B.5a di `berkas-validator/khusus-peneliti/`, berkas serah terima, isi `arsip/`, dan lembar kerja validator (*spreadsheet*) tidak pernah ditawarkan. 14 dan 15 memuat tes lengkap dengan kuncinya dan tetap ditawarkan atas keputusan pengembang. Aturan membuat ulang versi cetak tetap seperti di atas, jadi PDF yang diunduh bisa lebih lama dari Markdown-nya; kaki halamannya menyebut versinya.

## Membuat ulang berkas validator

Dari akar repo, setelah `composer install`:

```
php docs/revisi-10-analitik/dokumen-pdf/src/berkas-validator.php            # semua berkas di berkas-validator/
php docs/revisi-10-analitik/dokumen-pdf/src/berkas-validator.php --no-pdf   # tanpa buklet PDF
```

Skrip membaca 03, 08, 14–18 dan tabel di ketiga instrumen HTML (tanpa mengubahnya), menjalankan pemeriksaan jumlah butir, kebutaan B.5a, dan kode, lalu menulis lembar kerja dan `LAPORAN.md`. Satu pemeriksaan gagal berarti tidak ada berkas yang ditulis. Jalankan ulang setiap kali bank, tes, atau instrumen berubah, dan sekali lagi sebelum paket dikirim; versi berkas di setiap lembar ikut berubah. Jangan membuat ulang daftar B.5a setelah dikirim ke penilai, karena ID netralnya bisa berubah. Rinciannya di [21](../21_BERKAS_BUTIR_VALIDATOR.md#cara-membuat-ulang).
