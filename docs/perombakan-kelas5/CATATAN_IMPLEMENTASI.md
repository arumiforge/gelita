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
- **Size rules.** `$assetSizes` and `$assetDisplaySizes` use `map.region.*` and `reward.badge.*`; `library.image` is removed (library sizes stay recommended in `ImageSlots`, so `ImageSlots` no longer lists recommended sizes for the region map and badge, which are now required). `AssetChecklist::FALLBACK_SIZES` and the prefix match in `MediaController::displaySize()` are gone; every lookup uses exact key or `fnmatch`. `MediaStore::store()` refuses an SVG on a slot with a required size (it has no pixel size to check), with the same rule in `admin/media-upload.js`. `registerFile()` still takes the generated SVG sample images (`gelita:mockups`), which are placeholders by design. The slot page's upload hint said "Ukuran lain … ditolak", stale since PR #81; it now says larger images with the same shape are shrunk.
- **Existing uploads** of a wrong size are not touched; Kelengkapan keeps listing them as "Ukuran tidak sesuai".
- **Tests.** New `PasswordFlashTest` (password change lands on `/gerbang` with the message, no duplicate must-change flash for students and staff, the intro redirect keeps the message across two simulated requests); all four fail on the old controllers. `ImageSlotsTest::testEverySizePatternMatchesARealSlot` (each size pattern matches a seeded key or a catalogue pattern; fails on the old config with `map.region`). `ImageFitterTest`: region map upload is size-checked, SVG refused on sized slots and accepted on free ones. `MediaSlotPagesTest` expects the required size on the region map page.

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

## Display update — Phase F: touch-friendly play (11 October 2026)

Branch `tampilan-f-sentuh`. Implements Phase F of `docs/rencana-perbaikan-tampilan.md` (F1 CSS basics, F2 48 dp hit areas, F3 gestures and screens), run in a parallel session next to Phases A → B → C. The CSS and the gesture JS were written by two subagents with separate file ownership; the coordinating session did `game.js`, `core/keyboard.js`, the form attributes, `stage-check.mjs`, tests, and docs.

- **F1.** `body.game` has no tap highlight; controls and the stage use `touch-action: manipulation`; play surfaces are not selectable and show no long-press callout (reading text stays selectable); scene, map, and character images are not draggable (CSS plus `draggable=false` and a `dragstart` guard in `game.js`, because `map-kedu.php` and `components/character.php` belong to the other session). `:active` press feedback everywhere listed; `game.js` adds the passive `touchstart` listener iOS needs for it. Hovers that transform or highlight a border moved inside `@media (hover: hover)` in place (small diff); colour-only hovers stay. Explicit reduced-motion rules for `start-glow`, `.kenal.is-open`, and the hover/selection/press transforms (`cine-rise` already had one).
- **Puzzle press feedback brightens instead of scaling.** A scale on `.piece` would also scale the inline translate of the dragged art, so the art would lag the finger.
- **F2.** One shared centred `::before` of `max(100%, var(--tap))` on `.btn` and `.icon-btn` covers `.btn-sm`, story Lanjut/Kembali, HUD and narrator icons; own `::before` for `.narrator-auto`, `.chip-btn`, `.lang-opt`, `.word`, `.blank`, `.hud-brand` (38 dp), and the Benar atau Salah passage chip `.card-passage` (30 dp; both found by the new QA check). Slide dots get an area as wide as dot plus gap (neighbours never overlap). Checkboxes and radios already sit in 48 dp `.check` labels. `.media-credit` summary went from 44 to 48 dp. Exception: the map story's stacked narrator buttons keep their 36 dp height, since taller areas would cover the next button. `stage-check.mjs` now reports any interactive element in `#stage` whose box and pseudo-elements are all under 48 dp, with 1 px tolerance for rem rounding at 1366 × 657. Exempt: links inside running text, and the two documented exceptions (slide dots, map story narrator buttons), both of which repeat full-size Lanjut/Kembali. **Owner call:** if the dots must be 48 dp wide, their spacing has to grow to 48 dp (9 dots = 432 dp in the story caption, up to 15 in the dialogue box), which changes their look.
- **F3.1 drag.** New `core/drag.js` (`initDrag`, pointer capture, 8 px threshold, click swallowed after a real drag) replaces HTML5 drag-and-drop in `engines/puzzle.js` for mouse and touch; the drop calls the same `swap()`, so events are identical to tap-swap. The art moves by `translate` divided by the element's own on-screen scale (1 today: the stage scales through rem, not a transform). `Game.puzzleHintJigsaw` now mentions dragging too.
- **F3.2 zoom.** `#hunt-zoom` (`Game.huntZoom` "Perbesar adegan" / `Game.huntZoomClose` "Kecilkan adegan") toggles `.hunt.is-zoomed`: scene 0, 0, 1280 × 720 at z 40, clue ribbon on top (taps pass through it), button at z 42, below modals (50) and toasts (60). `overlay_opened` `{ overlay: 'hunt_zoom', level }` on open only. Esc closes it unless a modal, dialog, or lantern is open; the zoom stays open during wrong-tap and skipped-clue modals and closes itself when the attempt completes. `hunt_zoom` added to the overlay lists in `docs/06`, `docs/07`, and the `event_type` description in `ExportLabels` (the export dictionary had no overlay list before; it now names all six values).
- **F3.3 swipe.** `onSwipe()` in `core/drag.js` (touch/pen, 50 px, horizontal must dominate; ignored in scrolling panels, on controls, and while a modal is open). `game/slides.js` turns a swipe into a click on the current slide's own Lanjut/Kembali link, so events and narration follow the buttons exactly; it never follows the final link that leaves the screen. Wired through `narrator.js` (story, dialogue, region-done, Kenali, map story) and `library.js` (Pustaka).
- **F3.4 keyboard.** New `core/keyboard.js`, loaded only when `#stage` has a form field: `.stage-frame` gets `translateY(-n px)` just large enough to show the focused field above the keyboard (from `visualViewport`), never pushing its top out of view, and returns on blur, keyboard close, and `pageshow`. `autocorrect="off"` on usernames and school fields; `enterkeyhint` next/done on login, sign-up, and current-password fields. The new-password fields live in `components/password-field.php`, which was not in this phase's file list, so they have no `enterkeyhint`.
- **F3.5.** `maximum-scale=1` is appended to the viewport meta on iOS only (iPadOS detected by `Macintosh` + touch points).
- **`stage-check.mjs` speed fix.** `check()` read the attempt id with `page.getAttribute()`, which waits 30 s for a selector that only challenge screens have; every other screen took 30 s. It now uses `$eval`, so a full six-size run takes about 15 minutes instead of hours.
- **QA run.** Local SQLite copy of the game world (the `GradeFiveWorld` test trait written to a file, plus `ContentSeeder` for story and dialogue; no Pustaka pages, so Pustaka reading pages were checked only in the harness), served by `php -S` in development mode. `stage-check.mjs` at all six sizes plus the portrait rotate gate: 282 screen checks, no layout, overflow, position, or JS errors; the only failures were the hit areas fixed or exempted above, and a rerun of the affected screens at 1280 × 720, 1366 × 657, and 844 × 390 passes. Playwright with `hasTouch`/`isMobile` at 844 × 390 and 1024 × 768 on the real server: `keyboard.js` loads on `/masuk`, a finger drag swaps two puzzle pieces and leaves no drag classes, the zoom button is 48 dp (26 px and 38.4 px on screen), the zoomed scene fills the stage, an object tap while zoomed opens its result modal above the scene, the button closes the zoom, swipe left/right moves the region dialogue forward and back, no page errors; an iPad user agent gets `maximum-scale=1`, Android does not.
- **Tests.** New `TouchPlayTest` (touch basics, no transforming hover outside the hover query, reduced-motion rules, hit areas, drag wiring and no HTML5 drag, zoom button and event, swipe/keyboard wiring, form attributes). The subagents checked drag, zoom, and swipe in Chromium harnesses with CDP touch events at 844 × 390 and 1024 × 768 and with a mouse (75 checks), and every hit area with `elementFromPoint`.
