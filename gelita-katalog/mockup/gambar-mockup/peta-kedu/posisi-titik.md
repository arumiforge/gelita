# Posisi titik Peta Kedu — acuan web dan kandidat 02

Terbaru04: [spesifikasi](revisi-04/spesifikasi.md) memuat tiga polygon wilayah dan posisi target relatif master diorama baru. Titik03/02 dan posisi web di bawah merupakan riwayat; bukan nilai yang otomatis dipasang pada game.

Diperiksa 14 Oktober 2026 melalui tab Chrome pengguna pada [Peta Kedu asli](https://gelita.id/peta#app), secara baca saja. Sumber persentase adalah atribut style parent map-point di DOM; bukan pengukuran geografis.

| Wilayah | left asli | top asli | Bentuk pada kandidat 02 |
|---|---:|---:|---|
| Temanggung | 65% | 41% | Posisi relatif sama, tengah kanan |
| Magelang | 56% | 71% | Sedikit lebih kanan dan lebih bawah |
| Wonosobo | 28% | 55% | Posisi horizontal serupa, sedikit lebih bawah |

Persentase dihitung terhadap bidang gambar peta, bukan seluruh layar 1920 × 1080. Acuan implementasi awal tetap tiga nilai asli ini. Ukuran frame peta, pin, label dan transform titik harus diperhitungkan; koordinat layar absolut akan berubah saat bidang peta diberi ruang tersendiri untuk narasi.

Kandidat 02 adalah raster AI, sehingga pusat pin/kelompok label tidak menjamin tepat pada persentase CSS. Catatan pergeseran di atas merupakan pengamatan visual, bukan pengukuran otomatis atau koordinat baru yang sudah dipasang. Pada implementasi, periksa overlay tiga titik asli terhadap gambar terpilih. Bila harus mengikuti posisi raster, ukur ulang anchor yang sama, catat nilai sebelum/sesudah, dan jangan diam-diam mengganti lokasi tersimpan.

Tidak mengubah titik pada aplikasi asli. Pada permintaan terbaru 14 Oktober, pengguna mengizinkan perombakan bebas mockup/ukuran titik dan usulan diorama baru untuk HP; pembatasan konfirmasi perombakan total pada kandidat 02 telah digantikan izin ini. [Gambar kandidat 02](kandidat/mockup-peta-kedu-kandidat-02-16-9.webp) · [Manifest](../../../provenance/peta-kedu-mockup-02-20261014.json).

## Usulan baru kandidat 03

Koordinat asli di atas merupakan riwayat acuan web, bukan batas komposisi baru. Tiga kelompok kota diperbesar dan dijauhkan: Wonosobo kiri/tengah, Temanggung kanan/atas, Magelang kanan/bawah. [Rencana kotak sentuh](target-sentuh-03.json) mengatur kotak yang mencakup medali/label, bukan posisi geografis.

Dalam rencana bidang peta x48/y170/w1240/h520, pusat kotak Wonosobo sekitar 27,9%/53,8%, Temanggung 72,3%/26,2%, Magelang 65,0%/74,2%. Angka ini merupakan usulan untuk implementasi, bukan pengukuran pin raster atau perubahan web yang sudah dijalankan. Temanggung dinaikkan dan lebih ke kanan, Magelang lebih ke kanan, Wonosobo relatif tetap agar kelompok besar tidak berpotongan. [Panduan sentuh](spesifikasi-sentuh-03.md) · [Gambar 03](kandidat/mockup-peta-kedu-kandidat-03-16-9.webp).
