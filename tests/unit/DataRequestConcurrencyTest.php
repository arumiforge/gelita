<?php

use App\Models\DataDeletionRequestModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ConsentWorld;

/**
 * Two confirmations or two runs at once: one wins, the other is told (research
 * revision 10, part 4: 30 §7.1, §19.1 `DataRequestConcurrencyTest`).
 *
 * @internal
 */
final class DataRequestConcurrencyTest extends CIUnitTestCase
{
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
        Factories::reset('models');
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testASecondConfirmationIsRefused(): void
    {
        $id = $this->fileRequest('all');
        service('dataRequests')->confirm($id, 3);

        $this->expectExceptionMessage(lang('Admin.requestAlreadyConfirmed'));
        service('dataRequests')->confirm($id, 3);
    }

    public function testTwoRunsDeleteOnce(): void
    {
        $id = $this->fileRequest('all', 'ketua');

        // The second admin read the request while it was still `preview` (stale read)
        $stale = $this->requestRow($id);
        service('retentionService')->execute($id, 1);
        $audits = $this->conn->table('audit_logs')->where('action', 'delete_execute')->countAllResults();

        Factories::injectMock('models', DataDeletionRequestModel::class, new class ($stale) extends DataDeletionRequestModel {
            public function __construct(private array $stale)
            {
                parent::__construct();
            }

            public function find($id = null)
            {
                return $id === null ? parent::find() : $this->stale;
            }
        });

        try {
            service('retentionService')->execute($id, 1);
            $this->fail('the second run must be refused');
        } catch (\DomainException $e) {
            $this->assertSame(lang('Admin.requestAlreadyRun'), $e->getMessage());
        }

        $this->assertSame($audits, $this->conn->table('audit_logs')->where('action', 'delete_execute')->countAllResults(), 'no second deletion');
    }
}
