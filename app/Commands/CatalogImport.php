<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\CatalogImporter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Installs the images of the team's Katalog Aset (`gelita-katalog/`) into the
 * game: logo, buttons, app icons, backgrounds, story slides, maps, badges,
 * pins, lanterns, avatars, character frames, challenge images, and Pustaka
 * illustrations. Rules: App\Libraries\CatalogImporter.
 *
 * Run it after installation (`db:seed DatabaseSeeder`, the bank import), so
 * the slides, library pages, and challenge slots it links to exist. Images an
 * admin uploaded are kept unless `--timpa` is given, so running it again is
 * safe. Every installed image stays replaceable in the panel.
 */
class CatalogImport extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:katalog:impor';
    protected $description = 'Memasang semua gambar Katalog Aset (gelita-katalog/) ke slot gambar permainan.';
    protected $usage       = 'gelita:katalog:impor [--sumber=folder] [--dry-run] [--timpa]';
    protected $options     = [
        '--sumber'  => 'Folder Katalog Aset (bawaan: gelita-katalog di folder aplikasi)',
        '--dry-run' => 'Tampilkan yang akan dipasang tanpa mengubah apa pun',
        '--timpa'   => 'Ganti juga gambar yang sudah diunggah admin atau dipasang sebelumnya',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $root      = $this->option('sumber', $params) ?? ROOTPATH . 'gelita-katalog';
        $dryRun    = $this->flag('dry-run', $params);
        $overwrite = $this->flag('timpa', $params);

        if ($dryRun) {
            CLI::write('Uji coba (--dry-run): tidak ada yang diubah.', 'yellow');
        }

        try {
            $report = (new CatalogImporter($root))->run($dryRun, $overwrite);
        } catch (\Throwable $e) {
            CLI::error($e->getMessage());

            return EXIT_ERROR;
        }

        $rows = array_map(static fn (array $r): array => [$r['source'], $r['target'], CatalogImporter::STATUSES[$r['status']], $r['note']], $report);

        if ($rows !== []) {
            CLI::table($rows, ['Katalog', 'Slot', 'Hasil', 'Keterangan']);
        }

        $count = array_count_values(array_column($report, 'status')) + ['installed' => 0, 'planned' => 0, 'skipped' => 0, 'failed' => 0];

        CLI::write(sprintf(
            '%d dipasang, %d akan dipasang, %d dilewati, %d gagal.',
            $count['installed'],
            $count['planned'],
            $count['skipped'],
            $count['failed'],
        ), $count['failed'] > 0 ? 'yellow' : 'green');

        if (! $dryRun && $count['installed'] > 0 && service('assetPackages')->releasePackageCode() !== null) {
            CLI::write('Rilis aktif memakai paket aset: bangun paket baru (php spark gelita:paket:bangun) dan pasang di rilis agar siswa melihat gambar baru.', 'yellow');
        }

        return $count['failed'] > 0 ? EXIT_ERROR : EXIT_SUCCESS;
    }
}
