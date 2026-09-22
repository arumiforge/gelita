<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Kerangka tahap 2. Pembacaan workbook lewat ContentImportService dipasang tahap 3 & 7.
 */
class BankImport extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:bank:import';
    protected $description = 'Memuat workbook bank soal (XLSX) ke tabel konten dalam satu transaction.';
    protected $usage       = 'gelita:bank:import <file> [--dry-run]';
    protected $arguments   = [
        'file' => 'Path workbook .xlsx, mis. writable/uploads/bank-soal.xlsx',
    ];
    protected $options = [
        '--dry-run' => 'Pratinjau ringkasan & galat tanpa menulis database.',
    ];

    public function run(array $params)
    {
        $file = $params[0] ?? null;

        if ($file === null || ! is_file($file)) {
            CLI::error('Berkas workbook tidak ditemukan. Pemakaian: php spark ' . $this->usage);

            return EXIT_ERROR;
        }

        CLI::error($this->name . ' belum aktif — ContentImportService dipasang pada tahap 7.');

        return EXIT_ERROR;
    }
}
