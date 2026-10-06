# Catatan implementasi perombakan kelas 5

Keputusan dan penyimpangan dari ketiga dokumen perombakan, beserta alasannya dan hal yang perlu diketahui sesi berikutnya. Satu bagian per dokumen; bagian Dok 2 dan Dok 3 ditambahkan sesi yang mengerjakannya.

## Status (6 Oktober 2026)

| Dokumen | Status |
|---|---|
| Dok 1 | Selesai, di `main` |
| Dok 2 | Sebagian, di `main`: §2, §3–§4, §5.2 (server), §7, §9, §10 (mockup), narasi petunjuk per node `cari`. §11 di branch `dok2-mockup` (457 uji hijau), lihat bagian "Dok 2 — naskah cerita dan rekaman" |
| Dok 3 | Belum dimulai |

Sisa Dok 2:

1. **§5.3 klien + §12 teks UI** — `challenge.js` belum memakai `tries_left`/`completed`/`redirect`/`clue_closed` dari server; `FINISH_OFFER_AFTER` dan tombol "Selesaikan dengan jawaban ini" masih ada; penanda 3 bulatan, modal kesempatan, animasi gambar utuh Susun Gambar belum ada. Kunci `Js.php` baru (`triesLeft`, `triesLast`, `triesExhausted*`, `clueFailed*`, `clueTriesLeft`) belum ada; `finishAnyway`, `engine_pilihan`, dan kunci lama §12 belum dihapus; `cardN` masih "Kartu {0}"; label event `Admin.php` §12.3 belum persis.
2. ~~**§10 mockup**~~ — selesai, di `main` (PR #36).
3. ~~**§11 naskah cerita**~~ — selesai di branch `dok2-mockup` (PR ke `main`). Di server tetap perlu `gelita:story:update`, `gelita:narration:import`, lalu persetujuan narasi draft (§11.2 butir 3, Dok 3 §13).
4. Bagian "Dok 2" catatan ini dan uji manual §13.2.

### Perawatan repo

- 6 Oktober 2026: sejarah `main` ditulis ulang (penulis dan committer semua commit = Arumi Studios, baris `Co-Authored-By`/`Claude-Session` dibuang; isi berkas dan pesan commit tidak berubah). Semua branch lain dihapus. Hash commit lama yang disebut di dokumen lain tidak berlaku lagi.
- Branch lama `happy-gates` yang dihapus tanpa digabung berisi satu perbaikan yang **belum ada** di `main`: tombol "Kenali" di peta yang diketuk sebelum `game/map.js` siap (±0,8 detik setelah dimuat, terlihat di 844×390) hanya mengubah hash `#kenal-{code}`, sedangkan `html.js` menyembunyikan overlay `:target`, sehingga overlay tidak terbuka. Usulan perbaikannya: saat modul siap, hash `#kenal-{code}[-n]` yang sudah ada membuka overlay di slide n lalu hash dibersihkan (`history.replaceState`), tanpa memutar narasi otomatis; narasi peta tidak dimulai bila overlay Kenali terbuka. Kerjakan ulang bila masih terjadi (setelah tata letak 16:9 Dok 3).

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

## Dok 2 — naskah cerita dan rekaman

Dikerjakan di branch `dok2-mockup` (dibuat ulang dari `main` sesudah PR #36), sesuai `02_TANTANGAN_DAN_BANK_SOAL.md` §11. Suite: **457 test hijau** (2 dilewati, sama dengan `main`).

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
