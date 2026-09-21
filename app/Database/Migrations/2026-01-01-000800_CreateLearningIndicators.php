<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateLearningIndicators extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => $this->id(),
            'code'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'name_id'        => ['type' => 'VARCHAR', 'constraint' => 200],
            'name_en'        => ['type' => 'VARCHAR', 'constraint' => 200],
            'description_id' => ['type' => 'TEXT', 'null' => true],
            'description_en' => ['type' => 'TEXT', 'null' => true],
            'domain'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'is_active'      => $this->flag(1),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('domain');
        $this->createGelitaTable('learning_indicators');
    }

    public function down(): void
    {
        $this->dropGelitaTable('learning_indicators');
    }
}
