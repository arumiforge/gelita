# Keterangan mockup pilihan

## Pilihan dan cakupan

Pengguna mengonfirmasi **landscape 16:9 pertama pada revisi terakhir**. Versi kedua dengan tombol lebih besar tidak dipakai. Paket menyimpan satu gambar pilihan; tidak menggabungkan versi yang ditolak.

Gambar AI pilihan memiliki ukuran asli **1672 × 941**, sangat dekat dengan 16:9 akibat pembulatan keluaran. WebP referensi mempertahankan dimensi dan piksel RGB aslinya; gambar tidak diregangkan agar seolah-olah keluaran aslinya tepat 16:9. Kanvas implementasi harus tetap tepat 16:9, misalnya 1920 × 1080. Lanskap terpisah disiapkan tepat 1920 × 1080 dengan normalisasi proporsional bila diperlukan.

## Cakupan lintas kota

**Konsep bersama lintas kota/wilayah:** halaman “Tantangan selesai” menggunakan satu desain dan satu komponen bersama untuk seluruh kota/wilayah game. Temanggung pada gambar dan nama folder hanya contoh penerapan. Kota aktif, jenis tantangan, latar, isi hasil, label peta, dan tujuan navigasi mengikuti konfigurasi serta state permainan.

Nama Temanggung pada mockup tidak membatasi penggunaan desain. Panel, tombol, HUD, rating, kartu, badge, progres, menu, dan Detail hasil dipakai bersama. Teks “SUSUN GAMBAR” juga contoh jenis tantangan, bukan label tetap untuk semua hasil. Nama folder dan gambar pilihan dipertahankan untuk menjaga rujukan yang sudah tercatat.

## Keputusan desain

- HUD tanpa bilah latar penuh. Logo GELITA di kiri; nama wilayah di sebelah kanannya.
- Kanan HUD: bahasa, speaker, dan menu. Logo boleh mempertahankan lentera kecil yang memang bagian dari logonya.
- Tidak ada lentera tambahan di atas judul dan tidak ada progres serpihan di HUD layar ini.
- Panel navy dengan pinggiran emas; judul kemenangan, dua bintang terisi dan satu bintang kosong pada contoh ini.
- Koleksi memakai medali katalog `avatar.2`, bukan lentera baru. Total dan progres koleksi hanya muncul sekali.
- Waktu dan percobaan menjadi dua kartu ringkas. “Semua benar” ditandai hijau bersama centang.
- Informasi “Berhasil pada percobaan pertama: Belum” tersedia di **Detail hasil**, tertutup pada keadaan awal.
- Tombol bawah: kembali ke peta wilayah, Detail hasil, dan Berikutnya. Label pendek “Peta Temanggung” pada gambar berarti kembali ke peta wilayah tersebut.
- Home/Beranda, layar penuh, avatar/profil, dan keluar masuk menu. Peta Kedu tetap tersedia melalui menu; Home dan Peta Kedu hanya boleh digabung jika rute aktualnya sama.
- Bahasa tetap di HUD. Keputusan ikon terbaru 13 Oktober: klik membuka dua pilihan dengan bendera Indonesia/UK dan label “Bahasa Indonesia” / “English”. Speaker tidak dipindahkan ke menu.

## Konten contoh, bukan nilai tetap

Contoh gambar: waktu 2:05, percobaan 2 dari 3, dua dari tiga bintang, semua jawaban benar, total serpihan 1 dari 12, dan belum berhasil pada percobaan pertama. Implementasi mengambil nilai serta status dari data game; jangan membuat hadiah +1, XP, atau koin dari contoh ini.

## Sentuhan dan layar tetap

Aplikasi asli memakai kanvas landscape 16:9 dengan susunan tetap. Ikuti penskalaan yang sudah ada; jangan mengubah susunan untuk potret atau menambahkan reflow responsif.

Kesetiaan visual memakai mockup 01. Ukuran sentuh belum terbukti dari gambar. Ukur area klik sesudah penskalaan pada HP sebenarnya. Rumusnya `target layar = target kanvas × lebar tampilan / lebar kanvas`. Contoh: 144 px pada kanvas 1920 px menjadi 48 px saat tampil selebar 640 px.

Utamakan perluasan area klik transparan serta jarak yang cukup tanpa mengganti versi visual yang dipilih. Target area sentuh proyek 44–48 px pada tampilan akhir. Bila ruang pada ukuran layar yang didukung tidak mencukupi, dokumentasikan kendalanya; jangan menyatakan mockup sudah lolos uji sentuh.

## Batas paket

Mockup berarti **halaman lengkap beserta UI**. Lanskap tanpa UI hanya bahan pendukung. Gambar pilihan utama sudah memuat UI; paket juga menyediakan sumber komponen, preview interaktif dan empat gambar keadaan hasil render kode. Lihat [spesifikasi UI](spesifikasi-ui.md). Turunan ini tidak mengganti pilihan mockup 01.

Ini acuan perombakan desain, bukan perubahan aplikasi. Lanskap, ikon vektor tambahan, dan salinan aset berada di paket mockup. Pada revisi 13 Oktober, delapan entri baru ditambahkan ke data.json tanpa mengubah 580 entri lama. Status `user_override` dan `previous_style_review` tetap dipertahankan. Tidak ada perubahan atau rekaman audio.

Revisi 13 Oktober: panel, tombol primer/peta/detail, alas HUD dan bintang kini memakai gambar baru sesuai contoh; logo, medali, ID/EN, ikon dan lanskap dipakai ulang. Posisi dan ukuran mengikuti reference-layout.css. Skin lama disimpan sebagai sketsa awal. Gambar pilihan tetap acuan utuh; render kode adalah hasil penerapan dengan ornamen generasi yang sedikit berbeda.
