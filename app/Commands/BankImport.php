<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Memuat workbook bank soal (07_FEATURE_INTEGRATION.md FITUR 12a) lewat
 * ContentImportService — service yang sama dengan panel
 * `/admin/konten/impor-bank`. `--dry-run` hanya memvalidasi.
 */
class BankImport extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:bank:import';
    protected $description = 'Memuat workbook bank soal (XLSX) ke tabel konten dalam satu transaction.';
    protected $usage       = 'gelita:bank:import <file> [--dry-run] [--staff <username>]';
    protected $arguments   = [
        'file' => 'Path workbook .xlsx, mis. writable/uploads/bank-soal.xlsx',
    ];
    protected $options = [
        '--dry-run' => 'Pratinjau ringkasan & galat tanpa menulis database.',
        '--staff'   => 'Nama pengguna admin yang dicatat di audit log (bawaan: admin aktif pertama).',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $file = $this->resolvePath($params[0] ?? null);

        if ($file === null) {
            CLI::error('Berkas workbook tidak ditemukan. Pemakaian: php spark ' . $this->usage);

            return EXIT_ERROR;
        }

        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'xlsx') {
            CLI::error('Hanya workbook .xlsx yang diterima.');

            return EXIT_ERROR;
        }

        $dryRun  = array_key_exists('dry-run', $params) || CLI::getOption('dry-run') !== null;
        $service = service('contentImportService');

        try {
            $result = $dryRun ? $service->preview($file) : null;

            if (! $dryRun) {
                $staffId = $this->staffId((string) ($params['staff'] ?? CLI::getOption('staff') ?? ''));

                if ($staffId === null) {
                    CLI::error('Akun admin aktif tidak ditemukan untuk dicatat di audit log (pakai --staff <username>).');

                    return EXIT_ERROR;
                }

                $result = $service->import($file, $staffId);
            }
        } catch (\Throwable $e) {
            CLI::error('Workbook tidak dapat dibaca: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        $this->report($result, $dryRun);

        return $result['ok'] ? EXIT_SUCCESS : EXIT_ERROR;
    }

    /** @param array<string, mixed> $result */
    private function report(array $result, bool $dryRun): void
    {
        if ($dryRun && isset($result['summary'])) {
            $summary = $result['summary'];

            CLI::write(sprintf(
                'Workbook: %d node, %d bacaan, %d butir, %d opsi, %d petunjuk, %d pengecoh, %d halaman pustaka, %d media pustaka.',
                $summary['nodes'],
                $summary['passages'],
                $summary['items'],
                $summary['options'],
                $summary['hints'],
                $summary['distractors'],
                $summary['library'] ?? 0,
                $summary['library_media'] ?? 0,
            ));

            $rows = [];

            foreach ($summary['per_node'] as $ref => $counts) {
                $rows[] = [(string) $ref, (string) ($counts['items'] ?? 0), (string) ($counts['options'] ?? 0), (string) ($counts['hints'] ?? 0), (string) ($counts['distractors'] ?? 0)];
            }

            if ($rows !== []) {
                CLI::table($rows, ['Node', 'Butir', 'Opsi', 'Petunjuk', 'Pengecoh']);
            }
        }

        if (! $dryRun && $result['ok']) {
            CLI::table(
                array_map(static fn ($table, $count): array => [(string) $table, (string) $count], array_keys($result['written']), $result['written']),
                ['Tabel', 'Baris ditulis'],
            );
        }

        foreach ($result['errors'] as $error) {
            CLI::write(sprintf('GALAT  %s baris %d: %s', $error['sheet'], $error['row'], $error['message']), 'red');
        }

        foreach ($result['warnings'] as $warning) {
            CLI::write(sprintf('peringatan  %s baris %d: %s', $warning['sheet'], $warning['row'], $warning['message']), 'yellow');
        }

        if ($result['ok']) {
            CLI::write($dryRun
                ? 'Tidak ada galat. Jalankan lagi tanpa --dry-run untuk mengimpor, lalu php spark gelita:content:verify.'
                : 'Impor selesai. Lanjutkan dengan php spark gelita:content:verify.', 'green');
        } else {
            CLI::write(count($result['errors']) . ' galat; tidak ada yang ditulis ke database.', 'red');
        }
    }

    /**
     * spark berpindah ke folder public/ sebelum command berjalan, jadi path
     * relatif dicari dari direktori kerja shell pemanggil lalu dari root proyek.
     */
    private function resolvePath(?string $file): ?string
    {
        if ($file === null || trim($file) === '') {
            return null;
        }

        $isAbsolute = str_starts_with($file, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $file) === 1;
        $candidates = $isAbsolute ? [$file] : array_filter([
            getenv('PWD') !== false ? rtrim((string) getenv('PWD'), '/') . '/' . $file : null,
            ROOTPATH . $file,
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return realpath($candidate) ?: $candidate;
            }
        }

        return null;
    }

    /** Admin yang dicatat sebagai pelaku impor. */
    private function staffId(string $username): ?int
    {
        $builder = db_connect()->table('staff_users')->select('id')->where('role', 'admin')->where('is_active', 1);

        if ($username !== '') {
            $builder->where('username', $username);
        }

        $row = $builder->orderBy('id', 'ASC')->get()->getRowArray();

        return $row === null ? null : (int) $row['id'];
    }
}
