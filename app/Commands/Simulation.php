<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\Simulation\FieldSimulator;
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
 *
 * Research mode (owner, 20 November 2026): when the active study has
 * `field_mode = 1` with the `lima_pertemuan` plan, FieldSimulator plays
 * research schools one after another through field sessions P1–P5
 * (`--sekolah=22,18,16,13,11` students per school, or `--sekolah=6` with
 * `--siswa=300` spread over six schools).
 */
class Simulation extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:simulasi';
    protected $description = 'Membuat siswa simulasi dengan riwayat bermain lengkap (hanya pengembangan/uji).';
    protected $usage       = 'gelita:simulasi [--siswa=N] [--sekolah=N|N,N,…] [--seed=N] [--mulai=YYYY-MM-DD]';
    protected $options     = [
        '--siswa'   => 'Jumlah siswa simulasi baru (bawaan 12, paling banyak 500).',
        '--sekolah' => 'Mode penelitian: jumlah sekolah (siswa dibagi acak), atau jumlah siswa per sekolah dipisah koma, mis. 22,18,16,13,11. Sekolah berjalan satu per satu.',
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
        $field          = (int) (model(ResearchStudyModel::class)->activeStudy()['field_mode'] ?? 0) === 1;
        $sizes          = null;

        if ($field) {
            $sizes = self::schoolSizes($this->option('sekolah', $params), $this->option('siswa', $params) === null ? null : $count, $seed);

            if ($sizes === null) {
                CLI::error('--sekolah harus jumlah sekolah 1–20, atau jumlah siswa per sekolah dipisah koma (1–500 siswa seluruhnya).');

                return EXIT_ERROR;
            }

            $count = array_sum($sizes);
            $start = $this->startDate($this->option('mulai', $params), FieldSimulator::weeksNeeded(count($sizes)));
        }

        if ($start === null) {
            CLI::error('--mulai harus tanggal YYYY-MM-DD, paling lambat 14 hari sebelum hari ini (mode penelitian: cukup jauh untuk semua sekolah).');

            return EXIT_ERROR;
        }

        CLI::write(sprintf('Simulasi %d siswa, seed %d, mulai %s', $count, $seed, $start->format('Y-m-d')), 'yellow');

        if ($sizes !== null) {
            CLI::write('Mode penelitian: ' . count($sizes) . ' sekolah berurutan, siswa per sekolah ' . implode(', ', $sizes), 'yellow');
        }

        try {
            $log   = static fn (string $line) => CLI::write($line);
            $stats = $sizes !== null
                ? (new FieldSimulator(db_connect(), $seed, $start, $log))->runSchools($sizes)
                : (new Simulator(db_connect(), $seed, $start, $log))->run($count);
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

        // Research mode plays the five-meeting plan (FieldSimulator); the one-session think-aloud plan is not simulated
        if ((int) ($study['field_mode'] ?? 0) === 1 && (string) ($study['meeting_plan'] ?? '') !== 'lima_pertemuan') {
            return sprintf('mode penelitian dengan rencana %s belum bisa disimulasikan; pakai rencana lima_pertemuan.', (string) ($study['meeting_plan'] ?? ''));
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

    /**
     * First school day: by default Monday, $weeks weeks before this week (4
     * for free play: ±3 weeks of school days already past; research mode
     * needs about three weeks per school). A given date must leave the whole
     * run in the past.
     */
    private function startDate(?string $value, int $weeks = 4): ?\DateTimeImmutable
    {
        $latest = (new \DateTimeImmutable('today'))->modify('-' . max(14, ($weeks - 1) * 7) . ' days');

        if ($value === null) {
            return (new \DateTimeImmutable('monday this week'))->modify('-' . $weeks . ' weeks');
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);

        return $date === false || $date > $latest ? null : $date;
    }

    /**
     * Students per school in research mode: a comma list as given, or
     * $students (default 12 per school) spread over a number of schools with
     * seeded differences of up to ±30%. Null when the value is not valid.
     *
     * @return list<int>|null
     */
    public static function schoolSizes(?string $value, ?int $students, int $seed): ?array
    {
        $value ??= '1';

        if (preg_match('/^\d+(,\d+)+$/', $value) === 1) {
            $sizes = array_map('intval', explode(',', $value));

            return min($sizes) >= 1 && array_sum($sizes) <= 500 && count($sizes) <= 20 ? $sizes : null;
        }

        if (! ctype_digit($value) || (int) $value < 1 || (int) $value > 20) {
            return null;
        }

        $schools  = (int) $value;
        $students ??= 12 * $schools;

        if ($students < $schools || $students > 500) {
            return null;
        }

        $rng     = new \Random\Randomizer(new \Random\Engine\Mt19937($seed ^ 0x5c01));
        $weights = array_map(static fn (): int => $rng->getInt(70, 130), range(1, $schools));
        $sizes   = array_map(static fn (int $w): int => max(1, intdiv($students * $w, array_sum($weights))), $weights);

        for ($i = 0; array_sum($sizes) < $students; $i++) {
            $sizes[$i % $schools]++;
        }

        return $sizes;
    }
}
