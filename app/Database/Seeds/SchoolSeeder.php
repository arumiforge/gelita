<?php

namespace App\Database\Seeds;

use App\Libraries\SchoolImporter;

/**
 * Daftar sekolah resmi Jawa Tengah (NPSN) — sama dengan
 * `php spark gelita:schools:import`. Idempotent: hanya baris yang berubah
 * yang ditulis, dan tidak ada sekolah yang dihapus.
 */
class SchoolSeeder extends GelitaSeeder
{
    public function run(): void
    {
        $report = (new SchoolImporter($this->db))->import();

        $this->info(sprintf(
            'sekolah resmi: %d (%d baru, %d diperbarui)',
            $report['total'],
            $report['added'],
            $report['updated'],
        ));
    }
}
