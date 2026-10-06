<?php

namespace App\Database\Seeds;

use App\Services\ContentImportService;
use App\Services\SystemResetService;
use CodeIgniter\CLI\CLI;

/**
 * Bank soal final kelas 5 (docs/bank-soal/gelita-bank-soal-kelas5.xlsx,
 * Dok 2 §7) untuk mesin pengembangan: dipanggil DatabaseSeeder hanya bila
 * CI_ENVIRONMENT = development, sehingga 12 tantangan langsung dapat
 * dimainkan. Pengganti SampleItemSeeder (soal contoh Dok 1 §8). Server
 * lain mendapat bank yang sama lewat `php spark gelita:reset`.
 *
 * Impor dicatat atas nama akun admin pertama (audit `content_import`).
 * Gambar tantangan masih kosong sampai `php spark gelita:mockups`
 * memasang gambar contoh.
 */
class BankKelas5Seeder extends GelitaSeeder
{
    public function run(): void
    {
        $path = ROOTPATH . SystemResetService::DEFAULT_BANK;

        if (! is_file($path)) {
            $this->warn('Workbook bank soal kelas 5 tidak ditemukan: ' . SystemResetService::DEFAULT_BANK . '. Tantangan masih kosong.');

            return;
        }

        $staffId = $this->db->table('staff_users')->select('id')->where('role', 'admin')->where('is_active', 1)
            ->orderBy('id', 'ASC')->get()->getRow('id');

        if ($staffId === null) {
            $this->warn('Bank soal kelas 5 tidak diimpor: belum ada akun admin aktif.');

            return;
        }

        $result = (new ContentImportService())->import($path, (int) $staffId);

        if (! $result['ok']) {
            $this->warn('Impor bank soal kelas 5 gagal: ' . implode(' · ', array_map(
                static fn (array $e): string => $e['sheet'] . '#' . $e['row'] . ' ' . $e['message'],
                array_slice($result['errors'], 0, 5),
            )));

            return;
        }

        $this->info(sprintf(
            'Bank soal kelas 5 diimpor: %d soal, %d bacaan, %d petunjuk. Jalankan php spark gelita:mockups untuk gambar contoh.',
            $result['written']['items'] ?? 0,
            $result['written']['passages'] ?? 0,
            $result['written']['hints'] ?? 0,
        ));
    }

    private function warn(string $message): void
    {
        if (is_cli() && ! $this->silent) {
            CLI::write('  ' . $message, 'yellow');
        }
    }
}
