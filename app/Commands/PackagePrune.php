<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Services\AssetPackageMaintenance;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * `php spark gelita:paket:bersihkan` — delete package files that no kept
 * package refers to (docs/rencana-paket-aset.md §5.2), and builds abandoned
 * for more than 24 hours. `--hapus kode` deletes a whole package first,
 * refused while any release names it. `--coba` only reports.
 */
class PackagePrune extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:paket:bersihkan';
    protected $description = 'Menghapus berkas paket aset yang tidak dipakai paket mana pun.';
    protected $usage       = 'gelita:paket:bersihkan [--hapus kode-paket[,kode-lain]] [--coba]';
    protected $options     = [
        '--hapus' => 'Hapus paket ini (dipisah koma); ditolak bila masih dipakai rilis',
        '--coba'  => 'Hanya tampilkan apa yang akan dihapus',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $remove  = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('hapus', $params)))));
        $dryRun  = $this->flag('coba', $params);
        $summary = (new AssetPackageMaintenance())->prune($remove, $dryRun);
        $verb    = $dryRun ? 'akan dihapus' : 'dihapus';

        foreach ($summary['refused'] as $code) {
            CLI::write("Paket {$code} tidak dihapus: tidak terdaftar atau masih dipakai rilis.", 'yellow');
        }

        foreach ($summary['removed_packages'] as $code) {
            CLI::write("Paket {$code} {$verb}.");
        }

        foreach ($summary['abandoned'] as $code) {
            CLI::write("Pembangunan paket {$code} yang terhenti {$verb}.");
        }

        CLI::write(sprintf(
            '%d berkas aset dan %d folder kode %s (%s).',
            $summary['media_files'],
            count($summary['code_dirs']),
            $verb,
            PackageBuild::size($summary['bytes']),
        ), 'green');

        return $summary['refused'] === [] ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
