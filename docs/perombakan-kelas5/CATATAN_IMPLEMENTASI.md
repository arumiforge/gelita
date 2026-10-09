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
- **Bug found and fixed: new students logged out right after registering.** `game/register.js` checks the username with a 500 ms debounce (`GET /api/auth/username-available`). When that check fired after the step 3 submit, its request carried the pre-registration session cookie; the server had just regenerated the session for the new account, so the late response set the old cookie back and the next screen sent the student to `/masuk`. On `main` the old page usually unloads before the debounce fires (0 of 16 runs locally), but a slow POST on school Wi-Fi would hit it; the view transitions keep the old page alive slightly longer, which made it 4 of 12. Fix: the check stands down once `preventDoubleSubmit()` marks the form as submitting (12 of 12 runs clean). Server-side hardening (the username endpoint needs no session) is left as an open question, since `app/Controllers` was outside this session's scope.
- **Local QA setup.** The MySQL migrations do not run on SQLite, so for the browser checks I built a SQLite file from the test schema (`GradeFiveWorld`, unlock mode `free`) and served it with `php spark serve`, file sessions, and a git-ignored `.env`. Nothing touched a shared or production server.
