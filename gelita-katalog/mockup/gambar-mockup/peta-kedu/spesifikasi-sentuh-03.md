# Kandidat 03 — panduan sentuh dan pengelompokan

Permintaan 14 Oktober 2026 mengizinkan desain ulang bebas mockup untuk HP siswa. Pengguna akan mengimplementasikan kode sistem; panduan ini merupakan usulan geometri, bukan kode game atau hasil uji perangkat. Halaman tetap landscape 16:9 dengan skala seragam.

## Kelompok dan interaksi yang diusulkan

1. HUD: logo/konteks, serpihan dan tiga kontrol besar bahasa/speaker/menu. Menu menampung profil, beranda, fullscreen dan keluar seperti Tantangan selesai; bahasa tetap memiliki pilihan Indonesia/English memakai bendera resmi.
2. Peta: tiga pin sebagai tiga pilihan wilayah. Area sentuh meliputi medali dan seluruh nama/status kota. Jangan membuat target lentera/cek/bintang kecil yang berdesakan.
3. Panel wilayah: satu komponen berubah sesuai pin yang dipilih. Nama, tingkat, status, progres, bintang, Kenali/Belum didengar dan Lanjut tetap berasal dari state aplikasi. Contoh memilih Wonosobo; tidak mengubah urutan kesulitan atau aturan membuka wilayah.
4. Cerita: avatar dan dialog lebih tinggi, terpisah dari footer. Putar teal bulat dengan simbol play; Berikutnya emas memanjang dengan chevron; Sebelumnya navy/abu-abu saat disabled. Label dan bentuk membedakan fungsi selain warna. 1/3 bukan target klik.
5. Footer: Pustaka Kedu dan Profil dengan ikon/label besar. Avatar dan hit area dialog tidak masuk ke hit area navigasi bawah.

## Ukuran rencana untuk kode

[JSON geometri](target-sentuh-03.json) memakai kanvas 1920 × 1080. Skala = min(lebar viewport / 1920, tinggi viewport / 1080). Area klik boleh lebih besar daripada skin gambar, tetapi tidak menimpa target lain.

| Kelompok | Area dasar rencana | Pada 640 × 360 |
|---|---|---|
| Bahasa, speaker, menu | 144 × 144 | 48 × 48 px |
| Tiap pilihan kota termasuk nama | 288–300 × 224–240 | Minimum sisi pendek 74,7 px |
| Kenali dan Lanjut wilayah | 244 × 144 | 81,3 × 48 px |
| Sebelumnya dan Putar cerita | 144 × 144 | 48 × 48 px |
| Berikutnya cerita | 360 × 144 | 120 × 48 px |
| Pustaka / Profil | Tinggi 132; lebar 600 / 440 | Tinggi 44 px |
| Ringkasan serpihan jika tetap tombol | 520 × 132 | 173,3 × 44 px |

Rencana memberi jarak 46 px dasar antara panel cerita dan target footer (15,3 px pada 640); avatar memberi jarak 49 px dasar. Target rencana berada dalam kanvas dan tidak saling berpotongan. Perhitungan minimum 44 px lulus pada 640 × 360 dan 854 × 480. **Ini pemeriksaan angka rencana, bukan bukti bahwa raster AI mengikuti kotak persis atau UI game sudah memiliki area sentuh tersebut.** Sesuaikan posisi/skin saat implementasi agar padding tidak mengurangi jarak.

Pada perangkat dengan viewport game lebih kecil dari 640 × 360 setelah toolbar/letterbox, hit area rencana bisa lebih kecil; ukur bidang game nyata dan sesuaikan skala/padding/komposisi sebelum memakai angka ini. Jangan mengklaim pengujian perangkat dari gambar ukuran kecil.

## Diorama dan teks

Diorama pada kandidat 03 adalah usulan baru dengan ruang lebih jelas bagi medali/label. Tidak wajib memakai map.kedu lama. Buat background terpisah sesudah komposisi dipilih: jangan ikut memanggang pin, panel, teks atau tombol ke background aplikasi. Lokasi ilustratif bukan peta geografis terverifikasi.

Cerita pada mockup hanya cuplikan awal agar ukuran teks jelas. Implementasi menampilkan naskah asli dalam potongan yang sesuai ruang, mempertahankan urutan narasi dan akses sebelumnya/play/berikutnya. Label, progres, bintang dan bahasa tetap teks/state hidup.

[Paket](README.md) · [Gambar 03](kandidat/mockup-peta-kedu-kandidat-03-16-9.webp) · [640 × 360](../../../review/mockup-peta-kedu-03-640x360.webp) · [854 × 480](../../../review/mockup-peta-kedu-03-854x480.webp) · [Pemeriksaan rencana](../../../reports/peta-kedu-candidate-03-check-20261014.json).
