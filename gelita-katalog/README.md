# GELITA — Perpustakaan aset

Katalog statis internal untuk game edukasi kelas 5 tentang Temanggung, Magelang, dan Wonosobo. Dokumentasi diperbarui **12 Oktober 2026**, zona waktu Asia/Bangkok. Paket ini berisi katalog dan aset; kode aplikasi game, backend, workbook soal, serta rekaman audio tidak disertakan.

## Membuka katalog

Buka [index.html](index.html) langsung di browser. Tidak memerlukan akun, server, atau internet. Halaman menyediakan pencarian, kelompok koleksi, filter wilayah/gaya, detail aset, salin prompt, serta ekspor hasil JSON/CSV. Saat dibuka sebagai berkas lokal, gambar asli dibuka di tab baru untuk disimpan melalui browser.

Untuk membagikan katalog, sertakan `index.html`, seluruh `assets/`, `README.md`, `docs/`, dan `reports/` agar gambar serta tautan panduan/laporan tetap tersedia. Untuk pengembangan, gunakan seluruh folder ini. Mengunggahnya ke GitHub tidak otomatis mengaktifkan hosting web.

## Kondisi saat ini

| Isi | Jumlah/kondisi |
|---|---|
| Entri katalog | 370: 273 visual dan 97 brief audio |
| Visual utama | 221 WebP dan 52 SVG; 221 raster sesuai ukuran target |
| Ikon | 83 entri dibuat ulang pada 11 Oktober 2026 |
| Referensi yang dibuat ulang | 9 gambar baru; bukan pemulihan berkas asli |
| Audio | 88 narasi, 3 musik, 6 efek suara; identik dengan baseline |
| Status gaya | 273 “Sesuai gaya” berdasarkan keputusan pengguna (`user_override`) |

Jumlah visual menghitung entri utama, bukan seluruh berkas alternatif/sumber kontrol SVG. Semua visual ditandai sesuai atas keputusan pengguna 11 Oktober 2026, termasuk gambar lama yang belum diperbaiki. Ini **bukan audit visual baru**; `previous_style_review` menyimpan penilaian sebelumnya. Ukuran yang sesuai tidak membuktikan budaya, lisensi, kualitas pembesaran, animasi, atau integrasi game telah disahkan.

## Arah visual aktif

- Ilustrasi mengikuti render 3D petualangan dengan bentuk bervolume dan pencahayaan hangat.
- Jaka mengikuti [reference.01.webp](assets/referensi/reference.01.webp): rambut cokelat acak, kemeja krem, syal merah, celana cokelat, batik pinggang, sandal, dan ransel kulit. Jangan kembali ke lurik–iket atau rompi biru dari panduan lama.
- Avatar Jaka memakai [reference.03.webp](assets/referensi/reference.03.webp); Mbah Kedu mengikuti [reference.02.webp](assets/referensi/reference.02.webp). Syal merah hanya berlaku untuk Jaka.
- Kontrol UI memakai SVG emas yang jelas pada ukuran kecil; foto/video faktual mempertahankan brief dokumenter.
- Raster aktif memakai WebP. Nama mengikuti kode aset; kode resmi seperti `app.icon-512.png` tetap memakai berkas `app.icon-512.webp`.

273 prompt visual aktif sudah diselaraskan. Sumbernya `data.json`; salinan build ada di [docs/PROMPTS-ENGLISH.md](docs/PROMPTS-ENGLISH.md). Prompt yang benar-benar dipakai pada generasi AI dicatat terpisah dalam [provenance/](provenance/README.md).

## Peta folder

| Lokasi | Fungsi |
|---|---|
| [AGENTS.md](AGENTS.md) | Instruksi kerja AI dan urutan membaca dokumentasi |
| `data.json` | Sumber aktif semua entri katalog |
| `index.html` | Halaman siap dibuka; dihasilkan melalui build |
| `build.mjs` | Perintah build dari root |
| [assets/](assets/README.md) | Gambar menurut kelompok dan sumber kontrol SVG |
| [docs/](docs/README.md) | Konteks, keputusan, progres, panduan AI, skema, pemeliharaan, prompt |
| [templates/](templates/README.md) | Template tampilan dan interaksi katalog |
| [scripts/](scripts/README.md) | Build, validasi, browser, dan pemeliharaan aset |
| [reports/](reports/README.md) | Ringkasan/status dan hasil pemeriksaan bertanggal |
| [provenance/](provenance/README.md) | Manifest generasi, pekerjaan ikon, dan pemetaan nama lama |
| [review/](review/README.md) | Lembar kontak dan tangkapan layar |
| [archive/](archive/README.md) | Baseline audio, cadangan, dokumen, prompt, dan skrip historis |

Folder `sources/` dihapus pada 12 Oktober 2026. Kedua dokumennya memiliki salinan identik di `archive/source-documents/`. `README.txt` root yang mengulang panduan juga dihapus; versi historis di arsip dipertahankan.

## Memperbarui dan memeriksa

1. Baca [panduan AI](docs/AI-GUIDE.md) dan [pemeliharaan](docs/MAINTENANCE.md).
2. Ubah `data.json`, aset terkait, atau `templates/catalog.template.html`. Jangan mengedit `index.html` secara terpisah.
3. Jalankan `node build.mjs`, lalu `node scripts/validate.mjs` dari root. Keduanya memakai modul bawaan Node.js.
4. Bila aset berubah, periksa dimensi/alpha dan tampilan. Python memerlukan Pillow; pemeriksaan browser memerlukan Playwright, Edge, Python, dan Pillow. Perintah lengkap ada di panduan pemeliharaan.
5. Catat perubahan dan pemeriksaan nyata dalam [docs/PROGRESS.md](docs/PROGRESS.md), lalu tinjau berkas sebelum commit/push.

Jangan mengubah 97 objek audio atau membuat rekaman tanpa permintaan baru. Skrip migrasi/generasi lama di `archive/scripts/` bukan langkah rutin. Tidak ada regenerasi gambar atau perubahan audio pada perapian 12 Oktober 2026.
