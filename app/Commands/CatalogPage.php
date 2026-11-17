<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\CatalogSite;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Builds the public Katalog Aset site in gelita-katalog/ (owner,
 * 17 November 2026; App\Libraries\CatalogSite).
 *
 *   php spark gelita:katalog:halaman             # write the folder (only what changed)
 *   php spark gelita:katalog:halaman --dry-run   # list what would change, write nothing
 *   php spark gelita:katalog:halaman --cek       # exit 1 when the committed folder is stale
 *
 * A repository build step, run by a developer session after a picture, the
 * review list, a sound or a narration take changed; the result is committed.
 * A server only pulls it, so the step is not in gelita:setelah-pull.
 */
class CatalogPage extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:katalog:halaman';
    protected $description = 'Membangun halaman Katalog Aset (gelita-katalog/): semua gambar, musik, efek suara, dan narasi permainan.';
    protected $usage       = 'gelita:katalog:halaman [--dry-run] [--cek]';
    protected $options     = [
        '--dry-run' => 'Tampilkan yang akan berubah tanpa menulis apa pun',
        '--cek'     => 'Periksa apakah folder gelita-katalog/ masih sesuai sumbernya (keluar dengan kode 1 bila tidak)',
    ];

    public function run(array $params)
    {
        // Large masters (up to 2048 × 2048 and more) are decoded one at a time.
        ini_set('memory_limit', '1024M');

        $site = new CatalogSite();

        try {
            if ($this->flag('cek', $params)) {
                $problems = $site->check();

                if ($problems === []) {
                    CLI::write('Katalog Aset sesuai dengan sumbernya.', 'green');

                    return EXIT_SUCCESS;
                }

                CLI::write('Katalog Aset perlu dibangun ulang (php spark gelita:katalog:halaman):', 'yellow');

                foreach (array_slice($problems, 0, 30) as $problem) {
                    CLI::write('  ' . $problem);
                }

                if (count($problems) > 30) {
                    CLI::write('  … dan ' . (count($problems) - 30) . ' lagi.');
                }

                return EXIT_ERROR;
            }

            $dryRun = $this->flag('dry-run', $params);

            if ($dryRun) {
                CLI::write('Uji coba (--dry-run): tidak ada yang ditulis.', 'yellow');
            }

            $report = $site->build($dryRun);
        } catch (\Throwable $e) {
            CLI::error('Katalog Aset gagal dibangun: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        $model = $report['model'];
        CLI::write(sprintf(
            'Katalog Aset: %d gambar, %d musik, %d efek suara, %d baris narasi.',
            count($model['images']),
            count($model['music']),
            count($model['sfx']),
            count($model['narration']),
        ), 'light_cyan');

        $verb = $dryRun ? 'akan ' : '';
        CLI::write(sprintf(
            '  %d gambar %sdibuat ulang, %d berkas %sdisalin, %d berkas halaman %sditulis, %d berkas lama %sdihapus, %d tidak berubah.',
            count($report['encoded']),
            $verb,
            count($report['copied']),
            $verb,
            count($report['written']),
            $verb,
            count($report['removed']),
            $verb,
            $report['unchanged'],
        ));

        foreach ($report['failed'] as $source) {
            CLI::write('  Gagal dibaca: ' . $source, 'red');
        }

        if (! $dryRun) {
            [$bytes, $count] = $site->folderSize();
            CLI::write(sprintf('  Folder %s: %d berkas, %.1f MB.', CatalogSite::OUT_DIR, $count, $bytes / 1048576));
        }

        return $report['failed'] === [] ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
