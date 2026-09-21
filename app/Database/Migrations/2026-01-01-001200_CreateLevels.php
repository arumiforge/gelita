<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * FK map/background/badge → media_assets dipasang di
 * 2026-01-01-002900_AddLevelMediaForeignKeys.
 */
class CreateLevels extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => $this->id(),
            'sequence'            => ['type' => 'TINYINT', 'unsigned' => true],
            'code'                => ['type' => 'VARCHAR', 'constraint' => 40],
            'name_id'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'name_en'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'difficulty'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'focus_id'            => ['type' => 'TEXT'],
            'focus_en'            => ['type' => 'TEXT'],
            'cp_id'               => ['type' => 'TEXT', 'null' => true],
            'cp_en'               => ['type' => 'TEXT', 'null' => true],
            'tp_id'               => ['type' => 'TEXT', 'null' => true],
            'tp_en'               => ['type' => 'TEXT', 'null' => true],
            'intro_id'            => ['type' => 'TEXT', 'null' => true],
            'intro_en'            => ['type' => 'TEXT', 'null' => true],
            'map_media_id'        => $this->ref(true),
            'background_media_id' => $this->ref(true),
            'badge_media_id'      => $this->ref(true),
            'map_x'               => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '50.00'],
            'map_y'               => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '50.00'],
            'is_active'           => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('sequence');
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('difficulty');
        $this->forge->addKey('is_active');
        $this->createGelitaTable('levels');
    }

    public function down(): void
    {
        $this->dropGelitaTable('levels');
    }
}
