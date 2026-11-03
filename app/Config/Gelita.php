<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konstanta domain GELITA yang tidak perlu masuk database.
 * Properti skalar dapat ditimpa .env dengan awalan `gelita.`.
 */
class Gelita extends BaseConfig
{
    /** Engine tantangan (urutan = urutan node di setiap wilayah) */
    public array $engineTypes = ['puzzle', 'rumpang', 'boleh', 'cari'];

    /** Jenis interaksi soal yang dikenali sistem */
    public array $interactionTypes = ['puzzle_arrange', 'fill_blank_bank', 'verdict_card', 'find_object'];

    /** Batas percobaan per soal; lewat dari ini jawaban disimpan apa adanya (Dok 2 §5) */
    public int $maxTries = 3;

    /** Nilai jenis kelamin yang diterima pendaftaran */
    public array $genders = ['laki-laki', 'perempuan'];

    /** Rentang umur pendaftaran siswa kelas 5 (inklusif) */
    public array $registrationAge = [8, 15];

    /** Client Hints yang diminta lewat header Accept-CH (App\Filters\ClientHintsFilter) */
    public array $clientHints = [
        'Sec-CH-UA-Model', 'Sec-CH-UA-Platform-Version', 'Sec-CH-UA-Full-Version-List',
        'Sec-CH-UA-Arch', 'Sec-CH-UA-Bitness',
    ];

    /**
     * Awalan node_ref / item_key per wilayah (tmg-1 … wnb-4). Satu-satunya
     * peta kode: dipakai impor workbook, editor konten, dan kunci aset.
     */
    public array $levelPrefixes = ['tmg' => 'temanggung', 'mgl' => 'magelang', 'wnb' => 'wonosobo'];

    /** Locale yang didukung konten permainan */
    public array $locales = ['id', 'en'];

    /** Karakter yang punya suara & sprite */
    public array $characters = ['jaka', 'mbah_kedu'];

    /**
     * Pose sah per tokoh untuk `dialogues.pose` (docs/naskah-cerita.md,
     * "Kamus pose dan efek"). Narator tidak tampil sebagai gambar, jadi tanpa
     * pose. Gambar tiap pose ada di slot `char.{jaka|kedu}.{pose}.{n}`
     * ($characterAnimations); bila belum diunggah, tokoh tampil dengan `idle`.
     */
    public array $characterPoses = [
        'narator'   => [],
        'jaka'      => ['idle', 'happy', 'bow', 'sad', 'afraid', 'determined'],
        'mbah_kedu' => ['idle', 'smile', 'worried', 'weak'],
    ];

    /** Efek layar sah untuk `dialogues.effect`; tidak diputar bila gerak dikurangi */
    public array $dialogueEffects = ['fog', 'fog-lift', 'glow', 'flash', 'shake', 'dim'];

    /**
     * Konteks `dialogues.context_code`: global (level_id NULL) atau per
     * wilayah. Urutannya mengikuti alur permainan dan naskah.
     */
    public array $dialogueContexts = [
        'global' => ['intro', 'map_intro', 'ending', 'map_done'],
        'level'  => ['region_intro', 'level_open', 'level_done'],
    ];

    /**
     * Contexts whose slides may have a background of their own
     * (`dialogues.background_media_id`, slot DialogueModel::backgroundKey()).
     * A slide without one keeps the default: the story background for
     * `intro`/`ending`, the region background for the region contexts.
     * `map_intro` and `map_done` play in the Peta Kedu story bar and have no backdrop.
     */
    public array $dialogueBackgroundContexts = ['intro', 'ending', 'region_intro', 'level_open', 'level_done'];

    /**
     * WebGL screen effects on the narrated scenes (owner, 19 October 2026):
     * drifting fog, lantern sparks, light rays with bloom, and background
     * parallax, drawn by game/screen-fx.js with three.js. Off: the scenes keep
     * the CSS effects only (`.env`: gelita.screenFx = false). The browser
     * also falls back to the CSS effects without WebGL, on reduced motion,
     * after a lost WebGL context, or when frames stay too slow.
     */
    public bool $screenFx = true;

    /** three.js entry under public/assets/ (the folder carries the version; vendor/README.md) */
    public string $screenFxThree = 'vendor/three-0.186.1/three.module.js';

    /**
     * Event type yang boleh ditulis ke game_event_logs (perombakan kelas 5,
     * Dok 1 §12.3). Data mentah: tidak ada skor atau indeks yang dihitung.
     */
    public array $eventTypes = [
        // sesi & navigasi
        'session_started', 'session_resumed', 'session_paused', 'session_abandoned',
        'session_completed', 'locale_changed', 'level_opened', 'level_completed',
        'library_opened', 'library_page_viewed', 'dialogue_advanced', 'intro_completed',
        'screen_viewed', 'overlay_opened', 'page_hidden', 'page_visible', 'viewport_changed',
        // tantangan
        'challenge_opened', 'challenge_checked', 'challenge_completed', 'challenge_abandoned',
        'challenge_tries_exhausted', 'answer_submitted', 'answer_changed', 'answer_selected',
        'wrong_target_clicked', 'clue_failed', 'reading_progress', 'hint_opened',
        // audio
        'audio_play', 'audio_autoplay', 'audio_pause', 'audio_replay', 'audio_completed',
        // lain-lain
        'feedback_submitted', 'password_changed',
        // asset package and event queue integrity (rencana-paket-aset §7.1, §7.3)
        'package_changed', 'events_dropped',
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
        'levels'       => ['weak' => [0, 2], 'medium' => [3, 4], 'strong' => [5, 5]],
        'accept_level' => 'strong',   // registrasi hanya diterima bila kuat
    ];

    /**
     * Provinsi yang daftar sekolah resminya ada di tabel `schools`
     * (`gelita:schools:import`, docs/sekolah/). Siswa yang memilih provinsi ini
     * wajib mengisi NPSN; provinsi lain menulis nama sekolah sendiri.
     */
    public array $schoolDirectoryProvinces = ['33'];

    /**
     * Sasaran penelitian: siswa kelas 5 SD/MI. Pendaftaran hanya menerima
     * kelas ini.
     */
    public string $registrationClass = '5';

    /**
     * Jenjang sekolah (`schools.stage`) yang boleh dipakai mendaftar lewat
     * NPSN. SLB tetap boleh karena memuat SDLB. Sekolah yang diketik manual
     * atau di luar provinsi berdirektori tidak dapat diperiksa.
     */
    public array $registrationSchoolStages = ['sd', 'slb'];

    /*
     * Aturan per tingkat kesulitan wilayah (ukuran papan Susun Gambar,
     * jumlah rumpang & pengecoh bacaan, rentang Taksonomi Bloom) ada di
     * App\Libraries\DifficultyRules, karena dibaca juga oleh skrip
     * docs/bank-soal/build-workbook.php yang berjalan tanpa framework.
     */

    /** Nama pengguna siswa yang tidak boleh dipakai */
    public array $reservedUsernames = ['admin', 'guru', 'peneliti', 'ketua', 'gelita', 'root', 'system', 'test'];

    /** Throttle login */
    public array $loginThrottle = [
        'participant' => ['max_fail' => 8, 'lock_minutes' => 5],   // anak sering salah ketik
        'staff'       => ['max_fail' => 5, 'lock_minutes' => 15],
    ];

    /**
     * A staff session ends after this many seconds without a page request
     * (revision 10, part 1, 27 §11.1). Student sessions are not affected.
     */
    public int $staffIdleSeconds = 7200;

    /**
     * Lets migration 2026-01-01-003400 delete existing `guru` accounts and
     * their exports (27 K16). Off by default: the migration stops while such
     * accounts exist. Set `gelita.allowTeacherRemoval = true` in .env only
     * when the owner has decided to run it, and remove it afterwards.
     */
    public bool $allowTeacherRemoval = false;

    /**
     * Character animation per pose: up to `frames` images
     * `char.{jaka|kedu}.{pose}.{1..frames}` in media_assets, always looping
     * (owner decision 11 October 2026: 24 frames per pose, repeated).
     * `seconds` is the length of one loop. The speed follows the number of
     * frames actually uploaded (character_fps()): 24 frames in 2 s play at
     * 12 per second, 3 interim frames in 3 s at 1 per second. Frames that are
     * not uploaded are skipped.
     *
     * @var array<string, array{frames: int, seconds: float, loop: bool}>
     */
    public array $characterAnimations = [
        'jaka.idle'       => ['frames' => 24, 'seconds' => 3.0, 'loop' => true],
        'jaka.bow'        => ['frames' => 24, 'seconds' => 2.5, 'loop' => true],
        'jaka.happy'      => ['frames' => 24, 'seconds' => 2.0, 'loop' => true],
        'jaka.sad'        => ['frames' => 24, 'seconds' => 3.0, 'loop' => true],
        'jaka.afraid'     => ['frames' => 24, 'seconds' => 2.0, 'loop' => true],
        'jaka.determined' => ['frames' => 24, 'seconds' => 2.0, 'loop' => true],
        'kedu.idle'       => ['frames' => 24, 'seconds' => 3.5, 'loop' => true],
        'kedu.smile'      => ['frames' => 24, 'seconds' => 3.0, 'loop' => true],
        'kedu.worried'    => ['frames' => 24, 'seconds' => 3.0, 'loop' => true],
        'kedu.weak'       => ['frames' => 24, 'seconds' => 3.5, 'loop' => true],
    ];

    /**
     * Required file size per asset_key pattern, enforced on upload
     * (MediaStore::requiredSize(): exact key or fnmatch). Every pattern must
     * match a real slot key (ImageSlotsTest). Library images have no
     * required size, only a recommended one in ImageSlots.
     */
    public array $assetSizes = [
        'bg.*'             => [1920, 1080],
        'char.*'           => [700, 900],
        'map.region.*'     => [1400, 900],
        // tantangan (Dok 1 §6): adegan Cari Objek, gambar Susun Gambar (node 1,
        // minimal), benda Cari Objek (node 4), gambar pernyataan (node 3)
        'challenge.*.scene'     => [1280, 720],
        'challenge.item.*-1-*'  => [1200, 1200],
        'challenge.item.*-4-*'  => [240, 240],
        'challenge.item.*-3-*'  => [480, 360],
        'reward.badge.*'   => [320, 320],
        'ui.logo-hero'     => [1600, 600],
        'ui.btn-start'     => [1200, 220],  // long carved plate, about 5.5 : 1 (owner, 20 October 2026, second version)
        'ui.btn-start.*'   => [1200, 220],   // varian bahasa: ui.btn-start.en
        'ui.lang.*'        => [256, 256],   // language switch icons ui.lang.id / ui.lang.en
        'bg.loading'       => [1920, 1080], // tirai layar penuh (components/curtain.php)
        // App icons (AppController::manifest(), app_icon_src())
        'app.favicon'       => [48, 48],
        'app.apple-touch'   => [180, 180],
        'app.icon-192'      => [192, 192],
        'app.icon-512'      => [512, 512],
        'app.icon-maskable' => [512, 512],
        'avatar.*'          => [512, 512],
        // City art (display plan Phase C); bg.loading.{wilayah} falls under bg.*
        'ui.pin'            => [256, 256],
        'ui.lantern'        => [256, 256],
        'map.pin.*'         => [256, 256],
        'map.lantern.*'     => [256, 256],
        // Result screen skin (redesign 18 October 2026); bg.result.{wilayah} falls under bg.*
        'ui.result-panel'            => [1536, 752],
        'ui.result-button-primary'   => [600, 132],
        'ui.result-button-secondary' => [480, 112],
        'ui.result-button-detail'    => [320, 112],
        'ui.result-hud-control'      => [128, 128],
        'ui.result-star'             => [128, 128],
        'ui.result-medal'            => [512, 512],
        // Peta Kedu island map (owner, 20 October 2026): the region masks and pins below are measured on it
        'map.kedu-diorama'           => [1600, 900],
        // Jaka's bust avatar in the Peta Kedu story bar (owner, 18 October 2026)
        'ui.jaka-avatar'             => [512, 512],
        // Mbah Kedu's avatar in the Kenali overlay (owner, 19 October 2026)
        'ui.kedu-avatar'             => [512, 512],
    ];

    /**
     * Ukuran TAMPIL slot di panggung 16:9, dalam dp pada kanvas 1280 × 720
     * (Dok 3 §6), dengan pola kunci yang sama dengan $assetSizes. Hanya
     * keterangan untuk ilustrator dan halaman Media; validasi unggahan tetap
     * memakai $assetSizes (ukuran berkas).
     *
     * @var array<string, array{0: int, 1: int}>
     */
    public array $assetDisplaySizes = [
        'bg.*'                  => [1280, 720],  // latar penuh panggung, rasio sama
        'char.*'                => [300, 386],   // cerita & dialog; 220 × 283 di pendaftaran langkah 3
        'map.region.*'          => [880, 566],   // pin wilayah/node dalam persen
        'challenge.*.scene'     => [864, 486],   // benda Cari Objek dalam persen
        'challenge.item.*-1-*'  => [504, 504],   // papan Susun Gambar
        'challenge.item.*-4-*'  => [0, 0],       // lebar = w% adegan (persegi)
        'challenge.item.*-3-*'  => [192, 144],   // kartu pernyataan; lightbox 640 × 480
        'reward.badge.*'        => [72, 72],     // profile badge shelf (.region-badge-art, 4.5rem); the only screen
        'ui.logo-hero'          => [800, 300],
        'ui.btn-start'          => [480, 88],
        'ui.btn-start.*'        => [480, 88],
        'ui.lang.*'             => [32, 32],     // HUD language switch
        'bg.loading'            => [1280, 720],
        'avatar.*'              => [72, 72],     // profile; 36 × 36 in the HUD
        'ui.pin'                => [56, 56],     // map pin of every city without its own
        'map.pin.*'             => [56, 56],
        'ui.result-panel'       => [1017, 495], // result screen (/selesai/{id})
        'ui.result-medal'       => [223, 223],
        'ui.result-star'        => [57, 57],
        'ui.result-hud-control' => [68, 68],
        'map.kedu-diorama'      => [896, 504],  // Peta Kedu, the whole 16:9 picture, edges fading into the page (since 20 Oct 2026)
        'ui.jaka-avatar'        => [232, 232],  // Kenali overlay avatar; 120 × 120 in the Peta Kedu story bar
        'ui.kedu-avatar'        => [232, 232],  // Kenali overlay: round avatar on the caption's left end
    ];

    /**
     * Peta Kedu island map (`map.kedu-diorama`, the owner's picture of
     * 20 October 2026, catalogue mockup revisi-05), keyed by region code.
     * Measured on that picture; a replacement picture needs new values.
     *
     * - mask: the region's land as a feathered alpha picture in
     *         public/assets/ui/ (the rivers are the borders); a locked
     *         region's land is darkened through it;
     * - pin:  centre of the region's medallion, percent x / y of the picture.
     *
     * Regions without an entry show no shade and use their own map_x / map_y.
     *
     * @var array<string, array{mask: string, pin: array{0: float, 1: float}}>
     */
    public array $mapDiorama = [
        'temanggung' => [
            'mask' => 'ui/kedu-region-temanggung.webp',
            'pin'  => [69.0, 26.0],
        ],
        'magelang' => [
            'mask' => 'ui/kedu-region-magelang.webp',
            'pin'  => [66.0, 68.0],
        ],
        'wonosobo' => [
            'mask' => 'ui/kedu-region-wonosobo.webp',
            'pin'  => [27.0, 42.0],
        ],
    ];

    /**
     * Number of avatar slots (`avatar.1` … `avatar.N`) a student can choose
     * from (display plan Phase B). Only uploaded ones are offered.
     */
    public int $avatarSlots = 10;

    /**
     * Minify full HTML pages and put the "Mr. F" watermark at the top of the
     * page source (App\Filters\HtmlMinifyFilter, App\Libraries\HtmlMinifier).
     */
    public bool $minifyHtml = true;

    /**
     * Serve the minified mirror of JS/CSS (public/assets/min/, built by
     * `gelita:aset:minify`, App\Libraries\AssetMinifier) to browsers and into
     * asset packages while it is fresh. The sources stay unminified. Off in
     * tests (see __construct()), so a mirror on a developer machine never
     * changes the URLs other tests expect.
     */
    public bool $minifyAssets = true;

    public int $maxUploadBytes      = 64 * 1024 * 1024;
    public int $maxEventsPerBatch   = 50;

    /**
     * Folder for uploaded and installed media, relative to public/ and ending
     * in `/` (read through MediaStore::uploadDir()). The test suite uses its
     * own folder (see __construct()) so a test run never overwrites or
     * deletes the real uploads of a developer machine.
     */
    public string $uploadDir = 'assets/uploads/';

    /**
     * Client event queue rhythm (core/events.js via js_config() `eventFlush`;
     * rencana-log-asinkron §5.1 R1, owner decision 17 October 2026): send a
     * batch every `intervalMs`, or as soon as `maxEvents` are waiting. Forced
     * sends (tab hidden, page close, before navigation) do not wait for it.
     * Tunable per server in `.env` without a JS deploy:
     * `gelita.eventFlush.intervalMs = 30000`, `gelita.eventFlush.maxEvents = 40`.
     *
     * @var array{intervalMs: int, maxEvents: int}
     */
    public array $eventFlush = ['intervalMs' => 30000, 'maxEvents' => 40];
    public int $exportRetentionDays = 7;
    public int $sessionIdleMinutes  = 45;   // lewat ini → status 'paused'

    /** Retensi terjadwal (gelita:retention:run) */
    public int $attemptAbandonHours = 24;   // attempt in_progress tak tersentuh → 'abandoned'
    public int $sessionAbandonDays  = 30;   // sesi 'paused' tak tersentuh → 'abandoned'

    /**
     * Batas baris lembar Event Mentah, Navigasi, dan Penyelesaian (Dok 3 §9.2).
     * Di atas ini ekspor ditolak dan pemohon diminta mempersempit rentang tanggal.
     */
    public int $exportMaxRawEvents = 200000;

    /**
     * Penghapusan dibatalkan bila total baris terdampak saat eksekusi
     * menyimpang dari pratinjau lebih dari max(minimum, persen × pratinjau).
     */
    public int $deletionDriftMinRows    = 10;
    public float $deletionDriftFraction = 0.10;

    /** Garam hash IP (gelita.ipSalt di .env). IP mentah tidak pernah disimpan. */
    public string $ipSalt = '';

    /** Versi aset untuk cache-busting (gelita.assetVersion di .env) */
    public string $assetVersion = '1';

    /**
     * Asset package settings (docs/rencana-paket-aset.md §5, §6.3).
     *
     * - `aktif`: server-level kill switch. `gelita.paket.aktif = false` in
     *   .env turns package mode off even when the active release names a
     *   package; helpers then return normal URLs and /api/paket/aktif answers
     *   `package_code: null`.
     * - `batchSize`: files per build batch (admin panel: one request per batch).
     * - `copyLibraryImages`: copy Wikimedia Commons images of the library to
     *   the server while building (owner decision P4a).
     * - `budgets`: size budget per asset_key pattern in bytes (§2.2); larger
     *   files only produce a build warning. First matching pattern wins.
     *
     * @var array{aktif: bool, batchSize: int, copyLibraryImages: bool, budgets: array<string, int>}
     */
    public array $paket = [
        'aktif'             => true,
        'batchSize'         => 25,
        'copyLibraryImages' => true,
        'budgets'           => [
            'bg.*'                 => 450 * 1024,
            'map.*'                => 600 * 1024,
            'char.*'               => 250 * 1024,
            'ui.*'                 => 250 * 1024,
            'reward.*'             => 60 * 1024,
            'challenge.*.scene'    => 350 * 1024,
            'challenge.*'          => 250 * 1024,
            'library.*'            => 250 * 1024,
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // Tests store media under the same official names as a real install
        // (e.g. ui.logo-hero.webp), so they get a folder of their own
        if (ENVIRONMENT === 'testing') {
            $this->uploadDir    = 'assets/uploads-test/';
            $this->minifyAssets = false;
        }
    }
}
