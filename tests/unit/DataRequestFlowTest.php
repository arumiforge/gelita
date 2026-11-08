<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * Parents' requests: file → confirm → run (research revision 10, part 4:
 * 30 §7.1, K1, K8, §19.1 `DataRequestFlowTest`).
 *
 * @internal
 */
final class DataRequestFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->consent($this->conn, 1);
        $this->playData($this->conn, 1, 1);
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testResearcherFilesHeadResearcherConfirmsAdminRuns(): void
    {
        $this->asStaff('peneliti');
        $form = (string) $this->send(Method::GET, 'admin/permintaan-data/baru?kode=s03-a01')->getBody();
        $this->assertStringContainsString('S03', $form, 'school code to compare with L7');
        $this->assertStringNotContainsString('Jaka', $form);

        $this->send(Method::POST, 'admin/permintaan-data/baru', [
            'participant_code' => 'S03-A01', 'kind' => 'all', 'received_at' => date('Y-m-d\TH:i', time() - 3600),
            'received_via' => 'whatsapp', 'verified' => '1', 'verified_at' => date('Y-m-d\TH:i', time() - 1800),
        ]);
        $row = $this->conn->table('data_deletion_requests')->get()->getRowArray();
        $this->assertSame(['requested', 'hard', 'all', 'S03-A01'], [$row['status'], $row['mode'], $row['kind'], $row['participant_code']]);
        $this->assertSame('withdrawn_delete', service('consentService')->forParticipant(1)['status'], 'the consent status moves with the request');
        $this->assertNotNull($this->conn->table('participants')->where('id', 1)->get()->getRow('deactivated_at'));

        $this->asStaff('ketua');
        $this->send(Method::POST, 'admin/permintaan-data/' . $row['id'] . '/konfirmasi');
        $row = $this->requestRow((int) $row['id']);
        $this->assertSame(['preview', 3], [$row['status'], (int) $row['confirmed_by']]);
        $this->assertGreaterThan(0, (int) $row['affected_count']);

        $this->asStaff('admin');
        $this->assertStringContainsString(esc(lang('Admin.requestFromParents')), (string) $this->send(Method::GET, 'admin/tata-kelola')->getBody());
        $this->send(Method::POST, 'admin/tata-kelola/hapus/' . $row['id'] . '/jalankan', ['confirm' => 'HAPUS']);

        $row = $this->requestRow((int) $row['id']);
        $this->assertSame('executed', $row['status']);
        $this->assertSame(0, $this->conn->table('participants')->where('id', 1)->countAllResults());
        $this->assertSame('S03-A01', $row['participant_code'], 'the code stays as evidence');
    }

    public function testTheHeadResearchersOwnRequestGoesStraightToPreview(): void
    {
        $id = $this->fileRequest('all', 'ketua');

        $this->assertSame('preview', $this->requestRow($id)['status']);
    }

    public function testRejectAndCancel(): void
    {
        $id = $this->fileRequest('all');

        $this->asStaff('ketua');
        $this->send(Method::POST, 'admin/permintaan-data/' . $id . '/tolak', ['reason' => '']);
        $this->assertSame('requested', $this->requestRow($id)['status'], 'a reason is needed');
        $this->send(Method::POST, 'admin/permintaan-data/' . $id . '/tolak', ['reason' => 'kode salah']);
        $this->assertSame('cancelled', $this->requestRow($id)['status']);

        $second = $this->fileRequest('recording');
        $this->asStaff('peneliti');
        $this->send(Method::POST, 'admin/permintaan-data/' . $second . '/batal');
        $this->assertSame('cancelled', $this->requestRow($second)['status']);
    }

    public function testAResearcherSeesOnlyOwnRequests(): void
    {
        $this->conn->table('staff_users')->insert(['id' => 4, 'username' => 'peneliti_dua', 'password_hash' => 'x', 'role' => 'peneliti', 'display_name' => 'P2', 'is_active' => 1]);
        $id = $this->fileRequest('recording');

        $this->asStaff('peneliti');
        $this->assertStringContainsString('#' . $id, (string) $this->send(Method::GET, 'admin/permintaan-data')->getBody());

        $_SESSION = ['staff_id' => 4, 'staff_role' => 'peneliti', 'staff_last_seen' => time()];
        $this->assertStringNotContainsString('S03-A01', (string) $this->send(Method::GET, 'admin/permintaan-data')->getBody());
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->send(Method::GET, 'admin/permintaan-data/' . $id);
    }
}
