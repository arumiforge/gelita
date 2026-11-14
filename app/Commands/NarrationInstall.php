<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\NarrationImporter;
use App\Libraries\SyntheticNarrationInstaller;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Installs the backup narration made with ElevenLabs: copies the takes of
 * FOLDER/out/{id|en}/ into public/assets/audio/narasi/{id|en}/ and lists each
 * one in produksi.json with its sha256 and voice profile (from
 * FOLDER/voices.md), so the import stores them as synthetic voices. A line
 * that already has a real recording keeps it unless --timpa is given. Run
 * `gelita:narration:import` afterwards. Rules: App\Libraries\SyntheticNarrationInstaller.
 */
class NarrationInstall extends BaseCommand
{
    use CliOptions;

    /** The ElevenLabs workspace, relative to the repository root. */
    public const DEFAULT_DIR = 'ai-studio/elevenlabs-voice';

    protected $group       = 'GELITA';
    protected $name        = 'gelita:narration:install';
    protected $description = 'Memasang rekaman narasi cadangan (ElevenLabs) ke folder narasi dan mencatatnya sebagai suara sintetis.';
    protected $usage       = 'gelita:narration:install [--dir=FOLDER] [--dry-run] [--timpa]';
    protected $options     = [
        '--dir'     => 'Folder kerja ElevenLabs berisi voices.md dan out/{id,en}/ (bawaan: ' . self::DEFAULT_DIR . ')',
        '--dry-run' => 'Tampilkan yang akan dipasang tanpa menulis apa pun',
        '--timpa'   => 'Ganti juga rekaman suara asli yang sudah ada (bawaan: rekaman suara asli dipertahankan)',
    ];

    public function run(array $params)
    {
        $dir    = $this->option('dir', $params) ?? self::DEFAULT_DIR;
        $dryRun = $this->flag('dry-run', $params);

        // spark runs from public/, so a relative path is taken from the repository root.
        if (preg_match('#^([A-Za-z]:)?[/\\\\]#', $dir) !== 1) {
            $dir = ROOTPATH . $dir;
        }

        if ($dryRun) {
            CLI::write('Uji coba (--dry-run): tidak ada yang ditulis.', 'yellow');
        }

        try {
            $report = (new SyntheticNarrationInstaller(new NarrationImporter()))->install($dir, $dryRun, $this->flag('timpa', $params));
        } catch (\Throwable $e) {
            CLI::error('Pemasangan rekaman gagal: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        CLI::write('Rekaman cadangan dari ' . $dir, 'light_cyan');

        foreach ($report['unknown'] as $row) {
            CLI::write('  Nama tidak dikenal: ' . $row['file'] . ' (' . $row['reason'] . ')'
                . ($row['suggestion'] !== null ? ' → maksudnya ' . $row['suggestion'] . '?' : ''), 'yellow');
        }

        foreach ($report['kept'] as $row) {
            CLI::write('  Dilewati: ' . $row['file'] . ' (' . $row['reason'] . ')', 'yellow');
        }

        foreach ($report['errors'] as $error) {
            CLI::write('  ' . $error, 'red');
        }

        if ($report['files'] === 0 && $report['errors'] === []) {
            CLI::write('  Belum ada rekaman di out/.');
        }

        $verb = $dryRun ? 'akan dipasang' : 'dipasang';

        CLI::write(sprintf(
            '  %d %s, %d mengganti rekaman lama, %d tidak berubah, %d dilewati, %d nama tidak dikenal.',
            count($report['installed']),
            $verb,
            count($report['replaced']),
            count($report['unchanged']),
            count($report['kept']),
            count($report['unknown']),
        ), $report['errors'] === [] ? 'green' : 'red');

        if (! $dryRun && $report['errors'] === [] && ($report['installed'] !== [] || $report['replaced'] !== [] || $report['written'])) {
            CLI::write('  Langkah berikutnya: php spark gelita:narration:import, lalu dengarkan dan setujui di Rekaman narasi.');
        }

        return $report['errors'] !== [] || $report['unknown'] !== [] ? EXIT_ERROR : EXIT_SUCCESS;
    }
}
