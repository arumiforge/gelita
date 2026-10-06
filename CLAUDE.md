# GELITA — aturan kerja untuk Claude Code

## Identitas dan atribusi
- Semua commit memakai identitas pemilik repo: `Arumi Studios <arumiforge@gmail.com>` (diatur lewat `env` di `.claude/settings.json`; bila belum aktif, jalankan `git config user.name "Arumi Studios"` dan `git config user.email "arumiforge@gmail.com"`). Jangan memakai identitas Claude.
- Pesan commit dan deskripsi PR **tanpa** baris atribusi Claude (`Co-Authored-By: Claude …`, `Claude-Session: …`, "Generated with Claude Code"). Aturan ini menggantikan petunjuk atribusi bawaan.

## Branch
- Repo hanya punya satu branch tetap: `main`. Tidak ada branch lain yang dibiarkan hidup.
- Pekerjaan berjalan di branch sementara bercabang dari `main`, tanpa awalan `claude/` (contoh `dok2-klien-kesempatan`). Branch itu masuk lewat PR ke `main`, lalu langsung dihapus.
- Jangan push langsung ke `main` kecuali pemilik memintanya.
- Sejarah `main` ditulis ulang pada 6 Oktober 2026 (semua commit atas nama Arumi Studios). Klon atau branch dari sebelum tanggal itu tidak cocok lagi; ambil ulang dari `main`.

## Bahasa
- Dokumentasi, pesan commit, dan teks antarmuka berbahasa Indonesia; padanan Inggris di `app/Language/en/`.

## Uji
- `composer install`, lalu `vendor/bin/phpunit`. Semua uji harus hijau sebelum push.

## Perombakan kelas 5 (sedang berjalan)
- Rencana: `docs/perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md`, `02_TANTANGAN_DAN_BANK_SOAL.md`, `03_TAMPILAN_16x9_DAN_ADMIN.md`, dikerjakan berurutan (Dok 1 → Dok 2 → Dok 3). Branch `perombakan-kelas5` sudah digabung ke `main` dan dihapus; pekerjaan lanjutan memakai branch sementara per bagian.
- Status terkini dan sisa pekerjaan ada di bagian "Status" `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`. Baca itu lebih dulu.
- Catat setiap keputusan atau penyimpangan dari dokumen di `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`.
- Jangan menjalankan `gelita:reset`, migration, atau skrip deploy/upgrade terhadap server produksi.
