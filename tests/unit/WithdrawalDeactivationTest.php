<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * A withdrawal deactivates the account at once (research revision 10,
 * part 4: 30 §5.3, K5, §19.1 `WithdrawalDeactivationTest`).
 *
 * @internal
 */
final class WithdrawalDeactivationTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    private array $fs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->fs   = $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->conn->table('participants')->where('id', 1)->update(['password_hash' => password_hash('Kopi-Kedu7!', PASSWORD_DEFAULT)]);
        $this->conn->table('game_sessions')->where('id', 1)->update(['field_session_id' => $this->fs['id'], 'meeting_no' => 2, 'status' => 'active']);
        $this->consent($this->conn, 1);
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testAWithdrawalEndsTheGameAndRefusesLoginAndToken(): void
    {
        $this->signIn(1, 1);
        $this->send(Method::GET, 'peta')->assertOK();

        $result = service('consentService')->apply(1, 1, ['status' => 'withdrawn_keep', 'status_on' => date('Y-m-d')], 2);
        $this->assertSame(['status' => 'saved', 'deactivated' => true], $result);
        $this->assertNotNull($this->conn->table('participants')->where('id', 1)->get()->getRow('deactivated_at'), 'same transaction as the status');
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'participant_deactivated')->countAllResults());

        // The next game request ends the session, even mid-challenge
        $this->signIn(1, 1);
        $response = $this->send(Method::GET, 'peta');
        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('masuk', (string) $response->getRedirectUrl());

        $login = service('sessionService')->login('jaka5', 'Kopi-Kedu7!', 'id', []);
        $this->assertSame('inactive', $login['status']);
        $this->assertSame('invalid', service('sessionService')->login('jaka5', 'salah-Kedu7!', 'id', [])['status'], 'a wrong password says nothing about the account');

        $participant = model(App\Models\ParticipantModel::class)->find(1);
        $this->assertSame('inactive', service('fieldSessions')->checkToken('GHJKMN', $participant)['status']);
    }

    public function testRestoringTheStatusKeepsTheAccountOffUntilTheAdminTurnsItOn(): void
    {
        service('consentService')->apply(1, 1, ['status' => 'withdrawn_keep', 'status_on' => date('Y-m-d')], 2);

        $this->asStaff('admin');
        $this->send(Method::POST, 'admin/peserta/1/aktifkan', ['reason' => 'salah catat']);
        $this->assertSame(lang('Admin.reactivateStatusFirst'), $_SESSION['error'] ?? null);

        service('consentService')->apply(1, 1, ['status' => 'active'], 2);
        $this->assertNotNull($this->conn->table('participants')->where('id', 1)->get()->getRow('deactivated_at'), 'restoring the status does not reactivate');

        unset($_SESSION['error']);
        $this->asStaff('admin');
        $this->send(Method::POST, 'admin/peserta/1/aktifkan', ['reason' => '']);
        $this->assertSame(lang('Admin.reasonRequired'), $_SESSION['error'] ?? null);

        $this->asStaff('admin');
        $this->send(Method::POST, 'admin/peserta/1/aktifkan', ['reason' => 'salah catat P1']);
        $this->assertNull($this->conn->table('participants')->where('id', 1)->get()->getRow('deactivated_at'));
        $audit = json_decode((string) $this->conn->table('audit_logs')->where('action', 'participant_reactivated')->get()->getRow('metadata_json'), true);
        $this->assertSame(['participant_code' => 'S03-A01', 'reason' => 'salah catat P1'], $audit);
    }

    public function testLeavingDoesNotDeactivate(): void
    {
        service('consentService')->apply(1, 1, ['status' => 'left', 'status_on' => date('Y-m-d')], 2);

        $this->assertNull($this->conn->table('participants')->where('id', 1)->get()->getRow('deactivated_at'));
    }

    public function testOnlyTheAdminReactivates(): void
    {
        $this->asStaff('ketua');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->send(Method::POST, 'admin/peserta/1/aktifkan', ['reason' => 'x']);
    }
}
