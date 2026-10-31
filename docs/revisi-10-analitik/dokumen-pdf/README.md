# Dokumen PDF Penting

Per 31 Oktober 2026 · Bagian dari [revisi 10 analitik](../00_INDEKS.md)

Folder ini menyimpan dokumen PDF yang dibagikan ke luar tim: instrumen validasi ahli untuk tahap 2, dan dokumen lain yang perlu dicetak atau dikirim, termasuk berkas serah terima pengembang untuk pertemuan dengan peneliti. Setiap PDF dibuat dari sumber HTML di `src/`, jadi perubahan dilakukan di HTML, lalu PDF dibuat ulang. Keputusan yang mengubah isinya dicatat di [04_CATATAN_KEPUTUSAN.md](../04_CATATAN_KEPUTUSAN.md).

## Isi

| Berkas | Isi | Status |
| --- | --- | --- |
| [instrumen-validasi-ahli-materi-v2.1.pdf](instrumen-validasi-ahli-materi-v2.1.pdf) | Instrumen Validasi Ahli Materi v2.1, 43 halaman (diperbarui 27 Oktober 2026): panduan dan teori ukur, lembar penilaian 66 butir, pelabelan buta (B.5a), relevansi per butir (B.5b), telaah Skenario Digital, kode kesalahan, skala sikap, tes awal–akhir dan angket, analisis, lampiran | Disetujui pengembang 27 Oktober 2026; kotak status di sampul dihapus |
| [instrumen-validasi-pengamat-budaya-v2.1.pdf](instrumen-validasi-pengamat-budaya-v2.1.pdf) | Instrumen Validasi Pengamat Budaya v2.1, 34 halaman (diperbarui 27 Oktober 2026 dengan cek fakta [25](../25_CEK_FAKTA_BUDAYA.md)): panduan, lembar penilaian 53 butir, matriks 46 objek budaya, telaah *Periksa Unggahan* dan butir PBK, konfirmasi cek fakta, skala sikap, analisis, lampiran | Disetujui pengembang 27 Oktober 2026; kotak status di sampul dihapus |
| [instrumen-validasi-ahli-media-v1.1.pdf](instrumen-validasi-ahli-media-v1.1.pdf) | Instrumen Validasi Ahli Media v1.1 (langkah 1), 29 halaman: panduan, lembar penilaian 67 butir, uji perangkat, catatan temuan, telaah layar game sekarang, telaah 27 mock-up layar baru, analisis, lampiran | Disetujui pengembang 27 Oktober 2026; kotak status di sampul dihapus; teks token disamakan dengan 28 ("6 huruf dan angka", tanpa O, 0, I, 1, L) dan PDF dibuat ulang 30 Oktober 2026 atas permintaan pengembang |
| [serah-terima-peneliti.pdf](serah-terima-peneliti.pdf) | Berkas Serah Terima ke Peneliti, 32 halaman (31 Oktober 2026): pegangan pengembang untuk pertemuan dengan peneliti. Posisi pekerjaan, daftar periksa enam paket dokumen, isian dan tanda tangan yang hanya bisa diberikan peneliti, keputusan terbuka, keputusan yang sudah diterapkan beserta alasannya (alasan yang disimpulkan, bukan dari 04, ditandai †), tanya jawab, ketidaksesuaian antardokumen, `needs_verification`, keterbatasan, agenda, dan lembar catatan keputusan. Dokumen internal, tidak dikirim ke ahli atau komite | `draf pengembang`, `asal: pengembang` |
| [`berkas-validator/`](berkas-validator/) | Berkas per butir untuk validator, dibuat skrip dari 03, 08, 14–18 dan tabel instrumen: daftar buta B.5a (lembar kerja dan buklet PDF), daftar B.5b, lembar kerja B.6–B.9 ahli materi, B.7 versi buta, lembar kerja B.5–B.8 pengamat budaya, lembar kerja B.8 ahli media, `LAPORAN.md`; kunci B.5a di `khusus-peneliti/` (tidak dikirim). Penjelasannya di [21](../21_BERKAS_BUTIR_VALIDATOR.md) | Dibuat ulang 27 Oktober 2026 dari instrumen yang disetujui; dibuat ulang sekali lagi tepat sebelum dikirim ([20](../20_PAKET_VALIDASI.md), prasyarat no. 7) |
| `src/` | Sumber HTML ketiga instrumen dan berkas serah terima, gaya cetak bersama `_instrumen.css`, skrip `render.mjs`, dan skrip berkas validator `berkas-validator.php` (dengan folder `berkas-validator/` dan `berkas-validator-pdf.mjs`) | — |
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

## Membuat ulang berkas validator

Dari akar repo, setelah `composer install`:

```
php docs/revisi-10-analitik/dokumen-pdf/src/berkas-validator.php            # semua berkas di berkas-validator/
php docs/revisi-10-analitik/dokumen-pdf/src/berkas-validator.php --no-pdf   # tanpa buklet PDF
```

Skrip membaca 03, 08, 14–18 dan tabel di ketiga instrumen HTML (tanpa mengubahnya), menjalankan pemeriksaan jumlah butir, kebutaan B.5a, dan kode, lalu menulis lembar kerja dan `LAPORAN.md`. Satu pemeriksaan gagal berarti tidak ada berkas yang ditulis. Jalankan ulang setiap kali bank, tes, atau instrumen berubah, dan sekali lagi sebelum paket dikirim; versi berkas di setiap lembar ikut berubah. Jangan membuat ulang daftar B.5a setelah dikirim ke penilai, karena ID netralnya bisa berubah. Rinciannya di [21](../21_BERKAS_BUTIR_VALIDATOR.md#cara-membuat-ulang).
