<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * Who is offered the online-risk items (research revision 10, part 4: 30 §5.4,
 * K6, §19.1 `RiskItemsGateTest`). The T11/T12 screens and `risk_reconsent`
 * are the test module's (part 8, 34 §8).
 *
 * @internal
 */
final class RiskItemsGateTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use InstrumentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createInstrumentWorld();
    }

    protected function tearDown(): void
    {
        $this->dropInstrumentWorld();
        parent::tearDown();
    }

    public function testOnlyConsentYesAndStatusActive(): void
    {
        $consents = service('consentService');
        $this->assertFalse($consents->riskItemsAllowed(1), 'no row yet: not offered');

        $this->consent($this->conn, 1);
        $this->assertFalse($consents->riskItemsAllowed(1), 'K6: No by default');

        $consents->apply(1, 1, ['risk_items_consent' => '1'], 2);
        $this->assertTrue($consents->riskItemsAllowed(1));

        $consents->apply(1, 1, ['status' => 'left', 'status_on' => date('Y-m-d')], 2);
        $this->assertFalse($consents->riskItemsAllowed(1), 'status active only');
    }

    public function testTestAccountsGetRiskConsentYes(): void
    {
        $this->conn->table('participants')->where('id', 1)->update(['username' => 'uji.lama']);
        $created = service('testAccounts')->create(['uji.bahasa1'])[0];
        $id      = (int) $this->conn->table('participants')->where('username', $created['username'])->get()->getRow('id');

        $this->assertTrue(service('consentService')->riskItemsAllowed($id), 'validators see T11 on the test install (28 §13)');
    }

    public function testTheT11AndT12ScreensComeWithTheTestModule(): void
    {
        service('consentService')->apply(1, 1, ['risk_items_consent' => '1'], 2);
        $this->meeting($this->conn, 5);
        $version = (int) $this->conn->table('instrument_versions')->where('instrument', 'tes')->get()->getRow('id');
        $this->conn->table('instrument_sittings')->insert(['participant_id' => 1, 'study_id' => 1, 'occasion' => 'post', 'kind' => 'test', 'form' => 'B', 'version_id' => $version, 'started_at' => date('Y-m-d H:i:s'), 'submitted_at' => date('Y-m-d H:i:s')]);

        $this->assertRedirectsTo('tes/tambahan', $this->go(Method::GET, 'tes'));
        $page = (string) $this->go(Method::GET, 'tes/tambahan')->getBody();
        $this->assertStringContainsString(esc(lang('Game.riskAskTitle')), $page);
        $this->assertSame(2, substr_count($page, 'class="btn btn-secondary btn-lg flex-fill"'), 'two buttons of the same size and colour');

        $this->go(Method::POST, 'tes/tambahan', ['jawab' => 'ya']);
        $risk = $this->sittingRow($this->conn, 'post', 'risk');
        $this->assertSame([1, 1], [(int) $risk['risk_offered'], (int) $risk['risk_reconsent']]);
        $this->assertNotNull($risk['risk_reconsent_at']);

        // risk_reconsent is written once
        $this->go(Method::POST, 'tes/tambahan', ['jawab' => 'tidak']);
        $this->assertSame(1, (int) $this->sittingRow($this->conn, 'post', 'risk')['risk_reconsent']);

        $this->assertRedirectsTo('tes/tambahan/1', $this->go(Method::GET, 'tes'));
        $this->assertStringContainsString(esc(lang('Game.riskThanksTitle')), (string) $this->go(Method::GET, 'tes/pesan')->getBody());
    }
}
