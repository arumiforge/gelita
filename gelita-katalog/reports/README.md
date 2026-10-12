# Laporan dan lingkup pemeriksaan

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
