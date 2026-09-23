# 02_PROJECT_FOUNDATION.md — Fondasi Proyek CodeIgniter 4

> **Revisi 2 (21 September 2026).** Siswa kini login dengan nama pengguna dan kata sandi kuat. Tahap ini menambahkan: `Config\Gelita::$passwordPolicy`, library `PasswordPolicy` (satu sumber aturan sandi untuk server dan JavaScript), berkas bahasa `Auth.php` dwibahasa berisi microcopy kata sandi, perubahan `GameSessionFilter` ke sesi login siswa, throttle login siswa, dan kerangka command `gelita:bank:import`.

---

## Tujuan

Membangun proyek CodeIgniter 4 kosong tetapi lengkap: environment, konfigurasi, struktur folder, layout system, helper, filter, base controller, dan komponen global. Setelah tahap ini selesai, proyek siap menerima Model/Entity pada tahap 3.

---

## Konteks

GELITA adalah aplikasi web server-rendered. Tidak ada SPA framework. Frontend memakai **CodeIgniter View + HTML5 + CSS3 + JavaScript modern (ES modules)**. AJAX dipakai untuk interaksi permainan dan dashboard, bukan untuk merender seluruh halaman.

Aplikasi punya tiga area yang berbeda karakternya:

| Area | Prefix URL | Pengguna | Layout |
|---|---|---|---|
| **Game** | `/` | peserta didik (registrasi + login nama pengguna dan kata sandi) | `layouts/game.php` — layar penuh, HUD lentera, dwibahasa |
| **Admin** | `/admin` | guru & admin (login) | `layouts/admin.php` — sidebar, panel, tabel |
| **API** | `/api` | dipanggil JS game & dashboard | JSON, tanpa view |

Dua bahasa: `id` (default) dan `en`. String UI memakai localization CI4; konten permainan memakai kolom `*_id` / `*_en` di database.

---

## Environment

| Komponen | Versi minimum | Catatan |
|---|---|---|
| PHP | **8.2** (disarankan 8.3) | CodeIgniter 4.7.x mendukung PHP 8.1+ |
| CodeIgniter | **4.7.x** stable | pasang lewat Composer |
| Composer | 2.6+ | |
| MySQL | 8.0+ | atau **MariaDB 10.6+** |
| Web server | Apache 2.4 + mod_rewrite, atau Nginx 1.22+ | |

**Ekstensi PHP wajib:**

```
intl, mbstring, json, mysqlnd, curl, gd, zip, fileinfo, openssl, iconv, dom, xml, xmlwriter, simplexml
```

* `intl` — wajib CI4, dipakai localization dan format angka/tanggal.
* `gd` — validasi dimensi gambar saat upload media.
* `zip`, `dom`, `xmlwriter`, `simplexml` — wajib PhpSpreadsheet (export XLSX).
* `mbstring`, `iconv` — wajib mPDF (export PDF, teks UTF-8 Indonesia).

**Pengaturan PHP:**

```ini
memory_limit = 512M          ; export XLSX/PDF butuh ruang
max_execution_time = 300     ; export batch
upload_max_filesize = 64M    ; upload video pustaka
post_max_size = 72M
date.timezone = Asia/Jakarta
```

**Timezone aplikasi:** `Asia/Jakarta`. Semua `DATETIME(6)` disimpan dalam waktu server (WIB), tidak UTC, karena seluruh penelitian berlangsung di satu zona waktu dan guru membaca stempel waktu apa adanya. Ditetapkan di `app/Config/App.php` → `$appTimezone = 'Asia/Jakarta'`.

**Environment:** `development` untuk kerja lokal, `production` untuk server sekolah.

---

## Installation

Urutan membuat proyek dari nol:

```bash
# 1. Buat proyek CodeIgniter 4
composer create-project codeigniter4/appstarter gelita
cd gelita

# 2. Pasang library produksi
composer require phpoffice/phpspreadsheet
composer require mpdf/mpdf

# 3. Siapkan environment
cp env .env

# 4. Buat database
mysql -u root -p -e "CREATE DATABASE gelita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Isi .env (lihat bagian Configuration), lalu:
php spark migrate
GELITA_ADMIN_PASSWORD='SandiKuatAnda123!' php spark db:seed DatabaseSeeder

# 6. Jalankan
php spark serve
```

**Library Composer yang dipakai dan alasannya — tidak lebih dari ini:**

| Paket | Alasan |
|---|---|
| `codeigniter4/framework` | framework |
| `phpoffice/phpspreadsheet` | export XLSX multi-sheet untuk SPSS/R |
| `mpdf/mpdf` | export PDF laporan HTML/CSS berbahasa Indonesia |

**Yang sengaja TIDAK dipakai:**

* `codeigniter4/shield` — hanya butuh login staff dengan 2 role. Auth kustom sekitar 120 baris lebih mudah dipahami daripada tabel dan konfigurasi Shield.
* Library UUID — `random_bytes(16)` + format manual cukup (lihat helper `gelita_helper`).
* Library queue/job — export dijalankan sinkron dengan batch writer; bila perlu asinkron, pakai `php spark` command lewat cron.
* ORM/Repository pihak ketiga — CodeIgniter Model sudah cukup.

---

## Configuration

### `.env`

Templat berisi seluruh kunci di bawah tersedia sebagai berkas `env` di root proyek dan dilacak Git. Salin dengan `cp env .env`, lalu isi nilainya. `.env` sendiri tidak pernah di-commit.

```ini
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
# app.supportedLocales tidak dapat diisi lewat .env karena bernilai array;
# nilainya ['id', 'en'] ditetapkan di app/Config/App.php.
#--------------------------------------------------------------------
app.baseURL = 'http://localhost:8080/'
app.forceGlobalSecureRequests = false
app.appTimezone = 'Asia/Jakarta'
app.defaultLocale = 'id'
app.indexPage = ''

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = gelita
database.default.username = gelita_app
database.default.password = ''
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
encryption.key = 

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------
session.driver = 'CodeIgniter\Session\Handlers\DatabaseHandler'
session.savePath = 'ci_sessions'
session.cookieName = 'gelita_session'
session.expiration = 14400
session.regenerateDestroy = true
session.matchIP = false

# Satu mekanisme sesi untuk siswa dan staf: session CI4 berbasis database.
# Siswa: session('participant_id') + session('game_session_id').
# Staf : session('staff_id') + session('staff_role') + session('staff_school_id').
# Tidak ada cookie "ingat saya" — komputer kelas dipakai bergantian.
# matchIP tetap false: lab sekolah sering ber-NAT/DHCP, dan PK ci_sessions
# hanya `id` sehingga mengaktifkannya butuh perubahan skema.

#--------------------------------------------------------------------
# SECURITY (CSRF)
#--------------------------------------------------------------------
security.csrfProtection = 'session'
security.tokenRandomize = true
security.tokenName = 'gelita_csrf'
security.headerName = 'X-CSRF-TOKEN'
security.cookieName = 'gelita_csrf_cookie'
security.expires = 7200
security.regenerate = false
security.redirect = false

#--------------------------------------------------------------------
# COOKIE
#--------------------------------------------------------------------
cookie.samesite = 'Lax'
cookie.secure = false
cookie.httponly = true

#--------------------------------------------------------------------
# LOGGER — production: 4, development: 9
#--------------------------------------------------------------------
logger.threshold = 9

#--------------------------------------------------------------------
# DEBUG TOOLBAR (hanya aktif di development)
# collectVarData = false: data view (profil peserta) tidak disimpan ke
# writable/debugbar. Field kata sandi pada formulir selalu disamarkan oleh
# App\Filters\DebugToolbar, apa pun nilai kunci ini.
#--------------------------------------------------------------------
toolbar.collectVarData = false

#--------------------------------------------------------------------
# GELITA
# gelita.ipSalt WAJIB diganti string acak panjang per pemasangan; salt ini
# yang membuat hash IP tidak dapat dibalik.
#--------------------------------------------------------------------
gelita.ipSalt = 'ganti-dengan-string-acak-panjang'
gelita.exportRetentionDays = 7
gelita.maxEventsPerBatch = 50
gelita.assetVersion = '1'
```

Bangkitkan `encryption.key`:

```bash
php spark key:generate
```

`security.regenerate = false` penting: game mengirim banyak request AJAX beruntun; token yang berubah setiap request membuat request paralel gagal. Token tetap acak per-request (`tokenRandomize = true`) sehingga tetap aman terhadap BREACH.

### Tabel session

Session memakai database handler agar tahan restart dan bisa dipakai lintas worker. Tabel `ci_sessions` sudah menjadi bagian migration proyek (`2026-01-01-003000_CreateCiSessions`), jadi cukup:

```bash
php spark migrate
```

Jangan menjalankan `php spark session:migration`: perintah itu membuat migration `ci_sessions` kedua di `app/Database/Migrations/`.

### `app/Config/App.php`

```php
public string $baseURL          = 'http://localhost:8080/';
public string $indexPage        = '';
public string $defaultLocale    = 'id';
public bool   $negotiateLocale  = false;   // locale ditentukan session, bukan header
public array  $supportedLocales = ['id', 'en'];
public string $appTimezone      = 'Asia/Jakarta';
public string $charset          = 'UTF-8';
```

`negotiateLocale = false` disengaja: bahasa ditentukan pilihan eksplisit peserta (tombol ID/EN) dan disimpan di `game_sessions.locale`, bukan ditebak dari header browser. Ini syarat penelitian — bahasa harus tercatat, bukan berubah diam-diam.

### `app/Config/Database.php`

Tambahkan pada `$default`:

```php
'charset'  => 'utf8mb4',
'DBCollat' => 'utf8mb4_unicode_ci',
'numberNative' => true,
'foreignKeys'  => true,
```

### `app/Config/Cache.php`

```php
public string $handler = 'file';      // development & sekolah tanpa Redis
public int    $ttl     = 300;
```

Cache dipakai hanya untuk konten yang jarang berubah (level, node, item bank per release). Data penelitian tidak pernah di-cache.

### `app/Config/Logger.php`

```php
public $threshold = 4;   // production: 4 (info ke atas); development: 9
public $dateFormat = 'Y-m-d H:i:s.u';
```

### `app/Config/Filters.php`

```php
public array $aliases = [
    'csrf'          => \CodeIgniter\Filters\CSRF::class,
    'toolbar'       => \App\Filters\DebugToolbar::class,   // toolbar bawaan + penyamaran field kata sandi
    'honeypot'      => \CodeIgniter\Filters\Honeypot::class,
    'secureheaders' => \CodeIgniter\Filters\SecureHeaders::class,
    'staffAuth'     => \App\Filters\StaffAuthFilter::class,
    'staffRole'     => \App\Filters\StaffRoleFilter::class,
    'participantAuth' => \App\Filters\ParticipantAuthFilter::class,
    'gameSession'   => \App\Filters\GameSessionFilter::class,
    'apiSession'    => \App\Filters\ApiSessionFilter::class,
    'locale'        => \App\Filters\LocaleFilter::class,
    'jsonResponse'  => \App\Filters\JsonResponseFilter::class,
];

public array $globals = [
    'before' => ['locale'],
    'after'  => ['secureheaders'],   // 'toolbar' sudah wajib lewat $required
];

public array $methods = ['POST' => ['csrf'], 'PUT' => ['csrf'], 'PATCH' => ['csrf'], 'DELETE' => ['csrf']];
```

**Debug Toolbar dan kata sandi.** Toolbar bawaan CodeIgniter menyalin seluruh isi POST ke `writable/debugbar/*.json` tanpa syarat — `toolbar.collectVarData` hanya mengatur data view. Karena templat `env` memakai `CI_ENVIRONMENT = development`, tanpa penanganan khusus setiap kiriman `/daftar`, `/masuk`, `/ganti-sandi`, dan `/admin/login` akan meninggalkan kata sandi mentah di disk. Alias `toolbar` karena itu menunjuk `App\Filters\DebugToolbar`, yang menyamarkan field kredensial (`password`, `password_confirm`, `current_password`, …) sebelum toolbar mengambil potret request. Dikunci `tests/unit/DebugToolbarRedactionTest.php`.

### `app/Config/Validation.php`

Daftarkan ruleset kustom (dibuat tahap 3):

```php
public array $ruleSets = [
    Rules::class,
    FormatRules::class,
    FileRules::class,
    CreditCardRules::class,
    \App\Validation\GelitaRules::class,
];
```

### `app/Config/Gelita.php` — konfigurasi domain aplikasi

File baru, menyimpan konstanta domain yang tidak perlu masuk database:

```php
<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Gelita extends BaseConfig
{
    /** Tipe engine tantangan yang dikenali sistem */
    public array $engineTypes = ['puzzle', 'rumpang', 'boleh', 'pilihan', 'cari'];

    /** Locale yang didukung konten permainan */
    public array $locales = ['id', 'en'];

    /** Karakter yang punya suara & sprite */
    public array $characters = ['jaka', 'mbah_kedu'];

    /** Fase penelitian */
    public array $phases = ['umum', 'pretest', 'posttest'];

    /** Event type yang boleh ditulis ke game_event_logs */
    public array $eventTypes = [
        'session_started', 'session_resumed', 'session_paused', 'session_abandoned',
        'session_completed', 'locale_changed', 'level_opened', 'level_completed',
        'library_opened', 'library_page_viewed', 'dialogue_advanced',
        'challenge_opened', 'challenge_checked', 'challenge_completed',
        'challenge_skipped', 'challenge_abandoned', 'answer_submitted',
        'answer_changed', 'wrong_target_clicked', 'hint_opened',
        'audio_play', 'audio_pause', 'audio_replay', 'audio_completed',
        'feedback_submitted', 'password_changed',
    ];

    /**
     * Kebijakan kata sandi SISWA — literasi keamanan digital.
     * Satu-satunya sumber aturan: dipakai PasswordPolicy (server),
     * validasi 'strong_password', dan dikirim ke JavaScript lewat
     * <script type="application/json" id="password-policy">.
     */
    public array $passwordPolicy = [
        'min_length'      => 8,
        'max_length'      => 64,
        'require_upper'   => true,
        'require_lower'   => true,
        'require_digit'   => true,
        'require_symbol'  => true,
        'forbid_username' => true,
        // tingkat tampilan: jumlah syarat terpenuhi (0–5)
        'levels'          => ['weak' => [0, 2], 'medium' => [3, 4], 'strong' => [5, 5]],
        'accept_level'    => 'strong',   // registrasi hanya diterima bila kuat
    ];

    /** Nama pengguna siswa yang tidak boleh dipakai */
    public array $reservedUsernames = ['admin', 'guru', 'gelita', 'root', 'system', 'test'];

    /** Throttle login */
    public array $loginThrottle = [
        'participant' => ['max_fail' => 8, 'lock_minutes' => 5],   // anak sering salah ketik
        'staff'       => ['max_fail' => 5, 'lock_minutes' => 15],
    ];

    /**
     * Animasi karakter: frame sekuensial di media_assets.
     * Menggantikan tabel sprite_sheets/sprite_animations.
     */
    public array $characterAnimations = [
        'jaka.idle'    => ['frames' => 3, 'fps' => 4,  'loop' => true],
        'jaka.bow'     => ['frames' => 3, 'fps' => 6,  'loop' => false],
        'jaka.happy'   => ['frames' => 3, 'fps' => 6,  'loop' => false],
        'kedu.idle'    => ['frames' => 3, 'fps' => 3,  'loop' => true],
    ];

    /** Ukuran piksel wajib per slot aset, dipakai validasi upload */
    public array $assetSizes = [
        'bg.*'            => [1920, 1080],
        'char.*'          => [700, 900],
        'map.region'      => [1400, 900],
        'challenge.scene' => [1280, 720],
        'challenge.card'  => [480, 360],
        'challenge.option'=> [400, 400],
        'library.image'   => [960, 640],
        'reward.badge'    => [320, 320],
    ];

    public int $maxUploadBytes      = 64 * 1024 * 1024;
    public int $maxEventsPerBatch   = 50;
    public int $exportRetentionDays = 7;
    public int $sessionIdleMinutes  = 45;   // lewat ini → status 'paused'
}
```

---

## Folder Structure

```text
gelita/
├── app/
│   ├── Config/
│   │   ├── App.php
│   │   ├── Autoload.php
│   │   ├── Cache.php
│   │   ├── Database.php
│   │   ├── Filters.php
│   │   ├── Gelita.php             ← baru
│   │   ├── Routes.php
│   │   ├── Services.php
│   │   └── Validation.php
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── Game/                  ← controller permainan (tahap 4)
│   │   ├── Admin/                 ← controller panel (tahap 4)
│   │   └── Api/                   ← controller JSON (tahap 4)
│   ├── Database/
│   │   ├── Migrations/
│   │   └── Seeds/
│   ├── Entities/                  ← tahap 3
│   ├── Filters/
│   │   ├── ApiSessionFilter.php
│   │   ├── DebugToolbar.php           ← toolbar development + penyamaran kata sandi
│   │   ├── GameSessionFilter.php      ← memeriksa login siswa + sesi permainan
│   │   ├── JsonResponseFilter.php
│   │   ├── LocaleFilter.php
│   │   ├── ParticipantAuthFilter.php  ← hanya memeriksa siswa sudah login
│   │   ├── ParticipantCheck.php       ← trait: satu aturan untuk tiga filter siswa
│   │   ├── StaffAuthFilter.php
│   │   └── StaffRoleFilter.php
│   ├── Helpers/
│   │   ├── gelita_helper.php
│   │   ├── content_helper.php
│   │   └── ui_helper.php          ← ikon SVG, format angka/tanggal untuk view (tahap 5)
│   ├── Language/
│   │   ├── id/
│   │   │   ├── Game.php
│   │   │   ├── Auth.php           ← microcopy registrasi, login, kata sandi
│   │   │   ├── Admin.php
│   │   │   └── Validation.php
│   │   └── en/
│   │       ├── Game.php
│   │       ├── Auth.php
│   │       └── Validation.php     ← panel admin satu bahasa, jadi tidak ada en/Admin.php
│   ├── Libraries/
│   │   ├── PasswordPolicy.php     ← aturan & penilaian kekuatan sandi siswa
│   │   ├── GelitaExceptionHandler.php ← JSON seragam untuk /api, halaman galat bergaya GELITA
│   │   ├── RequestId.php          ← id acak per request (service gelitaRequestId)
│   │   ├── HashedIpSessionHandler.php ← belum dipasang, lihat § 3b
│   │   ├── ExcelWriter.php        ← penulis XLSX streaming untuk export (tahap 7)
│   │   ├── ContentVerifier.php    ← aturan verifikasi konten, panel + CLI (tahap 7)
│   │   └── MediaIntegrity.php     ← pemeriksa media_assets ↔ berkas (tahap 7)
│   ├── Commands/                  ← spark command gelita:* (kerangka tahap 2, isi tahap 7)
│   ├── Models/                    ← tahap 3
│   ├── Services/                  ← tahap 3 & 7
│   ├── Validation/
│   │   └── GelitaRules.php
│   └── Views/
│       ├── layouts/
│       │   ├── game.php
│       │   ├── admin.php
│       │   └── auth.php
│       ├── components/
│       ├── game/                  ← tahap 5
│       ├── admin/                 ← tahap 5
│       ├── errors/
│       └── pdf/                   ← template laporan PDF: report-study, report-participant (tahap 7)
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       │   ├── tokens.css
│       │   ├── base.css
│       │   ├── layout.css
│       │   ├── components.css
│       │   ├── game.css
│       │   └── admin.css
│       ├── js/
│       │   ├── core/
│       │   │   ├── config.js
│       │   │   ├── api.js
│       │   │   ├── events.js
│       │   │   ├── audio.js
│       │   │   ├── toast.js
│       │   │   └── storage.js
│       │   ├── engines/
│       │   ├── admin/
│       │   ├── game.js
│       │   └── admin.js
│       ├── vendor/
│       │   ├── echarts.min.js
│       │   └── howler.min.js
│       ├── data/
│       │   └── wilayah-id.json
│       ├── images/
│       ├── fonts/
│       ├── ui/ char/ bg/ map/ challenge/ library/ reward/ audio/
│       └── uploads/               ← media yang diunggah admin
├── writable/
│   ├── cache/  logs/  session/  uploads/
│   └── exports/                   ← hasil XLSX/PDF, di luar public/
├── tests/
├── env                            ← templat, dilacak Git
├── .env                           ← hasil `cp env .env`, tidak pernah di-commit
└── composer.json
```

Aturan folder aset:

* `public/assets/{ui,char,bg,map,challenge,library,reward,audio}/` — aset yang dikirim bersama source (seed).
* `public/assets/uploads/` — aset yang diunggah lewat panel admin. Ditulis dengan nama resmi dari `media_assets.asset_key`, bukan nama asli berkas pengguna.
* `public/assets/vendor/` — library frontend yang di-host sendiri. Tidak ada CDN.
* `writable/exports/` — **tidak** boleh berada di bawah `public/`. Unduhan dilayani controller yang memeriksa otorisasi lebih dulu.

#### Status `public/assets/vendor/` — terisi sejak tahap 5

Kedua library sudah di-commit apa adanya dari paket rilis resmi, beserta berkas lisensinya (`LICENSE-echarts.txt`, `NOTICE-echarts.txt`, `LICENSE-howler.md`).

| Berkas | Library | Versi yang dipakai | Sumber |
|---|---|---|---|
| `echarts.min.js` | Apache ECharts | `6.1.0` — dikunci pada 6.x, diperbarui manual | paket rilis resmi `echarts@6.1.0` (`dist/`) |
| `howler.min.js` | Howler.js | `2.2.4` — dikunci pada 2.2.x | paket rilis resmi `howler@2.2.4` (`dist/`) |

Checksum SHA-256 dan langkah memperbarui versi ada di [`public/assets/vendor/README.md`](../public/assets/vendor/README.md). Font lokal (Cinzel, Plus Jakarta Sans, IBM Plex Mono, `.woff2` subset latin + lisensi OFL) ada di `public/assets/fonts/`.

Alasan pemilihan kedua library dan daftar halaman yang memakainya ada di [05_VIEW_UI.md → *External CSS/JS Library*](05_VIEW_UI.md) dan [06_JAVASCRIPT.md → *Library JavaScript*](06_JAVASCRIPT.md).

> Folder ini sempat terabaikan Git karena pola `vendor/` pada `.gitignore` cocok dengan folder bernama `vendor` di kedalaman mana pun. Pola tersebut sudah dipersempit menjadi `/vendor/` (khusus folder Composer di root), jadi berkas di bawah `public/assets/vendor/` kini dapat di-commit. Jangan mengembalikan pola lama.

---

## Common Components

### 1. `app/Controllers/BaseController.php`

```php
<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['url', 'form', 'text', 'gelita', 'content'];
    protected string $locale = 'id';

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->locale = service('request')->getLocale() ?: 'id';
    }

    /** Respons JSON sukses dengan bentuk seragam */
    protected function ok(array $data = [], int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON([
            'success'    => true,
            'data'       => $data,
            'request_id' => service('gelitaRequestId'),
        ]);
    }

    /** Respons JSON gagal dengan bentuk seragam */
    protected function fail(string $code, string $message, int $status = 400, array $extra = [])
    {
        log_message('warning', "API fail [{$code}] {$message}");
        return $this->response->setStatusCode($status)->setJSON(array_merge([
            'success'    => false,
            'code'       => $code,
            'message'    => $message,
            'request_id' => service('gelitaRequestId'),
        ], $extra));
    }

    /** Ambil dan validasi payload JSON */
    protected function jsonBody(): array
    {
        $body = $this->request->getJSON(true);
        return is_array($body) ? $body : [];
    }
}
```

### 2. Filters

**`LocaleFilter`** — global `before`. Menentukan bahasa untuk seluruh request.

```php
Urutan penentuan locale:
1. Query string ?lang=id|en  → simpan ke session, redirect tanpa query
2. session('locale')
3. game_sessions.locale bila ada sesi permainan aktif
4. 'id'
Lalu: service('request')->setLocale($locale);
```

**`GameSessionFilter`** — dipasang pada route game yang butuh siswa login dan sesi permainan aktif.

```text
1. session('participant_id') kosong           → redirect /masuk  (simpan URL tujuan di flash)
2. participant tidak ada / terhapus           → session()->destroy(), redirect /masuk
3. participants.must_change_password = 1      → redirect /ganti-sandi (kecuali sedang di route itu)
4. session('game_session_id') kosong atau
   game_sessions.status = abandoned           → redirect /masuk (login membuat/melanjutkan sesi)
5. lolos → GameContext::load(participantId, gameSessionId)
```

Sesi `completed` tetap lolos: siswa masih boleh membuka peta, profil, refleksi, dan mengulang tantangan.

**`ParticipantAuthFilter`** — hanya langkah 1–2 di atas. Dipakai `/ganti-sandi` dan `/keluar`, yang harus dapat dibuka walau sandi wajib diganti.

**`ApiSessionFilter`** — logika sama, tetapi membalas JSON `401 {code:"INVALID_SESSION"}` atau `403 {code:"PASSWORD_CHANGE_REQUIRED"}` alih-alih redirect.

**`StaffAuthFilter`** — memeriksa `session('staff_id')`. Bila kosong → redirect `/admin/login`. Memuat staff aktif ke `service('staffContext')`. Bila `is_active = 0` → logout paksa.

**`StaffRoleFilter`** — menerima argumen role: `['filter' => 'staffRole:admin']`. Bila role tidak cocok → `403`.

**`JsonResponseFilter`** — `after` filter untuk grup `/api`: memastikan `Content-Type: application/json`, menambahkan header `X-Request-Id`, dan pada environment production mengubah exception tak tertangani menjadi respons error standar tanpa stack trace.

### 3. Helpers

**`app/Helpers/gelita_helper.php`**

```php
uuid4(): string
    // random_bytes(16) + set versi/varian + format 8-4-4-4-12

random_code(int $bytes = 16): string
    // bin2hex(random_bytes($bytes)) — dipakai session_code (pengenal internal, bukan token login)

participant_code(int $sequence): string
    // sprintf('GLT-%06d', $sequence)

hash_ip(?string $ip): ?string
    // hash('sha256', $ip . config('Gelita')->ipSalt ?? env('gelita.ipSalt'))

clamp(float $v, float $min, float $max): float

ms_to_human(int $ms): string        // 125000 → "2:05"

asset_url_versioned(string $path): string
    // base_url('assets/' . $path) . '?v=' . config('Gelita')->assetVersion

seeded_shuffle(array $items, string $seed): array
    // mt_srand(crc32($seed)); shuffle deterministik untuk item_selection_mode=fixed
    // WAJIB memanggil mt_srand() ulang dengan random_int setelahnya
```

**`app/Helpers/content_helper.php`**

```php
tr(?array $row, string $field, ?string $locale = null): string
    // Mengambil $row[$field.'_'.$locale]; bila kosong, jatuh ke $field.'_id'
    // Contoh: tr($node, 'title') → title_en bila locale en dan terisi, selain itu title_id

media_src(?int $mediaAssetId, ?string $fallback = null): string
    // Mengambil storage_path dari cache media; bila is_active=0 atau tidak ada,
    // mengembalikan placeholder assets/ui/placeholder.svg

audio_src(?int $audioAssetId): ?string
    // NULL bila approval_status != 'approved'

stars_html(int $stars): string
```

### 3a. Library `PasswordPolicy`

`app/Libraries/PasswordPolicy.php` — satu kelas kecil, dipakai validasi, `SessionService`, dan halaman ganti sandi.

```php
class PasswordPolicy
{
    public function __construct(private array $cfg = []) { $this->cfg = $cfg ?: config('Gelita')->passwordPolicy; }

    /** Hasil pemeriksaan per syarat — TIDAK pernah mengembalikan isi sandi */
    public function check(string $password, ?string $username = null): array
    {
        $c = [
            'length' => mb_strlen($password) >= $this->cfg['min_length']
                        && mb_strlen($password) <= $this->cfg['max_length']
                        && strlen($password) <= 72,
            'upper'  => (bool) preg_match('/[A-Z]/', $password),
            'lower'  => (bool) preg_match('/[a-z]/', $password),
            'digit'  => (bool) preg_match('/[0-9]/', $password),
            'symbol' => (bool) preg_match('/[^A-Za-z0-9]/', $password),
        ];
        $met   = count(array_filter($c));
        $level = $met <= 2 ? 'weak' : ($met <= 4 ? 'medium' : 'strong');
        $containsUsername = $username !== null && $username !== ''
            && str_contains(mb_strtolower($password), mb_strtolower($username));

        return ['criteria' => $c, 'met' => $met, 'level' => $level,
                'contains_username' => $containsUsername,
                'acceptable' => $level === 'strong' && ! $containsUsername];
    }

    /** Konfigurasi yang dikirim ke JavaScript (tanpa data rahasia) */
    public function toClient(): array { return $this->cfg; }
}
```

JavaScript di halaman registrasi (tahap 6) menjalankan aturan yang sama untuk umpan balik langsung; **keputusan akhir selalu dari server**.

### 3b. Library `HashedIpSessionHandler` — BELUM DIPASANG

`app/Libraries/HashedIpSessionHandler.php` sudah ada, tetapi **sengaja belum dijadikan `session.driver`**. `app/Config/Session.php` memakai `CodeIgniter\Session\Handlers\DatabaseHandler` bawaan. Catatan ini merekam hasil inspeksi kompatibilitasnya agar tidak hilang; enam butir di bawah harus diselesaikan lebih dulu sebelum handler ini diaktifkan.

Bentuk kelas saat ini:

```php
class HashedIpSessionHandler extends MySQLiHandler
{
    public function __construct(SessionConfig $config, string $ipAddress)
    {
        helper('gelita');

        parent::__construct($config, (string) hash_ip($ipAddress));
    }
}
```

#### Yang sudah terbukti bekerja

Diuji langsung terhadap MariaDB 10.11: satu siklus `open()` → `read()` → `write()` → `close()` menulis baris `ci_sessions` dengan `ip_address` berisi 64 karakter heksadesimal, dan IP mentah `10.1.2.3` tidak muncul di mana pun. `ci_sessions.ip_address` bertipe `VARCHAR(64)` — persis selebar SHA-256 heksadesimal, jadi tidak perlu perubahan skema. Kolom `id` `VARCHAR(128)` juga cukup untuk awalan `gelita_session:` + ID sesi.

#### Enam perubahan yang diperlukan

| # | Masalah | Bukti | Perubahan yang diperlukan |
|---|---|---|---|
| 1 | Kelas mewarisi `MySQLiHandler`, yang mengunci sesi dengan `GET_LOCK`/`RELEASE_LOCK` — fungsi khusus MySQL | Terhadap grup `tests` (SQLite3): `DatabaseException: Unable to prepare statement: no such function: GET_LOCK` | Strategi kunci yang sadar platform: pilih handler basis saat runtime, atau bungkus penghashan IP sebagai dekorator di atas handler yang dipilih `Services::session()` |
| 2 | `Services::session()` hanya memetakan handler per-platform ketika driver **persis** `DatabaseHandler::class` (`system/Config/Services.php:677`). Driver kustom melewati cabang itu — sekaligus melewati exception "Only MySQLi and Postgre are supported" yang seharusnya memperingatkan | Perbandingan kelas diuji langsung: driver kustom → tidak dipetakan | Override `Services::session()` di `app/Config/Services.php`, atau pertahankan `$driver = DatabaseHandler::class` dan lakukan penghashan IP sebelum nilainya sampai ke handler |
| 3 | Basis data uji (`Config\Database::$tests`) memakai SQLite3 `:memory:`, yang tidak akan pernah bisa menjalankan handler MySQLi | Sama dengan butir 1 | Sesi harus jatuh ke `ArrayHandler`/`FileHandler` saat `ENVIRONMENT === 'testing'`, atau selesaikan butir 1–2. **Saat ini aman hanya karena `CIUnitTestCase` otomatis menyuntikkan `MockSession` ber-`ArrayHandler`** — begitu ada test yang memakai sesi sungguhan, ini pecah |
| 4 | `hash_ip()` mengembalikan `null` untuk IP `null`/`''`; `(string) null` menjadi `''` dan itulah yang tersimpan | `hash_ip(null) === NULL`, `hash_ip('') === NULL`, baris tersimpan dengan `ip_address = ''` | Kolomnya `NOT NULL` sehingga `''` diterima, tetapi "tidak ada IP" jadi tidak terbedakan dari hash. Tetapkan penanda eksplisit, atau nyatakan `''` sebagai keputusan sadar |
| 5 | `Config\Gelita::$ipSalt` bernilai `''` secara bawaan | `config('Gelita')->ipSalt === ''` | Salt kosong membuat hash menjadi SHA-256 tanpa garam — dapat dibalik dengan pencarian menyeluruh ruang IPv4 dalam hitungan detik. Harus gagal cepat (lempar exception) bila salt kosong, sebelum handler boleh dipasang |
| 6 | `session.matchIP` harus tetap `false` | PK `ci_sessions` hanya `id` (migration `003000`) | CI4 mensyaratkan `ip_address` ikut dalam PK bila `matchIP` aktif. Mengaktifkannya butuh perubahan skema |

Butir 1, 2, dan 3 adalah penghalang pemasangan. Butir 5 adalah prasyarat keamanan. Butir 4 dan 6 adalah keputusan yang perlu dicatat, bukan cacat.

### 4. Services kustom — `app/Config/Services.php`

```php
public static function gelitaRequestId(bool $getShared = true): string
    // satu id acak per request, dipakai di semua respons JSON dan log

public static function gameContext(bool $getShared = true): \App\Services\GameContext
    // menyimpan session + progress aktif untuk request ini

public static function staffContext(bool $getShared = true): ?object
    // staff_users yang sedang login

public static function contentRepository(bool $getShared = true): \App\Services\ContentRepository
    // pembaca konten ber-cache (level, node, item, media)
```

Service bisnis tahap 3 juga didaftarkan di sini sehingga controller cukup memanggil `service('…')`: `sessionService`, `challengeService`, `scoringService`, `eventService`, `contentImportService`, dan `analyticsService(?int $schoolScope, bool $anonymous)` — yang terakhir menerima cakupan sekolah pemanggil (lapis ketiga otorisasi).

### 5. Layout System

CodeIgniter View Layouts dipakai, bukan include manual.

**`app/Views/layouts/game.php`** — kerangka:

```php
<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= $this->renderSection('title') ?: 'GELITA' ?></title>
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-name" content="<?= csrf_token() ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/base.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/layout.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/components.css') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/game.css') ?>">
  <?= $this->renderSection('head') ?>
</head>
<body class="game" data-locale="<?= esc(service('request')->getLocale()) ?>">
  <?= $this->include('components/hud') ?>
  <main id="app" class="app"><?= $this->renderSection('content') ?></main>
  <div id="modal-layer" class="modal-layer" hidden></div>
  <div id="toast-layer" class="toast-layer" role="status" aria-live="polite"></div>
  <script type="module" src="<?= asset_url_versioned('js/game.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
```

**`app/Views/layouts/admin.php`** — sidebar + konten, memuat `admin.css`, `echarts.min.js`, `js/admin.js`.

**`app/Views/layouts/auth.php`** — halaman login staff, minimal.

Halaman memakainya begini:

```php
<?= $this->extend('layouts/game') ?>
<?= $this->section('title') ?>Peta Kedu<?= $this->endSection() ?>
<?= $this->section('content') ?>
  ...
<?= $this->endSection() ?>
```

### 6. Localization

`app/Language/id/Game.php` dan `app/Language/en/Game.php`. Contoh:

```php
// app/Language/id/Game.php
return [
    'start'          => 'Mulai',
    'continue'       => 'Lanjut',
    'back'           => 'Kembali',
    'check'          => 'Periksa jawaban',
    'hint'           => 'Petunjuk',
    'shards'         => 'Serpihan Cahaya',
    'challengeOf'    => 'Tantangan {0} dari {1}',
    'accuracyFirst'  => 'Tepat sejak awal',
    'timeSpent'      => 'Waktu',
    'score'          => 'Skor',
    'locked'         => 'Terkunci',
    'completed'      => 'Selesai',
    'library'        => 'Pustaka Kedu',
    'reflection'     => 'Balai Refleksi',
    'logout'         => 'Keluar',
    'verdictBenar'   => 'Benar',
    'verdictSalah'   => 'Salah',
    'verdictPendapat'=> 'Pendapat',
    'factVsOpinion'  => 'Fakta bisa dibuktikan. Pendapat adalah perasaan atau selera seseorang.',
];
```

Dipakai `lang('Game.check')` di view, `lang('Game.challengeOf', [$i, $n])` untuk placeholder.

**`app/Language/{id,en}/Auth.php`** — microcopy registrasi dan kata sandi. Teks final (ramah anak, dwibahasa) sudah tersedia di lampiran Dokumen Bank Soal GELITA; kuncinya:

```php
// app/Language/id/Auth.php
return [
    'username'        => 'Nama pengguna',
    'usernameHelp'    => 'Pakai huruf kecil, angka, titik, atau garis bawah. 3–30 karakter.',
    'password'        => 'Kata sandi',
    'passwordRepeat'  => 'Ulangi kata sandi',
    'whyStrong'       => 'Kata sandi kuat menjaga akunmu aman, seperti kunci untuk rumahmu. Jangan sampai ada orang lain yang bisa masuk.',
    'ruleLength'      => 'Minimal 8 karakter',
    'ruleUpper'       => 'Ada huruf besar (A–Z)',
    'ruleLower'       => 'Ada huruf kecil (a–z)',
    'ruleDigit'       => 'Ada angka (0–9)',
    'ruleSymbol'      => 'Ada simbol (contoh: ! @ # ?)',
    'levelWeak'       => 'Sandimu masih lemah. Tambahkan huruf besar, angka, atau simbol.',
    'levelMedium'     => 'Lumayan! Tambah sedikit lagi agar benar-benar kuat.',
    'levelStrong'     => 'Hebat! Kata sandimu sudah kuat.',
    'notMatch'        => 'Kata sandi belum sama. Coba ketik ulang dengan hati-hati, ya.',
    'containsUsername'=> 'Kata sandi jangan memuat nama penggunamu.',
    'notStrongSubmit' => 'Kata sandi belum kuat. Penuhi kelima syarat di atas dulu, ya.',
    'tip1'            => 'Jangan bagikan kata sandimu kepada teman.',
    'tip2'            => 'Jangan pakai tanggal lahir atau namamu sendiri.',
    'tip3'            => 'Gunakan kata sandi berbeda untuk tiap akun.',
    'tip4'            => 'Minta bantuan orang tua atau guru untuk menyimpannya dengan aman.',
    'loginFailed'     => 'Nama pengguna atau kata sandi salah.',
    'locked'          => 'Terlalu banyak percobaan. Tunggu {0} menit, lalu coba lagi.',
    'mustChange'      => 'Gurumu sudah mengatur ulang kata sandimu. Buat kata sandi baru yang kuat, ya.',
    'showPassword'    => 'Lihat kata sandi',
    'hidePassword'    => 'Sembunyikan kata sandi',
    'capsLock'        => 'Tombol Caps Lock sedang menyala.',
];
```

`app/Language/en/Auth.php` memuat kunci yang sama dalam bahasa Inggris.

**Panel admin tetap satu bahasa (Indonesia).** Hanya area game yang dwibahasa. Ini keputusan sadar: guru/peneliti berbahasa Indonesia, dan menerjemahkan 14 halaman admin tidak memberi nilai penelitian.

### 7. Error Handling

* `app/Views/errors/html/error_404.php` dan `error_500.php` bergaya GELITA (bukan halaman CI default).
* Untuk `/api/*`, exception handler mengembalikan:

```json
{ "success": false, "code": "SERVER_ERROR", "message": "Terjadi kesalahan pada server.", "request_id": "..." }
```

* Di production, `display_errors = Off` dan stack trace hanya masuk `writable/logs/`.

### 8. Security Baseline

| Kontrol | Implementasi |
|---|---|
| CSRF | filter `csrf` pada semua POST/PUT/DELETE non-API; untuk API, token dikirim header `X-CSRF-TOKEN` yang dibaca JS dari `<meta name="csrf-token">` |
| Session | database handler, cookie `HttpOnly`, `SameSite=Lax`, `Secure` di production |
| Password | `password_hash($p, PASSWORD_DEFAULT)` + `password_verify` untuk staf **dan siswa**. Throttle: staf 5 gagal → kunci 15 menit; siswa 8 gagal → kunci 5 menit. Sandi siswa wajib lolos `PasswordPolicy` (5 syarat). Sandi mentah tidak pernah dicatat di log, event, audit, maupun `writable/debugbar` (field sandi disamarkan `App\Filters\DebugToolbar`) |
| SQL | Query Builder / prepared statements saja. Tidak ada string SQL yang dirangkai dari input |
| XSS | `esc()` pada semua output; JSON ke JS lewat `<script type="application/json">` bukan interpolasi string |
| Upload | whitelist MIME + ekstensi, verifikasi dimensi dengan `getimagesize()`, simpan dengan nama resmi, hitung SHA-256 |
| Header | `secureheaders` filter: `X-Content-Type-Options`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: same-origin` |
| Otorisasi | filter di level route, diulang di Service untuk operasi export/delete |
| Skor | validasi dan perhitungan mutlak server-side |

### 9. Spark Commands

Didaftarkan di `app/Commands/`. Kerangka kelasnya dibuat pada tahap ini; isinya dipasang pada tahap 7 (rincian perilaku di 07_FEATURE_INTEGRATION.md → *Catatan Implementasi Tahap 7*). Event `pre_system` (tempat `db_sync_timezone()` dipanggil) hanya terpicu pada request web, jadi command yang menulis waktu wajib memanggil `db_sync_timezone()` sendiri. `spark` berpindah ke folder `public/` sebelum command berjalan; path berkas relatif pada `gelita:bank:import` dicari dari direktori kerja shell lalu dari root proyek.

```text
php spark gelita:content:verify [--strict]      3 level × 5 node, engine, bank, kunci, media; kode keluar 1 bila ada galat
php spark gelita:retention:run                  retensi harian (cron): sesi/attempt menganggur, export kedaluwarsa,
                                                pratinjau penghapusan untuk data melewati retention_days
php spark gelita:score:recompute --profile CODE [--version V] [--study ID] [--node ID] [--dry-run]
php spark gelita:media:scan [--check-only]      MediaAssetSeeder (sinkron folder ↔ tabel) + laporan integritas
php spark gelita:bank:import FILE [--dry-run] [--staff USERNAME]
```

---

## Aturan Sistem

1. Tidak ada SPA framework. Seluruh halaman dirender server; JavaScript hanya menambah perilaku.
2. Setiap library baru harus punya alasan yang ditulis di `composer.json` atau komentar. Tiga paket Composer di atas adalah batasnya untuk v1.
3. Semua konten permainan dibaca dari database lewat `ContentRepository`, tidak pernah dari array PHP yang di-hard-code.
4. Bahasa disimpan di sesi permainan. Mengganti bahasa tidak boleh membuat sesi baru, mereset progres, atau mengubah skor.
5. `writable/exports/` di luar `public/`. Tidak ada berkas data penelitian yang dapat diakses tanpa melewati controller berotorisasi.
6. IP mentah tidak pernah disimpan; hanya `ip_hash`.
7. Timezone aplikasi `Asia/Jakarta`, dipakai konsisten oleh PHP dan MySQL (`SET time_zone` bila perlu).
8. Semua respons API memakai bentuk seragam `{success, data|code+message, request_id}`.
9. Aturan kata sandi siswa hanya ditulis di satu tempat (`Config\Gelita::$passwordPolicy`). JavaScript menerima salinannya dari server, tidak menulis ulang angka-angkanya sendiri.
10. Kata sandi — siswa maupun staf — tidak pernah muncul di log. Pastikan `Config\Logger` tidak mencatat body request, dan `log_message()` tidak pernah dipanggil dengan data formulir login/registrasi.

---

## Dependency

Dari **01_DATABASE.md**:

* Database `gelita` sudah ada dengan 29 tabel domain (+ `ci_sessions`) dan sudah di-seed.
* Kolom akun siswa di `participants` dan kebijakan kata sandi siswa.
* Kredensial database untuk diisi ke `.env`.
* Nama tabel dan kolom untuk konfigurasi Model pada tahap 3.

---

## Hasil Akhir

Setelah tahap ini selesai:

* `php spark serve` membuka halaman GELITA (boleh masih berisi placeholder) tanpa error.
* `php spark routes` menampilkan route dasar.
* Layout game dan admin merender lengkap dengan CSS dan modul JS termuat.
* Pergantian `?lang=en` mengubah string UI, dan pilihannya bertahan antar-request.
* Login admin dan siswa belum dibuat (tahap 4), tetapi filter, helper, service, config, `PasswordPolicy`, berkas bahasa `Auth.php`, dan struktur folder sudah siap.
* `(new \App\Libraries\PasswordPolicy())->check('Kedu#2026')` mengembalikan 5 syarat terpenuhi dan level `strong`; `check('kedu2026')` mengembalikan level `medium`.
* `composer.json` berisi tepat tiga dependency produksi.
* Struktur folder sesuai daftar di atas, dan `writable/exports/` sudah ada dengan permission tulis.
