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
        'global' => ['intro', 'map_intro', 'ending'],
        'level'  => ['region_intro', 'level_open', 'level_done'],
    ];

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
        'jaka.idle'       => ['frames' => 3, 'fps' => 4, 'loop' => true],
        'jaka.bow'        => ['frames' => 3, 'fps' => 6, 'loop' => false],
        'jaka.happy'      => ['frames' => 3, 'fps' => 6, 'loop' => false],
        'jaka.sad'        => ['frames' => 3, 'fps' => 4, 'loop' => false],
        'jaka.afraid'     => ['frames' => 3, 'fps' => 6, 'loop' => false],
        'jaka.determined' => ['frames' => 3, 'fps' => 6, 'loop' => false],
        'kedu.idle'       => ['frames' => 3, 'fps' => 3, 'loop' => true],
        'kedu.smile'      => ['frames' => 3, 'fps' => 4, 'loop' => false],
        'kedu.worried'    => ['frames' => 3, 'fps' => 4, 'loop' => false],
        'kedu.weak'       => ['frames' => 3, 'fps' => 3, 'loop' => false],
    ];

    /** Ukuran piksel wajib per slot aset, dipakai validasi upload */
    public array $assetSizes = [
        'bg.*'             => [1920, 1080],
        'char.*'           => [700, 900],
        'map.region'       => [1400, 900],
        // tantangan (Dok 1 §6): adegan Cari Objek, gambar Susun Gambar (node 1,
        // minimal), benda Cari Objek (node 4), gambar pernyataan (node 3)
        'challenge.*.scene'     => [1280, 720],
        'challenge.item.*-1-*'  => [1200, 1200],
        'challenge.item.*-4-*'  => [240, 240],
        'challenge.item.*-3-*'  => [480, 360],
        'library.image'    => [960, 640],
        'reward.badge'     => [320, 320],
        'ui.logo-hero'     => [1600, 600],
        'ui.btn-start'     => [720, 240],
        'ui.btn-start.*'   => [720, 240],   // varian bahasa: ui.btn-start.en
        'bg.loading'       => [1920, 1080], // tirai layar penuh (components/curtain.php)
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
        'map.region'            => [880, 566],   // pin wilayah/node dalam persen
        'challenge.*.scene'     => [864, 486],   // benda Cari Objek dalam persen
        'challenge.item.*-1-*'  => [504, 504],   // papan Susun Gambar
        'challenge.item.*-4-*'  => [0, 0],       // lebar = w% adegan (persegi)
        'challenge.item.*-3-*'  => [192, 144],   // kartu pernyataan; lightbox 640 × 480
        'ui.logo-hero'          => [800, 300],
        'ui.btn-start'          => [360, 120],
        'ui.btn-start.*'        => [360, 120],
        'bg.loading'            => [1280, 720],
    ];

    public int $maxUploadBytes      = 64 * 1024 * 1024;
    public int $maxEventsPerBatch   = 50;
    public int $exportRetentionDays = 7;
    public int $sessionIdleMinutes  = 45;   // lewat ini → status 'paused'

    /** Retensi terjadwal (gelita:retention:run) */
    public int $attemptAbandonHours = 24;   // attempt in_progress tak tersentuh → 'abandoned'
    public int $sessionAbandonDays  = 30;   // sesi 'paused' tak tersentuh → 'abandoned'

    /**
     * Ambang estimasi baris sheet Raw Events. Di atas ini export ditolak dan
     * pemohon diminta mempersempit rentang tanggal.
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
}
