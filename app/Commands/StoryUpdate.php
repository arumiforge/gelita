<?php

namespace App\Commands;

use App\Libraries\StorySync;
use App\Models\AuditLogModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Memperbarui tabel `dialogues` server yang sudah berjalan dengan naskah
 * cerita terbaru (app/Database/Seeds/data/story.php, salinan
 * docs/naskah-cerita.md). Jalankan setelah `php spark migrate`.
 *
 * Seeder tidak pernah menimpa baris yang sudah ada, jadi server lama tetap
 * memegang teks seeder sebelumnya sampai perintah ini dijalankan. Baris yang
 * sudah disunting admin dilewati dan dilaporkan; `--force` menimpanya. Audio
 * dan latar yang sudah dipasang admin tidak pernah disentuh. Aturan lengkap:
 * App\Libraries\StorySync.
 */
class StoryUpdate extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:story:update';
    protected $description = 'Menyinkronkan narasi dan dialog (tabel dialogues) dengan naskah cerita terbaru.';
    protected $usage       = 'gelita:story:update [--force] [--dry-run]';
    protected $options     = [
        '--force'   => 'Timpa juga baris yang teksnya sudah disunting admin',
        '--dry-run' => 'Tampilkan yang akan berubah tanpa menulis ke database',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $force  = $this->flag('force', $params);
        $dryRun = $this->flag('dry-run', $params);

        try {
            $report = (new StorySync())->run($force, $dryRun);
        } catch (\Throwable $e) {
            CLI::error('Gagal memperbarui cerita, tidak ada yang diubah: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        if ($dryRun) {
            CLI::write('Uji coba (--dry-run): tidak ada yang ditulis.', 'yellow');
        }

        foreach ($report['missing_levels'] as $code) {
            CLI::write("Wilayah {$code} tidak ada di tabel levels: barisnya dilewati. Jalankan db:seed LevelSeeder dulu.", 'yellow');
        }

        if ($report['skipped'] !== []) {
            CLI::write('Dilewati karena sudah disunting admin (pakai --force untuk menimpa):', 'yellow');

            foreach ($report['skipped'] as $label) {
                CLI::write('  - ' . $label);
            }
        }

        if ($report['deactivated'] !== []) {
            CLI::write('Dinonaktifkan:');

            foreach ($report['deactivated'] as $label) {
                CLI::write('  - ' . $label);
            }
        }

        CLI::write(sprintf(
            'Cerita: %d disisipkan, %d diperbarui, %d dilewati, %d dinonaktifkan, %d sudah sesuai.',
            count($report['inserted']),
            count($report['updated']),
            count($report['skipped']),
            count($report['deactivated']),
            count($report['unchanged']),
        ), 'green');

        if (! $dryRun && ($report['inserted'] !== [] || $report['updated'] !== [] || $report['deactivated'] !== [])) {
            service('contentRepository')->flush();

            model(AuditLogModel::class)->record('content_update', [
                'target_type' => 'dialogues',
                'target_id'   => 'story',
                'metadata'    => [
                    'via'         => 'cli',
                    'command'     => $this->name,
                    'force'       => $force,
                    'inserted'    => count($report['inserted']),
                    'updated'     => count($report['updated']),
                    'skipped'     => count($report['skipped']),
                    'deactivated' => count($report['deactivated']),
                ],
            ]);
        }

        return EXIT_SUCCESS;
    }

    /** Opsi tanpa nilai: `--force` terbaca sebagai `$params['force'] = null`. */
    private function flag(string $name, array $params): bool
    {
        return array_key_exists($name, $params) || CLI::getOption($name) !== null;
    }
}
