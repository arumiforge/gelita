# GELITA — Perpustakaan aset

Katalog statis internal untuk game edukasi kelas 5 tentang Temanggung, Magelang, dan Wonosobo. Dokumentasi diperbarui **15 Oktober 2026**, zona waktu Asia/Bangkok. Paket ini berisi katalog, aset, serta bahan revisi bank soal/naskah/puzzle di `update/`; kode aplikasi game dan backend tidak disertakan. Sembilan audio nonnarasi sintetis tersedia untuk pratinjau; rekaman narasi belum disertakan.

## Konteks layar aplikasi GELITA

Seluruh aset gambar dalam katalog ini digunakan di aplikasi GELITA yang memakai rasio layar tetap **16:9** (landscape). Halaman aplikasi tidak mendukung tata letak responsif yang menyesuaikan susunan elemen untuk rasio layar lain. Komposisi latar, penempatan karakter, dan elemen antarmuka perlu dipahami dalam konteks halaman 16:9 tersebut. Rasio ini berlaku pada halaman aplikasi; setiap aset tetap mengikuti ukuran dan proporsi slotnya masing-masing.

## Membuka katalog

Buka [index.html](index.html) langsung di browser. Tidak memerlukan akun, server, atau internet. Halaman memakai Bootstrap 5.3.8 dan font lokal, dengan pencarian, kelompok koleksi, filter wilayah/gaya, pengurutan nama/kode, detail aset, salin prompt/brief, serta menu “Simpan hasil” untuk ekspor JSON/CSV. Bagian “Ada yang baru di GELITA” memuat bahan revisi terbaru. Saat dibuka sebagai berkas lokal, gambar asli dibuka di tab baru untuk disimpan melalui browser.

Untuk membagikan katalog, sertakan `index.html`, seluruh `assets/` (termasuk `catalog/` dan `vendor/`), `README.md`, `docs/`, `reports/`, `update/`, dan `mockup/` agar tampilan, gambar, font, serta tautan bahan revisi/preview tetap tersedia. Untuk pengembangan, gunakan seluruh folder ini. Mengunggahnya ke GitHub tidak otomatis mengaktifkan hosting web.

## Kondisi saat ini

| Isi | Jumlah/kondisi |
|---|---|
| Entri katalog | 611: 514 visual dan 97 audio/brief (9 dapat diputar) |
| Visual utama | 461 WebP dan 53 SVG; 461 raster sesuai ukuran target |
| Frame karakter | 240: 10 pose × 24 frame; mengganti 30 frame lama |
| Ikon | 85: 83 diperbarui 11 Oktober; 2 bendera bahasa ditambahkan 13 Oktober; slot reference.19/20 diperbarui 14 Oktober |
| Referensi yang dibuat ulang | 9 gambar baru; bukan pemulihan berkas asli |
| Audio | 88 brief narasi identik dengan baseline; 3 musik dan 6 efek suara sintetis tersedia |
| Status gaya | 514 “Sesuai gaya” berbasis `user_override`; 273 awal, 210 frame tambahan, 8 aset hasil, 2 ikon bahasa, dan 21 puzzle tambahan |

Jumlah visual menghitung entri utama, bukan seluruh berkas alternatif/sumber kontrol SVG. Status 273 visual awal dipertahankan dari keputusan pengguna 11 Oktober 2026, termasuk gambar lama di luar karakter yang belum diperbaiki; 210 frame tambahan mengikuti permintaan 12 Oktober dan 8 aset hasil mengikuti contoh full UI pada 13 Oktober. Ini **bukan audit visual baru**; `previous_style_review` menyimpan penilaian atau catatan asal sebelumnya. Ukuran yang sesuai tidak membuktikan budaya, lisensi, kualitas pembesaran, animasi, atau integrasi game telah disahkan.

## Arah visual aktif

- Ilustrasi mengikuti render 3D petualangan dengan bentuk bervolume dan pencahayaan hangat.
- Jaka mengikuti [reference.01.webp](assets/referensi/reference.01.webp): rambut cokelat acak, kemeja krem, syal merah, celana cokelat, batik pinggang, sandal, dan ransel kulit. Jangan kembali ke lurik–iket atau rompi biru dari panduan lama.
- Avatar Jaka memakai [reference.03.webp](assets/referensi/reference.03.webp); Mbah Kedu mengikuti [reference.02.webp](assets/referensi/reference.02.webp). Syal merah hanya berlaku untuk Jaka.
- Kontrol UI memakai SVG emas yang jelas pada ukuran kecil; foto/video faktual mempertahankan brief dokumenter.
- Raster aktif memakai WebP. Nama mengikuti kode aset; kode resmi seperti `app.icon-512.png` tetap memakai berkas `app.icon-512.webp`.

514 prompt visual aktif tersedia. Sumbernya `data.json`; salinan build ada di [docs/PROMPTS-ENGLISH.md](docs/PROMPTS-ENGLISH.md). Prompt yang benar-benar dipakai pada generasi AI dicatat terpisah dalam [provenance/](provenance/README.md).

Frame karakter diperbarui 12 Oktober 2026: enam pose Jaka dan empat pose Mbah Kedu, masing-masing 24 WebP transparan 700 × 900. Sepuluh key pose dibuat dengan image_gen, kemudian diberi gerak sekunder bertahap; bukan 240 generasi AI terpisah atau animasi rig sendi. Kode frame 1–3 dipertahankan, frame 4–24 ditambahkan. Rincian di [laporan frame](reports/character-frame-report.json); [nama aset gaya lama](reports/remaining-legacy-style.txt) mengikuti riwayat audit dengan mengecualikan aset yang telah dibuat ulang.

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
| [mockup/](mockup/README.md) | Mockup pilihan pengguna, kebutuhan aset, prompt, dan petunjuk perombakan web |
| [archive/](archive/README.md) | Baseline audio, cadangan, dokumen, prompt, dan skrip historis |

Folder `sources/` dihapus pada 12 Oktober 2026. Kedua dokumennya memiliki salinan identik di `archive/source-documents/`. `README.txt` root yang mengulang panduan juga dihapus; versi historis di arsip dipertahankan.

## Memperbarui dan memeriksa

1. Baca [panduan AI](docs/AI-GUIDE.md) dan [pemeliharaan](docs/MAINTENANCE.md).
2. Ubah `data.json`, aset terkait, atau `templates/catalog.template.html`; tampilan/interaksi katalog ada di `assets/catalog/catalog.css` dan `assets/catalog/catalog.js`. Jangan mengedit `index.html` secara terpisah.
3. Jalankan `node build.mjs`, lalu `node scripts/validate.mjs` dari root. Keduanya memakai modul bawaan Node.js.
4. Bila aset berubah, periksa dimensi/alpha dan tampilan. Python memerlukan Pillow; pemeriksaan browser memerlukan Playwright, Edge, Python, dan Pillow. Perintah lengkap ada di panduan pemeliharaan.
5. Catat perubahan dan pemeriksaan nyata dalam [docs/PROGRESS.md](docs/PROGRESS.md), lalu tinjau berkas sebelum commit/push.

Permintaan 14 Oktober mengizinkan produksi 3 musik dan 6 efek suara tanpa narasi. 88 objek Narasi tetap dibekukan; baseline 97 brief tetap disimpan sebagai sejarah. Jangan mengubah narasi atau menjalankan ulang produksi audio tanpa permintaan baru. Skrip migrasi/generasi lama di `archive/scripts/` bukan langkah rutin. Tidak ada regenerasi gambar atau perubahan audio pada perapian 12 Oktober 2026.

Revisi full UI hasil 13 Oktober: [preview 16:9](mockup/gambar-mockup/tantangan-selesai-temanggung/preview-ui-lengkap.html), [aset dan petunjuk](mockup/gambar-mockup/tantangan-selesai-temanggung/kebutuhan-aset.md). Enam gambar baru, satu lanskap yang dipakai ulang, dan satu efek SVG menambah delapan slot tanpa mengganti entri lama.

Ikon bahasa aktif baru: [Indonesia](assets/ikon/icon.language-id.webp) dan [English/UK](assets/ikon/icon.language-en.webp). Slot reference.19/20 sekarang memakai bendera terbaru pada kode/jalur yang sama; gambar dan sumber SVG huruf lama tersimpan sebagai riwayat.

Halaman “Tantangan selesai” pada paket mockup adalah satu konsep bersama untuk seluruh kota/wilayah game. Temanggung hanya contoh visual; komponen menerima konteks wilayah, jenis tantangan, hasil, latar dan navigasi dari aplikasi. [Petunjuk komponen bersama](mockup/gambar-mockup/tantangan-selesai-temanggung/petunjuk-agent.md).

Revisi puzzle 14 Oktober: 21 gambar baru untuk variasi d–j (tujuh per wilayah) dan empat penggantian untuk Sindoro–Sumbing, Pringapus, stupa Borobudur, serta mi ongklok. Semua 25 gambar berupa WebP opak 1200 × 1200. [Daftar dan prompt](docs/PUZZLE-UPDATE-20261014.md), [asal generasi](provenance/puzzle-generation-results-20261014.json), dan [hasil pemeriksa keping](reports/puzzle-tile-review-20261014.json). Tidak ditemukan keping polos/kembar; 11 gambar memiliki catatan kemiripan/pola. Uji susun dua orang dan verifikasi budaya/agama belum dijalankan.


Revisi katalog 14 Oktober: [galeri mockup](index.html#mockups) menampilkan referensi pilihan, empat keadaan UI dan preview interaktif di halaman. [12 brief halaman berikutnya](mockup/persiapan/README.md) berisi komposisi, state, aset, audio, prompt dan kriteria pemeriksaan; [Peta Kedu kandidat 04](mockup/gambar-mockup/peta-kedu/revisi-04/README.md) memiliki preview interaktif, efek ketik, diorama tiga lapisan dan empat render full UI; riwayat 01–03 juga tampil. Kandidat belum dipilih pengguna. Sebelas halaman lain belum memiliki gambar baru. [Audio nonnarasi](assets/audio/README.md) dapat diputar dari koleksi Audio & narasi dan speaker preview hasil. [Manifest audio](provenance/nonnarration-audio-20261014.json), [penggantian slot bahasa](provenance/language-slot-update-20261014.json), dan [pemeriksaan paket](reports/catalog-update-20261014.json).
