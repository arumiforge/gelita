<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Kerangka tahap 2. RetentionService dipasang tahap 7; dijalankan cron harian (tahap 8).
 */
class RetentionRun extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:retention:run';
    protected $description = 'Menjalankan retention data & menghapus export kedaluwarsa.';
    protected $usage       = 'gelita:retention:run';

    public function run(array $params)
    {
        CLI::error($this->name . ' belum aktif — RetentionService dipasang pada tahap 7.');

        return EXIT_ERROR;
    }
}
