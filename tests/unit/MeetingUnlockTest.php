<?php

use App\Exceptions\ChallengeRuleException;
use App\Models\GameSessionModel;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\FieldWorld;

/**
 * Regions opened per meeting and challenge order in the API (research
 * revision 10, part 2: 28 §9, K17, K19, §19.1 `MeetingUnlockTest`).
 *
 * @internal
 */
final class MeetingUnlockTest extends CIUnitTestCase
{
    use FeatureTestTrait;
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

    public function testEachMeetingOpensItsRegions(): void
    {
        $this->assertSame([], $this->regionsOf(1));
        $this->assertSame(['temanggung'], $this->regionsOf(2));
        // P3 opens Magelang although Temanggung is not completed (11)
        $this->assertSame(['magelang'], $this->regionsOf(3));
        $this->assertSame(['wonosobo'], $this->regionsOf(4));
        $this->assertSame(['temanggung', 'magelang', 'wonosobo'], $this->regionsOf(5));
    }

    public function testTheMapShowsOnlyTheMeetingsRegionOpen(): void
    {
        $fs = $this->fieldSession($this->conn, 3, 'GHJKMN');
        $this->conn->table('game_sessions')->where('id', 1)->update(['field_session_id' => $fs['id'], 'meeting_no' => 3, 'status' => 'active']);
        $this->signIn(1, 1);

        $this->assertRedirectsTo('peta', $this->send(Method::GET, 'dialog/temanggung'), 'Temanggung locked at P3');
        $this->send(Method::GET, 'wilayah/magelang')->assertOK();

        $api = $this->send(Method::POST, 'api/nodes/' . $this->nodeId($this->conn, 'tmg-1') . '/attempts');
        $api->assertStatus(409);
        $this->assertStringContainsString('LEVEL_LOCKED', (string) $api->getBody());
    }

    public function testChallengeOrderHoldsInTheApi(): void
    {
        $fs = $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->conn->table('game_sessions')->where('id', 1)->update(['field_session_id' => $fs['id'], 'meeting_no' => 2, 'status' => 'active']);
        $this->signIn(1, 1);

        $api = $this->send(Method::POST, 'api/nodes/' . $this->nodeId($this->conn, 'tmg-2') . '/attempts');
        $api->assertStatus(409);
        $this->assertStringContainsString('NODE_LOCKED', (string) $api->getBody());

        $this->send(Method::POST, 'api/nodes/' . $this->nodeId($this->conn, 'tmg-1') . '/attempts')->assertOK();

        // The service refuses too, whatever the caller
        $this->expectException(ChallengeRuleException::class);
        service('challengeService')->openNode(model(GameSessionModel::class)->find(1), $this->nodeId($this->conn, 'tmg-3'));
    }

    public function testTheOneSessionPlanRegistersThenOpensTheChosenRegion(): void
    {
        $this->conn->table('research_studies')->update(['meeting_plan' => 'satu_sesi']);
        $fs = $this->fieldSession($this->conn, 1, 'GHJKMN', ['code' => 'S03-A-P1', 'region_code' => 'wonosobo']);

        $entry = service('fieldSessions')->planEntry(model(\App\Models\FieldSessionModel::class)->find($fs['id']));
        $this->assertTrue($entry['register']);
        $this->assertFalse($entry['intro'], 'no opening story');
        $this->assertSame(['wonosobo'], $entry['regions']);

        $this->conn->table('game_sessions')->where('id', 1)->update(['field_session_id' => $fs['id'], 'meeting_no' => 1, 'status' => 'active']);
        $this->assertSame(['wonosobo'], service('fieldSessions')->openRegions(model(GameSessionModel::class)->find(1)));

        // The panel form asks for the region on this plan only
        $this->expectException(\InvalidArgumentException::class);
        service('fieldSessions')->create(['school_id' => 1, 'rombel' => 'B', 'meeting_no' => 1, 'session_date' => '2030-01-15', 'opens' => '07:30', 'closes' => '09:30'], 2);
    }

    /** @return list<string> */
    private function regionsOf(int $meetingNo): array
    {
        $fs = $this->fieldSession($this->conn, $meetingNo, 'TK' . str_repeat((string) ($meetingNo + 1), 4));
        $this->conn->table('game_sessions')->where('id', 1)->update(['field_session_id' => $fs['id'], 'meeting_no' => $meetingNo]);
        $this->resetFieldServices();

        return service('fieldSessions')->openRegions(model(GameSessionModel::class)->find(1));
    }
}
