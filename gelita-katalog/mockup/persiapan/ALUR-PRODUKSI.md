# Alur pembuatan dan pemasangan mockup

Permintaan eksplisit15Oktober untuk Peta Kedu mengizinkan preview interaktif kandidat04 sebelum dipilih. Objek candidate kini dapat berisi preview dan states (coded-preview/candidate-history) yang selalu berlabel belum dipilih; status halaman tetap planned. Tidak perlu menganggap preview sebagai persetujuan desain atau integrasi game. [Paket04](../gambar-mockup/peta-kedu/revisi-04/README.md).

1. Baca brief halaman, prompt rencana, arah visual, dan fungsi halaman aplikasi aktual. Tentukan state awal serta teks ID/EN dari naskah terbaru. Gunakan data contoh fiktif untuk akun/profil.
2. Buat kandidat full UI landscape 1920 × 1080. Jika perlu raster baru, simpan prompt aktual, input referensi, hasil native, metode normalisasi dan hash dalam provenance. Pakai WebP; sumber SVG kontrol tetap SVG. Gambar pilihan yang sudah ada tidak ditimpa.
3. Simpan kandidat di `mockup/gambar-mockup/<nama-halaman>/kandidat/`. Catat bahwa kandidat belum dipilih; jangan menyamakan status gaya aset dengan persetujuan mockup.
4. Sesudah desain dipilih, catat pilihan memakai [template keputusan](TEMPLATE-KEPUTUSAN.md). Pecah kebutuhan aset dan map kode slot. Gunakan kembali aset yang cocok; buat aset baru hanya untuk kebutuhan yang belum ada.
5. Buat preview HTML/CSS/JS tetap 16:9 dengan HUD, navigasi demo, keyboard/fokus, bahasa, state/error relevan dan audio setelah tindakan pengguna. Simpan empat atau lebih state sebagai WebP di `variasi-ui/<state>/`; jumlah mengikuti fungsi halaman, bukan kewajiban empat untuk semua halaman.
6. Masukkan halaman di [inventaris](../catalog.json): `status: available`, `image`, `preview`, `brief`, `states`. `image` merupakan referensi yang sudah dipilih; state render diberi `kind: coded-preview`. Jika masih kandidat, biarkan status planned dan gunakan objek `candidate` (label, image, brief, prompt) dengan label belum dipilih; jangan mengisi image/preview utama. Dukungan kandidat ditambahkan untuk Peta Kedu 14 Oktober. Jangan mengubah jumlah aset utama untuk gambar turunan mockup.
7. Jalankan `node build.mjs`, `node scripts/validate.mjs`, dan pemeriksaan browser relevan. Ukur raster/alpha dan baca tautan lokal. Gunakan [template pemeriksaan](TEMPLATE-PEMERIKSAAN.md); catat hanya pemeriksaan nyata dalam PROGRESS.
8. Integrasi ke aplikasi asli merupakan pekerjaan berikutnya: cocokkan kode slot, rute, state, gameplay, bilingual, volume, penyimpanan progres dan data akun. Katalog/preview tidak menjadi bukti integrasi aplikasi.

Dokumen minimal setiap paket: README, keterangan-mockup, list-file, kebutuhan-aset, prompt-detail, petunjuk-agent, spesifikasi-ui, preview HTML, gambar pilihan serta state. Gunakan struktur [paket hasil](../gambar-mockup/tantangan-selesai-temanggung/README.md) sebagai contoh. Produksi/generasi bertanggal tidak dijalankan ulang untuk pemeliharaan rutin.

[Daftar persiapan](README.md) · [Panduan pemeliharaan](../../docs/MAINTENANCE.md).
