<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\MockupGenerator;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Gambar contoh (mockup) untuk slot gambar tantangan (Dok 2 §10).
 *
 * Tanpa opsi: hanya slot yang belum berisi gambar yang dibuatkan mockup.
 * `--paksa` membuat ulang mockup yang sudah ada (mis. sesudah label berubah);
 * gambar asli yang diunggah admin tidak pernah ditimpa. Juga dijalankan
 * `gelita:reset` (langkah 8).
 */
class Mockups extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:mockups';
    protected $description = 'Membuat gambar contoh (mockup) untuk slot gambar tantangan yang belum berisi gambar asli.';
    protected $usage       = 'gelita:mockups [--dry-run] [--paksa] [--node=tmg-4]';
    protected $options     = [
        '--dry-run' => 'Tampilkan rencana tanpa menulis apa pun.',
        '--paksa'   => 'Buat ulang mockup yang sudah ada. Gambar asli tetap tidak ditimpa.',
        '--node'    => 'Hanya satu tantangan, mis. tmg-4.',
    ];

    private const ACTIONS = [
        'create'   => 'dibuat',
        'refresh'  => 'dibuat ulang',
        'existing' => 'mockup sudah ada',
        'kept'     => 'gambar asli, dipertahankan',
        'failed'   => 'GAGAL',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $dryRun = $this->flag('dry-run', $params);
        $force  = $this->flag('paksa', $params);
        $node   = $this->option('node', $params);
        $node   = $node === null ? null : strtolower($node);

        if ($node !== null && preg_match('/^(tmg|mgl|wnb)-[1-4]$/', $node) !== 1) {
            CLI::error('Kode tantangan --node tidak dikenal: ' . $node . ' (contoh: tmg-4).');

            return EXIT_ERROR;
        }

        $result = (new MockupGenerator())->run($force, $dryRun, $node);

        if ($result['slots'] === []) {
            CLI::write('Tidak ada slot gambar tantangan' . ($node !== null ? ' untuk ' . $node : '') . '. Impor bank soal lebih dulu.', 'yellow');

            return EXIT_SUCCESS;
        }

        CLI::table(array_map(
            static fn (array $slot): array => [$slot['asset_key'], $slot['node'], $slot['kind'], self::ACTIONS[$slot['action']]],
            $result['slots'],
        ), ['Kunci', 'Tantangan', 'Jenis', $dryRun ? 'Rencana' : 'Hasil']);

        foreach ($result['failed'] as $failure) {
            CLI::write('! ' . $failure, 'red');
        }

        CLI::write(sprintf(
            '%s%d dibuat, %d dibuat ulang, %d mockup sudah ada, %d gambar asli dipertahankan%s.',
            $dryRun ? '--dry-run: tidak ada yang diubah. Rencana: ' : '',
            $result['created'],
            $result['refreshed'],
            $result['existing'],
            $result['kept'],
            $result['failed'] !== [] ? ', ' . count($result['failed']) . ' gagal' : '',
        ), $result['failed'] !== [] ? 'red' : 'green');

        return $result['failed'] === [] ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
