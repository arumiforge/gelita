<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel session CI4 (DatabaseHandler).
 * ip_address 64 karakter: cukup untuk IP mentah (DatabaseHandler bawaan) maupun
 * SHA-256 dari App\Libraries\HashedIpSessionHandler bila handler itu dipasang.
 */
class CreateCiSessions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false],
            '`timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL',
            'data'       => ['type' => 'BLOB', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('timestamp');
        $this->forge->createTable('ci_sessions', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('ci_sessions', true);
    }
}
