<?php

use App\Database\Migrations\CreateDerivedLayer;
use App\Services\SystemResetService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Tests\Support\Database\ResetTables;

/**
 * Migration 2026-01-01-004300 (research revision 10, part 9: 35 §12), run on
 * the schema before it; the test schema carries the same columns.
 *
 * @internal
 */
final class DerivedLayerMigrationTest extends CIUnitTestCase
{
    use ResetTables;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        require_once APPPATH . 'Database/Migrations/2026-01-01-004300_CreateDerivedLayer.php';
        $this->conn = $this->createResetTables();
        $forge      = Database::forge('tests');

        foreach ([...array_keys(CreateDerivedLayer::TABLES), 'derived_runs'] as $table) {
            $forge->dropTable($table, true);
        }

        $forge->dropColumn('research_studies', ['thresholds_locked_at', 'derived_run_id', 'derived_running_since']);
        $forge->dropColumn('data_exports', ['derived_run_id']);
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testUpAndDown(): void
    {
        $this->migration()->up();

        $this->assertTrue($this->conn->tableExists('derived_runs'));

        foreach (CreateDerivedLayer::TABLES as $table => [$keys, $columns]) {
            $this->assertTrue($this->conn->tableExists($table), $table);
            $this->assertSame(['run_id', ...$keys, 'dictionary_version', ...array_keys($columns)], $this->conn->getFieldNames($table), $table);
            $this->assertEqualsCanonicalizing(array_keys(self::$resetSchema[$table]), [...$this->conn->getFieldNames($table), '_primary'], "{$table}: the test schema matches");
        }

        foreach (['thresholds_locked_at', 'derived_run_id', 'derived_running_since'] as $column) {
            $this->assertTrue($this->conn->fieldExists($column, 'research_studies'), $column);
        }

        $this->assertTrue($this->conn->fieldExists('derived_run_id', 'data_exports'));
        $source = (string) file_get_contents(APPPATH . 'Database/Migrations/2026-01-01-004300_CreateDerivedLayer.php');
        preg_match("/addColumn\\('research_studies', \\[(.*?)\\]\\);/s", $source, $m);
        $this->assertStringNotContainsString('rapid_guess_ms', $m[1], 'the threshold columns are part 7s (35 §19 no. 8)');

        $this->migration()->down();

        foreach ([...array_keys(CreateDerivedLayer::TABLES), 'derived_runs'] as $table) {
            $this->assertFalse($this->conn->tableExists($table), $table);
        }

        $this->assertFalse($this->conn->fieldExists('thresholds_locked_at', 'research_studies'));
    }

    public function testRequiredMigration(): void
    {
        $this->assertSame('2026-01-01-004300', SystemResetService::REQUIRED_MIGRATION);
    }

    private function migration(): CreateDerivedLayer
    {
        require_once APPPATH . 'Database/Migrations/2026-01-01-004300_CreateDerivedLayer.php';

        return new CreateDerivedLayer(Database::forge('tests'));
    }
}
