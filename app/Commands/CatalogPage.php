<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\CatalogSite;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Builds the public Katalog Aset page in gelita-katalog/ (owner,
 * 17 November 2026; App\Libraries\CatalogSite).
 *
 *   php spark gelita:katalog:halaman                  # write the page and its list (only what changed)
 *   php spark gelita:katalog:halaman --dry-run        # list what would change, write nothing
 *   php spark gelita:katalog:halaman --cek            # exit 1 when the committed folder is stale
 *   php spark gelita:katalog:halaman --cek-tautan     # ask the live game which linked files it lacks
 *   php spark gelita:katalog:halaman --basis=URL      # link to another install instead of gelita.id
 *
 * A repository build step, run by a developer session after a picture, the
 * review list, a sound or a narration line changed; the result is committed.
 * A server only pulls it, so the step is not in gelita:setelah-pull.
 */
class CatalogPage extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:katalog:halaman';
    protected $description = 'Membangun halaman Katalog Aset (gelita-katalog/): semua gambar, musik, efek suara, dan narasi permainan, ditautkan ke gelita.id.';
    protected $usage       = 'gelita:katalog:halaman [--dry-run] [--cek] [--cek-tautan] [--basis=URL]';
    protected $options     = [
        '--dry-run'    => 'Tampilkan yang akan berubah tanpa menulis apa pun',
        '--cek'        => 'Periksa apakah folder gelita-katalog/ masih sesuai sumbernya (keluar dengan kode 1 bila tidak)',
        '--cek-tautan' => 'Periksa setiap tautan gambar dan suara ke server (HEAD) dan daftar yang belum ada',
        '--basis'      => 'Alamat permainan yang ditautkan (bawaan: ' . CatalogSite::BASE_URL . ')',
    ];

    public function run(array $params)
    {
        $base = $this->option('basis', $params);

        if ($base !== null && preg_match('#^https?://[^\s/]+#', $base) !== 1) {
            CLI::error('--basis harus berupa alamat lengkap, misalnya https://gelita.id');

            return EXIT_ERROR;
        }

        $site = new CatalogSite(null, null, $base);

        try {
            if ($this->flag('cek-tautan', $params)) {
                return $this->checkLinks($site);
            }

            if ($this->flag('cek', $params)) {
                $problems = $site->check();

                if ($problems === []) {
                    CLI::write('Katalog Aset sesuai dengan sumbernya.', 'green');

                    return EXIT_SUCCESS;
                }

                CLI::write('Katalog Aset perlu dibangun ulang (php spark gelita:katalog:halaman):', 'yellow');

                foreach ($problems as $problem) {
                    CLI::write('  ' . $problem);
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
        $linked = count(array_filter($model['images'], static fn (array $i): bool => $i['p'] !== null));
        CLI::write(sprintf(
            'Katalog Aset: %d gambar (%d ditautkan ke %s), %d musik, %d efek suara, %d baris narasi.',
            count($model['images']),
            $linked,
            $site->baseUrl(),
            count($model['music']),
            count($model['sfx']),
            count($model['narration']),
        ), 'light_cyan');

        $verb = $dryRun ? 'akan ' : '';
        CLI::write(sprintf('  %d berkas %sditulis, %d %sdihapus, %d tidak berubah.', count($report['written']), $verb, count($report['removed']), $verb, $report['unchanged']));

        foreach ($report['removed'] as $removed) {
            CLI::write('  ' . ($dryRun ? 'Akan dihapus: ' : 'Dihapus: ') . $removed);
        }

        if (! $dryRun) {
            [$bytes, $count] = $site->folderSize();
            CLI::write(sprintf('  Folder %s: %d berkas, %.0f KB.', CatalogSite::OUT_DIR, $count, $bytes / 1024));
        }

        return EXIT_SUCCESS;
    }

    /** HEAD every linked file on the server and list the missing ones (developer output). */
    private function checkLinks(CatalogSite $site): int
    {
        $urls    = $site->urls();
        $missing = [];
        $handles = [];
        $multi   = curl_multi_init();

        CLI::write('Checking ' . count($urls) . ' links on ' . $site->baseUrl() . ' …', 'light_cyan');

        foreach (array_chunk($urls, 16) as $chunk) {
            foreach ($chunk as $url) {
                $handle = curl_init($url);
                curl_setopt_array($handle, [CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 20]);
                curl_multi_add_handle($multi, $handle);
                $handles[$url] = $handle;
            }

            do {
                curl_multi_exec($multi, $running);
                curl_multi_select($multi);
            } while ($running > 0);

            foreach ($handles as $url => $handle) {
                $code = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

                if ($code !== 200) {
                    $missing[] = $code . ' ' . $url;
                }

                curl_multi_remove_handle($multi, $handle);
                curl_close($handle);
            }

            $handles = [];
        }

        curl_multi_close($multi);

        foreach ($missing as $line) {
            CLI::write('  ' . $line, 'yellow');
        }

        CLI::write(sprintf('%d of %d links answer 200; %d missing (the page shows them as "belum ada di gelita.id").', count($urls) - count($missing), count($urls), count($missing)), $missing === [] ? 'green' : 'yellow');

        return EXIT_SUCCESS;
    }
}
