<?php

use App\Database\Migrations\CleanResearchLogs;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Tests\Support\Database\OldLogShape;
use Tests\Support\Database\ResetTables;

/**
 * Migration 2026-01-01-003600 (research revision 10, part 3: 29 §5.8, K3):
 * the removed events go, `answer_changed` becomes `answer_submitted` with
 * `changed`, one `dialogue_completed` per session, context, and region, the
 * device columns are dropped, one audit row; down() runs.
 *
 * The tables come from ResetTables put back in their shape before 003600.
 *
 * @internal
 */
final class ResearchLogCleanupMigrationTest extends CIUnitTestCase
{
    use ResetTables;
    use OldLogShape;

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

    public function testUpCleansTheLogsAndAddsTheColumns(): void
    {
        $this->migration()->up();
        $db = $this->conn;

        $types = array_column($db->table('game_event_logs')->select('event_type')->get()->getResultArray(), 'event_type');
        $this->assertSame([], array_values(array_intersect($types, ['answer_selected', 'dialogue_advanced', 'audio_pause', 'answer_changed'])));

        $submitted = $db->table('game_event_logs')->where('event_type', 'answer_submitted')->orderBy('id')->get()->getResultArray();
        $this->assertCount(2, $submitted);
        $this->assertSame(['try_no' => 2, 'correct' => true, 'changed' => true], json_decode((string) $submitted[1]['payload_json'], true));
        $this->assertArrayNotHasKey('changed', json_decode((string) $submitted[0]['payload_json'], true), 'a plain submit stays as it was');

        $completed = $db->table('game_event_logs')->where('event_type', 'dialogue_completed')->orderBy('session_id')->orderBy('level_id')->get()->getResultArray();
        $this->assertSame(
            [[1, 1, 'mig-dc-1-region_intro-1'], [1, 2, 'mig-dc-1-region_intro-2'], [2, 1, 'mig-dc-2-region_intro-1']],
            array_map(static fn (array $r): array => [(int) $r['session_id'], (int) $r['level_id'], $r['client_event_id']], $completed),
            'once per session, context, and region; slide 2 of 4 counts for nothing',
        );
        $this->assertSame(['context' => 'region_intro', 'total' => 4, 'via' => 'migration'], json_decode((string) $completed[0]['payload_json'], true));
        $this->assertSame('2026-10-01 08:00:04', substr((string) $completed[0]['occurred_at'], 0, 19), 'the first last-slide row');
        $this->assertSame(4, (int) $completed[0]['sequence_no']);

        $fields = $db->getFieldNames('device_logs');
        foreach (['user_agent', 'client_hints_json', 'device_brand', 'device_model', 'pixel_ratio', 'touch_points', 'timezone', 'language', 'os_platform'] as $column) {
            $this->assertNotContains($column, $fields, $column);
        }
        $this->assertSame('Android', $db->table('device_logs')->get()->getRow('os_name'), 'kept columns keep their data');
        $this->assertContains('ready_ms', $db->getFieldNames('item_tries'));
        $this->assertContains('dictionary_version', $db->getFieldNames('data_exports'));
        $this->assertContains('participant_count', $db->getFieldNames('data_exports'));

        $audit = $db->table('audit_logs')->where('action', 'research_log_cleanup')->get()->getResultArray();
        $this->assertCount(1, $audit);
        $this->assertNull($audit[0]['staff_user_id']);
        $meta = json_decode((string) $audit[0]['metadata_json'], true);
        $this->assertSame(['via' => 'migration', 'dialogue_completed' => 3, 'answer_changed_renamed' => 1, 'deleted' => ['answer_selected' => 2, 'dialogue_advanced' => 8, 'audio_pause' => 1]], array_intersect_key($meta, array_flip(['via', 'dialogue_completed', 'answer_changed_renamed', 'deleted'])));
        $this->assertSame(1, $meta['device_logs_with_details']);
    }

    public function testDownBringsBackTheColumnsAndTheStoryEvents(): void
    {
        $migration = $this->migration();
        $migration->up();
        $migration->down();

        $this->assertContains('user_agent', $this->conn->getFieldNames('device_logs'));
        $this->assertNotContains('ready_ms', $this->conn->getFieldNames('item_tries'));
        $this->assertNotContains('dictionary_version', $this->conn->getFieldNames('data_exports'));
        $this->assertSame(3, $this->conn->table('game_event_logs')->where('event_type', 'dialogue_advanced')->countAllResults(), 'one per migrated dialogue_completed');
        $this->assertSame(0, $this->conn->table('game_event_logs')->where('event_type', 'dialogue_completed')->countAllResults());
    }

    private function migration(): CleanResearchLogs
    {
        require_once APPPATH . 'Database/Migrations/2026-01-01-003600_CleanResearchLogs.php';

        return new CleanResearchLogs(Database::forge('tests'));
    }
}
