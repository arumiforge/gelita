<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Services\TestAccountService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Test accounts for the validators' test install (research revision 10,
 * part 2: docs/revisi-10-analitik/28_IMPLEMENTASI_SESI_LAPANGAN.md §13,
 * change no. 24), on the `gelita:simulasi` pattern.
 *
 *   php spark gelita:uji:akun --buat                  # the default list
 *   php spark gelita:uji:akun --buat --nama=uji.cadangan3
 *   php spark gelita:uji:akun --reset=uji.bahasa1     # delete this account's play data
 *   php spark gelita:uji:akun --daftar                # accounts, codes, last play
 *
 * Refuses in production, when any student is not `uji.*`, and without an
 * active study (TestAccountService::refusal()).
 */
class TestAccounts extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:uji:akun';
    protected $description = 'Akun uji uji.* untuk instalasi uji validator (bukan untuk server penelitian).';
    protected $usage       = 'gelita:uji:akun --buat [--nama=uji.x] | --reset=uji.x | --daftar';
    protected $options     = [
        '--buat'   => 'Membuat akun uji yang belum ada (daftar bawaan, atau satu akun dengan --nama).',
        '--nama'   => 'Satu akun uji, mis. uji.cadangan3.',
        '--reset'  => 'Menghapus data permainan satu akun uji; akunnya tetap.',
        '--daftar' => 'Daftar akun uji, kodenya, dan waktu bermain terakhir.',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $service = service('testAccounts');
        $refusal = $service->refusal(ENVIRONMENT);

        if ($refusal !== null) {
            CLI::error('Ditolak: ' . $refusal);

            return EXIT_ERROR;
        }

        try {
            if (($name = $this->option('reset', $params)) !== null) {
                $deleted = $service->reset($name);
                CLI::write("Data permainan {$name} dihapus; akunnya tetap.", 'green');
                CLI::table(array_map(static fn (string $t, int $n): array => [$t, (string) $n], array_keys($deleted), $deleted), ['Tabel', 'Baris']);

                return EXIT_SUCCESS;
            }

            if ($this->flag('buat', $params)) {
                $one  = $this->option('nama', $params);
                $rows = $service->create($one === null ? TestAccountService::DEFAULT_NAMES : [$one]);

                CLI::table(array_map(static fn (array $r): array => [
                    $r['username'], $r['code'], $r['status'] === 'created' ? (string) $r['password'] : '(sudah ada, dilewati)',
                ], $rows), ['Nama pengguna', 'Kode', 'Kata sandi']);
                CLI::write('Kata sandi hanya tampil sekali ini. Catat lalu bagikan ke validator.', 'yellow');

                return EXIT_SUCCESS;
            }

            if ($this->flag('daftar', $params)) {
                CLI::table(array_map(static fn (array $r): array => [$r['username'], $r['code'], (string) ($r['last_play'] ?? '—')], $service->list()), ['Nama pengguna', 'Kode', 'Bermain terakhir']);

                return EXIT_SUCCESS;
            }
        } catch (\InvalidArgumentException $e) {
            CLI::error($e->getMessage());

            return EXIT_ERROR;
        }

        CLI::write('Pakai --buat, --reset=uji.x, atau --daftar. Lihat: php spark help gelita:uji:akun');

        return EXIT_ERROR;
    }
}
