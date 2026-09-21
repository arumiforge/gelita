<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateResearchPhases extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'        => $this->id(),
            'study_id'  => $this->ref(),
            'code'      => ['type' => 'VARCHAR', 'constraint' => 20],
            'sequence'  => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'label_id'  => ['type' => 'VARCHAR', 'constraint' => 150],
            'label_en'  => ['type' => 'VARCHAR', 'constraint' => 150],
            'is_active' => $this->flag(1),
        ]);

        $this->forge->addPrimaryKey('id');
        // (study_id, code) juga berfungsi sebagai index study_id
        $this->forge->addUniqueKey(['study_id', 'code']);
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'CASCADE');
        $this->createGelitaTable('research_phases');
    }

    public function down(): void
    {
        $this->dropGelitaTable('research_phases');
    }
}
