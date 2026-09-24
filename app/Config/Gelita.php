<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konstanta domain GELITA yang tidak perlu masuk database.
 * Properti skalar dapat ditimpa .env dengan awalan `gelita.`.
 */
class Gelita extends BaseConfig
{
    /** Tipe engine tantangan yang dikenali sistem */
    public array $engineTypes = ['puzzle', 'rumpang', 'boleh', 'pilihan', 'cari'];

    /**
     * Awalan node_ref / item_key per wilayah (tmg-1 … wnb-5). Satu-satunya
     * peta kode: dipakai impor workbook, editor konten, dan kunci aset.
     */
    public array $levelPrefixes = ['tmg' => 'temanggung', 'mgl' => 'magelang', 'wnb' => 'wonosobo'];

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
        'levels'       => ['weak' => [0, 2], 'medium' => [3, 4], 'strong' => [5, 5]],
        'accept_level' => 'strong',   // registrasi hanya diterima bila kuat
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
        'jaka.idle'  => ['frames' => 3, 'fps' => 4, 'loop' => true],
        'jaka.bow'   => ['frames' => 3, 'fps' => 6, 'loop' => false],
        'jaka.happy' => ['frames' => 3, 'fps' => 6, 'loop' => false],
        'kedu.idle'  => ['frames' => 3, 'fps' => 3, 'loop' => true],
    ];

    /** Ukuran piksel wajib per slot aset, dipakai validasi upload */
    public array $assetSizes = [
        'bg.*'             => [1920, 1080],
        'char.*'           => [700, 900],
        'map.region'       => [1400, 900],
        'challenge.scene'  => [1280, 720],
        'challenge.card'   => [480, 360],
        'challenge.option' => [400, 400],
        'library.image'    => [960, 640],
        'reward.badge'     => [320, 320],
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
