<?php

namespace App\Database\Seeds;

use App\Services\ScoringService;
use CodeIgniter\Database\RawSql;

/**
 * Rilis bawaan penelitian kelas 5. `scoring_version` berisi kode aturan
 * bintang (ScoringService::VERSION). Rilis baru hanya aktif bila belum ada
 * rilis aktif lain (setelah `gelita:reset` tabel ini kosong).
 */
class GameReleaseSeeder extends GelitaSeeder
{
    public const RELEASE_CODE = 'GELITA-KELAS5-2026';

    public function run(): void
    {
        $hasActive = $this->db->table('game_releases')->where('is_active', 1)->countAllResults() > 0;

        $this->insertIfMissing('game_releases', ['release_code' => self::RELEASE_CODE], [
            'app_version'     => '2.0.0',
            'content_version' => 'kelas5-1',
            'asset_version'   => '1',
            'scoring_version' => ScoringService::VERSION,
            'published_at'    => new RawSql('CURRENT_TIMESTAMP(6)'),   // jam server DB, sama dengan kolom default
            'notes'           => 'Rilis penelitian GELITA kelas 5 SD/MI (perombakan kelas 5).',
            'is_active'       => $hasActive ? 0 : 1,
        ]);
    }
}
