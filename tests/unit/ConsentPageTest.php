<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * Coded consent per student (research revision 10, part 4: 30 §5.2, §19.1 `ConsentPageTest`).
 *
 * @internal
 */
final class ConsentPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    private int $fs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->fs   = $this->fieldSession($this->conn, 2, 'GHJKMN')['id'];
        $this->asStaff('peneliti');
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testTheResearcherFillsConsentByCodeWithNoDefaultsYes(): void
    {
        $page = (string) $this->send(Method::GET, 'admin/sesi-lapangan/' . $this->fs . '/izin')->getBody();
        $this->assertStringContainsString('S03-A01', $page);
        $this->assertStringNotContainsString('Jaka', $page, 'codes only, never the name');
        $this->assertStringContainsString(esc(lang('Admin.consentMissing', [1])), $page);

        $this->post1(['parent_consent_on' => date('Y-m-d', strtotime('-3 days'))]);

        $row = service('consentService')->forParticipant(1);
        $this->assertSame([0, 0, 'active'], [(int) $row['recording_consent'], (int) $row['risk_items_consent'], $row['status']], 'K6: No by default');
        $this->assertSame(0, $this->conn->table('audit_logs')->where('action', 'consent_updated')->countAllResults(), 'the first save is not a change');
        $this->assertStringNotContainsString(esc(lang('Admin.consentMissing', [1])), (string) $this->send(Method::GET, 'admin/sesi-lapangan/' . $this->fs . '/izin')->getBody());
    }

    public function testWithdrawingAConsentNeedsADateAndIsAudited(): void
    {
        $this->consent($this->conn, 1, ['recording_consent' => 1]);

        $this->post1(['recording_consent' => '0']);
        $this->assertStringContainsString(lang('Admin.consentWithdrawnDate'), (string) ($_SESSION['error'] ?? ''));
        $this->assertSame(1, (int) service('consentService')->forParticipant(1)['recording_consent']);

        $this->post1(['recording_consent' => '0', 'recording_withdrawn_on' => date('Y-m-d')]);
        $row = service('consentService')->forParticipant(1);
        $this->assertSame([0, date('Y-m-d')], [(int) $row['recording_consent'], $row['recording_withdrawn_on']]);

        $audit = json_decode((string) $this->conn->table('audit_logs')->where('action', 'consent_updated')->where('target_id', '1')->get()->getFirstRow()->metadata_json, true);
        $this->assertSame(['participant_code' => 'S03-A01', 'field' => 'recording_consent', 'from' => 1, 'to' => 0], array_intersect_key($audit, array_flip(['participant_code', 'field', 'from', 'to'])));
        $this->assertArrayHasKey('on', $audit);
    }

    public function testDatesInTheFutureOrBeforeTheParentConsentAreRefused(): void
    {
        $this->consent($this->conn, 1);
        $this->post1(['status' => 'left', 'status_on' => date('Y-m-d', strtotime('+2 days'))]);
        $this->assertStringContainsString(lang('Admin.consentBadDate'), (string) ($_SESSION['error'] ?? ''));

        $this->post1(['status' => 'left', 'status_on' => date('Y-m-d', strtotime('-30 days'))]);
        $this->assertStringContainsString(lang('Admin.consentBadDate'), (string) ($_SESSION['error'] ?? ''));
        $this->assertSame('active', service('consentService')->forParticipant(1)['status']);
    }

    public function testTwoSavesAtOnceOneWins(): void
    {
        $this->consent($this->conn, 1);
        $stamp = (string) service('consentService')->forParticipant(1)['updated_at'];

        // Both pages were read with the same stamp
        $this->post1(['risk_items_consent' => '1', 'stamp' => $stamp]);
        $this->post1(['recording_consent' => '1', 'stamp' => $stamp]);

        $this->assertStringContainsString(lang('Admin.consentStale'), (string) ($_SESSION['error'] ?? ''));
        $row = service('consentService')->forParticipant(1);
        $this->assertSame([1, 0], [(int) $row['risk_items_consent'], (int) $row['recording_consent']], 'the second save changed nothing');
    }

    public function testTheAdminHasNoConsentPage(): void
    {
        $this->asStaff('admin');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->send(Method::GET, 'admin/sesi-lapangan/' . $this->fs . '/izin');
    }

    public function testMonitoringShowsTheMissingCountToResearchersOnly(): void
    {
        $this->conn->table('game_sessions')->insert(['session_code' => 'F1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'field_session_id' => $this->fs, 'meeting_no' => 2, 'status' => 'active', 'last_active_at' => date('Y-m-d H:i:s')]);

        $this->assertSame(1, service('analyticsService')->monitoring(1, true)[0]['consent_missing']);
        $this->assertArrayNotHasKey('consent_missing', service('analyticsService')->monitoring(1, false)[0]);
    }

    /** @param array<string, string> $values */
    private function post1(array $values): void
    {
        $current = service('consentService')->forParticipant(1) ?? [];
        $row     = $values + [
            'stamp'             => (string) ($current['updated_at'] ?? ''),
            'parent_consent_on' => (string) ($current['parent_consent_on'] ?? ''),
            'recording_consent' => (string) ($current['recording_consent'] ?? '0'),
            'risk_items_consent' => (string) ($current['risk_items_consent'] ?? '0'),
            'status'            => (string) ($current['status'] ?? 'active'),
        ];
        unset($_SESSION['error'], $_SESSION['message']);
        $this->send(Method::POST, 'admin/sesi-lapangan/' . $this->fs . '/izin', ['consent' => [1 => $row]]);
    }
}
