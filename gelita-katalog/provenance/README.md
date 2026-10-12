# Catatan asal dan generasi

Catatan ini membantu menelusuri pekerjaan terdahulu. Prompt aktif untuk pekerjaan baru tetap ada di `../data.json` dan [daftar prompt hasil build](../docs/PROMPTS-ENGLISH.md).

| Berkas | Isi |
|---|---|
| `generation-manifest.json` | Prompt/output sembilan referensi baru serta generasi ikon terpilih |
| `icon-generation-jobs.json` | Daftar pekerjaan generasi ikon terdahulu; bukan antrian yang harus dijalankan ulang |
| `icon-generation-results.json` | 20 hasil AI terpilih, kode aset, prompt, dan lokasi sumber pembuat |
| `migration-map.json` | 475 nama/jalur lama ke tujuan saat ini, termasuk format alternatif |
| `character-generation-results.json` | Sepuluh prompt dan sumber key pose karakter 3D, 12 Oktober 2026 |
| `character-keyposes/` | Key pose WebP lokal untuk mereproduksi frame gerak sekunder |

475 pemetaan bukan 475 visual unik. Sesudah penghapusan PNG, beberapa nama lama menunjuk tujuan WebP yang sama. Nama PNG/ICO pada kode resmi tidak diubah.

Lokasi absolut `source`/`path` di manifest bisa berada di komputer pembuat dan tidak disertakan di GitHub. Pertahankan sebagai bukti sejarah; jangan menganggapnya tautan aktif katalog. Hasil yang tersedia dibaca melalui `file`, `preview`, atau `generated_asset` di data.
