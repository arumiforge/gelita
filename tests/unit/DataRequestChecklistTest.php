<?php

use App\Services\DataRequestService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ConsentWorld;

/**
 * The checklist and the deadlines of a parent's request (research revision
 * 10, part 4: 30 §7.6, §7.7, §14, §19.1 `DataRequestChecklistTest`).
 *
 * @internal
 */
final class DataRequestChecklistTest extends CIUnitTestCase
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
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testItemsPerKind(): void
    {
        $this->assertSame(['system', 'server_exports', 'researcher_copies', 'backups', 'recordings', 'paper_sheets', 'parent_notified'], DataRequestService::itemsFor('all'));
        $this->assertSame(['system', 'server_exports', 'researcher_copies', 'backups', 'parent_notified'], DataRequestService::itemsFor('risk_items'));
        $this->assertSame(['recordings', 'parent_notified'], DataRequestService::itemsFor('recording'));
    }

    public function testParentNotifiedWaitsAndEveryRoleTicksItsOwn(): void
    {
        $service = service('dataRequests');
        $id      = $this->fileRequest('all', 'ketua');
        service('retentionService')->execute($id, 1);

        $this->assertSame(['system', 'server_exports'], array_keys($service->checklist($this->requestRow($id))), 'ticked when it ran');

        $this->refused(fn () => $service->tick($id, 'parent_notified', 3, 'ketua'), lang('Admin.requestChecklistBlocked'));
        $this->refused(fn () => $service->tick($id, 'backups', 2, 'peneliti'), lang('Admin.requestTickNotAllowed'));
        $this->refused(fn () => $service->tick($id, 'researcher_copies', 1, 'admin'), lang('Admin.requestTickNotAllowed'));
        $this->refused(fn () => $service->tick($id, 'system', 1, 'admin'), lang('Admin.requestBadItem'));

        $service->tick($id, 'backups', 1, 'admin');
        $service->tick($id, 'researcher_copies', 3, 'ketua');
        $service->tick($id, 'recordings', 2, 'peneliti');
        $service->tick($id, 'paper_sheets', 2, 'peneliti');
        $this->assertSame('executed', $this->requestRow($id)['status']);

        $service->tick($id, 'parent_notified', 3, 'ketua');
        $row = $this->requestRow($id);
        $this->assertSame('completed', $row['status']);
        $this->assertNotNull($row['completed_at']);
    }

    public function testDeadlinesAndTheirColours(): void
    {
        $row = ['received_at' => date('Y-m-d H:i:s', time() - 5 * 3600), 'executed_at' => null, 'checklist_json' => null];
        $this->assertSame(['received_hours' => 5, 'notify_left' => null, 'level' => 'ok'], service('dataRequests')->deadlines($row));

        $now = time();
        $this->assertSame('ok', service('dataRequests')->deadlines(['executed_at' => date('Y-m-d H:i:s', $now - 3600)] + $row, $now)['level']);
        $this->assertSame(['received_hours' => 5, 'notify_left' => 11, 'level' => 'warn'], service('dataRequests')->deadlines(['executed_at' => date('Y-m-d H:i:s', $now - 61 * 3600)] + $row, $now));
        $this->assertSame('late', service('dataRequests')->deadlines(['executed_at' => date('Y-m-d H:i:s', $now - 80 * 3600)] + $row, $now)['level']);
        $this->assertNull(service('dataRequests')->deadlines(['executed_at' => date('Y-m-d H:i:s', $now - 80 * 3600), 'checklist_json' => '{"parent_notified":{"at":"x","by":3}}'] + $row, $now)['notify_left'], 'no deadline once told');
    }

    private function refused(\Closure $action, string $message): void
    {
        try {
            $action();
            $this->fail('expected a refusal: ' . $message);
        } catch (\DomainException $e) {
            $this->assertSame($message, $e->getMessage());
        }
    }
}
