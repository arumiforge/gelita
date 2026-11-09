<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\InstrumentImporter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Imports the test, questionnaire, and attitude-scale data files (research
 * revision 10, part 8: 34 §6.2). `gelita:reset` runs the same import after
 * the question bank.
 */
class InstrumentImport extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:instrumen:impor';
    protected $description = 'Memuat isi tes, angket, dan skala sikap dari data/instrumen ke database (berversi).';
    protected $usage       = 'gelita:instrumen:impor [--berkas tes|angket|sikap] [--dry-run]';
    protected $options     = [
        '--berkas'  => 'Hanya satu berkas: tes, angket, atau sikap (bawaan: ketiganya).',
        '--dry-run' => 'Hanya memeriksa isi, tanpa menulis database.',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $only = $this->option('berkas', $params);

        if ($only !== null && ! in_array($only, InstrumentImporter::INSTRUMENTS, true)) {
            CLI::error('Pilihan --berkas: tes, angket, atau sikap.');

            return EXIT_ERROR;
        }

        $result = (new InstrumentImporter())->import($only === null ? null : [$only], $this->flag('dry-run', $params));

        foreach ($result['files'] as $name => $file) {
            $label = [
                'imported'  => 'diimpor',
                'unchanged' => 'tidak berubah',
                'checked'   => 'lolos pemeriksaan (dry run)',
                'refused'   => 'ditolak',
                'missing'   => 'tidak ditemukan',
            ][$file['status']] ?? $file['status'];
            CLI::write(sprintf('%-7s %-12s %s (%d butir)', $name, (string) $file['version'], $label, $file['items']));
        }

        foreach ($result['errors'] as $error) {
            CLI::error($error);
        }

        return $result['errors'] === [] ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
