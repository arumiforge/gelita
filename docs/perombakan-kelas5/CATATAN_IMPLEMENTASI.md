# Catatan implementasi perombakan kelas 5

Keputusan dan penyimpangan dari ketiga dokumen perombakan, beserta alasannya dan hal yang perlu diketahui sesi berikutnya. Satu bagian per dokumen; bagian Dok 2 dan Dok 3 ditambahkan sesi yang mengerjakannya.

## Status

Status terkini dan sisa pekerjaan dipindah ke [`docs/PROGRESS.md`](../PROGRESS.md) (9 Oktober 2026). Berkas ini tetap menjadi log keputusan; mulai 9 Oktober 2026 bagian baru ditulis dalam bahasa Inggris.

Sisa Dok 2:

1. ~~**§5.3 klien + §12 teks UI**~~ — selesai, di `main` (PR #37).
2. ~~**§10 mockup**~~ — selesai, di `main` (PR #36).
3. ~~**§11 naskah cerita**~~ — selesai, di `main` (PR #38). Sesudah instalasi tetap perlu `gelita:narration:import` lalu persetujuan narasi draft di panel.
4. ~~Bagian "Dok 2" untuk §2–§9 dan uji manual §13.2~~ — lihat "Dok 2 — server, bank soal, dan uji manual §13". Kedua temuannya diperbaiki di bagian "Pembersihan" (butir 11–12).

### Perawatan repo

- 6 Oktober 2026: sejarah `main` ditulis ulang (penulis dan committer semua commit = Arumi Studios, baris `Co-Authored-By`/`Claude-Session` dibuang; isi berkas dan pesan commit tidak berubah). Semua branch lain dihapus. Hash commit lama yang disebut di dokumen lain tidak berlaku lagi.
- ~~Branch `dok2-mockup` sengaja dibiarkan di remote~~ — tidak berlaku lagi: branch itu digabung lewat PR #41 dan sudah tidak ada di remote (7 Oktober 2026).
- ~~Branch lama `happy-gates` yang dihapus tanpa digabung berisi satu perbaikan yang **belum ada** di `main`: tombol "Kenali" di peta yang diketuk sebelum `game/map.js` siap (±0,8 detik setelah dimuat, terlihat di 844×390) hanya mengubah hash `#kenal-{code}`, sedangkan `html.js` menyembunyikan overlay `:target`, sehingga overlay tidak terbuka. Usulan perbaikannya: saat modul siap, hash `#kenal-{code}[-n]` yang sudah ada membuka overlay di slide n lalu hash dibersihkan (`history.replaceState`), tanpa memutar narasi otomatis; narasi peta tidak dimulai bila overlay Kenali terbuka. Kerjakan ulang bila masih terjadi (setelah tata letak 16:9 Dok 3).~~ — diperbaiki di "Dok 3 — panggung 16:9" (8 Oktober 2026).
- 7 Oktober 2026: proposal paket aset (unduh sekali, pindah layar dan ganti bahasa seketika) ditulis di [`docs/rencana-paket-aset.md`](../rencana-paket-aset.md). Belum dikerjakan; tahapan yang bergantung pada Dok 3 ada di §10, keputusan pemilik di §12.

---

## Dok 1 — Fondasi data dan reset

Dikerjakan di branch `perombakan-kelas5` (dari `main`), sesuai `01_FONDASI_DATA_DAN_RESET.md` §5–§14. Suite: **419 test hijau** (`vendor/bin/phpunit --no-coverage`).

### Batasan sesi

- **Dok 2 dan Dok 3 tidak terbaca di sesi ini.** Perintah untuk membaca judul bagian keduanya ditolak pemeriksa izin otomatis lingkungan kerja. Seluruh pekerjaan berpegang pada Dok 1 saja; rujukan "Dok 2 §…" / "Dok 3 §…" di kode dan catatan ini diambil dari teks Dok 1. **Sesi Dok 2 perlu memeriksa ulang** butir yang ditandai *(cek Dok 2)* di bawah.

### Keputusan dan penyimpangan

**§5 Skema (migration `004100`–`004600`)**

1. Index komposit lama `participant_id_study_id_phase_id_status` sekaligus menjadi index FK `participant_id`, jadi `004100` lebih dulu membuat index pengganti `participant_id_study_id_status`, baru kemudian membuang `phase_id`. Nama FK dan index dibaca dari `information_schema` (helper baru di `GelitaMigration`), sehingga migration tidak bergantung pada nama yang dibuat MariaDB atau MySQL.
2. `down()` mengembalikan **struktur**, bukan data: fase, persetujuan, opsi jawaban, dan skor yang sudah dibuang tidak kembali. Backup sebelum migrate di server tetap wajib (prosedur rilis Dok 3 §13).

**§7 `gelita:reset`**

3. **Isi ulang bawaan memakai `ContentSeeder::seedNodes()`**, bukan `ContentSeeder::run()` (§7.3 langkah 6). `insertIfMissing` di `run()` akan memunculkan lagi dialog atau halaman Pustaka yang sengaja dihapus admin, dan itu melanggar "tidak disentuh". `seedNodes()` hanya membuat 12 node.
4. **Mode DELETE (akun DML):** `ParticipantModel::generateCode()` memakai `MAX(id) + 1`. Sesudah reset DELETE, siswa pertama mendapat `GLT-000001`, lalu kode berikutnya melompat ke nomor id lama. §7.3 menulis "kode siswa melanjutkan nomor lama", padahal yang sebenarnya terjadi adalah lompatan itu. Peringatan di layar menjelaskannya dan menyarankan menjalankan ulang dengan akun `gelita_migrate` (reset aman diulang).
5. **`--bersihkan-media-tantangan`:** daftar akhir media yang tidak terpakai baru diketahui **sesudah** impor bank soal. Sebelum reset, layar hanya menampilkan perkiraan dari isi saat itu. Daftar akhir ditampilkan dan dikonfirmasi sesudah impor (lewat callback `konfirmasi_media`), kecuali `--ya`. `asset_key` yang dihapus ikut dicatat di metadata audit.
6. Tambahan di luar §7: command lebih dulu memeriksa koneksi database. Sandi atau DSN yang salah kini menghasilkan pesan yang jelas dengan kode 1, bukan galat mysqli yang membingungkan. `db_sync_timezone()` dipanggil seperti command lain yang menulis.
7. `SystemResetService` menerima folder ekspor lewat konstruktor (bawaan `writable/exports`). Tujuannya agar uji tidak menghapus berkas ekspor sungguhan.
8. Workbook bawaan: `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` (`SystemResetService::DEFAULT_BANK`). Bila belum ada, `development` menjalankan `SampleItemSeeder`, sedangkan lingkungan lain hanya memberi peringatan dan 12 tantangan tetap kosong.
9. Gambar mockup memakai kait `App\Libraries\MockupGenerator::generateMissing()` (Dok 2 §10). Selama kelas itu belum ada, ringkasan menulis "belum tersedia". *(Dok 2: kelas sudah ada, lihat bagian "Dok 2 — gambar mockup".)*
10. `--hapus-guru`: FK `audio_assets.approved_by` adalah `SET NULL`, jadi rekaman yang pernah disetujui akun guru kehilangan nama penyetujunya. Biasanya rekaman disetujui admin, sehingga dampaknya kecil.

**§8 Seeder**

11. `SampleItemSeeder` (hanya development): Susun Gambar menampilkan **satu** gambar per pengerjaan, jadi gambar contoh kedua ditaruh di variasi `b` (`-b91`), bukan di variasi `a` (`-a92`) seperti tertulis di §8. `ContentVerifier` menolak dua gambar dalam satu variasi Susun Gambar.
12. Judul dan instruksi node di `ContentSeeder` (`TEXTS`) hanya sementara. Isi akhirnya datang dari workbook (Dok 2 §9).
13. Pada database yang **tidak** direset, `GameReleaseSeeder` hanya mengaktifkan rilis baru bila belum ada rilis aktif. Sebaliknya `ResearchStudySeeder` selalu menambah studi aktif, sehingga `db:seed` di database lama bisa menghasilkan dua studi aktif. Jalur resminya adalah `gelita:reset`, yang mengosongkan tabel lebih dulu.
14. `GameReleaseSeeder` memakai `CURRENT_TIMESTAMP(6)` di MySQL/MariaDB dan `CURRENT_TIMESTAMP` di SQLite (uji).

**§9 Satu tahap, variasi acak**

15. Urutan petunjuk `cari` diacak dengan `seeded_shuffle(…, 'cari|' . attemptId)` saat payload dibuat, sesuai §9.2: tetap saat dimuat ulang, berbeda antarpengerjaan. `selected_item_ids_json` tetap urut `sequence`.
16. Pengecoh rumpang hanya dibaca per variasi (`config_json.variants.{a|b|c}.distractors`). Daftar tingkat node lama tidak dibaca lagi. Judul per variasi ada di `config_json.variants.{group}.title_id/en`.
17. Pengerjaan "terbaik" per node untuk `totalStars()`/`levelSummary()` adalah pengerjaan dengan bintang terbanyak; bila sama, yang paling awal.

**§10 Pendaftaran tiga langkah**

18. `register.js` tetap **satu modul** yang memilih perilaku menurut `data-step` formulir, tidak dipecah menjadi berkas per langkah (§10.5). Fungsi bersama (penanda kolom wajib, penjaga kirim-ganda) dipakai ketiga langkah, dan perilakunya tetap terpisah per langkah.
19. "Negara lain" dikirim formulir sebagai `country_code = XX` (sudah begitu sebelumnya). Nama negara disimpan di `country_name_snapshot`.

**§11 Perangkat**

20. Versi OS dan peramban mengikuti pemotongan bawaan matomo (mayor.minor). iPad yang dikoreksi dicatat dengan `os_version = NULL` karena versi dari UA adalah versi macOS palsu.
21. Payload `viewport_changed` saat ini `{w, h, orientation, rotate_gate}`. Kolom `stage_w`, `stage_h`, dan `scale` ditambahkan Dok 3 §7.

**§12 Data mentah**

22. Event `screen_viewed`, `challenge_tries_exhausted`, dan `clue_failed` **hanya boleh ditulis server**. `EventService::ingest()` menolaknya dari klien (`event_type_server_only`) agar data navigasi dan batas percobaan tidak dapat dipalsukan. Ini tambahan di luar §12.3.
23. `ScreenViewFilter` juga dipasang pada grup `participantAuth`, karena `ganti-sandi` termasuk tabel §12.4. `gerbang/peta` tercatat sebagai `gate`.
24. `<body data-screen-code>` dirender layout game dengan resolver yang sama dengan `ScreenViewFilter`, lalu dipakai untuk `{screen}` pada `page_hidden`/`page_visible`. Atribut `data-screen` lama memakai kosakata lain (`map-kedu`, `mission-brief`, …) dan dibiarkan untuk CSS/JS.
25. `overlay_opened.level` selalu berisi **kode** wilayah (seperti contoh "kenal" di §12.3), juga untuk `hint`, `exit`, dan `lightbox`. Id wilayahnya diisi di kolom `level_id`.
26. Waktu isian (`first_input_ms`/`last_input_ms`) dihitung **per percobaan**: dihitung ulang sesudah setiap cek berhasil. Soal yang tidak disentuh dalam satu percobaan dikirim tanpa waktu, dan `item_responses.duration_ms` menyimpan `last_input_ms` percobaan terakhir yang punya waktu.
27. Soal yang sudah benar **terkunci** di cek berikutnya: tidak dicek ulang dan tidak menulis baris `item_tries` baru. *(cek Dok 2 §5)*
28. `exhausted` dan `tries_left` sudah dihitung, lalu dikirim di respons `/check` dan payload `challenge_checked`. **Batas 3 kesempatan belum ditegakkan**: tidak ada penutupan otomatis, dan event `challenge_tries_exhausted`/`clue_failed` belum ditulis. Keduanya bagian Dok 2 §5. Tombol "Selesaikan dengan jawaban ini" masih ada.
29. `completion_reason` diisi saat pengerjaan ditutup: `solved` bila semua soal dinilai benar di akhir, selain itu `tries_exhausted`. Ini termasuk siswa yang menekan "Selesaikan dengan jawaban ini" sebelum kesempatannya habis. *(cek Dok 2 §5: mungkin perlu nilai ketiga)*

**§13 Bintang**

30. Pengerjaan selesai **tanpa soal dinilai** mendapat ★★★ ("tidak ada yang salah"). Ini kasus tepi yang tidak disebut §13.1, dan dalam praktik tidak terjadi karena bank kosong menolak pembukaan tantangan.

**§14 Kode yang dihapus**

31. Selain daftar §14 ikut dihapus: CSS mati (halaman persetujuan, mode urutan kartu, rumpang ketik, kartu pendapat/sumber/pilihan, chart dan visual analitik, editor opsi/keping, tirai engine pilihan), label jenis soal lama dan pilar literasi digital (tantangan 5 Wonosobo sudah tidak ada) di `Language/id/Admin.php`, `ChallengeItem::digitalPillar()`, formatter `number`/`ratio` di `ExportLabels`, dan nama variabel `levelScore` → `levelSummary`.
32. Ekspor hanya diubah seperlunya: fase dibuang, dan kolom `variant_group`, `completion_reason`, `max_tries`, `try_count`, `solved_on_try`, `assented_at` ditambahkan. Lembar `item_tries` dan `device_logs` ada di Dok 3 §9.
33. Dasbor admin kini versi minimal berisi hitungan mentah, tanpa chart. Laporan PDF studi dihapus, dan PDF siswa diringkas menjadi data mentah. Rancangan ulang panel ada di Dok 3.

**§15 Uji**

34. `tests/_support/Database/ResetTables.php` berisi salinan skema lengkap sesudah `004600` (30 tabel, kolom, nilai bawaan, kunci unik) untuk SQLite. Isinya disalin dari `information_schema` MariaDB. **Migration baru di Dok 2/3 harus ikut ditambahkan di sini** (format kolom `TIPE[(panjang)][?][=bawaan]`), kalau tidak uji yang memakai tabel itu tidak melihat kolom barunya.
35. `BankWorkbookGuideTest` kini memeriksa templat unduhan importer, ditambah workbook kelas 5 bila sudah ada. Workbook produksi lama tidak lagi diperiksa (lihat butir Dok 2 di bawah).

### Pengujian yang dijalankan

- **Otomatis:** 419 test. Uji baru §15.1: `SystemResetTest`, `RegisterWizardTest`, `DeviceProfileTest`, `ItemTryWritesTest`, `ScreenViewFilterTest`, `StarsTest`. `LanguageFilesTest` kini menolak kunci `phase*`/`consent*`/`*lainnya`.
- **MariaDB 10.11.14** (lokal di container, salinan database 004000 berisi data uji coba):
  - `migrate` → `migrate:rollback` → `migrate`: skema identik dan sama dengan database pengembangan.
  - `gelita:reset --dry-run`: jumlah per tabel benar, tidak ada yang berubah.
  - Konfirmasi salah: keluar dengan kode 1.
  - Reset dengan `--pemeliharaan`: berkas penanda dihapus kembali. Isi `levels`, `dialogues`, `library_*`, `media_assets`, `audio_assets` sama persis (hash). Hasilnya 12 node, `AUTO_INCREMENT` kembali ke 1, kode berikutnya `GLT-000001`, sekolah ketikan terhapus, `school_id` staf di-NULL-kan, staf dibuka kuncinya, satu baris audit.
  - Akun DML `gelita_app`: jatuh ke mode DELETE disertai peringatan.
  - `--hapus-guru` dan `--bersihkan-media-tantangan` (ditolak, lalu disetujui) juga dicoba.
- **MySQL 8.0.46:** biner dari paket Ubuntu `mysql-server-core-8.0` diekstrak ke folder sementara dan dijalankan di port 3307, tanpa dipasang sebagai layanan. Salinan 004000 yang sama dimuat. Hasilnya sama: `migrate` → rollback → `migrate` dengan skema identik, reset TRUNCATE, dan jatuh ke DELETE dengan akun DML.
- **Alur web** (`php spark serve` + curl + Chromium Playwright):
  - Pendaftaran 3 langkah **tanpa JavaScript** dan **dengan JavaScript**: umur salah, NPSN kosong, kartu sekolah NPSN, tombol Kembali dengan isian tetap, sandi lemah lalu kuat, belum mencentang persetujuan.
  - Isi `device_logs`:
    - Chrome Windows (Client Hints header) → Windows 11;
    - Chrome Android (Pixel 7) → smartphone Google Pixel 7, Android 14;
    - "iPad" ber-UA Macintosh dengan 5 titik sentuh → tablet/Apple/iPad/iPadOS, `corrected = ipad_desktop_ua`.
  - Masuk ulang → `via = resume`.
  - Satu tantangan Susun Gambar dimainkan, dua kali cek → dua baris `item_tries`, `try_count = 2`, beserta event `screen_viewed`, `answer_selected`, `challenge_checked`, `viewport_changed`, `page_visible`.
  - Ketiga engine batch dimainkan berurutan:
    - Susun Gambar disusun utuh → ★★★, `solved`;
    - Isi Rumpang tiga kali salah lalu "Selesaikan dengan jawaban ini" → ★, `tries_exhausted`;
    - Benar atau Salah → `answer_selected` `set`.

    Event `answer_selected` `swap`/`set`/`clear` dan `reading_progress` 25/50/75/100 tercatat.
  - Semua halaman panel yang diubah terbuka tanpa galat PHP/JavaScript.
  - Peramban yang diemulasikan Chromium tetap tercatat "Headless Chrome" karena Chromium mengirim `userAgentData` sendiri. Di perangkat sungguhan, nilainya mengikuti peramban itu.
- CodeIgniter 4.7.4 menolak port di dalam DSN (`database_default_DSN`). DSN ditulis tanpa port, seperti di README dan `08_DEPLOYMENT.md`.

### Untuk sesi Dok 2

- **Format importer sudah berubah:**
  - sheet baru `variants` (`node_ref`, `variant_group`, `title_id`, `title_en`);
  - kolom `variant_group` di `items` dan `distractors`;
  - sheet `options`, `pieces`, `sources` dihapus, beserta jenis soal selain `puzzle_arrange`, `fill_blank_bank`, `verdict_card`, `find_object`.

  Akibatnya `docs/bank-soal/gelita-bank-soal-produksi.xlsx` dan `build-workbook.php` lama **tidak cocok lagi** dengan importer. Workbook baru diharapkan di `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` (dibaca `gelita:reset` dan `BankWorkbookGuideTest`).
- **Uji yang membaca data bank soal lama** perlu disesuaikan saat datanya diganti: `BankReadingDataTest` dan `NarrationTables::seedHuntNode()` (dipakai `HuntClueAudioTest`, `NarrationImporterTest`, `NarrationPagesTest`) membaca `docs/bank-soal/data/*.php`.
- **Bentuk `config_json` node:** `{"selection":"variant","variants":{"a":{"title_id":…,"title_en":…,"distractors":[{"id":…,"en":…}]}, …}, …}`, ditambah kunci per engine dari `ContentSeeder::config()`. Kunci node lama (`items_per_round`, `allow_retry`, `require_reason`, …) dibuang importer, editor konten, dan `ContentVerifier`.
- **Sisa pekerjaan Dok 2:**
  - `SampleItemSeeder` masih ada dan harus dihapus (§8). `DatabaseSeeder` di development belum mengimpor workbook kelas 5.
  - Batas 3 kesempatan, penutupan otomatis, dan event `challenge_tries_exhausted`/`clue_failed` belum ditegakkan (butir 27–29).
  - `MockupGenerator` belum ada (butir 9).
- `Config\Gelita::$maxTries` (3) disalin ke `challenge_attempts.max_tries` saat pengerjaan dibuka. `ChallengeAttempt::triesLeft()`/`isExhausted()` sudah tersedia.
- Sebelum menjalankan `gelita:reset` dengan workbook kelas 5, pastikan workbook itu **tidak** memuat sheet `library`/`library_media` yang berbeda dari Pustaka sekarang. Importer akan menimpanya, padahal reset dimaksudkan tidak menyentuh Pustaka.

### Untuk sesi Dok 3

- Belum dibuat:
  - lembar ekspor `item_tries` dan `device_logs` (§9);
  - `stage_w`/`stage_h`/`scale` di `viewport_changed` (§7);
  - tata letak 16:9 untuk pendaftaran (§4), panel, dan dasbor.
- `<body data-screen-code>` sudah tersedia untuk kebutuhan tampilan per layar.
- README dan `docs/01_DATABASE.md` baru diberi catatan ringkas "Perombakan kelas 5 — Dokumen 1". Bagian lainnya masih menggambarkan keadaan lama (lima tantangan, chart, persetujuan wali) dan ditulis ulang di §11.
- Prosedur rilis (§13): `gelita:reset` dijalankan dengan DSN akun `gelita_migrate` tanpa port. Bila kode berikutnya bukan `GLT-000001`, reset jatuh ke DELETE; ulangi dengan akun migrasi.

---

## Dok 2 — gambar mockup

Dikerjakan di branch `dok2-mockup` (dari `main`), sesuai `02_TANTANGAN_DAN_BANK_SOAL.md` §10. Bersamaan dengan sesi §5.3/§12 di `dok2-klien-kesempatan`; `challenge.js`, `engines/*`, dan tabel teks §12 tidak disentuh. Suite: **453 test hijau** (2 dilewati, sama dengan `main`).

### Komponen

- `App\Libraries\MockupGenerator`:
  - `slots(?node)`: slot gambar yang **dipakai node/soal aktif** (`challenge_nodes.scene_media_id`, `challenge_items.media_asset_id`), jenisnya dari pola kunci §10.3 (`kindFor()`), berikut wilayah, label, keterangan, judul variasi, dan posisi benda (adegan). Bank kelas 5 → tepat **45 slot**: 9 Susun Gambar, 3 pernyataan, 3 adegan, 30 benda.
  - `run($paksa, $dryRun, $node)` dan `generateMissing()` (kait `gelita:reset`).
  - `svg($slot)`: SVG per jenis.
  - Satu baris audit `media_mockups` per jalankan yang menulis berkas. Label audit ditambahkan di `Language/id/Admin.php` (`audit.media_mockups`).
- `App\Commands\Mockups`: `php spark gelita:mockups [--dry-run] [--paksa] [--node=tmg-4]`. Tabel per slot (dibuat / dibuat ulang / mockup sudah ada / gambar asli, dipertahankan / GAGAL), kode keluar 1 bila ada yang gagal atau `--node` tidak dikenal.
- `SystemResetService::generateMockups()` kini memanggil `generateMissing()`. Ringkasan reset: "Gambar mockup: N gambar contoh dibuat, M sudah ada, K gambar asli dipertahankan". Kegagalan masuk daftar peringatan reset. Konstruktor menerima folder mockup (argumen ketiga) agar uji tidak menulis ke folder unggahan sungguhan.
- `MediaAssetModel::placeholderCount()`: jumlah gambar contoh aktif. **Untuk Dok 3 §8** (peringatan dasbor "Masih ada N gambar contoh" dan centang konfirmasi status studi). Daftar yang masih dipakai konten tersedia di `ContentVerifier::placeholdersInUse()`.
- Tidak ada migration baru (`is_placeholder`, `label`, `description` sudah ada sejak `004600`).

### Keputusan dan penyimpangan

1. **Status slot.** "Gambar asli" = baris `is_placeholder = 0` yang **berkasnya ada**, aktif atau dinonaktifkan admin. Keduanya tidak pernah ditimpa, juga dengan `--paksa`. Slot impor (berkas belum ada) dan mockup yang berkasnya hilang dianggap kosong dan dibuat. Mockup utuh hanya dibuat ulang dengan `--paksa`. Alasannya, §10.2 menulis "berkas aktif", tetapi gambar asli yang sedang dinonaktifkan admin pun tidak boleh hilang (MediaStore menghapus berkas lama berekstensi lain saat mendaftarkan berkas baru).
2. **`MediaStore` menandai setiap simpanan sebagai gambar asli** (`is_placeholder = 0`, kredit mockup dibuang). Sebelumnya unggahan admin yang menggantikan mockup tetap tercatat `is_placeholder = 1`, sehingga `--siap-penelitian` terus gagal. `MockupGenerator` mendaftarkan berkasnya lewat `registerFile()` lalu menandai ulang baris itu (`is_placeholder = 1`, `is_active = 1`, `credit`, `width_px`/`height_px` dari ukuran slot karena `getimagesize()` tidak membaca SVG).
3. **Keterangan gambar pernyataan tanpa catatan dalam kurung.** `media.note` `tmg-3-b03` berbunyi "arca Buddha duduk dari batu (sengaja tidak sesuai bacaan)"; bila tertulis di mockup, kunci pernyataannya bocor ke siswa. Bagian dalam kurung dibuang dari baris keterangan mockup (deskripsi di `media_assets` tetap utuh untuk admin).
4. **Adegan:** alas digambar di bawah **setiap posisi unik** dari semua soal aktif node itu (tmg-4: 8, mgl-4: 10, wnb-4: 12). Kotak benda dihitung seperti engine: `x, y, w` % dengan tinggi `w × 16/9` % (persegi dalam piksel), jadi permukaan alas ada di `y% × 720 + w% × 1280`. Spanduk memakai judul variasi pertama yang terisi (`config_json.variants.*.title_id`).
5. **Benda Cari Objek:** ikon dari `crc32(asset_key) % 6` (segitiga, persegi, belah ketupat, segi enam, bintang, cincin) dalam warna aksen wilayah. Target dan pengecoh memakai kerangka SVG yang sama; `MockupGeneratorTest` membandingkan kerangkanya tanpa ikon dan teks. Pita "GAMBAR CONTOH" pada benda berupa label kecil di tepi bawah ubin, bukan pita diagonal, agar latar tetap transparan dan bentuknya bulat.
6. **Susun Gambar:** teks hanya label, pita, dan kunci aset (tidak ada angka urutan). Kunci aset di tepi bawah memang mengandung angka (`tmg-1-01`), tetapi tidak tersusun sebagai nomor keping.
7. **`--node=tmg-4`:** parser CLI CodeIgniter tidak memecah `=`, jadi bentuk ini terbaca sebagai opsi bernama `node=tmg-4`. `Mockups::option()` menerima `--node tmg-4` maupun `--node=tmg-4`. **Masalah yang sama ada di `gelita:reset --bank=JALUR`**: `bankPath()` hanya membaca `--bank JALUR`, sehingga `--bank=JALUR` diam-diam memakai workbook bawaan. Ini belum diperbaiki karena di luar §10.
8. `DatabaseSeeder` (development) tidak membuat mockup; pesannya sudah menyarankan `php spark gelita:mockups`. `gelita:reset` selalu membuatnya kecuali `--tanpa-mockup`.
9. Fixture `tests/_support/Database/NarrationTables.php` tertinggal dari `004600` (tanpa `label`, `description`, `is_placeholder`). Kolom itu ditambahkan karena `MediaStore` kini selalu menulis `is_placeholder`.

### Pengujian yang dijalankan

- **Otomatis:** 453 test hijau.
  - `MockupGeneratorTest` (baru, 7 test, di atas dunia kelas 5 `GradeFiveWorld`):
    - 45 slot §10.4 per jenis, `--node`;
    - SVG sah lewat DOMDocument, `width`/`height`/`viewBox` = ukuran wajib slot di `Config\Gelita::$assetSizes`, huruf, pita, label;
    - baris `is_placeholder = 1`, `is_active = 1`, kredit, MIME SVG;
    - alas adegan per posisi; tanpa angka urutan; benda transparan dan target = pengecoh; palet wilayah; keterangan tanpa catatan dalam kurung;
    - gambar asli lewat `MediaStore::storeCopy()` → `is_placeholder = 0`, mockup lama terhapus, lalu tidak ditimpa `run()` maupun `run(paksa)`, juga saat dinonaktifkan;
    - `--dry-run` tidak menulis; berkas mockup yang hilang dibuat ulang;
    - `--siap-penelitian` galat (45 gambar), verifikasi biasa hanya peringatan;
    - command (`--dry-run`, `--node tmg-4`, `--node=wnb-4`, kode tidak dikenal).
  - `SystemResetTest::testResetFillsTheFortyFiveSlotsWithMockups` (baru): reset mengisi 45 slot. Reset kedua sesudah satu slot diganti gambar asli → 0 dibuat, 44 sudah ada, 1 dipertahankan.
- **MariaDB 10.11.14** (lokal di container, database baru: `migrate --all`, `db:seed DatabaseSeeder` development):
  - `gelita:mockups --dry-run` → rencana 45 dibuat, folder belum dibuat, tidak ada baris yang berubah.
  - `gelita:mockups` → 45 berkas di `public/assets/uploads/mockup/`, 45 baris `is_placeholder = 1`, aktif, berkredit.
  - `--paksa` → 45 dibuat ulang.
  - `challenge.tmg-4.scene` diganti PNG 1280×720 lewat `MediaStore::storeCopy()` (jalur unggahan panel) → `is_placeholder = 0`, kredit NULL, `mockup/challenge.tmg-4.scene.svg` terhapus. `gelita:mockups --paksa` → 44 dibuat ulang, 1 gambar asli dipertahankan, sha256 berkas dan baris tidak berubah. `--node=tmg-4 --dry-run` → 8 mockup sudah ada + 1 gambar asli.
  - `gelita:content:verify --siap-penelitian` → kode keluar 1 ("Belum siap untuk pengambilan data penelitian"); tanpa opsi → kode 0.
  - `gelita:reset --ya` → "0 gambar contoh dibuat, 44 sudah ada, 1 gambar asli dipertahankan". Folder mockup dihapus lalu reset lagi → "44 gambar contoh dibuat, 1 gambar asli dipertahankan", baris audit `media_mockups`.
  - Rupa SVG diperiksa dengan Chromium headless: Susun Gambar ketiga wilayah, adegan tmg-4/wnb-4, benda, dan gambar pernyataan.
- Belum dijalankan: permainan ujung ke ujung dengan mockup (§13.2 butir 2). Ini dilakukan bersama uji manual §5.3 setelah `dok2-klien-kesempatan` digabung.

---

## Dok 2 — klien dan teks UI

Dikerjakan di branch `dok2-klien-kesempatan` (dari `main`), sesuai `02_TANTANGAN_DAN_BANK_SOAL.md` §5.3 dan §12. Server (§5.2) sudah ada di `main` dan tidak diubah. Suite: **454 test hijau** setelah rebase ke `main` yang berisi §10 (`vendor/bin/phpunit --no-coverage`; 446 sebelum rebase).

### Jawaban butir Dok 1 yang ditandai *(cek Dok 2)*

- **No. 27 (soal benar terkunci):** sesuai Dok 2 §5.1–§5.2. Soal yang sudah benar tidak dinilai ulang dan tidak menulis `item_tries` baru. Klien kini juga menguncinya setelah muat ulang (`items[].state.locked`).
- **No. 28 (batas belum ditegakkan):** selesai. Server menegakkannya (`ChallengeService::submitCheck`/`submitAnswer`, `TryLimitTest`). Klien mengikuti `completed`/`redirect` dari `/check` dan `/responses`, dan tombol "Selesaikan dengan jawaban ini" sudah dihapus.
- **No. 29 (nilai ketiga `completion_reason`?):** tidak perlu. Tanpa tombol itu, pengerjaan hanya tertutup karena semua benar (`solved`) atau kesempatan habis (`tries_exhausted`: cek ke-3 pada engine batch, atau semua petunjuk Cari Objek tertutup dengan sedikitnya satu dilewati). `/complete` menolak pengerjaan yang belum memenuhi salah satunya (`TRIES_REMAINING`). Pengerjaan yang ditinggalkan berstatus `abandoned` tanpa `completion_reason`.

### Keputusan dan penyimpangan

**§5.3 Klien**

1. `FINISH_OFFER_AFTER` dan tombol "Selesaikan dengan jawaban ini" dihapus. `ctx.complete()` engine diganti `ctx.finish(result)`, yang pindah ke `result.redirect` (lentera diperbarui dari `result.shards`). `POST /complete` hanya dipanggil sebagai cadangan bila respons `completed` datang tanpa `redirect`.
2. **Penanda kesempatan** dirender server lalu diperbarui `challenge.js` (`ctx.setTries`):
   - engine batch: chip `#tries` di kepala tantangan ("Kesempatan ●●○");
   - Cari Objek: `#clue-tries` di bilah petunjuk, per petunjuk.

   Nilainya selalu dari server: saat dimuat `max_tries − tries_used` (`clues[i].state.tries` untuk Cari Objek), lalu `tries_left` (Cari Objek: `try_no` dari respons). `aria-label` berbunyi "Kesempatan: 2 dari 3" / "Kesempatan untuk petunjuk ini: 2".
3. **Modal percobaan salah:** judul per engine tetap ("Belum utuh" untuk Susun Gambar, "Belum semuanya tepat" untuk Isi Rumpang dan Benar atau Salah). Paragraf kedua berisi `triesLeft`/`triesLast`, dengan satu tombol **Perbaiki**. Cari Objek memakai kalimat yang sama untuk sisa kesempatan petunjuk itu, di bawah umpan balik pengecoh.
4. **Kesempatan habis** (`completed` + `tries_exhausted`): satu modal "Kesempatanmu sudah habis", tanpa tombol tutup, dengan satu tombol **Lanjut** ke `redirect`.
   - Isi Rumpang dan Benar atau Salah: semua isian dikunci, tanda merah tetap tampil, kunci tidak ditampilkan.
   - Susun Gambar: keping disusun ke posisi utuh dengan FLIP 600 ms (`reduced-motion`: tanpa animasi). Sesudah itu papan diberi `is-solved`, keterangan gambar tampil, lalu modal berisi `triesExhaustedPuzzle` + teks keterangan.
5. **Cari Objek:**
   - `clue_closed` → modal "Petunjuk ini dilewati" (tombol Lanjut) → petunjuk terbuka berikutnya. Petunjuk itu diberi ✗ di daftar dan tidak bisa dilompati lagi; bendanya tidak ditandai.
   - Bila ketukan itu sekaligus menutup pengerjaan, **hanya** modal "Kesempatanmu sudah habis" yang tampil, tidak dua modal berturut-turut.
   - Pengerjaan yang tertutup `tries_exhausted` setelah petunjuk terakhir justru *ditemukan* (ada petunjuk lain yang dilewati) juga memakai modal "Kesempatanmu sudah habis", tanpa konfeti. Konfeti dan "Semua objek ketemu!" hanya untuk `solved`.
6. **Galat aturan dari server** (mis. tab lain sudah memakai kesempatan terakhir):
   - `TRIES_EXHAUSTED` → pindah ke `redirect` di payload galat (atau muat ulang);
   - `CLUE_CLOSED` → muat ulang, sehingga tampilan mengikuti payload.
7. **Muat ulang di tengah tantangan:**
   - Isi Rumpang: rumpang `state.locked` diisi lagi dengan jawaban siswa sendiri, memakai chip pertama yang belum terpakai dengan teks sama.
   - Benar atau Salah: pernyataan `state.locked` dipulihkan dengan pilihan siswa sendiri. Keduanya bertanda benar dan terkunci.
   - Cari Objek: memulihkan `found`/`closed` per petunjuk dan `objects[].found`, lalu mulai dari petunjuk terbuka pertama.

   Jawaban salah memang tidak dikembalikan server (§3.3), jadi kotak yang salah kembali kosong. Angka ✓ di bar dimulai dari jumlah soal terkunci. ✗ diperkirakan dari sisanya bila `tries_used > 0`; untuk Cari Objek ✗ dimulai dari 0.
8. Judul variasi (`variant.title`) tampil sebagai `h2.rumpang-title` di atas bacaan Isi Rumpang. Judul itu disembunyikan bila sama dengan judul node, yaitu variasi tanpa judul sendiri (`variantTitle()` jatuh ke judul node).

**§12 Teks UI**

9. `accuracyFirst` tidak ada lagi di `main`: Dok 1 sudah menggantinya menjadi `firstPass` dengan teks "Berhasil pada Percobaan Pertama" / "Success on First Try". Nama `firstPass` dipertahankan.
10. Kunci baru §12.1 dipakai sebagai berikut:
    - `firstPassCount` diganti nama menjadi `firstTryCount` ("{0} dari {1} soal" / "{0} of {1} questions").
    - `yes`/`notYet` (hanya dipakai `first_pass_text()`) diganti `firstTryYes`/`firstTryNo` lalu dihapus.
    - `triesLabel` + `triesOf` dipakai untuk penanda kesempatan.
    - `triesUsed` / `wrongTaps` jadi petak statistik keempat di layar Selesai. Engine batch menampilkan "{check_count} dari {max_tries}"; Cari Objek menampilkan jumlah `wrong_click_count`.
    - `completionSolved` / `completionExhausted` jadi lencana alasan selesai di layar Selesai.
    - `readingLabel` menjadi judul cadangan bacaan Benar atau Salah.
    - `statementsLabel` menjadi judul di atas daftar pernyataan.
11. Dari daftar "dihapus" §12.1, yang masih tersisa dan kini dihapus adalah `engine_pilihan`, `variant_urutkan_informasi`, dan `questionOf`. Kunci lain sudah dihapus Dok 1. Dari §12.2, yang masih ada hanya `finishAnyway`.
12. **Balai Refleksi:**
    - `perRegion` "Bintang per wilayah" kini benar-benar menampilkan bintang. Isinya jumlah bintang pengerjaan terbaik per tantangan, dari 3 × jumlah tantangan wilayah (mis. "10 dari 12"); sebelumnya persentase.
    - `mostMissedText` memakai hitungan "{1} dari {2} soal", bukan persen.
    - `perEngine` tetap persentase.
13. Selain teks tabel §12.2, judul blok komentar "Puzzle & urutan" diganti "Puzzle". `cardsEmpty*`/`cardsOk*` masih menyebut "kartu" karena tidak ada di tabel. `huntDecoyTitle` kini sama dengan `huntOtherTitle` ("Bukan yang ini"), sesuai tabel.
14. **§12.3 `Admin.php`:** label event diubah persis tabel. `screen_viewed`, `overlay_opened`, dan `challenge_tries_exhausted` sudah ada dengan teks yang sama. Judul kolom "Menekan Cek jawabanku" di `admin/sessions/show.php` dan `ExportLabels` bukan label event, jadi dibiarkan (rancangan ulang panel di Dok 3). Panel admin hanya berbahasa Indonesia, jadi tidak ada `en/Admin.php`.
15. `LanguageFilesTest::testDocTwoTextKeys` (baru) memastikan:
    - kunci yang dihapus §12.1/§12.2 tidak ada di `id`/`en`;
    - kunci itu tidak dipakai kode (`'Game.x'` di `app/`, `t('x')` di `public/assets/js`);
    - teks kunci utama sama persis dengan tabel.
16. `docs/06_JAVASCRIPT.md` bagian "Keputusan perilaku" diperbarui seperlunya: aturan 3 kesempatan menggantikan "Selesaikan dengan jawaban ini", dan butir engine Pilihan dihapus. Penulisan ulang menyeluruh tetap di Dok 3 §11.

### Pengujian yang dijalankan

- **Otomatis:** `composer install`, lalu `vendor/bin/phpunit --no-coverage` → 454 test hijau (446 sebelum rebase). `node --check` untuk `challenge.js` dan keempat engine.
- **Peramban** (`php spark serve` + Chromium Playwright 1280 × 800). Database MariaDB 10.11 lokal di container: `migrate --all`, lalu `db:seed DatabaseSeeder`, yang mengimpor workbook kelas 5. Gambar mockup belum ada (§10), jadi gambar tampil sebagai "Gambar belum tersedia". Kunci jawaban dan token objek Cari Objek dibaca skrip uji dari database. Skrip uji tidak di-commit.
  - **Salah 3 kali (ID), Temanggung:**
    - Susun Gambar: 3 × cek tanpa menukar → penanda 3→2→1→0, modal "tinggal 2 lagi" / "Tinggal satu kesempatan lagi", keping tersusun 0..8, keterangan tampil, modal habis, lalu `/selesai`.
    - Isi Rumpang: 1 benar 2 salah → muat ulang (rumpang benar terkunci, penanda 2) → 2 × cek salah → modal habis, 2 kotak merah.
    - Benar atau Salah: semua kebalikan kunci × 3.
    - Cari Objek: petunjuk 1 salah 3× → "Petunjuk ini dilewati" + ✗ di daftar; petunjuk 2 salah 1× → muat ulang (petunjuk 2 aktif, penanda 2, petunjuk 1 tetap ✗) → sisanya benar → modal habis.
    - Database: keempat pengerjaan `completed`/`tries_exhausted`/★. Ada 3 event `challenge_tries_exhausted` dan 1 `clue_failed`. Tidak ada request ke `/complete`.
  - **Benar pada percobaan pertama (EN):** keempat tantangan Temanggung → konfeti dan modal sukses EN ("Chances: 3 of 3", "Check my picture", "Statement 1"), lalu `/selesai` dengan ★★★, `solved`, "All correct", "Wrong taps 0". Membuka ulang tantangan yang sudah selesai memulai pengerjaan baru dengan 3 kesempatan.
  - **Ke-12 tantangan sampai Balai Refleksi (ID):** satu tantangan (Magelang Benar atau Salah) salah 3 kali. Hasilnya "Bintang per wilayah" 12/10/12 dari 12, dan "Paling sering terlewat … 0 dari 5 soal".
  - Setelah rebase ke `main` yang sudah berisi §10: `gelita:mockups` (45 dibuat), lalu skenario salah 3 kali dan benar pada percobaan pertama diulang dengan gambar mockup, dengan hasil sama. Gambar utuh Susun Gambar tampil di balik modal habis. Ini sekaligus menjawab "Belum dijalankan" di bagian mockup untuk alur §5.3. Butir §13.2 lain (mengganti mockup dengan gambar asli di panel, halaman Narasi) belum.
  - Tidak ada galat JavaScript atau konsol di semua skenario.

---

## Dok 2 — naskah cerita dan rekaman

Dikerjakan di branch `dok2-mockup` (dibuat ulang dari `main` sesudah PR #36), sesuai `02_TANTANGAN_DAN_BANK_SOAL.md` §11. Suite: **457 test hijau** (2 dilewati, sama dengan `main`); 458 sesudah `main` berisi §5.3/§12 digabung ke branch.

### Yang berubah

- **§11.1 naskah.** Sebelas baris di `docs/naskah-cerita.md` diubah persis seperti tabel §11.1, lalu disalin ke `app/Database/Seeds/data/story.php`. *Benang merah* "15 Serpihan Cahaya" → "12 Serpihan Cahaya", dan keterangan bagian 5 "tantangan kelima" → "tantangan keempat". Baris `kenal-*` tidak disentuh.
- **`story-legacy.php` kini berkelompok**: `seeder` (18 baris lama, tidak berubah) dan `lima_belas` (teks 11 baris sebelum perombakan). `StorySync::legacyVersions()` menggabungkan semua kelompok per posisi, sehingga satu posisi boleh punya beberapa versi lama. Alasannya, tiga dari sebelas posisi (`_|intro|4`, `temanggung|level_done|2`, `_|ending|1`) sudah punya entri seeder, padahal format lama hanya menampung satu teks per posisi.
- **`gelita:story:update --lepas-audio-beda`** (`StorySync::releaseStaleAudio()`): sesudah sinkronisasi teks, tautan `dialogues.audio_{id,en}_asset_id` dilepas bila `audio_assets.transcript` rekamannya berbeda dari teks baris (perbedaan CRLF/spasi di ujung diabaikan). Rekaman dan baris `audio_assets` tidak dihapus. Daftar baris yang dilepas ikut dicatat di metadata audit `content_update`. Hanya berlaku untuk `dialogues`; petunjuk Cari Objek tanpa rekaman memang sudah tampil sebagai teks.
- **§11.2 rekaman dibangkitkan di sesi ini** (model TTS bisa diunduh, sha256 cocok):
  - `narrate.py --n 3` untuk 11 baris naskah + 12 petunjuk (`petunjuk-{tmg,mgl,wnb}-4-01…04`), ID dan EN. `tuntas-magelang-03` dan `petunjuk-tmg-4-04` EN diulang dengan 6 kandidat.
  - `publish.py` menulis 23 MP3 per bahasa dan `produksi.json`.
  - `petunjuk-tmg-4-05…08` (ID & EN) dihapus, beserta entrinya di `produksi.json` dan `qa-narasi.json`.
  - Jadi **100 rekaman per bahasa**: 88 naskah + 12 petunjuk.
- **`qa-narasi.json`** (200 baris): 46 entri baru atau diganti dari `out/narasi/*/report.json`, dengan LUFS diukur dari WAV terpilih.
  - Angka ke-46 baris itu berasal dari kandidat terpilih sebelum MP3, bukan transkripsi ulang rekaman final seperti rekaman lainnya.
  - Ringkasan di `docs/audio.md`: ID median CER 6,0%, 85/100 ≤ 10%; EN median 3,7%, 95/100 ≤ 10%.
  - **`tuntas-magelang-03` EN** (CER 13%): Whisper mendengar *Eight shards* sebagai "HRs" di keenam kandidat. Bunyinya hampir sama, jadi kemungkinan besar salah dengar ASR. Baris ini dicantumkan di daftar "dengarkan lebih dulu".
- **Hitungan rekaman di dokumentasi** (README, `docs/audio.md`, `docs/rencana-katalog-aset.md`): 96 → 100 per bahasa, 192 → 200 baris QA. Arahan suara dan prompt ilustrasi di `docs/rencana-katalog-aset.md` yang menyebut 15/5 serpihan ikut diubah. Bagian riwayat perubahan di README tidak diubah.
- `NarrationCatalog`/`NarrationImporter`/`export_lines.php` sudah membuat kelompok petunjuk untuk setiap node `cari` sebelum sesi ini; tidak ada perubahan kode di sana.

### Uji

- **`NarrationKelas5Test`** (baru, di atas `GradeFiveWorld` + `StorySync`):
  - `NarrationImporter::lines()` = 100 baris, dengan 12 kode petunjuk urut `tmg-4`, `mgl-4`, `wnb-4`;
  - teks petunjuk = petunjuk target bank soal;
  - berkas `public/assets/audio/narasi/{id,en}/*.mp3` dan `produksi.json` tepat 100 kode yang sama, sha256 cocok.
- **`StoryUpdateCommandTest`**: naskah 15 serpihan → 11 diperbarui, 0 dilewati. `--lepas-audio-beda`: tanpa opsi tidak menyentuh audio; `--dry-run` hanya melaporkan; transkrip lama dilepas, beda CRLF tidak; rekaman tetap ada; tercatat di audit. Kolom audio dibedakan dari `media_asset_id` agar uji gagal bila rujukannya salah.
- **`StoryDataTest`**: kelompok legacy, 18 + 11 entri, versi lama ≠ teks sekarang, versi `lima_belas` hanya berbeda pada kata bilangan.
- **MariaDB lokal** (database development berisi naskah 15 serpihan):
  - `gelita:story:update --dry-run` lalu sungguhan: 11 diperbarui, 77 sudah sesuai.
  - `gelita:narration:import`: 100 baru per bahasa, 0 baris tanpa rekaman.
  - Transkrip `intro-04` ID diubah ke versi lama, lalu `--lepas-audio-beda` → tepat `intro #4 (id)` dilepas.
  - **Bug yang tertangkap di sini:** versi pertama mencari transkrip lewat `audio_assets.media_asset_id`, padahal `dialogues.audio_*_asset_id` adalah FK ke `audio_assets.id`, sehingga 118 tautan terlepas. Diperbaiki dan ditambah uji.
- **Belum dikerjakan:** mendengarkan ke-46 rekaman baru satu per satu. Dilakukan saat persetujuan di **Konten → Narasi**.

---

## Dok 2 — server, bank soal, dan uji manual §13

Bagian ini melengkapi catatan Dok 2. Pekerjaan §2–§9 dikerjakan di branch `perombakan-kelas5` (commit `853fd43`, `6e6be14`, `671f12e`; digabung lewat `8d13723`) tanpa bagian catatan sendiri. Ringkasan di bawah disusun dari commit dan kodenya. Uji manual §13.2 dijalankan pada `main` `7bd3df4`, sebelum perombakan "instalasi baru satu langkah" di branch `dok2-mockup`.

### Ringkasan §2–§9

- **§2 `DifficultyRules`:** jumlah rumpang dan pengecoh Isi Rumpang tepat (3/5/7 dan 0/2/2). Aturan Benar atau Salah (paragraf, pernyataan, Bloom, keterampilan) dan Cari Objek (4 dicari + 4/6/8 pengecoh, Bloom petunjuk) ditambahkan, bersama `trueFalse()`, `hunt()`, `skillLabel()`, dan `bloomRange()` per jenis soal.
- **§3–§4 variasi dan pengacakan** (`ChallengeService::openNode`):
  - satu variasi acak per pengerjaan; variasi yang sudah pernah didapat siswa di semua sesinya dihindari (`variantsServed`); setelah semuanya pernah keluar, pilihan kembali dari semua variasi;
  - resume tidak mengganti variasi;
  - bank kata diacak dengan benih `bank|pengerjaan|k` sampai lolos aturan §4 (Temanggung: derangement);
  - urutan petunjuk Cari Objek diacak per pengerjaan; keping Susun Gambar memakai `seeded_derangement()`.
- **§5.2 server:** `submitCheck` dan `submitAnswer` menegakkan 3 kesempatan dan menutup pengerjaan dalam transaction yang sama (detail di bagian "klien dan teks UI", butir Dok 1 no. 27–29).
- **§7 format dan pemeriksaan:**
  - `BankRules` menjadi satu sumber pemeriksaan §7.5 untuk `build-workbook.php`, importer, `ContentVerifier`, dan uji;
  - `BankSource` meratakan berkas data, `BankState` membaca bank tersimpan dalam bentuk yang sama;
  - importer menolak jenis soal lama;
  - `gelita:content:verify --siap-penelitian` menjadikan gambar contoh sebagai galat.
- **§8–§9 bank soal final:**
  - `docs/bank-soal/data/*.php` berisi 12 tantangan, 126 soal (108 dinilai + 18 pengecoh), 30 variasi, 9 bacaan, 36 petunjuk, 12 kata pengecoh, dan 45 slot gambar;
  - `gelita-bank-soal-kelas5.xlsx` di-commit dan workbook produksi lama dihapus;
  - Pustaka dipindah byte demi byte ke `docs/pustaka/`;
  - `SampleItemSeeder` dihapus, dan `DatabaseSeeder` (development) mengimpor workbook kelas 5 lewat `BankKelas5Seeder`.

### Uji manual §13.2

Lingkungan: MariaDB 10.11 lokal di container, `php spark serve`, dan Chromium Playwright. Tidak ada yang dijalankan ke server produksi.

1. **`php docs/bank-soal/build-workbook.php`** (keluaran ke folder sementara) → 0 galat, 0 peringatan Bloom; 12 tantangan, 30 variasi, 9 bacaan, 126 soal, 36 petunjuk, 12 kata pengecoh, 45 slot. Isi workbook hasilnya identik dengan `gelita-bank-soal-kelas5.xlsx` yang di-commit (beda hanya di `docProps`).
2. **Reset sampai Balai Refleksi:**
   - `gelita:reset --ya --paksa` (lokal) → TRUNCATE, 126 soal, 45 mockup, kode siswa berikutnya `GLT-000001`.
   - Siswa baru memainkan ke-12 tantangan sampai Balai Refleksi tanpa galat JavaScript ("Bintang per wilayah" 12/12/12).
   - Mengulang tantangan memberi variasi yang belum pernah didapat, sampai ketiganya keluar: `tmg-1` c → a → b, `tmg-2` c → b → a, `tmg-3` b → a → c. Sesudah itu pilihan bebas dari ketiganya. `tmg-4` selalu `a`.
   - Skenario salah 3 kali, muat ulang di tengah, dan bahasa EN dijalankan sebelumnya pada kode tantangan yang sama (lihat bagian "klien dan teks UI"). Kode tantangan tidak berubah sejak itu.
3. **Mockup diganti gambar asli lewat panel Media:**
   - Admin mengunggah PNG 1200 × 1200 ke slot `challenge.item.tmg-1-04` di `/admin/media` → "Berkas … sudah tersimpan". Barisnya menjadi PNG, `is_placeholder = 0`, kredit mockup dikosongkan.
   - Susun Gambar siswa yang sedang memakai slot itu menampilkan PNG setelah dimuat ulang.
   - `gelita:mockups --paksa` → 44 dibuat ulang, "1 gambar asli dipertahankan"; sha256 berkas dan baris database tidak berubah.
4. **Narasi:**
   - Halaman **Konten → Narasi** menampilkan 100 baris per bahasa (88 naskah + 12 petunjuk).
   - Rekaman pengganti `intro-05` yang diunggah lewat **Unggah narasi** masuk sebagai draft `own_recording`.
   - **Setujui semua narasi draft** ID dan EN → 100 rekaman disetujui per bahasa, dan layar intro memakai rekaman baru itu.

### Temuan

- **Tautan audio petunjuk Cari Objek hilang setelah `gelita:reset`.** Reset membangun ulang soal dari workbook, jadi `challenge_items.audio_prompt_id`/`audio_prompt_en_id` ke-12 petunjuk kosong (0/12). Akibatnya halaman Narasi menunjukkan "88/100 disetujui, 12 belum ada" meski rekamannya ada dan disetujui, dan tombol ▶ petunjuk tidak tampil. `gelita:narration:import` sesudah reset memasang lagi ke-12 tautan, termasuk saat semua berkas "sama", dan statusnya tetap disetujui. **Setiap `gelita:reset` (juga di development) harus diikuti `gelita:narration:import`**; prosedur rilis Dok 3 §13 sudah memuat urutan ini. *Diperbaiki di bagian "Pembersihan" butir 11: reset kini memasang lagi tautan itu sendiri.*
- **Saran `ContentVerifier` keliru untuk gambar wilayah.** Peringatan "Gambar peta/latar/lencana wilayah belum diunggah … Jalankan php spark gelita:mockups untuk memasang gambar contoh" (`ContentVerifier::mediaFindings()`) tetap muncul sesudah `gelita:mockups`, karena generator hanya membuat 45 slot gambar tantangan (§10.4). Kalimat sarannya perlu dibedakan untuk media non-tantangan, mis. "Unggah di menu Gambar & suara". Belum diubah karena `ContentVerifier` sedang dirombak di branch `dok2-mockup`. *Diperbaiki di bagian "Pembersihan" butir 12.*

### Kriteria selesai §13.3

| Kriteria | Keadaan |
|---|---|
| 12 node, 126 soal, 30 variasi, isi §9 (ID & EN) | ✓ `BankKelas5DataTest`, workbook dibangun ulang identik |
| Variasi acak dan pengacakan bank kata §3–§4 | ✓ `VariantSelectionTest`, `WordBankShuffleTest`, uji manual butir 2 |
| Aturan 3 kesempatan di keempat tantangan; `item_tries` lengkap | ✓ `TryLimitTest`, uji peramban |
| Engine dan jenis soal lama terhapus | ✓ hanya 4 view dan 4 engine JS; sisa rujukan lama sedang dibersihkan di branch `dok2-mockup` |
| 45 mockup; penjaga "siap penelitian" | ✓ bagian "gambar mockup", uji manual butir 3 |
| 11 baris naskah dan rekaman diperbarui | ✓ bagian "naskah cerita dan rekaman" |
| README bank soal, workbook kelas 5, workbook lama dihapus, Pustaka di `docs/pustaka/` | ✓ commit `671f12e` |

---

## Pembersihan — instalasi baru tanpa kompatibilitas lama

Dikerjakan di branch `dok2-mockup` (dari `main` sesudah PR #38), atas permintaan pemilik (6 Oktober 2026); digabung lewat PR #41. GELITA belum pernah berjalan di server mana pun, jadi semua jalur pembaruan server lama, data lama, seeder lama, dan workbook lama dibuang. Admin hanya melakukan **instalasi baru**.

### Yang berubah

1. **Opsi `--nama=nilai`.** Parser CLI CodeIgniter membaca `--bank=x` sebagai opsi bernama `bank=x` tanpa nilai, sehingga `gelita:reset --bank=JALUR` diam-diam memakai workbook bawaan. Bug yang sama ada di `gelita:bank:import --staff=…`.
   - Pembacaan opsi kini satu trait, `App\Commands\Concerns\CliOptions` (`flag()`, `option()`), dipakai semua command.
   - `gelita:reset` menampilkan workbook yang akan diimpor, dan berhenti sebelum mengubah apa pun bila jalur `--bank` tidak ada.
2. **Migration digabung: 46 → 29 berkas, satu tabel per berkas** (`000100_CreateSchools` … `002900_CreateCiSessions`), urut ketergantungan FK.
   - Isinya dibangkitkan dari `SHOW CREATE TABLE` hasil rantai lama di MariaDB 10.11, lalu dibandingkan lewat `information_schema` (kolom, tipe, null, bawaan, collation, index, FK, engine). Hasilnya identik, kecuali dua hal:
     - empat kolom slot `library_pages.image_a/image_b/video/poster_media_id` (sudah digantikan `library_media`) sengaja dibuang;
     - nama index FK `schools.merged_into_id` kini nama bawaan Forge (`schools_merged_into_id_foreign`).
   - Rollback penuh lalu `migrate` lagi menghasilkan skema yang sama.
   - Helper upgrade `GelitaMigration` (`dropForeignKeyOn`, `addColumnsIfMissing`, …) dihapus. `SystemResetService::REQUIRED_MIGRATION` = `2026-01-01-002900`. `ResetTables` tanpa kolom slot Pustaka.
   - **Akibat:** database pengembangan yang dibuat dari rantai lama harus dibuat ulang (kosongkan database, `migrate`, `db:seed`), karena isi tabel `migrations`-nya tidak cocok lagi.
3. **Instalasi satu langkah.** `DatabaseSeeder` kini mengisi semuanya di semua lingkungan. Seeder baru `KontenKelas5Seeder` mengimpor Pustaka (`docs/pustaka/gelita-pustaka.xlsx`) dan bank soal kelas 5, lalu membuat 45 gambar contoh.
   - `BankKelas5Seeder` (hanya development) dihapus.
   - Tiga halaman Pustaka sementara `ContentSeeder::seedLibrary()` dihapus.
   - `ContentSeeder::seedDialogues()` publik dan dipakai uji sebagai pemuat cerita.
4. **Pembaruan cerita server lama dihapus:** `gelita:story:update`, `StorySync`, `data/story-legacy.php`, `--lepas-audio-beda`, `StoryUpdateCommandTest`. Cerita dipasang `ContentSeeder` saat instalasi; sesudahnya disunting di panel.
5. **`gelita:reset`:**
   - `--bersihkan-media-tantangan` (membuang kunci media lama) dihapus.
   - Jatuh ke `DELETE` bila akun tidak punya hak TRUNCATE juga dihapus: akun seperti itu kini ditolak dengan pesan jelas sebelum ada tabel yang dikosongkan.
   - Pemeriksaan `tableExists` untuk tabel yang pasti ada dihapus.
   - Fungsi reset tetap: mengosongkan data uji coba sebelum penelitian, membangun ulang 12 tantangan, mengimpor ulang bank soal, dan membuat gambar contoh yang hilang.
6. **Jalur kode untuk data lama:**
   - `PuzzleCut::resolve($config, $difficulty)` hanya membaca isian soal atau aturan wilayah. Cadangan ukuran tantangan lama dan akar panjang kunci dihapus; `ChallengeItem::puzzleLayout()` tanpa `$nodeGrid`.
   - Importer, verifier, dan editor: penanganan sheet `options`/`pieces`/`sources` dan kunci `config_json` node lama dihapus. `RETIRED_TYPE` diganti `UNKNOWN_TYPE` ("Jenis soal tidak dikenal.").
   - `ChallengeAttempt::maxTries()` tanpa cadangan `max_tries = 0`.
   - `AnswerText` tanpa `option_key`/`reason_text`.
   - Rute `/persetujuan` (pengalihan halaman persetujuan lama) dihapus.
   - `class_label()` hanya kelas SD. Kunci bahasa `classSmp`, `classOther`, `challengeLead_pilihan`, `challengeLead_urutkan_informasi` dihapus.
   - Teks dan uji Pustaka terkunci mengikuti 4 tantangan.
7. **Bug `gelita:bank:import --dry-run`:** ringkasan masih membaca `options` yang sudah tidak ada, sehingga perintahnya berhenti dengan `Undefined array key "options"`. Ringkasan kini memuat variasi. Uji baru `BankImportCommandTest`.
8. **Dokumentasi:**
   - README dan `docs/01`–`08` ditulis ulang untuk keadaan sekarang: tanpa catatan pembaruan, tanpa desain lama, dan dengan alur instalasi baru di `08_DEPLOYMENT.md`.
   - `docs/naskah-cerita.md`, `docs/audio.md`, `docs/bank-soal/README.md`, dan `docs/pustaka/` dirapikan.
   - Workbook Pustaka dibangun ulang karena teks subjudulnya berubah. Workbook bank soal dibangun ulang karena keterangan adegan `tmg-4` kini menyebut posisi rak, bukan "tata letak lama"; isi soal tidak berubah.
9. **Berkas lama lain yang dihapus:**
   - folder `system/` di root: salinan CodeIgniter 4.7.4 yang identik dengan `vendor/codeigniter4/framework/system` dan tidak pernah dipakai (`Config\Paths::$systemDirectory` menunjuk ke vendor);
   - `docs/literatur.md`: bank soal lama 15 node (pilihan ganda, urutkan, sumber, rumpang ketik), sudah digantikan `docs/bank-soal/` dan tidak dirujuk;
   - `core/storage.js` tidak lagi membuang kunci antrean lama `gelita.eventQueue`.
10. **Sengaja dipertahankan** (bukan kompatibilitas lama):
   - `deploy.sh`/`deploy.ps1`, untuk pembaruan kode berikutnya;
   - `gelita:schools:import --link-existing` (sama dengan tombol di panel Sekolah);
   - `GelitaSeeder::insertIfMissing()`, agar seeder aman diulang;
   - `ChallengeApiController::complete`, cadangan klien Dok 2 §5.2;
   - uji penjaga "fitur yang dihapus tidak kembali" di `RouteWiringTest`/`LanguageFilesTest`.
11. **Tautan rekaman petunjuk sesudah reset** (temuan uji manual §13.2 di `main`): `gelita:reset` meng-TRUNCATE `challenge_items`, sedangkan `audio_assets` tetap, sehingga 12 petunjuk Cari Objek kehilangan `audio_prompt_id`/`audio_prompt_en_id`. `NarrationImporter::relinkClues()` kini memasang lagi tautan dari rekaman terdaftar (`audio.narasi.{id,en}.petunjuk-…`, status persetujuan tetap) dan dipanggil reset sesudah impor bank. Ringkasan reset menyebut "… 24 tautan rekaman petunjuk"; `gelita:narration:import` tidak perlu diulang. Impor bank biasa (upsert per `item_key`) tidak memutus tautan, jadi hanya reset yang memerlukannya. Uji: `NarrationImporterTest::testRelinkCluesRestoresLinksAfterItemsAreRebuilt`; di MariaDB lokal 12/12 tautan per bahasa tetap ada sesudah `gelita:reset --ya`.
12. **Saran `ContentVerifier` untuk media yang belum diunggah:** saran `gelita:mockups` hanya untuk slot yang memang diisi generator (adegan tantangan dan gambar soal). Peta, latar, lencana wilayah, latar tantangan, dan gambar bacaan mendapat "Unggah di menu Gambar & suara." Uji: `MockupGeneratorTest::testVerifierSuggestsMockupsOnlyForMockupSlots`.

### Butir catatan di atas yang tidak berlaku lagi

- **Dok 1:**
  - butir 4 (mode DELETE) dan 5 (`--bersihkan-media-tantangan`): fiturnya dihapus;
  - butir 9 (kait mockup): sudah ada;
  - butir 13 (dua studi aktif pada database yang tidak direset): tidak relevan untuk instalasi baru;
  - butir 34: `ResetTables` kini sama dengan 29 migration;
  - "Untuk sesi Dok 2/Dok 3" tentang README, docs, dan workbook lama.
- **Dok 2 — naskah cerita:** `story-legacy.php` berkelompok dan `--lepas-audio-beda` sudah dihapus lagi; teks naskah dan rekaman tetap.

### Untuk sesi Dok 3

- **§13 (prosedur rilis satu kali, `deploy/linux/upgrade-kelas5.sh`, `deploy/windows/upgrade-kelas5.ps1`) tidak dikerjakan.** Rilis = instalasi baru menurut `docs/08_DEPLOYMENT.md`. Langkah panel di §13 (setujui narasi, ganti 45 gambar contoh, daftar siap penelitian, uji asap lalu `gelita:reset`) tetap berlaku sesudah instalasi.
- **§11 (dokumentasi):** README dan `docs/01`–`08` sudah menggambarkan keadaan sekarang. Dok 3 cukup memperbarui bagian yang ia ubah:
  - `05_VIEW_UI.md`: panggung 16:9;
  - `06_JAVASCRIPT.md`: `core/stage.js`;
  - `04`/`07`: menu dan halaman admin baru, lembar ekspor `item_tries`/`device_logs`;
  - `01`: kolom baru bila ada, serta `ResetTables`.
- Migration baru Dok 3 (bila ada) ditambahkan sebagai berkas baru sesudah `002900`, bukan dengan mengubah berkas yang ada, dan ikut dicatat di `ResetTables` serta `REQUIRED_MIGRATION`.

### Pengujian yang dijalankan

- **Otomatis:** `vendor/bin/phpunit --no-coverage` hijau sesudah setiap tahap; akhir: **444 test** (2 dilewati). Tanpa `--no-coverage`, di mesin tanpa Xdebug/PCOV PHPUnit keluar dengan kode 1 karena peringatan "No code coverage driver available" (`phpunit.dist.xml` meminta laporan coverage dengan `failOnWarning`); ini bawaan appstarter, belum diubah.
- **Peramban** (Chromium + Playwright, `php spark serve`, instalasi baru `gelita_uji`): daftar siswa 3 langkah → intro → peta → kartu misi → Susun Gambar Temanggung tampil 3 × 3 dengan gambar contoh, penanda kesempatan, tanpa galat JavaScript.
- **MariaDB 10.11 lokal:**
  - skema rantai lama (`gelita_lama`) dibandingkan dengan migration baru (`gelita_baru`) lewat `information_schema`, lalu rollback penuh dan `migrate` ulang;
  - instalasi baru dari database kosong: `migrate` (29 migration), lalu `GELITA_ADMIN_PASSWORD=… php spark db:seed DatabaseSeeder` → 12 tantangan, 126 soal, 88 baris dialog, 30 halaman Pustaka + 48 media, 45 gambar contoh; `gelita:content:verify` 0 galat;
  - `gelita:reset --ya --bank=docs/bank-soal/gelita-bank-soal-kelas5.xlsx` → bank diimpor, 45 gambar contoh sudah ada, kode siswa berikutnya `GLT-000001`;
  - `gelita:bank:import … --dry-run`: dengan kode lama berhenti dengan `Undefined array key "options"`, dengan kode baru menampilkan ringkasan per tantangan.

---

## Perbaikan sebelum Dok 3 — temuan tinjauan Dok 2

Dikerjakan di branch sementara dari `main` (7 Oktober 2026), menanggapi tinjauan sesudah Dok 2. Tinjauan itu tidak menggagalkan kriteria selesai Dok 2 §13.3. Semua temuan di bawah diperiksa ulang di kode sebelum diperbaiki, dan uji barunya gagal pada kode lama.

### Yang diperbaiki

1. **Batas 3 kesempatan bisa terlewati oleh dua permintaan bersamaan** (dua tab, atau kiriman ulang saat Wi-Fi lambat). `submitCheck()` membaca pengerjaan tanpa kunci lalu menulis `check_count = lama + 1`.
   - Akibatnya, di MySQL permintaan kedua menunggu kunci baris yang pertama lalu tetap jalan. Hasilnya `item_tries.try_no = 4`, dan pada cek ke-3 pengerjaan ditutup dua kali (`challenge_checked`/`challenge_completed` ganda).
   - **Perbaikan:** penguncian optimistis dengan UPDATE bersyarat (`ChallengeService::updateIf()`), sehingga tanpa `SELECT … FOR UPDATE` dan sama di MySQL maupun SQLite:
     - `check_count` hanya naik bila masih bernilai lama dan status masih `in_progress`;
     - `closeAttempt()` hanya mengubah `in_progress → completed`;
     - `abandonAttempt()` hanya mengubah `in_progress → abandoned`, jadi tombol Keluar di tab lama tidak menimpa pengerjaan yang sudah selesai.
   - **Cari Objek** punya celah yang sama: pemeriksaan `CLUE_CLOSED` berada di luar transaction, sehingga ketukan kedua dulu bisa berakhir di galat kunci unik `item_tries` (500). Kini baris `item_responses` hanya diubah bila `try_count` masih sama (dipakai `registerWrongClick()` dan `applyAnswer()`). `applyAnswer()` menulis baris jawaban **sebelum** `item_tries`, agar yang kalah gagal di UPDATE bersyarat, bukan di kunci unik.
   - **Permintaan yang kalah dibatalkan seluruhnya.** Jawabannya dibaca sesudah rollback, di luar transaction, agar tidak memakai snapshot lama:
     - pengerjaan sudah ditutup → `redirect` ke layar hasil;
     - petunjuk sudah tertutup → `CLUE_CLOSED`;
     - kesempatan batch habis → `TRIES_EXHAUSTED`;
     - selain itu kode baru **`ATTEMPT_CHANGED`** (409, `Game.errAttemptChanged`).
   - **Menyimpang dari usulan tinjauan.** Tinjauan mengusulkan `TRIES_EXHAUSTED` untuk semua kasus kalah. Kode itu tidak dipakai bila kesempatan sebenarnya masih ada, misalnya dua cek ke-1 bersamaan, karena maknanya keliru untuk data dan log. Bagi klien perilakunya sama: ke `redirect` bila ada, selain itu muat ulang.
   - **Di luar cakupan:** kiriman ulang *berurutan* (respons pertama hilang, siswa menekan lagi) tetap dihitung sebagai cek baru. Itu memang dua tekan tombol, jadi data tidak rusak, hanya penanda kesempatan di layar yang sempat tertinggal. Bila perlu, klien bisa mengirim nomor cek yang diharapkan dan server menolak yang tidak cocok.
2. **Cari Objek salah menandai petunjuk.** Bila siswa melompat lewat daftar sisa saat ketukan masih menunggu server, hasilnya dicatat ke petunjuk yang baru dipilih. Kini `pick()` memegang indeks petunjuk saat diketuk, dan tombol lompat diabaikan selama `busy` (`engines/cari.js`).
3. **Jawaban berubah selama "Cek jawabanku" diproses.** Selama `/check` berjalan isian dibekukan:
   - Susun Gambar: tukar keping, seret, dan lepas diabaikan;
   - Isi Rumpang: ketuk kotak dan chip kata diabaikan;
   - Benar atau Salah: radio yang aktif dinonaktifkan sementara, karena tombol panah juga mengubah radio.

   Waktu isian (`first_input_ms`/`last_input_ms`) kini diambil dari peta sebelum request. Bila request gagal, waktunya dikembalikan (`first` lama, `last` terbaru), sehingga isian selama request tidak hilang (`challenge.js` `check()`).
4. **Bintang salah bila soal dinonaktifkan di tengah pengerjaan.** `ItemResponseModel::countCorrect()` kini menyaring `ci.is_active = 1`, sama dengan penilaian (`expectedItems()`). Pengerjaan `solved` tidak lagi bernilai ★, dan `scorable_items` hanya menghitung soal yang masih dinilai.
   - **Pengerjaan tuntas yang belum ditutup.** Contohnya petunjuk Cari Objek terakhir yang belum ditemukan lalu dinonaktifkan: dulu setiap ketukan dijawab `CLUE_CLOSED` dan halaman dimuat ulang terus. Kini `openNode()` menutup pengerjaan seperti itu (`closingReason()`). Hasilnya `payload = null` + `redirect`: `ChallengeController::play()` mengalihkan ke layar hasil, dan `POST /api/nodes/{id}/attempts` membalas `{completed, attempt_id, redirect}`.
   - **Hanya benda jebakan tersisa.** Pengerjaan yang tinggal berisi benda jebakan (semua petunjuk dinonaktifkan) diperlakukan seperti isi tantangan berubah: ditinggalkan `content_changed`, lalu pengerjaan baru dibuka. Ia tidak ditutup dengan 0 soal dinilai, karena itu akan bernilai ★★★.
5. **Event `challenge_abandoned` palsu.** `ruleError()` kini menyetel `state.leaving` sebelum setiap muat ulang. Ini mencakup `TRIES_EXHAUSTED` tanpa `redirect`, `ATTEMPT_CHANGED`, `CLUE_CLOSED`, dan `ATTEMPT_CLOSED`, yang dulu jatuh ke `handleApiError()` lalu memuat ulang tanpa penanda.
6. **Teks:** EN `perEngine` = "Success on First Try by challenge type" (sesuai tabel §12.1).

### Keputusan

- **Tombol modal ketukan salah Cari Objek tetap "Coba lagi"** (*Try again*), bukan **Perbaiki**. §5.3 meminta Perbaiki untuk modal "Belum semuanya tepat" engine batch, dan di sana memang ada jawaban yang diperbaiki. Pada Cari Objek siswa mengetuk benda lain, jadi "Coba lagi" lebih tepat. Modal batch tetap memakai Perbaiki (`fixAnswers`).
- **Uji `TryLimitTest::testCorrectItemsAreLocked`** dulu memulihkan pengerjaan `solved` ke `in_progress` untuk memeriksa payload muat ulang. Kini pengerjaan seperti itu langsung ditutup saat dibuka (butir 4), jadi pemeriksaan payload dipindah ke sesudah cek ke-1, saat pengerjaan memang masih berjalan.

### Untuk sesi Dok 3 (temuan tinjauan yang sudah diperiksa, belum diubah)

- **Gambar pernyataan Benar atau Salah** tidak bisa diperbesar dan terpotong `object-fit: cover; max-height: 160px` (`boleh.php` `.card-image`, `game.css`). §6.3 Dok 2 memintanya, dan Dok 3 §5 merincinya (kartu 192 × 144, lightbox 640 × 480).
- **Aksesibilitas:**
  - modal `aria-describedby="modal-text"` hanya menunjuk paragraf pertama, sehingga "Kesempatanmu tinggal n lagi" tidak terbaca pembaca layar;
  - keping puzzle yang salah (`.is-misplaced`) hanya ditandai visual, tanpa perubahan `aria-label`.
- **Tampilan:**
  - hitungan ✓/✗ Susun Gambar tidak dipulihkan saat dimuat ulang (rumpang dan boleh memulihkannya dari `state.locked`);
  - bentuk jamak EN "1 blanks" / "1 cards" (`Js.blankEmptyText`, `Js.cardsEmptyText`; juga `puzzleWrongText` "1 pieces").

### Pengujian

- Uji baru di `TryLimitTest`:
  - cek ganda ditolak tanpa tulisan;
  - cek ke-3 ganda menutup sekali tanpa `try_no` 4;
  - cek ganda sesudah `solved` menunjuk layar hasil;
  - ketukan Cari Objek bersamaan → `ATTEMPT_CHANGED`/`CLUE_CLOSED`;
  - soal nonaktif tidak ikut bintang;
  - petunjuk terakhir nonaktif ditutup saat dibuka;
  - Keluar tidak menimpa pengerjaan selesai.

  Ketujuhnya gagal pada kode sebelum perbaikan.
- `vendor/bin/phpunit --no-coverage`: **451 test hijau** (444 + 7 baru).
- Klien JS diperiksa dengan `node --check`. Belum diuji di peramban.

---

## Dok 3 — data mentah dan ekspor

Dikerjakan di branch `dok3-data-mentah` (dari `main` sesudah PR #42), paralel dengan sesi tampilan admin dan sesi panggung game. Cakupan: §8.4 (`RawDataService`), §9 (ekspor), §10 (PDF), uji §12.1 bagian layanan.

### Kontrak untuk sesi admin

`App\Services\RawDataService` — satu tempat query setiap dimensi. Dimensi = kode lembar §9.2: `peserta`, `sesi`, `perangkat`, `progres`, `pengerjaan`, `jawaban`, `percobaan`, `navigasi`, `bantuan`, `selesai`, `event` (admin saja), `masukan`, `butir`. Tab `/admin/data/{dimensi}` §8.3 memakai delapan di antaranya.

```php
query(string $dimension, array $filters, StaffScope $scope): BaseBuilder   // baris mentah + `_key`; ubah dengan shape()
page(string $dimension, array $filters, int $page, int $perPage, ?string $sort): array
streamCsv(string $dimension, array $filters, $handle): int                 // jumlah baris data
```

Bentuk hasil `page()` (dipertahankan):

```php
[
  'rows'     => list<array<string, int|string|null>>, // kunci = columns[*].key; nilai berkode, sama dengan CSV
  'total'    => int,        // jumlah baris semua halaman
  'page'     => int,        // 1-based, dijepit ke 1..halaman terakhir
  'per_page' => int,        // 25 | 50 | 100 (lainnya → 25)
  'sort'     => ?string,    // yang dipakai: "kolom" naik, "-kolom" turun (tak sah → urutan bawaan)
  'columns'  => list<array{key: string, label: string, type: string}>,
]
```

- `type` ∈ `code|text|int|ms|datetime|bool|enum|json`. Waktu = ISO 8601 WIB dengan milidetik (`2026-10-07T08:30:15.123+07:00`), durasi = milidetik (int), boolean 1/0, JSON sebagai teks. Tampilan `m:ss` dan lencana dibuat tabel; label nilai lewat `ExportLabels::label($dimension, $key, $value)`.
- Kolom yang dapat diurutkan: `sortable($dimension)`. Kolom yang dihitung di PHP (judul variasi, jawaban, payload) tidak bisa diurutkan.
- Filter (`RawDataService::FILTER_KEYS`): `study_id`, `participant_id`, `participant_code`, `school_id`, `class_level`, `province_code`, `locale`, `level_id`, `node_id`, `variant_group`, `date_from`, `date_to` (Y-m-d). Kunci lain dan nilai rusak diabaikan. Rentang tanggal memakai waktu baris itu sendiri (mulai pengerjaan, waktu percobaan, waktu event, tanggal daftar untuk Peserta).
- Cakupan diikat ke objek: di controller pakai `$this->rawData()` (trait `StaffScope`, berisi `App\Services\StaffScope` dari `$this->dataScope()`), atau `service('rawDataService', $scope)`. Tanpa cakupan `page()`/`streamCsv()` melempar `LogicException`; dimensi yang tidak boleh (`event` untuk guru) → `DomainException` (petakan ke 404); dimensi tak dikenal → `InvalidArgumentException`.
- CSV per tabel: `BaseAdminController::rawCsvDownload($dimension, $filters)` → unduhan CSV (BOM, koma, judul = kode kolom), tercatat `export_csv` di audit. Rute `/admin/data/{dimensi}` dan `?format=csv` dibuat sesi admin.
- `readFilters()` kini juga menerima `participant_code` dan `variant_group`.


### Keputusan dan penyimpangan

1. **`StaffScope` sebagai objek nilai.** Kontrak §8.4 memakai `StaffScope $scope`, sedangkan `StaffScope` yang ada adalah trait controller dan tidak bisa menjadi tipe parameter. Karena itu dibuat `App\Services\StaffScope`, objek nilai hak baca (`admin($anonymous)`, `teacher($schoolId)`, `fromStaff($row)`).
   - Trait controller tetap ada, dan kini membuat objek itu lewat `dataScope()`.
   - `page()` dan `streamCsv()` tidak menerima cakupan sebagai parameter (sesuai kontrak dokumen). Cakupannya diikat ke objek lewat `withScope()` atau `service('rawDataService', $scope)`. Tanpa cakupan, keduanya menolak (gagal tertutup).
2. **Kode lembar §9.2 dipakai sebagai kode internal** di mana-mana: URL tabel, `scope_json.sheets`, nama berkas CSV, dan uji. Kode lama berbahasa Inggris (`Participants`, `Raw Events`, …) serta kunci bahasa `Admin.exportSheet`/`exportDimension` dihapus.
3. **Kolom yang ditambahkan atau ditafsirkan:**
   - Kamus Data mendapat kolom `judul`, agar kode kolom dan judul berlabel sama-sama tercatat.
   - Lembar Bantuan mendapat `item_key` dan `hint_id`. Nilainya diambil dari event `hint_opened` (`payload.hint_id`).
   - `butir.decoy` = `config_json.decoy`, hanya untuk Cari Objek; untuk jenis soal lain kosong.
   - `indicator` = kode indikator soal, atau indikator tantangannya bila soal tidak punya.
   - `event` memuat `session_code` dan `participant_code` di samping semua kolom `game_event_logs`. Tanpa keduanya, lembar itu tidak bisa digabung dengan lembar lain. Kolom `deleted_*` tidak ikut, karena baris yang ditandai hapus memang tidak diekspor.
4. **Nilai tanpa olahan:**
   - Waktu ditulis sebagai teks ISO 8601 (WIB, dengan milidetik) di XLSX maupun CSV, tidak sebagai sel tanggal Excel. Alasannya: milidetik tetap utuh dan isi XLSX sama dengan CSV.
   - Durasi selalu dalam milidetik. Konversi lama ke detik atau menit dihapus.
   - Mode berlabel hanya mengganti kode dengan label, ditambah kalimat jawaban dan nama wilayah.
5. **Judul kolom CSV dan XLSX berkode = kode kolom.** Pada mode berlabel, judulnya berbahasa Indonesia. CSV-ZIP memakai format `data_exports.format = 'csv'` (sudah ada di `in_list`) dengan berkas `.zip`, sehingga tanpa perubahan skema.
6. **Filter tanggal memakai waktu baris itu sendiri.** Contohnya waktu percobaan pada Riwayat Percobaan dan waktu event pada Navigasi; dulu semuanya memakai `game_sessions.started_at`. Rinciannya ada di 07 FITUR 14.
   - Filter `class_level` tidak ada di `ExportService::FILTER_KEYS` (§9.2), tetapi tetap diterima `RawDataService` untuk tabel admin.
   - Peserta difilter studi/bahasa lewat sesinya. Peserta tanpa sesi tetap muncul bila filter itu tidak dipakai.
7. **Kamus Data selalu ikut.** Permintaan yang hanya berisi lembar terlarang (misalnya guru meminta `event`) menghasilkan semua lembar yang boleh, sama seperti perilaku lama.
8. **`AnalyticsService` dipangkas:**
   - `summary()` kini memuat KPI §8.3 (`participant_count` = siswa terdaftar, `active_sessions` ≤ 10 menit, `completed_attempts_today`, `feedback_count`) beserta hitungan lama. `audio_usage` diganti `audio_plays`/`audio_autoplays`.
   - `participantProfile()` menambahkan tabel mentah `sessions`, `devices`, `progress` (per tantangan aktif) dan `feedback`.
   - `digitalSecurityLiteracy()`, `ParticipantModel::ageDistribution()`/`digitalSecurityStats()`, `AudioUsageEventModel::usageStats()` (rasio, rata-rata), dan endpoint `GET /api/admin/participants` dihapus.
   - Dasbor hanya diubah seperlunya: ubin "Sandi kuat" dibuang dan kunci audio disesuaikan. Tampilan barunya dikerjakan sesi admin.
9. **View yang disentuh karena backend berubah:**
   - `admin/export/index`: formulir lembar §9.2, berlabel/berkode, tombol CSV-ZIP;
   - `admin/participants/show`: unduh data siswa XLSX/CSV dan PDF;
   - `pdf/*`.

   Tata letaknya tetap memakai komponen lama; gaya baru menjadi bagian sesi admin.
10. **Laporan PDF satu siswa** memakai baris pengerjaan selesai berbintang terbanyak per tantangan (yang paling awal bila sama). Bila belum ada yang selesai, dipakai pengerjaan terakhir. Warnanya mengikuti token admin §8.1 (netral).
11. **Uji §12.1 di tingkat layanan:** `AdminTableTest` (kontrak `page()`, urutan, halaman, CSV = tabel, cakupan guru) dan `RawExportTest` (XLSX dibaca PhpSpreadsheet, CSV-ZIP dibaca `ZipArchive` + `fgetcsv`). Keduanya memakai fixture SQLite kecil `Tests\Support\Database\RawDataWorld`, bukan generator simulasi. Uji tampilan tabel (komponen dan rute `admin/data/*`) menjadi bagian sesi admin.

### Pengujian yang dijalankan

- `vendor/bin/phpunit --no-coverage`: **463 test hijau**. Sebelumnya 451. Ada 16 uji baru (9 `AdminTableTest`, 7 `RawExportTest`), sementara `ExportLabelsTest`/`ExportRulesTest` ditulis ulang.
- Pembuatan PDF lewat mPDF diuji ujung ke ujung di `RawExportTest`.
- Belum diuji terhadap MariaDB atau di peramban. Query `UNION` lembar Bantuan dan pengurutan memakai Query Builder (identifier diberi prefix dan quote), tetapi baru dijalankan di SQLite.

---

## Dok 3 — panel admin

Dikerjakan di branch `dok3-admin` (dari `main`), sesuai `03_TAMPILAN_16x9_DAN_ADMIN.md` §1.6, §8.1–§8.3, dan §12.1. `main` digabung sesudah PR #44, #45, #46, dan #48. Suite: **486 test hijau** (`vendor/bin/phpunit --no-coverage`).

### Keputusan dan penyimpangan

**§8.1 Arah desain dan kerangka**

1. **`admin.css` berdiri sendiri.** `layouts/admin.php` dan `layouts/auth.php` hanya memuat `admin.css`: font, token §8.1, reset, dan semua komponen yang dipakai view admin (tombol, formulir, lencana, modal, toast, kolom sandi, dan sebagainya). CSS permainan (`tokens`/`base`/`layout`/`components.css`) tidak ikut, sehingga perubahan panggung 16:9 tidak memengaruhi admin dan sebaliknya. Saat `main` (PR #48) digabung, aturan admin yang dipindah sesi panggung ke `admin.css` tidak dipakai; versi branch ini yang dipertahankan.
2. **Breadcrumb ada di topbar, bukan di kepala halaman.** §8.2 menaruh breadcrumb di kepala halaman, sedangkan §8.1 menaruhnya di topbar. Agar tidak tampil dua kali, breadcrumb hanya ada di topbar (`AdminMenu::crumbs()`, ditambah `$breadcrumb` halaman detail). Kepala halaman (`partials/admin-head`) berisi judul 24 px, deskripsi 14 px, dan aksi di kanan; `eyebrow` lama yang hanya mengulang nama grup dibuang.
3. **Satu sumber menu: `App\Libraries\AdminMenu`.** Sidebar dan breadcrumb membaca daftar yang sama. Grupnya Data penelitian, Konten, dan Pengaturan. Menu guru hanya Ringkasan, Siswa, Sesi bermain, Data mentah, Kritik & saran, dan Unduh data. Label menu di `Language/id/Admin.php` berganti mengikuti §8.3. `LanguageFilesTest` kini memeriksa `AdminMenu.php`.
4. **Responsif:** sidebar 248 px, kolom ikon 72 px di bawah 1280 px, laci `:target` di bawah 768 px. Topbar 56 px lengket. Ganti sandi dan Keluar dipindah ke menu akun topbar (`<details>`, tanpa JavaScript).

**§8.2 Komponen**

5. **`App\Libraries\AdminTable` + `components/data-table.php`.** `AdminTable::page()` menghasilkan bentuk yang sama dengan `RawDataService::page()`, yaitu `{rows, total, page, per_page, sort, columns}`. Karena itu satu komponen merender halaman daftar (Siswa, Sesi, Kritik & saran) maupun Data mentah.
   - Urutan `kolom`/`-kolom` hanya diterima untuk kolom yang punya ekspresi `sort`. Urutan lain diganti bawaan, dan pengurut stabil (`id DESC`) selalu ditambahkan.
   - `per_page` di luar 25/50/100 menjadi 25, dan halaman dijepit ke halaman terakhir.
   - CSV: UTF-8 dengan BOM, judul = kunci kolom, nilai mentah (kode, milidetik).
   - Tabel kecil tanpa pagination (ukuran wajib, temuan pindai, riwayat aktivitas) tetap memakai `components/admin-table` dengan urut di client.
6. **Sel:** `AdminTable::cell()` memformat sel menurut jenis kolom: `int`, `num`, `ms` (`m:ss` dengan `title` milidetik), `datetime` (`title` = nilai mentah), `code`, `status` (lencana), `bool`, `json`, `stars`, `clip` (teks dipotong yang dapat dibuka), dan `label:{grup}`. Jenis `enum` dari RawDataService ditampilkan berlabel lewat `ExportLabels::label()`, dengan kode aslinya di `title` (halaman Data mentah).
7. **Bilah filter** mendapat chip filter aktif. Setiap chip adalah tautan yang melepas satu filter. Kontrol baru: kode siswa, tantangan, variasi, kab/kota, jenis kelamin, umur, dan status progres. `StaffScope::readFilters()` menerima `q`, `district_code`, `gender`, `age`, `progress`, `variant_group`, dan `participant_code`; `apply_research_filters()` mengabaikan kunci yang tidak dikenalnya.
8. **Modal konfirmasi:** `form[data-confirm]` memakai `core/modal.js` yang sudah ada. Konfirmasi lama `<details class="confirm">` dipertahankan di halaman yang memakainya. Rilis, nonaktifkan media, dan reset sandi siswa memakai modal.
9. **Tab:** antarhalaman memakai `.tabs` dan tautan biasa (detail siswa `?tab=`, Data mentah, Tata kelola). Di dalam satu halaman (editor tantangan) dipakai `admin/tabs.js` dengan `data-tab-panel`, karena satu form node terbagi ke tab Info dan Variasi.

**§8.3 Halaman**

10. **Ringkasan tanpa bilah filter.** §8.3 tidak menyebut filter untuk Ringkasan. KPI memakai `AnalyticsService::summary()` dari PR #45 (`participant_count`, `active_sessions`, `completed_attempts_today`, `feedback_count`). Peringatan admin (gambar contoh, narasi, verifikasi konten) berasal dari `App\Libraries\ResearchReadiness`. Halaman Penelitian memakai sumber yang sama untuk **daftar siap penelitian**.
11. **ResearchReadiness:**
    - gambar contoh = `media_assets.is_placeholder = 1` yang aktif;
    - narasi = `NarrationCatalog::progress()`, semua bahasa disetujui;
    - verifikasi = `ContentVerifier::run(true)` tanpa galat.

    `StudyController` menolak perubahan status ke `active` (Berjalan) saat daftar belum hijau, kecuali form mengirim `confirm_not_ready = 1`. Label status `active` di panel menjadi "Berjalan".
12. **Detail siswa:**
    - Tab Pengerjaan, Jawaban, Navigasi, dan Bantuan memakai `RawDataService::page()` dengan filter `participant_id`. Riwayat percobaan per baris Jawaban berasal dari dimensi `percobaan` siswa yang sama, dikelompokkan menurut `attempt_id|item_key`.
    - Tab Profil, Sesi & perangkat, Progres (12 baris node, pengerjaan terakhir per node), dan Kritik & saran memakai query lokal kecil.
    - Unduh XLSX/CSV-ZIP dan Cetak PDF memakai rute `ExportController` dari PR #45.
13. **Sesi bermain:** linimasa kini bagian dari detail sesi (§8.3 hanya menyebut `/admin/sesi/{id}`). Rute lama `/admin/sesi/{id}/event` dialihkan ke `#linimasa`, dan `admin/sessions/timeline.php` dihapus.
14. **Data mentah** (`RawDataController`): delapan tab §8.3. Filter dipaksa ke cakupan sekolah, dan `DomainException` (guru meminta dimensi khusus admin) menjadi 404. Titik sambung ke layanan ada di `BaseAdminController::rawService()`/`rawPage()`/`rawCsv()`:
    - `rawService()` memakai `StaffScope::rawData()`, atau tiruan `Services::injectMock('rawDataService', …)` di uji;
    - `rawCsv()` memakai `rawCsvDownload()` dari PR #45, yang juga mencatat audit `export_csv`.
15. **Tantangan:**
    - Status verifikasi per tantangan diambil dari temuan `ContentVerifier::run(true)` yang lingkupnya diawali `Wilayah {nama} · tantangan {n} (`.
    - Gambar contoh tersisa = slot `challenge.{ref}.*` yang masih mockup aktif.
    - "Variasi aktif" menghitung variasi yang punya soal aktif. Cari Objek kelas 5 memang punya satu variasi adegan, jadi tampil 1/3. Hanya 0/x yang ditandai merah.
16. **Gambar & mockup:** status slot Asli / Gambar contoh / Kosong dihitung dari `is_active` dan `is_placeholder`. Tombol **Unggah gambar asli** per slot memakai `admin/media-upload.js` yang sudah ada; tanpa JavaScript, tautan `?asset_key=…#unggah` mengisi kotak unggah. Kolom ukuran menampilkan ukuran berkas (sebenarnya dan wajib, `$assetSizes`) dan ukuran tampil di panggung dalam dp (`Config\Gelita::$assetDisplaySizes` dari PR #48, Dok 3 §6; benda Cari Objek: "selebar w% adegan"). Butir ini diserahkan sesi panggung.
17. **Analitik, Aturan skor, dan ECharts** sudah dihapus sejak Dok 1 §14. Tidak ada rute atau aset yang tersisa (`RouteWiringTest`).

**§12.1 Uji**

18. **`AdminTableViewTest`, bukan `AdminTableTest`.** Sesi data mentah sudah memakai nama `AdminTableTest` untuk kontrak `RawDataService::page()` di tingkat layanan. Uji tampilan di branch ini mencakup:
    - render kolom, tautan urut dan `aria-sort`, pagination, pilihan 25/50/100, dan tombol CSV;
    - urut dan pagination `AdminTable` di SQLite;
    - CSV sama dengan isi tabel;
    - daftar Siswa admin dengan identitas;
    - guru: hanya sekolahnya, tanpa kolom identitas, juga di CSV, dan tanpa menu Konten.
19. **Dua perbaikan yang ditemukan uji ini:**
    - CSV `AdminTable` berputar tanpa henti di SQLite, karena `getUnbufferedRow()` mengembalikan `false`, bukan `null`, di akhir hasil;
    - join subquery "sesi terakhir" memakai `prefixTable()`, karena `join(…, escape: false)` tidak menambah prefix tabel.

### Pengujian yang dijalankan

- **Otomatis:** `vendor/bin/phpunit --no-coverage` → 486 test hijau.
- **Peramban (Chromium + Playwright, MariaDB lokal):** `migrate --all`, `db:seed`, `gelita:reset`, lalu `gelita:simulasi --siswa=30`. Semua halaman §8.3 dibuka di 1280, 1440, dan 1920 px: Ringkasan, Siswa, detail siswa (Profil, Jawaban, Pengerjaan), Sesi + detail, delapan tab Data mentah, Kritik & saran, Unduh data, Tantangan, editor tantangan, Gambar & mockup, Kelengkapan aset, Penelitian, Rilis, Sekolah, Staf, Tata kelola, dan Ubah sandi. Semuanya status 200, tanpa galat konsol, dan tanpa gulir horizontal halaman (tabel lebar menggulir di dalam `.table-wrap`). Unduh CSV Siswa diperiksa sebagai unduhan berkas.
- **Belum dilakukan:** uji keyboard dan pembaca layar menyeluruh, serta membuka CSV/XLSX di LibreOffice atau R (§12.3, bagian unduhan diuji sesi data mentah).

---

## Dok 3 — panggung 16:9

Dikerjakan di branch `dok3-panggung` dari `main` (7–8 Oktober 2026), sebagai salah satu dari tiga sesi paralel Dok 3. Cakupan: §1–§7 (panggung, token rem, `stage.css`, kerangka, tata letak setiap layar, tantangan lebar penuh tanpa Jaka, ukuran aset, JavaScript panggung), butir carry-over tinjauan Dok 2, perbaikan tombol "Kenali", uji §12.1 (`StageCssTest`, `ChallengeFrameTest`), skrip §12.2 `tools/qa/stage-check.mjs`, dan `docs/05`/`docs/06`. Panel admin (§8) dan ekspor (§9–§10) dikerjakan sesi lain. Versi awal branch ini (sampai `StageCssTest`) sudah digabung pemilik lewat PR #48; skrip QA, dokumentasi, dan catatan ini menyusul di PR berikutnya dari branch yang dimulai ulang dari `main`.

### Yang berubah

1. **Panggung.** `layouts/game.php` = `<html class="game-stage">` › `.stage-frame` › `#stage` (80 × 45 rem). Layar putar satu-satunya elemen di luar panggung. Skrip sebaris kecil di `<head>` mengisi `--app-w`/`--app-h` sebelum CSS dimuat; `core/stage.js` melanjutkannya.
2. **Satuan.** Semua CSS game (`tokens`, `base`, `stage`, `components`, `game`, `cinematic`) dikonversi ke rem (px ÷ 16, kecuali garis 1px). Token `--step-*`/`--sp-*` di CSS game diganti `--fs-*`/`--gap-*` (§2.3; `--sp-7` 48 dp menjadi `--gap-7`). Semua media query lebar/tinggi dan semua `vw`/`vh` dihapus. `StageCssTest` menjaganya.
3. **`layout.css` dihapus (§2.4).** Bagian game pindah ke `stage.css`. Bagian admin dan halaman masuk staf (`body.admin`, `.admin-*`, `.menu-*`, `.auth-*`, media query admin, lapisan modal/toast `position: fixed`) dipindah **apa adanya** ke awal `admin.css`, dalam blok bertanda "DARI tokens.css & layout.css". Blok itu juga memuat token ukuran lama (`--step-*`, `--sp-*`, `--tap` 44px, `--sidebar-w`, `--content-max`) dan memetakan `--fs-*` ke `--step-*`, sehingga `components.css` (dipakai bersama) tampil seperti sebelumnya di admin. Untuk itu `layouts/admin.php` hanya kehilangan tautan `layout.css`, dan `layouts/auth.php` menukar `layout.css` dengan `admin.css`. **Untuk sesi admin (§8):** blok ini boleh dibuang seluruhnya saat admin tidak lagi memakai `tokens.css`/`base.css`/`components.css`. *(Sesudah PR #47 `admin.css` berdiri sendiri dan layout admin tidak lagi memuat CSS game; blok pindahan ini sudah tidak ada.)*
4. **Kerangka (§3).** `#stage` memakai `contain: layout paint`. Akibatnya `position: fixed` di dalam panggung (tirai, overlay Kenali, latar slide, konfeti) berlabuh ke panggung tanpa harus diubah satu per satu, dan apa pun yang keluar terpotong. `main#app.stage-main` selebar panggung tanpa stacking context, jadi koordinat isi = koordinat panggung (tabel §4), dan overlay Kenali tetap dapat menutupi HUD.
5. **Koordinat (§3.3, §4–§5).** Semua angka ada di `stage.css` §Koordinat sebagai dp tanpa satuan (`--x --y --w --h`), diterapkan kelas `.stage-box`. Elemen dari partial bersama (kotak cerita, panel engine) mendapat aturan yang sama lewat daftar selektor berawalan `.stage`, agar menang atas `position: relative` milik `.panel-parchment`.
6. **HUD (§3.2).** Kiri: lentera + merek. Tengah: section baru `hudTitle` atau nama wilayah dari `$level`. Kanan: bahasa, suara, **layar penuh** (`#btn-fullscreen`, ikon baru `fullscreen`/`fullscreen-exit`, `Game.fullscreen`), beranda, profil, keluar.
7. **Tantangan (§5).** Jaka dihapus dari `_frame.php` (`.challenge-character`) dan layar selesai (`finished-jaka`, `.finished-layout`). Bingkai ukiran digambar `.challenge::before`. `.arena-panel`/`.arena`/`.arena-body` menjadi `display: contents`. Instruksi dipindah ke panel `aside.challenge-aside` (+ section `aside` opsional per engine).
8. **Layar selesai (§5.3).** Kartu tunggal, tiga statistik (ubin "Benar di akhir" dihapus), dan baris lembut `Game.exhaustedNote` bila kesempatan habis.
9. **JavaScript (§7):**
   - `core/stage.js` baru: `initStage`, `stageRect`, `stageScale`, layar penuh, dan `viewport_changed` (dipindah dari `core/device.js`) dengan `stage_w`, `stage_h`, `scale`;
   - `page_hidden`/`page_visible` pindah ke `core/events.js` (`initVisibilityTracking`);
   - `scrollToCenter()` menggulir panel `.stage-scroll` terdekat;
   - modal, toast, `#sr-announcer`, konfeti, dan lightbox berada di/berukuran `#stage`;
   - `core/lightbox.js` baru, dipakai Pustaka dan Benar atau Salah;
   - `boleh.js` tanpa cabang `matchMedia('(max-width: 640px)')`;
   - `trackReading()` membaca posisi gulir panel bacaan;
   - lapisan yang dibuat `inert` oleh tirai dan Kenali kini dicari di `#stage`.
10. **Ukuran aset (§6).** `Config\Gelita::$assetDisplaySizes` baru: ukuran tampil dp per pola slot, di samping `$assetSizes` (ukuran berkas, tetap dipakai validasi). Menampilkannya di halaman **Media** termasuk perombakan admin §8 dan ditinggalkan untuk sesi admin.

### Butir carry-over tinjauan Dok 2

- **Gambar pernyataan Benar atau Salah:** `button.card-image[data-zoom]` 192 × 144 dp dengan gambar utuh (`object-fit: contain`, tidak lagi `cover` + 160px); ketuk membuka lightbox 640 × 480 dp. Event `overlay_opened` `{ overlay: 'statement_image' }`.
- **`aria-describedby` modal** menunjuk semua paragraf (`#modal-text`, `#modal-text-2`, …).
- **Keping puzzle salah** diumumkan ke pembaca layar: `aria-label` slot ditambah "— belum di tempatnya" (`Js.pieceMisplaced`), dan dikembalikan saat keping ditukar.
- **Hitungan ✓/✗ Susun Gambar** dipulihkan saat dimuat ulang (✓ 0 / ✗ 1 bila `tries_used > 0`; satu gambar per pengerjaan).
- **Bentuk jamak EN:** kunci tunggal `blankEmptyTextOne`, `cardsEmptyTextOne`, `puzzleWrongTextOne` (ID dan EN); engine memilihnya saat n = 1.

### Tombol "Kenali" (Perawatan repo)

Masalahnya masih terjadi sesudah tata letak 16:9 dan kini diperbaiki sesuai usulan. Saat `game/map.js` siap, hash `#kenal-{code}` atau `#kenal-{code}-{n}` membuka overlay di slide n tanpa memutar narasi otomatis, lalu hash dibersihkan dengan `history.replaceState`. Narasi peta tidak dimulai selama overlay Kenali terbuka.

### Keputusan dan penyimpangan dari dokumen

- **Kotak teks dialog wilayah dan wilayah tuntas di y 472, bukan 520.** Kedua layar ini punya nav bar (656–720), yang akan menutupi kotak di 520–688. Cerita pembuka dan penutup tetap 520 karena "Lewati" ada di kanan atas.
- **Instruksi tantangan.** §5 tidak memberi tempat instruksi untuk Isi Rumpang, Benar atau Salah, dan Cari Objek. Karena itu:
  - Rumpang: instruksi 96, 128, 1088 × 40, bacaan 96, 176, 1088 × 384 (bukan 128 × 432);
  - Benar atau Salah: instruksi 652, 128, 596 × 64, pernyataan 652, 200, 596 × 440 (bukan 128 × 512);
  - Cari Objek: instruksi di panel kanan 1084, 192, 164 × 486. Ketukan salah dan waktu sudah tampil di kepala, jadi panel itu tidak mengulangnya.
- **Penanda kesempatan ●●○ hanya di kepala tantangan**, tidak diulang di bilah aksi (§5.1 dan §5.2 menyebut keduanya). Bilah aksi berisi tombol Cek saja.
- **Tiga tips Susun Gambar:** `Game.puzzleHint`/`puzzleHintJigsaw` yang ada, ditambah `Game.puzzleTipMark` dan `Game.puzzleTipHint` (baru, ID/EN). Keping yang benar tidak dikunci di engine ini, jadi tips tidak menjanjikannya.
- **Kontrol narasi sinematik** (◀ ⏸ ↻ ▶ + Otomatis) lebih lebar dari 240 dp. Kontrol diletakkan di kanan atas y 72, di samping "Lewati" (nav bar), tidak dalam satu kotak 1008, 72, 240 × 48.
- **Peta Kedu:**
  - "Pustaka Kedu", "Tonton penutup", dan "Balai Refleksi" tetap di nav bar, tidak dipindah ke panel kanan; panel kanan berisi kartu wilayah;
  - balon narasi Jaka 48, 560, 600 × 96 tanpa gambar tokoh besar di atas peta, cukup nama di balon;
  - judul "Peta Kedu" tampil di tengah HUD (`hudTitle`) dan sebagai `h1` tersembunyi.
- **Peta wilayah tanpa tombol Kenali.** Overlay Kenali hanya dirender di Peta Kedu (`$regionIntros` dari controller); menambahkannya ke peta wilayah butuh perubahan controller dan tidak dikerjakan. Panel kanan berisi judul, fokus, progres, dan daftar empat tantangan (tetap berguna untuk pembaca layar).
- **Beranda di HUD** dipertahankan di kanan (§3.2 tidak menyebutnya).
- **Profil:** empat ubin §4 (serpihan, tantangan selesai, bintang, waktu). Umur, jenis kelamin, kelas, dan sekolah tetap tampil sebagai daftar kecil di bawah nama. Ubin "Berhasil pada Percobaan Pertama" dihapus dari profil.
- **Hasil:** urutan kolom mengikuti §4 (Percobaan sebelum Berhasil pada Percobaan Pertama).
- **Kartu misi:** hasil terbaik kini memuat "Berhasil pada Percobaan Pertama x dari y" (§4).
- **Ukuran huruf lebih besar dari sebelumnya.** `--fs-body` 20 dp ≈ 20 px pada 1280 × 720, sedangkan `--step-0` lama ± 16 px. Panel dibuat bergulir di tempat yang perlu, sesuai §5.2 ("teks bacaan panjang tidak pernah mengecilkan huruf").
- **`visualViewport` tidak dipakai** untuk `--app-*`, meski disebut di §2.1. Papan ketik layar dan cubit-zoom akan mengecilkan panggung saat siswa mengetik di formulir. Yang dipakai `innerWidth`/`innerHeight`.
- **Layar putar** memakai rem dari lebar layar (`calc(var(--app-w) / 24)`) selama tampil. Panggung disembunyikan di belakangnya. Tanpa ini, rem ikut lebar panggung potret (± 4,9 px) dan teksnya tidak terbaca.
- **Lightbox** tetap `<dialog>` + `showModal()` (fokus terkunci, Esc). Dialog modal ada di top layer, di luar `#stage`, jadi ukurannya ditulis dalam rem agar tetap di dalam panggung; latar `::backdrop` menutupi bingkai juga.
- **Layar penuh lepas setiap pindah halaman** (perilaku peramban untuk navigasi dokumen). Memasang aplikasi tetap cara utama.
- `map.kedu` tidak ditambahkan ke `$assetSizes`, karena menambah validasi unggahan untuk slot yang mungkin sudah diunggah. `$assetDisplaySizes` hanya keterangan.

### Pengujian yang dijalankan

- `vendor/bin/phpunit --no-coverage`: hijau sesudah setiap tahap. Akhir sesudah `main` terbaru (data mentah, simulasi, paket aset) digabung: **481 test**.
- Uji baru:
  - `StageCssTest` (7): satuan viewport, media query lebar/tinggi, px selain 1px, panggung 80 × 45 rem, isi layout di dalam `#stage`, layout admin tanpa `layout.css`;
  - `ChallengeFrameTest` (5): tanpa Jaka di kerangka, engine, dan layar selesai; avatar Mbah Kedu tetap di Cari Objek; koordinat kerangka; gambar pernyataan dapat diperbesar.
- `RotateGateTest` kini membaca `stage.css`.
- **Peramban** (`tools/qa/stage-check.mjs`, Chromium, `php spark serve`, MariaDB lokal hasil `migrate` + `db:seed`, studi `unlock_mode = free`). Pemeriksaannya mencakup ±50 layar per ukuran: semua layar §4, keempat tantangan di ketiga wilayah, modal hasil, dan Kenali, pada 1280 × 720, 1920 × 1080, 1366 × 657, 1024 × 768, 2560 × 1080, dan 844 × 390. Pada 1280 × 720 juga dijalankan 12 layar selesai (kesempatan dihabiskan), hasil, wilayah tuntas, Pustaka + lightbox, penutup, dan refleksi; 390 × 844 sentuh memeriksa layar putar. Hasilnya (8 Oktober 2026, dijalankan dalam beberapa putaran karena satu putaran penuh lebih dari 30 menit):
  - **1280 × 720, 1920 × 1080, 1366 × 657:** 150 layar, tanpa temuan.
  - **1024 × 768, 2560 × 1080, 844 × 390 + layar putar 390 × 844:** satu temuan, posisi `button.object` Cari Objek Magelang bergeser 0,72 % di 844 × 390. Penyebabnya penunjuk mouse yang tertinggal di atas benda sesudah klik sebelumnya, sehingga `:hover` (`scale(1.05)`) menggeser kotaknya, bukan tata letak. Skrip kini memindahkan mouse ke pojok sebelum mengukur; putaran 1280 × 720 + 844 × 390 sesudahnya lolos (106 layar, termasuk layar putar).
  - Temuan lain sepanjang pengerjaan yang sudah diperbaiki: balon narasi Peta Kedu terpotong (kini `.stage-scroll`), judul bank kata terhitung meluap (kini `.visually-hidden`), pembanding posisi tidak membedakan pengerjaan dengan variasi acak berbeda.
- **Belum dijalankan:** uji manual §12.3 di laptop, tablet, dan ponsel sungguhan, serta uji pembaca layar.

---

## QA PR #42 dan data simulasi

Branch `qa-simulasi`, dikerjakan 7–8 Oktober 2026. `gelita:simulasi` dan `SimulationTest` sudah masuk `main` lewat PR #46. Uji peramban, perbaikan `check_no`, dan dokumentasi menyusul di PR berikutnya dari branch yang sama, yang dibuat ulang dari `main`.

### Lingkungan uji

- MariaDB 10.11 lokal dengan instalasi baru menurut `docs/08_DEPLOYMENT.md`: akun `gelita_app`/`gelita_migrate`, `migrate`, `db:seed DatabaseSeeder`, lalu `gelita:narration:import`.
- Aplikasi dijalankan dengan `PHP_CLI_SERVER_WORKERS=4 php spark serve`. Tanpa variabel itu server pengembangan PHP hanya satu proses, sehingga request tidak pernah bersamaan.
- Peramban: Chromium dari `/opt/pw-browsers` lewat Playwright 1.56.

### Uji peramban PR #42 (`tools/qa/challenge-races.mjs`)

```bash
PHP_CLI_SERVER_WORKERS=4 php spark serve
PLAYWRIGHT_BROWSERS_PATH=/opt/pw-browsers node tools/qa/challenge-races.mjs            # semua
PLAYWRIGHT_BROWSERS_PATH=/opt/pw-browsers node tools/qa/challenge-races.mjs --only=race # freeze, timing, cari, reload, race
```

Skrip mendaftarkan siswa baru lewat formulir 3 langkah untuk setiap kelompok uji. Ia membaca `item_tries` dan `game_event_logs` lewat `QA_MYSQL` (bawaan `mysql -uroot gelita`). Selama uji, `unlock_mode` studi aktif disetel `free` agar tantangan mana pun bisa dibuka; nilainya dipulihkan di akhir. Respons diperlambat dengan menahan request di `page.route` sampai pemeriksaan selesai. Hasil terakhir: **54/54 pemeriksaan lolos** di MariaDB, sesudah kedua perbaikan di bawah.

1. **Isian beku selama "Cek jawabanku"** — lolos.
   - Susun Gambar: ketuk dua keping dan seret keping selama `/check` tidak mengubah urutan.
   - Isi Rumpang: ketuk kotak terisi dan chip kata tidak mengubah isian.
   - Benar atau Salah: semua radio nonaktif, dan klik serta tombol panah diabaikan.
   - Di ketiganya, jawaban di layar sesudah hasil sama dengan `item_tries.answer_json` yang dinilai server, dan jawaban terkirim sama dengan layar sebelum cek.
2. **Waktu isian** — lolos. `first_input_ms`/`last_input_ms` terkirim untuk setiap soal yang disentuh, dan tersimpan di `item_tries` persis sama, dengan `first ≤ last`.
3. **Cari Objek** — lolos.
   - Tombol lompat petunjuk yang diketuk selama ketukan menunggu server diabaikan; teks petunjuk tetap.
   - Request membawa `item_id` petunjuk yang aktif saat diketuk, dan `item_tries` mencatatnya ke petunjuk itu.
   - Sesudah respons, lompat berfungsi lagi.
4. **Muat ulang tanpa `challenge_abandoned` palsu** — lolos.
   - Respons tiruan `TRIES_EXHAUSTED`, `ATTEMPT_CHANGED`, dan `ATTEMPT_CLOSED` pada `/check`, serta `CLUE_CLOSED` dan `ATTEMPT_CHANGED` pada `/responses`: halaman dimuat ulang tanpa event.
   - Dua tab sungguhan: tab B menghabiskan kesempatan, lalu cek tab A dijawab `TRIES_EXHAUSTED` + `redirect`. Tab A pindah ke layar hasil tanpa event.
   - Kontrol: menutup tab di tengah tantangan tetap mencatat `challenge_abandoned` (`pagehide`).
5. **Dua tab atau perangkat mengirim cek bersamaan** — lolos, 5 putaran.
   - Dua perangkat (dua konteks peramban, dua session PHP, siswa yang sama): request ditahan lalu dilepas serentak. Pada cek ke-1, satu berlaku dan yang lain `ATTEMPT_CHANGED`. Pada cek ke-3, satu menutup dan yang lain `TRIES_EXHAUSTED`.
   - Database: `MAX(try_no) = 3`, satu `challenge_checked` per `check_no`, dan satu `challenge_completed`.

### Temuan dan perbaikan

Sesi panggung (PR #48) sudah digabung sebelum perbaikan ini, jadi `challenge.js` boleh diubah di sini.

1. **Cek dari tab lama dihitung sebagai cek baru.**
   - Penyebab: dua tab di peramban yang sama memakai satu session PHP. Handler session MySQLi mengunci session (`GET_LOCK`), jadi request kedua tidak pernah bersamaan dengan yang pertama. Ia tiba sesudah cek tab lain di-commit, dan UPDATE bersyarat PR #42 menerimanya sebagai cek berikutnya dengan jawaban dari tampilan usang.
   - Data tidak rusak (tidak ada `try_no` 4 maupun penutupan ganda), tetapi siswa kehilangan satu kesempatan tanpa melihat hasil cek sebelumnya. Kasus kiriman ulang berurutan di butir 1 "Perbaikan sebelum Dok 3" sama.
   - Perbaikan server: `/check` menerima `check_no` opsional, yaitu nomor cek menurut layar klien. Bila tidak sama dengan `check_count + 1`, request dibalas `409 ATTEMPT_CHANGED` tanpa tulisan apa pun (`ChallengeService::submitCheck()`, uji `TryLimitTest::testStaleCheckNumberIsRejected`). Tanpa `check_no`, perilakunya sama seperti sebelumnya.
   - Perbaikan klien: `challenge.js` mengirim `check_no = checksUsed + 1`. `checksUsed` diambil dari `tries_used`, lalu dari `check_count` respons terakhir.
   - Hasil uji peramban: dua tab → tab lama `ATTEMPT_CHANGED` lalu memuat ulang.
2. **Menutup tab saat modal hasil akhir masih tampil menulis `challenge_abandoned`.**
   - Modal "Kesempatanmu sudah habis" atau "Hebat!" tampil sebelum `finish()` menyetel `state.completed`. Menutup tab pada saat itu mengirim `challenge_abandoned {via: pagehide, attempt_open: true}` untuk pengerjaan yang sudah `completed`.
   - Perbaikan: `check()` dan `submit()` menyetel `state.leaving` begitu respons membawa `completed`.
   - Hasil uji peramban: 0 event.

### Data simulasi (`gelita:simulasi`)

Rincian pemakaian ada di FITUR 19 `docs/07_FEATURE_INTEGRATION.md`. Keputusan:

- **Perintah spark, bukan seeder.**
  - Opsinya (`--siswa`, `--seed`, `--mulai`) dan penolakannya cocok dengan pola perintah GELITA lain (`CliOptions`, `db_sync_timezone()`).
  - Seeder `DatabaseSeeder` dipakai untuk instalasi baru, padahal simulasi tidak boleh ikut ke sana.
- **Layanan, bukan controller.**
  - Simulasi memanggil layanan yang dipanggil controller, dengan input yang disusun seperti controller.
  - Event yang ditulis controller atau filter (`screen_viewed`, `intro_completed`, `library_opened`, `feedback_submitted`) dan baris `participant_feedback` ditulis dengan urutan panggilan yang sama, karena tidak ada layanannya.
  - `SessionService` membutuhkan session PHP. Di CLI dipakai session PHP bawaan tanpa cookie di folder sementara, dimulai lagi sesudah `logout()`.
- **Jam simulasi (`SimClock`).**
  - Setiap langkah dijalankan pada jam saat ini. Sesudahnya, kolom waktu baris milik siswa itu yang nilainya ≥ penanda awal langkah digeser ke jam simulasi. Baris langkah sebelumnya sudah berada di masa lalu, jadi tidak tersentuh.
  - Penanda diambil dari waktu lokal maupun UTC (CURRENT_TIMESTAMP SQLite berzona UTC).
  - Event server dalam satu langkah diberi jarak 20 ms agar urutan `occurred_at` sama dengan urutan tulis.
  - Hanya `duration_ms` pengerjaan dan sesi yang dihitung ulang dari jam simulasi. Ini satu-satunya UPDATE di luar layanan, dan dicatat di FITUR 19.
- **Variasi soal berbenih.**
  - `ChallengeItemModel::pickVariant()` kini memanggil `randomIndex()` (bawaan `random_int`, perilaku produksi tidak berubah).
  - `SeededItemModel` menimpanya, disuntikkan lewat `Factories::injectMock`, lalu `Factories::reset('models')` di akhir.
  - Uji `testSameSeedGivesSameData` membandingkan sidik isi dua kali jalan, dan dicek juga di MariaDB.
- **"Studi aktif berstatus berjalan" ditafsirkan sebagai studi aktif yang sudah berisi sesi milik siswa selain `sim_*`.**
  - Status studi hanya `draft`/`active`/`closed`, dan pendaftaran membutuhkan studi `active`. Kalau perintah menolak setiap studi `active`, ia tidak bisa berjalan sama sekali.
  - Yang dijaga adalah data simulasi tidak bercampur dengan data penelitian.
  - Confirmed by the owner on 9 October 2026.
- **Nama pengguna `sim_NNN`, bukan `sim-NNN`.** Aturan `valid_username` hanya menerima `[a-z0-9._]`.
- **Sekolah lewat nama ketikan.**
  - Siswa Jawa Tengah mencentang "Sekolahku tidak ada di daftar". Hasilnya entri sekolah "belum terverifikasi" bernama "Sekolah Simulasi …" sehingga mudah dikenali; siswa simulasi tidak menempel ke sekolah resmi (NPSN).
  - Satu sekolah DIY menguji jalur provinsi tanpa direktori.
- **Data yang tidak terisi di dunia uji SQLite.** `GradeFiveWorld` tidak berisi dialog dan Pustaka, jadi `dialogue_advanced`/`library_page_viewed` hanya diperiksa di MariaDB.
- **Kinerja.** 40 siswa di MariaDB lokal ±1 menit (±390 pengerjaan, ±10.000 event).

### Pengujian

- `vendor/bin/phpunit --no-coverage`: **482 test hijau**, termasuk 6 `SimulationTest` dan 1 baru di `TryLimitTest`.
- `tools/qa/challenge-races.mjs` di MariaDB 10.11: 54/54.
- `gelita:simulasi --siswa=40` di MariaDB:
  - tidak ada kolom waktu siswa simulasi yang tersisa di jam dinding;
  - `answer_selected` jatuh di antara pembukaan dan cek;
  - dua kali jalan dengan `--seed=7` menghasilkan sidik isi yang sama.

---

## Dok 3 — documentation (§11)

Done on 9 October 2026 by three parallel sessions, one per file group, each auditing the docs against the code on `main` (code is the source of truth, not the plan document): PR #54 (`README.md`, `docs/01`, `02`, `08`, status notes in `rencana-perbaikan-tampilan.md` and `rencana-katalog-aset.md`), PR #53 (`docs/03`, `04`, `07`), PR #52 (`docs/05`, `06`, `bank-soal/README.md`, `naskah-cerita.md`, `audio.md`). Docs only; no code changed. Suite: **487 tests green** on the three branches merged together.

### How it was checked

- Routes in `docs/04` compared by script with `app/Config/Routes.php` (126 routes, matches `php spark routes`).
- Column lists of all 13 export sheets in `docs/07` compared with `RawDataService::COLUMNS`.
- Columns in `docs/01` compared with all 29 migrations and `ResetTables`.
- Every backticked class, method, path, lang key, CSS class, and `data-*` attribute in `docs/03`–`06` resolves to existing code.
- Counts (tests, tables, migrations, bank items, recordings) measured, not copied.

### Deviations from the §11 table

1. **`docs/08_DEPLOYMENT.md`:** the §13 upgrade procedure does not apply (release = fresh install). Only the post-install panel steps were completed: approve narration → replace 45 images → research-ready checklist → smoke test → `gelita:reset`.
2. **`docs/01_DATABASE.md`:** the row names migrations `004100`–`004600`, which no longer exist (the chain was consolidated to `000100`–`002900` in the cleanup). Dok 3 added no migrations. The derived-metrics section was already gone; a "Data mentah" subsection maps each `RawDataService` dimension to its source table and admin tab.
3. **`README.md`:** no "Pembaruan …" history block was left, so no "Riwayat pembaruan" section was added.
4. **Language:** edits inside the existing Indonesian docs are in Indonesian to keep each document consistent; one wholly new section ("Services and libraries index" in `docs/03`) is in English. `docs/02` §8 "Bahasa" now follows `.claude/rules/language.md`.

### Code issues found (follow-up)

Found during the audit and fixed in separate branches (see `docs/PROGRESS.md`):

1. The student detail Jawaban tab shows only the first 25 attempt rows: `ParticipantController::participantRaw()` asks `RawDataService::page()` for 10 000 rows, which only accepts 25/50/100. Exports are not affected.
2. Teachers can confirm a hidden username through the student-list search (`q` matches `p.username`).
3. List-table CSV downloads (Siswa, Sesi, linimasa, Kritik & saran) write no audit row, unlike the Data mentah CSV (`export_csv`).
4. Dead code: `ParticipantModel::cohortSummary()`, `ParticipantFeedbackModel::forStudy()`, `ratingDistribution()`.
5. Admin messages still name the old menu "Gambar & suara" (`ContentVerifier`, `ContentImportService`, `BankWorkbookGuide`, `admin/content/import.php`); the menu is "Gambar & mockup".
6. `layouts/admin.php` hardcodes Indonesian text instead of `lang('Admin.*')`; `layouts/auth.php` has no skip link.
7. `reflection.php` and `challenge-finished.php` request Mbah Kedu pose `happy`; the pose is `smile`.
8. `engines/boleh.js` sends `overlay_opened` (`statement_image`) without the region `level` code.
9. A `stage.css` comment gives the content area as "0, 56, 1280 × 600" while `.stage-main` is `inset: 0`.

### Follow-up: items 1–4 (branch `fix-admin-data`)

- **Item 1.** New `RawDataService::all($dimension, $filters)` returns every row in the bound scope (`{rows, columns}`, keyset iteration via `rows()`); the Jawaban tab uses it for the `percobaan` history instead of `page(…, 10000)`. The history is now ordered by try id (oldest first), which reads as a sequence; before it was newest first.
- **Item 2.** The student-list search `q` matches `participant_code` for everyone and `username`/`display_name` for admins only; the teacher placeholder is now "kode siswa". `docs/08` troubleshooting: a forgotten username is looked up by an admin, since teachers see neither name nor username.
- **Item 3.** List-table CSVs go through `BaseAdminController::tableCsv()`, which writes audit action `list_csv` (`target_type` `admin_table`, `target_id` = file name such as `siswa` or `linimasa-12`, metadata = scoped filters, sort, row count). A new code rather than reusing `export_csv`, so existing audit rows keep their meaning. The search text is logged only as `search: true/false`, because it may hold a student's name.
- **Item 4.** `ParticipantModel::cohortSummary()` and `ParticipantFeedbackModel::forStudy()` / `ratingDistribution()` removed with their private helpers; `docs/03` updated.

### Follow-up: items 5–6 (branch `fix-admin-copy`)

- **Item 5.** Fixed in PR #60 (`fix-menu-gambar-mockup`), which also rebuilt both workbooks; this branch took that version and only adds the same rename in `rencana-katalog-aset.md`.
- **Item 6.** `layouts/admin.php` reads its text from `Admin.*` (`skipToContent`, `breadcrumbLabel`, `activeStudyTitle`, `panelTitle`). The quick search is role-aware: admins get `quickSearch` ("Cari kode, nama pengguna, atau nama siswa"), teachers `quickSearchTeacher` ("Cari kode siswa"), matching what each role can search (item 2). `layouts/auth.php` has a skip link to `#auth-main`.

### Follow-up: items 7–9 (PR #58)

- **Item 7.** `reflection.php` and `challenge-finished.php` request Mbah Kedu pose `smile` (`Config\Gelita::$characterPoses`); the `components/character` docblock points to that list. `ChallengeFrameTest::testViewsRequestOnlyKnownPoses` checks every literal pose in `app/Views`.
- **Item 8.** The challenge context exposes `ctx.levelCode` (from `data-level`); `engines/boleh.js` sends it as `level` with `statement_image`, like every other `overlay_opened`. `docs/07` needed no change: its `overlay_opened` row already lists `level` for every overlay.
- **Item 9.** The `stage.css` comment says `<main>` covers the whole stage. Comment only, no rule changed.

### Duplicates closed

Two documentation sessions kept running after their PRs merged and reopened audit items on their own: PR #60 (item 5, same change as #57; merged first, so #57 kept #60's version) and PR #61 (items 1–4, duplicate of #56; closed). Both sessions were archived, as were the three fix sessions once their PRs merged.

**Owner decision (9 October 2026): option (a).** The default study stays seeded (and re-seeded by `gelita:reset`) as `active`, so the `ResearchReadiness` gate in `StudyController` only blocks switching a study to active, not data collection on a standard install. The admin checks the research-ready list by hand before data collection, as `docs/08` describes.

---

## Dok 3 — tests and done criteria (§12)

Branch `dok3-s12-uji-manual` (from `main` after PR #55), 9 October 2026. Scope: the parts of §12.3 that can run in a container, and the §12.4 evidence. §12.1 (unit tests) and §12.2 (`tools/qa/stage-check.mjs`) were already done by the stage, admin, and raw-data sessions.

### Environment

MariaDB 10.11 in the container, test database `gelita_uji`: `migrate --all`, `db:seed DatabaseSeeder`, `gelita:reset --ya`, `gelita:simulasi --siswa=30` (4 simulated schools), study unlock mode `free`. `php spark serve`, Chromium via Playwright 1.56. Nothing ran against production.

### Keyboard and screen reader (`tools/qa/a11y-check.mjs`, new)

A new Playwright script opens all 12 challenges (4 engines × 3 regions) with a fresh student and checks, with the keyboard only: Tab reaches every blank and word, verdict group, puzzle piece, hunt object, "Cek jawabanku", and the hint button; every stop has an accessible name and a visible focus ring; Enter/Space/arrows operate each engine; the hint and result dialogs take focus and are labelled; Escape closes the hint dialog and returns focus to its button; the Cari Objek clue is `aria-live`. With `--axe=…/axe.min.js` it also runs axe-core 4.10.2 (WCAG 2.1 A/AA) on `#stage`.

Result: **87 checks pass, 0 failures**, and axe reports no violations of any impact on the 12 screens. Two script details: puzzle pieces show focus as a gold edge and a `drop-shadow` on the artwork (`game.css`, `.piece:focus-visible`) rather than an outline, so the script accepts that glow as a focus ring; and each focus stop is tracked by element, because repeated controls (one passage chip per card) look alike.

The hints of Isi Rumpang and Benar atau Salah open in a dialog that takes focus, so they need no `aria-live` region; the plan's question about it is settled. A real screen-reader pass (NVDA/VoiceOver) remains for the owner.

### Exports opened in other software

Six exports were made through the panel: admin (identity on) XLSX berlabel and berkode, CSV-ZIP berkode and berlabel; guru (Temanggung school, anonymous by force) XLSX and CSV-ZIP. Each was opened as follows:

- **LibreOffice 24.2** (headless, every sheet to CSV) and **pandas** `read_excel`: the same 14 sheets (admin) / 13 sheets (guru, no Event Mentah) with identical shapes in both.
- **pandas** `read_csv` and **R 4.3** `read.csv` on every CSV: same row and column counts as the XLSX; coded and labelled CSV have equal shapes; every column of every sheet has its Kamus Data row (by code, or by title in the labelled export).
- **Guru:** no `event` sheet, no `display_name`/`username`/`school_name`/`school_npsn`, and no `answer_key` (Bank Soal has 16 columns instead of 17). **Admin with "Anonim" unticked:** identity columns present.
- Timestamps parse as `Asia/Jakarta` in R.

One finding, documented rather than changed: in a non-UTF-8 locale (`C`/`POSIX`, as on a bare Linux server) base R's `read.csv()` without `fileEncoding` stops at the first non-ASCII character and silently returns fewer rows (e.g. 24 of 232 Kamus rows). In a UTF-8 locale (Windows R 4.2+, macOS, Linux desktops) it reads everything and strips the BOM. `docs/07` now gives `read.csv(…, fileEncoding = "UTF-8-BOM")` or `readr::read_csv()`.

### Real devices

`docs/perombakan-kelas5/device-test-checklist.md` lists what only the owner can do: one full region on a laptop, tablet, and landscape phone; one NVDA/VoiceOver pass; opening an export in their own Excel/LibreOffice and R.

### Re-run after the §11 audit fixes

`main` after PR #63, 9 October 2026, same environment as above (fresh `gelita_uji`, `gelita:reset --ya`, `gelita:simulasi --siswa=30`, unlock mode `free`), `PHP_CLI_SERVER_WORKERS=8 php spark serve`, Chromium via Playwright 1.56.

- **Stage (`tools/qa/stage-check.mjs`):** all six sizes, every check passes. 44 screens per size (all §4 screens, the four challenges in the three regions, result modal, Kenali); on the first size of each run also the 12 finished screens with tries used up, results, region complete, Pustaka + lightbox, closing, and reflection (61 screens). 390 × 844 touch shows the rotate screen. The sizes ran in parallel runs: 1280 × 720 and 1366 × 657; 1024 × 768 and 844 × 390; 1920 × 1080 and 2560 × 1080 each in a fresh run of its own (the first pass at those two sizes overlapped seven seconds in which the working tree briefly held an old checkout, so it is not counted).
- **Admin (`tools/qa/admin-check.mjs`, new):** 1004 checks pass, 0 fail. 48 admin pages as admin and 26 data pages as a teacher, at 1280, 1440, and 1920 px: HTTP 200, no console errors or failed requests, no horizontal page scroll, skip link present. Audit items: the Jawaban tab lists all 89 of 89 tries of the student with the most tries (item 1); a teacher's name search finds nothing and a code search finds the student (item 2); each list CSV (Siswa, Sesi, timeline on the session page, Kritik & saran) writes one `list_csv` row (item 3); the quick search text matches the role (item 6). Content pages stay admin only.
- **Finding, fixed in PR #63:** the first attempt to run two stage runs at once failed at registration step 3. Two students who finished sign-up at the same moment got the same `participant_code`, because `ParticipantModel::generateCode()` read `MAX(id) + 1` before the INSERT; the second sign-up hit the unique key and saw HTTP 500 (5 of 10 parallel sign-ups on MariaDB). The row is now inserted with a placeholder and gets `GLT-%06d` from its own AUTO_INCREMENT id in the same transaction (`placeholderCode()`, `assignCode()`); 20 of 20 parallel sign-ups pass. Test: `RegisterWizardTest::testRegistrationCommittedAlongsideGetsItsOwnCode`.

### §12.4 done criteria

| Criterion | Evidence | State |
|---|---|---|
| 16:9 with frame at every test size, no page scroll, scroll only in panels | `stage-check.mjs` 8 October (decision log, Dok 3 stage session); re-run after the §11 audit fixes, all six sizes pass (above) | done |
| No Jaka on challenge and finished screens; content full width and centred | `ChallengeFrameTest`; `stage-check.mjs` | done |
| Pustaka and Kenali show the same content as before | content comes from the same database tables, unchanged by Dok 3; screens visited by `stage-check.mjs` | owner device pass |
| Neutral SaaS admin, formal tables, no charts; Data mentah 7 dimensions + Perangkat; CSV per table | PR #47 browser pass at 1280/1440/1920; re-run with `admin-check.mjs` after the admin audit fixes, 1004 checks pass (above) | done |
| XLSX/CSV-ZIP per §9 with Kamus Data | `RawExportTest`; this pass (LibreOffice, pandas, R) | done |
| §11 docs updated; all tests green | PR #52–#54; `vendor/bin/phpunit --no-coverage` 493 tests green after PR #63 | done |

## Account screens redesign (owner mockup, 9 October 2026)

Branch `sky/cool-pascal-2bsc2w`. The owner sent a mockup of `/mulai` and asked for three changes on top of it.

- **`/mulai` layout.** Jaka stands on the left as his own image (`component('character')`, pose `happy`; the owner chose a separate image over Jaka painted into the background), next to one dark panel with gold ornaments (`.ornate-panel`, `partials/ornate-frame.php`: corners, compass crest, divider). The two choices are row cards (icon, title and text, chevron); "Saya baru" is the primary card. The ornaments are inline SVG and CSS, so no new media slot is needed. The background stays `bg.welcome` (its media label already covers "sambutan / mulai"); the mockup art goes into that slot without Jaka and without UI.
- **`/gerbang`** shares the `.start-*` classes, so it takes the same layout (my call for one consistent look; the owner did not ask otherwise). "Langsung ke peta" is its primary card. It keeps its nav bar and the logged-in HUD.
- **HUD has no band.** The background gradient and bottom border of `.hud` are gone everywhere (owner request: "hilangkan semua background dan border bottom di hud"). A text shadow keeps HUD text readable over bright art.
- **Back in the top-left, logo centred** on the registration pages. A new HUD section `hudBack` (URL) replaces the left side with a Back button and puts `ui.logo` (falling back to the text brand) in the centre. Used by `/daftar/1…3` (Back → `/mulai`, `/daftar/1`, `/daftar/2`) and by `/mulai` (Back → `/`, which also drops its nav bar, matching the mockup). The Back links inside the registration panels are removed; the panel's button bar holds only Lanjut/Daftar on the right.
- New icons `user-plus` and `login` in `icon()`. No language keys change; the mockup uses the existing copy.
- Not changed in PR #65: `/masuk` kept its parchment panel and nav bar (done in the follow-up below).

### Follow-up: `/masuk` and the 16:9 rules (PR after #65)

Owner request: give `/masuk` the same design and make sure the new design follows the fixed 16:9 stage rules (Dok 3 §1–§4).

- `/masuk` now matches `/mulai`: Jaka (pose `bow`) at 140, 196, 280 × 460, the ornate panel at 432, 80, 560 × 576, Back to `/mulai` in the HUD with the logo centred, no nav bar. The parchment look is gone from this screen; inputs use the dark default style, and the show-password button and Caps Lock warning get dark-panel colours.
- The ornate panel is now a fixed frame with a scrolling body (`.ornate-body.stage-scroll`) on all three screens. Before, a long name on `/gerbang` (up to 150 characters) or a longer English text could push content out of the panel, which the stage rules forbid; now it scrolls inside the frame. Content is centred while it fits and starts at the top when it overflows (`margin: auto` on the first and last child, `flex-shrink: 0` on every item).
- The login panel is 16 dp taller than the `/mulai` panel and its rows sit 8 dp apart so the form with a one-line error still fits without scrolling; a two-line error (`Auth.locked`) scrolls.
- Checked with the `inspect()` of `tools/qa/stage-check.mjs` on rendered pages (no database server here): `/mulai` (id, en), `/daftar/1`–`3`, `/masuk` (empty, with error, en), and `/gerbang` with a 62-character name, at all six sizes. Every check passes: 16:9 and centred, no page scroll, nothing outside the stage, overflow only in `.stage-scroll`, the same relative positions at every size, and 48 dp tap targets for the new HUD Back and panel controls. The existing 40 dp HUD icon buttons are unchanged.

## Display fixes on /mulai, /masuk, /daftar (9 October 2026)

Branch `sky/confident-noether-id40gg`. Owner report: there are still display bugs on the login, sign-up, and start pages. Checked in Chromium on a local MariaDB install (`/mulai`, `/masuk` empty and with an error, `/daftar/1`–`3` empty and with errors, id and en, with stand-in art for Jaka, the background, and the logo) at all six stage sizes; the stage rules (`inspect()` of `tools/qa/stage-check.mjs`) pass everywhere, so the bugs were visual:

- **`/masuk`: the compass crest touched the HUD logo.** The crest sits half above the panel top (36 dp), so with the panel at y 80 it reached y 44, inside the 56 dp HUD, right under the centred logo. The panel moves to y 96 (same 560 × 576, bottom at 672).
- **`/daftar/3`: the form started below the visible area.** The "Mengapa kata sandi harus kuat?" box (Jaka 300 dp tall, tips in one column) filled the whole scrolling area, so the student saw no field and had to guess to scroll. The box is now compact (Jaka column 112 dp, caption-size text, tips in two columns); Nama pengguna and its input show without scrolling. Jaka stays in the box (Dok 1 allows it).
- **Error messages named the raw field.** Server rules had no labels, so any message without its own text read "display_name wajib diisi." (a name of only spaces passes the browser's `required` and is empty after trimming) or "Provinsi wajib diisi bila country_other tidak ada.". `RegisterController::rulesFor()` now gives every field its on-screen label, and province/district get the plain "{field} wajib diisi.". Test: `RegisterWizardTest::testErrorsNameTheFieldAsShownOnScreen`.
- **`/daftar/1–3` in the dark design** (owner decision, 10 October 2026, answering the question in PR #73). Same layout as `/mulai` and `/masuk`: Jaka on the left at the same spot (pose `happy` on steps 1–2, `bow` on step 3), the ornate panel at 432, 96, 784 × 576 (was the parchment panel at 200, 72, 880 × 576). Inside the panel: the head (title and stepper) 64 dp below the crest, the scrolling fields, and the 80 dp button bar; head and button bar are padded 32 dp so the corner ornaments stay clear. The stepper text drops to caption size so "Sekolah dan daerah" stays on one line. Jaka is no longer drawn inside the step 3 tips box, because he stands next to the panel (Dok 1 allowed him there but did not require it).
  - Choice pills, the school card, the tips box, and the stepper are restyled for the dark panel (gold borders, gold-tinted checked state). The password field is shared with the parchment `/ganti-sandi`, so its dark colours are scoped to `.ornate-panel` (labels, show-password button, Caps Lock, strength and match texts in the bright `--ok`/`--warn`/`--bad` instead of the parchment inks); the `/masuk` rules moved to the same scope.
  - The panel's fields area is 44 dp shorter than before, and in a province with a school directory the "Benar ini sekolahmu?" card (or the "Sekolahku tidak ada di daftar" box) appeared below the visible area. `register.js` now scrolls the fields down just enough to show it once the lookup answers (smooth unless reduced motion).
  - Checked like the fixes above: every stage rule passes at all six sizes for every step, empty and with errors, id and en, and the three school card states (SD found, MTs refused, unknown NPSN).

## Replacing a real image showed no change (9 October 2026)

Branch `sky/bold-archimedes-6tjayk`. Owner report: on `/admin/media`, a slot that already has a real image does not change when another image is uploaded.

- **Cause.** MediaStore saves every upload under the official name `assets/uploads/{asset_key}.{ext}`. A mockup is `.svg`, so its first real image (`.png`/`.jpg`) gets a new URL and shows. Replacing a real PNG with another PNG keeps the exact same URL, so browsers (and the admin thumbnail) keep the cached old file. The database row, the file on disk, and the content cache were already updated correctly.
- **Fix.** Media URLs now carry `?v=` from the first 12 characters of the stored `sha256` (`MediaStore::versionedPath()`): `MediaAssetModel::map()`/`activeKeyMap()` (so `media_src`, `media_key_src`, character frames, library media), `audio_src`, the admin thumbnails (`/admin/media`, the editors' media field), and the audio previews (`/admin/media/audio`, `/admin/narasi`). Rows without a hash keep the plain URL. `ContentRepository` cache keys are renamed (`media_urls`, `media_key_urls`) so unversioned maps cached before the deploy are not served for another hour. `ContentVerifier` strips the query before checking the file on disk.
- **nginx.** `?v=` URLs are already served `immutable`; that is right here because the version changes with the content. The comment in both `gelita-https.conf` files is updated.
- Test: `tests/unit/MediaReplaceTest.php`.

## Readable answers on the raw data tables (9 October 2026)

Branch `sky/amazing-mendel-wuy2p7`. Owner request: on Data mentah, the columns Jawaban pertama, Jawaban akhir, and Kunci jawaban show JSON; staff should see something easier to read, or be able to choose.

- **What was already there.** The full export (`/admin/ekspor`) already has the choice: Berlabel writes answers as sentences through `AnswerText`, Berkode keeps the stored JSON, and Excel defaults to Berlabel. Only the screen tables (`/admin/data/jawaban`, `/admin/data/percobaan`, the student page's Jawaban tab with its try history) and their per-table CSV always showed JSON.
- **Decision.** Readable by default, JSON one click away. The tables get a switch "Tampilan jawaban: Mudah dibaca | Asli (JSON)" (`components/answer-view-switch`, plain links, `?raw=1`). Only the answer columns change (`RawDataService::ANSWER_COLUMNS`); codes, times, and 1/0 stay as stored, so the table is still raw data. The table's Unduh CSV follows the switch, so the CSV still equals the table on screen; the audit row records which (`answers: text|json`). Researchers who need JSON for analysis use Asli (JSON) or the Berkode export, whose CSV default is unchanged.
- **Code.** `RawDataService::page()`, `streamCsv()`, `all()`, `rows()`, and `shape()` take `$readableAnswers`; it reuses the answer branch of the labelled mode without labelling anything else. In that mode the answer columns are typed `text`, not `json`, so they render as normal text instead of the monospace JSON cell. `BaseAdminController::readableAnswers()` reads the query. The export page's Berlabel/Berkode hints now say how answers are written.
- Tests: `AdminTableTest::testReadableAnswersChangeOnlyTheAnswerColumns`, `ParticipantPanelTest::testRawDataTablesShowReadableAnswersWithASwitchToJson`.

## Asset package: owner decisions (9 October 2026)

Branch `sky/optimistic-hopper-eqqcbm`. The owner answered the open questions of `docs/rencana-paket-aset.md` §12. The plan is updated; no code changes yet.

- **All recommendations P1–P10 accepted** as written in the plan. P1 (whether Stage 7 is needed) is still decided after the Stage 0 and Stage 4 measurements.
- **P11 changed: full download is mandatory before Mulai.** This is a fixed owner decision. There is no "core first, rest in the background" path and no partial package: `package_mode = sebagian` is gone, the per-region download curtain is gone, and the targets S6–S8 are redefined (full package ≤ 7 min for ≤ 40 MB on the reference network, so the image size budget becomes a requirement).
- **P12 new: admin switch for package mode** (plan §6.3). The admin can turn package mode on and off from Rilis → Paket aset, so the feature can ship while images and audio still change. On = build a package from every active asset and activate a release with it; Off = activate a release without a package, and the game loads assets from the network as today. Stored as the active release's `asset_version` (no new settings table), so the existing audit log and running-study lock apply. Text and questions are never packaged, so they update immediately either way; image and audio changes need **Bangun ulang paket**, prompted by a "changed assets" notice. Sessions record `package_mode = nonaktif` while the switch is off. The `.env` flag stays as an emergency kill switch.
- **My interpretation of P7 + P11** (plan §8.7): online mode is automatic only where the device cannot store the package (no service worker, private mode, not enough storage). Lab profiles that are wiped on logout must download like any device, unless the admin turns on "Izinkan mode daring untuk perangkat lab". The owner can tighten or loosen this.
- Fixed while editing: the plan mixed "Stage 5" and "Stage 7" for in-stage navigation; it is Stage 7 throughout. §2.7 now notes that media URLs carry `?v=` from the content hash since the media fix of the same day.
- Effort estimate for Stages 0–6 goes from 18–27 to 20–30 person-days.

## HUD controls without a frame (9 October 2026)

Branch `sky/beautiful-ritchie-rd2s1t`. Owner request: "hapus background dan border hud di semua halaman game, teks yang ada di hud jika keterbacaan kurang, tambahkan background".

- **No frame.** The HUD band was already gone; now its controls lose their pill background and border too: the sound, fullscreen, home, and logout buttons (`.hud .icon-btn`), the lantern toggle, the student chip, and the language switch. Icons and the lantern flame carry a drop shadow instead; hover enlarges the icon with a gold glow. The flame keeps its glow halo and the avatar keeps its gold disc: they are the lantern's light and the student's initial, not frames. The GELITA logo image on the account screens is unchanged.
- **Backing behind text only.** Text cannot be guaranteed readable over owner-replaceable art, so every HUD text gets one dark, borderless backing, `--hud-backing` (`rgba(11, 19, 32, .75)`): the centre title (only when it has text), the brand, the language switch, the Back button, the name and `@username` (`.user-text`), and the shard count with its bar. The opacity is chosen so `--ink-dim` on the backing over pure white art is still ≥ 4.5:1 (about 4.8:1); hover uses `--hud-backing-hover`.
- **Markup.** `components/lantern.php` wraps the count and the bar in `.lantern-meter` so they share one backing while the flame floats free. `hud.js` selectors (`.lantern-count b`, `.lantern-bar i`) are unchanged.
- Checked by rendering the real `components/hud` (logged in, and with `hudBack`) over white, sky, gold, and dark art in Chromium. Tap targets are unchanged (40 dp icon buttons, 48 dp chip, lantern, and Back).

## Asset package — Stage 0: baseline timing (October 2026)

Branch `paket-0-ukur-dasar` (from `main` after PR #71), 9 October 2026. Scope: `docs/rencana-paket-aset.md` §10 row "0. Ukur dasar" and §11.3. The script `tools/qa/nav-timing.mjs` measures screen switches and language switches on the reference network. The numbers below are the baseline that Stages 1, 1b, 4, and 5 are compared against. No app code changed.

**How to run.** From a fresh development setup (README §Instalasi: `composer install`, `.env`, `migrate`, `db:seed DatabaseSeeder`, `gelita:narration:import` with the narration approved) plus `apt install nginx php8.3-fpm`:

```bash
node tools/qa/nav-timing.mjs --serve --runs=20
```

- `--serve` starts a private nginx + php-fpm built at run time from `deploy/linux/nginx/gelita-https.conf` on `https://localhost:8443/`: HTTPS + HTTP/2, the production cache headers (`?v=` → immutable, everything else `no-cache` → 304), gzip, and `CI_ENVIRONMENT=production` (no debug toolbar). A generated `auto_prepend_file` sets the base URL and environment before `.env` loads, so `.env` is not touched. Everything lives in `writable/qa/nav/server/` and stops with the script. `php spark serve` is not comparable: single process, HTTP/1.1, no ETag/Last-Modified, so a warm visit downloads everything again.
- Chromium (Playwright), viewport 1280 × 720, throttled through CDP `Network.emulateNetworkConditions` to 1 Mbps down, 1 Mbps up, 150 ms latency. Service workers are blocked: `sw.js` only handles navigations as a pass-through, and requests it makes would escape the throttling.
- The script registers a test student, plays every challenge with throwaway answers until the tries run out (as `stage-check.mjs`), and registers a second student for the locked library. Development database only.
- Output: `writable/qa/nav/nav-{time}.json` (every sample with its request list) and `nav-{time}-summary.json` (p50/p95), plus a table on stdout. Ignored by git (`/writable/qa/`).

**What is measured.** Per screen of Dok 3 §4 (region Temanggung for per-region screens), 20 runs each:

- *cold*: HTTP cache cleared just before the navigation (`Network.clearBrowserCache`), i.e. the first screen on a fresh device; *warm*: the same screen again right after, every asset in the HTTP cache.
- *ready*: navigation start → the page module is initialised (`body[data-page-ready]`) and no curtain covers the stage, or the stage waits for a "tap to start" card (`gate` column). This is the "first meaningful paint of the stage".
- *decoded*: navigation start → every picture on the stage loaded and decoded (`<img>`, SVG `<image>` puzzle pieces, CSS background images; `img.decode()`). This is S2 without the tap. S3 is the number of those pictures still loading at *ready*.
- *Lang switch*: tap ID/EN → the reloaded screen ready with its pictures decoded (S4). Both directions, warm cache.
- *Tap path*: real clicks peta → wilayah → misi → tantangan (warm). S1 is tap → first visual change: a curtain played by the link on the old page, otherwise the new page's first paint.
- Requests and bytes per navigation (CDP), split HTML / API / same-origin assets / external, with the count of 304 revalidations. Bytes are transferred bytes (compressed, with headers); cache hits count 0.

**Baseline** (9 October 2026, `main` at PR #71, 20 runs, p50 / p95 in ms; Chromium 141 in a cloud container; no errors):

| Screen | Cold: ready | Cold: decoded | Warm: FCP | Warm: ready | Warm: decoded | Lang switch: decoded | Cold requests (HTML/API/assets/ext) · KB | Warm requests (304) · KB |
|---|--:|--:|--:|--:|--:|--:|--:|--:|
| awal | 1467 / 1485 | 1467 / 1486 | 352 / 436 | 594 / 607 | 595 / 608 | 800 / 822 | 1/0/23/0 · 133 | 24 (14) · 8 |
| mulai | 1467 / 1596 | 1468 / 1597 | 360 / 388 | 599 / 647 | 600 / 648 | 802 / 821 | 1/0/23/0 · 134 | 24 (14) · 8 |
| masuk | 1890 / 1961 | 1891 / 1962 | 364 / 412 | 936 / 1132 | 937 / 1133 | 1148 / 1324 | 1/0/27/0 · 145 | 28 (18) · 9 |
| daftar-1 | 2081 / 2324 | 2082 / 2325 | 352 / 384 | 928 / 1062 | 929 / 1064 | 1133 / 1165 | 1/0/29/0 · 185 | 30 (17) · 9 |
| gerbang | 1473 / 1579 | 1476 / 1581 | 304 / 316 | 594 / 728 | 596 / 730 | 805 / 845 | 1/1/26/0 · 135 | 28 (14) · 9 |
| ganti-sandi | 1657 / 1793 | 1658 / 1794 | 300 / 308 | 758 / 929 | 759 / 931 | 973 / 1006 | 1/1/24/0 · 137 | 26 (15) · 9 |
| intro (narrator-tap) | 2006 / 2257 | 2013 / 2263 | 372 / 400 | 1106 / 1144 | 1113 / 1149 | 1344 / 1614 | 1/1/26/0 · 147 | 28 (17) · 13 |
| peta | 2302 / 2435 | 2314 / 2446 | 316 / 324 | 1169 / 1302 | 1180 / 1311 | 1375 / 1535 | 1/1/32/0 · 217 | 34 (19) · 19 |
| dialog-temanggung (narrator-tap) | 2137 / 2267 | 2148 / 2280 | 360 / 388 | 1130 / 1251 | 1142 / 1263 | 1358 / 1380 | 1/1/29/0 · 192 | 31 (17) · 15 |
| wilayah-temanggung | 2177 / 2258 | 2179 / 2262 | 304 / 320 | 1093 / 1211 | 1095 / 1214 | 1306 / 1589 | 1/1/31/0 · 195 | 33 (19) · 11 |
| misi-temanggung-1 | 1475 / 1737 | 1477 / 1738 | 300 / 304 | 592 / 610 | 594 / 611 | 806 / 826 | 1/1/26/0 · 123 | 28 (14) · 9 |
| tantangan-temanggung-1 | 2359 / 2473 | 2364 / 2477 | 300 / 304 | 1126 / 1257 | 1130 / 1260 | 1331 / 1494 | 1/2/33/0 · 199 | 36 (20) · 13 |
| misi-temanggung-2 | 1464 / 1477 | 1466 / 1479 | 300 / 312 | 591 / 611 | 593 / 613 | 806 / 986 | 1/1/26/0 · 123 | 28 (14) · 9 |
| tantangan-temanggung-2 | 2342 / 2472 | 2344 / 2474 | 300 / 312 | 1127 / 1291 | 1129 / 1293 | — | 1/2/32/0 · 196 | 35 (20) · 12 |
| misi-temanggung-3 | 1475 / 1576 | 1476 / 1578 | 304 / 316 | 594 / 613 | 595 / 614 | 806 / 869 | 1/1/26/0 · 123 | 28 (14) · 9 |
| tantangan-temanggung-3 | 2495 / 2675 | 2499 / 2680 | 300 / 316 | 1289 / 1556 | 1292 / 1559 | — | 1/2/34/0 · 199 | 37 (21) · 12 |
| misi-temanggung-4 | 1470 / 1478 | 1472 / 1480 | 300 / 304 | 594 / 731 | 596 / 732 | 805 / 822 | 1/1/26/0 · 123 | 28 (14) · 9 |
| tantangan-temanggung-4 | 2421 / 2590 | 2426 / 2594 | 304 / 316 | 1128 / 1281 | 1132 / 1285 | — | 1/2/41/0 · 208 | 44 (20) · 12 |
| selesai-temanggung-1 | 1935 / 2739 | 1937 / 2742 | 316 / 336 | 913 / 1061 | 915 / 1063 | 1137 / 1157 | 1/1/28/0 · 180 | 30 (16) · 10 |
| hasil-temanggung-1 | 1461 / 1723 | 1463 / 1725 | 300 / 304 | 593 / 729 | 595 / 731 | 805 / 819 | 1/1/26/0 · 123 | 28 (14) · 9 |
| tuntas-temanggung (narrator-tap) | 2128 / 2288 | 2132 / 2292 | 364 / 384 | 1108 / 1258 | 1111 / 1262 | 1313 / 1336 | 1/1/29/0 · 189 | 31 (17) · 12 |
| pustaka-rak | 2039 / 2159 | 5747 / 6004 | 308 / 316 | 957 / 1178 | 1046 / 1182 | 1240 / 1310 | 1/2/30/9 · 618 | 40 (18) · 20 |
| pustaka-temanggung | 2075 / 2423 | 4467 / 5216 | 332 / 344 | 1246 / 1345 | 1260 / 1354 | 1322 / 1425 | 1/2/30/15 · 330 | 43 (2) · 63 |
| pustaka-terkunci-temanggung | 1460 / 1477 | 1462 / 1479 | 300 / 304 | 594 / 612 | 596 / 613 | 806 / 958 | 1/1/26/0 · 135 | 28 (14) · 9 |
| profil | 1473 / 1476 | 1476 / 1479 | 304 / 316 | 607 / 626 | 610 / 629 | 819 / 835 | 1/1/26/0 · 124 | 28 (14) · 10 |
| refleksi | 2192 / 2442 | 2194 / 2445 | 304 / 316 | 1093 / 1276 | 1095 / 1279 | 1306 / 1491 | 1/1/30/0 · 188 | 32 (18) · 11 |
| penutup (narrator-tap) | 2124 / 2423 | 2128 / 2428 | 364 / 380 | 1091 / 1241 | 1096 / 1246 | 1307 / 1332 | 1/1/29/0 · 188 | 31 (17) · 12 |

| Tap path (warm) | S1 response | FCP | ready | decoded | requests (304) · KB |
|---|--:|--:|--:|--:|--:|
| peta→wilayah | 7 / 16 | 2122 / 2134 | 2943 / 3218 | 2946 / 3220 | 35 (17) · 38 |
| wilayah→misi | 297 / 305 | 297 / 305 | 643 / 812 | 645 / 814 | 30 (12) · 36 |
| misi→tantangan | 13 / 15 | 1529 / 1534 | 2396 / 2694 | 2400 / 2698 | 37 (17) · 40 |

Language switch on a challenge screen (Susun Gambar, Temanggung 1): 1331 / 1494 ms.

**Compared with the targets (§1.2, Stage 4 values):**

| Target | Baseline (p95) | Gap |
|---|---|---|
| S1 tap → visual response ≤ 100 ms | 15–16 ms when the link plays a curtain (peta → wilayah, misi → tantangan); 305 ms on links without one (wilayah → misi: the old page stays until the new one paints) | met only on curtain links |
| S2 tap → new screen with all pictures decoded ≤ 400 ms | warm navigation 608–1559 ms (most screens 1.1–1.3 s); tap path 814–3220 ms; cold 1.5–6.0 s | 2–8 × over |
| S3 no picture appears late | 0 late on every screen except Pustaka (1–3 Wikimedia pictures arrive after the stage, cold and warm) | met except Pustaka |
| S4 language switch ≤ 400 ms | 822–1614 ms | 2–4 × over |

**Why the slow screens are slow:**

1. **JavaScript start, not bytes, dominates warm screens.** Warm: first paint ≈ 300 ms (HTML 4–15 KB, one RTT plus PHP), but the page module is ready only at 600 ms (simple screens) to 1.1–1.3 s (peta, wilayah, tantangan, narrated screens). Warm screens move only 8–20 KB, yet 14–21 of their 24–44 requests are 304 revalidations: the 14–20 JS modules without `?v=` and the fonts. The module imports run in tiers (`game.js` → `core/*` → page module → `engines/*`), and each tier waits one RTT for its 304s. Screens with a page module and engine (peta, tantangan, Pustaka, dialogue) pay two to three extra tiers. This is §3 item 2.
2. **Cold screens carry the shared bundle.** 120–220 KB per cold screen: CSS 49 KB (6 files), JS 41–60 KB (14–20 files), fonts 27–95 KB (3–7 files). At 1 Mbps that is 1.0–1.8 s before any picture.
3. **Pustaka loads its pictures from Wikimedia Commons.** The shelf pulls 9 external pictures (431 KB cold, decoded at 5.7–6.0 s); a region book 15 external requests (133 KB). Even warm, 9–13 external requests go out and 1–3 pictures still arrive after the stage. The asset package removes this only if the library pictures are copied locally (§2.2, P4a).
4. **Curtains add fixed time on the tap path.** peta → wilayah plays the "Menuju {wilayah}" curtain (1.8–2.4 s) before navigating: tap → ready 2.9–3.2 s. misi → tantangan plays the challenge curtain (1.2–1.5 s): 2.4–2.7 s. They answer S1 instantly but hide items 1–2.
5. **The language switch is a full reload plus a POST and redirect.** Two HTML round trips, then the same 304 tiers as a warm screen: ≈ 200 ms over the warm screen time.

**Limits of the measurement.**

- **Few pictures in the dev database.** Only the 45 challenge mockups (small SVG) and the library links exist. Backgrounds, maps, character frames, and the logo are uploaded by the admin on the server (§2.2), so most screens show 2 empty picture slots and 0 pictures. Picture bytes and decode times here are a lower bound: on the server each screen adds a 1920 × 1080 background (≤ 450 KB), and maps (≤ 600 KB) and character frames (≤ 250 KB each). At 1 Mbps, one 450 KB background adds about 3.6 s cold. Re-run against a copy of the server's `public/assets/uploads/` (or on the server with a test study) before the Stage 4 comparison.
- **Narration audio is outside the window.** Audio starts after the "tap to start" card or later, so it is not in the per-screen bytes.
- No CPU throttling, and Chromium on a container CPU rather than a Chromebook or iPad. Script parse and image decode will be slower on the reference devices. Manual runs on real devices stay in §11.3.
- CDP throttling adds the latency per request, not per packet, and uses one connection pool for the page. TLS and HTTP/2 come from the real nginx.
- Warm here means "just visited", with the HTTP cache full. A real first visit to a screen mid-game (shared CSS/JS/fonts cached, screen pictures not) sits between cold and warm.
- Runs from one container on 9 October; p95 over 20 runs. The spread between runs is small (p95 within 10–25 % of p50 except Pustaka and a few cold outliers).

**Observed while measuring (not fixed, app code is out of scope).** Once in about a dozen registrations the new student's PHP session was gone on the next request: the student landed on `/masuk`, and the server log shows a CSRF rejection of `POST /api/events` in the same second. It looks like a race between the session regeneration at registration (`session.regenerateDestroy = true`) and the page's event beacon. The script now logs in again when this happens. Worth a look; a real student would be asked to log in right after registering.

## Asset package — Stage 1: package builder (October 2026)

Branch `paket-1-pembangun`. Server side of `docs/rencana-paket-aset.md` Stage 1 (§5.1–§5.6, §6.1–§6.3, §10 row 1, §11.3), with owner decisions P3, P4a, P9, P11, P12. No student-visible change: package mode stays off until a release names a package.

- **Table `asset_packages`** (migration `2026-01-01-003000`, `REQUIRED_MIGRATION` raised). Columns as in plan §5.4, plus `code_version`, `content_version`, `groups_json`, `warnings_json`, and timestamps. Stored status codes follow the plan (`dibangun`/`terbit`/`ditarik`) plus **`membangun`** for a batch build in progress. `gelita:reset` leaves the table alone (group E).
- **"Active package" = the active release's `asset_version` is a valid package code whose URL map exists** (`writable/paket/{code}.php`, written only at the end of a successful build). The old default `asset_version = '1'` therefore means "off" with no data change, and a restored database without the package files falls back to normal URLs instead of broken ones. `Config\Gelita::$paket['aktif']` (`gelita.paket.aktif` in `.env`) overrides it. The package's status column is not consulted at runtime (one query per request: the active release row).
- **Helpers.** `media_src()`, `media_key_src()` (and through it `media_key_src_locale()` and `character_frame_src()`), and also `audio_src()` (narration is part of the package, plan §5.2 "URL narasi") return `assets/p/…` URLs for assets in the active package. The asset must still be active: a deactivated asset disappears as before, and an asset added after the build keeps its normal URL until the next build. `asset_url_versioned()` (CSS/JS entry) is **not** switched yet: it lives in `gelita_helper.php` next to `js_config()`, which belongs to Stages 4/5, and pointing pages at `assets/k/` only pays off with the service worker. The manifest gives `code_dir` for that step.
- **Code folder (P9) = `assets/k/{app_version}-{sha256 of all code files[0:8]}/`.** Deviation from the literal `assets/k/{app_version}/`: release `app_version` values here are hand-typed (`2.0.0`), not commit hashes, so the same value could cover different code and overwrite an immutable folder. The hash suffix keeps "one folder per code version" while making reuse impossible; unchanged code keeps the same folder, so a rebuild with unchanged code downloads nothing new. A code file that changes during a build aborts the build.
- **Code group contents:** `assets/css/*.css` without `admin.css`, `assets/js/**/*.js` without `admin.js` and `js/admin/`, `assets/vendor/*.js` (Howler), `assets/fonts/*.woff2`, and `offline.html` (copied to `k/{v}/offline.html`). On the current tree: 52 files, 0.51 MB. App icons, `wilayah-id.json`, and HTML are not packaged (§5.1).
- **Media:** every active `media_assets` row except audio whose `audio_assets` row is not approved (draft narration is left out and listed as a warning). Music and sound effects are packaged through their `media_assets` rows (`audio.music.*`, `audio.sfx.*`, registered by `gelita:media:scan`); `core/audio.js` still loads them by folder path, so Stage 2/4 should resolve them through the manifest keys. Hashes are computed from the source files; a differing `media_assets.sha256` only warns.
- **Groups (§5.1):** by asset_key pattern first (shared backgrounds, `char.*`, `ui.*`, `map.kedu`, `audio.sfx.*`, `audio.music.map`, narration `intro-*`/`peta-*` → `inti`; `bg.{region}.region`, `map.region.*`, `reward.badge.*`, challenge slots with a node ref, narration `kenal-/dialog-/tuntas-{region}` and `petunjuk-{prefix}-n` → `wilayah-*`; narration `penutup-*` and other music → `penutup`), then by database links (levels, nodes, items, library pages, passages, dialogues), else `inti`. The plan's "music for challenge/region unless already in another group" is read as: music other than the map theme goes to `penutup`.
- **Same bytes under two keys** become one manifest entry with `aliases` (e.g. a shared background). `total_bytes` is the sum of unique files, which is what a device downloads.
- **Batch API** for the admin panel: `AssetPackageBuilder::start()` → `step($code, $limit)` → `finish($code)`, state in `writable/paket/{code}.build.json` under an exclusive file lock so two batch requests never process the same file. `build()` runs all steps (CLI). Batch size `Config\Gelita::$paket['batchSize']` (25).
- **Changed-asset count (§6.3):** `AssetPackageService::changedSinceActive()` compares the `sha256` of eligible media now with the `sha256` recorded per key in the active package's URL map: one query plus one include. New, replaced, and removed assets all count; approving draft narration counts as new.
- **Commons copies (P4a):** while building (unless `--tanpa-pustaka` / `copyLibraryImages = false`), every active library image that is only a Commons link is downloaded once by the server (the same `?width=1280` rendition students see today), stored as an ordinary image `library.commons.{name}-{sha1[0:8]}` through `MediaStore::storeBytes()` (raster only, JPEG/PNG as delivered — P3), its `credit` copied, and linked via `library_media.media_asset_id`. The Commons URL stays in `external_url`; `LibraryMedia::resolve()` now keeps provider `commons` and the source link for such rows, so the credit and "source" link look as before while the image comes from GELITA. A failed download only warns; the row stays a link. Note this changes what students load **even with package mode off** (local copy instead of Wikimedia), which is the privacy improvement in plan §9.3.
- **Package codes** `pkt-YYYY-MM-DD-NN` (17 characters; the release form allows 20). Codes are never reused: `gelita:paket:bersihkan --hapus` leaves a `writable/paket/{code}.deleted` marker, because the old manifest URL may still be cached as immutable.
- **Commands** (Indonesian names, English classes): `gelita:paket:bangun` (`PackageBuild`; `--catatan`, `--versi-aplikasi`, `--tanpa-pustaka`), `gelita:paket:periksa [kode]` (`PackageCheck`; default: the release's package, else the latest built; exit 1 on any problem), `gelita:paket:bersihkan` (`PackagePrune`; `--hapus a,b`, `--coba`). Pruning keeps every file referenced by any package row or running build, never deletes a package a release names, removes builds stuck in `membangun` for 24 h, and skips media files written in the last hour (a batch may not have saved its state yet).
- **API:** `GET /api/paket/aktif` (`PackageApiController::active`), no login (the device asks on the welcome screen), `Cache-Control: no-store`, standard `{success, data}` envelope. The `X-Gelita-Paket` header on API responses (§6.2) is not added here.
- **Nginx:** `/assets/p/`, `/assets/k/`, and `/paket/pkt-*.json` get `immutable` without `?v=` (map value `2`), both platform configs; documented in `docs/08_DEPLOYMENT.md`.
- **Backups** (owner request after review): `backup.sh` writes `gelita-paket-{stamp}.tar.gz` with the four package folders as paths relative to the app root; `backup.ps1` writes `gelita-paket-{stamp}.zip` (`p`, `k`, `public/paket`) and `gelita-paket-peta-{stamp}.zip` (`writable/paket`), because both `paket` folders would collide at the root of one `Compress-Archive` zip. Missing folders (no package built yet) are skipped. A full package is up to ~50 MB, so 30 days of daily backups can take ~1.5 GB per location.
- **Not done here / follow-ups:** `gelita:content:verify --siap-penelitian` does not yet call `gelita:paket:periksa` (research-ready checklist, Stage 6); staff preview `?paket=` (Stage 6); `asset_url_versioned()` → `assets/k/` (Stage 2/4).
- Tests: `AssetPackageBuilderTest`, `AssetPackageHelperTest`, `AssetPackageCommandTest` (commands + API), with the shared world `tests/_support/Database/PackageWorld.php`.

## Asset package — Stage 1b: narration 48 kbps mono (October 2026)

Branch `paket-1b-narasi`. Plan `docs/rencana-paket-aset.md` §10 row "1b. Kodekan ulang narasi", owner decision P2 ("Yes, after a listening test of 5 files per language"). Details and measurements are in `docs/audio.md` → *Narration at 48 kbps*.

- **Transcoded from the 96 kbps MP3s, not from WAV.** The WAV masters (`tools/audio/out/`) were never committed and regenerating them is stochastic (VITS) and needs the TTS models plus 1–1.5 h per language with new Whisper QA. Re-encoding the committed MP3s keeps every take the owner already approved; the cost is one extra lossy generation. New script `tools/audio/reencode.py`; `publish.py` now encodes 48 kbps too, so a future WAV run lands at the same bitrate.
- **Same codec and container** (MP3 mono 44.1 kHz CBR, Xing header, no ID3). A lower sample rate (22.05/24 kHz) was tried and rejected: it changes the stream format the browsers and `MediaStore` were tested with, for a gain only in peak overshoot.
- **True-peak guard added.** At 48 kbps the encoder overshot the source peak on strong sibilants (up to +3.3 dBTP on `en/intro-04` with ffmpeg defaults). `encode()` now uses LAME `-q 2` and searches gain trims in 0.1 dB steps (`-q 2`, then `-q 0`) until the true peak is ≤ −0.5 dBTP, the ceiling of the 96 kbps files. 11 files are trimmed; only `en/intro-02` (−0.5 dB) and `en/intro-04` (−1.3 dB) change loudness by more than 0.1 LU.
- **Manifest.** `produksi.json` entries gain `bytes` and `bitrate_kbps` next to `sha256` and `voice_profile`. `NarrationImporter::production()` reads only `sha256` and `voice_profile`, so no app code changed.
- **Size.** Narration 24.65 MB → 12.33 MB (−12.32 MB); `public/assets/audio` 28.09 MB → 15.77 MB, matching the ±15.6 MB estimate in plan §2.3.
- **Servers that already imported the 96 kbps files.** Their `audio_assets` rows carry the old sha256. Running `gelita:narration:import` after deploying replaces them with the new files (same names, `production_method = tts` again) and, as for any replaced recording, puts them back to draft; staff approve them again under **Rekaman narasi**.
- **Asset checklist.** At the owner's request, `AssetChecklist` (`app/Libraries/AssetChecklist.php`) now describes narration as "MP3 mono 48 kbps" instead of "64–96 kbps". `docs/naskah-cerita.md`/`08_DEPLOYMENT.md` still recommend 64–96 / 96–128 kbps for voice-actor recordings; left for the owner to decide whether that recommendation should drop too.
- Whisper CER was not re-run at 48 kbps (no ASR models in this environment); the owner's listening test (P2) is the gate. Do not merge before it.

## Asset package — Stage 4: transitions, decode, short curtain (October 2026)

Branch `paket-4-transisi`. Implements plan `docs/rencana-paket-aset.md` §10 row "4. Transisi, decode, tirai pendek" (§4.2 M1, §4.4 L1, §4.5), with owner decisions P5 and P8.

- **Cross-document View Transitions (M1).** `stage.css` adds `@view-transition { navigation: auto; }` and names three parts: `#stage` (`gelita-stage`), the background `.scene` (`gelita-scene`), and the HUD (`gelita-hud`). Groups animate in 260 ms; stage and scene cross-fade in place (`object-fit: cover`) instead of morphing. `prefers-reduced-motion: reduce` turns navigation transitions off. Only the game layout loads `stage.css`, so the admin panel keeps plain navigation. Browsers without support ignore the at-rule. Checked in Chromium: link navigations and the language reload (POST `/bahasa` → redirect to the same URL) both run a transition; reduced motion does not.
- **Next-screen decode (§4.5).** New helper `next_assets_attr()` in `gelita_helper.php` (next to `js_config()`; it is a view helper, so `ui_helper.php` would also fit, but that file was outside this session's scope) and module `game/next-assets.js`. Each screen that knows the likely next screen names its images; the client decodes them while idle with `img.decode()` and holds them until `pagehide`. Caps: one screen ahead, 6 images, images only, nothing on Save-Data. The views pick the next screen from data they already have (no new queries except `map-level`, which reads the cached `nodesForLevel()` to get the first open node's background). Mapping in `docs/06_JAVASCRIPT.md`.
- **Not done from §4.5: decode `#scene-a` before showing it.** The current background is rendered visible by PHP (`is-visible`) so it works without JavaScript; holding it back until `decode()` would add JS to the first paint of every screen and could blank the background if the module fails. With the next-screen decode and the view transition (which keeps the old screen until the new one renders), the flash this item targets should already be gone. Re-check with the Stage 0 timing script once the package exists; decoding character frames per pose is likewise left to that measurement.
- **Short curtain (P8).** `components/curtain` puts `data-local-min-ms="600"` on `region` and `challenge`; `core/curtain.js` `curtainMinMs()` uses it when `assetsLocal()` is true. The `map` curtain after the opening story has no such attribute and keeps 2.5 s. The curtain's maximum and tap rules are unchanged.
- **"Assets are local" hook for Stage 2.** `core/config.js` exports `assetsLocal()` (false by default) and `setAssetsLocal(on)`. Stage 2 switches it on with `<html data-assets-local="1">` before `game.js` runs (preferred, since the page's own curtain starts at module load) or `setAssetsLocal(true)` later. Contract in `docs/06_JAVASCRIPT.md`. Nothing in Stage 4 switches it on, so behaviour today is unchanged.
- **Two-language JS texts (§4.4).** `js_config()` now sends `text: { id: {...}, en: {...} }` for every locale in `Config\Gelita::$locales` (the old shape was the active locale only). `core/config.js` adds `setLocale()`, `currentLocale()`, `textLocales()`, and still accepts the old flat map. The language switch stays L1 (form POST and reload); `hud.js` switches JS texts at submit, so anything JS shows during the reload is already in the new language, and tapping the active language is a no-op instead of a POST plus reload. The admin layout gets both languages too (it ignores them; the panel is Indonesian only). `JsConfigTest` was updated for the new shape.
- **P5.** `game.css`: `body.is-challenge .lang-switch { display: none; }`, the same mechanism as the HUD leave links. `challenge.js` keeps its `state.leaving` guard on the form for safety.
- **Tests.** `AssetTransitionTest` (CSS, curtain markup, hook default, P5, next-assets wiring), `JsConfigTest` (two languages, `next_assets_attr()`), `RegionStoryTest` (real `map-kedu` and `region-done` output carry the next region's assets). Browser: new `tools/qa/transition-check.mjs` (view transition on/off, `setLocale()` without requests, curtain minimum with the hook on/off, decode and caps, language button), plus `tools/qa/stage-check.mjs` at all six sizes.
- **Bug found and fixed: new students logged out right after registering.** `game/register.js` checks the username with a 500 ms debounce (`GET /api/auth/username-available`). When that check fired after the step 3 submit, its request carried the pre-registration session cookie; the server had just regenerated the session for the new account, so the late response set the old cookie back and the next screen sent the student to `/masuk`. On `main` the old page usually unloads before the debounce fires (0 of 16 runs locally), but a slow POST on school Wi-Fi would hit it; the view transitions keep the old page alive slightly longer, which made it 4 of 12. Fix: the check stands down once `preventDoubleSubmit()` marks the form as submitting (12 of 12 runs clean). Server-side hardening (the username endpoint needs no session) is left as an open question, since `app/Controllers` was outside this session's scope. This is most likely the logout Stage 0 observed while measuring ("Observed while measuring" above): in my request traces the CSRF rejection of `POST /api/events` came after the session was already lost, so it is a consequence, not the cause.
- **Local QA setup.** The MySQL migrations do not run on SQLite, so for the browser checks I built a SQLite file from the test schema (`GradeFiveWorld`, unlock mode `free`) and served it with `php spark serve`, file sessions, and a git-ignored `.env`. Nothing touched a shared or production server.

## Asset package — Stage 5: research data integrity (October 2026)

Branch `paket-5-integritas`. Plan: `docs/rencana-paket-aset.md` §7.1 and §7.3 (§7.2, the study package lock, is Stage 6).

- **Schema.** New migration `2026-01-01-003100_AddPackageTrackingToSessionsAndEvents`: `game_sessions.package_code` (VARCHAR 64) and `package_mode` (VARCHAR 10), `game_sessions.app_client_version` widened from 30 to 100 characters (`{app_version}+{package_code}` can exceed 30), and `game_event_logs.clock_offset_ms` (BIGINT, signed: a device clock can run ahead or behind). Existing sessions are backfilled with `package_mode = nonaktif` and their release's `app_version`, which is true: no device had a package before Stage 2. `SystemResetService::REQUIRED_MIGRATION = 2026-01-01-003100`.
- **Separate `package_code` column.** The plan only names `package_mode`; `package_code` gets its own column so the Sesi export and the Stage 6 study lock do not have to split `app_client_version` (an `app_version` may itself contain `+`).
- **Who knows the app version.** The server, not the client: `app_version` comes from the session's active release (`game_releases.app_version`), the client only reports `package_code` and `package_mode`. So the one `js_config()` key allowed for an app version went to `sequenceFloor` instead (below).
- **Client hook for Stage 2.** `window.GELITA_PACKAGE` = `{ package_code, package_mode }` or a function returning it (sync or Promise). `core/device.js` `packageInfo()` validates it; without it the client sends `{ package_code: null, package_mode: 'nonaktif' }`. With no `device_json` at all (JavaScript off, the change-password form) the server uses `SessionService::DEFAULT_PACKAGE_MODE = 'nonaktif'`; Stage 2/6 should revisit that default once package mode can be on.
- **Resume keeps the package (S8).** A resumed session keeps its columns. When the device reports a different package or mode the server writes `package_changed` {from, to, reason: 'resume', from_mode, to_mode}; a session from before Stage 5 gets its columns filled instead.
- **`sequence_no` across pages.** `sessionStorage` key `gelita.seq.{sessionTag}`, never below `CONFIG.sequenceFloor` (`js_config()`, helper `event_sequence_floor()`). Without the floor a new tab or a resume the next day would restart at 1. `core/config.js` gained the `sequenceFloor` key (it whitelists config keys).
- **Server events in the same number line.** Found in the browser run: the next page's `screen_viewed` is written while the previous page's last events are still in a beacon, so it took a number at or below theirs. The client now mirrors its last number in a cookie `gelita_seq={sessionTag}.{n}` (not HttpOnly, opaque tag, no identity), and `EventService::nextServerSequence()` = max(DB next, cookie + 1) numbers every server-written event and gives the floor. The tag must match the session, so another student's cookie on a shared computer is ignored. Known limit: two tabs of one session share the cookie but not `sessionStorage`, so they can still produce equal numbers; ties fall back to `occurred_at`.
- **Clock offset.** Every batch, fetch or beacon, carries `client_sent_at`; the server stores `clock_offset_ms = server_received_at − client_sent_at` for every row of that batch, also for audio events. Server-written events get 0; a missing or non-ISO `client_sent_at` gives NULL. `occurred_at` is never changed. Exported in the Navigasi and Event Mentah sheets.
- **No silent drop.** Queue limit 500 → 5,000 entries per channel. Over the limit the oldest entries make room (usually a stale session that never came back) and each one is counted for its own session; a write refused by the browser counts the whole batch. Counts live in `gelita.dropped.{channel}` (with an in-memory fallback), a warning toast `Js.eventQueueFull` shows once per page, and the next fetch batch carries `dropped` + `dropped_id`. The server writes one server-only `events_dropped` {dropped, channel} with `client_event_id = drop-{dropped_id}`, so a report re-sent after a lost answer is a duplicate. Beacons never carry the report (no answer to confirm it).
- **Beacons are not trusted.** Found while testing: Chromium aborts the `pagehide` beacon on an abrupt navigation (`net::ERR_ABORTED`, pre-existing behaviour), and offline it accepts the beacon and drops it later; in both cases the last page's events were lost silently. A forced flush now beacons the events and also keeps a copy in the offline queue (entries marked `b`); the next page re-sends it and the server answers `duplicate` for what already arrived. Offline no beacon is tried. Letting a beaconed copy go when the queue is full is not counted as dropped (it most likely arrived). Cost: the few events still pending at page exit are sent twice; the curtain already flushes before navigating, so this is usually empty.
- **Event types.** `package_changed` (client or server) and `events_dropped` (server only) added to `Config\Gelita::$eventTypes`, `RawDataService::NAVIGATION_EVENTS`, and the Indonesian event labels in `Admin.php` (needed by the labelled export).
- **Export.** Sesi sheet: `app_client_version`, `package_code`, `package_mode` (enum `packageMode` with labels) after `release_code`, all in the Kamus Data. Navigasi and Event Mentah: `clock_offset_ms`.
- **Tests.** `EventOrderingTest` (§11.3; including the §11.2 two-minute offline scenario with a skewed clock, resends, and a late beacon), `SessionPackageTest` (sign-up/login/resume columns, Sesi export and dictionary). The client queue logic was checked with a Node harness over the real `core/events.js`, `storage.js`, and `device.js` with stubbed browser APIs (not committed; there is no JS test runner in the repo). New `tools/qa/event-integrity.mjs` (Playwright, local MariaDB 10.11) runs the §11.2 scenario in Chromium: device clock 5 minutes behind, package hook set, sign-up, two pages, 120 s offline with one event per second, back online on a third page; all checks pass (sessions columns, every event once, increasing `sequence_no` across pages with server events in place, `clock_offset_ms` ≈ +300000). Migration `003100` was run up, down, and up again on MariaDB 10.11, including the backfill.

## No session on the no-login API endpoints (10 October 2026)

Branch `sky/gracious-archimedes-injcc3`. Closes the server-side open question from Stage 4 ("Bug found and fixed: new students logged out right after registering").

- **Cause.** The global `locale` filter read `session('locale')` on every request, so `GET /api/auth/username-available`, `/api/schools/lookup`, and `/api/paket/aktif` started a session and answered with `Set-Cookie: gelita_session=…`. Checked with `php spark serve` and curl: all three set a cookie, with and without an incoming session cookie.
- **Fix.** `LocaleFilter::STATELESS_PATHS` lists these three paths; for them the filter applies the default locale (`id`) and returns before touching the session. The exemption lives in the filter, not in `Config\Filters` `except`, because `GelitaExceptionHandler` also calls `LocaleFilter::before()` directly, so error responses on these paths stay cookie-free too. After the fix none of the three sends `Set-Cookie`; game pages still do.
- **Trade-off.** These endpoints no longer know the student's language. The only translated text they return is the rate-limit message (`Game.errRateLimited`), and the client never shows it: `register.js` falls silent on any error, and `handleApiError()` shows its own `Js` text for `RATE_LIMITED`. An English-speaking student loses nothing visible. The `register.js` guard from Stage 4 stays as a second layer.
- **New no-login endpoints** must be added to `STATELESS_PATHS`. `LocaleFilterSessionTest` checks that the listed paths never call the session, that other API paths still read the session locale, and that every listed path is a GET route outside the `apiSession` group.


## Asset catalogue decision and admin image slots (10 October 2026)

Branch `sky/youthful-turing-l3pvlp`.

- **Static catalogue: use the team's "Katalog Aset dan Prompt, Edisi 03" (ZIP).** The owner shared a ready static catalogue (`index.html`, `data.json` with 370 entries, `KATALOG-PROMPT.md`, 12 reference images; its `sources/rencana-katalog-aset.md` is identical to `docs/rencana-katalog-aset.md`). It covers the content of Part II of that plan (style guide, 225 English ChatGPT Image prompts, sizes, the 45 grade-5 challenge slots, characters, backgrounds, icons, music, narration directions). Checked against `Config\Gelita::$assetSizes`: every enforced size in the catalogue matches the code. Decision: the ZIP is the prompt and style catalogue; Part I of the plan (`docs/katalog-aset/build.php`, generated page, SVG templates, `AssetCatalogTest`) is **not** built for now. The ZIP is not committed and never goes under `public/` or onto a school server: it names decoy objects and describes answer images.
- **Known gaps in the ZIP (fix in its next edition, not in the repo).** It is hand-assembled, so it can drift from the code; bg.* entries only say "Latar layar umum"; every challenge entry carries the puzzle note "Ukuran minimum puzzle 1200 × 1200"; region maps still mention five posts and "koordinat baru belum diberikan" (grade 5 has four challenges; positions are now on the slot detail page); narration lists 88 lines while the game has 100 per language (88 story + 12 Cari Objek clues); the 12 "Referensi lama" and 14 "Usulan awal" entries use an older costume (red scarf) that conflicts with the lurik-and-iket character sheet, so they are reference only. The ZIP cannot show server status, so upload status lives in the panel.
- **Single slot catalogue in the app: `App\Libraries\ImageSlots`** (the `ImageSlots` idea of `rencana-perbaikan-tampilan.md` Phase A, reduced to text). For each slot the game reads: label, what the picture should show, the game screens with their public paths, format, transparency, fallback, priority (`wajib`/`opsional`/`unused`), and a recommended size where `$assetSizes` does not enforce one (`ui.logo` 512², `map.kedu` and `map.region.*` 1400 × 900, `reward.badge.*` 320², library 960 × 640). Enforced sizes stay in `$assetSizes`; enforcement was not changed. `MediaUsage::SLOTS` is gone (`slotLabel()` reads `ImageSlots`), and `AssetChecklist::UI_SLOTS` is now a key list labelled from `ImageSlots`. `ImageSlotsTest` keeps it in step: every slot the seeder registers is described, every literal key in a view's `media_key_src()` is a used slot, and `unused` slots are read by no view.
- **Stale labels fixed.** `bg.auth` is read by no view (student login and sign-up use `bg.welcome`), and `ui.placeholder` is never read (`media_src()` uses the static `assets/ui/placeholder.svg`); both are marked "Belum dipakai permainan" and left out of Kelengkapan. `ui.logo` is the HUD logo on `/mulai` and `/daftar/1–3` plus the fallback on `/`, not the admin header.
- **Panel.** `/admin/media` filters by group, region, challenge (`?tantangan=wnb-4`) and text; rows without a label or description show the `ImageSlots` text; "Dipakai di" shows public paths; the label links to the new detail page `GET /admin/media/{id}` (`MediaController::show`): preview with map pins drawn on top, description, sizes, format, screens with paths and editor links, pin positions in percent and pixels (read from the database, so moved pins are included), upload form that returns to the page (`back` accepts only `admin/media/{id}`), previous/next slot in the group. `MediaUsage::forMedia()` now returns `{label, path, edit}` per use, names the role of Cari Objek objects (target with its clue, or decoy), and adds character poses from the story lines (`characterUses()`). Kelengkapan gains a "Gambar tantangan" group (one row per challenge; sample images count as not done) and links slots to their detail page.
- **Not done (follow-ups).** Per-slide story backgrounds (`bg.cerita-*`, `dialogues.background_media_id`) still cannot be set from the panel (`rencana-perbaikan-tampilan.md` Phase A7). App icons and favicon remain static files in git. Planned slots in the ZIP (pins, lanterns, city loading backgrounds, avatars) are not read by the game and are not in the panel.
- **Tests.** `ImageSlotsTest`, `MediaSlotPagesTest` (list filters and text, detail pages for a region map, a decoy and a target object, a character pose, upload `back`, checklist group). `NarrationTables` gained `challenge_nodes.scene_media_id` and `challenge_items.media_asset_id`, which the checklist reads. Browser check on a local MariaDB 10.11 with `DatabaseSeeder` (sandbox only): list, checklist, and six detail pages at 1440 × 900, no console errors, no horizontal page scroll.

## Image slot sizes: minimum size and same ratio instead of exact pixels (11 October 2026)

Branch `sky/funny-keller-o981ko`. Owner decision: a larger image is accepted and resized; the original is not kept.

- **Before.** `MediaStore` refused any image whose pixel size differed from `Config\Gelita::$assetSizes`, even a 3840 × 2160 background for a 1920 × 1080 slot. That was stricter than needed and stricter than Dok 1 §6, which calls the Susun Gambar size `[1200, 1200]` a minimum.
- **Why exact is not needed.** The game draws every slot scaled (`object-fit`, display sizes in `$assetDisplaySizes` are all smaller than the file), and map pins and Cari Objek objects are placed in percent of the image, so a larger file with the same ratio shows identically.
- **New rule (`App\Libraries\ImageFitter`).** Smaller on either side → refused. Width-to-height ratio more than 1% off the slot (`RATIO_TOLERANCE`) → refused, since cropping could cut part of the picture or move hunt objects. Larger with the same ratio → accepted and shrunk with GD to exactly the slot size, centre-cropping the sliver the tolerance allows. PNG and WebP keep transparency; JPEG is saved at quality 90, WebP at 90, PNG at compression 6.
- **Why resize instead of keeping the original.** Students' school devices download only what the game needs, every stored file stays at the official size, and `AssetChecklist` / the "Ukuran tidak sesuai" count keep their exact-size meaning without a new rule.
- **Where it applies.** `store()` (Media page and every content editor through `resolveField()`) resizes the upload's temp file before it is moved; `storeCopy()` resizes the copy; `registerFile()` resizes the file in place under `public/`, because that file is what is served. `storeBytes()` (library Commons images, video thumbnails) had no size check and is unchanged. SVG has no pixel size and is not checked.
- **Refused for resizing.** An oversized GIF (it may be animated, and GD would drop frames or transparency); the message asks for the exact size or a PNG. An image too large to decode within `memory_limit` (GD needs about 5 bytes per pixel) is refused with a message to shrink it first, instead of a fatal error.
- **Browser check.** `admin/media-upload.js` uses the same rule and messages, so a too-small or wrongly shaped file is refused before upload; the server checks again.
- **Tests.** `ImageFitterTest`: the rule, PNG transparency after resize, JPEG crop within tolerance, GIF refused and untouched, `store()` with a larger and a smaller upload (a test double replaces `move_uploaded_file()`), `storeCopy()` resize and refusals, `registerFile()` resize in place.


## Character frame animation (11 October 2026)

Branch `sky/ecstatic-ritchie-bcmeha`. Owner asked to check whether the multi-image character animation works and fix it.

- **Finding.** It did not run at all. `Config\Gelita::$characterAnimations` defines 3 frames with `fps`/`loop` per pose and the panel accepts uploads for `char.{jaka|kedu}.{pose}.{1..3}`, but no player existed: every view rendered frame `.1` only and `fps`/`loop` were read nowhere (`rencana-perbaikan-tampilan.md` Phase D, never built). Frames 2 and 3 were uploaded for nothing.
- **Fix, following Phase D.** `character_frames()` returns the uploaded frames of a pose in order with `fps`/`loop`, falling back to `idle` when frame 1 of the pose is missing (so frame 1 always equals `character_frame_src()`); a missing later frame is skipped. `components/character.php` adds `data-frames`/`-fps`/`-loop` (via `character_frames_attr()`) only with 2 or more frames; dialogue lines add `data-pose-frames`/`-fps`/`-loop` and keep `data-pose-src`. New `core/sprite.js` swaps the `src` of the existing image (no node swap, so `cine-rise` and the mirrored right-hand figure are unaffected), decodes frames while idle first, holds the last frame of a non-looping pose and replays it when the figure comes back into view, pauses on hidden tabs and for unrendered figures (IntersectionObserver), and shows frame 1 only under reduced motion. `game.js` loads it only when a `.character[data-frames]` exists; `dialogue.js` restarts it on every pose change.
- **Texts.** Kelengkapan help and the character slot tips no longer say only frame 1 is shown; the "(sementara tampil dengan pose diam)" note now appears only when frame 1 is missing, since that is the only case that falls back. `MediaUsage::characterUses()` lists frames 2+ with the same uses as frame 1.
- **Not done.** Admin preview of the animation on the slot page (Phase A item 3) is still open.
- **Tests.** `CharacterFramesTest` (helper, fallback, attributes, component, wiring) and a `RegionStoryTest` assertion for dialogue lines. Browser check of `core/sprite.js` in Chromium with a harness page: looping and one-shot poses, a hidden slide shown later, a broken frame skipped, `setFrames()` on pose change, and reduced motion.

## Display update plan revised (11 October 2026)

Branch `sky/eloquent-lamport-6cjmvk`. Owner request: bring `docs/rencana-perbaikan-tampilan.md` in line with the project as it is now, then implement it phase by phase. Docs only; no code changed.

- **How it was checked.** Three read-only audits of `main` at PR #83, one per area (admin images; avatars and city art; toasts and touch), against every item of the 29 September plan. The plan text was rewritten in English for the fixed 16:9 stage; the old Indonesian version stays in git history (`1943499`), since almost none of its file references, sizes, or layouts still apply.
- **Bugs found, now Phase 0.** Music never plays on challenge and region screens (`Sfx.music()` returns while audio is locked and nothing replays it after the first tap). "Kata sandi baru tersimpan" is consumed by the `/mulai` → `/gerbang` redirect. The must-change-password message shows twice (flash toast plus the page alert). The `$assetSizes` keys `map.region`, `reward.badge`, and `library.image` match no real key, so region maps, badges, and library images are uploaded with no size check; SVG uploads skip the size check on every slot.
- **Owner decisions.** Order 0 → A → B → C → E → F; a new per-city curtain slot `bg.loading.{wilayah}` (fallback `bg.{wilayah}.region`, then the gradient); full touch scope; one branch and PR per phase.
- **My calls in the revision** (the owner can still change them):
  - The "Tampilan permainan" card page is dropped: the `/admin/media` filters and the slot detail page from PR #80 cover it, and new slots join `ImageSlots`.
  - Toasts keep the Dok 3 place on the stage (x 856, y 72, width 400 dp) instead of moving to the centre; only look and behaviour change. They do not wait for modals, so errors are always seen.
  - `library.image` leaves `$assetSizes` instead of being enforced as `library.*`, because `ImageSlots` already calls library sizes recommended and Commons images come in many shapes.
  - Story slide backgrounds use the key `bg.story.{context}.{sequence}`, so the `bg.*` size rule applies.
  - iOS input zoom is avoided with `maximum-scale=1` on iOS only (iOS keeps pinch zoom), because the stage rules forbid a px font-size floor.
- **`docs/PROGRESS.md`.** New "Display update" section with one row per phase. The asset package rows there still show Stages 0, 1, 1b, 4, and 5 as open although PRs #74–#78 are merged; those rows belong to the coordinating session and were left as they are.

## Display update — Phase 0: bug fixes (11 October 2026)

Branch `tampilan-0-perbaikan`. Implements Phase 0 of `docs/rencana-perbaikan-tampilan.md`.

- **Music.** `Sfx.music()` now records the requested track (`wantedMusic`) even while audio is locked or sound is off; `unlock()` and `setEnabled(true)` start it. `setEnabled(false)` stops the music but keeps the request, so switching sound back on resumes the screen's track; `stopMusic()` clears it. Before, every screen's music request at load returned early (audio locks on each new page until the first tap) and only the map-curtain path played. Checked in Chromium with a harness page that loads the real `core/audio.js` with a stub `Howl`: nothing before the tap, the track on the first unlock, no restart on a second unlock, stop on sound off, the newest request on sound on. The same harness fails on the old code.
- **"Kata sandi baru tersimpan".** `LoginController::changePassword()` redirects straight to `/gerbang` (to `/mulai` only if no game session is open after the automatic login, which keeps the old behaviour for that edge case). `introGate()` keeps the `message` flash for one more request, so a student who has not watched the opening story sees it on `/intro`. Keeping the flash there is harmless for other callers of `introGate()`: it only carries a message that would otherwise be lost.
- **Must-change message shown once.** The student login no longer sets the `Auth.mustChange` flash, and the staff login no longer sets its "Ganti dulu sandi sementara…" flash: both password pages already show the same text as an alert.
- **Size rules.** `$assetSizes` and `$assetDisplaySizes` use `map.region.*`; `$assetSizes` also has `reward.badge.*` (320 × 320). `$assetDisplaySizes` had no badge row until the follow-up below. `library.image` is removed (library sizes stay recommended in `ImageSlots`, so `ImageSlots` no longer lists recommended sizes for the region map and badge, which are now required). `AssetChecklist::FALLBACK_SIZES` and the prefix match in `MediaController::displaySize()` are gone; every lookup uses exact key or `fnmatch`. `MediaStore::store()` refuses an SVG on a slot with a required size (it has no pixel size to check), with the same rule in `admin/media-upload.js`. `registerFile()` still takes the generated SVG sample images (`gelita:mockups`), which are placeholders by design. The slot page's upload hint said "Ukuran lain … ditolak", stale since PR #81; it now says larger images with the same shape are shrunk.
- **Existing uploads** of a wrong size are not touched; Kelengkapan keeps listing them as "Ukuran tidak sesuai".
- **Tests.** New `PasswordFlashTest` (password change lands on `/gerbang` with the message, no duplicate must-change flash for students and staff, the intro redirect keeps the message across two simulated requests); all four fail on the old controllers. `ImageSlotsTest::testEverySizePatternMatchesARealSlot` (each size pattern matches a seeded key or a catalogue pattern; fails on the old config with `map.region`). `ImageFitterTest`: region map upload is size-checked, SVG refused on sized slots and accepted on free ones. `MediaSlotPagesTest` expects the required size on the region map page.
- **Follow-up (tampilan-0-susulan).** An audit of `main` found two gaps. `$assetDisplaySizes` gets `reward.badge.*` => 72 × 72 dp: the badge is shown only on the profile badge shelf (`.region-badge-art img`, 4.5rem); `region-done` shows no badge image. `MediaSlotPagesTest::testBadgeDetailShowsFileAndDisplaySize` checks `MediaController::displaySize()` and the slot page of `reward.badge.temanggung`. The music harness is now committed as `tools/qa/music-check.mjs` (real `core/audio.js`, stub `Howl`, served from disk with Playwright routing, no PHP server); it passes on the fix and fails three checks on the pre-fix `audio.js`. `MusicUnlockTest` guards the same wiring in the normal suite (wanted track kept while locked; `unlock()` and `setEnabled(true)` start it; `stopMusic()` clears it).

## Display update — Phase A: every game image from the panel (11 October 2026)

Branch `tampilan-a-gambar-admin`. Implements Phase A of `docs/rencana-perbaikan-tampilan.md`. Phases E and F run in a parallel session (owner request, 11 October 2026: parallel chats to keep each context short). The split is by file ownership: this session keeps A → B → C, which all touch `ImageSlots`, the seeder, and the media pages; the other session does E → F, which touch toast and touch code. Shared files (`docs/PROGRESS.md`, this log) are edited only in each session's own rows and sections.

- **A1 — file types per slot.** `ImageSlots` gets `types()`: a slot's own `types`, else PNG or WebP for transparent slots and JPG, PNG, or WebP for the rest. `typeRules()` sends the same rules to the browser in `#asset-sizes`. `MediaStore::store()` refuses other image types (`WRONG_TYPE`); keys that are no slot keep the general image rule, and video uploads are not affected. The slot page's file input `accept` and help text name the slot's types. `admin/media-upload.js` checks the type before the size. `MediaAssetSeeder::slots()` takes region codes from `Config\Gelita::$levelPrefixes`. The size-pattern check asked for in the plan already came with Phase 0 (`testEverySizePatternMatchesARealSlot`).
- **A2/A3 — app icons and manifest.** New group "Ikon aplikasi" with `app.favicon` 48², `app.apple-touch` 180², `app.icon-192`, `app.icon-512`, `app.icon-maskable` 512², all PNG only, optional. `GET /manifest.json` is now `Game\AppController::manifest()` (same URL, so installed apps update); `public/manifest.json` is deleted, because on Nginx (`try_files $uri`) and Apache (`!-f`) a static file would shadow the route. Icons come from `app_icon_src($slot, $fallback)`: the active slot with its `?v=` hash, else the static file in `public/assets/app/` or `public/favicon.ico`. The route is in `LocaleFilter::STATELESS_PATHS`, since browsers fetch the manifest without cookies and each fetch would otherwise start a session. `AssetPackageService::eligibleMedia()` leaves `app.*` out of the asset package (the browser reads icons outside the game). The seeder registers the five slots empty with default paths under `assets/ui/` (scanned), not `assets/uploads/`: a default path equal to the upload path would make a later seeder run deactivate an uploaded icon.
- **A4 — zoom and animation preview.** `admin.js` opens `a[data-zoom]` in `core/lightbox.js` (delegated, modifier clicks keep the new tab); `admin.css` has its own `.lightbox` rules. Used on list thumbnails (character frames, stored as `sprite_frame`, had no thumbnail before), the slot page preview, and `components/media-field`. The slot page of a `char.*` frame lists every frame of the pose and, with 2+ frames, plays them with `core/sprite.js` at the pose's fps; non-looping poses get a "Putar ulang" button.
- **A5 — `ui.placeholder` used.** `media_src()` without an explicit fallback uses `media_placeholder_src()`: the slot when uploaded, else `assets/ui/placeholder.svg`. `media_is_placeholder()` replaces the string check in `GameProgress::regionCurtain()`.
- **A6 — story slide backgrounds.** The dialogue editor shows a `media-field` per slide for the global `intro` and `ending` contexts (multipart form); `saveDialogues()` resolves it with `MediaStore::resolve()`, default key `bg.story.{context}.{sequence}` (under `bg.*`, so 1920 × 1080 is enforced). Region contexts never touch `background_media_id`. Emptying the code box removes the background, as everywhere `media-field` is used.
- **A7 — `bg.auth` used** on `/masuk`, `/daftar/1–3`, `/ganti-sandi`, with `bg.welcome` as fallback.
- **Deviation: Kelengkapan.** The plan said Kelengkapan lists `bg.auth` (and the placeholder) as optional. The checklist has no optional state, so adding them would count them as missing although the game runs fine without them. Both stay out of `AssetChecklist::UI_SLOTS` and appear as "opsional" in **Gambar & mockup**. Same for the new icon and story slots.
- **Tests.** `ImageSlotsTest` (types, transparency, lookup order), `ImageFitterTest::testSlotFileTypesAreEnforced`, `InstallableAppTest` rewritten (route, no session cookie, static fallback icons at the right sizes, slot icons with `?v`, layouts), `RouteWiringTest` (route, controller count), `MediaSlotPagesTest` (pose frames and animation, zoomable thumbnails, story backgrounds saved and removed, optional slots listed), `AssetPackageHelperTest` (placeholder slot), `StartGateTest` (`bg.auth` with fallback). Suite: 601 tests green. Browser (Chromium, harness page with the real `admin.js` and `admin.css`): lightbox opens and closes with Esc without leaving the page, a one-shot pose plays to its last frame and replays, a JPG for a transparent slot is refused before upload.

## Display update — Phase E: game-style toasts (11 October 2026)

Branch `tampilan-e-toast`. Implements Phase E of `docs/rencana-perbaikan-tampilan.md`, run in a parallel session next to Phases A → B → C.

- **Look.** Same stage place (x 856, y 72, width 400 dp). Navy gradient card, `.125rem` gold frame with a soft gold glow (red frame for `bad`), a 56 dp round badge with a ✓ ! ✕ i glyph, bold body-size text. Pop in (0.6 → 1.06 → 1), fade upward out; with reduced motion only a fade in and out. The badge glyphs are drawn in `components/toast.php` rather than taken from `icon()`, because the `warn`/`info` icons there are a triangle and a circle that look wrong inside a round badge (and `ui_helper.php` belongs to the other session).
- **Behaviour.** One toast at a time, at most 3 waiting (the oldest waiting is dropped when a fourth arrives). Identical to the one showing → its timer restarts; identical to the last waiting → skipped. Duration 1.2 s + 60 ms per character, clamped to 1.8–5 s. Tap or Esc closes; an Esc already used by a modal (`defaultPrevented`) or pressed while a modal is open does nothing. `bad` cards are `role=alert`, others `role=status`; once JavaScript runs the layer drops its own `role`/`aria-live` so the alert is not nested in a polite region.
- **Flash.** Without JavaScript the game flash fades after 3.5 s (it was 7 s; the plan says "about 3.5 s, as today", so I took the plan's number). With JavaScript flash toasts are removed from the DOM on load and enqueued first. Game `error` flashes render as `warn`.
- **Game vs admin.** `components/toast.php` uses `$withErrors` (true only in the game layout) as the game switch, since the layouts belong to the other session. `core/toast.js` switches on `body.game`; the admin keeps its stacked toasts and `admin.css` look unchanged.
- **Init without touching `game.js`.** `core/toast.js` sets itself up when the module is first evaluated; every game page imports it through `core/api.js`/`core/events.js`.
- **Tests.** New `GameToastTest` (game/admin markup, `warn` for flash errors, badge templates, script contract, `toastClose` in both locales, layer place and z order, CSS animations). Browser check in Chromium with a harness page of the real CSS and module: flash adoption order, size 400 dp at 856/72, tap and Esc close, merged duplicates, queue bound, Esc ignored with a modal open, taps outside the card pass through, timers.

## Display update — Phase B: avatars (11 October 2026)

Branch `tampilan-b-avatar`. Implements Phase B of `docs/rencana-perbaikan-tampilan.md`.

- **Data.** New migration `003200_AddParticipantAvatar` adds `participants.avatar_no TINYINT UNSIGNED NULL` (a new file, per the fresh-install rule); `REQUIRED_MIGRATION`, `ResetTables`, `ParticipantModel` (allowed field, rule `permit_empty|is_natural_no_zero`), and `Participant::toSafeArray()` follow. It is not research data: not in `RawDataService::COLUMNS['peserta']` (asserted by `AvatarTest`) and no event is logged when it changes.
- **Slots.** `Config\Gelita::$avatarSlots = 10`; slots `avatar.1` … `avatar.10` (512², PNG/WebP with transparency, optional, group "Avatar" in `ImageSlots`, display 72 dp). The seeder registers them empty under `assets/ui/avatar-N` (same reasoning as the icon slots in Phase A). `avatar_choices()` lists only uploaded slots; `avatar_src()` returns null for an emptied slot, so a student whose avatar was removed sees the initial again.
- **Component.** `components/avatar` replaces the inline initial in the HUD chip and the profile card (`has-art` with the picture).
- **Picker.** `components/avatar-picker`, round 64 dp tiles, "Huruf awal namaku" first, the chosen tile with a gold ring and a ✓ badge plus hidden text "dipilih". Not rendered while no avatar is uploaded. On `/daftar/1` (radio mode, below gender) the choice joins `STEP_FIELDS[1]`, is validated `permit_empty|in_list[uploaded numbers]`, and is passed to `SessionService::registerAndStart()`. In the profile it sits in a `<details>` "Ganti avatar" under the identity card; each tile is a submit button of one form (POST `/profil/avatar`, `gameSession` group), so one tap saves; a number that is not uploaded is refused with a flash `error`.
- **Language.** New `Game.avatar*` keys in id/en, placed inside the profile block (not at the end of the file) to avoid merge conflicts with the parallel E/F session, which may add `Game` keys too.
- **Tests.** New `AvatarTest` (choices only from uploaded slots, picker hidden until then, sign-up keeps and saves the choice, refuses a missing avatar, profile one-tap change and refusal and reset to the initial, HUD/profile pictures, not exported). `RegisterWizardTest` expects `avatar_no` among the step 1 fields. Suite: 606 tests green.
- **Browser.** The rendered `/daftar/1` (with a chosen avatar) and `/profil` (picker open) pages, served with the real `public/` assets in Chromium at 1280 × 720, 1920 × 1080, and 844 × 390 with touch: every stage rule of `tools/qa/stage-check.mjs` (`inspect()`) passes, tiles are 64 dp. The first render showed the heading "Ganti avatar" twice in the profile (summary and legend); the legend is now visually hidden there.

## Display update — Phase C: city pins, Kenali lanterns, city loading backgrounds (11 October 2026)

Branch `tampilan-c-kota`. Implements Phase C of `docs/rencana-perbaikan-tampilan.md`, with the owner's decision of a new slot per city for the curtain picture.

- **Slots** (all optional): `ui.pin`, `ui.lantern`, `map.pin.{wilayah}`, `map.lantern.{wilayah}` (256², PNG/WebP with transparency) and `bg.loading.{wilayah}` (under `bg.*`, 1920 × 1080). Registered by the seeder, described in `ImageSlots` (region slots link to the level editor), grouped per region in the asset package (`AssetPackageBuilder::groupFor()`); `ui.*` stays in `inti`.
- **Helpers.** `region_art_src()` (city → global → null), `region_loading_bg()` (city curtain picture → region background → null), `art_or_icon()` (`<img class="art">` or the built-in SVG icon), `region_curtain_attrs()`.
- **Pins.** With a picture, `.pin-icon.has-art` drops the gold disc; locked pins are greyscale with a lock mark, completed pins get a star mark, open and in-progress pins pulse with a glow (`pin-art-pulse`, off under reduced motion). Size (56 dp) and percent position are unchanged.
- **Kenali overlap fixed.** The 48 dp hit area of the pin's Kenali button covered the pin's top-right corner (14 × 38 dp), so a tap there opened Kenali instead of the region. The hit area now starts at the pin's right edge (`left: calc(50% + 1.75rem)`) and the face sits at its left, so it still reads as part of the pin. Browser check: 0 px² overlap at 1280 × 720 and 844 × 390.
- **Lanterns.** HUD lantern, map curtain lantern, Kenali buttons and card buttons use the uploaded lantern; the HUD flame stages become a growing `drop-shadow` glow.
- **Curtain picture.** `GameProgress::regionCurtain()` returns `bg` and puts it first in `preload`. `curtain_attrs()` writes `data-curtain-bg`; the `region` and `challenge` templates carry an empty `img.curtain-bg.is-deferred` that `core/curtain.js` fills and fades in after `load`. All four region callers use `region_curtain_attrs()`. On the map the pin also carries the picture in `data-preload` when it differs from the region background, so `map.js` warms it up; the first open region's list already goes to `next-assets`.
- **My call: mission brief.** The challenge curtain shows `bg.loading.{wilayah}` only when that slot is uploaded, not the region background: the challenge curtain has its own engine art in front, and the brief's background is already on screen behind it.
- **Level editor.** New section with `components/slot-field`: preview (lightbox), fixed code, file input only. The game reads these slots by key, so a code box (as in `media-field`) could save a picture under a key the game never reads. Removal stays in **Gambar & mockup** (Nonaktifkan).
- **Tests.** `RegionStoryTest`: pins and lanterns with fallbacks and status marks, HUD lantern art, package groups, curtain `data-curtain-bg` on pin, card, and last Kenali slide, preload order, map warm-up, fallback helper, JS wiring. `MediaSlotPagesTest`: level editor fields. Suite: 615 tests green. Browser (Chromium, rendered map with stand-in pictures, real assets): stage rules pass at 1280 × 720 and 844 × 390 with touch, no Kenali/pin overlap, a pin click fades the city picture in behind "Menuju Magelang…".

## Display update — Phase F: touch-friendly play (11 October 2026)

Branch `tampilan-f-sentuh`. Implements Phase F of `docs/rencana-perbaikan-tampilan.md` (F1 CSS basics, F2 48 dp hit areas, F3 gestures and screens), run in a parallel session next to Phases A → B → C. The CSS and the gesture JS were written by two subagents with separate file ownership; the coordinating session did `game.js`, `core/keyboard.js`, the form attributes, `stage-check.mjs`, tests, and docs.

- **F1.** `body.game` has no tap highlight; controls and the stage use `touch-action: manipulation`; play surfaces are not selectable and show no long-press callout (reading text stays selectable); scene, map, and character images are not draggable (CSS plus `draggable=false` and a `dragstart` guard in `game.js`, because `map-kedu.php` and `components/character.php` belong to the other session). `:active` press feedback everywhere listed; `game.js` adds the passive `touchstart` listener iOS needs for it. Hovers that transform or highlight a border moved inside `@media (hover: hover)` in place (small diff); colour-only hovers stay. Explicit reduced-motion rules for `start-glow`, `.kenal.is-open`, and the hover/selection/press transforms (`cine-rise` already had one).
- **Puzzle press feedback brightens instead of scaling.** A scale on `.piece` would also scale the inline translate of the dragged art, so the art would lag the finger.
- **F2.** One shared centred `::before` of `max(100%, var(--tap))` on `.btn` and `.icon-btn` covers `.btn-sm`, story Lanjut/Kembali, HUD and narrator icons; own `::before` for `.narrator-auto`, `.chip-btn`, `.lang-opt`, `.word`, `.blank`, `.hud-brand` (38 dp), and the Benar atau Salah passage chip `.card-passage` (30 dp; both found by the new QA check). Slide dots get an area as wide as dot plus gap (neighbours never overlap). Checkboxes and radios already sit in 48 dp `.check` labels. `.media-credit` summary went from 44 to 48 dp. Exception: the map story's stacked narrator buttons keep their 36 dp height, since taller areas would cover the next button. `stage-check.mjs` now reports any interactive element in `#stage` whose box and pseudo-elements are all under 48 dp, with 1 px tolerance for rem rounding at 1366 × 657. Exempt: links inside running text, and the two documented exceptions (slide dots, map story narrator buttons), both of which repeat full-size Lanjut/Kembali. **Owner call:** if the dots must be 48 dp wide, their spacing has to grow to 48 dp (9 dots = 432 dp in the story caption, up to 15 in the dialogue box), which changes their look. **Owner decision (11 October 2026): keep the dots as they are**; they are an indicator, and the full-size Lanjut/Kembali buttons and the swipe move the slides.
- **F3.1 drag.** New `core/drag.js` (`initDrag`, pointer capture, 8 px threshold, click swallowed after a real drag) replaces HTML5 drag-and-drop in `engines/puzzle.js` for mouse and touch; the drop calls the same `swap()`, so events are identical to tap-swap. The art moves by `translate` divided by the element's own on-screen scale (1 today: the stage scales through rem, not a transform). `Game.puzzleHintJigsaw` now mentions dragging too.
- **F3.2 zoom.** `#hunt-zoom` (`Game.huntZoom` "Perbesar adegan" / `Game.huntZoomClose` "Kecilkan adegan") toggles `.hunt.is-zoomed`: scene 0, 0, 1280 × 720 at z 40, clue ribbon on top (taps pass through it), button at z 42, below modals (50) and toasts (60). `overlay_opened` `{ overlay: 'hunt_zoom', level }` on open only. Esc closes it unless a modal, dialog, or lantern is open; the zoom stays open during wrong-tap and skipped-clue modals and closes itself when the attempt completes. `hunt_zoom` added to the overlay lists in `docs/06`, `docs/07`, and the `event_type` description in `ExportLabels` (the export dictionary had no overlay list before; it now names all six values).
- **F3.3 swipe.** `onSwipe()` in `core/drag.js` (touch/pen, 50 px, horizontal must dominate; ignored in scrolling panels, on controls, and while a modal is open). `game/slides.js` turns a swipe into a click on the current slide's own Lanjut/Kembali link, so events and narration follow the buttons exactly; it never follows the final link that leaves the screen. Wired through `narrator.js` (story, dialogue, region-done, Kenali, map story) and `library.js` (Pustaka).
- **F3.4 keyboard.** New `core/keyboard.js`, loaded only when `#stage` has a form field: `.stage-frame` gets `translateY(-n px)` just large enough to show the focused field above the keyboard (from `visualViewport`), never pushing its top out of view, and returns on blur, keyboard close, and `pageshow`. `autocorrect="off"` on usernames and school fields; `enterkeyhint` next/done on login, sign-up, current-password, and new-password fields (the new-password pair was added in the follow-up below).
- **F3.5.** `maximum-scale=1` is appended to the viewport meta on iOS only (iPadOS detected by `Macintosh` + touch points).
- **`stage-check.mjs` speed fix.** `check()` read the attempt id with `page.getAttribute()`, which waits 30 s for a selector that only challenge screens have; every other screen took 30 s. It now uses `$eval`, so a full six-size run takes about 15 minutes instead of hours.
- **QA run.** Local SQLite copy of the game world (the `GradeFiveWorld` test trait written to a file, plus `ContentSeeder` for story and dialogue; no Pustaka pages, so Pustaka reading pages were checked only in the harness), served by `php -S` in development mode. `stage-check.mjs` at all six sizes plus the portrait rotate gate: 282 screen checks, no layout, overflow, position, or JS errors; the only failures were the hit areas fixed or exempted above, and a rerun of the affected screens at 1280 × 720, 1366 × 657, and 844 × 390 passes. Playwright with `hasTouch`/`isMobile` at 844 × 390 and 1024 × 768 on the real server: `keyboard.js` loads on `/masuk`, a finger drag swaps two puzzle pieces and leaves no drag classes, the zoom button is 48 dp (26 px and 38.4 px on screen), the zoomed scene fills the stage, an object tap while zoomed opens its result modal above the scene, the button closes the zoom, swipe left/right moves the region dialogue forward and back, no page errors; an iPad user agent gets `maximum-scale=1`, Android does not.
- **Tests.** New `TouchPlayTest` (touch basics, no transforming hover outside the hover query, reduced-motion rules, hit areas, drag wiring and no HTML5 drag, zoom button and event, swipe/keyboard wiring, form attributes). The subagents checked drag, zoom, and swipe in Chromium harnesses with CDP touch events at 844 × 390 and 1024 × 768 and with a mouse (75 checks), and every hit area with `elementFromPoint`.
- **Follow-up: keyboard hints on the new-password fields (branch `tampilan-f-sandi`).** `components/password-field.php` now gives the password input `enterkeyhint="next"` and the confirm input `enterkeyhint="done"`. Both callers (`/daftar/3`, `/ganti-sandi`) put the confirm field right after the password field and have no text field after it (`/daftar/3` only has the assent checkbox), so the hints are fixed in the component instead of an extra view parameter. The reflection and feedback textareas (`game/reflection.php`) keep no `enterkeyhint`: Enter there adds a new line, which is already the platform default (`enter`), so the attribute would change nothing. `TouchPlayTest::testPasswordFieldsCarryKeyboardHints` renders both pages and asserts the two hints.

## Asset catalogue plan status brought up to date (11 October 2026)

Branch `sky/ecstatic-cerf-kzfsve`. Owner request: check the progress of `docs/rencana-katalog-aset.md` and update the document. Docs only; no code changed.

- **Finding.** The plan's status notes predated PRs #80–#89. They still said `ImageSlots` did not exist and that app icons, story slide backgrounds, and the 21 planned slots (pins, lanterns, city loading backgrounds, avatars) could not be set from the panel. The follow-ups listed under "Asset catalogue decision and admin image slots" above are closed: story backgrounds, app icons, and `bg.auth`/`ui.placeholder` by PR #87, avatars by PR #88, city art by PR #89.
- **Changes.** A new status note at the top of the plan says what is done, what is still open, and which earlier notes are outdated. §11.3 says the app icons are now `app.*` slots, with the static files kept as fallbacks. §11.4.2 says story slide backgrounds are set per slide, with codes `bg.story.{intro|ending}.{n}` instead of the proposed `bg.cerita-*`. §11.10 says its slots are live and lists their fallbacks and display sizes. Part I stays not built.
- **Open for the owner: avatar brief.** `ImageSlots` asks for head-and-shoulders portraits of a child or a friendly figure. The plan's §11.10 asks for neutral emblems without faces, so that no child has to choose by looks or gender. One of the two should be changed before avatar art is ordered. The `ui.pin` and `ui.lantern` texts also differ slightly; those differences are noted in §11.10 and need no decision.

## False "Koneksi terputus" toast while online (11 October 2026)

Branch `pbb/kind-dijkstra-wmb094`. Owner report: from sign-up onward, game pages showed the toast "Koneksi terputus — langkahmu tersimpan sementara …" although the device was online and every page worked.

- **Cause.** `core/events.js` uploads events in the background every 3 seconds. When the student moves to the next screen while a batch is still on its way, the browser cancels that request (`net::ERR_ABORTED`). `apiRequest` reports it as `NETWORK`, and `keepForLater()` showed `savedOffline` at once, on the page that was leaving. View transitions keep the old screen on display a little longer, so the toast was easy to see. Reproduced in Chromium on a local MariaDB copy: sign up, delay `/api/events`, open the next screen → toast; nothing was lost (the batch was in the offline queue and the beacon copy, and the next page resent it).
- **Fix.** The toast now waits until the failure holds: the browser reports `navigator.onLine === false`, `RATE_LIMITED` remains after `apiRequest`'s own three retries, or a quiet retry after 5 s (`RETRY_MS`, through `resend()`) fails too. A cancelled batch never gets that far, because the page is gone before the retry. The queue logic is unchanged: the batch is still stored first, and any accepted batch resets the failure count.
- **Browser check (Chromium).** Navigation with an upload in flight: three aborted requests, no toast, events arrive on the next page. `context.setOffline(true)`: toast at once. Online but the server refuses connections: first failure quiet, toast after the retry 5 s later.
- **Tests.** `EventQueueNoticeTest` pins the rule in `core/events.js` (retry constant, the three conditions, the retry through `resend()`, the reset on success). Suite green.

## Production console errors: CSP and `/api/events` 500 (11 October 2026)

Branch `pbb/practical-ramanujan-sogpm3`. Owner report: the browser console on `https://gelita.id/intro` shows CSP violations (inline script, Cloudflare beacon, `data:` audio) and `POST /api/events` answering 500.

- **CSP source.** Production sends CodeIgniter's own header (`curl -I https://gelita.id/intro`), so `app.CSPEnabled = true` is set in the server's `.env`; the repo default stays off. The policy was CodeIgniter's untouched sample, which blocks things GELITA uses.
- **Inline script.** The one inline script in a view (the `--app-w`/`--app-h` setter in `layouts/game.php`) moved to `public/assets/js/core/viewport.js`, a classic blocking script in `<head>` before the stylesheets, so it still runs before the first paint. Same hash as in the production error (`sha256-ZARdQS1…`), so this was the blocked script. The asset package builder picks up the new file through its `assets/js` rule.
- **Policy (`Config\ContentSecurityPolicy`).** Scripts, styles, fonts, connections, workers, and the manifest from `'self'`; `style-src-attr 'unsafe-inline'` because 17 views set positions and CSS variables in `style` attributes (attributes cannot carry nonces, and rewriting them is out of scope); `img-src`/`media-src` add `data:`, `blob:`, and `https:` (the silent WAV in `core/audio.js` and Howler's unlock, plus admin-entered library media on any host via `MediaLink`); `frame-src` only the three players `MediaLink` embeds; `object-src 'none'`; `frame-ancestors 'self'`.
- **Cloudflare beacon not allowed.** `static.cloudflareinsights.com` is injected by Cloudflare Web Analytics, not by the app. A third-party script on a children's research game is not added to the policy; the owner can turn off the automatic injection in the Cloudflare dashboard (Web Analytics / RUM). If the owner wants it, add the script host to `script-src`/`script-src-elem` and `cloudflareinsights.com` to `connect-src`.
- **`/api/events` 500 is a server-state problem, not code.** It does not reproduce on a fully migrated local MariaDB in production mode with the CSP on. Dropping `game_event_logs.clock_offset_ms` (added by migration `2026-01-01-003100`) reproduces it exactly: sign-up and pages work, every event batch answers `SERVER_ERROR`, and the log says `Unknown column 'clock_offset_ms' in 'INSERT INTO'`. Action for the owner: check `writable/logs/log-2026-10-11.log` on the server for that line and run `php spark migrate:status` / `php spark migrate` with the migration account (as `deploy/linux/deploy.sh` does). Never run reset there.
- **Service worker warning.** "navigation preload request was cancelled" appears when a navigation is cancelled mid-flight; `sw.js` already awaits `preloadResponse`. Harmless, left as is.
- **Checks.** New `ContentSecurityPolicyTest` (directives with CSP on, no inline `<script>` in any non-error view, `viewport.js` before the stylesheets). Chromium on local MariaDB with the production `.env` and `app.CSPEnabled = true`: before the change, the same inline-script and `data:` media errors as production; after, none on sign-up, intro, map, mission brief, profile, and library, `--app-w`/`--app-h` still set, all `/api/events` 200. Suite green.

## Asset catalogue plan aligned with `gelita-katalog/` (11 October 2026)

Branch `pbb/nifty-faraday-fcw9r6`. Owner request: use the references and images in `gelita-katalog/` (the team catalogue, now committed in the repo) to update `docs/rencana-katalog-aset.md`, and ask before resolving conflicts. Docs only; no code changed.

- **Owner decisions on conflicts.**
  - **Style: stylized 3D** (catalogue), replacing 2D gouache. The `{GAYA}`, `{NEGATIF}`, `{FRAME}`, and `{LENTERA}` blocks were rewritten, and the map prompts now describe 3D relief.
  - **Jaka: red-scarf design** (`reference.01`/`.03`/`.13`), replacing lurik and iket. This reverses the 10 October note above that called the red-scarf entries "reference only".
  - **Mbah Kedu:** outfit from `reference.14`, face from `reference.02`, and he keeps the walking stick. The blangkon and jarik question is closed.
  - **WebP is the default raster format.** `app.*` stays PNG, because the slots refuse WebP.
  - **Logo:** `ui.logo-hero` ← `reference.09`, `ui.logo` ← `reference.16`.
  - **Start button:** two finished images with their labels (MULAI, START), styled like `reference.10`.
  - **Not used:** `reference.15` (supporting character) is not needed; `reference.12` and `reference.17`–`.24` are reference only.
  - **UI icons:** the catalogue's redrawn shapes are the target, for a later code change that keeps `currentColor`.
- **Corrections found on the way.**
  - Region maps have four stops at `ContentSeeder::NODES` positions, not five.
  - The `ui.logo` screens follow `ImageSlots`.
  - The catalogue's `ui.logo-hero`, `ui.logo`, and `ui.btn-start(.en)` files are an ornament or flat placeholders, not the final art.
  - Fitting commands for `reference.09` (1600 × 700 → padded to 8:3 → 1600 × 600) and `reference.10` (960 × 288 → 960 × 320 → 720 × 240) were checked with ImageMagick: they give the slot sizes and keep alpha.
- **Still open.**
  - **Character frames.** The 30 catalogue frames still show the old lurik–iket design and must be redrawn.
  - **Missing button.** There is no START version of `reference.10` yet.
  - **Panel texts still describe the old art.** `ImageSlots` texts for `ui.btn-start` (gold button, dark letters) and the avatar brief (portraits; the catalogue avatars are faceless emblems) do not match.
  - **Library images.** The catalogue's 60 Pustaka images are AI illustrations, while §9.5 asks for real photos for factual pages. They also include a recognisable child with gimbal hair (rule 5), an illustrated regency emblem, and the old Jaka costume. The owner has not decided yet, so the plan's rules are unchanged.
  - **Icon code change.** Bringing the catalogue icon shapes into `icon()`, including drawing the 11 game icons the catalogue lacks.

## Catalogue images into the game, 24 looping frames, finished plans archived (11 October 2026)

Branch `pbb/nifty-faraday-fcw9r6` (second PR on it; the first, PR #100, is merged). Owner request: use every usable catalogue image, write a migration script that puts them in the database, make the admin image pages and the live game follow the latest decisions, support up to 24 looping frames per character pose, update the old design descriptions, and say whether the finished plans should be renamed.

- **Owner answers (11 October 2026).**
  - AI Pustaka illustrations are fine for now; the owner replaces them in the panel later.
  - Each pose has up to 24 frames, always looping.
  - Old design texts are updated.
  - The 30 old-design catalogue frames are imported as interim art.
  - The START button is generated from `reference.10`.
  - Region map challenge buttons move onto the catalogue maps' clearings.
  - Finished plans are archived.
- **Import command `gelita:katalog:impor` (`App\Libraries\CatalogImporter`).** It reads `gelita-katalog/data.json` and stores each usable image through `MediaStore::storeCopy()`, so the same type and size checks and the same shrinking apply as for a panel upload.
  - **What it installs:**
    - slot keys the game reads by name;
    - app icons, converted to PNG because `app.*` takes PNG only; the favicon is made from the 192 icon, since the catalogue's is 32 px;
    - story slide backgrounds as `bg.story.{intro|ending}.{n}`, linked to `dialogues.background_media_id`;
    - library illustrations on media `i` of page `n`: they replace the Commons link, or add a media row for a page without one;
    - video covers as `poster_media_id`.
  - **What it skips:** reference images, vector icons, and the picture of a video entry.
  - **Re-encoding:** every image is re-encoded as lossy WebP q82. The catalogue keeps lossless WebP (2 MB backgrounds), which would break the loading curtain's 8-second budget. The full catalogue shrinks from 108 MB to 18 MB, and alpha is kept for transparent slots.
  - **What it never replaces:** an admin upload, and a previous catalogue import. Both are replaced only with `--timpa`.
    - Installed rows carry the credit `Katalog Aset GELITA (gambar sementara)`, and `MediaStore::upsert()` clears it when an admin uploads a new file.
    - Video covers already downloaded by `gelita:library:thumbnails` count as real images and stay.
  - **Region maps:** for the three catalogue maps (matched by sha256), each region's four `challenge_nodes.map_x/map_y` are moved onto the map's clearings (measured on a 5% grid, in path order).
  - **Check against the real catalogue** (SQLite grade-5 world with Pustaka): 183 installed, 0 failed, and a second run installs nothing.
- **Catalogue edits (`gelita-katalog/`).**
  - `ui.logo-hero.webp` ← `reference.09` (padded to 8:3) and `ui.logo.webp` ← `reference.16`. The old files were ornaments without the logo.
  - `ui.btn-start.webp` ← `reference.10` (padded to 3:1).
  - `ui.btn-start.en.webp` is a START version of `reference.10`: the MULAI letters were removed by rebuilding the enamel between two clean columns, and START is drawn in the repo's Cinzel Bold with the gold sampled from the original letters.
  - `data.json` was updated; `node build.mjs` and `node scripts/validate.mjs` pass.
- **24 looping frames.**
  - `Config\Gelita::$characterAnimations` gives every pose `frames` 24, `loop` true, and `seconds`, the length of one loop.
  - Speed follows the number of frames actually uploaded (`character_fps()`, 0.5–24 per second): 24 frames in 2 s play at 12 per second, and 3 interim frames in 3 s at 1 per second, so few frames do not flicker.
  - `core/sprite.js` accepts speeds below 1.
  - **Seeder:** `MediaAssetSeeder` registers only frame 1 of each pose. Frames 2–24 get their row on upload, or when a file `assets/char/{pose}-{n}.*` exists. 240 empty rows would bury the Tokoh group.
  - **Panel:** new rows for `char.*` keys are `sprite_frame`. Kelengkapan marks a pose missing only when frame 1 is missing and shows `n/24`.
  - **Detail page:** the slot detail page lists all 24 frames (each links to its own page) and has a new form, **Unggah semua gambar gerak pose ini** (`POST admin/media/{id}/unggah-gerak`, `MediaController::uploadFrames`). It takes files in natural name order from a chosen start frame and warns about PHP's `max_file_uploads`.
- **Old design texts.** `ImageSlots` texts now describe:
  - the 3D, red-scarf Jaka and the `reference.14` Mbah Kedu;
  - the catalogue logo, emblem, and image buttons;
  - 3D relief maps;
  - faceless avatar medallions, as in the catalogue. This settles the avatar brief question in favour of the archived plan's emblems.
  - WebP in every format line except `app.*`.
- **Not changed, reported to the owner** (fixed on 12 October 2026, see *Cari Objek objects onto the scene tables*). In the three catalogue Cari Objek scenes, the objects (positions fixed in the approved question bank) do not sit on the drawn shelves; top-row objects float in the sky. The bank is frozen (`docs/bank-soal/README.md`), so the scenes need redrawing to the bank positions, or the owner decides to move the positions as a new release.
- **Docs.** `docs/rencana-katalog-aset.md` and `docs/rencana-perbaikan-tampilan.md` moved to `docs/arsip/` with a "finished" note and `docs/arsip/README.md`; links in code comments and docs were updated. Older entries in this log keep the names they had. `docs/rencana-paket-aset.md` stays, since Stages 2, 3, and 6 are open.
- **Tests.**
  - New: `CatalogImportTest` (fake catalogue).
    - **What it covers:** dry run, slots, PNG app icons, story and library links, video cover, the map stops, a second run, admin uploads with and without `--timpa`, and the credit cleared on upload.
    - **Guard:** it checks that the measured map hashes still match the catalogue files.
  - Updated: `CharacterFramesTest` (24 frames, loop, `character_fps()`), `MediaSlotPagesTest` (24-frame page, upload form, frame links), `NarrationPagesTest`, and `RegionStoryTest`.

## New character frames from the catalogue: 24 per pose (12 October 2026)

Branch `pbb/nifty-faraday-fcw9r6` (third PR on it; #100 and #101 are merged). Owner request: the catalogue now has new frames for Jaka and Mbah Kedu, 24 per pose; add them to the game and update the documents.

- **What the owner pushed** (`main`, commits `c132e4b`, `8b4d358`): `gelita-katalog/assets/karakter/` now holds 240 transparent WebP frames, 700 × 900, ten poses × 24.
  - The 30 old lurik–iket frames are replaced; the old ones are backed up in `gelita-katalog/archive/before-character-frames-20261012/`.
  - `data.json` has 580 entries (483 visual). The catalogue's own docs, reports, and provenance were updated. Ten AI key poses were given deterministic secondary motion with the feet fixed (`gelita-katalog/reports/character-frame-report.json`).
  - **Mbah Kedu follows `reference.02`** (cream shirt, batik vest, brown trousers, stick), not the `reference.14` jacket chosen on 11 October. The `ImageSlots` text follows the frames, since they are the owner's art.
- **Merge leftovers fixed.** The push left unresolved conflict markers in four catalogue files. The owner fixed them on `main` as well (`96a68e9`); this branch merged `main` and keeps `main`'s version of the four files, plus one note in the catalogue's `docs/PROGRESS.md`. This branch's own fix was:
  - `docs/PROGRESS.md`: both entries kept.
  - `index.html`, `reports/STATUS-GAMBAR.json`, `reports/validation-report.json`: regenerated with `node build.mjs` and `node scripts/validate.mjs` (validator passes).
  - `STYLE-AUDIT.csv` was kept with LF line endings.
- **Import change: a changed catalogue file is reinstalled.** Before, an image installed from the catalogue was skipped on every later run, so a server that had imported the old 3 frames per pose would never get the new frames 1–3. Now `CatalogImporter::installSlot()` re-encodes the catalogue file and compares its sha256 with the stored file. The re-encode is deterministic, so the outcome is:
  - same sha256 → skipped ("gambarnya sama");
  - different sha256 → reinstalled ("gambar katalog berubah, dipasang ulang");
  - admin uploads stay protected unless `--timpa` is given.
- **Upgrade check** (SQLite grade-5 world with Pustaka): import the previous edition (`6d30b54`), then the new one.
  - The new edition replaces 30 frames, adds 210, and skips the other 177 entries as unchanged; a third run skips everything.
  - All 240 frame rows are `sprite_frame`, about 15 MB after re-encoding (≈ 64 KB per frame).
  - `character_frames('jaka', 'happy')` returns 24 frames at 12 per second, looping.
- **Tests.** `CatalogImportTest::testChangedCatalogueFileIsReinstalled`. Suite green.

## Cari Objek objects onto the scene tables (12 October 2026)

Branch `pbb/nifty-faraday-fcw9r6` (fourth PR on it; #100–#102 are merged). Owner request: fix the three Cari Objek scenes. Of the options offered (move the objects, redraw the scenes, or leave them), the owner chose **moving the objects onto the drawn tables and shelves**. This is a **layout change to the approved question bank**: only `x`, `y`, `w` of the 30 objects change; objects, answers, decoys, clues, feedback, and item codes stay the same.

- **How the positions were measured.** Each catalogue scene (`gelita-katalog/assets/tantangan/challenge.{tmg,mgl,wnb}-4.scene.webp`, 1280 × 720) was gridded in 1 % steps. Each object's bottom edge (the item pictures have about 4 % transparent margin at the bottom) is placed in the middle of the depth of a table or shelf top. Each layout was checked by drawing the 30 catalogue object pictures onto the scenes.
  - **Temanggung** (three long tables, tops at about 47 %, 61 %, 76 %): 3 · 2 · 3 objects, zigzagged so no object hides another. Widths 10 / 11 / 12 % (smaller further back).
  - **Magelang** (pendopo, three tables on each side, centre aisle empty): per side 3 objects on the front table (w 8), 1 on the middle table (w 7), and 1 on the back table (w 6). The smallest is 52 px in the normal view, still above the 48 px tap minimum.
  - **Wonosobo** (bamboo shelf, wooden table, bamboo bench): 3 · 4 · 5 objects, w 10 as before.
  - Largest overlap is 13 % (limit 20 %, `BankRules::MAX_OVERLAP`). `build-workbook.php` reports 0 warnings.
- **Bank source and workbook.** `docs/bank-soal/data/{temanggung,magelang,wonosobo}.php` were changed and the workbook rebuilt. The three scene notes (`scene_media_note`, shown in the panel's Media page) now describe the catalogue scenes and their surface heights, also in the slot table of `docs/bank-soal/README.md`. The old Temanggung note ("tiga deret rak kosong … 27 %, 61 %, 93 %") described a layout no longer used.
- **Servers that already have the bank.** `CatalogImporter::SCENE_OBJECTS` maps the sha256 of each measured scene to every object's old and new position. `gelita:katalog:impor` handles it like the map buttons (`MAP_STOPS`), whenever that exact scene is installed from the catalogue:
  - an object still at its **old bank position** is moved;
  - an object already at the new position is skipped;
  - an object at **any other position** (placed by an admin in the challenge editor) is kept, unless `--timpa` is given.

  `gelita:bank:import` also writes the new positions, but it resets admin placements, as it always has.
- **Not changed.** The `.object` button stays a square circle. One catalogue object picture, `challenge.item.wnb-4-a07` (mi ongklok), has an opaque square background instead of a transparent one, so it shows as a picture tile on the bench. That is for the catalogue team or the panel to fix.
- **Tests.** In `CatalogImportTest`:
  - `testMeasuredSceneMovesObjectsOntoItsTables` covers old position → moved, admin position → kept, already moved → skipped, `--timpa`, and dry run;
  - `testMeasuredScenesMatchTheCatalogueAndTheBank` is a sha guard, and also checks that the bank carries exactly the positions in `SCENE_OBJECTS`.

## Catalogue start buttons, language icons, red-scarf frames, logo-only HUD centre, dialogue poses (12 October 2026)

Branch `pbb/hopeful-bardeen-48ljgp`. Owner request: use the catalogue's two start buttons (MULAI, START) per language; replace the blue lurik Jaka in the frame animation with the red-scarf Jaka from the catalogue; use the catalogue's language icons for the language button; show only the logo in the HUD centre (`hud-center hud-logo`), with no backing or border; check the frame changes in the dialogue so frames do not clash and follow each line's pose.

- **Why the server can still show old art.** The code already read `ui.btn-start` / `ui.btn-start.en` and the 24 red-scarf frames per pose; the catalogue files are correct (checked visually). `gelita:katalog:impor` never replaces an **admin upload** without `--timpa`, so old art uploaded in the panel before the catalogue (an older button, lurik frames 1–3) stays, while the catalogue fills the empty frames 4–24. The pose then loops between two drawings. A release on an asset package built before the import also keeps showing the old pictures. Nothing in the repo can change the server's database, so the fix is a safe, scoped command plus a warning:
  - **`--kunci=pola,pola`** (`CatalogImporter::run(..., $only)`) limits a run, and so `--timpa`, to slots matching fnmatch patterns (catalogue key or the slot a reference fills). For the owner's server: `gelita:katalog:impor --timpa --kunci="ui.btn-start*,ui.lang.*,char.jaka.*"` (dry run first), then `gelita:paket:bangun` if a release uses a package.
  - **Mixed pose warning.** After a run without `--timpa`, a pose with active frames from both a kept upload and the catalogue is reported with the new status `warning` ("perlu dicek"), naming the frames and the exact `--kunci` to use. The command's summary counts it and turns yellow.
  - Checked on local MariaDB: imported the previous catalogue edition (`6d30b54`, lurik frames), marked `char.jaka.idle.1–3` and `ui.btn-start` as admin uploads, imported the current edition → `char.jaka.idle` reported, `ui.btn-start` skipped; the scoped `--timpa` run installed 148 (2 buttons, 2 icons, 144 Jaka frames) and touched nothing else; a plain run afterwards skips everything.
- **Language icons.** New slots `ui.lang.id` and `ui.lang.en` (brand group, optional, 256 × 256, shown 44 dp; seeder paths `assets/ui/lang-{id,en}`; in Kelengkapan aset). The importer fills them from `reference.20` (ID) and `reference.19` (EN) through `CatalogImporter::REFERENCE_SLOTS`; other `reference.*` entries are still skipped. `components/lang-switch.php` shows both round icons with no backing (the chosen one bright with a gold glow, the other dimmed to 70 %) and keeps the text pill for both while either icon is missing, so an image never sits next to a text button. Each icon button is named "Bahasa Indonesia" / "English" (`Game.lang_id`, `Game.lang_en`, the same in both language files, since a language is named in itself).
- **HUD logo.** `.hud-center.hud-logo` drops the padding, rounded corners, and `--hud-backing` that `.hud-center:not(:empty)` gives region names; only the `ui.logo` image shows. The text fallback (`.hud-brand`) keeps its own backing for readability.
- **Dialogue frames.**
  - **CSS fought the frames.** The pose effects in `cinematic.css` (hop for `happy`, shiver for `afraid`, lift for `determined`, -3° tilt and desaturation for `sad`/`weak`) were written for the monogram but also moved the drawn 24-frame poses, which carry their own motion. They now apply only to a figure that shows the monogram (`:has(> .character-fallback)`); the reduced-motion overrides use the same selectors.
  - **Frame player race.** `core/sprite.js` `setFrames()` only cancelled the previous pose when the figure was visible. A decode still pending for the old pose could then finish after a pose change and put the old frames back. It now always stops the timer, invalidates the pending decode, and resets the frame index before taking the new pose.
  - **Browser check** (Chromium, both opening dialogues, Temanggung 15 lines and Magelang 16): each line was shown and both figures sampled every 40 ms for 2.5 s. 0 frames from another pose, the speaker always on its line's pose with 15–24 distinct frames, no CSS animation or rotation on the drawn figures. The listener keeps looping the pose of its own last line.
- **Not changed.** The dialogue box still covers the lower part of the figures and their name plates (existing layout, not part of this request).
- **Tests.** `CatalogImportTest`: language icons from their references, `--kunci` limiting `--timpa`, mixed pose warning (and gone after the scoped overwrite). `StartGateTest`: icon switch only when both icons exist, accessible names, slot size and seeder path. Suite green (651 tests).

## Smaller language icons (12 October 2026)

Branch `pbb/hopeful-bardeen-48ljgp` (second PR on it; #104 is merged). Owner report: the ID and English icons of the language switch show too large.

- **Size.** The icons are now 32 dp (`2rem`), the size of the other HUD icons, instead of 44 dp. Each button keeps a 40 dp box, like `.hud .icon-btn`, and the 48 dp hit area from `.lang-opt::before`. `$assetDisplaySizes['ui.lang.*']` and the slot tip say 32 dp.
- **Old stylesheet.** The `<img>` used to carry `width="256" height="256"` (the file size). A browser or an asset package that still served the `components.css` from before PR #104 had no `.lang-switch.is-image` rules, so it drew the icons at 256 px. The attributes are now the display size (32 × 32), so the icons stay small even then. Checked in Chromium with `components.css` blocked: 32 × 32.
- **Tests.** `StartGateTest` checks the 32 px attributes.

## Clue play button, modal icons, form errors in a modal, staff link (12 October 2026)

Branch `pbb/hopeful-bardeen-48ljgp` (third PR on it; #104 and #105 are merged). Owner request: the play button of the Cari Objek clue narration overlaps or looks messy; some modal icons are cut off (they may stick out above the box); on sign-up, empty fields should open a modal explaining the problem without reloading the page; a wrong login should also be a modal without a reload; a thin link to the admin login in the bottom-right corner of the welcome page only.

- **Clue play button.** `game.css` had two rules for `.hunt-clue-body.has-voice`. The later, older one (two columns) overrode the three-column layout (label and tries | ▶ | clue text), so the button and the clue text shared one cell. The old rule is removed. Checked in Chromium on `tmg-4` with approved narration: label 97–273 px, button 285–333 px, text from 345 px, no overlap.
- **Modal icons.** The game modal card scrolls (`max-height` 35rem, `overflow-y: auto`, stage.css), which clipped the round icon pulled above the card with a negative margin. The icon is now the dialog's first child, outside the card (`components/modal.php`, also the fallback markup in `core/modal.js`). It rises 32 dp above the card and overlaps 32 dp into its top padding. The kind is now on the dialog as `data-type`, used for the icon colour; the `modal-{type}` class stays on the card. The entrance animation moved from the card to the dialog, so the icon animates with it. The panel shares the markup: `admin.css` puts the icon back in the card's top-left corner (checked in Chromium, same look as before).
- **Sign-up: empty or invalid fields in a modal.** New `game/form-check.js`:
  - It sets `noValidate` (JavaScript only; without it the browser bubbles and the server still apply) and adds a capture-phase submit check before device.js and the double-submit guard.
  - It lists every field that is empty or invalid in one modal ("Masih ada yang kosong" — "Isi dulu bagian ini, ya: Nama lengkap, Umur dan Jenis kelamin."). It marks the fields with `.has-error` and focuses the first one when the modal closes.
  - Field names come from `data-field-name`, the `<label>`, or the fieldset `<legend>`; the assent checkbox uses `Game.assentName`.
  - It replaces `markInvalidFields()`. Server validation is unchanged; errors only the server can find (e.g. a taken name) still come back with a reload.
- **Login without a reload.** `game/login.js` sends the form with fetch and `X-Requested-With: XMLHttpRequest`. For that request `LoginController::login()` answers JSON instead of a redirect:
  - success and must-change-password: `{ ok: true, redirect }`;
  - wrong password, locked account, or empty field: `{ ok: false, field, message, csrfName, csrf }`.
  - A failure opens a modal ("Belum bisa masuk" with the server's message); the name stays, the password is cleared and focused.
  - Because `/masuk` is not loaded again after a failure, the controller keeps the `redirect_after_login` flash for the next try.
  - Any other answer (CSRF page, rate limit, network error) falls back to the plain POST. Without JavaScript the form still posts and redirects.
- **Staff link.** A thin "Masuk staf" link (key icon, 12 px text, 60 % opacity, brighter on hover or focus) in the bottom-right corner of `/` only, to `/admin/login`. This reverses the earlier rule in `StartGateTest` that the welcome page has no admin link; the test now allows exactly this one link.
- **Language keys.** New `Js.formMissingTitle`, `formMissingText`, `formInvalidTitle`, `formInvalidText`, `formAnd`, `formFix`, `loginFailedTitle`, and `Game.assentName`, `Game.staffLogin`, each in both languages.
- **Tests.**
  - New `LoginModalTest`: JSON on a wrong password, an empty field, success, and must-change; the kept `redirect_after_login` flash (fails without the fix); a plain POST still redirects.
  - `StartGateTest`: staff link only on `/`; the modal icon sits outside the card.
  - Browser check in Chromium on local MariaDB: all three sign-up steps and the login stay on the page (0 navigations) and show the modal; a correct login moves on. Suite green (659 tests).

## Stay on the challenge screen until Lanjut; blank number inside the box (12 October 2026)

Branch `sky/trusting-allen-tqi5le`. Owner request: when a challenge is finished, do not jump straight to the finished screen; stay on the last screen and move on only when the student taps the next button, so that e.g. Susun Gambar shows the whole picture. In Isi Rumpang, the blank number moves inside the box's top-left corner with the box's radius.

- **Hold on the finished screen.** `challenge.js` `finish()` no longer redirects. It stops the timer, updates the lantern, hides the check, hint, and exit buttons, and shows a hidden-by-default Lanjut button (`#btn-next`, rendered by each engine view: the action bar for puzzle/rumpang/boleh, under the target list for cari). Lanjut goes to `redirect` (fallback `POST /complete` unchanged). The closing modal's button is now "Oke" instead of "Lanjut" (`ctx.closingLabel`), so the student does not tap Lanjut twice. A page without `#btn-next` keeps the old direct redirect. Rule errors that already point to a finished attempt (`TRIES_EXHAUSTED`/`ATTEMPT_CHANGED` with `redirect`) still redirect at once.
- **Cari Objek.** After the attempt closes, the scene stays visible but takes no more taps (`done` flag in `engines/cari.js`).
- **Research events.** Unchanged: the attempt is closed by the server in the same response, and `state.leaving` is set, so leaving the held screen sends no `challenge_abandoned`.
- **Blank number.** `.blank-no` is an absolute tab in the blank's top-left corner (`border-radius: var(--radius) 0 var(--radius) 0`, the blank's own radius). It stays outside the `<button>` because `rumpang.js` rewrites the button's text; `.blank-wrap` is now an `inline-flex` box that hugs the button. Reading blanks get 2 rem side padding so the word never runs under the tab.
- **Checks.** Chromium on rendered puzzle, rumpang, and boleh pages with a mocked `/check`: after the modal the page stays, only Lanjut is visible and focused, Lanjut goes to `/selesai/…`. Suite green (659 tests).

## Clue narration on tap only, Kenali badge, map narration strip (12 October 2026)

Branch `sky/epic-hawking-qc1e8p`. Owner request: the Cari Objek clue play button still looks messy; clue audio must not play on its own, only when ▶ is tapped; check the Kenali badge logic; on `/peta` Jaka (`character-jaka pose-idle`) does not show, and the character, narration box, and play button may be rearranged as wide as the map, covering the unimportant bottom of the Kedu map.

- **Clue audio on tap only.** `engines/cari.js` no longer autoplays the next clue after a correct answer (the 600 ms `VOICE_DELAY_MS` timer is gone). Every clue is read out only by its ▶ (action `play`); switching clues still stops the previous one. The simulator (`gelita:simulasi`) now sends `play` for every clue it "listens" to, instead of `autoplay` after the first. Docs 06 and 07 updated.
- **Clue play button.** In Chromium the bar from #106 was already in order on first load, while playing, and at 800–1920 px wide. The messy case was a recording that fails to load (missing file, network, unsupported format): `NarrationPlayer#fail()` hides the buttons and prepends "Suara belum dapat diputar. Baca teksnya, ya." inside the player, so the sentence took the button's column and pushed the clue text onto two lines. Now:
  - `.clue-voice` is one fixed 48 dp slot in every state;
  - on failure a dashed round "sound off" mark (`.clue-voice-off`, title = `Js.audioNoSound`) takes the slot, and the sentence stays in the DOM, visually hidden, for screen readers (it is still a `role="status"`);
  - the label column is `max-content` instead of 11rem, so the gaps on both sides of ▶ are equal, and the label uses tabular digits so ▶ does not move between "Petunjuk 1" and "Petunjuk 3" (checked: same x on all four clues).
- **Kenali badge.** The client logic is sound: `narrator.js` sends `dialogue_advanced` `{index, total, context: 'region_intro'}` with `level_id` and flushes it at the last slide; `map.js` removes the badge on the pin and the card at once; the server rebuilds the list from those events on the next visit. One real bug: `GameEventLogModel::heardRegionIntros()` prefiltered with `LIKE '%"context":"region_intro"%'`. MySQL 8 (a supported stack, docs/01) stores a `JSON` column in binary form and returns it with a space after each colon, so the filter matched nothing there and the badge came back on every visit. MariaDB keeps the text as written, which is why it worked locally. The prefilter is now the bare word `region_intro`; `reachedLastSlide()` still checks the decoded context exactly. New `HeardRegionIntrosTest` inserts both spellings (fails without the fix). Note: "heard" means reaching the last slide, also by tapping ▶ through without waiting for the audio; this is unchanged.
- **Map narration strip (deviation from Dok 3 §4 table: "balon 48, 560, 600 × 96", no large character over the map).** Jaka was hidden on purpose by CSS (`.map-balloon > .character`, `.map-story-slide > .character { display: none }`), not a broken image. The owner now wants him shown. New layout, `stage.css`: strip 40, 536, 864 × 98, as wide as the map and below the lowest pin label (Magelang ends near y 530):
  - Jaka 124 × 168 in the first grid column, bottom-aligned, so he rises into the empty lower-left corner of `map.kedu`; his pose follows the slide (each `map_intro` slide carries its own figure), and the idle frames animate as before;
  - the balloon (16 px text, 3 lines fit) in the middle, without a tail;
  - the narrator controls as one row on a navy pill (◀ ▶ ↻ ▶|), the play/pause button gold and 48 dp; their column is as wide as the full pill, so the balloon does not shrink when ↻ appears during playback. Buttons stay 8 dp apart so the 48 dp hit areas (Phase F2) do not overlap.
  - Without `map_intro` lines the same strip holds Jaka and the `Game.mapLead` balloon. The strip covers part of the compass at the bottom right; the owner accepted covering the bottom of the map.
  - Checked in Chromium at 1280 × 720 and 1024 × 768 on local MariaDB with the catalogue art imported.
- **Tests.** `HuntClueAudioTest`: the old test that required autoplay after a correct answer is replaced by one that forbids `autoplay` in `cari.js` code, plus a test for the failure mark. Suite green.

## Asset package — Wave 2 coordination (12 October 2026)

Branch `sky/laughing-rubin-l2tmm2`. Stages 2 and 6 of `docs/rencana-paket-aset.md` §10 were built in parallel by two agents with separate file ownership (branches `paket-tahap2-service-worker` and `paket-panel-rilis`, both from `main` e20229c) and merged here into one PR, because this session pushes to one branch. Contracts between the two: Stage 6 adds `online_lab_allowed` to `/api/paket/aktif` and Stage 2 reads it (missing = false); `AssetPackageService::activeCode()` keeps meaning "the package the active release names", and the staff preview is a separate path (`servingCode()`), so the API, `X-Gelita-Paket`, code URLs, and the boot attributes never reflect a preview.

- **Merge fix-up.** `package_audio_urls()` (Stage 2) now uses `servingCode()` like the media helpers, so a staff preview also plays the previewed package's music and sound effects. Code URLs (`asset_url_versioned()`, `game_asset_url()`) and `asset_package_attrs()` stay on `activeCode()`: they must match what devices hold. A preview therefore shows the previewed media with the active release's code.
- `docs/08_DEPLOYMENT.md`: both stages added subsections (one conflict, both kept); the Stage 1 line "until the admin switch of Stage 6 exists" and the Stage 2 code-update note now point to **Rilis → Paket aset**.
- PHPUnit after both merges: 690 tests OK (661 + 15 Stage 2 + 14 Stage 6).

## Asset package — Stage 2: package service worker and downloader (October 2026)

Branch `paket-tahap2-service-worker` (from `main` e20229c), merged via `sky/laughing-rubin-l2tmm2`. Plan `docs/rencana-paket-aset.md` §10 row "2. Service worker paket" (§4.1, §5.5, §5.6, §6.2, §8.1–§8.7, §9.1–§9.3, §11.1), with owner decisions P7 and P11. Package mode off or no service worker: nothing changes for students.

- **`public/sw.js` rewritten** (`SW_VERSION = 2`). Caches: `gelita-aset` (package files and manifests, by URL, shared by all versions), `gelita-meta` (`gelita-meta/aktif.json`), and `gelita-offline-v1` (kept name).
  - Navigations: network with preload, `offline.html` when the network is down, never cached.
  - Package URLs (`assets/p/{sha16}.{ext}`, `assets/k/{version}/…`, `paket/pkt-*.json`): cache-first. A miss goes to the network unchanged and is not stored (§6.2 "evicted file comes from the server").
  - `/api*` and `/admin*`: no `respondWith`. Everything else is left to the browser.
  - One guarded write `putChecked()`: package URLs and `offline.html` only; refuses non-200, redirected, `Set-Cookie`, and `private`/`no-store` responses. The worker itself only stores `offline.html`; package files are written by the page-side downloader through its own guard with the same URL pattern.
  - Range written by hand: one range per request, suffix/open ranges, 416 past the end, whole file for a header it does not understand.
  - Updating the worker keeps the three caches.
- **Downloader in the page (`core/package.js`).**
  - 4 files in parallel; `crypto.subtle` sha256 before `cache.put`. On a mismatch the cached copy is dropped first (otherwise the worker would answer the retry from the cache), one retry, then `corrupt` with the key.
  - Network/5xx retries after 2, 4, 8 s, then `network`; 404/410 → `missing`. Resume per file without re-hashing.
  - Storage pre-check: **missing** bytes × 1.2 against `quota − usage`. Deviation: the plan says `total_bytes` × 1.2, which would refuse a resume on a device that already holds most of the package. `QuotaExceededError` while writing gives the same `quota` error.
  - Web Locks serialise downloads across tabs. `persist()` after a successful switch, not in Firefox (user-agent check); also exported for a "Simpan permanen" button.
  - Error codes only (`unsupported`, `disabled`, `server`, `manifest`, `quota`, `network`, `missing`, `corrupt`, `incomplete`, `aborted`); Stage 3 maps them to `Js.*` text. API for Stage 3 in `docs/06_JAVASCRIPT.md` (`check()` with `canStart`, `download()`, `chooseOnline()`, `verify()`, `clear()`, `persist()`).
- **Atomic switch done by the page, not the worker.** After every file the page checks that every manifest URL is in `gelita-aset` (presence, no re-hash, as §5.5 asks), then writes `aktif.json` last; the previous package stays in use until then. Old files are removed by the worker on a `gelita:prune` message only while a single window is open (`clients.matchAll`); otherwise pruning waits for the next `check()` or page. Deviation: clients do not report which package they show, so any second window defers.
- **S8 (no switch mid-session).** While `CONFIG.sessionTag` is set, `download()` fetches the files but does not write `aktif.json`; the next call without a session (welcome page) switches. Server-rendered URLs still follow `activeCode()`, so after a mid-session publish the session's later pages use the new package's URLs: unchanged files still come from the device cache, changed ones from the network. Pinning a session's URLs to `game_sessions.package_code` is left open.
- **Boot hook.** `core/package-boot.js`, an external classic script in `<head>` (CSP: no inline script), reads `data-package` / `data-sw="off"` from `asset_package_attrs()` and a localStorage mirror `gelita.paket` of `aktif.json` (Cache Storage is async). It sets `data-assets-local="1"` only when the page's package equals the device's, a worker controls the page, and online mode was not chosen. `game.js` imports `package.js` only on pages that name a package; `initPackage()` makes `window.GELITA_PACKAGE` the async `packageInfo()`.
- **`package_mode` at login:** `nonaktif` when the page names no package; `lengkap` when the device holds the page's package and the version-2 worker answers a ping; `daring` otherwise (online mode, no worker). `package_code` is the page's package, also in `daring` (§7.2).
- **Online mode (§8.7)** reasons `unsupported` (no service worker, Cache Storage, or `crypto.subtle`; Mulai allowed without asking), `storage` (only after `check()` found too little space or a download hit `quota`), `lab` (only with `online_lab_allowed: true`). Kept in `sessionStorage` `gelita.paket.daring`.
- **Kill switch (§11.1).** `.env` `gelita.paket.aktif = false` also makes game pages register `public/sw-kosong.js`: no fetch handler, deletes `gelita-meta`, keeps `gelita-aset`. For a worker bug that stops pages from loading, `docs/08` gives an Nginx `location = /sw.js` rule serving `sw-kosong.js`.
- **Code URLs.** `asset_url_versioned()` returns `assets/k/{code_version}/…` only for files in the active package **and present on disk**. New `game_asset_url()` for the two font preloads. With package mode on, code updates reach students only after a rebuild (docs/08; the deploy scripts do not rebuild).
- **Music and sound effects.** `js_config()` gained `audioUrls` (`music/map`, `sfx/click` → package URLs, empty when off); `core/audio.js` `soundUrl()` uses it.
- **`X-Gelita-Paket`.** `PackageHeaderFilter` (route filter on `api`, `api/*`) adds the active package code to API responses handled by a controller; absent while package mode is off and on requests refused by a before filter. No client reads it yet.
- **Tests.** `AssetPackageServiceWorkerTest` replaces `InstallableAppTest::testServiceWorkerOnlyCachesTheOfflinePage` and checks §9.1 items 1–3 statically; `AssetPackageCodeUrlTest` covers code URLs, boot attributes, `audioUrls`, and the header. Browser: `tools/qa/package-check.mjs` (`--serve`, `--sqlite`/`--mysql`), 64 checks pass on a local SQLite world with a 270-file, 15.6 MB package (download, 50 % interrupt and resume, sha256 errors, cache contents, 206/416, quota, no worker, S8/S9 mid-session publish, kill switch, package mode off). Chromium's offline emulation does not reach service-worker fetches, so the network-error check stops the server. `transition-check.mjs` still passes with package mode on and off.

## Asset package — Stage 6: Rilis → Paket aset panel and study package lock (October 2026)

Branch `paket-panel-rilis` (from `main` e20229c), merged via `sky/laughing-rubin-l2tmm2`. Plan `docs/rencana-paket-aset.md` §10 row 6 (§6.1–§6.3, §7.2, §8.7, §9.2, §11.3), owner decisions P10, P12.

- **Page.** `/admin/studi/rilis/paket` (`PackageController`, `staffRole:admin` like activating a release), a sub-page of Rilis (breadcrumb Rilis › Paket aset, no sidebar entry). Rilis gets a Mode paket panel with a link. Routes: `bangun`, `bangun/{code}/lanjut`, `{code}/terbitkan`, `matikan`, `izin-daring`, `periksa`, plus GET `/admin/studi/rilis/{id}/aktifkan` (confirmation page).
- **One activation path.** New `App\Services\ReleaseActivation`, used by Rilis (`StudyController::activateRelease`) and the switch: activate, flush the content cache, forget the package memo, sync package statuses, audit. `release_activate` metadata is always `{release_code, package_from, package_to, reason, running_study, via: rilis|paket}`.
- **Switch = automatic release.** `createForPackage()` copies `app_version`, `content_version`, and `scoring_version` from the active release. On: `asset_version` and release code = package code. Off: `asset_version = ''`, release code `tanpa-paket-YYYY-MM-DD-NN`. A taken code gets `-02`, `-03`. Audited as `release_create` with `automatic: true`.
- **Running-study rule.** Tick + non-empty reason (max 500 characters) is required when a study is `active` and the target release's package differs from the **active release's** package (not from the study's locked package). Covers switching on/off, publishing a rebuild, and Rilis activations; releases that keep the package need nothing. On the package page the check runs before the release is created, so a refusal leaves no orphan release.
- **Package statuses follow the active release:** named package = `terbit` (`published_at` = last activation); a previously `terbit` package = `ditarik`; never published = `dibangun`.
- **Batch build.** The page posts one batch at a time (`js/admin/package-build.js`; retries after 2/4/8 s, then **Coba lagi**). Each request runs builder steps of 5 files for a time budget (4 s with JavaScript, 20 s with the no-JS **Lanjutkan membangun** button). `finish()` runs once under `writable/paket/{code}.finish.lock`. Nyalakan while a build runs resumes it. The summary (`AssetPackageService::summary()`: size per group, files and bytes new to devices with the active package, warnings) is hidden while another build runs.
- **Lab online permission (§8.7)** is stored in `writable/paket/pengaturan.json`, not a table or column: it only means something with a package, only the API and the panel read it, the backups already archive `writable/paket/`, and a restore without that folder falls back to off. `/api/paket/aktif` always returns the stored value as `online_lab_allowed`, also while package mode is off. Audit: `package_online_lab`.
- **Staff preview (§9.2).** `previewCode()` / `servingCode()` are used only by `urlForId()` / `urlForKey()` (and, since the merge, `package_audio_urls()`). Requires an active staff account in the session. `?paket={code}` of a built package is stored in session key `package_preview`; `?paket=mati` (or empty, `0`) clears it. Works while package mode is off and regardless of the `.env` kill switch. No visible preview banner in the game (the layout belongs to Stage 2's files).
- **Study package lock.** Migration `003300` adds `research_studies.package_code` (VARCHAR 30, NULL), set when the status changes to `active` (`releasePackageCode()`, NULL = started with package mode off) and kept while the study runs; audit metadata `package_code`. Already-running studies are backfilled from the active release when its package exists. Penelitian shows the lock and warns when students now get another package. `SystemResetService::REQUIRED_MIGRATION = 2026-01-01-003300`.
- **Research-ready.** `ResearchReadiness` adds "Paket terbit, lengkap, dan terverifikasi" (package mode on and `AssetPackageMaintenance::verify()` clean), cached for an hour per package and manifest; **Periksa sekarang** and `gelita:content:verify --siap-penelitian` re-hash immediately. The command reports package mode off, or any file problem, as an error.
- **Changed-asset notice** partial on Paket aset, Rilis, Gambar & mockup, and Rekaman narasi, from `changedSinceActive()`; hidden while package mode is off.
- **Not done:** the deploy scripts do not rebuild the package while the switch is on (§6.3); it needs an owner decision on the running-study rule for an unattended deploy, so docs/08 tells admins to press **Bangun ulang paket** after a code deploy. Migration `003300` was not run on MariaDB/MySQL (none in the agent environment); its backfill SQL was checked on SQLite.
- **Tests.** `PackageSwitchTest` (9), `ReleasePackageLockTest` (4), `RouteWiringTest` (admin-only routes). Browser pass on a local SQLite world, 35 checks (build progress, publish during a running study, staff-only preview, notices clearing after a rebuild, lab permission in the API, Matikan, no horizontal scroll at 1280/390 px, no console errors). Seen but not changed: `/admin/dashboard` returns 500 on SQLite (`DashboardController::recentEvents()`, "no such column db_e.id"); production runs MySQL.

## Readable staff login link in the bottom-right corner (12 October 2026)

Owner request: fix the "Masuk staf" text on the welcome page and keep it in the bottom-right corner.

- **Problem.** The link from the earlier staff-link entry was 12 px grey text at 60 % opacity. On the real `bg.welcome` art it almost vanished, and on a phone in landscape it shrank to about 7 px.
- **Fix.** The link is now a small pill like the HUD text: `--fs-caption` (16 dp), semi-bold, `--ink` on `--hud-backing`, gold key icon, 40 dp tall with a 48 dp hit area, and a gold focus ring. The text is still `Game.staffLogin` ("Masuk staf" / "Staff login").
- **Position.** It is pinned in stage coordinates (`stage.css`): 24 dp from the right and 16 dp from the bottom of the 1280 × 720 stage, so it stays in the corner at every window size.
- **Install offer moved up.** `.welcome-install` moves from y 600 to y 576. Without this, the two-line Android install hint would overlap the larger link by about 10 dp. Dok 3 §4 and `docs/05_VIEW_UI.md` are updated to match.

## GELITA logo, centred, on the staff login page (12 October 2026)

Owner request: fix the admin login page so that it opens with the GELITA logo, centred.

- **Brand block.** `layouts/auth.php` now opens the card with the centred logo, using the first of these that exists:
  1. `ui.logo-hero` (`.auth-logo`, up to 280 px wide);
  2. the emblem `ui.logo` (72 px) above the word GELITA;
  3. the old "G" mark above the word GELITA.

  `Admin.panelTitle` sits underneath. The title "Masuk Panel Guru & Admin" and the lead line are centred; the form labels stay left-aligned.
- **Deviation from Dok 3 §1.6.** That section keeps game artwork off the login page. The logo is now the one exception; the page still loads only `admin.css` and keeps the neutral background. Dok 3 §1.6 and `docs/05_VIEW_UI.md` note the exception.
- **Image slots.** `ImageSlots` lists `/admin/login` as a screen for `ui.logo-hero` and `ui.logo`, so the asset checklist shows where the logo appears.
- **Tests.** `AdminLoginBrandTest` covers each of the three fallbacks. Browser screenshots with the catalogue logo were taken at 1280 × 800 and 390 × 780, with no horizontal scroll.

## No "Panel GELITA" caption on the staff login card (12 October 2026)

Owner request: remove the "Panel GELITA" text under the logo on `/admin/login`. The brand block now shows only the logo (or its fallback). The page title "Masuk Panel Guru & Admin" already names the panel. `Admin.panelTitle` stays, because the admin sidebar and breadcrumbs still use it. `AdminLoginBrandTest` checks that the caption is gone.

## Review of the script and question bank draft 1, and draft 2 (17 October 2026)

Owner request: review the owner's draft changes in `gelita-katalog/update/` against the live script (`docs/naskah-cerita.md` = `data/story.php`) and bank (`docs/bank-soal/`) from the views of a language expert, a grade-5 teacher, Kurikulum Merdeka, Bloom C1–C6, script–bank continuity, and Kedu culture. Then apply the fixes as draft 2.

- **Scope.** Content only. Draft 2 overwrites the draft files in `gelita-katalog/update/`; draft 1 stays in git history. No app code, seed data, or tests changed. Integration into the app is a separate branch (review §7).
- **Language exception.** The review document `gelita-katalog/update/telaah-draf-1-naskah-bank-soal.md` is in Indonesian, by owner decision, because its readers are teachers and researchers. This entry stays in English.
- **Main finding.** In draft 1 the six fact/opinion items could be answered from surface cues: every "Ucapan …" was an opinion, every "Kalimat …" was a fact, and every opinion began with "Menurut saya". Draft 2 breaks the confound with these changes:
  - a quoted fact from a person (mgl-3-b05);
  - an unmarked opinion sentence (mgl-3-a05);
  - an opinion without "menurut saya" (mgl-3-c).

  It also breaks other key patterns: gagasan pokok was always true, wnb-3-b alternated strictly, and "sama-sama" was always false. It fixes the remaining script–bank inconsistencies. Draft 2 differs from the live script in 32 lines.
- **Owner decisions.**
  - Puzzle variants a–c are the scored research set on the first attempt; d–j are for replays only. This needs a `pickVariant` change at integration.
  - Tobacco stays in `kenal-temanggung-02` as a regional fact.
- **Item keys edited in place.** Several true/false keys flip in draft 2 (mgl-3-a02, a05, b01, b05; wnb-3-a02, a06; wnb-3-b03/b04 swapped). This is allowed because research data collection has not started (`docs/PROGRESS.md`). After data collection starts, a changed key needs a new `item_key`.
- **Import dry-run** (local SQLite, never production). Both drafts fail only on rules the app does not yet allow:
  - skill `membedakan_fakta_pendapat`;
  - 10 puzzle variants per region;
  - one Temanggung distractor.

  Item keys in the workbook are explicit. Only `docs/bank-soal/data/*.php` → `BankSource` generates keys by position, so the inserted items a06, c08, and c09 need explicit-key support there.

## Draft 2 of the script and question bank integrated into the app (17 October 2026)

Owner request: integrate draft 2 (`gelita-katalog/update/`, review §7) into the app. The draft workbook carries every draft 1 and draft 2 bank change, so this integration brings both into the app.

- **Source of truth stays the data files.** `docs/bank-soal/data/*.php` now reproduce the draft workbook cell by cell in the six import sheets and DAFTAR_MEDIA, checked with a throwaway script against `gelita-katalog/update/bank-soal/bank-soal-kelas5.xlsx`. `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` is rebuilt from them. Only PETUNJUK and KAMUS_KOLOM wording comes from `BankWorkbookGuide`, updated to the draft's wording.
- **New data-file fields** (`BankSource`): explicit `key` (inserted items such as mgl-2-a06, wnb-2-c08/c09, and the moved wnb-2-b02 keep their codes), `active => false` (retired items stay in the workbook with `is_active = 0` and sequence 91, 92, … so stored answers keep their item), and per-item `review` status and note (revised items are `needs_verification`). A key that does not start with `{node}-{variant}` is an error.
- **Rules.** `BankRules::VARIANTS` became `[min, max]`: Susun Gambar 3–10 with a, b, and c required; Isi Rumpang and Benar atau Salah exactly 3; Cari Objek at least 1. `DifficultyRules`: Temanggung Isi Rumpang has exactly 1 distractor; skill `membedakan_fakta_pendapat` ("Membedakan fakta dan pendapat") is valid for sedang. Exports and the import form pick up the label from `DifficultyRules::SKILLS`.
- **First attempt draws from a–c.** `ChallengeItemModel::pickVariant()` restricts a student's first attempt at a challenge (no variant served yet) to `BankRules::FIRST_ATTEMPT_GROUPS` (a, b, c). This applies to every engine; only Susun Gambar has more variants. Later attempts keep the old rule (avoid variants already served, then any). An abandoned first attempt counts as served, so the next attempt may draw d–j. The analysis should use first attempts, as the review already says.
- **Word bank.** With one Temanggung distractor, the derangement rule for distractor-free banks no longer applies to any region. All three regions use the rule that the answers never appear in blank order. `WordBankShuffleTest` now checks that for tmg-2 too.
- **Story script.** `app/Database/Seeds/data/story.php` and `docs/naskah-cerita.md` take the 32 ID and 28 EN lines of draft 2, plus the new `kenal-wonosobo-02` title "Candi, Telaga, dan Tradisi" / "Temples, a Lake, and a Tradition". Codes, speakers, poses, and effects are unchanged.
- **Narration does not notice changed text.** Recordings are matched by line code only (`NarrationImporter::importOne`); there is no text hash. On a fresh install, `gelita:narration:import` registers the bundled MP3s of the 32 changed lines as drafts, and "Setujui semua rekaman" would approve audio that still says the old text. The catalogue note (catatan-revisi §5 item 6) that changed recordings "go back to draft" is only true once a new audio file is imported. Until the lines are re-recorded, approve narration line by line and skip those codes. No code change here; the owner decides whether a text-hash check is worth adding.
- **Existing databases.** `ContentSeeder` inserts missing dialogue lines only, so a server that already has content keeps the old script; update the 32 lines in the dialogue editor. The bank import does not overwrite a filled `media_note`, so revised slot notes (e.g. `challenge.item.tmg-1-01`) need the **Gambar & mockup** panel.
- **Draft content kept as is.** The port reproduces the draft exactly, including points the agents flagged for the researcher rather than fixing them: wnb-1-d01 (Candi Arjuna) and wnb-1-e01 (a Dieng crater) are administratively in Banjarnegara; retired items keep `verified`; a doubled full stop in the wnb-1-d01 review note; small EN wording differences in `kenal-wonosobo-02`, `dialog-magelang-08`, `tuntas-wonosobo-01`, and wnb-3-a05.

## `gelita:story:sync` for servers that already have content (17 October 2026)

Owner request: a command that brings the revised story lines to a server that already has content, instead of retyping 32 lines in the dialogue editor.

- **Why it is needed.** `ContentSeeder::seedDialogues()` only inserts missing rows, and `gelita:reset` keeps `dialogues`. A revised `data/story.php` therefore never reaches an installed server.
- **Overwrite rule.** A row is replaced only when its title and text equal the current script or an earlier version listed in the new `app/Database/Seeds/data/story-previous.php`. That file now holds the pre-draft-2 text of the 32 changed lines, taken from `data/story.php` at `2dcef33`. Any other text counts as a panel edit: it is reported and kept unless `--timpa` is given, the flag `gelita:katalog:impor` already uses for the same purpose. The alternative, overwriting every line that differs, would silently undo an admin's edits.
- **Scope of a write.**
  - Written: `title_id`, `title_en`, `text_id`, and `text_en`.
  - Untouched: character, pose, effect, background, active flag, and linked recordings.
  - Each update is conditional on the row still holding the text read in the plan (repo concurrency pattern), so a panel save during the run wins and is reported.
  - Missing lines are inserted like the seeder does.
  - The content cache is flushed.
  - A write is audited as `story_sync`.
- **Recordings stay stale.** The command lists replaced lines that still have linked audio, and warns not to use **Setujui semua rekaman** until they are re-recorded. Detecting stale audio in the panel itself (a text hash per recording) remains an open owner decision.
- **When the script changes again,** append the replaced versions to `story-previous.php` in the same commit; `docs/naskah-cerita.md` and `docs/08_DEPLOYMENT.md` say so.
- **Tests.** `StorySyncTest` covers:
  - the data guard: 32 codes, each a real script line, no earlier version equal to the current text;
  - the plan statuses, with no writes during planning;
  - the apply rules: known versions updated, panel edit kept, missing line inserted, recordings and active flag untouched;
  - idempotence, `--timpa`, and a row changed between plan and apply;
  - the command's dry run and real run, with the audit row.

## Voice-over narration: bulk cancel, script PDF, TTS files removed (17 October 2026)

Owner request: in **Rekaman narasi**, add a way to cancel every recording in bulk; delete the old recording files, because narration will be voice-over by real people instead of TTS; and add a PDF export of the script for the voice actors, in a tidy table with tight margins that saves paper.

- **Batalkan semua rekaman** (`POST /admin/konten/narasi/batalkan`, one language per press, with a confirmation box). `NarrationCatalog::cancelAll()` takes the same narration audio as bulk approval: recordings linked to script lines or Cari Objek clues, plus active imported `audio.narasi.{locale}.*` files. It unlinks them. The imported narration files among them get `approval_status = rejected` (bulk approval only takes drafts), their media rows are deactivated, and their files under `assets/uploads/` or the narration folder are deleted. Rows are kept, not deleted: `audio_usage_events.audio_asset_id` has no cascading delete, and research data should keep pointing at a real row. Audio that is not an imported narration file (e.g. mission-card narration picked by hand) is only unlinked: its status and file stay, because it may be used elsewhere. Re-uploading a file with the same name reactivates the media row and resets the audio row to `draft`. Audited as `narration_cancel_bulk`.
- **Old files.** The 200 TTS MP3s in `public/assets/audio/narasi/{id,en}/` and `produksi.json` are removed from git; the folders stay (`.gitkeep`) as the drop-in folder for `gelita:narration:import`. `NarrationKelas5Test::testBundledRecordingsMatchTheLines` is replaced by a check that no narration ships with the repo. `tools/audio/` (TTS scripts, `qa-narasi.json`) stays as history, since it also builds music and sound effects.
- **Script PDF** (`GET /admin/konten/narasi/naskah-pdf?bahasa=id|en|semua`, `App\Libraries\NarrationScriptPdf`, view `pdf/narration-script`). It uses mPDF like the report PDFs: A4 portrait, 7 mm side margins, DejaVu Sans Condensed 8.5 pt, and one table in game order (repeating header, a shaded row per scene) with a tick box, running number, file name, speaker with pose/effect as a delivery cue, and the text. With one language per print, the full script (88 lines without clue nodes) fits on about 5 pages; both languages side by side take about 7. Audited as `narration_script_download`.
- **Deploy note.** Servers that imported the TTS takes keep rows pointing at files this release deletes from git. Press **Batalkan semua rekaman** for both languages right after the deploy. Until then, approved lines still point at the deleted files: the browser gets a 404 and plays nothing, and a package rebuild reports them as `file_missing` warnings.

## Asynchronous logging: owner decisions (17 October 2026)

The plan in `docs/rencana-log-asinkron.md` (PR #119) asked for five decisions in its §10. The owner decided four of them on 17 October 2026:

- **Phases 1 and 2 approved, one branch and one PR each.** Phase 1 (client batching) goes first, then Phase 2 (early session close, batched audio writes). Separate PRs keep the two effects measurable on their own.
- **Flush interval 30 s, not the proposed 15 s.** At peak that is about 2–3 telemetry requests per playing student per minute, down from up to 21–41. Persist-on-push (R1) means the longer interval does not add loss on a browser crash, and the forced sends on tab hide, page close, and navigation are unchanged. The `dialogue_advanced` read-back on the map is not affected: the narrator flushes at the last slide and the curtain awaits the flush. The interval comes from server config, so it can be lowered without a JS deploy.
- **Phase 3 (write-behind inbox) is decided after the §7 load test,** not now.
- **Dashboard lag accepted.** "20 event terakhir" may be up to one interval behind; Phase 1 adds a short note under the list.
- **Not decided separately:** the §7 targets. They stay the proposal and serve as the bar for the Phase 3 decision.

## Asynchronous logging: Phases 1 and 2 in parallel (17 October 2026)

Owner request, later the same day: start Phases 1 and 2 at the same time in separate sessions, instead of Phase 2 after Phase 1. To keep the parallel-agent rule (no two sessions own the same file), the phases are split by layer rather than by recommendation number:

- **`log-1-klien` owns the browser** (`public/assets/js/**`, the event-integrity browser check, `docs/06`). **`log-2-server` owns every PHP file**, including the server half of R2/R3 that the plan had listed under Phase 1, the flush config, and the dashboard note.
- **A written contract** (plan §10 item 1) fixes the `/api/events` envelope and answer and the `eventFlush` config entry, so both sides are built at once.
- **Phase 2 merges first.** Phase 1 is still safe on its own: it falls back to `/api/audio-events` when the answer has no `audio` key, so audio is never silently dropped by an older server.
- **Measuring the two effects separately** (the reason for "one branch each") still works: the load test runs on Phase 2 alone, then on both.

## Asynchronous logging Phase 1: browser (branch `log-1-klien`, 17 October 2026)

Implements R1–R4 of `docs/rencana-log-asinkron.md` §5.1 on the browser side, against the contract in its §10 item 1. The server half (`/api/events` taking `audio`, heartbeat folded into the batch, `Gelita::$eventFlush` in `js_config()`) is on `log-2-server`, which merges first.

- **One transport, two channels.** `core/events.js` keeps a queue per channel (`events`, `audio`) and one sender: timer, running send, failure count. A batch takes up to 50 events from each channel into one `POST /api/events`. `FLUSH_MS`/`FLUSH_AT` come from `CONFIG.eventFlush` (30 000 ms / 40 events when missing); `FLUSH_AT` is capped at 50.
- **Persist-on-push uses the existing queue keys.** Every event is appended to `gelita.queue.{channel}` when it is created and removed by id once its batch is accepted; events in memory are tracked by id so a restore never queues one twice. The plan's alternative (one key per event) was not needed: the array stays small (≤ one batch window) during normal play. If old tablets show jank, that is the next step (plan §9).
- **Storage refused at push time is not a loss yet.** The event is still sent; it is stored, and a loss counted, only if its batch then fails. A quota failure therefore no longer shows `eventQueueFull` for events that do arrive.
- **Beaconed events stay queued and are marked (`b: 1`).** They are re-sent with the next page's first batch (R4), as the plan accepts (F4); the same page never beacons an event twice (visibilitychange hidden followed by pagehide).
- **Old-server detection.** Audio falls back to `/api/audio-events` when the answer has no `audio` key, and stays there for the rest of the page. The heartbeat (R3) is skipped only when `CONFIG.eventFlush` is present, because that entry ships in the same server change that folds the heartbeat into the batch; on an older server the 60-second heartbeat runs as before, so `last_active_at` keeps moving while the student plays. A deviation in wording only: the plan says "no batch went out"; the client counts an *accepted* batch, since a failed one does not refresh the session.
- **Refused batches (401, 403, 422) are also removed from the offline queue,** matching the old behaviour of dropping them; they would never be accepted.
- **`Storage.takeAll` removed;** `pendingEvents` reads without removing, `removeEvents`/`markBeaconed` rewrite by id.
- **`EventQueueNoticeTest` (PHP) pins the source text of `keepForLater`, `this.store(batch)`, `this.resend()` and `this.failures = 0` in `core/events.js`.** The sender is therefore a `Transport` class with those method names; the test passes unchanged. The test belongs to the PHP side (`tests/`), so it was not edited.
- **QA.** `tools/qa/event-integrity.mjs` now emits audio events too, adds fast navigation and a renderer crash mid-batch (CDP `Page.crash`, no pagehide), waits until the offline queue is empty instead of a fixed 3.5 s, and keeps its package hook when `core/package-boot.js` rewrites `window.GELITA_PACKAGE` (package mode off). New `tools/qa/event-requests.mjs` counts telemetry requests per minute for one scripted student.
- **Measured locally** (`php spark serve`, MariaDB 10.11, one student, 4 minutes: event every 2 s, audio every 6 s, page change every 45 s): old client 26.6 telemetry requests per minute; new client 5.2 against a server without Phase 2 (audio fallback) and 3.7 against `log-2-server` (eb6baf1, merged locally for the test only). What remains are the beacons on page changes and the re-send of their copies. Against that server `event-integrity.mjs` passed in full (120 s offline, fast navigation, crash mid-batch), the heartbeat was skipped while batches flowed and sent once by an idle tab, and the map's "Belum didengar" badge cleared after the curtain.


## Asynchronous logging Phase 2: server side (17 October 2026, branch `log-2-server`, PR #122)

Implements `docs/rencana-log-asinkron.md` §5.1 R2/R3 (server half) and §5.2 R5–R7. Phase 1 (browser JS, branch `log-1-klien`) is developed in parallel against the contract below; this PR merges first.

- **Combined batch (R2).** `POST /api/events` also takes `audio` (the entries `/api/audio-events` takes) and `audio_dropped` + `audio_dropped_id` (written as `events_dropped` with `channel: audio`). `events` may be `[]`. Each array is capped at `maxEventsPerBatch` (413 `TOO_MANY_EVENTS`); an `audio` value that is not an array is 422. The answer carries `audio: {accepted, duplicate, rejected}` whenever the request had an `audio` array, even an empty one. The `audio_dropped` report is recorded even without an `audio` array, so a client whose audio queue overflowed but has nothing new to send still reports it.
- **Heartbeat in the batch (R3).** `SessionService::heartbeatIfDue()` runs `heartbeat()` at most once per 50 s per session (`BATCH_HEARTBEAT_SECONDS`, cache key `gelita_heartbeat_{id}`), after a successful `/api/events`. Refused requests (429/413/422) do not beat. `heartbeat()` itself now updates only when `last_active_at` is still the value it read (the `updateIf()` pattern), so two tabs beating at once add the elapsed time once instead of twice.
- **Session released early (R5).** `EventApiController::ingest`, `AudioApiController::ingest`, and `SessionApiController::heartbeat` call `session()->close()` as their first statement; the `apiSession` and `locale` filters have already read and written what they need. Nothing later in these requests writes `$_SESSION` (the `packageHeader` after-filter only writes for a staff `?paket=` GET).
- **Batched audio (R6).** `EventService::recordAudioBatch()` serves both endpoints: one lookup each for the session's attempts and the audio assets, one `whereIn(client_event_id)` existence check, one `nextServerSequence()` with numbers assigned in batch order, then in one transaction an `INSERT IGNORE` of the raw events, a grouped `MAX(play_index)` per asset with indexes assigned in memory (rule of `AudioUsageEventModel::nextPlayIndex()`, now `OPENING_ACTIONS`), one `insertBatch` into `audio_usage_events`, and one `audio_use_count = audio_use_count + n` per attempt. `recordAudio()` stays as a one-entry wrapper for the simulator.
- **Race between tabs.** Two tabs can send the same stored entry at once. The raw event's unique `(session_id, client_event_id)` decides: after the `INSERT IGNORE`, rows whose `event_uuid` this request did not insert are counted as `duplicate` and get no `audio_usage_events` row and no counter increment. Play indexes are assigned after that, so the winner's numbers have no gaps. Two tabs sending *different* entries for the same asset at the same moment can still read the same `MAX(play_index)`; that was true of the per-event writer too and is left as is (no `SELECT … FOR UPDATE`, by rule).
- **Deviations from the plan.** (1) An unknown `audio_asset_id` is now rejected per entry as `audio_event_invalid`; before, it hit the foreign key and failed the whole request with a 500, which in a batch would block every other entry and make the client re-send forever. (2) A negative `listened_ms` is stored as 0 (the column is unsigned; MySQL strict mode would fail the batch). (3) The order of checks per entry is unchanged: `format_invalid`, then `attempt_not_in_session`, then `audio_event_invalid`, then duplicates.
- **Rate limit (R7).** Unchanged: `rateLimited('events')` at 120/min per session, and the combined request counts once.
- **Flush config.** `Config\Gelita::$eventFlush = ['intervalMs' => 30000, 'maxEvents' => 40]`, overridable with `gelita.eventFlush.intervalMs` / `gelita.eventFlush.maxEvents` in `.env`. `js_config()` exposes it as `eventFlush` through `event_flush_config()`, which casts `.env` strings and clamps to at least 1 s and at most `maxEventsPerBatch` events.
- **Dashboard note.** Under "Aktivitas terbaru" on Ringkasan: "Diperbarui tiap ±30 detik." (`Admin.recentEventsLag`, worded from `eventFlush.intervalMs`), through a new optional `note` on the `data-table` component.
- **Still to check on MariaDB/MySQL** (SQLite shows no locks): a `check` request is not held by a telemetry batch of the same student (session lock released); two concurrent batches with the same audio entry write one usage row; `INSERT IGNORE` affected-row counts match the SQLite tests.

## Asynchronous logging: §7 load test (17 October 2026)

Owner request: run the load test of `docs/rencana-log-asinkron.md` §7. Results are in its new §7.1; the decisions and deviations are here.

- **Protocol-level students instead of Playwright browsers.**
  - Ninety Chromium contexts would have used most of the 4 CPUs the server needed, so the load came from the clients instead of the code under test.
  - `tools/qa/load-test.mjs` sends the requests a real tab sends: login, rendered game pages, graded checks on the Benar atau Salah nodes, and telemetry, beacons, and heartbeats.
  - Telemetry follows the old or the new rules of `core/events.js`, both taken from the code and listed in the script header.
  - The rhythm matches `tools/qa/event-requests.mjs` (an event every 2 s, audio every 6 s). Its old-client figure of 30 requests per student per minute agrees with Phase 1's browser measurement of 26.6 with longer screens.
- **Same machine, two deployments.** Before (`cc54041`) and after (`d1ba33c`) ran side by side behind Nginx + PHP-FPM + MariaDB, set up as in `docs/08` Jalur L, over plain HTTP on localhost. Each run starts from one shared database snapshot with PHP-FPM restarted. No migration differs between the two versions, so one snapshot serves both.
- **Students** are registered by `tools/qa/load-students.php` through `SessionService::registerAndStart()`. On the CLI that method's final session-ID regeneration throws, because there is no web session. The script ignores only that error; everything before it is already written.
- **Extra stress run at 300 students** (baseline vs Phases 1 + 2, not in the plan). At 90 students the baseline was also far from the limits, so the larger run shows where the difference becomes visible:
  - check p95 52 → 37 ms;
  - page p95 60 → 38 ms;
  - host CPU 58 → 22 %.
- **Phase 3.** All §7 targets hold at 90 students, so under owner decision 3 the write-behind inbox is not built. The owner confirmed this on 18 October 2026. Reopen it only if one server hosts many schools at once and the load test (`tools/qa/load-test.mjs`) on that hardware misses the §7 targets.
- **Seen in passing, not fixed here.** Four 401 answers out of 45,129 telemetry requests in the 300-student baseline run. A likely cause, not verified: session-ID regeneration every 300 s with `regenerateDestroy = true` racing a parallel request that carries the old ID. The new client sends fewer parallel requests and had none. The old client does not keep a 401 batch for a later re-send, so the new client's behaviour on a 401 should be checked if the issue shows up on the real server.

## Page redesign 1: "Tantangan selesai" (18 October 2026, branch `sky/clever-hopper-yaht5p`)

Owner request: a series of page redesigns, starting with the challenge finished screen `/selesai/{id}`. Source: mockup 01 in `gelita-katalog/mockup/gambar-mockup/tantangan-selesai-temanggung/` (chosen image, `preview-ui-lengkap.html`, `spesifikasi-ui.md`, `reference-layout.css`, four rendered states). One shared layout for every region and challenge kind; Temanggung and "Susun Gambar" are sample data only.

- **Owner decisions (18 October 2026).**
  1. The redesigned HUD is used on this screen only, as an opt-in variant (`components/hud-menu.php`, section `hudStyle` = `menu` in `layouts/game.php`). Every other screen keeps the lantern HUD until its own redesign.
  2. The states the mockup does not show stay: when the region is complete, a card over the upper panel keeps Mbah Kedu's words and, for the attempt that opened it, the "Pustaka {wilayah} terbuka!" highlight with Baca Pustaka. The card can be closed (JS); the buttons below stay free, so without JavaScript nothing is blocked.
  3. The shard medal has its own slot `ui.result-medal`, filled by the importer from catalogue `avatar.2` (`CatalogImporter::COPY_SLOTS`), so changing avatar 2 later does not change the result screen.
- **Geometry.** Mockup canvas 1920 × 1080 px = stage 1280 × 720 dp (× 2/3). Panel `.result-card` 133, 145, 1017 × 495 dp; HUD controls 88 × 96 dp (hit area), visual disc 68 dp; buttons 88 dp tall. Positions follow `reference-layout.css`; its padding compensation of the skins became centred percent `background-size` values.
- **Skins.** New optional slots in a new panel group "Layar tantangan selesai": `ui.result-panel`, `ui.result-button-primary`, `-secondary`, `-detail`, `ui.result-hud-control`, `ui.result-star`, `ui.result-medal`, plus `bg.result.{wilayah}` (region group; only Temanggung exists in the catalogue, other regions fall back to their region background). `skin_style_attr()` passes filled slots as CSS custom properties; empty slots keep a plain navy-and-gold look. The catalogue `ui.result-shard-effects` SVG is not a slot: it is inlined as a data URI in `game.css`, because the asset package carries CSS but not images referenced from CSS.
- **Content.** Kind (`Game.engine_*`), title, stars 0–3 (`role="img"`, "2 dari 3 bintang"), shard count and one bar segment per shard, Waktu and Percobaan cards (Ketukan salah for Cari Objek), the finish badge, and one message: tries exhausted, several checks, or a new "right on the first try" line. Detail hasil (closed at first) shows Berhasil pada Percobaan Pertama. The main button keeps the old targets; the case "last challenge done, an earlier one still open" (no main button before, only the nav bar) now gets Berikutnya to the region map, since the nav bar is gone.
- **HUD menu.** Language popover with the flag icons (the same POST form, class `lang-switch`, so `game/hud.js` and the challenge frame keep working), sound, and a menu with profile, Peta Kedu, fullscreen (`#btn-fullscreen`, hidden when unsupported), and Keluar. Home and Peta Kedu are one row because their route is the same (`/peta`). Popovers are `<details data-popover>`: they work without JavaScript, and `hud.js` adds one-open-at-a-time, Escape, outside click, the close button, and focus back to the trigger.
- **Heartbeat.** It used to run only where the lantern was rendered; it now keys on `data-in-game` on either HUD, so the result screen still beats.
- **Not changed.** The `/hasil/{code}/{n}` table screen, the other screens' HUD, `Game.finishedTitle` ("Tantangan Selesai!", the mockup writes "selesai" in lower case).
- **Checked.** Browser screenshots at 1280 × 720 and 640 × 360 (Indonesian and English), against the mockup and its four states; every control on the screen has a hit area of at least 48 dp; Escape and outside click close the popovers and return focus.

### Follow-up after merge (18 October 2026, PR #129)

- **Language icons from the newest catalogue entries.** Owner request: use the latest flag icons. The importer now fills `ui.lang.id` / `ui.lang.en` from `icon.language-id` / `icon.language-en` (`CatalogImporter::ICON_SLOTS`, replacing `REFERENCE_SLOTS`); `reference.20` / `reference.19` are now skipped like every other reference, since the catalogue keeps them only as old aliases. Both HUDs read the same slots, so every screen changes. A server whose language slots still hold an older picture gets the flags on the next `gelita:katalog:impor`; a slot an admin uploaded needs `--timpa --kunci="ui.lang.*"`.
- **Region-done card as a modal.** Owner request: blur and slightly darken everything under the Mbah Kedu card so the eye goes to it. `.result-scrim` (fixed over the stage, `backdrop-filter: blur(6 dp)`, navy at 40 %) sits under the card; the HUD (z 20) stays sharp and usable. With JavaScript the card gets `aria-modal="true"`, the panel under it becomes `inert`, focus starts on the close button, and the close button, Escape, or a tap on the layer closes both and moves focus to the main button. Without JavaScript the layer is not drawn (`html:not(.js)`), so the buttons stay reachable as before.
- **Larger region-done modal for touch** (owner request, 18 October 2026, after PR #129). The card grows from 700 × 352 to 1040 × 560 dp (120, 128, just below the HUD controls). Inside it: Mbah Kedu up to 240 dp tall, narration at 30 dp, the Pustaka card at 28/20 dp, the close button 64 dp, and Baca Pustaka 72 dp tall (both above the 48 dp minimum). The sizes are scoped to `.result-kedu`, so `.library-unlocked` elsewhere is unchanged.

## Test suite no longer touches the real upload folder (18 October 2026, branch `sky/intelligent-hopper-w4yzc2`)

Problem seen on a developer machine: after `php spark gelita:katalog:impor`, one `vendor/bin/phpunit` run deleted the installed images (`ui.logo-hero.webp`, `app.icon-192.png`, `char.jaka.idle.1.webp`, …) while their rows stayed active. `CatalogImportTest::tearDown()` removed every file its in-memory database pointed to under `assets/uploads/`, and the importer stores files under the same official names as a real install. Before the fix, `CatalogImportTest` alone deleted or replaced 22 of 34 such files.

- **Decision.** The upload folder is a setting, `Config\Gelita::$uploadDir` (default `assets/uploads/`, path relative to `public/`), read through `MediaStore::uploadDir()`, which replaces the constant `MediaStore::UPLOAD_DIR`. The config constructor sets `assets/uploads-test/` when `ENVIRONMENT === 'testing'`, the same pattern as `Config\Database` choosing the `tests` group. The test folder is gitignored. Production behaviour is unchanged.
- **Why not a separate file-system root.** `storage_path` is both a path under `public/` and the public URL, and many readers use `FCPATH . storage_path`. Changing only the folder name keeps every reader working.
- **Follows from it.** These now use the setting: `MockupGenerator::defaultDir()` (was `DIR`), the empty slot rows from the bank import (`ContentImportService`), the orphan scan in `MediaIntegrity`, and the "own upload" checks in `CatalogImporter` and `NarrationCatalog::cancelAll()`. Rows that point into the real folder are never deleted during tests, because those checks no longer match them.
- **Tests.** `Tests\Support\RealUploadsGuard` puts files with the official names into the real folder (only where none exists), then checks that every file there is unchanged after the test. `CatalogImportTest` and the new `UploadFolderIsolationTest` use it. The old "skip if a real upload exists" guards in `NarrationImporterTest`, `NarrationPagesTest` and `MockupGeneratorTest` are removed.
- **Checked.** I filled the real folder with files under every name the suite writes (32 names plus a mockup) and ran the full suite: all of them were unchanged. An `strace` of a full run showed no access to the real folder apart from the guard's own files.

## Page redesign 2: Peta Kedu (18 October 2026, branch `sky/clever-hopper-yaht5p`)

Source: catalogue mockup Peta Kedu **candidate 04** (`gelita-katalog/mockup/gambar-mockup/peta-kedu/revisi-04/`: preview, spec, four rendered states), until now marked "not yet chosen".

- **Owner decisions (interview, 18 October 2026).**
  1. Candidate 04 is the base, with changes: fullscreen, home (Peta Kedu), profile, and logout (separate icons in candidate 02's HUD) go into the one Menu icon; **Pustaka Kedu, Tonton penutup, and Balai Refleksi stay in a bottom row** (04 had moved Pustaka into the menu); pins follow 04 with a wider name box so the text is not cramped.
  2. The three-region diorama replaces the map picture. It lives only in the mockup package, so the importer installs it from there into the new slot `map.kedu-diorama` (`CatalogImporter::MOCKUP_FILES`; a later data.json entry for the slot takes over). Without it the page keeps the old `map.kedu` picture with pins at `map_x`/`map_y`.
  3. Tapping a region selects it; one region panel shows that region (Kenali, Lanjut).
  4. The story bar takes the 04 look but keeps the voice narration (and its automatic start after the map curtain).
  5. Story line `peta-03` now names the Kenali button instead of the lantern icon (ID and EN, `data/story.php`; the old text is in `story-previous.php`, so `gelita:story:sync` updates installed servers). `Game.mapLead` changed the same way.
- **Layout (dp, mockup px × 2/3).** HUD 0–120 (logo, "Peta Kedu", shard pill 533, 16, 352 × 79, language/sound/menu); map 20, 116, 853 × 400 (04 had 433 dp; 33 dp went to the bottom row); region panel 887, 116, 373 × 400; story bar 20, 524, 1240 × 132; bottom row 656–720 (the existing nav bar). The diorama box stays 2:1 at full width, so 13 dp are cropped at its top and bottom; pins are placed in percent of that box.
- **Diorama geometry** in `Config\Gelita::$mapDiorama`: per region the 04 clip polygon, the medallion centre (04 pin box top-left + medallion centre, re-measured on the 1280 × 640 picture inside 04's 1280 × 650 frame), and the trail path into it. Measured on the catalogue picture; a replacement picture needs new values (slot tips say so).
- **Kenali** uses a book-and-magnifier icon (`icon('kenali')`, drawn after the catalogue's candidate `icon.kenali.svg`). The city lanterns `map.lantern.*` are no longer shown anywhere, so that slot is now `unused`; `ui.lantern` stays for the HUD lantern and the map curtain.
- **Kept behaviour.** Region curtain on Lanjut, locked toast (`aria-disabled` link), Kenali overlays with "Belum didengar" (now a badge on Kenali and a dot on the pin), preload of the next region, narration controls and events, `#region-{code}` as the target of a locked region's last Kenali slide.
- **Checked.** Browser screenshots at 1280 × 720 against the four rendered states of candidate 04; region selection, locked region, Kenali open/Escape with focus return, menu; at 640 × 360 every visible control is at least 48 dp.

### Follow-up after merge (18 October 2026)

Owner request after PR #133:
- **Story bar** is as wide as the map (20, 524, 853 × 176). Next is a round button like Previous with the mirrored chevron (it used the gold primary skin).
- **Portrait** is Jaka's bust avatar, not a pose frame. The new slot `ui.jaka-avatar` (512 × 512, shown at 120 dp) is filled from catalogue `reference.03`, the picture mockup candidate 04 uses there (`CatalogImporter::REFERENCE_SLOTS`; the other references stay out). Without it the circle shows his initial; a speaker other than Jaka would still get a pose frame.
- **Page buttons** (Pustaka Kedu, and once everything is done Tonton penutup and Balai Refleksi) moved from the bottom nav bar to the right of the story, under the region panel (887, 524, 373 × 176), drawn with the panel's button skins. The page no longer has a nav bar, so the story and the buttons take the freed bottom strip (up to y 700). With three buttons, Pustaka and Penutup share a row and a long label (EN "Watch the ending") wraps to two lines instead of being cut.
- **Mulai/Lanjut** in the region panel carries no arrow icon.
- **Checked.** Screenshots at 1280 × 720 in ID and EN (the all-done row previewed in the browser); at 640 × 360 every visible control is at least 48 dp.

## One look on every page (18 October 2026, branch `sky/clever-hopper-yaht5p`)

Owner request after PR #134, with four choices settled in a short interview:
- **One HUD.** `components/hud-menu` is now the only HUD; `components/hud` and `components/lang-switch` are removed. Every page has the same logo, language flag popover, sound control, and grouped menu.
  - The 120 dp HUD would cover the top of every page laid out for the old 56 dp strip. The owner chose a **compact size** (`.is-compact`, 56 dp: 48 dp logo, 56 dp controls with a 46 dp disc, a 352 × 48 dp shard pill). Pages setting `hudStyle` = `menu` (Peta Kedu, the result screen) keep the full size. No page layout moved.
  - Before login, Back (`hudBack`) comes first, then the logo; the welcome page still hides the brand. The menu then holds only fullscreen and hides itself when that is unsupported.
  - The shard pill is on every in-game page; the result screen turns it off (`hudShards` = `off`). The language control stays hidden during a challenge.
- **Shard pill.** The bar now sits on the count's row and fills the rest of the pill (pill 25 rem on the full HUD). The bar is a slim gold groove. The whole count is bold at its earlier sizes.
- **Story line `intro-07`** said the lantern was "in the top-left corner". It now says "at the top of the screen" (ID and EN). The old text is in `story-previous.php`, so `gelita:story:sync` updates installed servers.
- **Main buttons.** The owner chose **every main button**. Every `.btn-primary` on `#stage` takes the Lanjut / Balai Refleksi look: the layout sets `--skin-primary` (`ui.result-button-primary`) on `#stage`, and without the picture the same light-gold gradient is used. This is scoped to `.stage`, so the admin panel keeps its buttons. Narrow buttons stretch the skin a little.
- **Mission card** after the owner's picture: parchment with gold corners; a dark green `{n}/{total}` medallion between flourishes above the card; the region with a pin on a rule; a large serif title; the story; the hint box with a brown "i"; and the gold button with an arrow between flourishes. It moved to 260, 112, 760 × 536 so the medallion clears the compact HUD. The engine chip is dropped (the title names it); the position line stays for screen readers. The best result stays, for replays.
- **Diorama.** A completed region gets a **gold check badge** in the lock badge's place (owner's choice over replacing the whole medallion).
- **Kenali picture.** The owner's picture (book, magnifier, bulb) is **shipped with the game** as `public/assets/ui/icon-kenali.webp` (192 px, alpha), cropped from the attachment. `AssetPackageBuilder::CODE_SOURCES` now packs `assets/ui` (webp, svg), so offline packages carry it and the placeholder.
- **Checked.** Screenshots at 1280 × 720:
  - Peta Kedu, a region map, two mission cards, a challenge, login, `/mulai`, the welcome page, and the profile;
  - the menu, language, and shard popovers in the compact HUD.

  At 640 × 360 every HUD control and main button is at least 48 dp.

## Susun Gambar images from the catalogue (18 October 2026, branch `sky/gracious-heisenberg-ps39sd`)

Owner request: the catalogue has new images that suit Susun Gambar better; integrate them and replace the old ones.

- **What the catalogue has.** Puzzle edition of 14 October 2026 (`gelita-katalog/docs/PUZZLE-UPDATE-20261014.md`): 21 new variants d–j and four redrawn images (`challenge.item.tmg-1-01`, `tmg-1-03`, `mgl-1-01`, `wnb-1-07`; the old ones were night or white-background scenes that failed or nearly failed the tile check). All 30 are full-bleed daylight scenes, 1200 × 1200, listed in `data.json` under their slot keys. The five other a–c images stay.
- **No importer change needed.** The bank already has the 30 slots (draft 2 integration, 17 October 2026), and `CatalogImporter` installs `challenge.item.*` by key. On an existing server a plain `gelita:katalog:impor` fills the d–j slots (empty or sample) and reinstalls the four redrawn ones when the old file came from the catalogue (sha256 differs). An admin upload of one of the four is kept; `docs/08_DEPLOYMENT.md` gives the `--timpa --kunci=…` command limited to those four keys.
- **Guard test.** `CatalogImportTest::testCatalogueHasEveryPuzzleImageOfTheBank` reads the real `gelita-katalog/`: every puzzle slot of the bank has a catalogue entry with a square image of at least 1200 px, and a dry run limited to `challenge.item.*-1-*` plans all 30.
- **Deviation from the draft workbook.** The DAFTAR_MEDIA `source` column of the 25 affected slots still told the team to make the images ("BARU. Buat dengan prompt …", "WAJIB GANTI …"). It now says the image is in the catalogue (prompt code kept, Commons reference kept for the redrawn four) and that fact checking and a two-person assembly test remain before data collection. The workbook is rebuilt; `gelita-katalog/update/bank-soal/bank-soal-kelas5.xlsx` is the catalogue's own draft and is not changed.
- **Counts in active docs.** `docs/07` and `docs/08` said 45 challenge image slots (9 Susun Gambar); they now say 66 (30). Plan documents and older log entries keep their historical numbers.
- **Not done.** No server was touched. Fact checks (inscription script, reliefs, costumes, bundengan) and the two-person assembly test are the researcher's, as the catalogue's review notes say; `puzzle.review_status` there stays `needs_verification`.

## Asset package — Stage 3: "Siapkan GELITA" and "Periksa paket" (18 October 2026, branch `paket-3-layar`)

Plan `docs/rencana-paket-aset.md` §10 row 3 (§5.1, §5.5, §5.6, §8.1–§8.7, §11.2–§11.3), owner decisions P7 and P11. Package mode off, or no service worker: nothing changes for students.

- **Download screen as its own page, `/siapkan`** (`Game\PackageController::prepare`, view `game/prepare`, `game/prepare.js`), not a panel on the welcome screen: the welcome stage has no room for six progress rows, and a page of its own can also catch students who open `/mulai`, `/masuk`, or `/daftar/{n}` directly. Package mode off → the route redirects to its `?lanjut=` page (allow-list `mulai`, `masuk`, `daftar`, `daftar/1–3`).
- **The Mulai gate is client-side** (`game/package-gate.js`), because only the browser knows whether it holds the package. Welcome: Mulai is rewritten to `/siapkan`. `/mulai`, `/masuk`, `/daftar/{n}`: `location.replace('/siapkan?lanjut=…')`. Skipped with package mode off, during a running game session (S8), after an online choice in this browser session, and in browsers that cannot hold a package (§8.6, `daring`). A device the boot mirror says does not hold the page's package is sent at once, without waiting for a request. Without JavaScript nothing is gated; such a device cannot hold a package anyway.
- **No skip button (P11).** Mulai is a disabled `<button>` until `check()` reports `canStart` after `download()`; it then gets the focus. Downloading starts by itself on the page; network, server, and "incomplete" errors retry by themselves every 10 s and on the browser's `online` event, keeping finished files, and also offer **Coba lagi**.
- **Online mode (§8.7).** **Main daring di perangkat ini** with reason `storage` appears only after a `quota` error; the text says "Mode privat …" when the whole quota is below the package × 1.2 (private window or a browser that keeps very little), otherwise the shortfall in MB, or "sudah penuh" after a `QuotaExceededError` while writing. The `lab` choice appears only while `online_lab_allowed` is on, under the closed "Untuk guru" section, so students do not see it as a shortcut. Choosing online aborts the download (files kept).
- **Install hint.** The `[data-install]` box of `game/install.js` is reused on the download screen with an iPad-specific text (`Package.prepareInstallIos`), so iPad/iPhone get Bagikan → Tambah ke Layar Utama, Android the install button.
- **Where Periksa paket lives and who may open it: `/periksa-paket`, a game page without login.** Reasons: the check is about one device's browser storage, so it must run in the game origin on that device; a staff login on each student device would leave a staff session in a browser students use next (the panel login and the participant login share the session cookie); and every action (re-hash, download what is missing, ask for permanent storage, remove the package) touches only public images and sounds (§9.1). The worst a student can do is remove the package, which only forces a new download and is behind a confirmation. It works with package mode off too, so a device can free the space (§6.3). Linked from "Untuk guru" on Siapkan GELITA and described in `docs/08_DEPLOYMENT.md` (teacher routine). Not linked from the Stage 6 panel page, which this stage was not to change; a link there ("open on the device") is a possible follow-up.
- **Layout (owner, 18 October 2026, during review): full width, no Jaka.** Both screens use one ornate panel 32, 88, 1216 × 600 dp, so the longer texts fit on fewer lines and every button label stays on one line.
- **Texts.** View text in a new bilingual `Package.php` (added to `LanguageFilesTest::BILINGUAL`), script text in `Js.php` (`package*`, `inspect*`). Teacher hints on the student screen start with "Untuk guru:". Sizes and dates are formatted in the page (`game/package-ui.js`, `Intl`, WIB), matching `fmt_num()`/`fmt_date()`.
- **No change to `core/package.js`, `sw.js`, the builder, or the Stage 6 panel.**
- **Noticed, not changed:** with package mode on, pages load CSS/JS from the package's code snapshot, so any later code change (including this stage's) reaches devices only after **Bangun ulang paket** + publish, as `docs/08` already says.
- **Tests.** `PackageScreensTest` (6), `RouteWiringTest` (routes without filters, controller count 13). Browser: `tools/qa/package-screens.mjs`, 47 checks on a local SQLite world with a 430-file, 36.4 MB package (both languages, with music and narration): gate on welcome and `/masuk`, held until verified, progress per part, server stopped at ~30 % and resumed by itself with no file downloaded twice, Mulai focus + Enter, sign-up `lengkap`; Periksa paket verify / damaged file named / repair one file / clear; private mode, storage short, storage full mid-download, lab device → `daring`; package mode off → no gate, `nonaktif`; no service worker → gate skipped. `tools/qa/package-check.mjs` (Stage 2) still passes. Not tested on real iPads, Chromebook guest sessions, or a real private window (estimates are stubbed); those stay on the manual list of §11.3.

## Visible tagline under the welcome logo (18 October 2026, branch `welcome-tagline`)

Owner request: the welcome screen shows "Game Edukasi Literasi dan Etnopedagogi Kedu" under the large GELITA logo, in the page language ("Kedu Literacy and Ethnopedagogy Educational Game" in English). It is the existing `Game.tagline`, which was only a `.visually-hidden` text while the logo was an image. The hero box grows from 240, 120, 800 × 300 to 240, 104, 800 × 336 and the hero logo is capped at 280 dp high, so the tagline fits above Mulai (456) without moving it. With package mode on, the new CSS reaches devices after **Bangun ulang paket** + publish.

## Installed app opens from its icon on Xiaomi phones (18 October 2026, branch `sky/focused-heisenberg-aqw3qx`)

Owner report: on a Xiaomi phone (HyperOS), GELITA installed from Chrome opens once (right after install, or from Chrome's "installed" notification), but the home-screen icon afterwards only shows a "Web App" card in recents and closes without any GELITA page. The live server was healthy (manifest, icons, `/`, `sw.js` all fine). The owner's other app DATAKU (`arumiforge/datasiswa`) opens fine on the same phone; its manifest is `display: standalone`, `orientation: portrait`. GELITA asked for `display: fullscreen`, `display_override: [fullscreen, standalone]`, `orientation: landscape`. Other projects report the same HyperOS behaviour for installed apps that ask for `fullscreen` or a fixed `landscape` orientation, fixed by plain `standalone` without `orientation`.

Decision: the manifest (`Game\AppController::manifest()`) is `display: standalone` with no `display_override` and no `orientation`. Deviation from Dok 3 / display plan Phase A3, which locked landscape + fullscreen for the installed app: on every device the rotate screen now asks for landscape (as it already did on iPhone/iPad), and the HUD full-screen button stays. `Game.installHint` no longer promises a sideways, full screen. The manifest is made by PHP and is not part of the asset package, so no package rebuild is needed.
- [ ] After deploy: on phones that already installed GELITA, uninstall it and install again from Chrome (the broken icon cannot start, so it cannot pick up the new manifest by itself); then confirm the icon opens GELITA on the Xiaomi phone

## Install dialog on the welcome page (18 October 2026, branch `sky/focused-heisenberg-aqw3qx`)

Owner report and request: the "Pasang GELITA" button did not appear on the phone; on mobile the install offer should come first, as a dialog over a blurred background.

Why the button could stay hidden: it only appeared once Chrome fired `beforeinstallprompt`, and only `game/install.js` (inside the `game.js` module graph) listened for it. Chrome never fires the event while GELITA is already installed, after a recent dismissal, or in other browsers, and on a slow phone it can fire before the modules load, so it was missed.

Decisions:
- `core/install-boot.js`, a classic blocking script in `<head>` before the manifest link, holds the event in `window.GELITA_INSTALL_PROMPT`.
- Welcome page, touch devices not running the installed app: the **Pasang GELITA** button always shows, and a dialog (`showModal` with the new `blur` option and `install` icon) opens over the blurred stage. With Chrome's offer: *Nanti saja* / *Pasang sekarang*. Without it after 2.5 s: the Chrome menu steps plus "already installed? open it from its icon"; iPhone/iPad: Share → Add to Home Screen.
- It opens once per browser session (`sessionStorage`), after the rotate screen is gone; the button reopens it. Computers: no automatic dialog, the button only with Chrome's offer. The Siapkan GELITA page keeps its inline hint.
- Not done: asking for Android or Xiaomi permissions (a web page cannot), or detecting an existing install with `getInstalledRelatedApps()` (the dialog's "already installed" line covers that case).
- `docs/08` device step no longer says the installed app locks landscape (left over from the standalone manifest change, PR #142).

## Intro screen redesign (18 October 2026, branch `sky/blissful-brown-pmxupk`)

Owner request: bigger, touch-friendly icons on the intro; "Bagian n dari N" at the top centre inside the HUD with the page dots below it; the narrator controls on the caption title's row at the far right, the caption's Lanjut button replaced by ▶ reading "Lanjut" while there is a next slide; "Mulai petualangan" in the middle of the page; Lewati right under the menu button; a full-width caption filling the bottom; a bigger GELITA logo.

Decisions:
- **Full-size HUD on `/intro` and `/penutup`** (section `hudStyle` = `menu`): the 100 dp logo and 68 dp round controls. Section `hudShards` = `off`, because the HUD centre now holds the slide progress. Story line `intro-07` still points at "the lantern at the top of the screen"; it is visible on every page after the intro.
- **The ending shares the layout.** Both screens render `partials/cine-slides`, so the change applies to both; the dialogue, Kenali, and map story keep theirs.
- **Coordinates (dp):** `.cine-top` centred at y 12; caption 0, 496, 1280 × 224; narrator controls at y 508, 48 dp from the right (56 dp buttons, 8 dp apart; the head row keeps `--cine-controls-w` = 33rem free for them); character box 64, 112, 300 × 400; `.cine-final` centred between the HUD (120) and the caption; Lewati at y 104 flush with the menu disc's right edge (21 dp).
- **HUD band passes taps through on these screens** (`pointer-events: none` on `.hud`, back on its logo and controls), so the page dots under the HUD band work. "Ketuk untuk mulai" now covers the whole stage at z-index 14, under the HUD and Lewati, so both work before the story starts.
- **No-JavaScript fallback kept:** the caption still renders Kembali/Lanjut links (`.slide-nav.cine-step`); `.cine.is-live` hides them, and the swipe still clicks them. The final link moved out of `.slide-nav`, so `narrator.js` and `slides.js` now find it by `[data-slide-final]:not([href^="#"])` (same element in every other screen).
- **`narrator-controls` takes `nextLabel`:** ▶ gets the visible label and accessible name "Lanjut"; the label hides while the button is disabled (last slide).
- **Tests.** `CinematicViewTest::testIntroLayoutPutsProgressInTheHudAndTheFinalButtonInTheMiddle`. Checked in Chromium at 1280 × 720 (tap card, narrated slides, a character slide, the last slide, and without JavaScript) on a local SQLite world.

## Account pages, full-page tap card, automatic code version (18 October 2026, branch `sky/blissful-brown-pmxupk`)

Owner request: redesign `/masuk` and the password reset screen on the first attached picture (lantern tree above a lit village, empty right side for the form) and registration on the second (overgrown gate and a path of light); make "Ketuk untuk mulai" cover the whole page including the HUD, with larger text, the GELITA lantern picture instead of an SVG, and no frame, on every page; make sure the web serves the newest code while package mode is off without building a package and turning it off again. Interview: the owner updates the server without the deploy scripts while package mode is off; the password reset screen is `/ganti-sandi` (there is no self-service reset).

Decisions:
- **Pictures ship with the game** as `public/assets/ui/bg-auth-login.webp` and `bg-auth-register.webp` (the owner's 1920 × 1080 WebP files, unchanged), like the Kenali icon: they appear right after an update without `gelita:media:scan`, and `AssetPackageBuilder::CODE_SOURCES` already packs `assets/ui` WebP. They take precedence over the `bg.auth` slot, which is now marked unused in `ImageSlots` (the catalogue picture installed there would otherwise hide them); `bg.welcome` no longer lists the account screens.
- **Layout:** no Jaka figure (the picture is the scene). Ornate panel at 720, 88, 512 × 584 on `/masuk`, 720, 72, 512 × 568 on `/ganti-sandi` (above the Profil nav bar; it moved from the parchment panel to the same ornate panel), 656, 72, 576 × 600 on `/daftar/1–3`, whose head now stacks eyebrow, title, and stepper in three centred rows. Body class `is-auth-art` replaces the dark scene veil with a light one on the right only.
- **Tap card** (`components/narrator-tap`, intro, ending, region dialogue, region done): `inset: 0` at z-index 30, above the HUD (the intro's z-index 14 exception from the morning is gone). The `ui.lantern` picture at 192 dp (136 dp on the chapter and region-done cards, which also carry a title) with a breathing glow behind it; the SVG on a gold disc stays only while no picture is uploaded. Label 56 dp, hint 24 dp. No outline, also on the focus `narrator.js` gives the button on load; focus and press brighten the lantern instead.
- **Code version:** `asset_url_versioned()` adds `asset_file_version()` = `gelita.assetVersion` + 8 hex characters from the file's mtime and size while package mode is off, so a changed CSS/JS entry gets a new immutable URL with or without the deploy scripts. Imported modules still rely on nginx revalidation (`no-cache`); package mode on is unchanged (code from the package snapshot).
- **Tests.** `AssetFileVersionTest` (new), `AssetPackageCodeUrlTest`, `StartGateTest`, `RegisterWizardTest`, `MediaSlotPagesTest` updated. Checked in Chromium at 1280 × 720 on a local SQLite world: `/masuk`, `/daftar/1–3`, `/ganti-sandi`, the intro tap card, and the chapter tap card.

## Jaka on the account pages (18 October 2026, branch `sky/blissful-brown-pmxupk`)

Owner request: add the attached Jaka (waving, red scarf, backpack) without animation frames to `/masuk`, `/ganti-sandi`, and `/daftar/1–3`, placed to match the background; a tall figure is fine.

Decisions:
- **One still picture shipped with the game:** `public/assets/ui/jaka-wave.webp`, the owner's transparent 1024 × 1536 WebP trimmed to the figure (658 × 1490, quality 85). It is a plain `<img class="auth-jaka">`, not `components/character`, so no pose frames or `char.jaka.*` slots are involved; `assets/ui` is already packed into asset packages.
- **Placement (dp):** 265 × 600 with the feet at y 712. `/masuk` and `/ganti-sandi`: x 340, on the stone steps below the lantern tree. `/daftar/1–3`: x 372, on the path at the right pillar of the gate, so the lit arch opening stays visible and the panel (x ≥ 656) stays clear. This partly reverses "no Jaka" from the morning's redesign.
- **Tests.** `StartGateTest` and `RegisterWizardTest` check the still picture (and still no `data-character="jaka"`). Checked in Chromium at 1280 × 720 on `/masuk`, `/ganti-sandi`, `/daftar/1`, and `/daftar/3`.

## Intro, map curtain, and Peta Kedu polish (18 October 2026, branch `sky/blissful-brown-pmxupk`)

Owner list (with a mockup of the "Wilayah dipilih" panel):

- **Intro.**
  - The slide title gets no focus ring: `slides.js` focuses it for screen readers on every change, and that is not a control.
  - The narrator controls lose their borders (pill and buttons); ⏸/▶ is marked by a soft gold fill.
  - On a slide with a character, the figure stands at the left of the caption text and is drawn over it: `.cine-stage` 24, 288, 336 × 432 dp at z-index 4, and the caption text starts at x 328.
  - The space kept free for the controls on the title row follows them: 15rem without narration audio, 33rem with it (`.has-audio`), so titles no longer wrap beside a character.
  - The dots sit 8 dp under the eyebrow pill, and the current dot has a navy border inside a gold ring.
  - "Mulai petualangan" has no arrow and is set in capitals (CSS `text-transform`, the language string is unchanged).
- **Intro menu.** `GateController::intro()` now passes the signed-in participant. `components/hud-menu` shows the account rows to any signed-in player (`$signedIn`) and keeps the Peta Kedu row and the shard pill for pages with progress data (`$inGame`). Without progress (the opening story), the menu has Profil, **Kembali ke awal** (`/`, `Game.backHome`), Layar penuh, and Keluar.
- **Map curtain.**
  - The lantern picture (`ui.lantern`) is shown alone at 120 dp, without the gold disc. The SVG keeps the disc.
  - The tap button is just its PNG skin: no border, focus ring, or box shadow. It pulses with `transform` plus `drop-shadow`, which follows the picture's outline, instead of a box shadow (`tap-pulse`). This applies to every curtain.
- **Peta Kedu.**
  - **City captions** use the panel skin as a small 9-slice frame instead of the detail button. The selected city glows instead of getting an outline, and keyboard focus glows gold.
  - **Region panel background:** `bg.loading.{code}` (`region_loading_bg()`, else the region background) fills the panel inside the frame and is masked out towards the left and bottom; locked regions are greyed.
  - **Region panel text:** the eyebrow sits between two gold diamonds. The status pill has its own icon and colour: Terbuka sparkle and gold, Sedang dijelajahi a new `compass` icon and orange, Tuntas check and green, Terkunci lock and grey.
  - **Progress:** the bar takes the free width, followed by "n/N tantangan".
  - **Lanjut** in the panel uses a lower skin box, so it is as tall as Kenali. The Kenali picture stays the owner's book and magnifier, not the mockup's lantern.
  - **Story bar:** Previous, Play, Replay, and Next are 40 dp round buttons in the bar's top-right corner, level with the dots, and the first line keeps that corner free. The bar is 144 dp tall instead of 176, and the map and region panel grew to 428 dp, so the 2:1 diorama now shows whole (pins are in % of the picture, unaffected).
- **Tests.**
  - `CinematicViewTest::testIntroMenuHasTheAccountRows` and `RegionStoryTest::testRegionPanelShowsTheCityPictureAndStatus` are new.
  - Checked in Chromium at 1280 × 720 on a local SQLite world with the catalogue installed: intro slides with Mbah Kedu, a keyboard advance, the intro menu, the map curtain, and Peta Kedu with Temanggung and Wonosobo selected.

## Welcome modal and region dialogue in the intro layout (19 October 2026, branch `sky/pensive-einstein-x7fcqo`)

Owner request: the welcome card should appear as a modal right after the account is created, then lead into the intro. Redesign the region dialogue page like the intro: Lewati under the menu button, no Peta Kedu button at the bottom left (it is in the menu), and the screen head replaced by each region's difficulty, with extra style and animation.

Decisions:
- **Welcome modal.** The server still renders `.welcome-card` on `/intro` (readable without JavaScript). `game/intro.js` lifts it into the shared game modal: key icon, blurred stage, a 40-piece confetti burst, one **Lanjut** button. It opens above the "Ketuk untuk mulai" card, so the student reads it first and the next tap starts the intro; the extra tap is kept on purpose because the tap card is what unlocks audio on iPad. The `[data-narrator-welcome]` slot in the tap card is gone.
- **Region dialogue layout.** `partials/dialogue-scene` takes `cinematic`; only `/dialog/{code}` sets it. `/tuntas/{code}` keeps the parchment box, its screen head, and its nav, because the owner did not ask for it. The page uses the intro's pieces (`is-cinematic`, full-size HUD without the shard pill, `.cine-top`, `.cine-caption`, `.cine-final`, narrator controls with "Lanjut") rather than new ones.
- **Two characters, one caption.** Unlike the intro, both characters stay on stage (they talk to each other), standing on the caption with their feet tucked behind it, instead of one figure drawn over the caption text. The caption text keeps the full width.
- **Difficulty text.** The stored `levels.difficulty` (`mudah`, `sedang`, `sulit`) is shown as a badge between the characters: label, level, a three-bar meter, and a short cheer per level (`Game.difficultyHint_*`, new). Bars, not stars, so it is never read as the score stars (rule `bintang-1`). Colour per level: green, gold, ember.
- **Extra motion.** The badge drops in when the chapter card is tapped away, its bars rise and glow, and a light sweeps across it. The speaker has a breathing halo and a slow float, and the speaker chip pops in on every line (Jaka red, Mbah Kedu gold). A notch on the caption points at the speaker, and light motes rise through the scene. All of it is off under `prefers-reduced-motion`.
- **Page dots.** At 15–16 lines the intro's 16 dp dots ran into the region name in the HUD, so the dialogue uses 11 dp dots 8 dp apart (hit area 48 × 19 dp, within the owner's call on narrow dot targets, 11 October 2026).
- **QA tool.** `tools/qa/stage-check.mjs` exempts `.dialogue-ambience` from the clipped-content check, like the Ken Burns backgrounds: its motes layer is deliberately taller than the stage and clipped.
- **Pre-existing, not fixed here.** `stage-check` reports `div.hud-sheet.hud-menu-sheet` outside `#stage` on every full-size HUD page (`/intro`, `/peta`, and now `/dialog/{code}`); it is the same on `main`.
- **Owner follow-up (same day).** These points replace the badge, dots, and stage placement above:
  - The difficulty badge moved into the HUD, left of the language control (new HUD section `hudBadge`, `partials/region-difficulty`), without the cheer line (`Game.difficultyHint_*` removed).
  - "Bagian n dari N" and the dots moved from the HUD centre to the right of the speaker's name in the caption.
  - The caption stays full width (0, 448, 1280 × 272), but its content keeps to x 360–920. Jaka and Mbah Kedu (352 × 456 dp) stand over its two ends, feet on the bottom edge, instead of above it. The caption's notch is gone, since the speakers now flank the text.
  - The narrator controls moved to the bottom right of the text, because the first row now holds the name and the progress.
  - The badge's entrance keys off `body:has(.dialogue-cine.is-started)`, since it is no longer inside the scene.
- **Tests.** `RegionStoryTest::testDialogueUsesTheIntroLayoutWithTheDifficultyBadge` and `testRegionDoneSceneKeepsTheParchmentBox` are new; `CinematicViewTest::testWelcomeCardSurvivesAfterRegistration` checks the modal hand-off. Checked in Chromium on a local MariaDB install with the catalogue art: sign-up → welcome modal → tap card → intro, and `/dialog/temanggung`, `/dialog/magelang`, `/dialog/wonosobo` (first line, last line); `stage-check --only=intro,dialog` at all six sizes passes apart from the pre-existing finding above.

### Region dialogue, second owner follow-up (19 October 2026, branch `sky/pensive-einstein-x7fcqo`, after PR #154)

Owner request with a picture of the old box: bring the old narration box back (not full width), drop the "Bagian x dari x" text and keep the dots, put the region name in capitals and larger, move the characters closer to the left, right, and bottom edges, drop the name labels, show the shard pill under the logo, make the dots smaller, and put the narrator controls in the row of the name and the dots, at the right.

Decisions:
- **Box.** The parchment box is back at 160, 520, 960 × 176. It sits lower than the old 472 because the bottom nav bar is gone. Its first row holds the name, 8 dp dots, and the narrator controls. The controls stay one shared element (`narrator-controls`), placed over the row's right end; the row keeps `--dlg-controls-w` free for them (11.5rem, or 30rem with narration audio for ⏸ ↻ and Otomatis). The controls are restyled for parchment, with brown discs and "Lanjut" on the gold main-button skin like the old box's Lanjut. Kembali/Lanjut links stay in that row as the no-JavaScript fallback.
- **Final button.** "Masuk ke {wilayah}" stays centred on the page (from PR #154); the owner did not ask to move it back into the box.
- **Speaker name.** Back to the old brown pill for both characters, as in the owner's picture (PR #154 had Jaka red). It still pops in on every line.
- **Characters.** 320 × 464 dp at x 0 and x 960, feet on the bottom edge, behind the box as in the old layout. The name labels under them stay hidden.
- **HUD.** The shard pill comes back (`hudShards` no longer `off`) and sits under the logo at 24, 116, 360 wide. `body.is-cinematic` lets taps reach `.hud-shards`, which it blocked before. The region name is in Cinzel capitals at 36 dp. Both are scoped to `.is-region-dialogue`; other pages keep their HUD.
- **Tests.** `RegionStoryTest::testDialogueUsesTheIntroLayoutWithTheDifficultyBadge` was rewritten for the box. Checked in Chromium on a local MariaDB install: all three regions, the control row with simulated narration audio, and the shard detail popover; after merging `main` (closed HUD sheets hidden, PR #156), `stage-check --only=intro,dialog` passes at all six sizes.

## Closed HUD menu sheet reported outside the stage (19 October 2026, branch `sky/loving-hopper-6fbsjl`)

`node tools/qa/stage-check.mjs --only=intro,peta` reported `keluar dari #stage: div.hud-sheet.hud-menu-sheet` at every size on the pages with the full-size HUD (`/intro`, `/peta`, `/dialog/{code}`), also on `main`.

- **Cause: the closed sheet, not the open one.** Measured in Chromium at 1280 × 720 (stage dp): the open sheet sits at 801, 135 – 1234, 641, inside the stage. While the `<details>` is closed, Chromium still lays its content out (in a `content-visibility: hidden` slot below the summary), keeps the sheet's computed `display: grid`, and returns a real `getBoundingClientRect()`. That slot also becomes the absolutely positioned sheet's containing block, so the closed sheet's box sits 96 dp lower (801, 231 – 1234, 737), 17 dp past the stage bottom. Nothing is painted there, but the checker's `visible()` (display, visibility, opacity only) counts it. The language sheet is shorter, so its closed box stays inside; the compact HUD's menu sheet starts higher.
- **Fix: make the closed sheet truly hidden.** `stage.css`: `.hud-pop:not([open]) > .hud-sheet { display: none; }` for both HUD popovers. No layout change to the open sheet. The checker is left as is, so it still catches a real overflow of an open popover or of any element that is merely hidden by a closed `<details>` elsewhere.
- **Checks.** `stage-check --only=intro,peta` on a fresh local SQLite world (grade-5 world + `ContentSeeder`): all six sizes and the rotate gate pass. With the intro finished, `--only=intro,peta,kenali,dialog-temanggung,dialog-magelang,dialog-wonosobo` also passes at all six sizes (so `/peta` and `/dialog/{code}` are really visited). Playwright on `/intro`, `/peta`, and `/dialog/temanggung`: mouse click on the menu button opens and closes the sheet, the scrim/outside click and the close button close it (focus back to the button), Enter and Space toggle it, Tab reaches the first row, Escape closes it with focus back, opening the menu closes the language popover; the same by touch (`hasTouch`, 1024 × 768); no page errors. `vendor/bin/phpunit --no-coverage`: 751 tests pass.

## HTML minification and the page-source watermark (19 October 2026, branch `pbb/admiring-archimedes-sa2m0n`)

Owner request: minify the pages and put "Mr. F" in the figlet font ANSI Shadow at the top of the page source.

- **Where.** `App\Filters\HtmlMinifyFilter` (alias `minifyHtml`, global `after` filter) calls `App\Libraries\HtmlMinifier`. It only touches a full page (`text/html` whose body starts with the doctype or `<html>`, after any comments); JSON, CSV/XLSX, PDF, downloads, and HTML fragments pass through. Switch: `Config\Gelita::$minifyHtml` (default on, every environment, so the tests cover it).
- **Watermark.** An HTML comment holding the six ANSI Shadow lines (`HtmlMinifier::WATERMARK`, generated with pyfiglet `ansi_shadow`, trailing spaces trimmed) before the doctype. The HTML parser allows comments before the doctype without quirks mode. It pushes `<meta charset>` past the first 1024 bytes, which is harmless because the charset comes from the `Content-Type` header.
- **Safe minification, not maximum.** Comments are dropped (except IE conditional comments and the debug toolbar's `DEBUG-VIEW` markers, so the toolbar keeps working in development). Whitespace runs in text become one space, never zero, so inline and inline-block neighbours keep their gap; whitespace is removed only around tags that never render (doctype, html, head, meta, link, title, base, body). Inside tags only the whitespace between attributes is collapsed; quoted values stay. `<pre>`, `<textarea>`, `<script>`, `<style>`, `<code>`, and elements marked `data-keep-space` stay byte for byte. JS and CSS are not minified (the ES modules are served as files, without a bundler).
- **`data-keep-space`.** The only `white-space: pre-wrap` element that is not one of the kept tags is the full-text `<p>` in `.cell-more` (AdminTable); it now carries the marker. A marked element must not contain another element of the same tag.
- **Saving.** Small, about 2–6 % on the game pages: the views are already compact.
- **Checks.** `HtmlMinifierTest`. Real pages (`/`, `/masuk`, `/profil`, `/pustaka`, `/admin/login`) rendered from the grade-5 test world with the switch off and on, then compared in Chromium at 1280 × 720: identical screenshots, `innerText`, and element boxes, no page errors. `vendor/bin/phpunit --no-coverage`: 762 tests pass.

## Minified JS/CSS for browsers and packages (19 October 2026, branch `pbb/admiring-archimedes-sa2m0n`)

Owner questions after PR #160:
- Is the in-house approach good enough without a library?
- Can all JS/CSS be minified too, including in package mode?
- Can only what the browser gets be minified, with the source files left as they are?

Owner decision: use a PHP library for JS/CSS. HTML keeps the in-house `HtmlMinifier`; a library would add only a few percent, and nginx already gzips.

- **Library: `wikimedia/minify` 2.12 (Apache-2.0), not `matthiasmullie/minify`.**
  - `matthiasmullie/minify` 1.3.75 was the planned choice. A syntax-tree comparison of all 58 JS files (acorn, original against minified) found 2 changed files: it drops the spaces next to `${…}` inside a nested template literal. `admin/bank-import.js` would show "12soal untuk 3tantangan", and the error text in `core/package.js` changes the same way.
  - `tedivm/jshrink` 1.8.1 changed 4 files.
  - `wikimedia/minify` (MediaWiki's tokenizer-based minifier) changed none and gives the smallest output. Its dependencies are `pear/net_url2`, `ext-fileinfo`, and `ext-mbstring`. Production has PHP only, so a Node-based minifier was not an option.
  - The dependency is justified in `composer.json` → `gelita-dependency-notes`.
- **Mirror, not in place.**
  - `gelita:aset:minify` (`AssetMinifier`) writes `public/assets/min/{version}/{js,css,fonts}/…`, keeping names and folders so relative imports and `url('../fonts/…')` still resolve. Fonts are copied so the minified CSS finds them.
  - `{version}` is the first 12 hex digits of the sha256 over all minified files, so imported modules (no `?v=`) also get new URLs per build.
  - Everything happens inside `public/assets/min/`, the only new folder `www-data` must own. A build is written to a temporary folder, then becomes current with one rename of `sources.json`. The previous build is kept for open pages; older ones are deleted.
  - The sources in git are never written (checked with `git status` after a run).
- **Freshness guard, all or nothing.**
  - The helpers use the mirror only while every source still has the mtime and size recorded at build time and the set of files is unchanged.
  - One stale file switches every URL back to the originals. Checking files one by one would let a new `game.js` import old modules.
  - Cost: about 75 `stat()` calls per request, once (memoised in the shared `assetMinifier` service).
- **Package mode.**
  - `AssetPackageBuilder::start()` refreshes the mirror, and `codeTasks()` takes JS/CSS from it under the same keys. `runTask()`, the manifest hashes, `core/package.js`, and `sw.js` are unchanged.
  - The first package with minification gets a new `code_version`, so devices download the code group once more.
  - A failed run only adds the warning `minify_failed`, and the package keeps the original files.
- **Not minified:** `sw.js`, `sw-kosong.js`, `offline.html`, vendor files, media.
- **Off in tests** (`Config\Gelita::$minifyAssets` is false under `ENVIRONMENT === 'testing'`), so a mirror on a developer machine never changes the URLs other tests expect. `AssetMinifierTest` switches it on for its own temporary `public/`.
- **The JS tokenizer is lenient.** It does not report broken input; it passes it through. Correctness therefore rests on the syntax-tree check:
  - `tools/qa/minify-check.mjs` checks every real file;
  - `AssetMinifierTest` runs it whenever Node and acorn are present (in this cloud environment and on developer machines);
  - the server itself cannot run it (no Node).
- **Deploy.** A new step 9 runs `gelita:aset:minify` after `cache:clear`. A failure only warns, so a deploy never stays in maintenance because of it. `docs/08_DEPLOYMENT.md` → *Minified JS/CSS* covers permissions on existing servers.
- **Sizes (measured).**
  - JS: 380 KB → 221 KB.
  - CSS: 329 KB → 245 KB.
  - Gzipped per file, as nginx sends them: 221 KB → 137 KB.
- **Checks.**
  - `AssetMinifierTest` (8 tests, including the syntax-tree comparison of all real files) and the full suite (770 tests) pass.
  - Real pages (`/`, `/masuk`, `/intro`, `/profil`, `/pustaka`, `/admin/login`) were rendered from the grade-5 test world with the switch off and on in Chromium at 1280 × 720. Screenshots, text, element boxes, and loaded fonts are identical, and both sides load the same set of files. With the switch on, code comes only from `assets/min/{version}/`. No page or console errors.
  - The password eye toggle on `/masuk` works with the minified JS.

## Own background per slide for the region scenes (19 October 2026, branch `sky/beautiful-thompson-817hm4`)

Owner request: the `region_intro`, `level_open`, and `level_done` slides can each have their own background; a slide without one keeps today's background. The owner also asked about deeper screen effects with Three.js or Babylon. An interview settled both before any code (answers below). This branch is part 1; the effects are part 2 on a later branch.

**Interview answers (owner, 19 October 2026)**

- Background motion: crossfade with a slow Ken Burns zoom, like the intro.
- Scope: admin field, catalogue keys, and seed data.
- Effects library: Three.js, self-hosted, loaded only where needed; Babylon.js rejected (several MB, heavy on school Chromebooks and iPads, a full 3D engine these slides do not need).
- Effects wanted: drifting fog, fireflies / lantern sparks, light rays with bloom, parallax depth on the background.

**Part 1, as built**

- **No migration.** `dialogues.background_media_id`, the editor's "Latar slide" field, `MediaUsage`, and the asset package already covered it for `intro`/`ending`; only the region contexts ignored the column.
- **Which contexts:** `Config\Gelita::$dialogueBackgroundContexts` = everything but `map_intro` (it plays in the Peta Kedu story bar, which has no backdrop of its own).
- **Fallback:** the region background (`levels.background_media_id`) the layout already draws; for `region_intro` the overlay's dimmed `.kenal-bg`. A slide's own picture in the overlay shows at 0.85 opacity instead of 0.55, so the art reads while the overlay's shade keeps the caption legible.
- **Slot names:** `bg.story.{context}.{wilayah}.{n}` (e.g. `bg.story.level_open.temanggung.3`); global slides keep `bg.story.{intro|ending}.{n}`. One method, `DialogueModel::backgroundKey()`, serves the editor, the catalogue importer, and the seeder. The existing `bg.story.*` slot pattern already matches the longer keys.
- **Motion:** `core/backdrop.js` crossfades two stacked `<img>`: the layout's `#scene-a`/`#scene-b` (built for a crossfade, unused until now) on `/dialog` and `/tuntas`, two `.kenal-bg` in the overlay. A slide's own picture is `.is-own` and drifts on every screen, also on `/tuntas`, whose region background stays still as before. Consecutive slides with the same picture do not restart the fade. The next slide's picture is preloaded.
- **Why a `MutationObserver`:** `map.js` reopens an overlay with `show(n, { notify: false })`, which skips `onChange`, and an overlay can reopen on the slide that is already current. The observer on the narrator root follows `.is-current` and `.is-open`, and a closed overlay loads no pictures (three overlays share the map page).
- **Without JavaScript** the region slides keep the region background. The intro's per-slide `<img>` pattern works without JavaScript but would restart the fade on every line and cannot crossfade; the region scenes need the narrator anyway for the dialogue layout.
- **Catalogue:** `gelita:katalog:impor` reads `bg.cerita-kenal-{wilayah}-NN`, `bg.cerita-dialog-{wilayah}-NN`, and `bg.cerita-tuntas-{wilayah}-NN`, the same words as the narration codes (`NarrationImporter`). A picture for a slide or region the database does not have is skipped. `--kunci` matches catalogue keys, so the filter is `--kunci="bg.cerita-*"`.
- **Seed data:** `data/story.php` takes an optional `bg` (asset key); without it the derived key is used. `ContentSeeder::seedDialogues()` links the slide when that media row already exists. No line sets `bg` yet: the art does not exist. `StoryDataTest` compares lines with the script without `bg` and checks any `bg` is a `bg.story.*` slot. `gelita:story:sync` stays a text sync and does not touch backgrounds.
- **Deploy:** nothing to run. Pictures go in through **Konten → {wilayah} → Dialog** or, once the catalogue has them, `php spark gelita:katalog:impor --kunci="bg.cerita-*"` (dry run first).

**Part 2, WebGL screen effects (owner: "lanjut part 2", same day; part 1 merged as PR #164, part 2 restarted on the same branch name from `main`)**

- **Size is not a constraint (owner, mid-build: "berat tidak menjadi, file besar tidak menjadi masalah").** three.js r18x ships no `.min.js` builds. Instead of a custom tree-shaken build (a build step and a derived file), the official `build/three.module.js` and `build/three.core.js` of `three@0.186.1` are vendored unchanged (2.1 MB together), as the vendor README requires for Howler.
- **Folder carries the version** (`vendor/three-0.186.1/`): `three.module.js` imports `./three.core.js` without `?v=`, so an update must change the path or a browser could pair new and cached old files. The asset package keeps paths (`assets/k/{version}/vendor/…`), so the relative import works offline.
- **No add-ons, own bloom.** `UnrealBloomPass`/`EffectComposer` import the bare specifier `'three'` and would need an inline import map: blocked by the production CSP without a nonce, and only iOS 16.4+. The bloom is built from core render targets (bright pass, two separable blur rounds at 1/4 size) with a soft tone curve, so it works wherever WebGL 2 works.
- **Where:** the five full-screen narrated scenes (intro, ending, Kenali overlay, region dialogue, region done) through `data-fx3d` = `screen_fx_attr()`; the intro and ending are included because they use the same script effects (`intro-01` glow, `intro-03` fog). Not the Peta Kedu story bar. Kill switch `Config\Gelita::$screenFx` (`.env` `gelita.screenFx`).
- **Layering:** one canvas after `.narrator-fx`, so it paints over the backgrounds and under the characters and the caption: the text never sits behind fog. Light is composited with alpha = max(fog, light), so it brightens the page instead of covering it.
- **Which effects WebGL takes over:** fog, fog-lift, glow, and the CSS light motes of the region dialogue (hidden while `.has-fx3d`). Flash, dim, and shake stay CSS and gain a spark burst or dimming in WebGL.
- **Fallbacks,** all ending in the CSS effects: no WebGL 2 (checked before the 2.1 MB import, so no download and no console errors), failed import, shader error, lost context, reduced motion (never started), and a watchdog: frames slower than 45 ms for 4 s step down to 0.75× size, then 0.5× without bloom, then CSS.
- **Frame-rate budget:** not measurable here. Headless Chromium renders with SwiftShader on the CPU (7–8 fps at 1280 × 720); that exercised the watchdog (1280 → 960 → 640 px, CSS after about 13 s) but says nothing about real GPUs. The class tablets need a look by the owner (device test checklist).
- **Parallax without depth maps:** pointer (mouse, pen), else tilt where the browser sends it without a permission prompt (iOS asks, so iOS uses the sway; no prompt for students), else a slow sway. Backgrounds move 10/6 dp (scaled 1.04 so edges never show), character layers 20/10 dp, WebGL fog and sparks more. The `translate` property composes with Ken Burns (`transform`) and the speaking zoom; `.dialogue-stage` became `position: absolute; inset: 0` because a translated element is the containing block of its absolutely placed characters.
- **Lifecycle:** started the first time the scene is on screen, paused when it leaves (closed Kenali overlay); the map creates no canvas until an overlay opens. Parallax users are counted across the three overlays.
- **Checks:** Chromium with WebGL (SwiftShader), without WebGL (`--disable-webgl`), and with reduced motion: fog, glow, and fog-lift render under the caption; the CSS fog returns after `WEBGL_lose_context`; no console errors in any mode. `ScreenFxTest` (attribute, switch, vendored checksums, package source, CSS fallback contract) and view tests.

## Dip to black between story slides (19 October 2026, branch `sky/beautiful-thompson-817hm4`)

Owner report: on slide changes "an image gets stuck, not smooth". Interview answers: seen on the opening and ending story; another picture flashes between two slides; wanted a film-style dip to black on every slide change; only the background goes dark (caption, characters, HUD stay).

- **Cause.** Each `.cine-slide` carried its own `.cine-bg`. On a change the old slide vanished at once and the new one faded in over 0.8 s (`cine-in`), so the layout's story background (`bg.intro`) showed through in between.
- **Fix.** With JavaScript the story backgrounds go through the layout scene images, like the region scenes: `data-bg` on each slide, `core/backdrop.js` in a new dip mode with `.scene` as the veil (black in 0.35 s, swap with no transition, hold 0.1 s, lift in 0.5 s). The page starts on slide 1's own picture (`slide_bg_src()` in `intro.php`/`ending.php`), so the start shows no other picture either. `.cine.is-live` hides the per-slide `.cine-bg` and removes the slide fade; `.cine-slide` gets `isolation: isolate` to keep its gradient layered as the fade's stacking context used to.
- **Every slide change dips** (owner's choice over "only when the picture changes"); `narrator.js` now calls the backdrop only when the current slide really changed, not on every class change of the root (play/pause), so nothing dips twice.
- **Fast taps:** a newer slide takes over at any step and compares with the picture actually on screen (found in the browser check: three quick taps first ended on the stale picture).
- **Unchanged:** the region scenes keep their crossfade (the owner saw the problem only on the story screens); without JavaScript every slide still shows its own `.cine-bg`; reduced motion changes the picture without black.
- **Checks.** Chromium on the local grade-5 world, every animation frame sampled across five changes (own → own, own → none, none → own, three quick taps, back): the veil reaches full black, only the old and the new picture are ever visible, never two at once, and the right picture is up at the end; reduced motion: no black, same pictures. `CinematicViewTest` covers the markup and CSS contract.

## Kenali screen redesign (19 October 2026, branch `sky/friendly-volta-8fyowz`)

Owner request ("redesain halaman kenali"), no interview: the Mengenal {wilayah} overlay on Peta Kedu.

- **Avatar instead of animation frames.** Mbah Kedu is drawn as his round avatar (`ui.kedu-avatar`, catalogue `reference.02` "Mbah Kedu — avatar", via `CatalogImporter::REFERENCE_SLOTS`, like `ui.jaka-avatar`), reusing `partials/kedu-portrait`. Never a pose frame, so `.kenal-stage` and its parallax, pose, and reduced-motion rules are gone (`ScreenFxTest` follows). A speaker who is Jaka gets `ui.jaka-avatar`; no picture gives the initial in a gold disc. All 12 region lines are Mbah Kedu's today.
- **The picture brings its own gold ring**, so `.kenal-avatar .kedu-portrait` drops the partial's border, disc, and clipping for it, and only casts a shadow; the fallback keeps the partial's gold disc.
- **Avatar and caption read as one shape.** The caption bar (`.kenal-caption`) starts under the avatar's centre (`margin-left` = half the avatar, no left border, square left corners) and the 232 dp avatar sits on top of its left end, vertically centred, so the avatar's circle is the visible left edge. A narrator slide has no avatar and keeps a normal rounded bar.
- **Removed texts:** "Wilayah n · {tingkat}" (header eyebrow) and "Bagian n dari N" (caption eyebrow). Each slide's `aria-label` and the dots' visually hidden labels still say which part it is. The difficulty chip stays on `/dialog/{code}`.
- **One caption row:** speaker pill, then title (`.cine-title`), then the page dots and Lanjut (`partials/slide-nav`) at the right corner; the text follows below. With JavaScript the slide's own Kembali is hidden with `display: none` (it was `visibility: hidden` and kept its space).
- **Top band (120 dp, like the HUD):** Kembali (leaves the overlay, `data-kenal-close`, replaces the cross) at the left, the GELITA logo (`ui.logo-hero`, else `ui.logo`, else the text brand, same as the HUD) in the middle, the narrator controls at the right, where the cross used to be. The overlay covers the stage HUD (z-index 40 against 20), so the band is its own, not the HUD.
- **Title** "Mengenal {wilayah}" is centred under the band, on the same dark pill the intro uses for its progress text, not in the band.
- **Without JavaScript** the overlay's Kembali is a `#region-{code}` link and each slide still has its own Kembali (previous slide); only one pair is visible with JavaScript.
- **Deploy:** run `php spark gelita:katalog:impor --kunci="ui.kedu-avatar"` (dry run first) to install the avatar on a server that already ran the import; until then the initial shows. The asset package picks it up on its next build.
- **Checks.** `RegionStoryTest::testRegionIntroLayoutHasABackBandAvatarAndASingleCaptionRow`, `CatalogImportTest::testKeduAvatarComesFromTheMbahKeduReference`; Chromium check on a local SQLite world, see the commit.

## Kenali screen, owner pictures and the display font (19 October 2026, branch `pbb/sharp-edison-tnww8e`)

Owner request with pictures: avatar and caption at the bottom; Cinzel Decorative instead of the old font; Back, Lanjut, and Mbah Kedu's nameplate on the supplied plates; the region name like the example picture (gold letters over a gold rule with an ornament); narrator controls on `ui.result-hud-control` without a band; the nameplate placed as in the example (over the lower part of the avatar ring); the framed Jaka avatar for Jaka's lines. Interview answers: the font changes **everywhere**; **Kembali on the navy plate, Lanjut on the gold plate, nameplate on navy**, and the new gold plate is **Kenali only** (other pages keep `ui.result-button-primary`); the framed Jaka replaces his avatar **everywhere** (Peta Kedu story bar too); **Jaka also gets the nameplate**, and Otomatis stays as plain text.

- **Font.** Cinzel Decorative 1.002 (400, 700, 900) from the owner's TTFs, converted to WOFF2 with fontTools without subsetting (so the files are the unmodified OFL fonts; the Reserved Font Name "Cinzel" is not touched). `--font-display` and the error pages name it; the layout preloads the 700 file; Cinzel's files and licence are removed. Its lowercase letters are small capitals like Cinzel's, so the places that used Georgia for mixed case keep Georgia.
- **Plates.** `public/assets/ui/kenal-plate-gold.webp` and `kenal-plate-navy.webp` (the owner's PNGs as WebP, alpha kept), passed to the CSS as custom properties on `.kenal` with `game_asset_url()` (a relative `url()` in cinematic.css would break in the minified mirror and the asset package). Buttons are 64 dp tall and drawn with `border-image`, so the ornamented ends keep their shape and only the plain middle stretches with the label (gold slice 250 of 249 px, navy 115 of 131). Labels in Cinzel Decorative 700. Without the properties the buttons keep their old look.
- **Nameplate.** The speaker pill left the caption row; the name is on the navy plate inside `.kenal-avatar`, 86 % of the avatar wide, its top at 70 % of the avatar height, as in the example (the ring's bottom tip shows under it). Every speaker with an avatar gets one; the narrator has neither.
- **Bottom.** The slide's bottom padding is 12 dp (or the safe area); the avatar stands on the caption's bottom edge instead of being centred on it, so the bar's left end is a plain rounded corner now. The bar is at least 72 % of the avatar tall so the avatar's right half always covers it.
- **Title.** `.kenal-heading` holds the `h2` (gradient text clipped to the letters, dark drop shadows, no pill) and a decorative rule: two gold lines fading outwards with an inline SVG ornament (lotus petals and curls) in the middle. The letters are filled transparent with `-webkit-text-fill-color`, so `color` stays a plain gold for anything that cannot draw the gradient (no extra media query: StageCssTest allows only the stage ones).
- **Narrator controls.** 60 dp round buttons on the `ui.result-hud-control` skin (same dimming as the HUD), else a navy disc with a gold ring; no band; Otomatis as gold text with a shadow. They fit right of the logo at 1280 dp with all five controls showing.
- **Jaka avatar.** The framed picture lives in `gelita-katalog/mockup/gambar-mockup/kenali/aset/ui.jaka-avatar.webp` and is installed through `CatalogImporter::MOCKUP_FILES`; `reference.03` is no longer mapped. A server that installed the old bust gets the new picture on the next import (the file changed, so the catalogue row is reinstalled). On Peta Kedu `.kedu-portrait.has-art` drops our gold ring because the picture has its own. Slot text and display size (232 dp on Kenali, 120 dp on the story bar) updated.
- **Deploy:** `php spark gelita:katalog:impor --kunci="ui.jaka-avatar"` (dry run first; add `--timpa` only if an admin uploaded a Jaka avatar by hand), then **Bangun ulang paket** if package mode is on (new fonts and plates).
- **Checks.** `RegionStoryTest::testRegionIntroLayoutHasPlatesANameplateAndAGoldTitle`, `CatalogImportTest::testJakaAvatarComesFromTheFramedPictureInTheKenaliPackage`; Chromium on a local SQLite world (grade-5 world, content, catalogue): Kenali with Mbah Kedu and with a line switched to Jaka, the last slide, all five narrator controls, without JavaScript (both plates side by side), and Peta Kedu's story bar; `tools/qa/stage-check.mjs` for overflow from the wider font.

## Background music for the scenes and account pages, smaller Kenali title, HUD flag (19 October 2026, branch `pbb/sharp-edison-tnww8e`)

Owner request after PR #171: fix the HUD flag overflow; make "Mengenal {wilayah}" smaller; gamelan / Javanese background music for every screen-effect scene and every dialogue or narration except Jaka's story on Peta Kedu (which already has the map music), matching the mood and not disturbing the voice; music also on the home, login, sign-up, and change-password pages. Interview answers: one track per scene type **and per region** (12 tracks), **synthesized now**, music **down to about 20 %** under a voice, and the mission card gets the existing **challenge** music.

- **HUD flag.** Not a game bug. My layout check of PR #171 ran on a local world whose uploaded files the test suite had deleted, so the flag picture was a broken image (natural height 0), and a broken image draws taller than its box. With the files back the flag loads and fits (69 × 69 full size, 46 × 46 compact). No code change.
- **Title.** 44 → 32 dp; the ornament (64 → 52 dp wide) and the rule's overhang shrink with it.
- **Music.** Twelve original gending in `tools/audio/music_scenes.py`, built with the existing instruments, garap, and mastering of `tools/audio/` (not a new generator). `Piece` takes an optional `steps` list so gender and gambang can walk pelog barang (2 3 5 6 7). `music.py` without names and `master.py music` without `--only` still render only the three original tracks, so their files did not change. Details, moods, and laras per track in `docs/audio.md` → *Scene and account music*.
- **Which page plays what.** New layout section `music` → `<body data-music>`, started by `game.js` on the first tap: `welcome` on `/`, `/mulai`, `/gerbang`, `/masuk`, `/daftar/1–3`, `/ganti-sandi`; `opening` on `/intro`; `ending` on `/penutup`; `dialog-{code}` and `tuntas-{code}`; `challenge` on `/misi/…`. The gate (`/gerbang`) shares the welcome track because it uses the same view as `/mulai`. Peta Kedu keeps `map`; the Kenali overlay carries `data-music="kenal-{code}"` and `game/map.js` switches to it while open and back to `map` on close, also when the overlay opens from the address.
- **Ducking and crossfade** in `core/audio.js`: every `NarrationPlayer` state change reports to `narrationChanged()`; the music fades to `DUCKED` (0.2) of its level in 0.35 s and returns in 0.9 s once no voice has played for 1.2 s, so it stays down across slide changes. Track changes crossfade (0.9 s); a track asked for again while it fades out is faded back up instead of being started a second time. Jaka's story on Peta Kedu ducks the map music too.
- **Size and package.** The new files are 96 kbps (the three originals stay 128 kbps). `AssetPackageBuilder::groupFor()` puts `welcome` and `opening` in the core group, each region's three tracks in its region group, and `ending` with the ending. `AssetChecklist::SOUNDS` lists all twelve.
- **Deploy:** the files come with git. On a server with package mode on, run `php spark gelita:media:scan` (registers the new files) and then **Bangun ulang paket**.
- **Checks.** `BackgroundMusicTest` (every view's track, file, and checklist row; layout, game.js, map.js, ducking constant; generator), view assertions in `RegionStoryTest` and `MissionCardTest`, `AssetPackageBuilderTest` groups; `tools/qa/music-check.mjs` 15 checks including crossfade and ducking; Chromium on a local SQLite world with the real Howler and MP3s: the right track sounds on every reachable page, Kenali switches and returns, no page errors. Ducking under a real recorded voice could not be heard here (the local world has no recordings); the owner should listen on a device.


## Challenge screen redesign: header, action bar, blanks, choices, caption, stars (20 October 2026, branch `pbb/jolly-heisenberg-5r78py`)

Owner request with pictures: the challenge header like the example but without the "Wonosobo · Tantangan 1 dari 4 · Susun Gambar" line, the supplied gold-and-navy ring as the number avatar; Isi Rumpang blanks as a dotted line with the chosen word above it, no box and no number; the check button of every challenge redesigned like the example (gold button between gold rules); every star uses the new star; the puzzle's "Tentang gambar ini" like the example panel; the word choices on the navy plate. Interview answers: the action bar is **one centred bar across the arena** in all three batch engines; an empty star is **the same picture, grey and faded**; Petunjuk and Keluar stay **right of the stats capsule**; the navy plate also goes on the **Benar/Salah choices**.

- **Header.** `.challenge-head` keeps its coordinates (32, 64, 1216 × 56) and becomes a navy pill with a thin gold rim and a blue glow on its lower edge. The number sits on `public/assets/ui/challenge-badge.webp` (the owner's ring, cropped and scaled to 192 px, 11 KB), 64 dp so it rises a little above and below the plate. The title uses the display font. The eyebrow line is gone from the screen but kept as `.visually-hidden` text, so screen readers still hear region, position, and kind. Timer, ✓/✗ (its own small pill), and Kesempatan share one capsule `.challenge-stats` split by thin gold lines; the element ids that `challenge.js` uses did not change. The carved arena frame now starts below the header (16, 124, 1248 × 588) so the two do not draw double rims.
- **Action bar.** `.arena-actions` is 32, 648, 1216 × 56 in Susun Gambar, Isi Rumpang, and Benar atau Salah. The button keeps the main-button look of every game page (`ui.result-button-primary` skin, else the gold gradient), now with the label in the display font and a chevron instead of the old leading icon, a blue glow under it, and a gold rule on each side that ends in a four-point ornament (inline SVG in the CSS). Benar atau Salah's reading panel is 64 dp shorter (h 512) to make room. `#btn-next` (Lanjut) uses the same bar.
- **Blanks.** `button.blank` has no border and no number tab (`.blank-wrap`/`sup.blank-no` removed); its dotted line is a repeating radial-gradient in `--dots`. The word sits on the line; an empty blank holds a no-break space (`rumpang.js` placeholder, was "?") so the line stays on the text baseline. Active = soft gold tint; checked = word and dots green/red plus ✓/✗. The blank's number stays in its `aria-label`.
- **Navy plate.** `_frame.php` passes Kenali's `kenal-plate-navy.webp` as `--plate-navy` (absolute URL via `game_asset_url()`, same reason as on Kenali). Words (44 dp) and Benar/Salah choices (48 dp) use `border-image` with the end slice 115 px, so the ornamented ends keep their shape. A picked choice glows gold and keeps its ● mark.
- **Caption.** `.puzzle-caption` is a navy panel with a thin gold rim, a gold corner at the top right, an "i" medallion and gold title over a gold rule ending in a diamond, white text, and faint mountains along the bottom. All ornaments are CSS backgrounds (inline SVG), so a long text scrolls under them without moving them. The text stays the panel's only `<p>`, which `puzzle.js` reads for the result modal.
- **Stars.** The new star is the catalogue's `ui.result-star` art (same file, byte for byte). It is also shipped as `public/assets/ui/star.webp` so every star shows it before the import; `star_src()` prefers the slot, so a picture uploaded in the panel still wins. `stars_html()` (region map, mission card, profile, result history) and the result screen, Peta Kedu panel, profile and reflection tiles, the reflection rating, the "Terbaik" badge, and a completed Peta Kedu pin without pin art all use it. An empty star is the same picture with `.is-off` (greyscale, 45 % opacity). The admin panel keeps its line icon (`stars_html(..., false)` in `AdminTable`).
- **Deploy:** the files come with git. Rebuild the asset package if package mode is on (new pictures in `assets/ui`).
- **Checks.** `ChallengeFrameTest` (header, action bar, blanks, navy plate, caption), `GelitaHelperTest::testStarsHtml`, `ResultScreenTest`; Chromium on a local MariaDB world, see the commit.
- **Status medallions** (follow-up the same day). The owner's three round pictures replace the status marks, not the check button: navy = open, gold arc = started but not finished, green with a check = done, on the lantern's shard dots (and legend), the region-map pins, and the Peta Kedu "done" badge; a locked challenge keeps its lock (owner's confirmation). "Started" is new: `ChallengeAttemptModel::startedNodeIds()` (attempts still `in_progress`) feeds a separate `started` flag in `GameProgress::nodeOverview()`/`lanternMap()`, so the `status` values and every rule built on them stay as they were; the shard and pin labels read `Game.shardStarted` ("Sedang dikerjakan"). The gold medallion carries no number (its arc fills the middle); the navy one keeps the pin's number on top. **The pictures only reached the session as chat images, not files**, so `status_badge_src()` uses `public/assets/ui/status-{open,progress,done}.webp` when present and returns null otherwise, keeping the old dots, discs, and check (a started shard shows a half dot meanwhile). Adding the three files (WebP, transparent, about 256 px) is all that is left; checked in Chromium with drawn stand-ins that were not committed. Test: `StatusBadgeTest`.
- **Slot text** (follow-up the same day, owner's request). `ImageSlots` for `ui.result-star` in **Gambar & mockup** no longer says "result screen only, empty stars as an outline": label *Bintang permainan*, every star in the game, empty stars the same picture grey and faded, the screens that show it, and the shipped star as the fallback. The asset catalogue needs no change: the slot key and file are the same.

## Stage check after the challenge redesign; difficulty badge overflow (20 October 2026, branch `pbb/jolly-heisenberg-5r78py`)

- **Run.** `tools/qa/stage-check.mjs` at all six sizes on a local MariaDB world, after PR #178 (the script now presses Lanjut). Challenge, first-check modal, all 12 finished screens, result, region done, Pustaka, ending, and reflection: no failures.
- **Region-map pin shift (about 1 %).** Not a layout bug: the run changes progress between sizes, so a pin's label ("Terbuka" → "Sedang dikerjakan") and width change. With the same student at every size the region maps pass.
- **Difficulty badge** (`partials/region-difficulty`, owner request to fix). The light sweep `.region-difficulty::after` was 200 % of the badge's height (`inset: -50% …`), leaving hidden overflow inside the `overflow: hidden` badge (scrollable from script, flagged by the check at 1920 and 2560 px). It is now exactly as tall as the badge; the skew keeps the height, so the sweep looks the same. Reproduced without the change, passes with it.
- **Status medallions installed** (same day, owner's files). The owner's three pictures are now `public/assets/ui/status-open.webp` (navy), `status-progress.webp` (gold arc), and `status-done.webp` (green ✓): transparent, cropped to the medallion, centred on a square, 256 × 256 WebP (24–28 KB each). Checked in Chromium on the lantern, a region map with open (numbered), started, and done pins, and Peta Kedu with a finished region.

## Story screens: no picture before the tap card, lighter caption and controls (20 October 2026, branch `pbb/determined-tesla-qpw54u`)

Owner request: on the intro and every page with slides and "Ketuk untuk mulai", no slide picture before the tap card has appeared; `.cine-caption` without border-top, background, and box shadow; `.narrator-controls` without background and box shadow, the round buttons on `ui.result-hud-control`, Lanjut on `ui.result-button-primary`, size down to 2.5rem; no eyebrow on the intro, only the dots; the same for every page with the intro's style. Interview answers: the screen stays **dark until the tap** (the picture fades in with slide 1, not behind the card); **all** control buttons are 2.5rem (Lanjut's height too); the **region dialogue** gets the HUD skin on its round buttons as well.

- **Dark until the tap.** One CSS rule: `.stage:has(.narrator:not(.is-started) [data-narrator-tap]) > .scene` is transparent from the first paint, so it covers the intro, ending, region dialogue, and region done (every page that renders `components/narrator-tap`) and nothing else; Peta Kedu and Kenali have no tap card. `narrator.js` already sets `.is-started` on the tap, and the scene fades in over 0.6 s. Without JavaScript `noscript.css` shows the scene; if the module never runs, a failsafe animation shows it after 10 s, the same pattern as the curtain. Characters, caption, and HUD stay as they were (behind the card's dark veil).
- **Caption and controls (intro, ending).** `.cine-caption` has no band any more; the slide's own bottom gradient (`.cine-slide::after`) and the scene veil keep the text readable. The controls have no band; ◀ ⏸/▶ ↻ are 40 dp discs on `--skin-hud` (else the navy disc with a gold ring, as on Kenali), Lanjut is 40 dp tall on `--skin-primary` (else the gold gradient), Otomatis 40 dp with a text shadow. The 48 dp hit areas from `.icon-btn::before` with the 8 dp gap still do not overlap. The controls row is re-centred on the title row (y 541), and `--cine-controls-w` shrinks to 11.5rem / 28rem (with audio).
- **Skin source.** The layout now puts `--skin-hud` (`ui.result-hud-control`) on `#stage` next to `--skin-primary`, so every story page can use it without its own attribute. Kenali keeps its own `--skin-hud` on the overlay.
- **Region dialogue.** Its round buttons and Lanjut now follow the shared `.cine` rules (HUD skin, main-button skin); only Otomatis stays brown on the parchment. Position and size were already 40 dp.
- **Eyebrow.** `partials/cine-slides` drops the "Bagian n dari N" pill; the text stays as the dots' `aria-label`. The dots alone sit level with the HUD controls' centre (y 55).
- **Checks.** `CinematicViewTest` (dots without eyebrow, stage skin, caption and controls CSS, dark-until-tap rule and its fallbacks); full suite. Chromium on a rendered intro: scene opacity 0 under the tap card, 1 after the tap; controls 40 dp at y 521.


## Region map side column, three medallion states, opening story link (20 October 2026, branch `sky/fervent-curie-k4riwe`)

Owner request: larger pin medallions so the number sits neatly; the challenge list numbers wear the same medallion; no "Sedang dikerjakan" anywhere (only done, open, locked); no eyebrow in the region-map head; each card shows its title once; no Peta Kedu button at the bottom; Pustaka {wilayah} at the bottom of the side column, fixed, and the head and progress fixed while the list scrolls; the progress bar a little narrower, clear of the scrollbar; the owner's four pictures as the card backgrounds; on Peta Kedu no `kedu-panel-meta` line but "Lihat cerita pembuka" with an attractive icon; the shard HUD had the pictures the wrong way round. Interview answers: in the HUD a locked shard is the **navy medallion with a small lock**; the region-map pins and the list use the **same three meanings, locked with a lock** instead of its number.

- **Medallion mapping.** `status_badge_src()` now maps completed → `status-done.webp` (green ✓), open → `status-progress.webp` (gold arc), locked → `status-open.webp` (navy); the views draw the lock (`icon('lock')`, class `shard-lock` in the lantern, `badge-lock` on the maps). The file names stay as installed, so the asset package and catalogue need nothing new. This replaces the same day's "navy = open, gold arc = started, locked keeps its lock".
- **No "started" state.** `Game.shardStarted`, the `started` flag in `GameProgress::nodeOverview()`/`lanternMap()`, `ChallengeAttemptModel::startedNodeIds()`, and `.shard.is-started` are removed. Statuses and every rule built on them were never affected by it.
- **Region-map pins.** The pin box is 64 dp (was 48) and the medallion picture 80 dp (8 dp larger on each side, with `max-width: none` so the global image cap does not shift it off the number); the number is a 26 dp serif with a dark shadow in the gold arc's middle. A locked pin's picture is slightly desaturated. The pin label shows Terbuka or Terkunci.
- **Side column.** `.map-level .map-side` is a four-row grid (head, progress, list, foot); the aside lost `stage-scroll`, which moved to `ol.node-list`, so only the list scrolls and its scrollbar no longer touches the progress bar (which also got 16 dp right padding). The eyebrow "Wilayah n · difficulty" is gone from the head. Cards keep one visible title (`.eyebrow`, the challenge title; the `h2` repeated it) and `Game.challengeOf` as visually hidden text. Coordinates unchanged.
- **Pustaka and the nav bar.** The page no longer renders the `nav` section: Peta Kedu is in the HUD menu (same as the region dialogue since 19 October 2026). Pustaka {wilayah} is a full-width ghost button in `.map-side-foot` (48 dp) with the same lock attributes; `map.js` already listened on `document`.
- **Card pictures.** The owner's four 1672 × 941 pictures are `public/assets/ui/node-card-{cari,boleh,rumpang,puzzle}.webp` (640 × 360, 44–77 KB), mapped by what each shows (magnifier, ✓/✗ book, cloze card, puzzle board). `img.node-card-art` covers the card under a navy gradient that is darkest on the left, where the medallion and title sit; cards are at least 120 dp tall. A missing file means the plain card.
- **Peta Kedu panel.** `kedu-panel-meta` is gone. Unlocked regions show **Lihat cerita pembuka** (`Game.watchRegionOpening`, en "Watch the opening story") under the name: a gold play medallion with a soft glow pulse (off under reduced motion) and an underlined label, 48 dp tall, linking to `/dialog/{code}`, which already allows a replay of an unlocked region. Locked regions have no link.
- **Fixed rows.** The list has a fixed height, so `.map-level .node-list` uses `grid-auto-rows: max-content`; without it the cards (which clip their picture) shrank to their minimum and cut off Lihat hasil.
- **Deploy:** the files come with git. Rebuild the asset package if package mode is on (new pictures in `assets/ui`, changed CSS).
- **Checks.** `StatusBadgeTest` (rewritten for three states), `LibraryLockTest::testRegionMapCardsWearTheirMedallionAndPicture` and the Pustaka foot, `RegionStoryTest` (opening link, no meta line); full suite 803 tests. `tools/qa/stage-check.mjs --only=peta,wilayah-temanggung` passes at all six sizes and the rotate gate, on a local SQLite world (one done, one open, two locked challenges in Temanggung). Chromium: pin and list numbers centred on the gold arc, lantern legend with three states, head and Pustaka fixed while the list scrolls.

## Peta Kedu without frames, Jaka's nameplate, badge halo (20 October 2026, branch `sky/admiring-mendel-am6ed0`)

Owner request: on Peta Kedu, remove the background and frame of Jaka's narration bar and move his name onto his avatar like the Kenali screen (the owner's Mbah Kedu example); remove the region panel's background with its gold frame; give the badge icons on every page a black halo that follows their shape, so they stay clear on bright backgrounds.

- **Story bar.** `.kedu-story` lost its border, navy gradient, box shadow, and the `--skin-panel` frame; its text has a dark shadow (the same as the region panel) instead. The speaker is `.kedu-speaker`: the round avatar, 144 dp (the bar's height), with the name on `.kenal-nameplate` over its lower ring, reusing the Kenali rule and its navy plate (`--kenal-plate-navy` on the aside; `cinematic.css` now matches `:is(.kenal, .kedu-story)`). The `cite` keeps the title and the dots; only the narrator, who has no avatar, keeps his name there. The plain fallback (no `map_intro` lines) uses the same speaker and a narration without its name.
- **Region panel.** `.kedu-panel` lost the border, gradient, inset rings, and the `--skin-panel` frame. The region's loading picture stays (not part of the frame): it now fills the whole panel box and also fades at its top and right edges, since no frame hides them any more. Text shadows are a little stronger.
- **Badge halo.** One token, `--badge-halo` in `tokens.css`: four unblurred 2 dp black drop shadows (one per side) make a solid rim that follows the picture's alpha, then a 6 dp soft black glow. A blur-only shadow was tried first and was too faint on bright art. It is on every badge picture: the lantern's shard medallions, the region-map pins (also when locked), the challenge list medallions, the challenge number ring, and the Peta Kedu done badge. The region pins of Peta Kedu (`.kedu-medal`, pin art) are not badges and are unchanged.
- **Deploy:** the files come with git. Rebuild the asset package if package mode is on (changed CSS).
- **Checks.** `RegionStoryTest::testMapIsTheThreeRegionDiorama` (speaker and nameplate, no name in the `cite`, no backdrop or frame rules, the halo on each badge rule); full suite. `tools/qa/stage-check.mjs --only=peta` passes at all six sizes and the rotate gate on a local SQLite world with the catalogue installed. Chromium at 1280 × 720: Peta Kedu, a region map, and a challenge, compared before and after.

## Serpihan Cahaya plate in the HUD (20 October 2026, branch `sky/youthful-cannon-wbzf9t`)

Owner request: replace the HUD shard pill with the owner's new design (a carved gold plate with a crystal on the left, "Serpihan Cahaya" and the count over a glowing amber bar). The owner's pictures had the bar already filled; the bar must be filled by the page from the real count, with an effect like the picture rather than one flat colour.

- **Pictures.** From the owner's text-free picture (916 × 316 RGB on white): the white background cut out with a soft rim (one connected background area, the colour un-mixed from the white), the groove's lit part repainted as the dark track of its unlit end, and the warm halo the fill threw on the plate taken off (a blurred difference against the unlit end's rows, subtracted only around the old fill). Result `public/assets/ui/hud-shards-frame.webp` (909 × 300, alpha, 27 KB). The lit groove itself became the fill texture `hud-shards-fill.webp` (428 × 19, 2 KB). Like Kenali's plates, they ship with the game and are not image slots or catalogue entries.
- **Markup.** `components/lantern.php` keeps its structure (`.lantern-count b`, `.lantern-bar i`, the `<details>` breakdown), so `hud.js` `updateLantern()` still moves the bar live. New: `.lantern-gem`, the crystal's glow layer. `components/hud-menu.php` passes both pictures as `--shards-frame`/`--shards-fill` with `game_asset_url()` (a relative `url()` in the CSS would break the minified mirror and the asset package). The lantern icon (`.flame`, `ui.lantern`) stays in the markup but is hidden on the plate.
- **Layout.** The frame is the summary's background (`aspect-ratio: 909 / 300`), so hover and the open breakdown brighten the whole plate. Label, count, groove, and crystal sit at fractions of the frame, so a size only sets `--plate-w`: full HUD 372 × 123 dp with the same centre as the old pill (x 733); compact HUD 230 × 76 dp, centred, about 20 dp below the 56 dp strip (owner's choice, to keep the text readable); the region dialogue keeps its 360 dp under the logo. Label and count use the display font, the count's right edge meets the groove's.
- **Fill.** Four layers: a light sweep (4.5 s), a top highlight and darker lower edge, the owner's texture sized to the whole groove (a short fill shows its start rather than a squeezed copy), and a warm gradient under it; an outer amber glow and a hot leading edge (`::after`). At 0 % no glow or edge shows. Width still animates over 0.6 s.
- **Crystal.** Owner's choice: it glows by stage (completed regions): veiled at 0, 35 % and 70 % glow at 1–2, full glow with a slow pulse at 3. Sweep and pulse stop under reduced motion.
- **Deploy:** the files come with git. Rebuild the asset package if package mode is on (new pictures in `assets/ui`, changed CSS).
- **Checks.** `ResultScreenTest::testShardPlateDrawsTheFillOverTheOwnersFrame` (pictures shipped, frame and texture rules, crystal replaces the icon, stage-3 pulse, compact size) and the plate URLs and `.lantern-gem` in the compact HUD; `RegionStoryTest` (Peta Kedu HUD); full suite 804 tests. Chromium at 1280 × 720 (2×) on pages rendered by the view tests: Peta Kedu at 0/12, 10/12, 12/12 (stages 0, 2, 3) and the compact HUD at 6/12. The fill stays inside the groove from 0 % to 100 %, there is no white rim on the frame, and the label and count do not touch. In the compact HUD only the crystal's lower point and the curl reach about 16 dp below the strip.

## Serpihan Cahaya plate, third version (20 October 2026, branch `sky/youthful-cannon-wbzf9t`)

Owner request: a second picture to make the plate better proportioned, then (before that was merged) a third one instead, with the note that the text need not be large and should stay elegant. The second picture (about 4 : 1, on black) was installed and then replaced; only the third ships.

- **Picture.** The owner's 2000 × 667 RGB picture with a light checkerboard painted where transparency should be. The plate (1999 × 411, about 4.9 : 1) has an empty groove in its middle, so nothing had to be repainted. Background = light, nearly grey pixels connected to the border; a 3 px soft rim un-mixed against the local checker shade. Scaled to 1400 × 288 for `public/assets/ui/hud-shards-frame.webp` (54 KB). The fill texture `hud-shards-fill.webp` from the first version stays.
- **Text.** Label and count sit in the band above the groove, their ends level with the groove's: Cinzel Decorative 700, 2.7 % / 3 % of the plate width with 0.08 em / 0.04 em letter spacing, warm ivory `#EBD3A0` with a dark shadow and a faint warm glow; never below 11 / 12 dp (`max()`), so the compact plate stays readable.
- **Sizes.** Full HUD 480 × 99 dp at (493, 10), same centre as before, inside the 120 dp band; compact HUD 300 × 62 dp, centred, 6 dp below the 56 dp strip; the region dialogue keeps 360 dp wide (74 dp tall).
- **Fractions.** Groove 22.31 % / 48.66 %, 70.34 % × 13.87 % (frame px 446–1852 × 200–256), corners barely rounded like the groove's; crystal glow box 7 % / 15.8 %, 7.2 % × 57 %.
- **Checks.** `ResultScreenTest` (compact size), full suite. Chromium at 1280 × 720 (2×) on pages rendered by the view tests: Peta Kedu at 0/12, 10/12, 12/12 and the compact HUD at 6/12; the fill stays inside the groove, no light rim around the frame, label and count apart.
- **Deploy:** the files come with git. Rebuild the asset package if package mode is on (changed picture and CSS).

## Versioned URLs for the UI pictures (20 October 2026, branch `sky/youthful-cannon-wbzf9t`)

Owner report: after the shard plate was replaced, a normal refresh still showed the old look; a fresh incognito window was fine.

- **Cause.** CSS and JS already carry `?v=` from `asset_url_versioned()` (assetVersion + a hash of the file's mtime and size), so the new stylesheet loaded. The owner's pictures in `public/assets/ui/` went through `game_asset_url()` without `?v=`, and `hud-shards-frame.webp` was replaced three times under the same URL. A browser that had it cached kept the old picture on a normal refresh (heuristic freshness when the server sends no `Cache-Control`; a normal reload does not revalidate subresources), so the new 4.9 : 1 layout showed over an old frame. Not the service worker (it serves navigations network-first and package paths only) and not package mode (that would be stale in incognito too).
- **Fix.** Every `assets/ui` picture now uses `asset_url_versioned('ui/…')`: the package copy while a package is active, else `assets/ui/x.webp?v=…`. `asset_min_path()` maps no pictures, so the source file is served. nginx already marks `?v=` URLs immutable, which is safe because the hash changes whenever the file is replaced. Call sites: the HUD shard plate, Kenali's plates, the challenge frame (navy plate, number ring), Peta Kedu (navy plate, Kenali icon), the region map's card pictures, the login/sign-up/change-password backgrounds and Jaka, and `status_badge_src()`/`star_src()`. Fonts keep `game_asset_url()`, because the preload must match the stylesheet's unversioned font URL.
- **For browsers that already cached the old picture:** they pick up the new URL on the next page load once this is deployed; until then one hard refresh (Ctrl + Shift + R) clears it.
- **Checks.** `AssetFileVersionTest::testUiPicturesCarryTheFileVersion` (the URL carries the file hash, status medallion included, and no view calls `game_asset_url('ui/…')`); the view tests that compared picture URLs now compare `asset_url_versioned()`; full suite 805 tests. `CLAUDE.md` has the rule under the pitfalls.
- **Deploy:** the files come with git. Rebuild the asset package if package mode is on (changed views).

## Island map, Marcellus, region-map cards (20 October 2026, branch `pbb/affectionate-brahmagupta-t80b4y`)

Owner request with five pictures (island map, new logo, Pustaka book, green scarf frame, green button plate). Interview answers: "Lihat hasil" on the green plate and "Terkunci" on the navy plate ("Mulai" stays gold); the taller HUD only on the region map, the other pages keep their layout with roomier HUD parts; Marcellus for all game text ("lebih cocok daripada Cinzel dan Jakarta Sans"); on Peta Kedu the shard plate moves left and "Peta Kedu" goes to the middle.

- **Font.** Marcellus 400 (Fontsource 5.3.0, latin subset, OFL) replaces Cinzel Decorative and, in the game, Plus Jakarta Sans for every text (`--font-display`, `--font-body`); the Cinzel files are removed, the preload is Marcellus. Every `text-transform: uppercase` in the game CSS is gone, and the wide letter spacing that went with it is now .02em, so names, labels, buttons, the speaker, the region name, and the difficulty badge keep the language strings' own (proper Indonesian) casing. The Georgia overrides that existed only because Cinzel had no lowercase now use the display font. **Deviation:** Marcellus draws 1 like I and 0 like O ("O/I2"), which children misread, so the digits 0–9 come from Plus Jakarta Sans through a `unicode-range` face `Gelita Digits` listed first in both font tokens. Marcellus has one weight; bold is synthesized. The panel is unchanged. Capitals that are part of a picture (the start button `ui.btn-start`) stay as drawn.
- **Island map.** The owner's picture (1672 × 941) ships in the catalogue as `mockup/gambar-mockup/peta-kedu/revisi-05/aset/diorama.kedu-pulau.webp` and replaces the revisi-04 file in `CatalogImporter::MOCKUP_FILES` (slot `map.kedu-diorama`, required size now 1600 × 900). Regions follow the rivers: Wonosobo west (lake, plateau, harbour), Temanggung on the north-east plateau with the volcanoes, Magelang south-east with Borobudur. Masks: the island's land (sea and white cloud removed, largest component, holes filled) intersected with polygons traced along the rivers, feathered (5 px blur) and stored as 836 × 471 alpha WebP `public/assets/ui/kedu-region-{code}.webp`. `Config\Gelita::$mapDiorama` now holds `mask` and `pin`; `clip` and `route` are gone. A locked region is a darkened, nearly grey copy of the picture shown through its mask (`img.kedu-shade`, `brightness(.3) saturate(.15)`); open regions stay bright. No trails, frame, border, or backdrop; the picture box (0, 68, 896 × 504) fades out on all four edges into the page. Pins: Temanggung 69/26, Magelang 66/68, Wonosobo 27/42 (percent of the picture).
- **Peta Kedu HUD.** New section `hudLayout` = `title-centre` in `components/hud-menu`: no logo, shard plate at the top left (432 dp), "Peta Kedu" centred at 38 dp, and a dark gradient behind the HUD that fades out over 168 dp instead of a hard band.
- **Progress bar.** `.kedu-bar` is a 24 dp gold rim around a dark inset groove; the fill is a lit gold rod (top highlight, lower shade, glow, hot leading edge, slow light sweep), with notches at each quarter (one per challenge) and turquoise gem caps at both ends. The sweep stops under reduced motion.
- **Logo.** The owner's new logo (2000 × 750, alpha) scaled to 1600 × 600 replaces the catalogue file `assets/logo/ui.logo-hero.webp` (same 8:3 shape; its `data.json` description updated, the catalogue build not re-run). The game reads it from the slot `ui.logo-hero` as before (start page, HUD, staff login, Kenali).
- **Pustaka book.** The owner's book, trimmed and squared to 192 × 192 (`public/assets/ui/icon-pustaka.webp`), via `library_icon()` replaces the `book` line icon on the Pustaka buttons: Peta Kedu, the region map foot (an open Pustaka; a locked one keeps the lock), the Pustaka shelf's Baca buttons, the result screen's Pustaka card and Baca Pustaka, and the locked Pustaka page's link. Not changed: the reading-passage book icons inside challenges (not Pustaka).
- **Region map.** Section `hudStyle` = `tall` (`.is-compact.is-tall`): an 84 dp HUD with a 68 dp logo, the region name at 28 dp, 72 dp controls on a 60 dp disc, and a 360 dp shard plate; the map and the side column move down 28 dp (both start at y 100; the side column ends at 684). Cards `.has-frame`: the owner's scarf frame (`node-card-frame.webp`, 1000 × 427, its window 3.5–94.5 % × 11–86.5 %) with the engine picture in its window in sepia (`sepia(.95) saturate(.6) contrast(.92)`) under a parchment wash with a burnt rim, so it reads like a drawing in an old manuscript; the medallion on the scarf (13 %, middle), the title in dark ink at the clear top right, stars under it, the action at the bottom right. "Lihat hasil" sits on the owner's green plate (`plate-green.webp`, white background removed), "Terkunci" on the Kenali navy plate, "Mulai" stays the gold button; all 40 dp tall. Pustaka {wilayah} sits on the wide gold plate (`kenal-plate-gold.webp`), 56 dp.
- **Other pages' HUD.** The compact HUD keeps its 56 dp strip and 56 dp hit areas; the control discs (42 dp), flag, and logo (42 dp) are a little smaller and the shard plate is 256 × 53 dp, so every part has room above and below (it used to reach 6 dp below the strip).
- **Deploy.** Files come with git. On a server that already ran the catalogue import: `php spark gelita:katalog:impor --kunci="map.kedu-diorama,ui.logo-hero"` (dry run first; both replace earlier catalogue pictures without `--timpa`, an admin upload needs `--timpa`). Until it runs, Peta Kedu shows the old three-island picture under the new masks. Then **Bangun ulang paket** if package mode is on (new pictures, fonts, CSS).
- **Checks.** `StageCssTest::testGameTextIsMarcellusWithoutForcedCapitals`, `RegionStoryTest::testMapIsTheIslandWithShadedLockedRegions` (replaces the three-region test), `LibraryLockTest` (scarf frame, plates, tall HUD, Pustaka plate and book), `CatalogImportTest` (new mockup file, 1600 × 900), `ResultScreenTest` (compact plate); full suite 806 tests. `tools/qa/stage-check.mjs` on a local SQLite world with the catalogue installed (Temanggung in progress, the other two regions locked): `awal, mulai, masuk, daftar, gerbang, intro, peta, kenali, dialog-temanggung, wilayah-temanggung, misi-*, tantangan-temanggung-2, pustaka, profil, ganti-sandi` pass at all six sizes and the rotate gate. Chromium at 1280 × 720: Peta Kedu, the region map, the mission card, a challenge, the Pustaka shelf, the result page, the start page.

## Brighter logo, lettered buttons, start-page menu (20 October 2026, branch `pbb/nice-carson-7yeoiz`)

Owner request with four pictures: the logo from the previous change was too dark; a new GELITA logo, Mulai/Start plates, and lettered plates for Masuk/Log in, Daftar/Sign up, Berikutnya/Next; Profil and Keluar in the start page's menu.

- **Logo.** The owner's picture (2000 × 750) had no real transparency: the "transparent" background was a painted checkerboard. It was cut out (near-white, unsaturated pixels reachable from the edge or in pockets of 40 px or more, then grown by up to 20 px into light greys; the lantern's glass is protected, since its glow is near-white too; 2 px erosion and a 1.2 px soft edge), scaled to 1600 × 600 and replaces the catalogue file `assets/logo/ui.logo-hero.webp` (same 8:3 slot). Checked on a dark background: no white fringe or checker pockets left.
- **Mulai / Start.** The owner's plates are about 2 : 1, not 3 : 1 like the old capsule. **Deviation:** instead of padding them into the 720 × 240 slot (which would have shown them at 240 × 120 dp), the slot `ui.btn-start` / `ui.btn-start.en` is now 720 × 360 (shown 360 × 180 dp) and the welcome box moved from 460, 456, 360 × 120 to 460, 448, 360 × 180 (clear of the logo box, which ends at y 440, and of the install box at x 896). Each plate is fitted into the 2 : 1 canvas, centred, so both frames show at about the same height. The catalogue files `assets/antarmuka/ui.btn-start{,.en}.webp` are replaced; `data.json` sizes and descriptions updated (catalogue build not re-run).
- **Lettered form buttons.** Masuk (login page), Berikutnya (register steps 1 and 2), and Daftar (register step 3) are the owner's plates, shipped with the game as `public/assets/ui/btn-{login,next,register}[-en].webp` (192 px high, the two languages of a pair on one canvas size). `picture_button($name, $label)` (content_helper) renders `<button class="btn-art" type="submit">` with only the picture; the word is in `alt`, so screen readers still read "Masuk" / "Log in"; the English file follows the request locale. Without the file the old gold text button comes back. `.btn-art` (game.css): 64 dp high, glow on hover/press/focus, dimmed while the form is sending (the scripts already disable the button). The login button is centred instead of full width. "Di halaman awal" was read as the account pages reached from the start page: `/mulai` has no buttons of its own (two cards), and its cards stay as they are.
- **Start-page menu.** `HomeController::index` passes the signed-in student (safe shape) to the HUD, so the menu shows the profile row (to `/profil`) and Keluar; the Kembali ke awal row is left out on `/` itself (`$homePage`). Before login the menu still holds only fullscreen: Profil and Keluar need an account.
- **Deploy:** files come with git. On a server that already ran the catalogue import: `php spark gelita:katalog:impor --kunci="ui.logo-hero,ui.btn-start*"` (dry run first; an admin upload needs `--timpa`). Until then the old logo and the old Mulai capsule show (the capsule then sits in the taller box, smaller but intact). Then **Bangun ulang paket** if package mode is on.
- **Checks.** `StartGateTest::testWelcomeMenuHasProfileAndLogoutWhenSignedIn`, `StartGateTest::testLoginButtonIsTheLetteredPicture`, `RegisterWizardTest::testStepButtonsAreTheLetteredPictures`; slot sizes in `StartGateTest` and `CatalogImportTest` now 720 × 360.

## Account pages and gate: logo in the HUD centre, the owner's gate picture and emblems (20 October 2026, branch `sky/vigilant-cerf-lj5cx0`)

Owner request with five pictures (compass emblem, open book emblem, map scroll emblem, Jaka waving, the arch background): the GELITA logo in the middle of the HUD on Mulai, Masuk, Daftar, and Gerbang with Kembali in the top-right corner; on Gerbang the logo instead of the shard plate; the compass crest and the gate card icons replaced with the emblems; Gerbang on the new background with Jaka as one tall still picture whose feet are darkened into the background; the gate cards without corner ornaments or an extra background.

- **HUD.** New section `hudLayout` = `logo-centre` in `components/hud-menu` (`.is-logo-centre`): the logo alone in the middle at 48 dp over a soft top shade, no region name, no shard plate. Section `hudBack` now puts Back after the three controls, in the top-right corner, on every page that sets it (also `/siapkan` and `/periksa-paket`, so Back sits in one place across the account flow). Controls and Back share the row `.hud-end`; the controls keep their own box, so the language and menu sheets stay under their buttons. All controls keep a 48 dp hit area at 640 × 360.
- **Crest.** `partials/ornate-frame` shows the owner's compass emblem `public/assets/ui/ornate-crest.webp` (274 × 320) instead of the drawn SVG crest, 76 dp tall on the panel's top edge (a little more above the edge than below, so it clears the sign-up eyebrow). Applies to every ornate panel (Mulai, Masuk, Daftar, Ganti sandi, Siapkan, Periksa paket).
- **Emblems.** The three emblems came as RGB on white. Background = near-white pixels connected to the border; the edge was pulled in 1 px, softened, and un-mixed from white; trimmed to the subject. `gate-icon-intro.webp` (book, 220 × 256) and `gate-icon-map.webp` (map scroll, 219 × 256) replace the `book`/`map` line icons on the two gate cards (80 dp tall, `.start-emblem`). Mulai keeps its `user-plus`/`login` icons (not part of the request).
- **Gerbang.** Background `public/assets/ui/bg-gate.webp` (the owner's 1920 × 1080 picture as delivered), shipped with the game like the account pictures, so `bg.welcome` no longer applies to this page. A light veil only on the right (`body.is-gate-art`). Jaka: the owner's picture trimmed to 605 × 1400 (`jaka-gate.webp`), 640 dp tall standing on the path right of the arch (476, 80), feet on the stage's bottom edge. The darkening is baked into the file rather than a CSS gradient, because a gradient box would also darken the bright path around him: from 52 % of his height down his colours shift toward the path's dark teal (up to 95 %), and the last 16 % fades out, so the feet sink into the ground. The cards sit in `.gate-panel` (776, 80, 472 × 608) on the picture's dark side without panel fill, frame, or corner ornaments; only the compass emblem above the title and the divider remain. The cards keep a thin gold outline (they are the buttons) and a faint fill; the primary card keeps its gold glow. Kembali → `/` moved from the bottom nav bar to the HUD corner.
- **Checks.** `StartGateTest::testGateShowsTheOwnersPictureAndEmblems`, `StartGateTest::testOrnateCrestIsTheOwnersEmblem`, the Mulai and Daftar HUD tests updated (logo centre, Back after the controls); `ChallengeFrameTest::testViewsRequestOnlyKnownPoses` counts the `character` component calls left in the views (one fewer now, so its floor is "at least 3"). Full suite green. Chromium at 1280 × 720 on pages rendered from the grade-5 test world: Mulai, Masuk, Daftar 1, Gerbang, the language and menu popovers on the new row; at 640 × 360 every Gerbang control is at least 48 dp.
- **Deploy:** the files come with git. **Bangun ulang paket** if package mode is on (new pictures in `assets/ui`, changed CSS and views).

## Cinematic entrances and the long Mulai plate (20 October 2026, branch `sky/vigilant-cerf-lj5cx0`)

Owner request: cinematic effects on the pages ("use Theatre.js on some pages if needed") and a new pair of Mulai/Start plates (one picture, MULAI above START, on a painted checkerboard). Follows the six ideas offered for the start page after PR #196.

- **Mulai/Start plates.** Background = light, unsaturated pixels connected to the border; the rim pulled in 1 px, softened, and un-mixed from the light grey; split at the empty band between the plates; stray checker specks dropped by trimming to rows and columns the plate really fills. Each plate (about 5.8 : 1) sits centred on a 1200 × 220 canvas, replacing the catalogue files `assets/antarmuka/ui.btn-start{,.en}.webp`. Slot size `ui.btn-start*` is now 1200 × 220 (display 480 × 88 dp); the welcome button box is 400, 472, 480 × 112.
- **Theatre.js: not used (deviation from the suggestion, which left it to judgement).** Its runtime would be a new download on the very first page, for timelines that are a handful of cues; its editor (Studio) is AGPL and only helps while authoring. The browser's Web Animations API plays the same cue-sheet timelines with no library. `game/cine.js` holds three sheets: `welcome`, `gate`, `account` (Mulai, Masuk, Daftar 1–3).
- **What plays.** Welcome: letterbox bars, background pushing in from dark and blur, a flare, the logo coming into focus, tagline, Mulai, HUD (about 3 s); then the three.js screen effects of the story scenes with `glow` (light rays from above, sparks, thin mist, bloom), parallax, a 40 s background drift, and on the logo a float, a breathing glow, and a light sweep masked by the logo itself. Gate: picture push-in, Jaka walking in, crest turning in, cards sliding in one after the other (about 2 s); then sparks, mist, and parallax. Account pages: picture push-in, Jaka rising, panel and crest (about 1.5 s); no WebGL on the form pages.
- **Rules kept.** Cues only touch `opacity`, `filter`, and the individual `translate`/`scale`/`rotate` properties with `fill: backwards`, so stage positions, hover transforms, and the keyboard shift are untouched afterwards. The full film plays once per page per browser session; later visits only fade in (450 ms). A tap or key ends it. No flash of the final page before the entrance (`html.cine-ok` from `core/viewport.js`, parts held by CSS) and no trap if the module never runs (CSS shows them after 4 s). Nothing is held, played, or downloaded under reduced motion; the WebGL part follows `Config\Gelita::$screenFx` and drops out without WebGL 2.
- **Checks.** `CinematicEntranceTest` (sheets per view, `fill: backwards`, no `transform`, reduced-motion boot guard, 4 s fallback, loops behind the motion query, the logo mask); `CatalogImportTest` and `StartGateTest` for the 1200 × 220 slot; full suite 815 tests. Chromium at 1280 × 720 on pages rendered from the grade-5 test world, timelines scrubbed to fixed moments: welcome (0.2–3.2 s, then the rays and sheen), gate (0.15–2.6 s), Masuk (0.1–2 s); reload plays the short fade; with reduced motion no animation runs and the page shows at once.
- **Deploy:** on a server that already ran the catalogue import: `php spark gelita:katalog:impor --kunci="ui.btn-start*" --dry-run`, then without `--dry-run` (an admin upload in those slots needs `--timpa`). Then **Bangun ulang paket** if package mode is on (new script, CSS).

## Analysis: is the start-page animation smooth? (20 October 2026, branch `sky/vigilant-cerf-lj5cx0`)

Owner question after PR #198, with a close-up of the logo: is the animation too fast so it looks rough, is another effect on the page causing it, is there a bug, what can be improved; and the light on the welcome page should match the sun in the background picture.

**How it was measured.** Pages rendered from the grade-5 test world, in Chromium, frame times recorded with `requestAnimationFrame` for 9 s from load. This container has no GPU (software rendering and software WebGL), which exaggerates every cost, so variants were compared against each other by switching one suspect off at a time, not read as tablet numbers. Network: 3 Mbit/s with 150 ms latency for the load-order check.

**Findings.**
1. *Too fast? Partly.* The ease-out used (`cubic-bezier(.16, 1, .3, 1)`) does about 80 % of a move in the first quarter of its time, so each cue read as a snap followed by a long crawl, and the welcome film packed eight cues into under 3 s.
2. *Expensive animations (main cause).* The entrance animated `filter` (blur and brightness) on the full-screen background for 2.8 s and a blur on the 800 dp logo. A filter animation re-renders the layer every frame; it cannot run on the compositor. With the filters removed the entrance's worst frame went from 717 to about 280 ms and its p95 from 350 to about 150 ms (software rendering).
3. *Looping effects repainting.* The light sweep on the logo animated `background-position` inside a masked, blended layer: a full repaint of the logo on every frame for the whole visit. The parallax moved the logo (with its drop shadow, mask, and blend) every frame without its own layer. With the loops and the WebGL layer off, the after-entrance frame rate went from a p50 of 150 ms to 17 ms (software).
4. *A jolt right after the entrance.* three.js (2.1 MB) was downloaded, parsed, and its shaders compiled only once the entrance ended (long tasks of 1–5 s here, a few hundred ms on a tablet), then the sparks and mist appeared at full strength in one frame and the `glow` rays flared to full within 0.9 s. The effects also started at full resolution (2× on a retina tablet) with bloom; the watchdog lowers quality only after 4 s of slow frames.
5. *Bug: picture pops in.* On the slow connection the entrance started at 3.3 s while the background picture had not finished loading, so it appeared halfway through its push-in.
6. *Bug: double entrance on slow connections.* The CSS fallback shows the parts after 4 s if the module has not started. A connection only slightly slower than the test above starts the module after that, which hid the visible page again and replayed the film.
7. *Light not matched to the picture.* The rays always came from above the top edge (the story-scene default), with a halo mid-screen; the server's welcome picture has its sun low on the left.
8. Not a bug: the top of the logo cut off in the owner's close-up. The owner confirmed the close-up was cropped (20 October 2026); at 1280 × 720 the logo's top sits at y 104 dp, and the entrance zoom no longer exceeds 1.08.

**Changes.**
- Cues: no `filter` anywhere; the picture rises out of the dark stage with opacity and a 1.1 → 1 push-in; the logo 1.08 → 1 with the flare; gentle ease-out `cubic-bezier(.25, .46, .45, .94)`; welcome about 4 s, gate about 2.7 s, account pages about 2 s.
- Start: waits for the background and the logo to decode (at most 1.5 s); if the page is older than 3.5 s by then, no entrance (fixes 5 and 6).
- WebGL: the modules download during the entrance (after the pictures, only with WebGL 2), the layer starts one quality step down and fades in over 2.5 s; the welcome effect `sun` eases its rays in over 4 s to a soft level.
- Light: `findLight()` reads the welcome background (whatever the admin uploaded) and finds its light: the brightest warm patch of its upper 70 %, averaged over 5 × 5 samples of a 96 × 54 copy. The rays and halo come from there and spread all round, those falling over the land below faint. Checked on three pictures: the sun in the gate arch (22 %, 40 %), the horizon glow of the catalogue welcome picture (48 %, 29 %), and the lantern of the login picture. With no clear light the old source stays. The story scenes are unchanged (both modes compile and run).
- CSS: the logo sweep is a slim band moved by `translate` inside `.logo-sheen` (masked by the logo); parallax layers get `will-change: translate`; the gate's card panel no longer moves with the parallax (text stays crisp and still).

**Further suggestions, not done.** Check the frame rate once on a class tablet (the container cannot measure a GPU). If it still stutters there: turn the background drift off on the welcome page, or set `Config\Gelita::$screenFx` to false, which keeps every entrance and drops only the WebGL layer. A `modulepreload` for `cine.js` would shorten the dark moment before the entrance, but the modules are imported without the `?v=` version, so the preload URL would have to match exactly, including in package mode and the minified mirror; left out.

**Checks.** `CinematicEntranceTest` (no filter cues, the decode wait and late skip, sweep by translate, `will-change`, fade-in, the light option and the story default); full suite 816 tests. Chromium: the welcome entrance scrubbed at 0.3–4.2 s, the welcome page after the entrance on the catalogue picture and on a picture with a sun, `createScreenFx` in both modes without errors.

## Back plate, wide sign-up card, login without panel (20 October 2026, branch `claude/redesain-auth-pages-1f8h8r`)

Owner request with one picture (the navy plate): Kembali at the top left of Mulai, Masuk, Daftar, and Gerbang on that picture; Daftar redesigned without Jaka, the card nearly full width and centred, no compass emblem, and the fields split into two or three parts so a step fits the card without scrolling; Masuk without the compass emblem, without the panel's ornaments, background, and extra border, and more refined.

- **Branch name.** The session was assigned `claude/redesain-auth-pages-1f8h8r`, against the CLAUDE.md rule of branches without a `claude/` prefix. **Deviation:** kept, since the session's push target was fixed.
- **Back plate.** The picture is the Kenali navy plate already shipped (`public/assets/ui/kenal-plate-navy.webp`, 459 × 131, pixel-for-pixel the same art), so no new file. With `hudLayout` = `logo-centre` (Mulai, Masuk, Daftar 1–3, Gerbang) `components/hud-menu` renders `a.hud-back-plate` in the free top-left corner (12, 4; 48 dp tall) with the picture passed as `--plate-navy`; `border-image` keeps both ornamented ends (115 of 131 px, 42 dp at this height) and stretches only the middle, like Kembali on Kenali. Text and arrow in gold, hover/press brighten, focus ring. Pages with Back but without `logo-centre` (`/siapkan`, `/periksa-paket`) keep the plain Back after the controls (`.hud-end`).
- **Daftar.** No Jaka. One ornate card at 40, 64, 1200 × 632, centred under the HUD; `partials/ornate-frame` takes `crest => false`, so the card keeps its four gold corners but no compass crest. The head is one row: eyebrow and title on the left (title 32 dp), the stepper on the right. The fields sit in `.register-grid`: step 1 name, age, and class | gender and avatar picker; step 2 country, province, district | NPSN or school name; step 3 three columns, tips | name, password, and meter | the password rules and the repeat field, with the consent under the last two. For step 3 `components/password-field` got a `split` option that wraps its parts in `.pw-main` (password, meter, level, server error) and `.pw-aside` (rules, repeat field); the DOM and tab order stay name, password, repeat, consent. `/ganti-sandi` renders the field as before. In this layout the live status lines (strength, match, name check) and the rules use the caption size. At 1280 × 720, in Indonesian and English, every step fits without scrolling, also with the strength and mismatch messages showing; `.register-fields` stays a `.stage-scroll`, so a long server error summary still scrolls inside the card instead of overflowing.
- **Masuk.** No panel: `.login-panel` (720, 72, 512 × 624) holds the form straight on the picture's dark side, without fill, border, inset line, corner ornaments, or crest (the precedent of Gerbang). A 40 dp glowing gold title over a short gold rule, the lead text; fields as rounded translucent pills (56 dp) with a gold icon at the start (`user`, `key`; `.input-lead`), a soft gold glow on focus, the show/hide button inside the password field's right end; then the MASUK plate and the foot links. Jaka stays.
- **Checks.** `StartGateTest` (Back plate on Mulai, Masuk, Gerbang; `testBackPlateUsesTheNavyPlatePicture`, also that `/periksa-paket` keeps the plain Back; `testLoginStandsOnThePictureWithBackInTheHud`; the crest option), `RegisterWizardTest` (`testStepsShowOneWideCardWithColumns`, `testAccountStepSplitsThePasswordFieldInReadingOrder`, Back plate before the controls), `TouchPlayTest` (split only on `/daftar/3`); full suite 813 tests. `tools/qa/stage-check.mjs --only=mulai,masuk,daftar` passes at all six sizes and the rotate gate. Chromium at 1280 × 720 on a local SQLite world with the catalogue installed: Mulai, Masuk (also a failed login without JavaScript), Daftar 1–3 in ID and EN with avatars, step 3 with both status messages.
- **Merged with the cinematic entrances (PR #198).** The `account` sheet of `game/cine.js` keeps its Jaka cue (it now matches only on Masuk) and animates `:is(.ornate-panel, .login-panel)`, so the panel-less login form still rises in; `.login-panel` joins the parts held hidden until the entrance (`HELD` and `cinematic.css`). The sign-up card enters as `.ornate-panel` as before; its crest cue finds nothing there.
- **Deploy:** files come with git. **Bangun ulang paket** if package mode is on (changed CSS and views).

## Gate, Peta Kedu, and region-map refinements (21 October 2026, branch `claude/ui-refinements-gateway-map-challenge-yf9q2i`)

Owner request with two pictures (a dark bronze bar, an ornate navy plate): on Gerbang Jaka's legs too black (keep only a thin shade) and the welcome panel too close to the right edge (move it and Jaka left); on Peta Kedu the narrator controls a little higher, clear of the narration; on the region map no number on the pin and card medallions, no extra background or border around the map picture and its edges blending into the page, a black rim on the card stars, no icon on Terkunci, one notice (the modal) for a locked Pustaka, brighter sepia for done challenges, darker for the open one with a light title, black-and-white and dark for locked ones with a light title; the head and progress redesigned with the two pictures.

- **Branch name.** The session was assigned `claude/ui-refinements-gateway-map-challenge-yf9q2i`, against the CLAUDE.md rule of branches without a `claude/` prefix. **Deviation:** kept, since the session's push target was fixed.
- **Jaka's legs (Gerbang).** The undarkened original is not in the repository or the catalogue, so the darkening was undone in the file itself: the earlier entry describes it as a blend toward the path's teal from 52 % of the height down. Measured on the file, the blend is a smoothstep from 52 % to the bottom reaching 95 %, toward about rgb(8, 27, 30) (the colour of the faded bottom rows); inverting it with that curve (capped at 90 % to keep compression noise down) gives an even skin tone down to the ankles and the sandals back. Then 35 % of the old shift was put back, so the legs keep a thin shade and still sink into the path; the alpha fade of the last 16 % is unchanged. Same size (605 × 1400), WebP quality 88.
- **Gerbang positions.** Jaka 420, 80 and the cards 720, 80 (both 56 dp left; the panel now ends 88 dp from the right edge). Jaka still stands on the path right of the arch.
- **Peta Kedu.** `.narrator-controls` at `top: -.5rem` (was `.75rem`): the 40 dp buttons sit at y 548–588, above the speaker line (589), no longer beside it. The story box itself is unchanged.
- **Region map.** `$medal` shows no number on open (the lock on locked stays; the fallback without pictures keeps its marks). Terkunci is text only. `.map-frame`/`.map-canvas` lose border, fill, shadow, and radius on this page; the picture gets the same two-gradient mask as the Peta Kedu island. Card pictures per status: done `sepia(.9) … brightness(1.18)` under a lighter wash, open `brightness(.6)` under a dark brown wash, locked `grayscale(1) … brightness(.42)` under a dark wash; the title is cream on open and locked. The card stars get a 1.5 dp black rim (`--star-rim`, four drop-shadows like `--badge-halo` but thinner, as the stars are 20 dp).
- **Head and progress.** The plate picture is trimmed to its art (1907 × 430) and saved at 1200 × 271 as `public/assets/ui/map-head-plate.webp`; the region name sits in gold on it at the column width (about 70 dp tall). The focus stays as a short italic line under the plate (it does not fit inside without stretching the ornaments). The bar picture is trimmed (1671 × 96) and saved at 1200 × 69 as `public/assets/ui/map-progress-track.webp`; `.progress` takes its aspect ratio and a padding in % of the width that puts the content box on the dark channel (4.3–95.7 % × 19–78 %), so the existing `<i style="width: n%">` fills only the channel, in the glowing gold of the Peta Kedu bar. `--steps` (the number of challenges) draws one notch between challenges. The count `Game.nodesDoneOf` is centred under the bar. Both pictures pass to the page as `--head-plate` and `--progress-track`; without the files the old head and bar show.
- **Locked Pustaka.** The link carries both `aria-disabled` and `data-library-locked`, so the generic locked-link handler showed a toast and the Pustaka handler a modal (and the lock sound twice). The generic handler now skips `[data-library-locked]`. Checked in Chromium: before 1 toast + 1 modal, after 0 + 1.
- **Checks.** `LibraryLockTest` (`testRegionMapHeadAndProgressUseTheOwnersPictures`, `testLockedLibraryShowsOnlyTheModal`, no number, text-only Terkunci), `StatusBadgeTest`; full suite 820 tests. Chromium on a local SQLite world with the catalogue art: Gerbang, Peta Kedu (story live), region map with done, open, and locked cards.
- **Deploy:** files come with git. **Bangun ulang paket** if package mode is on (new pictures, CSS, JS, views).

## Region map: modal for locked pins, no HUD region name, readable done titles (21 October 2026, later; branch `claude/ui-refinements-gateway-map-challenge-yf9q2i`)

Owner request after PR #203: on the region page use the modal, not the `toast is-warn`; remove the `hud-identity-region` text; the title of a done card is hard to read on its background, is there a solution?

- **Branch.** The same assigned branch, restarted from `main` after PR #203 and PR #204 were merged.
- **Locked pin.** The generic locked-link handler in `game/map.js` opens `showModal()` (lock icon, `Game.nodeLockedTitle` "Tantangan masih terkunci" / "This challenge is still locked", the existing `Game.nodeLocked` text, button `Game.libraryUnderstood` "Mengerti") when the screen is `map-level`; the strings ride on the pin as `data-locked-*`, as on the locked Pustaka link. Peta Kedu keeps its toast for a locked region (the request named the region page only).
- **HUD.** New section `hudRegion` = `off` in `components/hud-menu` leaves the region name out; only the region map sets it, since its head plate shows the name. The result screen, region dialogue, and other pages keep it.
- **Done card title.** The cause is the bright sepia picture: its lines run behind the dark letters. Chosen fix: a soft parchment veil (cream, 90 % at the centre, fading out) behind the title and stars only, as the top layer of the done card's wash; the rest of the picture stays visible. Considered and not taken: a stronger cream text halo (blurry at 17 dp) and a solid label behind the title (hides more of the picture and changes the card's look).
- **Checks.** `LibraryLockTest::testRegionMapLockedPinOpensAModalAndTheHudHasNoRegionName`; full suite 821 tests. Chromium on the local world: a locked pin shows 1 modal and 0 toasts (was 1 toast), no region name in the HUD, the done card's title on a clean patch.
- **Deploy:** files come with git. **Bangun ulang paket** if package mode is on.

