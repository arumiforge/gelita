<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Retensi terjadwal (07_FEATURE_INTEGRATION.md FITUR 17), dijalankan cron
 * harian. Tidak pernah menghapus data penelitian: data yang melewati
 * `retention_days` hanya dibuatkan pratinjau penghapusan untuk admin.
 */
class RetentionRun extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:retention:run';
    protected $description = 'Menjalankan retensi: sesi/attempt menganggur, export kedaluwarsa, pratinjau masa simpan.';
    protected $usage       = 'gelita:retention:run';

    public function run(array $params)
    {
        // pre_system (tempat zona waktu DB disetel) tidak terpicu di CLI
        db_sync_timezone();

        try {
            $result = service('retentionService')->run(null);
        } catch (\Throwable $e) {
            CLI::error('Retensi gagal: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        CLI::write('Retensi ' . $result['at'], 'green');
        CLI::table([
            ['Sesi menganggur → paused', (string) $result['stale_sessions']],
            ['Attempt menggantung → abandoned', (string) $result['abandoned_attempts']],
            ['Sesi paused lama → abandoned', (string) $result['abandoned_sessions']],
            ['Berkas export kedaluwarsa dibuang', (string) $result['expired_exports']],
            ['Pratinjau penghapusan masa simpan', (string) count($result['retention_previews'])],
        ], ['Langkah', 'Jumlah']);

        foreach ($result['retention_previews'] as $preview) {
            CLI::write(sprintf(
                'Studi #%d: %d baris melewati masa simpan (sesi s.d. %s) → pratinjau #%d menunggu keputusan admin di /admin/tata-kelola.',
                $preview['study_id'],
                $preview['total'],
                $preview['date_to'],
                $preview['request_id'],
            ), 'yellow');
        }

        foreach ($result['warnings'] as $warning) {
            CLI::write($warning, 'yellow');
        }

        return EXIT_SUCCESS;
    }
}
