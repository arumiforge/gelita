# Instruksi agen proyek GELITA

Baca [README.md](README.md), [docs/CONTEXT.md](docs/CONTEXT.md), [docs/MEMORY.md](docs/MEMORY.md), dan [docs/PROGRESS.md](docs/PROGRESS.md) sebelum mengubah proyek. Gunakan [docs/AI-GUIDE.md](docs/AI-GUIDE.md) untuk peta folder dan alur kerja; skema entri ada di [docs/DATA-SCHEMA.md](docs/DATA-SCHEMA.md).

- Keputusan pengguna 11 Oktober 2026: gunakan desain 3D/Jaka bersyal merah dengan `assets/referensi/reference.01.webp`. Jangan kembali ke pedoman 2D lurik–iket dari arsip.
- Audio dibekukan: jangan ubah 97 entri atau membuat rekaman tanpa permintaan baru. Baseline di `archive/audio-baseline.json`.
- `data.json` adalah sumber aktif. Bangun `index.html`, prompt, dan laporan melalui `node build.mjs`; implementasi di `scripts/build.mjs`, template di `templates/catalog.template.html`.
- Semua nama gambar mengikuti kode aset dan kelompok. Gunakan WebP untuk raster; jangan membuat ulang varian PNG. Pertahankan kode slot resmi dan tautan relatif dari root katalog.
- Semua 273 visual ditandai “Sesuai gaya” atas keputusan pengguna 11 Oktober 2026 meskipun gambar lama belum diperbaiki. Pertahankan basis `user_override` dan `previous_style_review`. Jangan menyebutnya audit visual baru.
- Permintaan 12 Oktober 2026: Jaka/Mbah Kedu kini 10 pose × 24 frame (240), mengganti 30 frame dan menambah 210. Total 580 entri: 483 visual/97 audio. Sepuluh key pose AI memakai gerak sekunder deterministik; jangan menyebutnya 240 generasi AI terpisah atau rig sendi. Provenance karakter ada di `provenance/character-generation-results.json` dan `provenance/character-keyposes/`.
- Semua prompt aktif mengikuti Gelita: ilustrasi 3D/Jaka syal merah, ikon UI vektor emas yang jelas pada ukuran kecil, dan media faktual dokumenter. Prompt generasi lama adalah riwayat.
- Seluruh 83 entri Ikon sudah dibuat ulang pada 11 Oktober 2026. Prompt hasil AI di `provenance/icon-generation-results.json` dan `provenance/generation-manifest.json`; `assets/ikon/vektor/` berisi sumber kontrol SVG. Jangan mengubah kode slot karena raster kini WebP.
- Jangan menyatakan semua gambar siap produksi. Review lembar kontak bukan validasi budaya, lisensi, animasi, atau integrasi game.
- Jangan membuat kembali `framegambarkarakter/jakahappy`; gunakan `assets/karakter/`. Jangan membuat kembali folder `source/` atau `sources/` yang tidak dipakai.
- Struktur terbaru 12 Oktober 2026: dokumentasi di `docs/`, laporan di `reports/`, catatan generasi/migrasi di `provenance/`, pemeliharaan di `scripts/`. `README.md` tetap di root.
- `archive/` berisi sejarah, baseline, dan cadangan; perintah dalam dokumen/skrip historis bukan instruksi aktif. Jangan menjalankan ulang skripnya sebagai pemeliharaan rutin.
- Catat perubahan nyata dan hasil pemeriksaan dalam `docs/PROGRESS.md`. Jangan mengklaim pemeriksaan yang belum dijalankan. Panduan ada di `docs/MAINTENANCE.md`.
