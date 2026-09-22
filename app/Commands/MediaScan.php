<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Kerangka tahap 2. Sinkronisasi folder aset ↔ media_assets dipasang tahap 7.
 */
class MediaScan extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:media:scan';
    protected $description = 'Menyinkronkan folder public/assets dengan tabel media_assets.';
    protected $usage       = 'gelita:media:scan';

    public function run(array $params)
    {
        CLI::error($this->name . ' belum aktif — pemindai media dipasang pada tahap 7.');

        return EXIT_ERROR;
    }
}
