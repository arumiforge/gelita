<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Research revision 10, part 9 (docs/revisi-10-analitik/35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md §12):
 * the derived data layer. Adds tables and columns only; deletes nothing.
 *
 * up():
 * 1. `research_studies`: `thresholds_locked_at` (the G4 lock, K2; the
 *    threshold columns themselves are part 7's migration 004100),
 *    `derived_run_id` (the run every reader uses, K7), `derived_running_since`
 *    (the claim of a running computation, §9);
 * 2. `derived_runs`: one row per computation of one study (K6);
 * 3. the eight `derived_*` tables, one per download sheet `turunan_*`; every
 *    row carries `run_id` and `dictionary_version`, cascades from its run and
 *    from `participants` (30 §7.3); codes are joined in the sheets, never stored;
 * 4. `data_exports.derived_run_id`: the run a download used (§9).
 *
 * The column specs below are frozen with this migration; the test schema
 * (tests/_support/Database/ResetTables.php) carries the same columns.
 *
 * down() drops the tables and the columns.
 */
class CreateDerivedLayer extends GelitaMigration
{
    /**
     * Derived tables => [key columns, value columns]. Spec: `id` (BIGINT
     * unsigned), `int`, `flag` (TINYINT 1), `dec` (DECIMAL 8,4), `date`,
     * `dt` (DATETIME 3), `s:N` (VARCHAR N). Every value column is nullable:
     * empty means "no data", never 0 (11 → Data hilang).
     */
    public const TABLES = [
        'derived_participants' => [['participant_id'], [
            'consent_ok' => 'flag', 'withdrawn_on' => 'date', 'withdrawn_at' => 'dt', 'cutoff_basis' => 's:20',
            'is_pilot' => 'flag', 'analysable' => 'flag', 'complete' => 'flag', 'rapid_share' => 'dec',
            'regions_completed' => 'int', 'challenges_completed' => 'int', 'scored_challenges_completed' => 'int',
            'registration_corrected' => 'flag',
            'questionnaire_status' => 's:10', 'own_phone' => 'flag', 'device_count' => 'int', 'access_index' => 'int',
            'activity_breadth' => 'int', 'own_accounts' => 'int', 'social_media_own' => 'flag', 'social_media_use' => 'flag',
            'parent_active' => 'dec', 'parent_rules' => 'int', 'safety_info_sources' => 'int', 'game_time_group' => 's:10',
            'q_flags' => 's:60',
            'test_status' => 's:12', 'pre_d1' => 'int', 'pre_d2' => 'int', 'pre_d3' => 'int', 'post_d1' => 'int', 'post_d2' => 'int', 'post_d3' => 'int',
            'pre_capped' => 's:10', 'post_capped' => 's:10',
            'sikap_score' => 'dec', 'sikap_sk1' => 'dec', 'sikap_sk2' => 'dec', 'sikap_sk3' => 'dec', 'sikap_sk4' => 'dec',
            'sikap_n_answered' => 'int', 'sikap_flag_straight' => 'flag', 'sikap_missing_reason' => 's:20',
        ]],
        'derived_field_sessions' => [['participant_id', 'field_session_id'], [
            'attended' => 'flag', 'session_total_ms' => 'int', 'session_active_ms' => 'int', 'library_pages' => 'int', 'library_ms' => 'int',
            'headset_share' => 'dec', 'internet' => 's:100', 'noise' => 's:20', 'teacher_present' => 's:100',
            'minutes_available' => 'int', 'minutes_used' => 'int', 'l4_observations' => 'int',
        ]],
        'derived_challenges' => [['participant_id', 'challenge_node_id'], [
            'completion_status' => 's:12', 'skipped_failed_count' => 'int', 'skipped_left_count' => 'int', 'closed_meeting_no' => 'int',
        ]],
        'derived_attempts' => [['attempt_id', 'participant_id'], [
            'is_scored_attempt' => 'flag', 'is_retake' => 'flag', 'segments' => 'int', 'total_ms' => 'int', 'hidden_ms' => 'int',
            'idle_excess_ms' => 'int', 'active_ms' => 'int', 'cumulative_active_ms' => 'int', 'hint_count' => 'int',
            'instruction_replays' => 'int', 'material_returns' => 'int', 'exit_count' => 'int', 'reading_max_pct' => 'int',
            'meeting_no' => 'int', 'field_session_id' => 'id',
        ]],
        'derived_items' => [['participant_id', 'challenge_item_id'], [
            'first_try_score' => 'int', 'first_try_correct' => 'flag', 'missing_reason' => 's:12', 'final_correct' => 'flag', 'final_score' => 'int',
            'tries_used' => 'int', 'solved_on_try' => 'int', 'censored' => 'flag', 'censor_reason' => 's:10',
            'first_error_code' => 's:12', 'first_error_code_analysis' => 's:12', 'change_after_feedback' => 's:15', 'change_after_feedback_2' => 's:15',
            'answer_unchanged' => 'flag', 'score_change' => 'int', 'near_miss_count' => 'int', 'empty_tap_count' => 'int',
            'item_total_ms' => 'int', 'item_active_ms' => 'int', 'first_input_ms' => 'int', 'last_input_ms' => 'int',
            'first_action_key' => 's:10', 'first_action_score' => 'int', 'first_action_code' => 's:12', 'first_reason_code' => 's:12',
            'action_reason_pattern' => 's:20', 'reason_level' => 'int', 'shared_device' => 'flag', 'helped_content' => 'flag', 'rapid_guess' => 'flag',
        ]],
        'derived_tries' => [['try_id', 'participant_id'], [
            'attempt_id' => 'id', 'field_session_id' => 'id', 'is_scored_attempt' => 'flag', 'is_retake' => 'flag', 'scored_try_no' => 'int', 'anomaly' => 's:12',
            'error_code' => 's:12', 'error_code_at_try' => 's:12', 'decision_ms' => 'int', 'rapid_guess' => 'flag',
            'reason_decision_ms' => 'int', 'reason_rapid' => 'flag', 'error_code_analysis' => 's:12', 'last_try_exhausted' => 'flag',
            'hint_before' => 'flag', 'item_hint_before' => 'flag', 'item_narration_before' => 'flag', 'reason_code' => 's:12', 'action_score' => 'int',
            'shared_device' => 'flag', 'helped_content' => 'flag', 'helped_technical' => 'flag', 'encouraged' => 'flag', 'disturbed' => 'flag',
            'deviation_code' => 's:60', 'outside_session' => 'flag',
        ]],
        'derived_test_responses' => [['instrument_response_id', 'participant_id'], [
            'decision_ms' => 'int', 'rapid_guess' => 'flag',
        ]],
        'derived_observations' => [['field_sheet_row_id', 'participant_id'], [
            'attempt_id' => 'id', 'challenge_item_id' => 'id',
        ]],
    ];

    public function up(): void
    {
        $this->forge->addField([
            'id'                 => $this->id(),
            'study_id'           => $this->ref(false),
            'status'             => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'running'],
            'dictionary_version' => ['type' => 'VARCHAR', 'constraint' => 30],
            'idle_threshold_ms'  => ['type' => 'INT', 'unsigned' => true],
            'rapid_guess_ms'     => ['type' => 'INT', 'unsigned' => true],
            'test_rapid_guess_ms' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'input_fingerprint'  => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'participant_count'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'notes_json'         => $this->json(),
            'error'              => ['type' => 'TEXT', 'null' => true],
            'started_by'         => $this->ref(true),
            'via'                => ['type' => 'VARCHAR', 'constraint' => 10],
            'started_at'         => ['type' => 'DATETIME', 'constraint' => 3, 'null' => false],
            'finished_at'        => ['type' => 'DATETIME', 'constraint' => 3, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['study_id', 'status']);
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('started_by', 'staff_users', 'id', '', 'SET NULL');
        $this->create('derived_runs');

        foreach (self::TABLES as $table => [$keys, $columns]) {
            $fields = ['run_id' => $this->ref(false)];

            foreach ($keys as $key) {
                $fields[$key] = $this->ref(false);
            }

            $fields['dictionary_version'] = ['type' => 'VARCHAR', 'constraint' => 30];

            foreach ($columns as $name => $spec) {
                $fields[$name] = self::field($spec);
            }

            $this->forge->addField($fields);
            // Primary key (run, row key): participant_id is part of it only where 35 §12 names it
            $this->forge->addPrimaryKey(['run_id', ...array_values(array_diff($keys, $keys[0] === 'participant_id' ? [] : ['participant_id']))]);
            $this->forge->addKey('participant_id');
            $this->forge->addForeignKey('run_id', 'derived_runs', 'id', '', 'CASCADE');
            $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
            $this->create($table);
        }

        $this->forge->addColumn('research_studies', [
            'thresholds_locked_at'  => ['type' => 'DATETIME', 'null' => true],
            'derived_run_id'        => $this->ref(true),
            'derived_running_since' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addColumn('data_exports', [
            'derived_run_id' => $this->ref(true),
        ]);

        if ($this->isMysql()) {
            foreach (['research_studies', 'data_exports'] as $table) {
                $this->db->query('ALTER TABLE ' . $this->db->protectIdentifiers($table, true)
                    . ' ADD CONSTRAINT ' . $this->db->escapeIdentifiers($table . '_derived_run_id_foreign')
                    . ' FOREIGN KEY (derived_run_id) REFERENCES ' . $this->db->protectIdentifiers('derived_runs', true) . ' (id) ON DELETE SET NULL');
            }
        }
    }

    public function down(): void
    {
        if ($this->isMysql()) {
            foreach (['research_studies', 'data_exports'] as $table) {
                $this->db->query('ALTER TABLE ' . $this->db->protectIdentifiers($table, true) . ' DROP FOREIGN KEY ' . $this->db->escapeIdentifiers($table . '_derived_run_id_foreign'));
            }
        }

        $this->forge->dropColumn('data_exports', ['derived_run_id']);
        $this->forge->dropColumn('research_studies', ['thresholds_locked_at', 'derived_run_id', 'derived_running_since']);

        foreach (array_reverse(array_keys(self::TABLES)) as $table) {
            $this->forge->dropTable($table, true);
        }

        $this->forge->dropTable('derived_runs', true);
    }

    /** @return array<string, mixed> forge field of one spec */
    private static function field(string $spec): array
    {
        return match (true) {
            $spec === 'id'   => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            $spec === 'int'  => ['type' => 'INT', 'null' => true],
            $spec === 'flag' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            $spec === 'dec'  => ['type' => 'DECIMAL', 'constraint' => '8,4', 'null' => true],
            $spec === 'date' => ['type' => 'DATE', 'null' => true],
            $spec === 'dt'   => ['type' => 'DATETIME', 'constraint' => 3, 'null' => true],
            default          => ['type' => 'VARCHAR', 'constraint' => (int) substr($spec, 2), 'null' => true],
        };
    }

    private function create(string $table): void
    {
        $this->isMysql() ? $this->createGelitaTable($table) : $this->forge->createTable($table, true);
    }

    private function isMysql(): bool
    {
        return $this->db->DBDriver === 'MySQLi';
    }
}
