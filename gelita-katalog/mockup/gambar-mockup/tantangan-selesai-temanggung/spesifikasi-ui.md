# Spesifikasi UI lengkap — mockup 01

## Referensi utama

[Gambar pilihan](mockup-tantangan-selesai-temanggung-16-9.webp) memuat halaman lengkap dan tetap menjadi acuan desain yang dipilih. [Preview kode](preview-ui-lengkap.html) menyediakan bahan UI dan contoh interaksi yang bisa dicoba. Gambar di `variasi-ui/` adalah hasil render preview kode, bukan gambar pilihan baru dan bukan versi kedua dari diskusi.

## Lapisan dan ukuran kanvas preview

Kanvas 1920 × 1080 dengan penskalaan seragam `min(lebar layar / 1920, tinggi layar / 1080)`. Selisih rasio menghasilkan letterbox; susunan elemen tidak berubah menjadi potret. Ini mekanisme preview, bukan klaim bahwa kode aplikasi asli sudah diperiksa atau diganti.

| Lapisan | Sumber / ukuran dasar preview | Isi dinamis di atasnya |
|---|---|---|
| Lanskap | WebP 1920 × 1080 yang sudah selesai | Tidak ada UI tertanam |
| HUD | Kontrol 132 × 144; permukaan visual 102 × 102 | Logo, nama wilayah, ID/EN, speaker, menu |
| Panel hasil | WebP ui.result-panel; x=199.8, y=218.1, lebar=1525, tinggi=742.6 | Judul, bintang, koleksi, statistik, badge, pesan, tombol |
| Tombol bawah | Tinggi area klik 132 | Peta wilayah, Detail hasil, Berikutnya |
| Popover bahasa | Panel 720 × 360; baris klik tinggi 144 | Gambar bendera Indonesia/UK, nama bahasa, tanda pilihan |
| Menu | Panel 650 × 828; baris klik tinggi 144 | Avatar/profil, Home/Peta Kedu, fullscreen, keluar |
| Detail hasil | Panel 840 × 444 | Berhasil pada percobaan pertama: Belum |

Permukaan utama UI tersedia sebagai WebP transparan tanpa tulisan; kartu dan overlay tersedia sebagai SVG terpisah di [kebutuhan aset](kebutuhan-aset.md). Semua skin tidak memuat label sehingga penerjemahan, perubahan hasil, hit area dan aksesibilitas tetap bisa ditangani aplikasi. Detail menggunakan panel terpisah agar tidak mendorong isi keluar dari kanvas.

## Keadaan dan perilaku

- Halaman utama: semua overlay tertutup; dua bintang terisi, waktu 2:05, percobaan 2 dari 3, koleksi 1/12 adalah state contoh.
- Bahasa: pemicu ID membuka dua baris berisi bendera Indonesia/UK dari katalog dan nama bahasa. Pilihan EN/ID mengganti teks preview dan ikon pemicu; tidak menambahkan kontrol bahasa ke menu.
- Menu: grup profil/avatar, Home/Peta Kedu, layar penuh, dan keluar. Speaker serta bahasa tetap pada HUD. Escape/tutup mengembalikan fokus ke pemicu.
- Detail: menampilkan informasi percobaan pertama tanpa kartu negatif permanen pada hasil utama.
- Speaker: preview menampilkan keadaan aktif/nonaktif; tidak membuat atau memutar rekaman audio.
- Layar penuh: kode memakai API browser dan mengubah ikon sesuai keadaan; dukungan fullscreen pada perangkat asli belum disahkan.
- Tombol navigasi, profil, dan keluar adalah placeholder referensi. Integrasi harus memakai route/account/state aplikasi asli. Preview tidak menyimpan hadiah, hasil, atau akun.

## Ukuran sentuh dan batas pemeriksaan

Pada game selebar 640 px: hit area HUD 132/3 × 144/3 = 44 × 48 px; baris overlay tinggi 144/3 = 48 px; tombol bawah 132/3 = 44 px. Permukaan ikon boleh lebih kecil daripada hit area. Preview menguji batas dan target ini pada 640 × 360 serta 854 × 480; hasil aktual dicatat dalam [laporan browser](../../../reports/mockup-ui-browser-check.json).

Uji preview tidak mengesahkan ukuran klik atau perilaku pada aplikasi asli. Saat integrasi, ukur lagi skala dan area klik aktual game. Jangan mengganti gambar pilihan pertama dengan versi kedua untuk mengatasi ukuran; gunakan padding hit area dan jarak yang cukup.

## Kecocokan revisi 13 Oktober

Posisi diukur dari acuan 1672 × 941 dan dipetakan ke kanvas tetap 1920 × 1080. Logo katalog lebar 400 × 150 pada (22,14); divider x=400 dan nama Temanggung (433,56). Grup ID/speaker/menu berawal (1496,11), jarak 6. Medali berukuran 335 pada koordinat relatif panel (59.7,211.2). Kartu waktu/percobaan berukuran 280 × 165.3 dan 298.5 × 165.3. Tombol bawah berlebar 459.3 / 300.8 / 578.8 dengan area klik tinggi 132.

`reference-layout.css` mengompensasi padding alpha berdasarkan visible_bounds pada manifest, sehingga tepi skin tepat di koordinat visual. Font Plus Jakarta Sans memakai salinan font dari aplikasi asli. Detail ornamen hasil generasi berbeda sedikit dari screenshot; referensi pilihan tetap utuh dan tidak diganti. Kemiripan visual diperiksa dengan melihat render, bukan mengklaim kesamaan piksel atau pengujian aplikasi asli.

Ikon bahasa aktif berukuran 256 × 256 WebP transparan. Pada HUD preview, gambar 104 × 104 berada dalam hit area 132 × 144; tidak menggunakan crop pusat medali ID/EN lama. Klik English mengganti ikon pemicu menjadi bendera UK.

## Satu komponen hasil untuk seluruh kota/wilayah

Temanggung adalah konteks contoh visual. Gunakan komponen hasil yang sama pada seluruh kota/wilayah dan jenis tantangan dengan tata letak tetap 16:9.

| Dipakai bersama | Mengikuti konteks permainan |
|---|---|
| Layout HUD/panel, ukuran dan posisi elemen | Nama kota/wilayah dan judul jenis tantangan |
| Skin panel, tombol, alas HUD, bintang dan efek medali | Background hasil melalui pemetaan wilayah aplikasi |
| Komponen dua kartu, badge, progres dan Detail hasil | Waktu, percobaan, rating, status jawaban, koleksi, pesan dan detail |
| Bahasa, speaker dan menu | Preferensi bahasa/audio serta akun pengguna |
| Posisi tombol peta dan Berikutnya | Label Peta {namaWilayah}, rute peta wilayah dan alur tantangan berikutnya |

Peta Kedu tetap tujuan global melalui menu; tombol peta bawah menuju wilayah aktif. Background bg.result.temanggung adalah contoh latar, bukan kewajiban membuat gambar baru untuk setiap kota. Gunakan aset dan mapping yang tersedia di aplikasi.
