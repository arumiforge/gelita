<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;
use App\Libraries\ProgressRules;
use CodeIgniter\Database\RawSql;

/**
 * Research revision 10, part 2 (docs/revisi-10-analitik/28_IMPLEMENTASI_SESI_LAPANGAN.md §14):
 * field sessions and tokens, participant codes per school and class group,
 * seats and booklets, and progress per student instead of per game session.
 *
 * up():
 * 1. new tables `field_sessions`, `field_session_tokens`, `field_session_members`,
 *    `participant_seats`, `participant_code_counters`, `participant_progress`;
 * 2. new columns `game_sessions.field_session_id`/`meeting_no`,
 *    `participants.rombel`, `schools.research_code`,
 *    `research_studies.field_mode` (default 0) and `meeting_plan`;
 * 3. `participant_progress` is filled from the completed attempts already
 *    stored (one row per student per study, ProgressRules, the same rule as
 *    ChallengeService::refreshProgress()); the current region and challenge
 *    come from the student's latest `session_progress` row;
 * 4. `session_progress` is dropped.
 *
 * No progress is lost, so this is not a data-removing migration (28 §22 no. 11).
 * Runs on MySQL/MariaDB and on SQLite (tests); foreign keys on existing tables
 * are only added on MySQL/MariaDB.
 */
class CreateFieldSessions extends GelitaMigration
{
    public function up(): void
    {
        $this->createFieldSessions();
        $this->createTokens();
        $this->createMembers();
        $this->createSeats();
        $this->createCodeCounters();
        $this->createParticipantProgress();
        $this->addColumns();

        if ($this->db->tableExists('session_progress')) {
            $this->db->transException(true)->transStart();
            $this->backfillProgress();
            $this->db->transComplete();

            $this->forge->dropTable('session_progress', true);
        }
    }

    public function down(): void
    {
        $this->forge->addField([
            'session_id'              => $this->ref(false),
            'current_level_id'        => $this->ref(true),
            'current_node_id'         => $this->ref(true),
            'unlocked_level_sequence' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'completed_nodes'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'completed_levels'        => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'total_stars'             => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'updated_at'              => $this->stamp(true),
        ]);
        $this->forge->addPrimaryKey('session_id');
        $this->createTable('session_progress');

        // Every game session gets its student's progress back
        foreach ($this->db->table('participant_progress')->get()->getResultArray() as $row) {
            $sessions = $this->db->table('game_sessions')->select('id')
                ->where('participant_id', $row['participant_id'])->where('study_id', $row['study_id'])
                ->get()->getResultArray();

            foreach ($sessions as $session) {
                $this->db->table('session_progress')->insert([
                    'session_id'              => $session['id'],
                    'current_level_id'        => $row['current_level_id'],
                    'current_node_id'         => $row['current_node_id'],
                    'unlocked_level_sequence' => $row['unlocked_level_sequence'],
                    'completed_nodes'         => $row['completed_nodes'],
                    'completed_levels'        => $row['completed_levels'],
                    'total_stars'             => $row['total_stars'],
                ]);
            }
        }

        foreach (['game_sessions' => ['field_session_id', 'meeting_no'], 'participants' => ['rombel'],
            'schools' => ['research_code'], 'research_studies' => ['field_mode', 'meeting_plan']] as $table => $columns) {
            if ($this->isMysql()) {
                foreach ($this->db->getForeignKeyData($table) as $key) {
                    if (array_intersect((array) $key->column_name, $columns) !== []) {
                        $this->forge->dropForeignKey($table, $key->constraint_name);
                    }
                }
            }

            foreach ($this->db->getIndexData($table) as $index) {
                if (array_intersect((array) $index->fields, $columns) !== [] && $index->type !== 'PRIMARY') {
                    $this->forge->dropKey($table, $index->name, false);
                }
            }

            $this->forge->dropColumn($table, $columns);
        }

        foreach (['participant_progress', 'participant_code_counters', 'participant_seats',
            'field_session_members', 'field_session_tokens', 'field_sessions'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }

    // ------------------------------------------------------------ tables

    private function createFieldSessions(): void
    {
        $this->forge->addField([
            'id'              => $this->id(),
            'study_id'        => $this->ref(false),
            'code'            => ['type' => 'VARCHAR', 'constraint' => 20],
            'school_id'       => $this->ref(false),
            'rombel'          => ['type' => 'CHAR', 'constraint' => 1],
            'meeting_no'      => ['type' => 'TINYINT', 'unsigned' => true],
            'makeup_no'       => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'main_session_id' => $this->ref(true),
            'session_date'    => ['type' => 'DATE'],
            'opens_at'        => ['type' => 'DATETIME'],
            'closes_at'       => ['type' => 'DATETIME'],
            'closed_at'       => ['type' => 'DATETIME', 'null' => true],
            'region_code'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_by'      => $this->ref(true),
            'closed_by'       => $this->ref(true),
            'created_at'      => $this->stamp(false),
            'updated_at'      => $this->stamp(true),
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['study_id', 'code']);
        $this->forge->addKey(['school_id', 'rombel', 'meeting_no']);
        $this->forge->addKey(['opens_at', 'closes_at']);
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('school_id', 'schools', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('main_session_id', 'field_sessions', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'staff_users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('closed_by', 'staff_users', 'id', '', 'SET NULL');
        $this->createTable('field_sessions');
    }

    private function createTokens(): void
    {
        $this->forge->addField([
            'id'               => $this->id(),
            'field_session_id' => $this->ref(false),
            'token'            => ['type' => 'CHAR', 'constraint' => 6],
            'created_by'       => $this->ref(true),
            'created_at'       => $this->stamp(false),
            'revoked_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('token');
        $this->forge->addKey(['field_session_id', 'revoked_at']);
        $this->forge->addForeignKey('field_session_id', 'field_sessions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'staff_users', 'id', '', 'SET NULL');
        $this->createTable('field_session_tokens');
    }

    private function createMembers(): void
    {
        $this->forge->addField([
            'field_session_id' => $this->ref(false),
            'participant_id'   => $this->ref(false),
            'added_by'         => $this->ref(true),
            'created_at'       => $this->stamp(false),
        ]);
        $this->forge->addPrimaryKey(['field_session_id', 'participant_id']);
        $this->forge->addKey('participant_id');
        $this->forge->addForeignKey('field_session_id', 'field_sessions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('added_by', 'staff_users', 'id', '', 'SET NULL');
        $this->createTable('field_session_members');
    }

    private function createSeats(): void
    {
        $this->forge->addField([
            'id'             => $this->id(),
            'participant_id' => $this->ref(false),
            'study_id'       => $this->ref(false),
            'row_no'         => ['type' => 'TINYINT', 'unsigned' => true],
            'seat_no'        => ['type' => 'TINYINT', 'unsigned' => true],
            'booklet'        => ['type' => 'CHAR', 'constraint' => 2],
            'pre_form'       => ['type' => 'CHAR', 'constraint' => 1],
            'post_form'      => ['type' => 'CHAR', 'constraint' => 1],
            'entered_by'     => $this->ref(true),
            'entered_at'     => $this->stamp(false),
            'updated_at'     => $this->stamp(true),
            'locked_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['participant_id', 'study_id']);
        $this->forge->addKey('study_id');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('entered_by', 'staff_users', 'id', '', 'SET NULL');
        $this->createTable('participant_seats');
    }

    private function createCodeCounters(): void
    {
        $this->forge->addField([
            'id'        => $this->id(),
            'school_id' => $this->ref(false),
            'rombel'    => ['type' => 'CHAR', 'constraint' => 1],
            'next_no'   => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['school_id', 'rombel']);
        $this->forge->addForeignKey('school_id', 'schools', 'id', '', 'CASCADE');
        $this->createTable('participant_code_counters');
    }

    private function createParticipantProgress(): void
    {
        $this->forge->addField([
            'id'                      => $this->id(),
            'participant_id'          => $this->ref(false),
            'study_id'                => $this->ref(false),
            'current_level_id'        => $this->ref(true),
            'current_node_id'         => $this->ref(true),
            'unlocked_level_sequence' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'completed_nodes'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'completed_levels'        => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'total_stars'             => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'updated_at'              => $this->stamp(true),
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['participant_id', 'study_id']);
        $this->forge->addKey('study_id');
        $this->forge->addKey('updated_at');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('current_level_id', 'levels', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('current_node_id', 'challenge_nodes', 'id', '', 'SET NULL');
        $this->createTable('participant_progress');
    }

    private function addColumns(): void
    {
        $this->forge->addColumn('game_sessions', [
            'field_session_id' => $this->ref(true) + ['after' => 'study_id'],
            'meeting_no'       => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'field_session_id'],
        ]);
        $this->forge->addColumn('participants', [
            'rombel' => ['type' => 'CHAR', 'constraint' => 1, 'null' => true, 'after' => 'school_name_snapshot'],
        ]);
        $this->forge->addColumn('schools', [
            'research_code' => ['type' => 'VARCHAR', 'constraint' => 3, 'null' => true, 'after' => 'code'],
        ]);
        $this->forge->addColumn('research_studies', [
            'field_mode'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'unlock_mode'],
            'meeting_plan' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'lima_pertemuan', 'after' => 'field_mode'],
        ]);

        $this->forge->addKey(['field_session_id', 'participant_id']);

        if ($this->isMysql()) {
            $this->forge->addForeignKey('field_session_id', 'field_sessions', 'id', '', 'RESTRICT');
        }

        $this->forge->processIndexes('game_sessions');

        $this->forge->addUniqueKey('research_code');
        $this->forge->processIndexes('schools');
    }

    // ------------------------------------------------------------ backfill

    /** One `participant_progress` row per student and study that has a game session. */
    private function backfillProgress(): void
    {
        $levels = [];

        foreach ($this->db->table('levels')->select('id, sequence')->where('is_active', 1)->orderBy('sequence', 'ASC')->get()->getResultArray() as $level) {
            $nodeIds = array_map('intval', array_column(
                $this->db->table('challenge_nodes')->select('id')->where('level_id', $level['id'])->where('is_active', 1)->get()->getResultArray(),
                'id',
            ));

            $levels[] = ['sequence' => (int) $level['sequence'], 'node_ids' => $nodeIds];
        }

        $freeStudies = array_map('intval', array_column(
            $this->db->table('research_studies')->select('id')->where('unlock_mode', 'free')->get()->getResultArray(),
            'id',
        ));

        $pairs = $this->db->table('game_sessions')
            ->select('participant_id, study_id')
            ->groupBy(['participant_id', 'study_id'])
            ->get()
            ->getResultArray();

        foreach ($pairs as $pair) {
            $participantId = (int) $pair['participant_id'];
            $studyId       = (int) $pair['study_id'];

            $best = [];

            $attempts = $this->db->table('challenge_attempts ca')
                ->select('ca.challenge_node_id, ca.stars')
                ->join('game_sessions gs', 'gs.id = ca.session_id')
                ->where('gs.participant_id', $participantId)
                ->where('gs.study_id', $studyId)
                ->where('ca.status', 'completed')
                ->get()
                ->getResultArray();

            foreach ($attempts as $attempt) {
                $node        = (int) $attempt['challenge_node_id'];
                $best[$node] = max($best[$node] ?? 0, (int) $attempt['stars']);
            }

            $summary = ProgressRules::summarize($best, $levels, in_array($studyId, $freeStudies, true));

            $latest = $this->db->table('session_progress sp')
                ->select('sp.current_level_id, sp.current_node_id')
                ->join('game_sessions gs', 'gs.id = sp.session_id')
                ->where('gs.participant_id', $participantId)
                ->where('gs.study_id', $studyId)
                ->orderBy('gs.last_active_at', 'DESC')
                ->orderBy('gs.id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            $this->db->table('participant_progress')->insert($summary + [
                'participant_id'   => $participantId,
                'study_id'         => $studyId,
                'current_level_id' => $latest['current_level_id'] ?? null,
                'current_node_id'  => $latest['current_node_id'] ?? null,
            ]);
        }
    }

    // ------------------------------------------------------------ helpers

    /** createGelitaTable() on MySQL/MariaDB; SQLite (tests) takes no table attributes. */
    private function createTable(string $table): void
    {
        if ($this->isMysql()) {
            $this->createGelitaTable($table);

            return;
        }

        $this->forge->createTable($table, true);
    }

    private function isMysql(): bool
    {
        return $this->db->DBDriver === 'MySQLi';
    }

    /** DATETIME(6) defaulting to now (and on update); SQLite gets the plain form. */
    private function stamp(bool $onUpdate): array
    {
        if (! $this->isMysql()) {
            return ['type' => 'DATETIME', 'null' => false, 'default' => new RawSql('CURRENT_TIMESTAMP')];
        }

        return $onUpdate ? $this->datetimeNowOnUpdate() : $this->datetimeNow();
    }
}
