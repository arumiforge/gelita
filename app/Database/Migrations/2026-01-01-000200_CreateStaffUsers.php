<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Akun panel (admin, guru). `must_change_password` memaksa ganti sandi sesudah
 * sandi sementara dari gelita:staff:password.
 */
class CreateStaffUsers extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => $this->id(),
            'username'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'                => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'password_hash'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'must_change_password' => $this->flag(0),
            'role'                 => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'guru'],
            'display_name'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'school_id'            => $this->ref(true),
            'is_active'            => $this->flag(1),
            'failed_login_count'   => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'locked_until'         => $this->datetime(true),
            'last_login_at'        => $this->datetime(true),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('username');
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('role');
        $this->forge->addKey('is_active');
        $this->forge->addForeignKey('school_id', 'schools', 'id', '', 'SET NULL');
        $this->createGelitaTable('staff_users');
    }

    public function down(): void
    {
        $this->dropGelitaTable('staff_users');
    }
}
