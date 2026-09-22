<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Kerangka tahap 2. Isi pemeriksaan dibuat tahap 7.
 */
class ContentVerify extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:content:verify';
    protected $description = 'Memeriksa 3 level × 5 node, engine_type valid, dan item bank cukup.';
    protected $usage       = 'gelita:content:verify';

    public function run(array $params)
    {
        CLI::error($this->name . ' belum aktif — logika pemeriksaan dipasang pada tahap 7.');

        return EXIT_ERROR;
    }
}
