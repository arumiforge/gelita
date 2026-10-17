# Instruksi agen proyek GELITA

Baca [README.md](README.md), [docs/CONTEXT.md](docs/CONTEXT.md), [docs/MEMORY.md](docs/MEMORY.md), dan [docs/PROGRESS.md](docs/PROGRESS.md) sebelum mengubah proyek. Gunakan [docs/AI-GUIDE.md](docs/AI-GUIDE.md) untuk peta folder dan alur kerja; skema entri ada di [docs/DATA-SCHEMA.md](docs/DATA-SCHEMA.md).

- Keputusan pengguna 11 Oktober 2026: gunakan desain 3D/Jaka bersyal merah dengan `assets/referensi/reference.01.webp`. Jangan kembali ke pedoman 2D lurik–iket dari arsip.
- Audio: permintaan 14 Oktober mengizinkan 3 musik dan 6 efek suara nonnarasi. MP3/master WAV sintetis tersedia; 88 Narasi tetap dibekukan. Baseline 97 brief di `archive/audio-baseline.json` tidak ditimpa. Perubahan metadata yang diizinkan dicatat di `provenance/nonnarration-audio-20261014.json`; jangan produksi ulang tanpa permintaan baru.
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

- Revisi puzzle 14 Oktober 2026: 611 entri (514 visual/97 audio), 66 visual Tantangan. 21 variasi d–j baru dan empat penggantian mengikuti update/gambar-puzzle. Gunakan [paket revisi](docs/PUZZLE-UPDATE-20261014.md) dan [manifest](provenance/puzzle-generation-results-20261014.json); jangan menjalankan ulang produksi bertanggal sebagai pemeliharaan rutin.

- Katalog halaman 14 Oktober: `mockup/catalog.json` adalah inventaris galeri, ditanam oleh build. Satu mockup hasil tersedia; 12 halaman planned memiliki brief/prompt di `mockup/persiapan/`. Peta Kedu memiliki kandidat full UI WebP pada objek candidate, belum dipilih pengguna; kandidat04 memiliki preview interaktif dan states/riwayat sejak15Oktober; 11 halaman lain belum memiliki gambar baru. Jangan menyebutnya 12 redesain selesai. Slot reference.19/20 sekarang memakai bendera baru dengan kode/jalur lama; sumber SVG huruf lama hanya riwayat.

- Peta Kedu kandidat 03 (14 Oktober): pengguna mengizinkan perombakan bebas mockup/UI/ukuran titik dan usulan diorama baru untuk HP landscape; izin terbaru menggantikan batas konfirmasi perombakan total pada kandidat 02. Pengguna akan mengatur kode sistem sendiri. Gunakan mockup/gambar-mockup/peta-kedu/spesifikasi-sentuh-03.md; rencana hit area bukan uji DOM/perangkat. Jangan mengubah web asli atau mengganti aset map.kedu dari permintaan mockup ini.

- Peta Kedu kandidat04 (15 Oktober): preview interaktif ada di mockup/gambar-mockup/peta-kedu/revisi-04/README.md. Pustaka/Profil di menu, kontrol cerita ikon dan dot/efek ketik, Kenali buku/kaca pembesar, master diorama tiga lapisan yang diredupkan terpisah. candidate.preview/states tetap belum dipilih dan halaman planned. DOM preview diuji640x360/854x480; bukan uji perangkat/integrasi game. Pertahankan88 narasi dan aset resmi; peta03 disesuaikan hanya sebagai teks preview.
