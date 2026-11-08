<?php

use App\Database\Migrations\CleanResearchLogs;
use App\Libraries\ResearchLogCleanup;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;
use Tests\Support\Database\OldLogShape;
use Tests\Support\Database\ResetTables;

/**
 * `gelita:log:hitung` (research revision 10, part 3: 29 §5.8) prints what
 * migration 003600 will change, changes nothing, and its numbers equal what
 * the migration then does.
 *
 * @internal
 */
final class LogCountCommandTest extends CIUnitTestCase
{
    use OldLogShape;
    use ResetTables;
    use StreamFilterTrait;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = $this->createResetTables();
        $this->oldLogShape($this->conn);
        $this->seedOldLogs($this->conn);
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCountsMatchTheMigrationAndChangeNothing(): void
    {
        $before = $this->conn->table('game_event_logs')->countAllResults();

        command('gelita:log:hitung');
        $output = $this->getStreamFilterBuffer();

        $this->assertStringContainsString('Nothing was changed', $output);
        $this->assertMatchesRegularExpression('/dialogue_advanced\s*\|\s*deleted\s*\|\s*8/', $output);
        $this->assertMatchesRegularExpression('/dialogue_completed\s*\|[^|]*\|\s*3/', $output);
        $this->assertSame($before, $this->conn->table('game_event_logs')->countAllResults(), 'read only');
        $this->assertSame(0, $this->conn->table('audit_logs')->countAllResults());

        $counts = (new ResearchLogCleanup($this->conn))->count();

        require_once APPPATH . 'Database/Migrations/2026-01-01-003600_CleanResearchLogs.php';
        (new CleanResearchLogs(Database::forge('tests')))->up();

        $meta = json_decode((string) $this->conn->table('audit_logs')->get()->getRow('metadata_json'), true);
        $this->assertSame(array_intersect_key($counts['events'], $meta['deleted']), $meta['deleted']);
        $this->assertSame($counts['events']['answer_changed'], $meta['answer_changed_renamed']);
        $this->assertSame($counts['dialogue_completed'], $meta['dialogue_completed']);
        $this->assertSame($counts['device_logs_with_details'], $meta['device_logs_with_details']);
    }
}
