# Instruksi agen implementasi web GELITA

Gunakan [gambar pilihan](mockup-tantangan-selesai-temanggung-16-9.webp), [keputusan desain](keterangan-mockup.md), dan [pemetaan aset](kebutuhan-aset.md) sebagai acuan perombakan layar hasil. Paket ini berada di katalog; perubahan layar dikerjakan di repositori aplikasi web yang sebenarnya.

**Tugas utamanya merombak UI lengkap. Mengganti gambar lanskap saja tidak memenuhi tugas.** Gunakan [preview interaktif](preview-ui-lengkap.html), [spesifikasi UI](spesifikasi-ui.md), sumber panel/tombol di `aset-ui/`, dan gambar keadaan di `variasi-ui/`. Preview adalah bahan integrasi, bukan alasan mengganti state/route aplikasi dengan placeholder.

## Cakupan komponen bersama

**Konsep bersama lintas kota/wilayah:** halaman “Tantangan selesai” menggunakan satu desain dan satu komponen bersama untuk seluruh kota/wilayah game. Temanggung pada gambar dan nama folder hanya contoh penerapan. Kota aktif, jenis tantangan, latar, isi hasil, label peta, dan tujuan navigasi mengikuti konfigurasi serta state permainan.

Buat satu komponen/templat hasil yang menerima konteks wilayah dan data hasil. Gunakan nama properti serta mekanisme state/config yang sudah ada di aplikasi. Hindari menyalin halaman terpisah per kota atau mengunci “Temanggung”, “SUSUN GAMBAR”, “Peta Temanggung”, dan URL contoh di komponen bersama.

Pakai skin ui.result-* yang sama lintas wilayah. Background dipilih melalui mapping aset wilayah yang tersedia; bg.result.temanggung hanya contoh. Logo tetap GELITA. Label peta bawah mengikuti nama wilayah aktif; Peta Kedu tetap tujuan global melalui menu. Berikutnya mengikuti progres tantangan dan kondisi akhir wilayah/game.

## 1. Temukan implementasi aktual

Baca AGENTS.md dan dokumentasi aktif repositori web. Temukan layar hasil, HUD, komponen navigasi, penyimpanan hasil, penilaian bintang, dan cara katalog diimpor. Jangan mengasumsikan nama file atau framework hanya dari dokumen arsip. Periksa tujuan Home, Peta Kedu, Peta wilayah, dan Berikutnya sebelum menggabungkan kontrol.

## 2. Pertahankan kontrak layar

Gunakan rasio tetap **16:9 landscape** dan mekanisme penskalaan yang ada. Susunan tetap dua kolom; jangan membuat versi potret/reflow responsif. Pertahankan safe area dan hitung target sentuh sesudah penskalaan. Gambar pilihan adalah mockup pertama; jangan menyalin tampilan versi kedua atau papan HP potret.

## 3. Bangun UI dari komponen

Teks, kartu, badge, progres dan hit area dibuat dengan HTML/CSS/SVG. Panel, permukaan tombol, HUD dan bintang terisi memakai aset WebP khusus sesuai referensi. Jangan memasang gambar mockup seluruh halaman sebagai UI. Gunakan lanskap WebP baru di belakang komponen, medali serpihan dari katalog, serta SVG kontrol yang tersedia. Gunakan ui.result-panel dan ui.result-button-* dari katalog untuk material, pinggiran dan motif. Jangan mengganti aset tersebut dengan kotak polos atau permukaan SVG versi awal. Semua label tetap teks dinamis terpisah. Gunakan reference-layout.css sebagai acuan posisi dan kompensasi padding transparan, bukan sebagai pengganti mekanisme skala aplikasi.

HUD: logo + nama wilayah di kiri; bahasa + suara + menu di kanan. Tanpa bilah background penuh, lentera tambahan, atau penghitung koleksi pada layar hasil. Keputusan terbaru: gunakan icon.language-id (bendera Indonesia) dan icon.language-en (bendera UK); jangan kembali ke medali huruf ID/EN lama.

## 4. Terapkan perilaku kontrol

- Bahasa membuka popover berisi gambar bendera Indonesia + “Bahasa Indonesia” dan bendera UK + “English”, masing-masing dengan area klik yang cukup. Tunjukkan bahasa terpilih dan simpan preferensi melalui mekanisme aplikasi. Terjemahkan isi layar melalui sistem i18n yang ada.
- Speaker berganti ikon aktif/nonaktif dan mengikuti status audio sebenarnya.
- Menu memuat Home/Beranda, layar penuh, avatar/profil, dan keluar. Peta Kedu tersedia melalui menu; gabungkan dengan Home hanya jika tujuan sama. Tampilkan avatar milik pengguna; jangan menggunakan medali serpihan sebagai avatar akun secara paksa.
- Fullscreen memakai API browser yang didukung, menangani kegagalan, dan menyelaraskan ikon masuk/keluar dengan keadaan aktual. Tidak meniru fullscreen hanya dengan CSS.
- Detail hasil tertutup saat awal. Saat dibuka, tampilkan status keberhasilan pada percobaan pertama dari data game; contoh “Belum” bukan nilai tetap. Kelola `aria-expanded` dan pertahankan panel tetap dalam kanvas saat detail terbuka.
- Peta wilayah kembali ke wilayah aktif. Berikutnya mengikuti alur tantangan, akhir wilayah, atau akhir permainan. Jangan menentukan URL atau label akhir tanpa memeriksa logika yang ada.

## 5. Pertahankan data dan aksesibilitas

Waktu, percobaan, bintang, semua-benar, total koleksi dan pesan diambil dari state permainan. Jangan hardcode hasil screenshot atau menambah hadiah saat layar dibuka ulang. Rating tiga bintang harus mempunyai label yang terbaca, misalnya “2 dari 3 bintang”.

Gunakan button/link sesuai fungsi, nama aksesibel pada ikon, indikator fokus, keyboard, dan kontras yang terukur pada latar sebenarnya. Popover/menu menutup melalui tombol tutup, klik luar bila sesuai, dan Escape; kembalikan fokus ke pemicu. Kelola fokus sebagai modal hanya jika menu benar-benar modal. Animasi kilau singkat dan menghormati prefers-reduced-motion; jangan mengubah audio yang dibekukan.

## 6. Integrasikan aset melalui pemetaan slot

Salinan `aset-katalog/` adalah bahan referensi dengan hash sumber resmi. Slot resmi tetap memakai kode katalog. Lanskap sudah terdaftar sebagai bg.result.temanggung; tujuh slot ui.result-* tercantum dalam kebutuhan-aset.md dan manifest generasi 13 Oktober. Dua SVG fullscreen masih khusus paket preview, tidak ditambahkan sebagai slot aktif. Jangan mengganti latar seluruh wilayah secara global hanya karena halaman hasil memakai latar baru.

Katalog kini 590 entri: 580 entri awal, 8 aset hasil, dan 2 ikon bahasa. Seluruh 588 entri sebelum ikon bendera dipertahankan persis. Import slot resmi ke aplikasi melalui alur katalog yang ada. Jangan mengubah file hasil build secara terpisah. Background, logo dan avatar.2 tidak perlu digenerasi ulang; efek medali dilapis tanpa mengubah gambar sumber.

## 7. Verifikasi hasil implementasi

Bandingkan komposisi dengan mockup 01. Uji komponen hasil pada seluruh kota/wilayah yang dikonfigurasi: pergantian nama, jenis tantangan, latar, label/rute peta, hasil dan kondisi tantangan terakhir. Uji landscape pada lebar tampilan game yang benar-benar didukung, termasuk 640 px dan 854 px bila didukung. Ukur area klik akhir; target 44–48 px. Uji batas panel, crop/letterbox sesuai aplikasi, menu, bahasa, speaker, detail, profil, fullscreen, keluar, kedua tujuan peta, Berikutnya, refresh, pembukaan ulang hasil, dan kondisi akhir wilayah/game.

Jalankan pemeriksaan repositori yang relevan; simpan screenshot hasil aktual. Laporkan file berubah, uji yang benar-benar dijalankan, nilai ukuran sentuh terukur, serta kendala. Paket referensi ini belum membuktikan integrasi game, budaya, lisensi, atau seluruh gambar siap produksi.
