<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * Kritik dan saran during a study (research revision 10, part 8: 34 §9.4,
 * §3; §15.1 `ReflectionFeedbackStudyTest`).
 *
 * @internal
 */
final class ReflectionFeedbackStudyTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use InstrumentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createInstrumentWorld();
        $this->conn->table('participant_progress')->ignore(true)->insert(['participant_id' => 1, 'study_id' => 1]);
        $this->conn->table('participant_progress')->where('participant_id', 1)->update(['completed_nodes' => 99]);
    }

    protected function tearDown(): void
    {
        $this->dropInstrumentWorld();
        parent::tearDown();
    }

    public function testDuringAStudyEveryFieldMayStayEmpty(): void
    {
        $this->meeting($this->conn, 5);
        $now = date('Y-m-d H:i:s');

        foreach (['test' => 1, 'risk' => 2, 'attitude' => 3] as $kind => $version) {
            $this->conn->table('instrument_sittings')->insert(['participant_id' => 1, 'study_id' => 1, 'occasion' => $kind === 'attitude' ? 'p5' : 'post', 'kind' => $kind, 'version_id' => $version, 'risk_offered' => $kind === 'risk' ? 0 : null, 'started_at' => $now, 'submitted_at' => $now]);
        }

        $this->conn->table('game_event_logs')->insert(['event_uuid' => 'e-end', 'client_event_id' => 'end', 'session_id' => 1, 'participant_id' => 1, 'event_type' => 'dialogue_completed', 'payload_json' => '{"context":"ending"}', 'occurred_at' => $now]);

        $page = (string) $this->go(Method::GET, 'refleksi')->getBody();
        $this->assertStringContainsString(esc(lang('Game.reflectionAllOptional')), $page);
        $this->assertStringContainsString('data-min-answers="0"', $page);

        $this->assertRedirectsTo('refleksi', $this->go(Method::POST, 'refleksi', []));
        $row = $this->conn->table('participant_feedback')->get()->getRowArray();
        $this->assertNotNull($row, 'an all-empty form is still one row');
        $this->assertNull($row['rating']);

        $event = $this->conn->table('game_event_logs')->where('event_type', 'feedback_submitted')->get()->getRowArray();
        $this->assertSame(['rating' => null, 'open_answers' => 0], json_decode($event['payload_json'], true));
    }

    public function testOutsideFieldModeTheOldRulesStay(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $this->signIn(1, 1);

        $this->assertStringContainsString('data-min-answers="2"', (string) $this->go(Method::GET, 'refleksi')->getBody());
        $this->go(Method::POST, 'refleksi', ['liked_most' => 'Seru', 'hardest_part' => 'Cari Objek']);
        $this->assertArrayHasKey('rating', $_SESSION['errors'] ?? [], 'stars still required (§3)');
        $this->go(Method::POST, 'refleksi', ['rating' => '4', 'liked_most' => 'Seru']);
        $this->assertSame(lang('Game.reflectionNeedTwo'), $_SESSION['errors']['liked_most'] ?? null);
        $this->assertSame(0, $this->conn->table('participant_feedback')->countAllResults());

        $this->go(Method::POST, 'refleksi', ['rating' => '4', 'liked_most' => 'Seru', 'suggestion' => 'Lagi']);
        $this->assertSame(4, (int) $this->conn->table('participant_feedback')->get()->getRow('rating'));
    }
}
