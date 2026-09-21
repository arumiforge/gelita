<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateParticipants extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                       => $this->id(),
            'participant_code'         => ['type' => 'VARCHAR', 'constraint' => 40],
            'username'                 => ['type' => 'VARCHAR', 'constraint' => 30],
            'password_hash'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'must_change_password'     => $this->flag(0),
            'display_name'             => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'age'                      => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'gender'                   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'class_level'              => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'school_id'                => $this->ref(true),
            'school_name_snapshot'     => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'country_code'             => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true, 'default' => 'ID'],
            'country_name_snapshot'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'province_code'            => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'province_name_snapshot'   => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'district_code'            => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'district_name_snapshot'   => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'pw_first_submit_criteria' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'pw_weak_submit_count'     => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'failed_login_count'       => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'locked_until'             => $this->datetime(),
            'last_login_at'            => $this->datetime(),
            'password_changed_at'      => $this->datetime(),
        ] + $this->timestamps() + [
            'deleted_at'               => $this->datetime(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('participant_code');
        $this->forge->addUniqueKey('username');
        $this->forge->addKey('age');
        $this->forge->addKey('gender');
        $this->forge->addKey('class_level');
        $this->forge->addKey('school_id');
        $this->forge->addKey('school_name_snapshot');
        $this->forge->addKey('country_code');
        $this->forge->addKey('province_code');
        $this->forge->addKey('district_code');
        $this->forge->addKey('deleted_at');
        $this->forge->addForeignKey('school_id', 'schools', 'id', '', 'SET NULL');
        $this->createGelitaTable('participants');
    }

    public function down(): void
    {
        $this->dropGelitaTable('participants');
    }
}
