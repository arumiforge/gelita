<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Studi penelitian. Satu studi aktif menentukan mode buka wilayah (`unlock_mode`)
 * dan periode pengambilan data.
 */
class CreateResearchStudies extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => $this->id(),
            'code'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 200],
            'description'    => ['type' => 'TEXT', 'null' => true],
            'year_label'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'retention_days' => ['type' => 'INT', 'unsigned' => true, 'default' => 1825],
            'default_locale' => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => 'id'],
            'unlock_mode'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'sequential'],
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
