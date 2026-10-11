# Konteks proyek

Kondisi terdokumentasi: 12 Oktober 2026 (Asia/Bangkok). Paket `gelita-katalog` adalah katalog statis internal aset game edukasi kelas 5 tentang Temanggung, Magelang, dan Wonosobo. Buka melalui [index.html](../index.html); ini bukan source aplikasi GELITA lengkap.

## Batas paket

Tersedia: 370 entri, gambar, template HTML, skrip, provenance, laporan, dan arsip. Backend, database game, workbook bank soal, konfigurasi slot aplikasi, rekaman audio, dan pengujian integrasi game tidak disertakan. Dokumen lama menyebut 12 tantangan kelas 5; katalog memiliki 45 entri visual Tantangan, sehingga kedua angka mengukur hal berbeda.

## Sumber kebenaran

| Informasi | Sumber |
|---|---|
| Instruksi dan keputusan aktif | [AGENTS.md](../AGENTS.md), [MEMORY.md](MEMORY.md), [README.md](../README.md) |
| Entri, prompt aktif, target, jalur, status | `../data.json` |
| Isi/dimensi/alpha nyata | `../assets/` |
| Tampilan/interaksi | `../templates/catalog.template.html` |
| Hasil build | `../index.html`, `../reports/STATUS-GAMBAR.json`, `../reports/STYLE-AUDIT.*`, [PROMPTS-ENGLISH.md](PROMPTS-ENGLISH.md) |
| Prompt/output generasi sebelumnya | [provenance/](../provenance/README.md) |
| Bukti pekerjaan/pemeriksaan | [PROGRESS.md](PROGRESS.md), [reports/](../reports/README.md) |

Manifest bisa memuat lokasi gambar di komputer pembuat yang tidak tersedia di checkout lain; `file` di data menunjuk hasil yang disertakan. Hasil build tidak menggantikan sumber aktif.

## Kondisi visual dan audio

273 visual terdiri dari 221 WebP dan 52 SVG utama. Sembilan referensi hilang telah dibuat ulang sebagai gambar baru. Semua 83 Ikon sudah dibuat ulang; gambar di luar pekerjaan referensi/ikon memakai aset yang tersedia, dengan prompt aktif terbaru.

Semua visual “Sesuai gaya” berdasarkan `user_override`, termasuk aset lama yang belum diperbaiki. Audit lama (68 sesuai, 141 belum sesuai, 3 perlu tinjau, 61 tidak berlaku) tetap di `previous_style_review`; angka itu bukan status saat ini. Semua 221 raster aktif tepat target. Resize mempertahankan proporsi melalui crop/padding, tidak menambah detail atau memperbaiki gaya.

97 brief audio (88 narasi, 3 musik, 6 efek suara) identik dengan baseline. Target 100 narasi per bahasa pada dokumen asal adalah kebutuhan aplikasi terdahulu, bukan rekaman yang tersedia dalam paket ini.

## Struktur dan sejarah

Pada 12 Oktober 2026, dokumentasi dipusatkan di `docs/`, laporan di `reports/`, manifest di `provenance/`, template di `templates/`, pemeliharaan di `scripts/`. README, AGENTS, data, index, dan pintu masuk build tetap di root. `sources/` dihapus setelah salinannya dipastikan ada di `archive/source-documents/`.

Arsip merekam kondisi lama, termasuk arahan 2D dan rencana katalog belum dibangun. Pernyataan itu telah digantikan keputusan terbaru. Rujukan aplikasi/workbook/tautan di isi arsip tidak membuktikan berkas tersebut tersedia di sini.
