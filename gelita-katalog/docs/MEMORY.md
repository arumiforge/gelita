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

## Keputusan frame — 12 Oktober 2026

Pengguna meminta seluruh pose Jaka dan Mbah Kedu menjadi 24 gambar per pose, mengganti frame lama, serta daftar nama file gaya lama tanpa menampilkan gambarnya. Enam pose Jaka dan empat pose Mbah Kedu menghasilkan 240 frame; 30 slot lama tetap dan 210 slot baru ditambahkan. Sepuluh key pose 3D dibuat menggunakan image_gen; frame memakai gerak sekunder deterministik, bukan 240 generasi AI terpisah atau rig sendi. Jumlah aktif menjadi 580 entri: 483 visual dan 97 audio. `user_override` serta `previous_style_review` 273 visual awal tetap dipertahankan; frame tambahan memiliki catatan asal tersendiri.

## Konteks layar aplikasi — 12 Oktober 2026

Pengguna menjelaskan bahwa seluruh aset gambar digunakan di aplikasi GELITA yang memakai rasio layar tetap **16:9** (landscape). Halaman aplikasi tidak mendukung tata letak responsif untuk rasio layar lain. Gunakan konteks halaman 16:9 saat memahami komposisi dan penempatan aset di aplikasi asli; ukuran dan proporsi tiap berkas tetap mengikuti slotnya masing-masing.

## Batas klaim

Status sesuai dan dimensi target tidak menyatakan kesiapan produksi. Budaya, lisensi, animasi, kualitas individual, dan integrasi harus dibuktikan terpisah. Jangan menjalankan generasi/migrasi historis tanpa permintaan baru.

## Keputusan full UI hasil — 13 Oktober 2026

Pengguna meminta komposisi, ukuran logo/kota, posisi ID/speaker/menu, panel dan tombol mengikuti mockup full UI landscape pertama. Gunakan aset panel/tombol khusus, reuse lanskap dan logo, medali avatar.2 diberi efek tanpa diganti. Delapan slot ditambahkan: tujuh ui.result-* dan bg.result.temanggung; total 588 entri (491 visual/97 audio), 580 entri lama tetap persis. Referensi pilihan tidak ditimpa. Instruksi implementasi web ada di [paket mockup](../mockup/gambar-mockup/tantangan-selesai-temanggung/petunjuk-agent.md).

## Keputusan ikon bahasa — 13 Oktober 2026

Pengguna meminta ikon Indonesia memakai bendera Indonesia dan English memakai bendera UK. Dua medali WebP transparan 256 × 256 ditambahkan sebagai icon.language-id/en. Ini menggantikan penggunaan medali huruf ID/EN pada preview, bukan mengganti gambar reference.19/20. Total kini 590 entri (493 visual/97 audio), 85 ikon. Gunakan bendera disertai nama bahasa pada popover.

## Keputusan konsep hasil bersama — 13 Oktober 2026

Pengguna menegaskan bahwa halaman Tantangan selesai digunakan bersama di kota/wilayah lain. Temanggung adalah contoh mockup, bukan batas cakupan. Gunakan satu desain/komponen; nama kota, jenis tantangan, latar, data hasil, label/rute peta dan Berikutnya mengikuti konfigurasi serta state aplikasi. Nama folder, kode aset yang sudah ada dan gambar pilihan dipertahankan.
