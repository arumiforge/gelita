<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ConsentWorld;

/**
 * What each kind of request deletes (research revision 10, part 4: 30 §7.2,
 * §7.3, K9, §19.1 `DataRequestScopeTest`).
 *
 * @internal
 */
final class DataRequestScopeTest extends CIUnitTestCase
{
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->consent($this->conn, 1, ['risk_items_consent' => 1, 'recording_consent' => 1]);
        $this->playData($this->conn, 1, 1);
        $this->conn->table('participant_seats')->insert(['participant_id' => 1, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 1, 'booklet' => 'B1', 'pre_form' => 'A', 'post_form' => 'B']);
        // A second student whose data must stay
        $other = $this->student($this->conn, 'S03-A02', 'dewi');
        $this->consent($this->conn, $other);
        $this->playData($this->conn, $other, (int) $this->conn->table('game_sessions')->where('participant_id', $other)->get()->getRow('id'));
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testAllDeletesEveryTableOfTheStudentAndKeepsTheCode(): void
    {
        $id     = $this->fileRequest('all', 'ketua');
        $counts = service('retentionService')->decode($this->requestRow($id))['counts'];

        foreach (['participant_consents', 'participant_progress', 'participant_seats', 'participant_feedback', 'item_tries', 'game_sessions', 'participants'] as $table) {
            $this->assertSame(1, $counts[$table], $table);
        }

        service('retentionService')->execute($id, 1);

        foreach (['participants' => 'id', 'participant_consents' => 'participant_id', 'participant_seats' => 'participant_id', 'game_sessions' => 'participant_id'] as $table => $column) {
            $this->assertSame(0, $this->conn->table($table)->where($column, 1)->countAllResults(), $table);
        }

        $this->assertSame(1, $this->conn->table('participants')->countAllResults(), 'the other student stays');
        $this->assertSame('S03-A01', $this->requestRow($id)['participant_code']);
    }

    public function testRiskItemsWithoutAnswersOnlyChangesTheConsent(): void
    {
        $this->assertSame(0, $this->fileRequest('risk_items'));
        $row = service('consentService')->forParticipant(1);
        $this->assertSame([0, date('Y-m-d', time() - 7200)], [(int) $row['risk_items_consent'], $row['risk_items_withdrawn_on']]);
        $this->assertSame(0, $this->conn->table('data_deletion_requests')->countAllResults());
    }

    public function testRiskItemsDeletesTheRiskSittingOnly(): void
    {
        $this->riskAnswers($this->conn, 1, '2', '1');
        $this->conn->table('instrument_sittings')->insert(['participant_id' => 1, 'study_id' => 1, 'occasion' => 'pre', 'kind' => 'test', 'version_id' => 1, 'started_at' => date('Y-m-d H:i:s')]);

        $id = $this->fileRequest('risk_items', 'ketua');
        $this->assertSame(['instrument_responses' => 9, 'instrument_parts' => 1, 'instrument_blocks' => 0, 'instrument_sittings' => 1], service('retentionService')->decode($this->requestRow($id))['counts']);

        service('retentionService')->execute($id, 1);

        $this->assertSame(0, $this->conn->table('instrument_responses')->countAllResults());
        $this->assertSame(['test'], array_column($this->conn->table('instrument_sittings')->get()->getResultArray(), 'kind'), 'the test sitting stays');
        $this->assertSame(1, $this->conn->table('participants')->where('id', 1)->countAllResults());
        $this->assertSame(2, $this->conn->table('game_sessions')->countAllResults(), 'play data stays');
    }

    public function testRecordingDeletesNothingOnTheServer(): void
    {
        $id = $this->fileRequest('recording');
        service('dataRequests')->confirm($id, 3);

        $row = $this->requestRow($id);
        $this->assertSame(['executed', 0], [$row['status'], (int) $row['affected_count']]);
        $this->assertSame(0, (int) service('consentService')->forParticipant(1)['recording_consent']);
        $this->assertSame(1, $this->conn->table('participants')->where('id', 1)->countAllResults());
    }
}
