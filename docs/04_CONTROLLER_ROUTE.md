# 04_CONTROLLER_ROUTE.md — Route, Controller, Autentikasi, Otorisasi

Dokumen ini menggambarkan seluruh route GELITA, filter yang menjaganya, tanggung jawab tiap controller, dan kontrak JSON area `/api`. Sumber kebenarannya `app/Config/Routes.php`, `app/Config/Filters.php`, `app/Filters/`, dan `app/Controllers/`. Daftar lengkap yang terpasang dapat dilihat dengan `php spark routes`.

---

## Prinsip

Tiga area dengan karakter berbeda:

| Area | Prefix | Identitas | Respons |
|---|---|---|---|
| Permainan | `/` | `session('participant_id')` + `session('game_session_id')` setelah siswa masuk | HTML (server-rendered) |
| Panel admin | `/admin` | `session('staff_id')` setelah staf masuk | HTML |
| API | `/api`, `/api/admin` | sesi siswa (permainan) atau sesi staf (panel) | JSON |

* **Routing otomatis mati** (`$routes->setAutoRoute(false)` di `Routes.php` dan `Config\Routing::$autoRoute = false`). Hanya URL yang ditulis di `Routes.php` yang ada.
* **Grup route.** Area permainan memakai namespace `App\Controllers\Game`, panel `App\Controllers\Admin`, API `App\Controllers\Api`. Hak akses dipasang sebagai filter grup (`participantAuth`, `gameSession`, `apiSession`, `staffAuth`, `staffRole:admin`), bukan per method.
* **Siswa** mendaftar sendiri dengan nama pengguna dan kata sandi kuat (bagian dari materi literasi keamanan digital), lalu masuk di `/masuk`. `participant_code` (mis. `GLT-000123`) dibuat server sebagai kode pseudonim penelitian dan tidak dipakai untuk masuk. Sasaran pendaftaran: siswa kelas 5 SD/MI.
* **Staf** punya dua peran: `admin` dan `guru`. Akun staf (`staff_users`) dan akun siswa (`participants`) terpisah, begitu juga form masuknya (`/admin/login` dan `/masuk`).
* **CSRF** aktif untuk setiap POST/PUT/PATCH/DELETE, termasuk `/api` (`Config\Filters::$methods`). Token disimpan di session (`Config\Security`: `csrfProtection = 'session'`, nama field `gelita_csrf`, header `X-CSRF-TOKEN`, tanpa regenerasi per request).
* **Rate limit** memakai throttler CodeIgniter (cache): 120 request/menit per sesi permainan untuk endpoint tantangan dan event, 20 request/menit per hash IP untuk cek nama pengguna, 60 request/menit per hash IP untuk cek NPSN. Lihat [Respons API dan kode galat](#respons-api-dan-kode-galat).
* **HEAD** dilayani route GET dengan handler dan filter yang sama (`App\Libraries\HeadAsGetRouteCollection`, didaftarkan di `Config\Services::routes()`).

Pembagian kode:

```
Filter      = siapa boleh masuk (login, sandi sah, sesi aktif, peran)
Controller  = validasi input → panggil Service/Model → bentuk response (view, redirect, JSON)
Service     = aturan permainan dan data (ChallengeService, ScoringService, SessionService, …)
```

Tidak ada penilaian benar/salah, perhitungan bintang, atau query analitik di dalam controller. Status buka/kunci wilayah dan tantangan disusun trait `App\Controllers\Concerns\GameProgress` dari data yang sudah ditulis service (`session_progress`, `challenge_attempts`).

### Berkas controller

```text
app/Controllers/
├── BaseController.php                ok() / fail() / jsonBody() / deviceInfo()
├── Concerns/
│   ├── GameProgress.php              status wilayah & tantangan, lentera HUD, Pustaka, tirai wilayah
│   └── StaffScope.php                schoolScope(), readFilters(), scopedFilters(), analytics()
├── Game/
│   ├── BaseGameController.php        session(), participant(), hudData(), introGate(), dialogueGate(), toMap()
│   ├── HomeController.php            halaman awal, /mulai, ganti bahasa
│   ├── RegisterController.php        pendaftaran 3 langkah
│   ├── LoginController.php           masuk, keluar, ganti kata sandi siswa
│   ├── GateController.php            gerbang pemain lama, cerita pembuka
│   ├── MapController.php             peta Kedu & peta wilayah
│   ├── DialogueController.php        dialog pembuka wilayah, wilayah tuntas, penutup
│   ├── ChallengeController.php       kartu misi, layar tantangan, riwayat hasil, layar selesai
│   ├── LibraryController.php         rak Pustaka Kedu & Pustaka {wilayah}
│   ├── ProfileController.php         profil siswa
│   └── ReflectionController.php      Balai Refleksi (kritik & saran)
├── Admin/
│   ├── BaseAdminController.php       panel(), filterOptions(), rawPage(), csvResponse(), tableCsv(), back(), done(), modelErrors()
│   ├── AuthController.php            masuk/keluar staf
│   ├── AccountController.php         ganti sandi sendiri
│   ├── DashboardController.php       beranda panel
│   ├── ParticipantController.php     daftar & profil siswa, reset sandi siswa
│   ├── SessionController.php         daftar sesi, detail, catatan aktivitas
│   ├── FeedbackController.php        kritik & saran
│   ├── ExportController.php          unduh data mentah XLSX/CSV-ZIP & laporan PDF satu siswa
│   ├── ContentController.php         wilayah, tantangan, soal, impor bank soal, bacaan, Pustaka, dialog, verifikasi
│   ├── NarrationController.php       rekaman narasi naskah
│   ├── MediaController.php           gambar/video, audio, kelengkapan aset
│   ├── StudyController.php           penelitian & versi permainan
│   ├── PackageController.php         Rilis → Paket aset: package mode switch, batch build (asset package Stage 6)
│   ├── GovernanceController.php      penghapusan data, retensi, audit log
│   ├── SchoolController.php          direktori sekolah
│   └── StaffController.php           akun guru & admin
└── Api/
    ├── BaseApiController.php         gameSession(), attemptOrFail(), invalidPayload(), rateLimited(), serverTime()
    ├── AuthApiController.php         cek ketersediaan nama pengguna
    ├── SchoolApiController.php       cek NPSN
    ├── SessionApiController.php      bootstrap & siklus sesi
    ├── ContentApiController.php      wilayah, tantangan, Pustaka
    ├── ChallengeApiController.php    buka, jawab, cek, petunjuk, tutup, tinggalkan
    ├── EventApiController.php        batch raw event
    ├── AudioApiController.php        telemetri audio
    ├── ProgressApiController.php     progres authoritative
    └── AdminApiController.php        dataset JSON panel
```

---

## Filter

Alias di `Config\Filters::$aliases`; kelas aplikasi ada di `app/Filters/`.

| Alias | Kelas | Dipasang | Fungsi |
|---|---|---|---|
| `locale` | `LocaleFilter` | global, before | Menentukan bahasa: `?lang=id\|en` (GET non-API; disimpan ke session lalu redirect tanpa query) → `session('locale')` → `game_sessions.locale` sesi aktif → bawaan `id`. Panel (`/admin`, `/api/admin`) selalu `id`. The no-login API paths in `LocaleFilter::STATELESS_PATHS` (`/api/auth/username-available`, `/api/schools/lookup`, `/api/paket/aktif`) use the default `id` and never open the session, so their responses carry no `Set-Cookie`. |
| `secureheaders` | `CodeIgniter\Filters\SecureHeaders` | global, after | Header keamanan bawaan. |
| `csrf` | `CodeIgniter\Filters\CSRF` | semua POST/PUT/PATCH/DELETE | Token dari field `gelita_csrf` atau header `X-CSRF-TOKEN`. |
| `clientHints` | `ClientHintsFilter` | after, path `/`, `mulai`, `masuk`, `daftar`, `daftar/*` | Mengirim `Accept-CH` (`Config\Gelita::$clientHints`) agar peramban Chromium mengirim Client Hints entropi tinggi pada POST `/masuk` dan `/daftar/3` (`App\Libraries\DeviceProfile`, `device_logs`). |
| `participantAuth` | `ParticipantAuthFilter` | grup `/ganti-sandi`, `/keluar` | Siswa sudah masuk dan pesertanya masih ada (belum dihapus). Gagal → redirect `/masuk`. |
| `gameSession` | `GameSessionFilter` | grup layar permainan | `participantAuth` + `must_change_password = 0` + sesi permainan milik peserta dan berstatus selain `abandoned`; memuat `service('gameContext')`. Gagal: belum masuk → `/masuk` (GET menyimpan tujuan di flash `redirect_after_login`); wajib ganti sandi → `/ganti-sandi`; tanpa sesi → `/masuk`. |
| `apiSession` | `ApiSessionFilter` | grup API permainan | Aturan yang sama dengan `gameSession`, membalas JSON `401 INVALID_SESSION` atau `403 PASSWORD_CHANGE_REQUIRED`. |
| `screenView` | `ScreenViewFilter` | after, grup `participantAuth` dan `gameSession` | Mencatat event `screen_viewed` untuk setiap GET berstatus 200 `text/html` (kode layar, `path`, `from` = layar sebelumnya, parameter route). Redirect tidak dicatat; kegagalan pencatatan tidak menggagalkan halaman. |
| `jsonResponse` | `JsonResponseFilter` | after, grup `/api` dan `/api/admin` | `Content-Type: application/json` + header `X-Request-Id`. |
| `staffAuth` | `StaffAuthFilter` | grup panel dan `/api/admin` | Staf masuk dan akunnya aktif; peran, sekolah, dan nama di session disinkronkan dari `staff_users` setiap request. `must_change_password = 1` → hanya `/admin/akun/sandi` yang terbuka (halaman lain dialihkan ke sana, API `403 PASSWORD_CHANGE_REQUIRED`). Gagal masuk → `/admin/login` atau `401 INVALID_SESSION`. |
| `staffRole` | `StaffRoleFilter` | grup khusus admin (`staffRole:admin`) | Peran tidak cocok → HTML `404` (struktur panel tidak terpetakan), API `403 FORBIDDEN`. |
| `toolbar` | `App\Filters\DebugToolbar` | wajib (hanya aktif di development) | Debug Toolbar bawaan yang menyamarkan field kredensial (`pass`, `sandi`, `secret`, `token`) sebelum isi POST disimpan ke `writable/debugbar/`. |

Pemeriksaan siswa ditulis sekali di trait `App\Filters\ParticipantCheck` (status `OK` / `NO_LOGIN` / `MUST_CHANGE` / `NO_SESSION`) dan dipakai ketiga filter siswa. Kode layar `screen_viewed`: `gate`, `intro`, `map`, `region`, `dialogue`, `brief`, `challenge`, `finished`, `result`, `region_done`, `ending`, `library_index`, `library` / `library_locked`, `profile`, `reflection`, `change_password`.

---

## Route — permainan (HTML)

Namespace `App\Controllers\Game`. Filter global `locale` dan `secureheaders` berlaku di semua baris dan tidak diulang di kolom Filter. Semua POST juga melewati `csrf`.

### Tanpa login (termasuk pendaftaran)

| Method | Path | Handler | Filter | Keterangan |
|---|---|---|---|---|
| GET | `/` | `HomeController::index` | `clientHints` | Logo dan satu tombol Mulai → `/mulai` |
| GET | `/mulai` | `HomeController::start` | `clientHints` | Sudah masuk → redirect `/gerbang`; belum → pilihan "Saya baru" / "Sudah punya akun" |
| GET | `/daftar` | `RegisterController::index` | `clientHints` | Redirect ke langkah pertama yang belum lengkap |
| GET | `/daftar/{n}` | `RegisterController::step/$1` | `clientHints` | Langkah 1 Tentang kamu · 2 Sekolah dan daerah · 3 Akun rahasiamu |
| POST | `/daftar/{n}` | `RegisterController::saveStep/$1` | `clientHints` | Simpan langkah 1–2 ke session; langkah 3 membuat akun → redirect `/intro` |
| GET | `/masuk` | `LoginController::form` | `clientHints` | Form masuk siswa; sudah masuk → `/mulai` |
| POST | `/masuk` | `LoginController::login` | `clientHints` | Masuk + lanjutkan/buat sesi → tujuan tersimpan atau `/mulai`; wajib ganti sandi → `/ganti-sandi` |
| POST | `/bahasa` | `HomeController::setLocale` | — | Ganti bahasa → redirect ke `redirect_to` (path internal) |
| GET | `/siapkan` | `PackageController::prepare` | — | *(Asset package Stage 3.)* "Siapkan GELITA": package mode off → redirect to `?lanjut=` (`mulai`, `masuk`, `daftar`, `daftar/1–3`; anything else → `/mulai`); on → download screen |
| GET | `/periksa-paket` | `PackageController::inspect` | — | *(Asset package Stage 3.)* Teacher's "Periksa paket" for this device; also with package mode off |

### Sudah masuk, sandi belum tentu sah (`participantAuth`, `screenView`)

| Method | Path | Handler | Keterangan |
|---|---|---|---|
| GET | `/ganti-sandi` | `LoginController::changePasswordForm` | Form ganti sandi (wajib setelah reset oleh guru) |
| POST | `/ganti-sandi` | `LoginController::changePassword` | Simpan sandi baru, masuk otomatis → `/mulai` |
| GET | `/keluar` | `LoginController::logout` | Keluar → `/` |

### Bermain (`gameSession`, `screenView`)

`{code}` = kode wilayah (`temanggung`, `magelang`, `wonosobo`); `{seq}` = urutan tantangan 1–4 di wilayah itu.

| Method | Path | Handler | Keterangan |
|---|---|---|---|
| GET | `/gerbang` | `GateController::index` | Belum menonton cerita pembuka → `/intro`; selain itu pilihan pemain lama |
| GET | `/gerbang/peta` | `GateController::map` | Redirect `/peta` dengan flash `curtain=map` |
| GET | `/intro` | `GateController::intro` | Cerita pembuka; "Lewati" hanya bila sudah pernah menonton sampai selesai |
| GET | `/intro/selesai` | `GateController::finishIntro` | Isi `intro_seen_at` (sekali), event `intro_completed` → `/peta` |
| GET | `/peta` | `MapController::kedu` | Peta Kedu: tiga wilayah |
| GET | `/wilayah/{code}` | `MapController::level/$1` | Peta wilayah: empat tantangan |
| GET | `/dialog/{code}` | `DialogueController::show/$1` | Dialog pembuka wilayah (`level_open`) |
| GET | `/tuntas/{code}` | `DialogueController::done/$1` | Adegan wilayah tuntas (`level_done`) |
| GET | `/penutup` | `DialogueController::ending` | Penutup cerita (`ending`) setelah 12 tantangan tuntas |
| GET | `/misi/{code}/{seq}` | `ChallengeController::brief/$1/$2` | Kartu misi |
| GET | `/tantangan/{code}/{seq}` | `ChallengeController::play/$1/$2` | Layar tantangan (membuka atau melanjutkan attempt) |
| GET | `/hasil/{code}/{seq}` | `ChallengeController::result/$1/$2` | Riwayat pengerjaan selesai satu tantangan |
| GET | `/selesai/{attemptId}` | `ChallengeController::finished/$1` | Layar bintang satu pengerjaan |
| GET | `/pustaka` | `LibraryController::index` | Rak Pustaka Kedu |
| GET | `/pustaka/{code}` | `LibraryController::show/$1` | Buku Pustaka {wilayah} atau halaman terkunci |
| GET | `/profil` | `ProfileController::index` | Profil dan capaian sesi berjalan |
| POST | `/profil/avatar` | `ProfileController::avatar` | Ganti avatar (`avatar_no`: '' = huruf awal, atau nomor avatar yang sudah diunggah) → `/profil` + flash `Game.avatarSaved`; nomor lain → flash `error` |
| GET | `/refleksi` | `ReflectionController::index` | Balai Refleksi |
| POST | `/refleksi` | `ReflectionController::store` | Simpan kritik & saran → `/refleksi` |

Alur siswa baru: `/` → `/mulai` → `/daftar/1` → `/daftar/2` → `/daftar/3` → `/intro` → `/intro/selesai` → `/peta` → `/dialog/temanggung` → `/wilayah/temanggung` → `/misi/temanggung/1` → `/tantangan/temanggung/1` → API → `/selesai/{id}`. Attempt yang menuntaskan wilayah → `/tuntas/{code}` → wilayah berikutnya, atau di wilayah terakhir `/penutup` → `/refleksi`.

---

## Route — API permainan (JSON)

Namespace `App\Controllers\Api`, grup `api` dengan filter `jsonResponse`. POST melewati `csrf`.

| Method | Path | Handler | Filter | Batas | Tujuan |
|---|---|---|---|---|---|
| GET | `/api/auth/username-available?u=` | `AuthApiController::usernameAvailable` | `jsonResponse` | 20/menit per hash IP | Umpan balik nama pengguna saat mengetik di `/daftar/3` |
| GET | `/api/schools/lookup?npsn=` | `SchoolApiController::lookup` | `jsonResponse` | 60/menit per hash IP | Kartu "Benar ini sekolahmu?" di `/daftar/2` |
| GET | `/api/paket/aktif` | `PackageApiController::active` | `jsonResponse` | — | Active asset package (`package_code`, `manifest_url`, `manifest_sha256`; `package_code: null` when package mode is off), `disabled_by_server`, and `online_lab_allowed` (the panel's lab permission, always the stored value, also while package mode is off). Never reflects a staff preview. `Cache-Control: no-store`, no login. See `docs/rencana-paket-aset.md` §5.3 |
| GET | `/api/session` | `SessionApiController::show` | `jsonResponse`, `apiSession` | — | Bootstrap payload |
| POST | `/api/session/locale` | `SessionApiController::setLocale` | `jsonResponse`, `apiSession` | — | Ganti bahasa sesi |
| POST | `/api/session/heartbeat` | `SessionApiController::heartbeat` | `jsonResponse`, `apiSession` | — | Perbarui aktivitas |
| POST | `/api/session/complete` | `SessionApiController::complete` | `jsonResponse`, `apiSession` | — | Tandai sesi selesai bila semua tantangan tuntas |
| GET | `/api/levels` | `ContentApiController::levels` | `jsonResponse`, `apiSession` | — | Tiga wilayah + status |
| GET | `/api/levels/{id}/nodes` | `ContentApiController::nodes/$1` | `jsonResponse`, `apiSession` | — | Empat tantangan satu wilayah + status |
| GET | `/api/nodes/{id}` | `ContentApiController::node/$1` | `jsonResponse`, `apiSession` | — | Metadata tantangan (tanpa soal) + pengerjaan terbaik |
| GET | `/api/library/{levelId}` | `ContentApiController::library/$1` | `jsonResponse`, `apiSession` | — | Halaman Pustaka satu wilayah |
| POST | `/api/nodes/{id}/attempts` | `ChallengeApiController::open/$1` | `jsonResponse`, `apiSession` | 120/menit per sesi (`attempts`) | Buka/lanjutkan attempt + payload soal |
| POST | `/api/attempts/{id}/responses` | `ChallengeApiController::respond/$1` | `jsonResponse`, `apiSession` | 120/menit per sesi (`attempts`) | Satu ketukan benda Cari Objek |
| POST | `/api/attempts/{id}/check` | `ChallengeApiController::check/$1` | `jsonResponse`, `apiSession` | 120/menit per sesi (`attempts`) | "Cek jawabanku" Susun Gambar, Isi Rumpang, Benar atau Salah |
| POST | `/api/attempts/{id}/hints` | `ChallengeApiController::hint/$1` | `jsonResponse`, `apiSession` | 120/menit per sesi (`attempts`) | Buka teks petunjuk |
| POST | `/api/attempts/{id}/complete` | `ChallengeApiController::complete/$1` | `jsonResponse`, `apiSession` | — | Cadangan klien: tutup/ambil hasil pengerjaan |
| POST | `/api/attempts/{id}/abandon` | `ChallengeApiController::abandon/$1` | `jsonResponse`, `apiSession` | — | Tinggalkan attempt |
| POST | `/api/events` | `EventApiController::ingest` | `jsonResponse`, `apiSession` | 120/menit per sesi (`events`) | Batch raw event |
| POST | `/api/audio-events` | `AudioApiController::ingest` | `jsonResponse`, `apiSession` | 120/menit per sesi (`events`) | Telemetri audio |
| GET | `/api/progress` | `ProgressApiController::show` | `jsonResponse`, `apiSession` | — | Progres authoritative |

Batas per sesi dihitung per *bucket*: keempat endpoint `attempts` berbagi satu jatah, `/api/events` dan `/api/audio-events` berbagi jatah `events`.

---

## Route — panel admin (HTML)

Namespace `App\Controllers\Admin`, grup `admin`. Panel hanya berbahasa Indonesia. POST melewati `csrf`.

### Tanpa login

| Method | Path | Handler | Filter | Keterangan |
|---|---|---|---|---|
| GET | `/admin/login` | `AuthController::loginForm` | — | Form masuk staf; sudah masuk → `/admin/dashboard` |
| POST | `/admin/login` | `AuthController::login` | — | Proses masuk |
| GET | `/admin/logout` | `AuthController::logout` | — | Keluar → `/admin/login` (di luar `staffAuth`, jadi tetap terbuka saat sandi wajib diganti) |

### Guru dan admin (`staffAuth`)

Data siswa untuk guru selalu dibatasi sekolahnya (lihat [Otorisasi](#otorisasi)).

| Method | Path | Handler | Keterangan |
|---|---|---|---|
| GET | `/admin` | `DashboardController::index` | Ringkasan |
| GET | `/admin/dashboard` | `DashboardController::index` | Ringkasan (alias) |
| GET | `/admin/akun/sandi` | `AccountController::passwordForm` | Ganti sandi sendiri |
| POST | `/admin/akun/sandi` | `AccountController::changePassword` | Simpan sandi baru |
| GET | `/admin/peserta` | `ParticipantController::index` | Daftar siswa; `?format=csv` = CSV mentah dengan filter yang sama |
| GET | `/admin/peserta/{id}` | `ParticipantController::show/$1` | Detail siswa bertab (`?tab=profil\|sesi\|progres\|pengerjaan\|jawaban\|navigasi\|bantuan\|masukan`) |
| POST | `/admin/peserta/{id}/reset-sandi` | `ParticipantController::resetPassword/$1` | Sandi sementara siswa |
| GET | `/admin/sesi` | `SessionController::index` | Daftar sesi bermain; `?format=csv` |
| GET | `/admin/sesi/{id}` | `SessionController::show/$1` | Detail sesi: ringkasan, pengerjaan, linimasa event lengkap (`?type=`, urut, halaman, `?format=csv`) |
| GET | `/admin/sesi/{id}/event` | `SessionController::timeline/$1` | Alamat lama; dialihkan ke `/admin/sesi/{id}#linimasa` |
| GET | `/admin/data` | `RawDataController::index` | Dialihkan ke `/admin/data/jawaban` |
| GET | `/admin/data/{dimensi}` | `RawDataController::show/$1` | Data mentah per lembar §9.2 (tab `jawaban`, `percobaan`, `pengerjaan`, `progres`, `selesai`, `navigasi`, `bantuan`, `perangkat`); dimensi lain → 404; `?format=csv` |
| GET | `/admin/masukan` | `FeedbackController::index` | Kritik & saran; `?format=csv` |
| GET | `/admin/ekspor` | `ExportController::index` | Form unduh data + riwayat |
| POST | `/admin/ekspor/xlsx` | `ExportController::xlsx` | Bangun workbook XLSX data mentah |
| POST | `/admin/ekspor/csv` | `ExportController::csv` | Bangun CSV-ZIP (satu CSV per lembar) |
| POST | `/admin/ekspor/pdf` | `ExportController::pdf` | Laporan PDF satu siswa |
| GET | `/admin/ekspor/unduh/{id}` | `ExportController::download/$1` | Unduh berkas |

### Admin saja (`staffAuth`, `staffRole:admin`)

Menu kelompok Konten dan Pengaturan tidak dirender untuk guru (`App\Libraries\AdminMenu`); route-nya tetap dijaga `staffRole:admin`.

| Method | Path | Handler | Keterangan |
|---|---|---|---|
| GET | `/admin/konten` | `ContentController::index` | Ringkasan wilayah, tantangan, jumlah soal & variasi |
| GET | `/admin/konten/level/{id}` | `ContentController::level/$1` | Form wilayah |
| POST | `/admin/konten/level/{id}` | `ContentController::updateLevel/$1` | Simpan wilayah |
| GET | `/admin/konten/node/{id}` | `ContentController::node/$1` | Form tantangan + daftar soal per variasi |
| POST | `/admin/konten/node/{id}` | `ContentController::updateNode/$1` | Simpan tantangan |
| POST | `/admin/konten/node/{id}/item` | `ContentController::createItem/$1` | Tambah soal |
| POST | `/admin/konten/item/{id}` | `ContentController::updateItem/$1` | Simpan soal |
| POST | `/admin/konten/item/{id}/hapus` | `ContentController::deleteItem/$1` | Hapus soal (atau nonaktifkan bila sudah dijawab) |
| GET | `/admin/konten/impor-bank` | `ContentController::importForm` | Unggah workbook bank soal + riwayat impor |
| POST | `/admin/konten/impor-bank/pratinjau` | `ContentController::importPreview` | Periksa workbook tanpa menulis |
| POST | `/admin/konten/impor-bank/jalankan` | `ContentController::importRun` | Impor hasil pratinjau yang lolos |
| GET | `/admin/konten/impor-bank/templat` | `ContentController::importTemplate` | Unduh templat workbook |
| GET | `/admin/konten/bacaan/{levelId}` | `ContentController::passages/$1` | Teks bacaan satu wilayah |
| POST | `/admin/konten/bacaan/{levelId}` | `ContentController::savePassages/$1` | Simpan teks bacaan |
| GET | `/admin/konten/pustaka/{levelId}` | `ContentController::library/$1` | Halaman Pustaka satu wilayah |
| POST | `/admin/konten/pustaka/{levelId}` | `ContentController::saveLibrary/$1` | Simpan halaman & media Pustaka |
| GET | `/admin/konten/dialog/{levelId}` | `ContentController::dialogues/$1` | Cerita & dialog satu konteks (`?konteks=`; `levelId = 0` untuk konteks global) |
| POST | `/admin/konten/dialog/{levelId}` | `ContentController::saveDialogues/$1` | Simpan slide |
| POST | `/admin/konten/verifikasi` | `ContentController::verify` | Jalankan pemeriksaan konten, tampilkan temuan |
| GET | `/admin/konten/narasi` | `NarrationController::index` | Status rekaman narasi per bahasa |
| GET | `/admin/konten/narasi/unggah` | `NarrationController::uploadForm` | Form unggah banyak + laporan |
| POST | `/admin/konten/narasi/unggah` | `NarrationController::upload` | Unggah banyak rekaman |
| POST | `/admin/konten/narasi/impor-folder` | `NarrationController::importFolder` | Impor dari `public/assets/audio/narasi/{id\|en}/` |
| POST | `/admin/konten/narasi/setujui` | `NarrationController::approveAll` | Setujui semua rekaman narasi draft satu bahasa |
| GET | `/admin/konten/narasi/daftar-rekaman` | `NarrationController::recordingList` | Unduh daftar rekaman (XLSX) |
| GET | `/admin/media` | `MediaController::index` | Gambar & video |
| GET | `/admin/media/kelengkapan` | `MediaController::checklist` | Kelengkapan gambar & suara |
| GET | `/admin/media/{id}` | `MediaController::show/$1` | Detail slot gambar: isi, ukuran, halaman tampil, titik pin, unggah |
| POST | `/admin/media/unggah` | `MediaController::upload` | Unggah gambar/video ke `asset_key` |
| POST | `/admin/media/{id}/unggah-gerak` | `MediaController::uploadFrames/$1` | Upload many frames of the character pose of slot `{id}` at once (`frames[]`, natural name order, from frame `start`, up to 24) |
| POST | `/admin/media/{id}/nonaktif` | `MediaController::deactivate/$1` | Nonaktifkan berkas |
| GET | `/admin/media/audio` | `MediaController::audioIndex` | Rekaman suara |
| POST | `/admin/media/audio/unggah` | `MediaController::uploadAudio` | Unggah rekaman (status `draft`) |
| POST | `/admin/media/audio/{id}/setujui` | `MediaController::approveAudio/$1` | Setujui satu rekaman |
| POST | `/admin/media/pindai` | `MediaController::scan` | Pindai keselarasan `media_assets` dengan berkas di disk |
| GET | `/admin/studi` | `StudyController::index` | Daftar penelitian |
| POST | `/admin/studi` | `StudyController::store` | Buat penelitian |
| POST | `/admin/studi/{id}` | `StudyController::update/$1` | Ubah penelitian |
| GET | `/admin/studi/rilis` | `StudyController::releases` | Versi permainan |
| POST | `/admin/studi/rilis` | `StudyController::storeRelease` | Buat versi |
| GET | `/admin/studi/rilis/{id}/aktifkan` | `StudyController::confirmActivation/$1` | Confirmation page: the release changes the asset package during a running study (tick + reason) |
| POST | `/admin/studi/rilis/{id}/aktifkan` | `StudyController::activateRelease/$1` | Aktifkan versi |
| GET | `/admin/studi/rilis/paket` | `PackageController::index` | Rilis → Paket aset: package mode switch, build progress and summary, lab permission, history |
| POST | `/admin/studi/rilis/paket/bangun` | `PackageController::startBuild` | Nyalakan / Bangun ulang paket: start a build (or resume the running one). JSON for the page driver, redirect otherwise |
| POST | `/admin/studi/rilis/paket/bangun/{code}/lanjut` | `PackageController::stepBuild/$1` | One batch of the build; the last one finishes the package |
| POST | `/admin/studi/rilis/paket/{code}/terbitkan` | `PackageController::publish/$1` | Create a release naming the package and activate it (switch on / publish rebuild) |
| POST | `/admin/studi/rilis/paket/matikan` | `PackageController::switchOff` | Create a release without a package and activate it (switch off) |
| POST | `/admin/studi/rilis/paket/izin-daring` | `PackageController::saveOnlineLab` | "Izinkan mode daring untuk perangkat lab" |
| POST | `/admin/studi/rilis/paket/periksa` | `PackageController::verify` | Re-hash the active package now (`gelita:paket:periksa`) |
| GET | `/admin/tata-kelola` | `GovernanceController::index` | Hapus data & perawatan |
| POST | `/admin/tata-kelola/hapus/pratinjau` | `GovernanceController::deletePreview` | Langkah 1: hitung baris terdampak |
| POST | `/admin/tata-kelola/hapus/{id}/jalankan` | `GovernanceController::deleteExecute/$1` | Langkah 2: hapus dengan konfirmasi `HAPUS` |
| POST | `/admin/tata-kelola/hapus/{id}/batal` | `GovernanceController::deleteCancel/$1` | Batalkan pratinjau |
| GET | `/admin/tata-kelola/audit` | `GovernanceController::audit` | Riwayat aktivitas (`audit_logs`) |
| POST | `/admin/tata-kelola/retensi` | `GovernanceController::runRetention` | Jalankan retensi sekarang |
| GET | `/admin/sekolah` | `SchoolController::index` | Direktori sekolah |
| POST | `/admin/sekolah/gabung-otomatis` | `SchoolController::autoMerge` | Gabungkan semua nama yang cocok jelas |
| POST | `/admin/sekolah/{id}/gabung` | `SchoolController::merge/$1` | Gabungkan satu entri belum terverifikasi |
| POST | `/admin/sekolah/{id}/sahkan` | `SchoolController::verify/$1` | Sahkan entri sebagai sekolah ber-NPSN |
| GET | `/admin/staf` | `StaffController::index` | Akun guru & admin |
| POST | `/admin/staf` | `StaffController::store` | Buat akun |
| POST | `/admin/staf/{id}` | `StaffController::update/$1` | Ubah akun |
| POST | `/admin/staf/{id}/sandi` | `StaffController::resetPassword/$1` | Sandi sementara staf |
| POST | `/admin/staf/{id}/nonaktif` | `StaffController::deactivate/$1` | Nonaktifkan akun |

---

## Route — API panel admin (JSON)

Namespace `App\Controllers\Api`, grup `api/admin` dengan filter `jsonResponse` + `staffAuth`. Terbuka untuk guru dan admin; cakupan sekolah diterapkan di controller dan service.

| Method | Path | Handler | Tujuan |
|---|---|---|---|
| GET | `/api/admin/summary` | `AdminApiController::summary` | KPI hitungan (`AnalyticsService::summary()`) |
| GET | `/api/admin/sessions/{id}/timeline` | `AdminApiController::timeline/$1` | `{rows}` linimasa event satu sesi; sesi di luar sekolah guru → `403 FORBIDDEN` |
| GET | `/api/admin/exports/{id}/status` | `AdminApiController::exportStatus/$1` | Status pembangunan berkas (dipanggil berkala `admin/export.js`) |

`summary` menerima filter query string panel (`StaffScope::readFilters()` → `scopedFilters()`; yang dipakai `AnalyticsService` adalah filter penelitian baku `study_id`, `participant_id`, `level_id`, `node_id`, `school_id`, `class_level`, `province_code`, `locale`, `date_from`, `date_to`) dan membalas `participant_count`, `active_sessions` (aktif ≤ 10 menit), `completed_attempts_today`, `feedback_count`, `session_count`, `completed_sessions`, `completed_attempts`, `abandoned_attempts`, `item_tries`, `hint_count`, `audio_plays`, `audio_autoplays`; `school_id` dipaksa ke sekolah guru. `timeline` dan `exportStatus` tidak membaca filter.

---

## Controller permainan

### `Game\BaseGameController`

Dasar controller yang route-nya ada di grup `gameSession` (filter sudah memuat `GameContext`, jadi kepemilikan sesi tidak diperiksa ulang).

| Method | Fungsi |
|---|---|
| `session()` / `participant()` | Sesi permainan dan peserta request ini |
| `hudData()` | Data HUD setiap layar: `locale`, `participant` (`toSafeArray()`, tanpa `password_hash`), `progress` (`progressSummary()`), `lantern` (`lanternMap()`) |
| `requireLevel($code)` / `requireNode($levelId, $seq)` | Wilayah/tantangan aktif, atau 404 |
| `introGate()` | `participants.intro_seen_at` kosong → redirect `/intro`; flash `message` dipertahankan satu request lagi (`keepFlashdata`) agar tampil di `/intro` |
| `dialogueGate($session, $level)` | Wilayah baru terbuka (status `open`) yang dialog pembukanya belum tampil pada sesi login ini → redirect `/dialog/{code}`. Tanda "sudah tampil" disimpan di session PHP `dialogues_shown`, bukan di database |
| `toMap()` | Redirect `/peta` dengan flash `curtain=map` (tirai "Membuka Peta Kedu") |

Status yang dipakai layar dan API berasal dari trait `GameProgress`:

* **Wilayah:** `locked` (belum terbuka) / `open` (terbuka, belum ada tantangan selesai) / `in_progress` / `completed`. Wilayah terbuka bila `unlock_mode = free` atau `sequence <= session_progress.unlocked_level_sequence`. `entry` = `dialog/{code}` untuk wilayah `open`, selain itu `wilayah/{code}`.
* **Tantangan:** tantangan ke-n terbuka bila n = 1 atau tantangan ke-(n−1) sudah `completed`; `unlock_mode = free` membuka semuanya.
* **Pustaka:** `open` hanya bila wilayahnya `completed` (apa pun `unlock_mode`), `locked` bila wilayah terbuka tetapi belum tuntas, `unavailable` bila wilayah terkunci.

### `Game\HomeController` (tanpa login)

| Method | Aturan | Respons |
|---|---|---|
| `index()` | Satu tombol Mulai; `hideBrand = true` | view `game/welcome` |
| `start()` | `participant_id` dan `game_session_id` ada → redirect `/gerbang`; selain itu pilihan akun | view `game/start` / redirect |
| `setLocale()` | `locale: required\|valid_locale`; disimpan ke `session('locale')`; bila ada sesi permainan milik peserta → `SessionService::setLocale()` (event `locale_changed`). Tidak membuat sesi baru dan tidak menyentuh progres | redirect ke `safe_internal_url(redirect_to)` |

### `Game\PackageController` (asset package Stage 3, no login)

*(New, English.)* The device-side package screens (plan `docs/rencana-paket-aset.md` §5.6, §8.1–§8.7, P7, P11). Both are open without login like the welcome screen: what they act on is this browser's copy of the package, which holds public images and sounds only (§9.1). The server cannot tell whether a device holds the package, so the gate before Mulai is client-side (`game/package-gate.js`, see `docs/06_JAVASCRIPT.md`).

| Method | Rule | Response |
|---|---|---|
| `prepare()` | `?lanjut=` must match `mulai`, `masuk`, `daftar`, `daftar/1–3`, else `mulai`. No active package (`AssetPackageService::activeCode()` null) → redirect there. Otherwise the parts of the active package (`asset_packages.groups_json`, empty parts left out, in `AssetPackageBuilder::groupOrder()`) with labels from `groupLabel()` | view `game/prepare` / redirect |
| `inspect()` | Nothing server-side; the page reads the device through `core/package.js` | view `game/package-inspect` |
| `groupLabel()` (static) | `kode`, `inti`, `penutup` → `Package.group*`; `wilayah-{x}` → `Package.groupRegion` with the capitalised region code | string |

### `Game\RegisterController` (pendaftaran 3 langkah)

Seluruh langkah berjalan di server dan tetap bekerja tanpa JavaScript. Isian langkah 1–2 disimpan di session PHP `reg_wizard`; tidak ada data peserta yang ditulis sebelum langkah 3 berhasil. Membuka langkah yang lebih jauh dari langkah pertama yang belum lengkap dialihkan ke langkah itu.

| Langkah | View | Aturan validasi (`rulesFor()`) |
|---|---|---|
| 1 Tentang kamu | `game/register/step-1` | `display_name: required\|min_length[2]\|max_length[150]`; `age: required\|integer` dalam `Config\Gelita::$registrationAge` (8–15); `gender: required\|in_list[laki-laki,perempuan]` |
| 2 Sekolah dan daerah | `game/register/step-2` | `country_code`, `province_code`/`district_code` (`required_without[country_other]`), `country_other`; jalur NPSN (provinsi di `Config\Gelita::$schoolDirectoryProvinces`, yaitu 33, dan `school_manual` tidak dicentang): `school_npsn: required\|is_natural\|exact_length[8]\|known_npsn\|allowed_school_stage`; selain itu `school_name: required\|min_length[3]\|max_length[200]` |
| 3 Akun rahasiamu | `game/register/step-3` | `username: required\|valid_username\|not_reserved_username\|is_unique[participants.username]`; `password: required\|strong_password[username]`; `password_confirm: required\|matches[password]`; `assent: required\|in_list[1]`; `locale: permit_empty\|valid_locale` |

Langkah 2 menampilkan kartu sekolah dari NPSN (`SchoolDirectory::describe()`), atau centang "Sekolahku tidak ada di daftar" bila NPSN tidak dikenal. Langkah 3 menerima kebijakan sandi dalam bentuk klien (`PasswordPolicy::toClient()`).

**Alur POST `/daftar/3`** (termasuk metrik literasi keamanan digital):

```text
1. ResearchStudyModel::requireActiveStudy(); nama pengguna dinormalisasi.
2. PasswordPolicy::check(password, username) SEBELUM validasi lain:
     kiriman pertama pada sesi pendaftaran ini → session('reg_first_criteria', met)   // 0..5, bukan isi sandi
     sandi belum 'acceptable'               → session('reg_weak_count', +1)
3. Isian langkah 1–2 dari session divalidasi ulang; gagal → kembali ke langkah itu.
4. Validasi langkah 3; gagal → kembali ke /daftar/3 dengan galat per isian
   (pesan sandi: Auth.notStrongSubmit / Auth.containsUsername / Auth.notMatch).
   old() tidak pernah memuat password dan password_confirm.
5. Nama provinsi/kabupaten diverifikasi server terhadap RegionDirectory; kode tak dikenal → nama snapshot kosong.
6. SessionService::registerAndStart([... 'class_level' => Config\Gelita::$registrationClass ('5'),
     'assent' => true, 'registration_metrics' => [first_submit_criteria, weak_submit_count],
     'device' => deviceInfo()])
     → participants (assented_at, pw_first_submit_criteria, pw_weak_submit_count),
       game_sessions, device_logs (via 'register'), session()->regenerate(true).
7. Hapus reg_wizard, reg_first_criteria, reg_weak_count dari session.
8. Redirect /intro dengan flash 'welcome' (lang Game.registerWelcome, nama pengguna saja).
```

Sekolah: NPSN hanya dipakai di provinsi berdirektori; nama sekolah dipakai bila bukan jalur NPSN atau `school_manual` dicentang. `SchoolDirectory::resolve()` (di dalam `registerAndStart()`) menautkan NPSN ke sekolah resmi; nama → alias hasil penggabungan admin, sekolah resmi yang cocok tunggal, atau entri belum terverifikasi.

### `Game\LoginController`

| Method | Aturan | Respons |
|---|---|---|
| `form()` | Sudah masuk → redirect `/mulai`; flash `redirect_after_login` dipertahankan sampai POST | view `game/login` |
| `login()` | `username`, `password` wajib. `SessionService::login()`: `invalid` → `Auth.loginFailed` (identik untuk akun tak ada dan sandi salah); `locked` → `Auth.locked` dengan sisa menit; `must_change` → `/ganti-sandi` (tanpa flash: halaman itu sudah menampilkan `Auth.mustChange`); `ok` → `redirect_after_login` (path internal) atau `/mulai` | redirect |
| `changePasswordForm()` | `mustChange`, `username` (untuk syarat "tidak memuat nama pengguna"), `passwordPolicy` | view `game/change-password` |
| `changePassword()` | `current_password: required`; `password: required\|strong_password[username]\|differs[current_password]`; `password_confirm: required\|matches[password]`; `SessionService::changePassword()` (`invalid_current` → `Auth.wrongCurrent`). Lalu masuk otomatis untuk melanjutkan/membuat sesi; setelah reset guru event `password_changed` dicatat pada sesi baru dengan `via: reset` (ganti biasa: `via: self`) | redirect `/gerbang` + `Auth.passwordChanged` (langsung, karena `/mulai` mengalihkan siswa yang sudah masuk sekali lagi dan menghabiskan flash; `/mulai` hanya bila sesi permainan tidak terbuka) |
| `logout()` | `SessionService::logout()`: sesi `active` → `paused` + event `session_paused`; attempt `in_progress` tidak ditutup agar dapat dilanjutkan; `session()->destroy()` | redirect `/` |

### `Game\GateController`

| Method | Aturan | Respons |
|---|---|---|
| `index()` | `introGate()`; sapaan dengan `display_name` atau `username`; kartu "Lihat cerita pembuka" dan "Langsung ke peta" | view `game/start-choice` |
| `map()` | `toMap()` | redirect `/peta` |
| `intro()` | Slide `dialogues` konteks `intro`; `canSkip` = sudah pernah menonton | view `game/intro` |
| `finishIntro()` | `ParticipantModel::markIntroSeen()` (idempoten); event `intro_completed` `{first}` setiap kali | `toMap()` |

### `Game\MapController`

| Method | Aturan | Respons |
|---|---|---|
| `kedu()` | `introGate()`; `levelOverview()` + tirai per wilayah (`regionCurtain()`); narasi `map_intro`; flash `curtain=map` → `curtain = true` dan `curtainAssets` (peta, latar, frame tokoh, latar wilayah, audio narasi peta yang disetujui); `regionIntros` (narasi "Kenali wilayah" `region_intro` per wilayah, termasuk yang terkunci); `heard` (`GameEventLogModel::heardRegionIntros()`) | view `game/map-kedu` |
| `level($code)` | `introGate()`; wilayah terkunci → `/peta` + `Game.levelLocked`; `dialogueGate()`; `nodeOverview()`, `levelSummary`, `hasLibrary`, `library` (`libraryAccess()`) | view `game/map-level` |

### `Game\DialogueController`

Adegan bernarasi dari tabel `dialogues`, diputar `game/narrator.js`. Perpindahan slide dicatat klien lewat `/api/events` (`dialogue_advanced`).

| Method | Aturan | Respons |
|---|---|---|
| `show($code)` | `introGate()`; wilayah terkunci → `/peta`; `markDialogueShown()`; slide `level_open`; `tagline` = judul slide pertama `region_intro` | view `game/dialogue` |
| `done($code)` | Status wilayah ≠ `completed` → redirect `/wilayah/{code}`; slide `level_done`, `hasLibrary`, `nextRegion`, `allCompleted`; boleh ditonton ulang | view `game/region-done` |
| `ending()` | Belum semua tantangan tuntas → `/peta`; slide `ending` | view `game/ending` |

### `Game\ChallengeController`

| Method | Aturan | Respons |
|---|---|---|
| `brief($code, $seq)` | Wilayah atau tantangan terkunci → `/wilayah/{code}` + `Game.nodeLocked`; `dialogueGate()`; pengerjaan terbaik | view `game/mission-brief` |
| `play($code, $seq)` | Pemeriksaan yang sama; `engine_type` harus ada di `Config\Gelita::$engineTypes` **sebelum** attempt dibuka; `ChallengeService::openNode()` (gagal → `/wilayah/{code}` + `Game.challengeUnavailable`); payload soal ditanam di view | view `game/challenge/{puzzle\|rumpang\|boleh\|cari}` |
| `result($code, $seq)` | Attempt `completed` tantangan itu pada sesi ini + terbaik | view `game/challenge-result` |
| `finished($attemptId)` | Attempt harus milik sesi ini dan `completed`, selain itu 404. `levelSummary`, `levelCompleted`, `closesRegion` (attempt ini yang menuntaskan wilayah: penyelesaian pertama terakhir di antara tantangan wilayah, `GameProgress::closingAttemptId()`), `libraryUnlocked`, `allCompleted`, `nextRegion`, `wrongTaps` (Cari Objek: jumlah `wrong_click_count`) | view `game/challenge-finished` |

`play()` tidak menilai apa pun. Ia hanya membuka (atau melanjutkan) attempt dan menyiapkan payload; seluruh jawaban lewat API.

### `Game\LibraryController`, `ProfileController`, `ReflectionController`

| Method | Aturan | Respons |
|---|---|---|
| `LibraryController::index()` | Baris `levelOverview()` + status Pustaka, jumlah halaman, sampul (gambar pertama galeri halaman pertama → latar wilayah → gradien). Tidak mencatat event | view `game/library-index` |
| `LibraryController::show($code)` | Wilayah terkunci → `/peta`. Wilayah terbuka tetapi belum tuntas → halaman terkunci berisi progres dan tombol ke `entry` wilayah (bukan redirect; `library_opened` tidak dicatat). Wilayah tuntas → event `library_opened` | view `game/library` / `game/library-locked` |
| `ProfileController::avatar()` | `avatar_no` kosong atau kunci `avatar_choices()`; menulis `participants.avatar_no`; tidak ada event penelitian | redirect `/profil` |
| `ProfileController::index()` | Capaian sesi berjalan: attempt, durasi, jumlah benar pada percobaan pertama dari soal dinilai, `canReflect` | view `game/profile` |
| `ReflectionController::index()` | Hanya bila semua tantangan tuntas, selain itu `/peta` + `Game.reflectionLocked`; ringkasan perjalanan (bintang per wilayah dari 3 × jumlah tantangan, ketepatan per engine, tantangan paling sering terlewat) | view `game/reflection` |
| `ReflectionController::store()` | `rating: required\|integer\|greater_than[0]\|less_than[6]`; empat isian terbuka (`liked_most`, `hardest_part`, `new_learning`, `suggestion`, maks. 2000) dan minimal dua terisi; simpan `participant_feedback` + event `feedback_submitted` | redirect `/refleksi` |

---

## Controller API

### `Api\BaseApiController`

Dasar semua endpoint JSON permainan (memakai `GameProgress`).

| Method | Fungsi |
|---|---|
| `gameSession()` | Sesi permainan yang dimuat filter `apiSession` |
| `attemptOrFail($id, $requireOpen = true)` | Attempt tidak ada → `404 NOT_FOUND`; bukan milik sesi berjalan → `403 FORBIDDEN`; `$requireOpen` dan tidak `in_progress` → `409 ATTEMPT_CLOSED` |
| `invalidPayload($errors, $code = 'INVALID_PAYLOAD')` | `422` dengan `errors` per isian |
| `rateLimited($bucket, $capacity = 120)` | Throttler per bucket per sesi; habis → `429 RATE_LIMITED` + `retry_after` |
| `serverTime()` | Waktu server dengan mikrodetik dan offset, mis. `2026-09-21T09:14:22.481000+07:00` |

`AuthApiController`, `SchoolApiController`, dan `AdminApiController` langsung turunan `BaseController` (tanpa sesi permainan).

### `Api\AuthApiController` dan `Api\SchoolApiController`

```text
usernameAvailable()   GET ?u=nama        rate limit 20/menit per hash_ip()
  nama dinormalisasi (ParticipantModel::normalizeUsername())
  format tidak sah   → {available:false, reason:'format'}
  dicadangkan        → {available:false, reason:'reserved'}   (Config\Gelita::$reservedUsernames)
  sudah dipakai      → {available:false, reason:'taken'}
  tersedia           → {available:true}

lookup()              GET ?npsn=20318068 rate limit 60/menit per hash_ip()
  bukan 8 angka                              → {found:false, reason:'format'}
  tidak ada / belum terverifikasi / nonaktif → {found:false, reason:'unknown'}
  ada → {found:true, school: SchoolDirectory::describe()}   hanya data direktori publik
```

### `Api\SessionApiController`

| Method | Body | Respons `data` |
|---|---|---|
| `show()` | — | Bootstrap payload (di bawah) |
| `setLocale()` | `{locale}` (JSON atau form) | `{locale, server_time}`; locale tak dikenal → `422 INVALID_PAYLOAD`. Tidak membuat sesi baru dan tidak mereset progres |
| `heartbeat()` | `{}` | `{server_time}` (`SessionService::heartbeat()`) |
| `complete()` | `{}` | `{completed, status, progress}` (`SessionService::completeIfFinished()`) |

Bootstrap payload:

```json
{
  "success": true,
  "data": {
    "session": { "code": "...", "locale": "id", "status": "active", "study": "..." },
    "participant": { "code": "GLT-000123", "username": "jaka.kedu", "display_name": "..." },
    "progress": { "completed_nodes": 7, "completed_levels": 1, "unlocked_level_sequence": 2,
                  "total_stars": 18, "shards": 7, "shards_total": 12,
                  "current_level_id": 2, "current_node_id": 6 },
    "levels": [ { "id": 1, "code": "temanggung", "sequence": 1, "name": "Temanggung",
                  "difficulty": "mudah", "status": "completed", "entry": "wilayah/temanggung",
                  "stars": 10, "completed_nodes": 4, "total_nodes": 4,
                  "map_x": 0, "map_y": 0, "map_media": "...", "background": "..." } ],
    "server_time": "2026-09-21T09:14:22.481000+07:00"
  },
  "request_id": "..."
}
```

`shards` (Serpihan Cahaya) = jumlah tantangan tuntas; `shards_total` = jumlah tantangan aktif (12).

### `Api\ContentApiController` dan `Api\ProgressApiController`

Tidak pernah mengirim kunci jawaban; soal hanya keluar lewat `ChallengeApiController::open()`.

| Method | Aturan | Respons `data` |
|---|---|---|
| `levels()` | — | `{levels}` (`levelOverview()`) |
| `nodes($levelId)` | wilayah tidak ada → `404` | `{level:{id, code, sequence}, locked, nodes}` (`nodeOverview()`: `id, sequence, engine_type, variant, title, description, status, stars, map_x, map_y`) |
| `node($nodeId)` | tidak ada → `404`; wilayah terkunci → `409 LEVEL_LOCKED` | `{node:{id, level_id, sequence, engine_type, variant_code, title, instruction, description, max_tries, background, scene, hints_count}, best}` |
| `library($levelId)` | tidak ada → `404`; wilayah terkunci → `409 LEVEL_LOCKED`; wilayah belum tuntas → `409 LIBRARY_LOCKED` (kunci yang sama dengan `/pustaka/{code}`) | `{level:{id, code, name}, pages:[{id, sequence, title, body, media}]}` |
| `ProgressApiController::show()` | — | `{progress, levels, nodes:{levelId: [...]}, unlock_mode, server_time}` |

### `Api\ChallengeApiController`

Tidak ada penilaian di controller: benar/salah, bintang, dan penutupan ditentukan `ChallengeService` dari kunci di database; field `correct` kiriman browser tidak pernah dipakai. **Aturan 3 kesempatan** (`Config\Gelita::$maxTries`, disalin ke `challenge_attempts.max_tries`) ditegakkan server, dan `/check` serta `/responses` menutup pengerjaan sendiri bila semua benar (`completion_reason = solved`) atau kesempatan habis (`tries_exhausted`).

| Method | Request body | Pemeriksaan | Service | Respons `data` |
|---|---|---|---|---|
| `open($nodeId)` | `{}` | node ada; wilayah terbuka (`409 LEVEL_LOCKED`) | `openNode()` — attempt `in_progress` yang ada dilanjutkan; attempt baru memakai satu variasi acak | payload pemain (di bawah) |
| `respond($attemptId)` | `{item_id, answer:{object:<ref>}, client_event_id, duration_ms?}` | attempt terbuka; `item_id: required\|is_natural_no_zero\|item_in_attempt[attempt_id]`; `client_event_id: required\|max_length[120]`; `answer` objek; hanya petunjuk Cari Objek (`find_object`) | `submitAnswer()` | `{correct, first_pass, wrong_click, decoy, feedback, try_no, tries_left, clue_closed, change_count, wrong_click_count, progress:{answered, total}, completed, completion_reason, redirect, shards}` |
| `check($attemptId)` | `{answers:[{item_id, answer, first_input_ms?, last_input_ms?}], check_no?}` | `check_count >= max_tries` → `409 TRIES_EXHAUSTED` (dengan `redirect` bila sudah ditutup); attempt tidak `in_progress` → `409 ATTEMPT_CLOSED`; `check_no` (opsional, nomor cek menurut layar klien) ≠ `check_count + 1` → `409 ATTEMPT_CHANGED` tanpa perubahan; setiap `item_id` milik attempt; soal belum benar yang kosong → `422 INCOMPLETE` + `items` | `submitCheck()` | `{results:{itemId: bool}, correct_count, total, all_correct, check_count, try_no, tries_left, exhausted, detail:{itemId:{pieces_correct, misplaced}}, completed, completion_reason, redirect, shards}` — `detail` hanya untuk Susun Gambar |
| `hint($attemptId)` | `{hint_id, item_id?}` | attempt terbuka; `hint_id` > 0; `item_id` milik attempt; petunjuk bukan milik tantangan → `404` | `useHint()` | `{text, hint_count}` |
| `complete($attemptId)` | `{}` | attempt milik sesi; `abandoned` → `409 ATTEMPT_CLOSED`; masih berjalan tetapi belum semua benar dan kesempatan belum habis → `409 TRIES_REMAINING` | `completeAttempt()` bila masih `in_progress` | `{stars, first_pass_correct, final_correct, scorable_items, completion_reason, duration_ms, shards, redirect}` |
| `abandon($attemptId)` | `{}` | attempt milik sesi | `abandonAttempt()` (bintang 0, event `challenge_abandoned` `via: user_exit`; attempt yang sudah selesai tidak diubah) | `{status}` — status sebenarnya |

Pembagian engine: `respond()` untuk Cari Objek (`cari`, satu ketukan benda = satu percobaan untuk petunjuk aktif); `check()` untuk Susun Gambar (`puzzle`), Isi Rumpang (`rumpang`), dan Benar atau Salah (`boleh`) — satu "Cek jawabanku" = satu percobaan untuk semua soal yang belum benar, soal yang sudah benar terkunci. Engine yang salah jalur dibalas `422 INVALID_RESPONSE`.

* Cari Objek: ketukan pada petunjuk yang sudah ditemukan atau dilewati → `409 CLUE_CLOSED`. Ketukan salah ke-`max_tries` menutup petunjuk itu (`clue_closed = true`, event server `clue_failed`); semua petunjuk tertutup → pengerjaan ditutup.
* Engine batch: cek ke-`max_tries` yang masih ada salahnya → event `challenge_tries_exhausted`, pengerjaan ditutup `tries_exhausted`.
* Saat pengerjaan ditutup, respons berisi `completed: true`, `completion_reason`, `redirect` (`/selesai/{attemptId}`), dan `shards`. Klien pindah ke `redirect`; `/complete` hanya cadangan bila `completed` datang tanpa `redirect`, dan bersifat idempoten (pengerjaan yang sudah selesai dibalas hasil yang sama).

**Payload pemain** (`open()` dan `#challenge-data` di `/tantangan/{code}/{seq}`):

```text
{attempt_id, attempt_no, variant_group, variant:{group, title}, max_tries, tries_used, check_count,
 resumed, elapsed_ms,
 node:{id, level_id, sequence, engine_type, variant_code, title, instruction, description, background, scene},
 items:[ …toPlayerArray(), state:{tries} | {locked:true, answer, tries} ],
 hints:[ {id, item_id, sequence} ],  hints_count,
 word_bank   (Isi Rumpang ber-bank kata),
 verdict_options (Benar atau Salah),
 passages    (bila soal merujuk teks bacaan)}
Cari Objek: tanpa `items`, diganti `objects` (semua benda, token `ref` per attempt, posisi, gambar, `found`)
            dan `clues` (petunjuk dinilai, diacak dengan benih id attempt; `audio`, `state:{found, closed, tries}`).
```

Teks petunjuk tidak ada di payload; baru dikirim `POST /hints`, yang sekaligus mencatat pemakaiannya. `answer_key_json` tidak pernah ikut.

### `Api\EventApiController` dan `Api\AudioApiController`

```text
POST /api/events
  {events:[{client_event_id, event_type, occurred_at, sequence_no?,
            level_id?, node_id?, attempt_id?, item_id?, payload?}]}
  → {accepted, duplicate, rejected:[{index, reason}], server_time}
```

* Maksimum `Config\Gelita::$maxEventsPerBatch` (50) event per request; lebih → `413 TOO_MANY_EVENTS`. `events` bukan array → `422 INVALID_PAYLOAD`.
* `event_type` harus ada di `Config\Gelita::$eventTypes`. Tipe khusus server (`screen_viewed`, `challenge_tries_exhausted`, `clue_failed`) ditolak dari klien.
* Alasan penolakan per event: `format_invalid`, `event_type_unknown`, `event_type_server_only`, `client_event_id_invalid` (kosong / > 120 karakter), `duplicate_in_batch`.
* Idempoten terhadap `client_event_id` per sesi: kiriman ulang dihitung `duplicate` tanpa menambah baris.
* Rujukan memakai `level_id`, `node_id`, `attempt_id`, `item_id`; nama kolom lengkap `challenge_*_id` juga diterima.

`POST /api/audio-events` memakai amplop dan batas yang sama. Isi per event: `{audio_asset_id, action, listened_ms, completed, occurred_at, client_event_id, attempt_id?}`. `action` ∈ `play` (pemain menekan putar), `autoplay` (layar bernarasi memutar sendiri), `pause`, `replay`, `complete`; aksi lain → `audio_event_invalid`. `attempt_id` milik sesi lain → `attempt_not_in_session`. Hasilnya baris `audio_usage_events` beserta raw event pendampingnya (`audio_play`, `audio_autoplay`, …).

#### Combined batch, early session release, heartbeat (rencana-log-asinkron Phase 2)

*(New, English.)*

```text
POST /api/events
  {events:[…], audio?:[…], client_sent_at, dropped?, dropped_id?, audio_dropped?, audio_dropped_id?}
  → {accepted, duplicate, rejected, audio?:{accepted, duplicate, rejected}, server_time}
```

* `audio` holds the entries `/api/audio-events` takes; `events` may be `[]` when only audio is sent. Each array has at most `maxEventsPerBatch` (50) entries, else `413 TOO_MANY_EVENTS`; `audio` that is not an array → `422 INVALID_PAYLOAD`.
* The answer has the `audio` key whenever the request carried an `audio` array, even an empty one.
* `audio_dropped` + `audio_dropped_id` are written as `events_dropped` with `channel: audio`, exactly like `dropped`/`dropped_id` on `/api/audio-events`.
* The combined request counts once against the `events` bucket (120/minute per session).
* Every successful request runs `SessionService::heartbeat()` at most once per 50 s per session (`heartbeatIfDue()`, cache key `gelita_heartbeat_{session}`), so a tab that sends batches needs no separate heartbeat. `heartbeat()` only adds time when `last_active_at` is still the value it read, so two tabs beating at once count the time once.
* `/api/events`, `/api/audio-events`, and `/api/session/heartbeat` call `session()->close()` first: they never write `$_SESSION`, and the PHP session lock is released before any database work, so a `check`/`submit` from the same student does not wait for telemetry.
* Audio entries from both endpoints are written by `EventService::recordAudioBatch()` in one transaction: one `whereIn(client_event_id)` existence check, server `sequence_no` from `nextServerSequence()` in batch order, one `INSERT IGNORE` per table, one grouped `MAX(play_index)` per asset (play indexes assigned in batch order, rule of `AudioUsageEventModel::nextPlayIndex()`), and one `audio_use_count = audio_use_count + n` per attempt. An unknown `audio_asset_id` is rejected as `audio_event_invalid` instead of failing the batch on the foreign key.
* `/api/audio-events` and `/api/session/heartbeat` stay for tabs opened before the combined batch.

### `Api\AdminApiController`

Memakai trait `StaffScope`; lihat tabel [Route — API panel admin](#route--api-panel-admin-json). `exportStatus()` hanya untuk pemohon ekspor atau admin (`403 FORBIDDEN`), dan membalas `{id, status, format, anonymized, row_count, file_sha256, expires_at, error}`.

---

## Controller panel admin

### `Admin\BaseAdminController` dan trait `StaffScope`

* `panel($view, $title, $data)` merender halaman panel dengan data bersama: `pageTitle`, `filters`, `errors`, `activeStudy`, dan `filterOptions` (penelitian, wilayah, sekolah, provinsi dan kelas yang ada dalam cakupan pemanggil; dievaluasi hanya oleh halaman yang memakai bilah filter).
* Sidebar dan breadcrumb panel dibangun layout `layouts/admin` dan `components/admin-sidebar` dari `App\Libraries\AdminMenu` (`items()`, `crumbs()`); controller dapat menambah langkah breadcrumb lewat data `breadcrumb` (`[label => href|null]`).
* `back($path, $pesan)` / `done($path, $pesan)` kembali dengan flash `error` / `message`; `modelErrors()` meneruskan penolakan validasi Model agar penyimpanan yang gagal tidak dilaporkan "tersimpan".
* `StaffScope` (dipakai juga `Api\AdminApiController`): `schoolScope()` (`null` untuk admin, `school_id` untuk guru; guru tanpa sekolah → 0, tidak cocok dengan sekolah mana pun), `isAdmin()`, `staffId()`, `readFilters()`, `scopedFilters()` (memaksa `school_id` guru), `analytics()` (`AnalyticsService` terikat cakupan pemanggil), `dataScope($anonymous)` (`App\Services\StaffScope`: admin beridentitas kecuali diminta anonim, guru selalu anonim + sekolahnya) dan `rawData()` (`RawDataService` terikat cakupan itu; `page()`, `streamCsv()`). `readFilters()` menerima juga `participant_code`, `variant_group`, dan filter daftar siswa (`q`, `district_code`, `gender`, `age`, `progress`).
* `rawCsvDownload($dimension, $filters)`: CSV satu tabel data mentah (nilai berkode, judul = kode kolom, UTF-8 + BOM) dalam cakupan pemanggil, audit `export_csv`; dipakai `?format=csv` tabel `/admin/data/{dimensi}`.
* `tableCsv($name, $builder, $columns, $sort, $tieBreaker, $filters)`: CSV tabel daftar (`AdminTable::streamCsv()` lewat `csvResponse()`), audit `list_csv`; dipakai `?format=csv` Siswa, Sesi, linimasa sesi, dan Kritik & saran.

### `Admin\AuthController`

`loginForm()` (sudah masuk → `/admin/dashboard`; view `admin/login`), `login()`, `logout()`.

Aturan `login()`:

1. `username` dan `password` wajib.
2. `StaffUserModel::findByUsername()`. Akun terkunci → pesan penguncian, audit `login_failed` (`reason: locked`).
3. Akun tidak ada, sandi salah, atau akun nonaktif → pesan identik "Nama pengguna atau kata sandi salah."; akun aktif yang salah sandi menjalankan `registerFailedLogin()` (5 kali → terkunci 15 menit); audit `login_failed` (`reason: invalid`).
4. Berhasil → `clearFailedLogin()`, `session()->regenerate(true)`, set `staff_id`, `staff_role`, `staff_school_id`, `staff_name`; audit `login`.
5. `must_change_password = 1` → `/admin/akun/sandi`; selain itu `redirect_to` (path internal) atau `/admin/dashboard`.

`logout()`: audit `logout`, `session()->destroy()`, redirect `/admin/login`.

### `Admin\AccountController` (guru & admin)

`passwordForm()` → view `admin/account/password` (`mustChange` menampilkan pemberitahuan sandi sementara). Aturan `changePassword()`:

1. `current_password: required`; `password: required|min_length[12]|max_length[72]|differs[current_password]` (sama dengan pembuatan akun; 72 = batas bcrypt); `password_confirm: required|matches[password]`.
2. Sandi saat ini salah → `registerFailedLogin()`, audit `staff_password_change_failed`. Bila itu mengunci akun, kunci sesi staf dihapus dan sesi di-`regenerate(true)`, lalu redirect `/admin/login` dengan pesan penguncian — sesi yang dibajak tidak dapat dipakai menebak sandi.
3. Berhasil → `setPassword()` (sekaligus `must_change_password = 0`), `failed_login_count = 0`, `regenerate(true)`, audit `staff_password_change` dengan `after_reset`. Sandi sementara → redirect `/admin/dashboard`; selain itu kembali ke form dengan pesan berhasil.

### Data penelitian (guru & admin)

| Controller::method | Aturan | View |
|---|---|---|
| `DashboardController::index()` | KPI hitungan dari `AnalyticsService::summary()` (siswa terdaftar, sesi aktif ≤ 10 menit, pengerjaan selesai hari ini, kritik & saran); admin: peringatan `ResearchReadiness` (gambar contoh, narasi belum disetujui, verifikasi konten) dan pratinjau penghapusan buatan retensi; tabel 20 event terakhir dan progres 10 siswa terbaru | `admin/dashboard` |
| `ParticipantController::index()` | `AdminTable` di atas query siswa dalam cakupan (progres dari sesi terakhir). Filter `q` (kode; nama pengguna dan nama hanya untuk admin), `school_id`, `district_code`, `gender`, `age`, `progress` (`belum\|berjalan\|selesai`), `date_from`/`date_to` (tanggal daftar). Kolom Nama dan Nama pengguna (`identity`) tidak diambil untuk guru. `?format=csv` | `admin/participants/index` |
| `ParticipantController::show($id)` | Siswa di luar cakupan → 404. Tab Profil, Sesi & perangkat, Progres (12 baris node), Kritik & saran dari query lokal; tab Pengerjaan, Jawaban (+ riwayat `percobaan` per baris, seluruhnya lewat `RawDataService::all()`), Navigasi, Bantuan dari `RawDataService::page()` dengan `participant_id`; `?format=csv` pada tab data mentah. Aksi: unduh XLSX/CSV-ZIP dan cetak PDF (`ExportController`), reset sandi | `admin/participants/show` |
| `ParticipantController::resetPassword($id)` | Lihat di bawah | `admin/participants/reset-result` |
| `SessionController::index()` | `AdminTable`; filter kode siswa, sekolah, penelitian, bahasa, tanggal; `?format=csv` | `admin/sessions/index` |
| `SessionController::show($id)` | Sesi di luar cakupan → 404. Ringkasan, pengerjaan di sesi itu, linimasa `game_event_logs` (urutan, waktu klien, waktu server, event, layar/tantangan/soal, payload JSON) dengan filter `?type=` | `admin/sessions/show` |
| `SessionController::timeline($id)` | Redirect ke detail sesi | — |
| `RawDataController::show($dimensi)` | `RawDataService::page()` lewat `rawService()`/`rawPage()`; filter kode siswa, sekolah, wilayah, tantangan, variasi, tanggal (dipaksa ke cakupan sekolah); `DomainException` (guru meminta dimensi admin) → 404; `?format=csv` → `rawCsv()` (`rawCsvDownload()`) | `admin/data/index` |
| `FeedbackController::index()` | `AdminTable`: kode, sekolah, bintang, empat jawaban, waktu; filter sekolah dan tanggal; `?format=csv` | `admin/feedback/index` |

**Tabel server-side (`App\Libraries\AdminTable`).** `params()` membaca `page`, `per_page` (25/50/100, selain itu 25) dan `sort` (`kolom` naik, `-kolom` turun; kolom tak dikenal → urutan bawaan). `page()` menghitung total, menjepit halaman, menambah ORDER BY dari definisi kolom (`sort` = ekspresi SQL) plus pengurut stabil, dan mengembalikan bentuk yang sama dengan `RawDataService::page()`. `streamCsv()` menulis semua baris (UTF-8 + BOM, judul = kunci kolom, nilai mentah). `visibleColumns()` membuang kolom `identity` untuk guru. `BaseAdminController::csvResponse()` membungkus penulis CSV menjadi respons unduhan; `wantsCsv()` memeriksa `?format=csv`.

Peserta selalu dikirim ke view sebagai array aman (`Participant::toSafeArray()` + kolom tampilan), tidak pernah sebagai entity yang membawa `password_hash`.

**`ParticipantController::resetPassword($id)`:**

```text
1. Siswa harus dalam schoolScope() pemanggil (guru: sekolahnya saja) → selain itu 404.
2. Field confirm harus berisi "RESET".
3. SessionService::resetPasswordByStaff($id, staffId):
     sandi sementara acak yang memenuhi kebijakan, must_change_password = 1,
     audit participant_password_reset (tanpa sandi sementara di metadata).
4. Sandi sementara ditampilkan SEKALI di respons ini (Cache-Control: no-store);
   tidak disimpan, tidak lewat flash, tidak masuk log.
   Pada masuk berikutnya siswa diarahkan ke /ganti-sandi.
```

### `Admin\ExportController` (guru & admin)

| Method | Aturan |
|---|---|
| `index()` | Form filter (`ExportService::FILTER_KEYS`), pilihan lembar §9.2 (`allowedSheets()`), nilai berlabel/berkode, anonim (admin), riwayat 20 ekspor (guru: miliknya saja); view `admin/export/index` |
| `xlsx()` / `csv()` | Baris `data_exports` (`format` `xlsx` / `csv`) status `running` → `ExportService::build()` → audit `export`. POST: filter, `sheets[]` (kosong = semua yang boleh; Kamus Data selalu ikut), `labels` (`label` \| `code`; bawaan XLSX berlabel, CSV berkode), `anonymized`, `participant_id` (ekspor per siswa, kembali ke profil siswa). **Guru dipaksa `anonymized = 1`**, tanpa lembar `event` dan kolom `answer_key`; filter dipaksa ke cakupan sekolah |
| `pdf()` | Hanya laporan satu siswa (`participant_id` wajib, templat `participant`); tanpa `participant_id` ditolak dengan pesan. Laporan studi PDF tidak ada |
| `download($id)` | Hanya pemohon atau admin; berkas belum kedaluwarsa (`expires_at`, `Config\Gelita::$exportRetentionDays`) dan berada di direktori ekspor; audit `export_download` |

`ExportService` membaca ulang hak pemohon dari `staff_users` sebagai lapis ketiga (`StaffScope::fromStaff()`). Isi lembar berasal dari `RawDataService`.

### `Admin\ContentController` (admin)

Setiap perubahan memanggil `ContentRepository::flush()` dan menulis audit `content_update`.

| Method | Aturan |
|---|---|
| `index()` | Tabel 12 tantangan: wilayah, urutan, jenis, judul, variasi aktif (x/3), jumlah soal, gambar contoh tersisa (`media_assets.is_placeholder = 1` aktif dengan awalan `challenge.{ref}.`), status verifikasi dari `ContentVerifier::run(true)` (temuan dengan lingkup tantangan itu); tabel wilayah dengan tautan editor; view `admin/content/index` |
| `level()` / `updateLevel()` | `name_id`, `name_en` wajib; `difficulty` ∈ `mudah,sedang,sulit`; teks dwibahasa (`name`, `focus`, `cp`, `tp`, `intro`); gambar peta/latar/lencana; view `admin/content/level` |
| `node()` / `updateNode()` | `title_id`, `title_en` wajib; `engine_type` valid dan **tidak dapat diubah** bila tantangan sudah punya attempt; isian terpandu `cfg[...]` (`distractor_count`, `use_word_bank`, `as_reading`, judul & pengecoh per variasi) digabung ke `config_json`; gambar adegan/latar; audio pengantar; view `admin/content/node` bertab Info / Variasi (judul & pengecoh per variasi + tabel soal dengan Bloom, keterampilan, kunci) / Posisi benda (Cari Objek) |
| `createItem()` / `updateItem()` | `interaction_type` harus pasangan engine (`puzzle` → `puzzle_arrange`, `rumpang` → `fill_blank_bank`, `boleh` → `verdict_card`, `cari` → `find_object`); soal dinilai wajib punya `answer_key_json`; `variant_group` a–z/0–9; ukuran papan dan kunci Susun Gambar ditetapkan server dari tingkat kesulitan wilayah (`DifficultyRules`), dan soal yang sudah dijawab mempertahankan ukuran serta kuncinya; audio petunjuk hanya untuk `find_object` |
| `deleteItem()` | Soal yang sudah pernah dijawab hanya dinonaktifkan (data penelitian), selain itu dihapus |
| `importForm()` | Unggah workbook + 10 riwayat `content_import`; view `admin/content/import` |
| `importPreview()` | `file: uploaded\|ext_in[file,xlsx]\|max_size[file,20480]`; berkas disimpan sementara di `writable/uploads/`; `ContentImportService::preview()` disimpan di session `import_preview` |
| `importRun()` | Hanya bila pratinjau terakhir lolos; `ContentImportService::import()` (audit `content_import` di service); berkas sementara dihapus |
| `importTemplate()` | Unduh `gelita-bank-soal.xlsx` (`ContentImportService::template()`) |
| `passages()` / `savePassages()` | Teks bacaan satu wilayah; bacaan yang masih dirujuk soal tidak boleh dihapus; view `admin/content/passages` |
| `library()` / `saveLibrary()` | Halaman Pustaka dan galeri medianya (`library_media`: berkas atau tautan video luar + poster) dalam satu transaction; urutan halaman boleh ditukar; poster video luar yang kosong diambil otomatis sesudah commit (`VideoThumbnail`); view `admin/content/library` |
| `dialogues()` / `saveDialogues()` | Satu konteks per halaman (`?konteks=`, POST `context`). `levelId = 0`: konteks global `intro` (bawaan), `map_intro`, `ending`; selain itu `region_intro`, `level_open` (bawaan), `level_done` (`Config\Gelita::$dialogueContexts`). Teks, judul, tokoh, pose, efek, audio ID/EN; view `admin/content/dialogues` |
| `verify()` | `App\Libraries\ContentVerifier::run()` (pemeriksaan yang sama dengan `php spark gelita:content:verify`); view `admin/content/verify` |

### `Admin\NarrationController` (admin)

Aturan impor di `App\Libraries\NarrationImporter` (dipakai juga `gelita:narration:import`); status dan persetujuan massal di `App\Libraries\NarrationCatalog`. Cakupannya baris naskah `dialogues` dan narasi petunjuk Cari Objek (`petunjuk-{node}-NN`).

| Method | Aturan |
|---|---|
| `index()` | Baris narasi per konteks/wilayah dengan status audio ID/EN, kemajuan dan jumlah draft per bahasa; view `admin/narration/index` |
| `uploadForm()` | Batas unggahan efektif (`uploadLimits()`: terkecil dari `Config\Gelita::$maxUploadBytes` dan `upload_max_filesize`; `post_max_size`; `max_file_uploads`) + laporan dari flash `narration_report`; view `admin/narration/upload` |
| `upload()` | `locale` id/en; `getFileMultiple('files')`; hanya berkas unggahan sah yang diteruskan, sisanya dilaporkan gagal; `dry_run` = cocokkan nama saja; jumlah berkas ≥ `max_file_uploads` → peringatan; audit `narration_import` bila ada rekaman baru/diganti |
| `importFolder()` | Sama, sumber `public/assets/audio/narasi/{locale}/` |
| `approveAll()` | `NarrationCatalog::approveDrafts($locale)` (audit `audio_approve_bulk`); `back=audio` kembali ke halaman Rekaman suara |
| `recordingList()` | XLSX daftar rekaman untuk pengisi suara, dikirim dari memori; audit `narration_list_download` |

### `Admin\MediaController` (admin)

| Method | Aturan |
|---|---|
| `index()` | Tabel slot gambar & video: pratinjau, kode, label, isi yang diharapkan (`description`), ukuran tampil & berkas, status Asli / Gambar contoh / Kosong, pemakaian; filter `?status=asli\|mockup\|kosong`, `?jenis=image\|video`, `?kelompok=` (`ImageSlots::GROUPS`), `?wilayah=`, `?tantangan=tmg-4`, `?cari=`; label and description fall back to `App\Libraries\ImageSlots` for official slots; unggah per slot; hasil pindai; view `admin/media/index` |
| `show($id)` | Slot detail (added October 2026): what the picture should show, enforced or recommended file size, display size, format, transparency, fallback, every game screen with its public path (`MediaUsage::forMedia()`, character poses from the story lines), map pins from `levels`/`challenge_nodes`, upload form with `back` = this page, previous/next slot in the same group; view `admin/media/show` |
| `checklist()` | `App\Libraries\AssetChecklist::groups()` + ringkasan: slot yang belum berberkas, frame pose tokoh, latar/peta/lencana wilayah, gambar tantangan per tantangan (gambar contoh dihitung belum ada), poster video Pustaka, rekaman narasi; view `admin/media/checklist` |
| `upload()` | `asset_key` wajib; `file: uploaded[file]\|max_size[file,65536]`; `App\Libraries\MediaStore::store()` (nama resmi dari `asset_key`, dimensi dibaca server, ukuran slot lewat `ImageFitter` (lebih besar diperkecil), `is_placeholder = 0`, audit `media_upload`) |
| `deactivate($id)` | `is_active = 0`, audit `media_deactivate` |
| `audioIndex()` | Semua `audio_assets` + jumlah narasi draft per bahasa; view `admin/media/audio` |
| `uploadAudio()` | `asset_key`, `locale`, `context_code`, `transcript` wajib; durasi dibaca server; status selalu `draft` (unggah ulang `asset_key` yang sama mengembalikannya ke draft); audit `audio_upload` |
| `approveAudio($id)` | `approval_status = approved`, `approved_by`, `approved_at`; audit `audio_approve`. Hanya audio yang disetujui yang diputar untuk siswa |
| `scan()` | `App\Libraries\MediaIntegrity::check()` (berkas hilang, ukuran berubah, sha256 tidak cocok); audit `media_scan` |

### `Admin\StudyController` (admin)

| Method | Aturan |
|---|---|
| `index()` / `store()` / `update($id)` | Penelitian: `code` (unik, tidak diubah saat update), `name`, `status` ∈ `draft,active,closed` (*active* = Berjalan), `default_locale`, `unlock_mode` ∈ `sequential,free`, `retention_days`; audit `study_create` / `study_update`. Halaman menampilkan **daftar siap penelitian** (`App\Libraries\ResearchReadiness`: 0 gambar contoh aktif, semua narasi disetujui, `ContentVerifier::run(true)` tanpa galat). Mengubah status menjadi `active` saat daftar belum hijau ditolak kecuali POST `confirm_not_ready = 1`; view `admin/study/index` |
| `releases()` / `storeRelease()` | Versi permainan (`release_code` unik, `app_version`, `content_version`, `asset_version`, `scoring_version`, `notes`), dibuat nonaktif; audit `release_create`; view `admin/study/releases` |
| `activateRelease($id)` | `GameReleaseModel::activate()`, flush cache konten; audit `release_activate`. Sesi baru memakai versi ini |

*(Asset package Stage 6, English.)* Release activation now goes through `App\Services\ReleaseActivation`, shared with `PackageController`:

| Method | Rule |
|---|---|
| `activateRelease($id)` | `ReleaseActivation::activate()`. While a study is `active`, a release whose package (`AssetPackageService::packageOfRelease()`) differs from the active release's package needs POST `confirm_running_study = 1` and a non-empty `reason` (max 500 characters); otherwise it redirects to `confirmActivation`. Audit `release_activate` metadata: `release_code`, `package_from`, `package_to`, `reason` (null when no confirmation was needed), `running_study`, `via` (`rilis` \| `paket`). Package statuses follow the active release (`terbit` / `ditarik`) |
| `confirmActivation($id)` | GET page with the package change, the running study, the tick, and the reason; redirects back to Rilis when no confirmation is needed; view `admin/study/confirm-activation` |
| `store()` / `update($id)` | When the status changes to `active`, `research_studies.package_code` = the active release's package (rencana-paket-aset §7.2); kept while the study stays active; audit metadata `package_code`. The checklist has a fourth item, **Paket terbit, lengkap, dan terverifikasi** |
| `releases()` | Also shows package mode, a link to Paket aset, and the changed-asset notice; during a running study, rows with another package link to `confirmActivation` |

### `Admin\PackageController` (admin)

*(New section, English.)* Rilis → Paket aset, `docs/rencana-paket-aset.md` §6.3, §7.2, §8.7, §9.2. Package mode has no setting of its own: it is on when the active release's `asset_version` names a built package.

| Method | Rule |
|---|---|
| `index()` | Mode paket (Nyala/Mati, or "Mode paket dimatikan dari server" when `gelita.paket.aktif = false`), active package size/date/verification, changed-asset notice, running build with progress, the newest unpublished build's summary (`AssetPackageService::summary()`: size per group, files new to devices that hold the active package, warnings), lab permission, last 10 packages with **Pratinjau** links (`/?paket={code}`); view `admin/study/package` |
| `startBuild()` | `AssetPackageBuilder::start()` with the staff id; resumes a build still running (`runningBuild()`, under 24 h) instead of starting a second one. AJAX (`X-Requested-With`) → `{package_code, done, total, finished, percent, step_url}`; otherwise redirect to the page |
| `stepBuild($code)` | Builder steps of 5 files until the time budget is used (4 s for the page driver, 20 s without JavaScript); when everything is processed, `finish()` once under a file lock (`writable/paket/{code}.finish.lock`), so two tabs never finish twice. Errors → JSON `PACKAGE_BUILD` 409 or a flash message |
| `publish($code)` | Refuses an unknown/unfinished package or the active one. Running-study check first (no release is created when refused), then `ReleaseActivation::createForPackage($code)` + `activate()` with `via = paket` |
| `switchOff()` | Same with `createForPackage(null)` (asset_version empty); refused when already off |
| `saveOnlineLab()` | `AssetPackageService::setOnlineLabAllowed()`; audit `package_online_lab` `{online_lab_allowed}` |
| `verify()` | `AssetPackageService::verification($code, true)`: re-hash the active package now and refresh the cached result the checklist reads |

### `Admin\GovernanceController` (admin)

Logikanya di `App\Services\RetentionService`.

| Method | Aturan |
|---|---|
| `index()` | 30 permintaan penghapusan terakhir, batas retensi (`sessionIdleMinutes`, `attemptAbandonHours`, `sessionAbandonDays`, `exportRetentionDays`); view `admin/governance/index` |
| `deletePreview()` | Cakupan: nomor siswa, sesi, atau penelitian; `mode` `soft`/`hard`; hitung baris terdampak tanpa menghapus, simpan `data_deletion_requests` status `preview`; audit `delete_preview` |
| `deleteExecute($id)` | Field `confirm` harus persis `HAPUS`; permintaan harus berstatus `preview`; dijalankan dalam transaction; audit `delete_execute` |
| `deleteCancel($id)` | Hanya permintaan `preview`; audit `delete_cancel` |
| `audit()` | `audit_logs` dengan filter `action` dan rentang tanggal, 50 per halaman; view `admin/governance/audit` |
| `runRetention()` | Menjalankan sekarang pekerjaan cron `gelita:retention:run`; audit `retention_run` |

Penghapusan tidak pernah senyap. Tidak ada endpoint lain yang menghapus data penelitian.

### `Admin\SchoolController` (admin)

| Method | Aturan |
|---|---|
| `index()` | Statistik (sekolah resmi, belum terverifikasi, dipakai siswa), sumber data `sekolah-jateng.meta.json`; cari `?q=` (NPSN, nama, kecamatan, desa) dan `?kab=` → maks. 50 baris; entri belum terverifikasi beserta saran; jumlah yang cocok jelas; view `admin/schools/index` |
| `merge($id)` | POST `target_id` (saran) atau `target_npsn`; siswa dan akun guru pindah ke sekolah tujuan; audit `school_merge` |
| `autoMerge()` | Gabungkan semua entri yang cocok jelas (`SchoolDirectory::autoMerge()`) |
| `verify($id)` | POST `npsn` (8 angka, belum dipakai) + `name`; audit `school_verify` |

### `Admin\StaffController` (admin)

| Method | Aturan |
|---|---|
| `index()` | Akun tanpa `password_hash`, status terkunci dan wajib-ganti; view `admin/staff/index` |
| `store()` | `username: required\|alpha_dash\|min_length[3]\|max_length[100]\|is_unique`; `role` ∈ `admin,guru`; `password` 12–72; guru wajib punya sekolah (NPSN didahulukan, selain itu pilihan daftar sekolah yang dipakai); `must_change_password = 1`; audit `staff_create` |
| `update($id)` | Nama, email, peran, sekolah, aktif; admin tidak dapat menurunkan peran atau menonaktifkan akunnya sendiri; audit `staff_update` |
| `resetPassword($id)` | Konfirmasi `RESET`; `StaffUserModel::resetToTemporary()` (wajib ganti); sandi sementara ditampilkan sekali dengan `Cache-Control: no-store`; audit `staff_password_reset` |
| `deactivate($id)` | Bukan akun sendiri; `is_active = 0`; audit `staff_deactivate` |

---

## Autentikasi

Session CodeIgniter memakai `DatabaseHandler` (tabel `ci_sessions`, cookie `gelita_session`, kedaluwarsa 4 jam). Tidak ada cookie khusus berumur panjang dan tidak ada "ingat saya" — komputer sekolah dipakai bergantian.

### Siswa

* Pendaftaran di `/daftar/1..3`: nama pengguna, kata sandi kuat, ulangi sandi, dan centang persetujuan siswa (`participants.assented_at`), bersama data diri dan sekolah.
* Kebijakan sandi `Config\Gelita::$passwordPolicy`: 8–64 karakter, huruf besar, huruf kecil, angka, simbol, tidak memuat nama pengguna; pendaftaran hanya menerima tingkat `strong`. Diputuskan `App\Libraries\PasswordPolicy` di server (aturan validasi `strong_password`); JavaScript hanya menampilkan syarat yang dikirim server.
* `password_hash()` / `password_verify()` dengan `PASSWORD_DEFAULT`.
* Masuk di `/masuk`. Throttle `Config\Gelita::$loginThrottle['participant']`: 8 kegagalan → terkunci 5 menit.
* Setelah masuk: `session()->regenerate(true)`, lalu `participant_id` dan `game_session_id`. Login melanjutkan sesi studi aktif yang dapat dilanjutkan (event `session_resumed`, `device_logs` via `resume`) atau membuat sesi baru (`session_started`, `device_logs` via `login`).
* Sesi berstatus `completed` tetap dapat dibuka (peta, hasil, profil, refleksi, mengulang tantangan); pengerjaan ulang tetap tercatat.
* Sesi `active` yang tidak aktif melebihi `Config\Gelita::$sessionIdleMinutes` (45) ditandai `paused` oleh retensi; masuk berikutnya melanjutkannya.
* Siswa lupa sandi → guru mereset dari `/admin/peserta/{id}`; siswa wajib membuat sandi baru di `/ganti-sandi`. Tidak ada reset lewat email.

| Filter | Lolos bila | Gagal |
|---|---|---|
| `participantAuth` | `participant_id` ada dan pesertanya belum dihapus | redirect `/masuk` |
| `gameSession` | `participantAuth` + `must_change_password = 0` + `game_session_id` milik peserta, status ≠ `abandoned` | `/masuk` (GET menyimpan tujuan) atau `/ganti-sandi` |
| `apiSession` | sama dengan `gameSession` | `401 INVALID_SESSION` / `403 PASSWORD_CHANGE_REQUIRED` |

### Staf (admin & guru)

* `password_hash()` / `password_verify()` dengan `PASSWORD_DEFAULT`; sandi 12–72 karakter.
* Throttle `Config\Gelita::$loginThrottle['staff']`: 5 kegagalan → terkunci 15 menit. Salah tebak sandi saat ini di `/admin/akun/sandi` ikut throttle yang sama.
* Semua percobaan masuk (berhasil dan gagal) masuk `audit_logs` dengan hash IP, bukan IP mentah.
* Sandi yang diketahui admin (akun baru, reset) menyalakan `staff_users.must_change_password`. Selama bernilai 1, `StaffAuthFilter` hanya membuka `/admin/akun/sandi` (dibaca dari database setiap request, jadi reset saat staf sedang masuk langsung berlaku). Akun `admin` dari seeder tidak wajib ganti karena sandinya dipilih operator lewat `GELITA_ADMIN_PASSWORD`.
* Tidak ada "ingat saya".

---

## Otorisasi

### Peran

| Peran | Boleh |
|---|---|
| `admin` | Semua data semua sekolah; konten, narasi, media; penelitian & versi permainan; ekspor beridentitas dan anonim termasuk lembar Event Mentah dan kunci jawaban; penghapusan data dan retensi; direktori sekolah; akun staf; audit log |
| `guru` | Beranda, siswa, sesi, kritik & saran, dan dataset `/api/admin/*` **sekolahnya saja**; reset sandi siswa sekolahnya; ekspor dan CSV tabel **anonim saja** tanpa Event Mentah dan kunci jawaban; ganti sandi sendiri. Tidak dapat mengubah konten, menghapus data, atau mengelola akun |

Siswa tidak punya akses apa pun ke panel.

### Penegakan berlapis

1. **Route** — grup `staffAuth`, lalu `staffRole:admin` untuk bagian khusus admin.
2. **Controller** — `schoolScope()` / `scopedFilters()` dipanggil di setiap method yang membaca data siswa; siswa/sesi di luar cakupan dibalas 404 (HTML) atau `403 FORBIDDEN` (API). Jangan mengandalkan filter route saja.
3. **Service** — `AnalyticsService` dan `RawDataService` diikat ke cakupan sekolah (`StaffScope`) dan menambahkan `participants.school_id` di setiap query; `ExportService` membaca ulang hak pemohon dari `staff_users`.

Tiga lapis ini disengaja: kesalahan di satu lapis tidak langsung membocorkan data siswa. Untuk halaman HTML, akses tanpa hak dibalas `404`, bukan `403`, agar struktur panel tidak terpetakan.

---

## Respons API dan kode galat

Semua respons `/api` dan `/api/admin` berbentuk seragam dan membawa header `X-Request-Id`.

```json
{ "success": true,  "data": { … }, "request_id": "a1b2c3d4" }
{ "success": false, "code": "INVALID_RESPONSE", "message": "…", "request_id": "a1b2c3d4" }
```

* Sukses dibentuk `BaseController::ok()`; gagal oleh `BaseController::fail()` (controller) atau helper `api_error()` (filter). Respons gagal boleh membawa kunci tambahan: `errors` (per isian), `items` (`INCOMPLETE`), `redirect` (`TRIES_EXHAUSTED` pada pengerjaan yang sudah ditutup), `retry_after` (`RATE_LIMITED`).
* Body JSON dibaca `BaseController::jsonBody()`; JSON rusak menjadi array kosong lalu ditolak validasi.
* Exception tak tertangani di `/api` diubah menjadi JSON oleh `App\Libraries\GelitaExceptionHandler` (filter after tidak berjalan saat exception): status 5xx → `SERVER_ERROR`; 400/422 → `INVALID_PAYLOAD`; 401 → `INVALID_SESSION`; 403 → `FORBIDDEN`; 404/405 → `NOT_FOUND`; 409 → `CONFLICT`; 413 → `TOO_MANY_EVENTS`; 429 → `RATE_LIMITED`. Di production pesan, kelas, dan stack trace exception tidak pernah dikirim; semuanya hanya masuk `writable/logs/` dengan `request_id` yang sama, sehingga guru cukup menyebut `request_id` saat melapor.

| code | HTTP | Kapan |
|---|---:|---|
| `INVALID_SESSION` | 401 | Siswa/staf belum masuk, session kedaluwarsa, sesi permainan ditinggalkan; juga token CSRF ditolak tanpa login |
| `CSRF_EXPIRED` | 403 | Token CSRF ditolak padahal masih masuk (halaman terlalu lama terbuka, masuk ulang di tab lain) — klien menawarkan muat ulang |
| `PASSWORD_CHANGE_REQUIRED` | 403 | Sandi siswa direset guru, atau sandi sementara staf, belum diganti |
| `FORBIDDEN` | 403 | Peran tidak mencukupi, attempt/sesi/ekspor bukan milik pemanggil |
| `NOT_FOUND` | 404 | Wilayah, tantangan, attempt, petunjuk, atau ekspor tidak ada |
| `INVALID_PAYLOAD` | 422 | Validasi gagal; disertai `errors` |
| `INVALID_RESPONSE` | 422 | `item_id` bukan milik attempt, bentuk `answer` tidak sesuai, atau engine salah jalur (`/responses` vs `/check`) |
| `INCOMPLETE` | 422 | `/check`: masih ada soal belum benar yang tidak diisi; disertai `items` |
| `ATTEMPT_CLOSED` | 409 | Attempt sudah `completed`/`abandoned` |
| `TRIES_EXHAUSTED` | 409 | `/check` setelah ketiga kesempatan terpakai; `redirect` ke `/selesai/{id}` bila pengerjaannya sudah ditutup |
| `CLUE_CLOSED` | 409 | `/responses`: petunjuk Cari Objek itu sudah ditemukan atau dilewati — klien memuat ulang |
| `TRIES_REMAINING` | 409 | `/complete` pada pengerjaan yang belum semua benar dan masih punya kesempatan |
| `ATTEMPT_CHANGED` | 409 | `/check`, `/responses`, `/complete`: permintaan lain (tab kedua, kiriman ulang) lebih dulu mengubah pengerjaan ini, jadi permintaan ini tidak diterapkan (juga `/check` dengan `check_no` usang); `redirect` ke `/selesai/{id}` bila pengerjaannya sudah ditutup, selain itu klien memuat ulang |
| `LEVEL_LOCKED` | 409 | Wilayah belum terbuka |
| `LIBRARY_LOCKED` | 409 | `/api/library/{levelId}`: wilayah terbuka tetapi tantangannya belum semua selesai |
| `CONFLICT` | 409 | Exception 409 lain yang tidak ditangani controller |
| `TOO_MANY_EVENTS` | 413 | Batch melebihi `maxEventsPerBatch` |
| `RATE_LIMITED` | 429 | Jatah per sesi atau per hash IP habis; disertai `retry_after` |
| `SERVER_ERROR` | 500 | Exception tak tertangani |

Pesan untuk siswa diambil dari `app/Language/{id,en}/Game.php` (mis. `Game.errTriesExhausted`, `Game.errClueClosed`, `Game.errTriesRemaining`, `Game.errIncomplete`, `Game.errAttemptChanged`, `Game.errPageExpired`); pesan `/api/admin` berbahasa Indonesia.

---

## Aturan Sistem

Nomor aturan dirujuk dari kode (mis. `BaseApiController` → aturan 2, `safe_internal_url()` → aturan 11); jangan mengubah urutannya.

1. Semua penilaian, bintang, serpihan, dan status wilayah/tantangan ditentukan server. Klien tidak pernah menjadi sumber kebenaran.
2. Setiap endpoint memvalidasi: sesi valid → attempt/node milik sesi itu → item milik attempt itu. Ketiganya, bukan salah satu.
3. `attempt_id` dari URL selalu dicocokkan dengan sesi berjalan sebelum dipakai.
4. Mengganti bahasa tidak membuat sesi baru dan tidak mereset progres, jawaban, bintang, atau status penyelesaian.
5. Aturan 3 kesempatan ditegakkan server: pengerjaan hanya tertutup karena semua benar (`solved`) atau kesempatan habis (`tries_exhausted`); permintaan yang melanggarnya dibalas `TRIES_EXHAUSTED`, `CLUE_CLOSED`, `TRIES_REMAINING`, atau `INCOMPLETE`. Permintaan bersamaan diserialkan dengan UPDATE bersyarat (`check_count`, `try_count`, `status` harus masih bernilai lama); yang kalah dibatalkan seluruhnya dan dibalas `ATTEMPT_CHANGED` (atau `CLUE_CLOSED`/`TRIES_EXHAUSTED` bila itu keadaan terbarunya).
6. Guru tidak dapat mengekspor data beridentitas dan tidak dapat melihat siswa dari sekolah lain.
7. Setiap ekspor, unduhan ekspor, dan penghapusan menghasilkan baris `audit_logs`.
8. Penghapusan data selalu dua langkah: pratinjau jumlah terdampak, lalu eksekusi dengan konfirmasi teks `HAPUS`.
9. CSRF aktif untuk semua POST, termasuk `/api`. JavaScript mengirim token lewat header `X-CSRF-TOKEN`.
10. Rate limit per sesi untuk endpoint tantangan (`attempts`) dan event (`events`): 120 request/menit memakai cache CodeIgniter, agar satu tab bermasalah tidak membanjiri database. Endpoint tanpa login dibatasi per hash IP.
11. Redirect yang berasal dari input pengguna (`redirect_to`, `redirect_after_login`) wajib divalidasi sebagai path internal (`safe_internal_url()`).
12. Kata sandi (siswa maupun staf, termasuk sandi sementara hasil reset) tidak pernah ditulis ke flash data, `old()`, session, log, `game_event_logs`, maupun `audit_logs`.
13. Form masuk siswa dan staf selalu memberi pesan identik untuk nama pengguna tidak ada dan sandi salah.
14. Guru hanya dapat mereset sandi siswa di sekolahnya; setiap reset tercatat di `audit_logs`.
15. Request HEAD dilayani route GET dengan handler **dan** filter yang sama (`HeadAsGetRouteCollection`). Memetakan handler tanpa filter akan membuka halaman staf tanpa login. Filter yang punya efek samping khusus GET (menyimpan tujuan login, `?lang=`, `screen_viewed`) memeriksa `getMethod() === 'GET'`, sehingga tidak berjalan untuk HEAD.
16. Sandi staf yang diketahui admin (sandi awal akun baru, sandi sementara hasil reset) wajib diganti pemiliknya sebelum halaman panel lain dan `/api/admin/*` dapat dipakai (`staff_users.must_change_password`, ditegakkan `StaffAuthFilter`).

---

## Keterkaitan dokumen

* [01_DATABASE.md](01_DATABASE.md): struktur tabel, daftar `event_type` dan `interaction_type`.
* [02_PROJECT_FOUNDATION.md](02_PROJECT_FOUNDATION.md): `BaseController`, alias filter, `Config\Gelita`, helper.
* [03_MODEL_ENTITY.md](03_MODEL_ENTITY.md): Model, Entity, dan Service yang dipanggil controller — `SessionService`, `ChallengeService`, `ScoringService`, `EventService`, `AnalyticsService`, `RawDataService`, `ExportService`, `ContentImportService`, `ContentRepository`, `RetentionService`, `SchoolDirectory`.
* [05_VIEW_UI.md](05_VIEW_UI.md): isi view yang dipanggil controller di atas.
* [06_JAVASCRIPT.md](06_JAVASCRIPT.md): klien yang memakai endpoint `/api` dan kode galatnya.
* [07_FEATURE_INTEGRATION.md](07_FEATURE_INTEGRATION.md): alur fitur dari ujung ke ujung.

Pemeriksaan otomatis: `tests/` memuat uji route (mis. `RouteWiringTest`) dan uji aturan 3 kesempatan (`TryLimitTest`); jalankan `vendor/bin/phpunit`.

## Research revision 10, part 2: field sessions (28 §15)

Game (filter `participantAuth` + `screenView`, no game session needed):

| Route | Controller::method | Notes |
|---|---|---|
| `GET`, `POST /token` | `Game\FieldTokenController::form`, `::submit` | Token screen (P2–P5); wrong tokens throttled 10 per 10 minutes, then 5 minutes; screen code `field_token` |
| `GET /sampai-jumpa` | `Game\FieldTokenController::closed` | "See you" screen; screen code `field_closed` |
| `GET`, `POST /daftar/0` | `Game\RegisterController::token`, `::saveToken` (through `step`/`saveStep`) | Field mode only: registration starts with the token; step 2 (school) is skipped |

Panel (group `staffAuth`):

| Route | Controller::method | Capability |
|---|---|---|
| `GET /admin/sesi-lapangan` | `FieldSessionController::index` | `field_sessions.manage` |
| `GET`, `POST /admin/sesi-lapangan/baru` | `::create`, `::store` | `field_sessions.manage` |
| `GET /admin/sesi-lapangan/{id}` | `::show` | `field_sessions.manage` |
| `POST /admin/sesi-lapangan/{id}/jam`, `/token-baru`, `/tutup` | `::updateHours`, `::regenerateToken`, `::close` | `field_sessions.manage` |
| `GET`, `POST /admin/sesi-lapangan/{id}/kursi` | `::seats`, `::saveSeats` | `field_sessions.manage` |
| `GET`, `POST /admin/sesi-lapangan/{id}/susulan` | `::makeupForm`, `::storeMakeup` | `field_sessions.manage` |
| `GET /admin/pemantauan` | `MonitoringController::index` | `monitoring.view` (admin: numbers only) |
| `GET /api/admin/monitoring` | `Api\AdminApiController::monitoring` | `monitoring.view` |
| `POST /admin/sekolah/kode-penelitian` | `SchoolController::setResearchCode` | `schools.manage` |

The field mode switch and meeting plan are fields of the study form (`studies.manage`); they cannot change while a field session is open.

Gate (`Filters\ParticipantCheck`, status `CLOSED`): with field mode on and the study `active`, game pages redirect to `/token` (no game session of a field session) or `/sampai-jumpa` (its field session closed, which pauses the game session once with `session_paused {via: field_session_closed}`). The API answers `409 FIELD_SESSION_CLOSED`, except `POST /api/events`, `/api/audio-events`, and `/api/session/heartbeat` (the heartbeat then extends nothing). `ChallengeService::openNode()` also refuses a challenge whose predecessor in the region is not done (`409 NODE_LOCKED`, K17).

## Research revision 10, part 4: routes (30 §16)

All in the `staffAuth` group with `staffCan`:

| Route | Controller::method | Capability |
|---|---|---|
| `GET`, `POST /admin/sesi-lapangan/{id}/izin` | `ConsentController::edit`, `::save` | `field_sessions.manage` |
| `GET /admin/permintaan-data`, `GET /admin/permintaan-data/{id}` | `DataRequestController::index`, `::show` | `deletion.request`, `deletion.confirm`, `deletion.execute` (a `peneliti` sees own requests only) |
| `GET`, `POST /admin/permintaan-data/baru` | `::create`, `::store` | `deletion.request` |
| `POST /admin/permintaan-data/{id}/konfirmasi`, `…/tolak` | `::confirm`, `::reject` | `deletion.confirm` |
| `POST /admin/permintaan-data/{id}/batal` | `::cancel` | `deletion.request` (the filer, before confirmation) |
| `POST /admin/permintaan-data/{id}/centang` | `::tick` | per item (`DataRequestService::TICK_CAPABILITY`) |
| `GET /admin/permintaan-data/{id}/berita-acara` | `::minutes` | `deletion.confirm`, `deletion.execute` |
| `GET /admin/tindak-lanjut` | `RiskFollowUpController::index` | `risk_flags.view` |
| `GET`, `POST /admin/peserta/salinan` | `ParticipantCopyController::form`, `::download` | `research.export` |
| `POST /admin/peserta/{id}/betulkan`, `…/aktifkan` | `ParticipantController::correct`, `::reactivate` | `participants.accounts` |
| `GET /admin/ekspor/izin` | `ExportController::consentRegister` | `research.export` |
| `GET /admin/studi/{id}/penutupan` | `StudyClosureController::show` | `studies.view` |
| `POST /admin/studi/{id}/penutupan/hitung`, `…/jalankan` | `::preview`, `::run` | `identities.replace` |
| `GET`, `POST /admin/studi/{id}/penutupan/nama` | `::feedbackNames`, `::saveFeedbackNames` | `research.export` |

No new game or API route. `/masuk` and `/token` refuse a deactivated account (`Game.loginInactive`).
