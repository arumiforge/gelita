<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\SoundInstaller;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Installs music and sound effects made with ElevenLabs: copies the takes of
 * FOLDER/out/{music|sfx}/ over public/assets/audio/{music|sfx}/ and lists each
 * one in public/assets/audio/produksi.json with its sha256 and the model from
 * FOLDER/settings.md. Rules: App\Libraries\SoundInstaller.
 */
class SoundInstall extends BaseCommand
{
    use CliOptions;

    /** The ElevenLabs music and sound-effects workspace, relative to the repository root. */
    public const DEFAULT_DIR = 'ai-studio/elevenlabs-sound';

    protected $group       = 'GELITA';
    protected $name        = 'gelita:sound:install';
    protected $description = 'Memasang musik dan efek suara buatan ElevenLabs ke folder audio permainan.';
    protected $usage       = 'gelita:sound:install [--dir=FOLDER] [--dry-run]';
    protected $options     = [
        '--dir'     => 'Folder kerja berisi settings.md dan out/{music,sfx}/ (bawaan: ' . self::DEFAULT_DIR . ')',
        '--dry-run' => 'Tampilkan yang akan dipasang tanpa menulis apa pun',
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
            $report = (new SoundInstaller())->install($dir, $dryRun);
        } catch (\Throwable $e) {
            CLI::error('Pemasangan musik dan efek suara gagal: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        CLI::write('Musik dan efek suara dari ' . $dir, 'light_cyan');

        foreach ($report['unknown'] as $row) {
            CLI::write('  Nama tidak dikenal: ' . $row['file'] . ($row['suggestion'] !== null ? ' → maksudnya ' . $row['suggestion'] . '.mp3?' : ''), 'yellow');
        }

        foreach ($report['errors'] as $error) {
            CLI::write('  ' . $error, 'red');
        }

        if ($report['files'] === 0) {
            CLI::write('  Belum ada berkas di out/music/ atau out/sfx/.');
        }

        CLI::write(sprintf(
            '  %d %s, %d mengganti berkas ElevenLabs sebelumnya, %d tidak berubah, %d nama tidak dikenal.',
            count($report['installed']),
            $dryRun ? 'akan dipasang' : 'dipasang',
            count($report['replaced']),
            count($report['unchanged']),
            count($report['unknown']),
        ), $report['errors'] === [] ? 'green' : 'red');

        if (! $dryRun && $report['errors'] === [] && ($report['installed'] !== [] || $report['replaced'] !== [])) {
            CLI::write('  Bila mode paket dipakai, bangun paket baru (php spark gelita:paket:bangun) supaya perangkat memuat berkas baru.');
        }

        return $report['errors'] !== [] || $report['unknown'] !== [] ? EXIT_ERROR : EXIT_SUCCESS;
    }
}
