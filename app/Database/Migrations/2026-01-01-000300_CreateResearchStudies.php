<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateResearchStudies extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => $this->id(),
            'code'                => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'                => ['type' => 'VARCHAR', 'constraint' => 200],
            'description'         => ['type' => 'TEXT', 'null' => true],
            'year_label'          => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'status'              => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'retention_days'      => ['type' => 'INT', 'unsigned' => true, 'default' => 1825],
            'default_locale'      => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => 'id'],
            'unlock_mode'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'sequential'],
            'item_selection_mode' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'fixed'],
            'require_consent'     => $this->flag(1),
            'active_phase_code'   => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'umum'],
            'allow_phase_choice'  => $this->flag(0),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('year_label');
        $this->forge->addKey('status');
        $this->createGelitaTable('research_studies');
    }

    public function down(): void
    {
        $this->dropGelitaTable('research_studies');
    }
}
