<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Research revision 10, part 7 (docs/revisi-10-analitik/33_IMPLEMENTASI_MODE_PENELITIAN.md §9):
 * raw data for active time and the research mode in the game. Deletes nothing.
 *
 * up():
 * 1. `research_studies`: `idle_threshold_ms` (T_idle, 180000), `rapid_guess_ms`
 *    (T_teb, 3000), `min_stage_px` (960), and `test_rapid_guess_ms` (3000, the
 *    rapid-guess threshold of the test module, one value for every test;
 *    owner, 4 November 2026, 34 §17 no. 1). Read by part 9 (35 §5) and part 8;
 *    part 9 only adds `thresholds_locked_at`.
 * 2. `challenge_attempts.is_retake` (K6), filled for existing rows: 1 when the
 *    same student already had an earlier `completed` attempt at the node.
 * 3. `audio_usage_events.clock_offset_ms` (K10); old rows stay empty.
 * 4. New table `challenge_attempt_segments` (§5.4), one segment per existing
 *    attempt (`started_at` to `completed_at`, or still open), `left_via = migration`.
 *
 * down() drops the table and the columns.
 */
class AddResearchModeData extends GelitaMigration
{
    private const STUDY_COLUMNS = ['idle_threshold_ms', 'rapid_guess_ms', 'min_stage_px', 'test_rapid_guess_ms'];

    public function up(): void
    {
        $this->forge->addColumn('research_studies', [
            'idle_threshold_ms'   => ['type' => 'INT', 'unsigned' => true, 'default' => 180000],
            'rapid_guess_ms'      => ['type' => 'INT', 'unsigned' => true, 'default' => 3000],
            'min_stage_px'        => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 960],
            'test_rapid_guess_ms' => ['type' => 'INT', 'unsigned' => true, 'default' => 3000],
        ]);

        $this->forge->addColumn('challenge_attempts', [
            'is_retake' => $this->flag(0),
        ]);

        $this->forge->addColumn('audio_usage_events', [
            'clock_offset_ms' => ['type' => 'INT', 'null' => true],
        ]);

        $this->forge->addField([
            'id'                   => $this->id(),
            'challenge_attempt_id' => $this->ref(false),
            'session_id'           => $this->ref(false),
            'field_session_id'     => $this->ref(true),
            'segment_no'           => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'opened_at'            => ['type' => 'DATETIME', 'constraint' => 3, 'null' => false],
            'left_at'              => ['type' => 'DATETIME', 'constraint' => 3, 'null' => true],
            'left_via'             => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['challenge_attempt_id', 'segment_no']);
        $this->forge->addKey(['session_id', 'left_at']);
        $this->forge->addForeignKey('challenge_attempt_id', 'challenge_attempts', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('session_id', 'game_sessions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('field_session_id', 'field_sessions', 'id', '', 'SET NULL');

        $this->isMysql() ? $this->createGelitaTable('challenge_attempt_segments') : $this->forge->createTable('challenge_attempt_segments', true);

        $this->backfill();
    }

    public function down(): void
    {
        $this->forge->dropTable('challenge_attempt_segments', true);
        $this->forge->dropColumn('audio_usage_events', ['clock_offset_ms']);
        $this->forge->dropColumn('challenge_attempts', ['is_retake']);
        $this->forge->dropColumn('research_studies', self::STUDY_COLUMNS);
    }

    /** is_retake and one segment per existing attempt (§9). */
    private function backfill(): void
    {
        $attempts = $this->db->protectIdentifiers('challenge_attempts', true);
        $sessions = $this->db->protectIdentifiers('game_sessions', true);

        // An earlier completed attempt of the same student at the same node
        $retakes = array_map('intval', array_column($this->db->query(
            "SELECT a.id FROM {$attempts} a JOIN {$sessions} s ON s.id = a.session_id"
            . " WHERE EXISTS (SELECT 1 FROM {$attempts} b JOIN {$sessions} t ON t.id = b.session_id"
            . " WHERE t.participant_id = s.participant_id AND b.challenge_node_id = a.challenge_node_id"
            . " AND b.status = 'completed' AND b.id < a.id)",
        )->getResultArray(), 'id'));

        foreach (array_chunk($retakes, 500) as $ids) {
            $this->db->table('challenge_attempts')->whereIn('id', $ids)->update(['is_retake' => 1]);
        }

        $this->db->query(
            'INSERT INTO ' . $this->db->protectIdentifiers('challenge_attempt_segments', true)
            . ' (challenge_attempt_id, session_id, field_session_id, segment_no, opened_at, left_at, left_via)'
            . " SELECT a.id, a.session_id, s.field_session_id, 1, a.started_at, a.completed_at, 'migration'"
            . " FROM {$attempts} a JOIN {$sessions} s ON s.id = a.session_id",
        );
    }

    private function isMysql(): bool
    {
        return $this->db->DBDriver === 'MySQLi';
    }
}
