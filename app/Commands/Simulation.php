<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\Simulation\Simulator;
use App\Models\ResearchStudyModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Data siswa simulasi untuk mengembangkan dan menguji panel, tabel data
 * mentah, dan ekspor dengan isi yang realistis (App\Libraries\Simulation\Simulator).
 *
 * Siswa simulasi dikenali dari nama pengguna `sim_NNN` dan sekolah
 * "Sekolah Simulasi …"; sandinya Simulator::PASSWORD.
 *
 * Menolak berjalan di production, tanpa studi aktif, dan bila studi aktif
 * sudah berisi siswa sungguhan (pengambilan data berjalan): data simulasi
 * tidak boleh tercampur dengan data penelitian.
 */
class Simulation extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:simulasi';
    protected $description = 'Membuat siswa simulasi dengan riwayat bermain lengkap (hanya pengembangan/uji).';
    protected $usage       = 'gelita:simulasi [--siswa=N] [--seed=N] [--mulai=YYYY-MM-DD]';
    protected $options     = [
        '--siswa' => 'Jumlah siswa simulasi baru (bawaan 12, paling banyak 500).',
        '--seed'  => 'Benih pengacak; seed yang sama pada database awal yang sama memberi isi yang sama (bawaan 2026).',
        '--mulai' => 'Hari sekolah pertama simulasi (bawaan: Senin, 4 minggu sebelum hari ini). Harus sudah lewat.',
    ];

    public function run(array $params)
    {
        // pre_system (tempat zona waktu DB disetel) tidak terpicu di CLI
        db_sync_timezone();

        $refusal = self::refusal(ENVIRONMENT);

        if ($refusal !== null) {
            CLI::error('Ditolak: ' . $refusal);

            return EXIT_ERROR;
        }

        $count = $this->option('siswa', $params) ?? '12';
        $seed  = $this->option('seed', $params) ?? '2026';
        $start = $this->startDate($this->option('mulai', $params));

        if (! ctype_digit($count) || (int) $count < 1 || (int) $count > 500) {
            CLI::error('--siswa harus 1–500.');

            return EXIT_ERROR;
        }

        if (preg_match('/^-?\d+$/', $seed) !== 1) {
            CLI::error('--seed harus bilangan bulat.');

            return EXIT_ERROR;
        }

        [$count, $seed] = [(int) $count, (int) $seed];

        if ($start === null) {
            CLI::error('--mulai harus tanggal YYYY-MM-DD, paling lambat 14 hari sebelum hari ini.');

            return EXIT_ERROR;
        }

        CLI::write(sprintf('Simulasi %d siswa, seed %d, mulai %s', $count, $seed, $start->format('Y-m-d')), 'yellow');

        try {
            $stats = (new Simulator(db_connect(), $seed, $start, static fn (string $line) => CLI::write($line)))->run($count);
        } catch (\Throwable $e) {
            CLI::error('Simulasi gagal: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')');

            return EXIT_ERROR;
        }

        CLI::table(array_map(static fn (string $k, int $v): array => [$k, (string) $v], array_keys($stats), $stats), ['Hitungan', 'Jumlah']);
        CLI::write('Masuk sebagai siswa simulasi: nama pengguna sim_001, …, sandi ' . Simulator::PASSWORD, 'green');

        return EXIT_SUCCESS;
    }

    /**
     * Alasan menolak, atau null bila boleh berjalan.
     */
    public static function refusal(string $environment): ?string
    {
        if ($environment === 'production') {
            return 'gelita:simulasi tidak boleh dijalankan di production (CI_ENVIRONMENT = production).';
        }

        $study = model(ResearchStudyModel::class)->activeStudy();

        if ($study === null) {
            return 'tidak ada studi aktif (research_studies.status = active); pendaftaran siswa membutuhkannya.';
        }

        // shortcut: the simulator plays every region in free sittings, which field mode forbids;
        // it refuses instead of making its own `sim` field sessions (28 §19.2), until it learns meeting plans
        if ((int) ($study['field_mode'] ?? 0) === 1) {
            return 'mode penelitian menyala pada studi aktif. Matikan dulu di halaman Penelitian, lalu jalankan simulasi.';
        }

        // Pengambilan data berjalan: studi aktif sudah punya sesi milik siswa sungguhan
        $real = db_connect()->table('game_sessions s')
            ->join('participants p', 'p.id = s.participant_id')
            ->where('s.study_id', (int) $study['id'])
            ->notLike('p.username', Simulator::USERNAME_PREFIX, 'after')
            ->countAllResults();

        if ($real > 0) {
            return sprintf(
                'studi aktif %s sedang berjalan (%d sesi milik siswa sungguhan). Jalankan simulasi di database pengembangan, mis. sesudah gelita:reset.',
                $study['code'],
                $real,
            );
        }

        return null;
    }

    private function startDate(?string $value): ?\DateTimeImmutable
    {
        $latest = (new \DateTimeImmutable('today'))->modify('-14 days');

        if ($value === null) {
            // Senin, 4 minggu sebelum hari ini: ±3 minggu hari sekolah tetap sudah lewat
            return (new \DateTimeImmutable('monday this week'))->modify('-4 weeks');
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);

        return $date === false || $date > $latest ? null : $date;
    }
}
