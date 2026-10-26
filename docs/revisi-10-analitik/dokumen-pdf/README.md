# Dokumen PDF Penting

Per 26 Oktober 2026 · Bagian dari [revisi 10 analitik](../00_INDEKS.md)

Folder ini menyimpan dokumen PDF yang dibagikan ke luar tim: instrumen validasi ahli untuk tahap 2, dan dokumen lain yang perlu dicetak atau dikirim. Setiap PDF dibuat dari sumber HTML di `src/`, jadi perubahan dilakukan di HTML, lalu PDF dibuat ulang. Keputusan yang mengubah isinya dicatat di [04_CATATAN_KEPUTUSAN.md](../04_CATATAN_KEPUTUSAN.md).

## Isi

| Berkas | Isi | Status |
| --- | --- | --- |
| [instrumen-validasi-ahli-materi-v2.1.pdf](instrumen-validasi-ahli-materi-v2.1.pdf) | Instrumen Validasi Ahli Materi v2.1, 42 halaman: panduan dan teori ukur, lembar penilaian 66 butir, pelabelan buta (B.5a), relevansi per butir (B.5b), telaah Skenario Digital, kode kesalahan, skala sikap, tes awal–akhir dan angket, analisis, lampiran | Draf, menunggu persetujuan pemilik proyek |
| [instrumen-validasi-pengamat-budaya-v2.1.pdf](instrumen-validasi-pengamat-budaya-v2.1.pdf) | Instrumen Validasi Pengamat Budaya v2.1, 31 halaman: panduan, lembar penilaian 53 butir, matriks 46 objek budaya, telaah *Periksa Unggahan* dan butir PBK, konfirmasi cek fakta, skala sikap, analisis, lampiran | Draf, menunggu persetujuan pemilik proyek |
| [instrumen-validasi-ahli-media-v1.1.pdf](instrumen-validasi-ahli-media-v1.1.pdf) | Instrumen Validasi Ahli Media v1.1 (langkah 1), 29 halaman: panduan, lembar penilaian 67 butir, uji perangkat, catatan temuan, telaah layar game sekarang, telaah 27 mock-up layar baru, analisis, lampiran | Draf, menunggu persetujuan pemilik proyek |
| `src/` | Sumber HTML ketiga instrumen, gaya cetak bersama `_instrumen.css`, dan skrip `render.mjs` | — |
| `arsip/` | Draf asli pemilik proyek yang menjadi dasar instrumen di atas: `instrumen-validasi-ahli-gelita-r10-v2.0-draf.pdf` (ahli materi dan pengamat budaya dalam satu berkas, 23 Oktober 2026) dan `instrumen-validasi-ahli-media-gelita-v1.0-draf.pdf` (23 Oktober 2026) | Arsip, tidak diubah |

Ringkasan isi instrumen dan bedanya dari usulan telaah ada di [07 → Instrumen versi 2.1 dan 1.1](../07_TELAAH_INSTRUMEN_VALIDASI.md#instrumen-versi-21-dan-11).

## Sebelum dikirim ke validator

1. Pemilik proyek menyetujui isi instrumen.
2. Hapus kotak *STATUS* di sampul setiap HTML, lalu buat ulang PDF-nya.
3. Lampiran "Perubahan dari versi sebelumnya" (D.4) untuk peneliti; tidak perlu dicetak untuk validator.
4. Lembar kerja (*spreadsheet*) untuk lembar per butir disiapkan dari kode butir di bank soal, tes, dan angket.

## Membuat ulang PDF

Dari akar repo:

```
node docs/revisi-10-analitik/dokumen-pdf/src/render.mjs                 # semua
node docs/revisi-10-analitik/dokumen-pdf/src/render.mjs instrumen-validasi-ahli-media-v1.1   # satu berkas
```

Skrip memakai Playwright Chromium yang sudah terpasang (jangan menjalankan `playwright install`) dan huruf Inter serta Liberation Serif dari sistem. Ukuran halaman diatur di `_instrumen.css`: A4 tegak, dan A4 mendatar untuk lembar per butir yang lebar (bagian dengan `class="wide"`). Teks kaki halaman diambil dari `<meta name="pdf-footer">` di setiap HTML. Setelah membuat ulang, periksa bahwa tidak ada tabel yang menyisakan satu atau dua baris di halaman berikutnya.
