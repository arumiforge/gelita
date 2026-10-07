<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Tabel `data_deletion_requests` (docs/01_DATABASE.md).
 */
class CreateDataDeletionRequests extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => $this->id(),
            'requested_by'   => $this->ref(false),
            'scope_json'     => $this->json(false),
            'reason'         => ['type' => 'TEXT', 'null' => true],
            'mode'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'soft'],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'preview'],
            'affected_count' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'approved_by'    => $this->ref(true),
            'executed_at'    => $this->datetime(true),
            'created_at'     => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('requested_by');
        $this->forge->addKey('approved_by');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('requested_by', 'staff_users', 'id');
        $this->forge->addForeignKey('approved_by', 'staff_users', 'id', '', 'SET NULL');
        $this->createGelitaTable('data_deletion_requests');
    }

    public function down(): void
    {
        $this->dropGelitaTable('data_deletion_requests');
    }
}
