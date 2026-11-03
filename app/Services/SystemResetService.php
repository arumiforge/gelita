<?php

namespace App\Services;

use App\Database\Seeds\ContentSeeder;
use App\Libraries\ContentVerifier;
use App\Libraries\MockupGenerator;
use App\Libraries\NarrationImporter;
use App\Libraries\StaffAccess;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Database;

/**
 * Reset total GELITA untuk penelitian kelas 5 (perombakan kelas 5, Dok 1 §7).
 *
 * Mengosongkan semua data uji coba dan pengaturan, lalu menyiapkan ulang
 * struktur bawaan: 12 tantangan, pengaturan studi/rilis/indikator, dan bank
 * soal kelas 5. Pustaka, cerita (termasuk Kenali wilayah), level, media,
 * audio, direktori sekolah resmi, dan akun staf TIDAK ikut terhapus.
 *
 * Kelompok tabel:
 *   A. data penelitian & uji coba  → dikosongkan
 *   B. konten tantangan            → dikosongkan lalu dibangun ulang
 *   C. pengaturan                  → dikosongkan lalu diisi nilai bawaan
 *   D. dibersihkan sebagian        → sekolah belum terverifikasi; akun staf
 *   E. tidak disentuh              → levels, dialogues, library_*, media, audio,
 *                                    asset packages
 *
 * Logika ini dipakai command `php spark gelita:reset` (App\Commands\SystemReset)
 * dan diuji lewat tests/unit/SystemResetTest.php (SQLite).
 */
class SystemResetService
{
    /** Migration terakhir yang wajib sudah dijalankan. */
    public const REQUIRED_MIGRATION = '2026-01-01-003500';

    /** Kata yang wajib diketik bila tanpa --ya. */
    public const CONFIRM_WORD = 'RESET-KELAS5';

    /** Kelas aktif dalam rentang ini menahan reset (pola deploy.sh). */
    public const ACTIVE_GUARD_MINUTES = 10;

    public const GROUP_A = [
        'participants', 'game_sessions', 'participant_progress', 'challenge_attempts', 'item_responses',
        'item_tries', 'audio_usage_events', 'game_event_logs', 'device_logs', 'participant_feedback',
        'data_exports', 'data_deletion_requests', 'audit_logs', 'ci_sessions',
        // research revision 10, part 2 (28 §14)
        'field_sessions', 'field_session_tokens', 'field_session_members', 'participant_seats', 'participant_code_counters',
    ];

    public const GROUP_B = ['hints', 'challenge_items', 'reading_passages', 'challenge_nodes'];

    public const GROUP_C = ['research_studies', 'game_releases', 'learning_indicators'];

    /** Tabel kelompok E (tidak disentuh); dihitung untuk ringkasan. */
    public const KEPT = ['levels', 'dialogues', 'library_pages', 'library_media', 'media_assets', 'audio_assets', 'asset_packages'];

    /** Urutan pengosongan mengikuti FK: anak dulu. */
    public const CLEAR_ORDER = [
        'item_tries', 'item_responses', 'challenge_attempts', 'audio_usage_events', 'game_event_logs',
        'device_logs', 'participant_progress', 'participant_seats', 'field_session_members', 'field_session_tokens',
        'participant_feedback', 'game_sessions', 'field_sessions', 'participant_code_counters', 'participants',
        'data_exports', 'data_deletion_requests', 'audit_logs', 'hints', 'challenge_items',
        'reading_passages', 'challenge_nodes', 'research_studies', 'game_releases', 'learning_indicators',
        'ci_sessions',
    ];

    /** Lokasi bawaan workbook bank soal kelas 5 (Dok 2 §7). */
    public const DEFAULT_BANK = 'docs/bank-soal/gelita-bank-soal-kelas5.xlsx';

    private BaseConnection $db;

    /** Folder berkas unduhan data (writable/exports) yang ikut dikosongkan */
    private string $exportsDir;

    /** @var list<string> */
    private array $warnings = [];

    /** Folder gambar mockup relatif FCPATH; null = MockupGenerator::defaultDir() */
    private ?string $mockupDir;

    public function __construct(?BaseConnection $db = null, ?string $exportsDir = null, ?string $mockupDir = null)
    {
        $this->db         = $db ?? db_connect();
        $this->exportsDir = rtrim($exportsDir ?? WRITEPATH . 'exports', '/\\') . DIRECTORY_SEPARATOR;
        $this->mockupDir  = $mockupDir;
    }

    // ------------------------------------------------------------ pemeriksaan

    /**
     * Versi migration terakhir yang tercatat, atau null bila tabel migrations
     * belum ada.
     */
    public function latestMigration(): ?string
    {
        if (! $this->db->tableExists('migrations')) {
            return null;
        }

        $row = $this->db->table('migrations')->selectMax('version', 'version')->get()->getRowArray();

        return isset($row['version']) ? (string) $row['version'] : null;
    }

    public function migrationsReady(): bool
    {
        $latest = $this->latestMigration();

        return $latest !== null && strcmp($latest, self::REQUIRED_MIGRATION) >= 0;
    }

    /** Sesi `active` yang masih bergerak dalam 10 menit terakhir. */
    public function activeSessionCount(): int
    {
        if (! $this->db->tableExists('game_sessions')) {
            return 0;
        }

        return $this->db->table('game_sessions')
            ->where('status', 'active')
            ->where('last_active_at >=', date('Y-m-d H:i:s', time() - self::ACTIVE_GUARD_MINUTES * 60))
            ->countAllResults();
    }

    /**
     * Rencana reset: jumlah baris per tabel, tanpa menulis apa pun.
     *
     * @return array{clear: array<string, int>, rebuild: array<string, int>, settings: array<string, int>,
     *               unverified_schools: int, staff: array{admin: int, peneliti: int, ketua: int}, kept: array<string, int>,
     *               nodes_after: int}
     */
    public function plan(): array
    {
        $count = fn (string $table): int => $this->db->table($table)->countAllResults();

        $clear = [];

        foreach (self::GROUP_A as $table) {
            $clear[$table] = $count($table);
        }

        $rebuild = [];

        foreach (self::GROUP_B as $table) {
            $rebuild[$table] = $count($table);
        }

        $settings = [];

        foreach (self::GROUP_C as $table) {
            $settings[$table] = $count($table);
        }

        $kept = [];

        foreach (self::KEPT as $table) {
            $kept[$table] = $count($table);
        }

        $staff = [];

        foreach (StaffAccess::ROLES as $role) {
            $staff[$role] = $this->db->table('staff_users')->where('role', $role)->countAllResults();
        }

        return [
            'clear'              => $clear,
            'rebuild'            => $rebuild,
            'settings'           => $settings,
            'unverified_schools' => $this->db->table('schools')->where('is_verified', 0)->countAllResults(),
            'staff'              => $staff,
            'kept'               => $kept,
            'nodes_after'        => 3 * count(ContentSeeder::NODES),
        ];
    }

    // ---------------------------------------------------------------- reset

    /**
     * Menjalankan reset (langkah 4–11 Dok 1 §7.3). Pemeriksaan awal dan
     * konfirmasi menjadi tanggung jawab pemanggil (command).
     *
     * @param array{tanpa_bank?: bool, bank?: ?string, tanpa_mockup?: bool} $options
     *
     * @return array{cleared: array<string, int>, schools_removed: int,
     *               bank: array<string, mixed>, mockups: array<string, mixed>,
     *               findings: list<array{level: string, scope: string, message: string}>, errors: int,
     *               nodes: int, next_code: string, warnings: list<string>, seconds: float}
     */
    public function run(array $options = []): array
    {
        $started        = microtime(true);
        $this->warnings = [];
        $plan           = $this->plan();
        $counts         = $plan['clear'] + $plan['rebuild'] + $plan['settings'];

        // 4. Kosongkan kelompok A–C
        $this->clearTables();

        // 5. Bersihkan sebagian kelompok D
        $schools  = $this->removeUnverifiedSchools();
        $this->resetStaff();

        // 6. Isi ulang bawaan
        $this->seedDefaults();

        // 7. Bank soal kelas 5
        $bank = ! empty($options['tanpa_bank'])
            ? ['status' => 'skipped', 'message' => 'Bank soal tidak diimpor (--tanpa-bank).']
            : $this->importBank($options['bank'] ?? null);

        // 8. Gambar mockup (Dok 2 §10)
        $mockups = ! empty($options['tanpa_mockup'])
            ? ['status' => 'skipped', 'count' => 0, 'message' => 'Gambar mockup tidak dibuat (--tanpa-mockup).']
            : $this->generateMockups();

        // 9. Bersihkan cache
        $this->flushCaches();

        // 10. Verifikasi
        $findings = (new ContentVerifier())->run();
        $errors   = ContentVerifier::errorCount($findings);

        // 11. Audit
        $this->audit($counts, $options, $bank);

        return [
            'cleared'          => $counts,
            'schools_removed'  => $schools,
            'bank'             => $bank,
            'mockups'          => $mockups,
            'findings'         => $findings,
            'errors'           => $errors,
            'nodes'            => $this->db->table('challenge_nodes')->where('is_active', 1)->countAllResults(),
            'next_code'        => $this->nextParticipantCode(),
            'warnings'         => $this->warnings,
            'seconds'          => round(microtime(true) - $started, 1),
        ];
    }

    /**
     * Mengosongkan kelompok A–C. MySQL/MariaDB: TRUNCATE dengan
     * FOREIGN_KEY_CHECKS = 0 agar AUTO_INCREMENT kembali ke 1, sehingga kode
     * siswa pertama sesudah reset GLT-000001. TRUNCATE butuh hak DROP: akun
     * aplikasi yang hanya punya hak DML ditolak sebelum ada tabel yang
     * dikosongkan. SQLite (uji): builder truncate().
     */
    public function clearTables(): void
    {
        if ($this->db->DBDriver === 'SQLite3') {
            $this->db->query('PRAGMA foreign_keys = OFF');

            foreach (self::CLEAR_ORDER as $table) {
                $this->db->table($table)->truncate();
            }

            $this->db->query('PRAGMA foreign_keys = ON');

            return;
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach (self::CLEAR_ORDER as $table) {
                $this->db->query('TRUNCATE TABLE ' . $this->db->escapeIdentifiers($this->db->prefixTable($table)));
            }
        } catch (DatabaseException|\mysqli_sql_exception $e) {
            throw $this->isPrivilegeError($e)
                ? new \RuntimeException('Akun database tidak punya hak TRUNCATE (DROP). Jalankan reset dengan akun migrasi (database_default_DSN akun gelita_migrate).', 0, $e)
                : $e;
        } finally {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /** Sekolah ketikan uji coba (is_verified = 0) dibuang; FK staf ke sana di-NULL-kan dulu. */
    private function removeUnverifiedSchools(): int
    {
        $ids = array_map('intval', array_column(
            $this->db->table('schools')->select('id')->where('is_verified', 0)->get()->getResultArray(),
            'id',
        ));

        if ($ids === []) {
            return 0;
        }

        $this->db->table('schools')->whereIn('merged_into_id', $ids)->update(['merged_into_id' => null]);
        $this->db->table('schools')->whereIn('id', $ids)->delete();

        return count($ids);
    }

    /** Every staff account stays (login locks are cleared). */
    private function resetStaff(): void
    {
        $this->db->table('staff_users')->where('1 = 1', null, false)->update([
            'failed_login_count' => 0,
            'locked_until'       => null,
        ]);
    }

    /** Nilai bawaan pengaturan + 12 tantangan (dialog & Pustaka tidak disentuh). */
    private function seedDefaults(): void
    {
        $seeder = Database::seeder();
        $seeder->setSilent(true);

        foreach (['LearningIndicatorSeeder', 'GameReleaseSeeder', 'ResearchStudySeeder'] as $name) {
            $seeder->call($name);
        }

        (new ContentSeeder(config('Database'), $this->db))->seedNodes();
    }

    /**
     * Impor workbook bank soal kelas 5 (Dok 2 §7). Bila berkasnya tidak ada
     * (mis. --bank menunjuk berkas yang salah), 12 tantangan tetap kosong dan
     * reset memberi peringatan.
     *
     * @return array<string, mixed>
     */
    private function importBank(?string $path): array
    {
        $path ??= ROOTPATH . self::DEFAULT_BANK;

        if (! is_file($path)) {
            $this->warnings[] = 'Workbook bank soal kelas 5 tidak ditemukan (' . $path . '); 12 tantangan masih kosong. Impor dengan php spark gelita:bank:import.';

            return ['status' => 'missing', 'path' => $path, 'message' => 'Workbook bank soal tidak ditemukan.'];
        }

        $staffId = $this->systemStaffId();

        if ($staffId === null) {
            $this->warnings[] = 'Bank soal tidak diimpor: belum ada akun admin aktif untuk dicatat sebagai pengimpor.';

            return ['status' => 'skipped', 'path' => $path, 'message' => 'Tidak ada akun admin aktif.'];
        }

        $result = (new ContentImportService())->import($path, $staffId);

        if (! $result['ok']) {
            $this->warnings[] = 'Impor bank soal gagal: ' . implode(' · ', array_map(
                static fn (array $e): string => $e['sheet'] . '#' . $e['row'] . ' ' . $e['message'],
                array_slice($result['errors'], 0, 5),
            ));
        }

        // Butir soal dibangun ulang: tautan rekaman petunjuk Cari Objek yang sudah terdaftar dipasang lagi
        $clueAudio = $result['ok'] ? (new NarrationImporter($this->db))->relinkClues() : 0;

        return [
            'status'     => $result['ok'] ? 'imported' : 'failed',
            'path'       => $path,
            'sha256'     => hash_file('sha256', $path) ?: null,
            'written'    => $result['written'],
            'errors'     => count($result['errors']),
            'items'      => $this->db->table('challenge_items')->where('is_active', 1)->countAllResults(),
            'scorable'   => $this->db->table('challenge_items')->where('is_active', 1)->where('scorable', 1)->countAllResults(),
            'variants'   => (int) array_sum(array_map(
                static fn (array $row): int => count((array) (json_decode((string) $row['config_json'], true)['variants'] ?? [])),
                $this->db->table('challenge_nodes')->select('config_json')->where('is_active', 1)->get()->getResultArray(),
            )),
            'passages'   => $this->db->table('reading_passages')->where('is_active', 1)->countAllResults(),
            'clue_audio' => $clueAudio,
        ];
    }

    /** @return array<string, mixed> */
    private function generateMockups(): array
    {
        $result = (new MockupGenerator($this->db, $this->mockupDir))->generateMissing();

        if ($result['failed'] !== []) {
            $this->warnings[] = count($result['failed']) . ' gambar contoh gagal dibuat: ' . implode('; ', array_slice($result['failed'], 0, 3));
        }

        return ['status' => 'done'] + $result;
    }

    private function flushCaches(): void
    {
        try {
            cache()->clean();
        } catch (\Throwable) {
            // cache berkas yang tidak dapat dibersihkan tidak menggagalkan reset
        }

        service('contentRepository')->flush();

        foreach (glob($this->exportsDir . '*') ?: [] as $file) {
            if (is_file($file) && basename($file) !== 'index.html' && basename($file) !== '.htaccess') {
                @unlink($file);
            }
        }
    }

    /**
     * @param array<string, int>   $counts
     * @param array<string, mixed> $options
     * @param array<string, mixed> $bank
     */
    private function audit(array $counts, array $options, array $bank): void
    {
        $this->db->table('audit_logs')->insert([
            'staff_user_id' => null,
            'action'        => 'system_reset',
            'target_type'   => 'system',
            'target_id'     => 'kelas5',
            'metadata_json' => json_encode([
                'cleared'     => $counts,
                'options'     => array_filter($options, static fn ($value): bool => $value !== null && $value !== false && ! $value instanceof \Closure),
                'host'        => gethostname() ?: null,
                'bank_sha256' => $bank['sha256'] ?? null,
                'bank_status' => $bank['status'] ?? null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'occurred_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Next student code; right after the TRUNCATE it matches ParticipantModel::assignCode() for the next id. */
    public function nextParticipantCode(): string
    {
        $max = $this->db->table('participants')->selectMax('id', 'max_id')->get()->getRowArray();

        return participant_code(((int) ($max['max_id'] ?? 0)) + 1);
    }

    private function systemStaffId(): ?int
    {
        $row = $this->db->table('staff_users')
            ->select('id')
            ->where('role', 'admin')
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        return $row === null ? null : (int) $row['id'];
    }

    private function isPrivilegeError(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'command denied')
            || str_contains($message, 'access denied')
            || in_array((int) $e->getCode(), [1142, 1044, 1045, 1227], true);
    }
}
