<?php

namespace App\Commands;

use App\Libraries\ContentVerifier;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Pemeriksaan konten sebelum rilis — aturan yang sama dengan panel
 * `/admin/konten/verifikasi`. Kode keluar galat bila ada temuan `error`
 * (atau `warning` dengan --strict), agar dapat dipakai di skrip deploy.
 */
class ContentVerify extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:content:verify';
    protected $description = 'Memeriksa 3 level × 5 node, engine_type valid, bank item cukup, kunci lengkap, dan media.';
    protected $usage       = 'gelita:content:verify [--strict]';
    protected $options     = [
        '--strict' => 'Peringatan juga dianggap gagal (kode keluar 1).',
    ];

    public function run(array $params)
    {
        // konten dibaca segar, bukan dari cache yang mungkin lebih tua dari database
        service('contentRepository')->flush();

        $findings = (new ContentVerifier())->run();
        $errors   = ContentVerifier::errorCount($findings);
        $warnings = count($findings) - $errors;

        if ($findings === []) {
            CLI::write('Konten lengkap: 3 wilayah × 5 tantangan, tanpa temuan.', 'green');

            return EXIT_SUCCESS;
        }

        CLI::table(array_map(
            static fn (array $f): array => [$f['level'] === 'error' ? 'GALAT' : 'peringatan', $f['scope'], $f['message']],
            $findings,
        ), ['Tingkat', 'Cakupan', 'Temuan']);

        CLI::write(sprintf('%d galat, %d peringatan.', $errors, $warnings), $errors > 0 ? 'red' : 'yellow');

        $strict = array_key_exists('strict', $params) || CLI::getOption('strict') !== null;

        return $errors > 0 || ($strict && $warnings > 0) ? EXIT_ERROR : EXIT_SUCCESS;
    }
}
