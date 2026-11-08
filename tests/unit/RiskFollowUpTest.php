<?php

use App\Services\RiskFollowUpService;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * Follow-up flags of the online-risk items (research revision 10, part 4:
 * 30 §6, K7, §19.1 `RiskFollowUpTest`). The answers sit in minimal stand-ins
 * for the test module's tables (part 8, 34 §11.1).
 *
 * @internal
 */
final class RiskFollowUpTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->createRiskTables($this->conn);
        $fs  = $this->fieldSession($this->conn, 5, 'GHJKMN')['id'];
        $two = $this->student($this->conn, 'S03-A02', 'dewi');
        $tri = $this->student($this->conn, 'S03-A03', 'eko');
        $this->consent($this->conn, 1);
        $this->riskAnswers($this->conn, 1, '2', '0', $fs);
        $this->riskAnswers($this->conn, $two, '1', '1', $fs);
        $this->riskAnswers($this->conn, $tri, '0', '1', $fs);
        $this->riskAnswers($this->conn, $tri, '2', '0', $fs, 1);   // preview answers never count
    }

    protected function tearDown(): void
    {
        $this->dropRiskTables();
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testRuleTl1(): void
    {
        $this->assertSame('tl-1', RiskFollowUpService::RULE_VERSION);
        $this->assertNull(RiskFollowUpService::evaluate('0', true));
        $this->assertNull(RiskFollowUpService::evaluate('9', false));
        $this->assertNull(RiskFollowUpService::evaluate('7', true));
        $this->assertSame(['priority' => false], RiskFollowUpService::evaluate('1', false));
        $this->assertSame(['priority' => true], RiskFollowUpService::evaluate('1', true));
        $this->assertSame(['priority' => true], RiskFollowUpService::evaluate('2', false));
    }

    public function testHeadResearcherSeesCodesNotAnswersAndTheAuditHasNoCodes(): void
    {
        $this->asStaff('ketua');
        $this->assertStringContainsString(lang('Admin.menuRiskFollowUp'), (string) $this->send(Method::GET, 'admin')->getBody(), 'menu item while field mode is on');
        $response = $this->send(Method::GET, 'admin/tindak-lanjut');
        $page     = (string) $response->getBody();

        $this->assertStringContainsString(esc(lang('Admin.riskIntro')), $page);
        $this->assertStringContainsString('S03-A01', $page);
        $this->assertStringContainsString('S03-A02', $page);
        $this->assertStringNotContainsString('S03-A03', $page);
        $this->assertStringNotContainsString('risk_upset', $page);
        $this->assertStringContainsString('no-store', $response->response()->getHeaderLine('Cache-Control'));

        $groups = service('riskFollowUp')->flags(1);
        $this->assertSame(['S03-A01' => true, 'S03-A02' => true], array_column($groups[0]['rows'], 'priority', 'participant_code'));

        $audit = (string) $this->conn->table('audit_logs')->where('action', 'risk_flags_viewed')->get()->getRow('metadata_json');
        $this->assertSame(['field_session_code' => 'S03-A-P5', 'count' => 2, 'rule' => 'tl-1'], json_decode($audit, true));
        $this->assertStringNotContainsString('S03-A0', $audit);
    }

    public function testOnlyTheHeadResearcher(): void
    {
        $this->asStaff('peneliti');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->send(Method::GET, 'admin/tindak-lanjut');
    }

    public function testAClosedStudyShowsNoFlags(): void
    {
        $this->conn->table('research_studies')->update(['status' => 'closed']);

        $this->asStaff('ketua');
        $page = (string) $this->send(Method::GET, 'admin/tindak-lanjut')->getBody();
        $this->assertStringContainsString(esc(lang('Admin.riskStudyClosed')), $page);
        $this->assertStringNotContainsString('S03-A01', $page);
    }

    public function testDeletedAnswersFlagNoMore(): void
    {
        $id = $this->fileRequest('risk_items', 'ketua');
        service('retentionService')->execute($id, 1);

        $codes = array_column(service('riskFollowUp')->flags(1)[0]['rows'], 'participant_code');
        $this->assertSame(['S03-A02'], $codes);
    }
}
