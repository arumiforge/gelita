<?php

use App\Services\ChallengeService;
use App\Services\RetentionService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * Open attempts of a field session wait for the next meeting while the study
 * runs, and are closed when the study is closed (owner, 20 November 2026).
 *
 * @internal
 */
final class FieldAttemptRetentionTest extends CIUnitTestCase
{
    use BankPlay;

    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->originalKey             = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-retensi';
        $this->conn                    = $this->createGradeFiveWorld();
        $this->service                 = new ChallengeService();
    }

    protected function tearDown(): void
    {
        Factories::reset('models');
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testAFieldSessionAttemptSurvivesTheNightlyRunWhileTheStudyRuns(): void
    {
        $attempt = $this->staleAttempt(true);

        $this->assertSame(0, (new RetentionService())->abandonStaleAttempts(24));
        $this->assertSame('in_progress', $this->reload($attempt)->status);
    }

    public function testAnAttemptOutsideAFieldSessionIsStillAbandoned(): void
    {
        $attempt = $this->staleAttempt(false);

        $this->assertSame(1, (new RetentionService())->abandonStaleAttempts(24));
        $this->assertSame('abandoned', $this->reload($attempt)->status);
    }

    public function testTheNightlyRunClosesItOnceTheStudyIsNoLongerRunning(): void
    {
        $attempt = $this->staleAttempt(true);
        $this->conn->table('research_studies')->where('id', 1)->update(['status' => 'closed']);

        $this->assertSame(1, (new RetentionService())->abandonStaleAttempts(24));
        $this->assertSame('abandoned', $this->reload($attempt)->status);
    }

    public function testClosingTheStudyClosesItsOpenFieldAttemptsAtOnce(): void
    {
        $field = $this->staleAttempt(true);

        $this->assertSame(1, (new RetentionService())->abandonOpenStudyAttempts(1));
        $this->assertSame('abandoned', $this->reload($field)->status);
        $this->assertSame(0, (int) $this->reload($field)->stars);

        $event   = $this->conn->table('game_event_logs')->where('event_type', 'challenge_abandoned')->get()->getRowArray();
        $payload = json_decode((string) $event['payload_json'], true);
        $this->assertSame('retention', $payload['via']);
        $this->assertSame('study_closed', $payload['reason']);
        $this->assertSame('retention', $this->conn->table('challenge_attempt_segments')->where('challenge_attempt_id', $field->id)->get()->getRowArray()['left_via']);
    }

    /** An open attempt last touched two days ago, in a game session bound to a field session or not. */
    private function staleAttempt(bool $inFieldSession): \App\Entities\ChallengeAttempt
    {
        $attempt = $this->open('tmg-2');
        $old     = date('Y-m-d H:i:s', time() - 2 * 86400);

        if ($inFieldSession) {
            $this->conn->table('field_sessions')->insert([
                'study_id' => 1, 'code' => 'U01-A-P1', 'school_id' => 1, 'rombel' => 'A', 'meeting_no' => 1,
                'session_date' => substr($old, 0, 10), 'opens_at' => $old, 'closes_at' => $old,
            ]);
            $this->conn->table('game_sessions')->where('id', 1)->update(['field_session_id' => $this->conn->insertID()]);
        }

        $this->conn->table('challenge_attempts')->where('id', $attempt->id)->update(['started_at' => $old]);
        $this->conn->table('game_event_logs')->where('challenge_attempt_id', $attempt->id)->update(['server_received_at' => $old]);

        return $attempt;
    }
}
