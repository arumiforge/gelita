<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Kerangka tahap 2. Perhitungan ulang lewat ScoringService dipasang tahap 7.
 */
class ScoreRecompute extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:score:recompute';
    protected $description = 'Menghitung ulang skor attempt dengan scoring profile tertentu.';
    protected $usage       = 'gelita:score:recompute --profile <code>';
    protected $options     = [
        '--profile' => 'Kode scoring profile, mis. GELITA_V2.',
    ];

    public function run(array $params)
    {
        CLI::error($this->name . ' belum aktif — ScoringService dipasang pada tahap 7.');

        return EXIT_ERROR;
    }
}
