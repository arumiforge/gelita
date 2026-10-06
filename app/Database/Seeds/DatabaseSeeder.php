<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * GELITA_ADMIN_PASSWORD='SandiKuatAnda123!' php spark db:seed DatabaseSeeder
 *
 * Di CI_ENVIRONMENT = development bank soal kelas 5
 * (docs/bank-soal/gelita-bank-soal-kelas5.xlsx) ikut diimpor lewat
 * BankKelas5Seeder; lingkungan lain memakai `php spark gelita:reset`.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('SchoolSeeder');
        $this->call('StaffUserSeeder');
        $this->call('LearningIndicatorSeeder');
        $this->call('GameReleaseSeeder');
        $this->call('ResearchStudySeeder');
        $this->call('LevelSeeder');
        $this->call('MediaAssetSeeder');
        $this->call('ContentSeeder');

        if (ENVIRONMENT === 'development') {
            $this->call('BankKelas5Seeder');
        }
    }
}
