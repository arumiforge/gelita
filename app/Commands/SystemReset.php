<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Services\SystemResetService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Reset total GELITA untuk penelitian kelas 5 (Dok 1 §7).
 *
 * Mengosongkan semua data uji coba dan pengaturan, lalu menyiapkan ulang 12
 * tantangan, pengaturan bawaan, dan bank soal kelas 5. Pustaka, cerita,
 * level, media, audio, direktori sekolah resmi, dan akun staf tetap.
 * Data yang dikosongkan TIDAK dapat dikembalikan — buat backup dulu.
 *
 * JANGAN dijalankan di server produksi sebelum ketiga dokumen perombakan
 * selesai; prosedur rilisnya ada di Dok 3 §13.
 */
class SystemReset extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:reset';
    protected $description = 'Reset total data uji coba dan pengaturan untuk penelitian kelas 5 (Pustaka, cerita, media, sekolah resmi, staf tetap).';
    protected $usage       = 'gelita:reset [--dry-run] [--ya] [--paksa] [--pemeliharaan] [--tanpa-bank] [--bank=JALUR] [--tanpa-mockup] [--hapus-guru] [--bersihkan-media-tantangan]';
    protected $options     = [
        '--dry-run'                   => 'Hanya hitung dan tampilkan rencana; tidak menulis apa pun.',
        '--ya'                        => 'Lewati ketik konfirmasi (untuk skrip rilis).',
        '--paksa'                     => 'Abaikan penjaga "ada kelas aktif dalam 10 menit".',
        '--pemeliharaan'              => 'Aktifkan mode pemeliharaan (writable/pemeliharaan.flag) selama reset.',
        '--tanpa-bank'                => 'Jangan impor bank soal (12 tantangan kosong).',
        '--bank'                      => 'Workbook bank soal lain (bawaan docs/bank-soal/gelita-bank-soal-kelas5.xlsx).',
        '--tanpa-mockup'              => 'Jangan buat gambar mockup.',
        '--hapus-guru'                => 'Hapus juga semua akun guru (akun admin selalu tetap).',
        '--bersihkan-media-tantangan' => 'Hapus media challenge.* yang tidak dipakai soal/tantangan mana pun.',
    ];

    public function run(array $params)
    {
        // pre_system (tempat zona waktu DB disetel) tidak terpicu di CLI
        db_sync_timezone();

        $flag    = fn (string $name): bool => $this->flag($name, $params);
        $dryRun  = $flag('dry-run');
        $yes     = $flag('ya');
        $service = new SystemResetService();
        $db      = db_connect();

        CLI::write('GELITA · Reset total untuk penelitian kelas 5', 'yellow');

        // 1. Pra-pemeriksaan: koneksi database
        try {
            $db->initialize();
        } catch (\Throwable $e) {
            CLI::error('Tidak dapat tersambung ke database: ' . $e->getMessage());
            CLI::error('Periksa isian database di .env, atau DSN akun migrasi (database_default_DSN).');

            return EXIT_ERROR;
        }

        CLI::write(sprintf(
            'Database: %s @ %s (%s %s) · migration terakhir %s',
            $db->getDatabase(),
            $db->hostname ?: '-',
            $db->getPlatform(),
            $db->getVersion(),
            $service->latestMigration() ?? '(belum ada)',
        ));
        CLI::newLine();

        // 1. Pra-pemeriksaan: struktur database dan kelas aktif
        if (! $service->migrationsReady()) {
            CLI::error('Struktur database belum terbaru (perlu migration ' . SystemResetService::REQUIRED_MIGRATION . '). Jalankan `php spark migrate` dulu.');

            return EXIT_ERROR;
        }

        $active = $service->activeSessionCount();

        if ($active > 0 && ! $flag('paksa')) {
            CLI::error("Ada {$active} sesi bermain aktif dalam " . SystemResetService::ACTIVE_GUARD_MINUTES . ' menit terakhir. Tunggu kelas selesai, atau ulangi dengan --paksa.');

            return EXIT_ERROR;
        }

        $bank = $this->bankPath($params);

        if ($bank !== null && ! is_file($bank)) {
            CLI::error('Workbook --bank tidak ditemukan: ' . $bank . '. Tidak ada yang diubah.');

            return EXIT_ERROR;
        }

        $plan = $service->plan();
        $this->printPlan($plan);

        CLI::write('Bank soal: ' . ($flag('tanpa-bank') ? 'tidak diimpor (--tanpa-bank)' : ($bank ?? ROOTPATH . SystemResetService::DEFAULT_BANK)));

        $cleanMedia = $flag('bersihkan-media-tantangan');

        if ($cleanMedia) {
            // Daftar akhir baru diketahui sesudah impor bank soal; ini perkiraan dari isi sekarang
            $this->printMedia($service->unusedChallengeMedia(), 'Media tantangan yang SAAT INI tidak terpakai (daftar akhir dihitung ulang sesudah impor bank soal)');
        }

        if ($dryRun) {
            CLI::newLine();
            CLI::write('--dry-run: tidak ada yang diubah.', 'green');

            return EXIT_SUCCESS;
        }

        // 2. Konfirmasi
        CLI::newLine();
        CLI::write('Data ini tidak dapat dikembalikan. Pastikan backup sudah dibuat (deploy/linux/backup.sh atau deploy/windows/backup.ps1).', 'red');

        if (! $yes) {
            $answer = CLI::prompt('Ketik ' . SystemResetService::CONFIRM_WORD . ' untuk melanjutkan');

            if (trim((string) $answer) !== SystemResetService::CONFIRM_WORD) {
                CLI::error('Konfirmasi tidak cocok. Reset dibatalkan; tidak ada yang diubah.');

                return EXIT_ERROR;
            }
        }

        // 3. Mode pemeliharaan
        $maintenance = $flag('pemeliharaan');
        $flagFile    = WRITEPATH . 'pemeliharaan.flag';

        if ($maintenance) {
            file_put_contents($flagFile, 'gelita:reset ' . date('c'));
            CLI::write('Mode pemeliharaan aktif.', 'yellow');
        }

        try {
            $result = $service->run([
                'hapus_guru'      => $flag('hapus-guru'),
                'tanpa_bank'      => $flag('tanpa-bank'),
                'bank'            => $bank,
                'tanpa_mockup'    => $flag('tanpa-mockup'),
                'bersihkan_media' => $cleanMedia,
                // Sesudah impor: tampilkan daftar akhir, lalu minta konfirmasi kecuali --ya
                'konfirmasi_media' => function (array $media) use ($yes): bool {
                    $this->printMedia($media, 'Media tantangan tidak terpakai yang akan DIHAPUS');

                    return $yes || strtolower(trim((string) CLI::prompt('Hapus ' . count($media) . ' media ini?', ['y', 'n']))) === 'y';
                },
            ]);
        } catch (\Throwable $e) {
            CLI::error('Reset gagal: ' . $e->getMessage());
            CLI::error('Data yang sudah dikosongkan tidak dapat kembali. Perbaiki penyebabnya, lalu jalankan ulang `php spark gelita:reset` (aman diulang).');

            return EXIT_ERROR;
        } finally {
            if ($maintenance && is_file($flagFile)) {
                unlink($flagFile);
                CLI::write('Mode pemeliharaan dimatikan.', 'yellow');
            }
        }

        $this->printSummary($result);

        if ($result['errors'] > 0) {
            CLI::error('Verifikasi konten menemukan ' . $result['errors'] . ' galat. Impor ulang bank soal (`php spark gelita:bank:import berkas.xlsx`) lalu jalankan `php spark gelita:content:verify`.');

            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }

    /** @param list<array{id: int, asset_key: string, storage_path: string}> $media */
    private function printMedia(array $media, string $title): void
    {
        CLI::newLine();
        CLI::write($title . ' (' . count($media) . '):', 'yellow');

        foreach ($media as $row) {
            CLI::write('  ' . $row['asset_key'] . '  (' . $row['storage_path'] . ')');
        }
    }

    /** @param array<string, mixed> $plan */
    private function printPlan(array $plan): void
    {
        $rows = [];

        foreach ($plan['clear'] as $table => $count) {
            $rows[] = [$table, number_format($count, 0, ',', ' ')];
        }

        CLI::write('Akan DIKOSONGKAN', 'yellow');
        CLI::table($rows, ['Tabel', 'Baris']);

        CLI::write(sprintf(
            'Akan DIBANGUN ULANG: %d tantangan → %d tantangan, %d soal, %d bacaan, %d petunjuk',
            $plan['rebuild']['challenge_nodes'],
            $plan['nodes_after'],
            $plan['rebuild']['challenge_items'],
            $plan['rebuild']['reading_passages'],
            $plan['rebuild']['hints'],
        ));
        CLI::write(sprintf(
            'Akan DIISI ULANG nilai bawaan: %d studi, %d rilis, %d indikator',
            $plan['settings']['research_studies'],
            $plan['settings']['game_releases'],
            $plan['settings']['learning_indicators'],
        ));
        CLI::write(sprintf('Akan DIHAPUS: %d sekolah belum terverifikasi', $plan['unverified_schools']));
        CLI::write(sprintf(
            'TETAP: Pustaka %d halaman / %d media · cerita %d baris · %d wilayah · %d media · %d audio · %d akun staf (%d admin, %d guru)',
            $plan['kept']['library_pages'],
            $plan['kept']['library_media'],
            $plan['kept']['dialogues'],
            $plan['kept']['levels'],
            $plan['kept']['media_assets'],
            $plan['kept']['audio_assets'],
            $plan['staff']['admin'] + $plan['staff']['guru'],
            $plan['staff']['admin'],
            $plan['staff']['guru'],
        ));
    }

    /** @param array<string, mixed> $result */
    private function printSummary(array $result): void
    {
        CLI::newLine();
        CLI::write(sprintf(
            '✓ Dikosongkan (%s) · ✓ Pengaturan bawaan · ✓ %d tantangan · %d sekolah belum terverifikasi dihapus%s',
            strtoupper($result['mode']),
            $result['nodes'],
            $result['schools_removed'],
            $result['teachers_removed'] > 0 ? ' · ' . $result['teachers_removed'] . ' akun guru dihapus' : '',
        ), 'green');

        $bank = $result['bank'];
        CLI::write(match ($bank['status']) {
            'imported' => sprintf(
                '✓ Bank soal kelas 5: %d soal (%d dinilai + %d pengecoh), %d variasi, %d bacaan',
                $bank['items'],
                $bank['scorable'],
                $bank['items'] - $bank['scorable'],
                $bank['variants'],
                $bank['passages'],
            ),
            'failed' => 'Bank soal: impor gagal dari ' . $bank['path'],
            default  => 'Bank soal: ' . ($bank['message'] ?? $bank['status']),
        }, $bank['status'] === 'failed' ? 'red' : 'green');

        $mockups = $result['mockups'];
        CLI::write('Gambar mockup: ' . ($mockups['status'] === 'done'
            ? $mockups['count'] . ' gambar contoh dibuat'
                . ($mockups['existing'] > 0 ? ', ' . $mockups['existing'] . ' sudah ada' : '')
                . ($mockups['kept'] > 0 ? ', ' . $mockups['kept'] . ' gambar asli dipertahankan' : '')
            : $mockups['message']), $mockups['status'] === 'done' && $mockups['failed'] !== [] ? 'red' : null);

        if ($result['media_removed'] !== []) {
            CLI::write('Media tantangan dihapus: ' . count($result['media_removed']));
        }

        $warnings = count($result['findings']) - $result['errors'];
        CLI::write(sprintf('Verifikasi konten: %d galat, %d peringatan', $result['errors'], $warnings), $result['errors'] > 0 ? 'red' : 'green');

        foreach ($result['warnings'] as $warning) {
            CLI::write('! ' . $warning, 'yellow');
        }

        CLI::write(sprintf('Selesai dalam %s detik. Kode siswa berikutnya: %s', str_replace('.', ',', (string) $result['seconds']), $result['next_code']));
    }

    /** @param array<int|string, mixed> $params */
    private function bankPath(array $params): ?string
    {
        $path = $this->option('bank', $params);

        if ($path === null) {
            return null;
        }

        return str_starts_with($path, '/') ? $path : ROOTPATH . $path;
    }
}
