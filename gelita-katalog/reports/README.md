# Laporan dan lingkup pemeriksaan

Peta Kedu kandidat04: [browser/DOM](peta-kedu-04-browser-check.json) memeriksa typewriter, lapisan terkunci, ID/EN, menu/audio, tujuh gambar galeri/iframe dan target sentuh nyata pada delapan konfigurasi. [Paket/hash](peta-kedu-04-package-check.json) memeriksa master1774×887, empat render1920×1080, enam skin asli dan preservasi data/kandidat01–03. Perangkat fisik/fullscreen dan integrasi game belum diuji.

[Peta Kedu kandidat03](peta-kedu-candidate-03-check-20261014.json) memeriksa native/final, preservasi kandidat01/02/data dan14 kotak sentuh rencana pada640×360/854×480. Ukuran kecil disimpan untuk review visual. Perhitungan kotak bukan validasi DOM, touchscreen atau integrasi game.

[Peta Kedu kandidat 02](peta-kedu-candidate-02-check-20261014.json) memeriksa native/final, hash referensi/data/kandidat01 dan inventaris berlabel belum dipilih. Mencatat pembacaan web asli secara baca saja, serta browser galeri lokal yang diblokir protokol file. Bukan uji interaksi/integrasi Peta Kedu.

Laporan bukan sumber entri. Baca tanggal/lingkupnya bersama [PROGRESS.md](../docs/PROGRESS.md). Memindahkan laporan tidak berarti pemeriksaan dijalankan ulang.

| Berkas | Asal dan makna |
|---|---|
| `STATUS-GAMBAR.json` | Hasil build: jumlah entri/status, tanggal keputusan gaya dan waktu build |
| `STYLE-AUDIT.json`, `STYLE-AUDIT.csv` | Hasil build dari status aktif; seluruh visual `user_override`, bukan audit visual baru |
| `validation-report.json` | Hasil terbaru validator data/struktur/tautan; tidak mengukur kualitas budaya |
| `browser-check-report.json` | Hasil browser lulus terakhir; baca progres untuk percobaan gagal sesudahnya |
| `prompt-review-report.json` | Pemeriksaan 273 prompt pada 11 Oktober 2026 |
| `icon-update-report.json` | Rekap 83 ikon yang dibuat ulang pada 11 Oktober 2026 |
| `icon-replacement-verification.json` | Hash ikon dibandingkan cadangan; bukan review visual |
| `resize-report.json` | Resize 213 raster pada 11 Oktober 2026; 8 sudah tepat saat itu |
| `reorganization-report.json` | Pemindahan/perapian 12 Oktober 2026 dan pembandingan hash data/aset |
| `character-frame-report.json` | 10 key pose AI menjadi 240 frame gerak sekunder; hash, dimensi, alpha, dan cadangan |
| `character-frame-verification.json` | Pemeriksaan berkas frame, kaki tetap, riwayat, serta aset/data lain tidak berubah |
| `character-browser-check-report.json` | Pemeriksaan setiap pose/frame 24 dan kontrol preview lokal di Edge |
| `remaining-legacy-style.json`, `remaining-legacy-style.txt` | Nama aset yang belum diganti sejak riwayat audit lama; status katalog tetap `user_override` |

Keputusan gaya tetap 11 Oktober 2026 meskipun build dilakukan kemudian. Data audit lama per entri berada di `previous_style_review` dalam data aktif. Tidak semua laporan di folder ini dibuat ulang pada perapian dokumen.

Revisi puzzle 14 Oktober: [metrik keping](puzzle-tile-review-20261014.json) dan [verifikasi data/hash/ukuran](puzzle-update-verification-20261014.json). Metrik bukan validasi budaya atau uji susun manusia.

[Browser puzzle](puzzle-browser-check-20261014.json) mencatat 10 puzzle tiap wilayah, koleksi 66 Tantangan, detail dan pembukaan WebP baru. Pemeriksaan 25 detail dilakukan dalam browser-check.cjs.


[Pemeriksaan paket katalog 14 Oktober](catalog-update-20261014.json) meliputi slot bahasa, preservasi 600 entri, 88 Narasi, decode/durasi/puncak audio serta inventaris mockup. Laporan historis hash/mockup/puzzle tetap memakai snapshot terdahulu.
