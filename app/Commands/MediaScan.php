<?php

namespace App\Commands;

use App\Libraries\MediaIntegrity;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Menyinkronkan folder public/assets dengan tabel media_assets.
 *
 * 1. MediaAssetSeeder (idempoten): slot resmi + berkas di folder aset bawaan
 *    didaftarkan, metadata diperbarui, berkas yang hilang → is_active = 0.
 * 2. MediaIntegrity: aset aktif yang berkasnya hilang/berubah dan berkas
 *    unggahan tanpa baris dilaporkan (tanpa mengubah apa pun).
 */
class MediaScan extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:media:scan';
    protected $description = 'Menyinkronkan folder public/assets dengan tabel media_assets.';
    protected $usage       = 'gelita:media:scan [--check-only]';
    protected $options     = [
        '--check-only' => 'Hanya memeriksa integritas, tanpa mendaftarkan/menonaktifkan aset.',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $checkOnly = array_key_exists('check-only', $params) || CLI::getOption('check-only') !== null;

        if (! $checkOnly) {
            \Config\Database::seeder()->call('App\Database\Seeds\MediaAssetSeeder');
            service('contentRepository')->flush();
        }

        $findings = (new MediaIntegrity())->check();

        model(\App\Models\AuditLogModel::class)->record('media_scan', [
            'metadata' => ['findings' => count($findings), 'via' => 'cli', 'synced' => ! $checkOnly],
        ]);

        if ($findings === []) {
            CLI::write('Semua aset aktif cocok dengan berkasnya.', 'green');

            return EXIT_SUCCESS;
        }

        CLI::table(
            array_map(static fn (array $f): array => [$f['asset_key'], $f['issue']], $findings),
            ['Aset', 'Masalah'],
        );
        CLI::write(count($findings) . ' temuan. Unggah ulang lewat /admin/media atau periksa berkasnya.', 'yellow');

        return EXIT_ERROR;
    }
}
