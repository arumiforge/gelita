# Keputusan dan memori proyek

Catatan lokal proyek, diperbarui 12 Oktober 2026. Ini tidak mengubah memori global aplikasi AI. Kondisi aktual ada di [CONTEXT.md](CONTEXT.md); pekerjaan dan bukti di [PROGRESS.md](PROGRESS.md).

## Keputusan aktif — 11 Oktober 2026

1. Gunakan desain 3D. Jaka mengikuti reference.01: kemeja krem, syal merah, celana cokelat, batik pinggang, sandal, ransel. Avatar Jaka memakai reference.03; Mbah Kedu reference.02 tanpa syal Jaka.
2. Lengkapi sembilan referensi hilang sebagai gambar baru, bukan pemulihan berkas asli.
3. Nama berkas mengikuti kode aset dan kelompok; pertahankan kode slot serta jalur relatif. Format/sumber alternatif bukan entri tambahan.
4. Hapus `framegambarkarakter/jakahappy`; frame resmi tetap di `assets/karakter/`.
5. Bekukan 97 entri audio; tidak ada permintaan membuat rekaman.
6. Gunakan WebP untuk raster, hapus PNG lepas. Nama PNG/ICO dalam kode atau sumber generasi tetap sebagai riwayat/identitas.
7. Resize deterministik ke target; pembesaran bukan penambahan detail, generasi AI, atau perbaikan budaya/gaya.
8. Tandai 273 visual “Sesuai gaya” dengan basis `user_override`, termasuk gambar lama yang belum diperbaiki. Simpan audit lama di `previous_style_review`.
9. Selaraskan 273 prompt aktif dan buat ulang seluruh 83 Ikon. Prompt yang digunakan saat generasi tetap sebagai provenance.

Keputusan penandaan seluruh visual menggantikan status audit awal. 141 “Belum sesuai” dan 3 “Perlu tinjau” adalah riwayat, bukan status katalog sekarang. Ikon selesai dibuat ulang setelah audit awal; jangan menyimpulkan seluruh aset lain juga dibuat ulang.

## Keputusan struktur — 12 Oktober 2026

Pengguna meminta seluruh Markdown diselaraskan dengan folder yang sudah diunggah ke GitHub, folder sumber yang tidak digunakan dihapus, berkas dirapikan, README tetap di root, dan panduan terstruktur ditambahkan untuk AI.

Struktur: `docs/`, `reports/`, `provenance/`, `templates/`, `scripts/`; skrip sekali pakai di `archive/scripts/`. Folder sebenarnya bernama `sources/` dihapus sesudah dibandingkan dengan salinan arsip. `README.txt` root dihapus karena mengulang README; salinan historisnya tetap di arsip. Data, aset, dan baseline audio tidak diubah oleh perapian.

## Batas klaim

Status sesuai dan dimensi target tidak menyatakan kesiapan produksi. Budaya, lisensi, animasi, kualitas individual, dan integrasi harus dibuktikan terpisah. Jangan menjalankan generasi/migrasi historis tanpa permintaan baru.
