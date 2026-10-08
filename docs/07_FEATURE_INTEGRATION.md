# 07_FEATURE_INTEGRATION.md — Integrasi Fitur

## Tujuan

Menyatukan database, model, controller, view, dan JavaScript menjadi fitur aplikasi yang utuh. Dokumen ini menjelaskan alur setiap fitur GELITA dari ujung ke ujung seperti yang berjalan di kode sekarang: siswa kelas 5 SD/MI mendaftar, menjelajahi tiga wilayah Kedu (Temanggung → Magelang → Wonosobo), menyelesaikan 12 tantangan untuk mengumpulkan 12 Serpihan Cahaya, membaca Pustaka, lalu mengisi Balai Refleksi; staf menyiapkan konten, memantau data mentah, mengekspornya, dan merawat data penelitian.

Rincian tabel ada di [01_DATABASE.md](01_DATABASE.md), model dan service di [03_MODEL_ENTITY.md](03_MODEL_ENTITY.md), route di [04_CONTROLLER_ROUTE.md](04_CONTROLLER_ROUTE.md), view di [05_VIEW_UI.md](05_VIEW_UI.md), JavaScript di [06_JAVASCRIPT.md](06_JAVASCRIPT.md), dan pemasangan server di [08_DEPLOYMENT.md](08_DEPLOYMENT.md).

---

## Konteks

Pola alur yang berlaku untuk seluruh fitur:

```
User → View (CodeIgniter) → JavaScript → Route → Filter → Controller → Validation
     → Service → Model → Database → Response → View Update
```

Empat aturan yang tidak pernah dilanggar di seluruh fitur:

1. Benar/salah, kesempatan, bintang, serpihan, dan status buka/kunci ditentukan server.
2. Operasi yang menyentuh beberapa tabel dibungkus satu transaction.
3. Setiap tindakan siswa yang bermakna menulis raw event (`game_event_logs`); setiap tindakan staf yang bermakna menulis `audit_logs`.
4. Bahasa dan versi rilis (`game_sessions.locale`, `game_sessions.release_id`) tercatat pada setiap sesi, sehingga data hasil selalu dapat ditelusuri ke konten yang dimainkan.

GELITA hanya mencatat **data mentah**. Tidak ada skor 0–100, persentase, indeks kesulitan, atau peringkat yang dihitung untuk peneliti; pengolahan dilakukan dari ekspor (FITUR 14). Angka yang tampil kepada siswa (bintang, "Berhasil pada Percobaan Pertama: 2 dari 3 soal") hanya hadiah dan umpan balik di layar.

Istilah yang dipakai di seluruh dokumen:

| Istilah | Arti | Tabel/kolom |
|---|---|---|
| Wilayah | Temanggung (mudah), Magelang (sedang), Wonosobo (sulit) | `levels` (`sequence`, `difficulty`) |
| Tantangan | Satu dari 4 pos di peta wilayah: 1 Susun Gambar (`puzzle`), 2 Isi Rumpang (`rumpang`), 3 Benar atau Salah (`boleh`), 4 Cari Objek (`cari`) | `challenge_nodes` (`engine_type`) |
| Soal | Unit yang dinilai: satu gambar puzzle, satu rumpang, satu pernyataan, satu benda yang dicari | `challenge_items` |
| Variasi | Kelompok soal yang dimainkan utuh dalam satu pengerjaan (`a`, `b`, `c`) | `challenge_items.variant_group`, `challenge_attempts.variant_group` |
| Pengerjaan | Satu kali siswa memainkan satu tantangan; di kode bernama *attempt* | `challenge_attempts` (`attempt_no`) |
| Percobaan | Satu kali jawaban sebuah soal dicek (tombol **Cek jawabanku**, atau satu ketukan benda pada Cari Objek); paling banyak 3 | `item_tries.try_no`, `item_responses.try_count` |
| Kesempatan | Sisa percobaan yang masih boleh dipakai; kata yang tampil di layar siswa | dihitung dari `check_count` / `try_count` dan `max_tries` |
| Berhasil pada Percobaan Pertama | Soal yang benar pada percobaan ke-1 | `item_responses.first_pass_correct`, `challenge_attempts.first_pass_correct` |
| Benar di akhir | Soal yang benar pada percobaan terakhirnya | `item_responses.is_correct`, `challenge_attempts.final_correct` |
| Serpihan Cahaya | Satu serpihan per tantangan yang pernah selesai pada sesi itu; 12 seluruhnya | `session_progress.completed_nodes` |

## Komponen utama

| Komponen | Lokasi | Peran dalam alur |
|---|---|---|
| `SessionService` | `app/Services/SessionService.php` | daftar, masuk, lanjut/buat sesi, ganti & reset sandi, keluar, bahasa, heartbeat, sesi selesai |
| `SchoolDirectory` | `app/Services/SchoolDirectory.php` | NPSN → sekolah resmi, pencocokan nama, gabung & sahkan sekolah |
| `GameContext` | `app/Services/GameContext.php` | sesi, progres, dan siswa satu request (dimuat filter `gameSession`/`apiSession`) |
| `ChallengeService` | `app/Services/ChallengeService.php` | buka pengerjaan, pilih variasi, payload, aturan 3 kesempatan, penilaian, `item_tries`, penutupan, petunjuk |
| `ScoringService` | `app/Services/ScoringService.php` | bintang `bintang-1`, bintang total, ringkasan wilayah |
| `EventService` | `app/Services/EventService.php` | raw event server/klien dan telemetry audio |
| `ContentRepository` | `app/Services/ContentRepository.php` | konten aktif bercache (level, node, bank soal, bacaan, cerita, Pustaka) |
| `ContentImportService` | `app/Services/ContentImportService.php` | pratinjau & impor workbook bank soal dan Pustaka |
| `RawDataService`, `StaffScope` | `app/Services/` | query data mentah per dimensi untuk tabel panel, CSV, dan ekspor; hak baca staf |
| `ExportService`, `ReportService` | `app/Services/` | ekspor XLSX/CSV-ZIP dan laporan PDF satu siswa |
| `AnalyticsService` | `app/Services/AnalyticsService.php` | hitungan data mentah panel, profil siswa, linimasa event |
| `RetentionService` | `app/Services/RetentionService.php` | penghapusan data dua langkah dan retensi terjadwal |
| `SystemResetService` | `app/Services/SystemResetService.php` | reset sebelum penelitian |
| `DeviceProfile` | `app/Libraries/DeviceProfile.php` | profil perangkat untuk `device_logs` |
| `BankRules`, `DifficultyRules` | `app/Libraries/` | aturan bank soal dan aturan per tingkat |
| `ContentVerifier` | `app/Libraries/ContentVerifier.php` | verifikasi konten, termasuk `--siap-penelitian` |
| `MockupGenerator` | `app/Libraries/MockupGenerator.php` | gambar contoh 45 slot |
| `MediaStore`, `MediaIntegrity`, `MediaUsage`, `AssetChecklist`, `VideoThumbnail` | `app/Libraries/` | simpan berkas media, pindai, "Dipakai di", kelengkapan aset, poster video |
| `NarrationImporter`, `NarrationCatalog` | `app/Libraries/` | impor dan persetujuan narasi |
| `SchoolImporter` | `app/Libraries/SchoolImporter.php` | daftar sekolah resmi dari CSV |
| `ExcelWriter`, `ExportLabels`, `AnswerText` | `app/Libraries/` | penulis XLSX bertahap, judul/arti/nilai kolom dan Kamus Data, jawaban dalam kalimat |
| `ScreenViewFilter`, `ClientHintsFilter` | `app/Filters/` | event `screen_viewed`, header `Accept-CH` |
| Command `gelita:*` | `app/Commands/` | lihat [Command GELITA](#command-gelita) |

---

## FITUR 1: Pendaftaran Tiga Langkah dan Sekolah

Pendaftaran berjalan seluruhnya di server (`App\Controllers\Game\RegisterController`), sehingga tetap dapat dipakai tanpa JavaScript. Membuat kata sandi kuat sekaligus materi literasi keamanan digital: prosesnya tercatat sebagai dua angka metrik.

```text
 1. Siswa membuka / → "Mulai" → /mulai → "Saya baru" → GET /daftar
      RegisterController::index → langkah pertama yang belum lengkap
 2. /daftar/1 "Tentang kamu" (view game/register/step-1)
      display_name  wajib, 2–150 karakter
      age           wajib, bilangan bulat 8–15 (Config\Gelita::$registrationAge)
      gender        wajib, laki-laki | perempuan (Config\Gelita::$genders)
      kelas tidak diisi: ditampilkan tetap "Kelas 5 SD/MI"
    POST /daftar/1 → RegisterController::saveStep(1) → rulesFor(1)
      gagal → kembali ke /daftar/1 dengan galat per isian
      lolos → isian disimpan di session PHP `reg_wizard` → /daftar/2
 3. /daftar/2 "Sekolah dan daerah" (step-2)
      negara (Indonesia / negara lain), provinsi, kabupaten/kota (RegionDirectory)
      provinsi berdirektori (Config\Gelita::$schoolDirectoryProvinces = ['33'],
      Jawa Tengah) yang daftar resminya sudah terpasang → NPSN wajib:
        register.js: 8 angka → GET /api/schools/lookup?npsn= (debounce)
        → kartu "Benar ini sekolahmu?" (nama resmi, jenjang, kecamatan, kab/kota)
        aturan server: required|is_natural|exact_length[8]|known_npsn|allowed_school_stage
        NPSN tidak dikenal → centang "Sekolahku tidak ada di daftar" (school_manual)
        → kolom nama sekolah
      provinsi lain / negara lain → nama sekolah wajib (3–200 karakter)
    POST /daftar/2 → saveStep(2) → reg_wizard['2'] → /daftar/3
 4. /daftar/3 "Akun rahasiamu" (step-3)
      username: register.js → GET /api/auth/username-available?u= (debounce 500 ms)
                → bisa dipakai / format salah / dicadangkan / sudah dipakai
      password + password_confirm: pengukur kekuatan lokal (5 syarat dari
                Config\Gelita::$passwordPolicy, tanpa request)
      assent: centang "Aku mau ikut bermain GELITA. Aku tahu jawaban dan
              langkahku di dalam permainan dicatat untuk penelitian."
      device_json (tersembunyi): diisi core/device.js saat formulir dikirim
 5. POST /daftar/3 → RegisterController::register()
      a. PasswordPolicy::check() lebih dulu:
           kiriman pertama → session('reg_first_criteria') = jumlah syarat (0–5)
           sandi belum kuat → session('reg_weak_count') + 1
      b. isian langkah 1–2 dari reg_wizard divalidasi ULANG; galat → kembali
         ke langkah itu
      c. validasi langkah 3: valid_username, not_reserved_username,
         is_unique[participants.username], strong_password[username],
         matches[password], assent required|in_list[1]
         gagal → /daftar/3, kolom sandi KOSONG (sandi tidak pernah ikut old())
 6. SessionService::registerAndStart() — SATU TRANSACTION:
      a. PasswordPolicy diulang di service; username belum dipakai; assent wajib
      b. studi aktif (ResearchStudyModel::requireActiveStudy) dan rilis aktif
      c. SchoolDirectory::resolve() → school_id + nama snapshot (lihat di bawah)
      d. insert participants: participant_code (GLT-000001, …), username huruf
         kecil, password_hash, class_level = '5', snapshot negara/provinsi/
         kab-kota, pw_first_submit_criteria, pw_weak_submit_count,
         password_changed_at, assented_at = waktu server
      e. insert game_sessions (session_code acak, study_id, release_id, locale,
         ringkasan perangkat dari DeviceProfile, ip_hash) + session_progress nol
      f. insert device_logs (via = register)
      g. event session_started {via: 'register'}
 7. Hapus reg_wizard, reg_first_criteria, reg_weak_count dari session PHP;
    session()->regenerate(true); participant_id + game_session_id diset
 8. Redirect /intro + sambutan "Selamat datang, {nama pengguna}! Ingat nama
    pengguna dan kata sandimu, ya." (cerita pembuka wajib, FITUR 4)
```

Aturan langkah:

- Membuka `/daftar/{n}` sebelum langkah sebelumnya lengkap dialihkan ke langkah pertama yang belum lengkap (`firstIncomplete()`). Tombol **Kembali** adalah tautan `GET` ke langkah sebelumnya; isiannya tetap terisi dari session.
- Tidak ada baris database yang ditulis sebelum langkah 3 berhasil. `reg_wizard` ikut kedaluwarsa bersama session PHP.
- Kata sandi tidak pernah ditulis ke session, flash, `old()`, log, event, maupun ekspor. Yang disimpan hanya dua metrik literasi keamanan digital: `pw_first_submit_criteria` dan `pw_weak_submit_count`.
- Tombol Daftar tidak dinonaktifkan saat sandi masih lemah, agar penolakan server tercatat di `pw_weak_submit_count`.

**Sekolah (`App\Services\SchoolDirectory::resolve()`).** Satu sekolah = satu baris `schools`, sehingga semua siswa satu sekolah dapat dikelompokkan lewat `participants.school_id`:

1. NPSN dikenal → sekolah resmi (`is_verified = 1`).
2. Nama ketikan yang sudah pernah digabung admin (alias, `merged_into_id`) → sekolah tujuan gabungan.
3. Nama ketikan yang cocok jelas dengan satu sekolah resmi (`matchByName()`: kunci nama sama di kab/kota itu; sama setelah nama kecamatan/kab-kota dibuang; atau satu-satunya se-provinsi) → sekolah resmi itu.
4. Selain itu → entri belum terverifikasi (`is_verified = 0`), satu baris per (wilayah, kunci nama). Admin merapikannya di panel (FITUR 10c).

Daftar sekolah resmi dipasang `SchoolSeeder` saat instalasi dan `php spark gelita:schools:import` (dari `app/Database/Seeds/data/sekolah-jateng.csv`, lihat [`docs/sekolah/`](sekolah/README.md)). Sebelum daftar itu terpasang, siswa Jawa Tengah tetap dapat mendaftar dengan menulis nama sekolah, karena `directoryProvinces()` hanya memuat provinsi yang sudah punya sekolah resmi.

Kedua endpoint tanpa login dibatasi per hash IP: `/api/auth/username-available` 20 request/menit, `/api/schools/lookup` 60 request/menit.

---

## FITUR 2: Masuk, Melanjutkan Sesi, dan Catatan Perangkat

```text
 1. Siswa membuka / → "Mulai" → /mulai → "Saya sudah punya akun" → /masuk
    (komputer mana pun di kelas)
 2. Mengisi nama pengguna + kata sandi; device_json diisi saat dikirim
 3. POST /masuk → LoginController::login → SessionService::login()
 4. findByUsername (huruf kecil)
      tidak ada / sandi salah → registerFailedLogin (bila akun ada)
                               → pesan identik "nama pengguna atau kata sandi salah"
      terkunci                → "Terlalu banyak percobaan. Tunggu N menit."
                                (8 kali gagal → kunci 5 menit, Config\Gelita::$loginThrottle)
 5. Berhasil → clearFailedLogin (last_login_at)
 6. must_change_password = 1 → session diisi tanpa sesi permainan,
    redirect /ganti-sandi (FITUR 2a)
 7. GameSessionModel::resumable(peserta, studi aktif)
      ada   → status 'active', locale, last_active_at;
              event session_resumed {via: 'login'}; device_logs via = resume
      tidak → startNewSession(): game_sessions + session_progress baru,
              event session_started {via: 'login'}; device_logs via = login
 8. session()->regenerate(true); participant_id + game_session_id diset
 9. Redirect ke URL tujuan yang tersimpan (GameSessionFilter menyimpan path
    yang diminta sebelum login) atau /mulai → /gerbang (FITUR 4)
```

Satu studi berarti satu tahap bermain: siswa selalu melanjutkan sesi `active`/`paused` miliknya pada studi aktif. Sesi baru hanya dibuat bila belum ada sesi yang dapat dilanjutkan (mis. sesi lama ditinggalkan retensi, FITUR 17).

**Catatan perangkat (`device_logs`).** Setiap daftar dan masuk menulis satu baris hasil `App\Libraries\DeviceProfile::capture()`:

1. User-Agent + Client Hints header (`Sec-CH-UA-*`) diurai `matomo/device-detector`: jenis perangkat, merek, model, OS + versi + platform, peramban + versi + mesin, penanda bot. Header entropi tinggi diminta `App\Filters\ClientHintsFilter` (header `Accept-CH` dari `Config\Gelita::$clientHints`) pada `/`, `/mulai`, `/masuk`, dan `/daftar/*`, sehingga peramban Chromium mengirimnya pada `POST /masuk` dan `POST /daftar/3`.
2. `device_json` dari formulir (`core/device.js` → `deviceSignals()`): ukuran layar dan viewport, `pixel_ratio`, `touch_points`, orientasi, mode terpasang (PWA), bahasa, zona waktu, dan `navigator.userAgentData` untuk melengkapi hint yang tidak ada di header. JSON rusak atau lebih dari 4 KB diabaikan diam-diam; tanpa JavaScript, server memakai User-Agent dan header saja.
3. Koreksi iPad: OS Mac dengan `touch_points > 1` dicatat sebagai tablet Apple iPad/iPadOS, `corrected = ipad_desktop_ua`.
4. `ip_hash` = SHA-256 IP + `gelita.ipSalt`; IP mentah tidak pernah disimpan, dan tidak ada sidik jari peramban.

Kolom ringkas `game_sessions` (`device_type`, `os_name`, `browser_name`, `screen_size`, `is_touch`) diisi dari profil yang sama saat sesi dibuat (`DeviceProfile::sessionSummary()`).

**Keluar** (`/keluar`, `LoginController::logout`): sesi `active` menjadi `paused` + event `session_paused` {via: 'logout'}; pengerjaan `in_progress` sengaja tidak ditutup agar dapat dilanjutkan; `session()->destroy()`; kembali ke `/`. Komputer kelas yang ditinggal tanpa keluar tetap aman karena session PHP berakhir sendiri.

**Filter area game** (`App\Filters\ParticipantCheck`, dipakai tiga filter):

| Filter | Dipakai di | Memeriksa | Bila gagal |
|---|---|---|---|
| `participantAuth` | `/ganti-sandi`, `/keluar` | siswa login dan belum dihapus | → `/masuk` |
| `gameSession` | semua layar permainan | + sandi tidak wajib diganti, sesi permainan milik siswa dan tidak `abandoned`; memuat `GameContext` | → `/ganti-sandi` atau `/masuk` (path tujuan disimpan) |
| `apiSession` | `/api/*` permainan | sama dengan `gameSession` | JSON 403 `PASSWORD_CHANGE_REQUIRED` atau 401 `INVALID_SESSION` |

Sesi `completed` tetap lolos: peta, profil, Pustaka, refleksi, dan mengulang tantangan tetap dapat dibuka.

---

## FITUR 2a: Ganti Kata Sandi dan Reset oleh Guru

```text
RESET OLEH GURU
 1. Siswa lupa sandi → memberi tahu guru
 2. Guru membuka /admin/peserta/{id} (hanya siswa di sekolahnya; lainnya 404)
 3. Menekan "Reset kata sandi", mengetik RESET, kirim
    POST /admin/peserta/{id}/reset-sandi → ParticipantController::resetPassword
 4. SessionService::resetPasswordByStaff():
      sandi sementara acak 10 karakter yang lolos PasswordPolicy dan tidak
      memuat nama pengguna; password_hash baru, must_change_password = 1,
      kunci login dibuka; audit 'participant_password_reset' (tanpa sandi)
 5. Halaman hasil menampilkan sandi sementara SATU KALI
    (Cache-Control: no-store). Guru menyampaikannya langsung kepada siswa.

GANTI SANDI OLEH SISWA
 6. Siswa masuk dengan sandi sementara → /ganti-sandi
    (semua layar permainan ikut mengalihkan ke sini; API membalas 403
     PASSWORD_CHANGE_REQUIRED)
 7. Mengisi sandi saat ini + sandi baru + ulangi
 8. POST /ganti-sandi → LoginController::changePassword
      validasi: strong_password[username], differs[current_password], matches
      SessionService::changePassword(): sandi saat ini benar, sandi baru kuat
      → password_hash baru, must_change_password = 0, password_changed_at
 9. Sandi baru langsung dipakai SessionService::login() untuk melanjutkan
    atau membuat sesi permainan, lalu event password_changed:
      {via: 'self'}  bila siswa sudah punya sesi permainan (ganti sukarela dari Profil)
      {via: 'reset'} bila datang dari reset guru (dicatat pada sesi yang baru dibuka)
10. Redirect /mulai + pesan "kata sandi barumu sudah tersimpan"
```

Siswa dapat mengganti sandi sukarela dari halaman Profil (`/profil`) lewat langkah 7–10.

---

## FITUR 3: Pergantian Bahasa

```text
1. Siswa menekan ID atau EN pada HUD (form biasa, bukan AJAX)
2. POST /bahasa → HomeController::setLocale
     locale required|valid_locale; redirect_to wajib path internal (safe_internal_url)
3. session('locale') diset; bila ada sesi permainan milik siswa:
     SessionService::setLocale() → game_sessions.locale +
     event locale_changed {from, to}
4. Redirect kembali ke path yang sama
5. App\Filters\LocaleFilter (global) menetapkan bahasa request:
     ?lang= → session('locale') → game_sessions.locale → 'id'
     panel admin selalu 'id'
6. View merender ulang: lang() untuk teks antarmuka, tr()/$entity->text()
   untuk konten (kolom *_en, jatuh ke *_id bila kosong); gambar slot
   bertulisan memakai varian `{asset_key}.en` bila ada (media_key_src_locale)
```

Yang **tidak** berubah: sesi, progres, pengerjaan, jawaban, dan bintang. Di layar tantangan, `challenge.js` menandai pergantian bahasa sebagai bukan meninggalkan tantangan; halaman dimuat ulang dan pengerjaan yang sama dilanjutkan. Soal Isi Rumpang dinilai dengan kunci bahasa sesi saat itu (`answer_key_json.text_{locale}`).

## FITUR 4: Gerbang, Cerita Pembuka, Peta, dan Membuka Wilayah

```text
 1. /mulai (sudah login) → /gerbang → GateController::index
      intro_seen_at kosong → /intro (cerita pembuka wajib, tanpa "Lewati")
      selain itu           → pilihan "Lihat cerita pembuka" (/intro) atau
                             "Langsung ke peta" (/gerbang/peta)
 2. /intro → GateController::intro: slide dialogues konteks 'intro';
    tombol "Lewati" hanya bila siswa pernah menontonnya sampai selesai
 3. Slide terakhir → /intro/selesai → GateController::finishIntro
      ParticipantModel::markIntroSeen() mengisi intro_seen_at SEKALI
      event intro_completed {first: true|false} (dicatat setiap kali)
      → /peta dengan flash curtain=map (tirai "Membuka Peta Kedu")
 4. /peta → MapController::kedu (introGate(): kembali ke /intro selama
    intro_seen_at kosong, juga untuk URL yang diketik langsung)
      levelOverview(): status setiap wilayah dari session_progress dan
      pengerjaan selesai (ScoringService::levelSummary):
        locked      wilayah belum terbuka
        open        terbuka, belum ada tantangan selesai (wilayah baru)
        in_progress sebagian tantangan selesai
        completed   keempat tantangan selesai
      narasi Jaka 'map_intro' (otomatis setelah tirai peta, selain itu tombol ▶)
      tombol "Kenali {wilayah}" → overlay narasi 'region_intro' (Mbah Kedu),
        juga untuk wilayah terkunci; event overlay_opened {overlay: 'kenal', level}
        lencana "Belum didengar" sampai siswa mencapai slide terakhirnya
        (GameEventLogModel::heardRegionIntros(), per siswa lintas sesi)
 5. Klik wilayah terbuka → tirai wilayah "Menuju {wilayah}…" → tujuan `entry`
    (GameProgress::regionEntryPath):
      wilayah baru (open)        → /dialog/{code}: dialog pembuka 'level_open'
      in_progress / completed    → /wilayah/{code}
    Klik wilayah terkunci → pesan, tanpa navigasi
 6. /dialog/{code} → DialogueController::show: menandai dialog wilayah itu sudah
    tampil pada sesi PHP ini; tombol akhir → /wilayah/{code}
 7. /wilayah/{code} → MapController::level
      wilayah terkunci → /peta + pesan "selesaikan wilayah sebelumnya"
      dialogueGate(): wilayah baru yang dialognya belum tampil pada sesi login
      ini → /dialog/{code} (berlaku juga untuk /misi dan /tantangan)
      nodeOverview(): 4 pos tantangan; pos 1 selalu terbuka, pos n terbuka bila
      pos n-1 sudah selesai; pos selesai menampilkan bintang terbaiknya
      map.js mencatat level_opened {code}
```

**Mode buka wilayah** (`research_studies.unlock_mode`, diatur di FITUR 10b):

| Nilai | Wilayah | Tantangan di dalam wilayah | Pustaka |
|---|---|---|---|
| `sequential` (bawaan) | terbuka berurutan: wilayah berikutnya terbuka setelah keempat tantangan wilayah sebelumnya selesai (`session_progress.unlocked_level_sequence`) | berurutan | terbuka setelah wilayahnya tuntas |
| `free` | semua terbuka sejak awal | semua terbuka | tetap terbuka setelah wilayahnya tuntas |

Peta hanya membaca. Pembukaan wilayah berikutnya ditulis saat sebuah pengerjaan ditutup (FITUR 5a). Kunci wilayah ditegakkan lagi di `ChallengeService::openNode()` dan `POST /api/nodes/{id}/attempts` (409 `LEVEL_LOCKED`); urutan pos di dalam wilayah ditegakkan layar HTML (`ChallengeController`).

**Cerita bernarasi.** Seluruh layar cerita (cerita pembuka, narasi peta, Kenali wilayah, dialog pembuka, wilayah tuntas, penutup) diputar `game/narrator.js` dari tabel `dialogues` (88 baris, `app/Database/Seeds/data/story.php`, salinan [`docs/naskah-cerita.md`](naskah-cerita.md)). Slide berpindah dengan ketukan; bila rekaman narasinya disetujui (FITUR 13a), audio diputar dan slide maju sendiri setelah audio selesai. Setiap perpindahan slide mengirim `dialogue_advanced` {index, total, context, character} (`level_id` untuk konteks wilayah):

| `context` | Layar | `level_id` |
|---|---|---|
| `intro` | cerita pembuka `/intro` | — |
| `map_intro` | narasi Jaka di peta | — |
| `region_intro` | overlay "Kenali {wilayah}" (boleh untuk wilayah terkunci) | wilayah |
| `level_open` | dialog pembuka `/dialog/{code}` | wilayah |
| `level_done` | wilayah tuntas `/tuntas/{code}` | wilayah |
| `ending` | penutup `/penutup` | — |

"Didengar sampai habis" = ada `dialogue_advanced` konteks itu dengan `index >= total`. `/tuntas/{code}` dan `/penutup` boleh ditonton ulang; setiap tontonan menambah `dialogue_advanced`.

## FITUR 5: Kartu Misi dan Tantangan

Ini fitur inti. Setiap wilayah punya 4 tantangan dengan urutan sama; tingkatnya mengikuti `levels.difficulty` lewat `App\Libraries\DifficultyRules`:

| # | Tantangan | engine | Jenis soal | Temanggung (mudah) | Magelang (sedang) | Wonosobo (sulit) | Variasi |
|---:|---|---|---|---|---|---|---|
| 1 | Susun Gambar | `puzzle` | `puzzle_arrange` | 3 × 3 kotak | 4 × 4 kotak | 4 × 4 jigsaw | 3 (`a`–`c`), 1 gambar per variasi |
| 2 | Isi Rumpang | `rumpang` | `fill_blank_bank` | 3 rumpang, 0 pengecoh | 5 rumpang, 2 pengecoh | 7 rumpang, 2 pengecoh | 3, 1 bacaan rumpang per variasi |
| 3 | Benar atau Salah | `boleh` | `verdict_card` | 1 paragraf, 3 pernyataan | 2 paragraf, 5 pernyataan | 3 paragraf, 6 pernyataan | 3, 1 bacaan per variasi |
| 4 | Cari Objek | `cari` | `find_object` | 4 dicari + 4 pengecoh | 4 dicari + 6 pengecoh | 4 dicari + 8 pengecoh | 1 (`a`), satu adegan tetap |

### Alur engine batch (`puzzle`, `rumpang`, `boleh`)

```text
 1. Siswa di /wilayah/temanggung menekan pos 2 → /misi/temanggung/2
 2. ChallengeController::brief: kartu misi (judul, deskripsi, narasi pembuka
    bila disetujui, pengerjaan terbaik sebelumnya); pos terkunci → /wilayah/{code}
 3. "Mulai" → tirai tantangan (diputar di kartu misi, sebelum berpindah)
    → /tantangan/temanggung/2
 4. ChallengeController::play → ChallengeService::openNode()
      a. kunci wilayah diperiksa (kecuali unlock_mode = free)
      b. ada pengerjaan in_progress untuk node ini pada sesi ini → dipakai ulang
         (resume); bila semua soalnya sudah dinonaktifkan admin, pengerjaan
         lama ditinggalkan (challenge_abandoned {via: 'content_changed'}) dan
         yang baru dibuka
      c. bila tidak — SATU TRANSACTION:
           served = ChallengeAttemptModel::variantsServed(siswa, node)
                    (variasi yang pernah didapat siswa ini di SEMUA sesinya)
           picked = ChallengeItemModel::pickVariant(node, served):
                    variasi yang belum pernah didapat, dipilih random_int()
                    (bila semua sudah pernah, semua variasi jadi kandidat);
                    semua soal aktif variasi itu, urut sequence
           insert challenge_attempts: attempt_no, variant_group,
                  selected_item_ids_json, scorable_items,
                  max_tries = Config\Gelita::$maxTries (3), started_at
           ItemResponseModel::createPlaceholders() → satu baris 'pending' per soal
           event challenge_opened {attempt_no, variant_group, item_count}
      d. game_sessions.last_level_id/last_challenge_node_id dan
         session_progress.current_level_id/current_node_id diperbarui
      e. payload pemain (buildPayload) — TANPA answer_key_json:
           variant {group, title}, max_tries, tries_used (= check_count),
           elapsed_ms (jam tantangan dari started_at server), node,
           items[] (toPlayerArray + state), hints (id saja), passages,
           word_bank (rumpang), verdict_options (boleh: ["benar","salah"])
 5. View game/challenge/{engine}.php merender arena + <script id="challenge-data">;
    game/challenge.js memuat engines/{engine}.js; resume → klien mencatat
    challenge_opened {resumed: true}
 6. Siswa mengisi jawaban; setiap perubahan → event answer_selected
      Susun Gambar     {action: 'swap', from, to}  (ketuk dua keping untuk menukar)
      Isi Rumpang      {action: 'set'|'clear', value}  (ketuk rumpang, ketuk kata)
      Benar atau Salah {action: 'set', value}
    payload selalu memuat t_ms (ms sejak tantangan dibuka)
    Rumpang/pernyataan yang masih kosong → modal "Masih ada yang kosong",
    tidak dihitung percobaan
 7. "Cek jawabanku" ("Cek gambarku" pada Susun Gambar)
    → POST /api/attempts/{id}/check
      {answers: [{item_id, answer, first_input_ms, last_input_ms}], client_event_id, occurred_at}
 8. ChallengeApiController::check: pengerjaan milik sesi berjalan; check_count
    sudah = max_tries → 409 TRIES_EXHAUSTED; pengerjaan tertutup → 409
    ATTEMPT_CLOSED; setiap item_id wajib milik pengerjaan ini
 9. ChallengeService::submitCheck() — SATU TRANSACTION:
      soal yang belum benar wajib terisi, selain itu 422 INCOMPLETE
      check_count++
      untuk setiap soal yang BELUM benar (soal yang sudah benar terkunci:
      tidak dinilai ulang, tanpa percobaan baru):
        nilai dengan kunci DARI DATABASE (grade per interaction_type)
        applyAnswer(): first_answer_json + first_pass_correct pada percobaan
          pertama; final_answer_json, is_correct, status 'answered',
          try_count++, solved_on_try saat pertama kali benar,
          duration_ms = last_input_ms; jawaban berubah → change_count++ dan
          challenge_attempts.answer_change_count++
        recordTry(): satu baris item_tries {try_no, check_no, answer_json,
          is_correct, hint_opened_before, first_input_ms, last_input_ms}
      event challenge_checked {check_no, try_no, correct_count, total, exhausted}
      semua benar          → closeAttempt('solved')
      cek ke-3 masih salah → event challenge_tries_exhausted
                             {check_count, correct_count, total}
                             → closeAttempt('tries_exhausted')
10. Respons {results, correct_count, total, all_correct, check_count, try_no,
             tries_left, exhausted, detail, completed, completion_reason,
             redirect, shards}
      detail (Susun Gambar) = {pieces_correct, misplaced}: keping yang keliru
      ditandai ✗
11. challenge.js:
      belum selesai   → modal "Belum semuanya tepat" + "Kesempatanmu tinggal
                        {n} lagi" / "Tinggal satu kesempatan lagi…" + Perbaiki;
                        penanda kesempatan ●●○ diperbarui dari tries_left
      solved          → konfeti + modal sukses → Lanjut → redirect
      tries_exhausted → modal "Kesempatanmu sudah habis" (satu tombol Lanjut);
                        Susun Gambar lebih dulu menyusun keping ke posisi utuh
                        dan menampilkan keterangan gambar; Isi Rumpang dan
                        Benar atau Salah tetap bertanda merah, kunci TIDAK
                        ditampilkan
12. → /selesai/{attempt} (FITUR 5a)
```

Penilaian (`ChallengeService::grade()`): Susun Gambar — urutan keping sama persis dengan `answer_key_json.order`; Isi Rumpang — teks dibandingkan setelah `mb_strtolower(trim())` dengan `text_{locale}` (cadangan `text_id`); Benar atau Salah — pilihan harus salah satu `verdict_options` dan sama dengan kunci `verdict`; Cari Objek — benda yang diketuk adalah soal target itu sendiri. Field `correct` dari browser selalu diabaikan.

### Pengacakan

| Yang diacak | Cara | Stabil saat dimuat ulang? |
|---|---|---|
| Variasi | `random_int()` per pengerjaan baru, menghindari variasi yang pernah didapat siswa | ya (resume memakai pengerjaan yang sama) |
| Bank kata Isi Rumpang | `ChallengeService::wordBank()`: jawaban variasi + `distractor_count` pengecoh dari `config_json.variants.{grup}.distractors`, diacak `seeded_shuffle(…, 'bank\|{attempt}\|{k})` sampai lolos `bankShuffled()`: urutan jawaban di bank ≠ urutan rumpang; tanpa pengecoh (Temanggung) tidak ada kata di posisi rumpangnya sendiri | ya |
| Urutan petunjuk Cari Objek | `seeded_shuffle(clues, 'clue\|{attempt}')` | ya |
| Keping Susun Gambar | `seeded_derangement(n, 'puzzle\|{attempt}')`: tidak ada keping yang sudah di tempatnya | ya |
| Pernyataan Benar atau Salah, tombol Benar/Salah, posisi benda Cari Objek | tidak diacak | — |

### Muat ulang di tengah tantangan

Payload membawa `state` per soal: soal yang sudah benar → `{locked: true, answer, tries}` (jawaban siswa sendiri, terkunci); soal lain → `{tries}` saja. Jawaban yang salah tidak pernah dikembalikan, jadi kotaknya kosong lagi. Cari Objek membawa `clues[i].state` {found, closed, tries} dan `objects[].found`.

### Cari Objek (`cari`)

```text
1. Payload tanpa items:
     objects[] = SEMUA benda (target dan pengecoh) berbentuk identik:
                 {ref, x, y, w, media, found}; ref = HMAC(attempt, soal) dengan
                 encryption.key, urut posisi di layar
     clues[]   = hanya target yang dinilai: {item_id, text, audio, audio_id, state},
                 urutan teracak per pengerjaan
   Tidak ada penanda pengecoh, kunci, atau umpan balik pengecoh di payload.
2. Siswa mengetuk benda → POST /api/attempts/{id}/responses
     {item_id: <petunjuk aktif>, answer: {object: <ref>}, duration_ms, client_event_id}
     (duration_ms = ms sejak petunjuk itu tampil)
3. ChallengeService::submitAnswer() — SATU TRANSACTION:
     petunjuk sudah ditemukan/dilewati → 409 CLUE_CLOSED
     baris jawaban diubah hanya bila try_count masih sama; ketukan bersamaan
     yang kalah dibatalkan → 409 CLUE_CLOSED / ATTEMPT_CHANGED
     ref diterjemahkan server ke id soal (resolveObjectAnswer); item_id mentah
     dari klien tidak pernah dipakai sebagai jawaban
     benar → applyAnswer(): answered, is_correct = 1, solved_on_try, item_tries;
             event answer_submitted {try_no, correct, first_pass, change_count}
     salah → registerWrongClick(): try_count++, wrong_click_count++, item_tries;
             ketukan salah PERTAMA menutup first_pass_correct petunjuk ini = 0;
             event wrong_target_clicked {try_no, clicked_item_id, decoy, first_pass}
             ketukan salah ke-3 → petunjuk ditutup salah ("dilewati"): answered,
             is_correct = 0, final_answer_json = ketukan terakhir;
             event clue_failed {try_count}
     semua petunjuk tertutup → closeAttempt(semua benar ? 'solved' : 'tries_exhausted')
4. Respons {correct, first_pass, wrong_click, decoy, feedback, try_no, tries_left,
            clue_closed, change_count, wrong_click_count, progress, completed,
            completion_reason, redirect, shards}
     feedback = umpan balik pengecoh yang diketuk (wrong_feedback_*), baru
     dikirim SETELAH diketuk
5. engines/cari.js:
     benar        → benda ditandai, petunjuk berikutnya (narasinya diputar
                    otomatis bila suara aktif)
     salah        → benda bergetar + modal ("Bukan yang ini" + umpan balik) +
                    sisa kesempatan petunjuk itu (#clue-tries)
     clue_closed  → modal "Petunjuk ini dilewati", petunjuk diberi ✗, bendanya
                    TIDAK diungkap
     completed    → konfeti hanya untuk solved; selain itu modal "Kesempatanmu
                    sudah habis"; → redirect
   Daftar sisa petunjuk dapat dipakai melompat ke petunjuk lain (tidak
   menghitung percobaan).
```

Benda pengecoh (`config_json.decoy`, `scorable = 0`) tampil di adegan dan punya baris `item_responses`, tetapi tidak pernah diminta dijawab dan tidak menahan penutupan pengerjaan. `progress.total` tidak menghitung pengecoh.

### Penutupan dan bintang

`ChallengeService::closeAttempt()` berjalan di transaction yang sama dengan percobaan terakhir, jadi hasil tidak bergantung pada klien:

```text
a. challenge_attempts: status 'completed', completion_reason
   ('solved' | 'tries_exhausted'), completed_at, duration_ms
b. ScoringService::finalize(): first_pass_correct, final_correct,
   scorable_items, stars (dihitung dari item_responses soal yang dinilai)
c. refreshProgress(): session_progress (FITUR 5a)
d. event challenge_completed {stars, first_pass_correct, final_correct,
   scorable_items, completion_reason, attempt_no}
e. wilayah tuntas → event level_completed {level_stars}
sesudah commit: SessionService::completeIfFinished()
```

Aturan bintang (`ScoringService`, versi `bintang-1`, disimpan di `game_releases.scoring_version`):

| Bintang | Syarat |
|---|---|
| ★★★ | semua soal berhasil pada percobaan pertama |
| ★★ | semua soal benar di akhir (sebagian butuh percobaan ke-2/ke-3) |
| ★ | pengerjaan selesai, tetapi ada soal yang tetap salah setelah kesempatan habis |
| 0 | pengerjaan belum selesai atau ditinggalkan |

Petunjuk, suara, dan waktu tidak mengurangi bintang; semuanya tetap tercatat sebagai data bantuan.

`POST /api/attempts/{id}/complete` hanya cadangan klien bila respons selesai datang tanpa `redirect`. Endpoint ini idempoten: pengerjaan yang sudah selesai dibalas hasil dan `redirect` yang sama; pengerjaan yang belum boleh ditutup (masih ada kesempatan dan belum semua benar) dibalas 409 `TRIES_REMAINING`. Tidak ada tombol "selesaikan sekarang".

### Mengulang tantangan

Pos yang sudah selesai tetap dapat dibuka lagi. Pengerjaan baru mendapat `attempt_no` berikutnya, 3 kesempatan baru, dan variasi yang belum pernah didapat siswa itu bila masih ada. Pengerjaan lama tidak dihapus; setiap pengerjaan adalah data penelitian tersendiri. Riwayat pengerjaan selesai satu pos tampil di `/hasil/{code}/{n}` (`ChallengeController::result`). Bintang yang dihitung untuk progres adalah pengerjaan terbaik per pos (bintang terbanyak; bila sama, yang paling awal).

### Bila siswa meninggalkan tantangan

```text
A. Tombol Keluar (atau Back browser) → konfirmasi (event overlay_opened {overlay: 'exit'})
     → POST /api/attempts/{id}/abandon
     ChallengeService::abandonAttempt(): status 'abandoned', stars 0, completed_at,
     event challenge_abandoned {via: 'user_exit', attempt_no}
     Jawaban dan item_tries yang sudah dicek tetap tersimpan.

B. Menutup tab / memuat ulang / koneksi putus
     pagehide → event klien challenge_abandoned {via: 'pagehide', attempt_open: true},
     antrean dikirim lewat beacon (tanpa dialog bawaan browser)
     pengerjaan tetap 'in_progress'; saat siswa kembali ke pos itu, openNode()
     melanjutkannya. RetentionService menandai pengerjaan in_progress yang tidak
     tersentuh lebih dari 24 jam menjadi 'abandoned' (FITUR 17).
```

`challenge_abandoned` punya empat sumber yang dibedakan `payload.via`: `user_exit` dan `content_changed` (server), `retention` (server), dan `pagehide` (klien; pengerjaannya masih dapat dilanjutkan). Karena itu jumlah pengerjaan yang ditinggalkan dihitung dari `challenge_attempts.status = 'abandoned'`, bukan dari jumlah event. Event server ber-`client_event_id` berawalan `srv-`.

Tirai tantangan tidak memengaruhi waktu pengerjaan: tirai diputar di kartu misi, dan pengerjaan baru dibuka saat `/tantangan` dirender, sehingga `started_at` dan `duration_ms` tidak memuat durasi tirai.

---

## FITUR 5a: Serpihan Cahaya, Lentera, dan Wilayah Tuntas

```text
1. Saat pengerjaan ditutup, ChallengeService::refreshProgress() menghitung ulang
   session_progress dari pengerjaan 'completed' sesi itu:
     completed_nodes         = jumlah pos yang pernah selesai = Serpihan Cahaya
     completed_levels        = wilayah yang keempat posnya selesai
     unlocked_level_sequence = sequence wilayah tuntas terakhir + 1
                               (unlock_mode = free → semua wilayah)
     total_stars             = ScoringService::totalStars() (bintang terbaik per pos)
     current_level_id / current_node_id
2. Respons /check dan /responses membawa `shards`; challenge.js memperbarui
   lentera HUD sebelum pindah halaman
3. /selesai/{attempt} → ChallengeController::finished (hanya pengerjaan
   'completed' milik sesi ini):
     bintang, "Berhasil pada Percobaan Pertama" (x dari y soal; Susun Gambar
     Ya/Belum), benar di akhir, lencana alasan selesai ("Semua benar" / "Disimpan apa adanya"),
     percobaan dipakai ({check_count} dari {max_tries}; Cari Objek: jumlah
     ketukan salah), lama mengerjakan
     pengerjaan yang MENUNTASKAN wilayah (closingAttemptId: penyelesaian pertama
     pos terakhir) → tombol utama ke /tuntas/{code} dan momen
     "Pustaka {wilayah} terbuka!"; mengulang pos di wilayah yang sudah tuntas
     tidak memunculkannya lagi
4. /tuntas/{code} → DialogueController::done (hanya wilayah yang tuntas pada sesi
   ini): narasi 'level_done', lalu Baca Pustaka {wilayah} dan Lanjut ke wilayah
   berikutnya (entry + tirai wilayah), atau ke /penutup di wilayah terakhir
5. Keempat pos ketiga wilayah selesai (12 serpihan):
     SessionService::completeIfFinished(): game_sessions.status 'completed',
     ended_at, event session_completed {total_stars}
     /penutup → DialogueController::ending (narasi 'ending', boleh ditonton
     ulang) → Balai Refleksi (FITUR 9)
```

Lentera di HUD (`BaseGameController::hudData()` → `progressSummary()`, `lanternMap()`) menampilkan `shards` dari `shards_total` (jumlah pos aktif, 12) dan rincian status setiap pos per wilayah. Lentera, profil, dan layar selesai membaca jumlah pos aktif dari konten, bukan angka tetap.

---

## FITUR 6: Petunjuk

```text
1. Tombol 💡 tampil bila payload pengerjaan punya hints (hints_count > 0);
   payload hanya membawa id petunjuk {id, item_id, sequence}, bukan teksnya.
   Petunjuk node berlaku untuk semua variasi; petunjuk soal hanya untuk soal
   yang diminta dijawab (pengecoh tidak pernah disebut).
2. Siswa menekan 💡 → event overlay_opened {overlay: 'hint'} → konfirmasi
   "Petunjuk bisa membantumu. Buka petunjuk sekarang?"
3. POST /api/attempts/{id}/hints {hint_id, item_id?}
4. ChallengeService::useHint() — transaction:
     petunjuk harus milik node pengerjaan (atau soal di dalamnya), selain itu 404
     challenge_attempts.hint_count++
     item_responses.hint_used = 1 bila petunjuk terikat soal
     event hint_opened {hint_id}
5. Respons {text, hint_count} → modal teks petunjuk; petunjuk yang sudah dibuka
   tidak diminta ulang (setiap permintaan dihitung server)
6. Percobaan sesudahnya mencatat item_tries.hint_opened_before = 1
```

Petunjuk tidak mengurangi bintang. Pemakaiannya dianalisis sebagai data bantuan (`hint_count`, `hint_used`, `hint_opened_before`, event `hint_opened`).

## FITUR 7: Pencatatan Event, `screen_viewed`, dan Telemetry Audio

Semua event ditulis apa adanya ke `game_event_logs` oleh `App\Services\EventService`. Jenis yang diterima hanya `Config\Gelita::$eventTypes`; tidak ada skor atau indeks yang dihitung dari event.

### Event dari server

`EventService::record()` dipanggil di dalam transaction service pemanggil, dengan `client_event_id` berawalan `srv-`, `sequence_no` berikutnya per sesi, dan `occurred_at` = waktu server (DATETIME(6)).

| Event | Ditulis oleh | Payload utama |
|---|---|---|
| `session_started` | `SessionService` | `via`: `register`, `login`, `new_session` |
| `session_resumed` | `SessionService::login()` | `via: 'login'` |
| `session_paused` | `SessionService::logout()` | `via: 'logout'` |
| `session_abandoned` | `RetentionService` | `via: 'retention'` |
| `session_completed` | `SessionService::completeIfFinished()` | `total_stars` |
| `locale_changed` | `SessionService::setLocale()` | `from`, `to` |
| `intro_completed` | `GateController::finishIntro()` | `first` |
| `screen_viewed` | `App\Filters\ScreenViewFilter` | `screen`, `path`, `from`, `params` |
| `library_opened` | `LibraryController::show()` | — (`level_id`) |
| `challenge_opened` | `ChallengeService::openNode()` | `attempt_no`, `variant_group`, `item_count` |
| `challenge_checked` | `ChallengeService::submitCheck()` | `check_no`, `try_no`, `correct_count`, `total`, `exhausted` |
| `challenge_tries_exhausted` | `submitCheck()` | `check_count`, `correct_count`, `total` |
| `answer_submitted` / `answer_changed` | `submitAnswer()` (Cari Objek, benar) | `try_no`, `correct`, `first_pass`, `change_count` |
| `wrong_target_clicked` | `submitAnswer()` (Cari Objek, salah) | `try_no`, `clicked_item_id`, `decoy`, `first_pass` |
| `clue_failed` | `submitAnswer()` | `try_count` |
| `hint_opened` | `ChallengeService::useHint()` | `hint_id` |
| `challenge_completed` | `closeAttempt()` | `stars`, `first_pass_correct`, `final_correct`, `scorable_items`, `completion_reason`, `attempt_no` |
| `level_completed` | `closeAttempt()` | `level_stars` |
| `challenge_abandoned` | `abandonAttempt()` | `via`: `user_exit`, `content_changed`, `retention` |
| `feedback_submitted` | `ReflectionController::store()` | `rating`, `open_answers` |
| `password_changed` | `SessionService` / `LoginController` | `via`: `self`, `reset` |

`screen_viewed`, `challenge_tries_exhausted`, dan `clue_failed` hanya boleh ditulis server (`EventService::SERVER_ONLY`); kiriman klien dengan tipe itu ditolak `event_type_server_only`, sehingga data navigasi dan batas percobaan tidak dapat dipalsukan.

### `screen_viewed`

`ScreenViewFilter` (after) terpasang pada grup route `gameSession` dan `participantAuth`. Setiap respons `GET` 200 `text/html` untuk siswa bersesi dicatat dengan kode layar, kode layar sebelumnya (`from`, dari session PHP `last_screen`), dan parameter route. Pengalihan tidak dicatat, dan kegagalan pencatatan tidak menggagalkan halaman.

| Path | `screen` | `params` |
|---|---|---|
| `gerbang`, `gerbang/peta` | `gate` | — |
| `intro` | `intro` | — |
| `peta` | `map` | — |
| `wilayah/{code}` | `region` | `level` |
| `dialog/{code}` | `dialogue` | `code` |
| `misi/{code}/{n}` | `brief` | `level`, `seq` |
| `tantangan/{code}/{n}` | `challenge` | `level`, `seq` |
| `selesai/{id}` | `finished` | `attempt` |
| `hasil/{code}/{n}` | `result` | `level`, `seq` |
| `tuntas/{code}` | `region_done` | `level` |
| `penutup` | `ending` | — |
| `pustaka` | `library_index` | — |
| `pustaka/{code}` | `library` atau `library_locked` | `level` |
| `profil` | `profile` | — |
| `refleksi` | `reflection` | — |
| `ganti-sandi` | `change_password` | — |

Parameter `level` (dan `seq`) diterjemahkan ke kolom `level_id` (dan `challenge_node_id`). Layout game merender `<body data-screen-code>` dengan resolver yang sama, dipakai klien untuk `page_hidden`/`page_visible`. Lama tinggal di sebuah layar tidak dihitung sistem; peneliti menghitungnya dari selisih `occurred_at` event navigasi berikutnya dan pasangan `page_hidden`/`page_visible`.

### Event dari klien

| Event | Sumber | Kapan | Payload |
|---|---|---|---|
| `level_opened` | `game/map.js` | peta wilayah dibuka | `code` |
| `dialogue_advanced` | `game/narrator.js` | slide cerita berpindah | `index`, `total`, `context`, `character` |
| `overlay_opened` | `core/device.js`, `game/challenge.js` | overlay Kenali, modal petunjuk, modal keluar, lightbox Pustaka | `overlay` (`kenal`, `hint`, `exit`, `lightbox`), `level` (kode wilayah) |
| `answer_selected` | `game/challenge.js` | mengisi/mengubah soal sebelum cek | `action` (`set`, `clear`, `swap`), `value`, `from`, `to`, `t_ms` |
| `reading_progress` | `core/device.js` | panel bacaan digulir melewati 25/50/75/100 % (sekali per pengerjaan) | `passage_id`, `percent` |
| `challenge_opened` | `game/challenge.js` | pengerjaan dilanjutkan (resume) | `resumed: true`, `attempt_no` |
| `challenge_abandoned` | `game/challenge.js` | halaman tantangan ditutup (`pagehide`) | `via: 'pagehide'`, `attempt_open: true` |
| `library_page_viewed` | `game/library.js` | ganti halaman buku Pustaka | `page`, `total`, `page_id` |
| `page_hidden` / `page_visible` | `core/device.js` | `visibilitychange` | `screen` |
| `viewport_changed` | `core/device.js` | halaman dibuka dan setiap ukuran/orientasi berubah (debounce 1 detik, paling sering satu event per 5 detik) | `w`, `h`, `orientation`, `rotate_gate` |

```text
1. emit() → antrean core/events.js (client_event_id dibuat saat event lahir)
2. Dikirim tiap 3 detik atau saat antrean mencapai 20 event, maks. 50 per request
   → POST /api/events {events: [...]}
3. EventApiController::ingest (rate limit 120/menit per sesi; > 50 event → 413)
   → EventService::ingest():
     tipe tidak dikenal → ditolak per event (event_type_unknown)
     tipe server saja   → event_type_server_only
     client_event_id kosong/kembar dalam batch → ditolak per event
     occurred_at klien dipakai bila dapat diurai (dikonversi ke zona aplikasi,
     presisi mikrodetik), selain itu waktu server; server_received_at = waktu server
     GameEventLogModel::appendBatch(): idempoten terhadap (sesi, client_event_id)
4. Respons {accepted, duplicate, rejected[], server_time}
5. Tab disembunyikan/ditutup → antrean dan batch yang sedang dikirim ikut dikirim
   lewat sendBeacon; bila ternyata sudah sampai, server membalas duplicate.
   Tirai menunggu flush() selesai (paling lama 1,5 detik) sebelum berpindah, dan
   narrator langsung mengirim antrean saat slide terakhir tercapai.
```

### Telemetry audio

```text
1. Audio dirender hanya bila disetujui: audio_src() mengembalikan path bila
   approval_status = 'approved' dan berkasnya aktif; selain itu hanya teks
2. core/audio.js mengakumulasi waktu dengar sungguhan; aksi:
     play (siswa menekan ▶), autoplay (layar bernarasi memutar sendiri),
     pause, replay, complete
   Melanjutkan dari jeda tidak dikirim sebagai play baru.
3. emitAudio() → POST /api/audio-events {events: [{audio_asset_id, action,
   listened_ms, completed, occurred_at, client_event_id, attempt_id?}]}
4. AudioApiController::ingest: attempt_id hanya diterima bila milik sesi berjalan
   (selain itu attempt_not_in_session); EventService::recordAudio() — transaction:
     insert audio_usage_events (play_index dihitung server: nextPlayIndex)
     insert game_event_logs audio_play | audio_autoplay | audio_pause |
       audio_replay | audio_completed
     bila terkait pengerjaan → challenge_attempts.audio_use_count++
       (setiap event audio, bukan setiap pemutaran)
   idempoten terhadap client_event_id (kiriman ulang tidak menulis apa pun)
```

Durasi pembanding diambil dari `audio_assets.duration_ms` (data server), bukan dari browser. Putar otomatis tidak terjadi saat suara dimatikan (sakelar suara HUD) atau sebelum siswa berinteraksi dengan halaman; dalam kasus itu tidak ada event.

### Saat koneksi putus

```text
1. api.js gagal → ApiError 'NETWORK'
2. Event: antrean disimpan di localStorage (core/storage.js, per kanal dan per
   sesi permainan, maks. 500 entri terbaru) dan dikirim ulang saat koneksi pulih;
   server membalas duplicate untuk yang sudah masuk → tidak ada data ganda
3. Jawaban (/check, /responses): TIDAK diantrekan. Siswa melihat pesan
   "sambungan terputus", lalu menekan Cek lagi. Jawaban harus dinilai server;
   menyimpannya diam-diam di klien akan menampilkan hasil yang belum diterima.
4. Tab ditutup sebelum koneksi pulih: event tetap di localStorage perangkat itu
   dan dikirim saat aplikasi dibuka lagi oleh sesi yang sama; event siswa A tidak
   pernah dikirim ke sesi siswa B
```

Heartbeat `POST /api/session/heartbeat` (tiap 60 detik selama tab terlihat) memperbarui `game_sessions.last_active_at` dan menambah `duration_ms`; jeda lebih lama dari `Config\Gelita::$sessionIdleMinutes` tidak dihitung sebagai waktu bermain.

---

## FITUR 8: Pustaka Kedu

Pustaka Kedu (30 halaman, 48 media, sumber [`docs/pustaka/`](pustaka/README.md)) adalah bacaan pendamping tiap wilayah. Isinya dipasang `KontenKelas5Seeder` dari `docs/pustaka/gelita-pustaka.xlsx` dan tidak disentuh `gelita:reset` maupun impor bank soal.

```text
1. Tombol "Pustaka Kedu" di peta → /pustaka (LibraryController::index):
   rak satu kartu per wilayah dengan status Pustaka-nya
2. Tombol "Pustaka {wilayah}" di peta wilayah → /pustaka/{code}
3. Status per wilayah (GameProgress::libraryStatus/libraryAccess), apa pun
   unlock_mode studinya:
     wilayah belum terbuka            → /peta + pesan
     wilayah terbuka, belum tuntas    → view game/library-locked: progres x/4,
                                        penjelasan, "Lanjutkan tantangan" → entry
                                        wilayah; TIDAK dicatat library_opened
     keempat tantangan wilayah selesai → buku (langkah 4–6)
   GET /api/library/{levelId} memakai aturan yang sama → 409 LIBRARY_LOCKED
4. ContentRepository::libraryPages(): halaman aktif + media aktifnya
   (library_media, LibraryMediaModel)
5. View game/library: kiri galeri media, kanan judul + teks (rich_text()):
     gambar : unggahan, Wikimedia Commons, Google Drive, berkas https langsung;
              klik → lightbox (event overlay_opened {overlay: 'lightbox'})
     video  : unggahan (<video> berposter) atau YouTube/Vimeo/Drive sebagai
              facade berposter; iframe baru dipasang setelah siswa menekan Putar;
              YouTube lewat youtube-nocookie.com
     media luar: ikon ⓘ → kredit + "Lihat sumber ↗"
   Media tanpa berkas aktif atau tautan yang tidak dikenali App\Libraries\MediaLink
   tidak dirender.
6. Server mencatat library_opened (hanya bila isi buku tampil); library.js
   mencatat library_page_viewed tiap ganti halaman
```

Pustaka tidak memengaruhi bintang, serpihan, atau status pos. Karena Pustaka baru terbuka setelah wilayahnya tuntas, setiap `library_opened` dan `library_page_viewed` wilayah X selalu terjadi setelah keempat tantangan wilayah X selesai pada sesi itu; membaca Pustaka tidak dapat memengaruhi pengerjaan pertama tantangan wilayah itu (mengulang tetap boleh). Upaya membuka Pustaka yang masih terkunci tercatat hanya sebagai `screen_viewed` dengan `screen = library_locked`.

**Thumbnail video (`App\Libraries\VideoThumbnail`).** Poster video YouTube/Vimeo/Drive diunduh **server** satu kali, bukan oleh perangkat siswa: YouTube `i.ytimg.com/vi/{id}/maxresdefault.jpg` (cadangan `hqdefault.jpg`), Vimeo lewat oEmbed (hanya https `*.vimeocdn.com`), Drive `drive.google.com/thumbnail?id={id}&sz=w1280`. Respons diterima hanya bila 200, `Content-Type: image/*` bukan SVG, ≤ 4 MB, dan terbaca sebagai gambar. Gambar disimpan `MediaStore::storeBytes()` sebagai aset `library.thumb.{penyedia}.{id}-{sha1}` dan dipasang di `library_media.poster_media_id`. Dipanggil setelah simpan halaman Pustaka di panel (`ContentController::saveLibrary()`) dan setelah impor workbook berisi video luar tanpa poster; kegagalan tidak pernah menggagalkan penyimpanan (dicatat, admin melihat catatan). `php spark gelita:library:thumbnails [--force]` mengisi baris yang belum berposter; `--force` mengunduh ulang semuanya.

Format teks halaman (ditulis tanpa HTML): baris kosong = paragraf, `## ` subjudul, `- ` daftar, `> ` kotak "Tahukah kamu?", baris `Sumber:` catatan rujukan, `**tebal**`, `*miring*`. `rich_text()` meng-escape seluruh teks lebih dulu.

---

## FITUR 9: Balai Refleksi dan Kritik & Saran

```text
1. Setelah 12 tantangan selesai, layar selesai dan /penutup menautkan Balai Refleksi
2. /refleksi → ReflectionController::index
     guard: allNodesCompleted() (completed_nodes ≥ jumlah pos aktif);
            bila belum → /peta + pesan
3. Ringkasan perjalanan dari pengerjaan terbaik tiap pos (angka tampilan untuk
   siswa, bukan angka penelitian):
     Berhasil pada Percobaan Pertama (x dari y soal), tantangan selesai, lama
     bermain, soal benar; "Bintang per wilayah" (x dari 3 × jumlah tantangan);
     Berhasil pada Percobaan Pertama per jenis tantangan (persen);
     "Paling sering terlewat" (x dari y soal)
4. Formulir: bintang 1–5 + empat pertanyaan terbuka (liked_most, hardest_part,
   new_learning, suggestion)
5. POST /refleksi → ReflectionController::store
     rating 1–5 wajib; minimal dua jawaban terbuka terisi; maks. 2.000 karakter
     insert participant_feedback (participant_id, session_id, submitted_at)
     event feedback_submitted {rating, open_answers}
6. Masukan tampil di /admin/masukan (FITUR 11)
```

## FITUR 10: Panel Staf — Masuk, Cakupan Sekolah, dan Beranda

Panel admin selalu berbahasa Indonesia (`LocaleFilter`). Dua peran: `admin` (semua sekolah, konten, pengaturan) dan `guru` (hanya data siswa di sekolahnya).

```text
1. Staf membuka /admin/login
2. POST /admin/login → AuthController::login
     findByUsername → terkunci? → verifyPassword → is_active?
     gagal   : registerFailedLogin (5 kali gagal → kunci 15 menit),
               audit 'login_failed' {reason}, pesan identik untuk semua jenis
               kegagalan
     berhasil: session()->regenerate(true); staff_id, staff_role,
               staff_school_id, staff_name; clearFailedLogin; audit 'login'
     must_change_password = 1 → /admin/akun/sandi (FITUR 10a)
3. Redirect /admin/dashboard → DashboardController::index
     filter dari query string (readFilters) dipaksa ke cakupan sekolah
     AnalyticsService::summary(): HANYA hitungan data mentah —
       siswa, sesi, sesi selesai, pengerjaan selesai/ditinggalkan, jumlah
       item_tries, petunjuk dibuka, pemakaian suara
     ParticipantModel::cohortSummary(): sebaran siswa
     10 sesi bermain terakhir
     admin: peringatan pratinjau penghapusan buatan retensi (FITUR 17)
4. GET /admin/logout → audit 'logout', session()->destroy()
```

**Cakupan sekolah — tiga lapis.**

1. Route: `staffAuth` untuk seluruh `/admin` dan `/api/admin`; `staffRole:admin` untuk konten, media, studi, tata kelola, sekolah, dan staf. Halaman tanpa hak dibalas 404 (struktur panel tidak terpetakan), API 403 `FORBIDDEN`.
2. Controller: `StaffScope::schoolScope()` (NULL untuk admin, `school_id` untuk guru; guru tanpa sekolah → 0, tidak cocok dengan apa pun) dan `scopedFilters()`. Siswa atau sesi di luar cakupan dibalas 404, bukan 403.
3. Service: `AnalyticsService`, `RawDataService`, dan `ExportService` menambahkan `participants.school_id` pada setiap query (`App\Services\StaffScope`), dan `ExportService` membaca ulang hak pemohon dari `staff_users`.

`StaffAuthFilter` menyinkronkan peran, sekolah, dan status aktif dari database pada setiap request, sehingga perubahan oleh admin (termasuk menonaktifkan akun atau reset sandi) langsung berlaku. Dasbor admin yang lebih lengkap (peringatan gambar contoh, tabel formal) direncanakan di Dok 3 perombakan kelas 5.

API panel (`/api/admin/*`, `AdminApiController`): `summary` (KPI hitungan yang sama dengan beranda), `sessions/{id}/timeline` (linimasa event), `exports/{id}/status`.

---

## FITUR 10a: Akun Staf

```text
KELOLA AKUN (admin, /admin/staf, StaffController)
1. Buat akun: nama pengguna, nama tampilan, peran admin|guru, email, sandi awal
   (12–72 karakter); guru WAJIB punya sekolah (NPSN resmi atau pilihan sekolah
   yang sudah dipakai) agar cakupannya sama dengan siswanya
   → must_change_password = 1, audit 'staff_create'
2. Ubah akun → audit 'staff_update'
3. Reset sandi (ketik RESET) → StaffUserModel::resetToTemporary(): sandi
   sementara 16 karakter ditampilkan SEKALI (no-store), must_change_password = 1,
   kunci login dibuka; audit 'staff_password_reset'
4. Nonaktifkan (bukan akun sendiri) → is_active = 0, audit 'staff_deactivate'

GANTI SANDI SENDIRI (admin & guru, /admin/akun/sandi, AccountController)
5. Sandi saat ini wajib benar (salah → audit 'staff_password_change_failed',
   dihitung ke throttle login; akun terkunci → sesi diputus)
6. Sandi baru 12–72 karakter → must_change_password = 0,
   audit 'staff_password_change'
   Selama must_change_password = 1, hanya halaman ini yang terbuka; API membalas
   403 PASSWORD_CHANGE_REQUIRED.
```

Admin yang lupa sandi tanpa admin lain: `php spark gelita:staff:password USERNAME` memakai jalur yang sama (`resetToTemporary()`), mencetak sandi sementara sekali, dan mengaudit `staff_password_reset` dengan `via: cli`. Kata sandi staf tidak pernah masuk flash, `old()`, maupun `audit_logs`. Akun `admin` pertama dibuat `StaffUserSeeder` saat instalasi dari variabel lingkungan `GELITA_ADMIN_PASSWORD`.

---

## FITUR 10b: Studi dan Rilis

**Studi** (`/admin/studi`, `StudyController`, tabel `research_studies`): kode, nama, deskripsi, tahun ajaran, status (`draft` | `active` | `closed`), lama penyimpanan data (`retention_days`), bahasa awal permainan (`default_locale`), dan cara wilayah terbuka (`unlock_mode` `sequential` | `free`, FITUR 4). Tidak ada fase, pengaturan persetujuan, atau mode pemilihan soal. Sesi baru selalu memakai studi `active` dengan id terbesar (`ResearchStudyModel::activeStudy()`); tanpa studi aktif, pendaftaran dan masuk siswa gagal. Kode studi tidak dapat diubah setelah dibuat. Audit `study_create`, `study_update`.

**Rilis** (`/admin/studi/rilis`, tabel `game_releases`): nama versi (`release_code`), versi aplikasi, versi konten, versi aset, dan versi aturan bintang (`scoring_version`, sekarang `bintang-1`). Rilis baru disimpan nonaktif; **Aktifkan** (`GameReleaseModel::activate()`) menjadikannya satu-satunya rilis aktif dan mem-flush cache konten. Sesi baru mencatat rilis aktif di `game_sessions.release_id`; sesi yang sedang berjalan tetap pada rilisnya. Audit `release_create`, `release_activate`.

Instalasi baru membuat satu studi `STUDI-KEDU-KELAS5-2026` (aktif, `retention_days` 1825, `sequential`) dan rilis `GELITA-KELAS5-2026` (`ResearchStudySeeder`, `GameReleaseSeeder`).

---

## FITUR 10c: Sekolah

Halaman `/admin/sekolah` (admin, `SchoolController`, aturan di `App\Services\SchoolDirectory`):

```text
1. Cari sekolah resmi: NPSN persis, atau setiap kata cocok dengan awal kata nama
   sekolah, kecamatan, atau desa; filter kab/kota. Dipakai guru menyiapkan NPSN
   yang ditulis di papan saat sesi kelas.
2. Daftar nama sekolah ketikan siswa (is_verified = 0), terbanyak siswanya dulu,
   dengan saran sekolah serupa di kab/kota yang sama
3. Gabungkan → POST /admin/sekolah/{id}/gabung (SchoolDirectory::merge):
     siswa dan akun guru pindah ke sekolah tujuan, snapshot nama siswa ikut
     nama tujuan, entri asal dinonaktifkan dengan merged_into_id (menjadi alias:
     ketikan yang sama berikutnya langsung tertaut); audit 'school_merge'
     Sekolah resmi tidak dapat digabungkan ke sekolah lain.
4. Gabung otomatis → POST /admin/sekolah/gabung-otomatis (autoMerge): semua
   entri yang cocok jelas dengan sekolah resmi (aturan matchByName)
5. Sahkan → POST /admin/sekolah/{id}/sahkan (verify): entri menjadi sekolah
   ber-NPSN (8 angka, belum dipakai sekolah lain) agar siswa berikutnya dapat
   memakai NPSN itu; audit 'school_verify'
```

`php spark gelita:schools:import [--file=…] [--dry-run] [--link-existing]` (`App\Libraries\SchoolImporter`) memasang atau memperbarui daftar resmi: berkas diperiksa seluruhnya dulu, hanya baris berubah yang ditulis, tidak ada sekolah yang dihapus (NPSN yang hilang dari berkas hanya dilaporkan), dan entri ketikan siswa tidak disentuh selain kunci namanya. `--link-existing` sekaligus menjalankan gabung otomatis.

---

## FITUR 11: Siswa, Sesi, dan Linimasa Event di Panel

Guru dan admin; guru hanya untuk siswa sekolahnya.

```text
1. /admin/peserta (ParticipantController::index): 25 per halaman, cari kode/nama
   pengguna/nama, filter sekolah (admin), kelas, provinsi; per baris jumlah sesi,
   serpihan terbanyak dalam satu sesi, aktivitas terakhir
2. /admin/peserta/{id} (show):
     identitas aman (tanpa password_hash dan kolom throttle), wilayah, metrik
     sandi, "Setuju ikut bermain: ya · {assented_at}"
     AnalyticsService::participantProfile(): hitungan data mentah pengerjaan selesai
     catatan perangkat (device_logs) per daftar/masuk
     daftar sesi + progres; tombol Reset kata sandi (FITUR 2a) dan Laporan PDF (FITUR 15)
3. /admin/sesi (SessionController::index): filter studi, sekolah, kelas, bahasa,
   rentang tanggal
4. /admin/sesi/{id} (show): sesi, progres, device_logs sesi itu, setiap pengerjaan
   (variasi, alasan selesai, check_count/max_tries, bintang) dengan rincian
   per soal (item_responses: percobaan, benar pada percobaan ke-, petunjuk,
   salah pilih benda, jawaban akhir) dan setiap percobaan (item_tries)
5. /admin/sesi/{id}/event (timeline): linimasa game_event_logs (presisi
   milidetik), filter per jenis aktivitas; label aktivitas dari Language/id/Admin.php
6. /admin/masukan (FeedbackController::index): kritik & saran Balai Refleksi,
   sebaran bintang 1–5, filter sekolah dan kelas
```

Panel hanya menampilkan data mentah: tidak ada indeks kesulitan, daya pembeda, capaian indikator, perbandingan tes, atau chart. Pengolahan dilakukan peneliti dari ekspor (FITUR 14).

## FITUR 12: Kelola Konten

Editor konten (`/admin/konten`, admin saja, `Admin\ContentController`) dipakai untuk perbaikan kecil; isi bank soal lengkap masuk lewat workbook (FITUR 12a).

```text
1. /admin/konten → 3 kartu wilayah, tiap kartu 4 tantangan dengan jumlah soal
   aktif dan jumlah variasinya, versi konten aktif
2. /admin/konten/level/{id}: nama ID/EN, tingkat (mudah|sedang|sulit), fokus,
   CP/TP, teks pengantar, gambar peta/latar/lencana wilayah
3. /admin/konten/node/{id}: judul/instruksi/deskripsi ID+EN, jenis tantangan,
   indikator, pengaturan terpandu (config_json), gambar adegan (Cari Objek) dan
   latar tantangan, narasi pembuka kartu misi ID/EN, lalu tabel soal per variasi
4. Tambah/ubah soal → POST /admin/konten/node/{id}/item | /admin/konten/item/{id}
     jenis soal wajib sesuai engine (satu jenis per engine: puzzle_arrange,
     fill_blank_bank, verdict_card, find_object)
     soal dinilai wajib punya kunci (answer_key_json)
     Susun Gambar: ukuran papan, bentuk keping, dan kunci ditetapkan server dari
     tingkat wilayah (DifficultyRules); soal yang sudah dijawab tetap memakai
     ukuran dan kuncinya sendiri
     Cari Objek: dua pemilih audio narasi petunjuk (ID/EN), jenis lain selalu tanpa audio
5. Hapus soal → POST /admin/konten/item/{id}/hapus; soal yang sudah pernah dijawab
   hanya dinonaktifkan (data penelitian tetap utuh)
6. Media dari formulir (komponen media-field, App\Libraries\MediaStore): pilih
   asset_key yang ada ATAU unggah berkas baru; berkas baru disimpan dengan
   kunci bawaan tempat itu (mis. challenge.item.{kode soal}, challenge.{node}.scene)
7. Setiap simpanan: ContentRepository::flush() + audit 'content_update'
8. Verifikasi: tombol Periksa kelengkapan (POST /admin/konten/verifikasi) atau
   `php spark gelita:content:verify [--strict] [--siap-penelitian]` →
   App\Libraries\ContentVerifier (aturan di FITUR 12a)
```

Halaman lain di bawah Konten: bacaan per wilayah (`/admin/konten/bacaan/{level}`), Pustaka per wilayah (`/admin/konten/pustaka/{level}`: tambah/hapus/urutkan halaman dan medianya dari berkas atau tautan, satu transaction per wilayah), cerita per wilayah atau global (`/admin/konten/dialog/{level}`: teks ID/EN, tokoh, pose, efek, audio per baris), Narasi (FITUR 13a), dan Impor soal dari Excel (FITUR 12a).

`engine_type` tidak dapat diubah bila tantangan sudah pernah dimainkan, karena data lama tidak akan sebanding.

---

## FITUR 12a: Bank Soal Kelas 5 — Workbook, Impor, dan Verifikasi

Bank soal final kelas 5 (disetujui peneliti): **126 soal** (108 dinilai + 18 benda pengecoh), 30 variasi, 9 bacaan Benar atau Salah, 36 petunjuk (3 per tantangan), 12 kata pengecoh, dan 45 slot gambar tantangan. Rinciannya di [`docs/bank-soal/`](bank-soal/README.md).

### Sumber dan alur

```text
docs/bank-soal/data/{temanggung,magelang,wonosobo}.php     (sumber yang disunting)
  → php docs/bank-soal/build-workbook.php                  (memberi kode, memeriksa
                                                             BankRules, menulis workbook)
  → docs/bank-soal/gelita-bank-soal-kelas5.xlsx
  → diimpor: KontenKelas5Seeder (instalasi), gelita:reset (FITUR 18),
             php spark gelita:bank:import FILE, atau panel /admin/konten/impor-bank
```

Kode otomatis: soal `{node}-{variasi}{nn}` (mis. `tmg-2-a01`), bacaan Benar atau Salah `{node}-{variasi}` (mis. `mgl-3-a`); `node_ref` = `tmg-1` … `wnb-4` (awalan dari `Config\Gelita::$levelPrefixes`).

### Impor di panel

```text
1. /admin/konten/impor-bank → unduh templat (GET /admin/konten/impor-bank/templat)
   atau pakai workbook kelas 5
2. Unggah .xlsx (maks. 20 MB) → POST /admin/konten/impor-bank/pratinjau
     berkas disimpan di writable/uploads/ (di luar public/)
     ContentImportService::preview(): baca sheet data (sheet lain diabaikan),
     validasi setiap baris dan keadaan SESUDAH impor setiap tantangan yang
     disentuh (BankRules); TIDAK menulis database
     ringkasan per tantangan; galat (sheet + nomor baris + alasan); peringatan
3. Galat = 0 → POST /admin/konten/impor-bank/jalankan → ContentImportService::import()
     SATU TRANSACTION: slot media → nodes (+ variants, distractors ke config_json)
                       → passages → items → hints → library → library_media
     upsert menurut node_ref / passage_key / item_key / (level_code, sequence)
     asset_key yang belum terdaftar → baris media_assets nonaktif (slot kosong)
       dengan label dan description dari kolom media_label / media_note;
       slot yang sudah ada tidak diubah selain mengisi label/description kosong
     soal yang sudah punya item_responses: kunci jawabannya TIDAK boleh berubah
       → baris ditolak dan seluruh impor dibatalkan
4. ContentRepository::flush(); audit 'content_import' {file_sha256, written}
5. Jalankan verifikasi (FITUR 12 langkah 8), lalu gambar contoh (FITUR 12b)
```

CLI setara: `php spark gelita:bank:import FILE [--dry-run] [--staff USERNAME]` (pratinjau atau impor, ringkasan per tantangan, galat dan peringatan per baris; pelaku audit = admin `--staff` atau admin aktif pertama; kode keluar 1 bila ada galat).

### Format workbook

Baris pertama tiap sheet = header persis `ContentImportService::SHEETS`. Sel kosong = NULL. Header berbahasa Inggris karena dibaca sistem; panduan berbahasa Indonesia dibangun `App\Libraries\BankWorkbookGuide`: sheet **PETUNJUK** (langkah mengisi, arti sheet, tabel kunci jawaban, rentang Bloom), sheet **KAMUS_KOLOM** (setiap kolom: nama Indonesia, Wajib/Bersyarat/Opsional, arti, isian, contoh), header berwarna dengan catatan, dan dropdown untuk kolom berkode. Workbook kelas 5 juga memuat lampiran **DAFTAR_MEDIA** (daftar gambar yang harus disiapkan).

| Sheet | Kolom |
|---|---|
| `nodes` | `node_ref`, `title_id`, `title_en`, `instruction_id`, `instruction_en`, `description_id`, `description_en`, `distractor_count`, `scene_media_key`, `scene_media_label`, `scene_media_note`, `background_media_key`, `variant_code`, `indicator` |
| `variants` | `node_ref`, `variant_group`, `title_id`, `title_en` |
| `distractors` | `node_ref`, `variant_group`, `text_id`, `text_en` (pengecoh Isi Rumpang per variasi) |
| `passages` | `passage_key`, `level_code`, `title_id`, `title_en`, `body_id`, `body_en`, `media_asset_key`, `reference_source` |
| `items` | `item_key`, `node_ref`, `variant_group`, `sequence`, `interaction_type`, `indicator`, `bloom_level`, `skill`, `prompt_id`, `prompt_en`, `passage_key`, `answer_id`, `answer_en`, `x`, `y`, `w`, `decoy`, `wrong_feedback_id`, `wrong_feedback_en`, `media_asset_key`, `media_label`, `media_note`, `scorable`, `is_active`, `review_status`, `review_note`, `reference_source` |
| `hints` | `node_ref` **atau** `item_key`, `sequence`, `text_id`, `text_en` |
| `library`, `library_media` | hanya di workbook Pustaka (`docs/pustaka/gelita-pustaka.xlsx`); workbook bank soal tidak memilikinya, jadi impornya tidak pernah menyentuh Pustaka |

Pemetaan kunci ringkas → `answer_key_json` (`ContentImportService::answerKeyFor()`):

| interaction_type | Isi `answer_id` / `answer_en` | Disimpan sebagai |
|---|---|---|
| `puzzle_arrange` | kosong (dibuat otomatis: keping 0 … n² − 1 menurut papan tingkat wilayah) | `{"order":[0,1,…]}` + `config_json.grid`/`shape` dibekukan |
| `fill_blank_bank` | satu kata/frasa, mis. `Sumbing` | `{"text_id":…,"text_en":…}` |
| `verdict_card` | `benar` \| `salah` (disimpan huruf kecil) | `{"verdict":…}` + `config_json.skill` |
| `find_object` | `target` atau `decoy` | `{"target":true\|false}` + `config_json.x/y/w/decoy/wrong_feedback_*` |

Jenis soal di luar `Config\Gelita::$interactionTypes` ditolak.

### Aturan bank soal (`App\Libraries\BankRules`, `App\Libraries\DifficultyRules`)

Satu sumber aturan untuk skrip penyusun workbook, importer, `ContentVerifier`, dan uji `BankKelas5DataTest`.

Galat (impor ditolak / verifikasi gagal):

- tiap wilayah tepat 4 tantangan aktif berurutan `puzzle`, `rumpang`, `boleh`, `cari`; satu jenis soal per tantangan;
- Susun Gambar, Isi Rumpang, Benar atau Salah: tepat 3 variasi aktif; Cari Objek ≥ 1 variasi;
- Susun Gambar: 1 soal bergambar per variasi;
- Isi Rumpang: jumlah rumpang 3/5/7; tepat satu `___` per potongan di ID dan EN; potongan tidak diawali `___`; jawaban unik dalam variasi; jumlah pengecoh = `distractor_count` (0/2/2) dan tidak sama dengan jawaban;
- Benar atau Salah: satu bacaan per variasi dengan 1/2/3 paragraf; 3/5/6 pernyataan; kunci hanya `benar`/`salah`; setiap pernyataan punya keterampilan sah;
- Cari Objek: 4 target + 4/6/8 pengecoh per variasi; setiap pengecoh punya umpan balik ID; semua benda bergambar; posisi di dalam 0–100 % dan tidak saling tumpuk lebih dari 20 % luasnya;
- `bloom_level` wajib untuk rumpang, pernyataan, dan target Cari Objek; `item_key` unik.

Peringatan: Bloom di luar rentang tingkat; variasi tanpa pernyataan `benar` atau tanpa `salah`.

`ContentVerifier` menambah pemeriksaan database: 3 wilayah aktif, kunci soal dinilai, bacaan dari wilayah lain, soal "perlu dicek" (`review_status = needs_verification`), media yang dipakai tetapi belum berberkas, jawaban rumpang kembar antartantangan, kode soal kembar, dan **gambar contoh yang masih dipakai tantangan** — peringatan biasa, tetapi **galat** dengan `--siap-penelitian`. Kode keluar command: 1 bila ada galat (atau peringatan dengan `--strict`); `--siap-penelitian` yang gagal mencetak "Belum siap untuk pengambilan data penelitian".

## FITUR 12b: Gambar Contoh (Mockup) dan Penggantiannya

Agar permainan dapat diuji ujung ke ujung sebelum gambar asli tersedia, setiap slot gambar tantangan yang dipakai konten aktif diisi gambar contoh SVG buatan sistem (`App\Libraries\MockupGenerator`). Bank kelas 5 punya **45 slot**: 9 gambar Susun Gambar, 3 gambar pernyataan, 3 adegan Cari Objek, dan 30 benda.

```text
MEMBUAT
1. KontenKelas5Seeder (instalasi), gelita:reset (kecuali --tanpa-mockup), atau
   php spark gelita:mockups [--dry-run] [--paksa] [--node=tmg-4]
2. MockupGenerator::slots(): slot dari challenge_nodes.scene_media_id dan
   challenge_items.media_asset_id aktif; jenisnya dari pola kunci:
     challenge.*.scene      → adegan  1280 × 720 (alas di bawah setiap posisi benda)
     challenge.item.*-1-*   → Susun Gambar 1200 × 1200 (tanpa angka urutan)
     challenge.item.*-4-*   → benda Cari Objek 240 × 240 transparan
                              (target dan pengecoh bergaya identik)
     challenge.item.*-3-*   → gambar pernyataan 480 × 360
   ukuran = Config\Gelita::$assetSizes; label/keterangan dari media_assets.label
   dan description; pita "GAMBAR CONTOH"; palet per wilayah
3. Berkas ditulis ke public/assets/uploads/mockup/{asset_key}.svg, didaftarkan
   MediaStore::registerFile(), lalu baris media_assets ditandai
   is_placeholder = 1, is_active = 1, credit gambar contoh
4. Gambar asli (is_placeholder = 0 dan berkasnya ada, aktif maupun dinonaktifkan)
   TIDAK PERNAH ditimpa, juga dengan --paksa; --paksa hanya membuat ulang mockup
5. Satu audit 'media_mockups' per jalankan yang menulis berkas; command mencetak
   tabel per slot (dibuat / dibuat ulang / mockup sudah ada / gambar asli,
   dipertahankan / GAGAL), kode keluar 1 bila ada yang gagal atau --node tidak dikenal

MENGGANTI DENGAN GAMBAR ASLI
6. Admin membuka Gambar & suara (/admin/media) atau form soal/tantangan
   (FITUR 12) dan mengunggah berkas dengan kode yang sama (mis.
   challenge.item.tmg-4-03); ukuran harus sesuai slot
7. MediaStore menyimpan berkas dengan nama resmi, menandai baris
   is_placeholder = 0, membuang kredit gambar contoh, dan menghapus berkas
   mockup lama; permainan langsung memakai gambar asli tanpa impor ulang
8. Sebelum pengambilan data: php spark gelita:content:verify --siap-penelitian
   harus lolos (gambar contoh yang masih dipakai tantangan = galat)
```

Gambar contoh menuliskan nama benda, jadi Cari Objek dan pernyataan bergambar **belum layak** untuk pengambilan data sampai gambar asli dipasang. Daftar ukuran dan isi gambar asli ada di sheet `DAFTAR_MEDIA` workbook bank soal (isi yang sama tersimpan di `media_assets.label` dan `description`). `MediaAssetModel::placeholderCount()` dan `ContentVerifier::placeholdersInUse()` menghitung gambar contoh yang tersisa.

---

## FITUR 13: Media dan Audio

```text
GAMBAR / VIDEO (/admin/media, admin, MediaController)
1. Tabel aset: kunci, jenis, ukuran wajib vs sebenarnya, status, dan
   "Dipakai di" (App\Libraries\MediaUsage):
   tantangan, soal, bacaan, wilayah, halaman Pustaka, atau slot tampilan
2. Seret berkas ke baris aset → media-upload.js membaca dimensi di klien
   (ukuran tidak cocok → ditolak dengan pesan spesifik)
3. POST /admin/media/unggah → App\Libraries\MediaStore::store():
     MIME dibaca dari isi berkas (detectMime), ekstensi, ukuran
     (Config\Gelita::$maxUploadBytes); getimagesize() memverifikasi ulang dimensi
     (angka dari browser tidak dipercaya); slot berukuran wajib ditolak bila lain
     simpan ke public/assets/uploads/{asset_key}.{ext} (nama resmi)
     sha256, file_size, dimensi; upsert media_assets (is_placeholder = 0)
     audit 'media_upload'
4. Nonaktifkan → POST /admin/media/{id}/nonaktif, audit 'media_deactivate'
5. Pindai → POST /admin/media/pindai (sama dengan php spark gelita:media:scan):
   App\Libraries\MediaIntegrity membandingkan aset aktif dengan berkas (hilang,
   ukuran, SHA-256) dan berkas unggahan tanpa baris; audit 'media_scan'
6. Kelengkapan aset → /admin/media/kelengkapan (App\Libraries\AssetChecklist):
   slot tanpa berkas, frame pose tokoh, latar & peta wilayah, poster video
   Pustaka, rekaman narasi

AUDIO (/admin/media/audio)
7. POST /admin/media/audio/unggah: locale, tokoh, konteks, transcript (WAJIB),
   production_method → status awal 'draft' (tidak dikirim ke pemain);
   unggah ulang kunci yang sama → kembali 'draft'; audit 'audio_upload'
8. Admin memutar pratinjau → Setujui (POST /admin/media/audio/{id}/setujui):
   approval_status 'approved', approved_by, approved_at, audit 'audio_approve'
9. Baru setelah itu audio_src() mengembalikan path dan pemain mendengarnya
```

Unggahan dari editor konten memakai jalur `MediaStore` yang sama. Audio dialog, narasi pembuka kartu misi (`challenge_nodes.audio_intro_*`), dan narasi petunjuk dipilih dari audio yang sudah diunggah. `MediaStore::durationMs()` membaca durasi WAV dan MP3 untuk `audio_assets.duration_ms`. `php spark gelita:media:scan [--check-only]` lebih dulu menjalankan `MediaAssetSeeder` (idempoten, mendaftarkan slot; dilewati dengan `--check-only`) lalu `MediaIntegrity`; kode keluar 1 bila ada temuan.

---

## FITUR 13a: Narasi — Rekaman, Impor, dan Persetujuan

Seluruh layar cerita bernarasi memakai **100 rekaman per bahasa**: 88 baris naskah (`dialogues`) + 12 petunjuk Cari Objek. Rekaman bawaan (suara sintetis) ada di `public/assets/audio/narasi/{id,en}/` beserta manifest `produksi.json`; cara membuat dan memeriksanya ada di [`docs/audio.md`](audio.md).

```text
 1. Daftar rekaman: GET /admin/konten/narasi/daftar-rekaman (XLSX: kode berkas,
    konteks, wilayah, tokoh, pose, efek, judul, teks ID/EN, status);
    audit 'narration_list_download'
 2. Nama berkas = kode baris naskah (NarrationImporter::PREFIXES):
      intro-NN → intro · peta-NN → map_intro · kenal-{wilayah}-NN → region_intro
      dialog-{wilayah}-NN → level_open · tuntas-{wilayah}-NN → level_done
      penutup-NN → ending · petunjuk-{node}-NN → petunjuk Cari Objek
 3. Sumber berkas:
      folder public/assets/audio/narasi/{id|en}/ → php spark gelita:narration:import
        [--locale=id|en] [--dry-run], atau tombol "Impor dari folder"
        (POST /admin/konten/narasi/impor-folder); berkas didaftarkan di tempatnya
      panel: POST /admin/konten/narasi/unggah (banyak berkas, satu bahasa);
        berkas disalin ke public/assets/uploads/ lewat MediaStore
 4. NarrationImporter, per berkas:
      cocokkan nama → baris dialogues aktif (kode wilayah dari tabel levels)
      isi sama persis dengan rekaman terpasang → dilewati (persetujuan tetap)
      rekaman panel yang lebih baru daripada berkas folder → tidak ditimpa
      selain itu:
        media_assets  audio.narasi.{locale}.{kode}
        audio_assets  locale, character_code (narator = NULL), context_code =
                      konteks baris, transcript = teks baris bahasa itu,
                      duration_ms, production_method (tts bila isinya sama dengan
                      manifest produksi.json, selain itu own_recording), 'draft'
        dialogues.audio_{locale}_asset_id ← audio_assets.id
      cache konten di-flush; audit 'narration_import'
 5. Laporan: baru, diganti, sama, dilewati, nama tidak dikenal (+ saran nama
    terdekat), gagal, baris naskah yang belum punya rekaman
 6. /admin/konten/narasi (NarrationController::index, NarrationCatalog): baris per
    kelompok, kemajuan, dengarkan draft → "Setujui semua rekaman" ID|EN
    (POST /admin/konten/narasi/setujui) → NarrationCatalog::approveDrafts():
    hanya audio narasi berstatus draft pada bahasa itu (tertaut ke dialogues
    konteks naskah, berkunci audio.narasi.{locale}.*, atau dipilih sebagai
    narasi petunjuk), approved_by/approved_at, audit 'audio_approve_bulk'
 7. audio_src() kini mengembalikan rekaman itu; narrator.js memutarnya dan slide
    maju sendiri setelah audio selesai
```

Instalasi baru: `php spark gelita:narration:import`, lalu **Setujui semua rekaman** untuk bahasa Indonesia dan Inggris. Rekaman yang berubah kembali ke draft dan wajib didengar ulang sebelum tayang. Jenis berkas dibaca dari isinya: berkas yang bukan audio ditolak walau namanya `.mp3`. Narasi kartu misi dan audio lain tetap disetujui satu per satu di halaman Audio (FITUR 13).

### Narasi petunjuk arena `cari`

```text
1. Nama berkas petunjuk-{node}-NN.mp3, mis. petunjuk-tmg-4-01.mp3
     {node} = node_ref() setiap node `cari` aktif (tmg-4, mgl-4, wnb-4)
     NN     = urutan petunjuk target (find_object aktif, dinilai, bukan pengecoh)
              menurut challenge_items.sequence lalu id, mulai 01
     benda pengecoh tidak punya petunjuk → tidak punya kode berkas
2. NarrationImporter::match(): node bukan Cari Objek aktif atau NN di luar jumlah
   target → "nama tidak dikenal" + saran
3. media_assets audio.narasi.{locale}.petunjuk-{node}-NN
   audio_assets  context_code = 'hunt_clue', character_code = 'mbah_kedu',
                 transcript = prompt_{locale} soal, 'draft'
   challenge_items.audio_prompt_id | audio_prompt_en_id ← audio_assets.id
4. Halaman Narasi: kelompok petunjuk per node Cari Objek, tautan Sunting ke form
   soal; ikut kemajuan, daftar rekaman, persetujuan massal, dan Kelengkapan aset
5. Payload Cari Objek: clues[i].audio = audio_src(audio_prompt_{locale}) (hanya
   approved + berkas aktif); audio_id hanya dikirim bersama audio, jadi rekaman
   draft tidak terlihat di sumber halaman
6. engines/cari.js: petunjuk pertama hanya lewat tombol ▶ (action `play`);
   setelah jawaban benar petunjuk berikutnya diputar sendiri (`autoplay`) bila
   suara aktif; ganti atau lompat petunjuk → `pause`; lompat tidak memutar otomatis
```

Form soal Cari Objek juga dapat memilih audio petunjuk secara manual (ID/EN). Nomor NN tidak melekat pada soal: menonaktifkan, menyisipkan, atau mengurutkan ulang target menggeser nomor sesudahnya, jadi setelah perubahan seperti itu cocokkan ulang daftar rekaman. Workbook bank soal tidak membawa kolom audio.

**Catatan peneliti (telemetry).** Pemutaran narasi petunjuk dicatat di `audio_usage_events` dengan `challenge_attempt_id` pengerjaan Cari Objek yang sedang berjalan. Tabel itu tidak punya kolom soal: soalnya diturunkan dari `audio_asset_id` → `challenge_items.audio_prompt_id` / `audio_prompt_en_id` (atau dari asset_key `audio.narasi.{locale}.petunjuk-{node}-NN`), dengan `audio_assets.context_code = 'hunt_clue'`. Karena `challenge_attempts.audio_use_count` bertambah untuk **setiap** event audio (`play`, `autoplay`, `pause`, `replay`, `complete`), bandingkan pemakaian suara dari `audio_usage_events` (mis. hanya `action IN ('play','autoplay','replay')`), bukan dari angka mentah itu.

## FITUR 14: Ekspor Data Mentah (XLSX dan CSV-ZIP)

Isi ekspor adalah **data mentah** tujuh dimensi (ketepatan, percobaan, waktu, pengerjaan, progres, navigasi, bantuan) ditambah perangkat: satu baris = satu rekaman apa adanya, tanpa skor, persentase, indeks, peringkat, atau ringkasan demografi (Dok 3 §9). Query setiap lembar ada di `App\Services\RawDataService`, yang juga dipakai tabel panel `/admin/data/{dimensi}` dan CSV per tabel, sehingga isi tabel, CSV, dan ekspor selalu sama.

### Alur

```text
1. Guru/admin membuka /admin/ekspor (ExportController::index): formulir + 20
   permintaan terakhir (guru hanya miliknya sendiri)
2. Memilih filter (ExportService::FILTER_KEYS: studi, wilayah, tantangan,
   sekolah, provinsi, bahasa, rentang tanggal), lembar, nilai berlabel atau
   berkode, dan mode anonim
     guru → anonim selalu menyala; lembar Event Mentah tidak tersedia
   Dari /admin/peserta/{id}: participant_id → ekspor per siswa (§9.3)
3. POST /admin/ekspor/xlsx | /admin/ekspor/csv → ExportController::queue()
     filter dari POST dipaksa ke sekolah guru (scopedFilters, lapis kedua)
     insert data_exports (format xlsx|csv, status 'running',
       scope_json {filters, sheets, labels, participant_id?}, anonymized)
4. ExportService::build($exportId) — sinkron di request yang sama (batas 300 detik)
     context(): hak pemohon DIBACA ULANG dari staff_users (lapis ketiga) →
       StaffScope: akun nonaktif → gagal; guru → school_id sekolahnya,
       anonim, tanpa lembar event, tanpa kolom answer_key
     lembar event/navigasi/selesai diminta → hitung dulu; > Config\Gelita::
       $exportMaxRawEvents (200.000) → gagal dengan pesan "persempit rentang"
     setiap lembar: RawDataService::rows() (keyset per 2.000 baris)
     xlsx: ExcelWriter → writable/exports/{id}-{Ymd-His}.xlsx
     csv:  satu {lembar}.csv per lembar → ZipArchive → {id}-{Ymd-His}.zip
5. Selesai: data_exports.file_sha256, row_count (baris data tanpa Kamus),
   status 'done', expires_at = sekarang + exportRetentionDays (7 hari)
   Gagal: status 'failed' + error_message; berkas setengah jadi dihapus
6. Audit 'export' {format, anonymized, sheets, labels, participant_id,
   status, row_count, file_sha256} — satu baris per permintaan
7. Kembali ke /admin/ekspor (atau profil siswa): "Berkas #n siap diunduh"
   atau alasan gagal; export.js mem-polling /api/admin/exports/{id}/status
8. GET /admin/ekspor/unduh/{id}
     hanya pemilik atau admin; belum kedaluwarsa; berkas harus berada di dalam
     writable/exports/ (realpath); audit 'export_download'
```

### Format dan nilai

- **XLSX**: satu workbook, satu lembar per pilihan, `ExcelWriter` bertahap. Bawaan **berlabel**.
- **CSV-ZIP**: satu CSV per lembar (`peserta.csv`, …), UTF-8 dengan BOM, pemisah koma, siap SPSS/R/Python. Bawaan **berkode**.
- **Berkode**: judul kolom = kode kolom; nilai = kode tersimpan (`tries_exhausted`, `laki-laki`), boolean 1/0, jawaban dan kunci sebagai JSON tersimpan, payload JSON apa adanya.
- **Berlabel**: judul kolom Indonesia bersatuan ("Durasi (mdtk)"); kode → label ("Kesempatan habis", "Laki-laki", "ya"/"tidak"), wilayah → nama, jawaban dan kunci → kalimat (`AnswerText`).
- Di kedua mode: waktu = ISO 8601 zona Asia/Jakarta dengan milidetik (`2026-10-07T08:30:15.123+07:00`), durasi dalam milidetik. Tidak ada konversi ke detik/menit.
- **Kamus Data** (lembar `kamus`) selalu ikut: satu baris per lembar dan per kolom (lembar, kolom, judul, arti, satuan, nilai berkode `kode = label`). Sumbernya `App\Libraries\ExportLabels`.
- **Anonim** (selalu untuk guru): `display_name`, `username`, `school_name`, `school_npsn` tidak dibuat (dan tidak dibaca dari database); `school_ref` (`SCH-000012`) tetap.

### Lembar

| Kode | Nama lembar | Kolom |
|---|---|---|
| `kamus` | Kamus Data | lembar, kolom, judul, arti, satuan, nilai |
| `peserta` | Peserta | participant_code, display_name\*, username\*, school_name\*, school_npsn\*, school_ref, age, gender, class_level, country, province, district, registered_at, assented_at, intro_seen_at, pw_first_submit_criteria, pw_weak_submit_count |
| `sesi` | Sesi | session_code, participant_code, study_code, release_code, locale, status, started_at, last_active_at, ended_at, duration_ms, device_type, os_name, browser_name, screen_size, is_touch |
| `perangkat` | Perangkat | session_code, participant_code, via, created_at, device_type, device_brand, device_model, os_name, os_version, os_platform, browser_name, browser_version, browser_engine, is_bot, screen_w, screen_h, viewport_w, viewport_h, pixel_ratio, touch_points, orientation, is_standalone, language, timezone, corrected, detector, user_agent, client_hints |
| `progres` | Progres | session_code, participant_code, current_level, current_node_ref, unlocked_level_sequence, completed_nodes, total_nodes, completed_levels, total_stars, updated_at |
| `pengerjaan` | Pengerjaan Tantangan | attempt_id, session_code, participant_code, level, node_ref, node_sequence, engine_type, variant_group, variant_title, attempt_no, status, completion_reason, scorable_items, first_pass_correct, final_correct, check_count, max_tries, hint_count, answer_change_count, audio_use_count, stars, started_at, completed_at, duration_ms |
| `jawaban` | Jawaban Soal | attempt_id, session_code, participant_code, level, node_ref, variant_group, item_key, interaction_type, bloom_level, skill, indicator, prompt, display_order, status, try_count, first_answer, final_answer, answer_key\*\*, first_pass_correct, is_correct, solved_on_try, change_count, hint_used, wrong_click_count, started_at, answered_at, duration_ms |
| `percobaan` | Riwayat Percobaan | attempt_id, session_code, participant_code, node_ref, variant_group, item_key, try_no, check_no, answer, is_correct, hint_opened_before, first_input_ms, last_input_ms, occurred_at |
| `navigasi` | Navigasi | session_code, participant_code, sequence_no, occurred_at, server_received_at, event_type, screen, from_screen, level, node_ref, attempt_id, payload |
| `bantuan` | Bantuan | session_code, participant_code, occurred_at, jenis (`petunjuk` \| `audio`), level, node_ref, attempt_id, item_key, hint_id, asset_key, audio_context, audio_locale, action, play_index, listened_ms, completed |
| `selesai` | Penyelesaian | session_code, participant_code, occurred_at, event_type, level, node_ref, attempt_id, payload |
| `event` | Event Mentah (admin) | id, event_uuid, client_event_id, session_id, session_code, participant_id, participant_code, level_id, challenge_node_id, challenge_attempt_id, challenge_item_id, event_type, sequence_no, occurred_at, server_received_at, payload_json |
| `masukan` | Kritik & Saran | participant_code, session_code, rating, liked_most, hardest_part, new_learning, suggestion, submitted_at |
| `butir` | Bank Soal | item_key, level, node_ref, variant_group, variant_title, sequence, interaction_type, bloom_level, skill, indicator, prompt, passage_key, answer_key\*\*, decoy, scorable, is_active, media_asset_key |

\* hanya ekspor beridentitas (admin, anonim dimatikan). \*\* hanya admin.

Navigasi = event `screen_viewed`, `overlay_opened`, `dialogue_advanced`, `library_opened`, `library_page_viewed`, `locale_changed`, `page_hidden`, `page_visible`, `viewport_changed`, `reading_progress`. Penyelesaian = `challenge_completed`, `challenge_abandoned`, `challenge_tries_exhausted`, `clue_failed`, `level_completed`, `session_completed`, `intro_completed`, `feedback_submitted`. Bantuan menggabungkan event `hint_opened` dan setiap baris `audio_usage_events`. Event yang di-soft-delete (FITUR 16) tidak diekspor.

**Filter.** Rentang tanggal memakai waktu baris itu sendiri: tanggal daftar (Peserta), mulai sesi (Sesi, Progres), waktu catatan (Perangkat), mulai pengerjaan (Pengerjaan, Jawaban), waktu percobaan, waktu event, waktu kirim (Kritik & Saran). Wilayah dan tantangan berlaku pada lembar berbasis tantangan dan event; Peserta, Sesi, Perangkat, Progres, dan Kritik & Saran tidak terpengaruh. Bank Soal tidak bergantung siswa dan tidak dibatasi sekolah.

**CSV per tabel.** Setiap tabel `/admin/data/{dimensi}` punya tombol Unduh CSV (`?format=csv`, `BaseAdminController::rawCsvDownload()` → `RawDataService::streamCsv()`): baris dan filter sama dengan tabel, nilai berkode, audit `export_csv`.

`password_hash`, `failed_login_count`, `locked_until`, dan `ip_hash` tidak pernah dibaca ekspor. Data perangkat bukan identitas, jadi tetap ada di ekspor anonim.

### `ExcelWriter` — menulis bertahap

Lembar Event Mentah bisa ratusan ribu baris, jadi workbook tidak pernah dibangun di memori. `App\Libraries\ExcelWriter` menulis setiap lembar langsung sebagai XML SpreadsheetML ke berkas sementara (buffer di-flush berkala), lalu merangkainya menjadi paket XLSX dengan `ZipArchive`. Teks ditulis sebagai inline string, sehingga nilai berawalan `=`/`+`/`-`/`@` tidak pernah menjadi rumus.

```php
class ExcelWriter
{
    public function __construct(string $path);
    public function startSheet(string $name, array $headers, array $widths = []): void;
    public function row(array $values): void;
    public function feed(\Closure $generator): void;
    public function rowCount(): int;
    public function finish(): string;   // sha256 berkas
    public function abort(): void;      // buang berkas sementara saat gagal
}
```

Query dibaca per potongan 2.000 baris dengan keyset (`WHERE id > :terakhir ORDER BY id LIMIT 2000`), bukan hasil unbuffered: portabel antardriver dan tidak menahan koneksi selama lembar ditulis. PhpSpreadsheet hanya dipakai untuk **membaca** workbook (impor bank soal, uji).

---

## FITUR 15: Laporan PDF Satu Siswa

```text
1. Di /admin/peserta/{id}, tombol "Cetak laporan PDF" → POST /admin/ekspor/pdf
   {participant_id}
2. ExportController::queue('pdf'): tanpa participant_id → ditolak
   scope_json {filters, template: 'participant', participant_id}
3. ExportService::build() → ReportService::render():
     AnalyticsService::participantProfile() (terikat cakupan sekolah dan mode
     anonim pemohon; guru → gagal untuk siswa sekolah lain)
     view pdf/report-participant (setiap nilai lewat esc()) → mPDF (A4)
     → writable/exports/{id}-{Ymd-His}.pdf
4. Status, SHA-256, masa berlaku, audit, dan unduhan sama dengan FITUR 14
```

Isi laporan (Dok 3 §10) berupa tabel formal data mentah dengan gaya panel netral: identitas sesuai hak (anonim: kode siswa dan kode sekolah samaran), sesi bermain, perangkat, progres per tantangan aktif (status, variasi, jumlah pengerjaan, cek jawaban, Berhasil pada Percobaan Pertama *x* dari *y*, bintang, durasi dalam milidetik), serta kritik & saran. Tanpa skor, indikator, grafik, kalimat penilaian, atau bagian persetujuan. Laporan studi PDF tidak ada. Semua teks dari input pengguna di-escape sebelum masuk mPDF, karena mPDF memproses HTML.

---

## FITUR 16: Tata Kelola — Penghapusan Data dan Jejak Audit

Halaman `/admin/tata-kelola` (admin saja, `GovernanceController`) adalah satu-satunya jalur di aplikasi yang menghapus data penelitian. Logikanya ada di `App\Services\RetentionService`.

```text
1. Admin menentukan cakupan: tepat satu dari nomor siswa, nomor sesi, atau nomor
   penelitian (studi); cakupan studi dapat dipersempit dengan rentang tanggal
   mulai sesi. Memilih mode soft/hard dan menulis alasan.
2. POST /admin/tata-kelola/hapus/pratinjau
     RetentionService::normalizeScope() → preview($scope, $mode, $reason, $staffId):
       menghitung baris terdampak PER TABEL tanpa menghapus apa pun
       insert data_deletion_requests (status 'preview', affected_count)
       audit 'delete_preview'
3. Halaman menampilkan jumlah per tabel: game_event_logs, audio_usage_events,
   item_tries, item_responses, challenge_attempts, device_logs,
   session_progress, participant_feedback, game_sessions, participants
4. Admin mengetik HAPUS → POST /admin/tata-kelola/hapus/{id}/jalankan
     RetentionService::execute() — SATU TRANSACTION:
       jumlah dihitung ulang; bila menyimpang dari pratinjau lebih dari
       max(deletionDriftMinRows = 10, deletionDriftFraction = 10 % × pratinjau)
       → DIBATALKAN, admin diminta membuat pratinjau baru
       soft: game_event_logs.deleted_at/deleted_by/delete_reason untuk semua sesi
             dalam cakupan; participants.deleted_at bila cakupannya satu siswa
       hard: hapus dari anak ke induk (RetentionService::HARD_ORDER); baris
             participants hanya pada cakupan satu siswa
       data_deletion_requests: 'executed', approved_by, executed_at,
       affected_count final; audit 'delete_execute' dengan rincian per tabel
5. Pratinjau yang tidak jadi → POST /admin/tata-kelola/hapus/{id}/batal
   ('cancelled', audit 'delete_cancel')
```

Siswa yang di-soft-delete tidak dapat bermain lagi (filter area game menolak `participants.deleted_at`) dan tidak tampil di daftar panel maupun ekspor. Raw event tidak pernah dihapus karena agregatnya sudah dibuat; event hanya hilang lewat jalur ini.

**Jejak audit.** `/admin/tata-kelola/audit` menampilkan `audit_logs` (50 per halaman) dengan filter aksi dan rentang tanggal. `AuditLogModel::record()` menyimpan aksi, pelaku (`staff_user_id`, NULL untuk command CLI dan cron), target, metadata JSON, dan `ip_hash` (NULL bila bukan request web). Aksi yang ditulis kode sekarang:

| Kelompok | Aksi |
|---|---|
| Akun staf | `login`, `logout`, `login_failed`, `staff_create`, `staff_update`, `staff_deactivate`, `staff_password_reset`, `staff_password_change`, `staff_password_change_failed` |
| Siswa & sekolah | `participant_password_reset`, `school_merge`, `school_verify` |
| Konten & media | `content_update`, `content_import`, `media_upload`, `media_deactivate`, `media_scan`, `media_mockups`, `audio_upload`, `audio_approve`, `audio_approve_bulk`, `narration_import`, `narration_list_download` |
| Studi | `study_create`, `study_update`, `release_create`, `release_activate` |
| Data | `export`, `export_download`, `export_purge`, `delete_preview`, `delete_execute`, `delete_cancel`, `retention_run`, `system_reset` |

---

## FITUR 17: Retensi Terjadwal

```bash
php spark gelita:retention:run
```

Dijalankan cron harian; tombol "Jalankan retensi sekarang" di `/admin/tata-kelola` (`POST /admin/tata-kelola/retensi`) menjalankan `RetentionService::run()` yang sama.

```text
1. Sesi 'active' yang last_active_at lebih lama dari sessionIdleMinutes (45)
     → 'paused' (GameSessionModel::markStale; siswa masih dapat melanjutkan)
2. Pengerjaan 'in_progress' yang tidak tersentuh > attemptAbandonHours (24 jam)
     sentuhan terakhir = MAX(started_at, jawaban terakhir, event terakhir pengerjaan itu)
     → abandonAttempt(): 'abandoned', bintang 0,
       event challenge_abandoned {via: 'retention', idle_hours, last_touch}
3. Sesi 'paused' yang tidak tersentuh > sessionAbandonDays (30 hari)
     → 'abandoned', event session_abandoned {via: 'retention', idle_days, last_active_at}
4. data_exports kedaluwarsa (ExportService::purgeExpired)
     → berkas dihapus, file_path dikosongkan, audit 'export_purge'
5. Data studi yang melewati research_studies.retention_days
     → TIDAK dihapus otomatis. Satu pratinjau penghapusan per studi:
       scope {study_id, date_to = hari ini − retention_days − 1}, mode hard,
       scope_json.origin = 'retention'. Run berikutnya MEMPERBARUI pratinjau yang
       masih menunggu, bukan membuat baru. Pemiliknya admin yang menekan tombol,
       atau admin aktif pertama bila dijalankan cron. Beranda admin menampilkan
       peringatannya; eksekusi tetap lewat FITUR 16 dengan konfirmasi manusia.
6. Ringkasan ditulis ke audit_logs ('retention_run')
```

Langkah 5 disengaja. Retensi yang menghapus sendiri data penelitian tanpa seorang pun menyetujuinya adalah cara tercepat kehilangan data yang tidak dapat diulang. Sistem mengingatkan; manusia memutuskan.

## FITUR 18: Reset Sebelum Penelitian (`gelita:reset`)

Sesudah uji coba dan sebelum pengambilan data penelitian, satu perintah mengosongkan semua data uji coba dan pengaturan, lalu menyiapkan ulang 12 tantangan dan bank soal. Logika di `App\Services\SystemResetService`, command `App\Commands\SystemReset`. Aman diulang.

```bash
php spark gelita:reset --dry-run
php spark gelita:reset [--ya] [--paksa] [--pemeliharaan] [--tanpa-bank] [--bank=JALUR] [--tanpa-mockup] [--hapus-guru]
```

Di server, jalankan dengan akun migrasi (`database_default_DSN` akun `gelita_migrate`, tanpa port di DSN) karena `TRUNCATE` butuh hak DROP; rinciannya di [08_DEPLOYMENT.md](08_DEPLOYMENT.md). Akun yang hanya punya hak DML ditolak dengan pesan yang jelas, sebelum ada tabel yang dikosongkan.

| Kelompok | Tabel | Tindakan |
|---|---|---|
| A. Data penelitian & uji coba | `participants`, `game_sessions`, `session_progress`, `challenge_attempts`, `item_responses`, `item_tries`, `audio_usage_events`, `game_event_logs`, `device_logs`, `participant_feedback`, `data_exports`, `data_deletion_requests`, `audit_logs`, `ci_sessions` | dikosongkan (semua siswa dan staf ter-logout); berkas `writable/exports/*` ikut dihapus |
| B. Konten tantangan | `hints`, `challenge_items`, `reading_passages`, `challenge_nodes` | dikosongkan lalu dibangun ulang: 12 tantangan (`ContentSeeder::seedNodes()`) + impor workbook bank soal kelas 5 |
| C. Pengaturan | `research_studies`, `game_releases`, `learning_indicators` | dikosongkan lalu diisi nilai bawaan seeder |
| D. Dibersihkan sebagian | `schools`, `staff_users` | sekolah belum terverifikasi dihapus (`staff_users.school_id` yang menunjuknya di-NULL-kan); akun staf tetap dan kunci loginnya dibuka; `--hapus-guru` menghapus semua akun guru (admin tidak pernah) |
| E. Tidak disentuh | `levels`, `dialogues`, `library_pages`, `library_media`, `media_assets`, `audio_assets`, `migrations`, berkas di `public/assets/` | — |

```text
 1. Pra-pemeriksaan: koneksi database; migration terakhir ≥ 2026-01-01-002900
    (bila belum: "jalankan php spark migrate dulu"); tolak bila ada sesi 'active'
    yang bergerak dalam 10 menit terakhir (kecuali --paksa); --bank yang tidak ada
    → berhenti tanpa perubahan
 2. Rencana per tabel (plan()) ditampilkan; --dry-run berhenti di sini
 3. Konfirmasi: ketik persis RESET-KELAS5 (kecuali --ya); salah → kode keluar 1
 4. --pemeliharaan: writable/pemeliharaan.flag selama reset, dihapus di akhir
    walau gagal
 5. Kosongkan kelompok A–C: TRUNCATE dengan FOREIGN_KEY_CHECKS = 0, sehingga
    AUTO_INCREMENT kembali ke 1 dan kode siswa berikutnya GLT-000001
 6. Bersihkan kelompok D
 7. Isi ulang bawaan: LearningIndicatorSeeder, GameReleaseSeeder,
    ResearchStudySeeder, ContentSeeder::seedNodes() (hanya 12 tantangan; cerita
    dan Pustaka tidak disentuh)
 8. Impor docs/bank-soal/gelita-bank-soal-kelas5.xlsx (atau --bank) lewat
    ContentImportService::import(), kecuali --tanpa-bank
 9. Gambar contoh untuk slot yang masih kosong (MockupGenerator::generateMissing()),
    kecuali --tanpa-mockup; gambar asli dipertahankan
10. Bersihkan cache (cache()->clean(), ContentRepository::flush()) dan berkas ekspor
11. Verifikasi ContentVerifier; galat → kode keluar 1 dengan langkah perbaikan
    (impor ulang bank soal, lalu gelita:content:verify)
12. Satu baris audit 'system_reset' (staff_user_id NULL): jumlah baris per tabel
    yang dikosongkan, opsi, nama host, sha256 dan status workbook
13. Ringkasan: bank soal (soal, dinilai, variasi, bacaan), gambar contoh (dibuat,
    sudah ada, gambar asli dipertahankan), verifikasi, kode siswa berikutnya
```

Sesudah reset, ganti gambar contoh dengan gambar asli (FITUR 12b) dan pastikan `php spark gelita:content:verify --siap-penelitian` lolos sebelum kelas pertama.

---

## Ringkasan Transaction Boundary

| Operasi | Yang dibungkus satu transaction |
|---|---|
| Pendaftaran siswa | sekolah ketikan (bila baru) + `participants` (akun, metrik sandi, `assented_at`) + `game_sessions` + `session_progress` + `device_logs` + event |
| Sesi baru saat masuk | `game_sessions` + `session_progress` + event |
| Buka pengerjaan | `challenge_attempts` + `item_responses` placeholder + event |
| Cek jawaban (batch) | `challenge_attempts.check_count` + `item_responses` + `item_tries` + event + penutupan (bila selesai) |
| Ketukan benda (Cari Objek) | `item_responses` + `item_tries` + event + penutupan (bila semua petunjuk tertutup) |
| Penutupan pengerjaan | `challenge_attempts` (status, alasan, bintang) + `session_progress` + event `challenge_completed`/`level_completed` |
| Pakai petunjuk | `challenge_attempts.hint_count` + `item_responses.hint_used` + event |
| Event audio | `audio_usage_events` + `game_event_logs` + `challenge_attempts.audio_use_count` |
| Ingest event batch | insert batch (idempoten) |
| Impor workbook | slot media + `challenge_nodes` + `reading_passages` + `challenge_items` + `hints` (+ Pustaka) |
| Gabung sekolah | `participants` + `staff_users` + `schools` + audit |
| Eksekusi penghapusan | seluruh tabel terdampak + `data_deletion_requests` + audit |
| Retensi per pengerjaan/sesi | status + event |

Service memakai `transBegin()`/`transCommit()`/`transRollback()`; `EventService::record()` sengaja tidak membuka transaction sendiri agar ikut transaction pemanggil. Bila gagal, tidak ada yang setengah tersimpan. Sesi selesai (`completeIfFinished()`) dijalankan sesudah commit penutupan pengerjaan.

---

## Aturan Sistem

1. Benar/salah, kesempatan, bintang, serpihan, dan status buka/kunci ditentukan server tanpa kecuali; nilai `correct` dari browser selalu diabaikan.
2. Kunci jawaban, pemetaan bank kata, penanda pengecoh, dan umpan balik pengecoh tidak pernah ada di payload pemain; benda Cari Objek dikirim sebagai token HMAC per pengerjaan.
3. Setiap soal paling banyak 3 percobaan (`Config\Gelita::$maxTries`, disalin ke `challenge_attempts.max_tries`); setiap percobaan menulis satu baris `item_tries`.
4. Setiap operasi lintas tabel dibungkus satu transaction.
5. Setiap tindakan siswa yang bermakna menulis `game_event_logs`; setiap tindakan staf yang bermakna menulis `audit_logs`. `screen_viewed`, `challenge_tries_exhausted`, dan `clue_failed` hanya ditulis server.
6. Guru hanya melihat dan mengekspor data sekolahnya, selalu anonim, tanpa Catatan aktivitas dan kunci jawaban.
7. Raw event tidak dihapus karena agregatnya sudah dibuat; penghapusan data selalu dua langkah dengan konfirmasi teks dan tercatat.
8. Berkas ekspor berada di luar `public/`, punya SHA-256 dan masa berlaku.
9. Mengulang tantangan tidak menghapus catatan lama — setiap pengerjaan tersimpan sebagai data penelitian tersendiri.
10. Pustaka, petunjuk, dan audio tidak pernah memengaruhi bintang; semuanya dicatat sebagai perilaku belajar dan data bantuan.
11. Kata sandi siswa tidak pernah disimpan, dicatat, atau diekspor selain sebagai `password_hash`; metrik literasi keamanan digital hanya jumlah syarat dan jumlah penolakan.
12. IP mentah tidak pernah disimpan (hanya `ip_hash`); tidak ada sidik jari peramban.
13. Isi bank soal hanya masuk lewat impor workbook atau editor konten; impor workbook menolak perubahan kunci soal yang sudah dijawab, dan soal yang sudah dijawab tidak pernah dihapus (hanya dinonaktifkan).
14. Gambar asli tidak pernah ditimpa gambar contoh; pengambilan data dimulai hanya setelah `gelita:content:verify --siap-penelitian` lolos.

---

## Command GELITA

Semua opsi bernilai menerima `--opsi nilai` maupun `--opsi=nilai` (trait `App\Commands\Concerns\CliOptions`).

| Command | Perilaku | Kode keluar 1 bila |
|---|---|---|
| `gelita:bank:import FILE [--dry-run] [--staff USERNAME]` | pratinjau/impor workbook (FITUR 12a) | ada galat |
| `gelita:content:verify [--strict] [--siap-penelitian]` | `ContentVerifier`; cache konten di-flush dulu (FITUR 12a) | ada galat (atau peringatan dengan `--strict`) |
| `gelita:mockups [--dry-run] [--paksa] [--node=tmg-4]` | gambar contoh (FITUR 12b) | ada slot gagal atau `--node` tidak dikenal |
| `gelita:media:scan [--check-only]` | `MediaAssetSeeder` lalu `MediaIntegrity`; audit `media_scan` (FITUR 13) | ada temuan |
| `gelita:library:thumbnails [--force]` | poster video Pustaka (FITUR 8) | ada yang gagal |
| `gelita:narration:import [--locale=id\|en] [--dry-run]` | impor narasi dari folder (FITUR 13a); audit `narration_import` | ada berkas gagal atau `--locale` tidak dikenal |
| `gelita:schools:import [--file=…] [--dry-run] [--link-existing]` | daftar sekolah resmi (FITUR 10c) | impor gagal |
| `gelita:staff:password USERNAME` | sandi sementara staf (FITUR 10a) | nama pengguna kosong/tidak ditemukan |
| `gelita:retention:run` | `RetentionService::run()` (FITUR 17) | retensi melempar galat |
| `gelita:reset […]` | reset sebelum penelitian (FITUR 18) | pra-pemeriksaan/konfirmasi gagal atau verifikasi menemukan galat |

Command yang menulis waktu memanggil `db_sync_timezone()` sendiri, karena event `pre_system` hanya terpicu pada request web.

---

## Dependency

Dari **[01_DATABASE.md](01_DATABASE.md)**: seluruh tabel (29 migration, satu tabel per berkas) dan aturan integritas.

Dari **[02_PROJECT_FOUNDATION.md](02_PROJECT_FOUNDATION.md)**: konfigurasi `Config\Gelita`, filter, helper, folder `writable/exports/`.

Dari **[03_MODEL_ENTITY.md](03_MODEL_ENTITY.md)**: Model, Entity, Service, dan library `PasswordPolicy`.

Dari **[04_CONTROLLER_ROUTE.md](04_CONTROLLER_ROUTE.md)**: route, controller, otorisasi, bentuk request/response.

Dari **[05_VIEW_UI.md](05_VIEW_UI.md)**: view dan komponen.

Dari **[06_JAVASCRIPT.md](06_JAVASCRIPT.md)**: mesin tantangan, antrean event, sinyal perangkat, editor konten.

Dari **[`docs/bank-soal/`](bank-soal/README.md)**, **[`docs/pustaka/`](pustaka/README.md)**, **[`docs/naskah-cerita.md`](naskah-cerita.md)**, dan **[`docs/audio.md`](audio.md)**: isi bank soal kelas 5, Pustaka, naskah cerita, dan rekaman narasi.

Untuk **[08_DEPLOYMENT.md](08_DEPLOYMENT.md)**: instalasi baru (migrate, `DatabaseSeeder`, impor narasi), akun migrasi untuk `gelita:reset`, dan cron `gelita:retention:run`.

---

## Hasil Akhir

* Seorang siswa kelas 5 dapat mendaftar dalam tiga langkah dengan kata sandi kuat dan NPSN sekolahnya, masuk kembali dari komputer mana pun, menonton cerita pembuka, memainkan 12 tantangan di tiga wilayah berurutan dengan variasi acak dan 3 kesempatan per soal, mengumpulkan 12 Serpihan Cahaya, membaca Pustaka setiap wilayah yang tuntas, dan mengirim kritik & saran di Balai Refleksi.
* Setiap percobaan, perubahan jawaban, petunjuk, pemutaran suara, perpindahan layar, dan perangkat tercatat sebagai data mentah dengan stempel waktu presisi milidetik.
* Guru dapat mereset sandi siswa di sekolahnya, menelusuri sesi sampai setiap percobaan dan event, dan mengunduh XLSX anonim.
* Admin dapat memuat bank soal dari satu workbook dengan pratinjau galat per baris, mengganti gambar contoh dengan gambar asli, menyetujui narasi, mengelola studi, rilis, sekolah, dan akun staf, mengekspor data lengkap, serta menghapus data dengan pratinjau, konfirmasi, dan jejak audit.
* Satu perintah `gelita:reset` menyiapkan sistem bersih untuk pengambilan data penelitian; retensi terjadwal hanya mengingatkan, tidak pernah menghapus data penelitian sendiri.
* Koneksi yang sempat putus tidak menghilangkan event dan tidak menggandakan data.
