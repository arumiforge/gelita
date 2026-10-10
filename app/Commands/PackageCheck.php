<?php

namespace App\Commands;

use App\Models\AssetPackageModel;
use App\Services\AssetPackageMaintenance;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * `php spark gelita:paket:periksa [kode]` — re-hash every file of a package
 * against its manifest (docs/rencana-paket-aset.md §5.5). Without a code it
 * checks the package of the active release, or else the latest built one.
 * Exit code 1 on any problem, so scripts can rely on it.
 */
class PackageCheck extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:paket:periksa';
    protected $description = 'Memeriksa ulang isi berkas paket aset terhadap manifestnya.';
    protected $usage       = 'gelita:paket:periksa [kode-paket]';
    protected $arguments   = [
        'kode-paket' => 'Kode paket (bawaan: paket rilis aktif, atau paket terakhir yang dibangun)',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $code = trim((string) ($params[0] ?? ''));

        if ($code === '') {
            $code = service('assetPackages')->releasePackageCode()
                ?? (string) (model(AssetPackageModel::class)->latestBuilt()['package_code'] ?? '');
        }

        if ($code === '') {
            CLI::error('Belum ada paket aset. Bangun dulu dengan `php spark gelita:paket:bangun`.');

            return EXIT_ERROR;
        }

        $issues = (new AssetPackageMaintenance())->verify($code);

        if ($issues === []) {
            $row = model(AssetPackageModel::class)->findByCode($code);
            CLI::write(sprintf('Paket %s utuh: %d berkas cocok dengan manifest.', $code, (int) ($row['file_count'] ?? 0)), 'green');

            return EXIT_SUCCESS;
        }

        CLI::table(array_map(static fn (array $i): array => [$i['key'], $i['issue']], $issues), ['Berkas', 'Masalah']);
        CLI::error(sprintf('Paket %s bermasalah (%d temuan). Bangun ulang paketnya dengan `php spark gelita:paket:bangun`.', $code, count($issues)));

        return EXIT_ERROR;
    }
}
