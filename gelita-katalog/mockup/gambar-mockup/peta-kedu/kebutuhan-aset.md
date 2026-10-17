# Pemetaan awal Peta Kedu

**Terbaru04 —15Oktober:** [master diorama baru](revisi-04/aset/diorama.kedu-tiga-wilayah.webp) tanpa UI sudah tersedia, dengan tiga potongan clip-path dalam preview. [Paket04](revisi-04/README.md) memetakan penggunaan langsung ui.result-panel/button-primary/button-secondary/button-detail/hud-control/star, pin/avatar/logo/font/bendera resmi dan SVG Kenali baru. Footer Pustaka/Profil dipindah ke menu, kontrol cerita ikon/dot/efek ketik hidup. Ini menggantikan kebutuhan komposisi03 di bawah; riwayat tetap disimpan.

**Arah terbaru kandidat 03:** pengguna mengizinkan diorama baru, pengelompokan dan ukuran UI untuk HP landscape. Peta memakai usulan diorama baru dalam full UI; map.kedu lama tidak wajib dipakai dan tidak diganti. Background tanpa UI perlu dibuat tersendiri setelah komposisi dipilih. Panel wilayah menjadi satu komponen kontekstual; Kenali berikon lentera besar, Putar teal, Berikutnya emas, HUD bahasa/speaker/menu mengikuti Tantangan selesai. Bahan di tabel bawah adalah riwayat/acuan, bukan kewajiban menyalin struktur kandidat 02. [Panduan sentuh](spesifikasi-sentuh-03.md).

Revisi kandidat 02 mempertahankan relief kiri, tiga kartu wilayah kanan, narasi Jaka, HUD/footer dan [koordinat asli](posisi-titik.md) sebagai acuan. Dua screenshot pengguna dan DOM web asli menambah acuan struktur/fungsi, bukan slot gambar game baru. Kartu kompak, narasi terpisah dan pin yang sedikit bergeser pada raster dicatat sebagai usulan visual; konfigurasi web belum diubah.

Kandidat visual belum dipecah menjadi aset UI. Tidak ada kode slot baru atau penggantian gambar katalog.

| Acuan | Peran pada generasi | Perlakuan saat implementasi |
|---|---|---|
| map.kedu | Isi relief, gunung, sungai, teras dan motif candi | Evaluasi reuse dan keterbacaan pin |
| bg.map | Kabut dan suasana latar | Evaluasi reuse sesuai finishing komposisi terpilih |
| Mockup Tantangan selesai pilihan | Material navy/emas, ornamen dan hierarki | Reuse skin UI yang cocok |
| reference.01 | Identitas Jaka/syal merah | Reuse pose resmi assets/karakter |
| map.pin.temanggung/magelang/wonosobo | Kebutuhan pin dari brief | Reuse aset pin lokal; tidak dikirim sebagai input generasi ini |
| icon.language-id/en | Bendera HUD dan pilihan bahasa | Reuse ikon resmi terbaru |

Panel, tombol, label pin, jalur dan progres pada kandidat adalah bagian gambar AI, belum merupakan file komponen terpisah. Teks/angka/progres/hit area dibuat sebagai HTML/SVG saat preview diimplementasikan. Audio preview dapat memakai music/map.mp3 dan sfx/lock.mp3 yang sudah tersedia; produksi audio tidak diperlukan.

[Brief dan tautan aset awal](../../persiapan/peta-kedu/README.md) · [Paket kandidat](README.md).
