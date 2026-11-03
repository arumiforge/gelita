<?php

use App\Models\GameSessionModel;
use App\Models\ParticipantProgressModel;
use App\Services\ChallengeService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\FieldWorld;

/**
 * Progress follows the student, not the game session (research revision
 * 10, part 2: 28 §8, K6, §19.1 `ProgressAcrossMeetingsTest`).
 *
 * @internal
 */
final class ProgressAcrossMeetingsTest extends CIUnitTestCase
{
    use FieldWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createFieldWorld();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testAChallengeDoneAtP2StaysDoneAtP5(): void
    {
        $p2 = $this->fieldSession($this->conn, 2, 'GHJKMN', ['closed_at' => date('Y-m-d H:i:s')]);
        $p5 = $this->fieldSession($this->conn, 5, 'PQRSTU');
        $this->conn->table('game_sessions')->insertBatch([
            ['session_code' => 'P2', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'field_session_id' => $p2['id'], 'meeting_no' => 2, 'status' => 'paused'],
            ['session_code' => 'P5', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'field_session_id' => $p5['id'], 'meeting_no' => 5, 'status' => 'active'],
        ]);
        [$atP2, $atP5] = [model(GameSessionModel::class)->findByCode('P2'), model(GameSessionModel::class)->findByCode('P5')];

        $node = $this->nodeId($this->conn, 'tmg-1');
        $this->conn->table('challenge_attempts')->insert([
            'session_id' => $atP2->id, 'challenge_node_id' => $node, 'attempt_no' => 1, 'status' => 'completed', 'stars' => 3, 'selected_item_ids_json' => '[]',
        ]);
        $this->refresh($atP2, $node);

        $summary = service('scoringService')->levelSummary($atP5->id, (int) service('contentRepository')->level('temanggung')->id);
        $this->assertSame([1, 3], [$summary['completed_nodes'], $summary['stars']]);

        $row = model(ParticipantProgressModel::class)->forSession($atP5);
        $this->assertSame([1, 3], [(int) $row['completed_nodes'], (int) $row['total_stars']]);
        $this->assertSame(1, $this->conn->table('participant_progress')->where('participant_id', 1)->countAllResults(), 'one row per student and study');

        // Challenge 2 is open at P5 because challenge 1 was done at P2
        $this->assertTrue(isset(model(\App\Models\ChallengeAttemptModel::class)->completedFor($atP5)[$node]));
    }

    public function testLoggingInAfterACompletedSessionKeepsTheProgress(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $pid  = $this->student($this->conn, 'GLT-000009', 'sekar_ayu', 1);   // one completed session already
        $done = (int) $this->conn->table('game_sessions')->where('participant_id', $pid)->get()->getRow('id');
        $node = $this->nodeId($this->conn, 'tmg-1');
        $this->conn->table('challenge_attempts')->insert([
            'session_id' => $done, 'challenge_node_id' => $node, 'attempt_no' => 1, 'status' => 'completed', 'stars' => 2, 'selected_item_ids_json' => '[]',
        ]);

        $login = service('sessionService')->login('sekar_ayu', 'Kopi-Kedu7!', 'id', []);

        $this->assertSame('ok', $login['status']);
        $this->assertNotSame($done, $login['session']->id, 'a new game session');
        $this->assertSame(2, service('scoringService')->totalStars($login['session']->id), 'not starting from empty');
    }

    private function refresh(\App\Entities\GameSession $session, int $nodeId): void
    {
        $method = new ReflectionMethod(ChallengeService::class, 'refreshProgress');
        $method->invoke(service('challengeService'), $session, service('contentRepository')->node($nodeId));
    }
}
