<?php

use App\Services\StaffScope;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * T11, ang-21–ang-23, and T12 (research revision 10, part 8: 34 §8; 30 §5.4,
 * §7.3; §15.1 `RiskItemsFlowTest`).
 *
 * @internal
 */
final class RiskItemsFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use InstrumentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createInstrumentWorld();
        $this->conn->table('instrument_sittings')->insert(['participant_id' => 1, 'study_id' => 1, 'occasion' => 'post', 'kind' => 'test', 'form' => 'B', 'version_id' => 1, 'started_at' => date('Y-m-d H:i:s'), 'submitted_at' => date('Y-m-d H:i:s')]);
    }

    protected function tearDown(): void
    {
        $this->dropInstrumentWorld();
        parent::tearDown();
    }

    public function testWithoutConsentStraightToT12(): void
    {
        $this->meeting($this->conn, 5);
        $this->assertRedirectsTo('tes/pesan', $this->go(Method::GET, 'tes'));
        $this->assertRedirectsTo('tes', $this->go(Method::GET, 'tes/tambahan'), 'no T11');
        $this->go(Method::GET, 'tes/pesan');

        $risk = $this->sittingRow($this->conn, 'post', 'risk');
        $this->assertSame([0, null], [(int) $risk['risk_offered'], $risk['risk_reconsent']]);
        $this->assertNotNull($risk['submitted_at']);
        $this->assertSame(0, $this->conn->table('instrument_responses')->where('sitting_id', $risk['id'])->countAllResults());
    }

    public function testTidakGoesToT12AndYaAnswersTheItems(): void
    {
        service('consentService')->apply(1, 1, ['risk_items_consent' => '1'], 2);
        $this->meeting($this->conn, 5);
        $this->go(Method::POST, 'tes/tambahan', ['jawab' => 'tidak']);
        $this->assertRedirectsTo('tes/pesan', $this->go(Method::GET, 'tes'));
        $this->assertSame(0, (int) $this->sittingRow($this->conn, 'post', 'risk')['risk_reconsent']);

        // Another student says Ya
        $two = $this->student($this->conn, 'S03-A02', 'dewi');
        $this->consent($this->conn, $two, ['risk_items_consent' => 1]);
        $this->conn->table('participant_seats')->insert(['participant_id' => $two, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 2, 'booklet' => 'B2', 'pre_form' => 'B', 'post_form' => 'A']);
        $this->conn->table('instrument_sittings')->insert(['participant_id' => $two, 'study_id' => 1, 'occasion' => 'post', 'kind' => 'test', 'form' => 'A', 'version_id' => 1, 'started_at' => date('Y-m-d H:i:s'), 'submitted_at' => date('Y-m-d H:i:s')]);
        $session = (int) $this->conn->table('game_sessions')->where('participant_id', $two)->get()->getRow('id');
        $this->conn->table('game_sessions')->where('id', $session)->update(['field_session_id' => (int) $this->conn->table('field_sessions')->get()->getRow('id'), 'status' => 'active']);
        $this->signIn($two, $session);

        $this->go(Method::POST, 'tes/tambahan', ['jawab' => 'ya']);
        $this->go(Method::GET, 'tes/tambahan/1');
        $this->assertRedirectsTo('tes/tambahan/2', $this->go(Method::POST, 'tes/tambahan/1', ['a' => ['risk_upset' => '7']]));
        $this->go(Method::POST, 'tes/tambahan/2', ['a' => ['risk_stranger_contact' => '1']]);
        $this->assertRedirectsTo('tes/pesan', $this->go(Method::POST, 'tes/tambahan/3', ['a' => ['support_parent' => '1'], 'm' => ['ang-23' => '1']]));
        $this->go(Method::GET, 'tes/pesan');

        $risk    = $this->sittingRow($this->conn, 'post', 'risk', $two);
        $answers = array_column($this->conn->table('instrument_responses')->where('sitting_id', $risk['id'])->get()->getResultArray(), 'response', 'unit');
        $this->assertSame(['7', '1', '1', '0'], [$answers['risk_upset'], $answers['risk_stranger_contact'], $answers['support_parent'], $answers['support_none']]);
        $this->assertNotNull($risk['submitted_at']);

        // Never in the raw data or per-student sheets
        $scope = StaffScope::of(true, true);
        foreach (['peserta', 'tes_jawaban', 'tes_bagian', 'angket', 'sikap'] as $sheet) {
            foreach (service('rawDataService')->rows($sheet, ['study_id' => 1], $scope) as $row) {
                $this->assertArrayNotHasKey('risk_upset', $row, $sheet);
            }
        }

        // The risk_items scope deletes the risk sitting only (30 §7.3)
        $this->asStaff('ketua');
        $id = $this->fileRequest('risk_items', 'ketua', 'S03-A02');
        service('retentionService')->execute($id, 1);
        $this->assertNull($this->sittingRow($this->conn, 'post', 'risk', $two));
        $this->assertNotNull($this->sittingRow($this->conn, 'post', 'test', $two), 'the test stays');
    }
}
