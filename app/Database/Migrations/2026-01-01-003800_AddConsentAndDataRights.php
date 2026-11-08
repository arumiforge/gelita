<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;
use CodeIgniter\Database\RawSql;

/**
 * Research revision 10, part 4 (docs/revisi-10-analitik/30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md §15):
 * coded consent per student, deactivated accounts, parents' data requests,
 * study closure, and export purges.
 *
 * up():
 * 1. new table `participant_consents` (one row per student, FK participants ON DELETE CASCADE);
 * 2. new columns `participants.deactivated_at`/`assent_version`/`corrected_at`;
 *    `research_studies.closed_at`/`consent_register_exported_at`/`feedback_names_checked_at`/`identities_replaced_at`;
 *    the parent-request columns on `data_deletion_requests`; `data_exports.purged_by_request_id`.
 *
 * `research_studies.retention_days` keeps its default 1825 and existing rows
 * are not changed (30 §22 no. 8); `ci_sessions` is not touched (30 §12, the
 * retention run removes raw IPs once `gelita.researchPrivacy` is on).
 *
 * down() drops the new table and columns. Data already deleted through a
 * parent's request does not come back.
 */
class AddConsentAndDataRights extends GelitaMigration
{
    private const COLUMNS = [
        'participants'           => ['deactivated_at', 'assent_version', 'corrected_at'],
        'research_studies'       => ['closed_at', 'consent_register_exported_at', 'feedback_names_checked_at', 'identities_replaced_at'],
        'data_deletion_requests' => ['kind', 'participant_code', 'received_at', 'verified_at', 'received_via', 'confirmed_by', 'confirmed_at', 'completed_at', 'checklist_json'],
        'data_exports'           => ['purged_by_request_id'],
    ];

    public function up(): void
    {
        $this->forge->addField([
            'participant_id'          => $this->ref(false),
            'study_id'                => $this->ref(false),
            'parent_consent_on'       => ['type' => 'DATE', 'null' => true],
            'recording_consent'       => $this->flag(0),
            'recording_withdrawn_on'  => ['type' => 'DATE', 'null' => true],
            'risk_items_consent'      => $this->flag(0),
            'risk_items_withdrawn_on' => ['type' => 'DATE', 'null' => true],
            'status'                  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'status_on'               => ['type' => 'DATE', 'null' => true],
            'entered_by'              => $this->ref(true),
            'updated_by'              => $this->ref(true),
            'created_at'              => $this->stamp(),
            // Written by the code with microseconds: the conditional save compares it (30 §5.2)
            'updated_at'              => $this->stamp(),
        ]);
        $this->forge->addPrimaryKey('participant_id');
        $this->forge->addKey(['study_id', 'status']);
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('entered_by', 'staff_users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'staff_users', 'id', '', 'SET NULL');

        $this->isMysql() ? $this->createGelitaTable('participant_consents') : $this->forge->createTable('participant_consents', true);

        $this->forge->addColumn('participants', [
            'deactivated_at' => $this->datetime(true),
            'assent_version' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'corrected_at'   => $this->datetime(true),
        ]);
        $this->forge->addColumn('research_studies', [
            'closed_at'                    => $this->datetime(true),
            'consent_register_exported_at' => $this->datetime(true),
            'feedback_names_checked_at'    => $this->datetime(true),
            'identities_replaced_at'       => $this->datetime(true),
        ]);
        $this->forge->addColumn('data_deletion_requests', [
            'kind'             => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'participant_code' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'received_at'      => $this->datetime(true),
            'verified_at'      => $this->datetime(true),
            'received_via'     => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'confirmed_by'     => $this->ref(true),
            'confirmed_at'     => $this->datetime(true),
            'completed_at'     => $this->datetime(true),
            // TEXT, not JSON: the tick is conditional on the exact text read (MySQL 8 compares JSON semantically)
            'checklist_json'   => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addColumn('data_exports', [
            'purged_by_request_id' => $this->ref(true),
        ]);

        if ($this->isMysql()) {
            $this->forge->addForeignKey('confirmed_by', 'staff_users', 'id', '', 'SET NULL');
            $this->forge->processIndexes('data_deletion_requests');
            $this->forge->addForeignKey('purged_by_request_id', 'data_deletion_requests', 'id', '', 'SET NULL');
            $this->forge->processIndexes('data_exports');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('participant_consents', true);

        foreach (self::COLUMNS as $table => $columns) {
            if ($this->isMysql()) {
                foreach ($this->db->getForeignKeyData($table) as $key) {
                    if (array_intersect((array) $key->column_name, $columns) !== []) {
                        $this->forge->dropForeignKey($table, $key->constraint_name);
                    }
                }
            }

            $this->forge->dropColumn($table, $columns);
        }
    }

    private function isMysql(): bool
    {
        return $this->db->DBDriver === 'MySQLi';
    }

    private function stamp(): array
    {
        return $this->isMysql()
            ? $this->datetimeNow()
            : ['type' => 'DATETIME', 'null' => false, 'default' => new RawSql('CURRENT_TIMESTAMP')];
    }
}
