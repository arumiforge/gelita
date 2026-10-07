<?php

namespace App\Database\Seeds;

use App\Libraries\MockupGenerator;
use App\Services\ContentImportService;
use App\Services\SystemResetService;
use CodeIgniter\CLI\CLI;

/**
 * Isi permainan kelas 5 untuk instalasi baru (dipanggil DatabaseSeeder):
 *
 * 1. Pustaka Kedu: docs/pustaka/gelita-pustaka.xlsx (30 halaman, 48 media);
 * 2. bank soal final kelas 5: docs/bank-soal/gelita-bank-soal-kelas5.xlsx
 *    (12 tantangan, 126 soal, 30 variasi, Dok 2 §7);
 * 3. gambar contoh (mockup) untuk 45 slot gambar tantangan (Dok 2 §10).
 *
 * Kedua impor dicatat atas nama akun admin pertama (audit `content_import`),
 * jadi StaffUserSeeder harus sudah berjalan.
 */
class KontenKelas5Seeder extends GelitaSeeder
{
    public const PUSTAKA = 'docs/pustaka/gelita-pustaka.xlsx';

    public function run(): void
    {
        $staffId = $this->db->table('staff_users')->select('id')->where('role', 'admin')->where('is_active', 1)
            ->orderBy('id', 'ASC')->get()->getRow('id');

        if ($staffId === null) {
            $this->warn('Pustaka dan bank soal tidak diimpor: belum ada akun admin aktif.');

            return;
        }

        foreach (['Pustaka Kedu' => self::PUSTAKA, 'Bank soal kelas 5' => SystemResetService::DEFAULT_BANK] as $label => $relative) {
            $path = ROOTPATH . $relative;

            if (! is_file($path)) {
                $this->warn("{$label}: berkas {$relative} tidak ditemukan.");

                continue;
            }

            $result = (new ContentImportService())->import($path, (int) $staffId);

            if (! $result['ok']) {
                $this->warn("{$label}: impor gagal: " . implode(' · ', array_map(
                    static fn (array $e): string => $e['sheet'] . '#' . $e['row'] . ' ' . $e['message'],
                    array_slice($result['errors'], 0, 5),
                )));

                continue;
            }

            $this->info($label . ' diimpor: ' . implode(', ', array_map(
                static fn (string $key, int $count): string => "{$count} {$key}",
                array_keys(array_filter($result['written'])),
                array_filter($result['written']),
            )) . '.');
        }

        $mockups = (new MockupGenerator($this->db))->generateMissing();
        $this->info(sprintf('Gambar contoh: %d dibuat, %d sudah ada, %d gambar asli dipertahankan.', $mockups['count'], $mockups['existing'], $mockups['kept']));

        foreach ($mockups['failed'] as $failure) {
            $this->warn('Gambar contoh gagal: ' . $failure);
        }
    }

    private function warn(string $message): void
    {
        if (is_cli() && ! $this->silent) {
            CLI::write('  ' . $message, 'yellow');
        }
    }
}
