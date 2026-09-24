# 04_CONTROLLER_ROUTE.md — Route, Controller, Authentication, Authorization

> **Revisi 2 (21 September 2026).** Siswa kini **registrasi dan login dengan nama pengguna + kata sandi kuat** (literasi keamanan digital). Route `/lanjutkan` dan cookie `gelita_session` 30 hari dihapus; diganti `/masuk`, `/ganti-sandi`, dan session CI4. Ditambahkan: cek ketersediaan nama pengguna, reset sandi siswa oleh guru, impor workbook bank soal, editor teks bacaan bersama. Impor per-node dihapus karena sudah tercakup impor bank soal.

---

## Tujuan

Membuat seluruh route, controller, filter autentikasi, dan aturan otorisasi GELITA. Setelah tahap ini selesai, seluruh URL aplikasi dapat diakses dan mengembalikan respons yang benar (view kosong/placeholder masih boleh, karena view dikerjakan tahap 5).

---

## Konteks

Tiga area dengan karakter berbeda:

| Area | Prefix | Identitas | Respons |
|---|---|---|---|
| Game | `/` | `session('participant_id')` + `session('game_session_id')` setelah login siswa | HTML (server-rendered) |
| Admin | `/admin` | `session('staff_id')` setelah login staf | HTML |
| API | `/api` | session siswa (game) atau session staf (admin) | JSON |

**Siswa wajib registrasi dengan nama pengguna dan kata sandi kuat.** Membuat kata sandi kuat adalah bagian dari materi literasi keamanan digital: halaman registrasi menunjukkan syarat yang belum terpenuhi dan menolak sandi yang belum kuat. `participant_code` (mis. `GLT-000123`) tetap dibuat server sebagai kode pseudonim penelitian, tetapi tidak dipakai untuk login.

**Staf punya kata sandi** dengan dua role: `admin` dan `guru`. Akun staf (`staff_users`) dan akun siswa (`participants`) terpisah; form login keduanya juga terpisah (`/admin/login` dan `/masuk`).

Prinsip pembagian kode:

```
Controller  = validasi input → panggil Service → bentuk response
Service     = business rule (sudah dibuat tahap 3)
```

Tidak ada rumus skor, tidak ada query analitik, dan tidak ada perhitungan benar/salah di dalam controller.

---

## Yang Harus Dibuat

| Kategori | Jumlah | Folder |
|---|---:|---|
| Controller Game | 10 | `app/Controllers/Game/` |
| Controller Admin | 11 | `app/Controllers/Admin/` |
| Controller API | 8 | `app/Controllers/Api/` |
| Filter | 7 | `app/Filters/` (kerangka sudah ada dari tahap 2) |
| File route | 1 | `app/Config/Routes.php` |

---

## Struktur File

```text
app/Controllers/
├── BaseController.php                (sudah ada) ok()/fail()/jsonBody()/deviceInfo()
├── Concerns/
│   ├── GameProgress.php              status buka/kunci level & node, lentera, `entry` wilayah
│   └── StaffScope.php                schoolScope(), readFilters(), analytics() — dipakai admin & API admin
├── Game/
│   ├── BaseGameController.php        sesi/peserta request, data HUD, dialogueGate(), introGate(), toMap()
│   ├── HomeController.php            welcome, pilihan akun (/mulai), bahasa
│   ├── GateController.php            gerbang pemain lama, cerita pembuka wajib, jalan ke peta
│   ├── RegisterController.php        persetujuan + pendaftaran akun siswa
│   ├── LoginController.php           masuk, keluar, ganti kata sandi siswa
│   ├── MapController.php             peta Kedu & peta wilayah
│   ├── DialogueController.php        dialog Jaka & Mbah Kedu
│   ├── ChallengeController.php       kartu misi, layar tantangan, hasil
│   ├── LibraryController.php         Pustaka Kedu
│   ├── ProfileController.php         profil peserta
│   └── ReflectionController.php      Balai Refleksi + kritik & saran
├── Admin/
│   ├── BaseAdminController.php       panel(): judul, filter, activeStudy, filterOptions
│   ├── AuthController.php            login/logout staff
│   ├── AccountController.php         ganti sandi sendiri (guru & admin)
│   ├── DashboardController.php       ringkasan
│   ├── ParticipantController.php     daftar & profil peserta
│   ├── SessionController.php         daftar sesi + drilldown event
│   ├── AnalyticsController.php       level, node, item, indikator
│   ├── FeedbackController.php        kritik & saran
│   ├── ContentController.php         level, node, item, option, hint
│   ├── MediaController.php           media & audio (upload, approve)
│   ├── StudyController.php           study, phase, release, scoring profile
│   ├── ExportController.php          XLSX & PDF
│   ├── GovernanceController.php      deletion request, retention, audit log
│   └── StaffController.php           akun guru/admin
└── Api/
    ├── BaseApiController.php         gameSession(), attemptOrFail(), rateLimited()
    ├── SessionApiController.php
    ├── ContentApiController.php
    ├── ChallengeApiController.php
    ├── EventApiController.php
    ├── AudioApiController.php
    ├── ProgressApiController.php
    ├── AuthApiController.php         cek ketersediaan nama pengguna
    └── AdminApiController.php        data untuk chart dashboard
```

---

## Route

`app/Config/Routes.php`:

```php
<?php
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Game\HomeController::index');

// ---------------------------------------------------------------- GAME
$routes->group('', ['namespace' => 'App\Controllers\Game'], static function ($routes) {

    // Tanpa login
    $routes->get('mulai',        'HomeController::start');
    $routes->get('persetujuan',  'RegisterController::consent');
    $routes->post('persetujuan', 'RegisterController::storeConsent');
    $routes->get('daftar',       'RegisterController::form');
    $routes->post('daftar',      'RegisterController::store');
    $routes->get('masuk',        'LoginController::form');
    $routes->post('masuk',       'LoginController::login');
    $routes->post('bahasa',      'HomeController::setLocale');

    // Sudah login, belum tentu boleh bermain (mis. wajib ganti sandi)
    $routes->group('', ['filter' => 'participantAuth'], static function ($routes) {
        $routes->get('ganti-sandi',  'LoginController::changePasswordForm');
        $routes->post('ganti-sandi', 'LoginController::changePassword');
        $routes->get('keluar',       'LoginController::logout');
    });

    // Login + sandi sudah sah + sesi permainan aktif
    $routes->group('', ['filter' => 'gameSession'], static function ($routes) {
        $routes->get('gerbang',                  'GateController::index');
        $routes->get('gerbang/peta',             'GateController::map');
        $routes->get('intro',                    'GateController::intro');
        $routes->get('intro/selesai',            'GateController::finishIntro');
        $routes->get('peta',                     'MapController::kedu');
        $routes->get('wilayah/(:segment)',        'MapController::level/$1');
        $routes->get('dialog/(:segment)',         'DialogueController::show/$1');
        $routes->get('misi/(:segment)/(:num)',    'ChallengeController::brief/$1/$2');
        $routes->get('tantangan/(:segment)/(:num)','ChallengeController::play/$1/$2');
        $routes->get('hasil/(:segment)/(:num)',   'ChallengeController::result/$1/$2');
        $routes->get('selesai/(:num)',            'ChallengeController::finished/$1');
        $routes->get('pustaka/(:segment)',        'LibraryController::show/$1');
        $routes->get('profil',                    'ProfileController::index');
        $routes->get('refleksi',                  'ReflectionController::index');
        $routes->post('refleksi',                 'ReflectionController::store');
    });
});

// ---------------------------------------------------------------- API GAME
$routes->group('api', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => 'jsonResponse',
], static function ($routes) {

    // Tanpa login — dibatasi 20 request/menit per IP
    $routes->get('auth/username-available', 'AuthApiController::usernameAvailable');

    $routes->group('', ['filter' => 'apiSession'], static function ($routes) {
        $routes->get('session',               'SessionApiController::show');
        $routes->post('session/locale',       'SessionApiController::setLocale');
        $routes->post('session/heartbeat',    'SessionApiController::heartbeat');
        $routes->post('session/complete',     'SessionApiController::complete');

        $routes->get('levels',                'ContentApiController::levels');
        $routes->get('levels/(:num)/nodes',   'ContentApiController::nodes/$1');
        $routes->get('nodes/(:num)',          'ContentApiController::node/$1');
        $routes->get('library/(:num)',        'ContentApiController::library/$1');

        $routes->post('nodes/(:num)/attempts',         'ChallengeApiController::open/$1');
        $routes->post('attempts/(:num)/responses',     'ChallengeApiController::respond/$1');
        $routes->post('attempts/(:num)/check',         'ChallengeApiController::check/$1');
        $routes->post('attempts/(:num)/hints',         'ChallengeApiController::hint/$1');
        $routes->post('attempts/(:num)/complete',      'ChallengeApiController::complete/$1');
        $routes->post('attempts/(:num)/abandon',       'ChallengeApiController::abandon/$1');

        $routes->post('events',            'EventApiController::ingest');
        $routes->post('audio-events',      'AudioApiController::ingest');
        $routes->get('progress',           'ProgressApiController::show');
    });
});

// ---------------------------------------------------------------- ADMIN
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {

    $routes->get('login',   'AuthController::loginForm');
    $routes->post('login',  'AuthController::login');
    $routes->get('logout',  'AuthController::logout');

    $routes->group('', ['filter' => 'staffAuth'], static function ($routes) {

        $routes->get('/',            'DashboardController::index');
        $routes->get('dashboard',    'DashboardController::index');

        // Akun sendiri — guru & admin
        $routes->get('akun/sandi',   'AccountController::passwordForm');
        $routes->post('akun/sandi',  'AccountController::changePassword');

        // Data penelitian — guru & admin (scope guru dibatasi sekolahnya)
        $routes->get('peserta',                  'ParticipantController::index');
        $routes->get('peserta/(:num)',           'ParticipantController::show/$1');
        $routes->post('peserta/(:num)/reset-sandi', 'ParticipantController::resetPassword/$1');
        $routes->get('sesi',                     'SessionController::index');
        $routes->get('sesi/(:num)',              'SessionController::show/$1');
        $routes->get('sesi/(:num)/event',        'SessionController::timeline/$1');
        $routes->get('analitik/level',           'AnalyticsController::levels');
        $routes->get('analitik/node',            'AnalyticsController::nodes');
        $routes->get('analitik/node/(:num)',     'AnalyticsController::node/$1');
        $routes->get('analitik/butir',           'AnalyticsController::items');
        $routes->get('analitik/indikator',       'AnalyticsController::indicators');
        $routes->get('analitik/prepost',         'AnalyticsController::prePost');
        $routes->get('masukan',                  'FeedbackController::index');

        // Export — guru & admin, tetapi mode non-anonim hanya admin
        $routes->get('ekspor',                   'ExportController::index');
        $routes->post('ekspor/xlsx',             'ExportController::xlsx');
        $routes->post('ekspor/pdf',              'ExportController::pdf');
        $routes->get('ekspor/unduh/(:num)',      'ExportController::download/$1');

        // Konten — admin saja
        $routes->group('', ['filter' => 'staffRole:admin'], static function ($routes) {

            $routes->get('konten',                       'ContentController::index');
            $routes->get('konten/level/(:num)',          'ContentController::level/$1');
            $routes->post('konten/level/(:num)',         'ContentController::updateLevel/$1');
            $routes->get('konten/node/(:num)',           'ContentController::node/$1');
            $routes->post('konten/node/(:num)',          'ContentController::updateNode/$1');
            $routes->post('konten/node/(:num)/item',     'ContentController::createItem/$1');
            $routes->post('konten/item/(:num)',          'ContentController::updateItem/$1');
            $routes->post('konten/item/(:num)/hapus',    'ContentController::deleteItem/$1');
            $routes->post('konten/item/(:num)/opsi',     'ContentController::saveOptions/$1');
            $routes->get('konten/impor-bank',            'ContentController::importForm');
            $routes->post('konten/impor-bank/pratinjau', 'ContentController::importPreview');
            $routes->post('konten/impor-bank/jalankan',  'ContentController::importRun');
            $routes->get('konten/impor-bank/templat',    'ContentController::importTemplate');
            $routes->get('konten/bacaan/(:num)',         'ContentController::passages/$1');
            $routes->post('konten/bacaan/(:num)',        'ContentController::savePassages/$1');
            $routes->get('konten/pustaka/(:num)',        'ContentController::library/$1');
            $routes->post('konten/pustaka/(:num)',       'ContentController::saveLibrary/$1');
            $routes->get('konten/dialog/(:num)',         'ContentController::dialogues/$1');
            $routes->post('konten/dialog/(:num)',        'ContentController::saveDialogues/$1');
            $routes->post('konten/verifikasi',           'ContentController::verify');

            $routes->get('media',                        'MediaController::index');
            $routes->post('media/unggah',                'MediaController::upload');
            $routes->post('media/(:num)/nonaktif',       'MediaController::deactivate/$1');
            $routes->get('media/audio',                  'MediaController::audioIndex');
            $routes->post('media/audio/unggah',          'MediaController::uploadAudio');
            $routes->post('media/audio/(:num)/setujui',  'MediaController::approveAudio/$1');
            $routes->post('media/pindai',                'MediaController::scan');

            $routes->get('studi',                        'StudyController::index');
            $routes->post('studi',                       'StudyController::store');
            $routes->post('studi/(:num)',                'StudyController::update/$1');
            $routes->get('studi/rilis',                  'StudyController::releases');
            $routes->post('studi/rilis',                 'StudyController::storeRelease');
            $routes->post('studi/rilis/(:num)/aktifkan', 'StudyController::activateRelease/$1');
            $routes->get('studi/skoring',                'StudyController::scoringProfiles');
            $routes->post('studi/skoring',               'StudyController::storeScoringProfile');

            $routes->get('tata-kelola',                      'GovernanceController::index');
            $routes->post('tata-kelola/hapus/pratinjau',     'GovernanceController::deletePreview');
            $routes->post('tata-kelola/hapus/(:num)/jalankan','GovernanceController::deleteExecute/$1');
            $routes->post('tata-kelola/hapus/(:num)/batal',  'GovernanceController::deleteCancel/$1');
            $routes->get('tata-kelola/audit',                'GovernanceController::audit');
            $routes->post('tata-kelola/retensi',             'GovernanceController::runRetention');

            $routes->get('staf',                  'StaffController::index');
            $routes->post('staf',                 'StaffController::store');
            $routes->post('staf/(:num)',          'StaffController::update/$1');
            $routes->post('staf/(:num)/sandi',    'StaffController::resetPassword/$1');
            $routes->post('staf/(:num)/nonaktif', 'StaffController::deactivate/$1');
        });
    });
});

// ---------------------------------------------------------------- API ADMIN
$routes->group('api/admin', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => ['jsonResponse', 'staffAuth'],   // header JSON + X-Request-Id juga untuk API admin
], static function ($routes) {
    $routes->get('summary',          'AdminApiController::summary');
    $routes->get('levels',           'AdminApiController::levels');
    $routes->get('nodes',            'AdminApiController::nodes');
    $routes->get('items',            'AdminApiController::items');
    $routes->get('indicators',       'AdminApiController::indicators');
    $routes->get('prepost',          'AdminApiController::prePost');
    $routes->get('participants',     'AdminApiController::participants');
    $routes->get('sessions/(:num)/timeline', 'AdminApiController::timeline/$1');
    $routes->get('exports/(:num)/status',    'AdminApiController::exportStatus/$1');
});
```

`$routes->setAutoRoute(false);` — routing otomatis dimatikan.

---

## Route Reference

### Game (HTML)

| Method | URI | Controller::method | Param | Auth | Role | Tujuan | Response |
|---|---|---|---|---|---|---|---|
| GET | `/` | HomeController::index | — | tidak | — | logo + satu tombol Mulai | HTML |
| GET | `/mulai` | HomeController::start | — | tidak | — | belum login: pilih "Saya baru" / "Saya sudah punya akun"; sudah login: redirect `/gerbang` | HTML / redirect |
| GET | `/persetujuan` | RegisterController::consent | — | tidak | — | layar persetujuan penelitian | HTML |
| POST | `/persetujuan` | RegisterController::storeConsent | — | tidak | — | simpan centang consent ke session | redirect `/daftar` |
| GET | `/daftar` | RegisterController::form | — | tidak | — | formulir demographic | HTML |
| POST | `/daftar` | RegisterController::store | — | tidak | — | buat akun siswa + participant + sesi | redirect `/intro` |
| GET | `/masuk` | LoginController::form | — | tidak | — | form login siswa | HTML |
| POST | `/masuk` | LoginController::login | — | tidak | — | login + lanjutkan/buat sesi | redirect tujuan tersimpan atau `/mulai`, atau `/ganti-sandi` |
| GET | `/ganti-sandi` | LoginController::changePasswordForm | — | login | — | form ganti sandi | HTML |
| POST | `/ganti-sandi` | LoginController::changePassword | — | login | — | simpan sandi baru | redirect `/mulai` |
| GET | `/keluar` | LoginController::logout | — | login | — | keluar akun | redirect `/` |
| POST | `/bahasa` | HomeController::setLocale | — | opsional | — | ganti bahasa | redirect balik |
| GET | `/gerbang` | GateController::index | — | ya | — | belum menonton cerita pembuka → `/intro`; selain itu pilihan pemain lama | HTML / redirect |
| GET | `/gerbang/peta` | GateController::map | — | ya | — | flash `curtain=map` | redirect `/peta` |
| GET | `/intro` | GateController::intro | — | ya | — | cerita pembuka; "Lewati" hanya bila sudah pernah menonton | HTML |
| GET | `/intro/selesai` | GateController::finishIntro | — | ya | — | isi `intro_seen_at` (idempoten), event `intro_completed`, flash `curtain=map` | redirect `/peta` |
| GET | `/peta` | MapController::kedu | — | ya | — | peta 3 wilayah + narasi `map_intro`; flash `curtain=map` → tirai "Membuka Peta Kedu"; belum menonton cerita pembuka → `/intro` | HTML / redirect |
| GET | `/wilayah/{code}` | MapController::level | level code | ya | — | peta 5 pos | HTML; wilayah baru terbuka yang dialognya belum tampil → redirect `/dialog/{code}` |
| GET | `/dialog/{code}` | DialogueController::show | level code | ya | — | dialog pembuka wilayah; menandai dialog sudah tampil di sesi login | HTML |
| GET | `/misi/{code}/{seq}` | ChallengeController::brief | code, 1..5 | ya | — | kartu misi | HTML; gerbang dialog sama dengan `/wilayah` |
| GET | `/tantangan/{code}/{seq}` | ChallengeController::play | code, 1..5 | ya | — | layar tantangan | HTML; gerbang dialog sama dengan `/wilayah`, diperiksa sebelum attempt dibuka |
| GET | `/hasil/{code}/{seq}` | ChallengeController::result | code, 1..5 | ya | — | riwayat hasil node | HTML |
| GET | `/selesai/{attemptId}` | ChallengeController::finished | attempt id | ya | — | layar bintang & skor | HTML |
| GET | `/pustaka/{code}` | LibraryController::show | level code | ya | — | Pustaka Kedu | HTML |
| GET | `/profil` | ProfileController::index | — | ya | — | profil peserta | HTML |
| GET | `/refleksi` | ReflectionController::index | — | ya | — | Balai Refleksi | HTML |
| POST | `/refleksi` | ReflectionController::store | — | ya | — | simpan kritik & saran | redirect |

### API Game (JSON)

| Method | URI | Controller::method | Auth | Tujuan |
|---|---|---|---|---|
| GET | `/api/auth/username-available?u=` | AuthApiController::usernameAvailable | tidak (rate-limited) | `{available: bool}` untuk umpan balik saat mengetik |
| GET | `/api/session` | SessionApiController::show | sesi | bootstrap payload |
| POST | `/api/session/locale` | SessionApiController::setLocale | sesi | ganti bahasa |
| POST | `/api/session/heartbeat` | SessionApiController::heartbeat | sesi | update aktivitas |
| POST | `/api/session/complete` | SessionApiController::complete | sesi | tandai sesi selesai |
| GET | `/api/levels` | ContentApiController::levels | sesi | 3 level + status unlock |
| GET | `/api/levels/{id}/nodes` | ContentApiController::nodes | sesi | 5 node + status |
| GET | `/api/nodes/{id}` | ContentApiController::node | sesi | metadata node (tanpa item) |
| GET | `/api/library/{levelId}` | ContentApiController::library | sesi | halaman pustaka |
| POST | `/api/nodes/{id}/attempts` | ChallengeApiController::open | sesi | buka node → attempt + payload soal |
| POST | `/api/attempts/{id}/responses` | ChallengeApiController::respond | sesi | satu jawaban item |
| POST | `/api/attempts/{id}/check` | ChallengeApiController::check | sesi | periksa batch jawaban |
| POST | `/api/attempts/{id}/hints` | ChallengeApiController::hint | sesi | buka petunjuk |
| POST | `/api/attempts/{id}/complete` | ChallengeApiController::complete | sesi | tutup attempt + skor |
| POST | `/api/attempts/{id}/abandon` | ChallengeApiController::abandon | sesi | tinggalkan attempt |
| POST | `/api/events` | EventApiController::ingest | sesi | batch raw event |
| POST | `/api/audio-events` | AudioApiController::ingest | sesi | telemetry audio |
| GET | `/api/progress` | ProgressApiController::show | sesi | progres authoritative |

### Admin (HTML) — ringkas

| Method | URI | Controller::method | Role | Tujuan |
|---|---|---|---|---|
| GET | `/admin/login` | AuthController::loginForm | — | form login |
| POST | `/admin/login` | AuthController::login | — | proses login |
| GET | `/admin/logout` | AuthController::logout | staff | keluar |
| GET | `/admin/akun/sandi` | AccountController::passwordForm | guru, admin | form ganti sandi sendiri |
| POST | `/admin/akun/sandi` | AccountController::changePassword | guru, admin | ganti sandi sendiri |
| GET | `/admin/dashboard` | DashboardController::index | guru, admin | KPI + chart |
| GET | `/admin/peserta` | ParticipantController::index | guru, admin | daftar peserta (scoped) |
| GET | `/admin/peserta/{id}` | ParticipantController::show | guru, admin | profil & capaian |
| POST | `/admin/peserta/{id}/reset-sandi` | ParticipantController::resetPassword | guru (sekolahnya), admin | reset sandi siswa, tampilkan sandi sementara sekali |
| GET | `/admin/sesi` | SessionController::index | guru, admin | daftar sesi |
| GET | `/admin/sesi/{id}` | SessionController::show | guru, admin | detail sesi + attempt |
| GET | `/admin/sesi/{id}/event` | SessionController::timeline | guru, admin | linimasa event |
| GET | `/admin/analitik/level` | AnalyticsController::levels | guru, admin | skor per level |
| GET | `/admin/analitik/node` | AnalyticsController::nodes | guru, admin | heatmap kesulitan |
| GET | `/admin/analitik/node/{id}` | AnalyticsController::node | guru, admin | drilldown node |
| GET | `/admin/analitik/butir` | AnalyticsController::items | guru, admin | analisis butir p & D |
| GET | `/admin/analitik/indikator` | AnalyticsController::indicators | guru, admin | penguasaan indikator |
| GET | `/admin/analitik/prepost` | AnalyticsController::prePost | guru, admin | perbandingan fase |
| GET | `/admin/masukan` | FeedbackController::index | guru, admin | kritik & saran |
| GET/POST | `/admin/ekspor*` | ExportController | guru, admin | export XLSX/PDF |
| GET/POST | `/admin/konten*` | ContentController | **admin** | kelola konten, teks bacaan, impor workbook bank soal |
| GET/POST | `/admin/media*` | MediaController | **admin** | kelola media & audio |
| GET/POST | `/admin/studi*` | StudyController | **admin** | studi, fase, rilis, skoring |
| GET/POST | `/admin/tata-kelola*` | GovernanceController | **admin** | hapus data, retensi, audit |
| GET/POST | `/admin/staf*` | StaffController | **admin** | akun guru/admin |

---

## Controller — Spesifikasi

### `Game\HomeController`

| Method | Request | Validasi | Service/Model | Business rule | Response |
|---|---|---|---|---|---|
| `index()` | GET | — | — | satu tombol Mulai → `/mulai`, sama untuk siswa yang sudah maupun belum login; kirim `hideBrand => true` agar HUD tidak mengulang merek | view `game/welcome` |
| `start()` | GET | — | — | `participant_id` dan `game_session_id` ada di sesi → redirect `/gerbang`; selain itu pilihan akun | view `game/start` atau redirect |
| `setLocale()` | POST `locale`, `redirect_to` | `locale: required|valid_locale` | `SessionService::setLocale()` | bila ada sesi → simpan ke `game_sessions.locale` + event `locale_changed`. **Tidak** membuat sesi baru, tidak mereset progres. Selalu simpan juga ke `session('locale')` | redirect ke `redirect_to` yang sudah divalidasi berada di domain sendiri |


### `Game\GateController`

Turunan `BaseGameController`; seluruh route-nya di grup `gameSession`, jadi kepemilikan sesi dan data HUD terjamin.

| Method | Service/Model | Business rule | Response |
|---|---|---|---|
| `index()` | — | `introGate()`: `participants.intro_seen_at` kosong → redirect `/intro`; selain itu sapaan "Selamat datang kembali, {display_name ?: username}!" + kartu "Lihat cerita pembuka" (`/intro`) dan "Langsung ke peta" (`/gerbang/peta`) | view `game/start-choice` + `hudData()` |
| `map()` | — | `toMap()`: flash `curtain=map` untuk layar tirai "Membuka Peta Kedu" | redirect `/peta` |
| `intro()` | `ContentRepository::dialogues(null, 'intro')` → `DialogueModel::global('intro')` | slide dari `dialogues` context `intro` (tokoh, pose, efek, judul, latar, audio disetujui); `canSkip` = `intro_seen_at` terisi — bila `false` nav "Lewati" tidak dirender. "Lewati" menuju `/gerbang/peta` agar tirai peta ikut tampil | view `game/intro` |
| `finishIntro()` | `ParticipantModel::markIntroSeen()`, `EventService` | isi `intro_seen_at` hanya bila masih kosong; event `intro_completed` { first: bool } setiap kali | `toMap()` → redirect `/peta` + flash `curtain=map` |

### `Game\RegisterController`

| Method | Request | Validasi | Business rule |
|---|---|---|---|
| `consent()` | GET | — | tampilkan teks persetujuan versi aktif |
| `storeConsent()` | POST `participant_agree`, `guardian_agree`, `guardian_name` | `participant_agree: required`, `guardian_agree: required` bila `study.require_consent = 1` | simpan ke flash session + stempel waktu; **tidak** menulis DB sebelum peserta terbentuk |
| `form()` | GET | — | bila belum consent dan study mewajibkan → redirect `/persetujuan` |
| `store()` | POST (lihat di bawah) | lihat di bawah | lihat alur di bawah; redirect `/intro` |

**Aturan validasi `store()`:**

```php
[
  'display_name'   => 'required|min_length[2]|max_length[150]',
  'age'            => 'required|integer|greater_than[4]|less_than[81]',
  'gender'         => 'required|in_list[laki-laki,perempuan,lainnya]',
  'class_level'    => 'required|max_length[20]',
  'school_name'    => 'required|min_length[3]|max_length[200]',
  'country_code'   => 'required|max_length[5]',
  'province_code'  => 'required_without[country_other]|permit_empty|max_length[10]',
  'district_code'  => 'required_without[country_other]|permit_empty|max_length[10]',
  'country_other'  => 'permit_empty|max_length[100]',
  'phase'          => 'permit_empty|in_list[umum,pretest,posttest]',  // dipakai hanya bila study.allow_phase_choice = 1
  'locale'         => 'permit_empty|valid_locale',
  'username'       => 'required|valid_username|not_reserved_username|is_unique[participants.username]',
  'password'       => 'required|strong_password[username]',
  'password_confirm' => 'required|matches[password]',
]
```

Bila `allow_phase_choice = 0`, fase selalu `research_studies.active_phase_code` dan field `phase` dari request diabaikan.

**Alur `store()` — termasuk metrik literasi keamanan digital:**

```text
1. Hitung PasswordPolicy::check(password, username) SEBELUM validasi lain.
2. Bila ini percobaan kirim pertama pada sesi pendaftaran ini
   (session('reg_first_criteria') masih kosong)
     → session('reg_first_criteria', met)          // 0..5, bukan isi sandi
3. Bila sandi belum 'acceptable'
     → session('reg_weak_count', +1)
4. Jalankan seluruh validasi. Gagal → kembali ke form dengan:
     - error per field (pesan sandi: lang('Auth.notStrongSubmit') / 'containsUsername' / 'notMatch')
     - old() untuk semua field KECUALI password dan password_confirm (tidak pernah diisi ulang)
5. Lolos → SessionService::registerAndStart($input + [
     'first_submit_criteria' => session('reg_first_criteria'),
     'weak_submit_count'     => session('reg_weak_count') ?? 0 ])
6. Hapus reg_first_criteria & reg_weak_count dari session
7. session()->regenerate(true); set participant_id, game_session_id
8. Redirect /intro dengan flash sambutan: "Ingat nama pengguna dan kata sandimu, ya."
```

Kata sandi tidak pernah ditulis ke flash, `old()`, log, atau event.

### `Game\LoginController`

| Method | Request | Validasi | Business rule | Response |
|---|---|---|---|---|
| `form()` | GET | — | bila sudah login → redirect `/mulai`; pilihan fase hanya tampil bila `allow_phase_choice = 1` | view `game/login` |
| `login()` | POST `username`, `password`, `phase?` | `username: required`, `password: required` | `SessionService::login()`. `invalid` → pesan identik `lang('Auth.loginFailed')` tanpa membedakan nama salah/sandi salah; `locked` → `lang('Auth.locked', [menit])`; `must_change` → redirect `/ganti-sandi`; `ok` → redirect ke URL tujuan tersimpan (`redirect_after_login`) atau `/mulai` | redirect |
| `changePasswordForm()` | GET | — | tampilkan pesan `Auth.mustChange` bila datang dari reset guru; checklist syarat sama dengan registrasi | view `game/change-password` |
| `changePassword()` | POST `current_password`, `password`, `password_confirm` | `current_password: required`, `password: required|strong_password[username]` (username diambil dari akun), `password_confirm: required|matches[password]`; sandi baru ≠ sandi lama | `SessionService::changePassword()`; `must_change_password = 0`; lalu login otomatis untuk melanjutkan/membuat sesi; event `password_changed` (setelah reset guru, sesi baru ada sesudah login, jadi event dicatat pada sesi itu dengan `via: reset`) | redirect `/mulai` + toast |
| `logout()` | GET | — | `SessionService::logout()`: event `session_paused`, attempt `in_progress` **tidak** ditutup agar dapat dilanjutkan; `session()->destroy()` | redirect `/` |

### `Api\AuthApiController`

```php
usernameAvailable()   // GET ?u=nama
                      // validasi valid_username; bila tidak valid → {available:false, reason:'format'}
                      // reserved → {available:false, reason:'reserved'}
                      // dipakai → {available:false, reason:'taken'}
                      // rate limit 20/menit per ip_hash (cache CI4) → 429 RATE_LIMITED
```

Nama provinsi/kabupaten dikirim bersama kodenya dan disimpan sebagai snapshot. Server **tidak** mempercayai nama dari client begitu saja: nama diverifikasi terhadap `public/assets/data/wilayah-id.json` yang dimuat server-side; bila kode tidak dikenal, nama snapshot dikosongkan.

### `Game\MapController`

| Method | Business rule | Response |
|---|---|---|
| `kedu()` | `introGate()` lebih dulu: peserta yang belum menonton cerita pembuka dialihkan ke `/intro`, juga lewat URL yang diketik atau redirect setelah login. Lalu ambil 3 level + status dari `session_progress`. Level terkunci bila `unlock_mode = sequential` dan `sequence > unlocked_level_sequence`. Narasi Jaka dari `ContentRepository::dialogues(null, 'map_intro')`. Flash `curtain` = `map` (dari `toMap()`) → `curtain = true` dan `curtainAssets`: URL `map.kedu`, `bg.map`, frame tokoh sesuai pose narasi (cadangan idle), latar wilayah, dan audio narasi peta bahasa aktif yang disetujui — dimuat tirai dengan progres nyata | view `game/map-kedu` |
| `level($code)` | validasi level ada & terbuka; bila terkunci → redirect `/peta` + toast; ambil 5 node + status (selesai / terbuka / terkunci) dari `challenge_attempts` | view `game/map-level` |

Status node: node ke-`n` terbuka bila `n = 1` atau node ke-`n-1` sudah `completed`. Bila `unlock_mode = free`, semua terbuka (lihat D13 di 01_DATABASE.md; nilai `free` sengaja berbeda dari status wilayah `open`).

### `Game\ChallengeController`

| Method | Business rule | Response |
|---|---|---|
| `brief($code, $seq)` | tampilkan judul + deskripsi node, tombol Mulai | view `game/mission-brief` |
| `play($code, $seq)` | panggil `ChallengeService::openNode()`; render layar sesuai `engine_type`; payload soal ditanam sebagai `<script type="application/json" id="challenge-data">` | view `game/challenge/{engine}` |
| `result($code, $seq)` | daftar attempt `completed` untuk node itu pada sesi ini + terbaik | view `game/challenge-result` |
| `finished($attemptId)` | validasi attempt milik sesi ini & `completed`; tampilkan bintang, skor, ketepatan awal, durasi; bila level tuntas tampilkan pesan Mbah Kedu; bila 15 node tuntas → tombol Balai Refleksi | view `game/challenge-finished` |

Perhatian: `play()` **tidak** melakukan penilaian apa pun. Ia hanya membuka attempt dan menyiapkan payload. Seluruh interaksi jawaban lewat API.

### `Game\LibraryController`, `ProfileController`, `ReflectionController`

| Controller::method | Business rule |
|---|---|
| `LibraryController::show($code)` | halaman pustaka level; catat event `library_opened`; tidak memengaruhi skor sama sekali |
| `ProfileController::index()` | ringkasan capaian peserta pada sesi berjalan: serpihan, ketepatan rata-rata, total waktu, daftar attempt |
| `ReflectionController::index()` | hanya dapat diakses bila `session_progress.completed_nodes` = jumlah node aktif; bila belum → redirect `/peta` |
| `ReflectionController::store()` | validasi `rating: required|integer|greater_than[0]|less_than[6]`; minimal 2 dari 4 textarea terisi; simpan `participant_feedback` + event `feedback_submitted` |

### `Api\SessionApiController`

```php
show()       // GET  → bootstrap payload lengkap
setLocale()  // POST {locale}
heartbeat()  // POST {} → SessionService::heartbeat(); respons {server_time}
complete()   // POST {} → SessionService::completeIfFinished()
```

Bentuk **bootstrap payload** (dipakai `show()`):

```json
{
  "success": true,
  "data": {
    "session": { "code":"...", "locale":"id", "status":"active",
                 "phase":"pretest", "study":"STUDI-KEDU-2026" },
    "participant": { "code":"GLT-000123", "username":"jaka.kedu", "display_name":"..." },
    "progress": { "completed_nodes":7, "completed_levels":1,
                  "unlocked_level_sequence":2, "total_score":78.4,
                  "total_stars":15, "shards":7, "shards_total":15,
                  "current_level_id":2, "current_node_id":9 },
    "levels": [ { "id":1,"code":"temanggung","sequence":1,"name":"Temanggung",
                  "difficulty":"mudah","status":"completed","score":82.1,"stars":12 } ],
    "server_time": "2026-09-21T09:14:22.481000+07:00"
  },
  "request_id": "..."
}
```

### `Api\ChallengeApiController`

| Method | Request body | Validasi | Service | Response |
|---|---|---|---|---|
| `open($nodeId)` | `{}` | node ada, aktif, level unlock | `ChallengeService::openNode()` | payload yang sama dengan `#challenge-data`: `{attempt_id, attempt_no, resumed, elapsed_ms, node:{…, engine_type, allow_retry}, items:[…], hints:[{id, item_id, sequence}], hints_count}` + `word_bank` (rumpang ber-bank), `verdict_options`/`require_reason` (boleh), `passages`; engine `cari` memakai `objects` + `clues` alih-alih `items` |
| `respond($attemptId)` | `{item_id, answer:{...}, client_event_id, occurred_at, sequence_no, reason_text?}` | `item_id: required|item_in_attempt`, `client_event_id: required|max_length[120]` | `ChallengeService::submitAnswer()` | `{correct, first_pass, wrong_click, decoy, already_answered, feedback, correct_option_key, change_count, wrong_click_count, progress:{answered,total}}` — `correct_option_key` hanya terisi sesudah dijawab pada node `allow_retry = false`; `cari` menjawab dengan `answer:{object:<ref>}` |
| `check($attemptId)` | `{answers:[{item_id, answer, reason_text?}], client_event_id, occurred_at}` | tiap `item_id` harus ada di attempt | `ChallengeService::submitCheck()` | `{results:{itemId:bool}, correct_count, total, all_correct, check_count, retry_count, detail:{itemId:{pieces_correct, misplaced?}}}` — `detail` hanya untuk `puzzle_arrange`/`ordering`; `misplaced` (slot keliru) hanya untuk puzzle gambar yang susunan benarnya publik |
| `hint($attemptId)` | `{hint_id, item_id?, client_event_id}` | hint milik node/item attempt | `ChallengeService::useHint()` | `{text, hint_count}` |
| `complete($attemptId)` | `{client_event_id, occurred_at}` | attempt `in_progress`, semua item yang diminta dijawab sudah `answered` atau `skipped` (`ChallengeService::pendingItemIds()`; objek jebakan `cari` tidak dihitung) | `ChallengeService::completeAttempt()` | `{score, stars, first_pass_accuracy, final_accuracy, duration_ms, shards, level_completed, next_level_unlocked, redirect}` |
| `abandon($attemptId)` | `{reason}` | — | `ChallengeService::abandonAttempt()` | `{status:"abandoned"}` |

`respond()` dipakai engine `pilihan` dan `cari` (jawab per item). `check()` dipakai engine `puzzle`, `rumpang`, `boleh` (periksa sekaligus).

### `Api\EventApiController` & `AudioApiController`

```php
ingest()  // POST {events:[{client_event_id, event_type, occurred_at, sequence_no,
          //                level_id?, node_id?, attempt_id?, item_id?, payload?}]}
          // maksimum 50 event per request
          // → {accepted, duplicate, rejected:[{index, reason}]}
```

Rujukan per event memakai `level_id`, `node_id`, `attempt_id`, `item_id` (nama kolom lengkap `challenge_*_id` juga diterima).

API admin (`/api/admin/*`) yang datanya digambar chart menyertakan kunci `chart` — bentuk netral dari `App\Libraries\ChartData` untuk `admin/charts.js` — di samping baris mentahnya: `levels` (batang), `nodes` (heatmap), `indicators` (matriks, plus `per_level`), `prepost` (garis), `participants` (sebaran umur, plus `ages`).

`AudioApiController::ingest()` memakai amplop yang sama, dengan isi per event `{audio_asset_id, action: play|autoplay|pause|replay|complete, listened_ms, completed, occurred_at, client_event_id, attempt_id?}`. `attempt_id` hanya diterima bila attempt itu milik sesi berjalan; kiriman ulang dengan `client_event_id` yang sama dihitung `duplicate` tanpa menulis baris. `play_index` dihitung server: `play`/`autoplay`/`replay` membuka pemutaran baru, `pause`/`complete` termasuk pemutaran berjalan. Aksi lain ditolak per event (`audio_event_invalid`). Balasan: `{accepted, duplicate, rejected:[{index, reason}]}`.

`play` berarti pemain menekan tombol putar; `autoplay` berarti layar bernarasi (cerita pembuka, narasi peta) memutar slide sendiri setelah ketukan "Ketuk untuk mulai" atau saat maju otomatis. Raw event pendampingnya `audio_play` dan `audio_autoplay`. Analitik (`AudioUsageEventModel::usageStats()`) menghitung `total_plays` hanya dari `play` dan menaruh putar otomatis di `total_autoplays`, sehingga dasbor, profil peserta, dan laporan PDF menampilkan keduanya terpisah; sheet ekspor Audio Usage membawa kolom `action` apa adanya.

Status per event yang mungkin dikembalikan: `accepted`, `duplicate`, `rejected`.
Status level request: `200` (ada yang diterima), `401 INVALID_SESSION`, `422 INVALID_PAYLOAD`, `413` bila melebihi batas.

### `Admin\AuthController`

```php
loginForm()   // GET, bila sudah login → redirect dashboard
login()       // POST {username, password}
logout()      // GET
```

Aturan `login()`:

1. Validasi `username: required`, `password: required`.
2. `StaffUserModel::findByUsername()`.
3. Bila user tidak ada **atau** password salah → pesan identik: "Nama pengguna atau kata sandi salah." Jangan bedakan keduanya.
4. Bila `isLocked()` → "Akun terkunci sementara. Coba lagi dalam beberapa menit."
5. Bila `is_active = 0` → tolak dengan pesan yang sama seperti nomor 3.
6. Gagal → `registerFailedLogin()`, audit `login_failed`.
7. Berhasil → `session()->regenerate(true)`, set `staff_id`, `staff_role`, `staff_school_id`, `staff_name`; `clearFailedLogin()`; audit `login`.
8. Redirect ke `redirect_to` bila ada dan internal, selain itu `/admin/dashboard`.

`logout()`: audit `logout`, `session()->destroy()`, redirect `/admin/login`.

### `Admin\AccountController`

```php
passwordForm()     // GET  /admin/akun/sandi — guru & admin (filter staffAuth saja)
changePassword()   // POST {current_password, password, password_confirm}
```

Aturan `changePassword()`:

1. Validasi `current_password: required`; `password: required|min_length[12]|max_length[72]|differs[current_password]` (sama dengan pembuatan akun di `StaffController::store()`; 72 = batas bcrypt); `password_confirm: matches[password]`.
2. Sandi saat ini salah → `registerFailedLogin()`, audit `staff_password_change_failed`, kembali dengan "Kata sandi saat ini salah.".
3. Bila salah tebak itu mengunci akun (throttle staf: 5 kali → 15 menit), kunci sesi staf dihapus dan sesi di-`regenerate(true)`, lalu redirect ke `/admin/login` dengan pesan penguncian. Sesi yang dibajak tidak dapat dipakai menebak sandi.
4. Berhasil → `setPassword()` (sekaligus `must_change_password = 0`), `failed_login_count = 0`, `session()->regenerate(true)`, audit `staff_password_change` dengan metadata `after_reset`. Bila sebelumnya wajib ganti, redirect ke `/admin/dashboard`; selain itu kembali ke form dengan pesan berhasil.
5. Kata sandi tidak pernah masuk flash, `old()`, maupun `audit_logs`.

Tautan **Ubah sandi** ada di kepala setiap halaman panel.

**Wajib ganti sandi.** Sandi yang diketahui admin menyalakan `staff_users.must_change_password` (migration `003400`): `StaffController::resetPassword()` memanggil `setPassword($id, $temporary, true)`, dan `StaffController::store()` menyimpan akun baru dengan nilai 1. Selama bernilai 1:

* `AuthController::login()` mengarahkan ke `/admin/akun/sandi` (bukan `redirect_to`).
* `StaffAuthFilter` hanya membuka `/admin/akun/sandi`. Halaman lain dialihkan ke sana, `/api/admin/*` membalas `403 PASSWORD_CHANGE_REQUIRED`. Nilai dibaca dari database setiap request, sehingga reset saat staf sedang login langsung berlaku.
* Form menampilkan pemberitahuan sandi sementara. `/admin/logout` tetap terbuka (di luar grup `staffAuth`).

Akun `admin` hasil seeder tidak wajib ganti: sandinya dipilih operator lewat `GELITA_ADMIN_PASSWORD`.

### `Admin\DashboardController` & `AnalyticsController`

Semua method mengikuti pola yang sama:

```php
public function levels()
{
    $filters = $this->readFilters();            // dari query string
    $scope   = $this->schoolScope();            // NULL untuk admin, school_id untuk guru
    $data    = service('analyticsService')->levelBreakdown($filters + ['school_id' => $scope]);
    return view('admin/analytics/levels', ['rows' => $data, 'filters' => $filters]);
}
```

`readFilters()` dan `schoolScope()` ditaruh di `Admin\BaseAdminController` yang meng-extend `BaseController`:

```php
protected function schoolScope(): ?int
{
    return session('staff_role') === 'admin' ? null : (int) session('staff_school_id');
}
```

**Penting:** `schoolScope()` dipanggil di setiap method yang membaca data peserta. Jangan mengandalkan filter route saja.

### `Admin\ParticipantController::resetPassword($id)`

```text
1. Peserta harus berada dalam schoolScope() pemanggil (guru: sekolahnya saja) → selain itu 404
2. Konfirmasi: form POST dengan field confirm = "RESET"
3. SessionService::resetPasswordByStaff($id, staffId)
     sandi sementara acak yang memenuhi kebijakan (mis. "Kedu-7342-Lentera")
     must_change_password = 1, failed_login_count = 0, locked_until = NULL
     audit participant_password_reset (TANPA sandi sementara di metadata)
4. Tampilkan sandi sementara SEKALI di halaman hasil, dengan pesan:
   "Berikan kepada siswa. Siswa wajib membuat sandi baru saat masuk."
   Sandi sementara tidak disimpan, tidak dikirim lewat flash, tidak masuk log.
```

### `Admin\ContentController`

| Method | Business rule |
|---|---|
| `updateNode($id)` | validasi `engine_type` tidak boleh diubah bila node sudah punya attempt; ubah judul/instruksi/config; `ContentRepository::flush()`; audit `content_update` |
| `createItem($nodeId)` | validasi `interaction_type` cocok dengan `engine_type` node; `answer_key_json` wajib bila `scorable = 1` |
| `importForm()` | halaman unggah workbook bank soal + riwayat impor |
| `importPreview()` | validasi `file: uploaded|ext_in[xlsx]|max_size[20480]`; simpan sementara di `writable/uploads/`; `ContentImportService::preview()`; tampilkan ringkasan per node, galat per baris (sheet + nomor baris), dan peringatan (`needs_verification`, bank kurang dari minimum) |
| `importRun()` | hanya bila pratinjau terakhir tanpa galat; `ContentImportService::import()` dalam satu transaction; audit `content_import`; hapus berkas sementara |
| `importTemplate()` | unduh templat workbook kosong (`ContentImportService::template()`) |
| `passages($levelId)` / `savePassages($levelId)` | kelola `reading_passages` satu level; passage yang masih dirujuk item tidak boleh dihapus |
| `verify()` | jalankan pemeriksaan: 3 level, 5 node per level, engine valid, bank item ≥ `items_per_round`, tiap item scorable punya `answer_key_json`, tiap `single_choice`/`source_trust` punya tepat 1 opsi benar, `verdict` kunci termasuk `verdict_options` node, `passage_id` satu level dengan node, jumlah pengecoh rumpang ≥ `distractor_count`, dan daftar item `needs_verification`; tampilkan daftar temuan |

Pemeriksaan konten kembar (jawaban yang sama muncul di dua node) ikut dilaporkan `verify()` — ini menjaga analisis butir tidak menghitung konsep ganda.

`dialogues($levelId)` / `saveDialogues($levelId)` mengelola satu konteks naskah per halaman, dipilih lewat `?konteks=` (POST: field `context`). `levelId = 0`: konteks global `intro` (bawaan), `map_intro`, `ending`; selain itu konteks wilayah `region_intro`, `level_open` (bawaan), `level_done`. Konteks di luar daftar `Config\Gelita::$dialogueContexts` jatuh ke bawaannya. Selain teks, judul, tokoh, dan audio, admin menyunting pose dan efek; `DialogueModel` menolak pose yang bukan milik tokohnya. Suntingan di sini dilindungi dari `gelita:story:update` (dilewati kecuali `--force`).

### `Admin\MediaController`

`upload()`:

1. Validasi `file: uploaded[file]|max_size[file,65536]|is_image[file]` (atau `mime_in` untuk audio/video).
2. Cocokkan `asset_key` tujuan; ambil ukuran wajib dari `Config\Gelita::$assetSizes`.
3. `getimagesize()` → tolak bila tidak cocok dan `strict = true`.
4. Simpan ke `public/assets/uploads/{asset_key}.{ext}` dengan nama resmi, bukan nama asli.
5. Hitung `sha256`, `file_size`, dimensi → simpan/`update` `media_assets`.
6. Audit `media_upload`.

`uploadAudio()`: tambah field `locale`, `character_code`, `context_code`, `transcript` (wajib), `production_method`. Status awal `draft`. Durasi dibaca server-side; bila gagal dibaca, dibiarkan NULL.

`approveAudio($id)`: set `approval_status = 'approved'`, `approved_by`, `approved_at`. Audit `audio_approve`. **Hanya audio approved yang dikirim ke pemain.**

### `Admin\ExportController`

| Method | Business rule |
|---|---|
| `index()` | form filter: study, phase, level, sekolah, kelas, rentang tanggal, sheet yang disertakan, mode anonim |
| `xlsx()` | buat baris `data_exports` status `running`; panggil `ExportService`; pada sukses `markDone()` + audit `export`; **guru dipaksa `anonymized = 1`**; sheet `Raw Events` hanya untuk admin |
| `pdf()` | sama, template laporan; raw event tidak masuk PDF |
| `download($id)` | validasi export milik pemohon atau pemohon adalah admin; validasi `expires_at` belum lewat; kirim file dari `writable/exports/` lewat `$this->response->download()`; audit |

### `Admin\GovernanceController`

| Method | Business rule |
|---|---|
| `deletePreview()` | hitung jumlah baris terdampak per tabel tanpa menghapus apa pun; simpan `data_deletion_requests` status `preview` + `affected_count`; audit `delete_preview` |
| `deleteExecute($id)` | wajib request berstatus `preview`; wajib field `confirm` berisi teks persis `HAPUS`; jalankan dalam transaction; `mode = soft` → isi `deleted_at`/`deleted_by`/`delete_reason`; `mode = hard` → hapus baris; audit `delete_execute` dengan `affected_count` final |
| `runRetention()` | jalankan sekarang apa yang biasanya dijalankan cron |
| `audit()` | daftar `audit_logs` dengan filter aksi dan rentang tanggal |

Penghapusan tidak pernah senyap. Tidak ada endpoint yang menghapus data penelitian di luar alur ini.

---

## Authentication

### Siswa (game)

* Registrasi di `/daftar`: nama pengguna + kata sandi kuat + ulangi sandi, bersama data demografis dan persetujuan.
* Kebijakan sandi: minimal 8 karakter, huruf besar, huruf kecil, angka, simbol; tidak memuat nama pengguna. Aturan dari `Config\Gelita::$passwordPolicy`, diputuskan `PasswordPolicy` di server.
* `password_hash()` / `password_verify()` dengan `PASSWORD_DEFAULT`.
* Login di `/masuk`. Throttle: 8 kegagalan → `locked_until = NOW + 5 menit` (lebih longgar dari staf karena anak sering salah ketik, tetap cukup untuk menghentikan tebak-tebakan).
* Setelah login: `session()->regenerate(true)`, lalu `session('participant_id')` dan `session('game_session_id')`. Tidak ada cookie khusus berumur panjang dan tidak ada "ingat saya" — komputer kelas dipakai bergantian.
* Tiga filter:

| Filter | Syarat lolos | Bila gagal |
|---|---|---|
| `participantAuth` | `participant_id` ada dan peserta aktif | redirect `/masuk` |
| `gameSession` | lolos `participantAuth` + `must_change_password = 0` + `game_session_id` ada dan status ≠ `abandoned` | `/masuk` atau `/ganti-sandi` |
| `apiSession` | sama seperti `gameSession` | JSON `401 INVALID_SESSION` / `403 PASSWORD_CHANGE_REQUIRED` |

* Sesi berstatus `completed` tetap dapat dibuka (peta, hasil, profil, refleksi, dan mengulang tantangan); attempt ulang tetap tercatat sebagai data.
* Sesi `active` yang tidak aktif melebihi `Config\Gelita::$sessionIdleMinutes` ditandai `paused` oleh command retention; login berikutnya melanjutkannya.
* Siswa lupa sandi → guru mereset dari `/admin/peserta/{id}` (lihat `ParticipantController::resetPassword`). Tidak ada reset lewat email, karena siswa SD umumnya tidak punya email sendiri.

### Staf (admin & guru)

* `password_hash()` / `password_verify()` dengan `PASSWORD_DEFAULT`.
* Session CI4 dengan DatabaseHandler; `regenerate(true)` setelah login berhasil.
* Throttle: 5 kegagalan → `locked_until = NOW + 15 menit`.
* Semua percobaan login (berhasil dan gagal) masuk `audit_logs` dengan `ip_hash`, bukan IP mentah.
* Staf mengganti sandinya sendiri di `/admin/akun/sandi` (lihat `AccountController`). Salah tebak sandi saat ini ikut throttle yang sama.
* Sandi sementara dari admin (reset atau akun baru) wajib diganti sebelum panel terbuka — padanan `/ganti-sandi` siswa.
* Tidak ada "remember me" — data penelitian tidak layak dibiarkan terbuka di komputer bersama sekolah.

---

## Authorization

### Role

| Role | Boleh |
|---|---|
| `admin` | semua data semua sekolah; konten; media; studi & rilis; export anonim dan non-anonim; deletion; retensi; akun staff; audit log |
| `guru` | dashboard dan data peserta **sekolahnya saja**; export **anonim saja**; tidak dapat mengubah konten, menghapus data, atau mengelola akun |

Peserta tidak punya akses apa pun ke panel.

### Penegakan berlapis

1. **Route** — `['filter' => 'staffAuth']` lalu `['filter' => 'staffRole:admin']` untuk grup yang khusus admin.
2. **Controller** — `schoolScope()` menghasilkan `school_id` untuk guru dan `null` untuk admin.
3. **Service** — `AnalyticsService` dan `ExportService` menerima `school_id` dan menerapkannya di setiap query. Bila pemanggil lupa mengirim, Service melempar exception, bukan mengembalikan semua data.

Tiga lapis ini disengaja: kesalahan di satu lapis tidak langsung membocorkan data peserta.

### `StaffRoleFilter`

```php
public function before(RequestInterface $request, $arguments = null)
{
    $role = session('staff_role');
    if ($role === null) {
        return redirect()->to('/admin/login');
    }
    if ($arguments && ! in_array($role, $arguments, true)) {
        if (str_starts_with($request->getUri()->getPath(), 'api/')) {
            return service('response')->setStatusCode(403)->setJSON([
                'success' => false, 'code' => 'FORBIDDEN',
                'message' => 'Anda tidak memiliki akses ke bagian ini.',
            ]);
        }
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }
}
```

Untuk halaman HTML, akses tanpa hak dibalas `404`, bukan `403`, agar struktur panel tidak terpetakan oleh pengguna yang tidak berhak.

---

## Error Handling

Bentuk respons gagal API seragam:

```json
{ "success": false, "code": "INVALID_RESPONSE",
  "message": "Data jawaban tidak valid.", "request_id": "a1b2c3d4" }
```

| code | HTTP | Kapan |
|---|---:|---|
| `INVALID_SESSION` | 401 | siswa belum login, session kedaluwarsa, atau sesi permainan ditinggalkan; juga token CSRF ditolak di `/api/*` tanpa login (session sudah habis) |
| `CSRF_EXPIRED` | 403 | token CSRF ditolak di `/api/*` padahal masih login (halaman terlalu lama terbuka / masuk ulang di tab lain) — JavaScript menawarkan muat ulang |
| `PASSWORD_CHANGE_REQUIRED` | 403 | sandi siswa baru direset guru dan belum diganti |
| `INVALID_CREDENTIALS` | 401 | login gagal (pesan identik untuk nama/sandi salah) |
| `ACCOUNT_LOCKED` | 423 | terlalu banyak percobaan login gagal |
| `WEAK_PASSWORD` | 422 | sandi tidak memenuhi kebijakan |
| `USERNAME_TAKEN` | 422 | nama pengguna sudah dipakai |
| `FORBIDDEN` | 403 | role tidak mencukupi, atau attempt/sesi bukan milik pemanggil |
| `NOT_FOUND` | 404 | node/item/attempt tidak ada |
| `INVALID_PAYLOAD` | 422 | validasi gagal; sertakan `errors` per field |
| `INVALID_RESPONSE` | 422 | item tidak termasuk attempt, atau bentuk `answer` tidak sesuai `interaction_type` |
| `ATTEMPT_CLOSED` | 409 | attempt sudah `completed`/`abandoned` |
| `LEVEL_LOCKED` | 409 | level belum terbuka |
| `TOO_MANY_EVENTS` | 413 | melebihi `maxEventsPerBatch` |
| `RATE_LIMITED` | 429 | terlalu banyak request dari satu sesi |
| `SERVER_ERROR` | 500 | exception tak tertangani |

Di production, `SERVER_ERROR` tidak pernah menyertakan pesan exception, kelas, atau stack trace. Semuanya hanya masuk `writable/logs/` beserta `request_id` yang sama, sehingga guru cukup menyebut `request_id` saat melapor.

---

## Aturan Sistem

1. Semua penilaian, skor, dan status level ditentukan server. Client tidak pernah menjadi sumber kebenaran.
2. Setiap endpoint memvalidasi: sesi valid → attempt/node milik sesi itu → item milik attempt itu. Ketiganya, bukan salah satu.
3. `attempt_id` dari URL selalu dicocokkan dengan `session_id` sesi berjalan sebelum dipakai.
4. Mengganti bahasa tidak membuat sesi baru, tidak mereset progres, jawaban, skor, atau status penyelesaian.
5. Fase penelitian (`umum`/`pretest`/`posttest`) ditetapkan saat sesi dibuat dan tidak dapat diubah setelah ada event gameplay.
6. Guru tidak dapat mengekspor data non-anonim, dan tidak dapat melihat peserta dari sekolah lain.
7. Setiap export dan setiap penghapusan menghasilkan baris `audit_logs`.
8. Penghapusan data selalu dua langkah: pratinjau jumlah terdampak, lalu eksekusi dengan konfirmasi teks.
9. CSRF aktif untuk semua POST. JavaScript mengirim token lewat header `X-CSRF-TOKEN`.
10. Rate limit sederhana per sesi untuk `/api/events` dan `/api/attempts/*` (mis. 120 request/menit) memakai cache CI4, agar satu tab bermasalah tidak membanjiri database event.
11. Redirect yang berasal dari input pengguna (`redirect_to`) wajib divalidasi sebagai path internal.
12. Kata sandi (siswa maupun staf, termasuk sandi sementara hasil reset) tidak pernah ditulis ke flash data, `old()`, log, `game_event_logs`, maupun `audit_logs`.
13. Form login siswa dan staf selalu memberi pesan identik untuk nama pengguna tidak ada dan sandi salah.
14. Guru hanya dapat mereset sandi siswa di sekolahnya; setiap reset tercatat di `audit_logs`.
15. Request HEAD dilayani rute GET dengan handler **dan** filter yang sama (`App\Libraries\HeadAsGetRouteCollection`, didaftarkan di `Config\Services::routes()`). Tanpa itu CodeIgniter menjawab HEAD dengan 404; memetakan handler tanpa filter akan membuka halaman staf tanpa login. Filter yang punya efek samping khusus GET (simpan tujuan login, `?lang=`) memeriksa `getMethod() === 'GET'`, sehingga tidak berjalan untuk HEAD.
16. Sandi staf yang diketahui admin (sandi sementara hasil reset, sandi awal akun baru) wajib diganti pemiliknya sebelum halaman panel lain dan `/api/admin/*` dapat dipakai (`staff_users.must_change_password`, ditegakkan `StaffAuthFilter`).

---

## Dependency

Dari **01_DATABASE.md**: struktur tabel, aturan uniqueness, daftar `event_type` dan `interaction_type`.

Dari **02_PROJECT_FOUNDATION.md**: `BaseController` dengan `ok()`/`fail()`/`jsonBody()`, alias filter di `Config\Filters`, `Config\Gelita`, helper.

Dari **03_MODEL_ENTITY.md**: seluruh Model, Entity, dan delapan Service — khususnya `SessionService` (registrasi, login, ganti & reset sandi), `ChallengeService`, `ScoringService`, `EventService`, `AnalyticsService`, `ContentImportService`, `ContentRepository`; library `PasswordPolicy` dan rule `strong_password`.

---

## Hasil Akhir

Setelah tahap ini selesai:

* `php spark routes` menampilkan seluruh route di atas tanpa konflik, dengan auto-route mati.
* Alur siswa berjalan penuh lewat HTTP: `/` → `/persetujuan` → `/daftar` (nama pengguna + sandi kuat) → `/intro` → `/intro/selesai` → `/peta` → `/wilayah/temanggung` → `/misi/temanggung/1` → `/tantangan/temanggung/1` → API jawab → `/selesai/{id}` → `/keluar` → `/masuk` → kembali ke progres terakhir.
* Registrasi dengan sandi `kedu2026` ditolak dengan pesan syarat yang belum terpenuhi; `pw_weak_submit_count` bertambah; registrasi berikutnya dengan `Kedu#2026` diterima.
* Reset sandi oleh guru memaksa siswa ke `/ganti-sandi` pada login berikutnya.
* Impor workbook bank soal menampilkan pratinjau per node sebelum menulis database.
* Login admin bekerja, termasuk throttle dan audit; guru yang mencoba `/admin/konten` mendapat 404.
* Seluruh endpoint API mengembalikan bentuk JSON seragam, termasuk saat gagal.
* Percobaan mengirim `item_id` yang bukan milik attempt ditolak `422 INVALID_RESPONSE`.
* Mengirim ulang `client_event_id` yang sama membalas `duplicate` tanpa menambah baris.
* View masih boleh berupa placeholder — tampilannya dikerjakan tahap 5.
