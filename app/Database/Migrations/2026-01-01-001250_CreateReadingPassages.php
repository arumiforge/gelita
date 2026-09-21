<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateReadingPassages extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => $this->id(),
            'level_id'         => $this->ref(),
            'passage_key'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'title_id'         => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'title_en'         => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'body_id'          => ['type' => 'LONGTEXT'],
            'body_en'          => ['type' => 'LONGTEXT'],
            'media_asset_id'   => $this->ref(true),
            'reference_source' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'is_active'        => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('passage_key');
        $this->forge->addKey('level_id');
        $this->forge->addForeignKey('level_id', 'levels', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('media_asset_id', 'media_assets', 'id', '', 'SET NULL');
        $this->createGelitaTable('reading_passages');
    }

    public function down(): void
    {
        $this->dropGelitaTable('reading_passages');
    }
}
