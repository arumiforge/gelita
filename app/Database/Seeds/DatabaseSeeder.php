<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * GELITA_ADMIN_PASSWORD='SandiKuatAnda123!' php spark db:seed DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('StaffUserSeeder');
        $this->call('LearningIndicatorSeeder');
        $this->call('ScoringProfileSeeder');
        $this->call('GameReleaseSeeder');
        $this->call('ResearchStudySeeder');
        $this->call('LevelSeeder');
        $this->call('MediaAssetSeeder');
        $this->call('ContentSeeder');

        if (ENVIRONMENT === 'development') {
            $this->call('SampleItemSeeder');
        }
    }
}
