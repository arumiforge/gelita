<?php

namespace App\Commands;

use App\Libraries\SchoolImporter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Memasang daftar sekolah resmi (NPSN) ke tabel `schools` — lihat
 * docs/sekolah/README.md. Aman dijalankan berulang (deploy menjalankannya
 * setelah migrasi): hanya baris yang berubah yang ditulis, dan tidak ada
 * sekolah yang dihapus. Aturan lengkap: App\Libraries\SchoolImporter.
 *
 * `--link-existing` sekaligus menggabungkan nama sekolah ketikan siswa yang
 * cocok jelas dengan sekolah resmi (sama seperti tombol di Panel → Sekolah).
 */
class SchoolImport extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:schools:import';
    protected $description = 'Memasang daftar sekolah resmi (NPSN) dari app/Database/Seeds/data/sekolah-jateng.csv.';
    protected $usage       = 'gelita:schools:import [--file=PATH] [--dry-run] [--link-existing]';
    protected $options     = [
        '--file'          => 'Berkas CSV lain (bawaan: app/Database/Seeds/data/sekolah-jateng.csv)',
        '--dry-run'       => 'Tampilkan perubahan tanpa menulis apa pun',
        '--link-existing' => 'Gabungkan nama sekolah ketikan siswa yang cocok jelas dengan sekolah resmi',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $file   = $this->option('file', $params) ?? SchoolImporter::DEFAULT_FILE;
        $dryRun = $this->flag('dry-run', $params);
        $link   = $this->flag('link-existing', $params);

        if ($dryRun) {
            CLI::write('Uji coba (--dry-run): tidak ada yang ditulis.', 'yellow');
        }

        try {
            $report = (new SchoolImporter())->import($file, $dryRun);
        } catch (\Throwable $e) {
            CLI::error('Impor sekolah gagal: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        CLI::write(sprintf(
            'Sekolah resmi: %d di berkas — %d baru, %d diperbarui, %d tetap.',
            $report['total'],
            $report['added'],
            $report['updated'],
            $report['unchanged'],
        ), 'green');

        if ($report['rekeyed'] > 0) {
            CLI::write("  Kunci nama {$report['rekeyed']} sekolah ketikan siswa disegarkan.");
        }

        if ($report['missing'] !== []) {
            CLI::write(sprintf(
                '  %d NPSN resmi di database tidak ada lagi di berkas (dibiarkan): %s%s',
                count($report['missing']),
                implode(', ', array_slice($report['missing'], 0, 20)),
                count($report['missing']) > 20 ? ', …' : '',
            ), 'yellow');
        }

        if ($link) {
            $pairs = service('schoolDirectory')->autoMerge($dryRun);

            CLI::write(sprintf(
                '%s %d nama ketikan siswa ke sekolah resmi.',
                $dryRun ? 'Akan menggabungkan' : 'Menggabungkan',
                count($pairs),
            ), 'green');

            foreach ($pairs as $pair) {
                CLI::write(sprintf('  "%s" → %s (NPSN %s)', $pair['from']['name'], $pair['to']['name'], $pair['to']['code']), 'dark_gray');
            }
        }

        return EXIT_SUCCESS;
    }

    private function flag(string $name, array $params): bool
    {
        return array_key_exists($name, $params) || CLI::getOption($name) !== null;
    }

    /**
     * Nilai opsi dalam dua bentuk: `--file x` dan `--file=x` (bentuk kedua
     * terbaca parser CodeIgniter sebagai opsi bernama `file=x`).
     */
    private function option(string $name, array $params): ?string
    {
        $value = $params[$name] ?? CLI::getOption($name);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        foreach (array_merge(array_keys($params), array_keys(CLI::getOptions())) as $key) {
            if (is_string($key) && str_starts_with($key, $name . '=')) {
                return substr($key, strlen($name) + 1);
            }
        }

        return null;
    }
}
