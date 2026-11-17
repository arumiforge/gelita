<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\DevStepLedger;
use App\Libraries\PostPullCheck;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * After `git pull`: which spark commands this install needs now (owner,
 * 17 November 2026; PostPullCheck).
 *
 *   php spark gelita:setelah-pull              # report only, changes nothing
 *   php spark gelita:setelah-pull --jalankan   # run the due steps in order, after one confirmation
 *   php spark gelita:setelah-pull --tandai     # this install is current: record the import sources as done
 *
 * For developer and test installs. A production server is updated with
 * deploy/linux/deploy.sh or deploy/windows/deploy.ps1, so --jalankan refuses
 * there. Composer is never run from here (vendor/ is in use by this
 * process), and the validator guide needs a person to read it.
 */
class PostPull extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:setelah-pull';
    protected $description = 'Setelah git pull: menampilkan (dan bila diminta menjalankan) perintah yang perlu dijalankan di instalasi ini.';
    protected $usage       = 'gelita:setelah-pull [--jalankan [--ya]] | --tandai';
    protected $options     = [
        '--jalankan' => 'Jalankan langkah yang perlu, berurutan, setelah satu konfirmasi.',
        '--ya'       => 'Bersama --jalankan: tanpa pertanyaan konfirmasi.',
        '--tandai'   => 'Catat sumber impor yang sekarang sebagai sudah dijalankan, tanpa menjalankan apa pun.',
    ];

    private const COLORS = [
        PostPullCheck::DUE     => 'yellow',
        PostPullCheck::OK      => 'green',
        PostPullCheck::UNKNOWN => 'light_gray',
        PostPullCheck::SKIPPED => 'dark_gray',
        PostPullCheck::MANUAL  => 'yellow',
    ];

    public function run(array $params)
    {
        $check = PostPullCheck::forInstall();

        if ($this->flag('tandai', $params)) {
            $check->markAll();
            CLI::write('Sumber semua langkah impor dicatat sebagai sudah dijalankan (writable/' . DevStepLedger::FILE . ').', 'green');

            return EXIT_SUCCESS;
        }

        $steps = $check->steps();
        $this->report($steps);

        $due     = array_values(array_filter($steps, static fn (array $s): bool => $s['state'] === PostPullCheck::DUE));
        $unknown = array_filter($steps, static fn (array $s): bool => $s['state'] === PostPullCheck::UNKNOWN);

        if ($unknown !== []) {
            CLI::newLine();
            CLI::write('Langkah "belum-tercatat": instalasi ini belum pernah mencatatnya. Bila sudah pasti terbaru, jalankan php spark gelita:setelah-pull --tandai; bila ragu, jalankan perintahnya sendiri.', 'light_gray');
        }

        if ($due === []) {
            CLI::newLine();
            CLI::write('Tidak ada perintah yang perlu dijalankan.', 'green');

            return EXIT_SUCCESS;
        }

        CLI::newLine();
        CLI::write('Perintah yang perlu, berurutan:', 'yellow');

        foreach ($due as $step) {
            CLI::write('  ' . self::commandLine($step['command']));
        }

        if (! $this->flag('jalankan', $params)) {
            CLI::newLine();
            CLI::write('Jalankan semuanya dengan: php spark gelita:setelah-pull --jalankan');

            return EXIT_SUCCESS;
        }

        return $this->runSteps($due, $this->flag('ya', $params));
    }

    /** @param list<array{id: string, title: string, state: string, reason: string, command: string|null, runnable: bool}> $steps */
    private function report(array $steps): void
    {
        $rows = [];

        foreach ($steps as $step) {
            $rows[] = [
                CLI::color(str_pad($step['state'], 15), self::COLORS[$step['state']] ?? 'white'),
                $step['title'],
                $step['reason'],
            ];
        }

        CLI::table($rows, ['Keadaan', 'Langkah', 'Keterangan']);
    }

    /** @param list<array{id: string, title: string, state: string, reason: string, command: string|null, runnable: bool}> $due */
    private function runSteps(array $due, bool $yes): int
    {
        if (ENVIRONMENT === 'production') {
            CLI::error('Server produksi diperbarui dengan skrip deploy (deploy/linux/deploy.sh atau deploy/windows/deploy.ps1), bukan dengan --jalankan.');

            return EXIT_ERROR;
        }

        if ($due[0]['id'] === 'composer') {
            CLI::error('Jalankan composer install dulu (tidak bisa dari perintah ini), lalu ulangi php spark gelita:setelah-pull --jalankan.');

            return EXIT_ERROR;
        }

        $runnable = array_values(array_filter($due, static fn (array $s): bool => $s['runnable']));

        if (! $yes && CLI::prompt('Jalankan ' . count($runnable) . ' perintah di atas sekarang?', ['y', 'n']) !== 'y') {
            CLI::write('Dibatalkan; tidak ada yang dijalankan.');

            return EXIT_SUCCESS;
        }

        $ledger = DevStepLedger::forInstall();

        foreach ($runnable as $step) {
            CLI::newLine();
            CLI::write('» ' . self::commandLine($step['command']), 'cyan');
            passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(ROOTPATH . 'spark') . ' ' . $step['command'], $code);

            if ($code !== 0) {
                CLI::error('Berhenti: ' . $step['title'] . ' gagal (kode ' . $code . '). Perbaiki penyebabnya, lalu ulangi php spark gelita:setelah-pull.');

                return EXIT_ERROR;
            }

            if (isset(PostPullCheck::LEDGER_STEPS[$step['id']])) {
                $ledger->record($step['id']);
            }
        }

        CLI::newLine();
        CLI::write('Selesai: ' . count($runnable) . ' perintah dijalankan.', 'green');

        return EXIT_SUCCESS;
    }

    private static function commandLine(?string $command): string
    {
        return $command === null ? '' : (str_starts_with($command, 'composer') ? $command : 'php spark ' . $command);
    }
}
