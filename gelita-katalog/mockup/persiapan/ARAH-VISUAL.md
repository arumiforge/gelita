# Arah visual untuk mockup berikutnya

Tanggal 14 Oktober 2026. Acuan utama adalah [mockup hasil pilihan](../gambar-mockup/tantangan-selesai-temanggung/mockup-tantangan-selesai-temanggung-16-9.webp) dan [preview UI](../gambar-mockup/tantangan-selesai-temanggung/preview-ui-lengkap.html). Peta Kedu memiliki [kandidat visual](../gambar-mockup/peta-kedu/README.md); 11 halaman rencana lainnya belum memiliki gambar baru. Seluruh 12 halaman berikutnya belum dipilih pengguna.

- Kanvas tepat 1920 × 1080, landscape tetap 16:9; skala seragam, susunan tetap. Setiap ekspor harus memuat halaman lengkap, HUD, isi, panel dan tombol.
- Material 3D hangat, navy gelap, emas antik, krem untuk teks; ornamen membingkai isi, tidak mengurangi keterbacaan. Komposisi dan tekstur mengikuti hasil pilihan, bukan gaya 2D arsip.
- Jaka mengikuti [reference.01](../../assets/referensi/reference.01.webp), bersyal merah; Mbah Kedu mengikuti [reference.02](../../assets/referensi/reference.02.webp), tanpa syal Jaka. Gunakan pose di assets/karakter; jangan membuat karakter tambahan tanpa kebutuhan halaman.
- HUD: logo dan konteks wilayah di kiri; bahasa, speaker, menu di kanan. [Indonesia](../../assets/ikon/icon.language-id.webp) dan [English/UK](../../assets/ikon/icon.language-en.webp) menjadi ikon bahasa bersama; pilihan bahasa selalu disertai nama bahasa.
- Teks, angka, rating, progres dan hit area tetap elemen HTML/SVG yang hidup. Raster skin tidak boleh memuat isi dinamis yang harus diterjemahkan.
- Skala desain diperiksa pada 1920 × 1080, 854 × 480 dan 640 × 360; sasaran hit area minimum 44 px setelah skala. Ini pemeriksaan lanskap, bukan permintaan reflow aplikasi potret.
- Setiap halaman memakai context/state aplikasi; contoh wilayah tidak membatasi komponen untuk satu kota. Pertahankan aturan permainan, urutan narasi, rute dan nilai tersimpan.
- Media faktual pustaka tetap dokumenter. Adegan/objek budaya harus ditandai pending jika belum diverifikasi; status user_override katalog tidak menggantikan review isi.
- Audio preview memakai [sembilan audio nonnarasi](../../assets/audio/README.md); tidak autoplay. Musik lembut di bawah efek; volume/mute eksplisit, dan suara berhenti saat keluar.

Teks utama dalam gambar AI cukup singkat; preview HTML menjadi acuan keterbacaan dan terjemahan. Setiap hasil diberi label jelas: rencana, kandidat, pilihan pengguna, atau render kode. Jangan menyebut gambar turunan preview sebagai pilihan baru.

[Daftar brief](README.md) · [Alur produksi](ALUR-PRODUKSI.md).
