# Pemeliharaan dan pemeriksaan

Peta Kedu kandidat04: jalankan node scripts/verify-peta-kedu-04.cjs sesudah perubahan preview/galeri, lalu node scripts/validate.mjs. Pemeriksa memakai Playwright/Edge/Pillow, server GET loopback sementara, merender empat state dan menguji typewriter/ID-EN/menu/audio/galeri/target DOM640×360/854×480. PLAYWRIGHT_PACKAGE dan PYTHON_EXE dapat menunjuk runtime lokal. Ini pengujian preview, bukan integrasi aplikasi. Build dahulu jika inventaris galeri berubah. Skrip record-peta-kedu-04-20261015.py di arsip hanya pengemasan historis, bukan generasi/pemeliharaan rutin.

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

461 raster saat ini tepat target. Hanya jika resize diperlukan:

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

Pemeriksaan menguji Bootstrap/font lokal tanpa jaringan, pencarian/filter/pengurutan, collapse/dropdown, dialog/Escape/fokus, pembukaan gambar asli, ekspor JSON/CSV/TXT, audio, mobile serta galat JavaScript. Teks salin prompt/brief memakai clipboard stub agar tidak mengubah clipboard sistem pengguna. Hasil lulus ke `reports/browser-check-report.json`; enam screenshot WebP (halaman, koleksi, detail pada desktop/mobile) ke `review/`. Jika gagal, catat percobaan terbaru di PROGRESS; laporan lama bisa tetap ada dan bukan bukti hasil percobaan terbaru.

## Lembar kontak

`python scripts/inspect_assets.py` membuat lembar raster WebP. `node scripts/review-icons.mjs` membuat dua lembar ikon; memerlukan `sharp` melalui resolusi Node atau `$env:SHARP_PACKAGE`. Pembuatan lembar bukan audit budaya/gaya otomatis.

Delapan `contact-*.jpg` yang tersedia adalah lembar audit historis. Perapian dokumentasi tidak mengonversi atau meninjau ulangnya; keluaran baru memakai WebP.

## Sebelum commit/push

Tinjau sumber, hasil build, tautan, dan [PROGRESS.md](PROGRESS.md). Commit hasil build bersama sumber yang relevan agar katalog cocok dengan data aktif. `.git` berada di repositori induk; masalah lingkungan Git tidak mengizinkan perubahan konfigurasi tanpa kebutuhan jelas. Perapian ini tidak membuat commit atau push.


## Pemeriksaan puzzle

`python scripts/verify-puzzle-update-20261014.py` memeriksa 21 penambahan/empat penggantian, preservasi 586 objek lama dan 489 hash aset, riwayat gaya, dimensi 461 raster serta baseline audio. `python scripts/review-puzzle-generation-20261014.py` memeriksa keping menurut ambang panduan dan membuat lembar kontak/kisi/acak WebP. Pemeriksaan otomatis tidak menggantikan uji susun dua orang atau verifikasi isi. Skrip produksi bertanggal bukan pemeliharaan rutin dan tidak boleh digenerasi ulang tanpa permintaan baru.


## Revisi katalog 14 Oktober — bahasa, audio dan mockup

`python scripts/verify-catalog-update-20261014.py` membaca snapshot sebelum revisi, memeriksa 600 entri yang tidak berubah, 88 narasi baseline, salinan bendera/hash/cadangan, decode sembilan MP3, durasi, puncak hasil decode/oversampling dan batas loop, serta tautan brief/inventaris. Memerlukan numpy, Pillow dan ffmpeg lokal. Laporan tidak merupakan review dengar atau sertifikasi meter true peak.

`node scripts/browser-check.cjs` memeriksa galeri/state, preview iframe, playback, mute dan filter audio selain fungsi katalog. `node scripts/verify-mockup-ui.cjs` memeriksa preview hasil dan menyimpan ulang empat render state. Setelah render berubah, jalankan build/validator lagi jika inventaris berubah; hash state terbaru dicatat pada laporan revisi.

Validator/build memakai `scripts/catalog-revisions.mjs` untuk pengecualian field yang diizinkan. Baseline 97 objek asli tetap utuh; hanya 9 Musik/Efek suara mendapat produksi dan 2 slot bahasa diganti. Skrip produksi/persiapan bertanggal (produce-nonnarration-audio, update-catalog-package, prepare-mockup-briefs) adalah sekali pakai, bukan langkah rutin. Pemeriksa historis puzzle/mockup 12–13 Oktober memakai snapshot/hash lama dan tidak menggantikan pemeriksa paket terbaru untuk perubahan ini.
