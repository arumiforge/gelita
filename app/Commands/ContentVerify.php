<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
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
 * It also requires the asset package (docs/rencana-paket-aset.md §5.5, §7.2):
 * package mode must be on and `gelita:paket:periksa` must find no problem in
 * the active package (every file re-hashed here, never from a cache).
 */
class ContentVerify extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:content:verify';
    protected $description = 'Memeriksa 3 wilayah × 4 tantangan, variasi, bank soal kelas 5 (Dok 2 §7.5), kunci, dan media.';
    protected $usage       = 'gelita:content:verify [--strict] [--siap-penelitian]';
    protected $options     = [
        '--strict'          => 'Peringatan juga dianggap gagal (kode keluar 1).',
        '--siap-penelitian' => 'Gambar contoh (mockup) yang masih dipakai tantangan dianggap galat; paket aset wajib menyala dan utuh.',
    ];

    public function run(array $params)
    {
        // konten dibaca segar, bukan dari cache yang mungkin lebih tua dari database
        service('contentRepository')->flush();

        $ready    = $this->flag('siap-penelitian', $params);
        $findings = (new ContentVerifier())->run($ready);

        if ($ready) {
            $findings = array_merge($findings, $this->packageFindings());
        }
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

    /**
     * Research-ready package check: package mode on, active package intact.
     *
     * @return list<array{level: string, scope: string, message: string}>
     */
    private function packageFindings(): array
    {
        $packages = service('assetPackages');
        $code     = $packages->activeCode();

        if ($code === null) {
            return [[
                'level'   => 'error',
                'scope'   => 'paket aset',
                'message' => $packages->enabled()
                    ? 'Mode paket mati: belum ada paket aset yang terbit (Rilis → Paket aset, atau gelita:paket:bangun lalu rilis dengan paket itu).'
                    : 'Mode paket dimatikan dari server (gelita.paket.aktif = false).',
            ]];
        }

        // Fresh re-hash; it also refreshes the cached result the panel checklist shows
        $issues = $packages->verification($code, true)['issues'];

        return array_map(static fn (array $issue): array => [
            'level'   => 'error',
            'scope'   => 'paket ' . $code,
            'message' => $issue['key'] . ': ' . $issue['issue'],
        ], $issues);
    }
}
