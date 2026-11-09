<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Research revision 10, part 8 (docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md §11):
 * the pre/post test module, the digital-access questionnaire, the online-risk
 * items (T11/T12), and the attitude scale in Balai Refleksi.
 *
 * up():
 * 1. instrument content, versioned (§6): `instrument_versions`,
 *    `instrument_stimuli`, `instrument_items`; filled by `gelita:instrumen:impor`;
 * 2. the students' answers (§7): `instrument_sittings` (one per student, study,
 *    occasion and kind), `instrument_blocks` (hidden time limits, `p1`/`p23`),
 *    `instrument_parts`, `instrument_responses` (one row per answer unit).
 *    Every answer table cascades from `participants` (30 §7.3, §15);
 *    `RetentionService::HARD_ORDER` still deletes them one by one;
 * 3. `field_sessions.test_started_at`/`test_started_by` (the start-test button, §5.3),
 *    `research_studies.instrument_settings` (JSON, hidden limits, §7.4),
 *    `participant_feedback.rating` nullable (feedback during a study, §9.4).
 *
 * `research_studies.test_rapid_guess_ms` belongs to part 7's migration (owner,
 * 4 November 2026) and is not created here.
 *
 * down() drops the new tables and columns. A `rating` that is already empty
 * cannot come back: down() writes 0 into it before making the column NOT NULL again.
 */
class CreateInstrumentModule extends GelitaMigration
{
    /** Answer tables, children first (drop order). */
    private const ANSWER_TABLES = ['instrument_responses', 'instrument_parts', 'instrument_blocks', 'instrument_sittings'];

    /** Content tables, children first. */
    private const CONTENT_TABLES = ['instrument_items', 'instrument_stimuli', 'instrument_versions'];

    public function up(): void
    {
        // ------------------------------------------------------------ content
        $this->forge->addField([
            'id'           => $this->id(),
            'instrument'   => ['type' => 'VARCHAR', 'constraint' => 10],
            'version'      => ['type' => 'VARCHAR', 'constraint' => 20],
            'content_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'needs_verification'],
            'is_active'    => $this->flag(0),
            // Texts outside items (opening instructions, part titles, examples order), JSON
            'meta_json'    => $this->json(),
            'imported_at'  => $this->datetime(false),
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['instrument', 'version']);
        $this->forge->addKey(['instrument', 'is_active']);
        $this->create('instrument_versions');

        $this->forge->addField([
            'id'         => $this->id(),
            'version_id' => $this->ref(false),
            'code'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'show_json'  => $this->json(false),
            'image_slot' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['version_id', 'code']);
        $this->forge->addForeignKey('version_id', 'instrument_versions', 'id', '', 'CASCADE');
        $this->create('instrument_stimuli');

        $this->forge->addField([
            'id'          => $this->id(),
            'version_id'  => $this->ref(false),
            'code'        => ['type' => 'VARCHAR', 'constraint' => 20],
            'part'        => ['type' => 'VARCHAR', 'constraint' => 10],
            'form'        => ['type' => 'CHAR', 'constraint' => 1, 'null' => true],
            'position'    => ['type' => 'SMALLINT', 'unsigned' => true],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 10],
            'stimulus_id' => $this->ref(true),
            'show_json'   => $this->json(false),
            'key_json'    => $this->json(),
            'units_json'  => $this->json(false),
            'cell_json'   => $this->json(),
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['version_id', 'code']);
        $this->forge->addKey(['version_id', 'part', 'form', 'position']);
        $this->forge->addForeignKey('version_id', 'instrument_versions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('stimulus_id', 'instrument_stimuli', 'id', '', 'SET NULL');
        $this->create('instrument_items');

        // ------------------------------------------------------------ answers
        $this->forge->addField([
            'id'                => $this->id(),
            'participant_id'    => $this->ref(false),
            'study_id'          => $this->ref(false),
            'field_session_id'  => $this->ref(true),
            'occasion'          => ['type' => 'VARCHAR', 'constraint' => 4],
            'kind'              => ['type' => 'VARCHAR', 'constraint' => 14],
            'form'              => ['type' => 'CHAR', 'constraint' => 1, 'null' => true],
            'version_id'        => $this->ref(false),
            'is_makeup'         => $this->flag(0),
            'late'              => $this->flag(0),
            'is_preview'        => $this->flag(0),
            'risk_offered'      => $this->nullableFlag(),
            'risk_reconsent'    => $this->nullableFlag(),
            'risk_reconsent_at' => $this->datetime(true),
            'started_at'        => $this->datetime(false),
            'submitted_at'      => $this->datetime(true),
            'last_item_id'      => $this->ref(true),
            // Written by the code on every change (34 §18 no. 6: MAX(updated_at) fingerprint of part 9)
            'updated_at'        => ['type' => 'DATETIME', 'constraint' => 3, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['participant_id', 'study_id', 'occasion', 'kind', 'is_preview']);
        $this->forge->addKey(['study_id', 'kind']);
        $this->forge->addKey('field_session_id');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('field_session_id', 'field_sessions', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('version_id', 'instrument_versions', 'id', '', 'RESTRICT');
        $this->create('instrument_sittings');

        $this->forge->addField([
            'id'            => $this->id(),
            'sitting_id'    => $this->ref(false),
            'block'         => ['type' => 'VARCHAR', 'constraint' => 4],
            'started_at'    => $this->datetime(false),
            'limit_minutes' => ['type' => 'SMALLINT', 'unsigned' => true],
            'extra_minutes' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'closed_at'     => $this->datetime(true),
            'closed_reason' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['sitting_id', 'block']);
        $this->forge->addForeignKey('sitting_id', 'instrument_sittings', 'id', '', 'CASCADE');
        $this->create('instrument_blocks');

        $this->forge->addField([
            'id'              => $this->id(),
            'sitting_id'      => $this->ref(false),
            'part'            => ['type' => 'VARCHAR', 'constraint' => 10],
            'block_id'        => $this->ref(true),
            'opened_at'       => $this->datetime(false),
            'submitted_at'    => $this->datetime(true),
            'closed_at'       => $this->datetime(true),
            'closed_by_limit' => $this->flag(0),
            'raw_score'       => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'answered_count'  => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['sitting_id', 'part']);
        $this->forge->addForeignKey('sitting_id', 'instrument_sittings', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('block_id', 'instrument_blocks', 'id', '', 'CASCADE');
        $this->create('instrument_parts');

        $this->forge->addField([
            'id'              => $this->id(),
            'part_id'         => $this->ref(false),
            'sitting_id'      => $this->ref(false),
            'item_id'         => $this->ref(false),
            'unit'            => ['type' => 'VARCHAR', 'constraint' => 40],
            'response'        => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'score'           => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'error_codes'     => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'not_reached'     => $this->flag(0),
            'first_shown_at'  => $this->datetime(true),
            'shown_at'        => $this->datetime(true),
            'answered_at'     => $this->datetime(true),
            'view_count'      => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'time_on_item_ms' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'last_view_ms'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'answer_changes'  => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'revision'        => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'updated_at'      => ['type' => 'DATETIME', 'constraint' => 3, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['sitting_id', 'unit']);
        $this->forge->addKey('part_id');
        $this->forge->addKey('item_id');
        $this->forge->addForeignKey('part_id', 'instrument_parts', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('sitting_id', 'instrument_sittings', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'instrument_items', 'id', '', 'RESTRICT');
        $this->create('instrument_responses');

        // ------------------------------------------------------------ changed tables
        $this->forge->addColumn('field_sessions', [
            'test_started_at' => $this->datetime(true),
            'test_started_by' => $this->ref(true),
        ]);
        $this->forge->addColumn('research_studies', [
            'instrument_settings' => $this->json(),
        ]);
        $this->forge->modifyColumn('participant_feedback', [
            'rating' => ['name' => 'rating', 'type' => 'TINYINT', 'unsigned' => true, 'null' => true],
        ]);

        if ($this->isMysql()) {
            $this->forge->addForeignKey('test_started_by', 'staff_users', 'id', '', 'SET NULL');
            $this->forge->processIndexes('field_sessions');
        }
    }

    public function down(): void
    {
        foreach ([...self::ANSWER_TABLES, ...self::CONTENT_TABLES] as $table) {
            $this->forge->dropTable($table, true);
        }

        if ($this->isMysql()) {
            foreach ($this->db->getForeignKeyData('field_sessions') as $key) {
                if (in_array('test_started_by', (array) $key->column_name, true)) {
                    $this->forge->dropForeignKey('field_sessions', $key->constraint_name);
                }
            }
        }

        $this->forge->dropColumn('field_sessions', ['test_started_at', 'test_started_by']);
        $this->forge->dropColumn('research_studies', ['instrument_settings']);

        // An empty rating cannot come back: 0 stands in for it (34 §11.2)
        $this->db->table('participant_feedback')->where('rating', null)->update(['rating' => 0]);
        $this->forge->modifyColumn('participant_feedback', [
            'rating' => ['name' => 'rating', 'type' => 'TINYINT', 'unsigned' => true, 'null' => false],
        ]);
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
