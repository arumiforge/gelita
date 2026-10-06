<?php

namespace App\Commands;

use App\Libraries\ContentVerifier;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Pemeriksaan konten sebelum rilis — aturan yang sama dengan panel
 * `/admin/konten/verifikasi`. Kode keluar galat bila ada temuan `error`
 * (atau `warning` dengan --strict), agar dapat dipakai di skrip deploy.
 *
 * `--siap-penelitian`: gambar contoh (mockup) yang masih dipakai tantangan
 * menjadi galat (Dok 2 §7.5, §10.3) — syarat sebelum pengambilan data.
 */
class ContentVerify extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:content:verify';
    protected $description = 'Memeriksa 3 wilayah × 4 tantangan, variasi, bank soal kelas 5 (Dok 2 §7.5), kunci, dan media.';
    protected $usage       = 'gelita:content:verify [--strict] [--siap-penelitian]';
    protected $options     = [
        '--strict'          => 'Peringatan juga dianggap gagal (kode keluar 1).',
        '--siap-penelitian' => 'Gambar contoh (mockup) yang masih dipakai tantangan dianggap galat.',
    ];

    public function run(array $params)
    {
        // konten dibaca segar, bukan dari cache yang mungkin lebih tua dari database
        service('contentRepository')->flush();

        $ready    = $this->flag('siap-penelitian', $params);
        $findings = (new ContentVerifier())->run($ready);
        $errors   = ContentVerifier::errorCount($findings);
        $warnings = count($findings) - $errors;

        if ($findings === []) {
            CLI::write('Konten lengkap: 3 wilayah × 4 tantangan, tanpa temuan' . ($ready ? '; siap untuk penelitian.' : '.'), 'green');

            return EXIT_SUCCESS;
        }

        CLI::table(array_map(
            static fn (array $f): array => [$f['level'] === 'error' ? 'GALAT' : 'peringatan', $f['scope'], $f['message']],
            $findings,
        ), ['Tingkat', 'Cakupan', 'Temuan']);

        CLI::write(sprintf('%d galat, %d peringatan.', $errors, $warnings), $errors > 0 ? 'red' : 'yellow');

        if ($ready && $errors > 0) {
            CLI::write('Belum siap untuk pengambilan data penelitian.', 'red');
        }

        $strict = $this->flag('strict', $params);

        return $errors > 0 || ($strict && $warnings > 0) ? EXIT_ERROR : EXIT_SUCCESS;
    }

    /** Opsi tanpa nilai: `--strict` terbaca sebagai `$params['strict'] = null`. */
    private function flag(string $name, array $params): bool
    {
        return array_key_exists($name, $params) || CLI::getOption($name) !== null;
    }
}
