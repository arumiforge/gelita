<?php

use App\Database\Migrations\CreateInstrumentModule;
use App\Services\RetentionService;
use App\Services\SystemResetService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Tests\Support\Database\ResetTables;

/**
 * Migration 2026-01-01-004200 (research revision 10, part 8: 34 §11), run on
 * the schema before it, and the tables in the reset and deletion lists (§11.3, §15.2).
 *
 * @internal
 */
final class InstrumentModuleMigrationTest extends CIUnitTestCase
{
    use ResetTables;

    private const TABLES = ['instrument_responses', 'instrument_parts', 'instrument_blocks', 'instrument_sittings', 'instrument_items', 'instrument_stimuli', 'instrument_versions'];

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createResetTables();
        $forge      = Database::forge('tests');

        foreach (self::TABLES as $table) {
            $forge->dropTable($table, true);
        }

        $forge->dropColumn('field_sessions', ['test_started_at', 'test_started_by']);
        $forge->dropColumn('research_studies', ['instrument_settings']);
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testUpAndDown(): void
    {
        $this->migration()->up();

        foreach (self::TABLES as $table) {
            $this->assertTrue($this->conn->tableExists($table), $table);
        }

        $this->assertContains('updated_at', $this->conn->getFieldNames('instrument_sittings'), '34 §18 no. 6');
        $this->assertContains('updated_at', $this->conn->getFieldNames('instrument_responses'));
        $this->assertTrue($this->conn->fieldExists('test_started_at', 'field_sessions'));
        $this->assertTrue($this->conn->fieldExists('instrument_settings', 'research_studies'));
        $this->assertFalse($this->conn->fieldExists('test_rapid_guess_ms', 'research_studies'), 'part 7 owns it');

        $this->conn->table('participant_feedback')->insert(['participant_id' => 1, 'rating' => null]);
        $this->assertNull($this->conn->table('participant_feedback')->get()->getRow('rating'), 'rating may be empty (§11.2)');

        $this->migration()->down();

        foreach (self::TABLES as $table) {
            $this->assertFalse($this->conn->tableExists($table), $table);
        }

        $this->assertFalse($this->conn->fieldExists('test_started_at', 'field_sessions'));
        $this->assertSame('0', (string) $this->conn->table('participant_feedback')->get()->getRow('rating'), 'an empty rating becomes 0');
    }

    public function testTheTablesSitInTheResetAndDeletionLists(): void
    {
        $this->assertSame('2026-01-01-004200', SystemResetService::REQUIRED_MIGRATION);

        foreach (array_slice(self::TABLES, 0, 4) as $table) {
            $this->assertContains($table, SystemResetService::GROUP_A);
            $this->assertLessThan(array_search('participants', SystemResetService::CLEAR_ORDER, true), array_search($table, SystemResetService::CLEAR_ORDER, true), $table);
            $this->assertLessThan(array_search('participants', RetentionService::HARD_ORDER, true), array_search($table, RetentionService::HARD_ORDER, true), $table);
        }

        foreach (array_slice(self::TABLES, 4) as $table) {
            $this->assertContains($table, SystemResetService::GROUP_B, 'content, imported again');
        }
    }

    private function migration(): CreateInstrumentModule
    {
        require_once APPPATH . 'Database/Migrations/2026-01-01-004200_CreateInstrumentModule.php';

        return new CreateInstrumentModule(Database::forge('tests'));
    }
}
