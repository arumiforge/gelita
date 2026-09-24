<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Isi poster video Pustaka (YouTube/Vimeo/Drive) yang masih kosong dengan
 * thumbnail yang diunduh server — untuk baris yang sudah ada sebelum fitur
 * ini, atau yang gagal saat disimpan karena server sedang tanpa internet.
 *
 * Penyimpanan admin dan impor workbook sudah memanggil VideoThumbnail
 * sendiri; perintah ini hanya menyusul. `--force` mengunduh ulang juga untuk
 * baris yang sudah berposter (mis. setelah video diganti).
 */
class LibraryThumbnails extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:library:thumbnails';
    protected $description = 'Mengunduh thumbnail video luar Pustaka dan memasangnya sebagai poster.';
    protected $usage       = 'gelita:library:thumbnails [--force]';
    protected $options     = [
        '--force' => 'Unduh ulang juga untuk video yang sudah punya poster',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $force   = array_key_exists('force', $params) || CLI::getOption('force') !== null;
        $summary = service('videoThumbnail')->fillMissing(null, $force);

        CLI::write(sprintf(
            'Poster video Pustaka: %d terisi, %d dilewati, %d gagal.',
            $summary['filled'],
            $summary['skipped'],
            $summary['failed'],
        ), $summary['failed'] > 0 ? 'yellow' : 'green');

        foreach ($summary['errors'] as $error) {
            CLI::write('  - ' . $error, 'yellow');
        }

        if ($summary['filled'] + $summary['skipped'] + $summary['failed'] === 0) {
            CLI::write($force ? 'Tidak ada video luar di Pustaka.' : 'Semua video luar sudah berposter. Pakai --force untuk mengunduh ulang.');
        }

        if ($summary['failed'] > 0) {
            CLI::write('Server butuh HTTPS keluar ke i.ytimg.com, vimeo.com, i.vimeocdn.com, dan drive.google.com (docs/08).');
        }

        return $summary['failed'] > 0 ? EXIT_ERROR : EXIT_SUCCESS;
    }
}
