<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * expires_at diisi Model saat insert: created_at + Config\Gelita::$exportRetentionDays.
 */
class CreateDataExports extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => $this->id(),
            'requested_by'  => $this->ref(),
            'study_id'      => $this->ref(true),
            'format'        => ['type' => 'VARCHAR', 'constraint' => 10],
            'scope_json'    => $this->json(false),
            'anonymized'    => $this->flag(1),
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'queued'],
            'row_count'     => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'file_path'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'file_sha256'   => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'error_message' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'    => $this->datetimeNow(),
            'completed_at'  => $this->datetime(),
            'expires_at'    => $this->datetime(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('requested_by');
        $this->forge->addKey('study_id');
        $this->forge->addKey('status');
        $this->forge->addKey('expires_at');
        $this->forge->addForeignKey('requested_by', 'staff_users', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'SET NULL');
        $this->createGelitaTable('data_exports');
    }

    public function down(): void
    {
        $this->dropGelitaTable('data_exports');
    }
}
