<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateAuditLogs extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => $this->id(),
            'staff_user_id' => $this->ref(true),
            'action'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'target_type'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'target_id'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'metadata_json' => $this->json(),
            'ip_hash'       => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'occurred_at'   => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('staff_user_id');
        $this->forge->addKey('action');
        $this->forge->addKey('occurred_at');
        $this->forge->addForeignKey('staff_user_id', 'staff_users', 'id', '', 'SET NULL');
        $this->createGelitaTable('audit_logs');
    }

    public function down(): void
    {
        $this->dropGelitaTable('audit_logs');
    }
}
