# Skrip aktif

Puzzle: `check_puzzle_tiles.py` mengadaptasi pemeriksa pada panduan (ambang asli; output WebP). `review-puzzle-generation-20261014.py`, `verify-puzzle-update-20261014.py` dan `browser-puzzle-check.cjs` memeriksa keluaran, preservasi data/aset, serta akses di katalog. Skrip prepare/fetch/normalize/install/sync-puzzle bertanggal menyimpan proses sekali pakai 14 Oktober; bukan pemeliharaan rutin. Skrip install menolak katalog yang sudah berbeda dari snapshot. Tidak ada skrip yang menjalankan ulang AI secara otomatis.

Contoh perintah dijalankan dari root katalog. Skrip menentukan root dari lokasi berkas, sehingga pemindahan ke folder ini tidak mengubah lokasi data/aset.

| Skrip | Fungsi dan dampak |
|---|---|
| `build.mjs` | Dipanggil oleh `node build.mjs` di root; menulis HTML, prompt, status/audit |
| `validate.mjs` | Memeriksa katalog/struktur/tautan; menulis laporan validasi |
| `browser-check.cjs` | Playwright/Edge; laporan browser dan screenshot WebP |
| `resize_assets.py` | Tanpa argumen hanya memeriksa; `--apply` mencadangkan lalu resize, ukur, build/validasi |
| `measure_assets.py` | Mengukur raster dan menulis metadata ke data aktif |
| `inspect_assets.py` | Membuat lembar kontak raster WebP di `review/` |
| `review-icons.mjs` | Membuat lembar kontak ikon melalui `sharp` |
| `verify-icon-replacement.py` | Membandingkan hash ikon dengan ZIP cadangan dan memeriksa PNG lepas |
| `expand-character-frames.mjs` | Pekerjaan penggantian karakter 12 Oktober; 10 key pose AI menjadi 240 frame. Memerlukan `sharp`, 30 frame awal, dan cadangan belum ada; jangan dijalankan ulang rutin |
| `review-character-frames.mjs` | Memeriksa 240 frame/riwayat dan membuat lembar kontak serta preview gerak |
| `browser-character-check.cjs` | Memeriksa 24 frame setiap pose di katalog dan kontrol preview lokal |
| `review-legacy-style.mjs` | Mendaftar aset dari riwayat gaya lama yang belum dibuat ulang dan membuat lembar kontak; tidak mengubah status |

Build/validator hanya memerlukan Node.js. Skrip gambar Python memerlukan Pillow kecuali pemeriksaan hash. Browser memerlukan Playwright, Edge, Python/Pillow; lembar ikon memerlukan `sharp`. Pengaturan paket opsional dijelaskan di [MAINTENANCE.md](../docs/MAINTENANCE.md).

Migrasi dan pembuatan ikon sekali pakai disimpan di [archive/scripts/](../archive/scripts/README.md), bukan prosedur pemeliharaan aktif.


Revisi katalog/audio/mockup: `catalog-revisions.mjs` menyediakan pemeriksaan baseline dengan field yang diizinkan. `verify-catalog-update-20261014.py` memeriksa hasil terbaru tanpa mengubah aset. `produce-nonnarration-audio-20261014.py --apply`, `update-catalog-package-20261014.mjs`, dan `prepare-mockup-briefs-20261014.mjs` adalah produksi/persiapan sekali pakai; jangan dijalankan ulang rutin.
