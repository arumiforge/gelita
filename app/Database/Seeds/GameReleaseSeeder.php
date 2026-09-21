<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\RawSql;

class GameReleaseSeeder extends GelitaSeeder
{
    public function run(): void
    {
        $this->insertIfMissing('game_releases', ['release_code' => 'GELITA-2026-01'], [
            'app_version'     => '1.0.0',
            'content_version' => '1',
            'asset_version'   => '1',
            'scoring_version' => '2.0',
            'published_at'    => new RawSql('CURRENT_TIMESTAMP(6)'),   // jam server DB, sama dengan kolom default
            'notes'           => 'Rilis awal GELITA (seed).',
            'is_active'       => 1,
        ]);
    }
}
