<?php

use App\Database\Migrations\CreateFieldSessions;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Tests\Support\Database\ResetTables;

/**
 * Migration 2026-01-01-003500 fills `participant_progress` from the completed
 * attempts before it drops `session_progress` (research revision 10, part 2:
 * 28 §8, §14, §22 no. 11, §19.1 `ProgressBackfillMigrationTest`).
 *
 * The tables come from ResetTables in their shape before 003500: the new
 * tables and columns are removed and `session_progress` comes back; then
 * up() runs directly.
 *
 * @internal
 */
final class ProgressBackfillMigrationTest extends CIUnitTestCase
{
    use ResetTables;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = $this->createResetTables();
        $this->oldShape();
    }

    protected function tearDown(): void
    {
        $forge = Database::forge('tests');
        $forge->dropTable('session_progress', true);
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testProgressComesFromCompletedAttemptsOfEverySession(): void
    {
        $db = $this->conn;
        $db->table('research_studies')->insert(['code' => 'S', 'name' => 'Studi', 'status' => 'active', 'unlock_mode' => 'sequential']);

        foreach ([1, 2] as $level) {
            $db->table('levels')->insert(['sequence' => $level, 'code' => 'l' . $level, 'name_id' => 'L', 'name_en' => 'L', 'difficulty' => 'mudah', 'focus_id' => 'F', 'focus_en' => 'F']);

            foreach ([1, 2] as $sequence) {
                $db->table('challenge_nodes')->insert(['level_id' => $level, 'sequence' => $sequence, 'engine_type' => 'puzzle', 'title_id' => 'N', 'title_en' => 'N']);
            }
        }

        // Student 1: level 1 finished across two sessions, node 1 twice (★ then ★★★); student 2: nothing done
        $db->table('participants')->insertBatch([
            ['participant_code' => 'GLT-000001', 'username' => 'a', 'password_hash' => 'x'],
            ['participant_code' => 'GLT-000002', 'username' => 'b', 'password_hash' => 'x'],
        ]);
        $db->table('game_sessions')->insertBatch([
            ['session_code' => 'S1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'last_active_at' => '2026-10-01 08:00:00'],
            ['session_code' => 'S2', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'last_active_at' => '2026-10-02 08:00:00'],
            ['session_code' => 'S3', 'participant_id' => 2, 'study_id' => 1, 'release_id' => 1, 'last_active_at' => '2026-10-02 08:00:00'],
        ]);
        foreach ([[1, 1, 1], [2, 1, 3], [2, 2, 2], [2, 3, 0]] as [$session, $node, $stars]) {
            $db->table('challenge_attempts')->insert([
                'session_id' => $session, 'challenge_node_id' => $node, 'status' => $stars > 0 ? 'completed' : 'abandoned', 'stars' => $stars, 'selected_item_ids_json' => '[]',
            ]);
        }
        $db->table('session_progress')->insertBatch([
            ['session_id' => 1, 'current_level_id' => 1, 'current_node_id' => 1, 'completed_nodes' => 1, 'total_stars' => 1],
            ['session_id' => 2, 'current_level_id' => 2, 'current_node_id' => 3, 'completed_nodes' => 2, 'total_stars' => 5],
            ['session_id' => 3, 'current_level_id' => 1, 'current_node_id' => 1, 'completed_nodes' => 0, 'total_stars' => 0],
        ]);

        $this->migration()->up();

        $this->assertFalse($db->tableExists('session_progress'), 'dropped after the backfill');

        $rows = $db->table('participant_progress')->orderBy('participant_id')->get()->getResultArray();
        $this->assertCount(2, $rows);
        $this->assertSame(
            [1, 1, 2, 1, 2, 5, 2, 3],
            [(int) $rows[0]['participant_id'], (int) $rows[0]['study_id'], (int) $rows[0]['completed_nodes'], (int) $rows[0]['completed_levels'],
             (int) $rows[0]['unlocked_level_sequence'], (int) $rows[0]['total_stars'], (int) $rows[0]['current_level_id'], (int) $rows[0]['current_node_id']],
            'best stars per node (3 + 2), level 1 done so level 2 opens, current place from the latest session',
        );
        $this->assertSame([0, 0, 1], [(int) $rows[1]['completed_nodes'], (int) $rows[1]['total_stars'], (int) $rows[1]['unlocked_level_sequence']]);

        foreach (['field_sessions', 'field_session_tokens', 'field_session_members', 'participant_seats', 'participant_code_counters'] as $table) {
            $this->assertTrue($db->tableExists($table), $table);
        }

        $this->assertContains('field_session_id', $db->getFieldNames('game_sessions'));
        $this->assertContains('research_code', $db->getFieldNames('schools'));
        $this->assertContains('rombel', $db->getFieldNames('participants'));
        $this->assertSame('0', (string) $db->table('research_studies')->get()->getRow('field_mode'), 'field mode off by default');
    }

    private function migration(): CreateFieldSessions
    {
        require_once APPPATH . 'Database/Migrations/2026-01-01-003500_CreateFieldSessions.php';

        return new CreateFieldSessions(Database::forge('tests'));
    }

    /** Back to the schema before 003500. */
    private function oldShape(): void
    {
        $forge = Database::forge('tests');

        foreach (['participant_progress', 'participant_seats', 'participant_code_counters', 'field_session_members', 'field_session_tokens', 'field_sessions'] as $table) {
            $forge->dropTable($table, true);
        }

        foreach (['game_sessions' => ['field_session_id', 'meeting_no'], 'participants' => ['rombel'], 'research_studies' => ['field_mode', 'meeting_plan']] as $table => $columns) {
            $forge->dropColumn($table, $columns);
        }

        // research_code carries a unique key; the table is made again without it
        $forge->dropTable('schools', true);
        $forge->addField([
            'id'   => ['type' => 'INTEGER', 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('schools');

        $forge->addField([
            'session_id'              => ['type' => 'INTEGER'],
            'current_level_id'        => ['type' => 'INTEGER', 'null' => true],
            'current_node_id'         => ['type' => 'INTEGER', 'null' => true],
            'unlocked_level_sequence' => ['type' => 'INTEGER', 'default' => 1],
            'completed_nodes'         => ['type' => 'INTEGER', 'default' => 0],
            'completed_levels'        => ['type' => 'INTEGER', 'default' => 0],
            'total_stars'             => ['type' => 'INTEGER', 'default' => 0],
        ]);
        $forge->addPrimaryKey('session_id');
        $forge->createTable('session_progress');
    }
}
