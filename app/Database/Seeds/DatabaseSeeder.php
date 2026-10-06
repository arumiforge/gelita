<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Instalasi baru GELITA, sesudah `php spark migrate`:
 *
 *   GELITA_ADMIN_PASSWORD='SandiKuatAnda123!' php spark db:seed DatabaseSeeder
 *
 * Mengisi sekolah resmi, akun admin, pengaturan bawaan (indikator, versi
 * permainan, studi), tiga wilayah, slot media, 12 tantangan dan cerita,
 * lalu Pustaka Kedu, bank soal kelas 5, dan gambar contoh
 * (KontenKelas5Seeder). Sesudahnya tinggal impor narasi
 * (`php spark gelita:narration:import`) dan persetujuannya di panel.
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
        $this->call('KontenKelas5Seeder');
    }
}
