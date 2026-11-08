<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ConsentWorld;

/**
 * Download files on the server go with a parent's request (research revision
 * 10, part 4: 30 §7.4, §19.1 `DataRequestExportPurgeTest`).
 *
 * @internal
 */
final class DataRequestExportPurgeTest extends CIUnitTestCase
{
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    /** @var list<string> */
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->consent($this->conn, 1);
        $this->playData($this->conn, 1, 1);
        $this->conn->table('participants')->where('id', 1)->update(['created_at' => date('Y-m-d H:i:s', time() - 86400)]);
    }

    protected function tearDown(): void
    {
        array_map(static fn (string $f) => is_file($f) && unlink($f), $this->files);
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testFilesSinceRegistrationArePurgedOlderOnesStay(): void
    {
        $old = $this->export(time() - 3 * 86400);
        $new = $this->export(time() - 3600);

        $id = $this->fileRequest('all', 'ketua');
        service('retentionService')->execute($id, 1);

        $newRow = $this->conn->table('data_exports')->where('id', $new)->get()->getRowArray();
        $this->assertNull($newRow['file_path']);
        $this->assertSame($id, (int) $newRow['purged_by_request_id']);
        $this->assertFileDoesNotExist($this->files[1]);

        $oldRow = $this->conn->table('data_exports')->where('id', $old)->get()->getRowArray();
        $this->assertNotNull($oldRow['file_path']);
        $this->assertFileExists($this->files[0]);

        $audit = json_decode((string) $this->conn->table('audit_logs')->where('action', 'export_purge')->get()->getRow('metadata_json'), true);
        $this->assertSame(['export_ids' => [$new], 'request_id' => $id], $audit);
        $this->assertArrayHasKey('server_exports', service('dataRequests')->checklist($this->requestRow($id)));
    }

    private function export(int $at): int
    {
        $dir = WRITEPATH . 'exports';
        is_dir($dir) || mkdir($dir, 0775, true);
        $path          = $dir . DIRECTORY_SEPARATOR . 'uji-purge-' . bin2hex(random_bytes(4)) . '.xlsx';
        $this->files[] = $path;
        file_put_contents($path, 'x');

        $this->conn->table('data_exports')->insert([
            'requested_by' => 3, 'format' => 'xlsx', 'scope_json' => '{}', 'status' => 'done', 'file_path' => $path,
            'created_at' => date('Y-m-d H:i:s', $at), 'expires_at' => date('Y-m-d H:i:s', time() + 86400),
        ]);

        return (int) $this->conn->insertID();
    }
}
