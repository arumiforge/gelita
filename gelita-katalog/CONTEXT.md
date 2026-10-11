# Konteks proyek

Paket ini merupakan katalog aset internal, bukan source aplikasi GELITA lengkap. Tidak ada workbook bank soal, kode backend, konfigurasi game, atau rekaman audio di paket awal. Dokumentasi asal menyebut 12 tantangan kelas 5 dan 45 slot visual; katalog mempertahankan 45 entri Tantangan.

Sumber kebenaran aktif: README.md (keputusan), data.json (entri), berkas aset (hasil nyata), catalog.template.html (UI), STATUS-GAMBAR.json dan STYLE-AUDIT.* (hasil build).

Dokumen sumber lama memuat aturan 2D serta klaim katalog belum dibangun. Keduanya bersifat historis dan digantikan keputusan 11 Oktober 2026. Rujukan aplikasi dalam arsip tidak membuktikan file aplikasi tersedia di sini.

Audit: 273 entri visual terdiri dari 221 raster dan 52 SVG utama. Sembilan referensi dibuat ulang, tiga referensi awal tersedia, 209 raster hasil lama dipertahankan. Ada dua SVG alternatif tombol yang juga dipertahankan. 97 entri audio identik dengan baseline.

Interpretasi status terbaru: semua 273 visual ditandai Sesuai gaya atas permintaan pengguna 11 Oktober 2026, memakai basis user_override. Status ini tidak menyatakan gambar lama sudah digenerasi ulang. Interpretasi audit sebelumnya tetap dapat dilihat pada previous_style_review. Dimensi dan budaya dicatat terpisah.

Keputusan lanjutan 11 Oktober 2026: hapus seluruh varian PNG, pertahankan WebP untuk raster. Referensi tanpa WebP dikonversi lossless terlebih dahulu; kode slot resmi dan audio tidak berubah. Nama PNG dalam arsip/provenance adalah catatan historis.

11 Oktober 2026: resize deterministik melalui resize_assets.py telah dijalankan tanpa generasi AI. Semua 221 raster aktif tepat berukuran target; 213 diresize, 8 sudah tepat. WebP asli sebelum resize tersimpan dalam ZIP arsip. Gaya dan isi budaya belum diperbaiki melalui resize.
