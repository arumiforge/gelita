<?php

use App\Entities\ChallengeAttempt;
use App\Exceptions\ChallengeRuleException;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * An open first attempt moves to the student's new game session (research
 * revision 10, part 7: 33 §7.3, K3, §13.1 `AttemptResumeAcrossSessionsTest`).
 *
 * @internal
 */
final class AttemptResumeAcrossSessionsTest extends CIUnitTestCase
{
    use BankPlay;

    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->originalKey                 = (string) config(Encryption::class)->key;
        config(Encryption::class)->key     = 'kunci-uji-lanjut';
        $this->conn                        = $this->createGradeFiveWorld();
        $this->service                     = new ChallengeService();

        // Session 2 of the same student (the next meeting)
        $this->conn->table('game_sessions')->insert([
            'session_code' => 'S-BARU', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        Factories::reset('models');
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testTheOpenAttemptMovesAndKeepsItsClosedItemsAndTries(): void
    {
        $attempt = $this->open('tmg-4');
        $clues   = $this->itemsOfType($attempt, 'find_object');
        $decoy   = $this->itemsOfType($attempt, 'find_object', true)[0];

        $this->tapObject($attempt, $clues[0], $clues[0]);               // found: closed
        $first = $this->tapObject($attempt, $clues[1], $decoy);          // wrong tap: try 1
        $this->assertSame(1, $first['try_no']);
        $this->conn->table('game_sessions')->where('id', 1)->update(['last_active_at' => '2026-11-04 09:10:00']);

        $opened = $this->service->openNode($this->session(2), $attempt->challenge_node_id);

        $this->assertSame($attempt->id, $opened['attempt']->id, 'the same attempt, not a new one');
        $this->assertSame(2, $this->reload($attempt)->session_id);
        $this->assertSame(1, $this->conn->table('challenge_attempts')->countAllResults());

        // The found clue stays found; the next try of the other clue is try 2
        try {
            $this->tapObject($attempt, $clues[0], $clues[0]);
            $this->fail('closed clue reopened');
        } catch (ChallengeRuleException $e) {
            $this->assertSame('CLUE_CLOSED', $e->errorCode);
        }

        $this->assertSame(2, $this->tapObject($attempt, $clues[1], $decoy)['try_no']);

        $moved = $this->conn->table('game_event_logs')->where('session_id', 2)->where('event_type', 'challenge_opened')->get()->getResultArray();
        $this->assertCount(1, $moved);
        $payload = json_decode((string) $moved[0]['payload_json'], true);
        $this->assertTrue($payload['resumed']);
        $this->assertSame('S-KELAS5', $payload['moved_from_session']);

        $segments = $this->conn->table('challenge_attempt_segments')->where('challenge_attempt_id', $attempt->id)->orderBy('segment_no')->get()->getResultArray();
        $this->assertSame([[1, '1', 'session_ended'], [2, '2', null]], array_map(static fn (array $s): array => [(int) $s['segment_no'], (string) $s['session_id'], $s['left_via']], $segments));
        $this->assertStringStartsWith('2026-11-04 09:10:00', (string) $segments[0]['left_at'], 'closed at the old session\'s last activity');
    }

    public function testTwoRequestsAtOnceMoveTheAttemptOnce(): void
    {
        $attempt = $this->open('tmg-2');
        $stale   = $this->reload($attempt);                              // read before the other request moved it

        $this->service->openNode($this->session(2), $attempt->challenge_node_id);

        // The second request still reads the attempt in session 1
        $mock = new class () extends ChallengeAttemptModel {
            public ?ChallengeAttempt $stale = null;

            public function openForParticipant(int $participantId, int $nodeId, ?int $studyId): ?ChallengeAttempt
            {
                return $this->stale;
            }
        };
        $mock->stale = $stale;
        Factories::injectMock('models', ChallengeAttemptModel::class, $mock);

        $again = $this->service->openNode($this->session(2), $attempt->challenge_node_id);
        Factories::reset('models');

        $this->assertSame($attempt->id, $again['attempt']->id);
        $this->assertSame(2, $this->reload($attempt)->session_id);
        $this->assertSame(1, $this->conn->table('game_event_logs')->where('event_type', 'challenge_opened')->like('payload_json', 'moved_from_session')->countAllResults());
        $this->assertSame(2, $this->conn->table('challenge_attempt_segments')->where('challenge_attempt_id', $attempt->id)->countAllResults());
    }

    public function testAnotherStudentNeverTakesIt(): void
    {
        $attempt = $this->open('tmg-2');
        $other   = $this->newStudent(7);

        $opened = $this->service->openNode($other, $attempt->challenge_node_id);

        $this->assertNotSame($attempt->id, $opened['attempt']->id);
        $this->assertSame(1, $this->reload($attempt)->session_id);
    }

    public function testAttemptNumbersDoNotRepeatAcrossSessions(): void
    {
        $attempt = $this->open('tmg-2');
        $this->service->abandonAttempt($attempt);

        $opened = $this->service->openNode($this->session(2), $attempt->challenge_node_id);

        $this->assertSame(1, $attempt->attempt_no);
        $this->assertSame(2, $opened['attempt']->attempt_no, 'counted per student, not per session');
        $this->assertSame(0, (int) $opened['attempt']->is_retake, 'abandoned is not completed');
    }

    public function testLateEventsOfTheOldSessionLoseTheAttemptReference(): void
    {
        $attempt = $this->open('tmg-2');
        $this->service->openNode($this->session(2), $attempt->challenge_node_id);

        $result = service('eventService')->ingest(model(GameSessionModel::class)->find(1), [
            ['event_type' => 'reading_progress', 'client_event_id' => 'late-1', 'occurred_at' => '2026-11-04T02:00:00.000Z', 'attempt_id' => $attempt->id, 'payload' => ['percent' => 50]],
        ]);

        $this->assertSame(1, $result['accepted']);
        $this->assertNull($this->conn->table('game_event_logs')->where('client_event_id', 'late-1')->get()->getRow('challenge_attempt_id'));
    }
}
