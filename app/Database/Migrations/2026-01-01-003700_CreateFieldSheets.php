<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;
use CodeIgniter\Database\RawSql;

/**
 * Research revision 10, part 3 (docs/revisi-10-analitik/29_IMPLEMENTASI_DATA_PENELITIAN.md
 * §10.4, §12): the typed field sheets L1–L6.
 *
 * - `field_sheet_imports`: one row per sheet per upload. `purpose` is `main`
 *   (the typing that counts) or `check` (the second typing, §10.5). A new
 *   main upload of the same sheet for the same field session supersedes the
 *   old one (`superseded_at`, `superseded_by`), set by a conditional UPDATE.
 *   The uploaded file is not kept, only its SHA-256.
 * - `field_sheet_rows`: the rows; join keys as columns (`participant_id`,
 *   `seat_no`, `started_at`, `ended_at`), everything else in `values_json`
 *   under the FieldSheetSpec column codes. `participant_id` cascades on
 *   delete, the deletion rule of part 4 (30 §7.3).
 */
class CreateFieldSheets extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => $this->id(),
            'field_session_id' => $this->ref(false),
            'sheet'            => ['type' => 'VARCHAR', 'constraint' => 30],
            'spec_version'     => ['type' => 'VARCHAR', 'constraint' => 20],
            'purpose'          => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'main'],
            'file_sha256'      => ['type' => 'CHAR', 'constraint' => 64],
            'row_count'        => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'warning_count'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'names_confirmed'  => $this->flag(0),
            'uploaded_by'      => $this->ref(true),
            'created_at'       => $this->stamp(),
            'superseded_at'    => ['type' => 'DATETIME', 'null' => true],
            'superseded_by'    => $this->ref(true),
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['field_session_id', 'sheet', 'purpose', 'superseded_at']);
        $this->forge->addForeignKey('field_session_id', 'field_sessions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'staff_users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('superseded_by', 'field_sheet_imports', 'id', '', 'SET NULL');
        $this->createTable('field_sheet_imports');

        $this->forge->addField([
            'id'             => $this->id(),
            'import_id'      => $this->ref(false),
            'row_no'         => ['type' => 'INT', 'unsigned' => true],
            'participant_id' => $this->ref(true),
            'seat_no'        => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'started_at'     => ['type' => 'DATETIME', 'constraint' => 3, 'null' => true],
            'ended_at'       => ['type' => 'DATETIME', 'constraint' => 3, 'null' => true],
            'values_json'    => $this->json(false),
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['import_id', 'row_no']);
        $this->forge->addKey(['participant_id', 'started_at']);
        $this->forge->addForeignKey('import_id', 'field_sheet_imports', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->createTable('field_sheet_rows');
    }

    public function down(): void
    {
        $this->forge->dropTable('field_sheet_rows', true);
        $this->forge->dropTable('field_sheet_imports', true);
    }

    private function createTable(string $table): void
    {
        if ($this->db->DBDriver === 'MySQLi') {
            $this->createGelitaTable($table);

            return;
        }

        $this->forge->createTable($table, true);
    }

    private function stamp(): array
    {
        return $this->db->DBDriver === 'MySQLi'
            ? $this->datetimeNow()
            : ['type' => 'DATETIME', 'null' => false, 'default' => new RawSql('CURRENT_TIMESTAMP')];
    }
}
