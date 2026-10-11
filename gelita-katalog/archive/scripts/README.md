# Skrip historis — jangan dijalankan sebagai pemeliharaan

Skrip ini dipindahkan dari root pada 12 Oktober 2026 untuk merekam pekerjaan sekali pakai 11 Oktober 2026. Isi kode dipertahankan; jalur internalnya mengikuti tata letak/lingkungan lama dan bukan prosedur yang bisa langsung dijalankan dari folder ini.

| Skrip | Pekerjaan terdahulu |
|---|---|
| `migrate.mjs` | Migrasi nama aset, pembuatan baseline/arsip, status audit awal |
| `update-icons.mjs` | Pembaruan prompt/status dan persiapan SVG/pekerjaan ikon |
| `finish-icon-assets.mjs` | Memasang hasil AI, mengekspor kontrol, memperbarui manifest |
| `refine-native-icons.mjs` | Penyesuaian kontrol dan brief favicon |
| `finalize-prompts.mjs` | Penyelarasan akhir prompt aktif saat itu |

Jangan menjalankan ulang untuk build, resize, atau memperbaiki dokumentasi. Untuk pekerjaan aktif gunakan [scripts/](../../scripts/README.md) dan [MAINTENANCE.md](../../docs/MAINTENANCE.md). Permintaan generasi baru perlu memakai prompt/data aktif dan workflow baru yang sesuai.
