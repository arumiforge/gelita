<?php

namespace Tests\Support\Database;

use App\Services\SystemResetService;
use CodeIgniter\Database\BaseConnection;
use Config\Services;

/**
 * Dunia permainan kelas 5 di SQLite (grup `tests`): skema lengkap
 * (ResetTables), tiga wilayah, akun admin, lalu `gelita:reset` sungguhan
 * yang mengimpor workbook bank soal kelas 5 di repositori (126 soal, 30
 * variasi). Satu siswa dengan satu sesi aktif (ketiga wilayah terbuka)
 * siap bermain.
 *
 * Impor workbook hanya dijalankan sekali per kelas uji; uji berikutnya
 * memulihkan isi tabel dari salinan, agar suite tetap cepat.
 */
trait GradeFiveWorld
{
    use ResetTables;

    /** @var array<string, list<array<string, mixed>>>|null isi tabel sesudah reset + impor */
    private static ?array $gradeFiveSnapshot = null;

    private function createGradeFiveWorld(): BaseConnection
    {
        $db = $this->createResetTables();

        if (self::$gradeFiveSnapshot === null) {
            $this->seedGradeFiveBase($db);

            $exports = sys_get_temp_dir() . '/gelita-dunia-' . bin2hex(random_bytes(4));
            mkdir($exports);

            try {
                $result = (new SystemResetService($db, $exports))->run(['tanpa_mockup' => true]);
            } finally {
                array_map('unlink', glob($exports . '/*') ?: []);
                rmdir($exports);
            }

            if ($result['bank']['status'] !== 'imported' || $result['errors'] !== 0) {
                throw new \RuntimeException('Bank soal kelas 5 gagal disiapkan: ' . json_encode($result['warnings']));
            }

            self::$gradeFiveSnapshot = [];

            foreach (array_keys(self::$resetSchema) as $table) {
                self::$gradeFiveSnapshot[$table] = $db->table($table)->get()->getResultArray();
            }
        } else {
            foreach (self::$gradeFiveSnapshot as $table => $rows) {
                if ($rows !== []) {
                    $db->table($table)->insertBatch($rows);
                }
            }
        }

        $db->table('participants')->insert([
            'participant_code' => 'GLT-000001', 'username' => 'jaka5', 'password_hash' => 'x', 'display_name' => 'Jaka',
        ]);
        $db->table('game_sessions')->insert([
            'session_code' => 'S-KELAS5', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active',
        ]);
        // Ketiga wilayah terbuka (unlock_mode free), agar setiap tantangan dapat langsung dibuka
        $db->table('research_studies')->update(['unlock_mode' => 'free']);

        foreach (['contentRepository', 'gameContext', 'challengeService', 'eventService', 'scoringService', 'sessionService', 'assetPackages'] as $name) {
            Services::resetSingle($name);
        }

        return $db;
    }

    /** Id node aktif dari kode tantangan, mis. `tmg-2`. */
    private function nodeId(BaseConnection $db, string $ref): int
    {
        [$prefix, $sequence] = explode('-', $ref);
        $levelCode           = config('Gelita')->levelPrefixes[$prefix];

        return (int) $db->table('challenge_nodes n')->select('n.id')
            ->join('levels l', 'l.id = n.level_id')
            ->where('l.code', $levelCode)->where('n.sequence', (int) $sequence)
            ->get()->getRow('id');
    }

    private function seedGradeFiveBase(BaseConnection $db): void
    {
        foreach (['temanggung' => 'mudah', 'magelang' => 'sedang', 'wonosobo' => 'sulit'] as $code => $difficulty) {
            $db->table('levels')->insert([
                'sequence' => $db->table('levels')->countAllResults() + 1, 'code' => $code, 'name_id' => ucfirst($code), 'name_en' => ucfirst($code),
                'difficulty' => $difficulty, 'focus_id' => 'Fokus', 'focus_en' => 'Focus',
            ]);
        }

        $db->table('staff_users')->insert([
            'username' => 'admin_uji', 'password_hash' => 'x', 'role' => 'admin', 'display_name' => 'Admin', 'is_active' => 1,
        ]);
    }
}
