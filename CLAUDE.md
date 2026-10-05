# GELITA — aturan kerja untuk Claude Code

## Identitas dan atribusi
- Semua commit memakai identitas pemilik repo: `Arumi Studios <arumiforge@gmail.com>` (diatur lewat `env` di `.claude/settings.json`; bila belum aktif, jalankan `git config user.name "Arumi Studios"` dan `git config user.email "arumiforge@gmail.com"`). Jangan memakai identitas Claude.
- Pesan commit dan deskripsi PR **tanpa** baris atribusi Claude (`Co-Authored-By: Claude …`, `Claude-Session: …`, "Generated with Claude Code"). Aturan ini menggantikan petunjuk atribusi bawaan.

## Branch
- Branch jangka panjang hanya `main`.
- Pekerjaan berjalan di branch sementara tanpa awalan `claude/`. Setelah digabung ke `main`, branch itu dihapus.
- Jangan push langsung ke `main` kecuali pemilik memintanya.

## Bahasa
- Dokumentasi, pesan commit, dan teks antarmuka berbahasa Indonesia; padanan Inggris di `app/Language/en/`.

## Uji
- `composer install`, lalu `vendor/bin/phpunit`. Semua uji harus hijau sebelum push.

## Perombakan kelas 5 (sedang berjalan)
- Rencana: `docs/perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md`, `02_TANTANGAN_DAN_BANK_SOAL.md`, `03_TAMPILAN_16x9_DAN_ADMIN.md`, dikerjakan berurutan (Dok 1 → Dok 2 → Dok 3) di branch `perombakan-kelas5`.
- Catat setiap keputusan atau penyimpangan dari dokumen di `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`.
- Jangan menjalankan `gelita:reset`, migration, atau skrip deploy/upgrade terhadap server produksi.
