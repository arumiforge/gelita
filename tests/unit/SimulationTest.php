<?php

use App\Commands\Simulation;
use App\Libraries\Simulation\Simulator;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Encryption;
use Tests\Support\Database\GradeFiveWorld;

/**
 * `gelita:simulasi`: siswa simulasi bermain lewat layanan sungguhan pada
 * bank soal kelas 5 (GradeFiveWorld, SQLite).
 *
 * - penjaga: production, tanpa studi aktif, dan studi yang sudah berisi
 *   siswa sungguhan ditolak;
 * - isi: pendaftaran ke "Sekolah Simulasi", keempat jenis tantangan di
 *   ketiga wilayah, variasi hasil, event, kritik & saran, siswa belum selesai;
 * - batas 3 kesempatan dan aturan data tetap berlaku (bukan INSERT langsung);
 * - jam simulasi: semua waktu di masa lalu, urut, dan durasi dari jam simulasi;
 * - seed yang sama → isi yang sama.
 *
 * @internal
 */
final class SimulationTest extends CIUnitTestCase
{
    use GradeFiveWorld;
    use StreamFilterTrait;

    private BaseConnection $conn;
    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $encryption        = config(Encryption::class);
        $this->originalKey = (string) $encryption->key;
        $encryption->key   = 'kunci-uji-simulasi';

        $this->conn = $this->freshWorld();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCommandIsDiscoveredAndSyncsTimezone(): void
    {
        $commands = service('commands')->getCommands();

        $this->assertArrayHasKey('gelita:simulasi', $commands);
        $this->assertSame(Simulation::class, $commands['gelita:simulasi']['class']);
        $this->assertStringContainsString('db_sync_timezone()', (string) file_get_contents(APPPATH . 'Commands/Simulation.php'));
    }

    public function testRefusesProductionMissingStudyAndRunningStudy(): void
    {
        $this->assertNull(Simulation::refusal('development'));
        $this->assertStringContainsString('production', (string) Simulation::refusal('production'));

        // Field mode on: the simulator does not know meeting plans yet (28 §19.2)
        $this->conn->table('research_studies')->update(['field_mode' => 1]);
        $this->assertStringContainsString('mode penelitian', (string) Simulation::refusal('development'));
        $this->conn->table('research_studies')->update(['field_mode' => 0]);

        // Siswa sungguhan di studi aktif = pengambilan data berjalan
        $this->conn->table('participants')->insert(['participant_code' => 'GLT-000009', 'username' => 'jaka5', 'password_hash' => 'x']);
        $this->conn->table('game_sessions')->insert([
            'session_code' => 'S-ASLI', 'participant_id' => $this->conn->insertID(), 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active',
        ]);
        $this->assertStringContainsString('sedang berjalan', (string) Simulation::refusal('development'));

        $this->conn->table('game_sessions')->where('session_code', 'S-ASLI')->delete();
        $this->conn->table('research_studies')->update(['status' => 'closed']);
        $this->assertStringContainsString('tidak ada studi aktif', (string) Simulation::refusal('development'));
    }

    public function testInvalidOptionsAreRejected(): void
    {
        command('gelita:simulasi --siswa=0');
        $this->assertStringContainsString('--siswa harus 1–500', $this->getStreamFilterBuffer());

        $this->resetStreamFilterBuffer();
        command('gelita:simulasi --mulai=' . date('Y-m-d'));
        $this->assertStringContainsString('--mulai harus tanggal', $this->getStreamFilterBuffer());
        $this->assertSame(0, $this->conn->table('participants')->countAllResults());
    }

    public function testSimulatedStudentsPlayThroughRealServices(): void
    {
        $runStart = date('Y-m-d H:i:s');
        $stats    = $this->simulate(6, 11);

        // Siswa simulasi dapat dikenali
        $participants = $this->conn->table('participants')->orderBy('id')->get()->getResultArray();
        $this->assertSame(['sim_001', 'sim_002', 'sim_003', 'sim_004', 'sim_005', 'sim_006'], array_column($participants, 'username'));

        foreach ($participants as $row) {
            $this->assertStringStartsWith(Simulator::SCHOOL_PREFIX, (string) $row['school_name_snapshot']);
            $this->assertNotNull($row['assented_at']);
            $this->assertNotNull($row['intro_seen_at']);
            $this->assertSame('5', (string) $row['class_level']);
            $this->assertTrue(password_verify(Simulator::PASSWORD, (string) $row['password_hash']), 'sandi simulasi');
        }

        $this->assertGreaterThanOrEqual(3, $this->conn->table('schools')->like('name', Simulator::SCHOOL_PREFIX, 'after')->countAllResults(), 'beberapa sekolah');

        // Siswa pertama menuntaskan 15 tantangan (5 jenis × 3 wilayah, Skenario Digital since part 6); siswa ke-2 dan ke-3 belum selesai
        $first = $this->attempts($this->sessionOf('sim_001'));
        $this->assertSame(15, count(array_unique(array_column(array_filter($first, static fn (array $a): bool => $a['status'] === 'completed'), 'challenge_node_id'))));
        $engines = $this->conn->table('challenge_attempts a')->select('n.engine_type, l.code')
            ->join('challenge_nodes n', 'n.id = a.challenge_node_id')->join('levels l', 'l.id = n.level_id')
            ->where('a.status', 'completed')->groupBy('n.engine_type, l.code')->get()->getResultArray();
        $this->assertCount(15, $engines, 'kelima jenis tantangan di ketiga wilayah');
        // Part 6 (32 §15.2): scenario tries carry action and reason; the showcase closes one scenario after three tries
        $this->assertGreaterThan(0, $this->conn->table('item_tries')->where('action_key IS NOT NULL', null, false)->where('reason_code IS NOT NULL', null, false)->countAllResults());
        $this->assertGreaterThan(0, $this->conn->table('game_event_logs')->where('event_type', 'scenario_failed')->countAllResults());
        $this->assertSame('completed', $this->sessionStatus('sim_001'));
        $this->assertNotSame('completed', $this->sessionStatus('sim_002'));
        $this->assertNotSame('completed', $this->sessionStatus('sim_003'));
        $this->assertSame(1, $this->conn->table('participant_progress')->where('completed_levels', 3)->countAllResults() >= 1 ? 1 : 0);

        // Variasi hasil
        $reasons = array_count_values(array_filter(array_column($this->allAttempts(), 'completion_reason')));
        $this->assertGreaterThan(0, $reasons['solved'] ?? 0);
        $this->assertGreaterThan(0, $reasons['tries_exhausted'] ?? 0);
        $this->assertGreaterThan(0, $this->conn->table('challenge_attempts')->where('status', 'abandoned')->countAllResults(), 'pengerjaan ditinggalkan (Keluar)');
        $this->assertGreaterThan(0, $this->conn->table('item_responses')->where('solved_on_try', 1)->countAllResults(), 'benar sejak percobaan pertama');
        $this->assertGreaterThan(0, $this->conn->table('item_responses')->where('solved_on_try >', 1)->countAllResults(), 'salah lalu benar');

        // GradeFiveWorld tidak berisi dialog dan halaman Pustaka: dialogue_completed / library_page_viewed tidak diperiksa di sini
        foreach (['hint_opened', 'wrong_target_clicked', 'clue_failed', 'challenge_tries_exhausted', 'answer_submitted', 'screen_viewed',
            'intro_completed', 'library_opened', 'reading_progress', 'feedback_submitted',
            'level_opened', 'overlay_opened', 'challenge_abandoned', 'session_started'] as $type) {
            $this->assertGreaterThan(0, $this->conn->table('game_event_logs')->where('event_type', $type)->countAllResults(), "event {$type}");
        }

        // Removed types are never written (29 §5.1)
        $this->assertSame(0, $this->conn->table('game_event_logs')->whereIn('event_type', array_keys(App\Libraries\EventCatalog::REMOVED))->countAllResults());
        $this->assertGreaterThan(0, $this->conn->table('item_tries')->where('ready_ms IS NOT NULL', null, false)->countAllResults(), 'ready_ms for Benar atau Salah and Cari Objek');

        $this->assertSame(1, $this->conn->table('game_event_logs')->where('event_type', 'challenge_abandoned')->like('payload_json', 'pagehide')->countAllResults() > 0 ? 1 : 0, 'tab ditutup di tengah tantangan');
        $this->assertGreaterThan(0, $this->conn->table('participant_feedback')->countAllResults());
        $this->assertSame($stats['kritik_saran'], $this->conn->table('participant_feedback')->countAllResults());

        // Aturan data tetap ditegakkan layanan: paling banyak 3 cek dan 3 percobaan per soal
        $this->assertSame(0, $this->conn->table('challenge_attempts')->where('check_count >', 3)->countAllResults());
        $this->assertSame(0, $this->conn->table('item_tries')->where('try_no >', 3)->countAllResults());
        $this->assertSame(0, $this->conn->table('item_tries')->where('check_no IS NOT NULL')->where('first_input_ms IS NULL')->countAllResults(), 'waktu isian ikut tersimpan');

        // Jam simulasi: tidak ada waktu dari jam dinding, urutan event per sesi naik
        $this->assertSame(0, $this->conn->table('game_event_logs')->where('server_received_at >=', $runStart)->countAllResults());
        $this->assertSame(0, $this->conn->table('item_tries')->where('occurred_at >=', $runStart)->countAllResults());
        $this->assertSame(0, $this->conn->table('challenge_attempts')->where('started_at >=', $runStart)->countAllResults());
        $this->assertSame(0, $this->conn->table('participants')->where('created_at >=', $runStart)->countAllResults());

        $server = $this->conn->table('game_event_logs')->select('occurred_at')->where('session_id', $this->sessionOf('sim_001'))
            ->like('client_event_id', 'srv-', 'after')->orderBy('id')->get()->getResultArray();
        $times = array_column($server, 'occurred_at');
        $sorted = $times;
        sort($sorted);
        $this->assertSame($sorted, $times, 'event server berurutan menurut jam simulasi');

        foreach ($this->allAttempts() as $attempt) {
            if ($attempt['completed_at'] !== null) {
                $this->assertGreaterThan(0, (int) $attempt['duration_ms'], 'lama pengerjaan dari jam simulasi');
                $this->assertLessThan(3_600_000, (int) $attempt['duration_ms']);
            }
        }

        $this->assertGreaterThan(60_000, (int) $this->conn->table('game_sessions')->where('id', $this->sessionOf('sim_001'))->get()->getRow('duration_ms'));
    }

    public function testSameSeedGivesSameData(): void
    {
        $this->simulate(4, 5);
        $first = $this->fingerprint();

        $this->dropResetTables();
        $this->conn = $this->freshWorld();
        $this->simulate(4, 5);

        $this->assertSame($first, $this->fingerprint());

        $this->dropResetTables();
        $this->conn = $this->freshWorld();
        $this->simulate(4, 6);

        $this->assertNotSame($first, $this->fingerprint());
    }

    public function testNumbersContinueOnSecondRun(): void
    {
        $this->simulate(1, 1);
        $this->simulate(2, 2);

        $this->assertSame(['sim_001', 'sim_002', 'sim_003'], array_column($this->conn->table('participants')->orderBy('id')->get()->getResultArray(), 'username'));
    }

    // ------------------------------------------------------------- bantuan

    private function freshWorld(): BaseConnection
    {
        $db = $this->createGradeFiveWorld();

        // Siswa contoh GradeFiveWorld bukan siswa simulasi: dibuang, dan wilayah dibuka berurutan seperti bawaan
        $db->table('game_sessions')->truncate();
        $db->table('participants')->truncate();
        $db->table('research_studies')->update(['unlock_mode' => 'sequential']);

        return $db;
    }

    /** @return array<string, int> */
    private function simulate(int $count, int $seed): array
    {
        $start = (new DateTimeImmutable('monday this week'))->modify('-6 weeks');

        return (new Simulator($this->conn, $seed, $start))->run($count);
    }

    /** @return list<array<string, mixed>> */
    private function attempts(int $sessionId): array
    {
        return $this->conn->table('challenge_attempts')->where('session_id', $sessionId)->orderBy('id')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    private function allAttempts(): array
    {
        return $this->conn->table('challenge_attempts')->orderBy('id')->get()->getResultArray();
    }

    /** Sesi terakhir siswa simulasi. */
    private function sessionOf(string $username): int
    {
        return (int) $this->conn->table('game_sessions s')->select('s.id')
            ->join('participants p', 'p.id = s.participant_id')
            ->where('p.username', $username)->orderBy('s.id', 'DESC')
            ->get()->getRow('id');
    }

    private function sessionStatus(string $username): string
    {
        return (string) $this->conn->table('game_sessions')->where('id', $this->sessionOf($username))->get()->getRow('status');
    }

    /** Isi yang ditentukan seed (tanpa uuid dan kode sesi acak). */
    private function fingerprint(): string
    {
        $parts = [
            $this->conn->table('participants')->select('username, display_name, age, gender, school_name_snapshot, pw_weak_submit_count, created_at')->orderBy('id')->get()->getResultArray(),
            $this->conn->table('challenge_attempts')->select('session_id, challenge_node_id, attempt_no, variant_group, status, completion_reason, check_count, hint_count, stars, started_at, completed_at, duration_ms')->orderBy('id')->get()->getResultArray(),
            $this->conn->table('item_tries')->select('challenge_attempt_id, challenge_item_id, try_no, is_correct, answer_json, first_input_ms, last_input_ms, occurred_at')->orderBy('id')->get()->getResultArray(),
            $this->conn->table('game_event_logs')->select('session_id, event_type, sequence_no, occurred_at, payload_json')->orderBy('id')->get()->getResultArray(),
            $this->conn->table('participant_feedback')->select('rating, liked_most, suggestion, submitted_at')->orderBy('id')->get()->getResultArray(),
        ];

        return md5((string) json_encode($parts));
    }
}
