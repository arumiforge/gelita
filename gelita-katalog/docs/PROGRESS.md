# Progres proyek

Diperbarui 13 Oktober 2026 (Asia/Bangkok). Urutan di bawah membedakan hasil terkini dari pemeriksaan historis. Keputusan aktif dirangkum dalam [MEMORY.md](MEMORY.md).

## 13 Oktober 2026 — konsep hasil dipakai bersama lintas kota

- Menambahkan keterangan pengguna bahwa halaman Tantangan selesai adalah satu konsep untuk seluruh kota/wilayah, dengan Temanggung hanya contoh mockup. Memperbarui README proyek/mockup, daftar mockup, keterangan, spesifikasi, kebutuhan aset, instruksi agen, konteks/keputusan dan keterangan empat keadaan UI.
- Instruksi implementasi kini meminta satu komponen bersama: nama wilayah, jenis tantangan, mapping latar, data hasil, label/rute peta dan alur Berikutnya mengikuti konteks permainan. Panel/tombol/HUD dan ikon digunakan bersama; latar khusus kota dipilih dari mapping aset yang tersedia. Nama folder/kode serta gambar pilihan tetap dipertahankan. Cakupan dicatat dalam provenance mockup.
- Validator data/tautan dan pemeriksa paket lulus: 590 entri aktif, 774 tautan Markdown, 62 berkas paket, gambar/hash/salinan dan 97 audio utuh. Tidak mengubah data katalog, gambar, preview kode atau hasil render; build dan browser tidak diulang karena perubahan hanya keterangan.

## 13 Oktober 2026 — ikon bahasa bendera Indonesia dan UK

- Mengikuti koreksi pengguna: bahasa Indonesia memakai bendera merah–putih, English memakai bendera UK. Membuat dua medali dengan built-in ImageGen, memakai alas HUD navy–emas sebagai acuan. Memeriksa dua gambar hasil: merah di atas putih, Union Jack, tanpa tulisan; alpha luar transparan. Keluaran asli disimpan sebagai WebP dalam provenance, hasil resize proporsional 256 × 256 dipakai di katalog. [Prompt dan hasil](../provenance/language-flag-generation-results-20261013.json).
- Menambah icon.language-id dan icon.language-en tanpa mengubah 588 entri sebelumnya. Katalog kini 590 entri, 493 visual (440 WebP/53 SVG), 85 Ikon dan 97 audio. reference.19/20 serta salinan medali huruf lama tetap utuh.
- Preview HUD/pilihan bahasa memakai bendera baru, termasuk pergantian pemicu ke UK ketika English dipilih. Mempertahankan label Bahasa Indonesia/English, hit area dan layout tetap 16:9. Empat render full UI diperbarui; prompt, daftar 62 berkas, pemetaan dan instruksi agen diselaraskan.
- Build, validator data/tautan dan pemeriksa paket lulus; 588 objek sebelum ikon bendera serta seluruh aset lama/audio dipertahankan. Pemeriksaan dimensi/alpha/hash kedua WebP lulus. Browser katalog Edge lulus dengan 493 kartu dan dua ikon baru; browser preview lulus untuk flag aktif, pilihan bahasa/terjemahan, empat keadaan dan hit area minimal 44 px pada 640 × 360 serta 854 × 480. Render popover dibuka untuk review.
- Pemeriksa penggantian ikon historis dibatasi pada 83 changed_keys resmi agar penambahan dua ikon tidak dianggap penggantian terhadap backup lama. Pemeriksaan tersebut, sintaks skrip yang diubah dan whitespace Git lulus. Belum menerapkan ikon ke aplikasi game asli; tidak mengubah audio, commit atau push.

## 13 Oktober 2026 — full UI hasil mengikuti mockup pilihan

- Membaca panduan proyek dan melihat gambar asli exec-2171e211 setelah menormalkan awalan path /C:/ yang tidak valid di Windows. Mempertahankan WebP pilihan 1672 × 941 dengan RGB dan hash identik.
- Membuat enam skin WebP transparan melalui built-in ImageGen berdasarkan full UI pilihan: panel navy berornamen, tombol primer, peta, detail, alas HUD dan bintang. Tujuh panggilan mencakup satu koreksi panel. Prompt sebelum generasi, prompt aktual/koreksi, keluaran asli WebP dan hasil normalisasi dicatat dalam [provenance](../provenance/result-ui-generation-results-20261013.json). Normalisasi memakai crop margin alpha, contain proporsional dan padding, bukan tambahan detail AI.
- Memakai ulang background yang sudah selesai, logo ui.logo-hero, medali avatar.2, ID/EN dan ikon katalog. Menambah overlay efek SVG native berupa halo, garis cahaya dan kilau; medali tidak digenerasi ulang. Lanskap hanya dipromosikan ke slot bg.result.temanggung, tidak dibuat lagi.
- Menambahkan delapan slot resmi: tujuh ui.result-* dan bg.result.temanggung. Katalog kini 588 entri, 491 visual (438 WebP/53 SVG) dan 97 audio. 580 objek lama dan 494 berkas aset asli dipertahankan persis; baseline audio tetap identik. Cadangan sebelum perubahan ada di archive/before-result-ui-20261013/. Status mengikuti user_override berdasarkan contoh yang diminta, bukan audit baru seluruh katalog.
- Memperbaiki preview 1920 × 1080 berdasarkan ukuran/posisi acuan: logo/kota, grup ID–speaker–menu, panel, judul, bintang, medali/progres, dua kartu dan tiga tombol. Skin gambar dipisahkan dari teks dinamis dan hit area. reference-layout.css mengompensasi padding alpha; font Plus Jakarta Sans memakai salinan font aplikasi asli. Detail ornamen baru masih sedikit berbeda dari contoh, sehingga tidak diklaim identik piksel.
- Memperbarui instruksi agen web, kebutuhan aset, prompt, spesifikasi dan daftar 60 berkas paket. Empat render full UI disimpan dalam subfolder terpisah dan dibuka untuk review: utama, bahasa, menu, detail. Skin SVG awal tetap disimpan sebagai sketsa, tidak dipakai untuk panel/tombol/HUD utama.
- Build dan validator katalog lulus: HTML/data cocok, 588 kode/ID unik, seluruh 580 objek lama persis, 97 audio identik, gaya/riwayat dan slot lama dipertahankan, tautan dokumentasi tersedia. Browser katalog Edge lulus: 491 kartu visual, tujuh slot ui.result-* terlihat, gambar panel terbaca, detail/filter/pencarian/ekspor, 144 frame Jaka, 26 referensi, 97 audio, mobile tanpa overflow/galat JavaScript.
- Browser preview Edge lulus: empat keadaan 1920 × 1080, gambar/font lokal, bahasa/terjemahan, speaker toggle, menu/detail/Escape/fokus, skala tetap, nilai kartu tanpa overflow, hit area minimal 44 px dan dalam frame pada 640 × 360 serta 854 × 480. Pemeriksaan raster/alpha/dimensi/hash delapan aset lulus; pemeriksa paket mencocokkan referensi, salinan dan seluruh aset asli. Laporan: [preview](../reports/mockup-ui-browser-check.json), [paket](../reports/mockup-reference-verification.json), [aset](../reports/result-ui-assets-check.json).
- Pembaruan dokumen awal sempat berhenti akibat decoding cp1252; dilanjutkan dengan UTF-8 dan validator tautan lulus. Pemeriksaan sintaks JS dan whitespace Git lulus. Perubahan file aplikasi induk yang sudah ada tidak disentuh; tidak commit/push. Ini perubahan katalog dan preview, belum penerapan pada halaman game asli; route/akun/hadiah masih placeholder dan fullscreen perangkat nyata belum diuji.

## 12 Oktober 2026 — melengkapi mockup dengan bahan dan preview UI

- Menegaskan koreksi pengguna: mockup harus memuat halaman lengkap beserta UI; lanskap saja tidak memenuhi tujuan perombakan. Gambar pilihan mockup 01 memang sudah memuat UI dan dipertahankan identik, lalu dijadikan referensi utama yang lebih jelas dalam README, daftar berkas, prompt, dan instruksi agen.
- Menambah delapan sumber UI SVG native untuk panel hasil, tombol primer/sekunder/HUD, kartu statistik, serta panel bahasa/menu/detail. Membuat [preview HTML/CSS/JS](../mockup/gambar-mockup/tantangan-selesai-temanggung/preview-ui-lengkap.html) dengan kanvas tetap 1920 × 1080, penskalaan seragam, bahasa ID/EN dengan gambar katalog, speaker, menu, dan Detail hasil.
- Menyimpan empat gambar hasil render UI lengkap 1920 × 1080 WebP dalam subfolder keadaan yang berbeda, masing-masing beserta daftar/keterangan: tampilan utama, bahasa terbuka, menu terbuka, dan detail hasil terbuka. Ini turunan kode untuk menjelaskan interaksi, bukan mockup pilihan kedua atau generasi AI raster baru. Paket kini memiliki 49 berkas; daftar, pemetaan aset, spesifikasi UI dan provenance diperbarui.
- Membuka dan meninjau empat gambar UI. Memperbaiki nilai percobaan yang sempat membungkus keluar kartu. Pemeriksaan awal ukuran klik juga sempat gagal karena pengukuran mendahului event resize; menambahkan penantian skala, lalu pemeriksaan akhir lulus.
- `node scripts/verify-mockup-ui.cjs` lulus pada Edge headless: seluruh gambar lokal terbaca, empat keadaan tergambar, pilihan bahasa/terjemahan, speaker, menu/Escape/pengembalian fokus, Detail hasil, tanpa galat JavaScript, serta seluruh target klik yang terlihat minimal 44 px dan berada dalam viewport pada 640 × 360 dan 854 × 480. Pengukuran ini berlaku untuk preview, bukan aplikasi asli; fullscreen pada perangkat nyata belum diuji. Hasil ada di [laporan browser UI](../reports/mockup-ui-browser-check.json).
- `python scripts/verify-mockup-reference.py` lulus: 49 berkas tercatat, hash bahan UI/preview/gambar keadaan cocok, RGB gambar pilihan tetap identik, 20 salinan aset identik, data/HTML dan 494 aset asli tidak berubah, 97 objek audio tetap identik. Tidak menambah entri aktif atau mengintegrasikan route/auth/hadiah game; navigasi dan akun pada preview merupakan placeholder referensi.
- Validator katalog lulus dengan 740 tautan Markdown dan 6 tautan HTML statis; sintaks JavaScript preview/pemeriksa serta whitespace Git lulus. Tidak melakukan build katalog, commit, atau push.

## 12 Oktober 2026 — paket mockup pilihan untuk perombakan web

- Membuat [folder mockup](../mockup/README.md) dan daftar pilihan. Pengguna mengonfirmasi landscape pertama pada revisi terakhir, sebelum versi kedua dengan tombol lebih besar. Hanya gambar pilihan disimpan dalam subfolder `mockup/gambar-mockup/tantangan-selesai-temanggung/`; versi kedua, desktop awal, dan papan HP potret tidak disertakan.
- Menyimpan referensi mockup sebagai WebP lossless pada ukuran asli 1672 × 941, dengan RGB identik. Keluaran sedikit berbeda dari 16:9 karena pembulatan; dokumentasi tetap mewajibkan kanvas aplikasi tepat 16:9 dan tidak melakukan reflow responsif.
- Membuat satu lanskap baru memakai built-in image_gen, tanpa UI/teks/karakter/lentera, berdasarkan mockup pilihan dan reference.01 sebagai acuan finishing 3D. Menyimpan keluaran asli sebagai WebP dalam provenance dan final opak 1920 × 1080 dalam paket. Normalisasi proporsional LANCZOS dengan crop tengah sangat kecil bukan tambahan detail AI. Membuka dan memeriksa gambar lanskap hasil generasi; ini bukan audit seluruh katalog atau validasi geografis/budaya/lisensi.
- Menyertakan 20 salinan aset katalog yang identik byte demi byte, termasuk avatar.2, logo, ID/EN, dan kontrol SVG. Menambah dua SVG native 24 × 24 untuk masuk/keluar layar penuh, tanpa generasi raster dan tanpa memberi kode slot resmi baru. Panel, tombol, statistik, rating, badge dan progres diarahkan untuk dibuat dengan kode.
- Melengkapi daftar 31 berkas paket, keterangan keputusan, kebutuhan aset, prompt lanskap aktual, brief reproduksi mockup yang dibedakan dari prompt historis, petunjuk agen web, [provenance](../provenance/mockup-tantangan-selesai-temanggung.json), dan [laporan pemeriksaan](../reports/mockup-reference-verification.json).
- `python scripts/verify-mockup-reference.py` dan sintaks Python lulus: seluruh daftar paket tersedia, raster terbaca, SVG valid, lanskap tepat 16:9/opak, prompt cocok dengan provenance, 20 salinan cocok dengan sumber, RGB mockup identik, serta hash data/HTML dan seluruh 494 berkas aset asli tetap identik. Semua 97 objek audio identik dengan baseline; 580 entri aktif tidak ditambah atau diganti.
- `node scripts/validate.mjs` lulus: 580 ID/kode unik, 483 visual tersedia, 97 objek audio identik, serta 669 tautan Markdown dan 6 tautan HTML statis tersedia. Pemeriksaan whitespace Git lulus. Build dan browser aplikasi tidak dijalankan; sumber data/template katalog tidak berubah dan kode aplikasi tidak tersedia dalam paket. Ukuran sentuh setelah penskalaan serta integrasi web belum diuji. Tidak melakukan commit atau push; perubahan dokumentasi rasio layar yang sudah ada dipertahankan.

## 12 Oktober 2026 — catatan rasio layar aplikasi

- Menambahkan keterangan pengguna ke README, CONTEXT, MEMORY, dan AI-GUIDE: seluruh aset gambar digunakan di aplikasi GELITA dengan rasio layar tetap 16:9 (landscape), dan halaman aplikasi tidak mendukung tata letak responsif untuk rasio layar lain.
- Menjelaskan bahwa rasio tersebut berlaku pada halaman aplikasi, sementara ukuran dan proporsi tiap aset mengikuti slotnya. Panduan AI juga membedakan pemeriksaan desktop/mobile katalog dari dukungan responsif aplikasi game.
- Perubahan hanya berupa dokumentasi; data, gambar, audio, prompt, dan tampilan katalog tidak diubah.
- `node scripts/validate.mjs` lulus: 580 ID/kode unik, 483 visual tersedia, data HTML identik, 97 objek audio identik dengan baseline, serta 563 tautan Markdown dan 6 tautan HTML statis tersedia. Laporan validasi diperbarui. Build dan pemeriksaan browser tidak dijalankan karena sumber data/template tidak berubah.

## 12 October 2026 (later) — frames installed in the game

*(New section, English, written from the GELITA app repo; branch `pbb/nifty-faraday-fcw9r6`.)*

- `php spark gelita:katalog:impor` in the app now reinstalls an image it installed before when its catalogue file changed. A server that imported the old edition gets the 30 replaced frames plus 210 new ones, and everything else is skipped as unchanged. No catalogue files were changed for this.

## 12 Oktober 2026 — perbaikan konflik sebelum push GitHub

- Memeriksa PowerShell lokal dan Git repositori induk. Setelah `git fetch origin`, branch `main` dan `origin/main` sama-sama berada pada `8b4d358`; tidak ada perubahan lokal atau staging pada awal pemeriksaan.
- Menghapus penanda konflik merge yang sudah tersimpan dalam PROGRESS dengan mempertahankan kedua catatan pekerjaan. Menjalankan `node build.mjs` untuk membuat ulang HTML dan laporan status dari `data.json`, lalu `node scripts/validate.mjs` untuk membuat ulang laporan validasi. Tidak mengubah sumber data, aset, atau 97 objek audio.
- Build dan validator lulus: 580 ID/kode unik, 483 visual tersedia, 240 frame karakter, data HTML identik, audio identik dengan baseline, 563 tautan Markdown dan 6 tautan HTML statis tersedia. Sintaks satu blok JavaScript HTML dan parsing kedua laporan JSON lulus; keempat berkas bebas penanda konflik. Pemeriksaan whitespace Git lulus. Pemeriksaan browser tidak dijalankan dalam pekerjaan ini.
- Persiapan commit/push dibatasi pada `gelita-katalog/`: PROGRESS, index, laporan status, dan laporan validasi. Commit lama yang sudah ada di GitHub tidak diubah.

## 12 Oktober 2026 — 24 frame setiap pose Jaka dan Mbah Kedu

- Mengikuti permintaan pengguna: enam pose Jaka (idle, happy, bow, sad, afraid, determined) dan empat pose Mbah Kedu (idle, smile, worried, weak), masing-masing 24 frame. Total 240 WebP transparan 700 × 900 mengganti 30 file lama dan menambah 210. Kode frame 1–3 dipertahankan; frame ditampilkan dalam urutan angka per pose.
- Membuat dan memeriksa individual 10 key pose dengan image_gen, memakai reference.01/Jaka syal merah dan reference.02/Mbah Kedu. Sepuluh gambar dasar diberi gerak sekunder deterministik bertahap dengan kaki tetap; bukan 240 generasi AI terpisah, perubahan ekspresi antarframe, atau animasi rig sendi. Satu percobaan generasi ekspresi ditolak penyaring gambar; deskripsi ekspresi sederhana berhasil pada percobaan berikutnya.
- Mencadangkan data awal dan 30 frame lama di `archive/before-character-frames-20261012/`. Menyimpan prompt/sumber di [provenance karakter](../provenance/character-generation-results.json), key pose WebP lokal di `provenance/character-keyposes/`, dan parameter gerak/hash frame di [laporan frame](../reports/character-frame-report.json).
- Memperbarui data, build, validator, pemeriksaan browser, jumlah aktif dalam dokumentasi, serta panduan/provenance. Kini ada 580 entri: 483 visual (431 WebP, 52 SVG) dan 97 audio. `style_review` berbasis `user_override` serta `previous_style_review` seluruh 273 visual awal identik; 210 frame baru memiliki catatan asal sendiri.
- Build dan validator lulus: 580 ID/kode unik, 483 visual tersedia, 10 pose × 24 slot, data HTML identik, 475 jalur migrasi tersedia, dan 97 objek audio identik. Tautan 16 Markdown aktif serta 6 tautan HTML statis lulus.
- [Verifikasi frame](../reports/character-frame-verification.json) lulus: 240 berkas dengan hash berbeda, dimensi/alpha benar, piksel kaki yang terlihat dan alpha pada baris 853–899 identik per pose, serta 30 file lama benar-benar diganti. Seluruh 340 entri lain identik; 253 hash aset baseline lain tetap identik. Semua 431 raster aktif tepat target. Alpha transparan diperiksa untuk frame baru; ini tidak mengesahkan transparansi aset lama di luar karakter.
- Pemeriksaan kaki pertama membandingkan RGB piksel transparan dan gagal akibat pilihan encoder WebP; setelah RGB yang tidak terlihat dinormalisasi, pemeriksaan piksel terlihat/alpha lulus. Percobaan memperluas pemeriksaan alpha ke aset lama menemukan metadata alpha lama tidak sama dengan interpretasi Sharp untuk gambar opak `challenge.item.wnb-4-a07`; berkas/data itu tidak diubah dan pemeriksaan alpha dibatasi pada 240 frame baru. Dimensi seluruh raster tetap diperiksa.
- Browser Edge di sandbox gagal saat startup; kedua pemeriksaan di luar sandbox lulus. [Katalog](../reports/browser-check-report.json): 483 visual, 144 frame Jaka, detail/Escape, referensi, WebP asli, ekspor, 97 audio, pencarian/reset, mobile tanpa luapan/galat. [Karakter](../reports/character-browser-check-report.json): 24 kartu berurutan setiap pose, frame 24 semua pose terbuka pada 700 × 900, sepuluh pilihan pose/slider/playback preview, desktop/mobile tanpa luapan atau galat JavaScript.
- Membuat sepuluh lembar kontak frame dan [preview gerak lokal](../review/character-animation.html). Memeriksa seluruh key pose dan lembar frame happy/bow/weak; bukan review individual 240 gambar atau validasi budaya, lisensi, rig, maupun integrasi game.
- [Daftar nama gaya lama](../reports/remaining-legacy-style.txt) berisi 101 aset yang belum diganti berdasarkan `previous_style_review`; tiga aset lain masih tidak pasti di [laporan](../reports/remaining-legacy-style.json). Membaca enam lembar kontak sebelum penggantian dan membuat lima lembar kontak terbaru setelah karakter dikecualikan. Status katalog tidak diubah dan daftar ini bukan audit visual baru. Lembar keenam sementara yang sudah kedaluwarsa dihapus.
- Sintaks tujuh skrip JavaScript baru/diubah dan pemeriksaan whitespace Git lulus. Validator akhir lulus dengan 563 tautan Markdown; satu percobaan sempat gagal menulis laporan dengan galat filesystem `UNKNOWN`, lalu percobaan ulang berhasil. Tidak membuat commit atau push; staging yang sudah ada tidak diubah oleh agen. Audio tidak diubah dan tidak ada rekaman baru.

## 11 October 2026 (evening) — logo and start buttons from the references, game import

*(New section, English, written from the GELITA app repo; branch `pbb/nifty-faraday-fcw9r6`.)*

- **Owner decisions** in the app repo's decision log (`docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`): `ui.logo-hero` ← `reference.09`, `ui.logo` ← `reference.16`, start buttons as finished images styled like `reference.10`.
- **Replaced four images** (Pillow, Lanczos, lossy WebP q92 with full alpha):
  - `assets/logo/ui.logo-hero.webp`: `reference.09` padded with transparency to 1867 × 700 (8:3), scaled to 1600 × 600.
  - `assets/logo/ui.logo.webp`: `reference.16` scaled to 512 × 512.
  - `assets/antarmuka/ui.btn-start.webp`: `reference.10` padded to 960 × 320 (3:1), scaled to 720 × 240.
  - `assets/antarmuka/ui.btn-start.en.webp`: START made from `reference.10`. The MULAI letters were removed by rebuilding the enamel between two clean columns, then START was drawn in Cinzel Bold (the app's font) with the gold sampled from the original letters, a dark outline, a shadow, and a light bevel. Interim, until a real START exists.
- **Data:** in `data.json`, the four entries got a new `desc`, `generated_by`, `content_review`, and `actual`. The old ornament-only logo files and the flat SVG-rendered buttons are replaced; `ui.btn-start*.svg` stay as before.
- **Checks:** `node build.mjs` and `node scripts/validate.mjs` pass. No other image, prompt, or audio changed. The browser check was not run.
- **Game import:** the app now installs this catalogue's images with `php spark gelita:katalog:impor`, which re-encodes them as lossy WebP for the game. Images here stay lossless.

## 12 Oktober 2026 — dokumentasi dan struktur

- Membaca panduan/data/skrip dan memeriksa kondisi folder sebelum perapian.
- Memindahkan dokumentasi ke `docs/`, laporan ke `reports/`, manifest/pemetaan ke `provenance/`, template ke `templates/`, skrip aktif ke `scripts/`, dan lima skrip sekali pakai ke `archive/scripts/`.
- Mempertahankan README, AGENTS, data, index, dan pintu masuk `build.mjs` di root. `node build.mjs` tetap menjadi perintah build.
- Menghapus `sources/` setelah membandingkan kedua dokumennya dengan salinan identik di `archive/source-documents/`. Menghapus README.txt root yang mengulang panduan; salinan historis tetap tersedia.
- Memperbarui seluruh Markdown aktif dan memberi penanda pada dokumen Markdown historis; isi sumber lama dipertahankan sebagai bukti. Progres sebelum perapian ada di [arsip](../archive/before-2026-10-12/PROGRESS.md).
- Menambah panduan AI, skema data, pemeliharaan, serta README folder. Memperbaiki lokasi laporan, prompt, tautan HTML, dan pencarian root pada skrip aktif.
- Memisahkan tanggal keputusan gaya dari waktu build/validasi. Menghapus ketergantungan lokasi paket pribadi pada skrip browser/lembar ikon aktif; paket dapat diberikan melalui lingkungan.
- Build dan `scripts/validate.mjs` lulus: 370 ID/kode unik, 273 visual tersedia, data HTML identik, status override/riwayat utuh, 475 tujuan migrasi tersedia, dan audio identik. 16 Markdown aktif dengan 343 tautan serta 6 tautan HTML statis diperiksa; isi tautan dokumen arsip dikecualikan karena berasal dari repositori aplikasi lama. Sintaks tiga skrip JavaScript aktif yang diubah juga diperiksa; pemeriksaan whitespace Git lulus.
- Memeriksa hash sebelum/sesudah: `data.json` dan 283 berkas aset yang sudah ada identik byte demi byte. Seluruh 221 raster WebP berhasil dibaca, tepat target, dan metadata alpha cocok; tidak ada PNG lepas. Pemeriksaan resize tanpa perubahan menemukan 0 gambar perlu resize. Sintaks seluruh skrip Python aktif valid.
- Browser Edge desktop/mobile lulus pada 12 Oktober 2026: filter/pencarian, 18 frame Jaka, detail/Escape, ikon 512 px, 26 referensi, pembukaan WebP, ekspor JSON, 97 audio, hasil kosong/reset, tanpa luapan horizontal atau galat JavaScript. Startup di sandbox gagal; percobaan di luar sandbox lulus. Screenshot terbaru disimpan di `review/`; lembar kontak tidak dibuat ulang.
- Status Git repositori induk berhasil dibaca di luar sandbox setelah akses dari sandbox ditolak. Tidak membuat commit atau push. Daftar pemindahan dan bukti hash ada di [laporan perapian](../reports/reorganization-report.json); pemeriksaan data di [laporan validasi](../reports/validation-report.json), browser di [laporan browser](../reports/browser-check-report.json).
- Tidak ada generasi AI, rekaman, perubahan isi data, atau perubahan gambar aset dalam pekerjaan ini. Semua dokumentasi aktif kini mengikuti struktur dan kondisi terbaru; sejarah tetap diberi penanda.

## 11 Oktober 2026 — hasil terakhir sebelum perapian

- Seluruh 273 visual berstatus “Sesuai gaya” berdasarkan `user_override`; audit lama dipertahankan pada `previous_style_review`. Ini penetapan pengguna, bukan audit visual baru.
- 273 prompt aktif diselaraskan: ilustrasi 3D, identitas Jaka/Mbah Kedu, peta relief, alpha, label bahasa, dan SVG; media faktual tetap dokumenter. Audio identik dengan baseline.
- Seluruh 83 Ikon dibuat ulang: 20 generasi ilustratif terpilih, 4 turunan ukuran aplikasi, 51 SVG utama kategori Ikon, dan 8 kontrol SVG diekspor ke WebP. Ikon aplikasi memakai Lentera Kedu emas/navy; motif wilayah/avatar dipertahankan.
- Favicon 32 px diekspor dari SVG. Ikon aplikasi dan bundengan dikoreksi, diperiksa individual; lembar ikon diperiksa. Review ini bukan validasi budaya/animasi.
- Build/validator lulus saat itu: ID/kode, tautan, HTML/data, target, override, ikon, dan audio. Hash seluruh 83 ikon berbeda dari cadangan; tidak ada PNG lepas.
- Pemeriksaan browser lulus di Edge di luar sandbox: 273 kartu, filter, 18 frame Jaka, dialog/Escape, ikon 512 px, 26 referensi, pembukaan WebP, ekspor JSON, 97 audio, hasil kosong/reset, mobile tanpa luapan/galat. Percobaan startup Edge dalam sandbox sebelumnya gagal.
- Bukti: `reports/icon-update-report.json`, `reports/prompt-review-report.json`, `reports/icon-replacement-verification.json`, `provenance/icon-generation-results.json`, `provenance/generation-manifest.json`, dan `archive/before-icons-20261011.zip`.

## 11 Oktober 2026 — resize dan pembersihan format

- Menghapus 220 PNG lepas setelah memastikan WebP tersedia; membuat 11 WebP lossless untuk sembilan referensi dan dua screenshot. Kode resmi serta nama sumber historis tidak diubah.
- Resize deterministik tanpa generasi AI: 213 WebP diresize, 8 sudah tepat. Seluruh 221 raster aktif tepat target; 26 latar di folder latar berukuran 1920 × 1080.
- Mempertahankan proporsi dengan crop tengah atau padding transparan; backup ZIP diverifikasi sebelum penggantian. Pembesaran tidak menambahkan detail.
- Metadata/build/validator lulus; pemeriksaan ulang resize menemukan 0 perbedaan. SVG dan screenshot tidak ikut resize raster bertarget. bg.welcome dan ui.logo diperiksa individual; tidak ada audit ulang seluruh gambar saat resize.
- Bukti: `reports/resize-report.json`, `archive/before-resize-20261011T155118391929Z.zip`.

## 11 Oktober 2026 — pekerjaan awal (riwayat)

- Membuat ulang 9 referensi hilang: 02, 03, 05, 06, 07, 09, 10, 11, 12 sebagai gambar baru, bukan pemulihan asli.
- Menghapus folder tambahan jakahappy; frame resmi dipertahankan. Memetakan 475 nama lama ke nama berdasarkan kode/kelompok, termasuk varian format.
- Audit lembar kontak awal menghasilkan 68 sesuai, 141 belum sesuai, 3 perlu tinjau, dan 61 tidak berlaku. Angka ini **digantikan** keputusan penandaan semua visual sesuai; bukan status terkini.
- Pada tahap awal 213 raster berbeda dari target dan browser membuka PNG; kondisi tersebut digantikan pembersihan WebP/resize/pembaruan ikon berikutnya.
- Membuat katalog berkelompok dan dokumentasi lokal; membekukan 97 objek audio.

## Batas hasil

Gambar di luar pekerjaan referensi/ikon/karakter tidak dibuat ulang secara massal. Prompt terbaru serta status keputusan pengguna tidak membuktikan aset lama telah diperbaiki. Pemeriksaan preview gerak sekunder bukan validasi rig atau integrasi aplikasi game. Tidak ada validasi budaya/lisensi atau rekaman audio baru. Dokumen aplikasi/workbook di arsip bukan file yang disertakan dalam paket ini.
