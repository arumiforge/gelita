<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateSchools extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => $this->id(),
            'code'          => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 200],
            'country_code'  => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true, 'default' => 'ID'],
            'province_code' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'district_code' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'is_active'     => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('name');
        $this->forge->addKey('country_code');
        $this->forge->addKey('province_code');
        $this->forge->addKey('district_code');
        $this->createGelitaTable('schools');
    }

    public function down(): void
    {
        $this->dropGelitaTable('schools');
    }
}
