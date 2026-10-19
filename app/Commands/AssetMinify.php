<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\AssetMinifier;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * `php spark gelita:aset:minify` — writes the minified mirror of the JS/CSS
 * code to public/assets/min/ (AssetMinifier). The source files stay as they
 * are. Run by the deploy scripts after every code update; until it runs,
 * browsers keep getting the original files, so a missed run is slow, never
 * broken. A new asset package picks up the mirror by itself.
 */
class AssetMinify extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:aset:minify';
    protected $description = 'Membuat salinan ringkas JS/CSS di public/assets/min/ untuk peramban; berkas sumber tidak diubah.';
    protected $usage       = 'gelita:aset:minify [--periksa] [--paksa]';
    protected $options     = [
        '--periksa' => 'Hanya periksa apakah salinan ringkas masih sesuai dengan sumbernya',
        '--paksa'   => 'Buat ulang walaupun salinan ringkas masih sesuai',
    ];

    public function run(array $params)
    {
        $minifier = service('assetMinifier');

        if ($this->flag('periksa', $params)) {
            if ($minifier->isFresh()) {
                CLI::write('Salinan ringkas sesuai dengan sumbernya.', 'green');

                return EXIT_SUCCESS;
            }

            CLI::write('Salinan ringkas belum ada atau sudah usang; peramban mendapat berkas asli. Jalankan gelita:aset:minify.', 'yellow');

            return EXIT_ERROR;
        }

        if (! $minifier->enabled()) {
            CLI::write('Peringkasan aset dimatikan (Config\Gelita::$minifyAssets); tidak ada yang dibuat.', 'yellow');

            return EXIT_SUCCESS;
        }

        try {
            $result = $minifier->refresh($this->flag('paksa', $params));
        } catch (Throwable $e) {
            CLI::error('Salinan ringkas gagal dibuat: ' . $e->getMessage());
            CLI::write('Salinan lama (jika ada) tidak diubah; bila sudah usang, peramban mendapat berkas asli.');

            return EXIT_ERROR;
        }

        CLI::write(sprintf(
            '%s (versi %s): %d berkas, %s menjadi %s.',
            $result['rebuilt'] ? 'Salinan ringkas dibuat' : 'Salinan ringkas sudah sesuai',
            $result['version'],
            $result['files'],
            PackageBuild::size($result['source_bytes']),
            PackageBuild::size($result['min_bytes']),
        ), 'green');

        return EXIT_SUCCESS;
    }
}
