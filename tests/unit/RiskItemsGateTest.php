<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ConsentWorld;

/**
 * Who is offered the online-risk items (research revision 10, part 4: 30 §5.4,
 * K6, §19.1 `RiskItemsGateTest`). The T11/T12 screens and `risk_reconsent`
 * come with the test module (part 8, 34 §8); those checks are skipped until then.
 *
 * @internal
 */
final class RiskItemsGateTest extends CIUnitTestCase
{
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
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
        $this->assertNotSame('', lang('Game.riskAskTitle'));
        $this->assertNotSame('', lang('Game.riskThanksTitle'));
        $this->markTestSkipped('T11/T12 screens and risk_reconsent are built by the test module (part 8, 34 §8).');
    }
}
