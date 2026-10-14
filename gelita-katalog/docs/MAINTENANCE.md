# Pemeliharaan dan pemeriksaan

Jalankan perintah dari root `gelita-katalog`. Baca [AGENTS.md](../AGENTS.md) terlebih dahulu. Jangan menjalankan skrip historis.

## Build dan validasi

Memerlukan Node.js yang mendukung modul ES; tidak memakai paket npm tambahan.

```powershell
node build.mjs
node scripts/validate.mjs
```

Build memeriksa tautan gambar/kode/audio lalu menghasilkan HTML, prompt di `docs/`, dan status/audit di `reports/`. Validator memeriksa data HTML, ID/kode, status/riwayat, ikon, jalur migrasi, target baseline, serta struktur/tautan aktif. Tanggal keputusan gaya tetap 11 Oktober 2026; waktu build/validasi dicatat terpisah. Build ulang bukan audit visual baru.

## Metadata dan ukuran

Python dan Pillow diperlukan. Periksa tanpa perubahan:

```powershell
python scripts/resize_assets.py
```

440 raster saat ini tepat target. Hanya jika resize diperlukan:

```powershell
python scripts/resize_assets.py --apply
```

Skrip mencadangkan data dan aset yang berubah ke `archive/before-resize-*.zip`, mempertahankan proporsi (crop tengah pada gambar opak/padding transparan), menyimpan WebP lossless, memperbarui metadata, menjalankan build/validator, dan menulis `reports/resize-report.json`. Bila tidak ada ukuran berbeda, skrip tidak menimpa laporan lama. Pembesaran tidak menambah detail.

Sesudah penggantian raster manual, `python scripts/measure_assets.py` memperbarui metadata di data; lanjutkan build dan validasi. SVG tidak diukur oleh skrip tersebut. Perbandingan 83 ikon dengan cadangan: `python scripts/verify-icon-replacement.py`.

## Browser

Memerlukan Playwright untuk Node.js, Microsoft Edge, Python, dan Pillow.

```powershell
node scripts/browser-check.cjs
```

Playwright dapat tersedia melalui resolusi paket Node atau `$env:PLAYWRIGHT_PACKAGE` yang menunjuk folder paket. `$env:BROWSER_CHANNEL` mengganti channel bawaan `msedge`. Tidak ada lokasi komputer pembuat yang diwajibkan oleh skrip aktif.

Pemeriksaan menguji pencarian/filter, dialog/Escape, pembukaan gambar asli, ekspor JSON, audio, mobile, serta galat JavaScript. Hasil lulus ke `reports/browser-check-report.json`; screenshot WebP ke `review/`. Jika gagal, catat percobaan terbaru di PROGRESS; laporan lama bisa tetap ada dan bukan bukti hasil percobaan terbaru.

## Lembar kontak

`python scripts/inspect_assets.py` membuat lembar raster WebP. `node scripts/review-icons.mjs` membuat dua lembar ikon; memerlukan `sharp` melalui resolusi Node atau `$env:SHARP_PACKAGE`. Pembuatan lembar bukan audit budaya/gaya otomatis.

Delapan `contact-*.jpg` yang tersedia adalah lembar audit historis. Perapian dokumentasi tidak mengonversi atau meninjau ulangnya; keluaran baru memakai WebP.

## Sebelum commit/push

Tinjau sumber, hasil build, tautan, dan [PROGRESS.md](PROGRESS.md). Commit hasil build bersama sumber yang relevan agar katalog cocok dengan data aktif. `.git` berada di repositori induk; masalah lingkungan Git tidak mengizinkan perubahan konfigurasi tanpa kebutuhan jelas. Perapian ini tidak membuat commit atau push.

