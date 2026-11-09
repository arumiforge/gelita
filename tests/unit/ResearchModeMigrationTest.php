<?php

use App\Database\Migrations\AddResearchModeData;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Tests\Support\Database\ResetTables;

/**
 * Migration 2026-01-01-004100 (research revision 10, part 7: 33 §9), run on
 * the schema before it: study settings with their defaults, `is_retake`
 * filled for old retakes, the audio offset column, and one segment per
 * existing attempt.
 *
 * @internal
 */
final class ResearchModeMigrationTest extends CIUnitTestCase
{
    use ResetTables;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createResetTables();
        $forge      = Database::forge('tests');
        $forge->dropTable('challenge_attempt_segments', true);
        $forge->dropColumn('research_studies', ['idle_threshold_ms', 'rapid_guess_ms', 'min_stage_px', 'test_rapid_guess_ms']);
        $forge->dropColumn('challenge_attempts', ['is_retake']);
        $forge->dropColumn('audio_usage_events', ['clock_offset_ms']);
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testUpFillsAndDownRemoves(): void
    {
        $this->conn->table('research_studies')->insert(['code' => 'LAMA', 'name' => 'Lama', 'status' => 'active']);
        $this->conn->table('participants')->insertBatch([
            ['participant_code' => 'GLT-000001', 'username' => 'a', 'password_hash' => 'x'],
            ['participant_code' => 'GLT-000002', 'username' => 'b', 'password_hash' => 'x'],
        ]);
        $this->conn->table('field_sessions')->insert([
            'id' => 7, 'study_id' => 1, 'code' => 'S03-A-P2', 'school_id' => 1, 'rombel' => 'A', 'meeting_no' => 2,
            'session_date' => '2026-11-01', 'opens_at' => '2026-11-01 07:30:00', 'closes_at' => '2026-11-01 09:30:00',
        ]);
        $this->conn->table('game_sessions')->insertBatch([
            ['session_code' => 'S1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'paused', 'field_session_id' => null],
            ['session_code' => 'S2', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active', 'field_session_id' => 7],
            ['session_code' => 'S3', 'participant_id' => 2, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active', 'field_session_id' => null],
        ]);
        $attempt = static fn (int $session, int $node, string $status, ?string $done): array => [
            'session_id' => $session, 'challenge_node_id' => $node, 'attempt_no' => 1, 'status' => $status, 'selected_item_ids_json' => '[]',
            'started_at' => '2026-11-01 08:00:00', 'completed_at' => $done,
        ];
        $this->conn->table('challenge_attempts')->insertBatch([
            $attempt(1, 10, 'completed', '2026-11-01 08:05:00'),   // 1: first
            $attempt(2, 10, 'in_progress', null),                   // 2: retake (same student, node 10)
            $attempt(3, 10, 'in_progress', null),                   // 3: another student: no retake
            $attempt(1, 11, 'abandoned', '2026-11-01 08:09:00'),   // 4
            $attempt(2, 11, 'in_progress', null),                   // 5: an abandoned attempt is no completion
        ]);

        $this->migration()->up();

        $study = $this->conn->table('research_studies')->get()->getRowArray();
        $this->assertSame([180000, 3000, 960, 3000], [(int) $study['idle_threshold_ms'], (int) $study['rapid_guess_ms'], (int) $study['min_stage_px'], (int) $study['test_rapid_guess_ms']]);

        $retakes = array_column($this->conn->table('challenge_attempts')->select('id, is_retake')->orderBy('id')->get()->getResultArray(), 'is_retake', 'id');
        $this->assertSame([1 => 0, 2 => 1, 3 => 0, 4 => 0, 5 => 0], array_map('intval', $retakes));

        $this->assertTrue($this->conn->fieldExists('clock_offset_ms', 'audio_usage_events'));

        $segments = $this->conn->table('challenge_attempt_segments')->orderBy('challenge_attempt_id')->get()->getResultArray();
        $this->assertCount(5, $segments);
        $this->assertSame(['1', '1', 'migration'], [(string) $segments[0]['segment_no'], (string) $segments[0]['session_id'], $segments[0]['left_via']]);
        $this->assertStringStartsWith('2026-11-01 08:05:00', (string) $segments[0]['left_at']);
        $this->assertNull($segments[1]['left_at']);
        $this->assertSame('7', (string) $segments[1]['field_session_id']);

        $this->migration()->down();

        $this->assertFalse($this->conn->tableExists('challenge_attempt_segments'));
        $this->assertFalse($this->conn->fieldExists('is_retake', 'challenge_attempts'));
        $this->assertFalse($this->conn->fieldExists('min_stage_px', 'research_studies'));
    }

    private function migration(): AddResearchModeData
    {
        require_once APPPATH . 'Database/Migrations/2026-01-01-004100_AddResearchModeData.php';

        return new AddResearchModeData(Database::forge('tests'));
    }
}
