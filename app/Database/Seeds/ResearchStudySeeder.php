<?php

namespace App\Database\Seeds;

/**
 * Satu studi penelitian kelas 5, satu tahap bermain (tanpa fase).
 */
class ResearchStudySeeder extends GelitaSeeder
{
    public const STUDY_CODE = 'STUDI-KEDU-KELAS5-2026';

    public function run(): void
    {
        $this->insertIfMissing('research_studies', ['code' => self::STUDY_CODE], [
            'name'           => 'Penelitian GELITA Kelas 5 SD/MI 2026',
            'year_label'     => '2026',
            'status'         => 'active',
            'retention_days' => 1825,
            'default_locale' => 'id',
            'unlock_mode'    => 'sequential',
        ]);
    }
}
