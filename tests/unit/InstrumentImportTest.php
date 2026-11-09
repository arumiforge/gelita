<?php

use App\Libraries\InstrumentImporter;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * @internal
 */
final class InstrumentImportTest extends CIUnitTestCase
{
    use ResetTables;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createResetTables();
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testShippedFilesImport(): void
    {
        $result = (new InstrumentImporter($this->conn))->import();
        $this->assertSame([], $result['errors']);
    }
}
