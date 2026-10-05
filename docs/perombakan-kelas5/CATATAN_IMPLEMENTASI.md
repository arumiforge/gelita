# Catatan implementasi perombakan kelas 5

Keputusan dan penyimpangan dari ketiga dokumen perombakan, beserta alasannya dan hal yang perlu diketahui sesi berikutnya. Satu bagian per dokumen; bagian Dok 2 dan Dok 3 ditambahkan sesi yang mengerjakannya.

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
9. Gambar mockup memakai kait `App\Libraries\MockupGenerator::generateMissing()` (Dok 2 §10). Selama kelas itu belum ada, ringkasan menulis "belum tersedia".
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
