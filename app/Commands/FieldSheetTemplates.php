<?php

namespace App\Commands;

use App\Libraries\FieldSheetSpec;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Blank field sheets and the column spec (research revision 10, part 3:
 * 29 §10.1, §10.6, §13). Runs on the machine of P1 or the developer, never
 * on the production web server: L7, the only sheet with names, is never made
 * by the web panel.
 *
 *   php spark gelita:lembar:templat --keluar=lembar/       blank L1–L8 (L7 included), for printing
 *   php spark gelita:lembar:templat --l7 --keluar=lembar/  blank L7 only
 *   php spark gelita:lembar:templat --kolom=kolom.json     column spec as JSON for the static site
 *
 * L0 is a checklist without a table in 02, so it has no template here.
 */
class FieldSheetTemplates extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:lembar:templat';
    protected $description = 'Writes blank field-sheet templates (L7 included) or the column spec as JSON. Refuses to run in production.';
    protected $usage       = 'gelita:lembar:templat [--keluar=<folder>] [--l7] [--kolom=<file.json>]';
    protected $options     = [
        '--keluar' => 'Folder for the blank templates',
        '--l7'     => 'Only the blank L7 (parental consent)',
        '--kolom'  => 'Write the column spec (FieldSheetSpec) as JSON to this file',
    ];

    /** `--name value` or `--name=value` (29 §13 writes the second). */
    private function option(string $name, array $params): ?string
    {
        foreach (CLI::getOptions() + $params as $key => $value) {
            if ($key === $name && is_string($value) && $value !== '') {
                return $value;
            }

            if (is_string($key) && str_starts_with($key, $name . '=')) {
                return substr($key, strlen($name) + 1);
            }
        }

        return null;
    }

    public function run(array $params)
    {
        if (ENVIRONMENT === 'production') {
            CLI::error('gelita:lembar:templat does not run in production: L7 never comes from the web server.');

            return EXIT_ERROR;
        }

        $json   = $this->option('kolom', $params);
        $folder = $this->option('keluar', $params);
        $l7     = array_key_exists('l7', CLI::getOptions()) || array_key_exists('l7', $params);

        if (is_string($json) && $json !== '') {
            file_put_contents($json, json_encode(FieldSheetSpec::toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
            CLI::write('Column spec ' . FieldSheetSpec::VERSION . ' written to ' . $json, 'green');
        }

        if (is_string($folder) && $folder !== '') {
            if (! is_dir($folder) && ! mkdir($folder, 0775, true) && ! is_dir($folder)) {
                CLI::error('Folder cannot be made: ' . $folder);

                return EXIT_ERROR;
            }

            $sheets = $l7 ? ['l7_izin'] : FieldSheetSpec::sheets();
            $file   = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR . ($l7 ? 'lembar-l7-kosong.xlsx' : 'lembar-kosong.xlsx');

            (new \App\Services\FieldSheetService())->writeBlankTemplate($file, $sheets);
            CLI::write('Blank templates written to ' . $file . ' (' . implode(', ', $sheets) . ')', 'green');
        }

        if (! is_string($json) && ! is_string($folder)) {
            CLI::write('Usage: php spark ' . $this->usage, 'yellow');
        }

        return EXIT_SUCCESS;
    }
}
