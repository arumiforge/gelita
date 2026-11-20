<?php

use App\Commands\Simulation;
use App\Libraries\Simulation\FieldSimulator;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\GradeFiveWorld;

/**
 * `gelita:simulasi` in research mode (FieldSimulator, owner 20 November
 * 2026): research schools played one after another through field sessions
 * P1–P5 with the real services (GradeFiveWorld, SQLite).
 *
 * @internal
 */
final class FieldSimulationTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private BaseConnection $conn;
    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $encryption        = config(Encryption::class);
        $this->originalKey = (string) $encryption->key;
        $encryption->key   = 'kunci-uji-simulasi';

        $this->conn = $this->createGradeFiveWorld();
        $this->conn->table('game_sessions')->truncate();
        $this->conn->table('participants')->truncate();
        $this->conn->table('research_studies')->update(['field_mode' => 1, 'unlock_mode' => 'sequential', 'meeting_plan' => 'lima_pertemuan']);
        $this->conn->table('staff_users')->insert(['username' => 'peneliti_uji', 'password_hash' => 'x', 'role' => 'peneliti', 'display_name' => 'Peneliti', 'is_active' => 1]);
        $this->resetGelitaServices();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        $this->resetGelitaServices();
        parent::tearDown();
    }

    public function testRefusesOnlyThePlanItCannotPlay(): void
    {
        $this->assertNull(Simulation::refusal('development'));

        $this->conn->table('research_studies')->update(['meeting_plan' => 'satu_sesi']);
        $this->assertStringContainsString('satu_sesi', (string) Simulation::refusal('development'));
    }

    public function testSchoolsPlayOneAfterAnotherThroughFieldSessions(): void
    {
        $auditBefore = (int) ($this->conn->table('audit_logs')->selectMax('id')->get()->getRow('id') ?? 0);
        $stats       = $this->simulate([3, 2], 11);

        $this->assertSame(2, $stats['sekolah']);
        $this->assertSame($this->conn->table('participants')->countAllResults(), $stats['siswa']);
        $this->assertGreaterThan(0, $stats['tes_awal']);
        $this->assertGreaterThan(0, $stats['tes_akhir']);


        // Research schools with S codes, students coded per school and class group
        $codes = array_column($this->conn->table('participants')->select('participant_code')->orderBy('participant_code')->get()->getResultArray(), 'participant_code');
        $this->assertNotEmpty($codes);
        $this->assertMatchesRegularExpression('/^S\d{2}-A\d{2}$/', $codes[0]);

        // Five main field sessions per class group, all closed, in the past, with their simulated hours
        $sessions = $this->conn->table('field_sessions')->where('makeup_no', 0)->orderBy('id')->get()->getResultArray();
        $this->assertCount(10, $sessions);

        foreach ($sessions as $row) {
            $this->assertNotNull($row['closed_at']);
            $this->assertLessThan(date('Y-m-d'), $row['session_date']);
            $this->assertSame($row['session_date'], substr((string) $row['opens_at'], 0, 10));
        }

        // One school after the other: the second school's P1 comes after the first school's P5
        $bySchool = [];

        foreach ($sessions as $row) {
            $bySchool[$row['school_id']][(int) $row['meeting_no']] = $row['session_date'];
        }

        [$first, $second] = array_values($bySchool);
        $this->assertGreaterThan($first[5], $second[1]);

        // Pre- and post-tests sent on the simulation clock
        $tests = $this->conn->table('instrument_sittings')->where('kind', 'test')->get()->getResultArray();
        $this->assertNotEmpty($tests);

        foreach ($tests as $sitting) {
            $this->assertLessThan(date('Y-m-d'), substr((string) $sitting['started_at'], 0, 10));
        }

        // Nothing from the run is left on the wall clock (audit rows: only those the run wrote; the world's setup keeps its own)
        foreach (['game_sessions' => 'started_at', 'challenge_attempts' => 'started_at', 'game_event_logs' => 'occurred_at'] as $table => $column) {
            $this->assertSame(0, $this->conn->table($table)->where($column . ' >=', date('Y-m-d'))->countAllResults(), $table);
        }

        $this->assertGreaterThan(0, $this->conn->table('audit_logs')->where('id >', $auditBefore)->countAllResults());
        $this->assertSame(0, $this->conn->table('audit_logs')->where('id >', $auditBefore)->where('occurred_at >=', date('Y-m-d'))->countAllResults(), 'audit rows of the run');
        $this->assertSame(0, $this->conn->table('audit_logs')->where('id <=', $auditBefore)->where('occurred_at <', date('Y-m-d', strtotime('-1 day')))->countAllResults(), 'earlier audit rows untouched');
    }

    /**
     * @param list<int> $sizes
     *
     * @return array<string, int>
     */
    private function simulate(array $sizes, int $seed): array
    {
        $start = (new DateTimeImmutable('monday this week'))->modify('-' . FieldSimulator::weeksNeeded(count($sizes)) . ' weeks');

        return (new FieldSimulator($this->conn, $seed, $start))->runSchools($sizes);
    }

    private function resetGelitaServices(): void
    {
        foreach (['fieldSessions', 'instruments', 'instrumentGate', 'instrumentRepository', 'sessionService', 'challengeService', 'eventService', 'contentRepository', 'consentService', 'participantCodes'] as $name) {
            \Config\Services::resetSingle($name);
        }
    }
}
